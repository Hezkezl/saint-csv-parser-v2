<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:localetest
 */
class localetest implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Top}";
    public function parse()
    {
      include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use

        // loop through data
        $id  = 1;
        $handle = @fopen("cache/Locale/Resource_1769.locale", "r");
        print_r(unpack("C*",$handle));
        // Save some data
        $data = [
                '{Top}' => $id,
        ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeMountWiki.txt
        
        $this->io->text('Saving ...');
        //$info = $this->save('Achievement.txt', 20000);
        $info = $this->save("Avengers Work/localetest.txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}