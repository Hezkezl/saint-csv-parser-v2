<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:Description
 */
class Description implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{output}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $DescriptionCsv = $this->csv('Description');
        $DescriptionPageCsv = $this->csv('DescriptionPage');
        $DescriptionSectionCsv = $this->csv('DescriptionSection');
        $DescriptionStringCsv = $this->csv('DescriptionString');
        $QuestCsv = $this->csv('Quest');

        $this->PatchCheck($Patch, "Description", $DescriptionCsv);
        $PatchNumber = $this->getPatch("Description");

        $this->io->progressStart($DescriptionCsv->total);
        $OutputArray = [];// loop through data

        $TitleFormatStart = "{| border=\"0\" width=\"100%\" cellpadding=\"2\" cellspacing=\"0\" style=\"font-size:200%;color:white;\"\n";
        $SubTitleFormatStart = "{| border=\"0\" width=\"100%\" cellpadding=\"2\" cellspacing=\"0\" style=\"font-size:110%;color:white;\"\n";
        $FormatEnd = "\n|}\n";
        foreach ($DescriptionCsv->data as $id => $Description) {
            $PageArray = [];
            $this->io->progressAdvance();
            if (empty($Description['Text[Long]'])) continue;
            $Patch = $PatchNumber[$id];
            
            $Title = $Description['Text[Long]'];
            $RequiredQuest = "";
            if ($Description['Quest'] != "0") {
                $RequiredQuest = $QuestCsv->at($Description['Quest'])['Name'];
            }
            $SectionLink = $Description['Section'];

            $PageArray[] = $Title."\n";
            $Patch = $Patch."\n";
            $RequiredQuest = "Quest = ".$RequiredQuest."\n";
            $PageArray[] = $Patch;
            $PageArray[] = $RequiredQuest;

            $PageArray[] = "{| class=\"itembox shadowed\" style=\"color:white; width:100%; cellpadding=0; cellspacing=1;\" border={{{border|0}}}\n|-\n|{{#tag:tabber|";
            foreach(range(0,5) as $b) {
                $SubSection = $SectionLink. ".". $b;
                if (empty($DescriptionStringCsv->at($DescriptionSectionCsv->at($SubSection)['String'])['Text'])) continue;
                $Section = $DescriptionStringCsv->at($DescriptionSectionCsv->at($SubSection)['String'])['Text'];
                $PageArray[] = "$Section=\n{{V-tabber|";
                $PageArray[] = "<tabber>";
                $PageLink = $DescriptionSectionCsv->at($SubSection)['Page'];
                foreach(range(0,15) as $c) {
                    $SubPage = $PageLink. ".". $c;
                    if (empty($DescriptionStringCsv->at($DescriptionPageCsv->at($SubPage)["Text[0]"])['Text'])) break;
                    foreach(range(0,10) as $d) {
                        if (empty($DescriptionStringCsv->at($DescriptionPageCsv->at($SubPage)["Text[$d]"])['Text'])) continue;
                        $string = $DescriptionStringCsv->at($DescriptionPageCsv->at($SubPage)["Text[$d]"])['Text'];
                        if ($d === 0){
                            $PageArray[] = $string." = ";
                            $PageArray[] = "$SubTitleFormatStart{{PGH|$string}}$FormatEnd";
                        } else {
                            $PageArray[] = $string;
                        }
                        if (!empty($DescriptionPageCsv->at($SubPage)["Image[$d]"])){
                            $PageArray[] = "\n[[File:".sprintf("%06d", $DescriptionPageCsv->at($SubPage)["Image[$d]"]).".png]]";
                            
                            $IconoutputDirectory = $this->getOutputFolder() . "/$PatchID/DescriptionImages";
                            if (!is_dir($IconoutputDirectory)) {
                                mkdir($IconoutputDirectory, 0777, true);
                            }
                            $ImageID = sprintf("%06d", $DescriptionPageCsv->at($SubPage)["Image[$d]"]);
                            // build icon input folder paths
                            $ImageIcon = $this->getInputFolder() .'/icon/'. $this->iconize($ImageID, true);
                            // if icon doesn't exist (not in the input folder icon list), then skip
                            if (!file_exists($ImageIcon)) continue;
                            $ImageIconFileName = "$IconoutputDirectory/$ImageID.png";
                            // copy the input icon to the output filename
                            copy($ImageIcon, $ImageIconFileName);
                        }
                    }
                    $PageArray[] = "|-|";
                }
                $PageArray[] = "</tabber>\n}}\n{{!}}-{{!}}\n";
            }

            $OutputString = implode("\n", $PageArray)."\n}}\n|}";
            $OutputArray[] = "{{-start-}}\n$OutputString\n{{-stop-}}\n\n";

        }

        $output = implode("\n", $OutputArray)."\n}}";


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
        $info = $this->save("Description.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}

/*
11th April 2021 - Creation
*/