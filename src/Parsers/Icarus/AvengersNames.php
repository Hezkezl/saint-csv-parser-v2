<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:AvengersNames
 */
class AvengersNames implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{output}";
    public function parse()
    {
      include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use

        // loop through data
        //pcx64-w\Design\Image Resources\scaleform\SharedTextures\Appearance\Cap_America\128x256\HC_CaptainAmerica_Outfit01_V2.drm
        //pcx64-w\Design\Image Resources\scaleform\SharedTextures\Appearance\Cap_America\CaptainAmerica_Outfit01_V1.drm
        //pcx64-w\Design\Image Resources\scaleform\SharedTextures\Appearance\Cap_America\256x512\MP_CaptainAmerica_Outfit09_V1.drm
        // Save some data
        $outarray = [];
        foreach(range(0,8) as $range) {
            switch ($range) {
                case 0:
                    $Heroname1 = "Cap_America";
                    $Heroname2 = "CaptainAmerica";
                break;
                case 1:
                    $Heroname1 = "Hulk";
                    $Heroname2 = "Hulk";
                break;
                case 2:
                    $Heroname1 = "Iron_Man";
                    $Heroname2 = "IronMan";
                break;
                case 3:
                    $Heroname1 = "Kamala";
                    $Heroname2 = "Kamala";
                break;
                case 4:
                    $Heroname1 = "Kate_Bishop";
                    $Heroname2 = "Kate";
                break;
                case 5:
                    $Heroname1 = "Thor";
                    $Heroname2 = "Thor";
                break;
                case 6:
                    $Heroname1 = "Widow";
                    $Heroname2 = "BlackWidow";
                break;
                case 7:
                    $Heroname1 = "Black_Panther";
                    $Heroname2 = "BlackPanther";
                break;
                case 8:
                    $Heroname1 = "Hawkeye";
                    $Heroname2 = "Hawkeye";
                break;
            }
            foreach(range(0,20) as $a) {
                $paddinga = sprintf("%02d", $a);
                foreach(range(0,20) as $b) {
                    $dir1 = "pcx64-w\Design\Image Resources\scaleform\SharedTextures\Appearance\\$Heroname1\\128x256\\HC_";
                    $dir2 = "pcx64-w\Design\Image Resources\scaleform\SharedTextures\Appearance\\$Heroname1\\256x512\\MP_";
                    $dir3 = "pcx64-w\Design\Image Resources\scaleform\SharedTextures\Appearance\\$Heroname1\\";
                    $string = "". $Heroname2. "_Outfit";

                    $outstring = "". $dir1 ."". $string ."". $paddinga ."_V". $b .".drm\n". $dir2 ."". $string ."". $paddinga ."_V". $b .".drm\n". $dir3 ."". $string ."". $paddinga ."_V". $b .".drm\n";
                    $outarray[] = $outstring;
                    //var_dump($outstring);
                }
            }
        }
        $output = implode($outarray);
        $data = [
                '{output}' => $output,
        ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);

        // save our data to the filename: GeMountWiki.txt
        
        $this->io->text('Saving ...');
        //$info = $this->save('Achievement.txt', 20000);
        $info = $this->save("Avengers Work/AvengersNames.txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}