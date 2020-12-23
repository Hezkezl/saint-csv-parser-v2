<?php

namespace App\Parsers;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

trait CsvParseTrait
{
    /** @var SymfonyStyle */
    public $io;
    /** @var string */
    public $projectDirectory;
    /** @var array */
    public $data = [];
    /** @var array */
    public $internal = [];
    /** @var \stdClass */
    public $ex;

    /**
     * Query CSV file from SaintC extraction folder
     */
    public function csv($content): ParseWrapper
    {
        if (isset($this->internal[$content])) {
            return $this->internal[$content];
        }

        // $cache = $this->projectDirectory . getenv('CACHE_DIRECTORY');

        //get the current patch long ID
        $MainPath = "C:\Program Files (x86)\SquareEnix\FINAL FANTASY XIV - A Realm Reborn";
        $PatchID = file_get_contents("". $MainPath ."\game\\ffxivgame.ver");
        //$PatchID = "2020.10.21.0001.0000";
        $cache = "F:\Rogue\SaintCoinach.Cmd/$PatchID/rawexd";

        //$cache = $this->projectDirectory . getenv('CACHE_DIRECTORY');
        $filename = "{$cache}/{$content}.csv";

        // check cache and download if it does not exist. Ignoring now since Icarus rewrite in July 2020
        /*
        if (!file_exists($filename)) {
            $this->io->text("Downloading: '{$content}.csv' for the first time ...");

            $githubFilename = str_ireplace('{content}', $content, getenv('GITHUB_CSV_FILE'));
            try {
                $githubFiledata = file_get_contents($githubFilename);
            } catch (\Exception $ex) {
                $this->io->error("Could not get the file: {$githubFilename} from GITHUB, are you sure it exists? Filenames are case-sensitive.");
                die;
            }

            if (!$githubFiledata) {
                $this->io->text('<error>Could not download file from github: '. $githubFilename);die;
            }

            $pi = pathinfo($filename);
            if (!is_dir($pi['dirname'])) {
                mkdir($pi['dirname'], 0777, true);
            }

            file_put_contents($filename, $githubFiledata);
            $this->io->text('✓ Download complete');
        }
        */

        // grab wrapper
        $parser = new ParseWrapper($content, $filename);
        file_put_contents($filename.'.columns', json_encode($parser->columns, JSON_PRETTY_PRINT));
        file_put_contents($filename.'.offsets', json_encode($parser->offsets, JSON_PRETTY_PRINT));
        file_put_contents($filename.'.data', json_encode($parser->data, JSON_PRETTY_PRINT));

        $this->internal[$content] = $parser;

        return $parser;
    }

    /**
     * Set project directory
     */
    public function setProjectDirectory(string $projectDirectory)
    {
        $this->projectDirectory = $projectDirectory;
        return $this;
    }


    /**
     * Get Default Talk based on variables
     */
    public function getDefaultTalk($DefaultTalkCsv, $TargetCsv, $DataValue, $TargetColumn, $Header) {
        $DefaultTalk = [];
        foreach(range(0,2) as $b) {
            if (empty($DefaultTalkCsv->at($TargetCsv->at($DataValue)[$TargetColumn])["Text[$b]"])) continue;
            $DefaultTalk[0] = "\n". $Header ." ";
            $DefaultTalk[] = "". $DefaultTalkCsv->at($TargetCsv->at($DataValue)[$TargetColumn])["Text[$b]"];
        }
        return implode($DefaultTalk);
    }

