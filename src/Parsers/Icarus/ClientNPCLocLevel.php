<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * php bin/console app:parse:csv GE:ClientNPCLocLevel
 */
class ClientNPCLocLevel implements ParseInterface
{
    use CsvParseTrait;


    // the wiki output format / template we shall use
    const WIKI_FORMAT = "
        {{-start-}}
        '''{name}/Map/{objectid}'''
        {{NPCMap
          | base = {mapcode} - {map}{sub}.png
          | float_link = {name}
          | float_caption = {name} 
          | float_caption_coordinates = (x:{x2}, y:{y2})
          | x = {pixX}
          | y = {pixY}
          | zone = {map}
          | level_id = {id}
          | npc_id = {objectid}{NpcIssues}
          | patch = {patch}
          | Sublocation = {sublocation}
        }}
        {{-stop-}}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        $levelcsv = $this->csv('Level');
        $mapcsv = $this->csv('Map');
        $residentcsv = $this->csv('ENpcResident');
        $ENpcBaseCsv = $this->csv('ENpcBase');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $PlaceNameCsv = $this->csv('PlaceName');
        $QuestCsv = $this->csv('Quest');
        $MapMarkerCsv = $this->csv('MapMarker');

        // console writer
        $console = new ConsoleOutput();


        // download our CSV files
        $console->writeln(" Loading CSVs");
        

        $console->writeln(" Processing Level at ENpc Location data");
        var_dump($PatchID);
        // switch to a section so we can overwrite
        $console = $console->section();

        //planmap used
        //generate json
        $console->writeln(" Generating Map Range JSON Data ....");
        foreach($TerritoryTypeCsv->data as $id => $TerritoryTypeData) {
            $JSONMapRangeArray = [];
            $code = substr($TerritoryTypeData['Bg'], -4);
            if (file_exists('cache/'. $PatchID .'/lgb/'. $code .'_planmap.lgb.json')) {
                $url = 'cache/'. $PatchID .'/lgb/'. $code .'_planmap.lgb.json';
                $jdata = file_get_contents($url);
                $decodeJdata = json_decode($jdata);
                $mapLink = $TerritoryTypeData['Map'];
                foreach ($decodeJdata as $lgb) {
                    $InstanceObjects = $lgb->InstanceObjects;
                    foreach($InstanceObjects as $Object) {
                        $AssetType = $Object->AssetType;
                        if ($AssetType != 43) continue;
                        if ($Object->Object->PlaceNameEnabled == 0) continue;
                        $x = $Object->Transform->Translation->x;
                        $y = $Object->Transform->Translation->z;
                        if (!empty($x)) {
                            $scale = $mapcsv->at($mapLink)['SizeFactor'];
                        }
                        $c = $scale / 100.0;
                        $offsetx = $mapcsv->at($mapLink)['Offset{X}'];
                        $offsetValueX = ($x + $offsetx) * $c;
                        $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                        $offsety = $mapcsv->at($mapLink)['Offset{Y}'];
                        $offsetValueY = ($y + $offsety) * $c;
                        $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                        $PlaceName = $PlaceNameCsv->at($Object->Object->PlaceNameSpot)['Name'];
                        if (empty($PlaceName)) {
                            $PlaceName = $PlaceNameCsv->at($Object->Object->PlaceNameBlock)['Name'];
                        }
                        $JSONMapRangeArray[] = array(
                            'placename' => $PlaceName,
                            'x' => round($NpcLocX, 1),
                            'y' => round($NpcLocY, 1)
                        );
                    }
                }
                $JSONTeriArray[$id] = $JSONMapRangeArray;
            }
        }
        //$JsonData = json_encode($JSONTeriArray, JSON_PRETTY_PRINT);
        //var_dump($JSONTeriArray[920][65]);

        $NPCIssuerArray = [];

