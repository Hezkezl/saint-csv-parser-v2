<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:Instances
 */
class Instances implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "
    |Index = {Index}
|ContentID = {ContentID}
|ShortCode = {ShortCode}
|Type = {Type}
|Zone = {Zone}
|Description = {Description}
|ClassJobAllowed = {ClassJob}
|Roles =
{Roles}
{UnlockQuest}
{Required}
{Bools}
|PvP?                = {PvP}
{Roulettes}
{OutputData}";

    public function parse()
    {
        // grab CSV files we want to use
        $ContentFinderConditionCsv = $this->csv('ContentFinderCondition');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $PlaceNameCsv = $this->csv('PlaceName');
        $ClassJobCategoryCsv = $this->csv('ClassJobCategory');
        $ContentMemberTypeCsv = $this->csv('ContentMemberType');
        $ContentFinderConditionTransientCsv = $this->csv('ContentFinderConditionTransient');
        $QuestCsv = $this->csv('Quest');
        $ContentTypeCsv = $this->csv('ContentType');
        $InstanceContentCsv = $this->csv('InstanceContent');
        $InstanceContentBuffCsv = $this->csv('InstanceContentBuff');
        $InstanceContentTextDataCsv = $this->csv('InstanceContentTextData');

        // (optional) start a progress bar
        $this->io->progressStart($ContentFinderConditionCsv->total);

        // loop through data
        foreach ($ContentFinderConditionCsv->data as $id => $Content) {
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($Content['ShortCode'])) {
                continue;
            }

            $Zone = $PlaceNameCsv->at($TerritoryTypeCsv->at($Content['TerritoryType'])['PlaceName'])['Name'];
            $PvP = $Content['PvP'];
            $ClassJob = $ClassJobCategoryCsv->at($Content['AcceptClassJobCategory'])['Name'];

            // Roles info
            $Tanks = $ContentMemberTypeCsv->at($Content['ContentMemberType'])['TanksPerParty'];
            $Healers = $ContentMemberTypeCsv->at($Content['ContentMemberType'])['HealersPerParty'];
            $Melees = $ContentMemberTypeCsv->at($Content['ContentMemberType'])['MeleesPerParty'];
            $Ranged = $ContentMemberTypeCsv->at($Content['ContentMemberType'])['RangedPerParty'];

            $Roles = "    |Tanks   = ". $Tanks ."\n    |Healers = ". $Healers ."\n    |Melees  = ". $Melees ."\n    |Ranged  = ". $Ranged ."";
            $Description = $ContentFinderConditionTransientCsv->at($Content['id'])['Description'];

            $UnlockQuest = "";
            if ($Content['UnlockQuest'] !== '0') {
            	$UnlockQuest = "|RequiresQuest = ". $QuestCsv->at($Content['UnlockQuest'])['Name']. "";
            }

            $ClassJobLevelRequired = $Content['ClassJobLevel{Required}'];
            $ClassJobLevelSync = $Content['ClassJobLevel{Sync}'];
            $ItemLevelRequired = $Content['ItemLevel{Required}'];
            $ItemLevelSync = $Content['ItemLevel{Required}'];

            $Required = "|LevelRequired       = ". $ClassJobLevelRequired ."\n|LevelSync           = ". $ClassJobLevelSync ."\n|iLevelRequired      = ". $ItemLevelRequired ."\n|iLevelSync          = ". $ItemLevelSync ."";

            //Is X?
            $Undersized = $Content['AllowUndersized'];
            $Replacement = $Content['AllowReplacement'];
            $HighEndDuty = $Content['HighEndDuty'];
            $DutyRecorderAllowed = $Content['DutyRecorderAllowed'];
            $Bools = "|AllowUndersized     = ". $Undersized ."\n|AllowReplacement    = ". $Replacement ."\n|HighEndDuty         = ". $HighEndDuty ."\n|DutyRecorderAllowed = ". $DutyRecorderAllowed ."";

            $Type = $ContentTypeCsv->at($Content['ContentType'])['Name'];
            $ContentID = $Content['Content'];

            //Roulettes
            $Roulettes = "";

            $LevelingRoulette = str_replace("True","Leveling, ",$Content['LevelingRoulette']);
            $Level5060Roulette = str_replace("True","Level 50/60, ",$Content['Level50/60Roulette']);
            $MSQRoulette = str_replace("True","Main Story Quest, ",$Content['MSQRoulette']);
            $GuildHestRoulette = str_replace("True","GuildHest, ",$Content['GuildHestRoulette']);
            $TrialRoulette = str_replace("True","Trials, ",$Content['TrialRoulette']);
            $DailyFrontlineChallenge = str_replace("True","Frontline, ",$Content['DailyFrontlineChallenge']);
            $Level70Roulette = str_replace("True","Level 70, ",$Content['Level70Roulette']);
            $MentorRoulette = str_replace("True","Mentor, ",$Content['MentorRoulette']);
            $AllianceRoulette = str_replace("True","Alliance Raids, ",$Content['AllianceRoulette']);
            $NormalRaidRoulette = str_replace("True","Normal Raids, ",$Content['NormalRaidRoulette']);

            $RoulettesRaw = "". $LevelingRoulette ."". $Level5060Roulette ."". $MSQRoulette ."". $GuildHestRoulette ."". $TrialRoulette ."". $DailyFrontlineChallenge ."". $Level70Roulette ."". $MentorRoulette ."". $AllianceRoulette ."". $NormalRaidRoulette ."";
            $RoulettesReplace = str_replace("False","",$RoulettesRaw);
            if (!empty($RoulettesReplace)) {
            	$Roulettes = "|In Roulette = ". $RoulettesReplace ."";
            }


            //Content
            $OutputData = "";
            $ContentLinkType = $Content['ContentLinkType'];
            //Instance Content
            if ($ContentLinkType === "1") {

            	//give a short base
            	$InstanceContentLink = $InstanceContentCsv->at($Content['Content']);
            	//data
            	$TimeLimit = $InstanceContentLink['TimeLimit{min}'];
            	$WeeklyRestriction = str_replace("0","",$InstanceContentLink['WeekRestriction']);
            	$WeeklyRestriction = str_replace("1","|Weekly Restriction = True\n",$WeeklyRestriction);
            	//rewards - If anything is 0 then ommit it from the array
            	$InstanceClearExp = $InstanceContentLink['InstanceClearExp'];
                if ($InstanceClearExp == 0) {
                    $InstanceClearExpString = "";
                } elseif ($InstanceClearExp !== 0) {
                    $InstanceClearExpString = "\n|InstanceClearExp = ". $InstanceClearExp ."";
                }
                $NewPlayerBonusA = $InstanceContentLink['NewPlayerBonusA'];
                if ($NewPlayerBonusA == 0) {
                    $NewPlayerBonusAString = "";
                } elseif ($NewPlayerBonusA !== 0) {
                    $NewPlayerBonusAString = "\n|NewPlayerBonusA = ". $NewPlayerBonusA ."";
                }
                $NewPlayerBonusB = $InstanceContentLink['NewPlayerBonusB'];
                if ($NewPlayerBonusB == 0) {
                    $NewPlayerBonusBString = "";
                } elseif ($NewPlayerBonusB !== 0) {
                    $NewPlayerBonusBString = "\n|NewPlayerBonusB = ". $NewPlayerBonusB ."";
                }
                $FinalBossCurrencyA = $InstanceContentLink['FinalBossCurrencyA'];
                if ($FinalBossCurrencyA == 0) {
                    $FinalBossCurrencyAString = "";
                } elseif ($FinalBossCurrencyA !== 0) {
                    $FinalBossCurrencyAString = "\n|FinalBossCurrencyA = ". $FinalBossCurrencyA ."";
                }
                $FinalBossCurrencyB = $InstanceContentLink['FinalBossCurrencyB'];
                if ($FinalBossCurrencyB == 0) {
                    $FinalBossCurrencyBString = "";
                } elseif ($FinalBossCurrencyB !== 0) {
                    $FinalBossCurrencyBString = "\n|FinalBossCurrencyB = ". $FinalBossCurrencyB ."";
                }
                $FinalBossCurrencyC = $InstanceContentLink['FinalBossCurrencyC'];
                if ($FinalBossCurrencyC == 0) {
                    $FinalBossCurrencyCString = "";
                } elseif ($FinalBossCurrencyC !== 0) {
                    $FinalBossCurrencyCString = "\n|FinalBossCurrencyC = ". $FinalBossCurrencyC ."";
                }
                $BossExp0 = $InstanceContentLink['BossExp[0]'];
                if ($BossExp0 == 0) {
                    $BossExp0String = "";
                } elseif ($BossExp0 !== 0) {
                    $BossExp0String = "\n|BossExp0 = ". $BossExp0 ."";
                }
                $BossExp1 = $InstanceContentLink['BossExp[1]'];
                if ($BossExp1 == 0) {
                    $BossExp1String = "";
                } elseif ($BossExp1 !== 0) {
                    $BossExp1String = "\n|BossExp1 = ". $BossExp1 ."";
                }
                $BossExp2 = $InstanceContentLink['BossExp[2]'];
                if ($BossExp2 == 0) {
                    $BossExp2String = "";
                } elseif ($BossExp2 !== 0) {
                    $BossExp2String = "\n|BossExp2 = ". $BossExp2 ."";
                }
                $BossExp3 = $InstanceContentLink['BossExp[3]'];
                if ($BossExp3 == 0) {
                    $BossExp3String = "";
                } elseif ($BossExp3 !== 0) {
                    $BossExp3String = "\n|BossExp3 = ". $BossExp3 ."";
                }
                $BossExp4 = $InstanceContentLink['BossExp[4]'];
                if ($BossExp4 == 0) {
                    $BossExp4String = "";
                } elseif ($BossExp4 !== 0) {
                    $BossExp4String = "\n|BossExp4 = ". $BossExp4 ."";
                }
                $BossCurrencyA0 = $InstanceContentLink['BossCurrencyA[0]'];
                if ($BossCurrencyA0 == 0) {
                    $BossCurrencyA0String = "";
                } elseif ($BossCurrencyA0 !== 0) {
                    $BossCurrencyA0tring = "\n|BossCurrencyA0 = ". $BossCurrencyA0 ."";
                }
                $BossCurrencyA1 = $InstanceContentLink['BossCurrencyA[1]'];
                if ($BossCurrencyA1 == 0) {
                    $BossCurrencyA1String = "";
                } elseif ($BossCurrencyA1 !== 0) {
                    $BossCurrencyA1tring = "\n|BossCurrencyA1 = ". $BossCurrencyA1 ."";
                }
                $BossCurrencyA2 = $InstanceContentLink['BossCurrencyA[2]'];
                if ($BossCurrencyA2 == 0) {
                    $BossCurrencyA2String = "";
                } elseif ($BossCurrencyA2 !== 0) {
                    $BossCurrencyA2tring = "\n|BossCurrencyA2 = ". $BossCurrencyA2 ."";
                }
                $BossCurrencyA3 = $InstanceContentLink['BossCurrencyA[3]'];
                if ($BossCurrencyA3 == 0) {
                    $BossCurrencyA3String = "";
                } elseif ($BossCurrencyA3 !== 0) {
                    $BossCurrencyA3tring = "\n|BossCurrencyA3 = ". $BossCurrencyA3 ."";
                }
                $BossCurrencyA4 = $InstanceContentLink['BossCurrencyA[4]'];
                if ($BossCurrencyA4 == 0) {
                    $BossCurrencyA4String = "";
                } elseif ($BossCurrencyA4 !== 0) {
                    $BossCurrencyA4tring = "\n|BossCurrencyA4 = ". $BossCurrencyA4 ."";
                }
                $BossCurrencyB0 = $InstanceContentLink['BossCurrencyB[0]'];
                if ($BossCurrencyB0 == 0) {
                    $BossCurrencyB0String = "";
                } elseif ($BossCurrencyB0 !== 0) {
                    $BossCurrencyB0tring = "\n|BossCurrencyB0 = ". $BossCurrencyB0 ."";
                }
                $BossCurrencyB1 = $InstanceContentLink['BossCurrencyB[1]'];
                if ($BossCurrencyB1 == 0) {
                    $BossCurrencyB1String = "";
                } elseif ($BossCurrencyB1 !== 0) {
                    $BossCurrencyB1tring = "\n|BossCurrencyB1 = ". $BossCurrencyB1 ."";
                }
                $BossCurrencyB2 = $InstanceContentLink['BossCurrencyB[2]'];
                if ($BossCurrencyB2 == 0) {
                    $BossCurrencyB2String = "";
                } elseif ($BossCurrencyB2 !== 0) {
                    $BossCurrencyB2tring = "\n|BossCurrencyB2 = ". $BossCurrencyB2 ."";
                }
                $BossCurrencyB3 = $InstanceContentLink['BossCurrencyB[3]'];
                if ($BossCurrencyB3 == 0) {
                    $BossCurrencyB3String = "";
                } elseif ($BossCurrencyB3 !== 0) {
                    $BossCurrencyB3tring = "\n|BossCurrencyB3 = ". $BossCurrencyB3 ."";
                }
                $BossCurrencyB4 = $InstanceContentLink['BossCurrencyB[4]'];
                if ($BossCurrencyB4 == 0) {
                    $BossCurrencyB4String = "";
                } elseif ($BossCurrencyB4 !== 0) {
                    $BossCurrencyB4tring = "\n|BossCurrencyB4 = ". $BossCurrencyB4 ."";
                }
                $BossCurrencyC0 = $InstanceContentLink['BossCurrencyC[0]'];
                if ($BossCurrencyC0 == 0) {
                    $BossCurrencyC0String = "";
                } elseif ($BossCurrencyC0 !== 0) {
                    $BossCurrencyC0tring = "\n|BossCurrencyC0 = ". $BossCurrencyC0 ."";
                }
                $BossCurrencyC1 = $InstanceContentLink['BossCurrencyC[1]'];
                if ($BossCurrencyC1 == 0) {
                    $BossCurrencyC1String = "";
                } elseif ($BossCurrencyC1 !== 0) {
                    $BossCurrencyC1tring = "\n|BossCurrencyC1 = ". $BossCurrencyC1 ."";
                }
                $BossCurrencyC2 = $InstanceContentLink['BossCurrencyC[2]'];
                if ($BossCurrencyC2 == 0) {
                    $BossCurrencyC2String = "";
                } elseif ($BossCurrencyC2 !== 0) {
                    $BossCurrencyC2tring = "\n|BossCurrencyC2 = ". $BossCurrencyC2 ."";
                }
                $BossCurrencyC3 = $InstanceContentLink['BossCurrencyC[3]'];
                if ($BossCurrencyC3 == 0) {
                    $BossCurrencyC3String = "";
                } elseif ($BossCurrencyC3 !== 0) {
                    $BossCurrencyC3tring = "\n|BossCurrencyC3 = ". $BossCurrencyC3 ."";
                }
                $BossCurrencyC4 = $InstanceContentLink['BossCurrencyC[4]'];
                if ($BossCurrencyC4 == 0) {
                    $BossCurrencyC4String = "";
                } elseif ($BossCurrencyC4 !== 0) {
                    $BossCurrencyC4tring = "\n|BossCurrencyC4 = ". $BossCurrencyC4 ."";
                }
                $InstanceClearGil = $InstanceContentLink['InstanceClearGil'];
                if ($InstanceClearGil == 0) {
                    $InstanceClearGilString = "";
                } elseif ($InstanceClearGil !== 0) {
                    $InstanceClearGilString = "\n|InstanceClearGil = ". $InstanceClearGil ."";
                }
                $FinalBossExp = $InstanceContentLink['FinalBossExp'];
                if ($FinalBossExp == 0) {
                    $FinalBossExpString = "";
                } elseif ($FinalBossExp !== 0) {
                    $FinalBossExpString = "\n|FinalBossExp = ". $FinalBossExp ."";
                }

            	//Echo
            	$EchoDeath = "";
            	$EchoStart = "";
            	if ($InstanceContentBuffCsv->at($InstanceContentLink['InstanceContentBuff'])['Echo{Start}'] != 0) {
            		$EchoStart = "|Echo Start = ". $InstanceContentBuffCsv->at($InstanceContentLink['InstanceContentBuff'])['Echo{Start}'] ."";
            	}
            	if ($InstanceContentBuffCsv->at($InstanceContentLink['InstanceContentBuff'])['Echo{Death}'] != 0) {
            		$EchoDeath = "|Echo On Death = ". $InstanceContentBuffCsv->at($InstanceContentLink['InstanceContentBuff'])['Echo{Death}'] ."";
            	}
            	$Echo = "". $EchoStart ."". $EchoDeath ."";

            	//Objectives
            	$ObjStart = $InstanceContentLink['InstanceContentTextData{Objective}{Start}'];
            	$ObjEnd = $InstanceContentLink['InstanceContentTextData{Objective}{End}'];

            	$ObjCalc = ($ObjEnd - $ObjStart);

            	$ObjectiveRaw = "|ObjStartNum = ". $ObjStart ."\n|ObjEndNum = ". $ObjEnd ."\n|ObjNumDiff = ". $ObjCalc ."";
            	$Objectives = [];
            	foreach(range(0,$ObjCalc) as $i) {
            		$ObjDiff = ($ObjStart + $i);
                	if(!empty($ObjDiff)) {
                    	$Objectives[] = $InstanceContentTextDataCsv->at($ObjDiff)['Text'];
                	}
            	}
            	$Objectives = implode("\n", $Objectives);

            	$Rewards = "". $InstanceClearExpString ."". $NewPlayerBonusAString ."". $NewPlayerBonusBString ."". $FinalBossCurrencyAString ."". $FinalBossCurrencyBString ."". $FinalBossCurrencyCString ."". $BossExp0String ."". $BossExp1String ."". $BossExp2String ."". $BossExp3String ."". $BossExp4String ."". $BossCurrencyA0String ."". $BossCurrencyA1String ."". $BossCurrencyA2String ."". $BossCurrencyA3String ."". $BossCurrencyA4String ."". $BossCurrencyB0String ."". $BossCurrencyB1String ."". $BossCurrencyB2String ."". $BossCurrencyB3String ."". $BossCurrencyB4String ."". $BossCurrencyB0String ."". $BossCurrencyC1String ."". $BossCurrencyC2String ."". $BossCurrencyC3String ."". $BossCurrencyC4String ."". $InstanceClearGilString ."". $FinalBossExpString ."";

            	$OutputData = "|TimeLimit = ". $TimeLimit ."\n". $WeeklyRestriction ."\n". $Rewards ."\n". $Echo ."\n". $Objectives ."";
            }


            // Save some data
            $data = [
                '{Index}' => $Content['id'],
                '{ShortCode}' => $Content['ShortCode'],
                '{Zone}' => $Zone,
                '{PvP}' => $PvP,
                '{ClassJob}' => $ClassJob,
                '{Roles}' => $Roles,
                '{Description}' => $Description,
                '{UnlockQuest}' => $UnlockQuest,
                '{Required}' => $Required,
                '{Bools}' => $Bools,
                '{Type}' => $Type,
                '{Roulettes}' => $Roulettes,
                '{OutputData}' => $OutputData,
                '{ContentID}' => $ContentID,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('Instances.txt', 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
