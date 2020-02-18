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
| Strengths = {strengths}

| Special Action = {spaction}
| Special Action Description = {spactiondescription}
| Special Action Duration = 
| Special Action Type = {spactiontype}
| Special Action Points = {spactionpoints}
| Special Action Area = {spactionarea} <!-- 0, 30, 120, 360 -->
}}
{Bottom}";
    public function parse()
    {
        $patch = '5.2';
        // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
        // manually, set to false
        $Bot = "true";

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $ItemActionCsv = $this->csv('ItemAction');
        $CompanionCsv = $this->csv('Companion');
        $CompanionMoveCsv = $this->csv('CompanionMove');
        $CompanionTransientCsv = $this->csv('CompanionTransient');
        $MinionRaceCsv = $this->csv('MinionRace');
        $MinionSkillTypeCsv = $this->csv('MinionSkillType');


        //NOTES / PLAN
        //Cycle through Item.csv and continue; on anything other than ItemUICategory = Minion,
        //Link from Item.csv -> ItemAction -> Minion
        //Go from Minion.csv from there on.

        // (optional) start a progress bar
        $this->io->progressStart($ItemCsv->total);

        // loop through data
        foreach ($ItemCsv->data as $id => $Item) {
            $this->io->progressAdvance();
            $ItemName = $Item["Name"];

            // skip all but minions
            if ($Item["ItemUICategory"] !== "81") continue;

            // code starts here

            //first link from item -> Itemaction
            $MinionID = $ItemActionCsv->at($Item['ItemAction'])['Data[0]'];
            $MinionName = $CompanionCsv->at($MinionID)['Singular'];
            //give me a link to transient
            $MinionTransient = $CompanionTransientCsv->at($MinionID);
            //set up the base minion we want to the sheet
            $Minion = $CompanionCsv->at($MinionID);
            $Name = ucwords(strtolower(str_replace(" & ", " and ", $MinionName))); // replace the & character with 'and' in names
            $Description = strip_tags($CompanionTransientCsv->at($MinionID)['Description{Enhanced}']); // strip tags from description
            $Description = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), ' ', $Description); // delete any line breaks in description
            $Quote = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), '<br>', ($CompanionTransientCsv->at($MinionID)['Tooltip'])); // replace line breaks in quote

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
            //SP Angle
            $SPActionArea = $Minion['Skill{Angle}'];
            //SP Cost
            $SPActionPoints = $Minion['Skill{Cost}'];
            //SP Type
            $SPActionType = $MinionSkillTypeCsv->at($MinionTransient['MinionSkillType'])['Name'];

            if ($MinionTransient['HasAreaAttack'] == "True") {
                $AutoAttack = "AoE";
            } elseif ($MinionTransient['HasAreaAttack'] == "False") {
                $AutoAttack = "Single-target";
            }

            //strengths
            if ($MinionTransient['Strength{Gate}'] == "True") {
                $gate = "Gate, ";
            } elseif ($MinionTransient['Strength{Gate}'] == "False") {
                $gate = "";
            }
            if ($MinionTransient['Strength{Eye}'] == "True") {
                $eye = "Eye, ";
            } elseif ($MinionTransient['Strength{Eye}'] == "False") {
                $eye = "";
            }
            if ($MinionTransient['Strength{Shield}'] == "True") {
                $shield = "Shield, ";
            } elseif ($MinionTransient['Strength{Shield}'] == "False") {
                $shield = "";
            }
            if ($MinionTransient['Strength{Arcana}'] == "True") {
                $arcana = "Arcana, ";
            } elseif ($MinionTransient['Strength{Arcana}'] == "False") {
                $arcana = "";
            }

            $Strengths = "". $gate ."". $eye ."". $shield ."". $arcana ."";

            $SmallIcon = $Minion["Icon"];
            $Icon2 = substr($SmallIcon, -3);
            $LargeIcon = str_pad($Icon2, "6", "068", STR_PAD_LEFT);

            // ensure output directory exists
            $IconoutputDirectory = $this->getOutputFolder() . '/MinionIcons';
            // if it doesn't exist, make it
            if (!is_dir($IconoutputDirectory)) {
                mkdir($IconoutputDirectory, 0777, true);
            }

            // build icon input folder paths
            $LargeIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($LargeIcon);
            $SmallIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($Minion["Icon"]);

            // give correct file names to icons for output
            $LargeIconFileName = "{$IconoutputDirectory}/$Name (Minion) Patch.png";
            $SmallIconFileName = "{$IconoutputDirectory}/$Name (Minion) Icon.png";
            // actually copy the icons
            copy($SmallIconPath, $SmallIconFileName);
            if (file_exists($LargeIconPath)) {
                copy($LargeIconPath, $LargeIconFileName);
            };

            // change the top and bottom code depending on if I want to bot the pages up or not. Places Patch on subpage
            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name (Minion)/Patch'''\n$patch\n{{-stop-}}{{-start-}}\n'''$Name (Minion)'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name (Minion)\Patch?action=edit\n$patch\nhttp://ffxiv.gamerescape.com/wiki/$Name (Minion)?action=edit\n";
                $Bottom = "";
            };

            // Save some data
            $data = [
                '{Top}' => $Top,
                '{Name}' => $Name,
                '{Patch}' => $patch,
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
        $info = $this->save('GeMinionWiki - '. $patch .'.txt', 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }

    /**
     * Converts SE icon "number" into a proper path
     */
    private function iconize($number, $hq = false)
    {
        $number = intval($number);
        $extended = (strlen($number) >= 6);

        if ($number == 0) {
            return null;
        }

        // create icon filename
        $icon = $extended ? str_pad($number, 5, "0", STR_PAD_LEFT) : '0' . str_pad($number, 5, "0", STR_PAD_LEFT);

        // create icon path
        $path = [];
        $path[] = $extended ? $icon[0] . $icon[1] . $icon[2] .'000' : '0'. $icon[1] . $icon[2] .'000';

        $path[] = $icon;

        // combine
        $icon = implode('/', $path) .'.png';

        return $icon;
    }
}
