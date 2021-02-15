<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Weather
 */
class Weather implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{weather}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $WeatherCsv = $this->csv("Weather");

        // (optional) start a progress bar
        $this->io->progressStart($WeatherCsv->total);
        $weatherNames = [];
        // loop through data
        foreach ($WeatherCsv->data as $id => $Weather) {
            // skip blank icons
            if (empty($Weather['Icon'])) continue;

            $this->io->progressAdvance();

            $Icon = $Weather['Icon'];
            $Name = $Weather['Name'];
            $Description = $Weather['Description'];
            if (in_array($Name, $weatherNames)) continue;
            // icon copying code
            $weatherNames[] = $Name;
            if (!empty($Weather['Icon'])) {
                if (!file_exists($this->getOutputFolder() ."/$PatchID/WeatherIcons/{$Name} icon.png")) {
                    // ensure output directory exists
                    $IconOutputDirectory = $this->getOutputFolder() ."/$PatchID/WeatherIcons";
                    if (!is_dir($IconOutputDirectory)) {
                        mkdir($IconOutputDirectory, 0777, true);
                    }

                    // build icon input folder paths
                    $IconFileName = $this->getInputFolder() .'/icon/'. $this->iconize($Icon);
                    $IconOutputFileName = "{$IconOutputDirectory}/{$Name} icon.png";

                    // copy the input icon to the output filename
                    copy($IconFileName, $IconOutputFileName);
                }
            }


            // Save some data
            $data = [
                '{weather}' => "|-\n|$Name\n|[[File:$Name icon.png|link=]]\n|$Description",
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        };

        // save our data to the filename: GeMountWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Weather.txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
