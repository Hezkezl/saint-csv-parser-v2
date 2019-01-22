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
        | Subheading     = {subheading}{description}{slots}{stack}{requires}
        | Required Level = {level}
        | Item Level     = {itemlevel}
        | Untradable     = {untradable}
        | Unique         = {unique}{convertible}{sells}{hq}{dyeallowed}{crestallowed}{glamour}{desynthesis}{repair}{setbonus}{setbonusgc}{sanction}{bonus}{physicaldamage}{magicdamage}{defense}{block}
        }}';

    public function parse()
    {
        $patch = '4.5';

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $ItemActionCsv = $this->csv('ItemAction');
        $ItemFoodCsv = $this->csv('ItemFood');
        $ItemSearchCategoryCsv = $this->csv('ItemSearchCategory');
        $BaseParamCsv = $this->csv('BaseParam');
        $ItemSeriesCsv = $this->csv('ItemSeries');
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
                $ClassNames = $ClassJobCategoryCsv->at($item['ClassJobCategory'])['Name'];
                $ClassNames = preg_replace("/([A-Z]{3})\s/","$1, ",$ClassNames);
                $RequiredClasses = "\n| Requires       = ". $ClassNames;
            }

            // display Materia Slot if greater than 0
            $MateriaSlots = false;
            if ($item['MateriaSlotCount'] > 0) {
                $MateriaSlots = "\n| Slots          = ". $item['MateriaSlotCount'];
            }

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

            // if MaterializeType > 0, then item is Convertible into Materia.
            $Convertible = false;
            if ($item['MaterializeType'] > 0) {
                $Convertible = "\n| Convertible    = True";
            }

            // if Price{Low} > 0, then Sells = Price{Low}, otherwise Item = Unsellable.
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
                $Desynthesis = "\n| Desynthesizable= True\n| Desynth Level  = ". $SalvageCsv->at($item['Salvage'])['OptimalSkill'];
            }

            // display Repair Class if it is NOT equal to "adventurer" or if Item is NOT Seafood, Furniture, Miscellany
            $Repair = false;
            if ($item['ClassJob{Repair}'] == 0 || $item['ItemUICategory'] == 47 || $item['ItemUICategory'] == 65
                || $item['ItemUICategory'] == 66 || $item['ItemUICategory'] == 67 || $item['ItemUICategory'] == 68
                || $item['ItemUICategory'] == 69 || $item['ItemUICategory'] == 70 || $item['ItemUICategory'] == 71
                || $item['ItemUICategory'] == 72 || $item['ItemUICategory'] == 73 || $item['ItemUICategory'] == 74
                || $item['ItemUICategory'] == 75 || $item['ItemUICategory'] == 76 || $item['ItemUICategory'] == 77
                || $item['ItemUICategory'] == 78 || $item['ItemUICategory'] == 79 || $item['ItemUICategory'] == 80
                || $item['ItemUICategory'] == 64 || $item['ItemUICategory'] == 57 || $item['ItemUICategory'] == 61) {
            } else {
                $Repair = "\n| Repair Class   = ". ucwords(strtolower($ClassJobCsv->at($item['ClassJob{Repair}'])['Name']));
            }

            // display Damage. Also display HQ if item is HQ.
            // Double line break needed to separate this out from Repair
            $PhysicalDamage = false;
            $MagicDamage = false;
            if (($item['Damage{Phys}'] > 0 || $item['Damage{Mag}'] > 0) && $item['CanBeHq'] == "True") {
                $Delay = round(($item["Delay<ms>"]/1000),2,PHP_ROUND_HALF_UP);
                $PhysicalDamageHQ = $item['BaseParamValue{Special}[0]'] + $item['Damage{Phys}'];
                $MagicDamageHQ = $item['BaseParamValue{Special}[1]'] + $item['Damage{Mag}'];
                $AutoattackHQ = round((($Delay/3) * $PhysicalDamageHQ),2,PHP_ROUND_HALF_UP);
                $Autoattack = round((($Delay/3) * $item['Damage{Phys}']),2,PHP_ROUND_HALF_UP);
                $PhysicalDamage = "\n\n| Physical Damage    = " . $item['Damage{Phys}'] . "\n| Physical Damage HQ = " . $PhysicalDamageHQ;
                $MagicDamage = "\n| Magic Damage    = ". $item['Damage{Mag}'] ."\n| Magic Damage HQ = ". $MagicDamageHQ;
                $MagicDamage .= "\n| Auto-attack    = ". $Autoattack ."\n| Auto-attack HQ = ". $AutoattackHQ;
                $MagicDamage .= "\n| Delay          = ". $Delay;
            }   elseif (($item['Damage{Phys}'] > 0 || $item['Damage{Mag}'] > 0) && $item['CanBeHq'] == "False") {
                $Delay = round(($item["Delay<ms>"]/1000),2,PHP_ROUND_HALF_UP);
                $Autoattack = round((($Delay/3) * $item['Damage{Phys}']),2,PHP_ROUND_HALF_UP);
                $PhysicalDamage = "\n\n| Physical Damage = ". $item['Damage{Phys}'];
                $MagicDamage = "\n| Magic Damage    = ". $item['Damage{Mag}'];
                $MagicDamage .= "\n| Auto-attack = ". $Autoattack ."\n| Delay       = ". $Delay;
            }

            // display Block stats. Also display HQ stats if item is HQ
            $Block = false;
            if (($item['Block'] > 0 || $item['BlockRate'] > 0) && $item['CanBeHq'] == "True") {
                $BlockStrengthHQ = $item['BaseParamValue{Special}[1]'] + $item['Block'];
                $BlockRateHQ = $item['BaseParamValue{Special}[0]'] + $item['BlockRate'];
                $Block = "\n\n| Block Strength    = ". $item['BlockRate'] ."\n| Block Strength HQ = ". $BlockStrengthHQ;
                $Block .= "\n| Block Rate      = ". $item['BlockRate'] ."\n| Block Rate HQ   = ". $BlockRateHQ;
            } elseif (($item['Block'] > 0 || $item['BlockRate'] > 0) && $item['CanBeHq'] == "False") {
                $Block = "\n\n| Block Strength = ". $item['Block'];
                $Block .= "\n| Block Rate     = ". $item['BlockRate'];
            }

            // display Defense (and Magic Defense) if Def/MagDef is greater than 0, or if subheading is
            // ring, necklace, earring, or bracelets. Also Display Defense/MagDef HQ if item is HQ
            $Defense = false;
            if (($item['ItemUICategory'] == 40 || $item['ItemUICategory'] == 41
                    || $item['ItemUICategory'] == 42 || $item['ItemUICategory'] == 43)
                || ($item['Defense{Phys}'] > 0 || $item['Defense{Mag}'] > 0)
                && $item['CanBeHq'] == "True") {
                $DefenseHQ = ($item['Defense{Phys}'] + $item['BaseParamValue{Special}[0]']);
                $MagicDefenseHQ = ($item['Defense{Mag}'] + $item['BaseParamValue{Special}[1]']);
                $Defense = "\n\n| Defense    = ". $item['Defense{Phys}'] ."\n| Defense HQ = ". $DefenseHQ;
                $Defense .= "\n| Magic Defense    = ". $item['Defense{Mag}'] ."\n| Magic Defense HQ = ". $MagicDefenseHQ;
            } elseif (($item['ItemUICategory'] == 40 || $item['ItemUICategory'] == 41
                    || $item['ItemUICategory'] == 42 || $item['ItemUICategory'] == 43)
                || ($item['Defense{Phys}'] > 0 || $item['Defense{Mag}'] > 0)
                && $item['CanBeHq'] == "False") {
                $Defense = "\n\n| Defense       = ". $item['Defense{Phys}'];
                $Defense .= "\n| Magic Defense = ". $item['Defense{Mag}'];
            }

            // Set Bonus stats for Mog Station gear
            $SetBonus = [];
            if ($item['ItemSpecialBonus'] == 6) {
                $SetBonus[0] = "\n\n| SetBonus Set_Bonus_(Capped):=\n:[[". $ItemSeriesCsv->at($item['ItemSeries'])['Name'] ."]]";
                $SetBonus[1] = ":Active Under Lv. ". $item['ItemSpecialBonus{Param}'];
                foreach(range(0,5) as $i) {
                    if(!empty($item["BaseParam{Special}[$i]"])) {
                        $SetBonus[] .= ":". ($i+2) ." Equipped: [[". $BaseParamCsv->at($item["BaseParam{Special}[$i]"])
                            ['Name'] ."]] +". $item["BaseParamValue{Special}[$i]"];
                    }
                }
            }
            $SetBonus = implode("\n", $SetBonus);

            // Set Bonus stats for Grand Company gear
            $SetBonusGC = [];
            if ($item['ItemSpecialBonus'] == 2) {
                $SetBonusGC[0] = "\n\n| Other Conditions = ". $ItemSeriesCsv->at($item['ItemSeries'])['Name'] ."";
                $SetBonusGC[1] = "| Set              = ". $ItemSeriesCsv->at($item['ItemSeries'])['Name'] ."";
                $SetBonusGC[2] = "| Set Bonus        =<br>";
                foreach(range(0,5) as $i) {
                    if(!empty($item["BaseParam{Special}[$i]"])) {
                        $SetBonusGC[] .= ":". ($i+2) ." Equipped: [[". $BaseParamCsv->at($item["BaseParam{Special}[$i]"])
                            ['Name'] ."]] +". $item["BaseParamValue{Special}[$i]"];
                    }
                }
            }
            $SetBonusGC = implode("\n", $SetBonusGC);

            // Sanction Code (Sanction item gives you bonus on some GC gear)
            $Sanction = [];
            if ($item['ItemSpecialBonus'] == 4) {
                foreach(range(0,5) as $i) {
                    if(!empty($item["BaseParam{Special}[$i]"])) {
                        //$ParamName = $BaseParamCsv->at($item["BaseParam{Special}[$i]"])['Name'];
                        $ParamName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam{Special}[$i]"])['Name']);
                        $Sanction[0] = "\n";
                        $Sanction[] .= "| Latent ". $ParamName ." = +". $item["BaseParamValue{Special}[$i]"];
                        $Sanction[] .= "| Latent ". $ParamName ." Latent = Yes";
                        $Sanction[] .= "| Latent ". $ParamName ." Conditions = Sanction";
                    }
                }
            }
            $Sanction = implode("\n",$Sanction);

            // Bonus Stat Code (attempt)
            $BonusStat = [];
            if ($item['CanBeHq'] == "True" && !empty($item['BaseParam[0]'])) {
                //print($item["Name"]);
                //print "\n";
                foreach (range(0, 5) as $i) {
                    if (!empty($item["BaseParam[$i]"])) {
                        $BonusStatName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam[$i]"])['Name']);
                        $BonusStat[0] ="\n";
                        $BonusStat[] .= "| Bonus " . $BonusStatName . " = +" . $item["BaseParamValue[$i]"];
                        if (!empty($item['BaseParamValue{Special}[' . ($i+2) . ']'])) {
                            $BonusStat[] .= '| Bonus ' . $BonusStatName . ' HQ = +'.
                                ($item["BaseParamValue[$i]"] + $item['BaseParamValue{Special}[' . ($i+2) . ']']);
                        }
                    }
                }
            } elseif ($item['CanBeHq'] == "False" && !empty($item['BaseParam[0]'])) {
                foreach (range(0, 5) as $i) {
                    if (!empty($item["BaseParam[$i]"])) {
                        $BonusStatName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam[$i]"])['Name']);
                        $BonusStat[0] ="\n";
                        $BonusStat[] .= "| Bonus " . $BonusStatName . " = +" . $item["BaseParamValue[$i]"];
                    }
                }
            }
            $BonusStat = implode("\n",$BonusStat);

            // Save some data
            $data = [
                '{patch}' => $patch,
                '{id}' => $item['id'],
                '{rarity}' => $item['Rarity'],
                '{name}' => $item['Name'],
                '{subheading}' => $itemUiCategory['Name'],
                '{description}' => $Description ? $Description : "",
                '{slots}' => $MateriaSlots ? $MateriaSlots : "",
                '{stack}' => ($item['StackSize'] > 1) ? '\n| Stack          = '. $item['StackSize'] : "",
                '{requires}' => $RequiredClasses ? $RequiredClasses : "",
                '{level}' => $item['Level{Equip}'],
                '{itemlevel}' => $item['Level{Item}'],
                '{untradable}' => $item['IsUntradable'],
                '{unique}' => $item['IsUnique'],
                '{convertible}' => $Convertible ? $Convertible : "",
                '{sells}' => $Sells,
                '{hq}' => ($item['Price{Low}'] > 0) ? "\n| HQ             = ". $item['CanBeHq'] : "",
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
                '{block}' => $Block,
                '{defense}' => $Defense,
                '{setbonus}' => $SetBonus,
                '{setbonusgc}' => $SetBonusGC,
                '{sanction}' => $Sanction,
                '{bonus}' => $BonusStat,
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
