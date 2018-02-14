<?php

namespace App\Parsers\XIVDB;

use App\Parsers\CsvParseDataHandlerTrait;
use App\Parsers\CsvParseTrait;
use App\Parsers\ParseHandler;
use App\Parsers\ParseInterface;

/**
 * XIVDB:ActionDescriptions
 */
class ActionDescriptions extends ParseHandler implements ParseInterface
{
    use CsvParseTrait;
    use CsvParseDataHandlerTrait;

    public function parse()
    {
        // grab CSV files we want to use
        $ActionTransientCsv = $this->csv('ActionTransient');
        $this->output->writeln(['','']);

        if (file_exists(__DIR__ .'/ActionDescriptionsConditions.json')) {
            unlink(__DIR__ .'/ActionDescriptionsConditions.json');
        }

        if (file_exists(__DIR__ .'/ActionDescriptionsConditions_simple.txt')) {
            unlink(__DIR__ .'/ActionDescriptionsConditions_simple.txt');
        }

        // loop through data
        foreach($ActionTransientCsv->data as $id => $desc) {
            $this->output->writeln("Action: {$id}");

            if (empty($desc['Description'])) {
                continue;
            }

            $formatter = new ActionDescriptionFormatter();
            $description = $formatter->format($desc['Description']);

            $this->save('ActionDescriptions', [
                'id' => $desc['id'],
                'description' => $description,
            ]);
        }
    }
}
