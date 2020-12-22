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
        $MasterpieceCsv = $this->csv("MasterpieceSupplyDuty");
        $MultiplierCsv = $this->csv("MasterpieceSupplyMultiplier");
        $ParamgrowCsv = $this->csv("ParamGrow");
        $ItemCsv = $this->csv("Item");
        $ClassJobCsv = $this->csv("ClassJob");
        $HWDCrafterSupplyCsv = $this->csv("HWDCrafterSupply");
        $HWDCrafterSupplyRewardCsv = $this->csv("HWDCrafterSupplyReward");
        $HWDCraftersupplyTermCsv = $this->csv("HWDCrafterSupplyTerm");
        $HWDGathererInspectTermCsv = $this->csv("HWDGathereInspectTerm");
        $HWDGathererInspectionCsv = $this->csv("HWDGathererInspection");
        $HWDGathererInspectionRewardCsv = $this->csv("HWDGathererInspectionReward");

        $this->io->progressStart($MasterpieceCsv->total);
        foreach ($MasterpieceCsv->data as $id => $item) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();

            //---------------------------------------------------------------------------------
            // Actual code definition begins below!
            //---------------------------------------------------------------------------------

            $Collectable = [];

            foreach (range(0, 7) as $i) {
                $Class = $ClassJobCsv->at($item['ClassJob'])['Name{English}'];
                //$CurrencyID = $CurrencyCsv->at($item['Reward{Currency}'])['Item'];
                //$Currency = $ItemCsv->at($CurrencyID)['Name'];
                switch ($item['Reward{Currency}']) {
                    case 2:
                        $Currency = "Yellow Crafters' Scrip";
                        break;
                    case 4:
                        $Currency = "Yellow Gatherers' Scrip";
                        break;
                    case 6:
                        $Currency = "White Crafters' Scrip";
                        break;
                    case 7:
                        $Currency = "White Gatherers' Scrip";
                        break;
                    default:
                        $Currency = false;
                        break;
                }
                if ($item["RequiredItem[$i]"] > 0) {
                    $Name = $ItemCsv->at($item["RequiredItem[$i]"])['Name'];
                    $BonusMultiplier = $MultiplierCsv->at($item["BonusMultiplier[$i]"]);
                    $Level = $item["ClassJobLevel{Max}[$i]"];
                    $Star = str_repeat("{{Star}}", $item["Stars[$i]"]);
                    $LevelStar = ($item["Stars[$i]"] > 0) ? "$Level $Star" : "$Level";
                    $BaseCollect = $item["Collectability{Base}[$i]"];
                    $ExpModifier = $item["ExpModifier[$i]"];
                    $BaseScrip = $item["Reward{Scrips}[$i]"];
                    $ParamgrowEXP = $ParamgrowCsv->at($item["ClassJobLevel{Max}[$i]"])['ExpToNext'];
                    $BaseEXP = floor($ParamgrowEXP * ($ExpModifier/1000));
                    $Bonus1Collect = $item["Collectability{Bonus}[$i]"];
                    $Bonus1Scrip = floor($BaseScrip * ($BonusMultiplier["CurrencyMultiplier[1]"]/1000));
                    $Multiplier1 = $MultiplierCsv->at($item["BonusMultiplier[$i]"])['XpMultiplier[1]'];
                    $Bonus1EXP = floor($BaseEXP * ($Multiplier1/1000));
                    $Bonus2Collect = $item["Collectability{HighBonus}[$i]"];
                    $Bonus2Scrip = floor($BaseScrip * ($BonusMultiplier["CurrencyMultiplier[0]"]/1000));
                    $Multiplier2 = $MultiplierCsv->at($item["BonusMultiplier[$i]"])['XpMultiplier[0]'];
                    $Bonus2EXP = floor($BaseEXP * ($Multiplier2/1000));
                    $string = "{{-start-}}\n'''". $Name ."/Collectable'''\n{{ARR Infobox Collectable\n";
                    $string .= "|Class = ". $Class ."\n|Level = ". $LevelStar ."\n|Name = ". $Name ."\n|Scrip = ". $Currency ."\n";
                    $string .= "|Base = ". $BaseCollect ."\n|Base Scrip = ". $BaseScrip ."\n|Base EXP = ". $BaseEXP ."\n";
                    $string .= "|Bonus1 = ". $Bonus1Collect ."\n|Bonus1 Scrip = ". $Bonus1Scrip ."\n|Bonus1 EXP = ". $Bonus1EXP ."\n";
                    $string .= "|Bonus2 = ". $Bonus2Collect ."\n|Bonus2 Scrip = ". $Bonus2Scrip ."\n|Bonus2 EXP = ". $Bonus2EXP ."\n}}{{-stop-}}";
                    $Collectable[] = $string;
                }
            }

            $Collectable = implode("\n", $Collectable);

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
        $info = $this->save("$CurrentPatchOutput/Collectables - ". $Patch .".txt", 9999999);

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

            foreach (range(0, 16) as $i) {
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
        $info = $this->save("$CurrentPatchOutput/HWDCollectables - ". $Patch .".txt", 9999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}
