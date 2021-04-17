<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Sightseeing
 */
class Sightseeing implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
'''Sightseeing Log: {name}'''
{{ARR Infobox Sightseeing Log
| Patch = {patch}{expansion}
| Name        = {name}
| Location    = {name}
| Coordinates = X{x}-Y{y}
| Vista Record Number = {number}
| Impression = {impression}

| Description = {description}
| Walkthrough =
| Weather =
| Emote = {emote}
| Time ={time}
| Miscellaneous Requirement =

| Map =
| Map Description  =

| Screenshot =
| Screenshot Description =

| Vista Image = {vista}
| Vista Image Description =

| Icon = {name} SS Icon.png
}}{{-stop-}}";
    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $AdventureCsv = $this->csv('Adventure');
        $EmoteCsv = $this->csv('Emote');
        $MapCsv = $this->csv('Map');
        $LevelCsv = $this->csv('Level');

        // (optional) start a progress bar
        $this->io->progressStart($AdventureCsv->total);
        
        $this->PatchCheck($Patch, "Adventure", $AdventureCsv);
        $PatchNumber = $this->getPatch("Adventure");

        // loop through data
        foreach ($AdventureCsv->data as $id => $item) {
            $this->io->progressAdvance();

            $name = preg_replace("/\<Emphasis>|<\/Emphasis>/", null, $item['Name']);
            $emote = $EmoteCsv->at($item['Emote'])['Name'];
            if (empty($name)) continue;
            $Patch = $PatchNumber[$id];

            // SS log number code. Needs these calculations to properly show 001, 002, 015, etc
            // $expansionshort is used to show the abbreviation of the expansion name (HW, SB, SHB) for the image name
            $expansion = false;
            $expansionshort = false;
            if ($id <= 2162767) {
                $number1 = $id-2162687;
            } elseif (($id >= 2162768) && ($id <= 2162829)) {
                $number1 = $id-2162767;
                $expansion = "\n| Expansion   = Heavensward";
                $expansionshort = "HW";
            } elseif (($id >= 2162830) && $id <= 2162891) {
                $number1 = $id-2162829;
                $expansion = "\n| Expansion   = Stormblood";
                $expansionshort = "SB";
            } elseif (($id >= 2162892) && $id <= 2162936) {
                $number1 = $id-2162891;
                $expansion = "\n| Expansion   = Shadowbringers";
                $expansionshort = "SHB";
            } else {
                $number1 = "\n| Expansion   = ERROR: INVALID SIGHTSEEING LOG NUMBER";
            }
            // ensure there are always leading 0s. Two if single digit, one if double digit
            $number = str_pad($number1, 3, "0", STR_PAD_LEFT);

            // Vista Image name (used in icon copying section as well as in template above)
            $Vista = "Sightseeing Log - {$expansionshort}{$number}-Complete.png";

            //get x and y
            $x = $LevelCsv->at($item['Level'])['X'];
            $y = $LevelCsv->at($item['Level'])['Z'];
            $mapLink = $LevelCsv->at($item['Level'])['Map'];
            $scale = $MapCsv->at($mapLink)['SizeFactor'];
            $c = $scale / 100.0;
            $offsetx = $MapCsv->at($mapLink)['Offset{X}'];
            $offsetValueX = ($x + $offsetx) * $c;
            $LocX = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
            $offsety = $MapCsv->at($mapLink)['Offset{Y}'];
            $offsetValueY = ($y + $offsety) * $c;
            $LocY = ((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1);

            // Time code
            $MinTimeRaw = str_pad($item['MinTime'], 4, 0, STR_PAD_LEFT);
            $MaxTimeRaw = str_pad($item['MaxTime'], 4, 0, STR_PAD_LEFT);
            $MinTime = substr_replace($MinTimeRaw, ":", -2, -2);
            $MaxTime = substr_replace($MaxTimeRaw, ":", -2, -2);

            // icon copying code
            // ensure output directory exists
            $smallIconOutputDirectory = $this->getOutputFolder() ."/$PatchID/SightseeingLogIcons/small";
            $largeIconOutputDirectory = $this->getOutputFolder() ."/$PatchID/SightseeingLogIcons/large";
            if (!is_dir($smallIconOutputDirectory)) {
                mkdir($smallIconOutputDirectory, 0777, true);
            }
            if (!is_dir($largeIconOutputDirectory)) {
                mkdir($largeIconOutputDirectory, 0777, true);
            }

            // build icon input folder paths
            $smallIcon = $this->getInputFolder() .'/icon/'. $this->iconizeHR($item['Icon{List}']);
            $largeIcon = $this->getInputFolder() .'/icon/'. $this->iconizeHR($item['Icon{Discovered}']);
            $smalliconFileName = "{$smallIconOutputDirectory}/{$name} SS Icon.png";
            $largeiconFileName = "{$largeIconOutputDirectory}/{$Vista}";

            // copy the input icon to the output filenames
            copy($smallIcon, $smalliconFileName);
            copy($largeIcon, $largeiconFileName);

            // Save some data
            $data = [
                '{patch}' => $Patch,
                '{name}' => $name,
                '{expansion}' => $expansion,
                '{emote}' => $emote,
                '{impression}' => str_replace(array("\r\n", "\r", "\n"), "<br>", $item['Impression']),
                '{description}' => str_replace(array("\r\n", "\r", "\n"), "<br>", $item['Description']),
                '{number}' => $number,
                '{time}' => ($item['MinTime'] > 0) ? " $MinTime ~ $MaxTime" : '',
                '{vista}' => $Vista,
                '{x}' => round($LocX, 1),
                '{y}' => round($LocY, 1)
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // (optional) finish progress bar
        $this->io->progressFinish();

        // save
        $this->io->text('Saving data ...');
        $this->save("SightseeingLogs.txt", 999999);
    }
}
