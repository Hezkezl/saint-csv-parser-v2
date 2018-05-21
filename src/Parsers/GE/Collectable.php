<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseDataHandlerTrait;
use App\Parsers\CsvParseTrait;
use App\Parsers\ParseHandler;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * GE:Collectable
 */
class Collectable extends ParseHandler implements ParseInterface
{
    use CsvParseTrait;
    use CsvParseDataHandlerTrait;


    // the wiki format we shall use
    //{{Collectable|Class=Carpenter|Name=Adamantite Spear|Level=58|Scrip=Blue|Base=3200|Base Scrip=58|Base EXP=137088|Bonus1=4500|Bonus2=5800+}}
    // Doesn't work atm
    const WIKI_FORMAT = '
        {{Collectable
        {id}
        {ClassJob}
        {ClassJobLevel}
        {RewardCurrency}
        {RequiredItem0}
        {Quantity0}
        {CollectabilityHighBonus0}
        {CollectabilityBonus0}
        {CollectabilityBase0}
        }}';


    public function parse()
    {
        // grab CSV files we want to use
        $MasterpieceCsv = $this->csv('MasterpieceSupplyDuty');
        $MultiplierCsv = $this->csv('MasterpieceSupplyMultiplier');
        $ParamgrowCsv = $this->csv('Paramgrow');

        // (optional) start a progress bar
        $progress = new ProgressBar($this->output, $MasterpieceCsv->total);

        // loop through data
        foreach($MasterpieceCsv->data as $id => $item) {
            // (optional) increment progress bar
            $progress->advance();

            // grab paramgrow (exp value) for this item
            // doesn't seem to work atm T_T
            $Paramgrow = $ParamgrowCsv->at($item['id']);

            // skip ones without a name
            if (empty($item['RequiredItem[0]'])) {
                continue;
            }

            // Save some data
            $this->save('Collectability.txt', [
                'id' => $item['id'],
                'ClassJob' => $item['ClassJob'],
                'ClassJobLevel' => $item['ClassJobLevel'],
                'RewardCurrency' => $item['Reward{Currency}'],
                'RequiredItem0' => $item['RequiredItem[0]'],
                'Quantity0' => $item['Quantity[0]'],
                'CollectabilityHighBonus0' => $item['Collectability{HighBonus}[0]'],
                'CollectabilityBonus0' => $item['Collectability{Bonus}[0]'],
                'CollectabilityBase0' => $item['Collectability{Base}[0]'],
                'Expmodifier0' => $item['ExpModifier[0]'],
                'RewardScrips0' => $item['Reward{Scrips}[0]'],
                'BonusMultiplier0' => $item['BonusMultiplier[0]'],
                'ClassJobLevelMax0' => $item['ClassJobLevel{Max}[0]'],
                'Stars0' => $item['Stars[0]'],
                'RequiredItem1' => $item['RequiredItem[1]'],
                'Quantity1' => $item['Quantity[1]'],
                'CollectabilityHighBonus1' => $item['Collectability{HighBonus}[1]'],
                'CollectabilityBonus1' => $item['Collectability{Bonus}[1]'],
                'CollectabilityBase1' => $item['Collectability{Base}[1]'],
                'ExpModifier1' => $item['ExpModifier[1]'],
                'RewardScrips1' => $item['Reward{Scrips}[1]'],
                'BonusMultiplier1' => $item['BonusMultiplier[1]'],
                'ClassJobLevelMax1' => $item['ClassJobLevel{Max}[1]'],
                'Stars1' => $item['Stars[1]'],
                'RequiredItem2' => $item['RequiredItem[2]'],
                'Quantity2' => $item['Quantity[2]'],
                'CollectabilityHighBonus2' => $item['Collectability{HighBonus}[2]'],
                'CollectabilityBonus2' => $item['Collectability{Bonus}[2]'],
                'CollectabilityBase2' => $item['Collectability{Base}[2]'],
                'ExpModifier2' => $item['ExpModifier[2]'],
                'RewardScrips2' => $item['Reward{Scrips}[2]'],
                'BonusMultiplier2' => $item['BonusMultiplier[2]'],
                'ClassJobLevelMax2' => $item['ClassJobLevel{Max}[2]'],
                'Stars2' => $item['Stars[2]'],
                'RequiredItem3' => $item['RequiredItem[3]'],
                'Quantity3' => $item['Quantity[3]'],
                'CollectabilityHighBonus3' => $item['Collectability{HighBonus}[3]'],
                'CollectabilityBonus3' => $item['Collectability{Bonus}[3]'],
                'CollectabilityBase3' => $item['Collectability{Base}[3]'],
                'ExpModifier3' => $item['ExpModifier[3]'],
                'RewardScrips3' => $item['Reward{Scrips}[3]'],
                'BonusMultiplier3' => $item['BonusMultiplier[3]'],
                'ClassJobLevelMax3' => $item['ClassJobLevel{Max}[3]'],
                'Stars3' => $item['Stars[3]'],
                'RequiredItem4' => $item['RequiredItem[4]'],
                'Quantity4' => $item['Quantity[4]'],
                'CollectabilityHighBonus4' => $item['Collectability{HighBonus}[4]'],
                'CollectabilityBonus4' => $item['Collectability{Bonus}[4]'],
                'CollectabilityBase4' => $item['Collectability{Base}[4]'],
                'ExpModifier4' => $item['ExpModifier[4]'],
                'RewardScrips4' => $item['Reward{Scrips}[4]'],
                'BonusMultiplier4' => $item['BonusMultiplier[4]'],
                'ClassJobLevelMax4' => $item['ClassJobLevel{Max}[4]'],
                'Stars4' => $item['Stars[4]'],
                'RequiredItem5' => $item['RequiredItem[5]'],
                'Quantity5' => $item['Quantity[5]'],
                'CollectabilityHighBonus5' => $item['Collectability{HighBonus}[5]'],
                'CollectabilityBonus5' => $item['Collectability{Bonus}[5]'],
                'CollectabilityBase5' => $item['Collectability{Base}[5]'],
                'ExpModifier5' => $item['ExpModifier[5]'],
                'RewardScrips5' => $item['Reward{Scrips}[5]'],
                'BonusMultiplier5' => $item['BonusMultiplier[5]'],
                'ClassJobLevelMax5' => $item['ClassJobLevel{Max}[5]'],
                'Stars5' => $item['Stars[5]'],
                'RequiredItem6' => $item['RequiredItem[6]'],
                'Quantity6' => $item['Quantity[6]'],
                'CollectabilityHighBonus6' => $item['Collectability{HighBonus}[6]'],
                'CollectabilityBonus6' => $item['Collectability{Bonus}[6]'],
                'CollectabilityBase6' => $item['Collectability{Base}[6]'],
                'ExpModifier6' => $item['ExpModifier[6]'],
                'RewardScrips6' => $item['Reward{Scrips}[6]'],
                'BonusMultiplier6' => $item['BonusMultiplier[6]'],
                'ClassJobLevelMax6' => $item['ClassJobLevel{Max}[6]'],
                'Stars6' => $item['Stars[6]'],
                'RequiredItem7' => $item['RequiredItem[7]'],
                'Quantity7' => $item['Quantity[7]'],
                'CollectabilityHighBonus7' => $item['Collectability{HighBonus}[7]'],
                'CollectabilityBonus7' => $item['Collectability{Bonus}[7]'],
                'CollectabilityBase7' => $item['Collectability{Base}[7]'],
                'ExpModifier7' => $item['ExpModifier[7]'],
                'RewardScrips7' => $item['Reward{Scrips}[7]'],
                'BonusMultiplier7' => $item['BonusMultiplier[7]'],
                'ClassJobLevelMax7' => $item['ClassJobLevel{Max}[7]'],
                'Stars7' => $item['Stars[7]'],
                'ExpToNext' => $Paramgrow['ExpToNext'],
            ]);

            // format using Gamer Escape Formater and add to data array
            // allegedly. This doesn't work atm.
            $this->item[] = GeFormatter::format(self::WIKI_FORMAT, $item);

        }

        // (optional) finish progress bar
        $progress->finish();
    }
}
