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
    const WIKI_FORMAT = "{Top}{{ARR Infobox Item
        | Index          = {id}
        | Rarity         = {rarity}
        | Name           = {name}
        | Subheading     = {subheading}{description}{slots}{advancedmelding}{stack}{requires}
        | Required Level = {level}
        | Item Level     = {itemlevel}{untradable}{unique}{convertible}{sells}{dyeallowed}{crestallowed}{glamour}{desynthesis}{repair}{setbonus}{setbonusgc}{sanction}{bonus}{eureka}{physicaldamage}{magicdamage}{defense}{block}{itemaction}{MarketProhib}
        }}{Bottom}";

    public function parse()
    {
        $patch = '5.2';

        // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
        // manually, set to false
        $Bot = "true";

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $TripleTriadCardCsv = $this->csv('TripleTriadCard');
        $CompanionCsv = $this->csv('Companion');
        $OrchestrionCsv = $this->csv('Orchestrion');
        $GatheringSubCategoryCsv = $this->csv('GatheringSubCategory');
        $MountCsv = $this->csv('Mount');
        $LogMessageCsv = $this->csv('LogMessage');
        $SecretRecipeBookCsv = $this->csv('SecretRecipeBook');
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
        $BuddyEquipCsv = $this->csv('BuddyEquip');

        // (optional) start a progress bar
        $this->io->progressStart($ItemCsv->total);

        // loop through data
        foreach ($ItemCsv->data as $id => $item) {
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($item['Name'])) {
                continue;
            }
            $Name = $item['Name'];

            // change the top and bottom code depending on if I want to bot the pages up or not
            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name/Patch'''\n$patch\n<noinclude>[[Category:Patch Subpages]]</noinclude>\n{{-stop-}}{{-start-}}\n'''$Name'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name?action=edit\n";
                $Bottom = "";
            };

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

            // Market Prohibited
            $MarketProhib = [];
            if ($item['ItemSearchCategory'] == 0) {
                $MarketProhib[0] = "\n| Market Prohibited = Yes";
            }
            $MarketProhib = implode("\n", $MarketProhib);

            // Item Action
            $ItemAction = [];
            $stringtype1 = false;
            $stringtype2 = false;
            $outputstring = false;
            $outputstring0 = false;
            $outputstring1 = false;
            $outputstring2 = false;
            if ($item['ItemAction'] > 0) {

                $ItemActionNumber = $item["ItemAction"];
                $ItemActionType = $ItemActionCsv->at($ItemActionNumber)["Type"];
                //start of each itemaction code

                //start of 842 (remove status code)
                if ($ItemActionType == "842") {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = $StatusCsv->at($ItemActionEffectRaw)["Name"];
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Consumable Cures_";
                    //end text for string
                    $stringtype2 = "=&nbsp;";

                    //HQ - If there is no HQ it will not add anything extra
                    //$ItemActionEffectHQRaw = $ItemActionCsv->at($ItemActionNumber)["Data{HQ}[0]"];
                    //if ($ItemActionEffectHQRaw !== "0") {
                    //    $ItemActionEffectHQ = $StatusCsv->at($ItemActionEffectHQRaw)["Name"];
                    //    $HQString = "\n". $stringtype1HQ ."" . $ItemActionEffectHQ . "". $stringtype2 ."";
                    //} elseif ($ItemActionEffectHQRaw == "0") {
                    //    $ItemActionEffectHQ = "";
                    //    $HQString = "";
                    //}
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of single type code

                //start of 1013 (Barding code)
                if ($ItemActionType == "1013") {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = $BuddyEquipCsv->at($ItemActionEffectRaw)["Name"];
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Grantsnewitem = ";
                    //end text for string
                    $stringtype2 = "";
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of single type code

                 //start of 1055 (Restore GP code)
                if ($ItemActionType == "1055") {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = $ItemActionEffectRaw;
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Consumable Restores_GP = ";
                    //end text for string
                    $stringtype2 = "";

                    //HQ - If there is no HQ it will not add anything extra
                    $ItemActionEffectHQRaw = $ItemActionCsv->at($ItemActionNumber)["Data{HQ}[0]"];
                    if ($ItemActionEffectHQRaw !== "0") {
                        $ItemActionEffectHQ = $ItemActionEffectHQRaw;
                        $HQString = "\n| Consumable Restores_GP HQ = " . $ItemActionEffectHQ . "";
                    } elseif ($ItemActionEffectHQRaw == "0") {
                        $ItemActionEffectHQ = "";
                        $HQString = "";
                    }
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."". $HQString ."";

                }
                //end of single type code

                //start of 1322 (Whistle to mount code)
                if ($ItemActionType == "1322") {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = ucwords(strtolower($MountCsv->at($ItemActionEffectRaw)["Singular"]));
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Grantsnewitem = ";
                    //end text for string
                    $stringtype2 = "_(Mount)";
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of single type code

                //start of 2136 (Unlocks Master Recipes)
                if ($ItemActionType == "2136") {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = $SecretRecipeBookCsv->at($ItemActionEffectRaw)["Name"];
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Grants = ";
                    //end text for string
                    $stringtype2 = "_Recipe";
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of single type code

                //start of 2633 (Unlocks emotes etc)
                //if ($ItemActionType == "2633") {
                //    //NQ
                //    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                //    $ItemActionEffect = $LogMessageCsv->at($ItemActionEffectRaw)["Text"];
                //    if (empty($ItemActionEffect)) continue;
                //    if ($ItemActionEffectRaw == 0) continue;
                //    //start text for string
                //    $stringtype1 = "| Text = ";
                //    //end text for string
                //    $stringtype2 = "";
                //    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";
                //}
                //end of single type code

                //start of 3357 (TripleTriad Card)
                if ($ItemActionType == "3357") {

                    //NQ
                    $ItemActionEffectRaw1 = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = ucwords(strtolower($TripleTriadCardCsv->at($ItemActionEffectRaw)["Name"]));
                    $ItemActionEffectRaw = str_replace("&", "and", $ItemActionEffectRaw1);
                    if (empty($ItemActionEffect)) continue;

                    //start text for string
                    $stringtype1 = "| Grants = ";
                    //end text for string
                    $stringtype2 = "_(Triple_Triad_Card)";
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of single type code

                //start of 3800 (gives mgp)
                if ($ItemActionType == "3800") {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    //$ItemActionEffect = $TripleTriadCardCsv->at($ItemActionEffectRaw)["Name"];
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Consumable Grants_MGP = ";
                    //end text for string
                    $stringtype2 = "";
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffectRaw . "". $stringtype2 ."";

                }
                //end of single type code

                //start of 4107 (Tome of Folklore)
                if ($ItemActionType == "4107") {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = $GatheringSubCategoryCsv->at($ItemActionEffectRaw)["FolkloreBook"];
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Grants = ";
                    //end text for string
                    $stringtype2 = " Book";
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of single type code

                //start of 5845 (Orc Scrolls)
                if ($ItemActionType == "5845") {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = $OrchestrionCsv->at($ItemActionEffectRaw)["Name"];
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Grants = ";
                    //end text for string
                    $stringtype2 = "_Orchestrion_Roll";
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of single type code

                //start of 844, 845 and 846 (Battle Food/gathering food/attrib potions)
                if (($ItemActionType == "844") || ($ItemActionType == "845") || ($ItemActionType == "846")) {
                    //NQ
                    //item status effect
                    $ItemActionEffectStatus = $StatusCsv->at($ItemActionCsv->at($ItemActionNumber)["Data[0]"])["Name"];
                    $ItemActionEffectStatusReplace = str_replace(" ", "_", $ItemActionEffectStatus);
                    $ItemActionEffectStatusFmt = "\n| Consumable Adds_". $ItemActionEffectStatusReplace ."=&nbsp;";
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[1]"];
                    $ItemActionSeconds = $ItemActionCsv->at($ItemActionNumber)["Data[2]"];
                    //$ItemActionEffect = $ItemFoodCsv->at($ItemActionEffectRaw)["EXPBonus%"];
                    if (empty($ItemActionEffect)) continue;
                    //String for duration

                    $DurationMinutes = floor(($ItemActionSeconds / 60) % 60);
                    $DurationSeconds = $ItemActionSeconds % 60;
                    $DurationString = " ". $DurationMinutes ."m". $DurationSeconds ."s";
                    $DurationFormat1 = str_replace(" 0m", " ", $DurationString);
                    $DurationFormat = str_replace("m0s", "m", $DurationFormat1);
                    //$DurationFormat = str_replace("0m", "", $DurationString);
                    //$DurationFormatCut = substr($DurationFormat, 0, -3);
                    $Duration = "\n\n| Duration =". $DurationFormat. "";
                    //exp
                    $EXPBonus = $ItemFoodCsv->at($ItemActionEffectRaw)["EXPBonus%"];


                    if ($ItemActionType == "846") {
                        $EXPBonusFmt = "";
                    } elseif ($ItemActionType !== "846") {
                        $EXPBonusFmt = "| Consumable EXP_Bonus = +". $EXPBonus ."%";
                    }


                    $RecastNQ = $item['Cooldown<s>'];
                    $RecastHQpercent = ($RecastNQ * 0.1);
                    $RecastHQ = ($RecastNQ - $RecastHQpercent);

                    $RecastMinutes = floor(($RecastNQ / 60) % 60);
                    $RecastSeconds = $RecastNQ % 60;
                    $RecastString = " ". $RecastMinutes ."m". $RecastSeconds ."s";
                    $RecastFormatNQ1 = str_replace(" 0m", " ", $RecastString);
                    $RecastFormatNQ = str_replace("m0s", "m", $RecastFormatNQ1);

                    $RecastMinutesHQ = floor(($RecastHQ / 60) % 60);
                    $RecastSecondsHQ = $RecastHQ % 60;
                    $RecastStringHQ = " ". $RecastMinutesHQ ."m". $RecastSecondsHQ ."s";
                    $RecastFormatHQ1 = str_replace(" 0m", " ", $RecastStringHQ);
                    $RecastFormatHQ = str_replace("m0s", "m", $RecastFormatHQ1);

                    if ($ItemActionType == "846") {
                    $Recast = "\n| Recast = ". $RecastFormatNQ ."\n| Recast HQ = ". $RecastFormatHQ ."";
                    } elseif (($ItemActionType == "844") || ($ItemActionType == "845")) {
                        $Recast = "";
                    }

                    //each param value
                    //Start of base 0
                    $RelativeSwitchBool = $ItemFoodCsv->at($ItemActionEffectRaw)["IsRelative[0]"];
                    //switch to percentage if true and flat if false
                    if ($RelativeSwitchBool = True) {
                        $RelativeSwitch = "%";
                    } elseif ($RelativeSwitchBool = False) {
                        $RelativeSwitch = "";
                    }
                    $BaseStat = str_replace(" ", "_", $BaseParamCsv->at($ItemFoodCsv->at($ItemActionEffectRaw)["BaseParam[0]"])["Name"]);
                    if (!empty($BaseStat)) {
                        $BaseStatFmt = "| Consumable ". $BaseStat ." = ";
                        $BaseStatHQFmt = "| Consumable ". $BaseStat ." HQ = ";
                        $BaseStatCapFmt = "| Consumable ". $BaseStat ." Cap = ";
                        $BaseStatHQCapFmt = "| Consumable ". $BaseStat ." Cap HQ = ";

                    $BaseValue = $ItemFoodCsv->at($ItemActionEffectRaw)["Value[0]"];
                        $BaseValueFmt = "". $BaseStatFmt ."+". $BaseValue ."". $RelativeSwitch ."\n";

                    $BaseMax = $ItemFoodCsv->at($ItemActionEffectRaw)["Max[0]"];
                        $BaseMaxFmt = "". $BaseStatCapFmt ."+". $BaseMax ."\n";

                    $BaseValueHQ = $ItemFoodCsv->at($ItemActionEffectRaw)["Value{HQ}[0]"];
                        $BaseValueHQFmt = "". $BaseStatHQFmt ."+". $BaseValueHQ ."". $RelativeSwitch ."\n";

                    $BaseMaxHQ = $ItemFoodCsv->at($ItemActionEffectRaw)["Max{HQ}[0]"];
                    $BaseMaxHQFmt = "". $BaseStatHQCapFmt ."+". $BaseMaxHQ ."\n";

                    $outputstring0 = "\n". $BaseValueFmt ."". $BaseMaxFmt ."" . $BaseValueHQFmt . "". $BaseMaxHQFmt ."";
                    }

                    elseif (empty($BaseStat)) {
                        $outputstring0 = "";
                    }
                    //End of base 0

                    //Start of base 1
                    $RelativeSwitchBool1 = $ItemFoodCsv->at($ItemActionEffectRaw)["IsRelative[1]"];
                    //switch to percentage if true and flat if false
                    if ($RelativeSwitchBool1 = True) {
                        $RelativeSwitch1 = "%";
                    } elseif ($RelativeSwitchBool1 = False) {
                        $RelativeSwitch1 = "";
                    }
                    $BaseStat1 = str_replace(" ", "_", $BaseParamCsv->at($ItemFoodCsv->at($ItemActionEffectRaw)["BaseParam[1]"])["Name"]);
                    if (!empty($BaseStat1)) {
                        $BaseStatFmt1 = "| Consumable ". $BaseStat1 ." = ";
                        $BaseStatHQFmt1 = "| Consumable ". $BaseStat1 ." HQ = ";
                        $BaseStatCapFmt1 = "| Consumable ". $BaseStat1 ." Cap = ";
                        $BaseStatHQCapFmt1 = "| Consumable ". $BaseStat1 ." Cap HQ = ";

                        $BaseValue1 = $ItemFoodCsv->at($ItemActionEffectRaw)["Value[1]"];
                        $BaseValueFmt1 = "". $BaseStatFmt1 ."+". $BaseValue1 ."". $RelativeSwitch1 ."\n";

                        $BaseMax1 = $ItemFoodCsv->at($ItemActionEffectRaw)["Max[1]"];
                        $BaseMaxFmt1 = "". $BaseStatCapFmt1 ."+". $BaseMax1 ."\n";

                        $BaseValueHQ1 = $ItemFoodCsv->at($ItemActionEffectRaw)["Value{HQ}[1]"];
                        $BaseValueHQFmt1 = "". $BaseStatHQFmt1 ."+". $BaseValueHQ1 ."". $RelativeSwitch1 ."\n";

                        $BaseMaxHQ1 = $ItemFoodCsv->at($ItemActionEffectRaw)["Max{HQ}[1]"];
                        $BaseMaxHQFmt1 = "". $BaseStatHQCapFmt1 ."+". $BaseMaxHQ1 ."\n";

                        $outputstring1 = "\n". $BaseValueFmt1 ."". $BaseMaxFmt1 ."" . $BaseValueHQFmt1 . "". $BaseMaxHQFmt1 ."";
                    }

                    elseif (empty($BaseStat1)) {
                        $outputstring1 = "";
                    }
                    //End of base 1

                    //Start of base 2
                    $RelativeSwitchBool2 = $ItemFoodCsv->at($ItemActionEffectRaw)["IsRelative[2]"];
                    //switch to percentage if true and flat if false
                    if ($RelativeSwitchBool2 = True) {
                        $RelativeSwitch2 = "%";
                    } elseif ($RelativeSwitchBool1 = False) {
                        $RelativeSwitch2 = "";
                    }
                    $BaseStat2 = str_replace(" ", "_", $BaseParamCsv->at($ItemFoodCsv->at($ItemActionEffectRaw)["BaseParam[2]"])["Name"]);
                    if (!empty($BaseStat2)) {

                        $BaseStatFmt2 = "| Consumable ". $BaseStat2 ." = ";
                        $BaseStatHQFmt2 = "| Consumable ". $BaseStat2 ." HQ = ";
                        $BaseStatCapFmt2 = "| Consumable ". $BaseStat2 ." Cap = ";
                        $BaseStatHQCapFmt2 = "| Consumable ". $BaseStat2 ." Cap HQ = ";

                        $BaseValue2 = $ItemFoodCsv->at($ItemActionEffectRaw)["Value[2]"];
                        $BaseValueFmt2 = "". $BaseStatFmt2 ."+". $BaseValue2 ."". $RelativeSwitch2 ."\n";

                        $BaseMax2 = $ItemFoodCsv->at($ItemActionEffectRaw)["Max[2]"];
                        $BaseMaxFmt2 = "". $BaseStatCapFmt2 ."+". $BaseMax2 ."\n";

                        $BaseValueHQ2 = $ItemFoodCsv->at($ItemActionEffectRaw)["Value{HQ}[2]"];
                        $BaseValueHQFmt2 = "". $BaseStatHQFmt2 ."+". $BaseValueHQ2 ."". $RelativeSwitch2 ."\n";

                        $BaseMaxHQ2 = $ItemFoodCsv->at($ItemActionEffectRaw)["Max{HQ}[2]"];
                        $BaseMaxHQFmt2 = "". $BaseStatHQCapFmt2 ."+". $BaseMaxHQ2 ."\n";

                        $outputstring2 = "\n". $BaseValueFmt2 ."". $BaseMaxFmt2 ."" . $BaseValueHQFmt2 . "". $BaseMaxHQFmt2 ."";
                    }

                    elseif (empty($BaseStat2)) {
                        $outputstring2 = "";
                    }
                    //End of base 2

                    //end text for string
                    $stringtype2 = "";
                    $outputstring = "\n". $Duration ."" . $Recast . "". $outputstring0 ."". $outputstring1 ."". $outputstring2 ."". $EXPBonusFmt ."". $ItemActionEffectStatusFmt. "";
                }
                //end of single type code

                //start of 847 848 (HP/Mp Potions)
                if (($ItemActionType == "847") || ($ItemActionType == "848")) {

                if ($ItemActionType == "847") {
                    $BaseStat = "Restores_HP";
                } elseif ($ItemActionType == "848") {
                    $BaseStat = "Restores_MP";
                }

                //NQ
                $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                $ItemActionEffectCapRaw = $ItemActionCsv->at($ItemActionNumber)["Data[1]"];
                //HQ
                $ItemActionEffectHQRaw = $ItemActionCsv->at($ItemActionNumber)["Data{HQ}[0]"];
                $ItemActionEffectCapHQRaw = $ItemActionCsv->at($ItemActionNumber)["Data{HQ}[1]"];

                $ItemActionEffectRawString = "| Consumable ". $BaseStat ." = ";
                $ItemActionEffectCapRawString = "| Consumable ". $BaseStat ." Cap = ";
                $ItemActionEffectHQRawString = "| Consumable ". $BaseStat ." HQ = ";
                $ItemActionEffectCapHQRawString = "| Consumable ". $BaseStat ." Cap HQ = ";

                        $BaseValueFmt = "". $ItemActionEffectRawString ."+". $ItemActionEffectRaw ."%\n";

                        $BaseValueCapFmt = "". $ItemActionEffectCapRawString ."+". $ItemActionEffectCapRaw ."\n";

                        $BaseValueHQFmt = "". $ItemActionEffectHQRawString ."+". $ItemActionEffectHQRaw ."%\n";

                        $BaseValueHQCapFmt = "". $ItemActionEffectCapHQRawString ."+". $ItemActionEffectCapHQRaw ."\n";

                $outputstring0 = "\n". $BaseValueFmt ."". $BaseValueCapFmt ."" . $BaseValueHQFmt . "". $BaseValueHQCapFmt ."";

                //Recast
                $RecastNQ = $item['Cooldown<s>'];
                $RecastHQpercent = ($RecastNQ * 0.1);
                $RecastHQ = ($RecastNQ - $RecastHQpercent);

                $RecastMinutes = floor(($RecastNQ / 60) % 60);
                $RecastSeconds = $RecastNQ % 60;
                $RecastString = " ". $RecastMinutes ."m". $RecastSeconds ."s";
                $RecastFormatNQ1 = str_replace(" 0m", " ", $RecastString);
                $RecastFormatNQ = str_replace("m0s", "m", $RecastFormatNQ1);

                $RecastMinutesHQ = floor(($RecastHQ / 60) % 60);
                $RecastSecondsHQ = $RecastHQ % 60;
                $RecastStringHQ = " ". $RecastMinutesHQ ."m". $RecastSecondsHQ ."s";
                $RecastFormatHQ1 = str_replace(" 0m", " ", $RecastStringHQ);
                $RecastFormatHQ = str_replace("m0s", "m", $RecastFormatHQ1);

                $Recast = "\n| Recast = ". $RecastFormatNQ ."\n| Recast HQ = ". $RecastFormatHQ ."";

                if (empty($ItemActionEffectRaw)) continue;
                //start text for string
                $outputstring = "\n" . $Recast . "". $outputstring0 ."";

                }
                //end of single type code


                //start of 853 (Minions)
                if ($ItemActionType == "853") {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = ucwords(strtolower($CompanionCsv->at($ItemActionEffectRaw)["Singular"]));
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Grants = ";
                    //end text for string
                    $stringtype2 = "_(Minion)";
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of single type code

                //end of each itemaction code

                if (empty($ItemActionEffect)) {continue;}
                if ($ItemActionEffectRaw == "0") {continue;}
                $ItemAction1[0] ="\n";
                $ItemAction[] = "". $outputstring ."";

            }

            $ItemAction = implode("\n",$ItemAction);

            // Save some data
            $data = [
                '{Top}' => $Top,
                '{patch}' => $patch,
                '{id}' => $item['id'],
                '{rarity}' => $item['Rarity'],
                '{name}' => $Name,
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
                '{itemaction}' => $ItemAction,
                '{MarketProhib}' => $MarketProhib,
                '{Bottom}' => $Bottom,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
             $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeItemWiki - '. $patch .'.txt', 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
