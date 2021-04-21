<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:InclusionShop
 */
class InclusionShop implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Top}";
    public function parse()
    {
      include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $InclusionShopCsv = $this->csv("InclusionShop");
        $InclusionShopCategoryCsv = $this->csv("InclusionShopCategory");
        $InclusionShopSeriesCsv = $this->csv("InclusionShopSeries");
        $SpecialShopCsv = $this->csv("SpecialShop");


        // (optional) start a progress bar
        $this->io->progressStart($InclusionShopCsv->total);

        // loop through data
        foreach ($InclusionShopCsv->data as $id => $InclusionShop) {
            $this->io->progressAdvance();
            $InclusionShopArray = [];
            foreach(range(0,27) as $a) {
                $CategoryRaw = $InclusionShop["Category[$a]"];
                $CategoryName = $InclusionShopCategoryCsv->at($CategoryRaw)['Name'];
                if (empty($CategoryName)) continue;
                $SeriesLink = $InclusionShopCategoryCsv->at($CategoryRaw)['InclusionShopSeries'];
                $SpecialShopArray = [];
                foreach(range(0,9) as $i) {
                    $SeriesLinkID = "". $SeriesLink ."." .$i."";
                    if (empty($InclusionShopSeriesCsv->at($SeriesLinkID)['SpecialShop'])) continue;
                    $SpecialShopLink = $InclusionShopSeriesCsv->at($SeriesLinkID)['SpecialShop'];
                    $SpecialShopName = $SpecialShopCsv->at($SpecialShopLink)['Name'];
                    $SpecialShopArray[] = "". $SeriesLinkID ." = ". $SpecialShopName ."";
                };
                $SpecialShopArray = implode("\n", $SpecialShopArray);
                $InclusionShopArray[0] = "\nID = ". $id ."";
                $InclusionShopArray[] = "\nName = ". $CategoryName ."\nShops = \n". $SpecialShopArray ."";
            };
            $InclusionShopArray = implode("\n", $InclusionShopArray);
            

            // Save some data
            $data = [
                '{Top}' => $InclusionShopArray,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        };

        // save our data to the filename: GeMountWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        //$info = $this->save('Achievement.txt', 20000);
        $info = $this->save("$CurrentPatchOutput/InclusionShop - ". $Patch .".txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}