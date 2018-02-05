<?php

namespace App\Parsers\Examples;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

class ExampleItem implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        $this->output->writeln([
            '------------------------------------------------------',
            '<comment>Example Title</comment>',
            '------------------------------------------------------',
        ]);

        // get a CSV file
        $itemCsv = $this->getCsvFile('Item');

        // loop through the data
        foreach($itemCsv->data as $id => $row) {
            $this->output->write('Item Name: '. $row['Name']);
        }
    }
}
