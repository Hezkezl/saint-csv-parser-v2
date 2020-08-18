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
| Required Item = {RequiredItem}{Action}
| Notes =
| Lore =
| Etymology ={Flying}
| Music = {Music}
}}{Bottom}";
    public function parse()
    {
        // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
        // manually, set to false
        $Bot = "true";
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $ItemCsv = $this->csv("Item");
        $ItemActionCsv = $this->csv("ItemAction");
        $MountCsv = $this->csv("Mount");
        $MountActionCsv = $this->csv("MountAction");
        $MountTransientCsv = $this->csv("MountTransient");
        $ActionCsv = $this->csv("Action");
        $BGMCsv = $this->csv("BGM");

        // (optional) start a progress bar
        $this->io->progressStart($ItemCsv->total);

        foreach ($ItemCsv->data as $id => $Item) {
            $this->io->progressAdvance();

            if ($ItemActionCsv->at($Item['ItemAction'])['Type'] !== "1322") continue;

            // code starts here
            $RequiredItemName = $Item['Name'];
            $MountID = $ItemActionCsv->at($Item['ItemAction'])['Data[0]'];
            $MountName = $MountCsv->at($MountID)['Singular'];
            //set up the base mount we want to the sheet
            $Mount = $MountCsv->at($MountID);

            // change the name of the mount based off of the required item (for better capitalization on a few mounts)
            $MountItemRemove = array(" Bell", " Bugle", " Clarion", " Conch", " Core", " Crystal", " Dizi", " Fife", " Flute",
                " Gear", " Horn", " Identification Key", " Ignition Key", " Master Key", " Neurolink Key", " Medal", " Key",
                " Pendant (Left)", " Pendant (Right)", " Pipe", " Prism", " Resonator", " Seeds", " Seed", " Title", " Trumpet",
                " Warhorn", " Whistle");
            switch ($MountName) {
                case "company chocobo":
                    $Name = "Company Chocobo";
                    break;
                case "ahriman":
                    $Name = "Ahriman";
                    break;
                case "cavalry drake":
                    $Name = "Cavalry Drake";
                    break;
                case "magitek armor":
                    $Name = "Magitek Armor";
                    break;
                case "gilded magitek armor":
                    $Name = "Gilded Magitek Armor";
                    break;
                case "cavalry elbst":
                    $Name = "Cavalry Elbst";
                    break;
                case "war panther":
                    $Name = "War Panther";
                    break;
                case "Gloria-class airship":
                    $Name = "Gloria-class Airship";
                    break;
                case "original fat chocobo":
                    $Name = "Original Fat Chocobo";
                    break;
                case "black pegasus":
                    $Name = "Black Pegasus";
                    break;
                case "broken heart (right)":
                    $Name = "Broken Heart (Right)";
                    break;
                case "broken heart (left)":
                    $Name = "Broken Heart (Left)";
                    break;
                case "Indigo whale":
                    $Name = "Indigo Whale";
                    break;
                case "kamuy of the Nine Tails":
                    $Name = "Kamuy of the Nine Tails";
                    break;
                case "Circus ahriman":
                    $Name = "Circus Ahriman";
                    break;
                case "Great Vessel of Ronka":
                    $Name = "Great Vessel of Ronka";
                    break;
                default:
                    $Name = str_replace($MountItemRemove, null, $RequiredItemName);
                    break;
            }

            // clean up Description and Quote
            $Description = strip_tags($MountTransientCsv->at($Mount['id'])['Description{Enhanced}']); // strip tags from description
            $Description = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), " ", $Description); // replace line breaks with a space in Description
            $Description = preg_replace("/  +/", " ", $Description); // replace two spaces in Description with single space
            $Quote = str_replace(array("\n\r", "\r", "\n", "\t", "\0", "\x0b"), " ", ($MountTransientCsv->at($Mount['id'])['Tooltip'])); // replace line breaks with a space in Quote
            $Quote = preg_replace("/  +/", " ", $Quote); // replace two or more spaces in Quote with a single space
            $Quote = preg_replace("/ \- (.*)/", "<br>- [[$1]]", $Quote); // add line break before Quote giver's name and place name in [[Wiki Brackets]]

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
            $IconoutputDirectory = $this->getOutputFolder() . "/$CurrentPatchOutput/MountIcons";
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
                '{Airborne}' => ($Mount['IsAirborne'] == "True") ? "\n| Movement = Airborne" : "\n| Movement = Terrestrial",
                '{RequiredItem}' => $RequiredItemName,
                '{Action}' => $Action,
                '{Flying}' => ($Mount['IsFlying'] > 0) ? "\n| Flying = Yes" : "\n| Flying = No",
                '{Music}' => str_replace("music/ffxiv/", null, $BGMCsv->at($Mount['RideBGM'])['File']),
                '{Bottom}' => $Bottom,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        };

        // save our data to the filename: GeMountWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("$CurrentPatchOutput/Mounts - ". $Patch .".txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
