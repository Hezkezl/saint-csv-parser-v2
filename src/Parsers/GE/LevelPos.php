<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * php bin/console app:parse:csv GE:LevelPos
 */
class LevelPos implements ParseInterface
{
    use CsvParseTrait;


    // the wiki output format / template we shall use
    const WIKI_FORMAT = "
        {{-start-}}
        '''{name}/Map/{objectid}'''
        {{NPCMap
          | base = {mapcode} - {map}{sub}.png
          | float_link = {name}
          | float_caption = {name} (x:{x2}, y:{y2})
          | x = {pixX}
          | y = {pixY}
          | zone = {map}
          | level_id = {id}
          | npc_id = {objectid}
        }}
        {{-stop-}}";

    public function parse()
    {

        $levelcsv = $this->csv('Level');
        $mapcsv = $this->csv('Map');
        $residentcsv = $this->csv('ENpcResident');
        $TerritoryTypeCsv = $this->csv('TerritoryType');
        $PlaceNameCsv = $this->csv('PlaceName');

        // console writer
        $console = new ConsoleOutput();


        // download our CSV files
        $console->writeln(" Loading CSVs");


        $console->writeln(" Processing Level at ENpc Location data");

        // switch to a section so we can overwrite
        $console = $console->section();

        // loop through our sequences
        foreach($levelcsv->data as $id => $level) {
            $id = $level['id'];



            if ($level["Type"] = 8) {
                $name = ucwords(strtolower($residentcsv->at($level["Object"])['Singular']));
                $objectid = $level["Object"];
                if (empty($name)) {
                    continue;
                }

                $NpcLevelX = $level["X"];
                $NpcLevelY = $level["Y"];
                $NpcLevelZ = $level["Z"];
                $NpcLevelMap = $level["Map"];

                $scale = $mapcsv->at($level["Map"])['SizeFactor'];
                //$offsetx = $mapcsv["Offset{X}"];
                $c = $scale / 100.0;

                $offsetx = $mapcsv->at($level["Map"])['Offset{X}'];
                    $offsetValueX = ($NpcLevelX + $offsetx) * $c;
                    $NpcLevelX2 = ((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1);
                    $NpcPixelX = (($NpcLevelX2 - 1) * 50 * $c);

                $offsetz = $mapcsv->at($level["Map"])['Offset{Y}'];
                    $offsetValueZ = ($NpcLevelZ + $offsetz) * $c;
                    $NpcLevelZ2 = ((41.0 / $c) * (($offsetValueZ + 1024.0) / 2048.0) +1);
                    $NpcPixelZ = (($NpcLevelZ2 - 1) * 50 * $c);
                    //$npcpixelY2 = $NpcPixelZ /3.9

            $NpcMap1 = $mapcsv->at($level["Map"])['PlaceName'];
                $NpcPlaceName = $PlaceNameCsv->at($NpcMap1)['Name'];

            $NpcMapCodeName = $mapcsv->at($level["Map"])['Id'];

            $NpcMap2 = $mapcsv->at($level["Map"])['PlaceName{Sub}'];
                $NpcPlaceNameSub = $PlaceNameCsv->at($NpcMap2)['Name'];
            }


            if ($mapcsv->at($level["Map"])["PlaceName{Sub}"] > 0) {
            $sub = " - ".$PlaceNameCsv->at($mapcsv->at($level["Map"])["PlaceName{Sub}"])['Name']."";
            } elseif ($mapcsv->at($level["Map"])["PlaceName"] > 0) {
            $sub = "";
            }

            // build our data array using the GE Formatter
            $data = GeFormatter::format(self::WIKI_FORMAT, [
                '{x}'  => $NpcLevelX,
                '{id}' => $id,
                '{y}'    => $NpcLevelY,
                '{m}' => $NpcLevelMap,
                '{scale}' => $scale,
                '{x2}'  => round($NpcLevelX2, 1),
                '{y2}'  => round($NpcLevelZ2, 1),
                '{pixX}' => round($NpcPixelX, 2) /3.9,
                '{pixY}' => round($NpcPixelZ, 2) /3.9,
                //'{y3}'  => $NpcLevelY3,
                '{name}' => $name,
                '{npcname}' => $name,
                '{objectid}' => $objectid,
                '{map}' => $NpcPlaceName,
                '{sub}' => $sub,
                '{mapcode}' => substr($NpcMapCodeName, 0, 4),
            ]);

            $this->data[] = $data;

            $console->overwrite(" > Completed Sequence: {$id} --> }");
        }

        // save
        $console->writeln(" Saving... ");
        $this->save("NpcLevelPos.txt", 999999);
    }
}
