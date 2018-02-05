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
            // XIVDB
            case 'XIVDB_InstanceContent': return \App\Parsers\XIVDB\XIVDB_InstanceContent::class;

            // GamerEscape
            case 'GE_Quests': return \App\Parsers\GE\GE_Quests::class;

            // Examples
            case 'Example_Item': return \App\Parsers\Examples\ExampleItem::class;
        }

        return false;
    }
}
