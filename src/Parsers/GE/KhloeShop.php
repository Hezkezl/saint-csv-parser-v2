<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * php bin/console app:parse:csv GE:KhloeShop
 */
class KhloeShop implements ParseInterface
{
    use CsvParseTrait;


    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Output}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');
        $console = new ConsoleOutput();
        $console->writeln(" Loading CSVs");

        $WeeklyBingoRewardDataCsv = $this->csv("WeeklyBingoRewardData");
        $ItemCsv = $this->csv("Item");
        $TomestonesItemCsv = $this->csv("TomestonesItem");

        // console writer

        // download our CSV files
        $console->writeln(" Processing WeeklyBingoRewardData");

        // switch to a section so we can overwrite
        $console = $console->section();
        $ItemArray = [];
        $Currency[1] = "Allagan Tomestone of Poetics";
        $Currency[2] = "Allagan Tomestone of Allegory";
        $Currency[3] = "Allagan Tomestone of Revelation";
        // loop through our sequences
        foreach($WeeklyBingoRewardDataCsv->data as $id => $Bingo) {
            if ($Bingo['Reward{Item}[0]'] === "0") continue;
            foreach(range(0,1) as $i) {
                if ($Bingo["Reward{Item}[$i]"] === "0") continue;
                $ItemID = $Bingo["Reward{Item}[$i]"];
                $Type = $Bingo["Reward{Type}[$i]"];
                switch ($Type) {
                    case 1:
                        $Item = $ItemCsv->at($ItemID)['Name'];
                    break;
                    case 2:
                        $Item = $Currency[$ItemID];
                    break;
                }
                switch ($Bingo["Reward{HQ}[$i]"]) {
                    case "True":
                        $ItemReceiveHQ = "|HQItem=x";
                    break;
                    case "False":
                        $ItemReceiveHQ = "";
                    break;
                }
                $Quantity = $Bingo["Reward{Quantity}[$i]"];
                $ItemArray[] = "{{Sells3|$Item|Quantity=$Quantity$ItemReceiveHQ|Cost1=Wondrous Tails (Key Item)|Count1=1}}";
            }
            if ($Bingo["Reward{Item}[2]"] === "0") continue;
            $ItemID = $Bingo["Reward{Item}[2]"];
            $Type = $Bingo["Reward{Option}[1]"];
            switch ($Type) {
                case 1:
                    $Item = $ItemCsv->at($ItemID)['Name'];
                break;
                case 2:
                    $Item = $Currency[$ItemID];
                break;
            }
            switch ($Bingo["Reward{HQ}[2]"]) {
                case "True":
                    $ItemReceiveHQ = "|HQItem=x";
                break;
                case "False":
                    $ItemReceiveHQ = "";
                break;
            }
            $Quantity = $Bingo["Reward{Quantity}[2]"];
            $ItemArray[] = "{{Sells3|$Item|Quantity=$Quantity$ItemReceiveHQ|Cost1=Wondrous Tails (Key Item)|Count1=1}}";
        }
        asort($ItemArray);
        $NewItemArray = array_unique($ItemArray);
        $number = count($NewItemArray);
        $ShopOutputString = "{{-start-}}\n'''Khloe Aliapoh/Wondrous Tails'''\n";
        $ShopOutputString .= "{{Shop\n";
        $ShopOutputString .= "| Shop Name = Wondrous Tails\n";
        $ShopOutputString .= "| NPC Name = Khloe Aliapoh\n";
        $ShopOutputString .= "| Location = Rowena's Center for Cultural Promotion\n";
        $ShopOutputString .= "| Coordinates = 5.8-6.1\n";
        $ShopOutputString .= "| Total Items = $number\n";
        $ShopOutputString .= "| Shop = \n";
        $ShopOutputString .= "{{Tabsells3\n";
        $ShopOutputString .= "| Misc = \n";
        $ShopOutputString .= implode("\n",$NewItemArray)."\n";
        $ShopOutputString .= "}}\n";
        $ShopOutputString .= "}}\n";
        $ShopOutputString .= "{{-stop-}}\n";
        // build our data array using the GE Formatter
        $data = GeFormatter::format(self::WIKI_FORMAT, [
            '{Output}' => $ShopOutputString,
        ]);
        $this->data[] = $data;
        $console->overwrite(" > Completed Shop: {$id} --> }");

        // save
        $console->writeln(" Saving... ");
        $info = $this->save("KhloeShop.txt", 999999);
    }
}