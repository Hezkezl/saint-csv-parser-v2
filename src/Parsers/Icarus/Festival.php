<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Festival
 */
class Festival implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Output}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $FestivalCsv = $this->csv('Festival');

        // (optional) start a progress bar
        $this->io->progressStart($FestivalCsv->total);
        
        $jdata = file_get_contents("Patch/FestivalNames.json");
        $FestivalArray = json_decode($jdata, true);
        
        $Paths = array_diff(scandir("cache/$PatchID/lgb/"), array('..', '.'));
        $JsonArray = [];
        foreach ($Paths as $Path) {
            $this->io->progressAdvance();
            $url = "cache/$PatchID/lgb/$Path";
            $jdatar = file_get_contents($url);
            $decodeJdata = json_decode($jdatar);
            foreach ($decodeJdata as $lgb) {
                $Festival = $lgb->FestivalID;
                if (isset($FestivalArray[$Festival])) continue;
                if (!isset($FestivalArray[$Festival])) {
                    $Name = $lgb->Name;
                    $FestivalArray[$Festival] = $Name;
                }
            }
        }
        ksort($FestivalArray);
        $JSONOUTPUT = json_encode($FestivalArray, JSON_PRETTY_PRINT);
        if (!file_exists("Patch")) { mkdir("Patch", 0777, true); }
        $JSON_File = fopen("Patch/FestivalNames.json", 'w');
        fwrite($JSON_File, $JSONOUTPUT);
        fclose($JSON_File);
        $Output = implode("\n",$JsonArray);

        $data = [
            '{Output}' => $Output,
        ];

        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeEventItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Festivals.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}