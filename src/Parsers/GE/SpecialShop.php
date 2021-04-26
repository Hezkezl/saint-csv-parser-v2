<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * php bin/console app:parse:csv GE:SpecialShop
 */
class SpecialShop implements ParseInterface
{
    use CsvParseTrait;


    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{ShopOutput}
    {ShopDialogue}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');
        $console = new ConsoleOutput();
        $console->writeln(" Loading CSVs");

        $SpecialShopCsv = $this->csv("SpecialShop");
        $ItemCsv = $this->csv("Item");
        $QuestCsv = $this->csv("Quest");
        $AchievementCsv = $this->csv("Achievement");
        $DefaultTalkCsv = $this->csv("DefaultTalk");
        $GilShopCsv = $this->csv("Gilshop");
        $GilShopItemCsv = $this->csv("GilshopItem");

        // console writer

        // download our CSV files
        $console->writeln(" Processing SpecialShop");

        // switch to a section so we can overwrite
        $console = $console->section();

        // loop through our sequences
        foreach($SpecialShopCsv->data as $id => $SpecialShop) {
            $FuncShop = $this->getShop($id, "SpecialShop", $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $id, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, "", "","");
            $ShopOutput = $FuncShop["Shop"];
            $ShopDialogue = $FuncShop["Dialogue"];

            // build our data array using the GE Formatter
            $data = GeFormatter::format(self::WIKI_FORMAT, [
                '{ShopOutput}'  => $ShopOutput,
                '{ShopDialogue}'  => $ShopDialogue,

            ]);
            $this->data[] = $data;
            $console->overwrite(" > Completed Shop: {$id} --> }");
        }

        // save
        $console->writeln(" Saving... ");
        $info = $this->save("SpecialShops.txt", 999999);
    }
}