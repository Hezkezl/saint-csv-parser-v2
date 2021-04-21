<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Emotes
 */
class Emotes implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Output}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $EmoteCsv = $this->csv('Emote');
        $EmoteCategoryCsv = $this->csv('EmoteCategory');
        $EmoteModeCsv = $this->csv('EmoteMode');
        $TextCommandCsv = $this->csv('TextCommand');
        $LogMessageCsv = $this->csv('LogMessage');
        $ItemCsv = $this->csv('Item');
        $ItemActionCsv = $this->csv('ItemAction');
        $QuestCsv = $this->csv('Quest');

        // (optional) start a progress bar
        $this->io->progressStart($EmoteCsv->total);
        
        $this->PatchCheck($Patch, "Emote", $EmoteCsv);
        $PatchNumber = $this->getPatch("Emote");
        $ItemArray = [];
        foreach ($ItemCsv->data as $id => $Item) {
            if (strpos($Item['Name'], 'Ballroom Etiquette') === false) continue;
            if ($ItemActionCsv->at($Item['ItemAction'])['Type'] != "2633") continue;
            $EmoteID = $ItemActionCsv->at($Item['ItemAction'])['Data[0]'];
            $Name = $Item['Name'];
            $ItemArray[$EmoteID] = $Name;
        }
        $QuestArray = [];
        foreach ($QuestCsv->data as $id => $Quest) {
            if ($Quest['Emote{Reward}'] > 0){
                $EmoteID = $Quest['Emote{Reward}'];
                $Name = $Quest['Name'];
                $QuestArray[$EmoteID] = $Name;
            }
        }
        

        // loop through data
        $OutputArray = [];
        foreach ($EmoteCsv->data as $id => $Emote) {
            $this->io->progressAdvance();
            if (empty($Emote['Name'])) continue;
            $Name = $Emote['Name'];
            $Patch = $PatchNumber[$id];
            $Type = $EmoteCategoryCsv->at($Emote['EmoteCategory'])['Name'];
            $Description = $TextCommandCsv->at($Emote['TextCommand'])['Description'];
            
            //commands
            $Commands = [];
            if (!empty($TextCommandCsv->at($Emote['TextCommand'])['Command'])) {
                $Commands[] = $TextCommandCsv->at($Emote['TextCommand'])['Command'];
            }
            if (!empty($TextCommandCsv->at($Emote['TextCommand'])['Alias'])) {
                $Commands[] = $TextCommandCsv->at($Emote['TextCommand'])['Alias'];
            }
            if (!empty($TextCommandCsv->at($Emote['TextCommand'])['ShortAlias'])) {
                $Commands[] = $TextCommandCsv->at($Emote['TextCommand'])['ShortAlias'];
            }
            if (!empty($TextCommandCsv->at($Emote['TextCommand'])['ShortCommand'])) {
                $Commands[] = $TextCommandCsv->at($Emote['TextCommand'])['ShortCommand'];
            }
            $Command = implode(",",$Commands);
            $IconName = $Name." Icon.png";
            $Quest = "";
            $Item = "";
            $UnlockLinkID = $Emote['UnlockLink'];
            if (!empty($ItemArray[$UnlockLinkID])) {
                $Item = $ItemArray[$UnlockLinkID];
            }
            if (!empty($QuestArray[$id])) {
                $Quest = $QuestArray[$id];
            }
            $UsesArray = [];
            //if (!empty($Emote['TimelineID'])){
            //    $UsesArray[] = "Standing";
            //}
            if (!empty($Emote['TimelineID[GroundSitting]'])){
                $UsesArray[] = "GroundSitting/LayingDown";
            }
            if (!empty($Emote['TimelineID[ChairSitting]'])){
                $UsesArray[] = "ChairSitting";
            }
            //if (!empty($Emote['TimelineID[Mounting]'])){
            //    $UsesArray[] = "Mounting";
            //}
            //if (!empty($Emote['TimelineID[Lying]'])){
            //    $UsesArray[] = "Lying";
            //}
            if (!empty($Emote['TimelineID[Adjust]'])){
                $UsesArray[] = "Adjust";
            }
            switch ($Emote['unknown_10']) {
                case 'True':
                    $UsesArray[] = "Swimming";
                break;
                case 'False':
                    $UsesArray[] = "Standing";
                break;
            }
            $UsesArray[] = $Emote['HasCancelEmote'];
            $UsesArray[] = $Emote['DrawsWeapon'];
            $UsesArray[] = $Emote['unknown_9'];
            $UsesArray[] = $Emote['unknown_11'];
            $UsesArray[] = $Emote['unknown_14'];
            $UsesArray[] = $Emote['unknown_15'];
            $Uses = implode("\n", $UsesArray);

            $OGArray = array(
                //"<Clickable(<If(Equal(ObjectParameter(1),ObjectParameter(2)))>",
                //"<Else/><If(PlayerParameter(7))><SheetEn(ObjStr,2,PlayerParameter(7),1,1)/><Else/>ObjectParameter(2)</If></If>)/>",
                //"<If(Equal(ObjectParameter(1),ObjectParameter(3)))><If(PlayerParameter(8))><SheetEn(ObjStr,2,PlayerParameter(8),1,1)/>",
                //"<If(Equal(ObjectParameter(1),ObjectParameter(2)))>",
                //"<Else/>",
                //"</If>",
                //"<If(PlayerParameter(8))><SheetEn(ObjStr,2,PlayerParameter(8),1,1)/>/ObjectParameter(3)",
                //"<If(Equal(ObjectParameter(1),ObjectParameter(2)))>",
                //"<If(PlayerParameter(7))><SheetEn(ObjStr,2,PlayerParameter(7),1,1)/>/ObjectParameter(2)'s)/> "
            );
            $ReplaceArray = array(
                //"{[Loremtextmale|",
                //"|playername}}",
                //"",
                //"{[Loremtextmale|",
                //"|",
                //"}}",
                //"",
                //"",
                //" "
            );
            $TargetMessage = str_replace($OGArray, $ReplaceArray, $LogMessageCsv->at($Emote['LogMessage{Targeted}'])['Text']);
            $UnTargetMessage = str_replace($OGArray, $ReplaceArray, $LogMessageCsv->at($Emote['LogMessage{Untargeted}'])['Text']);


            
            $OutputString = "{{-start-}}\n";
            //$OutputString .= "'''$Name'''\n";
            //$OutputString .= "{{ARR Infobox Emote\n";
            //$OutputString .= "| Patch = $Patch\n";
            $OutputString .= "| Name = $Name\n";
            //$OutputString .= "| Type = $Type\n";
            //$OutputString .= "| Description = $Description\n";
            //$OutputString .= "| Command = $Command\n";
            //$OutputString .= "| Quest = $Quest\n";
            //$OutputString .= "\n";
            $OutputString .= "| Uses =\n$Uses\n";
            //$OutputString .= "\n";
            //$OutputString .= "| Item = $Item\n";
            //$OutputString .= "\n";
            //$OutputString .= "| TargetMessage = $TargetMessage\n";
            //$OutputString .= "| UnTargetMessage = $UnTargetMessage\n";
            //$OutputString .= "\n";
            //$OutputString .= "| Notes =\n";
            //$OutputString .= "}}\n";
            //$OutputString .= "{{-stop-}}\n";
            $OutputArray[] = $OutputString;
            
        }
        $Output = implode("\n",$OutputArray);

        $data = [
            '{Output}' => $Output,
        ];

        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeEventItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Emotes.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}