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
        | Item Level     = {itemlevel}{untradable}{unique}{convertible}{sells}{dyeallowed}{crestallowed}{glamour}{desynthesis}{repair}{MarketProhib}{setbonus}{bonus}{physicaldamage}{magicdamage}{defense}{block}{itemaction}
        }}{Bottom}";

    public function parse()
    {
        $patch = '5.21';

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
            //if (($item['ClassJob{Repair}'] == 0) || ($SalvageCsv->at($item['Salvage'])['OptimalSkill'] == 0)) {continue;}

            // remove Emphasis, comma, and wiki italic '' code in names
            $Name = preg_replace("/<Emphasis>|<\/Emphasis>|,|''/", "", $item['Name']);
            //$Name = str_replace("&", "and", $Name);

            // check if item can be Desynthesized
            $Desynth = false;
            $DesynthText = false;
            $DesynthTop = false;
            if (($SalvageCsv->at($item['Salvage'])['OptimalSkill'] > 1) && ($item['ItemUICategory'] == 47 || $item['ClassJob{Repair}'] > 0)) {
                $Desynth = "Yes";
            };

            // add Desynth template and page if item can be Desynthesized
            if ($Desynth == "Yes") {
                $DesynthText = "{{ARR Infobox Desynth\n|Item            = " . $item['Name'] . "\n|Primary Skill   = " .
                    ucwords(strtolower($ClassJobCsv->at($item['ClassJob{Repair}'])['Name'])) . "\n|Result 1        = \n" . ""
                    . "|Result 1 Amount = \n|Result 2        = \n|Result 2 Amount = \n|Result 3        = \n" . ""
                    . "|Result 3 Amount = \n|Result 4        = \n|Result 4 Amount = \n|Result 5        = \n" . ""
                    . "|Result 5 Amount = \n|Result 6        = \n|Result 6 Amount = \n}}";
            };

            if (($Bot == "true") && ($Desynth == "Yes")) {
                $DesynthTop = "{{-start-}}\n'''$Name/Desynth'''\n$DesynthText{{-stop-}}";
            } elseif (($Bot != "true") && ($Desynth == "Yes")) {
                $DesynthTop = "http://ffxiv.gamerescape.com/wiki/$Name/Desynth\n$DesynthText";
            }

            // change the top and bottom code depending on if I want to bot the pages up or not
            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name/Patch'''\n$patch\n<noinclude>[[Category:Patch Subpages]]</noinclude>\n{{-stop-}}{{-start-}}\n'''$Name'''\n";
                //$Top = "{{-start-}}\n'''$Name'''\n";
                $Bottom = "{{-stop-}}$DesynthTop";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name?action=edit\n";
                $Bottom = $DesynthTop;
            };

            // grab item ui category for this item
            $itemUiCategory = $ItemUiCategoryCsv->at($item['ItemUICategory']);

            // if multiple Required Classes separate with commas, otherwise 3 capital letters. If no classes, null
            $RequiredClasses = ($item['ClassJobCategory'] > 0)
                ? "\n| Requires       = " . preg_replace("/([A-Z]{3})\s/", "$1, ", ($ClassJobCategoryCsv->at($item['ClassJobCategory'])['Name']))
                : false;

            // change Fits/Gender to wiki-specific parameters
            $Description = false;
            if ($item['Description'] == "Fits: Game Masters") {
                $Description = "\n| FitsGM         = Game Masters";
            } elseif (!empty($item['Description'])) {
                $Description = "\n| Description    = " . $item['Description'];
            }
            if (!empty($item['EquipRestriction'])) {
                switch ($item['EquipRestriction']) {
                    case 2:
                        $Description = "\n| Gender         = Male";
                        break;
                    case 3:
                        $Description = "\n| Gender         = Female";
                        break;
                    case 4:
                        $Description = "\n| Fits           = Hyur\n| Gender         = Male";
                        break;
                    case 5:
                        $Description = "\n| Fits           = Hyur\n| Gender         = Female";
                        break;
                    case 6:
                        $Description = "\n| Fits           = Elezen\n| Gender         = Male";
                        break;
                    case 7:
                        $Description = "\n| Fits           = Elezen\n| Gender         = Female";
                        break;
                    case 8:
                        $Description = "\n| Fits           = Lalafell\n| Gender         = Male";
                        break;
                    case 9:
                        $Description = "\n| Fits           = Lalafell\n| Gender         = Female";
                        break;
                    case 10:
                        $Description = "\n| Fits           = Miqo'te\n| Gender         = Male";
                        break;
                    case 11:
                        $Description = "\n| Fits           = Miqo'te\n| Gender         = Female";
                        break;
                    case 12:
                        $Description = "\n| Fits           = Roegadyn\n| Gender         = Male";
                        break;
                    case 13:
                        $Description = "\n| Fits           = Roegadyn\n| Gender         = Female";
                        break;
                    case 14:
                        $Description = "\n| Fits           = Au Ra\n| Gender         = Male";
                        break;
                    case 15:
                        $Description = "\n| Fits           = Au Ra\n| Gender         = Female";
                        break;
                    case 16:
                        $Description = "\n| Fits           = Hrothgar\n| Gender         = Male";
                        break;
                    case 17:
                        $Description = "\n| Fits           = Viera\n| Gender         = Female";
                        break;
                    default:
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
                $AutoattackHQ = (floor((($item["Delay<ms>"]/1000)/3)*$PhysicalDamageHQ*100)/100);
                $Autoattack = (floor((($item["Delay<ms>"]/1000)/3)*$item['Damage{Phys}']*100)/100);
                $PhysicalDamage = "\n\n| Physical Damage    = " . $item['Damage{Phys}'] . "\n| Physical Damage HQ = " . $PhysicalDamageHQ;
                $MagicDamage = "\n| Magic Damage    = ". $item['Damage{Mag}'] ."\n| Magic Damage HQ = ". $MagicDamageHQ;
                $MagicDamage .= "\n| Delay          = ". $Delay;
                $MagicDamage .= "\n| Auto-attack    = ". $Autoattack ."\n| Auto-attack HQ = ". $AutoattackHQ;
            }   elseif (($item['Damage{Phys}'] > 0 || $item['Damage{Mag}'] > 0) && $item['CanBeHq'] == "False") {
                $Delay = round(($item["Delay<ms>"]/1000),2,PHP_ROUND_HALF_UP);
                $Autoattack = (floor((($item["Delay<ms>"]/1000)/3)*$item['Damage{Phys}']*100)/100);
                $PhysicalDamage = "\n\n| Physical Damage = ". $item['Damage{Phys}'];
                $MagicDamage = "\n| Magic Damage    = ". $item['Damage{Mag}'];
                $MagicDamage .= "\n| Delay       = ". $Delay ."\n| Auto-attack = ". $Autoattack;
            }

            // display Block/BlockRate and Block HQ/BlockRate HQ stats
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

            // switch code for GC gear, Sanction gear, Mog Station Gear, and Eureka Gear
            $SetBonus = [];
            switch ($item['ItemSpecialBonus']) {
                case 2: // GC Gear
                    $SetBonus[0] = "\n\n| Other Conditions = ". $ItemSeriesCsv->at($item['ItemSeries'])['Name'] ."";
                    $SetBonus[1] = "| Set              = ". $ItemSeriesCsv->at($item['ItemSeries'])['Name'] ."";
                    $SetBonus[2] = "| Set Bonus        =<br>";
                    foreach(range(0,5) as $i) {
                        if(!empty($item["BaseParam{Special}[$i]"])) {
                            $ParamName = $BaseParamCsv->at($item["BaseParam{Special}[$i]"])['Name'];
                            $SetBonus[] = ":". ($i+2) ." Equipped: [[$ParamName]] +". $item["BaseParamValue{Special}[$i]"];
                        }
                    }
                    break;
                case 4: // Sanction Gear
                    foreach(range(0,5) as $i) {
                        if(!empty($item["BaseParam{Special}[$i]"])) {
                            $ParamName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam{Special}[$i]"])['Name']);
                            $SetBonus[0] = "\n";
                            $SetBonus[] = "| Latent ". $ParamName ." = +". $item["BaseParamValue{Special}[$i]"];
                            $SetBonus[] = "| Latent ". $ParamName ." Latent = Yes";
                            $SetBonus[] = "| Latent ". $ParamName ." Conditions = Sanction";
                        }
                    }
                    break;
                case 6: // Mog Station Gear
                    $SetBonus[0] = "\n\n| SetBonus Set_Bonus_(Capped):=\n:[[". $ItemSeriesCsv->at($item['ItemSeries'])['Name'] ."]]";
                    $SetBonus[1] = ":Active Under Lv. ". $item['ItemSpecialBonus{Param}'];
                    foreach(range(0,5) as $i) {
                        if(!empty($item["BaseParam{Special}[$i]"])) {
                            $ParamName = $BaseParamCsv->at($item["BaseParam{Special}[$i]"])['Name'];
                            $SetBonus[] = ":". ($i+2) ." Equipped: [[$ParamName]] +". $item["BaseParamValue{Special}[$i]"];
                        }
                    }
                    break;
                case 7: // Eureka Gear
                    foreach (range(0, 5) as $i) {
                        if (!empty($item["BaseParam[$i]"])) {
                            $ParamName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam[$i]"])['Name']);
                            $SetBonus[0] = "\n";
                            $SetBonus[] = "| Bonus $ParamName = +". $item["BaseParamValue[$i]"];
                        }
                    } foreach (range(0,5) as $i) {
                    if (!empty($item["BaseParam{Special}[$i]"])) {
                        $BonusStatValue = ($item["BaseParamValue{Special}[$i]"] > 0) ? "+" : false;
                        $ParamName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam{Special}[$i]"])['Name']);
                        $SetBonus[0] = "\n";
                        $SetBonus[] = "| Eureka $ParamName = $BonusStatValue". $item["BaseParamValue{Special}[$i]"];
                    }
                }
                    break;
                default:
                    break;
            }

            $SetBonus = implode("\n", $SetBonus);

            // Bonus Stat Code for normal items
            $BonusStat = [];
            if (($item['CanBeHq'] == "True") && (!empty($item['BaseParam[0]'])) && ($item['ItemSpecialBonus'] != 7)) {
                foreach (range(0, 5) as $i) {
                    if ((!empty($item["BaseParam[$i]"])) && (!empty($item["BaseParamValue[$i]"]))) {
                        $BonusStatName = str_replace(" ", "_", $BaseParamCsv->at($item["BaseParam[$i]"])['Name']);
                        $BonusStat[0] = "\n";
                        $BonusStat[] = "| Bonus " . $BonusStatName . " = +" . $item["BaseParamValue[$i]"];
                        // create a different loop from the foreach above that goes from 0 to 5. For each one of those numbers
                        // (which will be the number in the BaseParamSpecial[X] column) match up the "foreach" loop number
                        // with the "for" loop number and print the stats. Complicated asfuck but should fix SE's retardedness with
                        // making BaseParam columns not matching up with +2 of foreach in the BaseParamValue{Special} column...
                        // not perfect, still doesn't post stats that have 0 NQ and +1 or more HQ. But should fix the missing
                        // HQ stats for the final stat, usually Vitality, on HQ crafted accessories and other items.
                        for ($x = 0; $x <= 5; $x++) {
                            if ($item["BaseParam[$i]"] == $item["BaseParam{Special}[$x]"]) {
                                $BonusStat[] = '| Bonus ' . $BonusStatName . ' HQ = +' . ($item["BaseParamValue[$i]"] + $item["BaseParamValue{Special}[$x]"]);
                            }
                        }
                            // old HQ stat code. Obsolete-ish now with the 'for' loop up above. Saving code just in case.
                            //if (!empty($item['BaseParamValue{Special}[' . ($i+2) . ']'])) {
                            //    $BonusStat[] = '| Bonus ' . $BonusStatName . ' HQ = +'.
                            //        ($item["BaseParamValue[$i]"] + $item['BaseParamValue{Special}[' . ($i+2) . ']']);
                            //}
                    }
                }
            } elseif (($item['CanBeHq'] == "False") && (!empty($item['BaseParam[0]'])) && ($item['ItemSpecialBonus'] != 7)) {
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

            // Remove En-dash and Em-dash from Subheading name
            $SubHeadingReplaceSearch = array("–", "—");
            $SubHeadingReplaceText = array("-", "-");
            $Subheading = str_replace($SubHeadingReplaceSearch, $SubHeadingReplaceText, $itemUiCategory['Name']);

            // Item Action
            $ItemAction = [];
            $outputstring = false;
            $outputstring0 = false;
            $outputstring1 = false;
            $outputstring2 = false;
            $HQString = false;
            $BaseStat = false;
            $Recast = false;
            if ($item['ItemAction'] > 0) {

                $ItemActionNumber = $item["ItemAction"];
                $ItemActionType = $ItemActionCsv->at($ItemActionNumber)["Type"];
                //start of each itemaction code

                //start of 842 (Remove Status code)
                if ($ItemActionType == 842) {

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
                    //if ($ItemActionEffectHQRaw !== 0) {
                    //    $ItemActionEffectHQ = $StatusCsv->at($ItemActionEffectHQRaw)["Name"];
                    //    $HQString = "\n". $stringtype1HQ ."" . $ItemActionEffectHQ . "". $stringtype2 ."";
                    //} elseif ($ItemActionEffectHQRaw == 0) {
                    //    $ItemActionEffectHQ = false;
                    //    $HQString = false;
                    //}
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of Remove Status code

                //start of 1013 (Barding code)
                if ($ItemActionType == 1013) {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = $BuddyEquipCsv->at($ItemActionEffectRaw)["Name"];
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Grantsnewitem = ";
                    //end text for string
                    $stringtype2 = false;
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of Barding code

                 //start of 1055 (Restore GP code)
                if ($ItemActionType == 1055) {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = $ItemActionEffectRaw;
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Consumable Restores_GP = ";
                    //end text for string
                    $stringtype2 = false;

                    //HQ - If there is no HQ it will not add anything extra
                    $ItemActionEffectHQRaw = $ItemActionCsv->at($ItemActionNumber)["Data{HQ}[0]"];
                    switch ($ItemActionEffectHQRaw) {
                        case 0:
                            break;
                        default:
                            $ItemActionEffectHQ = $ItemActionEffectHQRaw;
                            $HQString = "\n| Consumable Restores_GP HQ = " . $ItemActionEffectHQ;
                            break;
                    }
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."". $HQString ."";
                }
                //end of Restore GP code

                //start of 1322 (Whistle to Mount code)
                if ($ItemActionType == 1322) {

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
                //end of Whistle to Mount code

                //start of 2136 (Master Recipes)
                if ($ItemActionType == 2136) {

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
                //end of Master Recipes code

                //start of 2633 (Unlocks emotes etc)
                //if ($ItemActionType == 2633) {
                //    //NQ
                //    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                //    $ItemActionEffect = $LogMessageCsv->at($ItemActionEffectRaw)["Text"];
                //    if (empty($ItemActionEffect)) continue;
                //    if ($ItemActionEffectRaw == 0) continue;
                //    //start text for string
                //    $stringtype1 = "| Text = ";
                //    //end text for string
                //    $stringtype2 = false;
                //    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";
                //}
                //end of Emotes etc code

                //start of 3357 (TripleTriad Card)
                if ($ItemActionType == 3357) {

                    //NQ
                    $ItemActionEffectRaw1 = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffectRaw = str_replace("&", "and", $ItemActionEffectRaw1);
                    $ItemActionEffect = ucwords(strtolower($TripleTriadCardCsv->at($ItemActionEffectRaw)["Name"]));
                    if (empty($ItemActionEffect)) continue;

                    //start text for string
                    $stringtype1 = "| Grants = ";
                    //end text for string
                    $stringtype2 = "_(Triple_Triad_Card)";
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffect . "". $stringtype2 ."";

                }
                //end of TripleTriad Card code

                //start of 3800 (gives MGP)
                if ($ItemActionType == 3800) {

                    //NQ
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    //$ItemActionEffect = $TripleTriadCardCsv->at($ItemActionEffectRaw)["Name"];
                    if (empty($ItemActionEffect)) continue;
                    //start text for string
                    $stringtype1 = "| Consumable Grants_MGP = ";
                    //end text for string
                    $stringtype2 = false;
                    $outputstring = "\n". $stringtype1 ."" . $ItemActionEffectRaw . "". $stringtype2 ."";

                }
                //end of MGP code

                //start of 4107 (Tome of Folklore)
                if ($ItemActionType == 4107) {

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
                //end of Tome of Folklore code

                //start of 5845 (Orchestrion Rolls)
                if ($ItemActionType == 5845) {

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
                //end of Orchestrion Rolls code

                //start of 844, 845 and 846 (Battle Food/gathering food/attribute potions)
                if (($ItemActionType == 844) || ($ItemActionType == 845) || ($ItemActionType == 846)) {

                    //NQ
                    //item status effect
                    $ItemActionEffectStatus = $StatusCsv->at($ItemActionCsv->at($ItemActionNumber)["Data[0]"])["Name"];
                    $ItemActionEffectStatusReplace = str_replace(" ", "_", $ItemActionEffectStatus);
                    $ItemActionEffectStatusFmt = "\n| Consumable Adds_$ItemActionEffectStatusReplace = &nbsp;";
                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[1]"];
                    $ItemActionSeconds = $ItemActionCsv->at($ItemActionNumber)["Data[2]"];
                    //$ItemActionEffect = $ItemFoodCsv->at($ItemActionEffectRaw)["EXPBonus%"];
                    //if (empty($ItemActionEffect)) continue;
                    //String for duration

                    $DurationMinutes = floor(($ItemActionSeconds / 60) % 60);
                    $DurationSeconds = $ItemActionSeconds % 60;
                    $DurationString = " " . $DurationMinutes . "m" . $DurationSeconds . "s";
                    $DurationFormat1 = str_replace(" 0m", " ", $DurationString);
                    $DurationFormat = str_replace("m0s", "m", $DurationFormat1);
                    $Duration = ($DurationFormat == 30) ? "\n| Duration =$DurationFormat/60m" : "\n\n| Duration =$DurationFormat";

                    //exp
                    $EXPBonus = $ItemFoodCsv->at($ItemActionEffectRaw)["EXPBonus%"];
                    $EXPBonusFmt = ($ItemActionType == 846) ? false : "\n| Consumable EXP_Bonus = +$EXPBonus%";

                    //recast
                    $RecastNQ = $item['Cooldown<s>'];
                    $RecastHQpercent = ($RecastNQ * 0.1);
                    $RecastHQ = ($RecastNQ - $RecastHQpercent);

                    $RecastMinutes = floor(($RecastNQ / 60) % 60);
                    $RecastSeconds = $RecastNQ % 60;
                    $RecastString = " " . $RecastMinutes . "m" . $RecastSeconds . "s";
                    $RecastFormatNQ1 = str_replace(" 0m", " ", $RecastString);
                    $RecastFormatNQ = str_replace("m0s", "m", $RecastFormatNQ1);

                    $RecastMinutesHQ = floor(($RecastHQ / 60) % 60);
                    $RecastSecondsHQ = $RecastHQ % 60;
                    $RecastStringHQ = " " . $RecastMinutesHQ . "m" . $RecastSecondsHQ . "s";
                    $RecastFormatHQ1 = str_replace(" 0m", " ", $RecastStringHQ);
                    $RecastFormatHQ = str_replace("m0s", "m", $RecastFormatHQ1);

                    if ($ItemActionType == 846) {
                        $Recast = "\n| Recast = " . $RecastFormatNQ . "\n| Recast HQ = " . $RecastFormatHQ . "";
                    } elseif (($ItemActionType == 844) || ($ItemActionType == 845)) {
                        $Recast = false;
                    }

                    //Start of base parameters
                    for ($k = 0; $k < 3; $k++) {
                        ${"outputstring$k"} = false;

                        $BaseStat = str_replace(" ", "_", $BaseParamCsv->at($ItemFoodCsv->at($ItemActionEffectRaw)["BaseParam[$k]"])["Name"]);
                        if (!empty($BaseStat)) {
                            //switch to percentage if true and flat if false
                            $Relative = ($ItemFoodCsv->at($ItemActionEffectRaw)["IsRelative[$k]"] == "True") ? "%" : false;

                            $BaseStatFmt = "| Consumable " . $BaseStat . " = ";
                            $BaseStatHQFmt = "| Consumable " . $BaseStat . " HQ = ";

                            $BaseMax = $ItemFoodCsv->at($ItemActionEffectRaw)["Max[$k]"];
                            $BaseValue = $ItemFoodCsv->at($ItemActionEffectRaw)["Value[$k]"];
                            $BaseValueHQ = $ItemFoodCsv->at($ItemActionEffectRaw)["Value{HQ}[$k]"];
                            $BaseMaxHQ = $ItemFoodCsv->at($ItemActionEffectRaw)["Max{HQ}[$k]"];

                            $BaseValueFmt = "" . $BaseStatFmt . "+" . $BaseValue . "" . $Relative . "\n";
                            $BaseValueHQFmt = "" . $BaseStatHQFmt . "+" . $BaseValueHQ . "" . $Relative . "\n";

                            // don't display Cap if it's going to be +0
                            $BaseStatCapFmt = ($BaseMax > 0) ? "| Consumable " . $BaseStat . " Cap = " : false;
                            $BaseStatHQCapFmt = ($BaseMax > 0) ? "| Consumable " . $BaseStat . " Cap HQ = " : false;
                            $BaseMaxFmt = ($BaseMax > 0) ? "" .  $BaseStatCapFmt . "+" . $BaseMax . "\n" : false;
                            $BaseMaxHQFmt = ($BaseMax > 0) ? "" . $BaseStatHQCapFmt . "+" . $BaseMaxHQ . "\n" : false;

                            ${"outputstring$k"} = "\n" . $BaseValueFmt . "" . $BaseMaxFmt . "" . $BaseValueHQFmt . "" . $BaseMaxHQFmt . "";
                        }
                        //End of base parameters

                        //end text for string
                        $outputstring = "\n" . $Duration . "" . $Recast . "" . $outputstring0 . "" . $outputstring1 . "" . $outputstring2 . "" . $EXPBonusFmt . "" . $ItemActionEffectStatusFmt . "";
                    }
                    //end of Battle Food/gathering food/attribute potions code
                }

                //start of HP/MP Potions
                if (($ItemActionType == 847) || ($ItemActionType == 848)) {

                if ($ItemActionType == 847) {
                    $BaseStat = "Restores_HP";
                } elseif ($ItemActionType == 848) {
                    $BaseStat = "Restores_MP";
                }

                //NQ
                $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                if ($ItemActionEffectRaw == 0) {continue;}
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

                $Recast = "\n| Recast = $RecastFormatNQ\n| Recast HQ = $RecastFormatHQ";

                if (empty($ItemActionEffectRaw)) continue;
                //start text for string
                $outputstring = "\n" . $Recast . "". $outputstring0 ."";
                }
                //end of HP/MP Potions code

                //start of 853 (Minions)
                if ($ItemActionType == 853) {

                    $ItemActionEffectRaw = $ItemActionCsv->at($ItemActionNumber)["Data[0]"];
                    $ItemActionEffect = ucwords(strtolower($CompanionCsv->at($ItemActionEffectRaw)["Singular"]));
                    if (empty($ItemActionEffect)) continue;
                    $outputstring = "\n| Grants = " . $ItemActionEffect . "_(Minion)";
                }
                //end of Minions code
                //end of ItemAction code

                //breaks item output by not outputting item ID#'s 4551-4563 (potion, ether, etc) because they don't
                //give a status effect when they're used. So commenting this out for now (and likely will delete later)
                //if (empty($ItemActionEffect)) {continue;}

                $ItemAction1[0] ="\n";
                $ItemAction[] = $outputstring;
            }

            $ItemAction = implode("\n",$ItemAction);

            // Save some data
            $data = [
                '{Top}' => $Top,
                '{patch}' => $patch,
                '{id}' => $item['id'],
                '{rarity}' => $item['Rarity'],
                '{name}' => $Name,
                '{subheading}' => $Subheading,
                '{description}' => $Description
                    ? $Description
                    : "",
                '{slots}' => ($item['MateriaSlotCount'] > 0)
                    ? "\n| Slots          = ". $item['MateriaSlotCount']
                    : "",
                '{advancedmelding}' => ($item['MateriaSlotCount'] > 0) && ($item['IsAdvancedMeldingPermitted'] == "False")
                    ? "\n| Advanced Melds = False"
                    : "",
                '{stack}' => ($item['StackSize'] > 1)
                    ? "\n| Stack          = ". $item['StackSize']
                    : "",
                '{requires}' => $RequiredClasses
                    ? $RequiredClasses
                    : "",
                '{level}' => $item['Level{Equip}'],
                '{itemlevel}' => $item['Level{Item}'],
                '{untradable}' => ($item['IsUntradable'] == "True")
                    ? "\n| Untradable     = Yes"
                    : "",
                '{unique}' => ($item['IsUnique'] == "True")
                    ? "\n| Unique         = Yes"
                    : "",
                '{convertible}' => ($item['MaterializeType'] > 0)
                    ? "\n| Convertible    = Yes"
                    : "\n| Convertible    = No",
                '{sells}' => ($item['Price{Low}'] > 0)
                    ? "\n| Sells          = ". $item['Price{Low}'] ."\n| HQ             = ". $item['CanBeHq']
                    : "\n| Sells          = No\n| HQ             = ". $item['CanBeHq'],
                '{dyeallowed}' => $Dye,
                '{crestallowed}' => $Crest,
                '{glamour}' => ($item['IsGlamourous'] == "True")
                    ? "\n| Projectable    = Yes"
                    : "",
                '{desynthesis}' => ($Desynth == "Yes")
                    ? "\n| Desynthesizable= Yes\n| Desynth Level  = ". $item['Level{Item}']
                    : "\n| Desynthesizable= No",
                '{repair}' => $Repair,
                '{physicaldamage}' => $PhysicalDamage,
                '{magicdamage}' => $MagicDamage,
                '{block}' => $Block,
                '{defense}' => $Defense,
                '{setbonus}' => $SetBonus,
                '{bonus}' => $BonusStat,
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
