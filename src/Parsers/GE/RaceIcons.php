<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:RaceIcons
 */
class RaceIcons implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        // grab CSV files we want to use
        $ChocoboRaceAbilityCsv = $this->csv('ChocoboRaceAbility');

        // (optional) start a progress bar
        $this->io->progressStart($ChocoboRaceAbilityCsv->total);

        // loop through data
        foreach ($ChocoboRaceAbilityCsv->data as $id => $ability) {
            $this->io->progressAdvance();

            //
            // Your parse code here
            //

            if (!empty($ability['Icon'])) {
                //$iconstart = substr(($ability['Icon']), 0, 2);
                //$icon = "\n|Icon = 0". $iconstart ."000/0". ($ability['Icon']) .".png";

                // ensure output directory exists
                $EventIconoutputDirectory = $this->getOutputFolder() . '/chocoboabilityicon';
                if (!is_dir($EventIconoutputDirectory)) {
                    mkdir($EventIconoutputDirectory, 0777, true);
                }

                // build icon input folder paths
                $abilityIcon = $this->getInputFolder() .'/icon/'. $this->iconize($ability['Icon']);

                // if icon doesn't exist (not in the input folder icon list), then skip
                if (!file_exists($abilityIcon)) {
                    continue;
                }

                $abilityiconFileName = "{$EventIconoutputDirectory}/{$ability['Name']} Icon.png";


                // inform console what item we're copying
                //$this->io->text("Item: <comment>{$ability['Name']}</comment>");
                //$this->io->text(
                //sprintf(
                //'- copy <info>%s</info> to <info>%s</info>', $abilityIcon, $eventiconFileName
                //)
                //);

                // copy the input icon to the output filename
                copy($abilityIcon, $abilityiconFileName);
            }


            //$this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeEventItemWiki.txt
        //$this->io->progressFinish();
        //$this->io->text('Saving ...');
        //$info = $this->save('GeEventItemWiki - '. $patch .'.txt', 999999);

        //$this->io->table(
            //[ 'Filename', 'Data Count', 'File Size' ],
            //$info
        //);
    }

    /**
     * Converts SE icon "number" into a proper path
     */
    private function iconize($number, $hq = false)
    {
        $number = intval($number);
        $extended = (strlen($number) >= 6);

        if ($number == 0) {
            return null;
        }

        // create icon filename
        $icon = $extended ? str_pad($number, 5, "0", STR_PAD_LEFT) : '0' . str_pad($number, 5, "0", STR_PAD_LEFT);

        // create icon path
        $path = [];
        $path[] = $extended ? $icon[0] . $icon[1] . $icon[2] .'000' : '0'. $icon[1] . $icon[2] .'000';

        $path[] = $icon;

        // combine
        $icon = implode('/', $path) .'.png';

        return $icon;
    }
}
