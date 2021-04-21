<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:localeextract
 */
class localeextract implements ParseInterface
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
        $fh = fopen ("cache/Resource_12219.locale", "rb");
        $data = fread ($fh, 8);

        $header = unpack ("C1highbit/".
        "I1padding/".
        "I2padding2/".
        "I3padding3/".
        "C1eol", $data);
        var_dump($header);
        if (is_array ($header) && $header['highbit'] == 0x10) {
            print "This is a valid Locale file\n";
          }

        //var_dump($header);
        
        //$data = fread ($fh, 8);
        //$info = unpack ("N1length/A4type", $data);
        //$data = fread ($fh, 4);
        //$crc = unpack ("N1crc", $data);
        //$chunk['crc'] = $crc['crc'];
        //var_dump($chunk);
//
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