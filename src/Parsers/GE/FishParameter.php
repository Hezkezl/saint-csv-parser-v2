<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:FishParameter
 */
class FishParameter implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
'''{name}/Fishlog'''
{{Fishlog
|Name = {name}
|RecommendedLevel = {level}
|FishType = {type}
|FishSizeLarge =
|FishSizeSmall =
|PrimeLocations = {location}
|Bait =
|Fishing Log Description = {description}
|AquariumType =
|AquariumSize =
}}{{-stop-}}";
    public function parse()
    {
        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $FishParameterCsv = $this->csv('FishParameter');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $PlaceNameCsv = $this->csv('PlaceName');

        // (optional) start a progress bar
        $this->io->progressStart($FishParameterCsv->total);

        // loop through data
        foreach ($FishParameterCsv->data as $id => $fish) {
            $this->io->progressAdvance();

            //skip ones with no data
            if (empty($fish['Text'])) {
                continue;
            }

            $name = $ItemCsv->at($fish['Item'])['Name'];
            $territory = $TerritoryTypeCsv->at($fish['TerritoryType'])['PlaceName'];
            $location = $PlaceNameCsv->at($territory)['Name'];
            $fishtype = [
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
                10 => "Small Gig Head",
                11 => "Normal Gig Head",
                12 => "Large Gig Head",
                13 => "Small Gig Head",
                14 => "Normal Gig Head",
                15 => "Large Gig Head",
                16 => "Small Gig Head",
                17 => "Normal Gig Head",
                18 => "Large Gig Head",
                19 => "Small Gig Head",
                20 => "Normal Gig Head",
                21 => "Large Gig Head",
                22 => "Small Gig Head",
                23 => "Normal Gig Head",
                24 => "Large Gig Head",
                25 => null,
                26 => null,
            ];

            // Save some data
            $data = [
                '{name}' => $name,
                '{location}' => $location,
                '{level}' => $fish['GatheringItemLevel'],
                '{type}' => $fishtype[$fish['FishingRecordType']],
                '{description}' => $fish['Text'],
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // (optional) finish progress bar
        $this->io->progressFinish();

        // save
        $this->io->text('Saving data ...');
        $this->save('FishParameter.txt');
    }
}
