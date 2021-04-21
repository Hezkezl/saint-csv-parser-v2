<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:ARRM2
 */
class ARRM2 implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "";
    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');
        // grab CSV files we want to use
        $territoryTypeCsv = $this->csv("TerritoryType");
        $mapCsv = $this->csv("Map");
        $mapMarkerCsv = $this->csv("MapMarker");
        $placeNameCsv = $this->csv("PlaceName");
        $aetheryteCsv = $this->csv("Aetheryte");
        $EnpcResidentCsv = $this->csv("EnpcResident");
        $levelCsv = $this->csv("Level");
        $BNpcNameCsv = $this->csv("BNpcName");
        $gatheringpointcsv = $this->csv("GatheringPoint");
        $gatheringPointBaseCsv = $this->csv("GatheringPointBase");
        $gatheringtypecsv = $this->csv("GatheringType");
        $gatheringItemCsv = $this->csv("GatheringItem");
        $itemCsv = $this->csv("Item");
        $spearfishingItemCsv = $this->csv("SpearfishingItem");
        $eventItemCsv = $this->csv("EventItem");
        $gatheringPointTransientCsv = $this->csv("GatheringPointTransient");
        $GatheringRarePopTimeTableCsv = $this->csv("GatheringRarePopTimeTable");
        $ArrayEventHandlerCsv = $this->csv("ArrayEventHandler");
        $BNpcBaseCsv = $this->csv("BNpcBase");
        $QuestCsv = $this->csv("Quest");
        $WarpCsv = $this->csv("Warp");
        $GilShopCsv = $this->csv("GilShop");
        $EObjNameCsv = $this->csv("EObjName");
        $EObjCsv = $this->csv("EObj");
        $ExportedSGCsv = $this->csv("ExportedSG");
        $AdevntureCsv = $this->csv("Adventure");
        $EmoteCsv = $this->csv("Emote");
        $TreasureSpotCsv = $this->csv("TreasureSpot");
        $TreasureHuntRankCsv = $this->csv("TreasureHuntRank");
        $FateCsv = $this->csv("Fate");
        $fishingspotcsv = $this->csv("FishingSpot");
        $EnpcBaseCsv = $this->csv("EnpcBase");
        $TribeCsv = $this->csv("Tribe");
        $RaceCsv = $this->csv("Race");
        $BGMCsv = $this->csv("BGM");
        $WeatherRateCsv = $this->csv("WeatherRate");
        $WeatherCsv = $this->csv("Weather");
        $BGMSwitchCsv = $this->csv("BGMSwitch");
        $BGMSituationCsv = $this->csv("BGMSituation");
        $AchievementCsv = $this->csv("Achievement");
        $ActionCsv = $this->csv("Action");
        $VFXCsv = $this->csv("VFX");
        $CustomTalkCsv = $this->csv("CustomTalk");
        $LogMessageCsv = $this->csv("LogMessage");
        $gatheringPointBonusCsv = $this->csv("GatheringPointBonus");
        $GatheringConditionCsv = $this->csv("GatheringCondition");
        $GatheringPointBonusTypeCsv = $this->csv("GatheringPointBonusType");
        $DynamicEventCsv = $this->csv("DynamicEvent");
        $DynamicEventEnemyTypeCsv = $this->csv("DynamicEventEnemyType");
        $DynamicEventTypeCsv = $this->csv("DynamicEventType");
        $DynamicEventSingleBattleCsv = $this->csv("DynamicEventSingleBattle");

        //array for treasure spot ordering
        $TreasureSpot = [];

        foreach ($TreasureSpotCsv->data as $id => $TreasureSpotData) {
            $LevelID = $TreasureSpotData['Location'];
            $TreasureSpot[$LevelID] = $TreasureSpotData;
            // example = var_dump($TreasureSpot["4520640"]["id"]);
        }

        //array for Fate ordering
        $FateArray = [];

        foreach ($FateCsv->data as $id => $FateData) {
            $FateLocation = $FateData['Location'];
            $FateArray[$FateLocation] = $FateData;
            // example = var_dump($FateArray["4520640"]["id"]);
        }

        $DynamicFateArray = [];

        foreach ($DynamicEventCsv->data as $id => $DynamicFateData) {
            $DynamicFateLocation = $DynamicFateData['LGBEventObject'];
            $DynamicFateArray[$DynamicFateLocation] = $DynamicFateData;
            // example = var_dump($DynamicFateArray["4520640"]["id"]);
        }

        $lgbID = [];
        foreach ($QuestCsv->data as $id => $Questdata) {
            foreach (range(0, 49) as $i) {
                if (!empty($Questdata["Script{Instruction}[$i]"])) {
                    $argument = $Questdata["Script{Arg}[$i]"];
                        $lgbID[$argument] = $Questdata;
                }
            }
            //var_dump($lgbID["8156363"]["id"]);
        }

        $unknownArray = [];
        $AbalathiasSpineArray = [];
        $CoerthasArray = [];
        $DravaniaArray = [];
        $GyrAbaniaArray = [];
        $HingashiArray = [];
        $LaNosceaArray = [];
        $MorDhonaArray = [];
        $NorvrandtArray = [];
        $OthardArray = [];
        $ThanalanArray = [];
        $TheBlackShroudArray = [];
        $TheHighSeasArray = [];

        $this->io->progressStart($territoryTypeCsv->total);
        //create links to sheet
        foreach ($territoryTypeCsv->data as $id => $territoryType1) {
            $region = $placeNameCsv->at($territoryType1['PlaceName{Region}'])['Name'];
            $placename = str_replace("'", "\'", $placeNameCsv->at($territoryType1['PlaceName'])['Name']);
            $placenameSub = "";
            if ($mapCsv->at($territoryType1['Map'])['PlaceName{Sub}'] != 0) {
                    $placenameSub =  " - ". str_replace("'", "\'", $placeNameCsv->at($mapCsv->at($territoryType1['Map'])['PlaceName{Sub}'])['Name']) ."";
            }
            switch ($territoryType1['PlaceName{Region}']) {
                case 0:
                    $htmlarrayoutput = array_push($unknownArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 22:
                    $htmlarrayoutput = array_push($LaNosceaArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 23:
                    $htmlarrayoutput = array_push($TheBlackShroudArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 24:
                    $htmlarrayoutput = array_push($ThanalanArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 25:
                    $htmlarrayoutput = array_push($CoerthasArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 26:
                    $htmlarrayoutput = array_push($MorDhonaArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 148:
                    $htmlarrayoutput = array_push($unknownArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 497:
                    $htmlarrayoutput = array_push($AbalathiasSpineArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 498:
                    $htmlarrayoutput = array_push($DravaniaArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 2400:
                    $htmlarrayoutput = array_push($GyrAbaniaArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 2401:
                    $htmlarrayoutput = array_push($OthardArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 2402:
                    $htmlarrayoutput = array_push($HingashiArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 2405:
                    $htmlarrayoutput = array_push($unknownArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 2950:
                    $htmlarrayoutput = array_push($NorvrandtArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                case 3443:
                    $htmlarrayoutput = array_push($TheHighSeasArray, "                {label: '<a href=\\\"../". $id ."/". $id .".html\\\">". $placename ."". $placenameSub ."</a>'},");
                break;
                default:
                    # code...
                    break;
            }
        }
        $unknownArray = implode("\n",$unknownArray);
        $AbalathiasSpineArray = implode("\n",$AbalathiasSpineArray);
        $CoerthasArray = implode("\n",$CoerthasArray);
        $DravaniaArray = implode("\n",$DravaniaArray);
        $GyrAbaniaArray = implode("\n",$GyrAbaniaArray);
        $HingashiArray = implode("\n",$HingashiArray);
        $LaNosceaArray = implode("\n",$LaNosceaArray);
        $MorDhonaArray = implode("\n",$MorDhonaArray);
        $NorvrandtArray = implode("\n",$NorvrandtArray);
        $OthardArray = implode("\n",$OthardArray);
        $ThanalanArray = implode("\n",$ThanalanArray);
        $TheBlackShroudArray = implode("\n",$TheBlackShroudArray);
        $TheHighSeasArray = implode("\n",$TheHighSeasArray);


$mapswitchstring = "let mapswitch = [
  {
    label: 'Eorzea',
    collapsed: true,
    children: [
        {
            label: 'La Noscea',
            collapsed: true,
            children: [
". $LaNosceaArray ."
            ]
        },
        {
            label: 'Thanalan',
            collapsed: true,
            children: [
". $ThanalanArray ."
            ]
        },
        {
            label: 'The Black Shroud',
            collapsed: true,
            children: [
". $TheBlackShroudArray ."
            ]
        },
        {
            label: 'Coerthas',
            collapsed: true,
            children: [
". $CoerthasArray ."
            ]
        },
        {
            label: 'Mor Dhona',
            collapsed: true,
            children: [
". $MorDhonaArray ."
            ]
        },
        {
            label: 'Abalathia\'s Spine',
            collapsed: true,
            children: [
". $AbalathiasSpineArray ."
            ]
        },
        {
            label: 'Dravania',
            collapsed: true,
            children: [
". $DravaniaArray ."
            ]
        },
        {
            label: 'Gyr Abania',
            collapsed: true,
            children: [
". $GyrAbaniaArray ."
            ]
        },
        {
            label: 'The High Seas',
            collapsed: true,
            children: [
". $TheHighSeasArray ."
            ]
        }
    ]
  },
  {
    label: 'Far East',
    collapsed: true,
    children: [
        {
            label: 'Hingashi',
            collapsed: true,
            children: [
". $HingashiArray ."
            ]
        },
        {
            label: 'Othard',
            collapsed: true,
            children: [
". $OthardArray ."
            ]
        }
    ]
  },
  {
    label: 'Norvrandt',
    collapsed: true,
    children: [
". $NorvrandtArray ."
    ]
  },
  {
    label: '???',
    collapsed: true,
    children: [
". $unknownArray ."
    ]
  },
]
export { mapswitch };
;";
        //write htmllink file
        //if (!file_exists("output/arrmtest")) { mkdir("output/arrmtest", 0777, true); }
        //$htmllink = fopen("output/arrmtest/htmllist.mjs", 'w');
        //fwrite($htmllink, $mapswitchstring);
        //fclose($htmllink);

        foreach ($territoryTypeCsv->data as $id => $territoryType) {
        	if ($id != 641) continue;
        $teriID = $territoryType['id'];
        $this->io->progressAdvance();
        $teriName = $territoryType['Name'];
        //$mapLink = $territoryType['Map'];
        $mapLink = 365;
        $mapLinkToTeri = $mapCsv->at($mapLink)['TerritoryType'];
        $mapMarkerLink = $mapCsv->at($mapLink)['MapMarkerRange'];
        $bgPath = $territoryType['Bg'];
        $ZoneBGMRaw = $territoryType['BGM'];
        $ExclusiveTypeRaw = $territoryType['ExclusiveType'];
        
            
        $Titleplacename = $placeNameCsv->at($territoryType['PlaceName'])['Name'];
        $TitleplacenameSub = "";
        if ($mapCsv->at($territoryType['Map'])['PlaceName{Sub}'] != 0) {
                $TitleplacenameSub =  " - ". $placeNameCsv->at($mapCsv->at($territoryType['Map'])['PlaceName{Sub}'])['Name'] ."";
        }
        switch ($ExclusiveTypeRaw) {
            case 0:
            case 1:
            case 2:
                $ExclusiveType = "";
            break;
            case 3:
                $ExclusiveType = " (Event)";
            break;
        }
        switch (true) {
            case $ZoneBGMRaw < 1000:
                $ZoneBGM = "{label: '". $BGMCsv->at($territoryType['BGM'])['File']. "'},";
                $JSONZoneBGM = $BGMCsv->at($territoryType['BGM'])['File'];
            break;
            case $ZoneBGMRaw > 1000 && $ZoneBGMRaw < 50000:
                $daytimeBGM = $BGMCsv->at($BGMSituationCsv->at($territoryType['BGM'])['DaytimeID'])['File'];
                $nighttimeBGM = $BGMCsv->at($BGMSituationCsv->at($territoryType['BGM'])['NightID'])['File'];
                $battleBGM = $BGMCsv->at($BGMSituationCsv->at($territoryType['BGM'])['BattleID'])['File'];
                $daybreakBGM = "";
                $JSONdaybreakBGM = "";
                if (!empty($BGMCsv->at($BGMSituationCsv->at($territoryType['BGM'])['DaybreakID'])['File'])){
                    $daybreakBGM = "\n{label: 'Dawn</b> = ". $BGMCsv->at($BGMSituationCsv->at($territoryType['BGM'])['DaybreakID'])['File'] ."'},";
                    $JSONdaybreakBGM = $BGMCsv->at($BGMSituationCsv->at($territoryType['BGM'])['DaybreakID'])['File'];
                }
                $ZoneBGM = "{label: '<b>Day</b> = ". $daytimeBGM ."'},\n{label: '<b>Night</b> = ". $nighttimeBGM ."'},\n{label: '<b>Battle</b> = ". $battleBGM ."'},". $daybreakBGM ."";
                $JSONZoneBGM = array(
                    'Day' => $daytimeBGM,
                    'Night' => $nighttimeBGM,
                    'Battle' => $battleBGM,
                    'Dawn' => $JSONdaybreakBGM,
                );
            break;
            case $ZoneBGMRaw > 50000:
            $SwitchBGMString = [];
            $JSONdaybreakBGM = "";
            $JSONSwitchMusic = "";
                foreach(range(0,21) as $switchsub) {
                    $newbgmkey = "". $ZoneBGMRaw .".". $switchsub ."";
                    if (empty($BGMSwitchCsv->at($newbgmkey)['Quest'])) continue;
                    $SwitchMusic = "{label: '". $BGMCsv->at($BGMSwitchCsv->at($newbgmkey)['BGM'])['File']. "'},";
                    $JSONSwitchMusic = $BGMCsv->at($BGMSwitchCsv->at($newbgmkey)['BGM'])['File'];
                    if ($BGMSwitchCsv->at($newbgmkey)['Quest'] == 0) {
                        $SwitchBGMString[0] = "Initial Music = ". $SwitchMusic ."";
                    }
                    if ($BGMSwitchCsv->at($newbgmkey)['Quest'] !== 0) {
                        $BGMQuestRaw = $BGMSwitchCsv->at($newbgmkey)['Quest'];
                        $BGMquestName = addslashes(preg_replace('/[^\x00-\x7F]+/', '', $QuestCsv->at($BGMQuestRaw)['Name']));
                        //bgmsituation
                        if ($BGMSwitchCsv->at($newbgmkey)['BGM'] > 1000) {
                            $daytimeBGM = $BGMCsv->at($BGMSituationCsv->at($BGMSwitchCsv->at($newbgmkey)['BGM'])['DaytimeID'])['File'];
                            $nighttimeBGM = $BGMCsv->at($BGMSituationCsv->at($BGMSwitchCsv->at($newbgmkey)['BGM'])['NightID'])['File'];
                            $battleBGM = $BGMCsv->at($BGMSituationCsv->at($BGMSwitchCsv->at($newbgmkey)['BGM'])['BattleID'])['File'];
                            $daybreakBGM = "";
                            if (!empty($BGMCsv->at($BGMSituationCsv->at($territoryType['BGM'])['DaybreakID'])['File'])){
                                $daybreakBGM = "\n{label: 'Dawn</b> = ". $BGMCsv->at($BGMSituationCsv->at($BGMSwitchCsv->at($newbgmkey)['BGM'])['DaybreakID'])['File'] ."'},";
                                $JSONdaybreakBGM = $BGMCsv->at($BGMSituationCsv->at($BGMSwitchCsv->at($newbgmkey)['BGM'])['DaybreakID'])['File'];
                            }
                            $SwitchMusic = "{label: '<b>Day</b> = ". $daytimeBGM ."'},\n{label: '<b>Night</b> = ". $nighttimeBGM ."'},\n{label: '<b>Battle</b> = ". $battleBGM ."'},". $daybreakBGM ."";
                            $JSONSwitchMusic = array(
                                'Day' => $daytimeBGM,
                                'Night' => $nighttimeBGM,
                                'Battle' => $battleBGM,
                                'Dawn' => $JSONdaybreakBGM,
                            );
                        }
                        $SwitchBGMString[] = "{label: 'After Quest = <a href=\\\"https://ffxiv.gamerescape.com/wiki/". $BGMquestName ."\\\">". $BGMquestName ."</a>'},\n". $SwitchMusic ."\n{label: '<hr>'},\n";
                        $JSONZoneBGM = array(
                            'After Quest' => $BGMquestName,
                            'Music' => $SwitchMusic,
                        );
                    }
                }
                //{label: 'SizeFactor : ". $ZoneBGM ."'},
                $SwitchBGMOutput = implode($SwitchBGMString);
                $ZoneBGM = $SwitchBGMOutput;
            break;

            default:
                $ZoneBGM = "";
                $JSONZoneBGM = "";
            break;
        }
        $fixedTime = $territoryType['FixedTime'];
        $SizeFactorMap = $mapCsv->at($mapLink)['SizeFactor'];
        $OffsetXMap = $mapCsv->at($mapLink)['Offset{X}'];
        $OffsetYMap = $mapCsv->at($mapLink)['Offset{Y}'];
        $MapEvent = "{label: 'Event Map? : ". $mapCsv->at($mapLink)['IsEvent'] ."'},";
        $JSONMapEvent = $mapCsv->at($mapLink)['IsEvent'];
        $MountBool = $territoryType['Mount'];
        $StealthBool = $territoryType['Stealth'];
        $SearchBool = $territoryType['PCSearch'];
        $PVPZoneBool = $territoryType['IsPvpZone'];
        $mapCode = $mapCsv->at($mapLink)['Id'];
        //WeatherRate
        $WeatherRate = $territoryType['WeatherRate'];
        $WeatherArray = [];
        $JSONWeatherArray = [];
        $JSONZoneArrayEventHandlerData = [];
        foreach(range(0,7) as $w) {
            if (empty($WeatherRateCsv->at($WeatherRate)["Weather[$w]"])) continue;
            $WeatherType = $WeatherCsv->at($WeatherRateCsv->at($WeatherRate)["Weather[$w]"])['Name'];
            $WeatherIcon = sprintf("%06d", $WeatherCsv->at($WeatherRateCsv->at($WeatherRate)["Weather[$w]"])['Icon']);
            $WeatherArray[] = "{label: '<img src=../assets/icons/060000/". $WeatherIcon .".png width=18/>". $WeatherType. "'},\n";
            $JSONWeatherArray[] = $WeatherType;
        }
        $WeatherOutput = implode($WeatherArray);
        $placename = str_replace("'", "\'", $placeNameCsv->at($territoryType['PlaceName'])['Name']);
        $placenameSub = "";
        if ($mapCsv->at($territoryType['Map'])['PlaceName{Sub}'] != 0) {
            $placenameSub =  " - ". str_replace("'", "\'", $placeNameCsv->at($mapCsv->at($territoryType['Map'])['PlaceName{Sub}'])['Name']) ."";
        }
        $ZoneArrayEventHandlerOutput = [];
        $JSONZoneArrayEventHandlerOutput = [];
        foreach (range(0, 15) as $b) {
            $handlerData = $ArrayEventHandlerCsv->at($territoryType['ArrayEventHandler'])["Data[$b]"];
            $JSONhandlerData = $ArrayEventHandlerCsv->at($territoryType['ArrayEventHandler'])["Data[$b]"];
            if ($handlerData == 0) continue;
            if ($handlerData < 131000) {
                $questName = addslashes(preg_replace('/[^\x00-\x7F]+/', '', $QuestCsv->at($handlerData)['Name']));
                $ZoneArrayEventHandlerData = "Quest = <a href=\\\"https://ffxiv.gamerescape.com/wiki/". $questName ."\\\">". $questName ."</a><br>";
            }
            if ($handlerData > 131000 && $handlerData < 262000) {
                $ZoneArrayEventHandlerData = "Warp = ". $WarpCsv->at($handlerData)['Question'] ."<br>";
            }
            if ($handlerData > 262000 && $handlerData < 591000) {
                $ZoneArrayEventHandlerData = "Shop = ". $GilShopCsv->at($handlerData)['Name'] ."<br>";
            }
            if ($handlerData > 591000 && $handlerData < 721000) {
                $ZoneArrayEventHandlerData = "Default Talk = ". $handlerData ."<br>";
            }
            if ($handlerData > 721000 && $handlerData < 1245100) {
                $CustomTalkName = $CustomTalkCsv->at($handlerData)['Name'];
                $CustomTalkInstructionArray = [];
                $CustomTalkInstructionArray[0] = "<b>". $CustomTalkName ."</b><br>";
                foreach (range(0, 29) as $ct) {
                    $CustomTalkInstruction = $CustomTalkCsv->at($handlerData)["Script{Instruction}[$ct]"];
                    if (empty($CustomTalkInstruction)) continue;
                    switch (true){
                        case stristr($CustomTalkInstruction,'BGM'):
                            $CustomTalkArgument = "BGM = ". $BGMCsv->at($CustomTalkCsv->at($handlerData)["Script{Arg}[$ct]"])['File'] ."<br>";
                        break;
                        case stristr($CustomTalkInstruction,'ACTOR'):
                            $CustomTalkArgument = "NPC = ". $EnpcResidentCsv->at($CustomTalkCsv->at($handlerData)["Script{Arg}[$ct]"])['Singular'] ."<br>";
                        break;
                        case stristr($CustomTalkInstruction,'ACHIEVEMENT'):
                            $CustomTalkArgument = "Achievement = ". $AchievementCsv->at($CustomTalkCsv->at($handlerData)["Script{Arg}[$ct]"])['Name'] ."<br>";
                        break;
                        case stristr($CustomTalkInstruction,'LOG'):
                            $CustomTalkArgument = "Log Message = ". addslashes((preg_replace('/[^\x00-\x7F]+/', '', $LogMessageCsv->at($CustomTalkCsv->at($handlerData)["Script{Arg}[$ct]"])['Text']))) ."<br>";
                        break;
                        case stristr($CustomTalkInstruction,'QUEST'):
                            $questName = addslashes(preg_replace('/[^\x00-\x7F]+/', '', $QuestCsv->at($CustomTalkCsv->at($handlerData)["Script{Arg}[$ct]"])['Name']));
                            $CustomTalkArgument = "Quest = <a href=\\\"https://ffxiv.gamerescape.com/wiki/". $questName ."\\\">". $questName ."</a><br>";
                        break;
                        case stristr($CustomTalkInstruction,'Action'):
                            $CustomTalkArgument = "Action = ". $ActionCsv->at($CustomTalkCsv->at($handlerData)["Script{Arg}[$ct]"])['Name'] ."<br>";
                        break;
                        case stristr($CustomTalkInstruction,'VFX'):
                            $CustomTalkArgument = "VFX = ". $VFXCsv->at($CustomTalkCsv->at($handlerData)["Script{Arg}[$ct]"])['Location'] ."<br>";
                        break;

                        default:
                            $CustomTalkArgument = "". $CustomTalkInstruction ."<br>";
                        break;
                    }
                    $CustomTalkInstructionArray[] = addslashes($CustomTalkArgument);
                }
                $handlerData = implode($CustomTalkInstructionArray);
                $ZoneArrayEventHandlerData = "Custom Talk = ". $handlerData ."<br>";
            }
            if ($handlerData > 1245100 && $handlerData < 1703000) {
                $ZoneArrayEventHandlerData = "Opening = ". $handlerData ."<br>";
            }
            if ($handlerData > 1703000 && $handlerData < 1900500) {
                $ZoneArrayEventHandlerData = "Story = ". $handlerData ."<br>";
            }
            if ($handlerData > 1900500) {
                $ZoneArrayEventHandlerData = "Guide for Instance = ". $handlerData ."<br>";
            }
            $JSONZoneArrayEventHandlerData[] = $JSONhandlerData;
            $ZoneArrayEventHandlerOutput[] = "{label: \"". $ZoneArrayEventHandlerData ."\"},";
        }
        $ZoneArrayEventHandlerOutput = implode($ZoneArrayEventHandlerOutput);
        $output = [];
        $JSONMapMarker = [];
        foreach ($mapMarkerCsv->data as $key => $mapMarker) {
        	$keyExplode = explode(".", $key);
        	$keyID = $keyExplode[0];
        	$keySub = $keyExplode[1];
        	if ($keyID != $mapMarkerLink) continue;
        	$newKey = "". $keyID .".". $keySub ."";
        	$x = ($mapMarkerCsv->at($newKey)['X'] * 2);
        	$y = ($mapMarkerCsv->at($newKey)['Y'] * 2);
        	$icon = sprintf("%06d", $mapMarkerCsv->at($newKey)['Icon']);
            $iconMath = 1000 * floor($mapMarkerCsv->at($newKey)['Icon']/1000);
            $iconPath = sprintf("%06d", $iconMath);
            $subtextRaw = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), '<br>', $placeNameCsv->at($mapMarkerCsv->at($newKey)['PlaceName{Subtext}'])['Name']);
            switch ($mapMarkerCsv->at($newKey)['SubtextOrientation']) {
                case 1:
                    $subtextOrientation = "left";
                break;
                case 2:
                    $subtextOrientation = "right";
                break;
                case 3:
                    $subtextOrientation = "down";
                break;
                case 4:
                    $subtextOrientation = "top";
                break;

                default:
                    $subtextOrientation = "left";//if > 4 then world map
                break;
            }
            $markerDataKey = $mapMarkerCsv->at($newKey)['Data{Key}'];
            // .bindPopup(\"" . $mapMarkerCsv->at($newKey)['SubtextOrientation'] ."\")
            // .on('click', function(){window.location = ("../129/129.html")})
            switch ($mapMarkerCsv->at($newKey)['Data{Type}']) {
                case 1:
                    $markerPopup = ".on('click', function(){window.location = (\"../". $mapCsv->at($markerDataKey)['TerritoryType'] ."/". $mapCsv->at($markerDataKey)['TerritoryType'] .".html\")})";
                    $JSONmarkerPopup = "'TerritoryType' => ". $mapCsv->at($markerDataKey)['TerritoryType']. "";
                    $subtext = "<span class='w3-text-light-blue'>". $subtextRaw ."</span>";
                break;
                case 2:
                    $markerPopup = ".on('click', function(){window.location = (\"../". $mapCsv->at($markerDataKey)['TerritoryType'] ."/". $mapCsv->at($markerDataKey)['TerritoryType'] .".html\")})";
                    $JSONmarkerPopup = "'TerritoryType' => ". $mapCsv->at($markerDataKey)['TerritoryType']. "";
                    $subtext = "<span class='w3-text-light-blue'>". $subtextRaw ."</span>";
                break;
                case 3:
                    $markerPopup = ".bindPopup(\"<center><span class='sptitle'>Aetheryte</span></center>". str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), '<br>', $placeNameCsv->at($aetheryteCsv->at($mapMarkerCsv->at($newKey)['Data{Key}'])['AethernetName'])['Name']) ."\")";
                    $JSONmarkerPopup = array(
                        'AetheryteID' => $mapMarkerCsv->at($newKey)['Data{Key}'],
                        'AetheryteName' => str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), ' ', $placeNameCsv->at($aetheryteCsv->at($mapMarkerCsv->at($newKey)['Data{Key}'])['AethernetName'])['Name']),
                    );
                    $subtext = $subtextRaw;
                break;
                case 4://Aethernet Shards
                    $markerPopup = ".bindPopup(\"<center><span class='sptitle'>Aethernet Shard</span></center>". str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), '<br>', $placeNameCsv->at($mapMarkerCsv->at($newKey)['Data{Key}'])['Name']) ."\")";
                    $JSONmarkerPopup = array(
                        'AethernetID' => $mapMarkerCsv->at($newKey)['Data{Key}'],
                        'AethernetName' => str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), ' ', $placeNameCsv->at($aetheryteCsv->at($mapMarkerCsv->at($newKey)['Data{Key}'])['AethernetName'])['Name']),
                    );
                    $subtext = $subtextRaw;
                break;

                default:
                    $markerPopup = "";//if > 4 then world map
                    $subtext = $subtextRaw;
                    $JSONmarkerPopup = "";
                break;
            }

            $JSONMapMarker[] = array(
                'Key' => $newKey,
                'Icon' => $icon,
                'coords' => array(
                    $x / 2,
                    $y / 2,
                ),
                'SubText' => $subtextRaw,
                'SubtextOrientation' => $subtextOrientation,
                'Type' => $mapMarkerCsv->at($newKey)['Data{Type}'],
                'Data' => $JSONmarkerPopup,
            );
            //echo json_encode($JSONMapMarker, JSON_PRETTY_PRINT);
            $output[] = "\nvar markericon". $keyID ."". $keySub ." = L.icon({className: 'leaflet-div-icon2', iconUrl: '../assets/icons/". $iconPath ."/". $icon .".png',iconAnchor: [16, 16], });
var markerraw". $keyID ."". $keySub ." = L.marker(map.unproject([". $x .", ". $y ."], map.getMaxZoom()), {icon: markericon". $keyID ."". $keySub ."})". $markerPopup .".bindTooltip(\"<center>". $subtext ."</center>\", {direction: '". $subtextOrientation ."', permanent: true}).openPopup().addTo(mapmarker)";
        }
        //end of mapmarkers
        $output = implode($output);
        $JSONBGMInfo = array(
            'ID' => $ZoneBGMRaw, 
            'BGM' => $JSONZoneBGM,
        );
        //echo json_encode($JSONBGMInfo, JSON_PRETTY_PRINT);
        $JSONMapInfo = array(
            'ID' => $mapLink,
            'FixedTime' => $fixedTime,
            'SizeFactor' => $SizeFactorMap,
            'Offset X' => $OffsetXMap,
            'Offset Y' => $OffsetYMap,
            'EventMap' => $JSONMapEvent,
            'CanMount' => $MountBool,
            'CanStealth' => $StealthBool,
            'SearchPlayer' => $SearchBool,
            'Weather' => $JSONWeatherArray,
            'ArrayHandler' => $JSONZoneArrayEventHandlerData,
            'MapMarker' => $JSONMapMarker, 
        );
        //echo json_encode($JSONMapInfo, JSON_PRETTY_PRINT);

        

        //start of level/json data

        //json

        $code = substr($territoryType['Bg'], -4);
        $jsonOutput = [];
        //JSON Array Clearout
        $JSON_Vfx = [];
        $JSON_PositionMarker = [];
        $JSON_Gimmick = [];
        $JSON_Sound = [];
        $JSON_EventNPC = [];
        $JSON_Aetheryte = [];
        $JSON_EnvSpace = [];
        $JSON_PopRange = [];
        $JSON_exitrange = [];
        $JSON_MapRange = [];
        $JSON_EventObject = [];
        $JSON_EnvLocation = [];
        $JSON_Fate = [];
        $JSON_EventRange = [];
        $JSON_CollisionBox = [];
        $JSON_LineVfx = [];
        $JSON_ClientPath = [];
        $JSON_TargetMarker = [];
        $JSON_ChairMarker = [];
        $JSON_PrefetchRange = [];
        $JSON_Treasure = [];
        $JSON_BNPC = [];
        $JSON_Gathering = [];
        $FATELayerString = [];
        $FATELayerVar = [];
        $FATELayerStringVar = [];
        $FATELayerArrayVar = [];
        foreach(range(0,5) as $range) {
            if ($range == 0) {
                if (file_exists('cache/2020.10.06.0000.0000/lgb/'. $code .'_planlive.lgb.json')) {
                    $url = 'cache/2020.10.06.0000.0000/lgb/'. $code .'_planlive.lgb.json';
                }
            } elseif ($range == 1) {
                if (file_exists('cache/2020.10.06.0000.0000/lgb/'. $code .'_planevent.lgb.json')) {
                    $url = 'cache/2020.10.06.0000.0000/lgb/'. $code .'_planevent.lgb.json';
                }
            } elseif ($range == 2) {
                if (file_exists('cache/2020.10.06.0000.0000/lgb/'. $code .'_planmap.lgb.json')) {
                    $url = 'cache/2020.10.06.0000.0000/lgb/'. $code .'_planmap.lgb.json';
                }
            } elseif ($range == 3) {
                if (file_exists('cache/2020.10.06.0000.0000/lgb/'. $code .'_sound.lgb.json')) {
                    $url = 'cache/2020.10.06.0000.0000/lgb/'. $code .'_sound.lgb.json';
                }
            } elseif ($range == 4) {
                if (file_exists('cache/2020.10.06.0000.0000/lgb/'. $code .'_vfx.lgb.json')) {
                    $url = 'cache/2020.10.06.0000.0000/lgb/'. $code .'_vfx.lgb.json';
                }
            } elseif ($range == 5) {
                if (file_exists('cache/2020.10.06.0000.0000/lgb/'. $code .'_planner.lgb.json')) {
                    $url = 'cache/2020.10.06.0000.0000/lgb/'. $code .'_planner.lgb.json';
                }
            }
            if (empty($url)) continue;
        $jdata = file_get_contents($url);
        $decodeJdata = json_decode($jdata);
        foreach ($decodeJdata as $lgb) {
                $LayerID = $lgb->LayerID;
                $InstanceObjects = $lgb->InstanceObjects;
                $AssetType = "";
                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                switch ($scale) {
                    case 95:
                        $c2 = ($scale / 100.0) * 10;
                    break;
                    case 100:
                        $c2 = ($scale / 100.0) * 8;
                    break;
                    case 200:
                        $c2 = ($scale / 100.0) * 2;
                    break;
                    case 400:
                        $c2 = ($scale / 100.0) / 2;
                    break;
                    case 800:
                        $c2 = ($scale / 100.0) / 8;
                    break;
                }

                foreach($InstanceObjects as $Object) {
                    $AssetType = $Object->AssetType;
                    $InstanceID = "";
                    if (!empty($Object->InstanceID)) {
                        $InstanceID = $Object->InstanceID;
                    }
                    //notes:
                    //public enum eTriggerBoxShapeLayer
                    //{
                    //    TriggerBoxShapeBox = 0x1,
                    //    TriggerBoxShapeSphere = 0x2,
                    //    TriggerBoxShapeCylinder = 0x3,
                    //    TriggerBoxShapeBoard = 0x4,
                    //    TriggerBoxShapeMesh = 0x5,
                    //    TriggerBoxShapeBoardBothSides = 0x6,
                    //}
                    $Name = $lgb->strName;
                    $BaseId = "";
                    $x = "";
                    $y = "";
                    $NpcPixelX = "";
                    $NpcPixelY = "";
                    $NpcLocY = "";
                    $NpcLocX = "";
                    $AssetSort = "unknown";
                    $scalex = "32";
                    $scaley = "32";
                    $anchorx = "16";
                    $anchory = "16";
                    $ExtraInfo = "";
                    $polygonData = "";
                    $popupInfo = "";
                    $NewLineInfo = "";
                    $polygonCheck = false;
                    $RotatedMarkerBool = false;
                    switch ($AssetType) {
                        case 1:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "bg";
                            $lgbIcon = "060408";
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                        break;
                        case 3:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "light";
                            $lgbIcon = "060002";
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                        break;
                        case 4:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "Vfx";
                            $lgbIcon = "060914";
                            $AssetPath = $Object->Object->strAssetPath;
                            $colorRed = $Object->Object->Color->Red;
                            $colorGreen = $Object->Object->Color->Green;
                            $colorBlue = $Object->Object->Color->Blue;
                            $alpha = $Object->Object->Color->Alpha;
                            $color = sprintf("#%02x%02x%02x", $colorRed, $colorGreen, $colorBlue);
                            $popupInfo = "<center><span class='sptitle'>". $Name ."</span></center>". $AssetPath ."<br>Color : <div style= \\\"background: ". $color ."; display: inline-block; width: 20px;   height: 20px;\\\"></div> Alpha : ". $alpha ."";
                            
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                            $JSON_Vfx[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'AssetPath' => $AssetPath,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                ),
                                'Color' => array(
                                    'R' => $colorRed,
                                    'G' => $colorGreen,
                                    'B' => $colorBlue,
                                    'R' => $alpha,
                                )
                            );
                        break;
                        case 5:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "PositionMarker";
                            $lgbIcon = "060071";
                            $popupInfo = "<center><span class='sptitle'>". $Name ."</span></center><br>". $url ."<br>". $InstanceID ."";
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                            $JSON_PositionMarker[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                        break;
                        case 6:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "Gimmick";
                            $lgbIcon = "060071";
                            $doorCheck = $Object->Object->InitialDoorState;
                            $AssetPath = $Object->Object->strAssetPath;
                            $doorCheckStr = "";
                            $popupInfo = "<center><span class='sptitle'>". $doorCheckStr ."</span></center>". $Name ."<br>". $AssetPath ."";
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                            $JSON_Gimmick[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'AssetPath' => $AssetPath,
                                'DoorState' => $doorCheck,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                        break;
                        case 7:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "Sound";
                            $lgbIcon = "060979";
                            $lgbScale = $Object->Transform->Scale->y;
                            $c = $scale / 100.0;
                            $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                            $offsetValueX = ($x + $offsetx) * $c;
                            $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                            $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                            $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                            $offsetValueY = ($y + $offsety) * $c;
                            $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                            $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            //data
                            $AssetPath = $Object->Object->strAssetPath;
                            $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $lgbScale ."}).bindPopup(\"<center><span class='sptitle'>". $Name ."</span></center><br>Path: ". $AssetPath ."<br>". $InstanceID ."\").openPopup().addTo(". $AssetSort .")";
                            $polygonCheck = true;
                            //$popupInfo = "<center><span class='sptitle'>". $Name ."</span></center><br>". $AssetPath ."";
                            $JSON_Sound[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'AssetPath' => $AssetPath,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Scale' => $lgbScale,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    )
                                )
                            );
                        break;
                        case 8:
                            $BaseId = "". $Object->Object->ParentData->ParentData->BaseId ."";
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "EventNPC";
                            $lgbIcon = "060421";
                            $npcName = $EnpcResidentCsv->at($BaseId)['Singular'];
                            $GenderSwitch = $EnpcBaseCsv->at($BaseId)['Gender'];
                            switch ($GenderSwitch) {
                                case 0:
                                    $Gender = "Male";
                                break;
                                case 1:
                                    $Gender = "Female";
                                break;
                            }
                            $Tribe = $TribeCsv->at($EnpcBaseCsv->at($BaseId)['Tribe'])['Masculine'];
                            $Race = $RaceCsv->at($EnpcBaseCsv->at($BaseId)['Race'])['Masculine'];
                            $NPCQuests = "";
                            $coords = "";
                            $NPCDialogue = "";
                            $popupInfo = "<center><span class='sptitle'>". $npcName ."</span><br>". $Gender ."/". $Tribe ."/". $Race ."</center><br>ID: ". $BaseId ."". $coords ."". $NPCDialogue ."". $NPCQuests ."";
                            
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                            $JSON_EventNPC[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'EnpcBase' => $BaseId,
                                'EnpcName' => $npcName,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                            
                        break;
                        case 12:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "Aetheryte";
                            $lgbIcon = "060430";
                            $AssetPath = $Object->Object->ParentData->BaseId;
                            $AetheryteLink = $placeNameCsv->at($aetheryteCsv->at($AssetPath)['PlaceName'])['Name'];
                            if ($aetheryteCsv->at($AssetPath)['PlaceName'] == 0) {
                                $AetheryteLink = $placeNameCsv->at($aetheryteCsv->at($AssetPath)['AethernetName'])['Name'];
                            }
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                            $JSON_Aetheryte[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'BaseId' => $BaseId,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                            $popupInfo = "<center><span class='sptitle'>". $AetheryteLink ."</span><br>". $Name ."</center>";
                        break;
                        case 13:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "EnvSpace";
                            $EnvSpaceScale = $Object->Transform->Scale->y;
                            $c = $scale / 100.0;
                            $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                            $offsetValueX = ($x + $offsetx) * $c;
                            $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                            $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                            $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                            $offsetValueY = ($y + $offsety) * $c;
                            $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                            $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            //data
                            $EffectiveRange = $Object->Object->EffectiveRange;
                            $InterpolationTime = $Object->Object->InterpolationTime;
                            $Reverb = $Object->Object->Reverb;
                            $Filter = $Object->Object->Filter;
                            $strSoundAssetPath = $Object->Object->strSoundAssetPath;
                            $lgbIcon = "060711";
                            $JSON_EnvSpace[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'EffectiveRange' => $EffectiveRange,
                                'InterpolationTime' => $InterpolationTime,
                                'Reverb' => $Reverb,
                                'Filter' => $Filter,
                                'SoundAssetPath' => $strSoundAssetPath,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Scale' => $EnvSpaceScale,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                            $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $EnvSpaceScale ."}).bindPopup(\"<center><span class='sptitle'>". $Name ."</span></center><br>EffectiveRange: ". $EffectiveRange ."<br>InterpolationTime: ". $InterpolationTime ."<br>Reverb: ". $Reverb ."<br>Filter: ". $Filter ."<br>SoundAssetPath: ". $strSoundAssetPath ."<br>". $InstanceID ."\").openPopup().addTo(". $AssetSort .")";
                            $polygonCheck = true;
                        break;
                        case 40:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "PopRange";
                            $lgbIcon = "000000";
                            //polygon
                            $c = $scale / 100.0;
                            $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                            $offsetValueX = ($x + $offsetx) * $c;
                            $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                            $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                            $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                            $offsetValueY = ($y + $offsety) * $c;
                            $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                            $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            $scaleX = ($Object->Transform->Scale->x / $c2);
                            $relativePosCount = $Object->Object->_RelativePositions->PosCount;
                            $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'>". $Name ."</span></center><br>". $relativePosCount ." Sub Positions<br>\").openPopup().addTo(". $AssetSort .")";
                            $polygonCheck = true;
                            $JSON_PopRange[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'relativePosCount' => $relativePosCount,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Scale' => $scaleX,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                        break;
                        case 41:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "exitrange";
                            $lgbIcon = "060457";
                            //polygon
                            $c = $scale / 100.0;
                            $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                            $offsetValueX = ($x + $offsetx) * $c;
                            $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                            $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                            $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                            $offsetValueY = ($y + $offsety) * $c;
                            $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                            $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            $scaleX = ($Object->Transform->Scale->x / $c2);
                            $TerritoryTypeExitRangeRaw = str_replace("'", "\'", $placeNameCsv->at($territoryTypeCsv->at($Object->Object->TerritoryType)['PlaceName'])['Name']);
                            $TerritoryTypeExitRangeZone = $Object->Object->TerritoryType;
                            $DestInstanceID = $Object->Object->DestInstanceID;
                            $ReturnInstanceID = $Object->Object->ReturnInstanceID;
                            $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'>". $Name ."</span></center><br>". $TerritoryTypeExitRangeRaw ." (". $TerritoryTypeExitRangeZone .")<br>Destination ID = ". $DestInstanceID ."<br>Return ID = ". $ReturnInstanceID ."\").openPopup().addTo(". $AssetSort .")";
                            $polygonCheck = true;
                            $JSON_exitrange[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'TerritoryType' => $TerritoryTypeExitRangeZone,
                                'DestInstanceID' => $DestInstanceID,
                                'ReturnInstanceID' => $ReturnInstanceID,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Scale' => $scaleX,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                        break;
                        case 43:
                            if ($Object->Object->PlaceNameEnabled == 0) continue;
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "MapRange";
                            $lgbIcon = "000000";
                            //notes:
                            //public enum eTriggerBoxShapeLayer
                            //{
                            //    TriggerBoxShapeBox = 0x1,
                            //    TriggerBoxShapeSphere = 0x2,
                            //    TriggerBoxShapeCylinder = 0x3,
                            //    TriggerBoxShapeBoard = 0x4,
                            //    TriggerBoxShapeMesh = 0x5,
                            //    TriggerBoxShapeBoardBothSides = 0x6,
                            //}
                            //polygon
                            $c = $scale / 100.0;
                            $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                            $offsetValueX = ($x + $offsetx) * $c;
                            $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                            $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                            $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                            $offsetValueY = ($y + $offsety) * $c;
                            $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                            $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            //polygon
                            $scaleX = ($Object->Transform->Scale->x / $c2);
                            $TriggerBoxType = $Object->Object->ParentData->TriggerBoxShape;
                            $PlaceName = $placeNameCsv->at($Object->Object->PlaceNameBlock)['Name'];
                            $PlaceNameSpot = $placeNameCsv->at($Object->Object->PlaceNameSpot)['Name'];
                            //var_dump($TriggerBoxType);
                            switch ($TriggerBoxType) {
                                case 1://box
                                    
                                    $xscale = ($Object->Transform->Scale->x * 2);
                                    $yscale = ($Object->Transform->Scale->z * 2);
                                    $r = $Object->Transform->Rotation->y;
                                    $pos1x = $NpcPixelX - $xscale;
                                    $pos1y = $NpcPixelY - $yscale;
                                    $pos2x = $NpcPixelX + $xscale;
                                    $pos2y = $NpcPixelY - $yscale;
                                    $pos3x = $NpcPixelX + $xscale;
                                    $pos3y = $NpcPixelY + $yscale;
                                    $pos4x = $NpcPixelX - $xscale;
                                    $pos4y = $NpcPixelY + $yscale;
                                    $qx1 = $NpcPixelX + cos(-$r) * ($pos1x - $NpcPixelX) - sin(-$r) * ($pos1y - $NpcPixelY);
                                    $qy1 = $NpcPixelY + sin(-$r) * ($pos1x - $NpcPixelX) + cos(-$r) * ($pos1y - $NpcPixelY);
                                    $qx2 = $NpcPixelX + cos(-$r) * ($pos2x - $NpcPixelX) - sin(-$r) * ($pos2y - $NpcPixelY);
                                    $qy2 = $NpcPixelY + sin(-$r) * ($pos2x - $NpcPixelX) + cos(-$r) * ($pos2y - $NpcPixelY);
                                    $qx3 = $NpcPixelX + cos(-$r) * ($pos3x - $NpcPixelX) - sin(-$r) * ($pos3y - $NpcPixelY);
                                    $qy3 = $NpcPixelY + sin(-$r) * ($pos3x - $NpcPixelX) + cos(-$r) * ($pos3y - $NpcPixelY);
                                    $qx4 = $NpcPixelX + cos(-$r) * ($pos4x - $NpcPixelX) - sin(-$r) * ($pos4y - $NpcPixelY);
                                    $qy4 = $NpcPixelY + sin(-$r) * ($pos4x - $NpcPixelX) + cos(-$r) * ($pos4y - $NpcPixelY);
                                    $polylinebox = "var ". $AssetSort ."poly". $InstanceID ."Line = [map.unproject([". $qx1 .", ". $qy1 ."], map.getMaxZoom()),map.unproject([". $qx2 .", ". $qy2 ."], map.getMaxZoom()),map.unproject([". $qx3 .", ". $qy3 ."], map.getMaxZoom()),map.unproject([". $qx4 .", ". $qy4 ."], map.getMaxZoom()),];";

                                    $polygonData = "". $polylinebox ."\nvar ". $AssetSort ."poly". $InstanceID ." = new L.polygon(". $AssetSort ."poly". $InstanceID ."Line).bindPopup(\"<center><span class='sptitle'>". $PlaceName ."</span><br>". $PlaceNameSpot ."<br>". $InstanceID ."</center>\").openPopup().addTo(". $AssetSort .")";

                                    //$polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'>". $PlaceName ."</span><br>". $PlaceNameSpot ."<br>". $InstanceID ."</center>\").openPopup().addTo(". $AssetSort .")";
                                    $MapRangeSubArray = array(
                                        'Rotation' => $r,
                                        '1' => array(
                                            'X' => $qx1 / 2,
                                            'Y' => $qy1 / 2,
                                        ),
                                        '2' => array(
                                            'X' => $qx2 / 2,
                                            'Y' => $qy2 / 2,
                                        ), 
                                        '3' => array(
                                            'X' => $qx3 / 2,
                                            'Y' => $qy3 / 2,
                                        ), 
                                        '4' => array(
                                            'X' => $qx4 / 2,
                                            'Y' => $qy4 / 2,
                                        ),                                         
                                    );

                                break;
                                case 2:
                                    $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'>". $PlaceName ."</span><br>". $PlaceNameSpot ."<br>". $InstanceID ."</center>\").openPopup().addTo(". $AssetSort .")";
                                    $MapRangeSubArray = array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    );
                                break;
                                case 3:
                                    $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'>". $PlaceName ."</span><br>". $PlaceNameSpot ."<br>". $InstanceID ."</center>\").openPopup().addTo(". $AssetSort .")";$MapRangeSubArray = array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    );
                                break;
                                case 4:
                                    $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {color: \"#e100ff\", radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'>". $PlaceName ."</span><br>". $PlaceNameSpot ."<br>". $InstanceID ."</center>\").openPopup().addTo(". $AssetSort .")";$MapRangeSubArray = array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    );
                                break;
                                case 5:
                                    $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {color: \"#e100ff\", radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'>". $PlaceName ."</span><br>". $PlaceNameSpot ."<br>". $InstanceID ."</center>\").openPopup().addTo(". $AssetSort .")";$MapRangeSubArray = array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    );
                                break;
                                case 6:
                                    $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {color: \"#e100ff\", radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'>". $PlaceName ."</span><br>". $PlaceNameSpot ."<br>". $InstanceID ."</center>\").openPopup().addTo(". $AssetSort .")";$MapRangeSubArray = array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    );
                                break;
                            }
                            $polygonCheck = true;
                            $JSON_MapRange[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'TriggerBoxType' => $TriggerBoxType,
                                'PlaceName' => $Object->Object->PlaceNameBlock,
                                'PlaceNameSpot' => $Object->Object->PlaceNameSpot,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Scale' => $scaleX,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => $MapRangeSubArray,
                                )
                            );
                        break;
                        case 45:
                            $BaseId = "". $Object->Object->ParentData->BaseId ."";
                            // this also contains Sight Seeing Log
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "EventObject";
                            $lgbIcon = "060416";
                            $extradebuginfo = "";
                            $EobjName = ucwords($EObjNameCsv->at($BaseId)['Singular']);
                            if (strpos($Name, 'LVD_DE') !== false) {
                                if (empty($DynamicFateArray[$InstanceID]["id"])) continue;
                                $AssetSort = "fate";
                                $FateID = $DynamicFateArray[$InstanceID]["id"];
                                $lgbIcon = $DynamicEventTypeCsv->at($DynamicEventCsv->at($FateID)["EventType"])['Icon{Objective}[0]'];
                                $fateName = addslashes($DynamicEventCsv->at($FateID)['Name']);
                                $QuestDynamic = $QuestCsv->at($DynamicEventCsv->at($FateID)['Quest'])['Name'];
                                $EnemyType = $DynamicEventEnemyTypeCsv->at($DynamicEventCsv->at($FateID)['EnemyType'])['Name'];
                                $description = str_replace("'","",str_replace(array("\r", "\n", "\t", "\0", "\x0b"), '<br>', $DynamicEventCsv->at($FateID)['Description']));
                                //polygon
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                                $polygonData = "var fatemarker". $FateID ." = L.icon({className: 'leaflet-div-icon2', iconUrl: '../assets/icons/060000/0". $lgbIcon .".png', iconAnchor: [16,16], iconSize: [32,32], });\nvar fatemarkerpoly". $FateID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX .", color: \"#6d98c9\", dashArray: \"5 5\", fillOpacity: 0.5}).addTo(fate).addTo(FATE_". $FateID ."Layer)\nvar fate". $FateID ." = L.marker(map.unproject([". (round($NpcPixelX, 1)) .", ". (round($NpcPixelY, 1)) ."], map.getMaxZoom()), {icon: fatemarker". $FateID ."}).bindPopup(\"<center><span class='sptitle'>". $fateName ."</span></center><br>X: (". (round($NpcLocX, 1)) .") Y: (". (round($NpcLocY, 1)) .")<br>Enemy Type: ". $EnemyType ."<br>Quest: ". $QuestDynamic ."<br>". $description ."<br>DynamicFateID: <b>". $FateID ."</b>\").openPopup().addTo(fate).addTo(FATE_". $FateID ."Layer);\n";
                                
                                $FATELayerString[] = "{label: '<img src=../assets/icons/060000/0". $lgbIcon .".png width=18/>". $fateName ."', layer: FATE_". $FateID ."Layer},";
                                $FATELayerVar[] = "var FATE_". $FateID ."Layer = L.layerGroup();";
                                $polygonCheck = true;
                            }
                            $EobjDataRaw = $EObjCsv->at($BaseId)['Data'];
                            if ($EobjDataRaw == 0) {
                                $EobjData = $ExportedSGCsv->at($EObjCsv->at($BaseId)['SgbPath'])['SgbPath'];
                                $JSONEobjData = array(
                                    'ExportedSG' => $ExportedSGCsv->at($EObjCsv->at($BaseId)['SgbPath'])['SgbPath'],
                                );
                            }
                            if ($EobjDataRaw > 65000 && $EobjDataRaw < 131000) {
                                $questName = addslashes(preg_replace('/[^\x00-\x7F]+/', '', $QuestCsv->at($EobjDataRaw)['Name']));
                                $EobjData = "Used in Quest = <a href=\\\"https://ffxiv.gamerescape.com/wiki/". $questName ."\\\">". $questName ."</a><br>";
                                $JSONEobjData = array(
                                    'QuestID' => $EobjDataRaw,
                                    'QuestName' => $questName,
                                );
                            }
                            if ($EobjDataRaw > 131000 && $EobjDataRaw < 590000) {
                                $EobjData = "Warp = ". addslashes($WarpCsv->at($EobjDataRaw)['Question']) ."";
                                $JSONEobjData = array(
                                    'WarpID' => $EobjDataRaw,
                                    'WarpName' => $WarpCsv->at($EobjDataRaw)['Name'],
                                    'WarpQuestion' => $WarpCsv->at($EobjDataRaw)['Question'],
                                );
                            }
                            if ($EobjDataRaw > 590000 && $EobjDataRaw < 720000) {
                                $EobjData = "Default Talk = ". $EobjDataRaw ."<br>";
                                $JSONEobjData = array(
                                    'DefaultTalk' => $EobjDataRaw,
                                );
                            }
                            if ($EobjDataRaw > 721000 && $EobjDataRaw < 983000) {
                                $EobjData = "Custom Talk = ". $EobjDataRaw ."<br>";
                                $JSONEobjData = array(
                                    'CustomTalk' => $EobjDataRaw,
                                );
                            }
                            if ($EobjDataRaw > 983000 && $EobjDataRaw < 1048000) {
                                $EobjData = "VFX = ". $ExportedSGCsv->at($EObjCsv->at($BaseId)['SgbPath'])['SgbPath'] ."<br>";
                                $JSONEobjData = array(
                                    'VFX' => $ExportedSGCsv->at($EObjCsv->at($BaseId)['SgbPath'])['SgbPath'],
                                );
                            }
                            if ($EobjDataRaw > 1048000 && $EobjDataRaw < 1703000) {
                                $EobjData = "Data = ". $EobjDataRaw ."<br>";
                                $JSONEobjData = array(
                                    'Data' => $EobjDataRaw,
                                );
                            }
                            if ($EobjDataRaw > 1703000 && $EobjDataRaw < 2162700) {
                                $EobjData = "Sound Effect = ". $ExportedSGCsv->at($EObjCsv->at($BaseId)['SgbPath'])['SgbPath'] ."<br>";
                                $JSONEobjData = array(
                                    'SoundEffect' => $ExportedSGCsv->at($EObjCsv->at($BaseId)['SgbPath'])['SgbPath'],
                                );
                            }
                            //adventure
                            if ($EobjDataRaw > 2162700 && $EobjDataRaw < 2359200) {
                                $currentName = $AdevntureCsv->at($EObjCsv->at($BaseId)['Data'])['Name'];
                                $currentInfo = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), '<br>', $AdevntureCsv->at($EObjCsv->at($BaseId)['Data'])['Impression']);
                                $currentDescription = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), '<br>', $AdevntureCsv->at($EObjCsv->at($BaseId)['Data'])['Description']);
                                $minLevel = $AdevntureCsv->at($EObjCsv->at($BaseId)['Data'])['MinLevel'];
                                $emote = $EmoteCsv->at($AdevntureCsv->at($EObjCsv->at($BaseId)['Data'])['Emote'])['Name'];
                                $EobjData = "<center><span class='sptitle'>Vista<br>". $currentName ."</span></center>". $currentInfo ."<br>". $currentDescription ."<br>Min Level : ". $minLevel ."<br> Use Emote : ". $emote ."";
                                $AssetSort = "vista";
                                $lgbIcon = "060429";
                                $JSONEobjData = array(
                                    'VistaID' => $EobjDataRaw,
                                    'Name' => $currentName,
                                    'Emote' => $emote,
                                    'MinLevel' => $minLevel,
                                    'Impression' => $AdevntureCsv->at($EObjCsv->at($BaseId)['Data'])['Impression'],
                                    'Description' => $AdevntureCsv->at($EObjCsv->at($BaseId)['Data'])['Description'],
                                );
                            }
                            //arcade machine
                            if ($EobjDataRaw > 2359200 && $EobjDataRaw < 2818050) {
                                $EobjData = "Aracde Machine";
                                $AssetSort = "EventObject";
                                $lgbIcon = "060416";
                                $JSONEobjData = array(
                                    'Aracde Machine' => $EobjDataRaw,
                                );
                            }
                            $popupdebuginfo = "<br><i title=' Layer ID: ". $InstanceID ."\\n Layer Name: ". $Name ."\\n EobjDataRaw: ". $EobjDataRaw ."\\n Object: ". $BaseId ."\\n SgbPath: ". $ExportedSGCsv->at($EObjCsv->at($BaseId)['SgbPath'])['SgbPath'] ."\\n PopType: ". $EObjCsv->at($BaseId)['PopType'] ."\\n InvisibleType: ". $EObjCsv->at($BaseId)['Invisibility'] ."\\n EventHighAddition: ". $EObjCsv->at($BaseId)['EventHighAddition'] ."\\n Director Control?: ". $EObjCsv->at($BaseId)['DirectorControl'] ."\\n Targetable?: ". $EObjCsv->at($BaseId)['Target'] ."\\n Source: LGB". $extradebuginfo ."'><i>i</i></title>";
                            $popupInfo = "<center><span class='sptitle'>". $EobjName ."</span></center><br>". $EobjData ."". $popupdebuginfo ."";
                            
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                            $JSON_EventObject[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'EobjId' => $BaseId,
                                'EobjName' => $EobjName,
                                'EobjData' => $JSONEobjData,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                        break;
                        case 47:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "EnvLocation";
                            $lgbIcon = "000000";
                            $scaleX = ($Object->Transform->Scale->x / $c2);
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                            $JSON_EnvLocation[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Scale' => $scaleX,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                            $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'></span><br></center>\").openPopup().addTo(". $AssetSort .")";
                            //polygon
                            $polygonCheck = true;
                        break;
                        case 49:
                        // this contains FATES!
                                //var_dump("this is a fate");
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "EventRange";
                            $lgbIcon = "000000";
                            //polygon
                            $c = $scale / 100.0;
                            $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                            $offsetValueX = ($x + $offsetx) * $c;
                            $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                            $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                            $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                            $offsetValueY = ($y + $offsety) * $c;
                            $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                            $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            //polygon
                            $scaleX = ($Object->Transform->Scale->x / $c2);
                            $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'>".$InstanceID ."</span><br></center>\").openPopup().addTo(". $AssetSort .")";
                            if (strpos($Name, 'FATE') !== false) {
                                if (empty($FateArray[$InstanceID]["id"])) continue;
                                $AssetSort = "fate";
                                $FateID = $FateArray[$InstanceID]["id"];
                                $lgbIcon = $FateCsv->at($FateID)["Icon{Map}"];
                                $fateName = addslashes($FateCsv->at($FateID)['Name']);
                                $minlev = $FateCsv->at($FateID)['ClassJobLevel'];
                                $maxlev = $FateCsv->at($FateID)['ClassJobLevel{Max}'];
                                $objective = str_replace("''","\"",str_replace("'","",str_replace(array("\r", "\n", "\t", "\0", "\x0b"), '<br>', $FateCsv->at($FateID)['Objective'])));
                                $description = str_replace("'","",str_replace(array("\r", "\n", "\t", "\0", "\x0b"), '<br>', $FateCsv->at($FateID)['Description']));
                                //polygon
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                                $polygonData = "var fatemarker". $FateID ." = L.icon({className: 'leaflet-div-icon2', iconUrl: '../assets/icons/060000/0". $lgbIcon .".png', iconAnchor: [16,16], iconSize: [32,32], });\nvar fatemarkerpoly". $FateID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX .", color: \"#6d98c9\", dashArray: \"5 5\", fillOpacity: 0.5}).addTo(fate).addTo(FATE_". $FateID ."Layer)\nvar fate". $FateID ." = L.marker(map.unproject([". (round($NpcPixelX, 1)) .", ". (round($NpcPixelY, 1)) ."], map.getMaxZoom()), {icon: fatemarker". $FateID ."}).bindPopup(\"<center><span class='sptitle'>". $fateName ."</span></center><br>X: (". (round($NpcLocX, 1)) .") Y: (". (round($NpcLocY, 1)) .")<br>Level: ". $minlev ." - ". $maxlev ."<br>". $objective ."<br>". $description ."<br>FateID: <b>". $FateID ."</b>\").openPopup().addTo(fate).addTo(FATE_". $FateID ."Layer);\n";
                                
                                $FATELayerString[] = "{label: '<img src=../assets/icons/060000/0". $lgbIcon .".png width=18/>". $fateName ."', layer: FATE_". $FateID ."Layer},";
                                $FATELayerVar[] = "var FATE_". $FateID ."Layer = L.layerGroup();";
                                
                                $JSON_Fate[] = array(
                                    'LGB_ID' => $InstanceID,
                                    'AssetType' => $AssetType,
                                    'Name' => $Name,
                                    'FATEID' => $FateID,
                                    'Transform' => array(  
                                        'X' => $x,
                                        'Y' => $y,
                                        'Scale' => $scaleX,
                                        'Coords' => array(
                                            'X' => $NpcLocX,
                                            'Y' => $NpcLocY,
                                        ),
                                        'Pixel' => array(
                                            'X' => $NpcPixelX / 2,
                                            'Y' => $NpcPixelY / 2,
                                        ),
                                    )
                                );
                            }
                            //polygon
                            $polygonCheck = true;
                            $JSON_EventRange[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Scale' => $scaleX,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                        break;
                        case 57:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "CollisionBox";
                            $lgbIcon = "060626";
                            //polygon
                            $c = $scale / 100.0;
                            $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                            $offsetValueX = ($x + $offsetx) * $c;
                            $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                            $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                            $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                            $offsetValueY = ($y + $offsety) * $c;
                            $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                            $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            $xscale = ($Object->Transform->Scale->x * 2);
                            $yscale = ($Object->Transform->Scale->z * 2);
                            $r = $Object->Transform->Rotation->y;
                            $pos1x = $NpcPixelX - $xscale;
                            $pos1y = $NpcPixelY - $yscale;
                            $pos2x = $NpcPixelX + $xscale;
                            $pos2y = $NpcPixelY - $yscale;
                            $pos3x = $NpcPixelX + $xscale;
                            $pos3y = $NpcPixelY + $yscale;
                            $pos4x = $NpcPixelX - $xscale;
                            $pos4y = $NpcPixelY + $yscale;
                            $qx1 = $NpcPixelX + cos(-$r) * ($pos1x - $NpcPixelX) - sin(-$r) * ($pos1y - $NpcPixelY);
                            $qy1 = $NpcPixelY + sin(-$r) * ($pos1x - $NpcPixelX) + cos(-$r) * ($pos1y - $NpcPixelY);
                            $qx2 = $NpcPixelX + cos(-$r) * ($pos2x - $NpcPixelX) - sin(-$r) * ($pos2y - $NpcPixelY);
                            $qy2 = $NpcPixelY + sin(-$r) * ($pos2x - $NpcPixelX) + cos(-$r) * ($pos2y - $NpcPixelY);
                            $qx3 = $NpcPixelX + cos(-$r) * ($pos3x - $NpcPixelX) - sin(-$r) * ($pos3y - $NpcPixelY);
                            $qy3 = $NpcPixelY + sin(-$r) * ($pos3x - $NpcPixelX) + cos(-$r) * ($pos3y - $NpcPixelY);
                            $qx4 = $NpcPixelX + cos(-$r) * ($pos4x - $NpcPixelX) - sin(-$r) * ($pos4y - $NpcPixelY);
                            $qy4 = $NpcPixelY + sin(-$r) * ($pos4x - $NpcPixelX) + cos(-$r) * ($pos4y - $NpcPixelY);
                            $polylinebox = "var ". $AssetSort ."poly". $InstanceID ."Line = [map.unproject([". $qx1 .", ". $qy1 ."], map.getMaxZoom()),map.unproject([". $qx2 .", ". $qy2 ."], map.getMaxZoom()),map.unproject([". $qx3 .", ". $qy3 ."], map.getMaxZoom()),map.unproject([". $qx4 .", ". $qy4 ."], map.getMaxZoom()),];";
                            $polygonData = "". $polylinebox ."\nvar ". $AssetSort ."poly". $InstanceID ." = new L.polygon(". $AssetSort ."poly". $InstanceID ."Line, {color: \"#ab1313\", dashArray: \"10 10\"}).bindPopup(\"<center><span class='sptitle'>Collision Box</span><br>". $Name ."<br>". $InstanceID ."</center>\").openPopup().addTo(". $AssetSort .")";
                            $CollisionBoxSubArray = array(
                                'Rotation' => $r,
                                '1' => array(
                                    'X' => $qx1 / 2,
                                    'Y' => $qy1 / 2,
                                ),
                                '2' => array(
                                    'X' => $qx2 / 2,
                                    'Y' => $qy2 / 2,
                                ), 
                                '3' => array(
                                    'X' => $qx3 / 2,
                                    'Y' => $qy3 / 2,
                                ), 
                                '4' => array(
                                    'X' => $qx4 / 2,
                                    'Y' => $qy4 / 2,
                                ),                                         
                            );
                            $JSON_CollisionBox[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Scale' => $scaleX,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => $CollisionBoxSubArray,
                                )
                            );

                            $polygonCheck = true;

                            //$popupInfo = "<center><span class='sptitle'>Collision Box</span></center><br>". $Name ."<br>". $InstanceID ."";
                        break;
                        case 59:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "LineVfx";
                            $lgbIcon = "060457";
                            $MarkerRotation = rad2deg($Object->Transform->Rotation->y);
                            $RotatedMarkerBool = true;
                            $popupInfo = "<center><span class='sptitle'>". $Name ."</span></center><br>". $InstanceID ."";
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                            $JSON_LineVfx[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Rotation' => $Object->Transform->Rotation->y,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                        break;
                        case 65:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "ClientPath";
                            $lgbIcon = "060403";
                            $ControlPointsArray = $Object->Object->ParentData->ControlPointsArray;
                            $ControlPointsPathArray = [];
                            $ControlPointsPathString = "";
                            $c = $scale / 100.0;
                            $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                            $offsetValueX = ($x + $offsetx) * $c;
                            $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                            $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                            $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                            $offsetValueY = ($y + $offsety) * $c;
                            $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                            $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);

                            //$ControlPointsPathArray[0] = "map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()),";
                            //usort($ControlPointsArray, function($a, $b) { return $a->PointID <=> $b->PointID; });
                            $xsave = null;
                            $ysave = null;
                            $xsave = $x;
                            $ysave = $y;

                            $colorarray = array("#2c04bf","#86d539","#470f81","#bda8b0","#a13f22","#1673d8","#878e48","#b5b815","#c74ff0","#b762fe","#d627dc","#e83c34","#00caa7","#78c425","#e2ba42","#22edf7","#21bff7","#83525e","#626051","#2f37a9","#04c532","#771e41","#8850fe","#ff884b","#c883c4","#11e04f","#0807d3","#185636","#8c985e","#cf17a1","#95e6a9","#fb7613","#89cafa","#39fbfc","#8dae55","#12c6b1","#055d70","#ecbb74","#671b06","#1b0298","#57e219","#d4e077","#a7281d","#b9246b","#35ed38","#94aec8","#a698d0","#dc54e5","#a57b29","#ba7dd4","#95aff1","#e43476","#114fc2","#2adfda","#7a9a59","#f24a7e");
                            foreach($ControlPointsArray as $ControlPoints) {


                                //usort($ControlPoints, function($item1, $item2) { return $item1['PointID'] <=> $item2['PointID']; });

                                $PointX = $ControlPoints->Translation->x;
                                $PointY = $ControlPoints->Translation->z;
                                $PointXnew = $xsave + $PointX;
                                //var_dump("\nX :". $xsave ." + ". $PointX ." = ". $PointXnew ."");
                                $PointYnew = $ysave + $PointY;
                                //var_dump("\nY :". $ysave ." + ". $PointY ." = ". $PointYnew ."\n");
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($PointXnew + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $PointPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($PointYnew + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $PointPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                                $PointID = $ControlPoints->PointID;

                                //$xsave = $PointXnew;
                                //$ysave = $PointYnew;

                                $ControlPointsPathString = "map.unproject([". $PointPixelX .", ". $PointPixelY ."], map.getMaxZoom()),";
                                $ControlPointsPathArray[$PointID] = $ControlPointsPathString;
                                $JSONControlPointsPathString = array(
                                    'X' => $PointPixelX / 2,
                                    'Y' => $PointPixelY / 2,
                                );
                                $JSONControlPointsPathArray[$PointID] = $JSONControlPointsPathString;
                                //array_slice($ControlPointsPathArray, 0, 1);
                                //var_dump($ControlPointsPathArray);

                                //var pointList = [map.unproject([860, 1449], map.getMaxZoom()), map.unproject([860, 1249], map.getMaxZoom())];
                                //var firstpolyline = new L.Polyline(pointList, {color: 'green'}).addTo(ClientPath);
                            }
                            $randomColour = array_rand($colorarray);
                            $ControlPointsPathArray = implode($ControlPointsPathArray);
                            $popupInfo = "<center><span class='sptitle'>". $Name ."</span></center><br>Color : <div style= \\\"background: ". $colorarray[$randomColour] ."; display: inline-block; width: 20px;   height: 20px;\\\"></div><br>". $InstanceID ."";
                            $NewLineInfo = "\nvar ". $AssetSort ."". $InstanceID ."path = [". $ControlPointsPathArray."];\nvar ". $AssetSort ."". $InstanceID ."polyline = new L.Polyline(". $AssetSort ."". $InstanceID ."path, {color: '". $colorarray[$randomColour] ."'}).bindPopup(\"". $popupInfo ."\").openPopup().addTo(ClientPath);";
                            
                            $JSON_ClientPath[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => $JSONControlPointsPathArray,
                                )
                            );

                        break;
                        case 68:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "TargetMarker";
                            $lgbIcon = "060561";
                            $popupInfo = "<center><span class='sptitle'>". $Name ."</span></center>". $InstanceID ."";
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                            $JSON_TargetMarker[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                        break;
                        case 69:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "ChairMarker";
                            $lgbIcon = "060420";
                            $popupInfo = "<center><span class='sptitle'>". $Name ."</span></center>". $InstanceID ."";
                            if (!empty($x)) {
                                $scale = $mapCsv->at($mapLink)['SizeFactor'];
                                $c = $scale / 100.0;
                                $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                                $offsetValueX = ($x + $offsetx) * $c;
                                $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                                $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                                $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                                $offsetValueY = ($y + $offsety) * $c;
                                $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                                $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            }
                            if (empty($NpcPixelX)) {
                                $NpcPixelX = 0;
                                $NpcPixelY = 0;
                            }
                            $JSON_ChairMarker[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                        break;
                        case 71:
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $AssetSort = "PrefetchRange";
                            $lgbIcon = "";
                            //polygon
                            $c = $scale / 100.0;
                            $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                            $offsetValueX = ($x + $offsetx) * $c;
                            $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                            $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                            $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                            $offsetValueY = ($y + $offsety) * $c;
                            $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                            $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                            //polygon
                            $scaleX = ($Object->Transform->Scale->x / $c2);
                            $polygonCheck = true;
                            $polygonData = "\nvar ". $AssetSort ."poly". $InstanceID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX ."}).bindPopup(\"<center><span class='sptitle'>". $Name ."</span><br>". $InstanceID ."</center>\").openPopup().addTo(". $AssetSort .")";
                            
                            $JSON_PrefetchRange[] = array(
                                'LGB_ID' => $InstanceID,
                                'AssetType' => $AssetType,
                                'Name' => $Name,
                                'Transform' => array(  
                                    'X' => $x,
                                    'Y' => $y,
                                    'Scale' => $scaleX,
                                    'Coords' => array(
                                        'X' => $NpcLocX,
                                        'Y' => $NpcLocY,
                                    ),
                                    'Pixel' => array(
                                        'X' => $NpcPixelX / 2,
                                        'Y' => $NpcPixelY / 2,
                                    ),
                                )
                            );
                        break;

                        default:
                            $x = 0;
                            $y = 0;
                            $AssetSort = "unknown";
                            $lgbIcon = "060582";
                        break;
                    }
                    if (empty($NpcPixelX)) {
                        $NpcPixelX = 0;
                        $NpcPixelY = 0;
                    }
                    //needs work to make a polygon instead of marker?
                    $IconScale = "iconAnchor: [". $anchory .",". $anchorx ."], iconSize: [". $scaley .",". $scalex ."]";
                    $NpcName = $EnpcResidentCsv->at($BaseId)['Singular'];

                    //conv
                    if ($RotatedMarkerBool == true) {
                        $MarkerRotationInput = ", rotationAngle: ". $MarkerRotation .", rotationOrigin: 'center'";
                    } elseif ($RotatedMarkerBool == false) {
                        $MarkerRotationInput = "";
                    }
                    if ($polygonCheck == false) {
                        $jsonString = "\nvar ". $AssetSort ."icon". $InstanceID ." = L.icon({className: 'leaflet-div-icon2', iconUrl: '../assets/icons/060000/". $lgbIcon .".png',iconAnchor: [16, 16], });\nvar ". $AssetSort ."raw". $InstanceID ." = L.marker(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {icon: ". $AssetSort ."icon". $InstanceID ."". $MarkerRotationInput ."}).bindPopup(\"". $popupInfo ."\").openPopup().addTo(". $AssetSort .")". $NewLineInfo ."";
                    } elseif ($polygonCheck == true) {
                        $jsonString = "\n". $polygonData ."";
                    }
                    $jsonOutput[] = $jsonString;
                }
            }
        }
        sort($FATELayerString);
        $FateLayerArray = implode("\n", array_unique($FATELayerString));
        sort($FATELayerVar);
        $FATELayerArrayVar = implode("\n", array_unique($FATELayerVar));

        $jsonOutput = implode($jsonOutput);
        //level.exd loading
        $levelOutput = [];
        foreach ($levelCsv->data as $key => $levelData) {
            if ($levelData['Territory'] != $teriID) continue;
            $levelID = $levelData['id'];
            $AssetType = $levelData['Type'];
            $assetObject = $levelData['Object'];
            $EventId = $levelData['EventId'];
            $x = $levelData['X'];
            $y = $levelData['Z'];
            $scale = $mapCsv->at($mapLink)['SizeFactor'];
            $c = $scale / 100.0;
            $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
            $offsetValueX = ($x + $offsetx) * $c;
            $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
            $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
            $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
            $offsetValueY = ($y + $offsety) * $c;
            $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
            $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
            $polygonData = "";
            $scale = $mapCsv->at($mapLink)['SizeFactor'];
            $popupInfo = "LEVELDATA";
            $polygonCheck = false;
            switch ($scale) {
                case 100:
                    $c2 = ($scale / 100.0) * 2;
                break;
                case 200:
                case 400:
                    $c2 = ($scale / 100.0) * 8;
                break;

                default:
                    # code...
                    break;
            }
            switch ($AssetType) {
                case 1:
                    $AssetSort = "bg";
                    $lgbIcon = "060408";
                break;
                case 3:
                    $AssetSort = "light";
                    $lgbIcon = "060002";
                break;
                case 4:
                    $AssetSort = "Vfx";
                    $lgbIcon = "060914";
                break;
                case 5:
                    $AssetSort = "PositionMarker";
                    $lgbIcon = "060408";
                    if (!empty($lgbID[$levelID]["id"])) {
                        $PosMarkerQuestId = $lgbID[$levelID]["id"];
                    } else {
                        $PosMarkerQuestId = 65536;
                    }
                    $questName = addslashes(preg_replace('/[^\x00-\x7F]+/', '', $QuestCsv->at($PosMarkerQuestId)['Name']));
                    $QuestTextData = "Quest = <a href=\\\"https://ffxiv.gamerescape.com/wiki/". $questName ."\\\">". $questName ."</a><br>";
                    $popupInfo = "<center><span class='sptitle'>Position Marker</span></center><br>". $QuestTextData ."<br>". $levelID ."";
                    $JSON_PositionMarker[] = array(
                        'LGB_ID' => $levelID,
                        'AssetType' => $AssetType,
                        'Name' => "",
                        'AssetObject' => $assetObject,
                        'EventID' => $EventId,
                        'Transform' => array(  
                            'X' => $x,
                            'Y' => $y,
                            'Coords' => array(
                                'X' => $NpcLocX,
                                'Y' => $NpcLocY,
                            ),
                            'Pixel' => array(
                                'X' => $NpcPixelX / 2,
                                'Y' => $NpcPixelY / 2,
                            ),
                        )
                    );
                break;
                case 6:
                    $AssetSort = "Gimmick";
                    $lgbIcon = "060071";
                break;
                case 7:
                    $AssetSort = "Sound";
                    $lgbIcon = "060979";
                break;
                case 8:
                    $AssetSort = "EventNPC";
                    $lgbIcon = "060421";
                    $npcName = $EnpcResidentCsv->at($assetObject)['Singular'];
                    $GenderSwitch = $EnpcBaseCsv->at($assetObject)['Gender'];
                    switch ($GenderSwitch) {
                        case 0:
                            $Gender = "Male";
                        break;
                        case 1:
                            $Gender = "Female";
                        break;
                    }
                    $Tribe = $TribeCsv->at($EnpcBaseCsv->at($assetObject)['Tribe'])['Masculine'];
                    $Race = $RaceCsv->at($EnpcBaseCsv->at($assetObject)['Race'])['Masculine'];
                    $NPCQuests = "";
                    $coords = "";
                    $NPCDialogue = "";
                    $JSON_EventNPC[] = array(
                        'LGB_ID' => $levelID,
                        'AssetType' => $AssetType,
                        'Name' => "",
                        'AssetObject' => $assetObject,
                        'EventID' => $EventId,
                        'EnpcBase' => $assetObject,
                        'EnpcName' => $npcName,
                        'Transform' => array(  
                            'X' => $x,
                            'Y' => $y,
                            'Coords' => array(
                                'X' => $NpcLocX,
                                'Y' => $NpcLocY,
                            ),
                            'Pixel' => array(
                                'X' => $NpcPixelX / 2,
                                'Y' => $NpcPixelY / 2,
                            ),
                        )
                    );
                    //if (file_exists("https://garlandtools.org/db/doc/npc/en/2/". $assetObject .".json")) {
                    //    $npcurl = "https://garlandtools.org/db/doc/npc/en/2/". $assetObject .".json";
                    //    $npcjdata = file_get_contents($npcurl);
                    //    $npcdecodeJdata = json_decode($npcjdata);
                    //    $coords = "";
                    //    if (!empty($npcdecodeJdata->npc->coords)) {
                    //        $x = round($npcdecodeJdata->npc->coords[0], 1);
                    //        $y = round($npcdecodeJdata->npc->coords[1], 1);
                    //        $coords = "<br>". $x ."-". $y ."";
                    //    }
                    //    $NPCDialogue = "";
                    //    if (!empty($npcdecodeJdata->npc->talk)) {
                    //        $NPCDialogue = ("<br>". $npcdecodeJdata->npc->talk[0]);
                    //    }
                    //    $NPCQuests = "";
                    //    if (!empty($npcdecodeJdata->npc->quests)) {
                    //        $NPCQuests = "<br>". $QuestCsv->at($npcdecodeJdata->npc->quests[0])['Name'];
                    //    }
                    //}
                    $popupInfo = "<center><span class='sptitle'>". $npcName ."</span><br>". $Gender ."/". $Tribe ."/". $Race ."</center><br>ID: ". $assetObject ."". $coords ."". $NPCDialogue ."". $NPCQuests ."";
                break;
                case 9:
                    $AssetSort = "BattleNPC";
                    $lgbIcon = "060422";
                    $ArrayEventHandlerOutput = [];
                    foreach (range(0, 15) as $b) {
                    $handlerData = $ArrayEventHandlerCsv->at($BNpcBaseCsv->at($assetObject)['ArrayEventHandler'])["Data[$b]"];
                        if ($handlerData == 0) continue;
                        if ($handlerData < 131000) {
                            $questName = addslashes(preg_replace('/[^\x00-\x7F]+/', '', $QuestCsv->at($handlerData)['Name']));
                            $ArrayEventHandlerData = "Quest = <a href=\\\"https://ffxiv.gamerescape.com/wiki/". $questName ."\\\">". $questName ."</a><br>";
                        }
                        if ($handlerData > 131000 && $handlerData < 262000) {
                            $ArrayEventHandlerData = "Warp = ". addslashes($WarpCsv->at($handlerData)['Question']) ."<br>";
                        }
                        if ($handlerData > 262000 && $handlerData < 591000) {
                            $ArrayEventHandlerData = "Shop = ". $GilShopCsv->at($handlerData)['Name'] ."<br>";
                        }
                        if ($handlerData > 591000 && $handlerData < 721000) {
                            $ArrayEventHandlerData = "Default Talk = ". $handlerData ."<br>";
                        }
                        if ($handlerData > 721000 && $handlerData < 1245100) {
                            $ArrayEventHandlerData = "Custom Talk = ". $handlerData ."<br>";
                        }
                        if ($handlerData > 1245100 && $handlerData < 1703000) {
                            $ArrayEventHandlerData = "Opening = ". $handlerData ."<br>";
                        }
                        if ($handlerData > 1703000 && $handlerData < 1900500) {
                            $ArrayEventHandlerData = "Story = ". $handlerData ."<br>";
                        }
                        if ($handlerData > 1900500) {
                            $ArrayEventHandlerData = "Guide for Instance = ". $handlerData ."<br>";
                        }
                        $ArrayEventHandlerOutput[] = $ArrayEventHandlerData;
                    }
                    $ArrayEventHandlerOutput = implode($ArrayEventHandlerOutput);
                    $popupInfo = "<center><span class='sptitle'>BATTLE NPC</span></center><br>Involved in:<br> ". $ArrayEventHandlerOutput ."";
                    
                break;
                case 12:
                    $AssetSort = "Aetheryte";
                    $lgbIcon = "060430";
                break;
                case 13:
                    $AssetSort = "EnvSpace";
                    $lgbIcon = "060711";
                break;
                case 40:
                    $AssetSort = "PopRange";
                    $lgbIcon = "000000";
                    //polygon
                    $c = $scale / 100.0;
                    $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                    $offsetValueX = ($x + $offsetx) * $c;
                    $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                    $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                    $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                    $offsetValueY = ($y + $offsety) * $c;
                    $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                    $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                    $c2 = ($scale / 100.0) * 2;
                    $scaleX = ($levelData['Radius'] / $c2);
                    $poprangeinfo = "<center><span class='sptitle'>PopRange</span><br>". $levelID ."</center>";
                    if (!empty($TreasureSpot[$levelID]["id"])) {
                        $AssetSort = "Treasure";
                        $floorID = floor($TreasureSpot[$levelID]["id"]);
                        $lgbIcon = "0". $TreasureHuntRankCsv->at($floorID)['Icon'] ."";
                        $treasureName = $itemCsv->at($TreasureHuntRankCsv->at($floorID)['ItemName'])['Name'];
                        $popupInfo =  "<center><span class='sptitle'>". $treasureName ."</span><br></center>(X:". round($NpcLocX, 1) ." Y:". round($NpcLocY, 1) .")";
                        $poprangeinfo = "<center><span class='sptitle'>". $treasureName ."</span><br></center>";
                        $JSON_Treasure[] = array(
                            'LGB_ID' => $levelID,
                            'AssetType' => $AssetType,
                            'Name' => $treasureName,
                            'AssetObject' => $assetObject,
                            'EventID' => $EventId,
                            'TreasureID' => $floorID,
                            'Transform' => array(  
                                'X' => $x,
                                'Y' => $y,
                                'Scale' => $scaleX,
                                'Coords' => array(
                                    'X' => $NpcLocX,
                                    'Y' => $NpcLocY,
                                ),
                                'Pixel' => array(
                                    'X' => $NpcPixelX / 2,
                                    'Y' => $NpcPixelY / 2,
                                ),
                            )
                        );
                    }
                    $polygonCheck = false; //set for the different icon types
                    $polygonData = "\nvar ". $AssetSort ."poly". $levelID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX ."}).bindPopup(\"". $poprangeinfo ."\").openPopup().addTo(". $AssetSort .")";
                    
                    $JSON_PopRange[] = array(
                        'LGB_ID' => $levelID,
                        'AssetType' => $AssetType,
                        'Name' => "",
                        'AssetObject' => $assetObject,
                        'EventID' => $EventId,
                        'Transform' => array(  
                            'X' => $x,
                            'Y' => $y,
                            'Scale' => $scaleX,
                            'Coords' => array(
                                'X' => $NpcLocX,
                                'Y' => $NpcLocY,
                            ),
                            'Pixel' => array(
                                'X' => $NpcPixelX / 2,
                                'Y' => $NpcPixelY / 2,
                            ),
                        )
                    );
                break;
                case 41:
                    $AssetSort = "exitrange";
                    $lgbIcon = "060457";
                    //polygon
                break;
                case 43:
                    $AssetSort = "MapRange";
                    $lgbIcon = "060408";
                    //polygon
                break;
                case 45:
                    $AssetSort = "EventObject";
                    $lgbIcon = "060416";
                    $EobjData = "";
                    $EobjName = ucwords($EObjNameCsv->at($assetObject)['Singular']);
                    $EobjDataRaw = $EObjCsv->at($assetObject)['Data'];
                    $extradebuginfo = "";
                    if ($EobjDataRaw == 0) {
                        $EobjData = $ExportedSGCsv->at($EObjCsv->at($assetObject)['SgbPath'])['SgbPath'];
                    }
                    if ($EobjDataRaw > 65000 && $EobjDataRaw < 131000) {
                        $questName = addslashes(preg_replace('/[^\x00-\x7F]+/', '', $QuestCsv->at($EobjDataRaw)['Name']));
                        $EobjData = "Used in Quest = <a href=\\\"https://ffxiv.gamerescape.com/wiki/". $questName ."\\\">". $questName ."</a><br>";
                    }
                    if ($EobjDataRaw > 131000 && $EobjDataRaw < 590000) {
                        $EobjData = "Warp = ". addslashes($WarpCsv->at($EobjDataRaw)['Question']) ."";
                    }
                    if ($EobjDataRaw > 590000 && $EobjDataRaw < 720000) {
                        $EobjData = "Default Talk = ". $EobjDataRaw ."<br>";
                    }
                    if ($EobjDataRaw > 721000 && $EobjDataRaw < 983000) {
                        $EobjData = "Custom Talk = ". $EobjDataRaw ."<br>";
                    }
                    if ($EobjDataRaw > 983000 && $EobjDataRaw < 1048000) {
                        $EobjData = "VFX = ". $ExportedSGCsv->at($EObjCsv->at($assetObject)['SgbPath'])['SgbPath'] ."<br>";
                    }
                    if ($EobjDataRaw > 1048000 && $EobjDataRaw < 1703000) {
                        $EobjData = "Data = ". $EobjDataRaw ."<br>";
                    }
                    if ($EobjDataRaw > 1703000 && $EobjDataRaw < 2162700) {
                        $EobjData = "Sound Effect = ". $ExportedSGCsv->at($EObjCsv->at($assetObject)['SgbPath'])['SgbPath'] ."<br>";
                    }
                    //adventure
                    if ($EobjDataRaw > 2162700 && $EobjDataRaw < 2359200) {
                        $currentName = $AdevntureCsv->at($EObjCsv->at($assetObject)['Data'])['Name'];
                        $currentInfo = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), '<br>', $AdevntureCsv->at($EObjCsv->at($assetObject)['Data'])['Impression']);
                        $currentDescription = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), '<br>', $AdevntureCsv->at($EObjCsv->at($assetObject)['Data'])['Description']);
                        $minLevel = $AdevntureCsv->at($EObjCsv->at($assetObject)['Data'])['MinLevel'];
                        $emote = $EmoteCsv->at($AdevntureCsv->at($EObjCsv->at($assetObject)['Data'])['Emote'])['Name'];
                        $EobjData = "<center><span class='sptitle'>Vista<br>". $currentName ."</span></center>". $currentInfo ."<br>". $currentDescription ."<br>Min Level : ". $minLevel ."<br> Use Emote : ". $emote ."";
                        $AssetSort = "vista";
                        $lgbIcon = "060429";
                    }
                    //arcade machine
                    if ($EobjDataRaw > 2359200 && $EobjDataRaw < 2818050) {
                        $EobjData = "Aracde Machine";
                        $AssetSort = "EventObject";
                        $lgbIcon = "060416";
                    }
                    //Aether Current
                    if ($EobjDataRaw > 2818050 && $EobjDataRaw < 2949120) {
                        $CurrentQuest = $AdevntureCsv->at($EobjDataRaw)['Quest'];
                        $AssetSort = "current";
                        $EobjData = "X: (". (round($NpcLocX, 1)) .") Y: (". (round($NpcLocY, 1)) .")";
                        $lgbIcon = "060653";
                    }
                    $popupdebuginfo = "<br><i title=' Layer ID: ". $levelID ."\\n EobjDataRaw: ". $EobjDataRaw ."\\n Object: ". $assetObject ."\\n SgbPath: ". $ExportedSGCsv->at($EObjCsv->at($assetObject)['SgbPath'])['SgbPath'] ."\\n PopType: ". $EObjCsv->at($assetObject)['PopType'] ."\\n InvisibleType: ". $EObjCsv->at($assetObject)['Invisibility'] ."\\n EventHighAddition: ". $EObjCsv->at($assetObject)['EventHighAddition'] ."\\n Director Control?: ". $EObjCsv->at($assetObject)['DirectorControl'] ."\\n Targetable?: ". $EObjCsv->at($assetObject)['Target'] ."\\n Source: EXD". $extradebuginfo ."'><i>i</i></title>";
                    $popupInfo = "<center><span class='sptitle'>". $EobjName ."</span></center><br>". $EobjData ."". $popupdebuginfo ."";
                break;
                case 47:
                    $AssetSort = "EnvLocation";
                    $lgbIcon = "060423";
                    //polygon
                break;
                case 49:
                    $AssetSort = "EventRange";
                    $lgbIcon = "";
                    $eventRangeInfoOutput = "".$EventId ."";
                    $eventRangeTitleOutput = "???";
                    $c = $scale / 100.0;
                    $offsetx = $mapCsv->at($mapLink)['Offset{X}'];
                    $offsetValueX = ($x + $offsetx) * $c;
                    $NpcLocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                    $NpcPixelX = (($NpcLocX - 1) * 50 * $c *2);
                    $offsety = $mapCsv->at($mapLink)['Offset{Y}'];
                    $offsetValueY = ($y + $offsety) * $c;
                    $NpcLocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);
                    $NpcPixelY = (($NpcLocY - 1) * 50 * $c * 2);
                    $c2 = ($scale / 100.0) * 2;
                    $scaleX = ($levelData['Radius'] / $c2);
                    $polygonCheck = true;
                    if (!empty($QuestCsv->at($EventId)['Name'])){
                        $eventRangeTitleOutput = "Quest Event Range:";
                        $questName = str_replace('"', '', preg_replace('/[^\x00-\x7F]+/', '', $QuestCsv->at($EventId)['Name']));
                        $eventRangeInfoOutput = "Quest = <a href=\\\"https://ffxiv.gamerescape.com/wiki/". $questName ."\\\">". $questName ."</a><br>";
                    }
                    $eventrangeinfo = "<center><span class='sptitle'>". $eventRangeTitleOutput ."</span><br></center>". $eventRangeInfoOutput ."";
                    $polygonData = "\nvar ". $AssetSort ."poly". $levelID ." = new L.circle(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {radius: ". $scaleX ."}).bindPopup(\"". $eventrangeinfo ."\").openPopup().addTo(". $AssetSort .")";
                    //polygon
                break;
                case 51:
                    $AssetSort = "questmarker";
                    $lgbIcon = "061731";
                    $questName = "Unknown";
                    //TODO - this level id actually links to ToDoMainLocation
                     //   var_dump($lgbID[$levelID]["id"]);
                    if (!empty($lgbID[$levelID]["id"])) {
                        $PosMarkerQuestId = $lgbID[$levelID]["id"];
                        $questName = addslashes(preg_replace('/[^\x00-\x7F]+/', '', $QuestCsv->at($PosMarkerQuestId)['Name']));
                    }
                    $QuestTextData = "Quest = <a href=\\\"https://ffxiv.gamerescape.com/wiki/". $questName ."\\\">". $questName ."</a><br>";
                    $popupInfo = "<center><span class='sptitle'>Quest Marker</span></center><br>". $QuestTextData ."<br>". $levelID ."";
                    //polygon
                break;
                case 57:
                    $AssetSort = "CollisionBox";
                    $lgbIcon = "060626";
                break;
                case 59:
                    $AssetSort = "LineVfx";
                    $lgbIcon = "060457";
                break;
                case 65:
                    $AssetSort = "ClientPath";
                    $lgbIcon = "060403";
                break;
                case 68:
                    $AssetSort = "TargetMarker";
                    $lgbIcon = "060561";
                break;
                case 69:
                    $AssetSort = "ChairMarker";
                    $lgbIcon = "060420";
                break;
                case 71:
                    $AssetSort = "PrefetchRange";
                    $lgbIcon = "060496";
                break;

                default:
                    $AssetSort = "unknown";
                    $lgbIcon = "060582";
                break;
            }
            if ($polygonCheck == false) {
            $levelString = "\nvar ". $AssetSort ."icon". $levelID ." = L.icon({className: 'leaflet-div-icon2', iconUrl: '../assets/icons/060000/". $lgbIcon .".png',iconAnchor: [16, 16], });
var ". $AssetSort ."raw". $levelID ." = L.marker(map.unproject([". $NpcPixelX .", ". $NpcPixelY ."], map.getMaxZoom()), {icon: ". $AssetSort ."icon". $levelID ."}).bindPopup(\"". $popupInfo ."\").openPopup().addTo(". $AssetSort .")". $polygonData ."";
            } elseif ($polygonCheck == true) {
                $levelString = "\n". $polygonData ."";
            }
                    $levelOutput[] = $levelString;
        }
        $levelOutput = implode($levelOutput);
        //var_dump($levelOutput);


        //Mappy Data
        /*
        $mappyURL = "https://xivapi.com/mappy/map/". $mapLink ."";
        $mappyjdata = file_get_contents($mappyURL);
        $mappydecodeJdata = json_decode($mappyjdata);*/
        $mappyOutput = []; 
        $MonsterLayerString = [];
        $MonsterLayerArray = [];
        $MonsterLayerStringVar = [];
        $MonsterLayerArrayVar = [];
        /*
        foreach ($mappydecodeJdata as $mappyData) {
            $Hash = $mappyData->Hash;
            $BNpcBaseID = $mappyData->BNpcBaseID;
            $BNpcNameID = ucwords($BNpcNameCsv->at($mappyData->BNpcNameID)['Singular']);
            $NoThanksArray = array("Earthly Star", "Demi-Bahamut", "Seraph", "Demi-Phoenix", "Esteem", "Automaton Queen", "Bunshin");
            if (in_array($BNpcNameID, $NoThanksArray)) continue;
            $FateID = $mappyData->FateID;
            $HP = $mappyData->HP;
            $PixelX = ($mappyData->PixelX) * 2;
            $PixelY = ($mappyData->PixelY) * 2;
            $PosX = round($mappyData->PosX, 1);
            $PosY = round($mappyData->PosY, 1);
            $Type = $mappyData->Type;
            $NodeID = $mappyData->NodeID;
            switch ($Type) {
                case 'BNPC':
                    $AssetSort = "Monster";
                    $BnpcLayerID = preg_replace('/[^A-Za-z0-9]/', '', $BNpcNameID);
                    $mappyString = "\nvar ". $Type ."icon". $Hash ." = L.icon({className: 'leaflet-div-icon2', iconUrl: '../assets/icons/060000/060004.png',iconAnchor: [16, 16], });
var ". $Type ."raw". $Hash ." = L.marker(map.unproject([". $PixelX .", ". $PixelY ."], map.getMaxZoom()), {icon: ". $Type ."icon". $Hash ."}).bindPopup(\"<center><span class='sptitle'>". $BNpcNameID ."</span></center>HP = ". $HP ."<br>(X:". $PosX ." Y:". $PosY .")\").openPopup().addTo(BNPC_". $BnpcLayerID ."Layer)";
                    $mappyOutput[] = $mappyString;
                    $MonsterLayerString[] = "{label: '". addslashes($BNpcNameID) ."', layer: BNPC_". $BnpcLayerID ."Layer},";
                    $MonsterLayerStringVar[] = "var BNPC_". $BnpcLayerID ."Layer = L.layerGroup();";
                    $JSON_BNPC[] = array(
                        'UniqueHash' => $Hash,
                        'AssetType' => 9,
                        'BNpcBase' => $BNpcBaseID,
                        'BNpcName' => $BNpcNameID,
                        'FateID' => $FateID,
                        'HP' => $HP,
                        'Transform' => array(  
                            'Coords' => array(
                                'X' => $PosX,
                                'Y' => $PosY,
                            ),
                            'Pixel' => array(
                                'X' => $PixelX / 2,
                                'Y' => $PixelY / 2,
                            ),
                        )
                    );
                break;
                case 'Node':
                    $gatheringTimeTable = [];
                    $gpoint = $gatheringpointcsv->at($NodeID)['GatheringPointBase'];
                    $gpointbase = $gatheringPointBaseCsv->at($gpoint)['GatheringType'];
                    $gpointlimited = $gatheringPointBaseCsv->at($gpoint)['IsLimited'];
                    $gpointtype = $gatheringtypecsv->at($gpointbase)['Name'];
                    //switch to EN
                    if ($gpointtype != '●銛') {
                        $gpointtype = $gpointtype;
                    } elseif ($gpointtype = '●銛') {
                        $gpointtype = 'Spearfishing';
                    }
                    $gpointicon = $gatheringtypecsv->at($gpointbase)['Icon{Main}'];
                    
                    if ($gatheringPointTransientCsv->at($NodeID)['GatheringRarePopTimeTable'] > 0) {
                        $gpointicon = $gatheringtypecsv->at($gpointbase)['Icon{Off}'];
                        foreach (range(0, 2) as $t){
                            $startTimeRaw = $GatheringRarePopTimeTableCsv->at($gatheringPointTransientCsv->at($NodeID)['GatheringRarePopTimeTable'])["StartTime[$t]"];
                            if ($startTimeRaw == 65535) continue;
                            $startTimeFmt = date("g:i a", strtotime(substr_replace($startTimeRaw, ':', -2, 0)));
                            $gatheringTimeTableDuration = $GatheringRarePopTimeTableCsv->at($gatheringPointTransientCsv->at($NodeID)['GatheringRarePopTimeTable'])["Duration(m)[$t]"];
                            $tnum = $t + 1;
                            $gatheringTimeTableString = "#". $tnum ." Start: ". $startTimeFmt ." for ". $gatheringTimeTableDuration ." Mins<br>";
                            $gatheringTimeTable[] = $gatheringTimeTableString;
                        }
                    }
                    $gatheringTimeTable = implode($gatheringTimeTable);
                    $AssetSort = "gathering";
                    $gpointlevel = $gatheringPointBaseCsv->at($gpoint)['GatheringLevel'];
                    $gpointbonusRaw = $gatheringpointcsv->at($NodeID)['GatheringPointBonus[0]'];
                    $gpointbonus = "";
                    if ($gpointbonusRaw != 0) {
                        $GpointBonusConditionValue = $gatheringPointBonusCsv->at($gpointbonusRaw)['ConditionValue'];
                        $GpointBonusCondition = str_replace("<Value>IntegerParameter(1)</Value>", $GpointBonusConditionValue, $GatheringConditionCsv->at($gatheringPointBonusCsv->at($gpointbonusRaw)['Condition'])['Text']);
                        $GpointBonusTypeValue = $gatheringPointBonusCsv->at($gpointbonusRaw)['BonusValue'];
                        $GpointBonusType = str_replace("<Value>IntegerParameter(1)</Value>", $GpointBonusTypeValue, $GatheringPointBonusTypeCsv->at($gatheringPointBonusCsv->at($gpointbonusRaw)['BonusType'])['Text']);
                        $gpointbonus = "<br>Location Effect:<br>". $GpointBonusCondition ." <b>→</b> ". $GpointBonusType ."<br>";
                    }
                    $gpointItem = [];
                    foreach (range(0, 7) as $i) {
                        if ($gatheringPointBaseCsv->at($gpoint)["Item[$i]"] == 0) continue;
                        switch ($gpointbase) {
                            case 0:
                            case 1:
                            case 2:
                            case 3:
                                $gpointItemSingle = $itemCsv->at($gatheringItemCsv->at($gatheringPointBaseCsv->at($gpoint)["Item[$i]"])["Item"])["Name"];
                            break;
                            case 4:
                                $gpointItemSingle = $itemCsv->at($spearfishingItemCsv->at($gatheringPointBaseCsv->at($gpoint)["Item[$i]"])["Item"])["Name"];
                            break;
                        }
                        if ($gatheringpointcsv->at($NodeID)['Type'] == 3) {
                            $gpointItemSingle = $eventItemCsv->at($gatheringItemCsv->at($gatheringPointBaseCsv->at($gpoint)["Item[$i]"])["Item"])["Name"];
                        }

                        $gpointItemString = "Item: ". $gpointItemSingle ."<br>";
                        $gpointItem[] = $gpointItemString;
                    }
                    $gpointItem = implode($gpointItem);
                    $JSON_Gathering[] = array(
                        'ID' => $NodeID,
                        'AssetType' => "",
                        'BNpcBase' => $BNpcBaseID,
                        'Transform' => array(  
                            'Coords' => array(
                                'X' => $PosX,
                                'Y' => $PosY,
                            ),
                            'Pixel' => array(
                                'X' => $PixelX / 2,
                                'Y' => $PixelY / 2,
                            ),
                        )
                    );
                    $mappyString = "\nvar ". $Type ."icon". $Hash ." = L.icon({className: 'leaflet-div-icon2', iconUrl: '../assets/icons/060000/0". $gpointicon .".png',iconAnchor: [16, 16], });
var ". $Type ."raw". $Hash ." = L.marker(map.unproject([". $PixelX .", ". $PixelY ."], map.getMaxZoom()), {icon: ". $Type ."icon". $Hash ."}).bindPopup(\"<center><span class='sptitle'>". $gpointtype ."<br></span></center>". $gpointItem ."<br>". $gatheringTimeTable ."<br>Lv. ". $gpointlevel ."". $gpointbonus ."(X:". $PosX ." Y:". $PosY .")\").openPopup().addTo(". $AssetSort .")";
                    $mappyOutput[] = $mappyString;
                break;

                default:
                    # code...
                break;
            }
        } */
        $JSONZoneInfo = array(
            'ID' => $teriID, 
            'PlaceName' => "". $Titleplacename ."". $TitleplacenameSub ."",
            'Code' => $teriName, 
            'Map' => $JSONMapInfo, 
            'BGPath' => $bgPath, 
            'BGM' => $JSONBGMInfo, 
            'ExclusiveType' => $ExclusiveTypeRaw,
            'LGB_Data' => array(
                'Vfx' => $JSON_Vfx,
                'PositionMarker' => $JSON_PositionMarker,
                'Gimmick' => $JSON_Gimmick,
                'Sound' => $JSON_Sound,
                'EventNPC' => $JSON_EventNPC,
                'Aetheryte' => $JSON_Aetheryte,
                'EnvSpace' => $JSON_EnvSpace,
                'PopRange' => $JSON_PopRange,
                'exitrange' => $JSON_exitrange,
                'MapRange' => $JSON_MapRange,
                'EventObject' => $JSON_EventObject,
                'EnvLocation' => $JSON_EnvLocation,
                'Fate' => $JSON_Fate,
                'EventRange' => $JSON_EventRange,
                'CollisionBox' => $JSON_CollisionBox,
                'LineVfx' => $JSON_LineVfx,
                'ClientPath' => $JSON_ClientPath,
                'TargetMarker' => $JSON_TargetMarker,
                'ChairMarker' => $JSON_ChairMarker,
                'PrefetchRange' => $JSON_PrefetchRange,
                'Treasure' => $JSON_Treasure,
                'BNPC' => $JSON_BNPC,
                'Gathering' => $JSON_Gathering,
            ),
        );
        //echo json_encode($JSONZoneInfo, JSON_PRETTY_PRINT);
        $JSONAPIOUTPUT = json_encode($JSONZoneInfo, JSON_PRETTY_PRINT);
        //write Api file
        if (!file_exists("output/arrmtest/API/")) { mkdir("output/arrmtest/API/", 0777, true); }
        $JSONAPI_File = fopen("output/arrmtest/API/$id.json", 'w');
        fwrite($JSONAPI_File, $JSONAPIOUTPUT);
        fclose($JSONAPI_File);

        //$mappyOutput = implode($mappyOutput);
        sort($MonsterLayerString);
        $MonsterLayerArray = implode("\n", array_unique($MonsterLayerString));
        sort($MonsterLayerStringVar);
        $MonsterLayerArrayVar = implode("\n", array_unique($MonsterLayerStringVar));

        //fishing spots
        $fishingspot = [];
        foreach ($fishingspotcsv->data as $fishkey => $fishspot){
        $fishingitemsspot = null;
        $fishingitemsspot = array();
        $fishingitemsspotoutput = null;
        $fishingitemsspotoutput = array();
            $fishteri = $fishspot['TerritoryType'];
            if ($fishteri != $teriID) continue;

            //gather data
            $levelreq = $fishspot['GatheringLevel'];
            $bigfishreach = $fishspot['BigFish{OnReach}'];
            $bigfishend = $fishspot['BigFish{OnEnd}'];
            $pixX = ($fishspot['X'] * 2);
            $pixY = ($fishspot['Z'] * 2);
            $radius = $fishspot['Radius'] / 60;
            $radiusanchor = $radius / 2;
            $placenamefish = $placeNameCsv->at($fishspot['PlaceName'])['Name'];
            //items
            foreach(range(0,9) as $fi) {
                if (empty($itemCsv->at($fishspot["Item[$fi]"])['Name'])) continue;
                $fishitem = $itemCsv->at($fishspot["Item[$fi]"])['Name'];
                $fishingspotItemsString = "Fish: ". $fishitem ."<br>";
                array_push($fishingitemsspot, $fishingspotItemsString);
            }
            $fishingitemsspotoutput = implode($fishingitemsspot);

            $string =
                "\nvar fishingspot". $fishkey ."poly = new L.circle(map.unproject([". $pixX .", ". $pixY ."], map.getMaxZoom()), {radius: ". $radius ."}).bindPopup(\"<center><span class='sptitle'>". $placenamefish ."</span><br>". $fishingitemsspotoutput ."</center>\").openPopup().addTo(fishingspot)";
            $fishingspot[] = $string;
        }
        $fishingspot = implode($fishingspot);



        $NextMapLink = $teriID + 1;
        $PreviousMapLink = $teriID - 1;




		$jsString = "
import { mapswitch } from \"./../htmllist.mjs\";
var mapSW = [0, 4094],
    mapNE = [4094, 0];

var baseurl = \"../". $mapLinkToTeri ."/". $mapLinkToTeri ."/{z}/{x}/{y}.png\",
    base = L.tileLayer(baseurl),

    map = new L.map(\"map\", {
        center: [0, 0],
        zoom: 1,
        minZoom: 2,
        maxZoom: 4,
        noWrap: true,
        crs: L.CRS.Simple,
        //urlHash: true,
        layers: [base]
    });
map.setMaxBounds(new L.LatLngBounds(
    map.unproject(mapSW, map.getMaxZoom()),
    map.unproject(mapNE, map.getMaxZoom())
));

// markers and popups
var mapmarker = L.layerGroup().addTo(map);
var fate = L.layerGroup();
var current = L.layerGroup();
var vista = L.layerGroup();
var bg = L.layerGroup();
var fishingspot = L.layerGroup();
var EnvSpace = L.layerGroup();
var Sound = L.layerGroup();
var EventNPC = L.layerGroup();
var Vfx = L.layerGroup();
var aetheryte = L.layerGroup();
var gathering = L.layerGroup();
var PopRange = L.layerGroup();
var exitrange = L.layerGroup();
var EventObject = L.layerGroup();
var ExitRange = L.layerGroup();
var eventrange = L.layerGroup();
var questmarker = L.layerGroup();
var collisionbox = L.layerGroup();
var ClientPath = L.layerGroup();
var serverpath = L.layerGroup();
var CollisionBox = L.layerGroup();
var EventRange = L.layerGroup();
var MapRange = L.layerGroup();
var light = L.layerGroup();
var Gimmick = L.layerGroup();
var GimmickRange = L.layerGroup();
var ChairMarker = L.layerGroup();
var EnvLocation = L.layerGroup();
var TargetMarker = L.layerGroup();
var Aetheryte = L.layerGroup();
var LineVfx = L.layerGroup();
var PrefetchRange = L.layerGroup();
var PositionMarker = L.layerGroup();
var BattleNPC = L.layerGroup();
var unknown = L.layerGroup();
var Monster = L.layerGroup();
var Treasure = L.layerGroup();
". $MonsterLayerArrayVar ."
". $FATELayerArrayVar ."
". $output ."
". $jsonOutput ."
". $levelOutput ."
". $fishingspot ."


var coords = new L.control.attribution({position: 'topleft', prefix: 'X: 0, Y: 0'}).addTo(map);
map.on('mousemove', updateXY);
function isInteger(n) {
    return n % 1 === 0;
}
var mapSize = 512;
function convertXY(x, y) {
    var modifier = mapSize / 41;
    var xdec = isInteger(x);
    var ydec = isInteger(y);
    var mx, my;
    if (xdec === true && ydec === true) {
        mx = (x * modifier) - (modifier / 2);
        my = (y * modifier) - (modifier / 2);
    } else {
        mx = ((x - 1) * modifier);
        my = ((y - 1) * modifier);
    }
    return map.unproject([mx, my], 1);
}
function updateXY(e) {
    var modifier = mapSize / 41;
    var xy = map.project(e.latlng, 1);
    var xo = xy['x'];
    var yo = xy['y'];
    var xn = Number(((xo / modifier) + 1).toFixed(1));
    var yn = Number(((yo / modifier) + 1).toFixed(1));
    if (parseInt(xn) === xn) {
        xn = xn + \".0\";
    }
    if (parseInt(yn) === yn) {
        yn = yn + \".0\";
    }
    coords.getContainer().innerHTML = \"X: (\" + xn + \") Y: (\" + yn + \")\";
}

var overlays = {
    \"Map Labels\" : mapmarker,
    \"<img src=../assets/icons/060000/060501.png width=18/>FATEs\" : fate,
    \"<img src=../assets/icons/060000/060653.png width=18/>Currents\" : current,
    \"<img src=../assets/icons/060000/060465.png width=18/>Fishingspots\" : fishingspot,
    \"<img src=../assets/icons/060000/060421.png width=18/>NPCs\" : EventNPC,
    \"<img src=../assets/icons/060000/060004.png width=18/>Monsters\" : Monster,
    \"<img src=../assets/icons/060000/060438.png width=18/>Gathering\" : gathering,
    \"<img src=../assets/icons/060000/060429.png width=18/>Vistas\" : vista,
    \"<img src=../assets/icons/060000/060354.png width=18/>Treasure\" : Treasure,
}
var devoverlays = {
    \"<img src=../assets/icons/060000/060408.png width=18/>bg\" : bg,
    \"<img src=../assets/icons/060000/060002.png width=18/>light\" : light,
    \"<img src=../assets/icons/060000/060408.png width=18/>PositionMarker\" : PositionMarker,
    \"<img src=../assets/icons/060000/060914.png width=18/>Vfx\" : Vfx,
    \"<img src=../assets/icons/060000/060071.png width=18/>Gimmick\" : Gimmick,
    \"<img src=../assets/icons/060000/060979.png width=18/>Sound\" : Sound,
    \"<img src=../assets/icons/060000/060422.png width=18/>BattleNPC\" : BattleNPC,
    \"<img src=../assets/icons/060000/060430.png width=18/>Aetheryte\" : Aetheryte,
    \"<img src=../assets/icons/060000/060711.png width=18/>EnvSpace\" : EnvSpace,
    \"<img src=../assets/icons/060000/060408.png width=18/>PopRange\" : PopRange,
    \"<img src=../assets/icons/060000/060457.png width=18/>ExitRange\" : exitrange,
    \"<img src=../assets/icons/060000/060408.png width=18/>MapRange\" : MapRange,
    \"<img src=../assets/icons/060000/060416.png width=18/>EventObject\" : EventObject,
    \"<img src=../assets/icons/060000/060423.png width=18/>EnvLocation\" : EnvLocation,
    \"<img src=../assets/icons/060000/060496.png width=18/>EventRange\" : EventRange,
    \"<img src=../assets/icons/060000/060626.png width=18/>CollisionBox\" : CollisionBox,
    \"<img src=../assets/icons/060000/060457.png width=18/>ClientPath\" : LineVfx,
    \"<img src=../assets/icons/060000/060403.png width=18/>ClientPath\" : ClientPath,
    \"<img src=../assets/icons/060000/060561.png width=18/>TargetMarker\" : TargetMarker,
    \"<img src=../assets/icons/060000/060420.png width=18/>ChairMarker\" : ChairMarker,
    \"<img src=../assets/icons/060000/061731.png width=18/>QuestMarker\" : questmarker,
    \"<img src=../assets/icons/060000/060953.png width=18/>ServerPath\" : serverpath,
    \"<img src=../assets/icons/060000/060496.png width=18/>GimmickRange\" : GimmickRange,
    \"<img src=../assets/icons/060000/060354.png width=18/>EventRange\" : EventRange,
    \"<img src=../assets/icons/060000/060496.png width=18/>PrefetchRange\" : PrefetchRange,
}

// add layer control
var baseTree = [
  {
    label: 'Layers',
    children: [
      {label: 'Map Labels', layer: mapmarker},
      {label: '<img src=../assets/icons/060000/060501.png width=18/>FATEs', layer: fate,
        selectAllCheckbox: true,
        collapsed: true,
        children: [
        ". $FateLayerArray. "
        ]
      },
      {label: '<img src=../assets/icons/060000/060653.png width=18/>Currents', layer: current},
      {label: '<img src=../assets/icons/060000/060465.png width=18/>Fishing Spots', layer: fishingspot},
      {label: '<img src=../assets/icons/060000/061731.png width=18/><span title=\"Type = 51\">Quest Markers</span>', layer: questmarker},
      {label: '<img src=../assets/icons/060000/060421.png width=18/><span title=\"Type = 8\">NPCs</span>', layer: EventNPC},
      {label: '<img src=../assets/icons/060000/060004.png width=18/><span title=\"Type = 9\">Monsters</span>',
        selectAllCheckbox: true,
        collapsed: true,
        children: [
        ". $MonsterLayerArray. "
        ]
      },
      {label: '<img src=../assets/icons/060000/060438.png width=18/><span title=\"\">Gathering</span>', layer: gathering},
      {label: '<img src=../assets/icons/060000/060429.png width=18/><span title=\"\">Vistas</span>', layer: vista},
      {label: '<img src=../assets/icons/060000/060354.png width=18/><span title=\"\">Treasure</span>', layer: Treasure},
    ]
  },
  {
    label: 'Dev Layers',
    collapsed: true,
    children: [
      {label: '<img src=../assets/icons/060000/060002.png width=18/><span title=\"Type = 3\">Lights</span>', layer: light},
      {label: '<img src=../assets/icons/060000/060914.png width=18/><span title=\"Type = 4\">Vfx</span>', layer: Vfx},
      {label: '<img src=../assets/icons/060000/060408.png width=18/><span title=\"Type = 5\">Position Marker</span>', layer: PositionMarker},
      {label: '<img src=../assets/icons/060000/060071.png width=18/><span title=\"Type = 6\">Gimmick</span>', layer: Gimmick},
      {label: '<img src=../assets/icons/060000/060979.png width=18/><span title=\"Type = 7\">Sounds</span>', layer: Sound},
      {label: '<img src=../assets/icons/060000/060422.png width=18/><span title=\"Type = 9\">Battle Npc</span>', layer: BattleNPC},
      {label: '<img src=../assets/icons/060000/060430.png width=18/><span title=\"Type = 12\">Aetheryte</span>', layer: Aetheryte},
      {label: '<img src=../assets/icons/060000/060711.png width=18/><span title=\"Type = 13\">Env Space</span>', layer: EnvSpace},
      {label: '<img src=../assets/icons/060000/060408.png width=18/><span title=\"Type = 40\">PopRange</span>', layer: PopRange},
      {label: '<img src=../assets/icons/060000/060457.png width=18/><span title=\"Type = 41\">Exit Range</span>', layer: exitrange},
      {label: '<img src=../assets/icons/060000/060408.png width=18/><span title=\"Type = 43\">Map Range</span>', layer: MapRange},
      {label: '<img src=../assets/icons/060000/060416.png width=18/><span title=\"Type = 45\">Event Objects</span>', layer: EventObject},
      {label: '<img src=../assets/icons/060000/060423.png width=18/><span title=\"Type = 47\">Env Locations</span>', layer: EnvLocation},
      {label: '<img src=../assets/icons/060000/060496.png width=18/><span title=\"Type = 49\">Event Range</span>', layer: EventRange},
      {label: '<img src=../assets/icons/060000/060626.png width=18/><span title=\"Type = 57\">Collision Boxs</span>', layer: CollisionBox},
      {label: '<img src=../assets/icons/060000/060457.png width=18/><span title=\"Type = 59\">Exit Line VFX</span>', layer: LineVfx},
      {label: '<img src=../assets/icons/060000/060403.png width=18/><span title=\"Type = 65\">Client Paths</span>', layer: ClientPath},
      {label: '<img src=../assets/icons/060000/060953.png width=18/><span title=\"Type = 66\">Server Paths</span>', layer: serverpath},
      {label: '<img src=../assets/icons/060000/060496.png width=18/><span title=\"Type = 67\">Gimmick Range</span>', layer: GimmickRange},
      {label: '<img src=../assets/icons/060000/060561.png width=18/><span title=\"Type = 68\">Target Markers</span>', layer: TargetMarker},
      {label: '<img src=../assets/icons/060000/060420.png width=18/><span title=\"Type = 69\">Chairs</span>', layer: ChairMarker},
      {label: '<img src=../assets/icons/060000/060496.png width=18/><span title=\"Type = 71\">Prefetch Range</span>', layer: PrefetchRange},
    ]
  },
  {
    label: 'Zone Information',
    collapsed: true,
    children: [
        {label: '<table class=\"w3-table w3-striped w3-border\"><tr><th>Zone ID</th><th>Code</th></tr><tr><td>". $id ."</td><td>". $teriName ."</td></tr></table>'},
        {label: 'BG Path : ". $bgPath ."'},
        {label: 'Fixed Time : ". $fixedTime ."'},
        {
            label: 'BGM :',
            collapsed: true,
            children: [
                ". $ZoneBGM ."
            ]
        },
        {
            label: 'Map : ". $mapCode ."(". $mapLink .")',
            collapsed: true,
            children: [
                {label: 'SizeFactor : ". $SizeFactorMap ."'},
                {label: 'Offset X : ". $OffsetXMap ." Y : ". $OffsetYMap ."'},
                ". $MapEvent ."
            ]
        },
        {
            label: 'ArrayEventHandler',
            collapsed: true,
            children: [
                {label: '<b>Handler ID : ". $territoryType['ArrayEventHandler'] ."</b>'},
                ". $ZoneArrayEventHandlerOutput ."
            ]
        },
        {
            label: 'Weather',
            collapsed: true,
            children: [
                ". $WeatherOutput ."
            ]
        },
        {label: 'Can Use Mount? : ". $MountBool ."'},
        {label: 'Can Use Stealth? : ". $StealthBool ."'},
        {label: 'Can Search for PC? : ". $SearchBool ."'},
        {label: 'Is PVP Zone? : ". $PVPZoneBool ."'},
    ]
  },
];



L.control.layers.tree(null, baseTree, {collapsed:false}).addTo(map);
//left map switcher
var mapswitcher = L.control({position:'topleft'});
mapswitcher.onAdd = function (map) {
  this._div = L.DomUtil.create('div', 'info');
  this.update();
  return this._div;
};
mapswitcher.update = function (props) {
    this._div.innerHTML = '<br><h4 class=\"w3-text-white\">Map</h4>';
};
mapswitcher.addTo(map);
var layerControl = L.control.layers.tree(mapswitch, null, {position:'topleft'}).addTo(map);

var allMapLayers = {\"base\": base,
    \"mapmarker\": mapmarker,
    \"Lights\": light,
    \"vista\": vista,
    \"bg\": bg,
    \"current\": current,
    \"fates\": fate,
    \"gathering\": gathering,
    \"fishingspot\": fishingspot,
    \"EnvSpace\": EnvSpace,
    \"Sound\": Sound,
    \"EventNPC\": EventNPC,
    \"PopRange\": PopRange,
    \"Vfx\": Vfx,
    \"PositionMarker\": PositionMarker,
    \"exitrange\": exitrange,
    \"EventObject\": EventObject,
    \"questmarker\": questmarker,
    \"CollisionBox\": CollisionBox,
    \"ClientPath\": ClientPath,
    \"serverpath\": serverpath,
    \"EventRange\": EventRange,
    \"Gimmick\": Gimmick,
    \"LineVfx\": LineVfx,
    \"GimmickRange\": GimmickRange,
    \"EnvLocation\": EnvLocation,
    \"ChairMarker\": ChairMarker,
    \"TargetMarker\": TargetMarker,
    \"MapRange\": MapRange,
    \"Monster\": Monster,
    \"BattleNPC\": BattleNPC,
    \"Aetheryte\": Aetheryte,
    \"Treasure\": Treasure,
    \"PrefetchRange\": PrefetchRange,
};
var hash = new L.Hash(map, allMapLayers);
window.arrmMap = map;
";

        $htmlString = "<!DOCTYPE html>
<!--TerritoryType number : ". $id ."-->
<!--Map number : ". $mapLinkToTeri ."-->
<html style=\"height: 100%; margin: 0;\">
<title>". $Titleplacename ."". $TitleplacenameSub ."". $ExclusiveType ."</title>
<head>
<meta charset=\"UTF-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
<link rel=\"stylesheet\" href=\"../assets/css/main.css\">
<link rel=\"stylesheet\" href=\"../scripts/leaflet/leaflet.css\">
<link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/css?family=Lato\">
<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css\">
<link rel=\"stylesheet\" href=\"../assets/css/easy-button.css\">
<link rel=\"stylesheet\" href=\"../assets/css/L.Control.Layers.Tree.css\"
<link href=\"https://fonts.googleapis.com/css2?family=Roboto&display=swap\" rel=\"stylesheet\">
<link rel=\"shortcut icon\" href=\"../favicon.ico\" type=\"image/x-icon\">
<link rel=\"icon\" href=\"favicon.ico\" type=\"image/x-icon\">
<link type=\"application/json+oembed\" href=\"/oembed.json\" />
<meta content=\"https://arealmremapped.com/images/embedlogo.png\" property=\"og:image\">
<meta content=\"A Realm Remapped - Showing the true Eorzea.\" property=\"og:title\">
<meta content=\"". $Titleplacename ."". $TitleplacenameSub ."". $ExclusiveType ."
Aether Currents, Vistas, Treasure Maps, NPCs and more...\" property=\"og:description\">
<meta content=\"https://arealmremapped.com/images/embedlogo.png\" property=\"og:image\">
<meta name=\"twitter:card\" content=\"summary_large_image\">
<meta name=\"twitter:image\" content=\"https://http://arealmremapped.com/images/embedlogo.png\">
<meta name=\"theme-color\" content=\"#000\">
<script src=\"../scripts/leaflet/leaflet.js\"></script>
<!--<script src=\"scripts/leaflet/leaflet.map-hash.js\"></script> -->
<script src=\"../scripts/leaflet/leaflet-fullHash.js\"></script>
<script src=\"../assets/js/easy-button.js\"></script>
<script src=\"../assets/js/L.Control.Layers.Tree.js\"></script>
<script src=\"../assets/js/l.ellipse.js\"></script>
<script src=\"../assets/js/leaflet.rotatedMarker.js\"></script>
<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js\"></script>

</head>
<body style=\"height: 100%; margin: 0;\">

 <div class=\"w3-bar header-shadow\">
  <a href=\"../index.html\" class=\"w3-bar-item w3-button w3-mobile w3-green\">Home</a>
  <a href=\"../". $NextMapLink ."/". $NextMapLink .".html\" class=\"w3-bar-item w3-button w3-mobile w3-green w3-right\">Next</a>
  <a href=\"../". $PreviousMapLink ."/". $PreviousMapLink .".html\" class=\"w3-bar-item w3-button w3-mobile w3-green w3-right\">Previous</a>
  <span class=\"w3-bar-item w3-wide\"><b>". $Titleplacename ."". $TitleplacenameSub ."". $ExclusiveType ."</b></span>
</div>

<div id=\"map\" style=\"width: 100%; height: 100%; background: #000000;\"></div>
<script type=\"module\" src=\"". $id .".mjs\"></script>

</body>
</html>

";

        //write JS file
        if (!file_exists("output/arrmtest/$id")) { mkdir("output/arrmtest/$id", 0777, true); }
        $js_file = fopen("output/arrmtest/$id/$id.mjs", 'w');
        fwrite($js_file, $jsString);
        fclose($js_file);
        //write HTML file
        if (!file_exists("output/arrmtest/$id")) { mkdir("output/arrmtest/$id", 0777, true); }
        $html_file = fopen("output/arrmtest/$id/$id.html", 'w');
        fwrite($html_file, $htmlString);
        fclose($html_file);


        }

                // Save some data
                $data = [
                ];


                // format using Gamer Escape formatter and add to data array
                // need to look into using item-specific regex, if required.
                $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

            // save our data to the filename: ARRM2.txt
            $this->io->progressFinish();
            $this->io->text('Saving ...');
            $info = $this->save('ARRM2.txt', 9999999);

            $this->io->table(
                [ 'Filename', 'Data Count', 'File Size' ],
                $info
            );
    }
}