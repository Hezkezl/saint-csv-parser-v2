<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:AquariumFish
 */
class AquariumFish implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
    '''Aquarium/Fish'''
    {| class=\"GEtable sortable\" style=\"width: 100%;\"
        !Name!!Water!!Size
        |- 
{output}
|}
{{-stop-}}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $AquariumFishCsv = $this->csv('AquariumFish');
        $AquariumWaterCsv = $this->csv('AquariumWater');
        $ItemCsv = $this->csv('Item');
        // (optional) start a progress bar
        $this->io->progressStart($AquariumFishCsv->total);

        // loop through data
        $outputarray = [];
        foreach ($AquariumFishCsv->data as $id => $AquariumFishData) {
            if (empty($ItemCsv->at($AquariumFishData['Item'])['Name'])) continue;
            $outputarray[] = "{{Aquarium|name=". $ItemCsv->at($AquariumFishData['Item'])['Name'] ."|type=". $AquariumWaterCsv->at($AquariumFishData['AquariumWater'])['Name'] ."|size=". $AquariumFishData['Size'] ."}}";
        }
        $output = implode("\n", $outputarray);
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
        $info = $this->save("AquariumFish.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
