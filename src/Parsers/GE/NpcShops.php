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
        $FccShopCsv = $this->csv("FccShop");
        $LotteryExchangeShopCsv = $this->csv("LotteryExchangeShop");

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
                    /**
                     * 3538944 - > 3539050 goes prehandler
                     * 3407872 -> 3407879 goes LotteryExchangeShop
                     * 2752512 -> 2752515 FccShop
                     */
                    if ($ENpcBase["ENpcData[$i]"] >= 3276800 && $ENpcBase["ENpcData[$i]"] < 3279999) { //TOPIC SELECT
                        $TopicSelectName = "";
                        $TopicSelectName = $TopicSelectCsv->at($ENpcBase["ENpcData[$i]"])["Name"];
                        $TopicSelectArray = [];

                        foreach(range(0,9) as $a) {
                            $DataLink = $ENpcBase["ENpcData[$i]"];
                            if ($DataLink == 0) continue;
                            $ShopLink = $TopicSelectCsv->at($DataLink)["Shop[$a]"];
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
                                        $SpecialShopItemArray[] = "". $ItemFor ."". $ItemTrade ."";
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
                                        $Item2CostCount = $SpecialShopCsv->at($ShopLink)["Count{Cost}[$b][1]"];
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
                    if ($ENpcBase["ENpcData[$i]"] >= 2752500 && $ENpcBase["ENpcData[$i]"] < 2753000) { //FC SHOP
                        $FCShopName = "";
                        $FCShopName = $FccShopCsv->at($ENpcBase["ENpcData[$i]"])["Name"];
                        $FCShopArray = [];
                        foreach(range(0,9) as $b) {
                            $Item = $ItemCsv->at($FccShopCsv->at($ENpcBase["ENpcData[$i]"])["Item[$b]"])['Name'];
                            if (empty($Item)) continue;
                            $CreditsCost = $FccShopCsv->at($ENpcBase["ENpcData[$i]"])["Cost[$b]"];
                            $RankRequired = $FccShopCsv->at($ENpcBase["ENpcData[$i]"])["FCRank{Required}[$b]"];
                            $FCShopArray[] = "{{Sells|Item1=".$Item ."|Count1=".$CreditsCost ."|Required=FC Rank ". $RankRequired ."}}";
                        }
                        $ItemOutput = implode("\n", $FCShopArray);
                        $ShopOutputData = "\n". $FCShopName ."= FC Shop \n{{Tabsells\n". $ItemOutput ."}}\n\n|-|";
                    } // end of FC SHOP
                    if ($ENpcBase["ENpcData[$i]"] >= 3407800 && $ENpcBase["ENpcData[$i]"] < 3409999) { //LOTTERYEXCHANGESHOP
                        $LEShopName = "";
                        $LEShopArray = [];
                        foreach(range(0,15) as $b) {
                            $Item = $ItemCsv->at($LotteryExchangeShopCsv->at($ENpcBase["ENpcData[$i]"])["ItemAccepted[$b]"])['Name'];
                            if (empty($Item)) continue;
                            $Cost = $LotteryExchangeShopCsv->at($ENpcBase["ENpcData[$i]"])["AmountAccepted[$b]"];
                            $LEShopArray[] = "{{Sells|Item1=".$Item ."|Count1=".$Cost ."}}";
                        }
                        $ItemOutput = implode("\n", $LEShopArray);
                        $ShopOutputData = "\n". $LEShopName ."= Lottery Exchange Shop \n{{Tabsells\n". $ItemOutput ."}}\n\n|-|";
                    } // end of LOTTERYEXCHANGESHOP
                    if ($ENpcBase["ENpcData[$i]"] >= 1769000 && $ENpcBase["ENpcData[$i]"] < 1779999) { //SPECIALSHOP
                        $ShopID = $ENpcBase["ENpcData[$i]"];
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
                            $SpecialShopItemArray[] = "". $ItemFor ."". $ItemTrade ."";
                        }
                        $SpecialShopItemOutput = implode("\n", $SpecialShopItemArray);
                        $ShopOutput = "\n|". $ShopName ."\n". $SpecialShopItemOutput ."";
                        $ShopOutputData = "\n". $ShopName ."= Special Shop \n{{Tabsells\n". $ShopOutput ."}}\n\n|-|";
                    } // end of SPECIALSHOP
                    if ($ENpcBase["ENpcData[$i]"] >= 3538900 && $ENpcBase["ENpcData[$i]"] < 3540000) { //PREHANDLER
                        $ShopID = $PreHandlerCsv->at($ENpcBase["ENpcData[$i]"])["Target"];
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
                            $ShopOutputData = "\n". $ShopName ."= Prehandler \n{{Tabsells\n". $ShopOutput ."}}\n\n|-|";
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
                                $SpecialShopItemArray[] = "". $ItemFor ."". $ItemTrade ."";
                            }
                            $SpecialShopItemOutput = implode("\n", $SpecialShopItemArray);
                            $ShopOutput = "\n|". $ShopName ."\n". $SpecialShopItemOutput ."";
                            $ShopOutputData = "\n". $ShopName ."= Prehandler \n{{Tabsells\n". $ShopOutput ."}}\n\n|-|";
                        }
                    } // end of PREHANDLER
                    if ($ENpcBase["ENpcData[$i]"] >= 262000 && $ENpcBase["ENpcData[$i]"] < 264000) { //GILSHOP
                        $ShopLink = $ENpcBase["ENpcData[$i]"];
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
                        $ShopOutputData = "\n". $ShopName ."= Gilshop \n{{Tabsells\n". $ShopOutput ."}}\n\n|-|";
                    } // end of GILSHOP

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
        $info = $this->save("$CurrentPatchOutput/NpcShops - ". $Patch .".txt", 9999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
