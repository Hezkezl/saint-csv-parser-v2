<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
//use Symfony\Component\Console\Helper\ProgressBar;
/**
 * php bin/console app:parse:csv GE:Quests
 */
class Quests implements ParseInterface
{
    use CsvParseTrait;

    //the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
'''{name}'''
        {{ARR Infobox Quest
        |Patch = {patch}
        |Index = {id}
        |Name = {name}{types}{repeatable}{faction}{eventicon}{reputationrank}
        |Icontype = {questicontype}.png{smallimage}
        |Level = {level}{requiredclass}

        |Required Affiliation =
        |Quest Number = {id}

        |Required Quests ={prevquestspace1}{prevquest2}{prevquest3}{Locks}

        |Objectives =
{objectives}

        |Description = {description}{expreward}{gilreward}{sealsreward}
{tomestones}{relations}{instanceunlock}{questrewards}{catalystrewards}{guaranteeditem7}{guaranteeditem8}{guaranteeditem9}{guaranteeditem11}{questoptionrewards}{trait}
        |Issuing NPC = {questgiver}
        |NPC Location ={npcs}
        |Mobs Involved ={items}

        |Journal =
{journal}
        }}
{{-stop-}}{{-start-}}
'''Loremonger:{name}'''
<noinclude>{{Lorempageturn|prev={prevquest1}{prev2}{prev3}|next=}}{{Loremquestheader|{name}|Mined=X|Summary=}}</noinclude>
{{LoremLoc|Location={location}}}
{dialogue}{battletalk}{{-stop-}}
{{-start-}}
'''{name}/NPCs'''
{npcloc}{npclocend}
{{-stop-}}";

