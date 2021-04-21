<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:Aetheryte
 */
class Aetheryte implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{output}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $this->io->text('Reading CSVs ...');
        $AetheryteCsv = $this->csv('Aetheryte');
        $PlaceNameCsv = $this->csv('PlaceName');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $QuestCsv = $this->csv('Quest');
        $MapCsv = $this->csv('Map');

        
        //levellocations:
        $this->io->text('Generating PopRange Positions ...');
        $this->io->progressStart($TerritoryTypeCsv->total);
        foreach($TerritoryTypeCsv->data as $id => $TerritoryTypeData) {
            $this->io->progressAdvance();
            $JSONMapRangeArray = [];
            $code = substr($TerritoryTypeData['Bg'], -4);
            if (file_exists('cache/'. $PatchID .'/lgb/'. $code .'_planmap.lgb.json')) {
                $url = 'cache/'. $PatchID .'/lgb/'. $code .'_planmap.lgb.json';
                $jdata = file_get_contents($url);
                $decodeJdata = json_decode($jdata);
                foreach ($decodeJdata as $lgb) {
                    $InstanceObjects = $lgb->InstanceObjects;
                    foreach($InstanceObjects as $Object) {
                        $AssetType = $Object->AssetType;
                        $InstanceId = $Object->InstanceId;
                        if ($AssetType != 40) continue;
                        $x = $Object->Transform->Translation->x;
                        $y = $Object->Transform->Translation->z;
                        $LocX = $this->GetLGBPos($x, $y, $id, $TerritoryTypeCsv, $MapCsv)["X"];
                        $LocY = $this->GetLGBPos($x, $y, $id, $TerritoryTypeCsv, $MapCsv)["Y"];
                        $JSONMapRangeArray[$InstanceId] = array(
                            'x' => $LocX,
                            'y' => $LocY,
                        );
                    }
                }
                $JSONTeriArray[$id] = $JSONMapRangeArray;
            }
        }
        $this->io->progressFinish();
        
        $this->io->text('Generating Aetheryte Data ...');

        $this->io->progressStart($AetheryteCsv->total);
        $OutputArray = [];
        $AethernetArray = [];
        foreach ($AetheryteCsv->data as $id => $Aetheryte) {
            $this->io->progressAdvance();
            $Name = $PlaceNameCsv->at($Aetheryte['AethernetName'])['Name'];
            $AethernetGroup = $Aetheryte['AethernetGroup'];
            if ($AethernetGroup === "0") continue;
            $Invisible = "";
            if ($Aetheryte['unknown_19'] === "True"){
                $Invisible = "True";
            }
            $Teri = $Aetheryte['Territory'];
            $Zone = $PlaceNameCsv->at($TerritoryTypeCsv->at($Aetheryte['Territory'])['PlaceName'])['Name'];
            $PopRange = $Aetheryte['Level[0]'];
            $RequiresQuest = "";
            if (!empty($QuestCsv->at($Aetheryte['RequiredQuest'])['Name'])) {
                $RequiresQuest = "Requires Quest : [[".$QuestCsv->at($Aetheryte['RequiredQuest'])['Name']."]]";
            }
            $Pos = "(x:".$JSONTeriArray[$Teri][$PopRange]['x'].", y:".$JSONTeriArray[$Teri][$PopRange]['y'].")";
            $array[$AethernetGroup]["data"][$Zone][] = array (
                "Name" => $Name,
                "Pos" => $Pos,
                "TerritoryType" => $Teri,
                "Quest" => $RequiresQuest,
                "Invisible" => $Invisible,
            );
        }
        var_dump($array);
        foreach ($array as $key => $value) {
            $AetheryteString = "{{{!}} class=\"GEtable\" width=\"33%\"\n";
            $AetheryteString .= "{{!}}-\n";
            $AetheryteString .= "{{!}}colspan=\"3\"{{!}}<center>'''{$value["Zone"]}'''</center>\n";
            $AetheryteString .= "{{!}}-\n";
            $ZoneArray[] = $AetheryteString;
            $afterarray = [];
            foreach ($value["data"] as $key1 => $value1) {
                if (empty($value1["Invisible"])) {
                    $AetheryteString = "{{!}}{$value1["Name"]}\n";
                    $AetheryteString .= "{{!}}{$value1["Pos"]}\n";
                    $AetheryteString .= "{{!}}{$value1["Quest"]}\n";
                    $AetheryteString .= "{{!}}-\n";
                    $ZoneArray[] = $AetheryteString;
                }
                if (!empty($value1["Invisible"])) {
                    $afterarray[] = "[[".$value1["Name"]."]]";
                }
            }
            $afterout = join(' and ', array_filter(array_merge(array(join(', ', array_slice($afterarray, 0, -1))), array_slice($afterarray, -1)), 'strlen'));
            $afterstring = "}\n";
            $afterstring .= "Upon obtaining all of the above, you will also have Aethernet access to invisible shards at $afterout.\n";
            $ZoneArray[] = $afterstring;
            $ZoneArray[] = "\n{{!}}-{{!}}\n";
            $ZoneArray[] = "MAP IMAGE HERE";
        }
        $ZoneArray = implode("\n", $ZoneArray);
        //var_dump($ZoneArray);
        $this->saveExtra("Aetheryte.mediawiki",$ZoneArray);
        


        // Save some data
        $data = [
            '{output}' => $output,
        ];

        // format using Gamer Escape formatter and add to data array
        // need to look into using item-specific regex, if required.
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Aetheryte.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}

/*
11th April 2021 - Creation
*/