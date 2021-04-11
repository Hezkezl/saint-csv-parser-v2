<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:Translator
 */
class Translator implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}\n'''Auto-Translator'''\n{Header}{output}\n{{-stop-}}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $CompletionCsv = $this->csv('Completion');

        $this->io->progressStart($CompletionCsv->total);
        $OutputArray = [];
        $Headerarray[] = '{| class="itembox shadowed" style="color:white; width:100%; cellpadding=0; cellspacing=1;" border={{{border|0}}}';
        $Headerarray[] = '|-';
        $Headerarray[] = '|[[File:Main_Command_16_Icon.png|left|link=]]<onlyinclude>The Auto-Translator is accessed by pressing "Tab" while typing in the Chat Window. This gives you access to automatically translated phrases that appear in the native language of people around you.</onlyinclude>';
        $Headerarray[] = '|}';
        $Headerarray[] = '<br clear="all"/>';
        $Headerarray[] = 'Once you hit "Tab" with the chat box active, you are presented with a list of options, numbered 1 to a maximum of 9. If more than 9 options are available, the remaining options will be listed on another "page". If this happens, the list will start from 1 again.';
        $Headerarray[] = '';
        $Headerarray[] = 'For example, the first list contains 21 options. This means there are 2 pages of 9 options, and 1 page of 3.';
        $Headerarray[] = '';
        $Headerarray[] = '== Navigation ==';
        $Headerarray[] = 'If there are more than 9 options in the current list of options, the options are grouped in "pages." Using the "left" and "right" arrow keys, you can navigate to the next and previous page.';
        $Headerarray[] = 'Using the "down" and "up" arrows keys, you can navigate to the next and previous item in the list. You may also use the "pageup" and "pagedown" keys.';
        $Headerarray[] = '';
        $Headerarray[] = 'Pressing the "enter" key will select the highlighted item. You can also use the number shown next to the item to select it directly, or use the mouse to select an item.';
        $Headerarray[] = '';
        $Headerarray[] = 'If the selected item is shown in square brackets ("[" and "]"), you will be presented with a sublist. Otherwise, the item will be placed into the chat bar.';
        $Headerarray[] = '';
        $Headerarray[] = 'If you are in a sublist, and you want to go back a level, press the "escape" key once per level.';
        $Headerarray[] = '';
        $Headerarray[] = '== Full List of Options ==';
        $Headerarray[] = 'Below is shown an exhaustive list of the options available in the English version of [[Final Fantasy XIV: A Realm Reborn]].';
        $Headerarray[] = '';
        $Headerarray[] = '';
        $Header = implode("\n",$Headerarray);
        // loop through data
        $CompletionArray = [];
        foreach ($CompletionCsv->data as $id => $Completion) {
            $this->io->progressAdvance();
            if (empty($Completion['unknown_4'])) continue;
            $Sort = $Completion['unknown_1'];
            if (empty($Completion['unknown_3'])){
                $CompletionArray[$Sort][] = "## : ".$Completion['unknown_4'];
            }
            if ($Completion['unknown_3'] === "@"){
                $CompletionArray[$Sort][] = "# :".$Completion['unknown_4'];
            }
            if ((!empty($Completion['unknown_3'])) && ($Completion['unknown_3'] != "@")){
                $CompletionArray[$Sort][] = "# :".$Completion['unknown_4'];
                $sheetarray1 = explode("[", str_replace("]","",$Completion['unknown_3']));
                $PreSheetName = $sheetarray1[0];
                $SheetName = $this->csv($PreSheetName);
                switch ($PreSheetName) {
                    case 'Mount':
                    case 'Companion':
                        $Offset = "Singular";
                    break;
                    case 'Race':
                    case 'Tribe':
                        $Offset = "Masculine";
                    break;
                    case 'TextCommand':
                        $Offset = "Command";
                    break;
                    case 'PetMirage':
                        $Offset = "unknown_3";
                    break;
                    default:
                        $Offset = "Name";
                    break;
                }
                if (empty($sheetarray1[1])){
                    foreach ($SheetName->data as $id => $SheetData) {
                        if (empty($SheetName->at($id)[$Offset])) continue;
                        $CompletionArray[$Sort][] = "## : ".$SheetName->at($id)[$Offset];
                    }
                }
                if (!empty($sheetarray1[1])) {
                    $sheetarray2 = explode(",", $sheetarray1[1]);
                    foreach ($sheetarray2 as $key => $value) {
                        $explodevalue = explode("-", $value);
                        if (empty($explodevalue[1])) continue;
                        $range1 = $explodevalue[0];
                        $range2 = $explodevalue[1];
                        foreach (range($range1, $range2) as $i) {
                            if (empty($SheetName->at($i)[$Offset])) continue;
                            $CompletionArray[$Sort][] = "## : ".$SheetName->at($i)[$Offset];
                        }
                    }
                }
            }
        }
        ksort($CompletionArray);
        $finaloutput = [];
        foreach ($CompletionArray as $key => $value) {
            $finaloutput[$key] = implode("\n", $value);
        }

        $output = implode("\n",$finaloutput);

        // Save some data
        $data = [
            '{Header}' => $Header,
            '{output}' => $output,
        ];

        // format using Gamer Escape formatter and add to data array
        // need to look into using item-specific regex, if required.
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Translator.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}

/*
11th April 2021 - Creation
*/
