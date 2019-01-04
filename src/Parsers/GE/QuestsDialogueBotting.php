<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:QuestsDialogueBotting
 */
class QuestsDialogueBotting implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{{-start-}}
\'\'\'Loremonger:{name}\'\'\'
<noinclude>{{Lorempageturn|prev={prevquest1}{prevquest2}{prevquest3}|next=}}{{Loremquestheader|{name}|Mined=X|Summary=}}</noinclude>
{{LoremLoc|Location=}}
{dialogue}{battletalk}
{{-stop-}}';

    public function parse()
    {
        // grab CSV files
        $questCsv = $this->csv('Quest');

        $this->io->progressStart($questCsv->total);

        // loop through quest data
        foreach($questCsv->data as $id => $quest) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($quest['Name']) || $quest['Name'] === "Testdfghjkl;") {
                continue;
            }

            //---------------------------------------------------------------------------------
            // Actual code definition begins below!
            //---------------------------------------------------------------------------------

            //Show the Previous Quest(s) correct Name by looking them up.
            $prevquest1 = $questCsv->at($quest['PreviousQuest[0]'])['Name'];
            $prevquest2 = $questCsv->at($quest['PreviousQuest[1]'])['Name'];
            $prevquest3 = $questCsv->at($quest['PreviousQuest[2]'])['Name'];

            // Start Dialogue code
            $dialogue = [];
            $battletalk = [];
            $system = [];

            //If the Quest ID (NOT the same as id) is not empty, get the first three letters of the string after the
            //underscore (_) in its full name, and store it as $folder. ie: "BanNam305_03107" would be: $folder = 031
            if (!empty($quest['Id'])) {
                $folder = substr(explode('_', $quest['Id'])[1], 0, 3);
                $textdata = $this->csv("quest/{$folder}/{$quest['Id']}");
                //print_r($textdata);die;

                foreach($textdata->data as $i => $entry) {
                    // grab files to a friendlier variable name
                    //$id = $entry['id'];
                    $command = $entry['unknown_1'];
                    $text = $entry['unknown_2'];

                    // get the text group from the command
                    $textgroup = $this->getTextGroup($i, $command);

                    // ---------------------------------------------------------------
                    // Handle quest text data
                    // ---------------------------------------------------------------

                    /**
                     * Textgroup provides details on the command type, eg:
                     * type: (npc, question, todo, scene, etc
                     * npc: if "type == dialogue", then npc be the npc name!
                     * order: the entry order, might not need
                     *
                     * Fill up arrays and then you can use something like:
                     *
                     *          implode("\n", $objectives)
                     *
                     * to throw them in your wiki format at the bottom
                     */

                    // add dialogue
                    if ($textgroup->type == 'dialogue' && strlen($text) > 1) {
                        // example: NPC says: Blah blah blah
                        $dialogue[] = '{{Loremquote|' .$textgroup->npc .'|link=y|'. $text .'}}';
                    }

                    // add battletalk
                    if ($textgroup->type == 'battle_talk' && strlen($text) > 1) {
                        $battletalk[0] = "\n\n=== Battle Dialogue ===";
                        $battletalk[] = '{{Loremquote|' .$textgroup->npc .'|link=y|'. $text .'}}';
                    }

                    // add system messages
                    if ($textgroup->type == 'system' && strlen($text) > 1) {
                        $system[] = "\n<div>'''". $text ."'''</div>";
                    }

                    // ---------------------------------------------------------------
                }

            }
            //---------------------------------------------------------------------------------

            $data = [
                '{name}' => $quest['Name'],
                '{prevquest1}' => $prevquest1 ? $prevquest1 : "",
                '{prevquest2}' => $prevquest2 ? "|prev2=". $prevquest2 : "",
                '{prevquest3}' => $prevquest3 ? "|prev3=". $prevquest3 : "",
                '{dialogue}' => implode("\n", $dialogue),
                '{battletalk}' => implode("\n", $battletalk),
                '{system}' => implode("\n", $system),
            ];

            // format using Gamer Escape formatter and add to data array
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeQuestWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeQuestDialogue.txt');

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );

    }

    /**
     * This is from XIVDB v3 and will be maintained there.
     * Supports:
     * - BattleTalk
     * - Journal
     * - Scene
     * - Todo (Objectives)
     * - Pop
     * - Access
     * - Instance Talk
     * - Questions + Answers
     * - NPC Dialogue
     * - System
     *
     * @param $i
     * @param $command
     * @return \stdClass
     */
    private function getTextGroup($i, $command)
    {
        $data = new \stdClass();
        $data->type = null;
        $data->npc = null;
        $data->order = null;

        // split command
        $command = explode('_', $command);

        // special one (npc battle talk)
        if ($command[4] == 'BATTLETALK') {
            $data->type = 'battle_talk';
            $data->npc = ucwords(strtolower($command[3]));
            $data->order = isset($command[5]) ? intval($command[5]) : $i;
            return $data;
        }

        if (isset($command[6]) && ($command[6] == 'BATTLETALK')) {
            $data->type = 'battle_talk';
            $data->npc = ucwords(strtolower($command[5]));
            $data->order = isset($command[7]) ? intval($command[7]) : $i;
            return $data;
        }

        // build data structure from command
        switch($command[3]) {
            case 'SEQ':
                $data->type = 'journal';
                $data->order = intval($command[4]);
                break;

            case 'SCENE':
                $data->type = 'scene';
                $data->order = intval($command[7]);
                break;

            case 'TODO':
                $data->type = 'todo';
                $data->order = intval($command[4]);
                break;

            case 'POP':
                $data->type = 'pop';
                $data->order = $i;
                break;

            case 'ACCESS':
                $data->type = 'access';
                $data->order = $i;
                break;

            case 'INSTANCE':
                $data->type = 'instance_talk';
                $data->order = $i;
                break;

            case 'SYSTEM':
                $data->type = 'system';
                $data->order = $i;
                break;

            case 'QIB':
                $npc = filter_var($command[4], FILTER_SANITIZE_STRING);

                // sometimes QIB can be a todo
                if ($npc == 'TODO' or (isset($command[5])) && ($command[5]) == 'TODO') {
                    $data->type = 'todo';
                    $data->order = $i;
                    break;
                }

                $data->type = 'battle_talk';
                $data->npc = ucwords(strtolower($npc));
                $data->order = $i;
                break;

            default:
                $npc = ucwords(strtolower($command[3]));
                $order = isset($command[5]) ? intval($command[5]) : intval($command[4]);

                // if npc is numeric, budge over 1
                if (is_numeric($npc)) {
                    $npc = ucwords(strtolower($command[4]));
                    $order = intval($command[3]);
                }

                $data->type = 'dialogue';
                $data->npc = $npc;
                $data->order = $order;
        }

        return $data;
    }

}
