<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:drmextract
 */
class drmextract implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "";
    public function parse()
    {
        // grab CSV files we want to use

        include (dirname(__DIR__) . '/Paths.php');
        // grab CSV files we want to use
        //$territoryTypeCsv = $this->csv("TerritoryType");
        //$mapCsv = $this->csv("Map");
        $data = file_get_contents("cache/Resource_12219.locale");

        $header = unpack('l*', $data);

        $signature = $header[1];

        var_dump($signature);
        

            // Save some data
            $data = [
                //'{X}' => $X,
            ];


            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

            // save our data to the filename: test.txt
            $this->io->text('Saving ...');
            //$info = $this->save('QuickMapCalc.txt', 9999999);

            $this->io->table(
                [ 'Filename', 'Data Count', 'File Size' ],
                $info
            );
    }
}