    /**
     * Format dialogue for luasheets
     */
    public function getLuaDialogue($LuaName) { 
        //broke/empty lua files
        $SkipLuaArray = array(
            "CmnGscTripleTriadRoomMove_00371",
            "RegDra2TomestoneWarTrade_00298",
            "RegDra2TomestoneEsotericsTrade_00295",
            "RegDra2TomestoneFolkloreTrade_00333",
            "JobRelAnimaWeaponQuestSelect_00334",
            "ComArmGcArmyMember_00343",
            "ComArmGcArmyCaptureRefund_00436",
            "CmnDefMateriaMeld_00357",
            "CtsHwdDevLevelInvisible_00661",
            "CtsHwdLively_00638"
        );
        if (in_array($LuaName, $SkipLuaArray)){
            return "";
        }
        if (!in_array($LuaName, $SkipLuaArray)){
            $folder = substr(explode('_', $LuaName)[1], 0, 3);
            $textdata = $this->csv("custom/{$folder}/{$LuaName}");
            $LineArray = [];
            $a = (-1);
            $lastcommand = null;
            $AnswerNo1 = 0;
            $AnswerNo2 = 0;
            $AnswerNo3 = 0;
            $ResponseNo = 0;
            foreach ($textdata->data as $i => $entry) {
                $command = $entry['unknown_1'];
                $command = explode('_', $command);
                if (empty($entry['unknown_2'])) continue;
                $Text = $entry['unknown_2'];
                $spacer = "";
                if (empty($command[5])) {
                    if (empty($command[4])) {
                        $lastcommand = $command[3];
                    } else {
                        $lastcommand = $command[4];
                    }
                }
                if (!empty($command[5])) {
                    $lastcommand = $command[5];
                }
                if (!empty($lastcommand)) {
                    if (!is_numeric($lastcommand)){
                        $lastcommand = -1;
                    }
                    if (($lastcommand - 1) != $a) {
                        $spacer = "\n";
                    }
                }
                $a = $lastcommand;
                switch (true) {
                    case (stripos($Text, 'PlayerParameter(71)') !== false): //Player Race
                        $incorrectformattingarray = array("<Switch(PlayerParameter(71))>", "<Case(1)>", "<Case(2)>", "<Case(3)>", "<Case(4)>", "<Case(5)>", "<Case(6)>", "<Case(7)>", "<Case(8)>", "<Case(9)>","</Case>", "</Switch>");
                        $correctformattingarray = array("{{Loremtextconditional|", "", "|or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "", "', depending on Race}}");
                        $Text = str_ireplace($incorrectformattingarray, $correctformattingarray, $Text);
                    break;
                    case (stripos($Text, 'PlayerParameter(70)')): //Town
                        $incorrectformattingarray = array("<Switch(PlayerParameter(70))>", "<Case(1)>", "<Case(2)>", "<Case(3)>", "<Case(4)>", "<Case(5)>", "<Case(6)>", "<Case(7)>", "<Case(8)>", "<Case(9)>","</Case>", "</Switch>");
                        $correctformattingarray = array("{{Loremtextconditional|", "", "|or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "", "', depending on starting town(Limsa Lominsa/Gridania/Ul'dah)}}");
                        $Text = str_ireplace($incorrectformattingarray, $correctformattingarray, $Text);
                    break;
                    case (stripos($Text, 'IntegerParameter(1)') !== false): //unknown
                        $incorrectformattingarray = array("<Switch(IntegerParameter(1))>", "<Case(1)>", "<Case(2)>", "<Case(3)>", "<Case(4)>", "<Case(5)>", "<Case(6)>", "<Case(7)>", "<Case(8)>", "<Case(9)>","</Case>", "</Switch>");
                        $correctformattingarray = array("{{Loremtextconditional|", "", "|or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "", "'}}");
                        $Text = str_ireplace($incorrectformattingarray, $correctformattingarray, $Text);
                    break;
                    
                    default:
                        # code...
                    break;
                }
                switch (true) {
                    case (strpos($command[3], 'Q1') !== false):
                    case (strpos($command[3], 'Q2') !== false):
                    case (strpos($command[3], 'Q3') !== false):
                        $i = 1;
                        $LineArray[] = "|Question $i = $Text";
                    break;
                    case (strpos($command[3], 'A1') !== false):
                        $AnswerNo1 = $AnswerNo1 + 1;
                        $LineArray[] = "|Answer$AnswerNo1 = $Text";
                    break;
                    case (strpos($command[3], 'A2') !== false):
                        $AnswerNo2 = $AnswerNo2 + 1;
                        $LineArray[] = "|Answer$AnswerNo2 = $Text";
                    break;
                    case (strpos($command[3], 'A3') !== false):
                        $AnswerNo3 = $AnswerNo3 + 1;
                        $LineArray[] = "|Answer$AnswerNo3 = $Text";
                    break;
                    
                    default:
                        $LineArray[] = "$spacer$Text";
                    break;
                }

            }
            return implode("\n", $LineArray);
        }
    }

