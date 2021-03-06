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
    function is_english($str)
    {
    if (strlen($str) != strlen(utf8_decode($str))) {
        return false;
    } else {
        return true;
    }
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
        switch ($FileName) {
            case 'Item':
                $offset = '"Name"';
            break;
            case 'Quest':
                $offset = '"Name"';
            break;
            case 'ENpcResident':
                $offset = '"Singular"';
            break;
            case 'Description':
                $offset = '"Text[Long]"';
            break;
            
            default:
                $offset = '"id"';
            break;
        }
        $jdata = file_get_contents("Patch/$FileName.json");
        $PatchArray = json_decode($jdata, true);
        foreach ($CSV->data as $id => $CsvData) {
            $Key = $CsvData["id"];
            if (empty($Key)) continue;
            $PatchNo = $PatchNoData;
            if (empty($CsvData[$offset])) continue;
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
     * Get LGB Array
     */
    public function getLGBArray() {
        $ini = parse_ini_file('config.ini');
        $PatchID = file_get_contents("{$ini['MainPath']}\game\\ffxivgame.ver");
        $TerritoryTypeCsv = $this->csv("TerritoryType");
        $QuestCsv = $this->csv("Quest");
        $LevelCsv = $this->csv("Level");
        $ENpcResidentCsv = $this->csv("ENpcResident");
        $MapCsv = $this->csv("Map");
        $PlaceNameCsv = $this->csv("PlaceName");
        //bad subdivisions:
        $BadSubs = array("192", "193", "194", "365");
        //levellocations:
        $this->io->text('Producing lists of wiki correct names, This may take a while initially ...');
        foreach($TerritoryTypeCsv->data as $id => $TerritoryTypeData) {
            $JSONMapRangeArray = [];
            $code = substr($TerritoryTypeData['Bg'], -4);
            if (file_exists('cache/'. $PatchID .'/lgb/'. $code .'_planmap.lgb.json')) {
                $url = 'cache/'. $PatchID .'/lgb/'. $code .'_planmap.lgb.json';
                $jdata = file_get_contents($url);
                $decodeJdata = json_decode($jdata);
                foreach ($decodeJdata as $lgb) {
                    $InstanceObjects = $lgb->InstanceObjects;
                    foreach($InstanceObjects as $Object) {
                        $AssetType = $Object->AssetType;
                        if ($AssetType != 43) continue;
                        if ($Object->Object->PlaceNameEnabled == 0) continue;
                        $x = $Object->Transform->Translation->x;
                        $y = $Object->Transform->Translation->z;
                        $NpcLocX = $this->GetLGBPos($x, $y, $id, $TerritoryTypeCsv, $MapCsv)["X"];
                        $NpcLocY = $this->GetLGBPos($x, $y, $id, $TerritoryTypeCsv, $MapCsv)["Y"];
                        $PlaceName = $PlaceNameCsv->at($Object->Object->PlaceNameSpot)['Name'];
                        if (empty($PlaceName)) {
                            $PlaceName = $PlaceNameCsv->at($Object->Object->PlaceNameBlock)['Name'];
                        }
                        if (stripos($PlaceName, "Subdivision")) continue;
                        $JSONMapRangeArray[] = array(
                            'placename' => $PlaceName,
                            'x' => $NpcLocX,
                            'y' => $NpcLocY,
                            'code' => $code,
                            'id' => $id
                        );
                    }
                }
                $JSONTeriArray[$id] = $JSONMapRangeArray;
            }
        }
        foreach($QuestCsv->data as $id => $Quest) {
            if ($Quest['Festival'] === "0") continue;
            $QuestFestival = $Quest['Festival'];
            $Issuer = $Quest['Issuer{Start}'];
            if (!empty($ENpcResidentCsv->at($Issuer)['Singular'])) {
                $NpcFestivalQuestArray[$Issuer] = $QuestFestival;
            }
            foreach(range(0,49) as $i) {
                $Npc = $Quest["Script{Arg}[$i]"];
                if (($Npc > 1000000) && ($Npc < 1100000)) {
                    if (empty($ENpcResidentCsv->at($Npc)['Singular'])) continue;
                    $NpcFestivalQuestArray[$Npc] = $QuestFestival; 
                }
            }
            foreach(range(0,63) as $i) {
                $Npc = $Quest["Listener[$i]"];
                if (($Npc > 1000000) && ($Npc < 1100000)) {
                    if (empty($ENpcResidentCsv->at($Npc)['Singular'])) continue;
                    $NpcFestivalQuestArray[$Npc] = $QuestFestival; 
                }
            }
        }

        $Festivaljdata = file_get_contents("Patch/FestivalNames.json");
        $FestivalArray = json_decode($Festivaljdata, true);
        //gather lgb from LGB.json
        foreach ($TerritoryTypeCsv->data as $id => $teri) {  
            $code = $teri['Name'];
            if (empty($code)) continue;
            foreach(range(0,3) as $range) {
                if ($range == 0) {
                    $url = "cache/$PatchID/lgb/". $code ."_planlive.lgb.json";
                } elseif ($range == 1) {
                    $url = "cache/$PatchID/lgb/". $code ."_planevent.lgb.json";
                } elseif ($range == 2) {
                    $url = "cache/$PatchID/lgb/". $code ."_planmap.lgb.json";
                } elseif ($range == 3) {
                    $url = "cache/$PatchID/lgb/". $code ."_planner.lgb.json";
                }
                if (!file_exists($url)) continue;
                $jdata = file_get_contents($url);
                $decodeJdata = json_decode($jdata);
                $Festival = 0;
                foreach ($decodeJdata as $lgb) {
                    $LayerID = $lgb->LayerId;
                    $Name = $lgb->Name;
                    $InstanceObjects = $lgb->InstanceObjects;
                    $Festival = $lgb->FestivalID;
                    $AssetType = "";
                    foreach($InstanceObjects as $Object) {
                        $AssetType = $Object->AssetType;
                        $InstanceID = "";
                        if (!empty($Object->InstanceId)) {
                            $InstanceID = $Object->InstanceId;
                        }
                        $BaseId = "";
                        $x = "";
                        $y = "";
                        if ($AssetType == 8) {
                            $BaseId = "". $Object->Object->ParentData->ParentData->BaseId ."";
                            $x = $Object->Transform->Translation->x;
                            $y = $Object->Transform->Translation->z;
                            $NPCID = $BaseId;
                            if (empty($NpcFestivalQuestArray[$NPCID])){
                                if(!empty($Festival)){
                                    $NpcFestivalQuestArray[$NPCID] = $Festival;
                                }
                            }
                            if (!empty($NpcFestivalQuestArray[$NPCID])){
                                if ($Festival === "0") {
                                    $Festival = $NpcFestivalQuestArray[$NPCID];
                                }
                            }
                            $LGBArray[$NPCID] = array(
                                'Territory' => $id,
                                'x' => $x,
                                'y' => $y,
                                'id' => $InstanceID,
                                'festivalID' => $Festival,
                                'festivalName' => $Name
                            );
                        }
                    }
                }
            }
        }

        
        //gather lgb from level.exd
        foreach($LevelCsv->data as $id => $Level) {
            if ($Level['Type'] != 8) continue;
            $NPCID = $Level['Object'];
            $Festival = 0;
            if (!empty($NpcFestivalQuestArray[$NPCID])){
                $Festival = $NpcFestivalQuestArray[$NPCID];
            }
            $Name = "";
            if (in_array($Level['Map'], $BadSubs)) continue;
            $LGBArray[$NPCID] = array(
                'Territory' => $Level['Territory'],
                'x' => $Level['X'],
                'y' => $Level['Z'],
                'id' => $id,
                'festivalID' => $Festival,
                'festivalName' => $Name
            );
        }
        foreach ($ENpcResidentCsv->data as $id => $NPCs) {
            $subLocation = "";
            $keyarray = [];
            $Zone = "";
            if (!empty($LGBArray[$id]['Territory'])){
                $Territory = $LGBArray[$id]['Territory'];
                $Zone = $PlaceNameCsv->at($TerritoryTypeCsv->at($Territory)['PlaceName'])['Name'];
                //$X = $LGBArray[$id]['x'];
                //$Y = $LGBArray[$id]['y'];
                $X = $this->GetLGBPos($LGBArray[$id]['x'], $LGBArray[$id]['y'], $LGBArray[$id]['Territory'], $TerritoryTypeCsv, $MapCsv)["X"];
                $Y = $this->GetLGBPos($LGBArray[$id]['x'], $LGBArray[$id]['y'], $LGBArray[$id]['Territory'], $TerritoryTypeCsv, $MapCsv)["Y"];
                foreach (range(0, 1000) as $i) {
                    if (empty($JSONTeriArray[$Territory][$i]["x"])) break;
                    $calcA = ($X - $JSONTeriArray[$Territory][$i]["x"]); 
                    $calcB = ($Y - $JSONTeriArray[$Territory][$i]["y"]);
                    $calcX = $calcA * $calcB;
                    $keyarray[] = abs($calcX);
                }
                asort($keyarray);
                $smallestNumber = key($keyarray);
                if (empty($JSONTeriArray[$Territory][$smallestNumber]["placename"])) {
                    $subLocation = "";
                } else {
                    $subLocation = $JSONTeriArray[$Territory][$smallestNumber]["placename"];
                }
                if (empty($subLocation)){
                    $subLocation = $PlaceNameCsv->at($TerritoryTypeCsv->at($Territory)['PlaceName'])['Name'];
                }
            }
            $LGBArray["PlaceName"][$id]["PlaceName"] = str_replace("#","",$subLocation);
            $LGBArray["PlaceName"][$id]["Territory"] = str_replace("#","",$Zone);
        }
        $this->io->text("Done.");
        
        return $LGBArray;
    }

    /**
     * produce a list of potential npc errors
     */
    public function NameChecker($EventItemCsv, $ItemCsv) {
        
        $NameArray = [];
        foreach($EventItemCsv->data as $id => $EItem) {
            if (empty($EItem['Name'])) continue;
            $Name = strtolower($EItem['Name']);
            $NameArray[$Name] = "0";
        }
        foreach($ItemCsv->data as $id => $Item) {
            if (empty($Item['Name'])) continue;
            $Name = strtolower($Item['Name']);
            $NameArray[$Name] = "0";
        }

        return $NameArray;
    }

    /**
     * Format NPC Names for Wiki
     */
    public function NameFormat($NPCID, $ENpcResidentCsv, $ENpcBaseCsv, $NpcLocationArray, $LGBArray, $BadNames) {
        $NPCSwitch = "";
        // loop through our sequences
        $Festivaljdata = file_get_contents("Patch/FestivalNames.json");
        $FestivaldecodeJdata = json_decode($Festivaljdata, true);        
        $NameFormatted = $ENpcResidentCsv->at($NPCID)['Singular'];
        $PlaceNameLocation = $NpcLocationArray['PlaceName'];
        $ZoneLocation = $NpcLocationArray['Territory'];
        $IncorrectNames = array(" De ", " Bas ", " Mal ", " Van ", " Cen ", " Sas ", " Tol ", " Zos ", " Yae ", " The ", " Of The ", " Of ",
            "A-ruhn-senna", "A-towa-cant", "Bea-chorr", "Bie-zumm", "Bosta-bea", "Bosta-loe", "Chai-nuzz", "Chei-ladd", "Chora-kai", "Chora-lue",
            "Chue-zumm", "Dulia-chai", "E-sumi-yan", "E-una-kotor", "Fae-hann", "Hangi-rua", "Hanji-fae", "Kai-shirr", "Kan-e-senna", "Kee-bostt",
            "Kee-satt", "Lewto-sai", "Lue-reeq", "Mao-ladd", "Mei-tatch", "Moa-mosch", "Mosha-moa", "Moshei-lea", "Nunsi-lue", "O-app-pesi", "Qeshi-rae",
            "Rae-qesh", "Rae-satt", "Raya-o-senna", "Renda-sue", "Riqi-mao", "Roi-tatch", "Rua-hann", "Sai-lewq", "Sai-qesh", "Sasha-rae", "Shai-satt",
            "Shai-tistt", "Shee-tatch", "Shira-kee", "Shue-hann", "Sue-lewq", "Tao-tistt", "Tatcha-mei", "Tatcha-roi", "Tio-reeq", "Tista-bie", "Tui-shirr",
            "Vroi-reeq", "Zao-mosc", "Zia-bostt", "Zoi-chorr", "Zumie-moa", "Zumie-shai", "“", "”", "é", "ö", "�", "#", "—");
        $correctnames = array(" de ", " bas ", " mal ", " van ", " cen ", " sas ", " tol ", " zos ", " yae ", " the ", " of the ", " of ",
            "A-Ruhn-Senna", "A-Towa-Cant", "Bea-Chorr", "Bie-Zumm", "Bosta-Bea", "Bosta-Loe", "Chai-Nuzz", "Chei-Ladd", "Chora-Kai", "Chora-Lue",
            "Chue-Zumm", "Dulia-Chai", "E-Sumi-Yan", "E-Una-Kotor", "Fae-Hann", "Hangi-Rua", "Hanji-Fae", "Kai-Shirr", "Kan-E-Senna", "Kee-Bostt",
            "Kee-Satt", "Lewto-Sai", "Lue-Reeq", "Mao-Ladd", "Mei-Tatch", "Moa-Mosch", "Mosha-Moa", "Moshei-Lea", "Nunsi-Lue", "O-App-Pesi", "Qeshi-Rae",
            "Rae-Qesh", "Rae-Satt", "Raya-O-Senna", "Renda-Sue", "Riqi-Mao", "Roi-Tatch", "Rua-Hann", "Sai-Lewq", "Sai-Qesh", "Sasha-Rae", "Shai-Satt",
            "Shai-Tistt", "Shee-Tatch", "Shira-Kee", "Shue-Hann", "Sue-Lewq", "Tao-Tistt", "Tatcha-Mei", "Tatcha-Roi", "Tio-Reeq", "Tista-Bie", "Tui-Shirr",
            "Vroi-Reeq", "Zao-Mosc", "Zia-Bostt", "Zoi-Chorr", "Zumie-Moa", "Zumie-Shai", "\"", "\"", "e", "o", "", "", "");
        $PLAddition = "";
        $NameFormattedLower = strtolower($NameFormatted);
        if (isset($BadNames[$NameFormattedLower])){
            $NPCSwitch = " (NPC)";
        }
        switch ($NameFormatted) {
            case "airship ticketer":
            case "Ala Mhigan Resistance gate guard":
            case "alehouse wench":
            case "Alisaie's assistant":
            case "apartment caretaker":
            case "arms supplier":
            case "arms supplier & mender":
            case "arrivals attendant":
            case "calamity salvager":
            case "Celestine":
            case "chocobokeep":
            case "collectable appraiser":
            case "concerned mother":
            case "ferry skipper":
            case "flame officer":
            case "flame private":
            case "flame recruit":
            case "flame scout":
            case "flame sergeant":
            case "flame soldier":
            case "gate keeper":
            case "Gridanian merchant":
            case "Haermaga":
            case "housing enthusiast":
            case "Hunt billmaster":
            case "hunter-scholar":
            case "hunter-scholar":
            case "Imperial centurion":
            case "Imperial deserter":
            case "independent armorer":
            case "independent armorfitter":
            case "independent arms mender":
            case "independent arms mender":
            case "independent mender":
            case "independent merchant":
            case "independent sutler":
            case "inu doshin":
            case "irate coachman":
            case "Ironworks engineer":
            case "junkmonger":
            case "Keeper of the Entwined Serpents":
            case "local merchant":
            case "materia melder":
            case "material supplier":
            case "mender":
            case "minion enthusiast":
            case "OIC administrator":
            case "OIC officer of arms":
            case "OIC quartermaster":
            case "pernicious Temple Knight":
            case "picker of locks":
            case "recompense officer":
            case "Resident caretaker":
            case "Resistance fighter":
            case "Resistance officer":
            case "Saucer attendant":
            case "scrip exchange":
            case "seasoned adventurer":
            case "serpent lieutenant":
            case "serpent officer":
            case "serpent private":
            case "serpent recruit":
            case "serpent scout":
            case "spoils trader":
            case "steersman":
            case "storm captain":
            case "storm officer":
            case "storm recruit":
            case "storm soldier":
            case "Sultansworn elite":
            case "suspicious Coerthan":
            case "the Smith":
            case "tournament registrar":
            case "traveling merchant":
            case "traveling trader":
            case "Triple Triad trader":
            case "troubled coachman":
            case "wounded imperial":
            case "wounded Resistance fighter":
            case "wunthyll":
            case "amarokeep":
            case "merchant & mender":
            case "Calamity salvager":
            case "estate manservant":
            case "estate maidservant":
            case "hokonin":
            case "journeyman salvager":
            case "Rowena's representative":
            case "Allagan commerce node":
            case "Ananta junkmonger":
            case "Namazu junkmonger":
            case "falcon porter":
            case "housing merchant":
                $PLAddition = " ($PlaceNameLocation)";
                if (empty($PlaceNameLocation)){
                    switch ($NameFormatted) {
                        case 'junkmonger': // housing NPCs
                        case 'independent mender':
                        case 'independent merchant':
                        case 'material supplier':
                        case 'mender':
                        case 'materia melder':
                        case "estate manservant":
                        case "estate maidservant":
                        case "hokonin":
                        case "journeyman salvager":
                        case "Allagan commerce node":
                        case "Ananta junkmonger":
                        case "Namazu junkmonger":
                        case "housing merchant":
                            $PLAddition = " (Player Housing)";
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
                        case 'flame soldier':
                        case "Imperial centurion":
                        case 'flame recruit':
                        case 'wounded imperial':
                            $PLAddition = " (MSQ)";
                        break;
                         //Event
                            $PLAddition = " (Event)";
                        break;
                        case 'ferry skipper': //Unknowns
                        case 'chocobokeep':
                        case 'tournament registrar':
                        case 'expedition scholar':
                        case 'traveling merchant':
                        case 'seasoned adventurer':
                        case "journeyman salvager":
                        case "Sultansworn elite":
                        case "suspicious Coerthan":
                        case "Keeper of the Entwined Serpents":
                        case "Saucer attendant":
                        case "Haermaga":
                        case "Resistance fighter":
                        case "Sultansworn Elite":
                            $PLAddition = " (Unknown)";
                        break;
                        
                        default:
                            $PLAddition = " (Unknown)";
                        break;
                    }
                }
            break;
            case "saint's little helper":
            case 'enthralling illusionist':
            case 'royal seneschal':
            case "uncanny illusionist": // event:
            case "untrustworthy illusionist":
            case "unusual illusionist":
            case "Yellow Moon admirer":
            case "royal seneschal":
            case "royal servant":
            case "saint's little helper":
            case "Moonfire marine":
            case "Moonfire Faire chaperone":
            case "enthralling illusionist":
            case "Faire crier":
            case "unsavory illusionist":
            case "malevolent mummer":
            case "long-haired pirate":
            case "Gold Saucer attendant":
            case 'Yellow Moon admirer':
            case 'tournament registrar':
            case 'Little Yang':
            case 'Little Yin':
            case 'inu doshin':
            case 'inu doshin':
            case 'Ethelia':
            case 'campaign attendant':
            case 'well-informed adventurer':
            case 'House Valentione maid':
            case 'House Valentione maidservant':
            case 'magic pot':
            case 'minion enthusiast':
                $PLAddition = " (Event)";
                if (!empty($PlaceNameLocation)) {
                    if (!empty($LGBArray[$NPCID]['festivalID'])) {
                        $FestivalNameAddition = $LGBArray[$NPCID]['festivalID'];
                        $splitfes = explode("_", $FestivaldecodeJdata[$FestivalNameAddition]);
                        $fesYear = $splitfes[1];
                        $PLAddition = " ($splitfes[1]) ($PlaceNameLocation)";
                    }
                }
                if (empty($PlaceNameLocation)){
                    if (!empty($LGBArray[$NPCID]['festivalID'])) {
                        $FestivalNameAddition = $LGBArray[$NPCID]['festivalID'];
                        $splitfes = explode("_", $FestivaldecodeJdata[$FestivalNameAddition]);
                        $fesYear = $splitfes[1];
                        $PLAddition = " ($splitfes[1])";
                    }
                }
            break;
            //title 
            case "storm sergeant":
            case "storm private":
                if (!empty($ENpcResidentCsv->at($NPCID)['Title'])){
                    $Title = $ENpcResidentCsv->at($NPCID)['Title'];
                    $PLAddition = " ($Title)";
                }
            break;
            //just set year
            case "Rising vendor":
            case "Moonfire Faire vendor":
            case 'royal handmaiden':
            case "shady smock":
                if (!empty($LGBArray[$NPCID]['festivalID'])){
                    $FestivalNameAddition = $LGBArray[$NPCID]['festivalID'];
                    $splitfes = explode("_", $FestivaldecodeJdata[$FestivalNameAddition]);
                    $fesYear = $splitfes[1];
                    $PLAddition = " ($splitfes[1])";
                }
            break; 
                //Set placename to zone
            case "Starlight celebrant":
            case "Starlight Celebration crier":
            case "Starlight supplier":
            case "Rising attendant":
            case "resident caretaker":
            case "splendors vendor":
            case "expedition birdwatcher":
            case "expedition artisan":
            case "expedition scholar":
            case "spoils collector":
            case "spoils collector":
            case "Resistance supplier":
            case "recompense officer":
                $oldzone = array("Ul'dah - Steps of Nald",
                "Limsa Lominsa Upper Decks",
                "Old Gridania",
                "Shirogane Shores",
                "The Lavender Beds",
                "Limsa Lominsa Lower Decks",
                "Eureka "
                );
                $newzone = array("Ul'dah",
                "Limsa Lominsa",
                "Gridania",
                "Shirogane",
                "Lavender Beds",
                "Limsa Lominsa",
                ""
                );
                if (!empty($ZoneLocation)) {
                    if (!empty($LGBArray[$NPCID]['festivalID'])) {
                        $FestivalNameAddition = $LGBArray[$NPCID]['festivalID'];
                        $splitfes = explode("_", $FestivaldecodeJdata[$FestivalNameAddition]);
                        $fesYear = $splitfes[1];
                        $PLAddition = " ($splitfes[1]) (".str_ireplace($oldzone, $newzone, $ZoneLocation).")";
                    }
                    if (empty($LGBArray[$NPCID]['festivalID'])) {;
                        $PLAddition = " (".str_ireplace($oldzone, $newzone, $ZoneLocation).")";
                    }
                }
                if (empty($ZoneLocation)){
                    if (!empty($LGBArray[$NPCID]['festivalID'])) {
                        $FestivalNameAddition = $LGBArray[$NPCID]['festivalID'];
                        $splitfes = explode("_", $FestivaldecodeJdata[$FestivalNameAddition]);
                        $fesYear = $splitfes[1];
                        $PLAddition = " ($splitfes[1])";
                    }
                }
            break;
            case "eggsaminer":
                $RaceCsv = $this->csv('Race');
                $nameRace = $RaceCsv->at($ENpcBaseCsv->at($NPCID)['Race'])['Masculine'];
                $PLAddition = " ($nameRace)";
            break;
            case "Hydaelyn":
            case "Celestine ":
            case "giant beaver ":
            case "Komainu":
            case "Lightning":
            case "tender lamb":
                $PLAddition = " (NPC)";
            break;            
            default:
                # code...
            break;
        }

        //Quest Giver Name (All Words In Name Capitalized)
        $NpcMiqoCheck = $ENpcBaseCsv->at($NPCID)['Race']; //see if miqote
        //this explodes miqote's names into 2 words, capitalizes them and then puts it back together with a hyphen
        if ($NpcMiqoCheck == 4) {
            $NameFormatted = implode('-', array_map('ucfirst', explode('-', $NameFormatted)));
        }
        $NameFormatted = ucwords($NameFormatted);
        $NameFormatted = str_ireplace($IncorrectNames, $correctnames, $NameFormatted);
        $NameFormatted = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $NameFormatted);
        $NameFormatted = str_ireplace($IncorrectNames, $correctnames, $NameFormatted);
        $output['Name'] = $NameFormatted."$PLAddition$NPCSwitch";
        $output['IsEnglish'] = $this->is_english($NameFormatted);
        if (stripos($NameFormatted,"�")){
            $output['IsEnglish'] = false; 
        }
        $KillArray = array("Â", "Â (Unknown)", "—", "(A", "(B", "(Unknown)", "A", "B", "C", "D", "E", ":", "—C()", "—D()", "—()", "Â()");
        if (in_array($NameFormatted, $KillArray)) {
            $output['IsEnglish'] = false;
        }
        return $output;
    }
    /**
     * Generate ChocoboTaxi Pages
     */
    public function GetChocoboTaxi($ChocoboTaxiStandCsv, $ChocoboTaxiCsv, $FuncDataValue, $NameFormatted) {
        $Routes = [];
        foreach(range(0,7) as $i) {
            if (empty($ChocoboTaxiStandCsv->at($ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($FuncDataValue)["TargetLocations[$i]"])['Location'])['PlaceName'])) continue;
            $Routes[] = "|Route ". $i ." = ". $ChocoboTaxiStandCsv->at($ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($FuncDataValue)["TargetLocations[$i]"])['Location'])['PlaceName'] ."\n|Route ". $i ." Time = ". $ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($FuncDataValue)["TargetLocations[$i]"])['Fare'] ."\n|Route ". $i ." Cost = ". $ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($FuncDataValue)["TargetLocations[$i]"])['TimeRequired'] ."";
        }
        $RouteOut = implode("\n", $Routes);
        $ChocoboTaxiOut = "\n|Location = ". $ChocoboTaxiStandCsv->at($FuncDataValue)['PlaceName'] ."\n". $RouteOut ."";
        
        $PorterOut = "{{-start-}}
        '''". $NameFormatted ."/". $FuncDataValue ."/Porter'''
        {{Porter\n$ChocoboTaxiOut
        }}
        {{-stop-}}";

        return $PorterOut;
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
        $Resident = $this->csv('TripleTriadResident');
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
        

        //unknowns 
        $UnknownValue1 = 0;
        if(!empty($Resident->at($FuncDataValue)['unknown_1'])){
            $UnknownValue1 = $Resident->at($FuncDataValue)['unknown_1'];
        }
        //}
        /**
        $Unknown2 = $TripleTriadCsv->at($FuncDataValue)["unknown_26"];
        $UnknownArray = [];
        foreach(range(0,1) as $e) {
            if (empty($TripleTriadRuleCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadRule[$e]"])["Name"])) continue;
            $UnknownArray[] = "3 -> ". $TripleTriadRuleCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadRule[$e]"])["unknown_3"]."\n";
            $UnknownArray[] = "4 -> ". $TripleTriadRuleCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadRule[$e]"])["unknown_4"]."\n";
            $UnknownArray[] = "5 -> ". $TripleTriadRuleCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadRule[$e]"])["Unknown[5-4]"]."\n";
            $UnknownArray[] = "6 ->". $TripleTriadRuleCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadRule[$e]"])["unknown_6"]."\n";
            $UnknownArray[] = "7 ->". $TripleTriadRuleCsv->at($TripleTriadCsv->at($FuncDataValue)["TripleTriadRule[$e]"])["unknown_7"]."\n";
        }
        $UnknownRules = implode("", $UnknownArray);
        //RULES
        /**
        TripleTriadResident ->
        |Unknown 1 = $UnknownValue1
        TripleTriad ->
        |Unknown 2 = $Unknown2
        Rule = 
        |Unknown Rules = $UnknownRules
         */
        
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
    public function getShop($NpcName, $ShopType, $ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $SpecialShopID, $DefaultTalkCsv, $GilShopCsv, $GilShopItemCsv, $NpcPlaceName, $CoordLocation,$TopicSelect) {
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
        $oldarray = array("/",",",);
        $newarray = array(" and ","");
        $TopicSelectName = "";
        if (!empty($TopicSelect)){
            $TopicSelectName = "$TopicSelect - ";
        }
        switch ($ShopType) {
            case 'SpecialShop':  
                $ShopName = str_ireplace($oldarray,$newarray,"$TopicSelectName".$SpecialShopCsv->at($SpecialShopID)["Name"]);
                if (empty($ShopName)) { 
                    $ShopName = $SpecialShopID;
                }
                $QuestUnlock = "";
                if (!empty($QuestCsv->at($SpecialShopCsv->at($SpecialShopID)["Quest{Unlock}"])['Name'])) {
                    $QuestUnlock = "| Requires Quest = ". $QuestCsv->at($SpecialShopCsv->at($SpecialShopID)["Quest{Unlock}"])['Name']."\n";
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
                            $AdditionalQty = "";
                            if ($specialshopb == 1) {
                                $Additional = "|Additional=";
                                $AdditionalQty = "Additional";
                            }
                            $ItemInputArray[] = "$Additional$ItemReceive$ItemReceiveHQ|".$AdditionalQty."Quantity=$ItemReceiveAmount";
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
                        case 0:
                        case 4:
                            $OtherArray[] = "{{Sells3|$ItemInput$SpecialShopCostOutput$AchivementRequired$QuestRequired}}";
                        break;
                        default:
                            $OtherArray[] = "{{Sells3|$ItemInput$SpecialShopCostOutput$AchivementRequired$QuestRequired}}";
                        break;
                    }
                }
                asort($WeaponArray);
                asort($ArmorArray);
                asort($AccessoryArray);
                asort($OtherArray);
                if (!empty($WeaponArray)) {
                    $Weapons = "|Weapons = \n". implode("\n", str_ireplace("&","and", $WeaponArray)). "\n";
                }
                if (!empty($ArmorArray)) {
                    $Armor = "|Armor = \n".implode("\n", str_ireplace("&","and", $ArmorArray)). "\n";
                }
                if (!empty($AccessoryArray)) {
                    $Accessory = "|Accessory = \n".implode("\n", str_ireplace("&","and", $AccessoryArray)). "\n";
                }
                if (!empty($OtherArray)) {
                    $Other = "|Misc = \n".implode("\n", str_ireplace("&","and", $OtherArray)). "\n";
                }
                $ShopOutputString = "{{-start-}}\n'''". $NpcName ."/". $ShopName ."'''\n";
                $ShopOutputString .= "{{Shop\n";
                $ShopOutputString .= "| Shop Name = $ShopName\n";
                $ShopOutputString .= "| NPC Name = $NpcName\n";
                $ShopOutputString .= "| Location = $NpcPlaceName\n";
                $ShopOutputString .= "| Coordinates = $CoordLocation\n";
                $ShopOutputString .= "| Total Items = $number\n";
                $ShopOutputString .= "$QuestUnlock";
                $ShopOutputString .= "| Shop = \n";
                $ShopOutputString .= "{{Tabsells3\n";
                
                $CompleteText = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $SpecialShopID, "CompleteText", "");
                $DenyText = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $SpecialShopID, "NotCompleteText", "");

                $DialogueOutput = "{{-start-}}\n'''". $NpcName ."/Dialogue/Dialogue'''\n";
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
            case 'CollectablesShop':  
                $CollectablesShopCsv = $this->csv('CollectablesShop');
                $CollectablesShopItemCsv = $this->csv('CollectablesShopItem');
                $CollectablesShopRewardItemCsv = $this->csv('CollectablesShopRewardItem');
                $CollectablesShopRewardScripCsv = $this->csv('CollectablesShopRewardScrip');
                $CollectablesShopRefineCsv = $this->csv('CollectablesShopRefine');
                $CollectablesShopItemGroupCsv = $this->csv('CollectablesShopItemGroup');
                $CurrencyArray = $this->GetCurrency();
                $QuestRequired = "";
                if (!empty($QuestCsv->at($CollectablesShopCsv->at($SpecialShopID)["Quest"])["Name"])) {
                    $QuestRequired = "| Requires Quest = ". $QuestCsv->at($CollectablesShopCsv->at($SpecialShopID)["Quest"])["Name"];
                }

                $ShopName = str_ireplace($oldarray,$newarray,"$TopicSelectName".$CollectablesShopCsv->at($SpecialShopID)["Name"]);
                if (empty($ShopName)) { 
                    $ShopName = $SpecialShopID;
                }
                foreach (range(0, 10) as $i) {
                    switch($i) {
                        case 0:
                            $Class = "Carpenter";
                        break;
                        case 1:
                            $Class = "Blacksmith";
                        break;
                        case 2:
                            $Class = "Armorer";
                        break;
                        case 3:
                            $Class = "Goldsmith";
                        break;
                        case 4:
                            $Class = "Leatherworker";
                        break;
                        case 5:
                            $Class = "Weaver";
                        break;
                        case 6:
                            $Class = "Alchemist";
                        break;
                        case 7:
                            $Class = "Culinarian";
                        break;
                        case 8:
                            $Class = "Miner";
                        break;
                        case 9:
                            $Class = "Botanist";
                        break;
                        case 10:
                            $Class = "Fisher";
                        break;
                    }
                    $OtherArray = [];
                    $ShopItemID = $CollectablesShopCsv->at($SpecialShopID)["ShopItems[$i]"];
                    $countarray = [];
                    $CountCheck = false;
                    foreach(range(0,999) as $e) {
                        $SubDataValue = "". $ShopItemID .".". $e ."";
                        if (empty($ItemCsv->at($CollectablesShopItemCsv->at($SubDataValue)['Item'])['Name'])) break;
                        $countarray[] = $e;
                    }
                    $Count = count($countarray);
                    $Count = ($Count * 3);
                    if ($Count > 84){
                        $CountCheck = true;
                    }
                    foreach(range(0,999) as $b) {
                        $number = $b + 1;
                        $SubDataValue = "". $ShopItemID .".". $b ."";
                        $GroupID = $CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopItemGroup'];
                        if (empty($ItemCsv->at($CollectablesShopItemCsv->at($SubDataValue)['Item'])['Name'])) break;
                        $Item = $ItemCsv->at($CollectablesShopItemCsv->at($SubDataValue)['Item'])['Name'];
                        //special switch for collectable dealer:
                        switch ($NpcName) {
                            case 'Collectable Dealer':
                                $shopswitch = true;
                            break;
                            default:
                                $shopswitch = false;
                            break;
                        }
                        if ($shopswitch === true) {
                            $number = $number + 1;
                            //gather rewards script 
                            $RewardType = $CollectablesShopCsv->at($SpecialShopID)['RewardType'];
                            $CollectabilityLink = $CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRefine'];
                            if ($RewardType === "1") {
                                $RewardSheetLink = $CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRewardScrip'];
                                $Currency = $ItemCsv->at($CurrencyArray[$CollectablesShopRewardScripCsv->at($RewardSheetLink)['Currency']])['Name'];
                                $RewardAmt = $CollectablesShopRewardScripCsv->at($RewardSheetLink)['LowReward'];
                                $Collectability = $CollectablesShopRefineCsv->at($CollectabilityLink)['LowCollectability'];
                                if ($CountCheck === false){
                                    $OtherArray[] = "{{Sells3|$Currency|Quantity=$RewardAmt+|Cost1=$Item|Count1=1$QuestRequired|Collectable=$Collectability+}}";
                                }
                                if ($CountCheck === true){
                                    $OtherArray[$GroupID][] = "{{Sells3|$Currency|Quantity=$RewardAmt+|Cost1=$Item|Count1=1$QuestRequired|Collectable=$Collectability+}}";
                                }
                            }
                            if ($RewardType === "2") {
                                $RewardSheetLink = $CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRewardScrip'];
                                $RewardAmt = $CollectablesShopRewardItemCsv->at($RewardSheetLink)['RewardLow'];
                                $collect1 = $CollectablesShopRefineCsv->at($CollectabilityLink)['LowCollectability'];
                                $ItemReward = $ItemCsv->at($CollectablesShopRewardItemCsv->at($RewardSheetLink)['Item'])['Name'];
                                if ($CountCheck === false){
                                    $OtherArray[] = "{{Sells3|$ItemReward|Quantity=$RewardAmt+|Cost1=$Item|Count1=1$QuestRequired|Collectable=$Collectability+}}";
                                }
                                if ($CountCheck === true){
                                    $OtherArray[$GroupID][] = "{{Sells3|$ItemReward|Quantity=$RewardAmt+|Cost1=$Item|Count1=1$QuestRequired|Collectable=$Collectability+}}";
                                }
                            }

                        } 
                        if ($shopswitch === false) {
                            foreach(range(0,2) as $a) {
                                $number = $number + 1;
                                //gather rewards script 
                                $RewardType = $CollectablesShopCsv->at($SpecialShopID)['RewardType'];
                                $CollectabilityLink = $CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRefine'];
                                if ($RewardType === "1") {
                                    $RewardSheetLink = $CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRewardScrip'];
                                    $Currency = $ItemCsv->at($CurrencyArray[$CollectablesShopRewardScripCsv->at($RewardSheetLink)['Currency']])['Name'];
                                    switch ($a) {
                                        case 0:
                                            $RewardAmt = $CollectablesShopRewardScripCsv->at($RewardSheetLink)['LowReward'];
                                            $collect1 = $CollectablesShopRefineCsv->at($CollectabilityLink)['LowCollectability'];
                                            $collect2 = intval($CollectablesShopRefineCsv->at($CollectabilityLink)['MidCollectability']);
                                            if ($collect2 > 0){
                                                $collect2Calc = $collect2 - 1;
                                                $Collectability = "$collect1 - $collect2Calc";
                                            }else {
                                                $Collectability = "$collect1+";
                                            }
                                        break;
                                        case 1:
                                            $RewardAmt = $CollectablesShopRewardScripCsv->at($RewardSheetLink)['MidReward'];
                                            $collect1 = $CollectablesShopRefineCsv->at($CollectabilityLink)['MidCollectability'];
                                            $collect2 = intval($CollectablesShopRefineCsv->at($CollectabilityLink)['HighCollectability']);
                                            if ($collect2 > 0){
                                                $collect2Calc = $collect2 - 1;
                                                $Collectability = "$collect1 - $collect2Calc";
                                            }else {
                                                $Collectability = "$collect1+";
                                            }
                                        break;
                                        case 2:
                                            $RewardAmt = $CollectablesShopRewardScripCsv->at($RewardSheetLink)['HighReward'];
                                            $collect1 = $CollectablesShopRefineCsv->at($CollectabilityLink)['HighCollectability'];
                                            $Collectability = "$collect1+";
                                        break;
                                    }
                                    if ($CountCheck === false){
                                        $OtherArray[] = "{{Sells3|$Currency|Quantity=$RewardAmt|Cost1=$Item|Count1=1$QuestRequired|Collectable=$Collectability}}";
                                    }
                                    if ($CountCheck === true){
                                        $OtherArray[$GroupID][] = "{{Sells3|$Currency|Quantity=$RewardAmt|Cost1=$Item|Count1=1$QuestRequired|Collectable=$Collectability}}";
                                    }
                                }
                                if ($RewardType === "2") {
                                    $RewardSheetLink = $CollectablesShopItemCsv->at($SubDataValue)['CollectablesShopRewardScrip'];
                                    switch ($a) {
                                        case 0:
                                            $RewardAmt = $CollectablesShopRewardItemCsv->at($RewardSheetLink)['RewardLow'];
                                            $collect1 = $CollectablesShopRefineCsv->at($CollectabilityLink)['LowCollectability'];
                                            $collect2 = intval($CollectablesShopRefineCsv->at($CollectabilityLink)['MidCollectability']);
                                            if ($collect2 > 0){
                                                $collect2Calc = $collect2 - 1;
                                                $Collectability = "$collect1 - $collect2Calc";
                                            }else {
                                                $Collectability = "$collect1+";
                                            }
                                        break;
                                        case 1:
                                            $RewardAmt = $CollectablesShopRewardItemCsv->at($RewardSheetLink)['RewardMid'];
                                            $collect1 = $CollectablesShopRefineCsv->at($CollectabilityLink)['MidCollectability'];
                                            $collect2 = intval($CollectablesShopRefineCsv->at($CollectabilityLink)['HighCollectability']);
                                            if ($collect2 > 0){
                                                $collect2Calc = $collect2 - 1;
                                                $Collectability = "$collect1 - $collect2Calc";
                                            }else {
                                                $Collectability = "$collect1+";
                                            }
                                        break;
                                        case 2:
                                            $RewardAmt = $CollectablesShopRewardItemCsv->at($RewardSheetLink)['RewardHigh'];
                                            $collect1 = $CollectablesShopRefineCsv->at($CollectabilityLink)['HighCollectability'];
                                            $Collectability = "$collect1+";
                                        break;
                                    }
                                    $ItemReward = $ItemCsv->at($CollectablesShopRewardItemCsv->at($RewardSheetLink)['Item'])['Name'];
                                    if ($CountCheck === false){
                                        $OtherArray[] = "{{Sells3|$ItemReward|Quantity=$RewardAmt|Cost1=$Item|Count1=1$QuestRequired|Collectable=$Collectability}}";
                                    }
                                    if ($CountCheck === true){
                                        $OtherArray[$GroupID][] = "{{Sells3|$ItemReward|Quantity=$RewardAmt|Cost1=$Item|Count1=1$QuestRequired|Collectable=$Collectability}}";
                                    }
                                }
                            }

                        }
                        
                    }
                    if ($CountCheck === false){
                        usort($OtherArray, function($a, $b) {
                            $cmp = strcmp(preg_replace('/\d+/', '', $a), preg_replace('/\d+/', '', $b));
                            if ($cmp) {
                                return $cmp;
                            } else {
                                return strnatcmp($a, $b);
                            }
                        });
                        if (!empty($OtherArray)) {
                            $Other = "|Misc = \n".implode("\n", str_ireplace("&","and", $OtherArray)). "\n";
                        }
                        $ShopOutputString = "{{-start-}}\n'''". $NpcName ."/". $ShopName ."-$Class'''\n";
                        $ShopOutputString .= "{{Shop\n";
                        $ShopOutputString .= "| Shop Name = $ShopName-$Class\n";
                        $ShopOutputString .= "| NPC Name = $NpcName\n";
                        $ShopOutputString .= "| Location = $NpcPlaceName\n";
                        $ShopOutputString .= "| Coordinates = $CoordLocation\n";
                        $ShopOutputString .= "| Total Items = $number\n";
                        $ShopOutputString .= "$QuestRequired\n";
                        $ShopOutputString .= "| Shop = \n";
                        $ShopOutputString .= "{{Tabsells3\n";
                        $ShopOutputString .= "$Other\n";
                        $ShopOutputString .= "\n}}\n}}\n{{-stop-}}";
                        $ShopOutputArray[] = $ShopOutputString;
                        $ShopOutputNamesArray[] = "$ShopName-$Class";
                    }
                    if ($CountCheck === true){
                        $grouparrayoutput = [];
                        foreach ($OtherArray as $key => $value) {
                            if (!empty($value)) {
                                usort($value, function($a, $b) {
                                    $cmp = strcmp(preg_replace('/\d+/', '', $a), preg_replace('/\d+/', '', $b));
                                    if ($cmp) {
                                        return $cmp;
                                    } else {
                                        return strnatcmp($a, $b);
                                    }
                                });
                                $CountNoItems = count($value);
                                $grouparrayoutput = "|Misc = \n".implode("\n", str_ireplace("&","and", $value)). "\n";
                                $Group = "UNKNOWNGROUPNAME";
                                if (!empty($CollectablesShopItemGroupCsv->at($key)['Name'])){
                                    $Group = $CollectablesShopItemGroupCsv->at($key)['Name'];
                                }
                                $ShopOutputString = "{{-start-}}\n'''". $NpcName ."/". $ShopName ."-$Class-$Group'''\n";
                                $ShopOutputString .= "{{Shop\n";
                                $ShopOutputString .= "| Shop Name = $ShopName-$Class-$Group\n";
                                $ShopOutputString .= "| NPC Name = $NpcName\n";
                                $ShopOutputString .= "| Location = $NpcPlaceName\n";
                                $ShopOutputString .= "| Coordinates = $CoordLocation\n";
                                $ShopOutputString .= "| Total Items = $CountNoItems\n";
                                $ShopOutputString .= "$QuestRequired\n";
                                $ShopOutputString .= "| Shop = \n";
                                $ShopOutputString .= "{{Tabsells3\n";
                                $ShopOutputString .= "$grouparrayoutput\n";
                                $ShopOutputString .= "\n}}\n}}\n{{-stop-}}";
                                $ShopOutputArray[] = $ShopOutputString;
                                $ShopOutputNamesArray[] = "$ShopName-$Class-$Group";
                            }
                        }
                    }
                }
                $ShopOutputStringImp = implode("\n",$ShopOutputArray);
                $ShopOutputNames = implode(",",$ShopOutputNamesArray);
                $ShopOutput["Number"] = $number;
                $ShopOutput["Shop"] = "$ShopOutputStringImp";
                $ShopOutput["Name"] = $ShopOutputNames;
                return $ShopOutput;
            break;
            case 'GilShop':
                $DataValue = $SpecialShopID;
                $GilShopRequiredQuest = "";
                if (!empty($QuestCsv->at($GilShopCsv->at($DataValue)['Quest'])['Name'])) {
                    $GilShopRequiredQuest = "|Requires Quest =". $QuestCsv->at($GilShopCsv->at($DataValue)['Quest'])['Name']."\n";
                }
                $ShopName = str_ireplace($oldarray,$newarray,"$TopicSelectName".$GilShopCsv->at($DataValue)['Name']);
                if (empty($ShopName)) { 
                    $ShopName = $SpecialShopID;
                }

                
                $CompleteText = $this->getDefaultTalk($DefaultTalkCsv, $GilShopCsv, $SpecialShopID, "AcceptTalk", "");
                $DenyText = $this->getDefaultTalk($DefaultTalkCsv, $GilShopCsv, $SpecialShopID, "FailTalk", "");

                $DialogueOutput = "{{-start-}}\n'''". $NpcName ."/Dialogue/Dialogue'''\n";
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
                        foreach(range(0,1) as $c) {
                            if (empty($QuestCsv->at($GilShopItemCsv->at($GilShopSubArray)["Row{Required}[$c]"])["Name"])) continue;
                            if ($c === 0) {
                                $RequiredQuest = $QuestCsv->at($GilShopItemCsv->at($GilShopSubArray)["Row{Required}[$c]"])["Name"];
                                $RowRequiredArray[] = "|Requires Quest = ". $RequiredQuest;
                            }
                            if ($c === 1) {
                                $RequiredQuest = $QuestCsv->at($GilShopItemCsv->at($GilShopSubArray)["Row{Required}[$c]"])["Name"];
                                $RowRequiredArray[] = ",". $RequiredQuest;
                            }
                        }
                        if (!empty($AchievementCsv->at($GilShopItemCsv->at($GilShopSubArray)["Row{Required}[2]"])["Name"])){
                            $Requiredachievement = $AchievementCsv->at($GilShopItemCsv->at($GilShopSubArray)["Row{Required}[2]"])["Name"];
                            $RowRequiredArray[] = "|Requires Achievement = ". $Requiredachievement;
                        }
                        $NumberItems = $b + 1;
                        $RowRequired = implode("", $RowRequiredArray);
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
                asort($WeaponArray);
                asort($ArmorArray);
                asort($AccessoryArray);
                asort($OtherArray);
                if (!empty($WeaponArray)) {
                    $Weapons = "|Weapons = \n". implode("\n", str_ireplace("&","and", $WeaponArray)). "\n";
                }
                if (!empty($ArmorArray)) {
                    $Armor = "|Armor = \n".implode("\n", str_ireplace("&","and", $ArmorArray)). "\n";
                }
                if (!empty($AccessoryArray)) {
                    $Accessory = "|Accessory = \n".implode("\n", str_ireplace("&","and", $AccessoryArray)). "\n";
                }
                if (!empty($OtherArray)) {
                    $Other = "|Misc = \n".implode("\n", str_ireplace("&","and", $OtherArray)). "\n";
                }
                $ShopOutputString = "{{-start-}}\n'''". $NpcName ."/". $ShopName ."'''\n";
                $ShopOutputString .= "{{Shop\n";
                $ShopOutputString .= "| Shop Name = $ShopName\n";
                $ShopOutputString .= "| NPC Name = $NpcName\n";
                $ShopOutputString .= "| Location = $NpcPlaceName\n";
                $ShopOutputString .= "| Coordinates = $CoordLocation\n";
                $ShopOutputString .= "| Total Items = $NumberItems\n";
                $ShopOutputString .= "$GilShopRequiredQuest";
                $ShopOutputString .= "| Shop = \n";
                $ShopOutputString .= "{{Tabsells3\n";
                
                $ShopOutput["Shop"] = "\n$ShopOutputString\n$Weapons$Armor$Accessory$Other\n}}\n}}\n{{-stop-}}";
                $ShopOutput["Number"] = $NumberItems;
                $ShopOutput["Name"] = $ShopName;
                return $ShopOutput;
            break;
            case 'DisposalShop':
                $DataValue = $SpecialShopID;
                $DisposalShopCsv = $this->csv('DisposalShop');
                $DisposalShopItemCsv = $this->csv('DisposalShopItem');
                $ShopName = str_ireplace($oldarray,$newarray,"$TopicSelectName".$DisposalShopCsv->at($DataValue)['ShopName']);
                if (empty($ShopName)) { 
                    $ShopName = $SpecialShopID;
                }

                foreach(range(0,999) as $b) {
                    $ShopSubArray = "". $DataValue . "." . $b ."";
                    if (empty($DisposalShopItemCsv->at($ShopSubArray)['Item{Received}'])) break;
                    $NumberItems = $b + 1;
                    $CategoryPre = $ItemCsv->at($DisposalShopItemCsv->at($ShopSubArray)["Item{Received}"])["EquipSlotCategory"];
                    $ShopSellsItem = $ItemCsv->at($DisposalShopItemCsv->at($ShopSubArray)["Item{Received}"])["Name"];
                    $ShopSellsItemCost = $ItemCsv->at($DisposalShopItemCsv->at($ShopSubArray)["Item{Disposed}"])["Name"];
                    $ShopSellsItemCostAmt = $DisposalShopItemCsv->at($ShopSubArray)["Quantity{Received}"];
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
                            $WeaponArray[] = "{{Sells3|$ShopSellsItem|Quantity=$ShopSellsItemCostAmt|Cost1=$ShopSellsItemCost|Count1=1}}";
                        break;
                        case 2:
                            $ArmorArray[] = "{{Sells3|$ShopSellsItem|Quantity=$ShopSellsItemCostAmt|Cost1=$ShopSellsItemCost|Count1=1}}";
                        break;
                        case 3:
                            $AccessoryArray[] = "{{Sells3|$ShopSellsItem|Quantity=$ShopSellsItemCostAmt|Cost1=$ShopSellsItemCost|Count1=1}}";
                        break;
                        case 4:
                            $OtherArray[] = "{{Sells3|$ShopSellsItem|Quantity=$ShopSellsItemCostAmt|Cost1=$ShopSellsItemCost|Count1=1}}";
                        break;
                    }
                }
                asort($WeaponArray);
                asort($ArmorArray);
                asort($AccessoryArray);
                asort($OtherArray);
                if (!empty($WeaponArray)) {
                    $Weapons = "|Weapons = \n". implode("\n", str_ireplace("&","and", $WeaponArray)). "\n";
                }
                if (!empty($ArmorArray)) {
                    $Armor = "|Armor = \n".implode("\n", str_ireplace("&","and", $ArmorArray)). "\n";
                }
                if (!empty($AccessoryArray)) {
                    $Accessory = "|Accessory = \n".implode("\n", str_ireplace("&","and", $AccessoryArray)). "\n";
                }
                if (!empty($OtherArray)) {
                    $Other = "|Misc = \n".implode("\n", str_ireplace("&","and", $OtherArray)). "\n";
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
            case 'LotteryExchangeShop':
                $DataValue = $SpecialShopID;
                $LotteryExchangeShopCsv = $this->csv('LotteryExchangeShop');
                $ShopName = "$TopicSelectName".$SpecialShopID;

                foreach(range(0,23) as $b) {
                    if (empty($ItemCsv->at($LotteryExchangeShopCsv->at($DataValue)["ItemAccepted[$b]"]))) continue;
                    $NumberItems = $b + 1;
                    $CategoryPre = $ItemCsv->at($LotteryExchangeShopCsv->at($DataValue)["ItemAccepted$b]"])["EquipSlotCategory"];
                    $ShopAcceptsItem = $ItemCsv->at($LotteryExchangeShopCsv->at($DataValue)["ItemAccepted$b]"])["Name"];
                    $ShopAcceptsItemAmt = $LotteryExchangeShopCsv->at($DataValue)["AmountAccepted[$b]"];
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
                            $WeaponArray[] = "{{Unique Shop|$ShopAcceptsItem|Quantity=$ShopAcceptsItemAmt}}";
                        break;
                        case 2:
                            $ArmorArray[] = "{{Unique Shop|$ShopAcceptsItem|Quantity=$ShopAcceptsItemAmt}}";
                        break;
                        case 3:
                            $AccessoryArray[] = "{{Unique Shop|$ShopAcceptsItem|Quantity=$ShopAcceptsItemAmt}}";
                        break;
                        case 4:
                            $OtherArray[] = "{{Unique Shop|$ShopAcceptsItem|Quantity=$ShopAcceptsItemAmt}}";
                        break;
                    }
                }
                asort($WeaponArray);
                asort($ArmorArray);
                asort($AccessoryArray);
                asort($OtherArray);
                if (!empty($WeaponArray)) {
                    $Weapons = "|Weapons = \n". implode("\n", str_ireplace("&","and", $WeaponArray)). "\n";
                }
                if (!empty($ArmorArray)) {
                    $Armor = "|Armor = \n".implode("\n", str_ireplace("&","and", $ArmorArray)). "\n";
                }
                if (!empty($AccessoryArray)) {
                    $Accessory = "|Accessory = \n".implode("\n", str_ireplace("&","and", $AccessoryArray)). "\n";
                }
                if (!empty($OtherArray)) {
                    $Other = "|Misc = \n".implode("\n", str_ireplace("&","and", $OtherArray)). "\n";
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
                    if ($ModelBaseDye === "0"){
                        $ModelBaseDye = "";
                    }
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
            if ($DyeBase === "0"){
                $DyeBase = "";
            }
            if ($Base == 0) {
                $Model = false;
                if ($NpcEquipCsv->at($EnpcBase->at($id)['NpcEquip'])["Model{{$ENPCOffset0}}"] != 0) {
                    $Base = $NpcEquipCsv->at($EnpcBase->at($id)['NpcEquip'])["Model{{$ENPCOffset0}}"];
                    $DyeBase = $StainCsv->at($NpcEquipCsv->at($EnpcBase->at($id)['NpcEquip'])["Dye{{$ENPCOffset0}}"])['Name'];
                }
            }
            if ($Base == 4294967295) { //HEX for FFFF,FFFF
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
     * New Formatter
     */
    public function GEformat($data)
    {
        // sort keys by their length so long ones are formatted before smaller ones
        // this prevents keys such as "Job" affecting "ClassJobLevel"

        // set format
        $data = str_ireplace('        |','|', $data);
        $data = str_ireplace("        \n","\n", $data);
        $data = str_ireplace('        }}','}}', $data);
        $data = str_ireplace('        {{','{{', $data);
        $data = preg_replace("/(.*)dammy(.*)\n/", null, $data);
        $data = preg_replace("/(.*)★未使用(.*)削除予定★(.*)\n/",null, $data);
        $data = preg_replace("/{{Loremquote\\|Todo\d\d+\\|link=y\\|(.*)\n/", null, $data);
        //$data = preg_replace("/\n\n\n+/", "\n\n", $data);
        $data = preg_replace("/(QuestReward.*)\n\n(?!\\|Issuing NPC)/", "$1\n", $data);
        $data = preg_replace("/(QuestReward.*)\n(\\|Issuing NPC.*)/", "$1\n\n$2", $data);
        $data = preg_replace("/\s*|\s*/", null, $data);
        $data = preg_replace("/<Emphasis>|<\\/Emphasis>/", "", $data);
        $data = preg_replace("/<If\\(LessThan\\(PlayerParameter\\(11\\),12\\)\\)><If\\(LessThan\\(PlayerParameter\\(11\\),4\\)\\)>([^>]+)<Else\\/>([^>]+)<\\/If><Else\\/><If\\(LessThan\\(PlayerParameter\\(11\\),17\\)\\)>([^>]+)<Else\\/>([^>]+)<\\/If><\\/If>/", "{{Loremtextconditional|$1|or '$2' or '$3', depending on the time of day.}}", $data);
        $data = preg_replace("/{{Loremquote\\|Q\d+\\|link=y\\|(.*)}}/","\n{| class=\"datatable-GEtable\"\n|+$1\n|Place an answer Here <!--(Not all questions have answers and thus don't need a table, please evaluate and delete this if necessary.)-->\n|}\n", $data);
        $data = preg_replace("/{{Loremquote\\|A\d+\\|link=y\\|(.*)}}/","!<!--Answer to copy into table above--> $1", $data);
        $data = preg_replace("/{{Loremquote\\|(?:System)\\|link=y\\|(.*)}}/", "<div>'''$1'''</div>", $data);
        $data = preg_replace("/<Color\\(-3917469\\)>(.*)<\\/Color>/", "{{Loremascianspeak|$1}}", $data);
        $data = preg_replace("/<If\\(PlayerParameter\\(4\\)\\)>([\w\s']+)<Else\\/>([\w\s']+)<\\/If>/", "{{Loremtextmale|$2|$1}}", $data);
        $data = preg_replace("/<Color\\(-34022\\)>([\w\s,.\\/<>&'-]+)<\\/Color>/", "{{Color|Orange|$1}}", $data);
        $data = str_replace("(-???-)", null, $data);
        $data = preg_replace("/{{Loremquote\\|.*\\|link=y\\|\\(-(.*)-\\)/", "{{Loremquote|$1|link=y|", $data);
        $data = str_replace("<If(GreaterThan(PlayerParameter(52),0))><Clickable(<If(GreaterThan(PlayerParameter(52),0))><Sheet(GCRankLimsaMaleText,PlayerParameter(52),8)/><Else/></If><If(GreaterThan(PlayerParameter(53),0))><Sheet(GCRankGridaniaMaleText,PlayerParameter(53),8)/><Else/></If><If(GreaterThan(PlayerParameter(54),0))><Sheet(GCRankUldahMaleText,PlayerParameter(54),8)/><Else/></If>)/> <Split(<Highlight>ObjectParameter(1)</Highlight>, ,2)/><Else/><If(GreaterThan(PlayerParameter(53),0))><Split(<Highlight>ObjectParameter(1)</Highlight>, ,1)/><Else/><Split(<Highlight>ObjectParameter(1)</Highlight>, ,1)/></If></If>", "{{Loremtextconditional|<GC Rank/Surname>|The player's Grand Company Rank. If not in a GC, then their last name}}", $data);
        $data = preg_replace("/<If\(GreaterThan\(PlayerParameter\(52\),0\)\)>([^<]+)<Clickable\(<If\(GreaterThan\(PlayerParameter\(52\),0\)\)><Sheet\(GCRankLimsaMaleText,PlayerParameter\(52\),8\)\/><Else\/><\/If><If\(GreaterThan\(PlayerParameter\(53\),0\)\)><Sheet\(GCRankGridaniaMaleText,PlayerParameter\(53\),8\)\/><Else\/><\/If><If\(GreaterThan\(PlayerParameter\(54\),0\)\)><Sheet\(GCRankUldahMaleText,PlayerParameter\(54\),8\)\/><Else\/><\/If>\)\/> <Split\(<Highlight>ObjectParameter\(1\)<\/Highlight>, ,2\)\/><Else\/><If\(GreaterThan\(PlayerParameter\(53\),0\)\)>([^<]+)<Split\(<Highlight>ObjectParameter\(1\)<\/Highlight>, ,1\)\/><Else\/>[^<]+<Split\(<Highlight>ObjectParameter\(1\)<\/Highlight>, ,1\)\/><\/If><\/If>/", "{{Loremtextconditional|$1|If player is in a Grand Company. Otherwise, this will say \"$2\"", $data);
        $data = str_replace("<Sheet(GCRankLimsaMaleText,PlayerParameter(52),8)/><Else/></If><If(GreaterThan(PlayerParameter(53),0))><Sheet(GCRankGridaniaMaleText,PlayerParameter(53),8)/><Else/></If><If(GreaterThan(PlayerParameter(54),0))><Sheet(GCRankUldahMaleText,PlayerParameter(54),8)/><Else/></If>", "{{Loremtextconditional|<Player's Grand Company Rank>|Player's GC Rank is shown here}}", $data);
        $data = str_replace("<Split(<Highlight>ObjectParameter(1)</Highlight>, ,1)/>", "{{Loremforename}}", $data);
        $data = str_replace("<Split(<Highlight>ObjectParameter(1)</Highlight>, ,2)/>", "{{Loremsurname}}", $data);
        $data = str_replace("<Highlight>ObjectParameter(1)</Highlight>", "{{Loremforename}} {{Loremsurname}}", $data);
        $data = str_replace("<Sheet(Addon,9,0)/>", "{{HQ|2}}", $data);
        $data = preg_replace("/\\*<If\\(LessThan\\(IntegerParameter\\(\d+\\),IntegerParameter\\(\d+\\)\\)\\)>([^<]+)<Else\\/>([^<]+)<\\/If>\\./", "*$1.\n*$2.", $data);
        //Same as above, except without the \\. which was used to match a period after the </If>
        $data = preg_replace("/\\*<If\\(LessThan\\(IntegerParameter\\(\d+\\),IntegerParameter\\(\d+\\)\\)\\)>([^<]+)<Else\\/>([^<]+)<\\/If>/", "*$1\n*$2", $data);
        //below string replacement is for adding "an" before Armorer, Alchemist, Archer, Arcanist, Astrologian, or "a" before the other Job names (due to the vowel at the beginning of the name. Silly English language...)
        $data = str_replace("<If(Equal(PlayerParameter(68),10))>an <Sheet(ClassJob,PlayerParameter(68),0)/><Else/><If(Equal(PlayerParameter(68),14))>an <Sheet(ClassJob,PlayerParameter(68),0)/><Else/><If(Equal(PlayerParameter(68),5))>an <Sheet(ClassJob,PlayerParameter(68),0)/><Else/><If(Equal(PlayerParameter(68),26))>an <Sheet(ClassJob,PlayerParameter(68),0)/><Else/><If(Equal(PlayerParameter(68),33))>an <Sheet(ClassJob,PlayerParameter(68),0)/><Else/>a <Sheet(ClassJob,PlayerParameter(68),0)/></If></If></If></If></If>","{{Loremtextconditional|an Armorer|or 'an Alchemist/Archer/Arcanist/Astrologian', or 'a JobName' (depending on your current job)}}", $data);
        $data = preg_replace("/\\<UIForeground\\>[^<]+\\<\\/UIForeground\\>|\\<UIGlow\\>[^<]+\\<\\/UIGlow\\>|\\<72\\>[^<]+\\<\\/72\\>|\\<73\\>[^<]+\\<\\/73\\>/","",$data);
        //regex to add a % to the end of [[EXP Bonus]] gear
        $data = preg_replace("/(\\[\\[EXP Bonus\\]\\] \\+\d+)/", "$1%", $data);
        $data = str_replace("= False\n", "= No\n", $data);
        $data = str_replace("= True\n", "= Yes\n", $data);
        $data = str_replace("|Section = Class & Job Quests", "|Section = Class and Job Quests", $data);
        $data = preg_replace("/(Survey target areas.|Gather items at all the specified locations.)\nEvaluation Bonus:\n(\d+)～ \\+(\d+)%\n(\d+)～ \\+(\d+)%\n(\d+)～  \\+(\d+)%/", "\n*$1\n*Evaluation Bonus:\n**$2～ +$3%\n**$4～ +$5%\n**$6～  +$7%", $data);

        return trim($data) . "\n\n";
    }

    /**
     * Save to output file for extra
     */
    public function saveExtra($filename, $SourceData)
    {   
        $data = $this->GEformat($SourceData);
        $ini = parse_ini_file('config.ini');
        $PatchID = file_get_contents("{$ini['MainPath']}\game\\ffxivgame.ver");
        $folder = $this->projectDirectory . getenv('OUTPUT_DIRECTORY');
        if (!file_exists("{$folder}/{$PatchID}/")) {
            mkdir("{$folder}/{$PatchID}/", 0777, true);
        }
        $fileopen = fopen("{$folder}/{$PatchID}/{$filename}", 'w');
        fwrite($fileopen, $data);
        fclose($fileopen);

        return $this->io->text("Saved {$folder}/{$PatchID}/{$filename}");
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
     * Converts SE icon "number" into a proper path, use iconize($ImageID, true) for HR
     */
    private function iconize($number, $hr = false)
    {
        $number = intval($number);
        $extended = (strlen($number) >= 6);

        if ($number == 0) {
            return null;
        }
        $en = false;
        // create icon filename
        $icon = $extended ? str_pad($number, 5, "0", STR_PAD_LEFT) : '0' . str_pad($number, 5, "0", STR_PAD_LEFT);
        //var_dump($icon);
        if (($icon > 120000) && ($icon < 130000)){
            $en = true;
        }
        if (($icon > 150000) && ($icon < 182000)){
            $en = true;
        }

        // create icon path
        $path = [];
        $path[] = $extended ? $icon[0] . $icon[1] . $icon[2] .'000' : '0'. $icon[1] . $icon[2] .'000';

        $path[] = $icon;
        $encheck = $en ? "/en" : "";
        $hrcheck = $hr ? "_hr1" : "";
        // combine
        $icon = implode("$encheck/", $path) ."$hrcheck.png";

        return $icon;
    }
    /**
     * Array of currencies. Usage: $GetCurrency[1]; will give the item ID of the currency
     */
    private function GetCurrency()
    {
        $CurencyArray[1] = 10309;
        $CurencyArray[2] = 17833;
        $CurencyArray[3] = 10311;
        $CurencyArray[4] = 17834;
        $CurencyArray[5] = 10307;
        $CurencyArray[6] = 25199;
        $CurencyArray[7] = 25200;
        $CurencyArray[8] = 21072;
        $CurencyArray[9] = 21073;
        $CurencyArray[10] = 21074;
        $CurencyArray[11] = 21075;
        $CurencyArray[12] = 21076;
        $CurencyArray[13] = 21077;
        $CurencyArray[14] = 21078;
        $CurencyArray[15] = 21079;
        $CurencyArray[16] = 21080;
        $CurencyArray[17] = 21081;
        $CurencyArray[18] = 21172;
        $CurencyArray[19] = 21173;
        $CurencyArray[20] = 21935;
        $CurencyArray[21] = 22525;
        $CurencyArray[22] = 26533;
        $CurencyArray[23] = 26807;
        $CurencyArray[24] = 28063;
        $CurencyArray[25] = 28186;
        $CurencyArray[26] = 28187;
        $CurencyArray[27] = 28188;
        $CurencyArray[28] = 30341;

        return $CurencyArray;
    }
}