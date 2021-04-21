<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:ClientNPCLocLGB
 */
class ClientNPCLocLGB implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT ='{output}
     ';
    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $MapCsv = $this->csv('Map');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $fatecsv = $this->csv('Fate');
        $PlaceNameCsv = $this->csv('PlaceName');
        $EnpcResidentCsv = $this->csv('EnpcResident');
        $ENpcBaseCsv = $this->csv('ENpcBase');
        $QuestCsv = $this->csv('Quest');


        $zone = 133;
        $this->io->progressStart($TerritoryTypeCsv->total);

        $NPCIssuerArray = [];

        foreach ($QuestCsv->data as $id => $QuestData) {
            $QuestIssuer = $QuestData['Issuer{Start}'];
            $NPCIssuerArray[$QuestIssuer][] = $QuestData;
            // example = var_dump($SupplyItemArray['22720']['id']);
        }
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
                            $scale = $MapCsv->at($mapLink)['SizeFactor'];
                        }
                        $c = $scale / 100.0;
                        $offsetx = $MapCsv->at($mapLink)['Offset{X}'];
                        $offsetValueX = ($x + $offsetx) * $c;
                        $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                        $offsety = $MapCsv->at($mapLink)['Offset{Y}'];
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

        foreach ($TerritoryTypeCsv->data as $id => $teri) {
        $this->io->progressAdvance();
        $index = $teri['id'];
        //if ($index != $zone) continue;//comment this out for run all in teri
        $code = $teri['Name'];
        if (empty($code)) continue;
        //if ($code = "n4t2") continue; // skipping eulmore because of floors
        foreach(range(0,2) as $range) {
            if ($range == 0) {
                $url = "cache/$PatchID/lgb/". $code ."_planlive.lgb.json";
                //var_dump($url);
            } elseif ($range == 1) {
                $url = "cache/$PatchID/lgb/". $code ."_planevent.lgb.json";
            } elseif ($range == 2) {
                $url = "cache/$PatchID/lgb/". $code ."_planmap.lgb.json";
            }
            if (!file_exists($url)) continue;
        //$url = 'cache/lgb/'. $code .'_planevent.lgb.json'; // path to your JSON file
        $jdata = file_get_contents($url);
        $decodeJdata = json_decode($jdata);
            foreach ($decodeJdata as $lgb) {
                $LayerID = $lgb->LayerID;
                $Name = $lgb->strName;
                $InstanceObjects = $lgb->InstanceObjects;
                $AssetType = "";

                foreach($InstanceObjects as $Object) {
                    $AssetType = $Object->AssetType;
                    $InstanceID = "";
                    if (!empty($Object->InstanceID)) {
                        $InstanceID = $Object->InstanceID;
                    }
                    $BaseId = "";
                    $x = "";
                    $y = "";
                    if ($AssetType == 8) {
                        $BaseId = "". $Object->Object->ParentData->ParentData->BaseId ."";
                        $x = $Object->Transform->Translation->x;
                        $y = $Object->Transform->Translation->z;
                    } elseif ($AssetType == 45) {
                        $BaseId = "". $Object->Object->ParentData->BaseId ."";
                        $x = $Object->Transform->Translation->x;
                        $y = $Object->Transform->Translation->z;
                    } elseif ($AssetType == 6) {
                        $x = $Object->Transform->Translation->x;
                        $y = $Object->Transform->Translation->z;
                    }
                    if ($AssetType !== 8) continue;

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

                    $NpcMiqoCheck = $ENpcBaseCsv->at($BaseId)['Race']; //see if miqote
                    $NpcName = ucwords(strtolower($EnpcResidentCsv->at($BaseId)['Singular']));
                    //this explodes miqote's names into 2 words, capitalizes them and then puts it back together with a hyphen
                    if ($NpcMiqoCheck == 4) {
                        $NpcName = ucwords(strtolower($EnpcResidentCsv->at($BaseId)['Singular']));
                        $NpcName = implode('-', array_map('ucfirst', explode('-', $NpcName)));
                    }
                    $NpcName = str_replace($IncorrectNames, $correctnames, $NpcName);
                    if (empty($NpcName)) continue;


                    $scale = $MapCsv->at($teri["Map"])['SizeFactor'];
                    $c = $scale / 100.0;

                    $offsetx = $MapCsv->at($teri["Map"])['Offset{X}'];
                    $offsetValueX = ($x + $offsetx) * $c;
                    $X = round(((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1), 1);
                    $PixX = round(((($X - 1) * 50 * $c) /3.9 + 2), 2);

                    $offsetz = $MapCsv->at($teri["Map"])['Offset{Y}'];
                    $offsetValueZ = ($y + $offsetz) * $c;
                    $Y = round(((41.0 / $c) * (($offsetValueZ + 1024.0) / 2048.0) +1), 1);
                    $PixY = round(((($Y - 1) * 50 * $c) /3.9 + 5), 2);
                    //$npcpixelY2 = $NpcPixelZ /3.9

                    $NpcMap1 = $MapCsv->at($teri["Map"])['PlaceName'];
                        $NpcPlaceName = str_replace("''Prima Vista''", "Prima Vista", $PlaceNameCsv->at($NpcMap1)['Name']);

                    if ($code == "z3e2") {
                         $NpcPlaceName = "The Prima Vista Tiring Room";
                    }

                    $NpcMapCodeName = $MapCsv->at($teri["Map"])['Id'];

                    $NpcMap2 = $MapCsv->at($teri["Map"])['PlaceName{Sub}'];
                        $NpcPlaceNameSub = $PlaceNameCsv->at($NpcMap2)['Name'];
                    $NpcNameSubString = "Merchant & Mender (". $NpcPlaceName .")";
                    $NpcName = str_replace("Merchant & Mender", $NpcNameSubString, $NpcName);

                    if ($MapCsv->at($teri["Map"])["PlaceName{Sub}"] > 0) {
                    $sub = " - ".$PlaceNameCsv->at($MapCsv->at($teri["Map"])["PlaceName{Sub}"])['Name']."";
                    } elseif ($MapCsv->at($teri["Map"])["PlaceName"] > 0) {
                    $sub = "";
                    }

                    $keyarray = [];
                    foreach (range(0, 1000) as $i) {
                        if (empty($JSONTeriArray[$id][$i]["x"])) break;
                        $calcA = ($X - $JSONTeriArray[$id][$i]["x"]); 
                        $calcB = ($Y - $JSONTeriArray[$id][$i]["y"]);
                        $calcX = $calcA * $calcB;
                        $keyarray[] = abs($calcX);
                    }
                    asort($keyarray);
                    $smallestNumber = key($keyarray);
                    if (empty($JSONTeriArray[$id][$smallestNumber]["placename"])) {
                        $subLocation = "";
                    } else {
                        $subLocation = $JSONTeriArray[$id][$smallestNumber]["placename"];
                    }

                    
                $IssuerArray = [];
                foreach(range(0,20) as $i) {
                    if (empty($NPCIssuerArray["$BaseId"]["$i"]['Name'])) continue;
                    $IssuerArray[0] = "\n| Issuer =";
                    $IssuerArray[] = " ". $NPCIssuerArray["$BaseId"]["$i"]['Name'] .",";
                }
                $NpcIssues = implode("", $IssuerArray);
                //$url = "https://garlandtools.org/db/doc/npc/en/2/". $BaseId .".json";
                ////slow, but grabs the header of the page to check it exists, if 404 is found then use //blank values
                //$file_headers = @get_headers($url);
                //if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
                //    $patch = "";
                //    $placename = "";
                //}
                //else {
                //    $jdata = file_get_contents($url);
                //    $decodeJdata = json_decode($jdata);
                //    $patch = number_format($decodeJdata->npc->patch, 1);
                //    if ($decodeJdata->npc->patch == 1) {
                //        $patch = "2.0";
                //    }
                //    $placename = "";
                //    if (!empty($decodeJdata->npc->areaid)) {
                //        $placenameid = ($decodeJdata->npc->areaid);
                //        $placename = $PlaceNameCsv->at($placenameid)['Name'];
                //    }
                //}
                $placename = "unknown";

                    $MapCode = substr($NpcMapCodeName, 0, 4);

                    //for debugging:
                    //var_dump($lgb);
                    //$fileoutput = print_r(array($lgb, true));
                    //file_put_contents('file.txt', $fileoutput);

                    //start of section
                    $output =[];
                    //string for csv
                    //$string = "". $InstanceID .",". $x .",". $y .",". $AssetType .",". $BaseId .",". $Name .",";
                    //string for wikioutput
                    $string = "{{-start-}}\n'''". $NpcName ."/Map/". $BaseId ."'''\n{{NPCMap\n| base = ". $MapCode ." - ". $NpcPlaceName ."". $sub .".png\n| float_link = ". $NpcName ."\n| float_caption = ". $NpcName ." \n| float_caption_coordinates = (x:". $X .", y:". $Y .")\n| x = ". $PixX ."\n| y = ". $PixY ."\n| zone = ". $NpcPlaceName ."\n| level_id = ". $InstanceID ."\n| npc_id = ". $BaseId ."". $NpcIssues ."\n| Patch = ". $Patch ."\n| Sublocation  = ". $subLocation ."\n}}{{-stop-}}";

                    $output[] = $string;



                    // Save some data
                    $data = [
                        '{output}' => "\n\n$string",
                    ];
                    // format using Gamer Escape formatter and add to data array
                    // need to look into using item-specific regex, if required.
                    $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
                }
                }
            };
        }
        // save our data to the filename: LGB_NPC.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("$CurrentPatchOutput/ClientNPCLocationsLGB - ". $Patch .".txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}

/*
27th July 2020 - Adujusted to work with bot
13th Aug 2020 - Added |Issuer = variable for npcs which issue quests
*/