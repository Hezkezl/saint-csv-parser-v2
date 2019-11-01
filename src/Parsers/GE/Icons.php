<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Icons
 */
class Icons implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        $saveto = __DIR__.'/icons';
        $url = 'https://xivapi.com/item?key=db4c36d2ba9a4f5c917f2566&limit=1000&columns=ID,Name&page=%s';
        @mkdir($saveto);

        foreach(range(25, 29) as $page) {
            // grab all items
            $items = json_decode(file_get_contents(sprintf($url, $page)));

            foreach ($items->Results as $i => $item) {
                $filename = "{$saveto}/{$item->Name}_Icon.png";

                // skip if we already have it
                if (file_exists($filename)) {
                    continue;
                }

                // copy file (if it doesn't exist, it wont copy)
                $i++;
                @copy("https://xivapi.com/i2/ls/{$item->ID}.png", $filename);
                echo "Page: {$page} ({$i} / {$items->Pagination->Results}) - {$item->Name}\n";
            }
        }
    }
}
