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

        // loop through data
        foreach($ActionTransientCsv->data as $id => $desc) {
            $this->output->writeln("Action: {$id}");

            // skip blank descriptions
            if (empty($desc['Description'])) {
                continue;
            }

            // get formatter, format description
            $formatter = new ActionDescriptionFormatter();
            $description = $formatter->format($desc['Description']);

            // save to output
            $this->save('ActionDescriptions', [
                'id' => $desc['id'],
                'description' => $description,
            ]);
        }
    }
}
