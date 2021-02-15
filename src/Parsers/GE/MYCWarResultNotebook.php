<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:MYCWarResultNotebook
 */
class MYCWarResultNotebook implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
'''{NameURL}_(Field_Record)'''
{{Field Record
| Patch       = {Patch}
| Icon        = 0{Icon}.png
| Image       = 0{Image}.png
| Name        = {Name}
| Number      = {Number}
| Rarity      = {Rarity}
| Description = {Description}
}}
{{-stop-}}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $MYCWarResultNotebookCsv = $this->csv('MYCWarResultNotebook');
        // (optional) start a progress bar
        $this->io->progressStart($MYCWarResultNotebookCsv->total);
        
        $this->PatchCheck($Patch, "MYCWarResultNotebook", $MYCWarResultNotebookCsv);
        $PatchNumber = $this->getPatch("MYCWarResultNotebook");

        // loop through data
            foreach ($MYCWarResultNotebookCsv->data as $id => $MYCWarResultNotebookData) {
                if (empty($MYCWarResultNotebookData['Name'])) continue;
                $Patch = $PatchNumber[$id];
            // Save some data
            $data = [
                '{Icon}' => $MYCWarResultNotebookData['Icon'],
                '{Image}' => $MYCWarResultNotebookData['Image'],
                '{Number}' => $MYCWarResultNotebookData['Number'],
                '{Name}' => $MYCWarResultNotebookData['Name'],
                '{NameURL}' => str_replace(' ', '_', $MYCWarResultNotebookData['Name']),
                '{Rarity}' => $MYCWarResultNotebookData['Rarity'],
                '{Description}' => str_replace("Birthplace:", "\nBirthplace:", str_replace("Age:", "\nAge:", str_replace("Race:", "\nRace:", $MYCWarResultNotebookData['Description']))),
                '{Patch}' => $Patch,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }
        

        // save our data to the filename: GeEventItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("MYCWarResultNotebook.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}