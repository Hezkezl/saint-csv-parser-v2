<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * php bin/console app:parse:csv GE:Collectable
 */

class Collectable implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{Collectable}';

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');
        // grab CSV files
        $ParamgrowCsv = $this->csv("ParamGrow");
        $ItemCsv = $this->csv("Item");
        $ClassJobCsv = $this->csv("ClassJob");
        $HWDCrafterSupplyCsv = $this->csv("HWDCrafterSupply");
        $HWDCrafterSupplyRewardCsv = $this->csv("HWDCrafterSupplyReward");
        $HWDCraftersupplyTermCsv = $this->csv("HWDCrafterSupplyTerm");
        $HWDGathererInspectTermCsv = $this->csv("HWDGathereInspectTerm");
        $HWDGathererInspectionCsv = $this->csv("HWDGathererInspection");
        $HWDGathererInspectionRewardCsv = $this->csv("HWDGathererInspectionReward");
        $CollectablesShopCsv = $this->csv("CollectablesShop");
        $CollectablesShopItemCsv = $this->csv("CollectablesShopItem");
        $CollectablesShopItemGroupCsv = $this->csv("CollectablesShopItemGroup");
        $CollectablesShopRefineCsv = $this->csv("CollectablesShopRefine");
        $CollectablesShopRewardScripCsv = $this->csv("CollectablesShopRewardScrip");
        $CollectablesShopRewardItemCsv = $this->csv("CollectablesShopRewardItem");
        
        $CurrencyArray = $this->GetCurrency();
        $this->io->progressStart($CollectablesShopCsv->total);
        foreach ($CollectablesShopCsv->data as $id => $Shop) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();

            //---------------------------------------------------------------------------------
            // Actual code definition begins below!
            //---------------------------------------------------------------------------------

            $StringArray = [];
            
            $RewardType = $Shop['RewardType'];
            foreach (range(0, 10) as $i) {
                //if ($Shop["ShopItems[$i]"] === 0) continue;
                switch($i) {
                        case 0:
                            $Class = "Carpenter";
                        break;
                        case 1:
                            $Class = "Blacksmith";
                        break;
                        case 2:
                            $Class = "Armorer";
                        break;
                        case 3:
                            $Class = "Goldsmith";
                        break;
                        case 4:
                            $Class = "Leatherworker";
                        break;
                        case 5:
                            $Class = "Weaver";
                        break;
                        case 6:
                            $Class = "Alchemist";
                        break;
                        case 7:
                            $Class = "Culinarian";
                        break;
                        case 8:
                            $Class = "Miner";
                        break;
                        case 9:
                            $Class = "Botanist";
                        break;
                        case 10:
                            $Class = "Fisher";
                        break;
                }
                $ShopItemID = $Shop["ShopItems[$i]"];
                foreach(range(0,999) as $b) {
                    $SubDataValue = "". $ShopItemID .".". $b ."";
                    if (empty($ItemCsv->at($CollectablesShopItemCsv->at($SubDataValue)['Item'])['Name'])) break;
                    $Group = $CollectablesShopItemGroupCsv->at($CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopItemGroup'])['Name'];
                    $Item = $ItemCsv->at($CollectablesShopItemCsv->at($SubDataValue)['Item'])['Name'];
                    $LevelMin = $CollectablesShopItemCsv->at($SubDataValue)['LevelMin'];
                    $LevelMax = $CollectablesShopItemCsv->at($SubDataValue)['LevelMax'];
                    $LowCollect = $CollectablesShopRefineCsv->at($CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRefine'])['LowCollectability'];
                    $MidCollect = $CollectablesShopRefineCsv->at($CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRefine'])['MidCollectability'];
                    $HighCollect = $CollectablesShopRefineCsv->at($CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRefine'])['HighCollectability'];
                    $Star = str_repeat("★",$CollectablesShopItemCsv->at($SubDataValue)['Stars']);
                    //gather rewards script 
                    if ($RewardType === "1") {
                        $RewardSheetLink = $CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRewardScrip'];
                        $Currency = $ItemCsv->at($CurrencyArray[$CollectablesShopRewardScripCsv->at($RewardSheetLink)['Currency']])['Name'];
                        $LowReward = $CollectablesShopRewardScripCsv->at($RewardSheetLink)['LowReward'];
                        $MidReward = $CollectablesShopRewardScripCsv->at($RewardSheetLink)['MidReward'];
                        $HighReward = $CollectablesShopRewardScripCsv->at($RewardSheetLink)['HighReward'];
                        $ExpRatioLow = $CollectablesShopRewardScripCsv->at($RewardSheetLink)['ExpRatioLow'];
                        $ExpRatioMid = $CollectablesShopRewardScripCsv->at($RewardSheetLink)['ExpRatioMid'];
                        $ExpRatioHigh = $CollectablesShopRewardScripCsv->at($RewardSheetLink)['ExpRatioHigh'];

                        //ExpMaths
                        $ParamgrowEXP = $ParamgrowCsv->at($LevelMax)['ExpToNext'];
                        $BaseExp = floor($ParamgrowEXP * ($ExpRatioLow/1000));
                        $Bonus1EXP = floor($ParamgrowEXP * ($ExpRatioMid/1000));
                        $Bonus2EXP = floor($ParamgrowEXP * ($ExpRatioHigh/1000));
                        $String = "{{-start-}}\n";
                        $String .= "'''$Item/Collectable'''\n";
                        $String .= "{{ARR Infobox Collectable\n";
                        $String .= "|Class = $Class\n";
                        $String .= "|Level = $LevelMax\n";
                        $String .= "|Name = $Item\n";
                        $String .= "|Scrip = $Currency\n";
                        $String .= "|Base = $LowCollect\n";
                        $String .= "|Base Scrip = $LowReward\n";
                        $String .= "|Base EXP = $BaseExp\n";
                        $String .= "|Bonus1 = $MidCollect\n";
                        $String .= "|Bonus1 Scrip = $MidReward\n";
                        $String .= "|Bonus1 EXP = $Bonus1EXP\n";
                        $String .= "|Bonus2 = $HighCollect\n";
                        $String .= "|Bonus2 Scrip = $HighReward\n";
                        $String .= "|Bonus2 EXP = $Bonus2EXP\n";
                        $String .= "|Group = $Group\n";
                        $String .= "}}\n";
                        $String .= "{{-stop-}}\n";
                        $String .= "\n";
                    }
                    if ($RewardType === "2") {
                        $RewardSheetLink = $CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRewardScrip'];
                        $ItemReward = $ItemCsv->at($CollectablesShopRewardItemCsv->at($RewardSheetLink)['Item'])['Name'];
                        $LowReward = $CollectablesShopRewardItemCsv->at($RewardSheetLink)['RewardLow'];
                        $MidReward = $CollectablesShopRewardItemCsv->at($RewardSheetLink)['RewardMid'];
                        $HighReward = $CollectablesShopRewardItemCsv->at($RewardSheetLink)['RewardHigh'];
                        $String = "{{-start-}}\n";
                        $String .= "'''$Item/Collectable'''\n";
                        $String .= "{{ARR Infobox Collectable\n";
                        $String .= "|Class = $Class\n";
                        $String .= "|Level = $LevelMax\n";
                        $String .= "|Name = $Item\n";
                        $String .= "|Scrip = \n";
                        $String .= "|Base = $LowCollect\n";
                        $String .= "|Base Scrip = $LowReward\n";
                        $String .= "|Bonus1 = $MidCollect\n";
                        $String .= "|Bonus1 Scrip = $MidReward\n";
                        $String .= "|Bonus2 = $HighCollect\n";
                        $String .= "|Bonus2 Scrip = $HighReward\n";
                        $String .= "|Group = $Group\n";
                        $String .= "}}\n";
                        $String .= "{{-stop-}}\n";
                        $String .= "\n";
                    }
                    $StringArray[] = $String;
                }
            }

            $Collectable = implode("\n", $StringArray);

            //---------------------------------------------------------------------------------

            $data = [
                '{Collectable}' => $Collectable,
            ];

            // format using Gamer Escape formatter and add to data array
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeCollectWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Collectables.txt", 9999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );

        $console = new ConsoleOutput();
        $console->writeln(" Loading CSV files");
        $console->writeln(" Processing HWDCrafterSupply");

        // switch to a section so we can overwrite
        $console = $console->section();

        foreach ($HWDCrafterSupplyCsv->data as $id => $item) {
            //---------------------------------------------------------------------------------
            // Actual code definition begins below!
            //---------------------------------------------------------------------------------

            $HWDCollectable = [];
            $HWDClass = false;
            $id = $item['id'];

            foreach (range(0, 22) as $i) {
                if ($item["Item{TradeIn}[$i]"] > 0) {
                    switch($item['id']) {
                    case 0; case NULL; default;
                        break;
                    case 1:
                        $HWDClass = "Carpenter";
                        break;
                    case 2:
                        $HWDClass = "Blacksmith";
                        break;
                    case 3:
                        $HWDClass = "Armorer";
                        break;
                    case 4:
                        $HWDClass = "Goldsmith";
                        break;
                    case 5:
                        $HWDClass = "Leatherworker";
                        break;
                    case 6:
                        $HWDClass = "Weaver";
                        break;
                    case 7:
                        $HWDClass = "Alchemist";
                        break;
                    case 8:
                        $HWDClass = "Culinarian";
                        break;
                }
                    $HWDLevel = $item["Level[$i]"];
                    $HWDName = $ItemCsv->at($item["Item{TradeIn}[$i]"])['Name'];
                    $HWDCurrency = "Skybuilders' Scrip";
                    $HWDPhase = $HWDCraftersupplyTermCsv->at($item["TermName[$i]"])["Name"];
                    $HWDBaseCollect = $item["BaseCollectable{Rating}[$i]"];
                    $HWDBaseScrip = $HWDCrafterSupplyRewardCsv->at($item["BaseCollectable{Reward}[$i]"])["ScriptReward{Amount}"];
                    $HWDBaseEXP = $HWDCrafterSupplyRewardCsv->at($item["BaseCollectable{Reward}[$i]"])["ExpReward"];
                    $HWDBonus1Collect = $item["MidCollectable{Rating}[$i]"];
                    $HWDBonus1Scrip = $HWDCrafterSupplyRewardCsv->at($item["MidCollectable{Reward}[$i]"])["ScriptReward{Amount}"];
                    $HWDBonus1EXP = $HWDCrafterSupplyRewardCsv->at($item["MidCollectable{Reward}[$i]"])["ExpReward"];
                    $HWDBonus2Collect = $item["HighCollectable{Rating}[$i]"];
                    $HWDBonus2Scrip = $HWDCrafterSupplyRewardCsv->at($item["HighCollectable{Reward}[$i]"])["ScriptReward{Amount}"];
                    $HWDBonus2EXP = $HWDCrafterSupplyRewardCsv->at($item["HighCollectable{Reward}[$i]"])["ExpReward"];
                    $HWDstring = "{{-start-}}\n'''". $HWDName ."/Collectable'''\n{{ARR Infobox Collectable\n";
                    $HWDstring .= "|Class = ". $HWDClass ."\n|Level = ". $HWDLevel ."\n|Name = ". $HWDName ."\n|Scrip = ". $HWDCurrency ."\n|Phase = ". $HWDPhase ."\n";
                    $HWDstring .= "|Base = ". $HWDBaseCollect ."\n|Base Scrip = ". $HWDBaseScrip ."\n|Base EXP = ". $HWDBaseEXP ."\n";
                    $HWDstring .= "|Bonus1 = ". $HWDBonus1Collect ."\n|Bonus1 Scrip = ". $HWDBonus1Scrip ."\n|Bonus1 EXP = ". $HWDBonus1EXP ."\n";
                    $HWDstring .= "|Bonus2 = ". $HWDBonus2Collect ."\n|Bonus2 Scrip = ". $HWDBonus2Scrip ."\n|Bonus2 EXP = ". $HWDBonus2EXP ."\n}}{{-stop-}}";
                    $HWDCollectable[] = $HWDstring;
                }
            }

            $HWDCollectable = implode("\n", $HWDCollectable);

            //---------------------------------------------------------------------------------

            $data = [
                '{Collectable}' => $HWDCollectable,
            ];

            // format using Gamer Escape formatter and add to data array
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeCollectWiki.txt
        $console->overwrite(" > Completed HWDCrafter ID: {$id}");
        $this->io->text('Saving ...');
        $info = $this->save("HWDCollectables.txt", 9999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}
