<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * php bin/console app:parse:csv GE:Satisfaction
 */

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
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files
        $SatisfactionNpcCsv = $this->csv("$CurrentPatch/SatisfactionNpc");
        $SatisfactionSupplyCsv = $this->csv("$CurrentPatch/SatisfactionSupply");
        $ENpcResidentCsv = $this->csv("$CurrentPatch/ENpcResident");
        $SatisfactionSupplyRewardCsv = $this->csv("$CurrentPatch/SatisfactionSupplyReward");
        $ItemCsv = $this->csv("$CurrentPatch/Item");
        $CurrencyCsv = $this->csv("$CurrentPatch/Currency");

        $this->io->progressStart($SatisfactionSupplyCsv->total);

        // loop through quest data
        foreach ($SatisfactionSupplyCsv->data as $id => $item) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();

            //---------------------------------------------------------------------------------
            // Actual code definition begins below!
            //---------------------------------------------------------------------------------

            $Satisfaction = false;

            // need to build a switch based off of the Key/id of Satisfaction Supply and have it
            // look in SatisfactionNpc's columns of "SupplyIndex" [0] through [5] to match up to

            // skip ones without a name
            if (empty($item['Item'])) {
                continue;
            } else {
                // get the NPC name
                $npcid = floor($item['id']);
                if ($npcid > 25) {
                    $npcid = $npcid - 25;
                } elseif ($npcid > 20) {
                    $npcid = $npcid - 20;
                } elseif ($npcid > 15) {
                    $npcid = $npcid - 15;
                } elseif ($npcid > 10) {
                    $npcid = $npcid - 10;
                } elseif ($npcid > 5) {
                    $npcid = $npcid - 5;
                }
                $npc = $ENpcResidentCsv->at($SatisfactionNpcCsv->at($npcid)['Npc'])['Singular'];

                $Name = $ItemCsv->at($item["Item"])['Name'];
                $location = "Blank";
                $chance = $item["Probability<%>"];
                $collectlow = $item["Collectability{Low}"];
                $collectmid = $item["Collectability{Mid}"];
                $collecthigh = $item["Collectability{High}"];
                //$reward = $SatisfactionSupplyRewardCsv->at($item["Reward"]);
                $satisfylow = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Satisfaction{Low}"];
                $satisfymid = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Satisfaction{Mid}"];
                $satisfyhigh = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Satisfaction{High}"];
                $gillow = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Gil{Low}"];
                $gilmid = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Gil{Mid}"];
                $gilhigh = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Gil{High}"];
                $yellowlow = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Quantity{Low}[0]"];
                $yellowmid = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Quantity{Mid}[0]"];
                $yellowhigh = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Quantity{High}[0]"];
                $whitelow = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Quantity{Low}[1]"];
                $whitemid = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Quantity{Mid}[1]"];
                $whitehigh = $SatisfactionSupplyRewardCsv->at($item["Reward"])["Quantity{High}[1]"];
                $yellowtype = $ItemCsv->at($CurrencyCsv->at($SatisfactionSupplyRewardCsv->at($item["Reward"])["Reward{Currency}[0]"])["Item"])["Name"];
                $whitetype = $ItemCsv->at($CurrencyCsv->at($SatisfactionSupplyRewardCsv->at($item["Reward"])["Reward{Currency}[1]"])["Item"])["Name"];
                }

            //---------------------------------------------------------------------------------

            $data = [
                '{item}' => $Name,
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
        $info = $this->save("$CurrentPatchOutput/GeSatisfactionWiki - ". $Patch .".txt", 999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}
