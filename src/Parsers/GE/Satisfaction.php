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
    const WIKI_FORMAT = '{Output}';

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files
        $SatisfactionNpcCsv = $this->csv("SatisfactionNpc");
        $SatisfactionSupplyCsv = $this->csv("SatisfactionSupply");
        $ENpcResidentCsv = $this->csv("ENpcResident");
        $SatisfactionSupplyRewardCsv = $this->csv("SatisfactionSupplyReward");
        $ItemCsv = $this->csv("Item");
        $LevelCsv = $this->csv("Level");
        $TerritoryTypeCsv = $this->csv("TerritoryType");
        $PlaceNameCsv = $this->csv("PlaceName");

        $this->io->text('Generating Locations ...');
        $this->io->progressStart($LevelCsv->total);
        foreach($LevelCsv->data as $id => $Level) {
            $this->io->progressAdvance();
            if ($Level['Type'] != 8) continue;
            $NPCID = $Level['Object'];
            $LGBArray[$NPCID] = array(
                'Location' => $PlaceNameCsv->at($TerritoryTypeCsv->at($Level['Territory'])['PlaceName'])['Name']
            );
        }
        $this->io->progressFinish();

        // loop through npc data
        $Array = []; 
        $this->io->progressStart($SatisfactionNpcCsv->total);
        $this->io->text('Generating Satisfaction NPCs ...');
        foreach ($SatisfactionNpcCsv->data as $id => $supplynpc) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();

            //---------------------------------------------------------------------------------
            // Actual code definition begins below!
            //---------------------------------------------------------------------------------

            $Satisfaction = false;

            // skip ones without a name
            $Npc = $ENpcResidentCsv->at($supplynpc['Npc'])['Singular'];
            $NpcId = $supplynpc['Npc'];
            // get the NPC name
            if (empty($Npc)) continue;
            foreach(range(1,5) as $a) {
                foreach(range(0,20) as $b) {
                    $SubDataValue = "". $supplynpc["SupplyIndex[$a]"] .".". $b ."";
                    if (empty($ItemCsv->at($SatisfactionSupplyCsv->at($SubDataValue)['Item'])['Name'])) break;
                    $Name = $ItemCsv->at($SatisfactionSupplyCsv->at($SubDataValue)['Item'])['Name'];
                    if(empty($LGBArray[$NpcId]['Location'])){
                        $location = "Blank";
                    }
                    $location = $LGBArray[$NpcId]['Location'];
                    $chance = $SatisfactionSupplyCsv->at($SubDataValue)["Probability<%>"];
                    $collectlow = $SatisfactionSupplyCsv->at($SubDataValue)["Collectability{Low}"];
                    $collectmid = $SatisfactionSupplyCsv->at($SubDataValue)["Collectability{Mid}"];
                    $collecthigh = $SatisfactionSupplyCsv->at($SubDataValue)["Collectability{High}"];
                    //$reward = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"]);
                    $satisfylow = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Satisfaction{Low}"];
                    $satisfymid = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Satisfaction{Mid}"];
                    $satisfyhigh = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Satisfaction{High}"];
                    $gillow = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Gil{Low}"];
                    $gilmid = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Gil{Mid}"];
                    $gilhigh = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Gil{High}"];
                    $yellowlow = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Quantity{Low}[0]"];
                    $yellowmid = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Quantity{Mid}[0]"];
                    $yellowhigh = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Quantity{High}[0]"];
                    $whitelow = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Quantity{Low}[1]"];
                    $whitemid = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Quantity{Mid}[1]"];
                    $whitehigh = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Quantity{High}[1]"];
                    $yellowtypenumber = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Reward{Currency}[0]"];
                    $whitetypenumber = $SatisfactionSupplyRewardCsv->at($SatisfactionSupplyCsv->at($SubDataValue)["Reward"])["Reward{Currency}[1]"];
        
                    switch ($yellowtypenumber) {
                        case 2:
                            $yellowtype = "Yellow Crafters' Scrip";
                            break;
                        case 4:
                            $yellowtype = "Yellow Gatherers' Scrip";
                            break;
                        case 6:
                            $yellowtype = "White Crafters' Scrip";
                            break;
                        case 7:
                            $yellowtype = "White Gatherers' Scrip";
                            break;
                        default:
                            $yellowtype = false;
                            break;
                    }
                    switch ($whitetypenumber) {
                        case 2:
                            $whitetype = "Yellow Crafters' Scrip";
                            break;
                        case 4:
                            $whitetype = "Yellow Gatherers' Scrip";
                            break;
                        case 6:
                            $whitetype = "White Crafters' Scrip";
                            break;
                        case 7:
                            $whitetype = "White Gatherers' Scrip";
                            break;
                        default:
                            $whitetype = false;
                            break;
                    }
                    
                    $OutputString = "{{-start-}}\n";
                    $OutputString .= "'''$Name/Collectable'''\n";
                    $OutputString .= "{{Custom Delivery\n";
                    $OutputString .= "|Name = $Name\n";
                    $OutputString .= "|NPC = $Npc\n";
                    $OutputString .= "|Location = $location\n";
                    $OutputString .= "|Level = $SubDataValue\n";
                    $OutputString .= "|Chance = $chance\n";
                    $OutputString .= "|Collectability Low = $collectlow\n";
                    $OutputString .= "|Collectability Mid = $collectmid\n";
                    $OutputString .= "|Collectability High = $collecthigh\n";
                    $OutputString .= "|Satisfaction Low = $satisfylow\n";
                    $OutputString .= "|Satisfaction Mid = $satisfymid\n";
                    $OutputString .= "|Satisfaction High = $satisfyhigh\n";
                    $OutputString .= "|Gil Low = $gillow\n";
                    $OutputString .= "|Gil Mid = $gilmid\n";
                    $OutputString .= "|Gil High = $gilhigh\n";
                    $OutputString .= "|Yellow Type = $yellowtype\n";
                    $OutputString .= "|Yellow Low = $yellowlow\n";
                    $OutputString .= "|Yellow Mid = $yellowmid\n";
                    $OutputString .= "|Yellow High = $yellowhigh\n";
                    $OutputString .= "|White Type = $whitetype\n";
                    $OutputString .= "|White Low = $whitelow\n";
                    $OutputString .= "|White Mid = $whitemid\n";
                    $OutputString .= "|White High = $whitehigh\n";
                    $OutputString .= "}}\n";
                    $OutputString .= "{{-stop-}}\n";
                    $Array[] = $OutputString;
                }

            }
        }
        $Output = implode("\n", $Array);
        $this->io->progressFinish();

        //---------------------------------------------------------------------------------

        $data = [
            '{Output}' => $Output,
        ];

        // format using Gamer Escape formatter and add to data array
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeSatisfactionWiki.txt
        $this->io->text('Saving ...');
        $info = $this->save("SatisfactionNPC.txt", 999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}