        foreach ($QuestCsv->data as $id => $QuestData) {
            $QuestIssuer = $QuestData['Issuer{Start}'];
            $NPCIssuerArray[$QuestIssuer][] = $QuestData;
            // example = var_dump($SupplyItemArray['22720']['id']);
        }

        
        // loop through our sequences
        foreach($levelcsv->data as $id => $level) {
            $id = $level['id'];
            $terimap = $level['Territory'];
            //if ($terimap != 920) continue;



            if ($level["Type"] = 8) {
                $name = ucwords(strtolower($residentcsv->at($level["Object"])['Singular']));
                $objectid = $level["Object"];
                $IssuerArray = [];
                foreach(range(0,20) as $i) {
                    if (empty($NPCIssuerArray["$objectid"]["$i"]['Name'])) continue;
                    $IssuerArray[0] = "\n  | Issuer =";
                    $IssuerArray[] = " ". $NPCIssuerArray["$objectid"]["$i"]['Name'] .",";
                }
                $NpcIssues = implode("", $IssuerArray);
                //Array of names that should not be capitalized
            $IncorrectNames = array(" De ", " Bas ", " Mal ", " Van ", " Cen ", " Sas ", " Tol ", " Zos ", " Yae ", " The ", " Of The ", " Of ",
            "A-ruhn-senna", "A-towa-cant", "Bea-chorr", "Bie-zumm", "Bosta-bea", "Bosta-loe", "Chai-nuzz", "Chei-ladd", "Chora-kai", "Chora-lue",
            "Chue-zumm", "Dulia-chai", "E-sumi-yan", "E-una-kotor", "Fae-hann", "Hangi-rua", "Hanji-fae", "Kai-shirr", "Kan-e-senna", "Kee-bostt",
            "Kee-satt", "Lewto-sai", "Lue-reeq", "Mao-ladd", "Mei-tatch", "Moa-mosch", "Mosha-moa", "Moshei-lea", "Nunsi-lue", "O-app-pesi", "Qeshi-rae",
            "Rae-qesh", "Rae-satt", "Raya-o-senna", "Renda-sue", "Riqi-mao", "Roi-tatch", "Rua-hann", "Sai-lewq", "Sai-qesh", "Sasha-rae", "Shai-satt",
            "Shai-tistt", "Shee-tatch", "Shira-kee", "Shue-hann", "Sue-lewq", "Tao-tistt", "Tatcha-mei", "Tatcha-roi", "Tio-reeq", "Tista-bie", "Tui-shirr",
            "Vroi-reeq", "Zao-mosc", "Zia-bostt", "Zoi-chorr", "Zumie-moa", "Zumie-shai");
        $correctnames = array(" de ", " bas ", " mal ", " van ", " cen ", " sas ", " tol ", " zos ", " yae ", " the ", " of the ", " of ",
            "A-Ruhn-Senna", "A-Towa-Cant", "Bea-Chorr", "Bie-Zumm", "Bosta-Bea", "Bosta-Loe", "Chai-Nuzz", "Chei-Ladd", "Chora-Kai", "Chora-Lue",
            "Chue-Zumm", "Dulia-Chai", "E-Sumi-Yan", "E-Una-Kotor", "Fae-Hann", "Hangi-Rua", "Hanji-Fae", "Kai-Shirr", "Kan-E-Senna", "Kee-Bostt",
            "Kee-Satt", "Lewto-Sai", "Lue-Reeq", "Mao-Ladd", "Mei-Tatch", "Moa-Mosch", "Mosha-Moa", "Moshei-Lea", "Nunsi-Lue", "O-App-Pesi", "Qeshi-Rae",
            "Rae-Qesh", "Rae-Satt", "Raya-O-Senna", "Renda-Sue", "Riqi-Mao", "Roi-Tatch", "Rua-Hann", "Sai-Lewq", "Sai-Qesh", "Sasha-Rae", "Shai-Satt",
            "Shai-Tistt", "Shee-Tatch", "Shira-Kee", "Shue-Hann", "Sue-Lewq", "Tao-Tistt", "Tatcha-Mei", "Tatcha-Roi", "Tio-Reeq", "Tista-Bie", "Tui-Shirr",
            "Vroi-Reeq", "Zao-Mosc", "Zia-Bostt", "Zoi-Chorr", "Zumie-Moa", "Zumie-Shai");

                $NpcMiqoCheck = $ENpcBaseCsv->at($objectid)['Race']; //see if miqote
                //this explodes miqote's names into 2 words, capitalizes them and then puts it back together with a hyphen
                if ($NpcMiqoCheck == 4) {
                    $name = ucwords(strtolower($residentcsv->at($objectid)['Singular']));
                    $name = implode('-', array_map('ucfirst', explode('-', $name)));
                }
                if (empty($name)) {
                    continue;
                }

                $NpcLevelX = $level["X"];
                $NpcLevelY = $level["Y"];
                $NpcLevelZ = $level["Z"];
                $NpcLevelMap = $level["Map"];

                $scale = $mapcsv->at($level["Map"])['SizeFactor'];
                //$offsetx = $mapcsv["Offset{X}"];
                $c = $scale / 100.0;

                $offsetx = $mapcsv->at($level["Map"])['Offset{X}'];
                    $offsetValueX = ($NpcLevelX + $offsetx) * $c;
                    $NpcLevelX2 = round(((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1), 1);
                    $NpcPixelX = round(((($NpcLevelX2 - 1) * 50 * $c) /3.9 + 5), 2);

                $offsetz = $mapcsv->at($level["Map"])['Offset{Y}'];
                    $offsetValueZ = ($NpcLevelZ + $offsetz) * $c;
                    $NpcLevelZ2 = round(((41.0 / $c) * (($offsetValueZ + 1024.0) / 2048.0) +1), 1);
                    $NpcPixelZ = round(((($NpcLevelZ2 - 1) * 50 * $c) /3.9 + 5), 2);
                    //$npcpixelY2 = $NpcPixelZ /3.9


                
                
                



                    $NpcMap1 = $mapcsv->at($level["Map"])['PlaceName'];
                    $NpcPlaceName = $PlaceNameCsv->at($NpcMap1)['Name'];
    
                $NpcMapCodeName = $mapcsv->at($level["Map"])['Id'];
    
                $NpcMap2 = $mapcsv->at($level["Map"])['PlaceName{Sub}'];
                    $NpcPlaceNameSub = $PlaceNameCsv->at($NpcMap2)['Name'];
                }
    
    
                if ($mapcsv->at($level["Map"])["PlaceName{Sub}"] > 0) {
                $sub = " - ".$PlaceNameCsv->at($mapcsv->at($level["Map"])["PlaceName{Sub}"])['Name']."";
                } elseif ($mapcsv->at($level["Map"])["PlaceName"] > 0) {
                $sub = "";
                }
                $code = substr($NpcMapCodeName, 0, 4);
                if ($code == "z3e2") {
                    $NpcPlaceName = "The Prima Vista Tiring Room";
               }
                
                //this is the sublocationsection using json production above
                //NPC  = 14/15
                //A = 13/2
                //B = 3/14
                //            
                //Area of A =
                // 14-13 = 1
                // 15-2  = 11
                // Area: 1 * 11 = 11
                //            
                //Area of B =
                // 14-3 = 13
                // 15-14 = 1
                //            
                // Area: 13 * 1 = 13
                //            
                //11 < 13
                //            
                //A is closer
                $keyarray = [];
                foreach (range(0, 1000) as $i) {
                    if (empty($JSONTeriArray[$terimap][$i]["x"])) break;
                    $calcA = ($NpcLevelX2 - $JSONTeriArray[$terimap][$i]["x"]); 
                    $calcB = ($NpcLevelZ2 - $JSONTeriArray[$terimap][$i]["y"]);
                    $calcX = $calcA * $calcB;
                    $keyarray[] = abs($calcX);
                }
                asort($keyarray);
                $smallestNumber = key($keyarray);
                if (empty($JSONTeriArray[$terimap][$smallestNumber]["placename"])) {
                    $subLocation = "";
                } else {
                    $subLocation = $JSONTeriArray[$terimap][$smallestNumber]["placename"];
                }
           //$url = "https://garlandtools.org/db/doc/npc/en/2/". $objectid .".json";
           // //slow, but grabs the header of the page to check it exists, if 404 is found then use blank //values
           // $file_headers = @get_headers($url);
           // if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
           //     $patch = "";
           //     $placename = "";
           // }
           // else {
           //     $jdata = file_get_contents($url);
           //     $decodeJdata = json_decode($jdata);
           //     $patch = number_format($decodeJdata->npc->patch, 1);
           //     if ($decodeJdata->npc->patch == 1) {
           //         $patch = "2.0";
           //     }
           //     $placename = "";
           //     if (!empty($decodeJdata->npc->areaid)) {
           //         $placenameid = ($decodeJdata->npc->areaid);
           //         $placename = $PlaceNameCsv->at($placenameid)['Name'];
           //     }
           // }
           $placename = "unknown";

            // build our data array using the GE Formatter
            $data = GeFormatter::format(self::WIKI_FORMAT, [
                '{x}'  => $NpcLevelX,
                '{id}' => $id,
                '{y}'    => $NpcLevelY,
                '{m}' => $NpcLevelMap,
                '{scale}' => $scale,
                '{x2}'  => $NpcLevelX2,
                '{y2}'  => $NpcLevelZ2,
                '{pixX}' => $NpcPixelX,
                '{pixY}' => $NpcPixelZ,
                //'{y3}'  => $NpcLevelY3,
                '{name}' => $name,
                '{npcname}' => $name,
                '{objectid}' => $objectid,
                '{map}' => $NpcPlaceName,
                '{sub}' => $sub,
                '{NpcIssues}' => $NpcIssues,
                '{mapcode}' => $code,
                '{patch}' => $Patch,
                '{placename}' => $placename,
                '{sublocation}' => $subLocation,
            ]);

            $this->data[] = $data;

            $console->overwrite(" > Completed Sequence: {$id} --> }");
        }

        // save
        $console->writeln(" Saving... ");
        $this->save("$CurrentPatchOutput/ClientNPCLocationsLevel - ". $Patch .".txt", 999999);
    }
}


/*
27th July 2020 - Adujusted to work with bot
13th Aug 2020 - Added |Issuer = variable for npcs which issue quests
*/