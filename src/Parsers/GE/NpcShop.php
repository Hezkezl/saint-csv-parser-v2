<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:NpcShops
 */
class NpcShops implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{Top}";
    public function parse()
    {
      include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $ENpcBaseCsv = $this->csv("ENpcBase");
        $ENpcResidentCsv = $this->csv("ENpcResident");
        $TopicSelectCsv = $this->csv("TopicSelect");
        $CustomTalkCsv = $this->csv("CustomTalk");
        $GilShopCsv = $this->csv("GilShop");
        $GilShopItemCsv = $this->csv("GilShopItem");
        $SpecialShopCsv = $this->csv("SpecialShop");
        $PreHandlerCsv = $this->csv("PreHandler");
        $ItemCsv = $this->csv("Item");


        // (optional) start a progress bar
        $this->io->progressStart($ENpcBaseCsv->total);

        // loop through data
        foreach ($ENpcBaseCsv->data as $id => $ENpcBase) {
            $this->io->progressAdvance();
            $EventHandler = $ENpcBase['EventHandler'];
            $EnpcName = $ENpcResidentCsv->at($id)['Singular'];
            if (empty($EnpcName)) continue;
            $CustomTalkArray = [];
            $TopicSelectArray = [];
            $ENpcShopsArray = [];
            $ShopOutputData = "";
            //if ($id != 1001965) continue;
            foreach(range(0,31) as $i) {
                if ($ENpcBase["ENpcData[$i]"] == 0) continue;
                if(!empty($ENpcBase["ENpcData[$i]"])) {
                    if ($ENpcBase["ENpcData[$i]"] >= 3276800 && $ENpcBase["ENpcData[$i]"] < 3279999) { //TOPIC SELECT
                        $TopicSelectName = "";
                        $TopicSelectName = $TopicSelectCsv->at($ENpcBase["ENpcData[$i]"])["Name"];
                        $TopicSelectArray = [];
                        foreach(range(0,9) as $a) {
                            $DataLink = $ENpcBase["ENpcData[$i]"];
                            if ($DataLink == 0) continue;
                            $ShopLink = $TopicSelectCsv->at($DataLink)["Shop[$a]"];
                            //var_dump($ShopLink);
                            if ($ShopLink == 0) continue;

                            if ($ShopLink >= 262000 && $ShopLink < 264000) { // links to GilShop
                                $ShopName = $GilShopCsv->at($ShopLink)["Name"];
                                $ShopNameItems = $ItemCsv->at($GilShopItemCsv->at($ShopLink)["Item"])["Name"];
                                $GilShopItemArray = [];
                                foreach(range(0,50) as $b) {
                                    $GilShopSubArray = "". $ShopLink . "." . $b ."";
                                    if (empty($ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"])) continue;
                                    $GilShopSellsItem = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"];
                                    $GilShopSellsItemCost = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Price{Mid}"];
                                    $GilShopItemArray[] = "{{Sells|".$GilShopSellsItem ."|".$GilShopSellsItemCost ."}}";
                                }
                                $GilShopItemArrayOutput = implode("\n", $GilShopItemArray);
                                $ShopOutput = "|". $ShopName ." =\n". $GilShopItemArrayOutput ."\n";
                            }

                            //this is broke
                            if ($ShopLink > 720890 && $ShopLink < 722000) { // links to CustomTalk
                                $CustomTalkArray = [];
                                foreach(range(0,29) as $b) {
                                    $ShopInstruction = $CustomTalkCsv->at($ShopLink)["Script{Instruction}[$b]"];
                                    //if (empty($ShopInstruction)) continue;
                                    //if (!strstr($ShopInstruction, 'SHOP')) continue;
                                    $ShopArgument = $CustomTalkCsv->at($ShopLink)["Script{Arg}[$b]"];
                                    $CustomTalkArray[] = $ShopArgument;
                                }
                                $ShopOutput = implode("\n", $CustomTalkArray);
                                $ShopOutput = "CustomTalk -> ". $ShopName ."";
                            }
                            //this is broke

                            if ($ShopLink >= 3538900 && $ShopLink < 3540000) { // links to PreHandler
                                $ShopID = $PreHandlerCsv->at($ShopLink)["Target"];
                                if ($ShopID > 262100 && $ShopID < 269999) {
                                    $ShopName = $GilShopCsv->at($ShopID)["Name"];
                                    $ShopNameItems = $ItemCsv->at($GilShopItemCsv->at($ShopID)["Item"])["Name"];
                                    $GilShopItemArray = [];
                                    foreach(range(0,50) as $b) {
                                        $GilShopSubArray = "". $ShopID . "." . $b ."";
                                        if (empty($ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"])) continue;
                                        $GilShopSellsItem = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"];
                                        $GilShopSellsItemCost = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Price{Mid}"];
                                        $GilShopItemArray[] = "{{Sells|".$GilShopSellsItem ."|".$GilShopSellsItemCost ."}}";
                                    }
                                    $GilShopItemArrayOutput = implode("\n", $GilShopItemArray);
                                    $ShopOutput = "|". $ShopName ." =\n{{Tabsells". $GilShopItemArrayOutput ."";
                                }
                                if ($ShopID >= 1769000 && $ShopID < 1779999) {
                                    $ShopName = $SpecialShopCsv->at($ShopID)["Name"];

                                    $SpecialShopItemArray = [];
                                        foreach(range(0,59) as $b) {
                                        if (empty($ItemCsv->at($SpecialShopCsv->at($ShopID)["Item{Receive}[$b][0]"])["Name"])) continue;
                                        $Item1Name = $ItemCsv->at($SpecialShopCsv->at($ShopID)["Item{Receive}[$b][0]"])["Name"];
                                        $Item1Count = $SpecialShopCsv->at($ShopID)["Count{Receive}[$b][0]"];
                                        $Item1HQ = $SpecialShopCsv->at($ShopID)["HQ{Receive}[$b][0]"];
                                        $Item1Cost = $ItemCsv->at($SpecialShopCsv->at($ShopID)["Item{Cost}[$b][0]"])["Name"];
                                        $Item1CostCount = $SpecialShopCsv->at($ShopID)["Count{Cost}[$b][0]"];
                                        $Item1CostHQ = $SpecialShopCsv->at($ShopID)["HQ{Cost}[$b][0]"];
                                        $ItemFor = "{{Trades|". $Item1Name ."|Quantity=". $Item1Count ."";
                                        $ItemTrade = "|Item1=". $Item1Cost ."|Count1=". $Item1CostCount ."}}";
                                        if (!empty($ItemCsv->at($SpecialShopCsv->at($ShopID)["Item{Receive}[$b][1]"])["Name"])) {   
                                            $Item2Name = $ItemCsv->at($SpecialShopCsv->at($ShopID)["Item{Receive}[$b][1]"])["Name"];
                                            $Item2Count = $SpecialShopCsv->at($ShopID)["Count{Receive}[$b][1]"];
                                            $Item2HQ = $SpecialShopCsv->at($ShopID)["HQ{Receive}[$b][1]"];
                                            $ItemFor = "{{Trades|". $Item1Name ."|Quantity=". $Item1Count ."|". $Item2Name ."|Quantity=". $Item2Count ."";
                                            }
                                        if (!empty($ItemCsv->at($SpecialShopCsv->at($ShopID)["Item{Cost}[$b][1]"])["Name"])) { 
                                            $Item2Cost = $ItemCsv->at($SpecialShopCsv->at($ShopID)["Item{Cost}[$b][1]"])["Name"];
                                            $Item2CostCount = $SpecialShopCsv->at($ShopID)["Count{Cost}[$b][1]"];
                                            $Item2CostHQ = $SpecialShopCsv->at($ShopID)["HQ{Cost}[$b][1]"];
                                            $ItemTrade = "|Item1=". $Item1Cost ."|Count1=". $Item1CostCount ."|Item2=". $Item2Cost ."|Count2=". $Item2CostCount ."}}";
                                        }
                                        $SpecialShopItemArray[] = "". $ItemFor ." ". $ItemTrade ."";
                                    }
                                    $SpecialShopItemOutput = implode("\n", $SpecialShopItemArray);
                                    $ShopOutput = "\n|". $ShopName ."\n". $SpecialShopItemOutput ."";
                                }
                            }

                            if ($ShopLink >= 1769000 && $ShopLink < 1779999) { // links to SpecialShop
                                
                                $ShopName = $SpecialShopCsv->at($ShopLink)["Name"];

                                $SpecialShopItemArray = [];
                                    foreach(range(0,59) as $b) {
                                    if (empty($ItemCsv->at($SpecialShopCsv->at($ShopLink)["Item{Receive}[$b][0]"])["Name"])) continue;
                                    $Item1Name = $ItemCsv->at($SpecialShopCsv->at($ShopLink)["Item{Receive}[$b][0]"])["Name"];
                                    $Item1Count = $SpecialShopCsv->at($ShopLink)["Count{Receive}[$b][0]"];
                                    $Item1HQ = $SpecialShopCsv->at($ShopLink)["HQ{Receive}[$b][0]"];
                                    $Item1Cost = $ItemCsv->at($SpecialShopCsv->at($ShopLink)["Item{Cost}[$b][0]"])["Name"];
                                    $Item1CostCount = $SpecialShopCsv->at($ShopLink)["Count{Cost}[$b][0]"];
                                    $Item1CostHQ = $SpecialShopCsv->at($ShopLink)["HQ{Cost}[$b][0]"];
                                    $ItemFor = "{{Trades|". $Item1Name ."|Quantity=". $Item1Count ."";
                                    $ItemTrade = "|Item1=". $Item1Cost ."|Count1=". $Item1CostCount ."}}";
                                    if (!empty($ItemCsv->at($SpecialShopCsv->at($ShopLink)["Item{Receive}[$b][1]"])["Name"])) {   
                                        $Item2Name = $ItemCsv->at($SpecialShopCsv->at($ShopLink)["Item{Receive}[$b][1]"])["Name"];
                                        $Item2Count = $SpecialShopCsv->at($ShopLink)["Count{Receive}[$b][1]"];
                                        $Item2HQ = $SpecialShopCsv->at($ShopLink)["HQ{Receive}[$b][1]"];
                                        $ItemFor = "{{Trades|". $Item1Name ."|Quantity=". $Item1Count ."|". $Item2Name ."|Quantity=". $Item2Count ."";
                                        }
                                    if (!empty($ItemCsv->at($SpecialShopCsv->at($ShopLink)["Item{Cost}[$b][1]"])["Name"])) { 
                                        $Item2Cost = $ItemCsv->at($SpecialShopCsv->at($ShopLink)["Item{Cost}[$b][1]"])["Name"];
                                        //needs fix, only shows 0
                                        $Item2CostCount = $SpecialShopCsv->at($ShopID)["Count{Cost}[$b][1]"];
                                        //needs fix, only shows 0 ^^^^^^
                                        $Item2CostHQ = $SpecialShopCsv->at($ShopLink)["HQ{Cost}[$b][1]"];
                                        $ItemTrade = "|Item1=". $Item1Cost ."|Count1=". $Item1CostCount ."|Item2=". $Item2Cost ."|Count2=". $Item2CostCount ."}}";
                                    }
                                    $SpecialShopItemArray[] = "". $ItemFor ."". $ItemTrade ."";
                                }
                                $SpecialShopItemOutput = implode("\n", $SpecialShopItemArray);
                                $ShopOutput = "\n|". $ShopName ."=\n". $SpecialShopItemOutput ."";
                            }

                            $TopicSelectArray[] = $ShopOutput;
                        }
                        $TopicSelectOutputOld = implode("\n", $TopicSelectArray);
                        $ShopOutputData = "\n". $TopicSelectName ."= TOPIC SELECT \n{{Tabsells\n". $TopicSelectOutputOld ."}}\n\n|-|";
                    } // end of TOPIC SELECT

                }
                $ENpcShopsArray[] = $ShopOutputData;
            }
            $ENpcShopsOutput = implode("\n", $ENpcShopsArray);
            $ENpcShopsOutput = "NPC NAME = ". $EnpcName . " | ". $id ."". $ENpcShopsOutput ."}}";

            // Save some data
            $data = [
                '{Top}' => $ENpcShopsOutput,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        };

        // save our data to the filename: GeMountWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        //$info = $this->save('Achievement.txt', 20000);
        $info = $this->save("$CurrentPatchOutput/NpcShops - ". $Patch .".txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
