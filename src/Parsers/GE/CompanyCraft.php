<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * php bin/console app:parse:csv GE:CompanyCraft
 */
class CompanyCraft implements ParseInterface
{
    use CsvParseTrait;

    /**
     * this will hold all our CSV data
     * @var array
     */
    private $csvData;

    /**
     * list of CSV's to download
     * @var array
     */
    private $csvFiles = [
        'CompanyCraftSequence',
        'CompanyCraftPart',
        'CompanyCraftProcess',
        'CompanyCraftSupplyItem',
        'CompanyCraftDraft',
        'Item'
    ];

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '
        https://ffxiv.gamerescape.com/w/index.php?title={resulturl}/Recipe&action=edit
        {{ARR Infobox FCRecipe
        | Acquired = {draftcsv}
        | Result = {result}
        {phasedata}
    }}';

    public function parse()
    {
        // console writer
        $console = new ConsoleOutput();


        // download our CSV files
        $console->writeln(" Loading CSVs");

        foreach ($this->csvFiles as $filename) {
            $console->writeln(" >> {$filename}");
            $this->csvData[$filename] = $this->csv($filename);
        }

        $console->writeln(" Processing Company Craft data");

        // switch to a section so we can overwrite
        $console = $console->section();

        // loop through our sequences
        foreach ($this->getData('CompanyCraftSequence')->data as $sequence) {
            $id = $sequence['id'];

            // skip id 0
            if ($id == 0) continue;

            // reset our data row
            $phaseData = [];

            // each sequence has 8 parts
            $numberOfParts = range(0, 7);

            // loop through our parts
            foreach ($numberOfParts as $partNumber) {
                // grab the part id
                $partId = $sequence[sprintf('CompanyCraftPart[%s]', $partNumber)];

                // if id == 0, skip
                if ($partId == 0) continue;

                // grab the 'part row' from the 'part sheet', using our 'part id'
                $partRow = $this->getData('CompanyCraftPart', $partId);

                // each part has 3 processes
                $numberOfProcesses = range(0, 2);

                // loop through our processes
                foreach ($numberOfProcesses as $processNumber) {
                    // grab the process id
                    $processId = $partRow[sprintf('CompanyCraftProcess[%s]', $processNumber)];

                    // if id == 0, skip
                    if ($processId == 0) continue;

                    // grab the 'process row' from the 'process sheet', using our 'process id
                    $processRow = $this->getData('CompanyCraftProcess', $processId);

                    // each process has 12 items (with quantities and requirements)
                    $numberOfItems = range(0, 11);

                    // loop through our item sets
                    foreach ($numberOfItems as $itemNumber) {
                        // grab some info from the process row
                        $supplyItemId = $processRow[sprintf('SupplyItem[%s]', $itemNumber)];
                        $setQuantity  = $processRow[sprintf('SetQuantity[%s]', $itemNumber)];
                        $setsRequired = $processRow[sprintf('SetsRequired[%s]', $itemNumber)] * $setQuantity;

                        // if item id 0, ignore
                        if ($supplyItemId == 0) continue;

                        // get item via CompanyCraftSupplyItem
                        $item = $this->getData('CompanyCraftSupplyItem', $supplyItemId);
                        $item = $this->getData('Item', $item['Item']);

                        // increment phase numbers by 1 just for the text output
                        $num1 = $processNumber + 1;
                        $num2 = $itemNumber + 1;

                        // store our text
                        $phaseData[] = "| Phase {$num1} {$num2} = {$item['Name']}";
                        $phaseData[] = "| Phase {$num1} {$num2} Amount = {$setQuantity}/{$setsRequired}\n";
                    }
                }
            }


            if (empty($phaseData)) {
                continue;
            }

            // get company craft draft name
            $companyCraftDraftId   = $sequence['CompanyCraftDraft'];
            $companyCraftDraftName = $this->getData('CompanyCraftDraft', $companyCraftDraftId)['Name'];
            $companyCraftDraftName = ucwords(strtolower($companyCraftDraftName));

            // get our result item
            $resultItemId   = $sequence['ResultItem'];
            $resultItemName = $this->getData('Item', $resultItemId)['Name'];
            $resultItemUrl  = str_replace(" ", "_", $resultItemName);

            // build our data array using the GE Formatter
            $data = GeFormatter::format(self::WIKI_FORMAT, [
                '{draftcsv}'  => $companyCraftDraftName,
                '{result}'    => $resultItemName,
                '{resulturl}' => $resultItemUrl,
                '{phasedata}' => implode("\n", $phaseData)
            ]);

            $this->data[] = $data;

            $console->overwrite(" > Completed Sequence: {$id} --> {$resultItemName}");
        }

        // save
        $console->writeln(" Saving... ");
        $this->save("CompanyCraft.txt", 9999999);
    }
}
