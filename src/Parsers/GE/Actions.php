<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:Actions
 */
class Actions implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{ARR Infobox Action
|Patch = {patch}

|Index = {index}

|Name = {name}
|Type = {type}
{npcif}

|Acquired = {classjoblong}
|Acquired Level = {level}
|Affinity = {classjobshort} 		<!-- Comma separated -->

|Range = {range}
|Radius = {radius}

|Cast = {casttime}
|Recast = {recast}
|Duration =

|Description = {description}

}}";

    public function parse()
    {
        // grab CSV files we want to use
        $ActionCsv = $this->csv('Action');
        $ActionCategoryCsv = $this->csv('ActionCategory');
        $ActionTransientCsv = $this->csv('ActionTransient');
        $ClassJobCsv = $this->csv('ClassJob');
        $ClassJobCategoryCsv = $this->csv('ClassJobCategory');

        $patch = '5.21';
        // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
        // manually, set to false
        $Bot = "false";

        // (optional) start a progress bar
        $this->io->progressStart($ActionCsv->total);

        // loop through data
        foreach ($ActionCsv->data as $id => $Action) {
            $this->io->progressAdvance();
            $index = $Action['id'];

            $Name = $Action['Name'];

            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name/Patch'''\n$patch\n<noinclude>[[Category:Patch Subpages]]</noinclude>\n{{-stop-}}{{-start-}}\n'''$Name'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name?action=edit\n";
                $Bottom = "";
            };

            $Type = $ActionCategoryCsv->at($Action['ActionCategory'])['Name'];
            //add "NPC SKILL" if it's an npc so we can sort
           	if ($Action['ClassJob'] == "-1") {
           		$npcif = "|Player Allowed = False";
           	} elseif ($Action['ClassJob'] !== "-1") {
           		$npcif = "";
           	}

           	$ClassJobLong = ucwords(strtolower($ClassJobCsv->at($Action['ClassJob'])['Name']));
           	$ClassJobShort = str_replace(" ",",",$ClassJobCategoryCsv->at($Action['ClassJobCategory'])['Name']);
           	$Level = $Action['ClassJobLevel'];


           	if ($Action['Range'] == "-1") {
           		$Range = "3";
           	} elseif ($Action['Range'] !== "-1") {
           		$Range = $Action['Range'];
           	}
           	$Radius = $Action['EffectRange'];

           	if ($Action['Cast<100ms>'] == "0") {
           		$CastTime = "Instant";
           	} elseif ($Action['Cast<100ms>'] !== "0") {
           		$CastTimeRaw = $Action['Cast<100ms>'];
                $CastTimeMins = floor(($CastTimeRaw / 60) % 60);
                $CastSeconds = $CastTimeRaw % 60;
                $CastString = " ". $CastTimeMins ."m". $CastSeconds ."s";
                $CastFormat1 = str_replace(" 0m", " ", $CastString);
                $CastTime = str_replace("m0s", "m", $CastFormat1);
           	}

           	if ($Action['Recast<100ms>'] == "0") {
           		$Recast = "Instant";
           	} elseif ($Action['Recast<100ms>'] !== "0") {
           		$ReCastTimeRaw = $Action['Recast<100ms>'];
                $ReCastTimeMins = floor(($ReCastTimeRaw / 60) % 60);
                $ReCastSeconds = $ReCastTimeRaw % 60;
                $ReCastString = " ". $ReCastTimeMins ."m". $ReCastSeconds ."s";
                $ReCastFormat1 = str_replace(" 0m", " ", $ReCastString);
                $Recast = str_replace("m0s", "m", $ReCastFormat1);
                //$Recast = $ReCastTimeRaw;
           	}
           	$StatusGainedRaw = $Action['Status{GainSelf}'];
           	//$Duration = $StatusCsv->at($DurationRaw)['']
           	//$StatusGainedName = $
           	$Description = $ActionTransientCsv->at($Action['id'])['Description'];

           	$Combo = $ActionCsv->at($Action['Action{Combo}'])['Name'];



            // Save some data
            $data = [
                //'{top}' => $Top,
                //'{bottom}' => $Bottom,
                '{patch}' => $patch,
                '{name}' => $Name,
                '{type}' => $Type,
                '{classjoblong}' => $ClassJobLong,
                '{level}' => $Level,
                '{classjobshort}' => $ClassJobShort,
                '{range}' => $Range,
                '{radius}' => $Radius,
                '{casttime}' => $CastTime,
                '{recast}' => $Recast,
                '{description}' => $Description,
                '{npcif}' => $npcif,
                '{index}' => $index,
                '{combo}' => $Combo,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Actions - ". $patch .".txt", 999999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
