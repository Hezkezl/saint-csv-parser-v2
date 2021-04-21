<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Minions
 */
class Minions implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Top}{{ARR Infobox Minion
| Patch = {patch}
| Name = {name}
| Description = {description}
| Quote = {quote}
| Behavior = {Behaviour}
| Interacts = <!-- Comma delineated array of other minions it interacts with -->
| Acquisition =
| Required Item = {item}
| Notes =

<!-- Lords of Verminion details -->
| Family = {family}
| HP = {hp}
| ATK = {attack}
| DEF = {defense}
| SPD = {speed}
| Cost = {cost}
| Auto-attack = {autoattack}
| Strengths ={strengths}

| Special Action = {spaction}
| Special Action Description = {spactiondescription}
| Special Action Duration ={duration}
| Special Action Type = {spactiontype}
| Special Action Points = {spactionpoints}
| Special Action Area = {spactionarea} <!-- 0, 30, 120, 360 -->
}}
{Bottom}";
    public function parse()
    {
        // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
        // manually, set to false
        $Bot = "true";
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $ItemCsv = $this->csv("Item");
        $ItemActionCsv = $this->csv("ItemAction");
        $CompanionCsv = $this->csv("Companion");
        $CompanionMoveCsv = $this->csv("CompanionMove");
        $CompanionTransientCsv = $this->csv("CompanionTransient");
        $MinionRaceCsv = $this->csv("MinionRace");
        $MinionSkillTypeCsv = $this->csv("MinionSkillType");

        //NOTES / PLAN
        //Cycle through Item.csv and continue; on anything other than ItemUICategory = Minion,
        //Link from Item.csv -> ItemAction -> Minion
        //Go from Minion.csv from there on.

        // (optional) start a progress bar
        $this->io->progressStart($ItemCsv->total);
        
        $this->PatchCheck($Patch, "Item", $ItemCsv);
        $PatchNumber = $this->getPatch("Item");

        // loop through data
        foreach ($ItemCsv->data as $id => $Item) {
            $this->io->progressAdvance();
            $ItemName = $Item["Name"];

            // skip all but minions
            if ($Item["ItemUICategory"] !== "81") continue;
            $Patch = $PatchNumber[$id];

            // code starts here

            //first link from item -> Itemaction
            $MinionID = $ItemActionCsv->at($Item['ItemAction'])['Data[0]'];
            $MinionName = $CompanionCsv->at($MinionID)['Singular'];
            //give me a link to transient
            $MinionTransient = $CompanionTransientCsv->at($MinionID);
            //set up the base minion we want to the sheet
            $Minion = $CompanionCsv->at($MinionID);
            $Name = "".ucwords(strtolower(str_replace(" & ", " and ", $MinionName)))." (Minion)"; // replace the & character with 'and' in names
            $Description = strip_tags($CompanionTransientCsv->at($MinionID)['Description{Enhanced}']); // strip tags from Description
            $Description = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), " ", $Description); // replace line breaks with a space in Description
            $Description = preg_replace("/\s\s+/", " ", $Description); // replace any space that's more than two spaces with a single space in Description
            $Quote = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), " ", ($CompanionTransientCsv->at($MinionID)['Tooltip'])); // replace line breaks with a space in Quote
            $Quote = preg_replace("/\s\s+\-\s*(.*)/", "<br>- [[$1]]", $Quote); // add line break before Quote giver's name and place name in [[Wiki Brackets]]
            $Quote = preg_replace("/\s\s+/", " ", $Quote); // replace any space that's more than two spaces with a single space in Quote

            //behaviour
            $Behaviour = $CompanionMoveCsv->at($Minion['Behavior'])['Name'];
            //family
            $Family = $MinionRaceCsv->at($Minion['MinionRace'])['Name'];
            //hp
            $HP = $Minion['HP'];
            //cost
            $Cost = $Minion['Cost'];
            //Attack
            $Attack = $MinionTransient['Attack'];
            //Defense
            $Defense = $MinionTransient['Defense'];
            //Speed
            $Speed = $MinionTransient['Speed'];

            //SP Name
            $SPAction = $MinionTransient['SpecialAction{Name}'];
            //SP Description
            $SPActionDescription = $MinionTransient['SpecialAction{Description}'];
            $SPActionDescription = preg_replace("/\n.*Duration:<UIGlow>\d+<\/UIGlow><UIForeground>\d+<\/UIForeground>.*\n?/",
                null, $SPActionDescription);

            //Duration removal from Description and add to the | Special Action Duration parameter
            $DurationRemoval = str_replace("<UIForeground>F201F8</UIForeground><UIGlow>F201F9</UIGlow>Duration:<UIGlow>01</UIGlow><UIForeground>01</UIForeground> " ,
                null, $MinionTransient['SpecialAction{Description}']);
            $DurationRemoval = preg_replace("/[A-Za-z<]+.*/", null, $DurationRemoval);
            $DurationRemoval = preg_replace("/\n\s*/", null, $DurationRemoval);
            $Duration = preg_replace("/(\d+)/", " $1s", $DurationRemoval);

            //SP Angle
            $SPActionArea = $Minion['Skill{Angle}'];
            //SP Cost
            $SPActionPoints = $Minion['Skill{Cost}'];
            //SP Type
            $SPActionType = $MinionSkillTypeCsv->at($MinionTransient['MinionSkillType'])['Name'];

            if ($MinionTransient['HasAreaAttack'] == "True") {
                $AutoAttack = "AoE";
            } else {
                $AutoAttack = "Single-target";
            }

            //List of Strengths. Turned into an array so we can implode with comma to eliminate trailing commas
            $Strengths = [];
            if ($MinionTransient['Strength{Gate}'] == "True") {
                $Strengths[0] = " Gate";
            }
            if ($MinionTransient['Strength{Eye}'] == "True") {
                $Strengths[1] = " Eye";
            }
            if ($MinionTransient['Strength{Shield}'] == "True") {
                $Strengths[2] = " Shield";
            }
            if ($MinionTransient['Strength{Arcana}'] == "True") {
                $Strengths[3] = " Arcana";
            }

            $Strengths = implode(",", $Strengths);

            // beginning of Icon copying code
            $SmallIcon = $Minion["Icon"];
            $Icon2 = substr($SmallIcon, -3);
            $LargeIcon = str_pad($Icon2, "6", "068", STR_PAD_LEFT);

            // ensure output directory exists
            $IconoutputDirectory = $this->getOutputFolder() . "/$PatchID/MinionIcons";
            // if it doesn't exist, make it
            if (!is_dir($IconoutputDirectory)) {
                mkdir($IconoutputDirectory, 0777, true);
            }

            // build icon input folder paths
            $LargeIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($LargeIcon);
            $SmallIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($Minion["Icon"]);

            // give correct file names to icons for output
            $LargeIconFileName = "{$IconoutputDirectory}/$Name Patch.png";
            $SmallIconFileName = "{$IconoutputDirectory}/$Name Icon.png";
            // actually copy the icons
            copy($SmallIconPath, $SmallIconFileName);
            if (file_exists($LargeIconPath)) {
                copy($LargeIconPath, $LargeIconFileName);
            };

            // change the top and bottom code depending on if I want to bot the pages up or not. Places Patch on subpage
            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name/Patch'''\n$Patch\n{{-stop-}}{{-start-}}\n'''$Name'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name\Patch?action=edit\n$Patch\nhttp://ffxiv.gamerescape.com/wiki/$Name?action=edit\n";
                $Bottom = "";
            };

            // Save some data
            $data = [
                '{Top}' => $Top,
                '{Name}' => $Name,
                '{Patch}' => $Patch,
                '{Description}' => $Description,
                '{Quote}' => $Quote,
                '{Behaviour}' => $Behaviour,
                '{family}' => $Family,
                '{hp}' => $HP,
                '{cost}' => $Cost,
                '{attack}' => $Attack,
                '{defense}' => $Defense,
                '{speed}' => $Speed,
                '{autoattack}' => $AutoAttack,
                '{strengths}' => $Strengths,
                '{spactionarea}' => $SPActionArea,
                '{spactionpoints}' => $SPActionPoints,
                '{spactiondescription}' => $SPActionDescription,
                '{duration}' => $Duration,
                '{spactiontype}' => $SPActionType,
                '{spaction}' => $SPAction,
                '{item}' => $ItemName,
                '{Bottom}' => $Bottom,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        };

        // save our data to the filename: GeMountWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Minions.txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}