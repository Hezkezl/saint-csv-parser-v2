<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Spearfish
 */
class Spearfish implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
'''{name}/Fishlog'''
{{Fishlog
|Name = {name}
|RecommendedLevel = {level}
|FishType =
|FishSizeLarge =
|FishSizeSmall =
|PrimeLocations = {location}
|Bait = {type}
|Fishing Log Description = {description}
|AquariumType =
|AquariumSize =
}}{{-stop-}}";

    public function parse()
    {
        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $SpearfishItemCsv = $this->csv('SpearfishingItem');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $PlaceNameCsv = $this->csv('PlaceName');
        $GatheringItemLevelConvertTableCsv = $this->csv('GatheringItemLevelConvertTable');

        // loop through data
        foreach ($SpearfishItemCsv->data as $id => $Spear) {
            $this->io->progressAdvance();

            //skip ones with no data
            if (empty($Spear['Description'])) {
                continue;
            }

            $SpearName = $ItemCsv->at($Spear['Item'])['Name'];
            $Territory = $TerritoryTypeCsv->at($Spear['TerritoryType'])['PlaceName'];
            $SpearLocation = $PlaceNameCsv->at($Territory)['Name'];
            $SpearfishType = [
                0 => null,
                1 => null,
                2 => null,
                3 => null,
                4 => null,
                5 => null,
                6 => null,
                7 => null,
                8 => null,
                9 => null,
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
                26 => "Gig{{!}}Small Gig Head, Gig{{!}}Normal Gig Head, Gig{{!}}Large Gig Head",
            ];

            $SpearLevel = $GatheringItemLevelConvertTableCsv->at($Spear['GatheringItemLevel'])['GatheringItemLevel'];
            $SpearStar = str_repeat("{{Star}}", $GatheringItemLevelConvertTableCsv->at($Spear['GatheringItemLevel'])['Stars']);
            $SpearLevelStar = "$SpearLevel $SpearStar";

            // Save some data
            $data = [
                '{name}' => $SpearName,
                '{location}' => $SpearLocation,
                '{level}' => $SpearLevelStar,
                '{type}' => $SpearfishType[$Spear['FishingRecordType']],
                '{description}' => $Spear['Description'],
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        }

        // (optional) finish progress bar
        $this->io->progressFinish();

        // save
        $this->io->text('Saving data ...');
        $this->save('SpearFish.txt');
    }
}
