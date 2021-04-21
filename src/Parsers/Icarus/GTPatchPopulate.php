<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * php bin/console app:parse:csv GE:GTPatchPopulate
 */


class GTPatchPopulate implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{item}';

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');
        /** 
         * CHANGE THE BELOW TO THE SHEET YOU NEED:::::
         */
            // grab CSV files
            $QuestCsv = $this->csv("Quest");
        
        // $PSheet = "Achievement";
        $KeyName = "id";

        $SheetJSON = [];
        $QPatchArray = [];
        $this->io->progressStart($QuestCsv->total);
        foreach ($QuestCsv->data as $id => $Quest) { 
            $this->io->progressAdvance();

            if (empty($Quest['Name'])) continue;
            //get patch data into array
            //Grab the required sheet data
            $SheetURL = "https://garlandtools.org/db/doc/quest/en/2/$id.json";
            $SheetContents = file_get_contents($SheetURL);
            $SheetJdata = json_decode($SheetContents);
            foreach ($SheetJdata as $SheetData => $Value) {
                $PatchNo = $SheetJdata -> quest -> patch;
                $QPatchArray[$id] = number_format($PatchNo, 1);
            }
        }

            $JSONOUTPUT = json_encode($QPatchArray, JSON_PRETTY_PRINT);
            //write Api file
            if (!file_exists("Patch/")) { mkdir("Patch/", 0777, true); }
            $JSON_File = fopen("Patch/Quest.json", 'w');
            fwrite($JSON_File, $JSONOUTPUT);
            fclose($JSON_File);
            $this->io->progressFinish();

        // loop through test data
        //foreach ($SheetCsv->data as $id => $CsvSheetData) {
        //    // ---------------------------------------------------------
        //    $this->io->progressAdvance();
        //    if (empty($CsvSheetData['Name'])) continue;
//
        //    //---------------------------------------------------------------------------------
        //
        //    $data = [
        //        '{item}' => $PatchString,
        //    ];
        //
        //    // format using Gamer Escape formatter and add to data array
        //    $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        //}

        // save our data to the filename: GeSatisfactionWiki.txt
        //$this->io->text('Saving ...');
        //$info = $this->save("$CurrentPatchOutput/PatchPopulate - ". $Patch .".txt", 999999);
//
        //$this->io->table(
        //    ['Filename', 'Data Count', 'File Size'],
        //    $info
        //);
    }
}