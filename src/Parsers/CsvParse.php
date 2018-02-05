<?php

namespace App\Parsers;

class CsvParse
{
    /**
     * Add parsers here
     */
    public function get($parser)
    {
        switch($parser) {
            case 'XIVDB_InstanceContent': return \App\Parsers\XIVDB\InstanceContent::class;
            case 'Example_Item': return \App\Parsers\Examples\ExampleItem::class;
        }

        return false;
    }
}
