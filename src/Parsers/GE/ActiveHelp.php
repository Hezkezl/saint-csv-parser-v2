<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:ActiveHelp
 */
class ActiveHelp implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{output}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $HowToCsv = $this->csv('HowTo');
        $HowToCategoryCsv = $this->csv('HowToCategory');
        $HowToPageCsv = $this->csv('HowToPage');

        $this->PatchCheck($Patch, "HowTo", $HowToCsv);
        $PatchNumber = $this->getPatch("HowTo");

        $this->io->progressStart($HowToCsv->total);
        $OutputArray = [];
        $ReplaceOld = array("<If(GreaterThan(PlayerParameter(75),0))>", "<Else/>", "</If>", "<CommandIcon(10)/>", "<Gui(14)/>", "<Gui(15)/>", "<Gui(11)/>", "<Gui(8)/>", "<Gui(9)/>", "<Gui(10)/>", "<CommandIcon(19)/>", "<Gui(54)/>", "<Gui(55)/>", "<CommandIcon(8)/>", "<CommandIcon(13)/>", "<CommandIcon(17)/>", "<CommandIcon(14)/>", " <CommandIcon(15)/>", "<CommandIcon(9)/>", "<CommandIcon(11)/>", "<CommandIcon(12)/>", "<Gui(79)/>", "<Gui(81)/>", "<Gui(80)/>", "<Gui(78)/>", "<Switch(TopLevelParameter(220))><Case(1)>Sunday</Case><Case(2)>Monday</Case><Case(3)>Tuesday</Case><Case(4)>Wednesday</Case><Case(5)>Thursday</Case><Case(6)>Friday</Case><Case(7)>Saturday</Case></Switch> at <If(TopLevelParameter(218))><Switch(TopLevelParameter(218))><Case(1)>1</Case><Case(2)>2</Case><Case(3)>3</Case><Case(4)>4</Case><Case(5)>5</Case><Case(6)>6</Case><Case(7)>7</Case><Case(8)>8</Case><Case(9)>9</Case><Case(10)>10</Case><Case(11)>11</Case><Case(12)>12</Case><Case(13)>1</Case><Case(14)>2</Case><Case(15)>3</Case><Case(16)>4</Case><Case(17)>5</Case><Case(18)>6</Case><Case(19)>7</Case><Case(20)>8</Case><Case(21)>9</Case><Case(22)>10</Case><Case(23)>11</Case></Switch>", "/12:00 ", "<Time(1285056000)/>", "<If(LessThan(TopLevelParameter(218),12))>", "a.m./p.m. (Earth time)", "<ResetTime>01</ResetTime><If(TopLevelParameter(218))><Switch(TopLevelParameter(218))><Case(1)>1</Case><Case(2)>2</Case><Case(3)>3</Case><Case(4)>4</Case><Case(5)>5</Case><Case(6)>6</Case><Case(7)>7</Case><Case(8)>8</Case><Case(9)>9</Case><Case(10)>10</Case><Case(11)>11</Case><Case(12)>12</Case><Case(13)>1</Case><Case(14)>2</Case><Case(15)>3</Case><Case(16)>4</Case><Case(17)>5</Case><Case(18)>6</Case><Case(19)>7</Case><Case(20)>8</Case><Case(21)>9</Case><Case(22)>10</Case><Case(23)>11</Case></Switch>/12 <If(GreaterThanOrEqualTo(TopLevelParameter(218),12))>p.m./a.m. (Earth time)", "<Time(1415350800)/><If(TopLevelParameter(218))><Switch(TopLevelParameter(218))><Case(1)>1</Case><Case(2)>2</Case><Case(3)>3</Case><Case(4)>4</Case><Case(5)>5</Case><Case(6)>6</Case><Case(7)>7</Case><Case(8)>8</Case><Case(9)>9</Case><Case(10)>10</Case><Case(11)>11</Case><Case(12)>12</Case><Case(13)>1</Case><Case(14)>2</Case><Case(15)>3</Case><Case(16)>4</Case><Case(17)>5</Case><Case(18)>6</Case><Case(19)>7</Case><Case(20)>8</Case><Case(21)>9</Case><Case(22)>10</Case><Case(23)>11</Case></Switch><If(GreaterThanOrEqualTo(TopLevelParameter(218),12))>p.m./a.m.", "<ResetTime>1203</ResetTime>/12 <If(GreaterThanOrEqualTo(TopLevelParameter(218),12))>p.m./a.m", "<ResetTime>1206</ResetTime>/12 <If(GreaterThanOrEqualTo(TopLevelParameter(218),12))>p.m./a.m.");
        $ReplaceNew = array("", "/", "", "the Menu button", " RT/R2 ", " LT/L2 ", " Y / △ ", " A / × ", " B / ○ ", " X / □ ", " Back / Pad ", "{{TranslateGreen}}", "{{TranslateRed}}", " A / × ", " RB / R1 ", " RS / R3 ", " LT / L2", " RT / R2 ", " B / ○ ", " Y / △ ", " LB / L1 ", "[[File:Player39_Icon.png|link=]]", "[[File:Player41_Icon.png|link=]]", "[[File:Player40_Icon.png|link=]]", "[[File:Player37_Icon.png|link=]]", "", "", "(Reset Time)", "", "", "(Reset Time)", "(Reset Time)", "(Reset Time)", "(Reset Time)");
        // loop through data
        foreach ($HowToCsv->data as $id => $HowTo) {
            $this->io->progressAdvance();
            if (empty($HowTo['Name'])) continue;
            $Patch = $PatchNumber[$id];

            $Title = $HowTo['Name'];
            $Category = $HowToCategoryCsv->at($HowTo['Category'])['Category'];
            $Order = $HowTo['Sort'];
            //PC Images and Strings
            $PCOutputArray = [];
            foreach(range(0,4) as $i){
                $PageID = $HowTo["HowToPagePC[$i]"];
                $Image = "";
                if ($HowToPageCsv->at($PageID)['Image'] != "0"){
                    $IconoutputDirectory = $this->getOutputFolder() . "/$PatchID/ActiveHelpIcons";
                    if (!is_dir($IconoutputDirectory)) {
                        mkdir($IconoutputDirectory, 0777, true);
                    }
                    $ImageID = sprintf("%06d", $HowToPageCsv->at($PageID)['Image']);
                    // build icon input folder paths
                    $ImageIcon = $this->getInputFolder() .'/icon/'. $this->iconizeEN($ImageID);
                    // if icon doesn't exist (not in the input folder icon list), then skip
                    if (!file_exists($ImageIcon)) continue;
                    $ImageIconFileName = "$IconoutputDirectory/$ImageID.png";
                    // copy the input icon to the output filename
                    copy($ImageIcon, $ImageIconFileName);
                    $Image = "| PC_String_$i Image = $ImageID\n";
                }
                //this string has 3 strings but they are all duplicates for some reason?
                $String = "";
                if (!empty($HowToPageCsv->at($PageID)["Text[0]"])){
                    $String = "| PC_String_$i = ". str_replace($ReplaceOld, $ReplaceNew, $HowToPageCsv->at($PageID)["Text[0]"])."\n";
                }
                $PCOutputArray[] = "$Image$String";
            }
            $PCOutput = implode("", $PCOutputArray);
            
            //Controller Images and Strings
            $ControllerOutputArray = [];
            foreach(range(0,4) as $i){
                $PageID = $HowTo["HowToPageController[$i]"];
                $Image = "";
                if ($HowToPageCsv->at($PageID)['Image'] != "0"){
                    $IconoutputDirectory = $this->getOutputFolder() . "/$PatchID/ActiveHelpIcons";
                    if (!is_dir($IconoutputDirectory)) {
                        mkdir($IconoutputDirectory, 0777, true);
                    }
                    $ImageID = sprintf("%06d", $HowToPageCsv->at($PageID)['Image']);
                    // build icon input folder paths
                    $ImageIcon = $this->getInputFolder() .'/icon/'. $this->iconizeEN($ImageID);
                    // if icon doesn't exist (not in the input folder icon list), then skip
                    if (!file_exists($ImageIcon)) continue;
                    $ImageIconFileName = "$IconoutputDirectory/$ImageID.png";
                    // copy the input icon to the output filename
                    copy($ImageIcon, $ImageIconFileName);
                    $Image = "| Controller_String_$i Image = $ImageID\n";
                }
                //this string has 3 strings but they are all duplicates for some reason?
                $String = "";
                if (!empty($HowToPageCsv->at($PageID)["Text[0]"])){
                    $String = "| Controller_String_$i = ". str_replace($ReplaceOld, $ReplaceNew, $HowToPageCsv->at($PageID)["Text[0]"])."\n";
                }
                $ControllerOutputArray[] = "$Image$String";
            }
            $ControllerOutput = implode("", $ControllerOutputArray);

            $OutputString = "{{-start-}}\n";
            $OutputString .= "'''Active_Help/$Title'''\n";
            $OutputString .= "{{Active Help\n";
            $OutputString .= "| Patch = $Patch\n";
            $OutputString .= "| Title = $Title\n";
            $OutputString .= "| Category = $Category\n";
            $OutputString .= "| Order = $Order\n";
            $OutputString .= "$PCOutput";
            $OutputString .= "$ControllerOutput";
            $OutputString .= "}}\n";
            $OutputString .= "{{-stop-}}\n";
            $OutputArray[] = $OutputString;

        }

        $output = implode("\n", $OutputArray);


        // Save some data
        $data = [
            '{output}' => $output,
        ];

        // format using Gamer Escape formatter and add to data array
        // need to look into using item-specific regex, if required.
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("ActiveHelp.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}

/*
11th April 2021 - Creation
*/
