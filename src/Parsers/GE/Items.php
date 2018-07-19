<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * php bin/console app:parse:csv GE:Items
 */
class Items implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = 'http://ffxiv.gamerescape.com/wiki/{name}?action=edit
        {{ARR Infobox Item
        | Patch = {patch}
        | Index          = {id}
        | Rarity         = {rarity}
        | Name           = {name}
        | Subheading     = {subheading}{description}{slots}
        | Stack          = {stack}{requires}
        | Required Level = {level}
        | Item Level     = {itemlevel}
        | Untradable     = {untradable}
        | Unique         = {unique}{convertible}{sells}{hq}{dyeallowed}{crestallowed}{glamour}{desynthesis}{repair}{physicaldamage}{magicdamage}

        | Defense         = {defense}
        | Defense HQ      = {defensehq}
        | Magic Defense    = {magicdefense}
        | Magic Defense HQ = {magicdefensehq}{blockstrength}{blockrate}
        
        | Auto-attack     = {autoattack}
        | Auto-attack HQ  = {autoattackhq}
        | Delay           = {delay}

        | Bonus Strength    = +{strength}
        | Bonus Strength HQ = +{strengthhq}
        | Bonus Intelligence    = +{intelligence}
        | Bonus Intelligence HQ = +{intelligencehq}
        | Bonus Mind    = +{mind}
        | Bonus Mind HQ = +{mindhq}
        | Bonus Vitality    = +{vitality}
        | Bonus Vitality HQ = +{vitalityhq}
        | Bonus Piety    = +{piety}
        | Bonus Piety HQ = +{pietyhq}
        | Bonus Parry    = +{parry}
        | Bonus Parry HQ = +{parryhq}
        | Bonus Accuracy    = +{accuracy}
        | Bonus Accuracy HQ = +{accuracyhq}
        | Bonus Critical_Hit_Rate    = +{chr}
        | Bonus Critical_Hit_Rate HQ = +{chrhq}
        | Bonus Determination    = +{determination}
        | Bonus Determination HQ = +{determinationhq}
        | Bonus Skill_Speed    = +{skillspeed}
        | Bonus Skill_Speed HQ = +{skillspeedhq}
        | Bonus Accuracy    = +{accuracy}
        | Bonus Accuracy HQ = +{accuracyhq}
        | Bonus Spell_Speed    = +{spellspeed}
        | Bonus Spell_Speed HQ = +{spellspeedhq}
        | Bonus Craftsmanship    = +{craftsmanship}
        | Bonus Craftsmanship HQ = +{craftsmanshiphq}
        | Bonus Control    = +{control}
        | Bonus Control HQ = +{controlhq}
        | Bonus CP    = +{cp}
        | Bonus CP HQ = +{cphq}
        | Bonus Gathering    = +{gathering}
        | Bonus Gathering HQ = +{gatheringhq}
        | Bonus Perception    = +{perception}
        | Bonus Perception HQ = +{perceptionhq}
        | Bonus GP    = +{gp}
        | Bonus GP HQ = +{gphq}

        | Gallery =
        | Notes =
        | Etymology =
        }}';

    public function parse()
    {
        $patch = '4.35';

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $ItemActionCsv = $this->csv('ItemAction');
        $ItemFoodCsv = $this->csv('ItemFood');
        $ItemSearchCategoryCsv = $this->csv('ItemSearchCategory');
        $ItemSeriesCsv = $this->csv('ItemSeries');
        $ItemSpecialBonusCsv  = $this->csv('ItemSpecialBonus');
        $ItemUiCategoryCsv = $this->csv('ItemUICategory');
        $ClassJobCategoryCsv = $this->csv('ClassJobCategory');
        $ClassJobCsv = $this->csv('ClassJob');
        $SalvageCsv = $this->csv('Salvage');

        // (optional) start a progress bar
        $this->io->progressStart($ItemCsv->total);

        // loop through data
        foreach ($ItemCsv->data as $id => $item) {
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($item['Name'])) {
                continue;
            }

            // grab item ui category for this item
            $itemUiCategory = $ItemUiCategoryCsv->at($item['ItemUICategory']);

            // grab the Required Classes needed to equip this item
            $RequiredClasses = false;
            if ($item['ClassJobCategory'] > 0) {
                $RequiredClasses = "\n| Requires       = ". $ClassJobCategoryCsv->at($item['ClassJobCategory'])['Name'];
            }

            // display Materia Slot if greater than 0
            $MateriaSlots = false;
            if ($item['MateriaSlotCount'] > 0) {
                $MateriaSlots = "\n| Slots          = ". $item['MateriaSlotCount'];
            }

            //swap Description if gear is for Male or Female
            //$Description = false;
            //$Gender = false;
            //if ($item['Description'] === "Fits: All ♂") {
                //$Description === null;
                //$Gender = "\n| Gender         = Male";
            //} elseif ($item['Description'] === "Fits: All ♀") {
                //$Description === null;
                //$Gender = "\n| Gender         = Female";
            //} else {
                //$Description = $item['Description'];
            //}

            // change Fits/Gender to wiki-specific parameters
            $Description = false;
            if (!empty($item['Description'])) {
                switch ($item['Description']) {
                    case 'Fits: All ♂':
                        $Description = "\n| Gender         = Male";
                        break;
                    case 'Fits: All ♀':
                        $Description = "\n| Gender         = Female";
                        break;
                    case "Fits: All ♀\nCannot equip gear to hands, legs, and feet.":
                        $Description = "\n| Gender         = Female\n| Other Conditions = Cannot equip gear to hands, legs, and feet.";
                        break;
                    case "Fits: All ♂\nCannot equip gear to head.":
                        $Description = "\n| Gender         = Male\n| Other Conditions = Cannot equip gear to head.";
                        break;
                    case "Fits: All ♂\nCannot equip gear to hands, legs, and feet.":
                        $Description = "\n| Gender         = Male\n| Other Conditions = Cannot equip gear to hands, legs, and feet.";
                        break;
                    case 'Fits: Hyur ♂':
                        $Description = "\n| Fits           = Hyur\n| Gender         = Male";
                        break;
                    case 'Fits: Hyur ♀':
                        $Description = "\n| Fits           = Hyur\n| Gender         = Female";
                        break;
                    case 'Fits: Elezen ♂':
                        $Description = "\n| Fits           = Elezen\n| Gender         = Male";
                        break;
                    case 'Fits: Elezen ♀':
                        $Description = "\n| Fits           = Elezen\n| Gender         = Female";
                        break;
                    case 'Fits: Lalafell ♂':
                        $Description = "\n| Fits           = Lalafell\n| Gender         = Male";
                        break;
                    case 'Fits: Lalafell ♀':
                        $Description = "\n| Fits           = Lalafell\n| Gender         = Female";
                        break;
                    case 'Fits: Miqo\'te ♂':
                        $Description = "\n| Fits           = Miqo\'te\n| Gender         = Male";
                        break;
                    case 'Fits: Miqo\'te ♀':
                        $Description = "\n| Fits           = Miqo\'te\n| Gender         = Female";
                        break;
                    case 'Fits: Roegadyn ♂':
                        $Description = "\n| Fits           = Roegadyn\n| Gender         = Male";
                        break;
                    case 'Fits: Roegadyn ♀':
                        $Description = "\n| Fits           = Roegadyn\n| Gender         = Female";
                        break;
                    case 'Fits: Au Ra ♂':
                        $Description = "\n| Fits           = Au Ra\n| Gender         = Male";
                        break;
                    case 'Fits: Au Ra ♀':
                        $Description = "\n| Fits           = Au Ra\n| Gender         = Female";
                        break;
                    case 'Fits: Game Masters';
                        $Description = "\n| Fits           = Game Masters";
                        break;
                    case NULL:
                        break;
                    default:
                        $Description = "\n| Description    = ". $item['Description'];
                        break;
                }
            }

            // if MaterializeType > 0, then item is Convertible into Materia. If = 0, then not.
            $Convertible = false;
            if ($item['MaterializeType'] > 0) {
                $Convertible = "\n| Convertible    = ". $item['MaterializeType'];
            }

            // if Price{Low} > 0, then Sells = Price{Low}, otherwise Item = Unsellable.
            $Sells = false;
            if ($item['Price{Low}'] > 0) {
                $Sells = "\n| Sells          = ". $item['Price{Low}'];
            } else {
                $Sells = "\n| Sells          = No";
            }

            // if Dye is allowed, display it
            //$DyeAllowed = false;
            //if ($item['IsDyeable'] === "False") {
                //$DyeAllowed = "\n| Dye Allowed    = ". $item['IsDyeable'];
            //} else {
                //$DyeAllowed = "\n| Dye Allowed    = ". $item['IsDyeable'];
            //}

            // if Crest is allowed, display it
            //$CrestAllowed = false;
            //if ($item['IsCrestWorthy'] === False) {
                //$CrestAllowed = "\n| Crest Allowed  = ". $item['IsCrestWorthy'];
            //} else {
                //$CrestAllowed = "\n| Crest Allowed  = ". $item['IsCrestWorthy'];
            //}

            // if Glamourable, display Projectable = Yes
            //$Glamour = false;
            //if ($item['IsGlamourous'] === "False") {
                //$Glamour = "\n| Projectable    = ". $item['IsGlamourous'];
            //} else {
                //$Glamour = "\n| Projectable    = ". $item['IsGlamourous'];
            //}

            // display Desynthesis level if Salvage > 0 and the item can be repaired
            // (if both show up then it means it can be desynthesized. if only one shows up, it can't)
            $Desynthesis = false;
            if ($item['Salvage'] > 0 && $item['ClassJob{Repair}'] > 0) {
                $Desynthesis = "\n| Desynthesizable= Yes\n| Desynth Level  = ". $SalvageCsv->at($item['Salvage'])['OptimalSkill'];
            }

            // display Repair if it is NOT equal to adventurer
            // doesn't work?
            $Repair = false;
            if (!$item['ClassJob{Repair}'] === "adventurer") {
                $Repair = "\n| Repair Class   = ". ucwords(strtolower($ClassJobCsv->at($item['ClassJob{Repair}'])['Name']));
            }

            // display Physical Damage. Also display HQ if the value in Special[0] is > 0 (slight cheat
            // should check if HQ but... fuckit) Double line break needed to separate this out from Repair
            $PhysicalDamage = false;
            if ($item['Damage{Phys}'] > 0 && $item['BaseParamValue{Special}[0]'] > 0) {
                $PhysicalDamageHQ = $item['BaseParamValue{Special}[0]'] + $item['Damage{Phys}'];
                $PhysicalDamage = "\n\n| Physical Damage    = ". $item['Damage{Phys}'] ."\n| Physical Damage HQ = ". $PhysicalDamageHQ;
            } elseif ($item['Damage{Phys}'] > 0 && $item['BaseParamValue{Special}[0]'] == 0) {
                $PhysicalDamage = "\n\n| Physical Damage    = ". $item['Damage{Phys}'];
            }

            // display Magic Damage. Also display HQ if the value in Special[1] is > 0 (slight cheat
            // should check if HQ but... fuckit) No need for double line break since weapons *always*
            // have both physical and magic damage on them.
            $MagicDamage = false;
            if ($item['Damage{Mag}'] > 0 && $item['BaseParamValue{Special}[1]'] > 0) {
                $MagicDamageHQ = $item['BaseParamValue{Special}[1]'] + $item['Damage{Mag}'];
                $MagicDamage = "\n| Magic Damage    = ". $item['Damage{Mag}'] ."\n| Magic Damage HQ = ". $MagicDamageHQ;
            } elseif ($item['Damage{Mag}'] > 0 && $item['BaseParamValue{Special}[1]'] == 0) {
                $MagicDamage = "\n| Magic Damage    = ". $item['Damage{Mag}'];
            }

            // display Block Strength. Also display HQ if the value in Special[1] is > 0 (slight cheat
            // should check if HQ but... fuckit).
            $BlockStrength = false;
            if ($item['Block'] > 0 && $item['BaseParamValue{Special}[1]'] > 0) {
                $BlockStrengthHQ = $item['BaseParamValue{Special}[1]'] + $item['Block'];
                $BlockStrength = "\n\n| Block Strength    = ". $item['BlockRate'] ."\n| Block Strength HQ = ". $BlockStrengthHQ;
            } elseif ($item['Block'] > 0 && $item['BaseParamValue{Special}[1]'] == 0) {
                $BlockStrength = "\n\n| Block Strength = ". $item['Block'];
            }

            // display Block Rate. Also display HQ if the value in Special[0] is > 0 (slight cheat
            // should check if HQ but... fuckit).
            $BlockRate = false;
            if ($item['BlockRate'] > 0 && $item['BaseParamValue{Special}[0]'] > 0) {
                $BlockRateHQ = $item['BaseParamValue{Special}[0]'] + $item['BlockRate'];
                $BlockRate = "\n| Block Rate      = ". $item['BlockRate'] ."\n| Block Rate HQ   = ". $BlockRateHQ;
            } elseif ($item['BlockRate'] > 0 && $item['BaseParamValue{Special}[0]'] == 0) {
                $BlockRate = "\n| Block Rate     = ". $item['BlockRate'];
            }

            // Save some data
            $data = [
                '{patch}' => $patch,
                '{id}' => $item['id'],
                '{rarity}' => $item['Rarity'],
                '{name}' => $item['Name'],
                '{subheading}' => $itemUiCategory['Name'],
                '{description}' => $Description ? $Description : "",
                '{slots}' => $MateriaSlots ? $MateriaSlots : "",
                '{stack}' => $item['StackSize'],
                '{requires}' => $RequiredClasses ? $RequiredClasses : "",
                '{level}' => $item['Level{Equip}'],
                '{itemlevel}' => $item['Level{Item}'],
                '{untradable}' => $item['IsUntradable'],
                '{unique}' => $item['IsUnique'],
                '{convertible}' => $Convertible ? $Convertible : "",
                '{sells}' => $Sells,
                '{hq}' => "\n| HQ             = ". $item['CanBeHq'],
                //'{dyeallowed}' => $DyeAllowed ? $DyeAllowed : "",
                '{dyeallowed}' => "\n| Dye Allowed    = ". $item['IsDyeable'],
                //'{crestallowed}' => $CrestAllowed ? $CrestAllowed : "",
                '{crestallowed}' => "\n| Crest Allowed  = ". $item['IsCrestWorthy'],
                //'{glamour}' => $Glamour ? $Glamour : "",
                '{glamour}' => "\n| Projectable    = ". $item['IsGlamourous'],
                '{desynthesis}' => $Desynthesis,
                '{repair}' => $Repair,
                '{physicaldamage}' => $PhysicalDamage,
                '{magicdamage}' => $MagicDamage,
                '{blockstrength}' => $BlockStrength,
                '{blockrate}' => $BlockRate,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
             $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeItemWiki.txt');

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
