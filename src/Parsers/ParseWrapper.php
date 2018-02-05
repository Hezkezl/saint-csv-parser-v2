<?php

namespace App\Parsers;

use League\Csv\Reader;
use League\Csv\Statement;

class ParseWrapper
{
    public $data;
    public $content;
    public $filename;
    public $columns;
    public $offsets;
    public $total;

    public function __construct($content, $filename)
    {
        $this->content = $content;
        $this->filename = $filename;

        // grab CSV
        $this->csv = Reader::createFromPath($filename);

        // get columns
        $stmt = (new Statement())->offset(1)->limit(1);
        $this->columns = $stmt->process($this->csv)->fetchOne();
        $this->columns[0] = $this->columns[0] == '#' ? 'id' : $this->columns[0];
        $this->offsets = array_flip(array_filter($this->columns));

        // get data
        $stmt = (new Statement())->offset(3);
        $records = $stmt->process($this->csv)->getRecords();

        foreach($records as $i => $record) {
            $id = $record[0];

            // set column names for each record entry
            foreach($record as $offset => $value) {
                $columnName = $this->columns[$offset];
                if (empty($columnName)) {
                    $columnName = 'unknown_'. $offset;
                }

                $record[$columnName] = $value;
                unset($record[$offset]);
            }

            $this->data[$id] = $record;
        }

        $this->total = count($this->data);
    }

    /**
     * Get a row at a specific index
     */
    public function at($id)
    {
        return $this->data[$id] ?? false;
    }

    /**
     * Find rows that match some value
     */
    public function find($column, $value)
    {
        $res = [];
        foreach($this->data as $id => $row) {
            $rowValue = $row[$column];

            if ($rowValue == $value) {
                $res[] = $row;
                break;
            }
        }

        return $res;
    }
}
