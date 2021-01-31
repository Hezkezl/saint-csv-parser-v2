<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:CompanyCraft
 */
class CompanyCraft implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Top}{{ARR Infobox FCRecipe
{RequiredDraftItem}
{array}
}}{Bottom}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $CompanyCraftSequenceCsv = $this->csv('CompanyCraftSequence');
        $CompanyCraftPartCsv = $this->csv('CompanyCraftPart');
        $CompanyCraftProcessCsv = $this->csv('CompanyCraftProcess');
        $CompanyCraftSupplyItemCsv = $this->csv('CompanyCraftSupplyItem');
        $CompanyCraftDraftCsv = $this->csv('CompanyCraftDraft');
        $ItemCsv = $this->csv('Item');
        $CompanyCraftTypeCsv = $this->csv('CompanyCraftType');

        // (optional) start a progress bar
        $this->io->progressStart($CompanyCraftSequenceCsv->total);
        

        // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
        // manually, set to false
        $Bot = "false";

        // loop through data
        foreach ($CompanyCraftSequenceCsv->data as $id => $CompanyCraftSequence) {
            $this->io->progressAdvance();

            $ResultItem = $ItemCsv->at($CompanyCraftSequence["ResultItem"])["Name"];
            $RequiredDraftItem = "";
            $resultItemUrl  = str_replace(" ", "_", $ResultItem);
    
            // change the top and bottom code depending on if I want to bot the pages up or not
            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$resultItemUrl'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "https://ffxiv.gamerescape.com/w/index.php?title=$resultItemUrl/Recipe&action=edit\n";
                $Bottom = false;
            };

            if ($CompanyCraftSequence["CompanyCraftDraft"] < 0 ) {
                $RequiredDraftItem = "";
            } elseif ($CompanyCraftSequence["CompanyCraftDraft"] >= 0) {
                $RequiredDraftItemString = ucwords($CompanyCraftDraftCsv->at($CompanyCraftSequence["CompanyCraftDraft"])['Name']);
                $RequiredDraftItem = "|Acquired = ". $RequiredDraftItemString ."\n";
            }
            if (empty($ResultItem)) continue;
            $DraftCategory = $CompanyCraftSequence["CompanyCraftDraftCategory"];
            $ResultTypeSwitch = $CompanyCraftSequence["Category"];
            switch ($ResultTypeSwitch) {
                case 1:
                    $ResultType = "Aetherial Wheel Stand";
                break;
                case 2:
                    $ResultType = "Housing Small";
                break;
                case 3:
                    $ResultType = "Housing Medium";
                break;
                case 4:
                    $ResultType = "Housing Large";
                break;
                case 5:
                    $ResultType = "Airship";
                break;
                case 6:
                    $ResultType = "Aetherial Wheels";
                break;
                case 7:
                    $ResultType = "Submersibles";
                break;
                
                default:
                    $ResultType = "";
                break;
            }
            $array001 = [];
            $array002 = [];
            $array003 = [];
            foreach(range(0,7) as $a) {
                $CompanyCraftPartRaw = $CompanyCraftSequence["CompanyCraftPart[$a]"];
                if ($CompanyCraftPartRaw == 0) continue;
                $PhaseSwitch = "Phase";
                $CompanyCraftTypeCsv->at($CompanyCraftPartRaw)['Name'];
                $CompanyCraftTypeRaw = $CompanyCraftPartCsv->at($CompanyCraftPartRaw)['CompanyCraftType'];
                $bSwitch = FALSE;
                switch ($CompanyCraftTypeRaw) {
                    case 0:
                    case 1:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                        $PhaseSwitch = "Phase";
                    break;
                    case 8:
                        $PhaseSwitch = "Exterior Wall Phase";
                        $bSwitch = FALSE;
                    break;
                    case 9:
                        $PhaseSwitch = "Roof Phase";
                        $bSwitch = FALSE;
                    break;
                    case 10:
                    case 11:
                    case 12:
                    case 13:
                    case 14:
                    case 15:
                        $PhaseSwitch = $CompanyCraftTypeCsv->at($CompanyCraftPartCsv->at($CompanyCraftPartRaw)['CompanyCraftType'])['Name'];
                        $bSwitch = TRUE;
                    break;
                    case 16:
                    case 17:
                    case 18:
                    case 19:
                        $PhaseSwitch = "Phase";
                    break;
                    
                    default:
                        $PhaseSwitch = "Phase";
                    break;
                }
                $array002 = [];
                foreach(range(0,2) as $b) {
                    $CompanyCraftProcessRaw = $CompanyCraftPartCsv->at($CompanyCraftPartRaw)["CompanyCraftProcess[$b]"];
                    if ($CompanyCraftProcessRaw == 0) continue;
                    $array003 = [];
                    foreach(range(0,11) as $c) {
                        $SupplyItem = $ItemCsv->at($CompanyCraftSupplyItemCsv->at($CompanyCraftProcessCsv->at($CompanyCraftProcessRaw)["SupplyItem[$c]"])["Item"])["Name"];
                        if (empty($SupplyItem)) continue;
                        $SetQuantity = $CompanyCraftProcessCsv->at($CompanyCraftProcessRaw)["SetQuantity[$c]"];
                        $SetRequiredRaw = $CompanyCraftProcessCsv->at($CompanyCraftProcessRaw)["SetsRequired[$c]"];
                        $SetRequired = $SetQuantity * $SetRequiredRaw;
                        $bAdd = $b + 1;
                        $cAdd = $c + 1;
                        if ($bSwitch == FALSE) {
                            $NoPhaseSwitch = " ". $bAdd;
                        }
                        if ($bSwitch == TRUE) {
                            $NoPhaseSwitch = "";
                        }
                        $array003[] = "|". $PhaseSwitch ."". $NoPhaseSwitch ." ". $cAdd ." = ". $SupplyItem ."\n|". $PhaseSwitch ."". $NoPhaseSwitch ." ". $cAdd ." Amount = ". $SetQuantity ." / ". $SetRequired ."\n";
                    }
                    $array003 = implode("\n", $array003);
                    $array002[] = "". $array003 ."";
                }
                $array002 = implode("\n", $array002);
                $array001[] = "\n". $array002 ."";
            }
            $array001 = implode("\n", $array001);

            $output = "|Result = ". $ResultItem ."\n". $array001 ."";

            // Save some data
            $data = [
                '{Top}' => $Top,
                '{array}' => $output,
                '{resulturl}' => $resultItemUrl,
                '{RequiredDraftItem}' => $RequiredDraftItem,
                '{Bottom}' => $Bottom,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("$CurrentPatchOutput/CompanyCraft - ". $Patch .".txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}

/* Changelog
24 / 07 / 2020 - Created
*/
