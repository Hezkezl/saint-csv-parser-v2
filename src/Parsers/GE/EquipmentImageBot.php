<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * php bin/console app:parse:csv GE:EquipmentImageBot
 */

class EquipmentImageBot implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Male}{Female}\n\n------------------------------------\n\n{Weapon}\n\n{Acc}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files
        $ItemCsv = $this->csv("Item");

        $this->io->progressStart($ItemCsv->total);
        $this->PatchCheck($Patch, "Item", $ItemCsv);
        $PatchNumber = $this->getPatch("Item");

        // loop through test data
        $AllHeadArray = [];
        $AllBodyArray = [];
        $AllHandsArray = [];
        $AllLegsArray = [];
        $AllFeetArray = [];
        $MaleHeadArray = [];
        $MaleBodyArray = [];
        $MaleHandsArray = [];
        $MaleLegsArray = [];
        $MaleFeetArray = [];
        $FemaleHeadArray = [];
        $FemaleBodyArray = [];
        $FemaleHandsArray = [];
        $FemaleLegsArray = [];
        $FemaleFeetArray = [];
        $MaleArray = [];
        $FemaleArray = [];
        $WeaponArray = [];
        $MNKArray = [];
        $OneHandArray = [];
        $WARArray = [];
        $BRDArray = [];
        $DRGArray = [];
        $OneHandBLMArray = [];
        $BLMArray = [];
        $OneHandWHMArray = [];
        $WHMArray = [];
        $ShieldArray = [];
        $NINArray = [];
        $DRKArray = [];
        $MCHArray = [];
        $ASTArray = [];
        $SAMArray = [];
        $RDMArray = [];
        $GNBArray = [];
        $DNCArray = [];
        $SMNArray = [];        
        $MNKOut = "";
        $OneHandOut = "";
        $WAROut = "";
        $BRDOut = "";
        $DRGOut = "";
        $OneHandBLMOut = "";
        $BLMOut = "";
        $OneHandWHMOut = "";
        $WHMOut = "";
        $SHLDOut = "";
        $NINOut = "";
        $DRKOut = "";
        $MCHOut = "";
        $ASTOut = "";
        $SAMOut = "";
        $RDMOut = "";
        $GNBOut = "";
        $DNCOut = "";
        $SMNOut = "";
        foreach ($ItemCsv->data as $id => $Item) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();
            if (empty($Item['Name'])) continue;
            if ($PatchNumber[$id] != $Patch) continue;
            $Name = $Item['Name'];
            $Category = $Item['ItemUICategory'];
            $Restriction = $Item['EquipRestriction'];
            switch ($Restriction) {
                case '1': //all
                    switch ($Category) {
                        case '1'://MNK
                            $MNKArray[] = $Name;
                        break;
                        case '40'://Necklace
                            $NecklaceArray[] = $Name;
                        break;
                        case '41'://Earrings
                            $EarringArray[] = $Name;
                        break;
                        case '42'://Bracelet
                            $BraceletArray[] = $Name;
                        break;
                        case '43'://Ring
                            $RingArray[] = $Name;
                        break;
                        case '2':
                        case '12':
                        case '14':
                        case '16':
                        case '18':
                        case '20':
                        case '22':
                        case '24':
                        case '26':
                        case '28':
                        case '30':
                        case '32':
                        case '105':
                            $OneHandArray[] = $Name;//one handed
                        break;
                        case '3'://WAR
                            $WARArray[] = $Name;
                        break;
                        case '4'://BRD
                            $BRDArray[] = $Name;
                        break;
                        case '5'://DRG
                            $DRGArray[] = $Name;
                        break;
                        case '6'://OneHandBlm
                            $OneHandBLMArray[] = $Name;
                        break;
                        case '7'://BLM
                            $BLMArray[] = $Name;
                        break;
                        case '8'://OneHandWhm
                            $OneHandWHMArray[] = $Name;
                        break;
                        case '9'://WHM
                            $WHMArray[] = $Name;
                        break;
                        case '11'://Shield
                            $ShieldArray[] = $Name;
                        break;
                        case '84'://NIN
                            $NINArray[] = $Name;
                        break;
                        case '87'://DRK
                            $DRKArray[] = $Name;
                        break;
                        case '88'://MCH
                            $MCHArray[] = $Name;
                        break;
                        case '89'://AST
                            $ASTArray[] = $Name;
                        break;
                        case '96'://SAM
                            $SAMArray[] = $Name;
                        break;
                        case '97'://RDM
                            $RDMArray[] = $Name;
                        break;
                        case '106'://GNB
                            $GNBArray[] = $Name;
                        break;
                        case '107'://DNC
                            $DNCArray[] = $Name;
                        break;
                        case '10'://SMN/SCH
                        case '98':
                            $SMNArray[] = $Name;
                        break;
                        case '34':
                            $AllHeadArray[] = $Name;
                        break;
                        case '35':
                            $AllBodyArray[] = $Name;
                        break;
                        case '36':
                            $AllLegsArray[] = $Name;
                        break;
                        case '37':
                            $AllHandsArray[] = $Name;
                        break;
                        case '38':
                            $AllFeetArray[] = $Name;
                        break;
                        default:
                        break;
                    }
                break;
                case '2': //MALE
                    switch ($Category) {
                        case '34':
                            $MaleHeadArray[] = $Name;
                        break;
                        case '35':
                            $MaleBodyArray[] = $Name;
                        break;
                        case '36':
                            $MaleLegsArray[] = $Name;
                        break;
                        case '37':
                            $MaleHandsArray[] = $Name;
                        break;
                        case '38':
                            $MaleFeetArray[] = $Name;
                        break;
                        default:
                        break;
                    }
                break;
                case '3': //Female
                    switch ($Category) {
                        case '34':
                            $FemaleMaleHeadArray[] = $Name;
                        break;
                        case '35':
                            $FemaleMaleBodyArray[] = $Name;
                        break;
                        case '36':
                            $FemaleMaleLegsArray[] = $Name;
                        break;
                        case '37':
                            $FemaleMaleHandsArray[] = $Name;
                        break;
                        case '38':
                            $FemaleMaleFeetArray[] = $Name;
                        break;
                        default:
                        break;
                    }
                break;
                
                default:
                    switch ($Category) {
                        case '34':
                            $AllHeadArray[] = $Name;
                        break;
                        case '35':
                            $AllBodyArray[] = $Name;
                        break;
                        case '36':
                            $AllLegsArray[] = $Name;
                        break;
                        case '37':
                            $AllHandsArray[] = $Name;
                        break;
                        case '38':
                            $AllFeetArray[] = $Name;
                        break;
                        default:
                        break;
                    }
                break;
            }
        }


        
        $MNKOut = implode("\n", $MNKArray);
        if (!empty($MNKOut)) {$MNKOut = "\nEOP\n'01\n".$MNKOut;}
        $OneHandOut = implode("\n", $OneHandArray);
        if (!empty($OneHandArray)) {$OneHandOut = "\nEOP\n'02\n".$OneHandOut;}
        $WAROut = implode("\n", $WARArray);
        if (!empty($WARArray)) {$WAROut = "\nEOP\n'03\n".$WAROut;}
        $BRDOut = implode("\n", $BRDArray);
        if (!empty($BRDArray)) {$BRDOut = "\nEOP\n'04\n".$BRDOut;}
        $DRGOut = implode("\n", $DRGArray);
        if (!empty($DRGArray)) {$DRGOut = "\nEOP\n'05\n".$DRGOut;}
        $OneHandBLMOut = implode("\n", $OneHandBLMArray);
        if (!empty($OneHandBLMArray)) {$OneHandBLMOut = "\nEOP\n'06\n".$OneHandBLMOut;}
        $BLMOut = implode("\n", $BLMArray);
        if (!empty($BLMArray)) {$BLMOut = "\nEOP\n'07\n".$BLMOut;}
        $OneHandWHMOut = implode("\n", $OneHandWHMArray);
        if (!empty($OneHandWHMArray)) {$OneHandWHMOut = "\nEOP\n'08\n".$OneHandWHMOut;}
        $WHMOut = implode("\n", $WHMArray);
        if (!empty($WHMArray)) {$WHMOut = "\nEOP\n'09\n".$WHMOut;}
        $SHLDOut = implode("\n", $ShieldArray);
        if (!empty($ShieldArray)) {$SHLDOut = "\nEOP\n'11\n".$SHLDOut;}
        $NINOut = implode("\n", $NINArray);
        if (!empty($NINArray)) {$NINOut = "\nEOP\n'84\n".$NINOut;}
        $DRKOut = implode("\n", $DRKArray);
        if (!empty($DRKArray)) {$DRKOut = "\nEOP\n'87\n".$DRKOut;}
        $MCHOut = implode("\n", $MCHArray);
        if (!empty($MCHArray)) {$MCHOut = "\nEOP\n'88\n".$MCHOut;}
        $ASTOut = implode("\n", $ASTArray);
        if (!empty($ASTArray)) {$ASTOut = "\nEOP\n'89\n".$ASTOut;}
        $SAMOut = implode("\n", $SAMArray);
        if (!empty($SAMArray)) {$SAMOut = "\nEOP\n'96\n".$SAMOut;}
        $RDMOut = implode("\n", $RDMArray);
        if (!empty($RDMArray)) {$RDMOut = "\nEOP\n'97\n".$RDMOut;}
        $GNBOut = implode("\n", $GNBArray);
        if (!empty($GNBArray)) {$GNBOut = "\nEOP\n'106\n".$GNBOut;}
        $DNCOut = implode("\n", $DNCArray);
        if (!empty($DNCArray)) {$DNCOut = "\nEOP\n'107\n".$DNCOut;}
        $SMNOut = implode("\n", $SMNArray);
        if (!empty($SMNArray)) {$SMNOut = "\nEOP\n'98\n".$SMNOut;}
        $NeckOut = implode("\n", $NecklaceArray);
        if (!empty($NeckOut)) {$NeckOut = "\nEOP\n'40\n".$NeckOut;}
        $EarOut = implode("\n", $EarringArray);
        if (!empty($EarOut)) {$EarOut = "\nEOP\n'41\n".$EarOut;}
        $BraceletOut = implode("\n", $BraceletArray);
        if (!empty($BraceletOut)) {$BraceletOut = "\nEOP\n'42\n".$BraceletOut;}
        $RingOut = implode("\n", $RingArray);
        if (!empty($RingOut)) {$RingOut = "\nEOP\n'43\n".$RingOut;}
        //construct weaponstring:
        $WeaponOutput = "$MNKOut$OneHandOut$WAROut$BRDOut$DRGOut$OneHandBLMOut$BLMOut$OneHandWHMOut$WHMOut$SHLDOut$NINOut$DRKOut$MCHOut$ASTOut$SAMOut$RDMOut$GNBOut$DNCOut$SMNOut\nEOP\nEOF";
        $AccOutput = "$NeckOut$EarOut$BraceletOut$RingOut";
        $AllHeadItems = implode("\n", $AllHeadArray);
        $AllBodyItems = implode("\n", $AllBodyArray);
        $AllHandsItems = implode("\n", $AllHandsArray);
        $AllLegsItems = implode("\n", $AllLegsArray);
        $AllFeetItems = implode("\n", $AllFeetArray);

        $AllMaleHeadItems = "";
        $AllMaleBodyItems = "";
        $AllMaleHandsItems = "";
        $AllMaleLegsItems = "";
        $AllMaleFeetItems = "";

        if(!empty($MaleHeadArray)) {$AllMaleHeadItems = "\n".implode("\n", $MaleHeadArray);};
        if(!empty($MaleBodyArray)) {$AllMaleBodyItems = "\n".implode("\n", $MaleBodyArray);};
        if(!empty($MaleHandsArray)) {$AllMaleHandsItems = "\n".implode("\n", $MaleHandsArray);};
        if(!empty($MaleLegsArray)) {$AllMaleLegsItems = "\n".implode("\n", $MaleLegsArray);};
        if(!empty($MaleFeetArray)) {$AllMaleFeetItems = "\n".implode("\n", $MaleFeetArray);};

        
        $AllFemaleHeadItems = "";
        $AllFemaleBodyItems = "";
        $AllFemaleHandsItems = "";
        $AllFemaleLegsItems = "";
        $AllFemaleFeetItems = "";

        if(!empty($FemaleHeadArray)) {$AllFemaleHeadItems = "\n".implode("\n", $FemaleHeadArray);};
        if(!empty($FemaleBodyArray)) {$AllFemaleBodyItems = "\n".implode("\n", $FemaleBodyArray);};
        if(!empty($FemaleHandsArray)) {$AllFemaleHandsItems = "\n".implode("\n", $FemaleHandsArray);};
        if(!empty($FemaleLegsArray)) {$AllFemaleLegsItems = "\n".implode("\n", $FemaleLegsArray);};
        if(!empty($FemaleFeetArray)) {$AllFemaleFeetItems = "\n".implode("\n", $FemaleFeetArray);};
        
        $MaleOutput = "$AllHeadItems$AllMaleHeadItems\n";
        $MaleOutput .= "EOP\n";
        $MaleOutput .= "$AllBodyItems$AllMaleBodyItems\n";
        $MaleOutput .= "EOP\n";
        $MaleOutput .= "$AllHandsItems$AllMaleHandsItems\n";
        $MaleOutput .= "EOP\n";
        $MaleOutput .= "$AllLegsItems$AllMaleLegsItems\n";
        $MaleOutput .= "EOP\n";
        $MaleOutput .= "$AllFeetItems$AllMaleFeetItems\n";
        $MaleOutput .= "EOP\n";
        $MaleOutput .= "EOF\n\n------------------------------------\n\n";

        $FemaleOutput = "$AllHeadItems$AllFemaleHeadItems\n";
        $FemaleOutput .= "EOP\n";
        $FemaleOutput .= "$AllBodyItems$AllFemaleBodyItems\n";
        $FemaleOutput .= "EOP\n";
        $FemaleOutput .= "$AllHandsItems$AllFemaleHandsItems\n";
        $FemaleOutput .= "EOP\n";
        $FemaleOutput .= "$AllLegsItems$AllFemaleLegsItems\n";
        $FemaleOutput .= "EOP\n";
        $FemaleOutput .= "$AllFeetItems$AllFemaleFeetItems\n";
        $FemaleOutput .= "EOP\n";
        $FemaleOutput .= "EOF\n";
            
        //---------------------------------------------------------------------------------

        $data = [
            '{Male}' => $MaleOutput,
            '{Female}' => $FemaleOutput,
            '{Weapon}' => $WeaponOutput,
            '{Acc}' => $AccOutput,
        ];

        // format using Gamer Escape formatter and add to data array
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeSatisfactionWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("EquipmentImageBot.txt", 999999);

        $this->io->table(
            ['Filename', 'Data Count', 'File Size'],
            $info
        );
    }
}
