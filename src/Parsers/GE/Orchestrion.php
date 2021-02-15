<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * php bin/console app:parse:csv GE:Orchestrion
 */

class Orchestrion implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{Output}';

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files
        $OrchestrionCsv = $this->csv("Orchestrion");
        $OrchestrionUiparamCsv = $this->csv("OrchestrionUiparam");
        $OrchestrionCategoryCsv = $this->csv("OrchestrionCategory");

        $this->io->progressStart($OrchestrionCsv->total);

       //$this->PatchCheck($Patch, "Orchestrion", $OrchestrionCsv, "Name");
       //$PatchNumber = $this->getPatch("Orchestrion");
        // loop through test data
        foreach ($OrchestrionCsv->data as $id => $Orchestrion) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();
            if (empty($Orchestrion['Name'])) continue;
            $Name = str_replace(",","",$Orchestrion['Name']);
            $Acquisition = $Orchestrion['Description'];
            $Number = sprintf("%03d", $OrchestrionUiparamCsv->at($id)['Order']);
            if ($Number == "65535") {
                $Number = "";
            }
            $Category = $OrchestrionCategoryCsv->at($OrchestrionUiparamCsv->at($id)['OrchestrionCategory'])['Name'];
            
            $Contstructor = "{{-start-}}\n";
            $Contstructor .= "'''$Name Orchestrion Roll/Orchestrion'''\n";
            $Contstructor .= "{{OrchestrionLog\n";
            $Contstructor .= "| Name = $Name\n";
            $Contstructor .= "| Orchestrion Log = $Category\n";
            $Contstructor .= "| Orchestrion Log Number = $Number\n";
            $Contstructor .= "| Orchestrion Log Acquisition = $Acquisition\n";
            $Contstructor .= "}}\n";
            $Contstructor .= "{{-stop-}}\n";
            
            //---------------------------------------------------------------------------------

            $data = [
                '{Output}' => $Contstructor,
            ];

            // format using Gamer Escape formatter and add to data array
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeSatisfactionWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Orchestrion.txt", 999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}