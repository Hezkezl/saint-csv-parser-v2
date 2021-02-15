<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:MYCItems
 */
class MYCItems implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
'''{Name} (Lost Action)'''
{{Infobox Lost Action
|Name =  {Name} (Lost Action)
|Description = {Description}
|Type = {Type}
|Weight = {Weight}
|Icon = 0{Icon}.png
|Subheading = {SubHeading}
|Affinity = {Affinity}

|Range = {Range}
|Radius = {Radius}
|Duration = 
|Recast = {Recast}
|Cast = {CastTime}
|Uses = {Max}
|Target = {CastType}

}}
{{-stop-}}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $ActionCsv = $this->csv('Action');
        $ClassJobCategoryCsv = $this->csv('ClassJobCategory');
        $ActionCategoryCsv = $this->csv('ActionCategory');
        $ActionTransientCsv = $this->csv('ActionTransient');
        $MYCTemporaryItemCsv = $this->csv('MYCTemporaryItem');
        $MYCTemporaryItemUICategoryCsv = $this->csv('MYCTemporaryItemUICategory');
        // (optional) start a progress bar
        $this->io->progressStart($MYCTemporaryItemCsv->total);

        // loop through data
            foreach ($MYCTemporaryItemCsv->data as $id => $MYCTemporaryItemData) {
                if (empty($ActionCsv->at($MYCTemporaryItemData['Action'])['Name'])) continue;
            // Save some data
            $Description = $ActionTransientCsv->at($MYCTemporaryItemData['Action'])['Description'];

            if ($ActionCsv->at($MYCTemporaryItemData['Action'])['Cast<100ms>'] == "0") {
                $CastTime = "Instant";
              } elseif ($ActionCsv->at($MYCTemporaryItemData['Action'])['Cast<100ms>'] !== "0") {
                $CastTimeRaw = $ActionCsv->at($MYCTemporaryItemData['Action'])['Cast<100ms>'];
                  //$CastTimeMins = floor(($CastTimeRaw / 60) % 60);
                  //$CastSeconds = $CastTimeRaw % 60;
                  //$CastString = " ". $CastTimeMins ."m". $CastSeconds ."s";
                  //$CastFormat1 = str_replace(" 0m", " ", $CastString);
                  //$CastTime = str_replace("m0s", "m", $CastFormat1);
                  $CastTime = "". ($CastTimeRaw / 10) ."";
            }
  
              if ($ActionCsv->at($MYCTemporaryItemData['Action'])['Recast<100ms>'] == "0") {
                $Recast = "Instant";
              } elseif ($ActionCsv->at($MYCTemporaryItemData['Action'])['Recast<100ms>'] !== "0") {
                $ReCastTimeRaw = $ActionCsv->at($MYCTemporaryItemData['Action'])['Recast<100ms>'];
                  //$ReCastTimeMins = floor(($ReCastTimeRaw / 60) % 60);
                  //$ReCastSeconds = $ReCastTimeRaw % 60;
                  //$ReCastString = " ". $ReCastTimeMins ."m". $ReCastSeconds ."s";
                  //$ReCastFormat1 = str_replace(" 0m", " ", $ReCastString);
                  //$Recast = str_replace("m0s", "m", $ReCastFormat1);
                  $Recast = "". ($ReCastTimeRaw / 10) ."";
                  //$Recast = $ReCastTimeRaw;
              }
              $Name = $ActionCsv->at($MYCTemporaryItemData['Action'])['Name'];
              switch ($ActionCsv->at($MYCTemporaryItemData['Action'])['CastType']) {
                case 1:
                    $CastType = "single";
                break;
                case 2:
                    $CastType = "aoe";
                break;
                case 3:
                    $CastType = "cone";
                break;
                case 4:
                    $CastType = "line";
                break;
                default:
                    $CastType = "aoe";
                break;
              }

                // ensure output directory exists
                $IconoutputDirectory = $this->getOutputFolder() . "/$PatchID/LostActionsIcons";
                // if it doesn't exist, make it
                if (!is_dir($IconoutputDirectory)) {
                    mkdir($IconoutputDirectory, 0777, true);
                }

                // build icon input folder paths
                $SmallIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($ActionCsv->at($MYCTemporaryItemData['Action'])['Icon']);

                // give correct file names to icons for output
                $SmallIconFileName = "{$IconoutputDirectory}/$Name (Lost Action) Icon.png";
                // actually copy the icons
                copy($SmallIconPath, $SmallIconFileName);
            $data = [
                '{Name}' => $ActionCsv->at($MYCTemporaryItemData['Action'])['Name'],
                '{Range}' => $ActionCsv->at($MYCTemporaryItemData['Action'])['Range'],
                '{Radius}' => $ActionCsv->at($MYCTemporaryItemData['Action'])['EffectRange'],
                '{Affinity}' => str_replace(" ", ", ", $ClassJobCategoryCsv->at($ActionCsv->at($MYCTemporaryItemData['Action'])['ClassJobCategory'])['Name']),
                '{Icon}' => $ActionCsv->at($MYCTemporaryItemData['Action'])['Icon'],
                '{SubHeading}' => $ActionCategoryCsv->at($ActionCsv->at($MYCTemporaryItemData['Action'])['ActionCategory'])['Name'],
                '{Weight}' => $MYCTemporaryItemData['Weight'],
                '{Max}' => $MYCTemporaryItemData['Max'],
                '{Description}' => $Description,
                '{Recast}' => $Recast,
                '{CastType}' => $CastType,
                '{CastTime}' => $CastTime,
                '{Type}' => $MYCTemporaryItemUICategoryCsv->at($MYCTemporaryItemData['Category'])['Name'],
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }
        

        // save our data to the filename: GeEventItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("MYCItems.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}