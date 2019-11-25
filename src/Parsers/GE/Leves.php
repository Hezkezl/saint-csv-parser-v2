<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:Leves
 */
class Leves implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Top}{{ARR Infobox Levequest
|Name  = {name}
|Patch = {patch}
|Level = {level}

|Guildleve Type     = {guildtype}
|Levequest Type     = {levetype}{duration}
|Levequest Location = {Location}

|Recommended Classes = {classes}

{Objective}

{mobobjective}
{objective}
{fldobjective}

|Description = {description}

|EXPReward = {exp}
|GilReward = ~{gil}
|SealsReward =  <!-- Raw number, no commas. Delete if not needed -->

| Levequest Reward List = {Reward}

<!--  If rewards are conditional, such as only appearing inside of a chest during the leve itself, use these below -->
<!--  Can be combined with the above options. Useful for Amber-encased Vilekin -->
|LevequestRewardOption 1       =  <!-- Item name only -->
|LevequestRewardOption 1 Count =  <!-- Use only if more than 1 -->

|Issuing NPC = {npc}
|Client = {client}

|NPCs Involved  = {npcinvolve} <!-- List of NPCs involved (besides the quest giver,) comma separated-->
|Mobs Involved  = {mobinvolve} <!-- List any Mobs who are involved, comma separated-->
|Items Involved = {item} <!-- List any items used, comma separated-->
|Wanted Target  =  <!-- Usually found during Battlecraft leves -->


{Map}

{FieldLeveMap}

|Strategy =
|Walkthrough =
|Dialogue =
|Etymology =
|Images =
|Notes =
}}{Bottom}";

    public function parse()
    {
        // grab CSV files we want to use
        $LeveCsv = $this->csv('Leve');
        $LeveClientCsv = $this->csv('LeveClient');
        $LeveAssignmentCsv = $this->csv('LeveAssignmentType');
        $ItemCsv = $this->csv('Item');
        $CraftLeveCsv = $this->csv('CraftLeve');
        $GatheringLeveCsv = $this->csv('GatheringLeve');
        $BattleLeveCsv = $this->csv('BattleLeve');
        $BNpcNameCsv = $this->csv('BNpcName');
        $LevelCsv = $this->csv('Level');
        $MapCsv = $this->csv('Map');
        $PlaceNameCsv = $this->csv('PlaceName');
        $ClassJobCsv = $this->csv('ClassJobCategory');
        $JournalGenreCsv = $this->csv('JournalGenre');
        $ENpcResidentCsv = $this->csv('ENpcResident');
        $EventItemCsv = $this->csv('EventItem');
        $LeveRewardItemGroupCsv = $this->csv('LeveRewardItemGroup');
        $LeveRewardItemCsv = $this->csv('LeveRewardItem');
        $LeveStringCsv = $this->csv('LeveString');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $GatheringLeveRouteCsv = $this->csv('GatheringLeveRoute');



        // (optional) start a progress bar
        $this->io->progressStart($LeveCsv->total);

        // loop through data
        foreach ($LeveCsv->data as $id => $leve) {
            $this->io->progressAdvance();

            $patch = '5.11';

            // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
            // manually, set to false
            $Bot = "false";
            $Name = $leve['Name'];

            // skip ones without a name, and skip if name contains Kanji, Hiragana, or Katakana
            if ((empty($Name)) || (preg_match("/[\x{30A0}-\x{30FF}\x{3040}-\x{309F}\x{4E00}-\x{9FBF}]+/u", $Name))) {
                continue;
            }

            // change the top and bottom code depending on if I want to bot the pages up or not
            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name/Patch'''\n$patch\n<noinclude>[[Category:Patch Subpages]]</noinclude>\n{{-stop-}}{{-start-}}\n'''$Name'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name?action=edit\n";
                $Bottom = "";
            };

            // Change the 'Levequest Type' parameter to appropriate name
            $levetype = false;
            switch ($leve['LeveAssignmentType']) {
                case 0: case 13: case 14: case 15:
                    break;
                case 1: case 16: case 17: case 18:
                    $levetype = "Battlecraft";
                    break;
                case 2:case 3:case 4:
                    $levetype = "Fieldcraft";
                    break;
                case 5: case 6: case 7: case 8: case 9:
                case 10:case 11:case 12:
                    $levetype = "Tradecraft";
                    break;
                default:
                    break;
            };

            // Give the proper name to the Levequest's type (the card icon)
            $guildtype = [
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
                4 => NULL,
                5 => NULL,
                6 => "Valor",
                7 => "Tenacity (Guildleve)",
                8 => "Wisdom",
                9 => "Justice",
                10 => "Dilligence",
                11 => "Temperance",
                12 => "Devotion",
                13 => "Veracity",
                14 => "Piety (Guildleve)",
                15 => "Candor",
                16 => "Industry",
                17 => "Courage",
                18 => "Constancy",
                19 => "Ingenuity",
                20 => "Contentment",
                21 => "Promptitude",
                22 => "Prudence",
                23 => "Resolve",
                24 => "Ambition",
                25 => "Benevolence",
                26 => "Charity",
                27 => "Sapience",
                28 => "Hability",
                29 => "Munificence",
                30 => "Sincerity",
                31 => "Vengeance",
                32 => "Vocation",
                33 => "Service",
                34 => "Equity",
                35 => "Wit",
                36 => "Unity",
                37 => "Truth",
                38 => "Mercy",
                39 => "Gravity (Guildleve)",
                40 => "Confidence",
                41 => "Sympathy",
                42 => "Concord",
                43 => "Diversity",
                44 => "Esteem",
                45 => "Conviction",
                46 => "Constancy",
                47 => "Charity",
                48 => "Munificence",
                49 => "Piety (Guildleve)",
                50 => "Candor",
                51 => "Benevolence",
                52 => "Concord",
                53 => "Sincerity",
            ];

            // Assigning Grand Company and classes for appropriate leves
            $grandcompany = false;
            if ($leve['LeveAssignmentType'] == 1) {
                $classes = "Disciples of Magic, Disciples of War";
            } elseif ($leve['LeveAssignmentType'] == 16) {
                $classes = "Disciples of Magic, Disciples of War";
                $grandcompany = "The Maelstrom";
            } elseif ($leve['LeveAssignmentType'] == 17) {
                $classes = "Disciples of Magic, Disciples of War";
                $grandcompany = "The Order of the Twin Adder";
            } elseif ($leve['LeveAssignmentType'] == 18) {
                $classes = "Disciples of Magic, Disciples of War";
                $grandcompany = "The Immortal Flames";
            } else {
                $classes = $LeveAssignmentCsv->at($leve['LeveAssignmentType'])['Name'];
            }

            // Objective text for Disciple of the Hand leves
            $TradecraftObjective = false;
            $FieldcraftObjective = false;
            $Item = false;
            $NpcInvolvement = false;
            $Npc = false;
            $RewardNumber = false;
            $TargetNumber = false;
            $RouteNumber = false;
            $GatheringLeveNumber = false;
            $MobInvolvement = [];
            $InvolvementObjective = [];
            $BattlecraftItemsInvolved = [];
            $RewardItem = [];
            $Objective = [];
            $Map = [];
            $FieldLeveMap = [];


            // | Levequest Reward List =
            foreach(range(0,7) as $i) {
                foreach(range(0,8) as $a) {

                    //|LevequestReward 3        = item name
                    $RewardItemName = $ItemCsv->at($LeveRewardItemGroupCsv->at($LeveRewardItemCsv->at($leve['LeveRewardItem'])["LeveRewardItemGroup[$i]"])["Item[$a]"])['Name'];

                    //|LevequestReward 6 Count  = x
                    $ItemRewardAmount = $LeveRewardItemGroupCsv->at($LeveRewardItemCsv->at($leve['LeveRewardItem'])["LeveRewardItemGroup[$i]"])["Count[$a]"];
                    //skip if the reward is zero therefore no reward then increase the Reward number by 1
                    if ($ItemRewardAmount == 0) continue;
                    $RewardNumber = ($RewardNumber + 1);
                    //probability
                    $RewardChance = $LeveRewardItemCsv->at($leve['LeveRewardItem'])["Probability<%>[$i]"];
                    //is the item HQ?
                    if ($LeveRewardItemGroupCsv->at($LeveRewardItemCsv->at($leve['LeveRewardItem'])["LeveRewardItemGroup[$i]"])["HQ[$a]"] == "False") {
                        $RewardHQ = "";
                    } elseif ($LeveRewardItemGroupCsv->at($LeveRewardItemCsv->at($leve['LeveRewardItem'])["LeveRewardItemGroup[$i]"])["HQ[$a]"] == "True") {
                        $RewardHQ = "|LevequestReward ". $RewardNumber ." HQ     = x\n";
                    }
                    //string
                    $RewardItem[0] = "\n";
                    $RewardItem[] = "|LevequestReward ". $RewardNumber ."        = ". $RewardItemName ."\n|LevequestReward ". $RewardNumber ." Count  = ". $ItemRewardAmount ."\n|LevequestReward ". $RewardNumber ." Chance = ". $RewardChance ."%\n". $RewardHQ ."";

                }
            }

            if ($levetype == "Tradecraft") {
                $CraftLeveItem = $CraftLeveCsv->at($leve['DataId'])['Item[0]'];
                $CraftLeveItemQty = $CraftLeveCsv->at($leve['DataId'])['ItemCount[0]'];
                $ItemSingle = $ItemCsv->at($CraftLeveItem)['Singular'];
                $ItemPlural = $ItemCsv->at($CraftLeveItem)['Plural'];
                $ItemVowel = $ItemCsv->at($CraftLeveItem)['StartsWithVowel'];
                $Item = $ItemCsv->at($CraftLeveItem)['Name'];
                $NpcName = $ENpcResidentCsv->at($LevelCsv->at($leve['Level{Levemete}'])['Object'])['Singular'];
                if ($CraftLeveItemQty > 1) {
                    $TradecraftObjective = "*Deliver [[$Item|$ItemPlural]] to {{NPCLink|$NpcName}}. 0/$CraftLeveItemQty";
                } elseif ($ItemVowel == "0" && $CraftLeveItemQty == "1") {
                    $TradecraftObjective = "*Deliver a [[$Item|$ItemSingle]] to {{NPCLink|$NpcName}}. 0/$CraftLeveItemQty";
                } elseif ($ItemVowel == "1" && $CraftLeveItemQty == "1") {
                    $TradecraftObjective = "*Deliver an [[$Item|$ItemSingle]] to {{NPCLink|$NpcName}}. 0/$CraftLeveItemQty";
                }
            } elseif ($levetype == "Battlecraft") {
                foreach(range(0,7) as $i) {
                    if ($BattleLeveCsv->at($leve['DataId'])["BNpcName[$i]"] > 1) {
                        $TargetNumber = ($TargetNumber + 1);
                        $BNpcName = "|Target ". $TargetNumber ." Name     = ". ucwords(strtolower($BNpcNameCsv->at($BattleLeveCsv->at($leve['DataId'])["BNpcName[$i]"])['Singular']));
                        //Data per monster
                        $BCTime = "|Target ". $TargetNumber ." Time     = ". $BattleLeveCsv->at($leve['DataId'])["Time[$i]"];
                        $BCBaseID = "|Target ". $TargetNumber ." ID       = ". $BattleLeveCsv->at($leve['DataId'])["BaseID[$i]"];
                        $BCLevel = "|Target ". $TargetNumber ." Level    = ". $BattleLeveCsv->at($leve['DataId'])["EnemyLevel[$i]"];
                        if (!empty($EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$i]"])['Name'])) {
                        $BCItemsInvolved = "|Target ". $TargetNumber ." Drops    = ". $EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$i]"])['Name']. "\n";
                        $BCItemQTY = "|Target ". $TargetNumber ." QTY      = ". $BattleLeveCsv->at($leve['DataId'])["ItemsInvolvedQty[$i]"] ."\n";
                        $BCItemDropRate = "|Target ". $TargetNumber ." DropRate = ". $BattleLeveCsv->at($leve['DataId'])["ItemDropRate[$i]"] ." %\n";
                        } elseif (empty($EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$i]"])['Name'])) {
                        $BCItemsInvolved = "";
                        $BCItemQTY = "";
                        $BCItemDropRate = "";
                        }
                        $BCToDoNumber = "|Target ". $TargetNumber ." Amount   = ". $BattleLeveCsv->at($leve['DataId'])["ToDoNumberInvolved[$i]"];
                        $BCToDoParam = "|Target ". $TargetNumber ." Param    = ". $BattleLeveCsv->at($leve['DataId'])["ToDoParam[$i]"];
                        $MobInvolvement[] = $BNpcName;
                        $InvolvementObjective[0] = "Enemies:";
                        $InvolvementObjective[] = "" .$BNpcName ."\n" .$BCTime ."\n" .$BCBaseID ."\n" .$BCLevel ."\n" .$BCItemsInvolved ."" .$BCItemQTY ."" .$BCItemDropRate ."" .$BCToDoNumber ."\n" .$BCToDoParam ."\n";
                    }

                    $BNpcNameObjective = ucwords(strtolower($BNpcNameCsv->at($BattleLeveCsv->at($leve['DataId'])["BNpcName[0]"])['Singular']));
                    foreach (range(0,1) as $a) {
                        $ObjectiveText = str_replace("<SheetEn(BNpcName,2,IntegerParameter(1),1,1)/>", "". $BNpcNameObjective ."", $LeveStringCsv->at($BattleLeveCsv->at($leve['DataId'])["Objective[$a]"])['Objective']);
                        //sort SE's if code
                        /**
                         * THIS IS BROKEN AND DOES NOT WORK 100% PLEASE FIX BEFORE USE
                        */
                        foreach(range(0,7) as $i) {
                            $ItemIF = $EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[0]"])['Name'];
                        }
                        if (!empty($ItemIF)) {
                            $ObjectiveText = str_replace("<If(Equal(IntegerParameter(1),0))>Attack target to reveal its<Else/>", "", $ObjectiveText);
                            $ObjectiveText = str_replace("<SheetEn(EventItem,1,IntegerParameter(1),1,1)/>", $ItemIF, $ObjectiveText);
                            $ObjectiveText = str_replace("</If>", "", $ObjectiveText);
                            $ObjectiveText = str_replace("<If(Equal(IntegerParameter(1),0))>the /soothe emote<Else/><SheetEn(EventItem,2,IntegerParameter(1),1,1)/>", "". $ItemIF ."", $ObjectiveText);
                        } elseif (empty($ItemIF)) {
                            $ObjectiveText = str_replace("<If(Equal(IntegerParameter(1),0))>Attack target to reveal its<Else/>", "", $ObjectiveText);
                            $ObjectiveText = str_replace("<SheetEn(EventItem,1,IntegerParameter(1),1,1)/>", $ItemIF, $ObjectiveText);
                            $ObjectiveText = str_replace("</If>", "", $ObjectiveText);
                            $ObjectiveText = str_replace("<If(Equal(IntegerParameter(1),0))>the /soothe emote<Else/><SheetEn(EventItem,2,IntegerParameter(1),1,1)/>", "the /soothe emote", $ObjectiveText);
                        }

                        if (!empty($ObjectiveText)) {
                            $ObjectiveString = "|Objective = ". $ObjectiveText ."";
                        }
                        /**
                         * THIS IS BROKEN AND DOES NOT WORK 100% PLEASE FIX BEFORE USE
                        */


                        $Objective[] = "". $ObjectiveString ."";
                        //$Objective[1] = "";
                    }

                    // doesn't work. attempt to make it so that if there's an item that appears during a battle leve, then it should get priority over
                    // just displaying the "Defeat target enemies." text. But... doesn't work. Also need to finish adding in the item list for
                    // items involved here, as it doesn't work either (items involved currently only works for tradecraft leves, but battlecraft
                    // can have them too. They're just in the battleleve file instead of the craftleve file...)
                    //if ($BattleLeveCsv->at($leve['DataId'])["BNpcName[$i]"] > 1 && $BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$i]"] > 1) {
                        //$BattlecraftItemsInvolved = $EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$i]"])['Name'];
                        //$BNpcName = ucwords(strtolower($BNpcNameCsv->at($BattleLeveCsv->at($leve['DataId'])["BNpcName[$i]"])['Singular']));
                        //$MobInvolvement[] = $BNpcName;
                        //$InvolvementObjective[0] = "*Obtain target items.";
                        //$InvolvementObjective[] = $BattlecraftItemsInvolved;

                    //}
                }
            } elseif ($levetype == "Fieldcraft") {

             // Need to do something for fieldcraft ones, but haven't even begun to think about it yet so commenting out.
            
                $FieldLeveItem = $CraftLeveCsv->at($leve['DataId'])['Item[0]'];
                $FieldLeveItemQty = $CraftLeveCsv->at($leve['DataId'])['ItemCount[0]'];
                $ItemSingle = $ItemCsv->at($CraftLeveItem)['Singular'];
                $ItemPlural = $ItemCsv->at($CraftLeveItem)['Plural'];
                $ItemVowel = $ItemCsv->at($CraftLeveItem)['StartsWithVowel'];
                $Item = $ItemCsv->at($CraftLeveItem)['Name'];
                $NpcName = $ENpcResidentCsv->at($LevelCsv->at($leve['Level{Levemete}'])['Object'])['Singular'];
                //if ($FieldLeveItemQty > 1) {
                //    $FieldcraftObjective = "*Deliver [[$Item|$ItemPlural]] to {{NPCLink|$NpcName}}. 0/$FieldLeveItemQty";
                //} elseif ($ItemVowel == "0" && $FieldLeveItemQty == "1") {
                //    $FieldcraftObjective = "*Deliver a [[$Item|$ItemSingle]] to {{NPCLink|$NpcName}}. 0/$FieldLeveItemQty";
                //} elseif ($ItemVowel == "1" && $FieldLeveItemQty == "1") {
                //    $FieldcraftObjective = "*Deliver an [[$Item|$ItemSingle]] to {{NPCLink|$NpcName}}. 0/$FieldLeveItemQty";
                //}
                $ObjectiveString = $LeveStringCsv->at($GatheringLeveCsv->at($leve['DataId'])["Objective[0]"])["Objective"];
                $ObjectiveString2 = $LeveStringCsv->at($GatheringLeveCsv->at($leve['DataId'])["Objective[1]"])["Objective"];
                if (empty($ObjectiveString2)) {
                    $FieldcraftObjective = "|Objective = ". $ObjectiveString ."";
                } elseif (empty($ObjectiveString)) {
                    $FieldcraftObjective = "";
                } elseif (!empty($ObjectiveString2)) {
                    $FieldcraftObjective = "|Objective = ". $ObjectiveString ."\n". $ObjectiveString2 ."";
                }
            //maps for fieldleve
                foreach (range(0,3) as $c) { // 4 of GatheringLeve
                    $GatheringLeveNumber = ($GatheringLeveNumber + 1);
                    foreach (range(0,11) as $s) { //12 of LeveRoute
                        $RouteNumber = ($RouteNumber + 1);
                        $X = $LevelCsv->at($GatheringLeveRouteCsv->at($GatheringLeveCsv->at($leve["DataId"])["Route[$c]"])["PopRange[$s]"])["X"];
                        $Y = $LevelCsv->at($GatheringLeveRouteCsv->at($GatheringLeveCsv->at($leve["DataId"])["Route[$c]"])["PopRange[$s]"])["Z"];
                        $route = $GatheringLeveCsv->at($leve["DataId"])["Route[$c]"];
                        $PopRangeRoute = $GatheringLeveRouteCsv->at($route)["PopRange[$s]"];

                        //superimpose data:

                        //get the map positions for each object

                        $PopRangeLevelTeri = $LevelCsv->at($leve['Level{Levemete}'])["Territory"];
                        $PopRangeTeriZoneID = $TerritoryTypeCsv->at($PopRangeLevelTeri)['Name']; //Zone ID
                        $PopRangeTeriPlaceName = $PlaceNameCsv->at($TerritoryTypeCsv->at($PopRangeLevelTeri)['PlaceName'])['Name']; //PlaceName
                        $PopRangeBase = "". $PopRangeTeriZoneID ." - ". $PopRangeTeriPlaceName .".png";
                        if (empty($X))continue;
                        //position calculator
                        $scale = $MapCsv->at($TerritoryTypeCsv->at($PopRangeLevelTeri)['Map'])['SizeFactor'];
                        $a = $scale / 100.0;
                        $offsetx = $MapCsv->at($TerritoryTypeCsv->at($PopRangeLevelTeri)['Map'])['Offset{X}'];
                        $offsetValueX = ($X + $offsetx) * $a;
                        $LocX = ((41.0 / $a) * (($offsetValueX + 1024.0) / 2048.0) +1);
                        $PixelX = ((($LocX - 1) * 50 * $a) /2);

                        $offsety = $MapCsv->at($TerritoryTypeCsv->at($PopRangeLevelTeri)['Map'])['Offset{Y}'];
                        $offsetValueY = ($Y + $offsety) * $a;
                        $LocY = ((41.0 / $a) * (($offsetValueY + 1024.0) / 2048.0) +1);
                        $PixelY = ((($LocY - 1) * 50 * $a) /2);

                        $PopRange = "{{Superimpose2\n| border = \n| collapse = \n| base = ". $PopRangeBase ."\n| base_width = 1024px\n| base_style = float: left\n| base_alt = PopRange\n| base_caption =\n| base_link =\n\n";
                        $PopRange2 = "| float". $RouteNumber ." = Map19_Icon.png\n| float". $RouteNumber ."_width = 36px\n| float". $RouteNumber ."_alt = ". $RouteNumber ."\n| float". $RouteNumber ."_caption =\n| link". $RouteNumber ." =\n| x". $RouteNumber ." = ". $PixelX ."\n| y". $RouteNumber ." = ". $PixelY ."\n| t". $RouteNumber ." =";
                        $FieldLeveMap[0] = "". $PopRange ."\n";
                        $FieldLeveMap[] = "". $PopRange2 ."\n";
                    }
                }
            }



            //check to see if theres a "start" before moving to levemete
            $LevelMeteStart = $leve['Level{Levemete}'];
            $LevelStart = $leve['Level{Start}'];
            if ($LevelStart !== "0") {
                $LevelMete = $LevelStart;
            } elseif ($LevelStart == "0") {
                $LevelMete = $LevelMeteStart;
            }
            $LevelX = $LevelCsv->at($LevelMete)['X']; //Raw X
            $LevelY = $LevelCsv->at($LevelMete)['Z']; //Raw Y
            //Get the zone id and placenames
            $LevelTeri = $LevelCsv->at($LevelMete)['Territory'];
            $LevelTeriZoneID = $TerritoryTypeCsv->at($LevelTeri)['Name']; //Zone ID
            $LevelTeriPlaceName = $PlaceNameCsv->at($TerritoryTypeCsv->at($LevelTeri)['PlaceName'])['Name']; //PlaceName
            $LevelTeriString = "|levelTeri = ". $LevelTeri ."\n";
            $LevelTeriString .= "|ZoneID       = ". $LevelTeriZoneID ."\n";
            $LevelTeriString .= "|PlaceName       = ". $LevelTeriPlaceName ."\n";


            $LevelObject = $LevelCsv->at($LevelMete)['Object'];
            $ObjectName = ucwords(strtolower($ENpcResidentCsv->at($LevelObject)['Singular']));


            $MapString = "|LeveMeteID = ". $LevelMete ."\n|levelX = ". $LevelX ."\n|levelY = ". $LevelY ."\n". $LevelTeriString ."|levelObject = ". $LevelObject ."\n|ENpcName = ". $ObjectName ."\n";

            $Map[] = "". $MapString ."";




            $MobInvolvement = array_unique($MobInvolvement);
            $MobInvolvement = implode(", ", $MobInvolvement);
            $InvolvementObjective = array_unique($InvolvementObjective);
            $MobObjective = implode("\n", $InvolvementObjective);
            $RewardItem = implode("\n", $RewardItem);
            $Objective = implode(", ", $Objective);
            $Map = implode("", $Map);
            $FieldLeveMap = implode("", $FieldLeveMap);



            //NOTES TO DO:
            //I found that in places like "CraftLeve.exd there is a "Repeats" Column, this means we can show on the
            //wiki that we can have it say when "Repeats = 2" "You can hand it in a total of 3 times for extra exp"

            //I can link each GatheringLeve to a gathering point. What i cannot figure out how to do is how to
            //link that gathering point to level. I would need to somehow find it in "Object" Column in Level
            //and output the key and then go back to that key to get the x and y

                // Save some data
            $data = [
                '{Top}' => $Top,
                '{patch}' => $patch,
                '{index}' => $leve['id'],
                '{name}' => $leve['Name'],
                '{level}' => $leve['ClassJobLevel'],
                '{guildtype}' => $guildtype[$leve['LeveVfx']],
                '{duration}' => ($levetype == "Battlecraft") ? "\n|Leve Duration      = ". $leve['TimeLimit'] ."m" : "",
                '{levetype}' => $levetype,
                '{grandcompany}' => ($leve['LeveAssignmentType'] == 16 || $leve['LeveAssignmentType'] == 17 || $leve['LeveAssignmentType'] == 18)
                    ? "\n|Grand Company      = ". $grandcompany : "",
                '{classes}' => $classes,
                '{objective}' => $TradecraftObjective,
                '{fldobjective}' => $FieldcraftObjective,
                '{mobobjective}' => $MobObjective,
                '{description}' => $leve['Description'],
                '{exp}' => ($leve['ExpReward'] > 0) ? $leve['ExpReward'] : "{{Information Needed}}",
                '{gil}' => $leve['GilReward'],
                '{npc}' => $Npc,
                '{client}' => $LeveClientCsv->at($leve['LeveClient'])['Name'],
                '{npcinvolve}' => $NpcInvolvement,
                '{mobinvolve}' => $MobInvolvement,
                '{item}' => $Item,
                '{Bottom}' => $Bottom,
                '{Reward}' => $RewardItem,
                '{Objective}' => $Objective,
                '{Map}' => $Map,
                '{Location}' => $LevelTeriPlaceName,
                '{FieldLeveMap}' => $FieldLeveMap,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("GeLeveWiki.txt - ". $patch .".txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
