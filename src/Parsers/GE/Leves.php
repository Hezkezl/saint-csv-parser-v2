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
|Levequest Location ={grandcompany}

<!-- Disciples of Magic, Disciples of War for \"Battlecraft\" leves. The full name of the class
otherwise (Fisher, Botanist, Goldsmith, Alchemist, etc) -->
|Recommended Classes = {classes}

|Objectives = <!-- Just list them exactly as they appear on screen -->
{objective}{mobobjective}

|Description = {description}

|EXPReward = {exp}
|GilReward = ~{gil}
|SealsReward =  <!-- Raw number, no commas. Delete if not needed -->

<!--  If rewards need count data, add them one at a time below, adding more rows as needed-->
|LevequestReward 1       =  <!-- Item name only -->
|LevequestReward 1 Count =  <!-- Use only if more than 1 -->

<!--  If rewards are conditional, such as only appearing inside of a chest during the leve itself, use these below -->
<!--  Can be combined with the above options. Useful for things like Amber-encased Vilekin -->
|LevequestRewardOption 1       =  <!-- Item name only -->
|LevequestRewardOption 1 Count =  <!-- Use only if more than 1 -->

|Issuing NPC = {npc}
|Client = {client}

|NPCs Involved  = {npcinvolve} <!-- List of NPCs involved (besides the quest giver,) comma separated-->
|Mobs Involved  = {mobinvolve} <!-- List any Mobs who are involved, comma separated-->
|Items Involved = {item} <!-- List any items used, comma separated-->
|Wanted Target  =  <!-- Usually found during Battlecraft leves -->

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
            $Item = false;
            $NpcInvolvement = false;
            $Npc = false;
            $MobInvolvement = [];
            $InvolvementObjective = [];
            $BattlecraftItemsInvolved = [];

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
                    if ($BattleLeveCsv->at($leve['DataId'])["BNpcName[$i]"] > 1 && $BattleLeveCsv->at($leve['DataId'])["ItemsInvolved[$i]"] == "0") {
                        $BNpcName = ucwords(strtolower($BNpcNameCsv->at($BattleLeveCsv->at($leve['DataId'])["BNpcName[$i]"])['Singular']));
                        $MobInvolvement[] = $BNpcName;
                        $InvolvementObjective[0] = "*Defeat target enemies.";
                        $InvolvementObjective[] = $BNpcName;
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
            } // Need to do something for fieldcraft ones, but haven't even begun to think about it yet so commenting out.
            //elseif ($levetype == "Fieldcraft") { }

            $MobInvolvement = array_unique($MobInvolvement);
            $MobInvolvement = implode(", ", $MobInvolvement);
            $InvolvementObjective = array_unique($InvolvementObjective);
            $MobObjective = implode("\n*", $InvolvementObjective);

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
