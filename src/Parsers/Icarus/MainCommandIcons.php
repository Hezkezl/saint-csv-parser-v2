<?php


namespace App\Parsers\GE;
use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
//use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:MainCommandIcons
 */
class MainCommandIcons implements ParseInterface



{

    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{{Micon|Icon={icon}|Name={Name}|Description={Description}|Category={Category}|Patch={Patch}}}';


    public function parse()
    {
      include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $MainCommandCsv = $this->csv("MainCommand");
        $MainCommandCategoryCsv = $this->csv("MainCommandCategory");


    // (optional) start a progress bar
        $this->io->progressStart($MainCommandCsv->total);
        
        $this->PatchCheck($Patch, "MainCommand", $MainCommandCsv);
        $PatchNumber = $this->getPatch("MainCommand");

        // loop through data
        foreach ($MainCommandCsv->data as $id => $MainCommand) {
            $this->io->progressAdvance();
            $Icon = sprintf("%06d", $MainCommand['Icon']);

            // skip ones without an icon
            if (empty($MainCommand['Icon'])) {
                continue;
            }
            $Patch = $PatchNumber[$id];

            $Category = $MainCommandCategoryCsv->at($MainCommand['MainCommandCategory'])['Name'];
            $Name = $MainCommand['Name'];
            $Description = $MainCommand['Description'];

            
            $IconoutputDirectory = $this->getOutputFolder() . "/$PatchID/MainCommandIcons";
            if (!is_dir($IconoutputDirectory)) {
                mkdir($IconoutputDirectory, 0777, true);
            }
            $ImageID = $Icon;
            // build icon input folder paths
            $ImageIcon = $this->getInputFolder() .'/icon/'. $this->iconizeHD($ImageID);
            // if icon doesn't exist (not in the input folder icon list), then skip
            if (!file_exists($ImageIcon)) continue;
            $ImageIconFileName = "$IconoutputDirectory/$ImageID.png";
            // copy the input icon to the output filename
            copy($ImageIcon, $ImageIconFileName);


            // Save some data
            $data = [
                '{Category}' => $Category,
                '{Name}' => $Name,
                '{Description}' => $Description,
                '{Icon}' => $Icon,
                '{Patch}' => $Patch,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ... map number');
        $info = $this->save("MainCommandIcons.txt", 999999);
        //$info = $this->save('pvp.html', 999999);
        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info

        );

    }

}