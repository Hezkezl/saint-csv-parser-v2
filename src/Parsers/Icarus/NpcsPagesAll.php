<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * php bin/console app:parse:csv GE:NpcsPagesAll
 */

class NpcsPagesAll implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{Output}';

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');
        $this->io->text('Reading CSV Data ...');
        // grab CSV files
        $ENpcBaseCsv = $this->csv('ENpcBase');
        $ENpcResidentCsv = $this->csv('ENpcResident');
        $QuestCsv = $this->csv('Quest');
        $CustomTalkCsv = $this->csv('CustomTalk');
        $CustomTalkNestHandlersCsv = $this->csv('CustomTalkNestHandlers');
        $HowToCsv = $this->csv('HowTo');
        $HowToCategoryCsv = $this->csv('HowToCategory');
        $HowToPageCsv = $this->csv('HowToPage');
        $JingleCsv = $this->csv('Jingle');
        $ScreenImageCsv = $this->csv('ScreenImage');
        $GatheringLeveCsv = $this->csv('GatheringLeve');
        $GatheringLeveRuleCsv = $this->csv('GatheringLeveRule');
        $EventItemCsv = $this->csv('EventItem');
        $ItemCsv = $this->csv('Item');
        $GilShopCsv = $this->csv('GilShop');
        $GilShopItemCsv = $this->csv('GilShopItem');
        $DefaultTalkCsv = $this->csv('DefaultTalk');
        $AchievementCsv = $this->csv('Achievement');
        $LeveCsv = $this->csv('Leve');
        $CraftLeveCsv = $this->csv('CraftLeve');
        $ChocoboTaxiStandCsv = $this->csv('ChocoboTaxiStand');
        $ChocoboTaxiCsv = $this->csv('ChocoboTaxi');
        $PlaceNameCsv = $this->csv('PlaceName');
        $GuildLeveAssignmentCsv = $this->csv('GuildLeveAssignment');
        $GuildLeveAssignmentTalkCsv = $this->csv('GuildLeveAssignmentTalk');
        $GCShopCsv = $this->csv('GCShop');
        $GrandCompanyCsv = $this->csv('GrandCompany');
        $LogMessageCsv = $this->csv('LogMessage');
        $SpecialShopCsv = $this->csv('SpecialShop');
        $SwitchTalkVariationCsv = $this->csv('SwitchTalkVariation');
        $TripleTriadCardCsv = $this->csv('TripleTriadCard');
        $TripleTriadCsv = $this->csv('TripleTriad');
        $TripleTriadRuleCsv = $this->csv('TripleTriadRule');
        $FCCShopCsv = $this->csv('FccShop');
        $DpsChallengeOfficerCsv = $this->csv('DpsChallengeOfficer');
        $DpsChallengeCsv = $this->csv('DpsChallenge');
        $TopicSelectCsv = $this->csv('TopicSelect');
        $PreHandlerCsv = $this->csv('PreHandler');
        $InclusionShopCsv = $this->csv('InclusionShop');
        $InclusionShopCategoryCsv = $this->csv('InclusionShopCategory');
        $InclusionShopSeriesCsv = $this->csv('InclusionShopSeries');
        $DisposalShopCsv = $this->csv('DisposalShop');
        $DisposalShopItemCsv = $this->csv('DisposalShopItem');
        $DescriptionCsv = $this->csv('Description');
        $ClassJobCategoryCsv = $this->csv('ClassJobCategory');
        $BalloonCsv = $this->csv('Balloon');
        $BehaviourCsv = $this->csv('Behavior');
        $TribeCsv = $this->csv('Tribe');
        $RaceCsv = $this->csv('Race');
        $LeveStringCsv = $this->csv('LeveString');
        $WarpCsv = $this->csv('Warp');
        $WarpConditionCsv = $this->csv('WarpCondition');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $MapCsv = $this->csv('Map');
        $LevelCsv = $this->csv('Level');
        $CraftLeveTalkCsv = $this->csv('CraftLeveTalk');
        $CharaMakeCustomizeCsv = $this->csv('CharaMakeCustomize');
        $CharaMakeTypeCsv = $this->csv('CharaMakeType');
        $NpcEquipCsv = $this->csv('NpcEquip');
        $StainCsv = $this->csv('Stain');

        $NpcNameArray = [];
        $NpcMainPage = [];
        $this->PatchCheck($Patch, "ENpcResident", $ENpcResidentCsv);
        $PatchNumber = $this->getPatch("ENpcResident");
        $NpcPatchArray = [];

        //Get Fesitval ID's


        //levellocations:
        $this->io->text('Generating PlaceName Positions ...');
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
                        if ($AssetType != 43) continue;
                        if ($Object->Object->PlaceNameEnabled == 0) continue;
                        $x = $Object->Transform->Translation->x;
                        $y = $Object->Transform->Translation->z;
                        $NpcLocX = $this->GetLGBPos($x, $y, $id, $TerritoryTypeCsv, $MapCsv)["X"];
                        $NpcLocY = $this->GetLGBPos($x, $y, $id, $TerritoryTypeCsv, $MapCsv)["Y"];
                        $PlaceName = $PlaceNameCsv->at($Object->Object->PlaceNameSpot)['Name'];
                        if (empty($PlaceName)) {
                            $PlaceName = $PlaceNameCsv->at($Object->Object->PlaceNameBlock)['Name'];
                        }
                        $JSONMapRangeArray[] = array(
                            'placename' => $PlaceName,
                            'x' => $NpcLocX,
                            'y' => $NpcLocY,
                            'code' => $code,
                            'id' => $id
                        );
                    }
                }
                $JSONTeriArray[$id] = $JSONMapRangeArray;
            }
        }
        $this->io->progressFinish();
        //gather Festivals from quests
        $this->io->text('Generating list of Festivals from Quests ...');
        $this->io->progressStart($QuestCsv->total);
        foreach($QuestCsv->data as $id => $Quest) {
            $this->io->progressAdvance();
            if ($Quest['Festival'] === "0") continue;
            $QuestFestival = $Quest['Festival'];
            $Issuer = $Quest['Issuer{Start}'];
            if (!empty($ENpcResidentCsv->at($Issuer)['Singular'])) {
                $NpcFestivalQuestArray[$Issuer] = $QuestFestival;
            }
            foreach(range(0,49) as $i) {
                $Npc = $Quest["Script{Arg}[$i]"];
                if (($Npc > 1000000) && ($Npc < 1100000)) {
                    if (empty($ENpcResidentCsv->at($Npc)['Singular'])) continue;
                    $NpcFestivalQuestArray[$Npc] = $QuestFestival; 
                }
            }
            foreach(range(0,63) as $i) {
                $Npc = $Quest["Listener[$i]"];
                if (($Npc > 1000000) && ($Npc < 1100000)) {
                    if (empty($ENpcResidentCsv->at($Npc)['Singular'])) continue;
                    $NpcFestivalQuestArray[$Npc] = $QuestFestival; 
                }
            }
        }
        $this->io->progressFinish();

        //gather lgb from level.exd
        $this->io->text('Generating Level.exd Positions ...');
        $this->io->progressStart($LevelCsv->total);
        foreach($LevelCsv->data as $id => $Level) {
            $this->io->progressAdvance();
            if ($Level['Type'] != 8) continue;
            $NPCID = $Level['Object'];
            $Festival = 0;
            if (!empty($NpcFestivalQuestArray[$NPCID])){
                $Festival = $NpcFestivalQuestArray[$NPCID];
            }
            $Name = "";
            $LGBArray[$NPCID] = array(
                'Territory' => $Level['Territory'],
                'x' => $Level['X'],
                'y' => $Level['Z'],
                'id' => $id,
                'festivalID' => $Festival,
                'festivalName' => $Name
            );
        }
        $Festivaljdata = file_get_contents("Patch/FestivalNames.json");
        $FestivalArray = json_decode($Festivaljdata, true);
        $this->io->progressFinish();
        //gather lgb from LGB.json
        $this->io->text('Generating LGB.json Positions ...');
        $this->io->progressStart($TerritoryTypeCsv->total);
        foreach ($TerritoryTypeCsv->data as $id => $teri) {  
            $this->io->progressAdvance();
            $code = $teri['Name'];
            if (empty($code)) continue;
            foreach(range(0,3) as $range) {
                if ($range == 0) {
                    $url = "cache/$PatchID/lgb/". $code ."_planlive.lgb.json";
                } elseif ($range == 1) {
                    $url = "cache/$PatchID/lgb/". $code ."_planevent.lgb.json";
                } elseif ($range == 2) {
                    $url = "cache/$PatchID/lgb/". $code ."_planmap.lgb.json";
                } elseif ($range == 3) {
                    $url = "cache/$PatchID/lgb/". $code ."_planner.lgb.json";
                }
                if (!file_exists($url)) continue;
                $jdata = file_get_contents($url);
                $decodeJdata = json_decode($jdata);
                $Festival = 0;
                foreach ($decodeJdata as $lgb) {
                    $LayerID = $lgb->LayerId;
                    $Name = $lgb->Name;
                    $InstanceObjects = $lgb->InstanceObjects;
                    $Festival = $lgb->FestivalID;
                    $AssetType = "";
                    foreach($InstanceObjects as $Object) {
                        $AssetType = $Object->AssetType;
                        $InstanceID = "";
                        if (!empty($Object->InstanceId)) {
                            $InstanceID = $Object->InstanceId;
                        }
                        $BaseId = "";
                        $x = "";
                        $y = "";
                        if ($AssetType == 8) {
                            $BaseId = "". $Object->Object->ParentData->ParentData->BaseId ."";
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $NPCID = $BaseId;
                            if (!empty($NpcFestivalQuestArray[$NPCID])){
                                if ($Festival === "0") {
                                    $Festival = $NpcFestivalQuestArray[$NPCID];
                                }
                            }
                            $LGBArray[$NPCID] = array(
                                'Territory' => $id,
                                'x' => $x,
                                'y' => $y,
                                'id' => $InstanceID,
                                'festivalID' => $Festival,
                                'festivalName' => $Name
                            );
                        }
                    }
                }
            }
        }
        $this->io->progressFinish();
        //var_dump($LGBArray['1034078']['Territory']);

        
            
        $this->io->text('Generating NPC -> Sublocation Positions ...');
        $this->io->progressStart($ENpcResidentCsv->total);
        foreach ($ENpcResidentCsv->data as $id => $NPCs) {
            $this->io->progressAdvance();
            $subLocation = "";
            if (!empty($LGBArray[$id]['Territory'])){
                $Territory = $LGBArray[$id]['Territory'];
                $X = $LGBArray[$id]['x'];
                $Y = $LGBArray[$id]['y'];
                $keyarray = [];
                foreach (range(0, 1000) as $i) {
                    if (empty($JSONTeriArray[$Territory][$i]["x"])) break;
                    $calcA = ($X - $JSONTeriArray[$Territory][$i]["x"]); 
                    $calcB = ($Y - $JSONTeriArray[$Territory][$i]["y"]);
                    $calcX = $calcA * $calcB;
                    $keyarray[] = abs($calcX);
                }
                asort($keyarray);
                $smallestNumber = key($keyarray);
                if (empty($JSONTeriArray[$Territory][$smallestNumber]["placename"])) {
                    $subLocation = "";
                } else {
                    $subLocation = $JSONTeriArray[$Territory][$smallestNumber]["placename"];
                }
                if (empty($subLocation)){
                    $subLocation = $PlaceNameCsv->at($TerritoryTypeCsv->at($Territory)['PlaceName'])['Name'];
                }
            }
            $NameFormatted = $this->NameFormat($id, $ENpcResidentCsv, $ENpcBaseCsv, $subLocation, $LGBArray);
            $NPCNameLocationArrray[$id] = $subLocation;
        }
        $this->io->progressFinish();

        $this->io->text('Generating NPC Name Array ...');
        $this->io->progressStart($ENpcResidentCsv->total);
        foreach ($ENpcResidentCsv->data as $id => $NPCs) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();
            //main NPC constructor

            
            $NameFunc = $this->NameFormat($id, $ENpcResidentCsv, $ENpcBaseCsv, $NPCNameLocationArrray[$id], $LGBArray);
            $NameFormatted = $NameFunc['Name'];
            if ($NameFunc['IsEnglish'] === false) continue;
            if (empty($NameFormatted)) continue;
            if (empty($NameFormatted)) continue;
            if (empty($NpcPatchArray[$NameFormatted])) {
                $NpcPatchArray[$NameFormatted] = $PatchNumber[$id];
            }
            if (empty($LGBArray[$id]['x'])) continue;
            $NpcNameArray[$NameFormatted][] = $id;
        }
        $this->io->progressFinish();
        $NPCIds = [];
        foreach ($NpcNameArray as $key => $value) {
            $NPCIds[$key] = implode(",", $value);
        }
        //subpage arrays
        //$GetHowToArray = [];

        $IssuerArray = [];
        $this->io->text('Generating Quest Issuer Array ...');
        $this->io->progressStart($QuestCsv->total);
        foreach ($QuestCsv->data as $id => $Quests) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();
            if (empty($Quests['Issuer{Start}'])) continue;
            $IssuerArray[$Quests['Issuer{Start}']][] = str_ireplace(",","&#44;",$Quests['Name']);
        }
        $Issuers = [];
        foreach ($IssuerArray as $key => $value) {
            $Issuers[$key] = implode(",", $value);
        }
        $this->io->progressFinish();


        $PorterArray = [];
        //data constructor
        $this->io->text('Generating NPC Data ...');
        $this->io->progressStart($ENpcResidentCsv->total);
        foreach ($ENpcResidentCsv->data as $id => $NPCs) {
            $this->io->progressAdvance();            
            $subLocation = "";
            if (!empty($LGBArray[$id]['Territory'])){
                $Territory = $LGBArray[$id]['Territory'];
                $X = $LGBArray[$id]['x'];
                $Y = $LGBArray[$id]['y'];
                $keyarray = [];
                foreach (range(0, 1000) as $i) {
                    if (empty($JSONTeriArray[$Territory][$i]["x"])) break;
                    $calcA = ($X - $JSONTeriArray[$Territory][$i]["x"]); 
                    $calcB = ($Y - $JSONTeriArray[$Territory][$i]["y"]);
                    $calcX = $calcA * $calcB;
                    $keyarray[] = abs($calcX);
                }
                asort($keyarray);
                $smallestNumber = key($keyarray);
                if (empty($JSONTeriArray[$Territory][$smallestNumber]["placename"])) {
                    $subLocation = "";
                } else {
                    $subLocation = $JSONTeriArray[$Territory][$smallestNumber]["placename"];
                }
            }
            //produce map
            $MapOutputString = "";
            $sub = "";
            $NpcPlaceName = "";
            if (!empty($LGBArray[$id]['x'])) {
                $MapX = $this->GetLGBPos($LGBArray[$id]['x'], $LGBArray[$id]['y'], $LGBArray[$id]['Territory'], $TerritoryTypeCsv, $MapCsv)["X"];
                $MapY = $this->GetLGBPos($LGBArray[$id]['x'], $LGBArray[$id]['y'], $LGBArray[$id]['Territory'], $TerritoryTypeCsv, $MapCsv)["Y"];
                $MapXPix = $this->GetLGBPos($LGBArray[$id]['x'], $LGBArray[$id]['y'], $LGBArray[$id]['Territory'], $TerritoryTypeCsv, $MapCsv)["PX"];
                $MapYPix = $this->GetLGBPos($LGBArray[$id]['x'], $LGBArray[$id]['y'], $LGBArray[$id]['Territory'], $TerritoryTypeCsv, $MapCsv)["PY"];

                $CoordLocation = "". $MapX ."-". $MapY ."";
                $SubLocation = $subLocation;
                $NpcPlaceName = $SubLocation;
                $MapName = $PlaceNameCsv->at($TerritoryTypeCsv->at($LGBArray[$id]['Territory'])['PlaceName'])['Name'];
                $NpcMapCodeName = $TerritoryTypeCsv->at($LGBArray[$id]['Territory'])['Name'];
                $MapID = $TerritoryTypeCsv->at($LGBArray[$id]['id'])['Map'];
                if ($MapCsv->at($MapID)["PlaceName{Sub}"] > 0) {
                    $sub = " - ".$PlaceNameCsv->at($MapCsv->at($MapID)["PlaceName{Sub}"])['Name']."";
                } elseif ($MapCsv->at($MapID)["PlaceName"] > 0) {
                    $sub = "";
                }
                $code = substr($NpcMapCodeName, 0, 4);
                //if ($code == "z3e2") {
                //    $NpcPlaceName = "The Prima Vista Tiring Room";
                //}
                $BasePlaceName = "$code - {$MapName}{$sub}";
    
                $LevelID = $LGBArray[$id]['id'];
                $MapArray[] = $MapOutputString;
            }
            
            $NameFunc = $this->NameFormat($id, $ENpcResidentCsv, $ENpcBaseCsv, $NPCNameLocationArrray[$id], $LGBArray);
            $NameFormatted = $NameFunc['Name'];
            
            if ($NameFunc['IsEnglish'] === false) continue;
            if (empty($NameFormatted)) continue;

            $datarray = [];
            $QuestCheck = [];
            $WarpCheck = [];
            $ShopCheck = [];
            $DialogueCheck = [];
            $LeveCheck = [];
            $HowToCheck = [];
            $ChocoboTaxiCheck = [];
            $TripleTriadCheck = [];
            $GetPorterArray = [];
            foreach(range(0,31) as $i) {
                if ($ENpcBaseCsv->at($id)["ENpcData[$i]"] == 0) continue;
                if (empty($ENpcBaseCsv->at($id)["ENpcData[$i]"])) continue;
                $DataValue = $ENpcBaseCsv->at($id)["ENpcData[$i]"];
                //check for each type of subpage:
                switch (true) {
                    case ($DataValue > 131000) && ($DataValue < 139999): //WARP
                        $WarpCheck[] = $DataValue.",";
                        $DefaultTalkAccept = $this->getDefaultTalk($DefaultTalkCsv, $WarpCsv, $DataValue ,'ConditionSuccessEvent', '| Success Talk =');
                        $DefaultTalkFail = $this->getDefaultTalk($DefaultTalkCsv, $WarpCsv, $DataValue ,'ConditionFailEvent', '| Fail Talk =');
                        $DefaultTalkConfirm = $this->getDefaultTalk($DefaultTalkCsv, $WarpCsv, $DataValue ,'ConfirmEvent', '| Unavailable Talk =');
                        $WarpOption = $WarpCsv->at($DataValue)['Name'];
                        $WarpTargetLocation = $PlaceNameCsv->at($TerritoryTypeCsv->at($WarpCsv->at($DataValue)['TerritoryType'])['PlaceName'])['Name'];
                        if (empty($WarpOption)) {
                            $WarpOption = "Teleports to $WarpTargetLocation";
                        }
                        $WarpConfirm = $WarpCsv->at($DataValue)['Question'];
                        //condition
                        $Condition = $WarpCsv->at($DataValue)['WarpCondition'];
                        $WarpCost = $WarpConditionCsv->at($Condition)['Gil'];
                        $RequiredQuestArray = [];
                        foreach(range(1,4) as $b) {
                            switch ($b) {
                                case 1:
                                case 2:
                                case 4:
                                    $QuestText = "RequiredQuest{". $b ."}";
                                break;
                                case 3:
                                    $QuestText = "DRequiredQuest{". $b ."}";
                                break;
                            }
                            if (empty($QuestCsv->at($WarpConditionCsv->at($Condition)[$QuestText])['Name'])) continue;
                            $RequiredQuestArray[] = $QuestCsv->at($WarpConditionCsv->at($Condition)[$QuestText])['Name'];
                        }
                        $RequiredQuests = implode(",", $RequiredQuestArray);
                        $RequiredLevel = $WarpConditionCsv->at($Condition)['Class{Level}'];
                        $WarpString = "{{-start-}}\n'''". $NameFormatted ."/$DataValue/Warp'''\n";
                        $WarpString .= "{{WarpTemplate\n";
                        $WarpString .= "| Option = $WarpOption\n";
                        $WarpString .= "| Confirm = $WarpConfirm\n";
                        $WarpString .= "| RequiredQuests = $RequiredQuests\n";
                        $WarpString .= "| RequiredLevel = $RequiredLevel\n";
                        $WarpString .= "| Cost = $WarpCost\n";
                        $WarpString .= "". $DefaultTalkAccept ."". $DefaultTalkFail ."". $DefaultTalkConfirm ."\n";
                        $WarpString .= "}}\n";
                        $WarpString .= "{{-stop-}}\n\n";
                        $WarpPagesArray[] = $WarpString;
                    break;
                    case ($DataValue > 262100) && ($DataValue < 269999): //GILSHOP
                        $FuncShop = $this->getShop($NameFormatted, "GilShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $DataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                        $ShopOutputArray[] = $FuncShop["Shop"];
                        $ShopDialogueArray[] = $FuncShop["Dialogue"];
                        if(!empty($FuncShop["Dialogue"])){
                            $ShopCheck[] = "Dialogue,";
                        }
                        $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                        $ShopCheck[] = $FuncShop["Name"].",";
                    break;
                    case ($DataValue > 393000) && ($DataValue < 399999): //GUILDLEVEASSIGNMENT
                        $DialogueCheck[] = $DataValue.",";
                        $GuildLeveTalkArray = [];
                        $GuildLeveTalkType = $GuildLeveAssignmentCsv->at($DataValue)['Type'];
                        foreach(range(0,7) as $a) {
                            if ($GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["Talk[$a]"] != "0"){
                                $GuildLeveTalkString = "{{Dialoguebox3|Intro=$GuildLeveTalkType|Dialogue=\n";
                                $GuildLeveTalkString .= $GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["Talk[$a]"];
                                $GuildLeveTalkString .= "}}";
                                $GuildLeveTalkArray[] = $GuildLeveTalkString;
                            }
                        }
                        $GuildLeveTalkImpoloded = implode("\n", $GuildLeveTalkArray);
                        $DialogueString = "{{-start-}}\n";
                        $DialogueString .= "'''$NameFormatted/$DataValue/Dialogue'''\n";
                        $DialogueString .= "$GuildLeveTalkImpoloded\n";
                        $DialogueString .= "{{-stop-}}\n";
                        $DialogueArray[] = $DialogueString;
                    break;
                    case ($DataValue > 589000) && ($DataValue < 599999)://DEFAULTTALK
                        $DialogueCheck[] = $DataValue.",";
                        $DefaultTalk = [];
                        foreach(range(0,2) as $b) {
                            if (empty($DefaultTalkCsv->at($DataValue)["Text[$b]"])) continue;
                            $DefaultTalk[] = "{{Dialoguebox3|Intro=|Dialogue=". $DefaultTalkCsv->at($DataValue)["Text[$b]"]. "}}";
                        }
                        $DefaultTalkImplode = implode("\n", $DefaultTalk);
                        $DialogueString = "{{-start-}}\n";
                        $DialogueString .= "'''$NameFormatted/$DataValue/Dialogue'''\n";
                        $DialogueString .= "$DefaultTalkImplode\n";
                        $DialogueString .= "{{-stop-}}\n";
                        $DialogueArray[] = $DialogueString;
                    break;
                    case ($DataValue > 720000) && ($DataValue < 729999): //CUSTOMTALK
                        $CollectableShopCheck = $CustomTalkCsv->at($DataValue)["SpecialLinks"];
                        if ($CollectableShopCheck >= 3866620 && $CollectableShopCheck < 3866999){//COLLECTABLESHOPS
                            $FuncShop = $this->getShop($NameFormatted, "CollectablesShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $CollectableShopCheck, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                            $ShopCheck[] = $FuncShop["Name"].",";
                            $ShopOutputArray[] = $FuncShop["Shop"];
                            $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                        } 
                            
                        foreach(range(0,29) as $a) {
                            if (empty($CustomTalkCsv->at($DataValue)["Script{Instruction}[$a]"])) continue;
                            $Instruction = $CustomTalkCsv->at($DataValue)["Script{Instruction}[$a]"];
                            $Argument = $CustomTalkCsv->at($DataValue)["Script{Arg}[$a]"];
                            switch (true) {
                                case (strpos($Instruction, 'HOWTO') !== false):
                                    $HowToCheck[] = $HowToCsv->at($Argument)['Name'].",";
                                    $DataValue = $Argument;
                                break;
                                case (strpos($Instruction, 'DISPOSAL') !== false):
                                break;
                                case (strpos($Instruction, 'SHOP') !== false):
                                    switch (true) {
                                        case ($Argument > 262100) && ($Argument < 269999): //GILSHOP
                                            $FuncShop = $this->getShop($NameFormatted, "GilShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $Argument, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                            $ShopOutputArray[] = $FuncShop["Shop"];
                                            $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                            $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                            $ShopCheck[] = $FuncShop["Name"].",";
                                        break;
                                        case ($Argument > 1769000) && ($Argument < 1779999)://SPECIALSHOP
                                            $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $Argument, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                            $ShopOutputArray[] = $FuncShop["Shop"];
                                            $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                            $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                            $ShopCheck[] = $FuncShop["Name"].",";
                                        break;
                                        case ($Argument > 2752000) && ($Argument < 2752999)://FCCSHOP
                                            $ShopName = $FCCShopCsv->at($Argument)['Name'];
                                            if (empty($ShopName)) {$ShopName = $Argument;};
                                            $FccShopArray = [];
                                            $NumberItems = 0;
                                            $ShopCheck[] = $ShopName.",";
                                            foreach(range(0,9) as $b) {
                                                if (empty($ItemCsv->at($FCCShopCsv->at($Argument)["Item[$b]"])['Name'])) break;
                                                $Item = $ItemCsv->at($FCCShopCsv->at($Argument)["Item[$b]"])['Name'];
                                                $Amount = $FCCShopCsv->at($Argument)["Cost[$b]"];
                                                $Rank = $FCCShopCsv->at($Argument)["FCRank{Required}[$b]"];
                                                $FccShopArray[] = "{{Sells3|$Item|Quantity=1|Cost1=Credits|Count1=$Amount|Requires Rank = $Rank}}";
                                                $NumberItems = $b + 1;
                                            }
                                            asort($FccShopArray);
                                            $FcshopImplode = implode("\n", $FccShopArray);
                                            $ShopOutputString = "{{-start-}}\n'''". $NameFormatted ."/". $ShopName ."'''\n";
                                            $ShopOutputString .= "{{Shop\n";
                                            $ShopOutputString .= "| Shop Name = $ShopName\n";
                                            $ShopOutputString .= "| NPC Name = $NameFormatted\n";
                                            $ShopOutputString .= "| Location = $NpcPlaceName\n";
                                            $ShopOutputString .= "| Coordinates = $CoordLocation\n";
                                            $ShopOutputString .= "| Total Items = $NumberItems\n";
                                            $ShopOutputString .= "| Shop = \n";
                                            $ShopOutputString .= "{{Tabsells3\n";
                                            $ShopOutputString .= "|Misc = \n";
                                            $ShopOutputString .= "$FcshopImplode\n";
                                            $ShopOutputString .= "}}\n}}\n{{-stop-}}\n";
                                            $ShopOutputArray[] = $ShopOutputString;
                                        break;
                                        //need to add instance content?
                                        default:
                                        break;
                                    }
                                break;
                                default:
                                break;
                            }
                        }
                        if (!empty($CustomTalkNestHandlersCsv->at("". $DataValue. ".1")['NestHandler'])){
                            foreach(range(1,99) as $b) {
                                if (empty($CustomTalkNestHandlersCsv->at("". $DataValue. ".". $b ."")['NestHandler'])) break;
                                $NestDataValue = $CustomTalkNestHandlersCsv->at("". $DataValue. ".". $b ."")['NestHandler'];
                                switch (true) {
                                    case ($NestDataValue > 262100) && ($NestDataValue < 269999)://Gilshop
                                        $FuncShop = $this->getShop($NameFormatted, "GilShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $NestDataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                        $ShopOutputArray[] = $FuncShop["Shop"];
                                        $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                        if(!empty($FuncShop["Dialogue"])){
                                            $ShopCheck[] = "Dialogue,";
                                        }
                                        $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                        $ShopCheck[] = $FuncShop["Name"].",";
                                    break;
                                    case ($NestDataValue > 1769000) && ($NestDataValue < 1779999)://SPECIALSHOP
                                        $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $NestDataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                        $ShopOutputArray[] = $FuncShop["Shop"];
                                        $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                        if(!empty($FuncShop["Dialogue"])){
                                            $ShopCheck[] = "Dialogue,";
                                        }
                                        $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                        $ShopCheck[] = $FuncShop["Name"].",";
                                    break;
                                    case ($NestDataValue > 3407872) && ($NestDataValue < 3409999)://LotteryExchangeShop
                                        $FuncShop = $this->getShop($NameFormatted, "LotteryExchangeShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $NestDataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                        $ShopCheck[] = $FuncShop["Name"].",";
                                        $ShopOutputArray[] = $FuncShop["Shop"];
                                        $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                    break;
                                    case ($NestDataValue > 3470000) && ($NestDataValue < 3479999)://disposal
                                        $FuncShop = $this->getShop($NameFormatted, "DisposalShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $NestDataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                        $ShopCheck[] = $FuncShop["Name"].",";
                                        $ShopOutputArray[] = $FuncShop["Shop"];
                                        $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                    break;
                                    default:
                                    break;
                                }
                            }
                        }
                    break;
                    case ($DataValue > 910000) && ($DataValue < 919999): //CRAFT LEVE //doesnt work?
                        $LeveCheck[] = $DataValue.",";
                        $DialogueCheck[] = $DataValue.",";
                        $CraftLeveTalkArray = [];
                        $CraftLeveTalkType = $LeveCsv->at($CraftLeveCsv->at($DataValue)['Leve'])['Name'];
                        foreach(range(37,42) as $a) {
                            if ($CraftLeveTalkCsv->at($CraftLeveCsv->at($DataValue)['CraftLeveTalk'])["unknown_$a"] != "0"){
                                $CraftLeveTalkStringSub .= $CraftLeveTalkCsv->at($CraftLeveCsv->at($DataValue)['CraftLeveTalk'])["Talk[$a]"];
                                $CraftLeveTalkStringSub .= "\n";
                                $CraftLeveTalkArray[] = $CraftLeveTalkStringSub;
                            }
                        }
                        $CraftLeveTradesArray = [];
                        foreach(range(0,3) as $a) {
                            while (!empty($CraftLeveCsv->at($DataValue)["Item[$a]"])) {
                                $ItemTradeIn = $CraftLeveCsv->at($DataValue)["Item[$a]"];
                                $ItemTradeAmount = $CraftLeveCsv->at($DataValue)["Item[$a]"];
                                $CraftLeveTradesArray[] = "Trades in $ItemTradeAmount x $ItemTradeIn";
                            }
                        }
                        $CraftLeveTrades = implode("\n", $CraftLeveTradesArray);
                        $CraftLeveTalkImpoloded = implode("\n", $CraftLeveTalkArray);
                        $CraftLeveTalkString = "{{Dialoguebox3|Intro=$CraftLeveTalkType|Dialogue=\n";
                        $CraftLeveTalkString .= "$CraftLeveTrades\n";
                        $CraftLeveTalkString .= $CraftLeveTalkImpoloded;
                        $CraftLeveTalkString .= "\n}}";
                        $DialogueString = "{{-start-}}\n";
                        $DialogueString .= "'''$NameFormatted/$DataValue/Dialogue'''\n";
                        $DialogueString .= "$CraftLeveTalkString\n";
                        $DialogueString .= "{{-stop-}}\n";
                        $DialogueArray[] = $DialogueString;
                    break;
                    case ($DataValue > 1179000) && ($DataValue < 1179999): //CHOCOBOTAXISTAND
                        $ChocoboTaxiCheck[] = $DataValue.",";
                        $FuncDataValue = $DataValue;
                        $GetPorterArray[] = $this->GetChocoboTaxi($ChocoboTaxiStandCsv, $ChocoboTaxiCsv, $FuncDataValue);
                    break;
                    case ($DataValue > 1440000) && ($DataValue < 1449999): //GCSHOP// omitted
                    break;
                    case ($DataValue > 1507000) && ($DataValue < 1509999): //GUILDORDERGUIDE// omitted
                    break;
                    case ($DataValue > 1570000) && ($DataValue < 1579999): //GUILDORDEROFFICER// omitted
                    break;
                    case ($DataValue > 1769000) && ($DataValue < 1779999)://SPECIALSHOP
                        $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $DataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                        $ShopOutputArray[] = $FuncShop["Shop"];
                        $ShopDialogueArray[] = $FuncShop["Dialogue"];
                        $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                        if(!empty($FuncShop["Dialogue"])){
                            $ShopCheck[] = "Dialogue,";
                        }
                        $ShopCheck[] = $FuncShop["Name"].",";
                    break;
                    case ($DataValue > 2030000) && ($DataValue < 2039999)://SWITCHTALK
                        $DialogueCheck[] = $DataValue.",";
                        $SwitchTalkArray = [];
                        foreach(range(0,20) as $b) {
                            $SubDataValue = "". $DataValue .".". $b ."";
                            if (empty($SwitchTalkVariationCsv->at($SubDataValue)['DefaultTalk'])) break;
                            $Quest0 = "";
                            $Quest1 = "";
                            if (!empty($QuestCsv->at($SwitchTalkVariationCsv->at($SubDataValue)['Quest[0]'])['Name'])) {
                                $Quest0 = "". $QuestCsv->at($SwitchTalkVariationCsv->at($SubDataValue)['Quest[0]'])['Name'];

                            }
                            $TextStringArray = [];
                            foreach(range(0,2) as $c) {
                                if ($DefaultTalkCsv->at($SwitchTalkVariationCsv->at($SubDataValue)["DefaultTalk"])["Text[$c]"] === "0") continue;
                                $TextStringArray[] = $DefaultTalkCsv->at($SwitchTalkVariationCsv->at($SubDataValue)["DefaultTalk"])["Text[$c]"];
                            }
                            $TextString = implode("\n", $TextStringArray);
                            if (empty($Quest0)) {
                                $SwitchTalkArray[] = "{{Dialoguebox3|Intro=Default|Dialogue=\n". $TextString ."}}\n";
                            }
                            if (!empty($Quest0)) {
                                $SwitchTalkArray[] = "{{Dialoguebox3|Intro=After|Quest=". $Quest0 ."|Dialogue=\n". $TextString ."}}\n";
                            }
                        }
                        $SwitchTalkOut = implode("\n", $SwitchTalkArray);
                        $DialogueString = "{{-start-}}\n";
                        $DialogueString .= "'''$NameFormatted/$DataValue/Dialogue'''\n";
                        $DialogueString .= "$SwitchTalkOut\n";
                        $DialogueString .= "{{-stop-}}\n";
                        $DialogueArray[] = $DialogueString;
                    break;
                    case ($DataValue > 2290000) && ($DataValue < 2299999)://TRIPLETRIAD
                        $TripleTriadCheck[] = $DataValue.",";
                        $GetTripleTriadArray[] = $this->GetTripleTriad($ItemCsv, $TripleTriadCardCsv, $TripleTriadCsv, $QuestCsv, $DataValue, $DefaultTalkCsv, $TripleTriadRuleCsv, $NameFormatted);
                    break;
                    case ($DataValue > 2752000) && ($DataValue < 2752999)://FCCSHOP
                        $ShopCheck[] = $DataValue.",";
                        $ShopName = $FCCShopCsv->at($DataValue)['Name'];
                        if (empty($ShopName)) {$ShopName = "General";};
                        $FccShopArray = [];
                        $NumberItems = 0;
                        foreach(range(0,9) as $b) {
                            if (empty($ItemCsv->at($FCCShopCsv->at($DataValue)["Item[$b]"])['Name'])) break;
                            $Item = $ItemCsv->at($FCCShopCsv->at($DataValue)["Item[$b]"])['Name'];
                            $Amount = $FCCShopCsv->at($DataValue)["Cost[$b]"];
                            $Rank = $FCCShopCsv->at($DataValue)["FCRank{Required}[$b]"];
                            $FccShopArray[] = "{{Sells3|$Item|Quantity=1|Cost1=Credits|Count1=$Amount|Requires Rank = $Rank}}";
                            $NumberItems = $b + 1;
                        }
                        asort($FccShopArray);
                        $FcshopImplode = implode("\n", $FccShopArray);
                        $ShopOutputString = "{{-start-}}\n'''". $NameFormatted ."/". $ShopName ."'''\n";
                        $ShopOutputString .= "{{Shop\n";
                        $ShopOutputString .= "| Shop Name = $ShopName\n";
                        $ShopOutputString .= "| NPC Name = $NameFormatted\n";
                        $ShopOutputString .= "| Location = $NpcPlaceName\n";
                        $ShopOutputString .= "| Coordinates = $CoordLocation\n";
                        $ShopOutputString .= "| Total Items = $NumberItems\n";
                        $ShopOutputString .= "| Shop = \n";
                        $ShopOutputString .= "{{Tabsells3\n";
                        $ShopOutputString .= "|Misc = \n";
                        $ShopOutputString .= "$FcshopImplode\n";
                        $ShopOutputString .= "}}\n}}\n{{-stop-}}\n";
                        $ShopOutputArray[] = $ShopOutputString;
                    break;
                    case ($DataValue > 3080000) && ($DataValue < 3089999): //DPSCHALLENGEOFFICER
                        $DPSChallengeCheck[] = $DataValue.",";
                    break;
                    case ($DataValue > 3270000) && ($DataValue < 3279999)://TOPIC SELECT
                        foreach(range(0,9) as $a) {
                            if ($TopicSelectCsv->at($DataValue)["Shop[$a]"] == 0) continue;
                            $ShopLink = $TopicSelectCsv->at($DataValue)["Shop[$a]"];
                            switch (true) {
                                case ($ShopLink >= 262000 && $ShopLink < 264000): //gilshop
                                    $FuncShop = $this->getShop($NameFormatted, "GilShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $ShopLink, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                    $ShopOutputArray[] = $FuncShop["Shop"];
                                    $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                    $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                    if(!empty($FuncShop["Dialogue"])){
                                        $ShopCheck[] = "Dialogue,";
                                    }
                                    $ShopCheck[] = $FuncShop["Name"].",";
                                break;
                                case ($ShopLink >= 3538900 && $ShopLink < 3540000): //Prehandler
                                    $ShopID = $PreHandlerCsv->at($ShopLink)["Target"];
                                    switch (true) {
                                        case ($ShopID > 262100 && $ShopID < 269999): //gilshop
                                            $FuncShop = $this->getShop($NameFormatted, "GilShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $ShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                            $ShopOutputArray[] = $FuncShop["Shop"];
                                            $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                            $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                            if(!empty($FuncShop["Dialogue"])){
                                                $ShopCheck[] = "Dialogue,";
                                            }
                                            $ShopCheck[] = $FuncShop["Name"].",";
                                        break;
                                        case ($ShopID >= 1769000 && $ShopID < 1779999): //specialshop
                                            $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $ShopID, $DefaultTalkCsv, $ShopID, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                            $ShopOutputArray[] = $FuncShop["Shop"];
                                            $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                            $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                            if(!empty($FuncShop["Dialogue"])){
                                                $ShopCheck[] = "Dialogue,";
                                            }
                                            $ShopCheck[] = $FuncShop["Name"].",";
                                        break;
                                        case ($ShopID >= 3866620 && $ShopID < 3866999): //COLLECTABLESHOPS
                                            $FuncShop = $this->getShop($NameFormatted, "CollectablesShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $ShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                            $ShopCheck[] = $FuncShop["Name"].",";
                                            $ShopOutputArray[] = $FuncShop["Shop"];
                                            $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                        break;
                                        case ($ShopID >= 3801000 && $ShopID < 3809999): //InclusionShop
                                            foreach(range(0,29) as $b) {
                                                if (empty($InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$b]"])['Name'])) continue;
                                                foreach(range(0,20) as $c) {
                                                    $SubDataValue = "". $ShopID .".". $c ."";
                                                    if (empty($InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'])) break;
                                                    $IncShopID = $InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'];
                                                    $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $IncShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                                    $ShopOutputArray[] = $FuncShop["Shop"];
                                                    $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                                    $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                                    $ShopCheck[] = $FuncShop["Name"].",";
                                                }
                                            }
                                        break;
                                        case ($ShopID >= 3604400 && $ShopID < 3609999): //Description
                                            $DescriptionTitle = $DescriptionCsv->at($ShopID)['Text[Long]'];
                                            $HowToCheck[] = $DescriptionTitle.",";
                                        break;

                                        default:
                                        break;
                                    }
                                break;
                                case ($ShopLink >= 1769000 && $ShopLink < 1779999): //Specialshop
                                    $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $ShopLink, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                    $ShopOutputArray[] = $FuncShop["Shop"];
                                    $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                    $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                    if(!empty($FuncShop["Dialogue"])){
                                        $ShopCheck[] = "Dialogue,";
                                    }
                                    $ShopCheck[] = $FuncShop["Name"].",";
                                break;
                                
                                default:
                                break;
                            }
                        }

                    break;
                    case ($DataValue > 3470000) && ($DataValue < 3479999): //DISPOSAL SHOP
                        $FuncShop = $this->getShop($NameFormatted, "DisposalShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $DataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                        $ShopCheck[] = $FuncShop["Name"].",";
                        $ShopOutputArray[] = $FuncShop["Shop"];
                        $TotalItems[$NameFormatted][] = $FuncShop["Number"];

                    break;
                    case ($DataValue > 3530000) && ($DataValue < 3539999)://PREHANDLER
                        $ShopID = $PreHandlerCsv->at($DataValue)["Target"];
                        switch (true) {
                            case ($ShopID > 262100 && $ShopID < 269999): //gilshop
                                $FuncShop = $this->getShop($NameFormatted, "GilShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $ShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                $ShopOutputArray[] = $FuncShop["Shop"];
                                $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                if(!empty($FuncShop["Dialogue"])){
                                    $ShopCheck[] = "Dialogue,";
                                }
                                $ShopCheck[] = $FuncShop["Name"].",";
                            break;
                            case ($ShopID >= 1769000 && $ShopID < 1779999): //specialshop
                                $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $ShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                $ShopOutputArray[] = $FuncShop["Shop"];
                                $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                if(!empty($FuncShop["Dialogue"])){
                                    $ShopCheck[] = "Dialogue,";
                                }
                                $ShopCheck[] = $FuncShop["Name"].",";
                            break;
                            case ($ShopID >= 3866620 && $ShopID < 3866999): //COLLECTABLESHOPS 
                                $FuncShop = $this->getShop($NameFormatted, "CollectablesShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $ShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                $ShopCheck[] = $FuncShop["Name"].",";
                                $ShopOutputArray[] = $FuncShop["Shop"];
                                $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                            break;
                            case ($ShopID >= 3801000 && $ShopID < 3809999): //InclusionShop
                                foreach(range(0,29) as $b) {
                                    if (empty($InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$b]"])['Name'])) continue;
                                    foreach(range(0,20) as $c) {
                                        $SubDataValue = "". $ShopID .".". $c ."";
                                        if (empty($InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'])) break;
                                        $InclusionShopSpecialShopID = $InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'];
                                        $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $InclusionShopSpecialShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                                        $ShopOutputArray[] = $FuncShop["Shop"];
                                        $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                        $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                                        if(!empty($FuncShop["Dialogue"])){
                                            $ShopCheck[] = "Dialogue,";
                                        }
                                        $ShopCheck[] = $FuncShop["Name"].",";
                                    }
                                }
                            break;
                            case ($ShopID >= 3604400 && $ShopID < 3609999): //Description
                                $DescriptionTitle = $DescriptionCsv->at($ShopID)['Text[Long]'];
                                $HowToCheck[] = $DescriptionTitle.",";
                            break;

                            default:
                            break;
                        }
                    break;
                    case ($DataValue > 3604000) && ($DataValue < 3609999): //DESCRIPTION 
                        $DescriptionTitle = $DescriptionCsv->at($DataValue)['Text[Long]'];
                        $HowToCheck[] = $DescriptionTitle.",";
                    break;
                    case ($DataValue >= 3866620 && $DataValue < 3866999): //COLLECTABLESHOPS 
                        $FuncShop = $this->getShop($NameFormatted, "CollectablesShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $DataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation);
                        $ShopCheck[] = $FuncShop["Name"].",";
                        $ShopOutputArray[] = $FuncShop["Shop"];
                        $TotalItems[$NameFormatted][] = $FuncShop["Number"];
                    break;
                    default:
                        $datarray[] = $ENpcBaseCsv->at($id)["ENpcData[$i]"];
                    break;
                }
            }
            $WarpCheckArrayOut = implode("", $WarpCheck);
            $WarpCheckArray[$NameFormatted][] = $WarpCheckArrayOut;
            $ShopCheckArrayOut = implode("", array_unique($ShopCheck));
            $ShopCheckArray[$NameFormatted][] = $ShopCheckArrayOut;
            $DialogueCheckArrayOut = implode("", $DialogueCheck);
            $DialogueCheckArray[$NameFormatted][] = $DialogueCheckArrayOut;
            $LeveCheckArrayOut = implode("", $LeveCheck);
            $LeveCheckArray[$NameFormatted][] = $LeveCheckArrayOut;
            $HowToCheckArrayOut = implode("", $HowToCheck);
            $HowToCheckArray[$NameFormatted][] = $HowToCheckArrayOut;
            $ChocoboTaxiCheckArrayOut = implode("", $ChocoboTaxiCheck);
            $ChocoboTaxiCheckArray[$NameFormatted][] = $ChocoboTaxiCheckArrayOut;
            $TripleTriadCheckArrayOut = implode("", $TripleTriadCheck);
            $TripleTriadCheckArray[$NameFormatted][] = $TripleTriadCheckArrayOut;
            $dataout = implode(",", $datarray);
            $dataarray[$NameFormatted][] = $dataout;
            
            if (!empty($GetPorterArray) ) {
                $PorterArray[] = "{{-start-}}
                '''". $NameFormatted ."/". $id ."/Porter'''
                {{Porter". implode("\n", $GetPorterArray). "
                }}
                {{-stop-}}";
            }
        
        }
        $this->io->progressFinish();
        $ShopItemsNumber = [];
        foreach ($TotalItems as $key => $value) {
            $ShopItemsNumber[$key] = array_sum($value);
        }
        $Warparrayimplode = [];
        foreach ($WarpCheckArray as $key => $value) {
            $Warparrayimplode[$key] = implode("", array_unique($value));
        }
        $Shoparrayimplode = [];
        foreach ($ShopCheckArray as $key => $value) {
            $Shoparrayimplode[$key] = implode("", array_unique($value));
        }
        $Dialoguearrayimplode = [];
        foreach ($DialogueCheckArray as $key => $value) {
            $Dialoguearrayimplode[$key] = implode("", array_unique($value));
        }
        $Levearrayimplode = [];
        foreach ($LeveCheckArray as $key => $value) {
            $Levearrayimplode[$key] = implode("", array_unique($value));
        }
        $HowToarrayimplode = [];
        foreach ($HowToCheckArray as $key => $value) {
            $HowToarrayimplode[$key] = implode("", array_unique($value));
        }
        $ChocoboTaxiarrayimplode = [];
        foreach ($ChocoboTaxiCheckArray as $key => $value) {
            $ChocoboTaxiarrayimplode[$key] = implode("", array_unique($value));
        }
        $TripleTriadarrayimplode = [];
        foreach ($TripleTriadCheckArray as $key => $value) {
            $TripleTriadarrayimplode[$key] = implode("", array_unique($value));
        }
        $dataarrayimplode = [];
        foreach ($dataarray as $key => $value) {
            $dataarrayimplode[$key] = implode("", array_unique($value));
        }
        //Subdata constructors
        $PorterOut = implode("\n",$PorterArray);
        $TripleTriadOut = implode("\n", $GetTripleTriadArray);
        $ShopOut = implode("\n", $ShopOutputArray);
        $ShopDialogueOut = implode("", $ShopDialogueArray);
        $WarpPages = implode("", $WarpPagesArray);
        $DialoguePages = implode("", $DialogueArray);

        //equipment/appearance 
        //color generator
        $CMPfile= "Resources/human.cmp";
        $buffer = unpack("C*",file_get_contents($CMPfile));
        $buffer = array_chunk($buffer, 4);
        foreach ($buffer as $i => $rgba) {
            [$r, $g, $b, $a] = $rgba;
        
            $hex = sprintf("%02x%02x%02x", $r, $g, $b,);
        
            $buffer[$i] = $hex;
        }
        //hair style array
        $hairStyles = [];

        foreach ($CharaMakeCustomizeCsv->data as $id => $CharaMakeCustomize) {
            $roundId = floor($CharaMakeCustomize['id'] / 100) * 100;
            $featureId = $CharaMakeCustomize['FeatureID'];

            $hairStyles[$roundId][$featureId] = $CharaMakeCustomize;
        }

        //loop through Item.csv to make a model array
        $itemArray = [];
        $weaponArray = [];

        foreach ($ItemCsv->data as $id => $ItemData) {
            if ($ItemData['EquipSlotCategory'] = 0) continue;
            $Category = $ItemData['ItemUICategory'];
            $Weapon = $ItemData['EquipSlotCategory'];
            $ModelMainBase = str_replace(", ", "-", $ItemData['Model{Main}']);
            $ModelSubBase = str_replace(", ", "-", $ItemData['Model{Sub}']);

            $name = $ItemData['Name'];
            $itemArray[$Category][$ModelMainBase] = $ItemData;
            $itemArray[$Category][$ModelSubBase] = $ItemData;
            $weaponArray[$ModelMainBase] = $ItemData;
            $weaponArray[$ModelSubBase] = $ItemData;
        }
        //generate appearances
        $this->io->text('Building NPC Appearance Outputs ...');
        $this->io->progressStart($ENpcBaseCsv->total);
        foreach ($ENpcBaseCsv->data as $id => $EnpcBase) {
            $this->io->progressAdvance();
            
            $NameFunc = $this->NameFormat($id, $ENpcResidentCsv, $ENpcBaseCsv, $NPCNameLocationArrray[$id], $LGBArray);
            $NameFormatted = $NameFunc['Name'];
            if ($NameFunc['IsEnglish'] === false) continue;
            if (empty($NameFormatted)) continue;
            $Index = $EnpcBase['id'];
            $debug = false;
            $needsFix = "FALSE";
            $Race = $RaceCsv->at($EnpcBase['Race'])['Masculine'];
            if ($EnpcBase['Race'] < 1) continue;
            $genderBase = $EnpcBase['Gender'];
            $Gender = $EnpcBase['Gender'];
            if ($Gender == 0) {
                $Gender = "Male";
            } elseif ($Gender == 1) {
                $Gender = "Female";
            }
            $BaseFace = $EnpcBase['Face'];
            $face = null;
            $face = $BaseFace % 100; // Value matches the asset number, % 100 approximate face # nicely.
            $BodyTypeBase = $EnpcBase['BodyType'];
            switch ($BodyTypeBase)
            {
                case 1:
                    $BodyType = "Adult";
                    break;
                case 2:
                    $BodyType = "Adult";
                    break;
                case 3:
                    $BodyType = "Elderly";
                    break;
                case 4:
                    $BodyType = "Child";
                    break;
                default:
                    $BodyType = "Unknown";
                    break;
            }
            $Height = $EnpcBase['Height'];
            $Tribe = $TribeCsv->at($EnpcBase['Tribe'])['Masculine'];
            $HairStyle = $EnpcBase['HairStyle'];$GenderCalc = $EnpcBase['Gender'];
            $TribeCalc = $EnpcBase['Tribe'];
            if (($GenderCalc = 0) && ($TribeCalc = 1)) {
                $Calc = false;
            }
            if (($GenderCalc = 1) && ($TribeCalc = 1)) {
                $Calc = "10";
            }
            $extraFeatureShape = $EnpcBase['ExtraFeature1'];
            $extraFeatureSize = $EnpcBase['ExtraFeature2OrBust'];
            $isMale = boolval($genderBase) ? 'false' : 'true';
            $tribeKey = $EnpcBase['Tribe'];
            $tailIconIndex = null;
            switch ($tribeKey)
            {
                case 1: // Midlander
                    $tribeKeyCalc = ($isMale == "true") ? 0 : 1;
                    break;
                case 2: // Highlander
                    $tribeKeyCalc = ($isMale == "true") ? 2 : 3;
                    break;
                case 3: // Wildwood
                    $tribeKeyCalc = ($isMale == "true") ? 4 : 5;
                    break;
                case 4: // Duskwight
                    $tribeKeyCalc = ($isMale == "true") ? 6 : 7;
                    break;
                case 5: // Plainsfolks
                    $tribeKeyCalc = ($isMale == "true") ? 8 : 9;
                    break;
                case 6: // Dunesfolk
                    $tribeKeyCalc = ($isMale == "true") ? 10 : 11;
                    break;
                case 7: // Seeker of the Sun
                    $tribeKeyCalc = ($isMale == "true") ? 12 : 13;
                    break;
                case 8: // Keeper of the Moon
                    $tribeKeyCalc = ($isMale == "true") ? 14 : 15;
                    break;
                case 9: // Sea Wolf
                    $tribeKeyCalc = ($isMale == "true") ? 16 : 17;
                    break;
                case 10: // Hellsguard
                    $tribeKeyCalc = ($isMale == "true") ? 18 : 19;
                    break;
                case 11: // Raen
                    $tribeKeyCalc = ($isMale == "true") ? 20 : 21;
                    break;
                case 12: // Xaela
                    $tribeKeyCalc = ($isMale == "true") ? 22 : 23;
                    break;
                case 13: // Helions
                    $tribeKeyCalc = ($isMale == "true") ? 24 : 25;
                    break;
                case 14: // The Lost
                    $tribeKeyCalc = ($isMale == "true") ? 26 : 27;
                    break;
                case 15: // Rava
                    $tribeKeyCalc = ($isMale == "true") ? 28 : 29;
                    break;
                case 16: // Veena
                    $tribeKeyCalc = ($isMale == "true") ? 30 : 31;
                    break;
            }
            //face/fur/tail icons
            $BaseFaceCalc = $face - 1;
            $race = $EnpcBase['Race'];
            //var_dump($race);
            $warning = false;
            $warningGen = false;
            if ($face > 6) {
                $warning = "\n|Custom Face = yes";
                $BaseFaceCalc = 1;
            }
            if ($BaseFaceCalc < 1) {
                $warning = "\n|Custom Face = yes";
                $BaseFaceCalc = 1;
            }
            $tailOrEarShape = $extraFeatureShape -1;
            if ($tailOrEarShape > 50) {
                $warningGen = " - Custom ?";
                $tailOrEarShape = 1;
            }
            if ($tailOrEarShape < 1) {
                $warningGen = " - Custom ?";
                $tailOrEarShape = 1;
            }
            switch ($tribeKey)
            {
                case 1: // Midlander
                    $tribeCode = ($isMale == "true") ? 0 : 100;
                    $headIconIndex = ($isMale == "true") ? 5 : 6;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."";
                    break;
                case 2: // Highlander
                    $tribeCode = ($isMale == "true") ? 200 : 300;
                    $headIconIndex = ($isMale == "true") ? 5 : 6;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."";
                    break;
                case 3: // Wildwood
                case 4: // Duskwight
                    $tribeCode = ($isMale == "true") ? 400 : 500;
                    $headIconIndex = ($isMale == "true") ? 4 : 5;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $EarShape = $extraFeatureShape;
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."\n|Ear Shape = ". $EarShape ."";
                    break;
                case 5: // Plainsfolks
                case 6: // Dunesfolk
                    $tribeCode = ($isMale == "true") ? 600 : 700;
                    $headIconIndex = ($isMale == "true") ? 4 : 5;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $EarShape = $extraFeatureShape;
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."\n|Ear Shape = ". $EarShape ."";
                    break;
                case 7: // Seeker of the Sun
                case 8: // Keeper of the Moon
                    $tribeCode = ($isMale == "true") ? 800 : 900;
                    $headIconIndex = ($isMale == "true") ? 6 : 7;
                    $tailIconIndex = ($isMale == "true") ? 2 : 3;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $tailIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$tailIconIndex][$tailOrEarShape]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."\n|Tail Shape = ". $tailIcon .".png";
                    break;
                case 9: // Sea Wolf
                case 10: // Hellsguard
                    $tribeCode = ($isMale == "true") ? 1000 : 1100;
                    $headIconIndex = ($isMale == "true") ? 5 : 6;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."";
                    break;
                case 11: // Raen
                case 12: // Xaela
                    $tribeCode = ($isMale == "true") ? 1200 : 1300;
                    $headIconIndex = ($isMale == "true") ? 6 : 7;
                    $tailIconIndex = ($isMale == "true") ? 2 : 3;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $tailIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$tailIconIndex][$tailOrEarShape]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."\n|Tail Shape = ". $tailIcon .".png";
                    break;

                // No alternate genders for Hrothgar, Viera.
                // For Hrothgar, these might be faces too?
                case 13: // Helions
                case 14: // The Lost
                    $tribeCode = 1400;
                    $furIconIndex = 2;
                    $tailIconIndex = 4;
                    $furIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$furIconIndex][$BaseFaceCalc]"];
                    $tailIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$tailIconIndex][$tailOrEarShape]"];
                    $extraIcons = "|Fur Type = ". $furIcon .".png\n|Tail Shape = ". $tailIcon .".png";
                    break;
                case 15: // Rava
                case 16: // Veena
                    $tribeCode = 1500;
                    $headIconIndex = 5;
                    $earIconIndex = 14;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $earIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$earIconIndex][$tailOrEarShape]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."\n|Ear Shape = ". $earIcon .".png";
                    break;
            }
            //HairColor
            $GenderValue = ($isMale == "true") ? 0 : 1;
            $listIndex = ($tribeKey * 2 + $GenderValue) * 5 + 4;
            $hairIndex = $listIndex * 256;
            $hairColorBase = $EnpcBase['HairColor'];
            $hairColorIndex = $hairIndex + $hairColorBase;
            $hairColor = $buffer[$hairColorIndex];
            $hairHighlightColor = false;
            if ($EnpcBase['HairHighlightColor'] != 0) {
                $HairHighlightColorOffset = 1 * 256;
                $hairHighlightColorBase = $EnpcBase['HairHighlightColor'];
                $hairHighlightColorIndex = $HairHighlightColorOffset + $hairHighlightColorBase;
                $hairHighlightColor = $buffer[$hairHighlightColorIndex];
            }
            //HairStyle
            $hairStyleBase = $EnpcBase['HairStyle'];
            $warningHair = false;
            if ($hairStyleBase > 200) {
                $hairStyleBase = 1;
                $warningHair = "\n|Custom Hair = yes";
            }
            $hairStyleRaw = $hairStyles[$tribeCode][$hairStyleBase];
            $hairStyleIcon = "".$hairStyleRaw['Icon'] .".png".$warningHair ."";
            //Skin Colour
            $listIndex = ($tribeKey * 2 + $GenderValue) * 5 + 3;
            $skinIndex = $listIndex * 256;

            $skinColorBase = $EnpcBase['SkinColor'];
            $skinColorIndex = $skinIndex + $skinColorBase;
            $skinColor = $buffer[$skinColorIndex];

            //Eyes
            $EyeColorOffset = 0 * 256;
            $eyeColorBase = $EnpcBase['EyeColor'];
            $eyeColorBuffer = $eyeColorBase + $EyeColorOffset;
            $eyeColor = $buffer[$eyeColorBuffer];

            $heterochromiaColor ="";
            $eyeHeterochromia = $EnpcBase['EyeHeterochromia'];
            if ($eyeHeterochromia != $eyeColorBase) {
                $heterochromiaBuffer = $eyeHeterochromia + $EyeColorOffset;
                $heterochromiaColor = $buffer[$heterochromiaBuffer];
            }
            $eyeSize = "Large";
            $eyeShapeBase = $EnpcBase['EyeShape'];
            $eyeShape = $eyeShapeBase + 1;
            if ($eyeShapeBase >= 128) {
                $eyeShape = ($eyeShapeBase - 128) + 1;
                $eyeSize = "Small";
            }
            //Mouth and Lips
            $LightLipFacePaintColorOffset = 7 * 256;
            $DarkLipFacePaintColorOffset = 2 * 256;
            $mouthShape = $EnpcBase['Mouth'];
            if ($tribeKey == 13 || 14) {
                $lipColourBase = $EnpcBase['LipColor'];
                $mouthData = "|Mouth = ". $mouthShape ."\n|FurType = ". $lipColourBase ."";
            }
            if ($mouthShape >= 128) {
                $mouthShape = 1 + ($mouthShape - 128);
                if ($EnpcBase['LipColor'] >= 128) {
                    $lipShade = "Light";
                    $lipColourCalc = $EnpcBase['LipColor'] + $LightLipFacePaintColorOffset;
                    $lipColour = $buffer[$lipColourCalc];
                } elseif ($EnpcBase['LipColor'] < 128){
                    $lipShade = "Dark";
                    $lipColourCalc = $EnpcBase['LipColor'] + $DarkLipFacePaintColorOffset;
                    $lipColour = $buffer[$lipColourCalc];
                }
                $mouthData = "|Mouth = ". $mouthShape ."\n|Lip Color = ". $lipColour ."\n|Lip Shade = ". $lipShade ."";
            } elseif ($mouthShape < 128) {
                $mouthShape = $mouthShape + 1;
                $lipShade = false;
                $lipColour = false;
                $mouthData = "|Mouth = ". $mouthShape ."";
            }
            //Face Paint
            //get facepaint keys based on gender/race
            $baseRowKey = 1600;
            switch ($tribeKey)
            {
                case 1: // Midlander
                case 2: // Highlander
                case 3: // Wildwood
                case 4: // Duskwight
                case 5: // Plainsfolks
                case 6: // Dunesfolk
                case 7: // Seeker of the Sun
                case 8: // Keeper of the Moon
                case 9: // Sea Wolf
                case 10: // Hellsguard
                case 11: // Raen
                case 12: // Xaela
                    $tribeOffset = $baseRowKey + (($tribeKey - 1) * 100);
                    $FacePaintCustomizeIndex = ($isMale == "true") ? $tribeOffset : $tribeOffset + 50;
                    break;

                case 13: // Helions
                    $FacePaintCustomizeIndex = 2800;
                    break;
                case 14: // The Lost
                    $FacePaintCustomizeIndex = 2850;
                    break;
                case 15: // Rava
                    $FacePaintCustomizeIndex = 2900;
                    break;
                case 16: // Veena
                    $FacePaintCustomizeIndex = 2950;
                    break;
            }
            //Face Paint Color
            $facePaintColorBase = $EnpcBase['FacePaintColor'];
            $facePaintColor = false;
            $facePaintColorShade = "Light";
            if ($facePaintColorBase >= 128) {
                $facePaintColorShade = "Light";
                $facePaintColourIndex = 1 + ($facePaintColorBase - 128);
                $facePaintColourCalc = $EnpcBase['FacePaintColor'] + $LightLipFacePaintColorOffset;
                $facePaintColorRGB = $buffer[$facePaintColourCalc];
                $facePaintColor = "|Face Paint Color = ". $facePaintColorRGB ."\n|Face Paint Shade = ". $facePaintColorShade ."";
            } elseif ($facePaintColorBase < 128) {
                $facePaintColorShade = "Dark";
                $facePaintColourCalc = $EnpcBase['FacePaintColor'] + $DarkLipFacePaintColorOffset;
                $facePaintColorRGB = $buffer[$facePaintColourCalc];
                $facePaintColor = "|Face Paint Color = ". $facePaintColorRGB ."\n|Face Paint Shade = ". $facePaintColorShade ."";
            }
            //Face Paint Icon
            $facePaintBase = $EnpcBase['FacePaint'] + 1;
            $facePaintIcon = false;
            if ($facePaintBase >= 128) {
                $facePaint = 1 + ($facePaintBase - 128);
                $facePaintReverse = "|Face Paint Reversed = True";
                $facePaintIconIndex = $FacePaintCustomizeIndex + $facePaint;
                $facePaintIconImage = $CharaMakeCustomizeCsv->at($facePaintIconIndex)['Icon'];
                if ($facePaintIconImage > 0) {
                    $facePaint = $facePaintIconImage;
                    $facePaintIcon = "|Face Paint = ". $facePaintIconImage ."\n". $facePaintReverse ."";
                }
            } elseif ($facePaintBase < 128) {
                $facePaintIconIndex = $FacePaintCustomizeIndex + $facePaintBase;
                $facePaintIconImage = $CharaMakeCustomizeCsv->at($facePaintIconIndex)['Icon'];
                $facePaint = $facePaintIconImage;
                $facePaintReverse = "|Face Paint Reversed = False";
                if ($facePaintIconImage > 0) {
                    $facePaint = $facePaintIconImage;
                    $facePaintIcon = "|Face Paint = ". $facePaintIconImage ."\n". $facePaintReverse ."\n". $facePaintColor ."";
                }
            }
            //Extra Features
            $raceKey = $EnpcBase['Race'];
            switch ($raceKey)
            {
                case 1: // Hyur
                case 5: // Roegadyn
                    $extraFeatureName = null;
                    break;

                case 2: // Elezen
                case 3: // Lalafell
                case 8: // Viera
                    $extraFeatureName = "Ear";
                    break;

                case 4: // Miqo'te
                case 6: // Au Ra
                case 7: // Hrothgar
                    $extraFeatureName = "Tail";
                    break;
            }
            // Bust & Muscles - flex fields.
            $bust = false;
            $bustAndMuscle = false;
            if ($raceKey == 5 || $raceKey == 1)
            {
                // Roegadyn & Hyur
                $bust = false;
                $muscle = $EnpcBase["BustOrTone1"];
                if ($isMale == "false"){
                    $bust = "\n|BustSize = ". $EnpcBase["ExtraFeature2OrBust"] ."";
                }
                $bustAndMuscle = "\n|Muscles = ". $muscle ."". $bust ."";
            }
            else if ($raceKey == 6 && $isMale == "true")
            {
                // Au Ra male muscles
                $muscle = $EnpcBase["BustOrTone1"];
                $bustAndMuscle = "\n|Muscles = ". $muscle ."";
            }
            else if ($isMale == "false")
            {
                // Other female bust sizes
                $bust = $EnpcBase["BustOrTone1"];
                $bustAndMuscle = "\n|BustSize = ". $bust ."";
            }
            $extraFeature = false;
            if ($extraFeatureName != null) {
                $extraFeature = "\n|". $extraFeatureName ." Length = ". $extraFeatureSize ."";
            }
            //Facial Feature
            $facialFeature = null;
            $facialFeatureArray = null;
            $facialFeatureArray = [];
            $facialFeatureBase =  null;
            $facialFeatureBasePad = null;
            $facialFeatureIcon = null;
            $facialFeatureIcon = [];
            // ^ i couldn't find the cause so i emptied out all values ^
            $facialFeatureBase = $EnpcBase['FacialFeature'];
            $facialFeatureArray = array(($facialFeatureBase & 1) == 1, ($facialFeatureBase & 2) == 2, ($facialFeatureBase & 4) == 4, ($facialFeatureBase & 8) == 8, ($facialFeatureBase & 16) == 16, ($facialFeatureBase & 32) == 32, ($facialFeatureBase & 64) == 64, ($facialFeatureBase & 128) == 128);
            $facialFeatureArraysplit = str_split($facialFeatureBasePad);
            //facial features
            // colors
            $listIndex = ($tribeKey * 2 + $GenderValue) * 5 + 4;
            $facialFeatureIndex = $listIndex * 256;
            $facialFeatureColorBase = $EnpcBase['FacialFeatureColor'];
            $facialFeatureColorIndex = $facialFeatureIndex + $facialFeatureColorBase;
            $facialFeatureColor = $buffer[$facialFeatureColorIndex];
            $tribe = $EnpcBase['Tribe'];
            switch ($tribeKey)
            {
                case 1: // Midlander
                case 2: // Highlander
                case 3: // Wildwood
                case 4: // Duskwight
                case 5: // Plainsfolks
                case 6: // Dunesfolk
                case 7: // Seeker of the Sun
                case 8: // Keeper of the Moon
                case 9: // Sea Wolf
                case 10: // Hellsguard
                case 11: // Raen
                case 12: // Xaela
                    $facialFace = $face - 1;
                    break;
                case 13: // Helions
                case 14: // The Lost
                    $facialFace = $face;
                    break;
                case 15: // Rava
                case 16: // Veena
                    $facialFace = $face - 1;
                    break;
            }
            if ($face < 7) {
                for ($i=0; $i < 5; $i++) {
                    if ($facialFeatureArray[$i] == 1) {
                        $facialFeatureIcon[$i] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                    }
                }
            } elseif ($face > 6) {
                $facialFeatureicon = [];
            }


            if (!empty($facialFeatureIcon)) {
                $facialFeature = implode(",", $facialFeatureIcon);
            }
            //tattoos
            $facialFeatureExtraPre = false;
            $facialFeatureExtraImplode = false;
            $facialFeatureExtraColor = false;
            $facialFeatureExtra = [];
            if ($face < 7) {
                for ($i=5; $i < 7; $i++) {
                    if ($facialFeatureArray[$i] == 1) {

                        switch ($tribeKey)
                        {
                            case 1: // Midlander
                            case 2: // Highlander
                                $facialFeatureExtraPre = "\n|Tattoos = ";
                                $facialFeatureExtraColor = "\n|Tattoo Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 3: // Wildwood
                                $facialFeatureExtraPre = "\n|Ear Clasp = ";
                                $facialFeatureExtraColor = "\n|Ear Clasp Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 4: // Duskwight
                            case 5: // Plainsfolks
                            case 6: // Dunesfolk
                            case 7: // Seeker of the Sun
                                $facialFeatureExtraPre = "\n|Tattoos = ";
                                $facialFeatureExtraColor = "\n|Tattoo Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 8: // Keeper of the Moon
                                if ($GenderCalc == 0) {
                                    $facialFeatureExtraPre = "\n|Tattoos = ";
                                    $facialFeatureExtraColor = "\n|Tattoo Color = ". $facialFeatureColor ."";
                                    $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                } elseif ($GenderCalc == 1) {
                                    $facialFeatureExtraPre = "\n|Ear Clasp = ";
                                    $facialFeatureExtraColor = "\n|Ear Clasp Color = ". $facialFeatureColor ."";
                                    $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                }
                                break;
                            case 9: // Sea Wolf
                            case 10: // Hellsguard
                                $facialFeatureExtraPre = "\n|Tattoos = ";
                                $facialFeatureExtraColor = "\n|Tattoo Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 11: // Raen
                            case 12: // Xaela
                                $facialFeatureExtraPre = "\n|Limbal Rings = ";
                                $facialFeatureExtraColor = "\n|Limbal Ring Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 13: // Helions
                            case 14: // The Lost
                                $facialFeatureExtraPre = "\n|Tattoos = ";
                                $facialFeatureExtraColor = false;
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 15: // Rava
                            case 16: // Veena
                                $facialFeatureExtraPre = "\n|Tattoos = ";
                                $facialFeatureExtraColor = "\n|Tattoo Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                        }
                    }
                }
            } elseif ($face > 6) {
                $facialFeatureicon = [];
            }
            if (!empty($facialFeatureExtra)) {
                $facialFeatureExtraImplode = implode(",", $facialFeatureExtra);
            }
            $facialFeatureExtra = "". $facialFeatureExtraPre ."". $facialFeatureExtraImplode ."". $facialFeatureExtraColor ."";
            if ($headIcon < 1) {
                $headIcon = "CustomFace";
            }
            //pure debugging of certain strings
            if ($debug == true) {
                var_dump($facialFeatureBase);
                var_dump($facialFeatureArray);
                var_dump($facialFeatureBasePad);

                $ex = $EnpcBase['FacialFeature'];
                $cusomizekeystring = "
                tribeCode > ". $tribeCode ."
                isMale  > ". $isMale ."
                FacePaintCustomizeIndex > ". $FacePaintCustomizeIndex ."
                genderBase > ". $genderBase ."
                face > ". $face ."
                tribeKey > ". $tribeKey ."
                hairColorIndex > ". $hairColorIndex ."
                extraFeatureName > ". $extraFeatureName ."
                hairColorIndex > ". $extraFeatureShape ."
                hairColorIndex > ". $extraFeatureSize ."
                bustAndMuscle > ". $bustAndMuscle ."
                FacialFeature > ". $facialFeature ."". $facialFeatureExtra ."
                FacePaintCustomizeIndex > ". $FacePaintCustomizeIndex ."
                facePaint > ". $facePaint ."
                facePaintIconIndex > ". $facePaintIconIndex ."
                extrafea > ". $ex ."
                extraIcons > ". $extraIcons . "
                BaseFace > ". $BaseFace ."
                hairStyleBase > ". $hairStyleBase ."
                hairStyleIcon > ". $hairStyleIcon ."
                BaseFaceCalc > ". $BaseFaceCalc ."
                ";
            }
            if ($debug == false) {
                $cusomizekeystring = false;
            }
            $eyebrowsBase = $EnpcBase['Eyebrows'];
            $Eyebrows = $eyebrowsBase + 1;
            $noseBase = $EnpcBase['Nose'];
            $Nose = $noseBase + 1;
            $jawBase = $EnpcBase['Jaw'];
            $Jaw = $jawBase + 1;
            
            $NameFunc = $this->NameFormat($id, $ENpcResidentCsv, $ENpcBaseCsv, $NPCNameLocationArrray[$id], $LGBArray);
            $NameFormatted = $NameFunc['Name'];
            if ($NameFunc['IsEnglish'] === false) continue;
            if (empty($NameFormatted)) continue;

            $BodyOutput = "{{NPC Appearance\n";
            $BodyOutput .= "|Race = ". $Race ."\n";
            $BodyOutput .= "|Gender = ". $Gender ."\n";
            $BodyOutput .= "|Body Type = ". $BodyType ."\n";
            $BodyOutput .= "|Height = ". $Height ."\n";
            $BodyOutput .= "|Clan = ". $Tribe ."\n";
            $BodyOutput .= "". $extraIcons ."\n";
            $BodyOutput .= "|Hair Style = ". $hairStyleIcon ."\n";
            $BodyOutput .= "|Skin Color = ". $skinColor ."\n";
            $BodyOutput .= "|Hair Color = ". $hairColor ."\n";
            $BodyOutput .= "|Hair Highlight Color = ". $hairHighlightColor ."\n";
            $BodyOutput .= "|Facial Feature = ". $facialFeature ."". $facialFeatureExtra ."". $extraFeature ."". $bustAndMuscle ."\n";
            $BodyOutput .= "|Eyebrows = ". $Eyebrows ."\n";
            $BodyOutput .= "|Eye Shape = ". $eyeShape ."\n";
            $BodyOutput .= "|Eye Size = ". $eyeSize ."\n";
            $BodyOutput .= "|Eye Color = ". $eyeColor ."\n";
            $BodyOutput .= "|Eye Heterochromia = ". $heterochromiaColor ."\n";
            $BodyOutput .= "|Nose = ". $Nose ."\n";
            $BodyOutput .= "|Jaw = ". $Jaw ."\n";
            $BodyOutput .= "". $mouthData ."\n";
            $BodyOutput .= "". $facePaintIcon ."\n";
            $EquipmentArray = $this->getEquipment($ENpcBaseCsv, $NpcEquipCsv, $weaponArray, $isMale, $StainCsv, $id, $itemArray);
            
            $EquipmentOutput = "|Head = ". $EquipmentArray['Head']['Item'] ."\n";
            $EquipmentOutput .= "|Head Dye = ". str_ireplace("0", "", $EquipmentArray['Head']['Dye']) ."\n";
            $EquipmentOutput .= "|Visor = ". $EquipmentArray['Visor'] ."\n";
            $EquipmentOutput .= "|Body = ". $EquipmentArray['Body']['Item'] ."\n";
            $EquipmentOutput .= "|Body Dye = ". str_ireplace("0", "", $EquipmentArray['Body']['Dye']) ."\n";
            $EquipmentOutput .= "|Hands = ". $EquipmentArray['Hands']['Item'] ."\n";
            $EquipmentOutput .= "|Hands Dye = ". str_ireplace("0", "", $EquipmentArray['Hands']['Dye']) ."\n";
            $EquipmentOutput .= "|Legs = ". $EquipmentArray['Legs']['Item'] ."\n";
            $EquipmentOutput .= "|Legs Dye = ". str_ireplace("0", "", $EquipmentArray['Legs']['Dye']) ."\n";
            $EquipmentOutput .= "|Feet = ". $EquipmentArray['Feet']['Item'] ."\n";
            $EquipmentOutput .= "|Feet Dye = ". str_ireplace("0", "", $EquipmentArray['Feet']['Dye']) ."\n";
            $EquipmentOutput .= "|Main Hand = ". $EquipmentArray['MainHand']['Item'] ."\n";
            $EquipmentOutput .= "|Off Hand = ". $EquipmentArray['OffHand']['Item'] ."\n";
            $EquipmentOutput .= "}}\n";

            $NPCEquipmentArray[$NameFormatted][] = "$BodyOutput\n$EquipmentOutput";


        }
        $NPCUniqueApperanceIDs = [];
        $EquipmentarrayUnique = [];
        foreach ($NPCEquipmentArray as $key => $value) {
            $EquipmentarrayUnique[$key] = array_unique($value);
            $EquipmentArrayFormatted = [];
            $a = 0;
            $ApperanceCounter = [];
            foreach ($EquipmentarrayUnique[$key] as $key1 => $value1) {
                $a++;
                $EquipmentArrayFormatted[$key1] = "{{-start-}}\n'''$key/Appearance/$a'''\n$value1\n{{-stop-}}";
                $ApperanceCounter[] = $a;
            }
            $ApperanceCount = implode(",", $ApperanceCounter);
            $EquipmentarrayUnique[$key] = implode("\n", $EquipmentArrayFormatted);
            $NPCUniqueApperanceIDs[$key] = $ApperanceCount;
        }
        $EquipmentOut = implode("\n", $EquipmentarrayUnique);
        //if (!file_exists("output/$PatchID")) { mkdir("output/$PatchID", 0777, true); }
        //$EquipFile = fopen("output/$PatchID/ENPCEquipment.txt", 'w');
        //fwrite($EquipFile, json_encode($EquipmentarrayImplode));
        //fclose($EquipFile);
        $this->io->progressFinish();
        //mainpage constructor
        $this->io->text('Building NPC Final Outputs ...');
        $this->io->progressStart($ENpcResidentCsv->total);
        foreach ($ENpcResidentCsv->data as $id => $NPCs) {
            $this->io->progressAdvance();
            
            $NameFunc = $this->NameFormat($id, $ENpcResidentCsv, $ENpcBaseCsv, $NPCNameLocationArrray[$id], $LGBArray);
            $NameFormatted = $NameFunc['Name'];
            if ($NameFunc['IsEnglish'] === false) continue;
            if (empty($NameFormatted)) continue;
            //Race/Gender/Tribe
            switch ($ENpcBaseCsv->at($id)['Race']) {
                case 0:
                    $Race = "| Race = Non-Humanoid";
                    $Tribe = "";
                    $Gender = "";
                break;
                
                default:
                    $Race = "| Race = ". $RaceCsv->at($ENpcBaseCsv->at($id)['Race'])['Masculine'];
                    switch ($ENpcBaseCsv->at($id)['Gender']) {
                        case 0:
                            $Gender = "\n    | Gender = Male";
                        break;
                        case 1:
                            $Gender = "\n    | Gender = Female";
                        break;
                    }
                    $Tribe = "\n    | Clan = ". $TribeCsv->at($ENpcBaseCsv->at($id)['Tribe'])['Masculine'] ."";
                break;
            }

            $subLocation = "";
            if (!empty($LGBArray[$id]['Territory'])){
                $Territory = $LGBArray[$id]['Territory'];
                $X = $LGBArray[$id]['x'];
                $Y = $LGBArray[$id]['y'];
                $keyarray = [];
                foreach (range(0, 1000) as $i) {
                    if (empty($JSONTeriArray[$Territory][$i]["x"])) break;
                    $calcA = ($X - $JSONTeriArray[$Territory][$i]["x"]); 
                    $calcB = ($Y - $JSONTeriArray[$Territory][$i]["y"]);
                    $calcX = $calcA * $calcB;
                    $keyarray[] = abs($calcX);
                }
                asort($keyarray);
                $smallestNumber = key($keyarray);
                if (empty($JSONTeriArray[$Territory][$smallestNumber]["placename"])) {
                    $subLocation = "";
                } else {
                    $subLocation = $JSONTeriArray[$Territory][$smallestNumber]["placename"];
                }
            }
            //$NpcEquipmentArrayOpen = "{{-start-}}\n". implode("\n", )."";
            //produce map
            $MapOutputString = "";
            $sub = "";
            if (!empty($LGBArray[$id]['x'])) {
                $MapX = $this->GetLGBPos($LGBArray[$id]['x'], $LGBArray[$id]['y'], $LGBArray[$id]['Territory'], $TerritoryTypeCsv, $MapCsv)["X"];
                $MapY = $this->GetLGBPos($LGBArray[$id]['x'], $LGBArray[$id]['y'], $LGBArray[$id]['Territory'], $TerritoryTypeCsv, $MapCsv)["Y"];
                $MapXPix = $this->GetLGBPos($LGBArray[$id]['x'], $LGBArray[$id]['y'], $LGBArray[$id]['Territory'], $TerritoryTypeCsv, $MapCsv)["PX"];
                $MapYPix = $this->GetLGBPos($LGBArray[$id]['x'], $LGBArray[$id]['y'], $LGBArray[$id]['Territory'], $TerritoryTypeCsv, $MapCsv)["PY"];

                $SubLocation = $subLocation;
                $MapName = $PlaceNameCsv->at($TerritoryTypeCsv->at($LGBArray[$id]['Territory'])['PlaceName'])['Name'];
                $NpcMapCodeName = $TerritoryTypeCsv->at($LGBArray[$id]['Territory'])['Name'];
                $MapID = $TerritoryTypeCsv->at($LGBArray[$id]['Territory'])['Map'];
                if ($MapCsv->at($MapID)["PlaceName{Sub}"] > 0) {
                    $sub = " - ".$PlaceNameCsv->at($MapCsv->at($MapID)["PlaceName{Sub}"])['Name']."";
                } elseif ($MapCsv->at($MapID)["PlaceName"] > 0) {
                    $sub = "";
                }
                $code = substr($NpcMapCodeName, 0, 4);
                if ($code == "z3e2") {
                    $NpcPlaceName = "The Prima Vista Tiring Room";
                }
                if ($code == "f1d9") {
                    $MapName = "The Haunted Manor";
                }
                $BasePlaceName = "$code - {$MapName}{$sub}";
    
                $LevelID = $LGBArray[$id]['id'];
                $Patch = $PatchNumber[$id];
                $MapFestival = "";
                $FestivalID = $LGBArray[$id]["festivalID"];
                if (!empty($LGBArray[$id]["festivalID"])) {
                    $FesitvalName = $FestivalArray[$FestivalID];
                    $MapFestival = "  | Event = ". str_replace("_", " (", $FesitvalName). ")\n";
                }
                $QuestIssuer = "";
                if (!empty($Issuers[$id])){
                    $QuestIssuer = "  | Issuer = ".$Issuers[$id]."\n";
                }
                //MapOutput = 
                $MapOutputString = "{{-start-}}\n'''". $NameFormatted ."/Map/". $id ."'''\n";
                $MapOutputString .= "{{NPCMap\n";
                $MapOutputString .= "  | base = $BasePlaceName.png\n";
                $MapOutputString .= "  | float_link = $NameFormatted\n";
                $MapOutputString .= "  | float_caption = $NameFormatted\n";
                $MapOutputString .= "  | float_caption_coordinates = (x:". $MapX .", y:". $MapY .")\n";
                $MapOutputString .= "  | x = $MapXPix\n";
                $MapOutputString .= "  | y = $MapYPix\n";
                $MapOutputString .= "  | zone = $MapName\n";
                $MapOutputString .= "  | level_id = $LevelID\n";
                $MapOutputString .= "  | npc_id = $id\n";
                $MapOutputString .= "$QuestIssuer";
                $MapOutputString .= "  | patch = $Patch\n";
                $MapOutputString .= "  | Sublocation = $SubLocation\n";
                $MapOutputString .= "$MapFestival";
                $MapOutputString .= "}}\n";
                $MapOutputString .= "{{-stop-}}\n";
                $MapArray[] = $MapOutputString;
            }
            $ShopItemsTotalNo = null;
            if (!empty($ShopItemsNumber[$NameFormatted])) {
                $ShopItemsTotalNo = $ShopItemsNumber[$NameFormatted];
            } else {
                $ShopItemsTotalNo = "";
            }
            if (!empty($NPCUniqueApperanceIDs[$NameFormatted])) {
                $UniqueApperances = $NPCUniqueApperanceIDs[$NameFormatted];
            } else {
                $UniqueApperances = "";
            }
            //check festival
            $FestivalNPC = "";
            if (!empty($LGBArray[$id]["festivalID"])) {
                $FesitvalName = $FestivalArray[$FestivalID];
                $FestivalNPC = "\n    | Event = ". str_replace("_", " (", $FesitvalName). ")";
            }

            $dataout = implode("\n", $datarray);
            $LastMapLoc = "";
            if (!empty($NPCIds[$NameFormatted])) {
                $LastMapLocExp = explode(",",$NPCIds[$NameFormatted]);
                $LastMapLoc = end($LastMapLocExp);
            }
            
            $Patch = $NpcPatchArray[$NameFormatted];
            if ($NameFunc['IsEnglish'] === false) continue;
            if (empty($NameFormatted)) continue;
            $ListofIDS = "";
            if (!empty($NPCIds[$NameFormatted])){
                $ListofIDS = $NPCIds[$NameFormatted];
            }
            $Npcarray2[$NameFormatted][0] = "{{-start-}}\n'''". $NameFormatted ."'''
            {{Infobox NPC
            <!-- 

                The data on this page is automatically generated and should not be touched. If you believe something on this page is no longer accurate, please contact someone from the Wiki Admin Team on Discord. Biography, Notes and other player generated data is located at $NameFormatted/Player Data
                
            -->
            | Patch = $Patch
            | Name = $NameFormatted
            | Image = 
            $Race$Gender$Tribe
            | Title = ". $NPCs["Title"] ."
            | IDs = $ListofIDS
            | Apperance IDs = $UniqueApperances
            | Shop = ".substr($Shoparrayimplode[$NameFormatted],0,-1)."
            | TotalItems = $ShopItemsTotalNo
            | Warp = ".substr($Warparrayimplode[$NameFormatted],0,-1)."
            | Dialogue = ".substr($Dialoguearrayimplode[$NameFormatted],0,-1)."
            | Leves = ".substr($Levearrayimplode[$NameFormatted],0,-1)."
            | Active Help = ".substr($HowToarrayimplode[$NameFormatted],0,-1)."
            | Porter = ".substr($ChocoboTaxiarrayimplode[$NameFormatted],0,-1)."
            | Triple Triad = ".substr($TripleTriadarrayimplode[$NameFormatted],0,-1)."
            | Last Position = $LastMapLoc$FestivalNPC
            }}
            {{-stop-}}";
            $NpcPlayerDataString = "{{-start-}}\n'''". $NameFormatted ."/Player_Data'''\n";
            $NpcPlayerDataString .= "{{Player Data\n";
            $NpcPlayerDataString .= "|Name = $NameFormatted\n";
            $NpcPlayerDataString .= "|Full Name = \n";
            $NpcPlayerDataString .= "|Title = \n";
            $NpcPlayerDataString .= "|Employer = \n";
            $NpcPlayerDataString .= "|Occupation = \n";
            $NpcPlayerDataString .= "|Affiliation = \n";
            $NpcPlayerDataString .= "|Bio = \n";
            $NpcPlayerDataString .= "|Notes = \n";
            $NpcPlayerDataString .= "|Images = \n";
            $NpcPlayerDataString .= "|Pre-Calamity Dialogue =\n";
            $NpcPlayerDataString .= "}}\n";
            $NpcPlayerDataString .= "{{-stop-}}\n";
            $NpcPlayerDataArray[$NameFormatted][0] = $NpcPlayerDataString;
        
        }
            
        //if (!file_exists("output/$PatchID")) { mkdir("output/$PatchID", 0777, true); }
        //$EquipFile = fopen("output/$PatchID/ENPCEquipment.txt", 'w');
        //fwrite($EquipFile, $htmlString);
        //fclose($EquipFile);
            
        //---------------------------------------------------------------------------------
        $finaloutputarray = [];
        foreach ($Npcarray2 as $key => $value) {
            $finaloutput[$key] = implode("\n", $value);
        }
        $FinalNpcPlayerDataArray = [];
        foreach ($NpcPlayerDataArray as $key => $value) {
            $FinalNpcPlayerDataArray[$key] = implode("\n", $value);
        }
        $FinalNpcPlayerData = implode("\n", $FinalNpcPlayerDataArray);

        $Output = implode("\n", $finaloutput);

        $MapOutput = implode("\n", $MapArray);

        //$GetHowToOut = implode("\n", array_unique($GetHowToArray));
        
        $data = [
            '{Output}' => $Output,
        ];

        // format using Gamer Escape formatter and add to data array
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeCollectWiki.txt
        $this->io->progressFinish();
        $this->io->text(' Saving NPC_Main');
        $info = $this->save("NPC_Main.txt", 9999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
        $this->saveExtra("NPC_TripleTriad.txt", $TripleTriadOut);
        $this->saveExtra("NPC_Porter.txt", $PorterOut);
        $this->saveExtra("NPC_Map.txt", $MapOutput);
        $this->saveExtra("NPC_Shop.txt", $ShopOut);
        $this->saveExtra("NPC_Shop_Dialogue.txt", $ShopDialogueOut);
        $this->saveExtra("NPC_Warp.txt", $WarpPages);
        $this->saveExtra("NPC_Dialogue.txt", $DialoguePages);
        $this->saveExtra("NPC_Appearance.txt", $EquipmentOut);
        $this->saveExtra("NPC_Player_Data.txt", $FinalNpcPlayerData);
    }
}