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
|AquariumSize ={folklore}
}}{{-stop-}}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $FishParameterCsv = $this->csv('FishParameter');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $PlaceNameCsv = $this->csv('PlaceName');
        $GatheringItemLevelConvertTableCsv = $this->csv('GatheringItemLevelConvertTable');
        $GatheringSubCategoryCsv = $this->csv('GatheringSubCategory');

        // (optional) start a progress bar
        $this->io->progressStart($FishParameterCsv->total);

        // loop through data
        foreach ($FishParameterCsv->data as $id => $fish) {
            $this->io->progressAdvance();

            //skip ones with no data
            if (empty($fish['Text'])) {
                continue;
            }

            $Name = str_replace("#", "", ($ItemCsv->at($fish['Item'])['Name']));
            $Territory = $TerritoryTypeCsv->at($fish['TerritoryType'])['PlaceName'];
            $Location = $PlaceNameCsv->at($Territory)['Name'];
            $Fishtype = [
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
            $Level = $GatheringItemLevelConvertTableCsv->at($fish['GatheringItemLevel'])['GatheringItemLevel'];
            $Star = str_repeat("{{Star}}", $GatheringItemLevelConvertTableCsv->at($fish['GatheringItemLevel'])['Stars']);
            $LevelStar = "$Level $Star";

            $Folklore = false;
            if (!empty($fish['GatheringSubCategory'])) {
                $Folklore = $ItemCsv->at($GatheringSubCategoryCsv->at($fish['GatheringSubCategory'])['Item'])['Name'];
            }

            // Fishing Drawing Icon copying
            $IconNumber = $ItemCsv->at($fish['Item'])['Icon'];
            $Drawing = substr($IconNumber, -4);
            $DrawingIcon = str_pad($Drawing, "6", "078", STR_PAD_LEFT);

            // ensure output directory exists
            $IconOutputDirectory = $this->getOutputFolder() . "/$PatchID/CavemanFishingIcons/Fishing";
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
                '{location}' => $Location,
                //'{level}' => $fish['GatheringItemLevel'],
                //'{level}' => $levelstar,
                '{level}' => ($GatheringItemLevelConvertTableCsv->at($fish['GatheringItemLevel'])['Stars'] > 0) ? $LevelStar : $Level,
                '{type}' => $Fishtype[$fish['FishingRecordType']],
                '{description}' => $fish['Text'],
                '{folklore}' => $Folklore ? "\n|Folklore = $Folklore" : "",
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // (optional) finish progress bar
        $this->io->progressFinish();

        // save
        $this->io->text('Saving data ...');
        $info = $this->save("FishParameter.txt", 999999);
    }
}