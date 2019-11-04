<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Mounts
 */
class Mounts implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Top}{{ARR Infobox Mount
| Index = {Index}
| Name = {Name} (Mount)
| Description = {Description}
| Acquisition = 
| Quote = {Quote}{Airborne}
| Required Item = {Action}
| Notes =
| Lore =
| Etymology ={Flying}
| Music = {Music}
}}{Bottom}";
    public function parse()
    {
        $Patch = '5.1';
        // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
        // manually, set to false
        $Bot = "true";

        // grab CSV files we want to use
        $MountCsv = $this->csv('Mount');
        $MountActionCsv = $this->csv('MountAction');
        $MountTransientCsv = $this->csv('MountTransient');
        $ActionCsv = $this->csv('Action');
        $BGMCsv = $this->csv('BGM');

        // (optional) start a progress bar
        $this->io->progressStart($MountCsv->total);

        // loop through data
        foreach ($MountCsv->data as $id => $Mount) {
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($Mount['Singular']) || empty($Mount['Icon'])) {
                continue;
            }

            // code starts here
            $Name = ucwords(strtolower(str_replace(" & ", " and ", $Mount['Singular']))); // replace the & character with 'and' in names
            $Description = strip_tags($MountTransientCsv->at($Mount['id'])['Description{Enhanced}']); // strip tags from description
            $Description = str_replace("\n", "", $Description); // delete any line breaks in description
            $Quote = str_replace("\n", "<br>", ($MountTransientCsv->at($Mount['id'])['Tooltip'])); // replace line breaks in quote

            // Using the value at MountAction inside Mount.csv, match that up with the column "Action[0]" in the file
            // MountAction.csv, and take THAT value and match it with the "Name" column from Action.csv
            $Action1 = $ActionCsv->at($MountActionCsv->at($Mount['MountAction'])["Action[0]"])['Name'];
            $Action2 = $ActionCsv->at($MountActionCsv->at($Mount['MountAction'])["Action[1]"])['Name'];
            if ($MountActionCsv->at($Mount['MountAction'])["Action[1]"] > 0) {
                $Action = "\n| Actions = $Action1, $Action2";
            } elseif ($MountActionCsv->at($Mount['MountAction'])["Action[0]"] > 0){
                $Action = "\n| Actions = $Action1";
            } else {
                $Action = "\n| Actions =";
            };

            // Icon copying
            $SmallIcon = $Mount["Icon"];
            $Icon2 = substr($SmallIcon, -3);
            $LargeIcon = str_pad($Icon2, "6", "068", STR_PAD_LEFT);
            $LargeIcon2 = str_pad($Icon2, "6", "077", STR_PAD_LEFT);

            // ensure output directory exists
            $IconoutputDirectory = $this->getOutputFolder() . '/MountIcons';
            // if it doesn't exist, make it
            if (!is_dir($IconoutputDirectory)) {
                mkdir($IconoutputDirectory, 0777, true);
            }

            // build icon input folder paths
            $LargeIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($LargeIcon);
            $LargeIconPath2 = $this->getInputFolder() .'/icon/'. $this->iconize($LargeIcon2);
            $SmallIconPath = $this->getInputFolder() .'/icon/'. $this->iconize($Mount["Icon"]);

            // give correct file names to icons for output
            $LargeIconFileName = "{$IconoutputDirectory}/$Name (Mount) Patch.png";
            $SmallIconFileName = "{$IconoutputDirectory}/$Name (Mount) Icon.png";
            // actually copy the icons
            copy($SmallIconPath, $SmallIconFileName);
            if (file_exists($LargeIconPath)) {
                copy($LargeIconPath, $LargeIconFileName);
            } else {
                copy($LargeIconPath2, $LargeIconFileName);
            };

            // change the top and bottom code depending on if I want to bot the pages up or not. Places Patch on subpage
            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name (Mount)/Patch'''\n$Patch\n{{-stop-}}{{-start-}}\n'''$Name (Mount)'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name (Mount)\Patch?action=edit\n$Patch\nhttp://ffxiv.gamerescape.com/wiki/$Name (Mount)?action=edit\n";
                $Bottom = "";
            };

            // Save some data
            $data = [
                '{Top}' => $Top,
                '{Name}' => $Name,
                '{Patch}' => $Patch,
                '{Index}' => $Mount['id'],
                '{Description}' => $Description,
                '{Quote}' => $Quote,
                '{Airborne}' => ($Mount['IsAirborne'] == "False") ? "\n| Movement = Airborne" : "\n| Movement = Terrestrial",
                '{Action}' => $Action,
                '{Flying}' => ($Mount['IsFlying'] > 0) ? "\n| Flying = Yes" : "\n| Flying = No",
                '{Music}' => str_replace("music/ffxiv/", "", $BGMCsv->at($Mount['RideBGM'])['File']),
                '{Bottom}' => $Bottom,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        };

        // save our data to the filename: GeMountWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeMountWiki.txt', 2000);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
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
