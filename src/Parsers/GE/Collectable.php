<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class Collectable implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{Collectable}';

    public function parse()
    {

        // grab CSV files
        $MasterpieceCsv = $this->csv('MasterpieceSupplyDuty');
        $MultiplierCsv = $this->csv('MasterpieceSupplyMultiplier');
        $ParamgrowCsv = $this->csv('Paramgrow');
        $ItemCsv = $this->csv('Item');
        $ClassJobCsv = $this->csv('ClassJob');
        $CurrencyCsv = $this->csv('Currency');

        $this->io->progressStart($MasterpieceCsv->total);

        // loop through quest data
        foreach ($MasterpieceCsv->data as $id => $item) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();

            //---------------------------------------------------------------------------------
            // Actual code definition begins below!
            //---------------------------------------------------------------------------------

            $Collectable = [];

            foreach (range(0, 7) as $i) {
                $Class = $ClassJobCsv->at($item['ClassJob'])['Name{English}'];
                $CurrencyID = $CurrencyCsv->at($item['Reward{Currency}'])['Item'];
                $Currency = $ItemCsv->at($CurrencyID)['Name'];
                if ($item["RequiredItem[$i]"] > 0) {
                    $Name = $ItemCsv->at($item["RequiredItem[$i]"])['Name'];
                    $BonusMultiplier = $MultiplierCsv->at($item["BonusMultiplier[$i]"]);
                    $Level = $item["ClassJobLevel{Max}[$i]"];
                    $BaseCollect = $item["Collectability{Base}[$i]"];
                    $BaseScrip = $item["Reward{Scrips}[$i]"];
                    $ParamgrowEXP = $ParamgrowCsv->at($item["ClassJobLevel{Max}[$i]"])['ExpToNext'];
                    $BaseEXP = floor($ParamgrowEXP * ($item['ExpModifier[0]']/1000));
                    $Bonus1Collect = $item["Collectability{Bonus}[$i]"];
                    $Bonus1Scrip = floor($BaseScrip * ($BonusMultiplier["CurrencyMultiplier[1]"]/1000));
                    $Bonus1EXP = floor($BaseEXP * ($BonusMultiplier["XpMultiplier[1]"]/1000));
                    $Bonus2Collect = $item["Collectability{HighBonus}[$i]"];
                    $Bonus2Scrip = floor($BaseScrip * ($BonusMultiplier["CurrencyMultiplier[0]"]/1000));
                    $Bonus2EXP = floor($BaseEXP * ($BonusMultiplier["XpMultiplier[0]"]/1000));
                    $string = "{{-start-}}\n'''". $Name ."'''\n{{ARR Infobox Collectable\n";
                    $string .= "|Class = ". $Class ."\n|Level = ". $Level ."\n|Name = ". $Name ."\n|Scrip = ". $Currency ."\n";
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
        $info = $this->save('GeCollectWiki.txt');

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}
