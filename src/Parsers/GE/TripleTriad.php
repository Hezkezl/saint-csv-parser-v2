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
    const WIKI_FORMAT = "{{-start-}}
'''{name} (Triple Triad Card)'''
{{ARR Infobox TTCard
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

Large Icon: {LargeIcon}
Small Icon: {SmallIcon}
}}{{-stop-}}";
    public function parse()
    {
        $Patch = '5.1';

        // grab CSV files we want to use
        $TripleTriadCardCsv = $this->csv('TripleTriadCard');
        $TripleTriadCardResidentCsv = $this->csv('TripleTriadCardResident');
        $TripleTriadCardRarityCsv = $this->csv('TripleTriadCardRarity');
        $TripleTriadCardTypeCsv = $this->csv('TripleTriadCardType');

        // (optional) start a progress bar
        $this->io->progressStart($TripleTriadCardCsv->total);

        // loop through data
        foreach ($TripleTriadCardCsv->data as $id => $TripleTriad) {
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($TripleTriad['Name'])) {
                continue;
            }

            // Your parse code here

            $Description = strip_tags($TripleTriad['Description']);
            $Description = str_replace("\n", "<br>", $Description);
            $Name = str_replace(" & ", " and ", $TripleTriad['Name']);
            $Family = $TripleTriadCardResidentCsv->at($TripleTriad['id'])['TripleTriadCardType'];
            $Type = [
                0 => NULL,
                1 => "Primal",
                2 => "Scion",
                3 => "Beastman",
                4 => "Garlean",
                5 => NULL,
            ];
            $LargeIcon = (82100 + $TripleTriad['id']);
            $SmallIcon = (82500 + $TripleTriad['id']);

            // Save some data
            $data = [
                '{Name}' => $Name,
                //'{Name}' => $TripleTriadCardCsv->at($TripleTriad['Name']),
                '{Patch}' => $Patch,
                '{Index}' => $TripleTriad['id'],
                '{Rarity}'=> $TripleTriadCardResidentCsv->at($TripleTriad['id'])['TripleTriadCardRarity'],
                '{Family}' => ($Family > 0) ? "\n| Family = ". $Type["$Family"] : "\n| Family =",
                '{ValueTop}' => $TripleTriadCardResidentCsv->at($TripleTriad['id'])['Top'],
                '{ValueRight}' => $TripleTriadCardResidentCsv->at($TripleTriad['id'])['Right'],
                '{ValueBottom}' => $TripleTriadCardResidentCsv->at($TripleTriad['id'])['Bottom'],
                '{ValueLeft}' => $TripleTriadCardResidentCsv->at($TripleTriad['id'])['Left'],
                '{Description}' => $Description,
                '{LargeIcon}' => $LargeIcon,
                '{SmallIcon}' => $SmallIcon,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeTripleTriadWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeTripleTriadWiki.txt', 2000);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
