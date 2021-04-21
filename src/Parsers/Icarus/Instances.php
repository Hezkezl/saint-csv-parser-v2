<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

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

        include (dirname(__DIR__) . '/Paths.php');

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

            $Roles = "    |Tanks   = ". $Tanks ."\n    |Healers = ". $Healers ."\n    |Melees  = ". $Melees
                ."\n    |Ranged  = ". $Ranged ."";
            $Description = $ContentFinderConditionTransientCsv->at($Content['id'])['Description'];

            $UnlockQuest = false;
            if ($Content['UnlockQuest'] !== '0') {
            	$UnlockQuest = "|RequiresQuest = ". $QuestCsv->at($Content['UnlockQuest'])['Name']. "";
            }

            $ClassJobLevelRequired = $Content['ClassJobLevel{Required}'];
            $ClassJobLevelSync = $Content['ClassJobLevel{Sync}'];
            $ItemLevelRequired = $Content['ItemLevel{Required}'];
            $ItemLevelSync = $Content['ItemLevel{Required}'];

            $Required = "|LevelRequired       = ". $ClassJobLevelRequired ."\n|LevelSync           = ". $ClassJobLevelSync
                ."\n|iLevelRequired      = ". $ItemLevelRequired ."\n|iLevelSync          = ". $ItemLevelSync ."";

            //Is X?
            $Undersized = $Content['AllowUndersized'];
            $Replacement = $Content['AllowReplacement'];
            $HighEndDuty = $Content['HighEndDuty'];
            $DutyRecorderAllowed = $Content['DutyRecorderAllowed'];
            $Bools = "|AllowUndersized     = ". $Undersized ."\n|AllowReplacement    = ". $Replacement
                ."\n|HighEndDuty         = ". $HighEndDuty ."\n|DutyRecorderAllowed = ". $DutyRecorderAllowed ."";

            $Type = $ContentTypeCsv->at($Content['ContentType'])['Name'];
            $ContentID = $Content['Content'];

            //Roulettes
            $Roulettes = false;

            $LevelingRoulette = str_replace("True","Leveling, ",$Content['LevelingRoulette']);
            $Level506070Roulette = str_replace("True","Level 50/60/70, ",$Content['Level50/60/70Roulette']);
            $MSQRoulette = str_replace("True","Main Story Quest, ",$Content['MSQRoulette']);
            $GuildHestRoulette = str_replace("True","GuildHest, ",$Content['GuildHestRoulette']);
            $TrialRoulette = str_replace("True","Trials, ",$Content['TrialRoulette']);
            $DailyFrontlineChallenge = str_replace("True","Frontline, ",$Content['DailyFrontlineChallenge']);
            //$Level70Roulette = str_replace("True","Level 70, ",$Content['Level70Roulette']);
            $MentorRoulette = str_replace("True","Mentor, ",$Content['MentorRoulette']);
            $AllianceRoulette = str_replace("True","Alliance Raids, ",$Content['AllianceRoulette']);
            $NormalRaidRoulette = str_replace("True","Normal Raids, ",$Content['NormalRaidRoulette']);

            $RoulettesRaw = "". $LevelingRoulette ."". $Level506070Roulette ."". $MSQRoulette ."". $GuildHestRoulette
                ."". $TrialRoulette ."". $DailyFrontlineChallenge ."". $MentorRoulette ."". $AllianceRoulette
                ."". $NormalRaidRoulette ."";
            $RoulettesReplace = str_replace("False",null,$RoulettesRaw);
            $RoulettesReplace = preg_replace("/, $/", null, $RoulettesReplace);
            if (!empty($RoulettesReplace)) {
            	$Roulettes = "|In Roulette = ". $RoulettesReplace ."";
            }

            $InstanceClearExpString = false;
            $NewPlayerBonusAString = false;
            $NewPlayerBonusBString = false;
            $FinalBossCurrencyAString = false;
            $FinalBossCurrencyBString = false;
            $FinalBossCurrencyCString = false;
            $BossExp0String = false;
            $BossExp1String = false;
            $BossExp2String = false;
            $BossExp3String = false;
            $BossExp4String = false;
            $BossCurrencyA0String = false;
            $BossCurrencyA1String = false;
            $BossCurrencyA2String = false;
            $BossCurrencyA3String = false;
            $BossCurrencyA4String = false;
            $BossCurrencyB0String = false;
            $BossCurrencyB1String = false;
            $BossCurrencyB2String = false;
            $BossCurrencyB3String = false;
            $BossCurrencyB4String = false;
            $BossCurrencyC0String = false;
            $BossCurrencyC1String = false;
            $BossCurrencyC2String = false;
            $BossCurrencyC3String = false;
            $BossCurrencyC4String = false;
            $InstanceClearGilString = false;
            $FinalBossExpString = false;

            //Content
            $OutputData = false;
            $ContentLinkType = $Content['ContentLinkType'];
            //Instance Content
            if ($ContentLinkType === "1") {

            	//give a short base
            	$InstanceContentLink = $InstanceContentCsv->at($Content['Content']);

            	//data
            	$TimeLimit = $InstanceContentLink['TimeLimit{min}'];
            	$WeeklyRestriction = str_replace("0","",$InstanceContentLink['WeekRestriction']);
            	$WeeklyRestriction = str_replace("1","|Weekly Restriction = True\n",$WeeklyRestriction);

            	//rewards - If anything is 0 then omit it from the array
            	$InstanceClearExp = $InstanceContentLink['InstanceClearExp'];
                if ($InstanceClearExp !== 0) {
                    $InstanceClearExpString = "\n|InstanceClearExp = ". $InstanceClearExp ."";
                }
                $NewPlayerBonusA = $InstanceContentLink['NewPlayerBonusA'];
                if ($NewPlayerBonusA !== 0) {
                    $NewPlayerBonusAString = "\n|NewPlayerBonusA = ". $NewPlayerBonusA ."";
                }
                $NewPlayerBonusB = $InstanceContentLink['NewPlayerBonusB'];
                if ($NewPlayerBonusB !== 0) {
                    $NewPlayerBonusBString = "\n|NewPlayerBonusB = ". $NewPlayerBonusB ."";
                }
                $FinalBossCurrencyA = $InstanceContentLink['FinalBossCurrencyA'];
                if ($FinalBossCurrencyA !== 0) {
                    $FinalBossCurrencyAString = "\n|FinalBossCurrencyA = ". $FinalBossCurrencyA ."";
                }
                $FinalBossCurrencyB = $InstanceContentLink['FinalBossCurrencyB'];
                if ($FinalBossCurrencyB !== 0) {
                    $FinalBossCurrencyBString = "\n|FinalBossCurrencyB = ". $FinalBossCurrencyB ."";
                }
                $FinalBossCurrencyC = $InstanceContentLink['FinalBossCurrencyC'];
                if ($FinalBossCurrencyC !== 0) {
                    $FinalBossCurrencyCString = "\n|FinalBossCurrencyC = ". $FinalBossCurrencyC ."";
                }

                //cleaned up original code and turned into a loop. Accomplishes the same thing with 1/10th the code!
                for ($i = 0; $i <=4; $i++) {
                    $BossExp[$i] = $InstanceContentLink["BossExp[$i]"];
                    $BossCurrencyA[$i] = $InstanceContentLink["BossCurrencyA[$i]"];
                    $BossCurrencyB[$i] = $InstanceContentLink["BossCurrencyB[$i]"];
                    $BossCurrencyC[$i] = $InstanceContentLink["BossCurrencyC[$i]"];
                    if ($BossExp[$i] !== 0) {
                        ${'BossExp'. $i .'String'} = "\n|BossExp" .$i ." = ". $BossExp[$i];
                    }
                    if ($BossCurrencyA[$i] !== 0) {
                        ${'BossCurrencyA'. $i .'String'} = "\n|BossCurrencyA" .$i ." = ". $BossCurrencyA[$i];
                    }
                    if ($BossCurrencyB[$i] !== 0) {
                        ${'BossCurrencyB'. $i .'String'} = "\n|BossCurrencyB" .$i ." = ". $BossCurrencyB[$i];
                    }
                    if ($BossCurrencyC[$i] !== 0) {
                        ${'BossCurrencyC'. $i .'String'} = "\n|BossCurrencyC" .$i ." = ". $BossCurrencyC[$i];
                    }
                }

                $InstanceClearGil = $InstanceContentLink['InstanceClearGil'];
                if ($InstanceClearGil !== 0) {
                    $InstanceClearGilString = "\n|InstanceClearGil = ". $InstanceClearGil ."";
                }
                $FinalBossExp = $InstanceContentLink['FinalBossExp'];
                if ($FinalBossExp !== 0) {
                    $FinalBossExpString = "\n|FinalBossExp = ". $FinalBossExp ."";
                }


            	//Echo
            	$EchoDeath = false;
            	$EchoStart = false;
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

            	//Unused variable
            	//$ObjectiveRaw = "|ObjStartNum = ". $ObjStart ."\n|ObjEndNum = ". $ObjEnd ."\n|ObjNumDiff = ". $ObjCalc ."";

            	$Objectives = [];
            	foreach(range(0,$ObjCalc) as $i) {
            		$ObjDiff = ($ObjStart + $i);
                	if(!empty($ObjDiff)) {
                    	$Objectives[] = $InstanceContentTextDataCsv->at($ObjDiff)['Text'];
                	}
            	}
            	$Objectives = implode("\n", $Objectives);

            	$Rewards = "". $InstanceClearExpString ."". $NewPlayerBonusAString ."". $NewPlayerBonusBString ."". $FinalBossCurrencyAString
                    ."". $FinalBossCurrencyBString ."". $FinalBossCurrencyCString ."". $BossExp0String ."". $BossExp1String
                    ."". $BossExp2String ."". $BossExp3String ."". $BossExp4String ."". $BossCurrencyA0String ."". $BossCurrencyA1String
                    ."". $BossCurrencyA2String ."". $BossCurrencyA3String ."". $BossCurrencyA4String ."". $BossCurrencyB0String
                    ."". $BossCurrencyB1String ."". $BossCurrencyB2String ."". $BossCurrencyB3String ."". $BossCurrencyB4String
                    ."". $BossCurrencyC0String ."". $BossCurrencyC1String ."". $BossCurrencyC2String ."". $BossCurrencyC3String
                    ."". $BossCurrencyC4String ."". $InstanceClearGilString ."". $FinalBossExpString ."";

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
        $info = $this->save("$CurrentPatchOutput/Instances - ". $Patch .".txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}