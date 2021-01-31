<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:FishingSpot
 */
class FishingSpot implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
'''Fishing Log: {name}'''
{{ARR Infobox Fishing Log
| Location = {name}
| Coordinates =
| Level = {level}
| Type = {water}
| Requirements =
| Map = {name}-Fishing.jpg
| Fish =
{fish}
}}{{-stop-}}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $FishingSpotCsv = $this->csv('FishingSpot');
        $PlaceNameCsv = $this->csv('PlaceName');

        // (optional) start a progress bar
        $this->io->progressStart($FishingSpotCsv->total);

        // loop through data
        foreach ($FishingSpotCsv->data as $id => $fishingspot) {
            $this->io->progressAdvance();

            //skip ones with no data
            if (empty($fishingspot['PlaceName'])) {
                continue;
            }

            $Location = $PlaceNameCsv->at($fishingspot['PlaceName'])['Name'];

            $Fish = [];
            foreach(range(0,9) as $i) {
                if (!empty($fishingspot["Item[$i]"])) {
                    $Item = $ItemCsv->at($fishingspot["Item[$i]"])["Name"];
                    $string = "{{ARR Infobox Fishing\n|Item            = $Item\n|FishingLoc      = $Location\n";
                    $string .= "|HoleBait        = \n|HoleConditions  = \n|Normal Weather  = \n|Weather Chain   = \n";
                    $string .= "|Mooch           = \n|Mooch Chain     = \n|Intuition       = \n|Intuition Count = \n}}";
                    $Fish[] = $string;
                }
            }

            $Watertype = [
                0 => "Coastlines",
                1 => "Deep Sea",
                2 => "Rivers",
                3 => "Lakes",
                4 => "Sands",
                5 => "Skies",
                6 => "Floating Islands",
                7 => "Magma",
                8 => "Aetherochemical Spills",
                9 => "Salt Lakes",
                10 => "Gig{{!}}Small Gig Head",
                11 => "Gig{{!}}Normal Gig Head",
                12 => "Gig{{!}}Large Gig Head",
                13 => "Gig{{!}}Small Gig Head",
                14 => "Gig{{!}}Normal Gig Head",
                15 => "Gig{{!}}Large Gig Head",
                16 => "Gig{{!}}Small Gig Head",
                17 => "Gig{{!}}Normal Gig Head",
                18 => "Gig{{!}}Large Gig Head",
                19 => "Gig{{!}}Small Gig Head",
                20 => "Gig{{!}}Normal Gig Head",
                21 => "Gig{{!}}Large Gig Head",
                22 => "Gig{{!}}Small Gig Head",
                23 => "Gig{{!}}Normal Gig Head",
                24 => "Gig{{!}}Large Gig Head",
                25 => null,
                26 => null,
            ];

            // Save some data
            $data = [
                '{name}' => $Location,
                '{level}' => $fishingspot['GatheringLevel'],
                '{water}' => $Watertype[$fishingspot['FishingSpotCategory']],
                '{fish}' => implode("\n", $Fish),
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // (optional) finish progress bar
        $this->io->progressFinish();

        // save
        $this->io->text('Saving data ...');
        $info = $this->save("$CurrentPatchOutput/FishingSpot - ". $Patch .".txt", 999999);
    }
}
