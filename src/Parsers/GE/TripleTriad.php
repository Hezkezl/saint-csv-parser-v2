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

            // code starts here
            $Description = strip_tags($TripleTriad['Description']); // strip <Emphasis> and other tags from the Description
            $Description = str_replace("\n", "<br>", $Description); // replace literal line breaks with <br>

            // Using the ID#/key from TripleTriadCard.csv, match that up with the column "TripleTriadCardType" in the file
            // TripleTriadCardResident, and take THAT value and match it with the "Name" column from TripleTriadCardRarity.csv
            $Family = $TripleTriadCardTypeCsv->at($TripleTriadCardResidentCsv->at($TripleTriad['id'])['TripleTriadCardType'])['Name'];
            // Do the same process as above, except use TripleTriadCardType.csv and
            // return the "Stars" / Rarity of the card  instead
            $Rarity = $TripleTriadCardRarityCsv->at($TripleTriadCardResidentCsv->at($TripleTriad['id'])['TripleTriadCardRarity'])['Stars'];

            $LargeIcon = (82100 + $TripleTriad['id']);
            $SmallIcon = (82500 + $TripleTriad['id']);

            // Save some data
            $data = [
                '{Name}' => str_replace(" & ", " and ", $TripleTriad['Name']),
                '{Patch}' => $Patch,
                '{Index}' => $TripleTriad['id'],
                '{Rarity}' => $Rarity,
                '{Family}' => ($TripleTriadCardResidentCsv->at($TripleTriad['id'])['TripleTriadCardType'] > 0)
                    ? "\n| Family = ". $Family : "\n| Family =",
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
