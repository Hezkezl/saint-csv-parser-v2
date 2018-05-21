<?php

namespace App\Parsers\XIVDB;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * XIVDB:Cabinet
 */
class Cabinet implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        // grab CSV files we want to use
        $CabinetCsv = $this->csv('Cabinet');

        // loop through data
        foreach($CabinetCsv->data as $id => $cabinet) {
            // add to array
            $this->data[] = [
                'id' => "'{$cabinet['id']}'",
                'item' => "'{$cabinet['Item']}'",
                'order' => "'{$cabinet['Order']}'",
                'category' => "'{$cabinet['Category']}'",
            ];
        }

        // build sql - HAX
        $sql = [];
        foreach($this->data as $row) {
            $sql[] = 'INSERT INTO xiv_cabinet (`id`, `item`, `order`, `category`) VALUES ('. implode(',', $row) .');';
        }

        $this->save('CabinetSql', 10000, $sql);
    }
}
