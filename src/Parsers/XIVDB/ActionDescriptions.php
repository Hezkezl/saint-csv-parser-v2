<?php

namespace App\Parsers\XIVDB;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * XIVDB:ActionDescriptions
 */
class ActionDescriptions implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        // grab CSV files we want to use
        $ActionTransientCsv = $this->csv('ActionTransient');

        // loop through data
        foreach($ActionTransientCsv->data as $id => $desc) {
            $this->io->text("Action: {$id}");

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
