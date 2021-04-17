<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Leves
 */
class Leves implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Top}{{ARR Infobox Levequest
|Index = {index}
|Name  = {name}
|Patch = {patch}
|Level = {level}{card}

|Guildleve Type     = {guildtype}
|Levequest Type     = {levetype}{duration}
|Levequest Location = {location}{grandcompany}
|Header Image       = {header}.png

|Recommended Classes = {classes}
{trdobjective}{fldobjective}{ObjectiveTask}{mobobjectivelist}{objectiveItemTodo}{mobobjective}{description}{turnins}

|EXPReward = {exp}
|GilReward = ~{gil}{seals}{reward}{npc}
|Client = {client}

|NPCs Involved  = {npcinvolve}<!-- List of NPCs involved (besides the quest giver,) comma separated-->
|Items Involved = {item}<!-- List any items used, comma separated-->{mobinvolve}

|Strategy =
|Walkthrough =
|Dialogue =
|Etymology =
|Images =
|Notes =
}}{Bottom}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $LeveCsv = $this->csv('Leve');
        $LeveVfxCsv = $this->csv('LeveVfx');
        $LeveClientCsv = $this->csv('LeveClient');
        $LeveAssignmentCsv = $this->csv('LeveAssignmentType');
        $ItemCsv = $this->csv('Item');
        $CraftLeveCsv = $this->csv('CraftLeve');
        $GatheringLeveCsv = $this->csv('GatheringLeve');
        $BattleLeveCsv = $this->csv('BattleLeve');
        $BNpcNameCsv = $this->csv('BNpcName');
        $LevelCsv = $this->csv('Level');
        $PlaceNameCsv = $this->csv('PlaceName');
        $ENpcResidentCsv = $this->csv('ENpcResident');
        $EventItemCsv = $this->csv('EventItem');
        $LeveRewardItemGroupCsv = $this->csv('LeveRewardItemGroup');
        $LeveRewardItemCsv = $this->csv('LeveRewardItem');
        $LeveStringCsv = $this->csv('LeveString');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $GatheringLeveRouteCsv = $this->csv('GatheringLeveRoute');
        //$MapCsv = $this->csv('Map');
        //$TownCsv = $this->csv('Town');
        $GatheringPointCsv = $this->csv('GatheringPoint');
        $GatheringPointBaseCsv = $this->csv('GatheringPointBase');
        $GatheringItemCsv = $this->csv('GatheringItem');
        $LevemetePatchCsv = $this->csv('LevemetePatch');
        //$CompanyLeveCsv = $this->csv('CompanyLeve');
        //$CompanyLeveRuleCsv = $this->csv('CompanyLeveRule');

        // (optional) start a progress bar
        $this->io->progressStart($LeveCsv->total);
        
        $this->PatchCheck($Patch, "Leve", $LeveCsv);
        $PatchNumber = $this->getPatch("Leve");

        // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
        // manually, set to false
        $Bot = "true";

        // loop through data
        foreach ($LeveCsv->data as $id => $leve) {
            $this->io->progressAdvance();

            // skip if name is blank, or if name contains Kanji, Hiragana, or Katakana (JP text)
            $Name = $leve['Name'];
            if ((empty($Name)) || (preg_match("/[\x{30A0}-\x{30FF}\x{3040}-\x{309F}\x{4E00}-\x{9FBF}]+/u", $Name))) {
                continue;
            }
            $Patch = $PatchNumber[$id];

            //fordebug
            //$db = $leve['DataId'];
            //if ($id != 858) continue;
            //fordebug

            //get the Patch and Issuing NPC from a separate file called LevemetePatch.csv which was custom made
            $Npc = $LevemetePatchCsv->at($leve['Name'])['Levemete'];

            //Test to see wtf is going on with patch numbers
            //echo "Patch = $PatchFile -- $Npc -- $Patch\n";
            //Seems to work fine, idk.

            // change the top and bottom code depending on if I want to bot the pages up or not
            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name?action=edit\n";
                $Bottom = false;
            };

            // Change the 'Levequest Type' parameter to appropriate name
            $levetype = false;
            switch ($leve['LeveAssignmentType']) {
                case 0: case 13: case 14: case 15:
                    break;
                case 1:
                    $levetype = "Battlecraft";
                    break;
                case 16: case 17: case 18:
                    $levetype = "Grand Company";
                    break;
                case 2: case 3: case 4:
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
                1 => "Platinum",
                2 => "Gold",
                3 => "Blue",
                4 => "Silver",
                5 => "Bronze",
                6 => "Valor",
                7 => "Tenacity (Guildleve)",
                8 => "Wisdom",
                9 => "Justice",
                10 => "Diligence",
                11 => "Temperance (Guildleve)",
                12 => "Devotion (Guildleve)",
                13 => "Veracity",
                14 => "Piety (Guildleve)",
                15 => "Candor",
                16 => "Industry",
                17 => "Courage",
                18 => "Constancy",
                19 => "Ingenuity (Guildleve)",
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
                31 => "Vengeance (Guildleve)",
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
                45 => "Conviction (Guildleve)",
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

            // declare certain variables as false so they don't wind up getting reprinted by accident
            $TradecraftObjective = false;
            $FieldcraftObjective = false;
            $BattleObjective = false;
            $Item = false;
            $NpcName = false;
            $RewardNumber = false;
            $TargetNumber = false;
            //$RouteNumber = false;
            //$GatheringLeveNumber = false;
            //$MobInvolvement = [];
            $MoreTradein = false;
            $FieldLeveItemQty = false;
            $RewardHQ = false;
            $LevelMete = false;
            $MobsInvolvedArr = [];
            $InvolvementObjective = [];
            $RewardItem = [];
            $Map = [];
            $FieldLeveMap = [];
            $MobObjectiveList = [];
            $objectiveItemTodo = false;
            $ObjectiveTask = false;

            //$debugleveid = $leve['DataId'];

            // Levequest Reward List. Need a double foreach here.
            foreach(range(0,7) as $i) {
                //$GroupNumber = $i + 1;
                foreach(range(0,8) as $a) {

                    //|LevequestReward 3        = item name
                    $RewardItemName = $ItemCsv->at($LeveRewardItemGroupCsv->at($LeveRewardItemCsv->at($leve['LeveRewardItem'])["LeveRewardItemGroup[$i]"])["Item[$a]"])['Name'];

                    //|LevequestReward 6 Count  = x
                    $ItemRewardAmount = $LeveRewardItemGroupCsv->at($LeveRewardItemCsv->at($leve['LeveRewardItem'])["LeveRewardItemGroup[$i]"])["Count[$a]"];

                    //skip if the reward is zero therefore no reward then increase the Reward number by 1
                    if ($ItemRewardAmount == 0) continue;
                    $RewardNumber = ($RewardNumber + 1);
                    $count = 0;

                    foreach(range(0,8) as $c) {
                        //just dead variable so "count" can see if it exists or not
                        $defitem = $LeveRewardItemGroupCsv->at($LeveRewardItemCsv->at($leve['LeveRewardItem'])["LeveRewardItemGroup[$i]"])["Item[$c]"];
                        if ($defitem != 0) {
                            //up the count by 1 every time defitem isnt 0
                            $count++;
                        }
                    }

                    //probability
                    //$ItemChance = floor(100/$count);
                    $GroupChance = ($LeveRewardItemCsv->at($leve['LeveRewardItem'])["Probability<%>[$i]"]);

                    $TotalChance = round($GroupChance / $count, 1);

                    //is the item HQ?
                    if ($LeveRewardItemGroupCsv->at($LeveRewardItemCsv->at($leve['LeveRewardItem'])["LeveRewardItemGroup[$i]"])["HQ[$a]"] == "False") {
                        $RewardHQ = false;
                    } elseif ($LeveRewardItemGroupCsv->at($LeveRewardItemCsv->at($leve['LeveRewardItem'])["LeveRewardItemGroup[$i]"])["HQ[$a]"] == "True") {
                        $RewardHQ = "\n|LevequestReward ". $RewardNumber ." HQ     = x";
                    }

                    //string
                    $RewardItem[0] = "\n";
                    $RewardItem[] =
                        "|LevequestReward ". $RewardNumber ."        = ". $RewardItemName
                        ."\n|LevequestReward ". $RewardNumber ." Count  = ". $ItemRewardAmount
                        ."\n|LevequestReward ". $RewardNumber ." Chance = ". $TotalChance ."%". $RewardHQ;
                }
            }

            // Objective text for Disciple of the Hand leves
            if ($levetype == "Tradecraft") {
                $CraftLeveItem = $CraftLeveCsv->at($leve['DataId'])['Item[0]'];
                $CraftLeveItemQty = $CraftLeveCsv->at($leve['DataId'])['ItemCount[0]'];
                $ItemSingle = $ItemCsv->at($CraftLeveItem)['Singular'];
                $ItemPlural = $ItemCsv->at($CraftLeveItem)['Plural'];
                $ItemVowel = $ItemCsv->at($CraftLeveItem)['StartsWithVowel'];
                $Item = $ItemCsv->at($CraftLeveItem)['Name'];
                $MoreTradeinRaw = $CraftLeveCsv->at($leve['DataId'])['Repeats'];
                if ($MoreTradeinRaw == 0) {
                    $MoreTradein = false;
                } elseif ($MoreTradeinRaw !== 0) {
                    $MoreTradeinMaths = ($MoreTradeinRaw + 1);
                    $MoreTradein = "\n|TurnInRepeat = ". $MoreTradeinMaths;
                }
                $NpcName = $ENpcResidentCsv->at($LevelCsv->at($leve['Level{Levemete}'])['Object'])['Singular'];
                if ($CraftLeveItemQty > 1) {
                    $TradecraftObjective = "\n|Objectives =\n*Deliver [[$Item|$ItemPlural]] to {{NPCLink|$NpcName}}. 0/$CraftLeveItemQty";
                } elseif ($ItemVowel == "0" && $CraftLeveItemQty == "1") {
                    $TradecraftObjective = "\n|Objectives =\n*Deliver a [[$Item|$ItemSingle]] to {{NPCLink|$NpcName}}. 0/$CraftLeveItemQty";
                } elseif ($ItemVowel == "1" && $CraftLeveItemQty == "1") {
                    $TradecraftObjective = "\n|Objectives =\n*Deliver an [[$Item|$ItemSingle]] to {{NPCLink|$NpcName}}. 0/$CraftLeveItemQty";
                }
            } elseif ($levetype == "Battlecraft") {
                foreach(range(0,7) as $i) {
                	//ToDo Items involved
                	//at the moment im just overwriting 7 times over which is stupid, ideally i want to make an array and then implode it but array just isn't working for me today.
                    if ($BattleLeveCsv->at($leve['DataId'])["Objective[0]"] == 2) {
                    	$objectiveItemTodoName = ucwords(strtolower($EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[0]"])['Singular']));
                    	$objectiveItemTodoQty = $BattleLeveCsv->at($leve['DataId'])["ToDoParam[0][0]"];
                    	$objectiveItemTodo = "\n*". $objectiveItemTodoName ." 0/". $objectiveItemTodoQty ."";
                    	//var_dump($objectiveItemTodo);
                    }
                    if ($BattleLeveCsv->at($leve['DataId'])["BNpcName[$i]"] > 1) {
                        $BCToDoNumber = false;
                        $BCItemsInvolved = false;
                        $BCItemQTY = false;
                        $BCItemDropRate = false;
                        $ItemIF = false;
                        $ItemIFSingular = false;

                        $TargetNumber = ($TargetNumber + 1);
                        $BNpcName = "|Target " . $TargetNumber . " Name     = " . ucwords(strtolower($BNpcNameCsv->at($BattleLeveCsv->at($leve['DataId'])["BNpcName[$i]"])['Singular']));
                        $MobsInvolved = ucwords(strtolower($BNpcNameCsv->at($BattleLeveCsv->at($leve['DataId'])["BNpcName[$i]"])['Singular']));

                        $MobsInvolvedArr[] = $MobsInvolved;

                        //Data per monster
                        //$BCTime = "\n|Target " . $TargetNumber . " Time     = " . $BattleLeveCsv->at($leve['DataId'])["Time[$i]"];
                        //$BCBaseID = "\n|Target " . $TargetNumber . " ID       = " . $BattleLeveCsv->at($leve['DataId'])["BaseID[$i]"];
                        $BCLevel = "\n|Target " . $TargetNumber . " Level    = " . $BattleLeveCsv->at($leve['DataId'])["EnemyLevel[$i]"];
                        if (!empty($EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$i]"])['Name'])) {
                            $BCItemsInvolved = "\n|Target " . $TargetNumber . " Drops    = " . $EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$i]"])['Name'];
                            $BCItemQTY = "\n|Target " . $TargetNumber . " QTY      = " . $BattleLeveCsv->at($leve['DataId'])["ItemsInvolvedQty[$i]"];
                            $BCItemDropRate = "\n|Target " . $TargetNumber . " DropRate = " . $BattleLeveCsv->at($leve['DataId'])["ItemDropRate[$i]"] ."%";
                        }

                        if ($BattleLeveCsv->at($leve['DataId'])["ToDoNumberInvolved[$i]"] > 0) {
                            $BCToDoNumber = "\n|Target " . $TargetNumber . " Required Amount = " . $BattleLeveCsv->at($leve['DataId'])["ToDoNumberInvolved[$i]"];
                            //$BCToDoParam = "\n|Target " . $TargetNumber . " Param    = " . $BattleLeveCsv->at($leve['DataId'])["ToDoParam[$i]"];
                        }

                        //$MobInvolvement[] = $BNpcName;
                        $BNpcNameObjective = ucwords(strtolower($BNpcNameCsv->at($BattleLeveCsv->at($leve['DataId'])["BNpcName[0]"])['Singular']));

                        $ObjectiveText = $LeveStringCsv->at($BattleLeveCsv->at($leve['DataId'])["Objective[0]"])['Objective'];
                        //$ObjectiveText2 = $LeveStringCsv->at($BattleLeveCsv->at($leve['DataId'])["Objective[1]"])['Objective'];

                         // THIS is where i was working on the "replace SE text to displace correctly" stuff but its a mess
                        foreach(range(0,7) as $j) {
                            //this empty check needs to be here otherwise the pacify/beckon/true form objectives default
                            //to empty (since [7] is the last one checked, and the game only uses [0],[1],[2] for items
                            //involved right now (could change in future), so even though 0-7 are available and 7 is
                            //empty/0 so that's what gets used instead of the true value we're looking for.)
                            if (empty($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$j]"])) continue;
                            else {
                                $ItemIF = $EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$j]"])['Name'];
                                $ItemIFSingular = $EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$j]"])['Singular'];
                            }
                        }

                        $ObjectiveTextKey = $BattleLeveCsv->at($leve['DataId'])["Objective[0]"];
                        if ($ObjectiveTextKey == 5) {
                            if (!empty($ItemIF)) {
                                $ObjectiveText = "Weaken target and then pacify it using the [[". $ItemIF ."|". $ItemIFSingular ."]].";
                            } else {
                                $ObjectiveText = "Weaken target and then pacify it using the /soothe emote.";
                            }
                        } elseif ($ObjectiveTextKey == 6) {
                            if (!empty($ItemIF)) {
                                $ObjectiveText = "Use the [[". $ItemIF ."|". $ItemIFSingular ."]] to reveal target's true form, then defeat it.";
                            } else  {
                                $ObjectiveText = "Attack target to reveal its true form, then defeat it.";
                            }
                        } elseif ($ObjectiveTextKey == 9) {
                            $ObjectiveText = "Use the /beckon emote to lead [[". $BNpcNameObjective ."]] safely to the specified location.";
                        }

                        $ObjectiveTask = "\n|Objectives =\n*" . $ObjectiveText ."\n";
                        if ($BattleLeveCsv->at($leve['DataId'])["ToDoNumberInvolved[$i]"] > 0) {
                            $MobObjectiveList[] = "**" . $MobsInvolved . ": 0/" . $BattleLeveCsv->at($leve['DataId'])["ToDoNumberInvolved[$i]"];
                        } //elseif ($BattleLeveCsv->at($leve['DataId'])["ToDoNumberInvolved[$i]"] == 0) {
                          //  $MobObjectiveList[] = "**" . $EventItemCsv->at($BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$i]"])['Name'] . ": 0/" . $BattleLeveCsv->at($leve['DataId'])["ToDoParam[$i]"] . "";
                        //}

                        //$BattleObjective = "\n|Objectives = \n*" . $ObjectiveText;

                        //i have an issue here where i need to put $MobObjectiveList AFTER [0] but before the next line. if you have any ideas let me know
                        //  fixed
                        //$InvolvementObjective[0] = $BattleObjective;
                        $InvolvementObjective[0] = "\n";
                        $InvolvementObjective[] = "" . $BNpcName . "" . $BCLevel . "" . $BCItemsInvolved . "" . $BCItemQTY . "" . $BCItemDropRate . "" . $BCToDoNumber ."";
                    }
                }
            }
            elseif ($levetype == "Fieldcraft") {
                $NpcName = $ENpcResidentCsv->at($LevelCsv->at($leve['Level{Levemete}'])['Object'])['Singular'];

                // Fishing leves are literally crafting leves, so copying modified code here from Tradecraft section above
                if ($classes == "Fisher") {
                    $FieldLeveItem = $CraftLeveCsv->at($leve['DataId'])['Item[0]'];
                    $FieldLeveItemQty = $CraftLeveCsv->at($leve['DataId'])['ItemCount[0]'];
                    $ItemSingle = $ItemCsv->at($FieldLeveItem)['Singular'];
                    $ItemPlural = $ItemCsv->at($FieldLeveItem)['Plural'];
                    $ItemVowel = $ItemCsv->at($FieldLeveItem)['StartsWithVowel'];
                    $Item = $ItemCsv->at($FieldLeveItem)['Name'];
                    if ($FieldLeveItemQty > 1) {
                        $FieldcraftObjective = "\n|Objectives =\n*Deliver [[$Item|$ItemPlural]] to {{NPCLink|$NpcName}}. 0/$FieldLeveItemQty";
                    } elseif ($ItemVowel == "0" && $FieldLeveItemQty == "1") {
                        $FieldcraftObjective = "\n|Objectives =\n*Deliver a [[$Item|$ItemSingle]] to {{NPCLink|$NpcName}}. 0/$FieldLeveItemQty";
                    } elseif ($ItemVowel == "1" && $FieldLeveItemQty == "1") {
                        $FieldcraftObjective = "\n|Objectives =\n*Deliver an [[$Item|$ItemSingle]] to {{NPCLink|$NpcName}}. 0/$FieldLeveItemQty";
                    }
                } else {
                    // If the required item in GatheringLeve.csv is not empty
                    if (!empty($GatheringLeveCsv->at($leve['DataId'])['RequiredItem[0]'])) {
                        $FieldLeveItem = $GatheringLeveCsv->at($leve['DataId'])['RequiredItem[0]'];
                        $FieldLeveItemQty = $GatheringLeveCsv->at($leve['DataId'])['RequiredItemQty[0]'];
                        $Item = $EventItemCsv->at($FieldLeveItem)['Name'];
                    } else {
                        $gatheringItemArray = [];
                        foreach(range(0,3) as $routes) {
                        	foreach(range(0,11) as $points) {
                        		foreach(range(0,7) as $pointsItems) {
                        			if (!empty($EventItemCsv->at($GatheringItemCsv->at($GatheringPointBaseCsv->at($GatheringPointCsv->at($GatheringLeveRouteCsv->at($GatheringLeveCsv->at($leve['DataId'])["Route[$routes]"])["GatheringPoint[$points]"])['GatheringPointBase'])["Item[$pointsItems]"])['Item'])['Name']))
                        			$gatheringItemArray[] = $EventItemCsv->at($GatheringItemCsv->at($GatheringPointBaseCsv->at($GatheringPointCsv->at($GatheringLeveRouteCsv->at($GatheringLeveCsv->at($leve['DataId'])["Route[$routes]"])["GatheringPoint[$points]"])['GatheringPointBase'])["Item[$pointsItems]"])['Item'])['Name'];
                        		}
                        	}
                        }
                        $gatheringItems = implode(", ", array_unique($gatheringItemArray));
                        $Item = $gatheringItems;
                    }
                    $ObjectiveString = $LeveStringCsv->at($GatheringLeveCsv->at($leve['DataId'])["Objective[0]"])["Objective"];
                    $ObjectiveString2 = $LeveStringCsv->at($GatheringLeveCsv->at($leve['DataId'])["Objective[1]"])["Objective"];
                    if (empty($ObjectiveString2)) {
                        // if the objective of the gathering leve is to obtain multiples of a single item
                        if ($GatheringLeveCsv->at($leve['DataId'])["Objective[0]"] == 11 || $GatheringLeveCsv->at($leve['DataId'])["Objective[0]"] == 12) {
                            $FieldcraftObjective = "\n|Objectives = \n*$ObjectiveString\n**$Item: 0/$FieldLeveItemQty";
                        } else {
                            // otherwise, objective should be an evaluation leve so go ahead and display the whole thing
                            $FieldcraftObjective = "\n|Objectives = $ObjectiveString";
                        }

                    } elseif (empty($ObjectiveString)) {
                        $FieldcraftObjective = false;
                    } elseif (!empty($ObjectiveString2)) {
                        $FieldcraftObjective = "\n|Objectives = ". $ObjectiveString ."\n". $ObjectiveString2;
                    }
                }

            //maps for fieldleve
                /*commenting out for now since this isn't used anywhere
                foreach (range(0,3) as $c) { // 4 of GatheringLeve
                    $GatheringLeveNumber = ($GatheringLeveNumber + 1);
                    foreach (range(0,11) as $s) { //12 of LeveRoute
                        $RouteNumber = ($RouteNumber + 1);
                        $X = $LevelCsv->at($GatheringLeveRouteCsv->at($GatheringLeveCsv->at($leve["DataId"])["Route[$c]"])["PopRange[$s]"])["X"];
                        $Y = $LevelCsv->at($GatheringLeveRouteCsv->at($GatheringLeveCsv->at($leve["DataId"])["Route[$c]"])["PopRange[$s]"])["Z"];
                        //$route = $GatheringLeveCsv->at($leve["DataId"])["Route[$c]"];
                        //$PopRangeRoute = $GatheringLeveRouteCsv->at($route)["PopRange[$s]"];

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

                        $PopRange = "{{Superimpose2\n| border = \n| collapse = \n| base = ". $PopRangeBase
                            ."\n| base_width = 1024px\n| base_style = float: left\n| base_alt = PopRange\n| base_caption =\n| base_link =\n\n";
                        $PopRange2 = "| float". $RouteNumber ." = Map19_Icon.png\n| float". $RouteNumber ."_width = 36px\n| float". $RouteNumber
                            ."_alt = ". $RouteNumber ."\n| float". $RouteNumber ."_caption =\n| link". $RouteNumber ." =\n| x". $RouteNumber ." = ".
                            $PixelX ."\n| y". $RouteNumber ." = ". $PixelY ."\n| t". $RouteNumber ." =";
                        $FieldLeveMap[0] = "". $PopRange ."\n";
                        $FieldLeveMap[] = "". $PopRange2 ."\n";
                    }
                }
                */
            }
            ////Guild Leve Output
            //if ($leve["JournalGenre"] == 200 || $leve["JournalGenre"] == 201 || $leve["JournalGenre"] == 202) {
            //    $CompanyLeveLink = $leve["DataId"];
            //    //report to ?
//
            //    //objectives
            //    //switch for stringfix
            //    $GCRuleParamInterger = $CompanyLeveCsv->at($CompanyLeveLink)['RuleParam'];
            //    switch ($GCRuleParamInterger) {
            //        case 1:
            //            $GCRuleParam = "/poke";
            //        break;
            //        case 2:
            //            $GCRuleParam = "/doubt";
            //        break;
            //    }
            //    //im keeping this very simple and open incase we ever need to modify it.
            //    if ($CompanyLeveCsv->at($CompanyLeveLink)['Rule'] == 1) {
            //        $GCRuleObjective = $CompanyLeveCsv->at($CompanyLeveLink)['Rule']
            //        $GCRuleBNpcName = ucwords($BNpcNameCsv->at($CompanyLeveCsv->at($CompanyLeveLink)['BNpcName[0]'])['Singular']);
            //        $GCToDoParam01 = ucwords($BNpcNameCsv->at($CompanyLeveCsv->at($CompanyLeveLink)['ToDoParam[0][1]'])['Singular']);
            //        $GCRuleHelp = "*Objective failed if all ". $GCToDoParam01 ." are lost.";
            //        foreach(range(0,7) as $n) {
            //            if (!empty($BNpcNameCsv->at($CompanyLeveCsv->at($CompanyLeveLink)["BNpcName[$n]"])['Singular'])){
            //                $GCTargets = ucwords($BNpcNameCsv->at($CompanyLeveCsv->at($CompanyLeveLink)["BNpcName[$n]"])['Singular']);
            //                $GCTargetsAmount = //?????????????????
            //            }
            //        }
            //    }
            //    if ($CompanyLeveCsv->at($CompanyLeveLink)['Rule'] == 3) {
            //        $GCRuleEventItem = $EventItemCsv->at($CompanyLeveCsv->at($CompanyLeveLink)['ItemsInvolved[0]'])['Plural'];
            //        $GCRuleObjective = "*Discover who is carrying the ". $GCRuleEventItem ." and slay the target.";
            //        $GCRuleHelp = "*Slay enemies that respond to the ". $GCRuleParam ." emote and collect the ". $GCRuleEventItem .".";
            //        $GCToDoParam00 = $CompanyLeveCsv->at($CompanyLeveLink)['ToDoParam[0][0]'];
            //        $GCObjective = "*". $GCRuleBNpcName .": 0/". $GCToDoParam00 ."";
            //    }
//
            //    $GCObjectivesString = "|Objectives=\n". $GCReportTo ."\n". $GCRuleObjective ."\n". $GCObjective ."\n". $GCRuleHelp ."";
            //    //items involved
            //    //npcs involved
            //    //mobs involved
//
            //}


            //check to see if there's a "start" before moving to levemete
            $LevelMeteStart = $leve['Level{Levemete}'];
            $LevelStart = $leve['Level{Start}'];
            if ($LevelStart !== "0") {
                $LevelMete = $LevelStart;
            } elseif ($LevelStart == "0") {
                $LevelMete = $LevelMeteStart;
            }
            $LevelX = $LevelCsv->at($LevelMete)['X']; //Raw X
            $LevelY = $LevelCsv->at($LevelMete)['Z']; //Raw Y

            //Get the zone id and place names
            $LevelTeri = $LevelCsv->at($LevelMete)['Territory'];
            $LevelTeriZoneID = $TerritoryTypeCsv->at($LevelTeri)['Name']; //Zone ID
            $LevelTeriPlaceName = $PlaceNameCsv->at($TerritoryTypeCsv->at($LevelTeri)['PlaceName'])['Name']; //PlaceName
            $LevelTeriString = "\n|levelTeri = ". $LevelTeri;
            $LevelTeriString .= "\n|ZoneID       = ". $LevelTeriZoneID;
            $LevelTeriString .= "\n|PlaceName       = ". $LevelTeriPlaceName;
            $PlaceNameStart = $PlaceNameCsv->at($leve['PlaceName{Start}'])['Name'];

            $LevelObject = $LevelCsv->at($LevelMete)['Object'];
            $ObjectName = ucwords(strtolower($ENpcResidentCsv->at($LevelObject)['Singular']));

            $Map[] = "\n|LeveMeteID = ". $LevelMete ."\n|levelX = ". $LevelX ."\n|levelY = ". $LevelY ."\n". $LevelTeriString ."|levelObject = ". $LevelObject ."\n|ENpcName = ". $ObjectName;

            //images (super impose and header image)
            //added sprintf("%06d", ....) which will always keep it a 6 digit number with 0 in front for icons
            $VFXOuter = "\n\n|Frame = ". sprintf("%06d", $LeveVfxCsv->at($leve['LeveVfx{Frame}'])['Icon']) .".png";
            $VFXInner = "\n|Image = ". sprintf("%06d", $LeveVfxCsv->at($leve['LeveVfx'])['Icon']) .".png";
            $VFXTown = "\n|Town  = ". sprintf("%06d", $leve['Icon{CityState}']) .".png";
            $VFXImage = "". $VFXOuter ."". $VFXInner ."". $VFXTown;

            //header image
            $Header = $leve['Icon{Issuer}'];

            //$MobInvolvement = array_unique($MobInvolvement);
            //$MobInvolvement = implode(", ", $MobInvolvement);
            $MobsInvolvedArr = array_unique($MobsInvolvedArr);
            $MobsInvolvedArr = implode(", ", $MobsInvolvedArr);
            //$MobsInvolvedArr = substr($MobsInvolvedArr, 0, -2);
            $InvolvementObjective = array_unique($InvolvementObjective);
            $MobObjective = implode("\n", $InvolvementObjective);
            $MobObjectiveList = implode("\n", $MobObjectiveList);
            $RewardItem = implode("\n", $RewardItem);
            $Map = implode("", $Map);
            $FieldLeveMap = implode("", $FieldLeveMap);

            //NOTES TO DO:
            //are we doing CompanyLeve?
            //make it fit for wiki template

            //levequest header image copying code.
            /*
            if (!empty($Header)) {
                if (!file_exists($this->getOutputFolder() . "/questheadericons/{$Header}.png")) {
                    // ensure output directory exists
                    $QuestIconOutputDirectory = $this->getOutputFolder() . '/levequestheaderpics';
                    if (!is_dir($QuestIconOutputDirectory)) {
                        mkdir($QuestIconOutputDirectory, 0777, true);
                    }

                    // build icon input folder paths
                    $levequestIcon = $this->getInputFolder() . '/icon/' . $this->iconizeHR($Header);

                    $levequesticonFileName = "{$QuestIconOutputDirectory}/{$Header}.png";

                    // if icon exists, then skip
                    if (file_exists($levequesticonFileName)) continue;

                    // inform console what item we're copying
                    $this->io->text("Ability: <comment>{$leve['Name']}</comment>");
                    $this->io->text(
                    sprintf('- copy <info>%s</info> to <info>%s</info>', $levequestIcon, $levequesticonFileName));

                    // copy the input icon to the output filename
                    copy($levequestIcon, $levequesticonFileName);
                }
            }*/

            // Save some data
            $data = [
                '{Top}' => $Top,
                '{patch}' => $Patch,
                '{index}' => $leve['id'],
                '{name}' => $leve['Name'],
                '{level}' => $leve['ClassJobLevel'],
                '{guildtype}' => $guildtype[$leve['LeveVfx']],
                '{duration}' => ($leve['TimeLimit'] > 0) ? "\n|Leve Duration      = ". $leve['TimeLimit'] : "",
                '{levetype}' => $levetype,
                '{seals}' => $grandcompany ? "\n|SealsReward =  <!-- Raw number, no commas. Delete if not needed -->" : "",
                '{grandcompany}' => $grandcompany ? "\n|Grand Company      = ". $grandcompany : "",
                '{classes}' => $classes,
                '{trdobjective}' => $TradecraftObjective,
                '{fldobjective}' => $FieldcraftObjective,
                '{Btlobjective}' => $BattleObjective,
                '{mobobjective}' => $MobObjective,
                '{description}' => "\n\n|Description = ". $leve['Description'],
                '{exp}' => ($leve['ExpReward'] > 0) ? $leve['ExpReward'] : "{{Information Needed}}",
                '{gil}' => ($leve['GilReward'] > 0) ? $leve['GilReward'] : "{{Information Needed}}",
                '{npc}' => "\n\n|Issuing NPC = $Npc",
                '{client}' => $LeveClientCsv->at($leve['LeveClient'])['Name'],
                '{npcinvolve}' => $NpcName,
                '{mobinvolve}' => $MobsInvolvedArr ? "\n|Mobs Involved  = $MobsInvolvedArr\n|Wanted Target  = <!-- Usually found during Battlecraft leves -->" : "",
                '{mobobjectivelist}' => $MobObjectiveList,
                '{item}' => $Item,
                '{Bottom}' => $Bottom,
                '{reward}' => $RewardItem,
                '{Map}' => $Map,
                '{location}' => $PlaceNameStart,
                '{header}' => $Header,
                '{FieldLeveMap}' => $FieldLeveMap,
                '{card}' => $VFXImage,
                '{turnins}' => $MoreTradein,
                '{objectiveItemTodo}' => $objectiveItemTodo,
                '{ObjectiveTask}' => $ObjectiveTask,
                //'{db}' => $db,
                //'{wanted}' => ($levetype == "Battlecraft") ? "\n|Wanted Target  = <!-- Usually found during Battlecraft leves -->" : "",
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeLeveWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Leves.txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
