<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:RaceAbility
 */
class RaceAbility implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "http://ffxiv.gamerescape.com/wiki/{name}?action=edit
{{ARR Infobox Race Ability
|Index = {index}
|Patch = {patch}
|Name = {name}
|Type = {type}
|Manual = {manual}
|Description = {description}
|Duration ={duration}
}}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $ChocoboRaceAbilityCsv = $this->csv('ChocoboRaceAbility');
        $ChocoboRaceAbilityTypeCsv = $this->csv('ChocoboRaceAbilityType');

        // (optional) start a progress bar
        $this->io->progressStart($ChocoboRaceAbilityCsv->total);
        
        $this->PatchCheck($Patch, "ChocoboRaceAbility", $ChocoboRaceAbilityCsv);
        $PatchNumber = $this->getPatch("ChocoboRaceAbility");

        // loop through data
        foreach ($ChocoboRaceAbilityCsv->data as $id => $ability) {
            $this->io->progressAdvance();

            //
            // Your parse code here
            //
            if (empty($ability['Name'])) continue;
            $Patch = $PatchNumber[$id];

            $name = $ability['Name'];
            $duration = false;

            if (substr("$name", -1) != "I") {
                $manual = "Chocobo Training Manual - ". $name ." I";
            } else {
                $manual = "Chocobo Training Manual - ". $name;
            }

            if (in_array($ability['id'], array( 1, 2, 3, 16, 17, 18, 22, 23, 24, 25, 26, 27, 52, 53, 54, 55, 56, 57))) {
                $duration = " ". $ability['Value'] . "s";
                $description = preg_replace("/\n<UIForeground>.*/", null, $ability['Description']);
            } else {
                $description = $ability['Description'];
            }

            if ($ChocoboRaceAbilityTypeCsv->at($ability['ChocoboRaceAbilityType'])['IsActive'] == "True") {
                $type = "Ability";
            } else {
                $type = "Trait";
            }

            if (!empty($ability['Icon'])) {

                // ensure output directory exists
                $EventIconoutputDirectory = $this->getOutputFolder() . "/$PatchID/ChocoboAbilityIcons";
                if (!is_dir($EventIconoutputDirectory)) {
                    mkdir($EventIconoutputDirectory, 0777, true);
                }

                // build icon input folder paths
                $abilityIcon = $this->getInputFolder() .'/icon/'. $this->iconize($ability['Icon']);

                // if icon doesn't exist (not in the input folder icon list), then skip
                if (!file_exists($abilityIcon)) continue;

                $abilityiconFileName = "{$EventIconoutputDirectory}/{$ability['Name']} Icon.png";

                // inform console what item we're copying
                $this->io->text("Ability: <comment>{$ability['Name']}</comment>");
                $this->io->text(
                    sprintf('- copy <info>%s</info> to <info>%s</info>', $abilityIcon, $abilityiconFileName));

                // copy the input icon to the output filename
                copy($abilityIcon, $abilityiconFileName);
            }

            $data = [
                '{index}' => $ability['id'],
                '{patch}' => $Patch,
                '{name}' => $ability['Name'],
                '{type}' => $type,
                '{description}' => $description,
                '{manual}' => $manual,
                '{duration}' => $duration,
            ];

            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeEventItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("ChocoboRaceAbilities.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}