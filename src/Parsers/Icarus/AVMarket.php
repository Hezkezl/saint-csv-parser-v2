<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * php bin/console app:parse:csv GE:AVMarket
 */
class AVMarket implements ParseInterface
{
    use CsvParseTrait;


    // the wiki output format / template we shall use
    const WIKI_FORMAT = "";

    public function parse()
    {

        $MarketplaceJson = "https://avengers.square-enix-games.com/en-us/marketplace.json";
        $jdata = file_get_contents($MarketplaceJson);
        $decodeJdata = json_decode($jdata);
        
        //Featured
        //Outfits
        $outfitsData = $decodeJdata -> orderedItems;
        $outfitarray = [];
        foreach ($outfitsData as $outfitsDatas) {
            //$CharacterName = ucwords(str_replace("-", " ", $outfitsDatas -> characterSlug));
            if (empty($outfitsDatas -> outfitSlug)) continue;
            $title = ucwords(str_replace("-", " ", $outfitsDatas -> outfitSlug));
            //$title = ucwords(strtolower($outfitsDatas -> title));

            $outfitarray[] = "". $title ."";
        }
        //Emotes
        $EmotesData = $decodeJdata -> orderedItems;
        $emotesArray = [];
        foreach ($EmotesData as $EmotesDatas) {
            if (empty($outfitsDatas -> emoteSlug)) continue;
            $title = ucwords(str_replace("-", " ", $EmotesDatas -> emoteSlug));

            $emotesArray[] = "". $title ."";
        }
        //takedowns
        $takedownsData = $decodeJdata -> orderedItems;
        $takedownsArray = [];
        foreach ($takedownsData as $takedownsDatas) {
            if (empty($outfitsDatas -> takedownSlug)) continue;
            $title = ucwords(strtolower($takedownsDatas -> title));

            $takedownsArray[] = "". $title ."";
        }
        //nameplates
        $nameplatesData = $decodeJdata -> orderedItems;
        $nameplatesArray = [];
        foreach ($nameplatesData as $nameplatesDatas) {
            if (empty($outfitsDatas -> nameplateSlug)) continue;
            $findarray = array("MM", "IM");
            $replacearray = array("Ms Marvel", "Iron Man");

            $title = ucwords(strtolower(str_replace("_", " ", str_replace($findarray, $replacearray, $nameplatesDatas -> title))));

            $nameplatesArray[] = "". $title ."";
        }
        $featuredOutfits = implode(", ", $outfitarray);
        $featuredEmotes = implode(", ", $emotesArray);
        $featuredTakedowns = implode(", ", $takedownsArray);
        $featuredNameplates = implode(", ", $nameplatesArray);

        $featuredOutput = "<!--FEATURED-->\n". $featuredOutfits .", ". $featuredEmotes .", ". $featuredTakedowns .", ". $featuredNameplates .",";
        //singlecharactermarketplace
        
        $SingleOutputArray = [];
        foreach(range(1,6) as $range) {
            switch ($range) {
                case 1:
                    $url = "https://avengers.square-enix-games.com/en-us/characters/captain-america.json";
                    $name = "CAPTAIN AMERICA";
                    $replaceNP = "CA";
                break;
                case 2:
                    $url = "https://avengers.square-enix-games.com/en-us/characters/ms-marvel.json";
                    $name = "MS MARVEL";
                    $replaceNP = "MM";
                break;
                case 3:
                    $url = "https://avengers.square-enix-games.com/en-us/characters/black-widow.json";
                    $name = "BLACK WIDOW";
                    $replaceNP = "BW";
                break;
                case 4:
                    $url = "https://avengers.square-enix-games.com/en-us/characters/hulk.json";
                    $name = "HULK";
                    $replaceNP = "H";
                break;
                case 5:
                    $url = "https://avengers.square-enix-games.com/en-us/characters/iron-man.json";
                    $name = "IRON MAN";
                    $replaceNP = "IM";
                break;
                case 6:
                    $url = "https://avengers.square-enix-games.com/en-us/characters/thor.json";
                    $name = "THOR";
                    $replaceNP = "T";
                break;
            }
            $jdata = file_get_contents($url);
            $decodeJdata = json_decode($jdata);
            
            //Outfits
            $outfitsData = $decodeJdata -> outfits;
            $outfitarray = [];
            foreach ($outfitsData as $outfitsDatas) {
                $CharacterName = ucwords(str_replace("-", " ", $outfitsDatas -> characterSlug));
                $title = ucwords(strtolower($outfitsDatas -> title));
                $showmarketplace = $outfitsDatas -> showInMarketplace;
                if ($showmarketplace == true) continue;
                $outfitarray[] = "". $title ."";
            }
            //Emotes
            $EmotesData = $decodeJdata -> emotes;
            $emotesArray = [];
            foreach ($EmotesData as $EmotesDatas) {
                $title = ucwords(strtolower($EmotesDatas -> title));
                $showmarketplace = $EmotesDatas -> showInMarketplace;
                if ($showmarketplace == true) continue;

                $emotesArray[] = "". $title ."";
            }
            //takedowns
            $takedownsData = $decodeJdata -> takedowns;
            $takedownsArray = [];
            foreach ($takedownsData as $takedownsDatas) {
                $title = ucwords(strtolower($takedownsDatas -> title));
                $showmarketplace = $takedownsDatas -> showInMarketplace;
                if ($showmarketplace == true) continue;

                $takedownsArray[] = "". $title ."";
            }
            //nameplates
            $nameplatesData = $decodeJdata -> nameplates;
            $nameplatesArray = [];
            foreach ($nameplatesData as $nameplatesDatas) {
                $title = str_replace($replaceNP, "", $nameplatesDatas -> title);
                $titlename = ucwords(strtolower($name));
                $title = "". $titlename ." ". $title ."";
                $showmarketplace = $nameplatesDatas -> showInMarketplace;
                if ($showmarketplace == true) continue;

                $nameplatesArray[] = "". $title ."";
            }
            $featuredOutfits = implode(", ", $outfitarray);
            $featuredEmotes = implode(", ", $emotesArray);
            $featuredTakedowns = implode(", ", $takedownsArray);
            $featuredNameplates = implode(", ", $nameplatesArray);

            $SingleOutputArray[] = "<!--". $name ."-->\n". $featuredOutfits .", ". $featuredEmotes .", ". $featuredTakedowns .", ". $featuredNameplates .",\n";

        }
        $SingleOutput = implode("\n", $SingleOutputArray);

        $Totaloutput = "
{{Active|Items=
". $featuredOutput ."

". $SingleOutput ."
}}
<tabber>
Emotes =
{{#dpl:
| titlematch = {{#arrayprint:active|{{!}}|@@@@}}
| category   = Emotes
| includepage = {Emote} marketplacedpl
| namespace  =
| includesubpages=false
| format     =,,\\n
| table      = class=\"itembox sortable\" style=\"color:white; width:100%;\",-,Item,Price
| tablerow   =[[%%]]
| dplcache=emote{{SUBPAGENAME}}-7
| allowcachedresults=true
| suppresserrors=true
}}
|-|
Nameplates =
{{#dpl:
| titlematch = {{#arrayprint:active|{{!}}|@@@@}}
| category   = Nameplates 
| includepage = {Nameplate} marketplacedpl
| namespace  =
| includesubpages=false
| format     =,,\\n
| table      = class=\"itembox sortable\" style=\"color:white; width:100%;\",-,Item,Price
| tablerow   =[[%%]]
| dplcache=nameplates{{SUBPAGENAME}}-7
| allowcachedresults=true
| suppresserrors=true
}}
|-|
Outfits =
{{#dpl:
| titlematch = {{#arrayprint:active|{{!}}|@@@@}}
| category   = Outfits 
| includepage = {Outfit} marketplacedpl
| namespace  =
| includesubpages=false
| format     =,,\\n
| table      = class=\"itembox sortable\" style=\"color:white; width:100%;\",-,Item,Price
| tablerow   =[[%%]]
| dplcache=Outfits{{SUBPAGENAME}}-7
| allowcachedresults=true
| suppresserrors=true
}}
|-|
Takedowns =
{{#dpl:
| titlematch = {{#arrayprint:active|{{!}}|@@@@}}
| category   = Takedowns
| includepage = {Takedown} marketplacedpl
| namespace  =
| includesubpages=false
| format     =,,\\n
| table      = class=\"itembox sortable\" style=\"color:white; width:100%;\",-,Item,Price
| tablerow   =[[%%]]
| dplcache=takedown{{SUBPAGENAME}}-7
| allowcachedresults=true
| suppresserrors=true
}}
</tabber>";
//write to file
if (!file_exists("output/avengers")) { mkdir("output/avengers", 0777, true); }
$file = fopen("output/avengers/AVMarketPlace.txt", 'w');
fwrite($file, $Totaloutput);
fclose($file);

        // save
        //$console->writeln(" Saving... ");
        //$this->save("Avengers/Market.txt", 999999);
    }
}
