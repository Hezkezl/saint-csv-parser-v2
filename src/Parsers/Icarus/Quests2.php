<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Quests2
 */
class Quests2 implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "---
    {debug}
    ---";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $QuestCsv = $this->csv('Quest');
        $ClassJobCategoryCsv = $this->csv('ClassJobCategory');
        $ClassJobCsv = $this->csv('ClassJob');
        $GrandCompanyCsv = $this->csv('GrandCompany');
        $BeastTribeCsv = $this->csv('BeastTribe');

        // (optional) start a progress bar
        $this->io->progressStart($QuestCsv->total);

        // loop through data
        foreach ($QuestCsv->data as $id => $QuestData) {
            $this->io->progressAdvance();
            //gather data
            $Name = $QuestData["Name"];
            $Id = $QuestData["Id"];
            $Expansion = $QuestData["Expansion"];
            $ClassJobArray = [];
            foreach(range(0,1) as $i) {
                $ClassJobCategory = $ClassJobCategoryCsv->at($QuestData["ClassJobCategory[$i]"])["Name"];
                $ClassJobLevel = $QuestData["ClassJobLevel[$i]"];
                $ClassJobArray[] = "[". $i ."] - ". $ClassJobCategory ."\n[". $i ."] - ". $ClassJobLevel ."";
            }
            $ClassJob = implode("\n", $ClassJobArray);
            $QuestLevelOffset = $QuestData["QuestLevelOffset"];
            $PreviousQuestJoin = $QuestData["PreviousQuestJoin"];
            $PreviousQuestUnk = $QuestData["unknown_11"];
            $PreviousQuestArray = [];
            foreach(range(0,2) as $i) {
                $PreviousQuestName = $QuestCsv->at($QuestData["PreviousQuest[$i]"])["Name"];
                $PreviousQuestId = $QuestData["PreviousQuest[$i]"];
                $PreviousQuestArray[] = "[". $i ."] - ". $PreviousQuestName ." ( ". $PreviousQuestId ." )";
            }
            $PreviousQuest = implode("\n", $PreviousQuestArray);
            $QuestLockJoin = $QuestData["QuestLockJoin"];
            $Header = $QuestData["Header"];
            $Unk18 = $QuestData["unknown_18"];
            $Unk19 = $QuestData["unknown_19"];
            $ClassJobUnlock = $ClassJobCsv->at($QuestData["ClassJob{Unlock}"])["Name"];
            $GrandCompany = $GrandCompanyCsv->at($QuestData["GrandCompany"])["Name"];
            $GrandCompanyRank = $QuestData["GrandCompanyRank"];
            $InstanceContentArray = [];
            foreach(range(0,2) as $i) {
                $InstanceContent = $QuestData["InstanceContent[$i]"];
                $InstanceContentArray[] = "[". $i ."] - ". $InstanceContent ."";
            }
            $InstanceContent = implode("\n", $InstanceContentArray);
            $Festival = $QuestData["Festival"];
            $FestivalBegin = $QuestData["FestivalBegin"];
            $FestivalEnd = $QuestData["FestivalEnd"];
            $BellStart = $QuestData["Bell{Start}"];
            $BellEnd = $QuestData["Bell{End}"];
            $BeastTribe = $BeastTribeCsv->at($QuestData["BeastTribe"])["Name"];

            $debug = "
            Name = $Name
            Key - $id
            id = $Id
            Expansion = $Expansion
            Classjob = 
            $ClassJob
            QuestLevelOffset = $QuestLevelOffset
            PreviousQuestJoin = $PreviousQuestJoin
            PreviousQuestUnk = $PreviousQuestUnk
            PreviousQuest = 
            $PreviousQuest
            QuestLockJoin = $QuestLockJoin
            Header = $Header
            Unk18 = $Unk18
            Unk19 = $Unk19
            ClassJobUnlock = $ClassJobUnlock
            GrandCompany = $GrandCompany
            GrandCompanyRank = $GrandCompanyRank
            InstanceContent = 
            $InstanceContent
            Festival = $Festival
            FestivalBegin = $FestivalBegin
            FestivalEnd = $FestivalEnd
            BellStart = $BellStart
            BellEnd = $BellEnd
            BeastTribe = $BeastTribe
            ";

            // Save some data
            $data = [
                '{debug}' => $debug,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeEventItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("$CurrentPatchOutput/Quests2 - ". $Patch .".txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}