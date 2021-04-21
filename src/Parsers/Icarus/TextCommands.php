<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:TextCommands
 */
class TextCommands implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Output}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $TextCommandCsv = $this->csv('TextCommand');

        // (optional) start a progress bar
        $this->io->progressStart($TextCommandCsv->total);
        
        $this->PatchCheck($Patch, "TextCommand", $TextCommandCsv);
        $PatchNumber = $this->getPatch("TextCommand");
        
        $OutputArray = [];
        foreach ($TextCommandCsv->data as $id => $TextCommand) {
            $this->io->progressAdvance();
            if (empty($TextCommand['Command'])) continue;
            $Command = $TextCommand['Command'];
            $Patch = $PatchNumber[$id];
            $Description = $TextCommand['Description'];
            
            $Commands = [];
            if (!empty($TextCommand['Alias'])) {
                $Commands[] = $TextCommand['Alias'];
            }
            if (!empty($TextCommand['ShortAlias'])) {
                $Commands[] = $TextCommand['ShortAlias'];
            }
            if (!empty($TextCommand['ShortCommand'])) {
                $Commands[] = $TextCommand['ShortCommand'];
            }
            $Alias = implode(",",$Commands);


            
            $OutputString = "{{-start-}}\n";
            $OutputString .= "'''TextCommand$Command'''\n";
            $OutputString .= "{{ARR Infobox TextCommand\n";
            $OutputString .= "| Patch = $Patch\n";
            $OutputString .= "| Command = $Command\n";
            $OutputString .= "| Alias = $Alias\n";
            $OutputString .= "| Description = $Description\n";
            $OutputString .= "\n";
            $OutputString .= "| Notes =\n";
            $OutputString .= "}}\n";
            $OutputString .= "{{-stop-}}\n";
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
        $info = $this->save("TextCommands.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}