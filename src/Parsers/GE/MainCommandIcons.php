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
    const WIKI_FORMAT = '{{Micon|Icon=MainIcon{icon}|Name={Name}|Description={Description}|Category={Category}|Patch={Patch}}}';


    public function parse()
    {
      include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $MainCommandCsv = $this->csv("MainCommand");
        $MainCommandCategoryCsv = $this->csv("MainCommandCategory");


    // (optional) start a progress bar
        $this->io->progressStart($MainCommandCsv->total);

        // loop through data
        foreach ($MainCommandCsv->data as $id => $MainCommand) {
            $this->io->progressAdvance();
            $Icon = $MainCommand['Icon'];

            // skip ones without an icon
            if (empty($Icon)) {
                continue;
            }

            $Category = $MainCommandCategoryCsv->at($MainCommand['MainCommandCategory'])['Name'];
            $Name = $MainCommand['Name'];
            $Description = $MainCommand['Description'];


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
        $info = $this->save("$CurrentPatchOutput/MainCommandIcons - ". $Patch .".txt", 999999);
        //$info = $this->save('pvp.html', 999999);
        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info

        );

    }

}
