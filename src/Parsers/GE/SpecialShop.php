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
    const WIKI_FORMAT = "{name}
{quest}{deny}{accept}
{item}";

    public function parse()
    {

        $SpecialShopCsv = $this->csv('SpecialShop');
        $ItemCsv = $this->csv('Item');
        $QuestCsv = $this->csv('Quest');
        $AchievementCsv = $this->csv('Achievement');
        $DefaultTalkCsv = $this->csv('DefaultTalk');

        // console writer
        $console = new ConsoleOutput();


        // download our CSV files
        $console->writeln(" Loading CSVs");
        $console->writeln(" Processing SpecialShop");

        // switch to a section so we can overwrite
        $console = $console->section();

        // loop through our sequences
        foreach($SpecialShopCsv->data as $id => $SpecialShop) {
            $id = $SpecialShop['id'];
            $name = $SpecialShop['Name'];
            $QuestUnlock = $QuestCsv->at($SpecialShop['Quest{Unlock}'])['Name'];
            //if the quest is a 0 (not unlocked via quest) then don't output.
            if (empty($QuestUnlock)) {
                    $QuestUnlock = "";
                } elseif (!empty($QuestUnlock)) {
                    $QuestUnlock = "\n| Quest = ". $QuestUnlock ."";
            }
            $DenyMessage = $DefaultTalkCsv->at($SpecialShop['NotCompleteText'])['Text[0]'];
                if (!empty($DenyMessage)) {
                    $DenyMessage0 = str_replace("0","",$DefaultTalkCsv->at($SpecialShop['NotCompleteText'])['Text[0]']);
                    $DenyMessage1 = str_replace("0","",$DefaultTalkCsv->at($SpecialShop['NotCompleteText'])['Text[1]']);
                    $DenyMessage2 = str_replace("0","",$DefaultTalkCsv->at($SpecialShop['NotCompleteText'])['Text[2]']);
                    $DenyMessage =  "\n| Dialogue = ". $DenyMessage0 ."\n". $DenyMessage1 ."\n". $DenyMessage2 ."";
                } elseif (empty($DenyMessage)) {
                    $DenyMessage = "";
                }
            $AcceptMessage = $DefaultTalkCsv->at($SpecialShop['CompleteText'])['Text[0]'];
                if (!empty($AcceptMessage)) {
                    $AcceptMessage0 = str_replace("0","",$DefaultTalkCsv->at($SpecialShop['CompleteText'])['Text[0]']);
                    $AcceptMessage1 = str_replace("0","",$DefaultTalkCsv->at($SpecialShop['CompleteText'])['Text[1]']);
                    $AcceptMessage2 = str_replace("0","",$DefaultTalkCsv->at($SpecialShop['CompleteText'])['Text[2]']);
                    $AcceptMessage =  "\n| Additional Dialogue = ". $AcceptMessage0 ."\n". $AcceptMessage1 ."\n". $AcceptMessage2 ."";
                } elseif (empty($AcceptMessage)) {
                    $AcceptMessage = "";
                }

            //loop though every Item Receive column
            $item =[];
            foreach(range(0,59) as $i) {
                //if there's no item there, skip it and no output.
                if (empty($ItemCsv->at($SpecialShop["Item{Receive}[$i][0]"])["Name"])) {
                    continue;
                }
                //gather item received and the amount received + the other item you get if there is one
                $ItemReward = $ItemCsv->at($SpecialShop["Item{Receive}[$i][0]"])["Name"];
                $ItemReward2 = $ItemCsv->at($SpecialShop["Item{Receive}[$i][1]"])["Name"];
                $ItemRewardAmount = $SpecialShop["Count{Receive}[$i][0]"];
                $ItemReward2Amount = $SpecialShop["Count{Receive}[$i][1]"];

                //find if the item you get is HQ or not and output nothing for no and "HQ" if yes
                $ItemRewardHQ = $SpecialShop["HQ{Receive}[$i][0]"];
                if ($ItemRewardHQ = "False") {
                    $ItemRewardHQFmt = "";
                } elseif ($ItemRewardHQ = "True") {
                    $ItemRewardHQFmt = "|HQItem=x";
                }
                $ItemReward2HQ = $SpecialShop["HQ{Receive}[$i][1]"];
                if ($ItemReward2HQ = "False") {
                    $ItemReward2HQFmt = "";
                } elseif ($ItemReward2HQ = "True") {
                    $ItemReward2HQFmt = "|HQItem=x";
                }

                //if there is no 2nd reward then just output nothing so it doesn't clog up the output
                $ItemRewardFmt = "". $ItemRewardHQFmt ."". $ItemReward ."|Quantity=". $ItemRewardAmount ."". $ItemRewardHQFmt ."";

                //if (!empty($ItemReward2)) {
                //$ItemRewardFmt = "". $ItemRewardHQFmt ."". $ItemReward ."|Quantity=". $ItemRewardAmount ." and ". $ItemReward2HQFmt ."". $ItemReward2 ." x ". $ItemReward2Amount;
                //}

                //item cost 0
                //get the cost type then amount
                $ItemCostType = $ItemCsv->at($SpecialShop["Item{Cost}[$i][0]"])["Name"];

                //if Item{Cost} is 'Gil' and if Count{Cost} = 0 then retrieve the shop selling price from Item.csv
                if ($ItemCostType == "Gil" && $SpecialShop["Count{Cost}[$i][0]"] == "0") {
                    $ItemCostAmount = $ItemCsv->at($SpecialShop["Item{Receive}[$i][0]"])["Price{Mid}"];
                } else {
                    $ItemCostAmount = $SpecialShop["Count{Cost}[$i][0]"];
                }

                //does it need a collectablity rating amount?
                $Collectability = $SpecialShop["CollectabilityRating{Cost}[$i][0]"];
                if ($Collectability = "False") {
                    $CollectabilityFmt = "";
                } elseif ($Collectability = "True") {
                    $CollectabilityFmt = " at a Collectability Rating of ". $Collectability ."+";
                }

                //item cost 1
                $SecondItemCostType = $ItemCsv->at($SpecialShop["Item{Cost}[$i][1]"])["Name"];
                $SecondItemCostAmount = $SpecialShop["Count{Cost}[$i][1]"];
                //is the item only HQ trade in ? if not output nothing for no and "HQ" if yes
                $SecondItemCostTypeHQ = $SpecialShop["HQ{Cost}[$i][1]"];
                if ($SecondItemCostTypeHQ = "False") {
                    $SecondItemCostTypeHQFmt = "";
                } elseif ($SecondItemCostTypeHQ = "True") {
                    $SecondItemCostTypeHQFmt = "HQ ";
                }

                //does it need a collectablity rating amount?
                $SecondCollectability = $SpecialShop["CollectabilityRating{Cost}[$i][1]"];
                if ($SecondCollectability = "False") {
                    $SecondCollectabilityFmt = "";
                } elseif ($SecondCollectability = "True") {
                    $SecondCollectabilityFmt = " at a Collectability Rating of ". $SecondCollectability ."+";
                }

                //item cost 2
                $ThirdItemCostType = $ItemCsv->at($SpecialShop["Item{Cost}[$i][2]"])["Name"];
                $ThirdItemCostAmount = $SpecialShop["Count{Cost}[$i][2]"];
                //is the item only HQ trade in ? if not output nothing for no and "HQ" if yes
                $ThirdItemCostTypeHQ = $SpecialShop["HQ{Cost}[$i][2]"];
                if ($ThirdItemCostTypeHQ = "False") {
                    $ThirdItemCostTypeHQFmt = "";
                } elseif ($ThirdItemCostTypeHQ = "True") {
                    $ThirdItemCostTypeHQFmt = "HQ ";
                }

                //does it need a collectablity rating amount?
                $ThirdCollectability = $SpecialShop["CollectabilityRating{Cost}[$i][2]"];
                if ($ThirdCollectability = "False") {
                    $ThirdCollectabilityFmt = "";
                } elseif ($ThirdCollectability = "True") {
                    $ThirdCollectabilityFmt = " at a Collectability Rating of ". $ThirdCollectability ."+";
                }

                //if there is no 2nd and 3rd reward then just output nothing so it doesn't clog up the output
                if (!empty($ThirdItemCostType)) {
                    $SecondItemCostFmt = "|Item1=". $ItemCostType ."|Count1=". $ItemCostAmount ."|Item2=". $SecondItemCostType ."|Count2=". $SecondItemCostAmount ."|Item3=". $ThirdItemCostType ."|Count3=". $ThirdItemCostAmount ."";
                } elseif (empty($SecondItemCostType)) {
                    $SecondItemCostFmt = "|Item1=". $ItemCostType ."|Count1=". $ItemCostAmount ."";
                } elseif (!empty($SecondItemCostType)) {
                    $SecondItemCostFmt = "|Item1=". $ItemCostType ."|Count1=". $ItemCostAmount ."|Item2=". $SecondItemCostType ."|Count2=". $SecondItemCostAmount ."";
                }

                //Quest item?
                $QuestItem = $QuestCsv->at($SpecialShop["Quest{Item}[$i]"])["Name"];
                if (!empty($QuestItem)) {
                    $QuestItem =  "\n    |Quest = ".$QuestItem;
                } elseif (empty($QuestItem)) {
                    $QuestItem = "";
                }

                //Item is Unlocked from Achievement
                $AchievementUnlock = $AchievementCsv->at($SpecialShop["AchievementUnlock[$i]"])["Name"];
                if (!empty($AchievementUnlock)) {
                    $AchievementUnlock =  "\n    |Achievement = ". $AchievementUnlock ."";
                } elseif (empty($AchievementUnlock)) {
                    $AchievementUnlock = "";
                }

                //Item Patch Number
                //    $PatchNumber = $SpecialShop["PatchNumber[$i]"];
                //    if ($PatchNumber != "0") {
                //        $PatchNumberFmt =  "\n    |Patch Number = ". $PatchNumber ."";
                //    } elseif ($PatchNumber = "0") {
                //        $PatchNumberFmt = "";
                //    }

                $string = "\n{{Trades|". $ItemRewardFmt ."". $SecondItemCostFmt ."". $QuestItem ."". $AchievementUnlock ."}}";
                $item[] = $string;
            }
            $item = implode($item);

            // build our data array using the GE Formatter
            $data = GeFormatter::format(self::WIKI_FORMAT, [
                '{id}'  => $id,
                '{name}'  => (!empty($name)) ? "| $name =" : "",
                '{item}'  => $item,
                '{quest}'  => $QuestUnlock,
                '{deny}' => $DenyMessage,
                '{accept}' => $AcceptMessage,

            ]);
            $this->data[] = $data;
            $console->overwrite(" > Completed Shop: {$id} --> }");
        }

        // save
        $console->writeln(" Saving... ");
        $this->save("SpecialShop.txt", 999999);
    }
}
