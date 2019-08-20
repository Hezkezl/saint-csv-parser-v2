<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class Satisfaction implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '
{{-start-}}
\'\'\'{item}/Collectable\'\'\'
{{Custom Delivery
|Name = {item}
|NPC = {npc}
|Location = {location}
|Level = {id}
|Chance = {chance}
|Collectability Low = {collectlow}
|Collectability Mid = {collectmid}
|Collectability High = {collecthigh}
|Satisfaction Low = {satisfylow}
|Satisfaction Mid = {satisfymid}
|Satisfaction High = {satisfyhigh}
|Gil Low = {gillow}
|Gil Mid = {gilmid}
|Gil High = {gilhigh}
|Yellow Type = {yellowtype}
|Yellow Low = {yellowlow}
|Yellow Mid = {yellowmid}
|Yellow High = {yellowhigh}
|White Type = {whitetype}
|White Low = {whitelow}
|White Mid = {whitemid}
|White High = {whitehigh}
}}{{-stop-}}';

    public function parse()
    {

        // grab CSV files
        $SatisfactionNpcCsv = $this->csv('SatisfactionNpc');
        $SatisfactionSupplyCsv = $this->csv('SatisfactionSupply');
        $SatisfactionSupplyRewardCsv = $this->csv('SatisfactionSupplyReward');
        $ItemCsv = $this->csv('Item');
        $CurrencyCsv = $this->csv('Currency');

        $this->io->progressStart($SatisfactionSupplyCsv->total);

        // loop through quest data
        foreach ($SatisfactionSupplyCsv->data as $id => $item) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();

            //---------------------------------------------------------------------------------
            // Actual code definition begins below!
            //---------------------------------------------------------------------------------

            $Satisfaction = [];

                if ($item["Item"] > 0) {
                    $Name = $ItemCsv->at($item["Item"])['Name'];

                    $BaseCollect = $item["Collectability{Base}[$i]"];
                    $BaseScrip = $item["Reward{Scrips}[$i]"];
                    $Bonus1Collect = $item["Collectability{Bonus}[$i]"];
                    $Bonus2Collect = $item["Collectability{HighBonus}[$i]"];
                    $Bonus2Scrip = floor($BaseScrip * ($BonusMultiplier["CurrencyMultiplier[0]"]/1000));
                    $string = "{{-start-}}\n'''". $Name ."'''\n{{ARR Infobox Collectable\n";
                    $string .= "|Class = ". $Class ."\n|Level = ". $Level ."\n|Name = ". $Name ."\n|Scrip = ". $Currency ."\n";
                    $string .= "|Base = ". $BaseCollect ."\n|Base Scrip = ". $BaseScrip ."\n|Base EXP = ". $BaseEXP ."\n";
                    $string .= "|Bonus2 = ". $Bonus2Collect ."\n|Bonus2 Scrip = ". $Bonus2Scrip ."\n|Bonus2 EXP = ". $Bonus2EXP ."\n}}{{-stop-}}";
                    $Satisfaction[] = $string;
                }

            $Satisfaction = implode("\n", $Satisfaction);

            //---------------------------------------------------------------------------------

            $data = [
                '{item}' => $item,
                '{npc}' => $npc,
                '{location}' => $location,
                '{id}' => $id,
                '{chance}' => $chance,
                '{collectlow}' => $collectlow,
                '{collectmid}' => $collectmid,
                '{collecthigh}' => $collecthigh,
                '{satisfylow}' => $satisfylow,
                '{satisfymid}' => $satisfymid,
                '{satisfyhigh}' => $satisfyhigh,
                '{gillow}' => $gillow,
                '{gilmid}' => $gilmid,
                '{gilhigh}' => $gilhigh,
                '{yellowtype}' => $yellowtype,
                '{yellowlow}' => $yellowlow,
                '{yellowmid}' => $yellowmid,
                '{yellowhigh}' => $yellowhigh,
                '{whitetype}' => $whitetype,
                '{whitelow}' => $whitelow,
                '{whitemid}' => $whitemid,
                '{whitehigh}' => $whitehigh,
            ];

            // format using Gamer Escape formatter and add to data array
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeSatisfactionWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeSatisfactionWiki.txt');

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}
