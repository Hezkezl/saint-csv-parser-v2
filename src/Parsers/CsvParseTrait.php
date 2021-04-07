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
        $ini = parse_ini_file('config.ini');
        $MainPath = $ini['MainPath'];
        $PatchID = file_get_contents("". $MainPath ."\game\\ffxivgame.ver");
        $cache = "{$ini['Cache']}/$PatchID/rawexd";

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
     * Generate Patch Json
     */
    public function PatchCheck($PatchNoData, $FileName, $CSV) {
        if (!file_exists("Patch/$FileName.json")) { 
            $MakeFile = fopen("Patch/$FileName.json", 'w');
            fwrite($MakeFile, NULL);
            fclose($MakeFile);
        }
        $jdata = file_get_contents("Patch/$FileName.json");
        $PatchArray = json_decode($jdata, true);
        foreach ($CSV->data as $id => $CsvData) {
            $Key = $CsvData["id"];
            if (empty($Key)) continue;
            $PatchNo = $PatchNoData;
            if (isset($PatchArray[$Key])) continue;
            if (!isset($PatchArray[$Key])) {
                $PatchArray[$Key] = $PatchNo;
            }
        }
        $JSONOUTPUT = json_encode($PatchArray, JSON_PRETTY_PRINT);
        //write Api file
        if (!file_exists("Patch")) { mkdir("Patch", 0777, true); }
        $JSON_File = fopen("Patch/$FileName.json", 'w');
        fwrite($JSON_File, $JSONOUTPUT);
        fclose($JSON_File);
    }
    /**
     * Get Patch Data
     */
    public function getPatch($FileName) {
        if (!file_exists("Patch/$FileName.json")) { 
            $this->io->text(" WARNING: There is no $FileName.json to get patch data from");
            exit();
        }
        if (file_exists("Patch/$FileName.json")) { 
            $jdata = file_get_contents("Patch/$FileName.json");
            $PatchArray = json_decode($jdata, true);
            return $PatchArray;
        }
    }


    /**
     * Format NPC Names for Wiki
     */
    public function NameFormat($NPCID, $ENpcResidentCsv, $ENpcBaseCsv, $PlaceNameLocation) {
        $NameFormatted = $ENpcResidentCsv->at($NPCID)['Singular'];
        $IncorrectNames = array(" De ", " Bas ", " Mal ", " Van ", " Cen ", " Sas ", " Tol ", " Zos ", " Yae ", " The ", " Of The ", " Of ",
            "A-ruhn-senna", "A-towa-cant", "Bea-chorr", "Bie-zumm", "Bosta-bea", "Bosta-loe", "Chai-nuzz", "Chei-ladd", "Chora-kai", "Chora-lue",
            "Chue-zumm", "Dulia-chai", "E-sumi-yan", "E-una-kotor", "Fae-hann", "Hangi-rua", "Hanji-fae", "Kai-shirr", "Kan-e-senna", "Kee-bostt",
            "Kee-satt", "Lewto-sai", "Lue-reeq", "Mao-ladd", "Mei-tatch", "Moa-mosch", "Mosha-moa", "Moshei-lea", "Nunsi-lue", "O-app-pesi", "Qeshi-rae",
            "Rae-qesh", "Rae-satt", "Raya-o-senna", "Renda-sue", "Riqi-mao", "Roi-tatch", "Rua-hann", "Sai-lewq", "Sai-qesh", "Sasha-rae", "Shai-satt",
            "Shai-tistt", "Shee-tatch", "Shira-kee", "Shue-hann", "Sue-lewq", "Tao-tistt", "Tatcha-mei", "Tatcha-roi", "Tio-reeq", "Tista-bie", "Tui-shirr",
            "Vroi-reeq", "Zao-mosc", "Zia-bostt", "Zoi-chorr", "Zumie-moa", "Zumie-shai", "“", "”", "é", "ö");
        $correctnames = array(" de ", " bas ", " mal ", " van ", " cen ", " sas ", " tol ", " zos ", " yae ", " the ", " of the ", " of ",
            "A-Ruhn-Senna", "A-Towa-Cant", "Bea-Chorr", "Bie-Zumm", "Bosta-Bea", "Bosta-Loe", "Chai-Nuzz", "Chei-Ladd", "Chora-Kai", "Chora-Lue",
            "Chue-Zumm", "Dulia-Chai", "E-Sumi-Yan", "E-Una-Kotor", "Fae-Hann", "Hangi-Rua", "Hanji-Fae", "Kai-Shirr", "Kan-E-Senna", "Kee-Bostt",
            "Kee-Satt", "Lewto-Sai", "Lue-Reeq", "Mao-Ladd", "Mei-Tatch", "Moa-Mosch", "Mosha-Moa", "Moshei-Lea", "Nunsi-Lue", "O-App-Pesi", "Qeshi-Rae",
            "Rae-Qesh", "Rae-Satt", "Raya-O-Senna", "Renda-Sue", "Riqi-Mao", "Roi-Tatch", "Rua-Hann", "Sai-Lewq", "Sai-Qesh", "Sasha-Rae", "Shai-Satt",
            "Shai-Tistt", "Shee-Tatch", "Shira-Kee", "Shue-Hann", "Sue-Lewq", "Tao-Tistt", "Tatcha-Mei", "Tatcha-Roi", "Tio-Reeq", "Tista-Bie", "Tui-Shirr",
            "Vroi-Reeq", "Zao-Mosc", "Zia-Bostt", "Zoi-Chorr", "Zumie-Moa", "Zumie-Shai", "\"", "\"", "e", "o");
        //array (Airship_Ticketer,Ala_Mhigan_Resistance_Gate_Guard,Alehouse_Wench,Alisaie%27s_Assistant,Apartment_Caretaker,Arms_Supplier,Arms_Supplier_%26_Mender,Arrivals_Attendant,Calamity_Salvager,Celestine,Chocobokeep,Collectable_Appraiser,Concerned_Mother,Eggsaminer,Enthralling Illusionist (same NPC, just different years),Expedition_Artisan (needs checking 2 if NPCs?),Expedition_Birdwatcher (maybe same NPC just needs copying),Expedition_Scholar (same as above),Faire_Crier (why didn’t this overwrite?),Ferry_Skipper,Flame Officer,Flame Private,Flame Recruit,Flame Scout,Flame Sergeant,Flame Soldier,Gate_Keeper (why didn’t this overwrite?),Gold_Saucer_Attendant (should be 4),Gridanian_Merchant (fix with moves),Haermaga,Housing_Enthusiast,Hunt_Billmaster,Hunter-scholar,Hunter-Scholar,Imperial_Centurion,Imperial_Deserter,Independent_Armorer,Independent_Armorfitter,Independent_Arms_Mender,Independent_Arms_Mender,Independent_Mender,Independent_Merchant,Independent_Sutler,Inu_Doshin,Irate_Coachman (why only 1),Ironworks_Engineer,Ironworks_Engineer (needs attention),Junkmonger,Keeper_of_the_Entwined_Serpents,Local_Merchant,Lonesome_Lass,Long-haired_Pirate (why only 1),Malevolent_Mummer (why only 1),Mammet Dispensator #012P,Mammet Dispensator #012T,Materia_Melder,Material_Supplier,Mender,Minion_Enthusiast (why 1?),Moonfire_Faire_Vendor,Moonfire_Marine,Oic_Administrator,Oic_Officer_of_Arms,Oic_Quartermaster,Pernicious_Temple_Knight,Picker of Locks,Recompense_Officer,Resident_Caretaker,Resistance Fighter,Resistance Officer,Rising_Attendant (needs more investigation),Rising_Vendor (only 1),Royal_Handmaiden,Royal_Seneschal,Royal_Servant,Saint%27s_Little_Helper,Saucer_Attendant (needs more investigation),Scrip_Exchange,Seasoned Adventurer,Serpent Lieutenant,Serpent Officer,Serpent Private,Serpent Recruit,Serpent Scout,Shady_Smock,Splendors_Vendor,Spoils_Collector (needs more investigation),Spoils_Trader,Starlight_Celebrant,Starlight_Celebration_Crier,Starlight_Supplier,Steersman,Storm Captain,Storm Officer,Storm Recruit,Storm Soldier,Storm_Private,Storm_Sergeant (only 3?),Sultansworn_Elite,Suspicious_Coerthan,The_Smith,Tournament_Registrar,Traveling_Merchant,Traveling_Trader,Triple_Triad_Trader,Triple_Triad_Trader,Troubled_Coachman (why 1?),Uncanny_Illusionist,Untrustworthy_Illusionist,Unusual_Illusionist,Well-informed_Adventurer,Wounded Imperial,Wounded Resistance Fighter,Wunthyll,Yellow_Moon_Admirer,Enie)
        $PLAddition = "";
        switch ($NameFormatted) {
            case "airship ticketer";
            case "ala mhigan resistance gate guard";
            case "alehouse wench";
            case "alisaie's assistant";
            case "apartment caretaker";
            case "arms supplier";
            case "arms supplier & mender";
            case "arrivals attendant";
            case "calamity salvager";
            case "celestine";
            case "chocobokeep";
            case "collectable appraiser";
            case "concerned mother";
            case "eggsaminer";
            case "enthralling illusionist";
            case "expedition artisan";
            case "expedition birdwatcher";
            case "expedition scholar";
            case "faire crier";
            case "ferry skipper";
            case "flame officer";
            case "flame private";
            case "flame recruit";
            case "flame scout";
            case "flame sergeant";
            case "flame soldier";
            case "gate keeper";
            case "gold saucer attendant";
            case "gridanian merchant";
            case "haermaga";
            case "housing enthusiast";
            case "hunt billmaster";
            case "hunter-scholar";
            case "hunter-scholar";
            case "imperial centurion";
            case "imperial deserter";
            case "independent armorer";
            case "independent armorfitter";
            case "independent arms mender";
            case "independent arms mender";
            case "independent mender";
            case "independent merchant";
            case "independent sutler";
            case "inu doshin";
            case "irate coachman";
            case "ironworks engineer";
            case "ironworks engineer";
            case "junkmonger";
            case "keeper of the entwined serpents";
            case "local merchant";
            case "lonesome lass";
            case "long-haired pirate";
            case "malevolent mummer";
            case "mammet dispensator #012p";
            case "mammet dispensator #012t";
            case "materia melder";
            case "material supplier";
            case "mender";
            case "minion enthusiast";
            case "moonfire faire vendor";
            case "moonfire marine";
            case "oic administrator";
            case "oic officer of arms";
            case "oic quartermaster";
            case "pernicious temple knight";
            case "picker of locks";
            case "recompense officer";
            case "resident caretaker";
            case "resistance fighter";
            case "resistance officer";
            case "rising attendant";
            case "rising vendor";
            case "royal handmaiden";
            case "royal seneschal";
            case "royal servant";
            case "saint's little helper";
            case "saucer attendant";
            case "scrip exchange";
            case "seasoned adventurer";
            case "serpent lieutenant";
            case "serpent officer";
            case "serpent private";
            case "serpent recruit";
            case "serpent scout";
            case "shady smock";
            case "splendors vendor";
            case "spoils collector";
            case "spoils trader";
            case "starlight celebrant";
            case "starlight celebration crier";
            case "starlight supplier";
            case "steersman";
            case "storm captain";
            case "storm officer";
            case "storm recruit";
            case "storm soldier";
            case "storm private";
            case "storm sergeant";
            case "sultansworn elite";
            case "suspicious coerthan";
            case "the smith";
            case "tournament registrar";
            case "traveling merchant";
            case "traveling trader";
            case "triple triad trader";
            case "troubled coachman";
            case "uncanny illusionist";
            case "untrustworthy illusionist";
            case "unusual illusionist";
            case "well-informed adventurer";
            case "wounded imperial";
            case "wounded resistance fighter";
            case "wunthyll";
            case "Yellow Moon admirer";
            case "Enie";
            case "amarokeep";
                $PLAddition = " ($PlaceNameLocation)";
                if (empty($PlaceNameLocation)){
                    switch ($NameFormatted) {
                        case 'junkmonger': // housing NPCs
                        case 'independent mender':
                        case 'independent merchant':
                        case 'material supplier':
                        case 'mender':
                        case 'materia melder':
                            $PLAddition = "(Housing)";
                        break;
                        case 'storm soldier': //MSQ
                        case 'flame officer':
                        case 'storm officer':
                        case 'serpent officer':
                        case 'serpent scout':
                        case 'flame scout':
                        case 'flame private':
                        case 'flame sergeant':
                        case 'serpent lieutenant':
                        case 'serpent private':
                        case 'storm private':
                        case 'flame soldier':
                        case 'imperial centurion':
                        case 'flame recruit':
                        case 'wounded imperial':
                            $PLAddition = "(MSQ)";
                        break;
                        case 'yellow moon admirer': //Event
                        case "saint's little helper":
                        case 'enthralling illusionist':
                        case 'royal handmaiden':
                        case 'royal seneschal':
                            $PLAddition = "(Event)";
                        break;
                        case 'ferry skipper': //Unknowns
                        case 'chocobokeep':
                        case 'tournament registrar':
                        case 'expedition scholar':
                        case 'traveling merchant':
                        case 'seasoned adventurer':
                        case 'Enie':
                            $PLAddition = "(Unknown)";
                        break;
                        
                        default:
                            # code...
                            break;
                    }
                }
            break;
            
            default:
                # code...
            break;
        }

        //Quest Giver Name (All Words In Name Capitalized)
        $NpcMiqoCheck = $ENpcBaseCsv->at($NPCID)['Race']; //see if miqote
        $NpcName = ucwords(strtolower($ENpcResidentCsv->at($NPCID)['Singular']));
        //this explodes miqote's names into 2 words, capitalizes them and then puts it back together with a hyphen
        if ($NpcMiqoCheck == 4) {
            $NpcName = ucwords(strtolower($ENpcResidentCsv->at($NPCID)['Singular']));
            $NpcName = implode('-', array_map('ucfirst', explode('-', $NpcName)));
        }
        $NameFormatted = str_replace($IncorrectNames, $correctnames, $NpcName)."$PLAddition";
        return $NameFormatted;
    }
    /**
     * Generate ChocoboTaxi Pages
     */
    public function GetChocoboTaxi($ChocoboTaxiStandCsv, $ChocoboTaxiCsv, $FuncDataValue) {
        $Routes = [];
        foreach(range(0,7) as $i) {
            if (empty($ChocoboTaxiStandCsv->at($ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($FuncDataValue)["TargetLocations[$i]"])['Location'])['PlaceName'])) continue;
            $Routes[] = "|Route ". $i ." = ". $ChocoboTaxiStandCsv->at($ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($FuncDataValue)["TargetLocations[$i]"])['Location'])['PlaceName'] ."\n|Route ". $i ." Time = ". $ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($FuncDataValue)["TargetLocations[$i]"])['Fare'] ."\n|Route ". $i ." Cost = ". $ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($FuncDataValue)["TargetLocations[$i]"])['TimeRequired'] ."";
        }
        $RouteOut = implode("\n", $Routes);
        $ChocoboTaxiOut = "\n|Location = ". $ChocoboTaxiStandCsv->at($FuncDataValue)['PlaceName'] ."\n". $RouteOut ."";

        return $ChocoboTaxiOut;
    }
    /**
     * Generate X and Y for LGB/LEVEL Locations
     */
    public function GetLGBPos($x, $y, $TerritoryID, $TerritoryTypeCsv, $MapCsv) {
        $mapLink = $TerritoryTypeCsv->at($TerritoryID)['Map'];
        if (!empty($x)) {
            $scale = $MapCsv->at($mapLink)['SizeFactor'];
        } else {
            $scale = 100;
        }
        $c = $scale / 100.0;
        $offsetx = $MapCsv->at($mapLink)['Offset{X}'];
        $offsetValueX = ($x + $offsetx) * $c;
        if ($c < 1) {
            $c = 1;
        }
        $LocX = round(((41.0 / $c) * (($offsetValueX + 1024.0) / 2048.0) +1), 1);
        $NpcPixelX = round(((($LocX - 1) * 50 * $c) /3.9 + 5), 2);
        $offsety = $MapCsv->at($mapLink)['Offset{Y}'];
        $offsetValueY = ($y + $offsety) * $c;
        $LocY = round(((41.0 / $c) * (($offsetValueY + 1024.0) / 2048.0) +1), 1);
        $NpcPixelZ = round(((($LocY - 1) * 50 * $c) /3.9 + 5), 2);
        $POSArray["X"] = $LocX; 
        $POSArray["Y"] = $LocY; 
        $POSArray["PX"] = $NpcPixelX; 
        $POSArray["PY"] = $NpcPixelZ; 
        return $POSArray;
    }
    /**
     * Generate Triple Triad Pages
     */
    public function GetTripleTriad($ItemCsv, $TripleTriadCardCsv, $TripleTriadCsv, $QuestCsv, $FuncDataValue, $DefaultTalkCsv, $TripleTriadRuleCsv, $NpcName) {
        $RewardsArray = [];
        foreach(range(0,3) as $i) {
            if (empty($ItemCsv->at($TripleTriadCsv->at($FuncDataValue)["Item{PossibleReward}[$i]"])['Name'])) continue;
            $RewardsArray[] = str_replace("&", "and",str_replace(" Card", "", $ItemCsv->at($TripleTriadCsv->at($FuncDataValue)["Item{PossibleReward}[$i]"])['Name']));
        }
        $Rewards = implode(",", $RewardsArray);
        $PreviousQuestsArray = [];
        foreach(range(0,2) as $i) {
            if (empty($QuestCsv->at($TripleTriadCsv->at($FuncDataValue)["PreviousQuest[$i]"])['Name'])) continue;
            $PreviousQuestsArray[] = $QuestCsv->at($TripleTriadCsv->at($FuncDataValue)["PreviousQuest[$i]"])['Name'];
        }
        if (!empty($PreviousQuestsArray)) {
            $PreviousQuests = "\n|Required Quests = ". implode(",", $PreviousQuestsArray);
        }
        else {
            $PreviousQuests = "";
        }
        //TALK
        $TextStringArray = [];     

        foreach(range(0,4) as $a) {
            $Header = "";
            switch ($a) {
                case 0:
                    $ColumnType = "Challenge";
                    $TextFronter = "Challenge";
                break;
                case 1:
                    $ColumnType = "Unavailable";
                    $TextFronter = "Unavailable";
                break;
                case 2:
                    $ColumnType = "NPCWin";
                    $TextFronter = "NPCWin";
                break;
                case 3:
                    $ColumnType = "Draw"; //PCWin
                    $TextFronter = "PCWin";
                break;
                case 4:
                    $ColumnType = "PCWin"; //Draw
                    $TextFronter = "NPCDraw";
                break;
            }
            $TargetColumn = "DefaultTalk{{$ColumnType}}";
            $TextStringArray[] = $this->getDefaultTalk($DefaultTalkCsv, $TripleTriadCsv, $FuncDataValue, $TargetColumn, "\n|$TextFronter = ");
        }
        $TextOutput = implode("", $TextStringArray);

        //UsesCards : 
        
        $UsesCardsFixedArray = [];
        foreach(range(0,4) as $i) {
            if (empty($TripleTriadCardCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadCard{Fixed}[$i]"])["Name"])) continue;
            $UsesCardsFixedArray[] = str_replace("&", "and",$TripleTriadCardCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadCard{Fixed}[$i]"])["Name"]);
        }
        $UsesCardsFixed = implode(",", $UsesCardsFixedArray);

        
        $UsesCardsVariableArray = [];
        foreach(range(0,4) as $i) {
            if (empty($TripleTriadCardCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadCard{Variable}[$i]"])["Name"])) continue;
            $UsesCardsVariableArray[] = str_replace("&", "and",$TripleTriadCardCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadCard{Variable}[$i]"])["Name"]);
        }
        $UsesCardsVariable = implode(",", $UsesCardsVariableArray);

        $Fee = $TripleTriadCsv->at($FuncDataValue)["Fee"];
        
        $RegionalRules = $TripleTriadCsv->at($FuncDataValue)["UsesRegionalRules"];
        
        //RULES
        
        $RulesArray = [];
        foreach(range(0,1) as $i) {
            if (empty($TripleTriadRuleCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadRule[$i]"])["Name"])) continue;
            $RulesArray[] = $TripleTriadRuleCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadRule[$i]"])["Name"];
        }
        $Rules = implode(",", $RulesArray);
        return "{{-start-}}
        '''$NpcName/$FuncDataValue/TripleTriad'''
        {{TripleTriadTemplate
        |Fee = $Fee
        |RegionalRules = $RegionalRules
        |Rules = $Rules
        |Rewards = $Rewards$PreviousQuests
        |Deck = $UsesCardsFixed,$UsesCardsVariable
        $TextOutput
        |Win = 
        |Lose = 
        |Draw = 
        }}
        {{-stop-}}";
    }
    /**
     * Get Default Talk based on variables
     */
    public function getDefaultTalk($DefaultTalkCsv, $TargetCsv, $FuncDataValue, $TargetColumn, $Header) {
        $DefaultTalk = [];
        foreach(range(0,2) as $b) {
            if (empty($DefaultTalkCsv->at($TargetCsv->at($FuncDataValue)[$TargetColumn])["Text[$b]"])) continue;
            $DefaultTalk[0] = "\n". $Header ." ";
            $DefaultTalk[] = "". str_replace("─","-",$DefaultTalkCsv->at($TargetCsv->at($FuncDataValue)[$TargetColumn])["Text[$b]"]);
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
    public function getShop($NpcName, $ShopType, $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $SpecialShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation) {
        $WeaponArray = [];
        $ArmorArray = [];
        $AccessoryArray = [];
        $OtherArray = [];
        $number = "";
        $Weapons = "";
        $Armor = "";
        $Accessory = "";
        $Other = "";
        $ShopOutput = [];
        $NumberItems = 0;
        switch ($ShopType) {
            case 'SpecialShop':  
                $ShopName = $SpecialShopCsv->at($SpecialShopID)["Name"];
                if (empty($ShopName)) { 
                    $ShopName = $SpecialShopID;
                }
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
                $ShopOutputString = "{{-start-}}\n'''". $NpcName ."/". $ShopName ."'''\n";
                $ShopOutputString .= "{{Shop\n";
                $ShopOutputString .= "| Shop Name = $ShopName\n";
                $ShopOutputString .= "| NPC Name = $NpcName\n";
                $ShopOutputString .= "| Location = $NpcPlaceName\n";
                $ShopOutputString .= "| Coordinates = $CoordLocation\n";
                $ShopOutputString .= "| Total Items = $number\n";
                $ShopOutputString .= "| Shop = \n";
                $ShopOutputString .= "{{Tabsells3\n";
                
                $CompleteText = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $SpecialShopID, "CompleteText", "");
                $DenyText = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $SpecialShopID, "NotCompleteText", "");

                $DialogueOutput = "{{-start-}}\n'''". $NpcName ."/Dialogue'''\n";
                $DialogueOutput .= "{{Dialoguebox3|Intro={{check}} Granted Access|Dialogue=$CompleteText}}\n";
                $DialogueOutput .= "{{Dialoguebox3|Intro={{x}} Denied Access|Dialogue=$DenyText}}\n";
                $DialogueOutput .= "{{-stop-}}\n";

                if (empty($CompleteText && $DenyText)) {
                    $DialogueOutput = "";
                }

                $ShopOutput["Dialogue"] = $DialogueOutput;
                $ShopOutput["Number"] = $number;
                $ShopOutput["Shop"] = "\n$ShopOutputString\n$Weapons$Armor$Accessory$Other\n}}\n}}\n{{-stop-}}";
                $ShopOutput["Name"] = $ShopName;
                return $ShopOutput;
            break;
            case 'GilShop':
                $DataValue = $SpecialShopID;
                $GilShopRequiredQuest = "";
                if (!empty($QuestCsv->at($GilShopCsv->at($DataValue)['Quest'])['Name'])) {
                    $GilShopRequiredQuest = "\n|Unlock Quest = ". $QuestCsv->at($GilShopCsv->at($DataValue)['Quest'])['Name'];
                }
                $ShopName = $GilShopCsv->at($DataValue)['Name'];
                if (empty($ShopName)) { 
                    $ShopName = $SpecialShopID;
                }

                
                $CompleteText = $this->getDefaultTalk($DefaultTalkCsv, $GilShopCsv, $SpecialShopID, "AcceptTalk", "");
                $DenyText = $this->getDefaultTalk($DefaultTalkCsv, $GilShopCsv, $SpecialShopID, "FailTalk", "");

                $DialogueOutput = "{{-start-}}\n'''". $NpcName ."/Dialogue'''\n";
                $DialogueOutput .= "{{Dialoguebox3|Intro={{check}} Granted Access|Dialogue=$CompleteText}}\n";
                $DialogueOutput .= "{{Dialoguebox3|Intro={{x}} Denied Access|Dialogue=$DenyText}}\n";
                $DialogueOutput .= "{{-stop-}}\n";

                if (empty($CompleteText && $DenyText)) {
                    $DialogueOutput = "";
                }

                $ShopOutput["Dialogue"] = $DialogueOutput;
                foreach(range(0,50) as $b) {
                    $GilShopSubArray = "". $DataValue . "." . $b ."";
                    if (!empty($ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"])) {
                        $GilShopSellsItem = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"];
                        $GilShopSellsItemCost = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Price{Mid}"];
                        $RowRequiredArray = [];
                        foreach(range(0,2) as $c) {
                            if (empty($QuestCsv->at($GilShopItemCsv->at($GilShopSubArray)["Row{Required}[$c]"])["Name"])) continue;
                            $RequiredQuest = $QuestCsv->at($GilShopItemCsv->at($GilShopSubArray)["Row{Required}[$c]"])["Name"];
                            $RowRequiredArray[] = "|Requires Quest = ". $RequiredQuest;
                        }
                        $NumberItems = $b + 1;
                        $RowRequired = implode("\n", $RowRequiredArray);
                        $CategoryPre = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["EquipSlotCategory"];
                        switch ($CategoryPre) {
                            case '0':
                                $Category = 4;
                            break;
                            case '1':
                            case '2':
                            case '13':
                                $Category = 1;
                            break;
                            case '3':
                            case '4':
                            case '5':
                            case '6':
                            case '7':
                            case '8':
                            case '15':
                            case '16':
                            case '18':
                            case '19':
                            case '20':
                            case '21':
                                $Category = 2;
                            break;
                            case '9':
                            case '10':
                            case '11':
                            case '12':
                                $Category = 3;
                            break;
                            
                            default:
                                $Category = 4;
                            break;
                        }
                        switch ($Category) {
                            case 1:
                                $WeaponArray[] = "{{Sells3|$GilShopSellsItem|Quantity=1|Cost1=Gil|Count1=$GilShopSellsItemCost$RowRequired}}";
                            break;
                            case 2:
                                $ArmorArray[] = "{{Sells3|$GilShopSellsItem|Quantity=1|Cost1=Gil|Count1=$GilShopSellsItemCost$RowRequired}}";
                            break;
                            case 3:
                                $AccessoryArray[] = "{{Sells3|$GilShopSellsItem|Quantity=1|Cost1=Gil|Count1=$GilShopSellsItemCost$RowRequired}}";
                            break;
                            case 4:
                                $OtherArray[] = "{{Sells3|$GilShopSellsItem|Quantity=1|Cost1=Gil|Count1=$GilShopSellsItemCost$RowRequired}}";
                            break;
                        }
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
                $ShopOutputString = "{{-start-}}\n'''". $NpcName ."/". $ShopName ."'''\n";
                $ShopOutputString .= "{{Shop\n";
                $ShopOutputString .= "| Shop Name = $ShopName\n";
                $ShopOutputString .= "| NPC Name = $NpcName\n";
                $ShopOutputString .= "| Location = $NpcPlaceName\n";
                $ShopOutputString .= "| Coordinates = $CoordLocation\n";
                $ShopOutputString .= "| Total Items = $NumberItems\n";
                $ShopOutputString .= "| Shop = \n";
                $ShopOutputString .= "{{Tabsells3\n";
                
                $ShopOutput["Shop"] = "\n$ShopOutputString\n$Weapons$Armor$Accessory$Other\n}}\n}}\n{{-stop-}}";
                $ShopOutput["Number"] = $NumberItems;
                $ShopOutput["Name"] = $ShopName;
                return $ShopOutput;
            break;
            
            default:
                # code...
            break;
        }
    }

    /**
     * Get Equipment for NPCs
     */
    public function getEquipment($EnpcBase, $NpcEquipCsv, $weaponArray, $isMale, $StainCsv, $id, $itemArray)
    {
        foreach(range(0,1) as $a) {
            switch ($a) {
                case 0://mainhand
                    $ENPCOffset0 = "MainHand";
                    $StringOffset0 = "Main Hand";
                break;
                case 1://OffHand
                    $ENPCOffset0 = "OffHand";
                    $StringOffset0 = "Off Hand";
                break;
            }
            $ModelBase = str_replace(", ", "-", $EnpcBase->at($id)["Model{{$ENPCOffset0}}"]);
            $guess = false;
            $Model = false;
            if ($ModelBase == 0) {
                $Model = false;
                if ($NpcEquipCsv->at($EnpcBase->at($id)['NpcEquip'])["Model{{$ENPCOffset0}}"] != 0) {
                    $ModelBase = str_replace(", ", "-", $NpcEquipCsv->at($EnpcBase->at($id)['NpcEquip'])["Model{{$ENPCOffset0}}"]);
                    $ModelBaseDye = $NpcEquipCsv->at($EnpcBase->at($id)['NpcEquip'])["Dye{{$ENPCOffset0}}"];
                }
            }
            if ($ModelBase > 0) {
                $MainModMain = explode("-", $ModelBase);
                $MainModa = $MainModMain[0];
                $MainModb = $MainModMain[1];
                $MainModc = $MainModMain[2];
                $MainModd = $MainModMain[3];
                $MainModMaina = $MainModa;
                if ($MainModMaina < 8999) {
                    $ModelbOrigin = $MainModb;
                    $MainModel = "". $MainModa ."-". $MainModb ."-". $MainModc ."-". $MainModd ."";
                    if (empty($weaponArray[$MainModel]["Name"])) {
                        do {
                            $MainModb--;
                            $MainModel = "". $MainModa ."-". $MainModb ."-". $MainModc ."-". $MainModd ."";
                            $guess = "\n|$StringOffset0 Guess = yes";
                            if ($MainModb < 0) {
                                break;
                            }
                        } while (empty($weaponArray[$MainModel]["Name"]));
                    }
                    if (empty($weaponArray[$MainModel]["Name"])) {
                        do {
                            $MainModb++;
                            $MainModel = "". $MainModa ."-". $MainModb ."-". $MainModc ."-". $MainModd ."";
                            $guess = "\n|$StringOffset0 Guess = yes";
                            if ($MainModb > $ModelbOrigin) {
                                break;
                            }
                        } while (empty($weaponArray[$MainModel]["Name"]));
                    }
                    if ($MainModa < 8999) {
                        if (empty($weaponArray[$MainModel]["Name"])) {
                            $Model = "Custom $StringOffset0";
                        }
                        if (!empty($weaponArray[$MainModel]["Name"])) {
                            if ($MainModb >= 0) {
                                $MainModel = "". $MainModa ."-". $MainModb ."-". $MainModc ."-". $MainModd ."";
                                $Model = "". $weaponArray[$MainModel]["Name"] ."". $guess ."";
                            }
                            if ($MainModb < 0) {
                                $Model = "Custom $StringOffset0";
                            }
                        }
                    }
                }
                if ($MainModa > 8999) {
                    $Model = "Custom $StringOffset0";
                }
            }
            $Output["$ENPCOffset0"]["Item"] = $Model;
        }
        $Visor = $EnpcBase->at($id)['Visor'];
        $Output["Visor"] = $Visor;
        foreach(range(0,4) as $a) {
            switch ($a) {
                case 0://Head
                    $ENPCOffset0 = "Head";
                    $Cat = "34";
                break;
                case 1://Body
                    $ENPCOffset0 = "Body";
                    $Cat = "35";
                break;
                case 2://Hands
                    $ENPCOffset0 = "Hands";
                    $Cat = "37";
                break;
                case 3://Legs
                    $ENPCOffset0 = "Legs";
                    $Cat = "36";
                break;
                case 4://Feet
                    $ENPCOffset0 = "Feet";
                    $Cat = "38";
                break;
            }
            $guess = false;
            $Model = false;
            $Modela = null;
            $Modelb = null;
            $isMale = 
            $Base = $EnpcBase->at($id)["Model{{$ENPCOffset0}}"];
            $DyeBase = $StainCsv->at($EnpcBase->at($id)["Dye{{$ENPCOffset0}}"])['Name'];
            if ($Base == 0) {
                $Model = false;
                if ($NpcEquipCsv->at($EnpcBase->at($id)['NpcEquip'])["Model{{$ENPCOffset0}}"] != 0) {
                    $Base = $NpcEquipCsv->at($EnpcBase->at($id)['NpcEquip'])["Model{{$ENPCOffset0}}"];
                    $DyeBase = $NpcEquipCsv->at($EnpcBase->at($id)['NpcEquip'])["Dye{{$ENPCOffset0}}"];
                }
            }
            if ($Base == 4294967295) {
                $Model = false;
                $Base = 0;
            }
            if ($Base > 0) {
                $Modela = $Base & 0xFFFF;
                if ($Modela < 8999) {
                    $Modelb = ($Base >> 16) & 0xFFFF;
                    $Modelc = ($Base >> 32) & 0xFFFF;
                    $Modeld = ($Base >> 48) & 0xFFFF;
                    $ModelbOrigin = ($Base >> 16) & 0xFFFF;
                    $CompModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                    if (empty($itemArray[$Cat][$CompModel]["Name"])) {
                        $Modelb = $ModelbOrigin;
                        do {
                            $Modelb--;
                            if ($Modelb < 0) {
                                break;
                            }
                            $CompModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|$ENPCOffset0 Guess = yes";
                        } while (empty($itemArray[$Cat][$CompModel]["Name"]));
                    }
                    if (empty($itemArray[$Cat][$CompModel]["Name"])) {
                        do {
                            $Modelb++;
                            $CompModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|$ENPCOffset0 Guess = yes";
                            if ($Modelb > 300) {
                                break;
                            }
                        } while (empty($itemArray[$Cat][$CompModel]["Name"]));
                    }
                    if ($Modela < 8999) {
                        if ($Modelb >= 0) {
                            $CompModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $canWearBool = "";
                            $ItemRestriction = 0;
                            if (!empty($itemArray[$Cat][$CompModel]["EquipRestriction"])){
                                $ItemRestriction = $itemArray[$Cat][$CompModel]["EquipRestriction"];
                                switch ($ItemRestriction) {
                                    case 0:
                                        $canWearBool = "";
                                    break;
                                    case 1:
                                        $canWearBool = ($isMale == "true") ? "" : "\n|Needs Verification = yes";
                                    break;
                                    case 2:
                                        $canWearBool = ($isMale == "true") ? "\n|Needs Verification = yes" : "";
                                    break;
                                    
                                    default:
                                        $canWearBool = "";
                                    break;
                                }
                                $Model = "". $itemArray[$Cat][$CompModel]["Name"] ."". $guess ."";
                            }
                        }
                        if ($Modelb < 0) {
                            $Model = "Custom $ENPCOffset0";
                        }
                    }
                }
                if ($Modela > 8999) {
                    $Model = "Custom $ENPCOffset0";
                }
                if ($a === 2){                        
                    if ($Modela > 8999) {
                        $Model = "Custom Hands";
                    }
                    if ($Modela == 9903) {
                        $Model = false;
                    }
                    if ($Modela == 0) {
                        $Model = false;
                    }
                }
            }
            $Output["$ENPCOffset0"]["Item"] = $Model;
            $Output["$ENPCOffset0"]["Dye"] = $DyeBase;
        }
        return $Output;
    }

    /**
     * Get input folder
     */
    public function getInputFolder()
    {
        $ini = parse_ini_file('config.ini');
        $PatchID = file_get_contents("{$ini['MainPath']}\game\\ffxivgame.ver");
        return "{$ini['SaintPath']}/$PatchID/ui";
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
        $ini = parse_ini_file('config.ini');
        $PatchID = file_get_contents("{$ini['MainPath']}\game\\ffxivgame.ver");
        // create a chunk of data, if chunk size is 0/false we save the entire lot
        $dataset = $dataset ? $dataset : $this->data;
        $dataset = $chunkSize ? array_chunk($dataset, $chunkSize) : [ $dataset ];

        $folder = $this->projectDirectory . getenv('OUTPUT_DIRECTORY');

        // save each chunk
        $info = [];
        foreach ($dataset as $chunkCount => $data) {
            // build folder and filename
            $saveto = "{$folder}/{$PatchID}/{$filename}";

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
