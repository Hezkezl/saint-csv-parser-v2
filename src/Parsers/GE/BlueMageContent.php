<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:BlueMageContent
 */
class BlueMageContent implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Content}{Score}{Action}{SpellBook}";

    public function parse()
    {
      include (dirname(__DIR__) . '/Paths.php');
        // grab CSV files we want to use
        $ContentFinderConditionCsv = $this->csv("ContentFinderCondition");
        $QuestCsv = $this->csv("Quest");
        $AOZContentCsv = $this->csv("AOZContent");
        $AOZArrangementCsv = $this->csv("AOZArrangement");
        $AOZContentBriefingBNpcCsv = $this->csv("AOZContentBriefingBNpc");
        $BNpcNameCsv = $this->csv("BNpcName");
        $AOZBossCsv = $this->csv("AOZBoss");
        $AOZScoreCsv = $this->csv("AOZScore");
        $AozActionCsv = $this->csv("AozAction");
        $AozActionTransientCsv = $this->csv("AozActionTransient");
        $ActionCsv = $this->csv("Action");
        $ActionCategoryCsv = $this->csv("ActionCategory");
        $ClassJobCategoryCsv = $this->csv("ClassJobCategory");
        $ActionTransientCsv = $this->csv("ActionTransient");
        $ContentTypeCsv = $this->csv("ContentType");
        $PlaceNameCsv = $this->csv("PlaceName");
        // (optional) start a progress bar
        $this->io->progressStart($ContentFinderConditionCsv->total);

        $ContentArray = [];
        // loop through data
        //get patch for content
        $this->PatchCheck($Patch, "ContentFinderCondition", $ContentFinderConditionCsv);
        $PatchNumber = $this->getPatch("ContentFinderCondition");
        
        $this->PatchCheck($Patch, "AozAction", $AozActionCsv);
        $PatchNumberAction = $this->getPatch("AozAction");
        foreach ($ContentFinderConditionCsv->data as $id => $Content) {
            $this->io->progressAdvance();
            $ShortCode = $Content['ShortCode'];
            if (strpos($ShortCode, 'aoz') === false) continue;
            $ContentName = $Content['Name'];
            $Patch = $PatchNumber[$id];
            $ContentID = (int)str_ireplace("aoz","",$ShortCode);
            $OrderID = str_pad($ContentID, 2, '0', STR_PAD_LEFT);
            $ContentImage = $Content['Image'].".png";
            $RequiredLevel = $Content['ClassJobLevel{Required}'];
            $RequiredQuest = $QuestCsv->at($Content['UnlockQuest'])['Name'];

            $GilReward = $AOZContentCsv->at($ContentID)['GilReward'];
            $AlliedSealsReward = $AOZContentCsv->at($ContentID)['AlliedSealsReward'];
            $TomestonesReward = $AOZContentCsv->at($ContentID)['TomestonesReward'];
            $StandardFinishTime = gmdate("i:s",$AOZContentCsv->at($ContentID)['StandardFinishTime']);
            $IdealFinishTime = gmdate("i:s",$AOZContentCsv->at($ContentID)['IdealFinishTime']);
            $ActArray = [];
            foreach(range(1,3) as $i) {
                $EnemiesArray = [];
                if ($AOZContentCsv->at($ContentID)["Act$i"] == 0) continue;
                $ActMonsters = $AOZContentCsv->at($ContentID)["Act$i"];
                $ArenaType = $AOZContentCsv->at($ContentID)["ArenaType[$i]"] + 1;
                $Arena = "07248$ArenaType.png";
                $BnpcNamePosArray = [];
                $FightType = $AOZContentCsv->at($ContentID)["Act{$i}FightType"];
                switch ($FightType) {
                    case 1:
                        foreach(range(0,20) as $b) {
                            $VulnArray = [];
                            $SubDataValue = "". $ActMonsters .".". $b ."";
                            if (empty($AOZArrangementCsv->at($SubDataValue)['AOZContentBriefingBNpc'])) break;
                            $BNPCName =  ucwords($BNpcNameCsv->at($AOZContentBriefingBNpcCsv->at($AOZArrangementCsv->at($SubDataValue)['AOZContentBriefingBNpc'])['BNpcName'])['Singular']);
                            $EnemiesArray[] = "{{colorlink|Light Blue|".$BNPCName."}}";
                            $BNPCPosRaw = $AOZArrangementCsv->at($SubDataValue)['Position']; 
                            //$BNPCPos = $AOZArrangementCsv->at($SubDataValue)['Position'];   
                            $BNPCX = ($BNPCPosRaw%25) * 16 - 38;  
                            $BNPCY = floor(($BNPCPosRaw/25)) * 16 + 8 -30;
                            $BNPCPos = "$BNPCX / $BNPCY";
                            $BTag = ++$b;
                            $MobID = $AOZArrangementCsv->at($SubDataValue)['AOZContentBriefingBNpc'];
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['SlowVuln']) {
                                case 'False':
                                    $VulnArray[] = "Slow";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['PetrificationVuln']) {
                                case 'False':
                                    $VulnArray[] = "Petrification/Freeze";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['ParalysisVuln']) {
                                case 'False':
                                    $VulnArray[] = "Paralysis";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['InterruptionVuln']) {
                                case 'False':
                                    $VulnArray[] = "Interruption";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['BlindVuln']) {
                                case 'False':
                                    $VulnArray[] = "Blind";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['StunVuln']) {
                                case 'False':
                                    $VulnArray[] = "Stun";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['SleepVuln']) {
                                case 'False':
                                    $VulnArray[] = "Sleep";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['HeavyVuln']) {
                                case 'False':
                                    $VulnArray[] = "Heavy";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['BindVuln']) {
                                case 'False':
                                    $VulnArray[] = "Bind";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['FlatOrDeathVuln']) {
                                case 'False':
                                    $VulnArray[] = "Flat Damage/Death";
                                break;
                                default:
                                break;
                            }
                            $VulnOutput = implode(", ",$VulnArray);

                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Endurance']) {
                                case 11:
                                    $Endurance = "Low";
                                break;
                                case 11:
                                    $Endurance = "Below Average";
                                break;
                                case 12:
                                    $Endurance = "Average";
                                break;
                                case 13:
                                    $Endurance = "Above Average";
                                break;
                                case 14:
                                    $Endurance = "High";
                                break;
                            }

                            $WeaknessArray = [];
                            $StrengthArray = [];
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Fire']) {
                                case 20:
                                    $WeaknessArray[] = "Fire";
                                break;
                                case 21:
                                    $StrengthArray[] = "Fire";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Ice']) {
                                case 20:
                                    $WeaknessArray[] = "Ice";
                                break;
                                case 21:
                                    $StrengthArray[] = "Ice";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Wind']) {
                                case 20:
                                    $WeaknessArray[] = "Wind";
                                break;
                                case 21:
                                    $StrengthArray[] = "Wind";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Earth']) {
                                case 20:
                                    $WeaknessArray[] = "Earth";
                                break;
                                case 21:
                                    $StrengthArray[] = "Earth";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Thunder']) {
                                case 20:
                                    $WeaknessArray[] = "Thunder";
                                break;
                                case 21:
                                    $StrengthArray[] = "Thunder";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Water']) {
                                case 20:
                                    $WeaknessArray[] = "Water";
                                break;
                                case 21:
                                    $StrengthArray[] = "Water";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Slashing']) {
                                case 20:
                                    $WeaknessArray[] = "Slashing";
                                break;
                                case 21:
                                    $StrengthArray[] = "Slashing";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Piercing']) {
                                case 20:
                                    $WeaknessArray[] = "Piercing";
                                break;
                                case 21:
                                    $StrengthArray[] = "Piercing";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Blunt']) {
                                case 20:
                                    $WeaknessArray[] = "Blunt";
                                break;
                                case 21:
                                    $StrengthArray[] = "Blunt";
                                break;
                                default:
                                break;
                            }
                            switch ($AOZContentBriefingBNpcCsv->at($MobID)['Magic']) {
                                case 20:
                                    $WeaknessArray[] = "Magic";
                                break;
                                case 21:
                                    $StrengthArray[] = "Magic";
                                break;
                                default:
                                break;
                            }
                            $Strengths = implode(", ",$StrengthArray);
                            $Weaknesses = implode(", ",$WeaknessArray);

                            $BNPCString =  "|Act $i Monster $BTag = $BNPCName\n";
                            $BNPCString .= "|Act $i Monster $BTag X = $BNPCX\n";
                            $BNPCString .= "|Act $i Monster $BTag Y = $BNPCY\n";
                            $BNPCString .= "|Act $i Monster $BTag Endurance = $Endurance\n";
                            $BNPCString .= "|Act $i Monster $BTag Strengths = $Strengths\n";
                            $BNPCString .= "|Act $i Monster $BTag Weaknesses = $Weaknesses\n";
                            $BNPCString .= "|Act $i Monster $BTag Vulnerabilities = $VulnOutput\n";
                            $BnpcNamePosArray[] = $BNPCString;
                        }
                    break;
                    case 2:      
                        $BNPCName = $BNpcNameCsv->at($AOZContentBriefingBNpcCsv->at($AOZBossCsv->at($ActMonsters)['Boss'])['BNpcName'])['Singular'];
                        $EnemiesArray[] = "{{colorlink|Light Blue|".$BNPCName."}}";
                        $BNPCPosRaw = $AOZBossCsv->at($ActMonsters)['Position'];
                        $MobID = $AOZBossCsv->at($ActMonsters)['Boss'];
                        $BNPCX = ($BNPCPosRaw%25) * 16 - 38;  
                        $BNPCY = floor(($BNPCPosRaw/25)) * 16 + 8 -30;
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['SlowVuln']) {
                            case 'False':
                                $VulnArray[] = "Slow";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['PetrificationVuln']) {
                            case 'False':
                                $VulnArray[] = "Petrification/Freeze";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['ParalysisVuln']) {
                            case 'False':
                                $VulnArray[] = "Paralysis";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['InterruptionVuln']) {
                            case 'False':
                                $VulnArray[] = "Interruption";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['BlindVuln']) {
                            case 'False':
                                $VulnArray[] = "Blind";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['StunVuln']) {
                            case 'False':
                                $VulnArray[] = "Stun";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['SleepVuln']) {
                            case 'False':
                                $VulnArray[] = "Sleep";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['HeavyVuln']) {
                            case 'False':
                                $VulnArray[] = "Heavy";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['BindVuln']) {
                            case 'False':
                                $VulnArray[] = "Bind";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['FlatOrDeathVuln']) {
                            case 'False':
                                $VulnArray[] = "Flat Damage/Death";
                            break;
                            default:
                            break;
                        }

                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Endurance']) {
                            case 11:
                                $Endurance = "Low";
                            break;
                            case 11:
                                $Endurance = "Below Average";
                            break;
                            case 12:
                                $Endurance = "Average";
                            break;
                            case 13:
                                $Endurance = "Above Average";
                            break;
                            case 14:
                                $Endurance = "High";
                            break;
                        }
                        $VulnOutput = implode(", ",$VulnArray);
                        

                        $WeaknessArray = [];
                        $StrengthArray = [];
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Fire']) {
                            case 20:
                                $WeaknessArray[] = "Fire";
                            break;
                            case 21:
                                $StrengthArray[] = "Fire";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Ice']) {
                            case 20:
                                $WeaknessArray[] = "Ice";
                            break;
                            case 21:
                                $StrengthArray[] = "Ice";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Wind']) {
                            case 20:
                                $WeaknessArray[] = "Wind";
                            break;
                            case 21:
                                $StrengthArray[] = "Wind";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Earth']) {
                            case 20:
                                $WeaknessArray[] = "Earth";
                            break;
                            case 21:
                                $StrengthArray[] = "Earth";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Thunder']) {
                            case 20:
                                $WeaknessArray[] = "Thunder";
                            break;
                            case 21:
                                $StrengthArray[] = "Thunder";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Water']) {
                            case 20:
                                $WeaknessArray[] = "Water";
                            break;
                            case 21:
                                $StrengthArray[] = "Water";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Slashing']) {
                            case 20:
                                $WeaknessArray[] = "Slashing";
                            break;
                            case 21:
                                $StrengthArray[] = "Slashing";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Piercing']) {
                            case 20:
                                $WeaknessArray[] = "Piercing";
                            break;
                            case 21:
                                $StrengthArray[] = "Piercing";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Blunt']) {
                            case 20:
                                $WeaknessArray[] = "Blunt";
                            break;
                            case 21:
                                $StrengthArray[] = "Blunt";
                            break;
                            default:
                            break;
                        }
                        switch ($AOZContentBriefingBNpcCsv->at($MobID)['Magic']) {
                            case 20:
                                $WeaknessArray[] = "Magic";
                            break;
                            case 21:
                                $StrengthArray[] = "Magic";
                            break;
                            default:
                            break;
                        }
                        $Strengths = implode(", ",$StrengthArray);
                        $Weaknesses = implode(", ",$WeaknessArray);
                        $BNPCString =  "|Act $i Monster 1 = $BNPCName\n";
                        $BNPCString .= "|Act $i Monster 1 X = $BNPCX\n";
                        $BNPCString .= "|Act $i Monster 1 Y = $BNPCY\n";
                        $BNPCString .= "|Act $i Monster 1 Endurance = $Strengths\n";
                        $BNPCString .= "|Act $i Monster 1 Strengths = $Strengths\n";
                        $BNPCString .= "|Act $i Monster 1 Weaknesses = $Weaknesses\n";
                        $BNPCString .= "|Act $i Monster 1 Vulnerabilities = $VulnOutput\n";
                        $BnpcNamePosArray[] = $BNPCString;
                    break;
                }
                $BnpcNamePos = implode("\n",$BnpcNamePosArray);
                $EnemiesOutput = implode(", ",$EnemiesArray);
                $ActArray[] = "|Act $i Arena = $Arena\n|Act $i Enemies = $EnemiesOutput\n\n$BnpcNamePos
                ";
            }
            $ActArrayOut = implode("\n",$ActArray);


            $ContentString = null;

            $ContentString = "{{-start-}}\n'''$ContentName'''\n{{ARR Infobox Carnivale\n";
            $ContentString .= "|Patch = $Patch\n";
            $ContentString .= "|Name = $ContentName\n";
            $ContentString .= "|Number = $OrderID\n";
            $ContentString .= "|Header = $ContentImage\n\n";
            $ContentString .= "|Required Level = $RequiredLevel\n";
            $ContentString .= "|Required Quest = $RequiredQuest\n\n";
            $ContentString .= "|Standard Completion Time = $StandardFinishTime\n";
            $ContentString .= "|Ideal Completion Time = $IdealFinishTime\n\n";
            $ContentString .= "|First Completion Gil Bonus = $GilReward\n";
            $ContentString .= "|First Completion Seal Bonus = $AlliedSealsReward\n";
            $ContentString .= "|First Completion Tomestone Bonus = $TomestonesReward\n\n";
            $ContentString .= "$ActArrayOut\n\n";
            $ContentString .= "|Image =\n";
            $ContentString .= "|Notes =\n";
            $ContentString .= "}}\n{{-stop-}}\n";

            $ContentArray[] = $ContentString;

        }
        $ContentOutput = implode("\n",$ContentArray);
        
        //bonus list:
        $BonusHeader = `        === Bonus List ===
        The Bonus List details various predetermined conditions and is accessed through the "Bonus Details" beside the "Challenge" button in the Masked Carnivale menu. Each week when a stage is selected for bonus rewards, it will be assigned conditions required to complete it to receive the bonus rewards. Novice rating doesn't require any conditions, Moderate requires 1 condition to complete, and Advanced requires 2 conditions to complete. It's possible to complete the conditions in other stages, but it will only increase your points instead of giving bonus rewards. Some achievements require players to fulfill certain conditions in specific stages to get them. 
        {{{!}} class="GEtable" width="100%" 
        !colspan="1" {{!}} Bonus !! Description\n`;
        $ScoreArray = [];
        foreach ($AOZScoreCsv->data as $id => $Score) {
            $Name = $Score["unknown_3"];
            $Description = $Score["unknown_4"];
            $Hidden = $Score["unknown_1"];
            switch ($Hidden) {
                case 'False':
                    $HiddenCheck = " (Hidden)";
                break;
                default:
                $HiddenCheck = "";
                break;
            }
            $Points = $Score["unknown_2"];
            $ScoreString = "{{!}}-\n";
            $ScoreString .= "{{!}}'''$Name'''$HiddenCheck\n";
            $ScoreString .= "{{!}}$Description\n";
            $ScoreString .= "{{!}}$Points\n";
            $ScoreArray[] = $ScoreString;
        }
        $ScoreOutput = "$BonusHeader".implode("", $ScoreArray);

        //Actions:
        $ActionArray = [];
        $SpellBookArray = [];
        foreach ($AozActionCsv->data as $id => $Action) {
            $ActionID = $Action['Action'];
            $Name = $ActionCsv->at($ActionID)['Name'];
            if (empty($Name)) continue;
            $Patch = $PatchNumberAction[$id];
            $Type = $ActionCategoryCsv->at($ActionCsv->at($ActionID)['ActionCategory'])['Name'];
            $Rank = $Action['unknown_2'];
            $StatsString = $AozActionTransientCsv->at($id)['Stats'];
            $StatsString = str_ireplace("<UIForeground>F201F8</UIForeground><UIGlow>F201F9</UIGlow>", "", $StatsString);
            $StatsString = str_ireplace("<UIGlow>01</UIGlow><UIForeground>01</UIForeground>", "", $StatsString);
            $StatsString = str_ireplace("Type: ", "", $StatsString);
            $StatsString = str_ireplace("Aspect: ", "", $StatsString);
            $StatsString = str_ireplace("Rank: ", "", $StatsString);
            $StatsArray = explode("\n",$StatsString);
            $DamageType = $StatsArray[0];
            $Aspect = $StatsArray[1];
            $Number = $AozActionTransientCsv->at($id)['Number'];
            $Description = str_ireplace("\n","{{tab|}}\n",$AozActionTransientCsv->at($id)['Description']);

            if ($ActionCsv->at($ActionID)['Range'] == "-1") {
              $Range = "3";
            } elseif ($ActionCsv->at($ActionID)['Range'] !== "-1") {
              $Range = $ActionCsv->at($ActionID)['Range'];
            }
            $Radius = $ActionCsv->at($ActionID)['EffectRange'];
            $CastType = $ActionCsv->at($ActionID)['CastType'];
            switch ($CastType) {
              case 1:
                  $CastType = "single";
              break;
              case 2:
                  $CastType = "aoe";
              break;
              case 3:
                  $CastType = "cone";
              break;
              case 4:
                  $CastType = "line";
              break;
              default:
                  $CastType = "aoe";
              break;
            }
            if ($ActionCsv->at($ActionID)['Cast<100ms>'] == "0") {
              $CastTime = "Instant";
            } elseif ($ActionCsv->at($ActionID)['Cast<100ms>'] !== "0") {
              $CastTimeRaw = $ActionCsv->at($ActionID)['Cast<100ms>'];
              $CastTime = "". ($CastTimeRaw / 10) ."";
            }

            if ($ActionCsv->at($ActionID)['Recast<100ms>'] == "0") {
              $Recast = "Instant";
            } elseif ($ActionCsv->at($ActionID)['Recast<100ms>'] !== "0") {
              $ReCastTimeRaw = $ActionCsv->at($ActionID)['Recast<100ms>'];
              $Recast = "". ($ReCastTimeRaw / 10) ."";
            }
            $ActionDescription = str_ireplace("\n","{{tab|}}",$ActionTransientCsv->at($ActionCsv->at($ActionID)['id'])['Description']);
            $Cost = $ActionCsv->at($ActionID)['PrimaryCost{Value}'] * 100;

            $ActionString = "{{-start-}}\n'''$Name'''\n{{ARR Infobox Action\n";
            $ActionString .= "|Patch = $Patch\n";
            $ActionString .= "|Name = $Name\n";
            $ActionString .= "|Type = $Type\n";
            $ActionString .= "|Damage Type = $DamageType\n";
            $ActionString .= "|Aspect = $Aspect\n";
            $ActionString .= "|Rank = $Rank\n";
            $ActionString .= "|Acquired = Blue Mage\n";
            $ActionString .= "|Acquired Level =\n";
            $ActionString .= "|Min Level =\n";
            $ActionString .= "\n";
            $ActionString .= "|Affinity = BLU\n";
            $ActionString .= "|BluNumber = $Number\n";
            $ActionString .= "\n";
            $ActionString .= "|Range = $Range\n";
            $ActionString .= "|Radius = $Radius\n";
            $ActionString .= "|Potency =        <!-- Base attack or cure potency of the action -->\n";
            $ActionString .= "\n";
            $ActionString .= "|Target = $CastType\n";
            $ActionString .= "\n";
            $ActionString .= "|MP = $Cost\n";
            $ActionString .= "\n";
            $ActionString .= "|Cast = $CastTime\n";
            $ActionString .= "|Recast = $Recast\n";
            $ActionString .= "|Duration = \n";
            $ActionString .= "|Description = $ActionDescription\n";
            $ActionString .= "\n";
            $ActionString .= "|Additional Effect = \n";
            $ActionString .= "|Additional Effect Duration = \n";
            $ActionString .= "|Additional Effect Potency =\n";
            $ActionString .= "|Additional Effect Bonus =\n";
            $ActionString .= "\n";
            $ActionString .= "|Mob Notes = $Description\n";
            $ActionString .= "\n";
            $ActionString .= "}}\n{{-stop-}}\n";
            $ActionArray[] = $ActionString;

            switch ($AozActionTransientCsv->at($id)['LocationKey']) {
                case '1':
                    $ContentType = "Mob";
                    $ContentName = $PlaceNameCsv->at($AozActionTransientCsv->at($id)['Location'])['Name'];
                break;
                case '2':
                    $ContentType = "Totem";
                    $ContentName = "Whalaqee $Name Totem";
                break;
                case '3':
                    $ContentType = "Quest";
                    $ContentName = "Out of the Blue";
                break;
                case '4':
                    $ContentType = substr($ContentTypeCsv->at($ContentFinderConditionCsv->at($AozActionTransientCsv->at($id)['Location'])['ContentType'])['Name'],0,-1);
                    $ContentName = $ContentFinderConditionCsv->at($AozActionTransientCsv->at($id)['Location'])['Name'];
                break;
                
                default:
                    $ContentType = "Unknown";
                    $ContentName = "Unknown";
                break;
            }

            $SpellBookArray[] = "{{BlueSpell|Name=$Name|Location1={{BSD|$ContentType|$ContentName}}}}";
            
            // beginning of Icon copying code
            $SmallIcon = $AozActionTransientCsv->at($id)['Icon'];

            // ensure output directory exists
            $IconoutputDirectory = $this->getOutputFolder() . "/$PatchID/BluIcons";
            // if it doesn't exist, make it
            if (!is_dir($IconoutputDirectory)) {
                mkdir($IconoutputDirectory, 0777, true);
            }

            // build icon input folder paths
            $SmallIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($AozActionTransientCsv->at($id)['Icon']);

            // give correct file names to icons for output
            $SmallIconFileName = "{$IconoutputDirectory}/$Name Icon.png";
            // actually copy the icons
            copy($SmallIconPath, $SmallIconFileName);
        }

        $ActionOutput = implode("", $ActionArray);
        $SpellBookOutput = implode("\n", $SpellBookArray);

        //bluemage spellbook
        foreach ($AOZContentBriefingBNpcCsv->data as $id => $Npc) {
            $Name = ucwords($BNpcNameCsv->at($Npc['BNpcName'])['Singular']);
            if (empty($Name)) continue;
            // beginning of Icon copying code
            $SmallIcon = $Npc['TargetSmall'];
            $LargeIcon = $Npc['TargetLarge'];

            // ensure output directory exists
            $IconoutputDirectory = $this->getOutputFolder() . "/$PatchID/BluIcons";
            // if it doesn't exist, make it
            if (!is_dir($IconoutputDirectory)) {
                mkdir($IconoutputDirectory, 0777, true);
            }

            // build icon input folder paths
            $SmallIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($SmallIcon);
            $LargeIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($LargeIcon);
            // give correct file names to icons for output
            $SmallIconFileName = "{$IconoutputDirectory}/{$Name}_Carnivale_Mini.png";
            $LargeIconFileName = "{$IconoutputDirectory}/{$Name}_Carnivale.png";
            // actually copy the icons
            copy($SmallIconPath, $SmallIconFileName);
            copy($LargeIconPath, $LargeIconFileName);

        }


        // Save some data
        $data = [
            '{Content}' => $ContentOutput,
            '{Score}' => $ScoreOutput,
            '{Action}' => $ActionOutput,
            '{SpellBook}' => $SpellBookOutput,
        ];

        // format using Gamer Escape formatter and add to data array
        // need to look into using item-specific regex, if required.
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("BlueMageContent.txt", 999999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}