<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:TestQuest
 */
class TestQuest implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{output}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        //$QuestChat = $this->csv('quest/000/ClsArc002_00067');
        $QuestChat = $this->csv('Quest');
        // (optional) start a progress bar
        $this->io->progressStart($QuestChat->total);

        // loop through data
        $outputarray = [];
        $NpcName = null;
        $NPCArray = [];
        foreach ($QuestChat->data as $id => $entry) {
            //get the 2 columns in text sheet
            $command = $entry['unknown_1'];
            $text = $entry['unknown_2'];
            //if empty then skip that shit
            if (empty($text)) continue;
            //split the "Command" column name up into parts every time "_" exists
            $CommandExplode = explode("_", $command);
            if (empty($NpcName)) {
                //set the variable "NpcName" to get the first line in sheet
                $NpcName = $CommandExplode[3];
            }
            if (!empty($NpcName)) {
                //if npcname is the same as the last set variable then...
                if ($NpcName == $CommandExplode[3]) {
                    $NPCArray[0] = "{{Loremquote|". ucwords(strtolower($NpcName)) ."|link=y|";
                    $NPCArray[] = "". $text ."\n----\n"; 
                } else {
                    //if its not the same then set the new "first line" name
                    $NpcName = $CommandExplode[3];
                    $NpcArrayOutput = implode("", $NPCArray);
                    //make a new empty array now the previous has been saved in $NpcArrayOutput
                    $NPCArray = [];
                    $NPCArray[0] = "{{Loremquote|". ucwords(strtolower($NpcName)) ."|link=y|";
                    $NPCArray[] = "". $text ."\n----\n"; 
                    $outputarray[] = "" .$NpcArrayOutput ."}}";
                }
            }
        }
        //because im lazy i just fixed a smol problem here
        $output = str_replace("\n----\n}}", "}}", implode("\n\n", $outputarray));
        // Save some data
        $data = [
            '{output}' => $output,
        ];

        // format using Gamer Escape formatter and add to data array
        // need to look into using item-specific regex, if required.
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        

        // save our data to the filename: GeEventItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("$CurrentPatchOutput/TestQuest - ". $Patch .".txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}