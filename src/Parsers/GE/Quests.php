<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class Quests implements ParseInterface
{
    use CsvParseTrait;

    // the wiki format we shall use
    const WIKI_FORMAT = '
        {{ARR Infobox Quest
        |Patch = {patch}
        |Name = {name}{types}{repeatable}{faction}{eventicon}
        {smallimage}
        |Level = {level}
        {requiredclass}
        |Required Affiliation =
        |Quest Number ={instancecontent1}{instancecontent2}{instancecontent3}

        |Required Quests = {prevquest1}{prevquest2}{prevquest3}
        |Unlocks Quests =

        |Objectives =
        {objectives}
        |Description =
        
        |EXPReward ={gilreward}{sealsreward}
        {tomestones}{relations}{instanceunlock}{questrewards}{catalystrewards}{guaranteeditem7}{guaranteeditem8}{guaranteeditem9}{guaranteeditem10}{guaranteeditem11}{questoptionrewards}
        |Issuing NPC = {questgiver}
        |NPC Location =
        
        |NPCs Involved =
        |Mobs Involved =
        |Items Involved =
        
        |Journal =
        {journal}
        
        |Strategy =
        |Walkthrough =
        |Dialogue =
        |Etymology =
        |Images =
        |Notes =
        }}
        http://ffxiv.gamerescape.com/wiki/Loremonger:{name}?action=edit
        <noinclude>{{Lorempageturn|prev={prevquest1}|next=}}{{Loremquestheader|{name}|Mined=X|Summary=}}</noinclude>
        {{LoremLoc|Location=}}
        {dialogue}';

    public function parse()
    {
        // i should pull this from xivdb :D
        $patch = '4.3';

        // grab CSV files
        $questCsv = $this->csv('Quest');
        $ENpcResidentCsv = $this->csv('ENpcResident');
        $ItemCsv = $this->csv('Item');
        $EmoteCsv = $this->csv('Emote');

        $this->io->progressStart($questCsv->total);

        // loop through quest data
        foreach($questCsv->data as $id => $quest) {
            //print_r(array_keys($quest));die;
            // ---------------------------------------------------------
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($quest['Name'])) {
                continue;
            }
            // ---------------------------------------------------------

            //-------------------------------------------------------------------------------------------------------------------------------------------------------------
            //-------------------------------------------------------------------------------------------------------------------------------------------------------------
            //-------------------------------------------------------------------------------------------------------------------------------------------------------------
            //-------------------------------------------------------------------------------------------------------------------------------------------------------------
            //-------------------------------------------------------------------------------------------------------------------------------------------------------------
            //-------------------------------------------------------------------------------------------------------------------------------------------------------------

            // change tomestone name to wiki switch template depending on name
            // converts number in tomestone_reward to name, then changes name
            $tomestoneList = [
                1 => '|ARRTomestone = ',
                2 => '|TomestoneLow = ',
                3 => '|TomestoneHigh = ',
            ];

            // Loop through guaranteed QuestRewards and display the item
            $questRewards = [];
            foreach(range(0,5) as $i) {
                $guaranteeditemname = $ItemCsv->at($quest["Item{Reward}[0][{$i}]"])['Name'];
                if ($quest["ItemCount{Reward}[0][{$i}]"] > 0) {
                    $string = "\n\n|QuestReward ". ($i+1) ." = ". $guaranteeditemname;

                    if ($quest["ItemCount{Reward}[0][{$i}]"] > 1) {
                        $string .= "\n|QuestReward ". ($i+1) ." Count = ". $quest["ItemCount{Reward}[0][{$i}]"] ."\n";
                    }

                    $questRewards[] = $string;
                }
            }
            $questRewards = implode("\n", $questRewards);

            // Loop through catalyst rewards and display them as QuestReward 6 - QuestReward 8
            $catalystRewards = [];
            foreach(range(0,2) as $i) {
                $guaranteedcatalystname = $ItemCsv->at($quest["Item{Catalyst}[{$i}]"])['Name'];
                if ($quest["ItemCount{Catalyst}[{$i}]"] > 1) {
                    $string = "\n|QuestReward ". (6+$i) ." = ". $guaranteedcatalystname;

                    if ($quest["ItemCount{Catalyst}[{$i}]"] > 1) {
                        $string .= "\n|QuestReward ". (6+$i) ." Count = ". $quest["ItemCount{Catalyst}[{$i}]"] ."\n";
                    }

                    $catalystRewards[] = $string;
                }
            }
            $catalystRewards = implode("\n", $catalystRewards);

            // Loop through optional quest rewards and display them, as QuestRewardOption #
            $questoptionRewards = [];
            foreach(range(0,4) as $i) {
                $optionalitemname = $ItemCsv->at($quest["Item{Reward}[1][{$i}]"])['Name'];
                // if optional item count is greater than zero, show the reward. If count is greater than 1,
                // show the count. If reward is HQ, show HQ. Otherwise do nothing.

                if ($quest["ItemCount{Reward}[1][{$i}]"] > 0) {
                    $string = "\n|QuestRewardOption ". ($i+1) ." = ". $optionalitemname;

                    if ($quest["ItemCount{Reward}[1][{$i}]"] > 1) {
                        $string .= "\n|QuestRewardOption ". ($i+1) ." Count = ". $quest["ItemCount{Reward}[1][{$i}]"] ."\n";
                    }

                    if ($quest["IsHQ{Reward}[1][{$i}]"] == "True") {
                        $string .= "\n|QuestRewardOption ". ($i+1) ." HQ = x\n";
                    }

                    $questoptionRewards[] = $string;
                }
            }
            $questoptionRewards = implode("\n", $questoptionRewards);

            // don't display QuestReward 10 if no "Emote" is rewarded
            $guaranteedreward7 = false;
            if ($quest['Emote{Reward}']) {
                $emoterewardname = $EmoteCsv->at($quest["Emote{Reward}"])['Name'];
                $string = "\n|QuestReward 10 = ". $emoterewardname;
                $guaranteedreward7 = $string;
            }

            // don't display QuestReward 11 if no "Action" is rewarded
            $guaranteedreward8 = false;
            if ($quest['Action{Reward}']) {
                $string = "\n|QuestReward 11 = ". $quest['Action{Reward}'];
                $guaranteedreward8 = $string;
            }

            // don't display QuestReward 12 if no "General Action 0" is rewarded
            $guaranteedreward9 = false;
            if ($quest['GeneralAction{Reward}[0]']) {
                $string = "\n|QuestReward 12 = ". $quest['GeneralAction{Reward}[0]'];
                $guaranteedreward9 = $string;
            }

            // don't display QuestReward 13 if no "General Action 1" is rewarded
            $guaranteedreward10 = false;
            if ($quest['GeneralAction{Reward}[1]']) {
                $string = "\n|QuestReward 13 = ". $quest['GeneralAction{Reward}[1]'];
                $guaranteedreward10 = $string;
            }

            // don't display QuestReward 14 if no "Other Reward" is rewarded
            $guaranteedreward11 = false;
            if ($quest['Other{Reward}']) {
                $string = "\n|QuestReward 14 = ". $quest['Other{Reward}'];
                $guaranteedreward11 = $string;
            }

            // don't display the event icon if it's 0/blank. If it's not, then show it in html comment
            $eventicon = false;
            if ($quest['Icon{Special}'] == 0) {
            } else {
                $string = "\n|Event = <!-- ". $quest['Icon{Special}'] ." -->";
                $eventicon = $string;
            }

            // don't display the "SmallIcon" if it's 0/blank. If it's not, then show it in html comment
            $smallimage = false;
            if ($quest['Icon'] == 0) {
            } else {
                $string = "\n|SmallImage = ". $quest['Name'] ." Image.png <!-- ". $quest['Icon'] ." -->";
                $smallimage = $string;
            }

            // don't display Beast Tribe Faction if "None", otherwise show it
            $faction = false;
            if ($quest['BeastTribe']) {
                $string = "\n|Faction = ". ucwords(strtolower($quest['BeastTribe']));
                $faction = $string;
            }

            // don't display 'Beast Tribe Reputation Required' if equal to "None", otherwise show it
            $reputation = false;
            if ($quest['BeastReputationRank'] == "None") {
            } else {
                $string = "\n|Required Reputation = ". $quest['BeastReputationRank'];
                $reputation = $string;
            }

            // don't display Beast Tribe Relations reward if it's zero
            $relations = false;
            if ($quest['ReputationReward'] > 0) {
                $string = "\n|Relations = ". $quest['ReputationReward'];
                $relations = $string;
            }

            // don't display Misc Reward Dungeon unlock unless one is defined
            $instanceunlock = false;
            if ($quest['InstanceContent{Unlock}']) {
                $string = "\nMisc Reward = [[". $quest['InstanceContent{Unlock}'] ."]] unlocked.";
                $instanceunlock = $string;
            }

            // don't display Grand Company Seal Reward if it's zero
            $sealsreward = false;
            if ($quest['GCSeals'] > 0) {
                $string = "\n|SealsReward = ". $quest['GCSeals'];
                $sealsreward = $string;
            }

            // don't display required class if equal to adventurer
            $requiredclass = false;
            if ($quest['ClassJob{Required}'] == "adventurer") {
            } else {
                $string = "\n|Required Class = ". ucwords(strtolower($quest['ClassJob{Required}']));
                $requiredclass = $string;
            }

            // blank GilReward if equal to 0
            if ($quest['GilReward'] > 0) {
                $string = "\n|GilReward = ". $quest['GilReward'];
                $gilreward = $string;
            } else {
                $string = "\n|GilReward =";
                $gilreward = $string;
            }

            $JournalGenreCsv = $this->csv('JournalGenre');
            $JournalCategoryCsv = $this->csv('JournalCategory');
            $JournalSectionCsv = $this->csv('JournalSection');

            //In Quest.csv, take the raw number from Index:JournalGenre and convert it into an actual Name by
            //looking inside the JournalGenre.csv file and returning the Index:Name for its entry.
            $JournalGenreName = $JournalGenreCsv->at($quest['JournalGenre'])['Name'];
            //^^^ La Noscean Sidequests

            //Stores entire row of JournalGenre in $JournalGenre
            $JournalGenreRow = $JournalGenreCsv->at($quest['JournalGenre']);
            //^^^ 53,61411,29,"La Noscean Sidequests"

            //Take the same row from $JournalGenreName (JournalGenre.csv) and, using the information found at
            //the 'JournalCategory' index for $JournalGenreName, return the 'Name' index for that number from the
            //JournalCategory.csv file.
            $JournalGenreCategoryName = $JournalCategoryCsv->at($JournalGenreRow['JournalCategory'])['Name'];
            //^^^ Lominsan Sidequests

            //Stores entire row of JournalGenreCategory in $JournalGenreCategory
            $JournalGenreCategoryRow = $JournalCategoryCsv->at($JournalGenreRow['JournalCategory']);
            //^^^ 29,"Lominsan Sidequests",3,1,3

            //Take the same row from $JournalGenreCategory (JournalCategory.csv) and, using the information found at
            //the 'JournalSection' index for $JournalGenreCategory, return the 'Name' index for that number from the
            //JournalSection.csv file.
            $JournalSectionName = $JournalSectionCsv->at($JournalGenreCategoryRow['JournalSection'])['Name'];
            //^^^ Sidequests

            $JournalCategoryRow = $JournalSectionCsv->at($JournalGenreCategoryRow['JournalSection']);
            //^^^ 3,"Sidequests",True,True

            echo "JournalGenreName: {$JournalGenreName}\nJournalGenreCategoryName: {$JournalGenreCategoryName}\nJournalSectionName:{$JournalSectionName}\n\n\n\n\n\n";

            //$sectionnumber = $JournalCategoryCsv->at($JournalGenreCategory)['JournalSection'];
            //$section = $JournalSectionCsv->at($sectionnumber)['Name'];

            $section = false;
            // if section = Sidequests, then show Section, Subtype and Subtype2, otherwise show
            // Section, Type, and Subtype (making assumption that Type is obsolete with sidequests
            // due to Type and Subtype being identical in the dats for those)
            // Slight cheat here, forcing Type = Sidequest for Sidequests. We shouldn't do that!
            if ($section == "Sidequests") {
                $string = "\n|Type = Sidequest";
                // Sidequests using Subtype show correct in-game Journal
                // Otherwise they would show things like 'Dravanian Hinterlands Sidequest'
                // instead of 'Dravanian Sidequests'. Saving in code just in case.

                $string .= "\n|Subtype = $JournalGenreName";
                $string .= "\n|Subtype2 = ". $quest['PlaceName'];
                $types = $string;
            } else {
                $string = "\n|Section = $section";
                $string .= "\n|Type = $JournalGenreName";
                $string .= "\n|Subtype = $JournalSectionName";
                $types = $string;
            }

            // Show Repeatable as 'Yes' for instantly repeatable quests, or 'Daily' for dailies, or none
            $repeatable = false;
            if (($quest['IsRepeatable'] == "True") && ($quest['RepeatIntervalType'] == "1")) {
                $string = "\n|Repeatable = Daily";
                $repeatable = $string;
            } elseif (($quest['IsRepeatable'] == "True") && ($quest['RepeatIntervalType'] == "0")) {
                $string = "\n|Repeatable = Yes";
                $repeatable = $string;
            }


            //---------------------------------------------------------------
            // CONVERT RAW DATA TO ACTUAL NAMES
            //---------------------------------------------------------------

            // item reward name
            //$ItemCsv->at($quest['Item{Reward}[0][0]'])['Name'];

            // npc start + finish name
            $questgiver = $ENpcResidentCsv->at($quest['ENpcResident{Start}'])['Singular'];

            // prev quest name
            $questCsv->at($quest['PreviousQuest[0]'])['Name'];


            //-------------------------------------------------------------------------------------------------------------------------------------------------------------


            $data = [
                '{patch}' => $patch,
                '{name}' => $quest['Name'],
                '{types}' => $types,
                //'{questicontype}' => $npcIconAvailable,
                //'{genre}' => $quest['journal_genre,']
                //'{category}' => $genre ? $genre->journal_category : '',
                //'{section}' => $category ? $category->journal_section : '',
                //'{subtype2}' => $quest['place_name,']
                '{eventicon}' => $eventicon,
                '{smallimage}' => $smallimage,
                '{level}' => $quest['ClassJobLevel[0]'],
                '{reputationrank}' => $reputation,
                '{repeatable}' => $repeatable,
                //'{interval}' => $quest['repeat_interval_type,']
                '{faction}' => $faction,
                '{requiredclass}' => $requiredclass,
                '{instancecontent1}' => $quest['InstanceContent[0]'] ? "|Dungeon Requirement = ". $quest['InstanceContent[0]'] : "",
                '{instancecontent2}' => $quest['InstanceContent[1]'] ? ", ". $quest['InstanceContent[1]'] : "",
                '{instancecontent3}' => $quest['InstanceContent[2]'] ? ", ". $quest['InstanceContent[2]'] : "",
                '{prevquest1}' => $quest['PreviousQuest[0]'] ?  $quest['PreviousQuest[0]'] : "",
                '{prevquest2}' => $quest['PreviousQuest[1]'] ? ", ". $quest['PreviousQuest[1]'] : "",
                '{prevquest3}' => $quest['PreviousQuest[2]'] ? ", ". $quest['PreviousQuest[2]'] : "",
                '{gilreward}' => $gilreward,
                '{sealsreward}' => $sealsreward,
                '{tomestones}' => $quest['TomestoneCount{Reward}'] ? $tomestoneList[$quest['Tomestone{Reward}']] . $quest['TomestoneCount{Reward}'] : '',
                '{relations}' => $relations,
                '{instanceunlock}' => $instanceunlock,
                '{questrewards}' => $questRewards,
                '{catalystrewards}' => $catalystRewards,
                '{guaranteeditem7}' => $guaranteedreward7,
                '{guaranteeditem8}' => $guaranteedreward8,
                '{guaranteeditem9}' => $guaranteedreward9,
                '{guaranteeditem10}' => $guaranteedreward10,
                '{guaranteeditem11}' => $guaranteedreward11,
                '{questoptionrewards}' => $questoptionRewards,
                //'{questgiver}' => ucwords(strtolower($quest['ENpcResident{Start}'])),
                '{questgiver}' => $questgiver,
                //'{journal}' => implode("\n", $journal),
                //'{objectives}' => implode("\n",  $objectives),
                //'{dialogue}' => implode("\n", $dialogue),
            ];

            // format using GamerEscape Formater and add to data array
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeQuestWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeQuestWiki.txt');

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
