<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * php bin/console app:parse:csv GE:NPCARRAYTEST
 */

class NPCARRAYTEST implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{Output}
    {Porter}
    {TripleTriad}
    {MapOutput}
    {ShopOutput}
    {ShopDialogue}
    {WarpPages}
    {DialoguePages}';

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
        $CustomTalkDynamicIconCsv = $this->csv('CustomTalkDynamicIcon');
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
        $CollectablesShopCsv = $this->csv('CollectablesShop');
        $CollectablesShopItemCsv = $this->csv('CollectablesShopItem');
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

        $this->io->text('Generating NPC Name Array ...');
        $this->io->progressStart($ENpcResidentCsv->total);
        $NpcNameArray = [];
        $NpcMainPage = [];
        $this->PatchCheck($Patch, "ENpcResident", $ENpcResidentCsv);
        $PatchNumber = $this->getPatch("ENpcResident");
        $NpcPatchArray = [];
        
        $PlaceNameLocation = ""; //TEMP

        foreach ($ENpcResidentCsv->data as $id => $NPCs) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();
            //main NPC constructor

            $Name = $NPCs['Singular'];
            if (empty($Name)) continue;
            $NpcNameArray[$Name][] = $id;
            if (empty($NpcPatchArray[$Name])) {
                $NpcPatchArray[$Name] = $PatchNumber[$id];
            }
        }
        $this->io->progressFinish();
        $NPCIds = [];
        foreach ($NpcNameArray as $key => $value) {
            $NPCIds[$key] = implode(",", $value);
        }
        //subpage arrays
        //$GetHowToArray = [];

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

        //gather lgb from level.exd
        $this->io->text('Generating Level.exd Positions ...');
        $this->io->progressStart($LevelCsv->total);
        foreach($LevelCsv->data as $id => $Level) {
            $this->io->progressAdvance();
            if ($Level['Type'] != 8) continue;
            $NPCID = $Level['Object'];
            $LGBArray[$NPCID] = array(
                'Territory' => $Level['Territory'],
                'x' => $Level['X'],
                'y' => $Level['Z'],
                'id' => $id
            );
        }
        $this->io->progressFinish();
        //gather lgb from LGB.json
        $this->io->text('Generating LGB.json Positions ...');
        $this->io->progressStart($TerritoryTypeCsv->total);
        foreach ($TerritoryTypeCsv->data as $id => $teri) {  
            $this->io->progressAdvance();
            $code = $teri['Name'];
            if (empty($code)) continue;
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
                $jdata = file_get_contents($url);
                $decodeJdata = json_decode($jdata);
                foreach ($decodeJdata as $lgb) {
                    $LayerID = $lgb->LayerId;
                    $Name = $lgb->Name;
                    $InstanceObjects = $lgb->InstanceObjects;
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
                            $LGBArray[$NPCID] = array(
                                'Territory' => $id,
                                'x' => $x,
                                'y' => $y,
                                'id' => $InstanceID
                            );
                        }
                    }
                }
            }
        }
        $this->io->progressFinish();
        //var_dump($LGBArray['1034078']['Territory']);






        $PorterArray = [];
        //data constructor
        $this->io->text('Generating NPC Data ...');
        $this->io->progressStart($ENpcResidentCsv->total);
        foreach ($ENpcResidentCsv->data as $id => $NPCs) {
            $this->io->progressAdvance();
            $Name = $NPCs['Singular'];//Array of names that should not be capitalized
            $NameFormatted = $this->NameFormat($id, $ENpcResidentCsv, $ENpcBaseCsv, $PlaceNameLocation);

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
                    case ($DataValue > 65535) && ($DataValue < 69999):
                        $QuestCheck[] = $QuestCsv->at($DataValue)["Name"].",";
                    break;
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
                        $WarpString = "{{-start-}}\n'''". $NameFormatted ."/Warp/$DataValue'''\n";
                        $WarpString .= "{{WarpTemplate\n";
                        $WarpString .= "| Option = $WarpOption\n";
                        $WarpString .= "| Confirm = $WarpConfirm\n";
                        $WarpString .= "| RequiredQuests = $RequiredQuests\n";
                        $WarpString .= "| RequiredLevel = $RequiredLevel\n";
                        $WarpString .= "| Cost = $WarpCost\n";
                        $WarpString .= "". $DefaultTalkAccept ."". $DefaultTalkFail ."". $DefaultTalkConfirm ."\n";
                        $WarpString .= "}}\n\n";
                        $WarpPagesArray[] = $WarpString;
                    break;
                    case ($DataValue > 262100) && ($DataValue < 269999): //GILSHOP
                        $ShopCheck[] = $DataValue.",";
                        $ShopCheck[] = "Dialogue,";
                        $FuncShop = $this->getShop($NameFormatted, "GilShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $DataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv);
                        $ShopOutputArray[] = $FuncShop["Shop"];
                        $ShopDialogueArray[] = $FuncShop["Dialogue"];
                        $TotalItems[$Name][] = $FuncShop["Number"];
                    break;
                    case ($DataValue > 393000) && ($DataValue < 399999): //GUILDLEVEASSIGNMENT
                        $DialogueCheck[] = $DataValue.",";
                        $GuildLeveTalkArray = [];
                        $GuildLeveTalkType = $GuildLeveAssignmentCsv->at($DataValue)['unknown_1'];
                        foreach(range(31,38) as $a) {
                            if ($GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_$a"] != 0){
                                $GuildLeveTalkString = "{{Dialoguebox3|Intro=$GuildLeveTalkType|Dialogue=\n";
                                $GuildLeveTalkString .= $GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_$a"];
                                $GuildLeveTalkString .= "}}";
                                $GuildLeveTalkArray[] = $GuildLeveTalkString;
                            }
                        }
                        $GuildLeveTalkImpoloded = implode("\n", $GuildLeveTalkArray);
                        $DialogueString = "{{-start-}}\n";
                        $DialogueString .= "'''$NameFormatted/Dialogue'''";
                        $DialogueString .= "$GuildLeveTalkImpoloded\n";
                        $DialogueString .= "{{-stop-}}\n";
                        $DialogueArray[] = $DialogueString;
                    break;
                    case ($DataValue > 589000) && ($DataValue < 599999)://DEFAULTTALK
                        $DialogueCheck[] = $DataValue.",";
                    break;
                    case ($DataValue > 720000) && ($DataValue < 729999): //CUSTOMTALK
                        $DialogueCheck[] = $DataValue.",";
                        foreach(range(0,29) as $a) {
                            if (empty($CustomTalkCsv->at($DataValue)["Script{Instruction}[$a]"])) continue;
                            $Instruction = $CustomTalkCsv->at($DataValue)["Script{Instruction}[$a]"];
                            $Argument = $CustomTalkCsv->at($DataValue)["Script{Arg}[$a]"];
                            switch (true) {
                                case (strpos($Instruction, 'HOWTO') !== false):
                                    $HowToCheck[] = $HowToCsv->at($Argument)['unknown_1'].",";
                                    $DataValue = $Argument;
                                    //$GetHowToArray[] = $this->GetHowTo($HowToCsv, $HowToCategoryCsv, $HowToPageCsv, $DataValue);
                                break;
                                case (strpos($Instruction, 'DISPOSAL') !== false):
                                    $ShopCheck[] = $Argument.",";
                                break;
                                case (($Argument > 65535) && ($Argument < 69999)):
                                    $QuestCheck[] = $QuestCsv->at($Argument)["Name"].",";
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
                                        $ShopCheck[] = $NestDataValue.",";
                                    break;
                                    case ($NestDataValue > 1769000) && ($NestDataValue < 1779999)://SPECIALSHOP
                                        $ShopCheck[] = $NestDataValue.",";
                                        $ShopCheck[] = "Dialogue,";
                                        $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $NestDataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv);
                                        $ShopOutputArray[] = $FuncShop["Shop"];
                                        $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                        $TotalItems[$Name][] = $FuncShop["Number"];
                                    break;
                                    case ($NestDataValue > 3407872) && ($NestDataValue < 3409999)://LotteryExchangeShop
                                        $ShopCheck[] = $NestDataValue.",";
                                    break;
                                    case ($NestDataValue > 3470000) && ($NestDataValue < 3479999)://disposal
                                        $ShopCheck[] = $NestDataValue.",";
                                    break;
                                    default:
                                    break;
                                }
                            }
                        }
                    break;
                    case ($DataValue > 910000) && ($DataValue < 919999): //CRAFT LEVE
                        $LeveCheck[] = $DataValue.",";
                    break;
                    case ($DataValue > 1179000) && ($DataValue < 1179999): //CHOCOBOTAXISTAND
                        $ChocoboTaxiCheck[] = $DataValue.",";
                        $FuncDataValue = $DataValue;
                        $GetPorterArray[] = $this->GetChocoboTaxi($ChocoboTaxiStandCsv, $ChocoboTaxiCsv, $FuncDataValue);
                    break;
                    case ($DataValue > 1440000) && ($DataValue < 1449999): //GCSHOP
                        $ShopCheck[] = $DataValue.",";
                    break;
                    case ($DataValue > 1507000) && ($DataValue < 1509999): //GUILDORDERGUIDE
                    break;
                    case ($DataValue > 1570000) && ($DataValue < 1579999): //GUILDORDEROFFICER
                    break;
                    case ($DataValue > 1769000) && ($DataValue < 1779999)://SPECIALSHOP
                        $ShopCheck[] = $DataValue.",";
                        $ShopCheck[] = "Dialogue,";
                        $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $DataValue, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv);
                        $ShopOutputArray[] = $FuncShop["Shop"];
                        $ShopDialogueArray[] = $FuncShop["Dialogue"];
                        $TotalItems[$Name][] = $FuncShop["Number"];
                    break;
                    case ($DataValue > 2030000) && ($DataValue < 2039999)://SWITCHTALK
                        $DialogueCheck[] = $DataValue.",";
                    break;
                    case ($DataValue > 2290000) && ($DataValue < 2299999)://TRIPLETRIAD
                        $TripleTriadCheck[] = $DataValue.",";
                        $GetTripleTriadArray[] = $this->GetTripleTriad($ItemCsv, $TripleTriadCardCsv, $TripleTriadCsv, $QuestCsv, $DataValue, $DefaultTalkCsv, $TripleTriadRuleCsv, $NameFormatted);
                    break;
                    case ($DataValue > 2752000) && ($DataValue < 2752999)://FCCSHOP
                        $ShopCheck[] = $DataValue.",";
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
                                    $ShopCheck[] = $ShopLink.",";
                                break;
                                case ($ShopLink >= 3538900 && $ShopLink < 3540000): //Prehandler
                                    $ShopID = $PreHandlerCsv->at($ShopLink)["Target"];
                                    switch (true) {
                                        case ($ShopID > 262100 && $ShopID < 269999): //gilshop
                                            $ShopCheck[] = $ShopID.",";
                                        break;
                                        case ($ShopID >= 1769000 && $ShopID < 1779999): //specialshop
                                            $ShopCheck[] = $ShopID.",";
                                        break;
                                        case ($ShopID >= 3866620 && $ShopID < 3866999): //COLLECTABLESHOPS
                                            $ShopCheck[] = $ShopID.",";
                                        break;
                                        case ($ShopID >= 3801000 && $ShopID < 3809999): //InclusionShop
                                            foreach(range(0,29) as $b) {
                                                if (empty($InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$b]"])['Name'])) continue;
                                                foreach(range(0,20) as $c) {
                                                    $SubDataValue = "". $ShopID .".". $c ."";
                                                    if (empty($InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'])) break;
                                                    $ShopCheck[] = $InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'].",";
                                                    $IncShopID = $InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'];
                                                    $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $IncShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv);
                                                    $ShopOutputArray[] = $FuncShop["Shop"];
                                                    $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                                    $TotalItems[$Name][] = $FuncShop["Number"];
                                                }
                                            }
                                        break;
                                        case ($ShopID >= 3604400 && $ShopID < 3609999): //Description
                                            $DialogueCheck[] = $ShopID.",";
                                        break;

                                        default:
                                        break;
                                    }
                                break;
                                case ($ShopLink >= 1769000 && $ShopLink < 1779999): //Specialshop
                                    $ShopCheck[] = $ShopLink;
                                    $ShopCheck[] = "Dialogue,";
                                    $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $ShopLink, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv);
                                    $ShopOutputArray[] = $FuncShop["Shop"];
                                    $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                    $TotalItems[$Name][] = $FuncShop["Number"];
                                break;
                                
                                default:
                                break;
                            }
                        }

                    break;
                    case ($DataValue > 3470000) && ($DataValue < 3479999): //DISPOSAL SHOP
                        $ShopCheck[] = $DataValue.",";
                    break;
                    case ($DataValue > 3530000) && ($DataValue < 3539999)://PREHANDLER
                        $ShopID = $PreHandlerCsv->at($DataValue)["Target"];
                        switch (true) {
                            case ($ShopID > 262100 && $ShopID < 269999): //gilshop
                                $ShopCheck[] = $ShopID.",";
                            break;
                            case ($ShopID >= 1769000 && $ShopID < 1779999): //specialshop
                                $ShopCheck[] = $ShopID.",";
                                $ShopCheck[] = "Dialogue,";
                                $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $ShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv);
                                $ShopOutputArray[] = $FuncShop["Shop"];
                                $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                $TotalItems[$Name][] = $FuncShop["Number"];
                            break;
                            case ($ShopID >= 3866620 && $ShopID < 3866999): //COLLECTABLESHOPS
                                $ShopCheck[] = $ShopID.",";
                            break;
                            case ($ShopID >= 3801000 && $ShopID < 3809999): //InclusionShop
                                foreach(range(0,29) as $b) {
                                    if (empty($InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$b]"])['Name'])) continue;
                                    foreach(range(0,20) as $c) {
                                        $SubDataValue = "". $ShopID .".". $c ."";
                                        if (empty($InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'])) break;
                                        $ShopCheck[] = $InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'].",";
                                        $InclusionShopSpecialShopID = $InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'];
                                        $ShopCheck[] = "Dialogue,";
                                        $FuncShop = $this->getShop($NameFormatted, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $InclusionShopSpecialShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv);
                                        $ShopOutputArray[] = $FuncShop["Shop"];
                                        $ShopDialogueArray[] = $FuncShop["Dialogue"];
                                        $TotalItems[$Name][] = $FuncShop["Number"];
                                    }
                                }
                            break;
                            case ($ShopID >= 3604400 && $ShopID < 3609999): //Description
                                $DialogueCheck[] = $ShopID.",";
                            break;

                            default:
                            break;
                        }
                    break;
                    case ($DataValue > 3604000) && ($DataValue < 3609999): //DESCRIPTION
                        $DialogueCheck[] = $DataValue.",";
                    break;
                    default:
                        $datarray[] = $ENpcBaseCsv->at($id)["ENpcData[$i]"];
                    break;
                }
            }
            $WarpCheckArrayOut = implode("", $WarpCheck);
            $WarpCheckArray[$Name][] = $WarpCheckArrayOut;
            $ShopCheckArrayOut = implode("", array_unique($ShopCheck));
            $ShopCheckArray[$Name][] = $ShopCheckArrayOut;
            $DialogueCheckArrayOut = implode("", $DialogueCheck);
            $DialogueCheckArray[$Name][] = $DialogueCheckArrayOut;
            $LeveCheckArrayOut = implode("", $LeveCheck);
            $LeveCheckArray[$Name][] = $LeveCheckArrayOut;
            $HowToCheckArrayOut = implode("", $HowToCheck);
            $HowToCheckArray[$Name][] = $HowToCheckArrayOut;
            $ChocoboTaxiCheckArrayOut = implode("", $ChocoboTaxiCheck);
            $ChocoboTaxiCheckArray[$Name][] = $ChocoboTaxiCheckArrayOut;
            $TripleTriadCheckArrayOut = implode("", $TripleTriadCheck);
            $TripleTriadCheckArray[$Name][] = $TripleTriadCheckArrayOut;
            $QuestCheckArrayOut = implode("", $QuestCheck);
            $QuestCheckArray[$Name][] = $QuestCheckArrayOut;
            $dataout = implode(",", $datarray);
            $dataarray[$Name][] = $dataout;
            
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
        $Questarrayimplode = [];
        foreach ($QuestCheckArray as $key => $value) {
            $Questarrayimplode[$key] = implode("", array_unique($value));
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

        //mainpage constructor
        $this->io->text('Building NPC Final Outputs ...');
        $this->io->progressStart($ENpcResidentCsv->total);
        foreach ($ENpcResidentCsv->data as $id => $NPCs) {
            $this->io->progressAdvance();
            $Name = $NPCs['Singular'];//Array of names that should not be capitalized
            $NameFormatted = $this->NameFormat($id, $ENpcResidentCsv, $ENpcBaseCsv, $PlaceNameLocation);
            if ((empty($NameFormatted)) || (preg_match("/[\x{30A0}-\x{30FF}\x{3040}-\x{309F}\x{4E00}-\x{9FBF}]+/u", $NameFormatted))) {
                continue;
            }
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

            
            //safety checks
            if (!empty($Questarrayimplode[$Name])) {
                $QuestCheckOut = substr($Questarrayimplode[$Name],0,-1);
            }
            if (empty($Questarrayimplode[$Name])) {
                $QuestCheckOut = "";
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
                $MapID = $TerritoryTypeCsv->at($LGBArray[$id]['id'])['Map'];
                if ($MapCsv->at($MapID)["PlaceName{Sub}"] > 0) {
                    $sub = " - ".$PlaceNameCsv->at($MapCsv->at($MapID)["PlaceName{Sub}"])['Name']."";
                } elseif ($MapCsv->at($MapID)["PlaceName"] > 0) {
                    $sub = "";
                }
                $code = substr($NpcMapCodeName, 0, 4);
                if ($code == "z3e2") {
                    $NpcPlaceName = "The Prima Vista Tiring Room";
                }
                $BasePlaceName = "$code - {$MapName}{$sub}";
    
                $LevelID = $LGBArray[$id]['id'];
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
                $MapOutputString .= "  | patch = REPLACEMEWITHPATCH\n";
                $MapOutputString .= "  | Sublocation = $SubLocation\n";
                $MapOutputString .= "}}\n";
                $MapOutputString .= "{{-stop-}}\n";
                $MapArray[] = $MapOutputString;
            }
            $ShopItemsTotalNo = null;
            if (!empty($ShopItemsNumber[$Name])) {
                $ShopItemsTotalNo = $ShopItemsNumber[$Name];
            } else {
                $ShopItemsTotalNo = "";
            }
            $dataout = implode("\n", $datarray);
            $LastMapLocExp = explode(",",$NPCIds[$Name]);
            $LastMapLoc = end($LastMapLocExp);
            $Patch = $NpcPatchArray[$Name];
            $Npcarray2[$Name][0] = "{{-start-}}
            {{Infobox NPC
            | Patch = $Patch
            | Name = $NameFormatted
            | Image = 
            $Race$Gender$Tribe
            | IDs = $NPCIds[$Name]
            | Quests = $QuestCheckOut
            | Shop = ".substr($Shoparrayimplode[$Name],0,-1)."
            | TotalItems = $ShopItemsTotalNo
            | Warp = ".substr($Warparrayimplode[$Name],0,-1)."
            | Dialogue = ".substr($Dialoguearrayimplode[$Name],0,-1)."
            | Leves = ".substr($Levearrayimplode[$Name],0,-1)."
            | Active Help = ".substr($HowToarrayimplode[$Name],0,-1)."
            | Porter = ".substr($ChocoboTaxiarrayimplode[$Name],0,-1)."
            | Triple Triad = ".substr($TripleTriadarrayimplode[$Name],0,-1)."
            | Last Position = $LastMapLoc
            }}
            {{-stop-}}";
        
        }
            
        //---------------------------------------------------------------------------------
        $finaloutputarray = [];
        foreach ($Npcarray2 as $key => $value) {
            $finaloutput[$key] = implode("\n", $value);
        }

        $Output = implode("\n", $finaloutput);

        $MapOutput = implode("\n", $MapArray);

        //$GetHowToOut = implode("\n", array_unique($GetHowToArray));
        
        $data = [
            '{Output}' => $Output,
            '{Porter}' => $PorterOut,
            '{TripleTriad}' => $TripleTriadOut,
            '{MapOutput}' => $MapOutput,
            '{ShopOutput}' => $ShopOut,
            '{ShopDialogue}' => $ShopDialogueOut,
            '{WarpPages}' => $WarpPages,
            '{DialoguePages}' => $DialoguePages,
        ];

        // format using Gamer Escape formatter and add to data array
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeCollectWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("NPCARRAYTEST.txt", 9999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}