    /**
     * Get Specialshop items and name
     */
    public function getSpecialShop($ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $SpecialShopID) {
        $WeaponArray = [];
        $ArmorArray = [];
        $AccessoryArray = [];
        $OtherArray = [];
        $number = "";
        $Weapons = "";
        $Armor = "";
        $Accessory = "";
        $Other = "";
        foreach(range(0,59) as $specialshopc) {
            if (empty($ItemCsv->at($SpecialShopCsv->at($SpecialShopID)["Item{Cost}[$specialshopc][0]"])['Name'])) continue;
            $ItemInputArray = [];
            $QuestRequired = "";
            if (!empty($QuestCsv->at($SpecialShopCsv->at($SpecialShopID)["Quest{Item}[$specialshopc]"])["Name"])) {
                $QuestRequired = "|Requires Quest = ". $QuestCsv->at($SpecialShopCsv->at($SpecialShopID)["Quest{Item}[$specialshopc]"])["Name"];
            }
            $AchivementRequired = "";
            if (!empty($AchievementCsv->at($SpecialShopCsv->at($SpecialShopID)["AchievementUnlock[$specialshopc]"])["Name"])) {
                $AchivementRequired = "|Requires Achievement = ". $AchievementCsv->at($SpecialShopCsv->at($SpecialShopID)["AchievementUnlock[$specialshopc]"])["Name"];
            }
            $SpecialShopCostArray = [];
            foreach(range(0,2) as $specialshope) {
                if (!empty($ItemCsv->at($SpecialShopCsv->at($SpecialShopID)["Item{Cost}[$specialshopc][$specialshope]"])['Name'])) {
                    $ItemCost = $ItemCsv->at($SpecialShopCsv->at($SpecialShopID)["Item{Cost}[$specialshopc][$specialshope]"])['Name'];
                    $ItemCostAmount = $SpecialShopCsv->at($SpecialShopID)["Count{Cost}[$specialshopc][$specialshope]"];
                    switch ($SpecialShopCsv->at($SpecialShopID)["HQ{Cost}[$specialshopc][$specialshope]"]) {
                        case "True":
                            $ItemCostHQ = "|HQ".($specialshope + 1)."=x";
                        break;
                        case "False":
                            $ItemCostHQ = "";
                        break;
                    }
                    $ItemCostCollectability = "";
                    if ($SpecialShopCsv->at($SpecialShopID)["CollectabilityRating{Cost}[$specialshopc][$specialshope]"] != 0) {
                        $ItemCostCollectability = "|Collectability Rating = ". $SpecialShopCsv->at($SpecialShopID)["CollectabilityRating{Cost}[$specialshopc][$specialshope]"];
                    }
                    $count = $specialshope + 1;
                    $SpecialShopCostArray[] = "|Cost$count=$ItemCost|Count$count=$ItemCostAmount$ItemCostHQ$ItemCostCollectability";
                }
            }
            $SpecialShopCostOutput = implode("", $SpecialShopCostArray);
            foreach(range(0,1) as $specialshopb) {
                if (empty($ItemCsv->at($SpecialShopCsv->at($SpecialShopID)["Item{Receive}[$specialshopc][$specialshopb]"])['Name'])) continue;
                $number = $specialshopc + 1;
                if (!empty($ItemCsv->at($SpecialShopCsv->at($SpecialShopID)["Item{Receive}[$specialshopc][$specialshopb]"])['Name'])) {
                    $ItemReceive =  $ItemCsv->at($SpecialShopCsv->at($SpecialShopID)["Item{Receive}[$specialshopc][$specialshopb]"])['Name'];
                    $ItemReceiveAmount = $SpecialShopCsv->at($SpecialShopID)["Count{Receive}[$specialshopc][$specialshopb]"];
                    switch ($SpecialShopCsv->at($SpecialShopID)["HQ{Receive}[$specialshopc][$specialshopb]"]) {
                        case "True":
                            $ItemReceiveHQ = "|HQItem=x";
                        break;
                        case "False":
                            $ItemReceiveHQ = "";
                        break;
                    }
                    $Category = $SpecialShopCsv->at($SpecialShopID)["SpecialShopItemCategory[$specialshopc][$specialshopb]"];
                    $Additional = "";
                    if ($specialshopb == 1) {
                        $Additional = "|Additional=";
                    }
                    $ItemInputArray[] = "$Additional$ItemReceive$ItemReceiveHQ|Quantity=$ItemReceiveAmount";
                }
            }
            $ItemInput = implode("", $ItemInputArray);
            switch ($Category) {
                case 1:
                    $WeaponArray[] = "{{Sells3|$ItemInput$SpecialShopCostOutput$AchivementRequired$QuestRequired}}";
                break;
                case 2:
                    $ArmorArray[] = "{{Sells3|$ItemInput$SpecialShopCostOutput$AchivementRequired$QuestRequired}}";
                break;
                case 3:
                    $AccessoryArray[] = "{{Sells3|$ItemInput$SpecialShopCostOutput$AchivementRequired$QuestRequired}}";
                break;
                case 4:
                    $OtherArray[] = "{{Sells3|$ItemInput$SpecialShopCostOutput$AchivementRequired$QuestRequired}}";
                break;
            }
        }
        if (!empty($WeaponArray)) {
            $Weapons = "|Weapons = \n". implode("\n", $WeaponArray). "\n";
        }
        if (!empty($ArmorArray)) {
            $Armor = "|Armor = \n".implode("\n", $ArmorArray). "\n";
        }
        if (!empty($AccessoryArray)) {
            $Accessory = "|Accessory = \n".implode("\n", $AccessoryArray). "\n";
        }
        if (!empty($OtherArray)) {
            $Other = "|Misc = \n".implode("\n", $OtherArray). "\n";
        }
        return "|TotalItems = $number\n{{Tabsells3\n$Weapons$Armor$Accessory$Other";
    }

