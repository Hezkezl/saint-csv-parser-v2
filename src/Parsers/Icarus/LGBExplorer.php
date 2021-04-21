<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:LGBExplorer
 */
class LGBExplorer implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{output}";
    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $TerritoryTypeCsv = $this->csv('TerritoryType');


        // (optional) start a progress bar
        $this->io->progressStart($TerritoryTypeCsv->total);

        // loop through data
        foreach ($TerritoryTypeCsv->data as $id => $TeriData) {
            $this->io->progressAdvance();

            // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
            // manually, set to false
            if (empty($TeriData['Bg'])) continue;
            $bg = substr($TeriData['Bg'], 0, -4);
            $string = "bg/". $bg ."bg.lbg
bg/". $bg ."planevent.lgb
bg/". $bg ."planlive.lgb
bg/". $bg ."planmap.lgb
bg/". $bg ."planner.lgb
bg/". $bg ."sound.lgb
bg/". $bg ."vfx.lgb";

            // Save some data
            $data = [
                '{output}' => $string,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        };

        // save our data to the filename: GeMountWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('LGBExplorer.txt', 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}