<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * php bin/console app:parse:csv GE:SatisfactionTable
 */

class SatisfactionTable implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{Name}
{Table}
';

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files
        $SatisfactionNpcCsv = $this->csv("SatisfactionNpc");
        $SatisfactionSupplyCsv = $this->csv("SatisfactionSupply");
        $ENpcResidentCsv = $this->csv("ENpcResident");
        $SatisfactionSupplyRewardCsv = $this->csv("SatisfactionSupplyReward");
        $ItemCsv = $this->csv("Item");
        $QuestCsv = $this->csv("Quest");
        $PlaceNameCsv = $this->csv("PlaceName");

        $this->io->progressStart($SatisfactionNpcCsv->total);

        // loop through quest data
        foreach ($SatisfactionNpcCsv->data as $id => $Npc) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();

            $Name = $ENpcResidentCsv->at($Npc['Npc'])['Singular'];
            $OutputRanks = [];
            foreach(range(1,4) as $a) {
                $Satisfaction = $Npc["Satisfaction{Required}[$a]"];
                $OutputRewards = [];
                foreach(range(0,2) as $b) {
                    $RewardsAmount = $Npc["ItemCount[$a][$b]"];
                    $OutputRewards[] =  $ItemCsv->at($Npc["Item[$a][$b]"])['Name']."";
                }
                $Rewards = implode(", ", $OutputRewards);
                $OutputRanks[] = "{{SatisfactionLevel|NPC=$Name|Level=$a|Satisfaction=$Satisfaction|Rewards=$Rewards|Title = }}";
            }
            $Table = implode("\n", $OutputRanks);

            //---------------------------------------------------------------------------------

            $data = [
                '{Name}' => $Name,
                '{Table}' => $Table,
            ];

            // format using Gamer Escape formatter and add to data array
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeSatisfactionWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("SatisfactionTable.txt", 999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}