    /**
     * Get input folder
     */
    public function getInputFolder()
    {
        $PatchID = file_get_contents("C:\Program Files (x86)\SquareEnix\FINAL FANTASY XIV - A Realm Reborn\game\\ffxivgame.ver");
        return "F:\Rogue\SaintCoinach.Cmd/$PatchID/ui";
    }

    /**
     * Get output folder
     */
    public function getOutputFolder()
    {
        return $this->projectDirectory . getenv('OUTPUT_DIRECTORY');
    }

    /**
     * Create an inout/output
     */
    public function setInputOutput(InputInterface$input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        return $this;
    }

    /**
     * Save to a file, if chunk size
     */
    public function save($filename, $chunkSize = 200, $dataset = false)
    {
        // create a chunk of data, if chunk size is 0/false we save the entire lot
        $dataset = $dataset ? $dataset : $this->data;
        $dataset = $chunkSize ? array_chunk($dataset, $chunkSize) : [ $dataset ];

        $folder = $this->projectDirectory . getenv('OUTPUT_DIRECTORY');

        // save each chunk
        $info = [];
        foreach ($dataset as $chunkCount => $data) {
            // build folder and filename
            $saveto = "{$folder}/{$filename}";

            // save chunked data
            file_put_contents($saveto, implode("\n", $data));
            $info[] = [
                $saveto,
                count($data),
                filesize($saveto)
            ];
        }

        return $info;
    }

    /**
     * Converts SE icon "number" into a proper path
     */
    private function iconize($number, $hq = false)
    {
        $number = intval($number);
        $extended = (strlen($number) >= 6);

        if ($number == 0) {
            return null;
        }

        // create icon filename
        $icon = $extended ? str_pad($number, 5, "0", STR_PAD_LEFT) : '0' . str_pad($number, 5, "0", STR_PAD_LEFT);

        // create icon path
        $path = [];
        $path[] = $extended ? $icon[0] . $icon[1] . $icon[2] .'000' : '0'. $icon[1] . $icon[2] .'000';

        $path[] = $icon;

        // combine
        $icon = implode('/', $path) .'.png';

        return $icon;
    }
}
