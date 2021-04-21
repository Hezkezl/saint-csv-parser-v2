<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:TripleTriad
 */
class TripleTriad implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Top}{{ARR Infobox TTCard
| Name = {Name}
| Patch = {Patch}
| Index = {Index}
| Rarity = {Rarity}{Family}
| Requires = {Name} Card
| ValueTop    = {ValueTop}
| ValueRight  = {ValueRight}
| ValueBottom = {ValueBottom}
| ValueLeft   = {ValueLeft}
| Description = {Description}
}}{Bottom}";
    public function parse()
    {
        // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
        // manually, set to false
        $Bot = "true";
        include (dirname(__DIR__) . '/Paths.php');
        // grab CSV files we want to use
        $TripleTriadCardCsv = $this->csv("TripleTriadCard");
        $TripleTriadCardResidentCsv = $this->csv("TripleTriadCardResident");
        $TripleTriadCardRarityCsv = $this->csv("TripleTriadCardRarity");
        $TripleTriadCardTypeCsv = $this->csv("TripleTriadCardType");

        // (optional) start a progress bar
        $this->io->progressStart($TripleTriadCardCsv->total);
        
        $this->PatchCheck($Patch, "TripleTriadCard", $TripleTriadCardCsv);
        $PatchNumber = $this->getPatch("TripleTriadCard");

        // loop through data
        foreach ($TripleTriadCardCsv->data as $id => $TripleTriad) {
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($TripleTriad['Name'])) {
                continue;
            }
            $Patch = $PatchNumber[$id];


            // code starts here
            $Name = str_replace(" & ", " and ", $TripleTriad['Name']); // replace the & character with 'and' in names
            $Description = strip_tags($TripleTriad['Description']); // strip <Emphasis> and other tags from the Description
            $Description = str_replace("\n", "<br>", $Description); // replace literal line breaks with <br>

            // Using the ID#/key from TripleTriadCard.csv, match that up with the column "TripleTriadCardType" in the file
            // TripleTriadCardResident, and take THAT value and match it with the "Name" column from TripleTriadCardRarity.csv
            $Family = $TripleTriadCardTypeCsv->at($TripleTriadCardResidentCsv->at($TripleTriad['id'])['TripleTriadCardType'])['Name'];
            // Do the same process as above, except use TripleTriadCardType.csv and
            // return the "Stars" / Rarity of the card  instead
            $Rarity = $TripleTriadCardRarityCsv->at($TripleTriadCardResidentCsv->at($TripleTriad['id'])['TripleTriadCardRarity'])['Stars'];

            // change the top and bottom code depending on if I want to bot the pages up or not
            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name (Triple Triad Card)'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name (Triple Triad Card)?action=edit\n";
                $Bottom = "";
            };

            // Icon copying
            $LargeIcon = (82100 + $TripleTriad['id']);
            $SmallIcon = (82500 + $TripleTriad['id']);
            // ensure output directory exists
            $TriadIconoutputDirectory = $this->getOutputFolder() . "/$PatchID/TripleTriadIcons";
            // if it doesn't exist, make it
            if (!is_dir($TriadIconoutputDirectory)) {
                mkdir($TriadIconoutputDirectory, 0777, true);
            }

            // build icon input folder paths
            $LargeIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($LargeIcon);
            $SmallIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($SmallIcon);
            // give correct file names to icons for output
            $LargeIconFileName = "{$TriadIconoutputDirectory}/$Name (Triple Triad Card) Full.png";
            $SmallIconFileName = "{$TriadIconoutputDirectory}/$Name (Triple Triad Card) icon.png";
            // actually copy the icons
            copy($LargeIconPath, $LargeIconFileName);
            copy($SmallIconPath, $SmallIconFileName);

            // Save some data
            $data = [
                '{Top}' => $Top,
                '{Name}' => $Name,
                '{Patch}' => $Patch,
                '{Index}' => $TripleTriad['id'],
                '{Rarity}' => $Rarity,
                '{Family}' => ($TripleTriadCardResidentCsv->at($TripleTriad['id'])['TripleTriadCardType'] > 0)
                    ? "\n| Family = ". $Family : "\n| Family =",
                '{ValueTop}' => str_replace("10", "A", $TripleTriadCardResidentCsv->at($TripleTriad['id'])['Top']),
                '{ValueRight}' => str_replace("10", "A", $TripleTriadCardResidentCsv->at($TripleTriad['id'])['Right']),
                '{ValueBottom}' => str_replace("10", "A", $TripleTriadCardResidentCsv->at($TripleTriad['id'])['Bottom']),
                '{ValueLeft}' => str_replace("10", "A", $TripleTriadCardResidentCsv->at($TripleTriad['id'])['Left']),
                '{Description}' => $Description,
                '{LargeIcon}' => $LargeIcon,
                '{SmallIcon}' => $SmallIcon,
                '{Bottom}' => $Bottom,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        };

        // save our data to the filename: GeTripleTriadWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("TripleTriad.txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}