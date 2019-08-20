<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:Recipes
 */
class Mappy implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '
<div style="position: relative; float: left;">
{{User:IcarusTwine/Sandbox3
  | base = Upperdecksmap01.jpg
  | base_width = 819px
  | float = Player1_Icon.png
  | float_width = 32px
  | float_link = {npc}
  | float_caption = {npc} (x{PosX} Y:{PosY})
  | x = {pixelX}
  | y = {pixelY}
}}';

    public function parse()
    {
        // grab CSV files we want to use
        $MappyCsv = $this->csv('Mappy5.01');

        // (optional) start a progress bar
        $this->io->progressStart($MappyCsv->total);

        // loop through data
        foreach ($MappyCsv->data as $id => $Mappy) {
            $this->io->progressAdvance();

            // Save some data
            $data = [
                '{npc}' => $Mappy['Name'],
                '{Map}' => $Mappy['MapTerritoryID'],
                '{PosY}' => $Mappy['PosX'],
                '{PosX}' => $Mappy['PosY'],
                '{pixelX}' => $Mappy['PixelX/2.5'],
                '{pixelY}' => $Mappy['PixelY/2.5'],

            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeNPCMapWiki.txt');

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}