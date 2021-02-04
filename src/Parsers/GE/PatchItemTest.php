<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * php bin/console app:parse:csv GE:PatchItemTest
 */

class PatchItemTest implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{item}';

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files
        $ItemCsv = $this->csv("Item");

        $this->io->progressStart($ItemCsv->total);

        //This checks if the patch file exists, if not then makes it + updates it with new info
        $this->PatchCheck($Patch, "Item", $ItemCsv, "Name");

        
    /**
     * this will make an array to use all over the sheet, 
     * usage: $PatchNumber[Name of item or ID depending on what is set above]
     * Example: $PatchNumber[Rhodolite] will output "5.25"
     */
        $PatchNumber = $this->getPatch("Item");
        // loop through test data
        foreach ($ItemCsv->data as $id => $item) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();
            if (empty($item['Name'])) continue;
            $ItemName = $item['Name'];
            $PatchNo = $PatchNumber[$ItemName];
            $PatchString = "This items name is $ItemName and patch set is $PatchNo";
            //var_dump($PatchString);
            

            //---------------------------------------------------------------------------------

            $data = [
                '{item}' => $PatchString,
            ];

            // format using Gamer Escape formatter and add to data array
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeSatisfactionWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("$CurrentPatchOutput/PatchItemTest - ". $Patch .".txt", 999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}