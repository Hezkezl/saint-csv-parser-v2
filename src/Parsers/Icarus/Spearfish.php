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
|FishType = Spearfishing
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
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $SpearfishItemCsv = $this->csv('SpearfishingItem');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $PlaceNameCsv = $this->csv('PlaceName');
        $GatheringItemLevelConvertTableCsv = $this->csv('GatheringItemLevelConvertTable');

        // (optional) start a progress bar
        $this->io->progressStart($SpearfishItemCsv->total);

        // loop through data
        foreach ($SpearfishItemCsv->data as $id => $Spear) {
            $this->io->progressAdvance();

            //skip ones with no data
            if (empty($Spear['Description'])) {
                continue;
            }

            $Name = str_replace("#", "", ($ItemCsv->at($Spear['Item'])['Name']));
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

            // Fishing Drawing Icon copying
            $IconNumber = $ItemCsv->at($Spear['Item'])['Icon'];
            $Drawing = substr($IconNumber, -4);
            $DrawingIcon = str_pad($Drawing, "6", "078", STR_PAD_LEFT);

            // ensure output directory exists
            $IconOutputDirectory = $this->getOutputFolder() . "/$PatchID/CavemanFishingIcons/Spearfishing";
            // if it doesn't exist, make it
            if (!is_dir($IconOutputDirectory)) {
                mkdir($IconOutputDirectory, 0777, true);
            }

            // build icon input folder paths
            $LargeIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($DrawingIcon);

            // give correct file names to icons for output
            $LargeIconFileName = "{$IconOutputDirectory}/Model-$Name.png";
            // actually copy the icons
            copy($LargeIconPath, $LargeIconFileName);


            // Save some data
            $data = [
                '{name}' => $Name,
                '{location}' => $SpearLocation,
                '{level}' => ($GatheringItemLevelConvertTableCsv->at($Spear['GatheringItemLevel'])['Stars'] > 0) ? $SpearLevelStar : $SpearLevel,
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
        $info = $this->save("Spearfish.txt", 999999);
    }
}