    public function parse()
    {
        include (dirname(__DIR__) .'/Paths.php');

        //grab CSV files
        $questCsv = $this->csv("Quest");
        $ENpcResidentCsv = $this->csv("ENpcResident");
        $ItemCsv = $this->csv("Item");
        $EmoteCsv = $this->csv("Emote");
        $JournalGenreCsv = $this->csv("JournalGenre");
        $JournalCategoryCsv = $this->csv("JournalCategory");
        $JournalSectionCsv = $this->csv("JournalSection");
        $PlaceNameCsv = $this->csv("PlaceName");
        $ClassJobCsv = $this->csv("ClassJob");
        $ActionCsv = $this->csv("Action");
        $GeneralActionCsv = $this->csv("GeneralAction");
        $OtherRewardCsv = $this->csv("QuestRewardOther");
        $BeastReputationRankCsv = $this->csv("BeastReputationRank");
        $BeastTribeCsv = $this->csv("BeastTribe");
        $TraitCsv = $this->csv("Trait");
        $EventIconTypeCsv = $this->csv("EventIconType");
        $KeyItemCsv = $this->csv("EventItem");
        /* unused files
        $InstanceContentCsv = $this->csv("InstanceContent");
        */
        $LevelCsv = $this->csv("Level");
        $MapCsv = $this->csv("Map");
        $paramGrowCsv = $this->csv("ParamGrow");

        $this->io->progressStart($questCsv->total);

        //loop through quest data
        $replacestring = [];
        foreach($questCsv->data as $id => $quest) {
            //---------------------------------------------------------
            $this->io->progressAdvance();

            //skip ones without a name
            if (empty($quest['Name']) || $quest['Name'] === "Testdfghjkl;") {
                continue;
            }

            //---------------------------------------------------------------------------------
            //Actual code definition begins below!
            //---------------------------------------------------------------------------------

            //if the quest name has one of the special symbols then output if its sync or lock
            $SpecialChar = false;
            if (strpos($quest['Name'], " ") !== false) {
                $SpecialChar = "\n|Quest Sync = True";
                $replacestring[] = "". $quest['Name'] ."\n}}". $SpecialChar ."\n}}";
            } elseif (strpos($quest['Name'], " ") !== false) {
                $SpecialChar = "\n|Job Lock = True";
                $replacestring[] = "". $quest['Name'] ."\n}}". $SpecialChar ."\n}}";
            }

            //Grab the correct EventIconType which should then show the correct Icon for a quest
            //(the 'Blue Icon' that appears above an NPC's head, instead of the minimap icon)
            $EventIconType = $EventIconTypeCsv->at($quest['EventIconType'])['NpcIcon{Available}'];
            $EventIconType += $EventIconType ? (($quest['IsRepeatable']) == "False" ? 1 : 2) : 0;
            //$EventIconType = $EventIconTypeCsv->at($quest['EventIconType'])['NpcIcon{Available}'] ? (($quest['IsRepeatable']) == "False" ? 1 : 2) : 0;

            //change Rewarded Tomestone Number to Correct Wiki Parameter/Name
            $tomestoneList = [
                1 => "\n|ARRTomestone = ",
                2 => "\n|TomestoneLow = ",
                3 => "\n|TomestoneHigh = ",
            ];

            //Loop through guaranteed QuestRewards and display the Item Name
            $questRewards = [];
            $RewardNumber = false;
            foreach (range(0, 5) as $i) {
                $guaranteeditemname = $ItemCsv->at($quest["Item{Reward}[0][{$i}]"])['Name'];
                if ($quest["ItemCount{Reward}[0][{$i}]"] > 0) {
                    $RewardNumber = ($RewardNumber + 1);
                    $string = "\n|QuestReward ". $RewardNumber ." = ". $guaranteeditemname;

                    //Show the Qty if more than 1 is received.
                    if ($quest["ItemCount{Reward}[0][{$i}]"] > 1) {
                        $string .= "\n|QuestReward ". $RewardNumber ." Count = ". $quest["ItemCount{Reward}[0][{$i}]"] ."\n";
                    }

                    $questRewards[] = $string;
                }
            }

            $questRewards = implode("\n", $questRewards);

            //Loop through catalyst rewards and display the Item Name as QuestReward #.
            $catalystRewards = [];
            foreach (range(0, 2) as $i) {
                $guaranteedcatalystname = $ItemCsv->at($quest["Item{Catalyst}[{$i}]"])['Name'];
                if ($quest["ItemCount{Catalyst}[{$i}]"] > 0) {
                    $RewardNumber = ($RewardNumber + 1);
                    $string = "\n|QuestReward ". $RewardNumber ." = ". $guaranteedcatalystname;

                    //show Catalyst Qty received if greater than 1
                    if ($quest["ItemCount{Catalyst}[{$i}]"] > 1) {
                        $string .= "\n|QuestReward ". $RewardNumber ." Count = ". $quest["ItemCount{Catalyst}[{$i}]"] ."\n";
                    }

                    $catalystRewards[] = $string;
                }
            }

            $catalystRewards = implode("\n", $catalystRewards);

            //Loop through optional quest rewards and display them, as QuestRewardOption $i.
            //(Does not use the $RewardNumber value, as Optional Rewards have different priority)
            $questoptionRewards = [];
            foreach (range(0, 4) as $i) {
                $optionalitemname = $ItemCsv->at($quest["Item{Reward}[1][{$i}]"])['Name'];

                //if optional item count is greater than zero, show the reward.
                if ($quest["ItemCount{Reward}[1][{$i}]"] > 0) {
                    $string = "\n|QuestRewardOption ". ($i + 1) ." = $optionalitemname";

                    //If Qty is greater than 1, show Qty.
                    if ($quest["ItemCount{Reward}[1][{$i}]"] > 1) {
                        $string .= "\n|QuestRewardOption ". ($i + 1) ." Count = ". $quest["ItemCount{Reward}[1][{$i}]"] ."";
                    }

                    //If reward is HQ, show HQ.
                    if ($quest["IsHQ{Reward}[1][{$i}]"] === "True") {
                        $string .= "\n|QuestRewardOption ". ($i + 1) ." HQ = x";
                    }

                    $questoptionRewards[] = $string;
                }
            }
            $questoptionRewards = implode("\n", $questoptionRewards);

            //If Emote is received as reward, display Emote Name from Emote.csv
            $guaranteedreward7 = false;
            if ($quest['Emote{Reward}']) {
                $RewardNumber = ($RewardNumber + 1);
                $guaranteedreward7 = "\n|QuestReward ". $RewardNumber ." = ". $EmoteCsv->at($quest["Emote{Reward}"])['Name'];
            }

            //If Class/Job Action is rewarded, display Action Name from Action.csv
            $guaranteedreward8 = false;
            if ($quest['Action{Reward}']) {
                $RewardNumber = ($RewardNumber + 1);
                $guaranteedreward8 = "\n|QuestReward ". $RewardNumber ." = ". $ActionCsv->at($quest['Action{Reward}'])['Name'];
            }

            //If General Action is rewarded, display Name from GeneralAction
            $guaranteedreward9 = [];
            foreach (range(0, 1) as $i) {
                if ($quest["GeneralAction{Reward}[{$i}]"] > 0) {
                    $RewardNumber = ($RewardNumber + 1);
                    $guaranteedreward9[] = "\n|QuestReward ". $RewardNumber ." = ".
                        $GeneralActionCsv->at($quest["GeneralAction{Reward}[{$i}]"])['Name'];
                }
            }

            $guaranteedreward9 = implode("\n", $guaranteedreward9);

            //If "Other Reward" is received, then show Other Name from OtherReward.csv
            $guaranteedreward11 = false;
            if ($quest['Other{Reward}']) {
                $RewardNumber = ($RewardNumber + 1);
                $guaranteedreward11 = "\n|QuestReward ". $RewardNumber ." = ".
                    $OtherRewardCsv->at($quest['Other{Reward}'])['Name'];
            }

            $TraitRewardName = false;
            $TraitReward = $TraitCsv->find("Quest", $quest["id"]);
            //if ($TraitReward[0]['id'] > 0) {
            if (isset($TraitReward[0]) && $TraitReward[0]['id'] > 0) {
                $RewardNumber = ($RewardNumber + 1);
                $TraitRewardName = "\n|QuestReward ". $RewardNumber ." = ". $TraitReward[0]['Name'];
            }

            //Convert Event Icon parameter to the appropriate event with a placeholder year (except Heavensturn)
            $eventicon = [
                80101 => "\n|Event = Moonfire Faire (2020)",
                80102 => "\n|Event = Lightning Strikes (2020)",
                80103 => "\n|Event = All Saints' Wake (2020)",
                80104 => "\n|Event = Breaking Brick Mountains (2020)",
                80105 => "\n|Event = The Maiden's Rhapsody (2019)",
                80106 => "\n|Event = Starlight Celebration (2020)",
                80107 => "\n|Event = Heavensturn (2014)",
                80108 => "\n|Event = Valentione's Day (2020)",
                80109 => "\n|Event = Little Ladies' Day (2020)",
                80110 => "\n|Event = Hatching-tide (2020)",
                80112 => "\n|Event = Heavensturn (2015)",
                80113 => "\n|Event = The Rising (2020)",
                80115 => "\n|Event = Heavensturn (2016)",
                80116 => "\n|Event = The Make It Rain Campaign (2020)",
                80117 => "\n|Event = Yo-kai Watch (2020)",
                80118 => "\n|Event = Heavensturn (2017)",
                80119 => "\n|Event = Heavensturn (2018)",
                80120 => "\n|Event = Heavensturn (2019)",
                80121 => "\n|Event = Monster Hunter World",
                80122 => "\n|Event = Heavensturn (2020)",
                80123 => "\n|Event = A Nocturne for Heroes",
                80124 => "\n|Event = Heavensturn (2020)",
                80126 => "\n|Event = UNKNOWN VALENTIONE'S DAY ALTERNATIVE"
            ];

            $instanceunlock = false;
            /* If you unlock a Dungeon during this quest, show the Name.
            commenting out because of the Patch 5.2 change to InstanceContent and the removal of 'Name'
            if ($quest['InstanceContent{Unlock}']) {
                $instanceunlock = "\n|Misc Reward = [[". $InstanceContentCsv->at($quest['InstanceContent{Unlock}'])['Name'] ."]] unlocked.";
            }
            */

            //Need to add the ClassJobLevel[0] value to the LevelOffset value to get the actual level of the quest
            $QuestLevel = ($quest['ClassJobLevel[0]'] + $quest['QuestLevelOffset']);

            //Show EXPReward if more than zero and round it down (if needed) Otherwise, blank it.
            $paramGrow = $paramGrowCsv->at($QuestLevel);
            $QuestEXP = floor(($quest['ExpFactor'] * $paramGrow['ScaledQuestXP'] * $paramGrow['QuestExpModifier']) / 100);
            if ($quest['Level{Max}'] > 0) {
                $paramGrowMaxLevel = $paramGrowCsv->at($quest['Level{Max}']);
                $QuestEXPMaxLevel = floor(($quest['ExpFactor'] * $paramGrowMaxLevel['ScaledQuestXP'] * $paramGrowMaxLevel['QuestExpModifier']) / 100);
                $QuestEXP = "$QuestEXP-$QuestEXPMaxLevel";
            }

            //Stores entire row of JournalGenre in $JournalGenre
            $JournalGenreRow = $JournalGenreCsv->at($quest['JournalGenre']);
            //^^^ 53,61411,29,"La Noscean Sidequests"

            //In Quest.csv, take the raw number from Index:JournalGenre and convert it into an actual Name by
            //looking inside the JournalGenre.csv file and returning the Index:Name for its entry.
            $JournalGenreName = $JournalGenreCsv->at($quest['JournalGenre'])['Name'];
            //^^^ La Noscean Sidequests

            //Stores entire row of JournalGenreCategory in $JournalGenreCategory
            $JournalCategoryRow = $JournalCategoryCsv->at($JournalGenreRow['JournalCategory']);
            //^^^ 29,"Lominsan Sidequests",3,1,3

            //Show the Index # of the JournalCategory.csv file
            //$JournalCategoryNumber = $JournalCategoryCsv->at($JournalGenreRow['JournalCategory'])['id'];
            //^^^ 29

            //Take the same row from $JournalGenreName (JournalGenre.csv) and, using the information found at
            //the 'JournalCategory' index for $JournalGenreName, return the 'Name' index for that number from the
            //JournalCategory.csv file.
            $JournalCategoryName = $JournalCategoryCsv->at($JournalGenreRow['JournalCategory'])['Name'];
            //^^^ Lominsan Sidequests

            //$JournalSectionRow = $JournalSectionCsv->at($JournalCategoryRow['JournalSection']);
            //^^^ 3,"Sidequests",True,True

            //Take the same row from $JournalGenreCategory (JournalCategory.csv) and, using the information found at
            //the 'JournalSection' index for $JournalGenreCategory, return the 'Name' index for that number from the
            //JournalSection.csv file.
            $JournalSectionName = $JournalSectionCsv->at($JournalCategoryRow['JournalSection'])['Name'];
            //^^^ Sidequests

            //Show the Index # of the JournalSection.csv file
            //$JournalSectionNumber = $JournalSectionCsv->at($JournalCategoryRow['JournalSection'])['id'];
            //^^^ 3

            //If SectionName is MSQ, or Chronicles of a New Era, show Section and Type.
            //commenting out MSQ for a test
            //$JournalSectionName === "Main Scenario (ARR/Heavensward/Stormblood)" || $JournalSectionName === "Main Scenario (Shadowbringers)" ||
            if ($JournalSectionName === "Chronicles of a New Era") {
                $types = "\n|Section = $JournalSectionName\n|Type = $JournalCategoryName";

                //Else if $JournalSectionName is Sidequests and $JournalCategoryName is not equal to Side Story Quests
                //show Type, Subtype, and Subtype2 (Placename for SubType2 since all Sidequests except SSQ show it.)
            } elseif ($JournalSectionName === "Sidequests" && $JournalCategoryName != "Side Story Quests") {
                $types = "\n|Type = $JournalSectionName\n|Subtype = $JournalCategoryName\n|Subtype2 = ". $PlaceNameCsv->at($quest['PlaceName'])['Name'];

                //Otherwise, for everything else show Section, Type, and Subtype.
            } else {
                $types = "\n|Section = $JournalSectionName\n|Type = $JournalCategoryName\n|Subtype = $JournalGenreName";
            }

            //Show Repeatable as 'Yes' for instantly repeatable quests, 'Daily' for dailies, 'Weekly', or 'wtf' for unknown
            $repeatable = false;
            if ($quest['IsRepeatable'] === "True") {
                switch ($quest['RepeatIntervalType']) {
                    case 0:
                        $repeatable = "\n|Repeatable = Yes";
                        break;
                    case 1:
                        $repeatable = "\n|Repeatable = Daily";
                        break;
                    case 2:
                        $repeatable = "\n|Repeatable = Weekly";
                        break;
                    default:
                        $repeatable = "UNKNOWN PLEASE FIX";
                        break;
                }
            }

            /*Show the Previous Quest(s) correct Name by looking them up. Also replace any commas in the name with &#44;
            (the "html code" for a comma.) */
            $prevquest1 = str_replace(",", "&#44;", ($questCsv->at($quest['PreviousQuest[0]'])['Name']));
            $prevquestspace1 = str_replace(",", "&#44;", ($questCsv->at($quest['PreviousQuest[0]'])['Name']));
            $prevquest2 = str_replace(",", "&#44;", ($questCsv->at($quest['PreviousQuest[1]'])['Name']));
            $prevquest3 = str_replace(",", "&#44;", ($questCsv->at($quest['PreviousQuest[2]'])['Name']));

            /* Show the names of Required Dungeons to Unlock this quest.
            Commented out as of Patch 5.2 with the InstanceContent name removal
            $InstanceContent1 = $InstanceContentCsv->at($quest['InstanceContent[0]'])['Name'];
            $InstanceContent2 = $InstanceContentCsv->at($quest['InstanceContent[1]'])['Name'];
            $InstanceContent3 = $InstanceContentCsv->at($quest['InstanceContent[2]'])['Name']; */

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

            //Quest Giver Name (All Words In Name Capitalized)
            $questgiver = str_replace($IncorrectNames, $correctnames, ucwords(strtolower($ENpcResidentCsv->at($quest['Issuer{Start}'])['Singular'])));

            //Start Quest Objectives / Journal Entry / Dialogue code
            //Declare variables as false so php program stops giving warnings that they weren't defined
            $description = false;
            $objectives = [];
            $dialogue = [];
            $journal = [];
            $prejournal = [];
            $battletalk = [];
            $system = [];
            $ItemsInvolved = false;
            $NpcsInvolved = false;
            $npcloc = false;

            /*If the Quest ID (NOT the same as id) is not empty, get the first three letters of the string after the
               underscore (_) in its full name, and store it as $folder. ie: "BanNam305_03107" would be: $folder = 031 */
            if (!empty($quest['Id'])) {
                $folder = substr(explode('_', $quest['Id'])[1], 0, 3);
                $textdata = $this->csv("quest/{$folder}/{$quest['Id']}");

                foreach ($textdata->data as $i => $entry) {
                    //grab files to a friendlier variable name
                    //$id = $entry['id'];
                    $command = $entry['unknown_1'];
                    $text = $entry['unknown_2'];

                    //get the text group from the command
                    $textgroup = $this->getTextGroup($i, $command);


                    //---------------------------------------------------------------
                    //Handle quest text data
                    //---------------------------------------------------------------

                    /**
                     * Textgroup provides details on the command type, eg:
                     * type: (npc, question, to do, scene, etc
                     * npc: if "type == dialogue", then npc be the npc name!
                     * order: the entry order, might not need
                     *
                     * Fill up arrays and then you can use something like:
                     *
                     *          implode("\n", $objectives)
                     *
                     * to throw them in your wiki format at the bottom
                     */

                    //add objective
                    if ($textgroup->type == 'todo' && strlen($text) > 1) {
                        $objectives[] = '*'. $text;
                    }

                    //add dialogue
                    if ($textgroup->type == 'dialogue' && strlen($text) > 1) {
                        //example: NPC says: Blah blah blah
                        $dialogue[] = '{{Loremquote|'. $textgroup->npc .'|link=y|'. $text .'}}';
                    }

                    //add journal
                    if ($textgroup->type == 'journal' && strlen($text) > 1) {
                        //$journal[] = '*'.$text;
                        $prejournal[] = '*'. $text;
                    }

                    //add battletalk
                    if ($textgroup->type == 'battle_talk' && strlen($text) > 1) {
                        $battletalk[0] = "\n\n=== Battle Dialogue ===";
                        $battletalk[] = '{{Loremquote|'. $textgroup->npc .'|link=y|'. $text .'}}';
                    }

                    //add system messages
                    if ($textgroup->type == 'system' && strlen($text) > 1) {
                        $system[] = "\n<div>'''". $text ."'''</div>";
                    }
                }

                //do the $quest['Name']/NPCs page for each quest
                $npcloc = [];
                $ItemsInvolved = [];
                $KeyItemsInvolved = [];
                $NpcsInvolved = [];

                //Look up the Quest Scripts looking through the columns "Script{Instruction}[0-49]"
                foreach (range(0, 49) as $i) {
                    if (!empty($quest["Script{Instruction}[$i]"])) {
                        //Look up the Required Items (RITEM[0-5]) using the Name value from ItemCsv
                        foreach (range(0, 5) as $key) {
                            if ($quest["Script{Instruction}[$i]"] == "RITEM{$key}") {
                                $string = $ItemCsv->at($quest["Script{Arg}[$i]"])['Name'];
                                $ItemsInvolved[] = $string;
                            }
                        }

                        //Look up the Required Key Items (ITEM[0-6]) using the Name value from KeyItemCsv
                        foreach (range(0, 6) as $key) {
                            if ($quest["Script{Instruction}[$i]"] == "ITEM{$key}") {
                                $string = $KeyItemCsv->at($quest["Script{Arg}[$i]"])['Name'];
                                $KeyItemsInvolved[] = $string;
                            }
                        }

                        //Look up all of the NPCS Involved (Actor[0-31]) and convert to their proper Singular name.
                        //Also create the questname/NPCs code
                        foreach (range(0, 31) as $key) {
                            if ($quest["Script{Instruction}[$i]"] == "ACTOR{$key}") {
                                if (!empty($ENpcResidentCsv->at($quest["Script{Arg}[$i]"])['Singular'])) {
                                    $npcname = str_replace($IncorrectNames, $correctnames, ucwords(strtolower($ENpcResidentCsv->at($quest["Script{Arg}[$i]"])['Singular'])));
                                    $NpcsInvolved[] = $npcname;
                                    $npcloc[] = "{{QuestNPC|Name=$npcname|ID=". $quest["Script{Arg}[$i]"] ."|Quest=". $quest['Name'] ."}}\n";
                                }
                            }
                        }
                    }
                }

                $ItemsInvolved = implode(", ", $ItemsInvolved);
                $KeyItemsInvolved = implode(", ", $KeyItemsInvolved);

                /* Remove the Quest Giver from the Involvement array, then make sure that there are only unique/
                non-repeating names in NPCsInvolved, then separate each NPC with a comma if there's more than 1 left */
                $NpcsInvolved = implode(", ", array_unique(array_merge(array_diff($NpcsInvolved, array("$questgiver")))));

                //Display Items or Key Items involved (or both) depending on what's needed
                if (!empty($ItemsInvolved)) {
                    if (!empty($KeyItemsInvolved)) {
                        $ItemsInvolved = "\n|Items Involved = $ItemsInvolved, $KeyItemsInvolved";
                    } else {
                        $ItemsInvolved = "\n|Items Involved = $ItemsInvolved";
                    }
                } elseif (!empty($KeyItemsInvolved)) {
                    $ItemsInvolved = "\n|Items Involved = $KeyItemsInvolved";
                } else {
                    $ItemsInvolved = "\n|Items Involved =";
                }

                /* Ternary code for Item Involvement. Above code should be faster.
                $ItemsInvolved = (!empty($ItemsInvolved))
                    ? ((!empty($KeyItemsInvolved))
                        ? "\n|Items Involved = $ItemsInvolved, $KeyItemsInvolved"
                        : "\n|Items Involved = $ItemsInvolved")
                    : ((!empty($KeyItemsInvolved))
                        ? "\n|Items Involved = $KeyItemsInvolved"
                        : "\n|Items Involved =");
                */

                //delete the first array entry from the journal and place it in $description instead
                $description = array_slice($prejournal, 0, 1);
                //create $journal without the first line that was placed in $description
                $journal = array_merge(array_diff($prejournal, $description));
                //remove the asterisk at the beginning of the description (added from prejournal)
                $description = implode("\n", str_replace("*", "", $description));

                //quest header image copying code. Should probably comment this out most of the time with /* before
                //the beginning of the code and put */ after the code for easier commenting, as compared to
                //putting // in front of every line. ie:  */ commented out code here <line breaks etc/everything too> /*


                if (!empty($quest['Icon'])) {
                    if (!file_exists($this->getOutputFolder() ."/$CurrentPatchOutput/QuestHeaderIcons/{$quest['Icon']}.png")) {
                        // ensure output directory exists
                        $QuestIconOutputDirectory = $this->getOutputFolder() ."/$CurrentPatchOutput/QuestHeaderIcons";
                        if (!is_dir($QuestIconOutputDirectory)) {
                            mkdir($QuestIconOutputDirectory, 0777, true);
                        }

                        // build icon input folder paths
                        $questIcon = $this->getInputFolder() .'/icon/'. $this->iconize($quest['Icon']);

                        // if icon doesn't exist (not in the input folder icon list), then skip
                        //if (!file_exists($questIcon)) continue;

                        $questiconFileName = "{$QuestIconOutputDirectory}/{$quest['Icon']}.png";

                        // inform console what item we're copying
                        //$this->io->text("Ability: <comment>{$quest['Name']}</comment>");
                        //$this->io->text(
                        //sprintf('- copy <info>%s</info> to <info>%s</info>', $questIcon, $questiconFileName));

                        // copy the input icon to the output filename
                        copy($questIcon, $questiconFileName);
                    }
                }

            }

            //Define Quest Giver location for Loremonger
            if (!empty($MapCsv->at($LevelCsv->at($quest['Issuer{Location}'])['Map'])['PlaceName{Sub}'])) {
                $Location = $PlaceNameCsv->at($MapCsv->at($LevelCsv->at($quest['Issuer{Location}'])['Map'])['PlaceName{Sub}'])['Name'];
            } else {
                $Location = $PlaceNameCsv->at($MapCsv->at($LevelCsv->at($quest['Issuer{Location}'])['Map'])['PlaceName'])['Name'];
            }

            //---------------------------------------------------------------------------------

            $data = [
                '{patch}' => $Patch,
                '{id}' => $quest['id'],
                '{name}' => $quest['Name'],
                '{types}' => $types,
                '{questicontype}' => $EventIconType,
                '{eventicon}' => $quest['Icon{Special}'] ? $eventicon[$quest['Icon{Special}']] : '',
                '{smallimage}' => ($quest['Icon'] > 0) ? "\n\n|Header Image = ". $quest['Icon'] .".png" : "",
                '{level}' => $QuestLevel,
                '{reputationrank}' => ($quest['BeastReputationRank'] > 0) ? "\n|Required Reputation = ". $BeastReputationRankCsv->at($quest['BeastReputationRank'])['Name'] : "",
                '{repeatable}' => $repeatable,
                '{faction}' => ($quest['BeastTribe']) ? "\n|Faction = ". ucwords(strtolower($BeastTribeCsv->at($quest['BeastTribe'])['Name'])) : "",
                '{requiredclass}' => ($quest['ClassJob{Required}'] > 0) ? "\n\n|Required Class = ". $ClassJobCsv->at($quest['ClassJob{Required}'])['Name{English}'] : "",
                '{prevquestspace1}' => $prevquestspace1 ? " ". $prevquestspace1 : "",
                '{prevquest1}' => $prevquest1 ? $prevquest1 : "",
                '{prevquest2}' => $prevquest2 ? ", ". $prevquest2 : "",
                '{prevquest3}' => $prevquest3 ? ", ". $prevquest3 : "",
                '{prev2}' => (!empty($prevquest2)) ? "|prev2=$prevquest2" : "",
                '{prev3}' => (!empty($prevquest3)) ? "|prev3=$prevquest3" : "",
                '{expreward}' => $QuestEXP ? "\n\n|EXPReward = $QuestEXP" : "\n\n|EXPReward = ",
                '{gilreward}' => ($quest['GilReward'] > 0) ? "\n|GilReward = ". $quest['GilReward'] : "\n|GilReward =",
                '{sealsreward}' => ($quest['GCSeals'] > 0) ? "\n|SealsReward = ". $quest['GCSeals'] : "",
                '{tomestones}' => $quest['TomestoneCount{Reward}'] ? $tomestoneList[$quest['Tomestone{Reward}']] . $quest['TomestoneCount{Reward}'] : '',
                '{relations}' => ($quest['ReputationReward'] > 0) ? "\n|Relations = ". $quest['ReputationReward'] : "",
                '{instanceunlock}' => $instanceunlock,
                '{questrewards}' => $questRewards,
                '{catalystrewards}' => $catalystRewards,
                '{guaranteeditem7}' => $guaranteedreward7,
                '{guaranteeditem8}' => $guaranteedreward8,
                '{guaranteeditem9}' => $guaranteedreward9,
                '{guaranteeditem11}' => $guaranteedreward11,
                '{questoptionrewards}' => $questoptionRewards,
                '{questgiver}' => $questgiver,
                '{journal}' => implode("\n", $journal),
                '{objectives}' => implode("\n", $objectives),
                '{dialogue}' => implode("\n", $dialogue),
                '{battletalk}' => implode("\n", $battletalk),
                '{system}' => implode("\n", $system),
                '{trait}' => $TraitRewardName,
                '{items}' => $ItemsInvolved,
                '{description}' => $description,
                '{npcs}' => "\n\n|NPCs Involved = $NpcsInvolved",
                '{npcloc}' => implode($npcloc),
                '{npclocend}' => (!empty($ENpcResidentCsv->at($quest["Target{End}"])['Singular'])) ? "{{QuestNPC|Name=".
                    str_replace($IncorrectNames, $correctnames, ucwords(strtolower($ENpcResidentCsv->at($quest["Target{End}"])['Singular']))) ."|ID=".
                    $quest["Target{End}"] ."|Quest=". $quest['Name'] ."|Questend=True}}\n" : "",
                '{Locks}' => $SpecialChar,
                /* unused / old code
                '{instancecontent1}' => $InstanceContent1 ? "\n|Dungeon Requirement = ". $InstanceContent1 : "",
                '{instancecontent2}' => $InstanceContent2 ? ", ". $InstanceContent2 : "",
                '{instancecontent3}' => $InstanceContent3 ? ", ". $InstanceContent3 : "",
                '{script}' => $questscripts,
                '{npclocation}' => $NpcLocation,
                */
                '{location}' => $Location,
            ];

            // format using Gamer Escape formatter and add to data array
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeQuestWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("$CurrentPatchOutput/Quests - ". $Patch .".txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }

    /**
     * This is from XIVDB v3.
     * Supports:
     * - BattleTalk
     * - Journal
     * - Scene
     * - Todo (Objectives)
     * - Pop
     * - Access
     * - Instance Talk
     * - Questions + Answers
     * - NPC Dialogue
     * - System
     *
     * @param $i
     * @param $command
     * @return \stdClass
     */
    private function getTextGroup($i, $command)
    {
        $data = new \stdClass();
        $data->type = null;
        $data->npc = null;
        $data->order = null;

        // split command
        $command = explode('_', $command);

        // bug fix for me being fuckin stupid and not updating the /cache/quests/ files on a new patch
        // gives an 'undefined index: 4' error if those aren't updated, due to blank quest info file... whups.
        //if (!isset($command[1])) {
        //    return $data;
        //}

        // special one (npc battle talk)
        if ($command[4] == 'BATTLETALK') {
            $data->type = 'battle_talk';
            $data->npc = ucwords(strtolower($command[3]));
            $data->order = isset($command[5]) ? intval($command[5]) : $i;
            return $data;
        }

        if (isset($command[6]) && ($command[6] == 'BATTLETALK')) {
            $data->type = 'battle_talk';
            $data->npc = ucwords(strtolower($command[5]));
            $data->order = isset($command[7]) ? intval($command[7]) : $i;
            return $data;
        }

        // build data structure from command
        switch($command[3]) {
            case 'SEQ':
                $data->type = 'journal';
                $data->order = intval($command[4]);
                break;

            case 'SCENE':
                $data->type = 'scene';
                $data->order = intval($command[7]);
                break;

            case 'TODO':
                $data->type = 'todo';
                $data->order = intval($command[4]);
                break;

            case 'POP':
                $data->type = 'pop';
                $data->order = $i;
                break;

            case 'ACCESS':
                $data->type = 'access';
                $data->order = $i;
                break;

            case 'INSTANCE':
                $data->type = 'instance_talk';
                $data->order = $i;
                break;

            case 'SYSTEM':
                $data->type = 'system';
                $data->order = $i;
                break;

            case 'QIB':
                $npc = filter_var($command[4], FILTER_SANITIZE_STRING);

                // sometimes QIB can be a todo
                if ($npc == 'TODO' or (isset($command[5])) && ($command[5]) == 'TODO') {
                    $data->type = 'todo';
                    $data->order = $i;
                    break;
                }

                $data->type = 'battle_talk';
                $data->npc = ucwords(strtolower($npc));
                $data->order = $i;
                break;

            default:
                $npc = ucwords(strtolower($command[3]));
                $order = isset($command[5]) ? intval($command[5]) : intval($command[4]);

                // if npc is numeric, budge over 1
                if (is_numeric($npc)) {
                    $npc = ucwords(strtolower($command[4]));
                    $order = intval($command[3]);
                }

                $data->type = 'dialogue';
                $data->npc = $npc;
                $data->order = $order;
        }

        return $data;
    }
}
