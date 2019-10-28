<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Items
 */
class Items implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
'''{name}/Patch'''
{patch}
<noinclude>[[Category:Patch Subpages]]</noinclude>
{{-stop-}}{{-start-}}
'''{name}'''
        {{ARR Infobox Item
        | Index          = {id}
        | Rarity         = {rarity}
        | Name           = {name}
        | Subheading     = {subheading}{description}{slots}{advancedmelding}{stack}{requires}
        | Required Level = {level}
        | Item Level     = {itemlevel}{untradable}{unique}{convertible}{sells}{dyeallowed}{crestallowed}{glamour}{desynthesis}{repair}{setbonus}{setbonusgc}{sanction}{bonus}{eureka}{physicaldamage}{magicdamage}{defense}{block}
        }}{{-stop-}}";

    public function parse()
    {
        $patch = '5.1';

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $ItemActionCsv = $this->csv('ItemAction');
        $ItemFoodCsv = $this->csv('ItemFood');
        $StatusCsv = $this->csv('Status');
        //$ItemSearchCategoryCsv = $this->csv('ItemSearchCategory');
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

            // don't display Dye status for equipment that its not applicable to, and do show Crest/Dye for Shield/Head/Body
            $Dye = false;
            $Crest = false;
            switch ($item['ItemUICategory']) {
                case 33; case 39; case 40; case 41; case 42; case 43; case 44; case 45; case 46; case 47;
                case 48; case 49; case 50; case 51; case 52; case 53; case 54; case 55; case 56; case 58;
                case 59; case 60; case 61; case 62; case 63; case 64; case 71; case 73; case 74; case 75;
                case 81; case 82; case 83; case 85; case 86; case 94; case 95; case 99; case 100;
                    break;
                case 11; case 34; case 35;
                    $Crest = "\n| Crest Allowed  = ". $item['IsCrestWorthy'];
                    $Dye = "\n| Dye Allowed    = ". $item['IsDyeable'];
                    break;
                case NULL:
                    break;
                default:
                    $Dye = "\n| Dye Allowed    = ". $item['IsDyeable'];
                    break;
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
                //$AutoattackHQ = round((($Delay/3) * $PhysicalDamageHQ),2,PHP_ROUND_HALF_DOWN);
                $AutoattackHQ = (floor((($item["Delay<ms>"]/1000)/3)*$PhysicalDamageHQ*100)/100);
                //$Autoattack = round((($Delay/3) * $item['Damage{Phys}']),2,PHP_ROUND_HALF_DOWN);
                $Autoattack = (floor((($item["Delay<ms>"]/1000)/3)*$item['Damage{Phys}']*100)/100);
                $PhysicalDamage = "\n\n| Physical Damage    = " . $item['Damage{Phys}'] . "\n| Physical Damage HQ = " . $PhysicalDamageHQ;
                $MagicDamage = "\n| Magic Damage    = ". $item['Damage{Mag}'] ."\n| Magic Damage HQ = ". $MagicDamageHQ;
                $MagicDamage .= "\n| Delay          = ". $Delay;
                $MagicDamage .= "\n| Auto-attack    = ". $Autoattack ."\n| Auto-attack HQ = ". $AutoattackHQ;
            }   elseif (($item['Damage{Phys}'] > 0 || $item['Damage{Mag}'] > 0) && $item['CanBeHq'] == "False") {
                $Delay = round(($item["Delay<ms>"]/1000),2,PHP_ROUND_HALF_UP);
                //$Autoattack = round((($Delay/3) * $item['Damage{Phys}']),2,PHP_ROUND_HALF_DOWN);
                $Autoattack = (floor((($item["Delay<ms>"]/1000)/3)*$item['Damage{Phys}']*100)/100);
                $PhysicalDamage = "\n\n| Physical Damage = ". $item['Damage{Phys}'];
                $MagicDamage = "\n| Magic Damage    = ". $item['Damage{Mag}'];
                $MagicDamage .= "\n| Delay       = ". $Delay ."\n| Auto-attack = ". $Autoattack;
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
            if ((($item['ItemUICategory'] == 40 || $item['ItemUICategory'] == 41
                        || $item['ItemUICategory'] == 42 || $item['ItemUICategory'] == 43)
                    || $item['Defense{Phys}'] > 0 || $item['Defense{Mag}'] > 0)
                && $item['CanBeHq'] == "True") {
                $DefenseHQ = ($item['Defense{Phys}'] + $item['BaseParamValue{Special}[0]']);
                $MagicDefenseHQ = ($item['Defense{Mag}'] + $item['BaseParamValue{Special}[1]']);
                $Defense = "\n\n| Defense    = ". $item['Defense{Phys}'] ."\n| Defense HQ = ". $DefenseHQ;
                $Defense .= "\n| Magic Defense    = ". $item['Defense{Mag}'] ."\n| Magic Defense HQ = ". $MagicDefenseHQ;
            } elseif ((($item['ItemUICategory'] == 40 || $item['ItemUICategory'] == 41
                        || $item['ItemUICategory'] == 42 || $item['ItemUICategory'] == 43)
                    || $item['Defense{Phys}'] > 0 || $item['Defense{Mag}'] > 0)
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
                        $SetBonus[] = ":". ($i+2) ." Equipped: [[". $BaseParamCsv->at($item["BaseParam{Special}[$i]"])
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
                        $SetBonusGC[] = ":". ($i+2) ." Equipped: [[". $BaseParamCsv->at($item["BaseParam{Special}[$i]"])
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
                        $Sanction[] = "| Latent ". $ParamName ." = +". $item["BaseParamValue{Special}[$i]"];
                        $Sanction[] = "| Latent ". $ParamName ." Latent = Yes";
                        $Sanction[] = "| Latent ". $ParamName ." Conditions = Sanction";
                    }
                }
            }
            $Sanction = implode("\n",$Sanction);

            // Eureka Gear stats
            $EurekaBonus = [];
            if ($item['ItemSpecialBonus'] == 7) {
                foreach (range(0, 5) as $i) {
                    if (!empty($item["BaseParam[$i]"])) {
                        $BonusStatName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam[$i]"])['Name']);
                        $EurekaBonus[0] = "\n";
                        $EurekaBonus[] = "| Bonus ". $BonusStatName ." = +". $item["BaseParamValue[$i]"];
                    }
                } foreach (range(0,5) as $i) {
                    if (!empty($item["BaseParam{Special}[$i]"])) {
                        ($item["BaseParamValue{Special}[$i]"] > 0) ? $BonusStatValue = "+" : $BonusStatValue = false;
                        $BonusStatName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam{Special}[$i]"])['Name']);
                        $EurekaBonus[] = "| Eureka ". $BonusStatName ." = $BonusStatValue". $item["BaseParamValue{Special}[$i]"];
                    }
                }
            }
            $EurekaBonus = implode("\n",$EurekaBonus);

            // Bonus Stat Code for normal items
            $BonusStat = [];
            if ($item['CanBeHq'] == "True" && !empty($item['BaseParam[0]'] && !$item['ItemSpecialBonus'] == 7)) {
                foreach (range(0, 5) as $i) {
                    if (!empty($item["BaseParam[$i]"])) {
                        $BonusStatName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam[$i]"])['Name']);
                        $BonusStat[0] ="\n";
                        $BonusStat[] = "| Bonus " . $BonusStatName . " = +" . $item["BaseParamValue[$i]"];
                        if (!empty($item['BaseParamValue{Special}[' . ($i+2) . ']'])) {
                            $BonusStat[] = '| Bonus ' . $BonusStatName . ' HQ = +'.
                                ($item["BaseParamValue[$i]"] + $item['BaseParamValue{Special}[' . ($i+2) . ']']);
                        }
                    }
                }
            } elseif ($item['CanBeHq'] == "False" && !empty($item['BaseParam[0]'] && !$item['ItemSpecialBonus'] == 7)) {
                foreach (range(0, 5) as $i) {
                    if (!empty($item["BaseParam[$i]"])) {
                        $BonusStatName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam[$i]"])['Name']);
                        $BonusStat[0] ="\n";
                        $BonusStat[] = "| Bonus " . $BonusStatName . " = +" . $item["BaseParamValue[$i]"];
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
                '{slots}' => ($item['MateriaSlotCount'] > 0) ? "\n| Slots          = ". $item['MateriaSlotCount'] : "",
                '{advancedmelding}' => ($item['MateriaSlotCount'] > 0) && ($item['IsAdvancedMeldingPermitted'] == "False") ? "\n| Advanced Melds = False" : "",
                '{stack}' => ($item['StackSize'] > 1) ? "\n| Stack          = ". $item['StackSize'] : "",
                '{requires}' => $RequiredClasses ? $RequiredClasses : "",
                '{level}' => $item['Level{Equip}'],
                '{itemlevel}' => $item['Level{Item}'],
                '{untradable}' => ($item['IsUntradable'] == "True") ? "\n| Untradable     = Yes" : "",
                '{unique}' => ($item['IsUnique'] == "True") ? "\n| Unique         = Yes" : "",
                '{convertible}' => ($item['MaterializeType'] > 0)
                    ? "\n| Convertible    = Yes"
                    : "\n| Convertible    = No",
                '{sells}' => ($item['Price{Low}'] > 0)
                    ? "\n| Sells          = ". $item['Price{Low}'] ."\n| HQ             = ". $item['CanBeHq']
                    : "\n| Sells          = No\n| HQ             = ". $item['CanBeHq'],
                '{dyeallowed}' => $Dye,
                '{crestallowed}' => $Crest,
                '{glamour}' => ($item['IsGlamourous'] == "True") ? "\n| Projectable    = Yes" : "",
                '{desynthesis}' => ($item['Salvage'] > 0 && $item['ClassJob{Repair}'] > 0)
                    ? "\n| Desynthesizable= Yes\n| Desynth Level  = ". $SalvageCsv->at($item['Salvage'])['OptimalSkill']
                    : "\n| Desynthesizable= No",
                '{repair}' => $Repair,
                '{physicaldamage}' => $PhysicalDamage,
                '{magicdamage}' => $MagicDamage,
                '{block}' => $Block,
                '{defense}' => $Defense,
                '{setbonus}' => $SetBonus,
                '{setbonusgc}' => $SetBonusGC,
                '{sanction}' => $Sanction,
                '{bonus}' => $BonusStat,
                '{eureka}' => $EurekaBonus,
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
