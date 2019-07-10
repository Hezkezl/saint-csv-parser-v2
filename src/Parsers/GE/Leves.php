<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:Recipes
 */
class Leves implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
'''{result}'''
{{ARR Infobox Levequest
|Name  = {name}
|Patch = {patch}
|Level = {level}

|Guildleve Type     = {guildtype}
|Levequest Type     = {levetype}
|Leve Duration      = 20m
|Levequest Location ={grandcompany}

<!-- Disciples of Magic, Disciples of War for \"Battlecraft\" leves. The full name of the class
otherwise (Fisher, Botanist, Goldsmith, Alchemist, etc) -->
|Recommended Classes =  {classes}

|Objectives = <!-- Just list them exactly as they appear on screen -->
{objective}

|Description = {description}

|EXPReward = {exp}
|GilReward = ~{gil}
|SealsReward =  <!-- Raw number, no commas. Delete if not needed -->

<!--  If all rewards are single items, just add them below as a comma delineated list  -->
| Levequest Reward List =

<!--  If rewards need count data, add them one at a time below, adding more rows as needed-->
|LevequestReward 1       =  <!-- Item name only -->
|LevequestReward 1 Count =  <!-- Use only if more than 1 -->

<!--  If rewards are conditional, such as only appearing inside of a chest during the leve itself, use these below -->
<!--  Can be combined with the above options. Useful for Amber-encased Vilekin -->
|LevequestRewardOption 1       =  <!-- Item name only -->
|LevequestRewardOption 1 Count =  <!-- Use only if more than 1 -->

|Issuing NPC = {npc}
|Client = {client}

|NPCs Involved  = {npcinvolve} <!-- List of NPCs involved (besides the quest giver,) comma separated-->
|Mobs Involved  =  <!-- List any Mobs who are involved, comma separated-->
|Items Involved = {item} <!-- List any items used, comma separated-->
|Wanted Target  =  <!-- Usually found during Battlecraft leves -->

|Strategy =
|Walkthrough =
|Dialogue =
|Etymology =
|Images =
|Notes =
}}{{-stop-}}";

    public function parse()
    {
        // grab CSV files we want to use
        $LeveCsv = $this->csv('Leve');
        $LeveClientCsv = $this->csv('LeveClient');
        $LeveAssignmentCsv = $this->csv('LeveAssignmentType');
        $ItemCsv = $this->csv('Item');
        $CraftLeveCsv = $this->csv('CraftLeve');
        $GatheringLeveCsv = $this->csv('GatheringLeve');
        $LevelCsv = $this->csv('Level');
        $LeveVfxCsv = $this->csv('LeveVfx');
        $MapCsv = $this->csv('Map');
        $PlaceNameCsv = $this->csv('PlaceName');
        $ClassJobCsv = $this->csv('ClassJobCategory');
        $JournalGenreCsv = $this->csv('JournalGenre');

        // (optional) start a progress bar
        $this->io->progressStart($LeveCsv->total);

        // loop through data
        foreach ($LeveCsv->data as $id => $leve) {
            $this->io->progressAdvance();

            $patch = '5.0';

            // skip ones without a name
            if (empty($leve['Name'])) {
                continue;
            }

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
            if ($leve['DataID'] >= 917054 && $leve['DataID'] <= 920000) {
                $CraftLeveItem = $CraftLeveCsv->at($leve['DataID'])['Item[0]'];
                $CraftLeveItemQty = $CraftLeveCsv->at($leve['DataID'])['ItemCount[0]'];
                $ItemSingle = $CraftLeveItem->at($ItemCsv)['Singular'];
                $ItemPlural = $CraftLeveItem->at($ItemCsv)['Plural'];
                $ItemVowel = $CraftLeveItem->at($ItemCsv)['StartsWithVowel'];
                $Item = $CraftLeveItem->at($ItemCsv)['Name'];
                if (($ItemVowel == 0 || $ItemVowel == 1) && $CraftLeveItemQty > 1) {
                    $Objective = "*Deliver [[$Item|$ItemPlural]] to {{NPCLink|$NpcName}}. 0/$CraftLeveItemQty";
                } elseif ($ItemVowel == 0 && $CraftLeveItemQty == 1) {
                    $Objective = "*Deliver a [[$Item|$ItemPlural]] to {{NPCLink|$NpcName}}. 0/$CraftLeveItemQty";
                } elseif ($ItemVowel == 1 && $CraftLeveItemQty == 1) {
                    $Objective = "*Deliver an [[$Item|$ItemSingle]] to {{NPCLink|$NpcName}}. 0/$CraftLeveItemQty";
                }
            }

                // Save some data
            $data = [
                '{patch}' => $patch,
                '{index}' => $leve['id'],
                '{name}' => $leve['Name'],
                '{level}' => $leve['ClassJobLevel'],
                '{guildtype}' => $guildtype[$leve['LeveVfx']],
                '{levetype}' => $levetype,
                '{grandcompany}' => ($leve['LeveAssignmentType'] == 16 || $leve['LeveAssignmentType'] == 17 || $leve['LeveAssignmentType'] == 18)
                    ? "\n|Grand Company      = ". $grandcompany : "",
                '{classes}' => $classes,
                '{objective}' => ,
                '{description}' => $leve['Description'],
                '{exp}' => ($leve['ExpReward'] > 0) ? $leve['ExpReward'] : "{{Information Needed}}",
                '{gil}' => $leve['GilReward'],
                '{npc}' => ,
                '{client}' => $LeveClientCsv->at($leve['LeveClient'])['Name'],
                '{npcinvolve}' => ,
                '{item}' => ,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeLeveWiki.txt');

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
