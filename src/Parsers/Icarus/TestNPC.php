<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:TestNPC
 */
class TestNPC implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{output}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $ENpcBaseCsv = $this->csv('ENpcBase');
        $ENpcResidentCsv = $this->csv('ENpcResident');
        $QuestCsv = $this->csv('Quest');
        $CustomTalkCsv = $this->csv('CustomTalk');
        $CustomTalkNestHandlersCsv = $this->csv('CustomTalkNestHandlers');
        $CustomTalkDynamicIconCsv = $this->csv('CustomTalkDynamicIcon');
        $HowToCsv = $this->csv('HowTo');
        $HowToCategoryCsv = $this->csv('HowToCategory');
        $HowToPageCsv = $this->csv('HowToPage');
        $JingleCsv = $this->csv('Jingle');
        $ScreenImageCsv = $this->csv('ScreenImage');
        $GatheringLeveCsv = $this->csv('GatheringLeve');
        $GatheringLeveRuleCsv = $this->csv('GatheringLeveRule');
        $EventItemCsv = $this->csv('EventItem');
        $ItemCsv = $this->csv('Item');
        $GilShopCsv = $this->csv('GilShop');
        $GilShopItemCsv = $this->csv('GilShopItem');
        $DefaultTalkCsv = $this->csv('DefaultTalk');
        $AchievementCsv = $this->csv('Achievement');
        $LeveCsv = $this->csv('Leve');
        $CraftLeveCsv = $this->csv('CraftLeve');
        $ChocoboTaxiStandCsv = $this->csv('ChocoboTaxiStand');
        $ChocoboTaxiCsv = $this->csv('ChocoboTaxi');
        $PlaceNameCsv = $this->csv('PlaceName');
        $GuildLeveAssignmentCsv = $this->csv('GuildLeveAssignment');
        $GuildLeveAssignmentTalkCsv = $this->csv('GuildLeveAssignmentTalk');
        $GCShopCsv = $this->csv('GCShop');
        $GrandCompanyCsv = $this->csv('GrandCompany');
        $LogMessageCsv = $this->csv('LogMessage');
        $SpecialShopCsv = $this->csv('SpecialShop');
        $SwitchTalkVariationCsv = $this->csv('SwitchTalkVariation');
        $TripleTriadCardCsv = $this->csv('TripleTriadCard');
        $TripleTriadCsv = $this->csv('TripleTriad');
        $TripleTriadRuleCsv = $this->csv('TripleTriadRule');
        $FCCShopCsv = $this->csv('FccShop');
        $DpsChallengeOfficerCsv = $this->csv('DpsChallengeOfficer');
        $DpsChallengeCsv = $this->csv('DpsChallenge');
        $TopicSelectCsv = $this->csv('TopicSelect');
        $PreHandlerCsv = $this->csv('PreHandler');
        $InclusionShopCsv = $this->csv('InclusionShop');
        $InclusionShopCategoryCsv = $this->csv('InclusionShopCategory');
        $InclusionShopSeriesCsv = $this->csv('InclusionShopSeries');
        $DisposalShopCsv = $this->csv('DisposalShop');
        $DisposalShopItemCsv = $this->csv('DisposalShopItem');
        $DescriptionCsv = $this->csv('Description');
        $CollectablesShopCsv = $this->csv('CollectablesShop');
        $CollectablesShopItemCsv = $this->csv('CollectablesShopItem');
        $ClassJobCategoryCsv = $this->csv('ClassJobCategory');
        $BalloonCsv = $this->csv('Balloon');
        $BehaviourCsv = $this->csv('Behavior');
        $TribeCsv = $this->csv('Tribe');
        $RaceCsv = $this->csv('Race');
        $LeveStringCsv = $this->csv('LeveString');
        $WarpCsv = $this->csv('Warp');
        $WarpConditionCsv = $this->csv('WarpCondition');
        //$EmoteCsv = $this->csv('Emote');
        //$TextCommandCsv = $this->csv('TextCommand');
        // (optional) start a progress bar

        //functions: 

        
        $this->io->progressStart($ENpcBaseCsv->total);
        // loop through data
        $outputarray = [];
        foreach ($ENpcBaseCsv->data as $id => $ENpcBase) {
            //if ($id != 1000100) continue;
            $this->io->progressAdvance();
            //Array of names that should not be capitalized
            $IncorrectNames = array(" De ", " Bas ", " Mal ", " Van ", " Cen ", " Sas ", " Tol ", " Zos ", " Yae ", " The ", " Of The ", " Of ",
                "A-ruhn-senna", "A-towa-cant", "Bea-chorr", "Bie-zumm", "Bosta-bea", "Bosta-loe", "Chai-nuzz", "Chei-ladd", "Chora-kai", "Chora-lue",
                "Chue-zumm", "Dulia-chai", "E-sumi-yan", "E-una-kotor", "Fae-hann", "Hangi-rua", "Hanji-fae", "Kai-shirr", "Kan-e-senna", "Kee-bostt",
                "Kee-satt", "Lewto-sai", "Lue-reeq", "Mao-ladd", "Mei-tatch", "Moa-mosch", "Mosha-moa", "Moshei-lea", "Nunsi-lue", "O-app-pesi", "Qeshi-rae",
                "Rae-qesh", "Rae-satt", "Raya-o-senna", "Renda-sue", "Riqi-mao", "Roi-tatch", "Rua-hann", "Sai-lewq", "Sai-qesh", "Sasha-rae", "Shai-satt",
                "Shai-tistt", "Shee-tatch", "Shira-kee", "Shue-hann", "Sue-lewq", "Tao-tistt", "Tatcha-mei", "Tatcha-roi", "Tio-reeq", "Tista-bie", "Tui-shirr",
                "Vroi-reeq", "Zao-mosc", "Zia-bostt", "Zoi-chorr", "Zumie-moa", "Zumie-shai");
            $correctnames = array(" de ", " bas ", " mal ", " van ", " cen ", " sas ", " tol ", " zos ", " yae ", " the ", " of the ", " of ",
                "A-Ruhn-Senna", "A-Towa-Cant", "Bea-Chorr", "Bie-Zumm", "Bosta-Bea", "Bosta-Loe", "Chai-Nuzz", "Chei-Ladd", "Chora-Kai", "Chora-Lue",
                "Chue-Zumm", "Dulia-Chai", "E-Sumi-Yan", "E-Una-Kotor", "Fae-Hann", "Hangi-Rua", "Hanji-Fae", "Kai-Shirr", "Kan-E-Senna", "Kee-Bostt",
                "Kee-Satt", "Lewto-Sai", "Lue-Reeq", "Mao-Ladd", "Mei-Tatch", "Moa-Mosch", "Mosha-Moa", "Moshei-Lea", "Nunsi-Lue", "O-App-Pesi", "Qeshi-Rae",
                "Rae-Qesh", "Rae-Satt", "Raya-O-Senna", "Renda-Sue", "Riqi-Mao", "Roi-Tatch", "Rua-Hann", "Sai-Lewq", "Sai-Qesh", "Sasha-Rae", "Shai-Satt",
                "Shai-Tistt", "Shee-Tatch", "Shira-Kee", "Shue-Hann", "Sue-Lewq", "Tao-Tistt", "Tatcha-Mei", "Tatcha-Roi", "Tio-Reeq", "Tista-Bie", "Tui-Shirr",
                "Vroi-Reeq", "Zao-Mosc", "Zia-Bostt", "Zoi-Chorr", "Zumie-Moa", "Zumie-Shai");

            //Quest Giver Name (All Words In Name Capitalized)
            $NpcMiqoCheck = $ENpcBase['Race']; //see if miqote
            $NpcName = ucwords(strtolower($ENpcResidentCsv->at($id)['Singular']));
            //this explodes miqote's names into 2 words, capitalizes them and then puts it back together with a hyphen
            if ($NpcMiqoCheck == 4) {
                $NpcName = ucwords(strtolower($ENpcResidentCsv->at($id)['Singular']));
                $NpcName = implode('-', array_map('ucfirst', explode('-', $NpcName)));
            }
            $Name = str_replace($IncorrectNames, $correctnames, $NpcName);
            //kill empty + JP names
            if (empty($Name)) continue;
            if (preg_match('/[^\x00-\x7F]+/', $Name)) continue;

            $BehaviourBalloon = $BehaviourCsv->at($ENpcBase['Behavior'])['Balloon'];
            $BalloonText = [];
            foreach(range(0,10) as $b) {
                $SubDataValue = "". $BehaviourBalloon .".". $b ."";
                if (empty($BehaviourCsv->at($SubDataValue)['Balloon'])) break;
                $BalloonText[] = $BalloonCsv->at($BehaviourCsv->at($SubDataValue)['Balloon'])['Dialogue'];
            }
            $BalloonOutput = implode("\nBalloon Text: ", $BalloonText);
            $BalloonSingle = "";
            if (!empty($BalloonCsv->at($ENpcBase['Balloon'])['Dialogue'])) {
                $BalloonSingle = "\nBalloon Text: ". $BalloonCsv->at($ENpcBase['Balloon'])['Dialogue']. "\n";
            }
            //Race/Gender/Tribe
            switch ($ENpcBase['Race']) {
                case 0:
                    $Race = "| Race = Non-Humanoid";
                    $Tribe = "";
                    $Gender = "";
                break;
                
                default:
                    $Race = "| Race = ". $RaceCsv->at($ENpcBase['Race'])['Masculine'];
                    switch ($ENpcBase['Gender']) {
                        case 0:
                            $Gender = "\n| Gender = Male";
                        break;
                        case 1:
                            $Gender = "\n| Gender = Female";
                        break;
                    }
                    $Tribe = "\n| Clan = ". $TribeCsv->at($ENpcBase['Tribe'])['Masculine'] ."";
                break;
            }
            $DataArray = [];
            $InvolvedInQuestArray = [];
            $SwitchTalkArray = [];
            $HowToArray = [];
            $TripleTriadArray = [];
            $ChocoboArray = [];
            $WarpArray = [];
            $ShopArray = [];
            $ShopsLinkArray = [];
            $LotteryExchangeShopArray = [];
            foreach(range(0,31) as $i) {
                if ($ENpcBase["ENpcData[$i]"] == 0) continue;
                if(!empty($ENpcBase["ENpcData[$i]"])) {
                    $DataValue = $ENpcBase["ENpcData[$i]"];
                    switch (true) {
                        case ($DataValue > 65535) && ($DataValue < 69999):
                            $QuestName = $QuestCsv->at($DataValue)['Name'];
                            $InvolvedInQuestArray[] = $QuestName;
                            $SwitchOutput = "";
                        break;
                        case ($DataValue > 131000) && ($DataValue < 139999): //WARP
                            $DefaultTalkAccept = $this->getDefaultTalk($DefaultTalkCsv, $WarpCsv, $DataValue ,'ConditionSuccessEvent', '| Success Talk =');
                            $DefaultTalkFail = $this->getDefaultTalk($DefaultTalkCsv, $WarpCsv, $DataValue ,'ConditionFailEvent', '| Fail Talk =');
                            $DefaultTalkConfirm = $this->getDefaultTalk($DefaultTalkCsv, $WarpCsv, $DataValue ,'ConfirmEvent', '| Unavailable Talk =');
                            $WarpOption = $WarpCsv->at($DataValue)['Name'];
                            if (empty($WarpOption)) continue;
                            $WarpConfirm = $WarpCsv->at($DataValue)['Question'];
                            //condition
                            $Condition = $WarpCsv->at($DataValue)['WarpCondition'];
                            $WarpCost = $WarpConditionCsv->at($Condition)['Gil'];
                            $RequiredQuestArray = [];
                            foreach(range(1,4) as $b) {
                                switch ($b) {
                                    case 1:
                                    case 2:
                                    case 4:
                                        $QuestText = "RequiredQuest{". $b ."}";
                                    break;
                                    case 3:
                                        $QuestText = "DRequiredQuest{". $b ."}";
                                    break;
                                }
                                if (empty($QuestCsv->at($WarpConditionCsv->at($Condition)[$QuestText])['Name'])) continue;
                                $RequiredQuestArray[] = $QuestCsv->at($WarpConditionCsv->at($Condition)[$QuestText])['Name'];
                            }
                            $RequiredQuests = implode(",", $RequiredQuestArray);
                            $RequiredLevel = $WarpConditionCsv->at($Condition)['Class{Level}'];
                            $WarpArray[] = "$WarpOption=\n{{WarpTemplate\n| Option = $WarpOption\n| Confirm = $WarpConfirm\n| RequiredQuests = $RequiredQuests\n| RequiredLevel = $RequiredLevel\n| Cost = $WarpCost\n". $DefaultTalkAccept ."". $DefaultTalkFail ."". $DefaultTalkConfirm ."\n}}";
                            //$SwitchOutput = "| GatheringLeveID = ". $DataValue ."\n". $GLItemsRequired ."". $Objective ."";
                        break;
                        case ($DataValue > 262100) && ($DataValue < 269999): //GILSHOP
                            $GilShopName = $GilShopCsv->at($DataValue)['Name'];
                            $GilShopIcon = $GilShopCsv->at($DataValue)['Icon'];
                            $GilShopRequiredQuest = "";
                            if (!empty($QuestCsv->at($GilShopCsv->at($DataValue)['Quest'])['Name'])) {
                                $GilShopRequiredQuest = "\n|Unlock Quest = ". $QuestCsv->at($GilShopCsv->at($DataValue)['Quest'])['Name'];
                            }
                            $DefaultTalkAccept = $this->getDefaultTalk($DefaultTalkCsv, $GilShopCsv, $DataValue ,'AcceptTalk', '{{Dialoguebox3|Intro=Success Talk |Dialogue='). "\n}}";
                            $DefaultTalkFail = $this->getDefaultTalk($DefaultTalkCsv, $GilShopCsv, $DataValue ,'FailTalk', '{{Dialoguebox3|Intro=Fail Talk |Dialogue='). "\n}}";
                            $GilShopItemArray = [];
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
                                    $GilShopItemArray[] = "     {{Sells3|" . $GilShopSellsItem . "|Quantity=1|Cost1=Gil|Count1=" . $GilShopSellsItemCost . "". $RowRequired. "}}";
                                }
                            }
                            $GilShopItemArrayOutput = implode("\n", $GilShopItemArray);
                            $ShopsLinkArray[] = $GilShopName;
                            $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $GilShopName ."'''\n|NPC Name = ". $Name ."\n\n". $GilShopName ."\nIcon : ". $GilShopIcon ."". $GilShopRequiredQuest ."\n| TotalItems = ". $NumberItems ."\n". $GilShopItemArrayOutput ."\n|Dialogue=\n". $DefaultTalkAccept. "". $DefaultTalkFail. "}}\n{{-stop-}}";
                        break;
                        case ($DataValue > 393000) && ($DataValue < 399999): //GUILDLEVEASSIGNMENT
                            $Category = $GuildLeveAssignmentCsv->at($DataValue)['unknown_1'];
                            $Quest = $QuestCsv->at($GuildLeveAssignmentCsv->at($DataValue)['Quest[0]'])['Name'];
                            //talk
                            $WelcomeText = "";
                            $DenyText = "";
                            $GoodbyeText = "";
                            $HandlerDenyText = "";
                            $TurnInText = "";
                            $WrongGCFailText = "";
                            $RankFailText = "";
                            $BountyText = "";
                            if ($GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_31"] != "0") {
                                $WelcomeText = "\n\nWelcome Text = ".$GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_31"];
                            }
                            if ($GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_32"] != "0") {
                                $DenyText = "\n\nDeny Text = ".$GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_32"];
                            }
                            if ($GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_33"] != "0") {
                                $GoodbyeText = "\n\nGoodbye Text = ".$GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_33"];
                            }
                            if ($GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_34"] != "0") {
                                $HandlerDenyText = "\n\nHandler Deny Text = ".$GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_34"];
                            }
                            if ($GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_35"] != "0") {
                                $TurnInText = "\n\nTurn In Text = ".$GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_35"];
                            }
                            if ($GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_36"] != "0") {
                                $WrongGCFailText = "\n\nWrongGCFail Text = ".$GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_36"];
                            }
                            if ($GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_37"] != "0") {
                                $RankFailText = "\n\nRank Fail Text = ".$GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_37"];
                            }
                            if ($GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_38"] != "0") {
                                $BountyText = "\n\nBounty Text = ".$GuildLeveAssignmentTalkCsv->at($GuildLeveAssignmentCsv->at($DataValue)['AssignmentTalk'])["unknown_38"];
                            }
                            
                            $String = "". $WelcomeText ."". $DenyText ."". $GoodbyeText ."". $HandlerDenyText ."". $TurnInText ."". $WrongGCFailText ."". $RankFailText ."". $BountyText ."";
                            
                            $SwitchOutput = "". $Category ."\nQuest : ". $Quest ."\nText:". $String ."";
                        break;
                        case ($DataValue > 589000) && ($DataValue < 599999)://DEFAULTTALK
                            $String = [];
                            foreach(range(0,2) as $b) {
                                if ($DefaultTalkCsv->at($DataValue)["Text[$b]"] == "0") continue;
                                $String[] = $DefaultTalkCsv->at($DataValue)["Text[$b]"];
                            }
                            $StringOut = implode("\n", $String);
                            $SwitchTalkArray[] = "{{Dialoguebox3|Intro=Default|Dialogue=\n". $StringOut ."}}\n";
                            break;
                        break;
                        case ($DataValue > 720000) && ($DataValue < 729999): //CUSTOMTALK
                            $MainOption = null;
                            $SubOption = null;
                            $ActorIcon = null;
                            $MapIcon = null;
                            $DynamicIcon = null;
                            if (!empty($CustomTalkCsv->at($DataValue)['unknown_65'])) {
                                $MainOption = "| Main Option = ". $CustomTalkCsv->at($DataValue)['unknown_65'] ."";
                            }
                            if (!empty($CustomTalkCsv->at($DataValue)['unknown_66'])) {
                                $SubOption = "\n| Sub Option = ". $CustomTalkCsv->at($DataValue)['unknown_66'] ."";
                            }
                            if ($CustomTalkCsv->at($DataValue)['Icon{Actor}'] != 0) {
                                $ActorIcon = "\n| Actor Icon = 0". $CustomTalkCsv->at($DataValue)['Icon{Actor}'] .".png";
                            }
                            if ($CustomTalkCsv->at($DataValue)['Icon{Map}'] != 0) {
                                $MapIcon = "\n| Map Icon = 0". $CustomTalkCsv->at($DataValue)['Icon{Map}'] .".png";
                            }
                            if ($CustomTalkCsv->at($DataValue)['unknown_77'] != 0) {
                                //TODO : Add a subrange 
                                $DynamicIconSmall = "Dynamic Icon Small = 0". $CustomTalkDynamicIconCsv->at($CustomTalkCsv->at($DataValue)['unknown_77'])['SmallIcon'] .".png";
                                $DynamicIconLarge = "Dynamic Icon Large = 0". $CustomTalkDynamicIconCsv->at($CustomTalkCsv->at($DataValue)['unknown_77'])['LargeIcon'] .".png";
                                $DynamicIcon = "". $DynamicIconSmall ." | ". $DynamicIconLarge ."";
                            }
                            $LuaText = "";
                            $LuaName = "";

                            if (!empty($CustomTalkCsv->at($DataValue)['Name'])) {
                                $LuaName = $CustomTalkCsv->at($DataValue)['Name'];
                                $LuaText = $this->getLuaDialogue($LuaName);
                            }
                            $OptionsConstructor = "". $MainOption ."". $SubOption ."". $ActorIcon ."". $MapIcon ."". $DynamicIcon ."\n". $LuaText ."";
                            $CustomTalkArray = [];
                            foreach(range(0,29) as $a) {
                                if (empty($CustomTalkCsv->at($DataValue)["Script{Instruction}[$a]"])) continue;
                                $Instruction = $CustomTalkCsv->at($DataValue)["Script{Instruction}[$a]"];
                                $Argument = $CustomTalkCsv->at($DataValue)["Script{Arg}[$a]"];
                                switch (true) {
                                    case (strpos($Instruction, 'ACHIEVEMENT') !== false):
                                        $InstructionQuest = $AchievementCsv->at($Argument)['Name'];
                                        $CustomTalkArray[] = "| GivesAchievement = ". $InstructionQuest ."";
                                    break;
                                    case (strpos($Instruction, 'ITEM') !== false):
                                        $InstructionItem = $ItemCsv->at($Argument)['Name'];
                                        $CustomTalkArray[] = "| AcceptsItem = ". $InstructionItem ."";
                                        //DEBUG $CustomTalkArray[] = "". $Instruction ." | ". $Argument ." -> ITEM -> ". $InstructionItem ."";
                                    break;
                                    case (strpos($Instruction, 'IMAGE') !== false):
                                        $ScreenImageImage = $ScreenImageCsv->at($Argument)['Image'];
                                        $ScreenImageJingle = "";
                                        if (!empty($JingleCsv->at($ScreenImageCsv->at($Argument)['Jingle'])['unknown_1'])) {
                                            $ScreenImageJingle = "\n| Jingle = ". $JingleCsv->at($ScreenImageCsv->at($Argument)['Jingle'])['unknown_1'];
                                        }
                                        $CustomTalkArray[] = "| ScreenImage = ". $ScreenImageImage .".png". $ScreenImageJingle ."";
                                        //DEBUG $CustomTalkArray[] = "". $Instruction ." | ". $Argument ." -> SCREENIMAGE : ". $ScreenImageImage ."\nJINGLE : ". $ScreenImageJingle ."";
                                    break;
                                    case (strpos($Instruction, 'HOWTO') !== false):
                                        $HowToTitle = $HowToCsv->at($Argument)['unknown_1'];
                                        $HowToCategory = $HowToCategoryCsv->at($HowToCsv->at($Argument)['Category'])['Category'];
                                        $HowToImagesArray = [];
                                        foreach(range(0,9) as $b) {
                                            if ($HowToCsv->at($Argument)["Images[$b]"] == 0) continue;
                                            $HowToStringArray = [];
                                            foreach(range(5,7) as $c) {
                                                if (!empty($HowToPageCsv->at($HowToCsv->at($Argument)["Images[$b]"])["unknown_$c"])) {
                                                    $HowToStringArray[] = "". $HowToPageCsv->at($HowToCsv->at($Argument)["Images[$b]"])["unknown_$c"] ."";
                                                }
                                            }
                                            $HowToPageImage = $HowToPageCsv->at($HowToCsv->at($Argument)["Images[$b]"])["Image"];
                                            $HowToString = implode("\n", $HowToStringArray);
                                            $HowToImagesArray[] = "| Image_". $b ." = ". $HowToPageImage .".png\n| String_". $b ." = ". $HowToString ."";
                                        }
                                        $HowToImplode = implode("\n", $HowToImagesArray);
                                        $HowToArray[] = "{{HowToTemplate?\n| Title = ". $HowToTitle."\n| Category = ". $HowToCategory ."\n". $HowToImplode ."\n}}";
                                        break;
                                    break;
                                    case (strpos($Instruction, 'LOG') !== false):
                                        $LogMessage = $LogMessageCsv->at($Argument)['Text'];
                                        $CustomTalkArray[] = "". $Instruction ." | ". $Argument ." -> LOGMESSAGE -> \n". $LogMessage ."";
                                    break;
                                    case (strpos($Instruction, 'DISPOSAL') !== false):
                                        $ShopName = $DisposalShopCsv->at($Argument)['ShopName'];
                                        $DisposalShopArray = [];
                                        foreach(range(0,400) as $b) {
                                            $SubDataValue = "". $Argument .".". $b ."";
                                            if (empty($DisposalShopItemCsv->at($SubDataValue)['Item{Received}'])) break;
                                            $ItemReceived = $ItemCsv->at($DisposalShopItemCsv->at($SubDataValue)['Item{Received}'])['Name'];
                                            $QuantityReceived = $DisposalShopItemCsv->at($SubDataValue)['Quantity{Received}'];
                                            $ItemDisposed = $ItemCsv->at($DisposalShopItemCsv->at($SubDataValue)['Item{Disposed}'])['Name'];
                                            $DisposalShopArray[] = "Dispose 1 x ". $ItemDisposed ." to gain ". $QuantityReceived ." x ". $ItemReceived ."";
                                            $NumberItems = $b + 1;
                                        }
                                        $DisposalOutput = implode("\n", $DisposalShopArray);
                                        $ShopsLinkArray[] = $ShopName;
                                        $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $ShopName ."'''\n|NPC Name = ". $Name ."\n". $ShopName ."\n| TotalItems = ". $NumberItems ."\n". $DisposalOutput ."}}\n{{-stop-}}";
                                    break;

                                    //case (($Argument > 1000000) && ($Argument < 1099999)):
                                    //    $InstructionNPC = $ENpcResidentCsv->at($Argument)['Singular'];
                                    //    $CustomTalkArray[] = "". $Instruction ." | ". $Argument ." -> ENPC -> ". $InstructionNPC ."";
                                    //break;
                                    case (($Argument > 65535) && ($Argument < 69999)):
                                        $InstructionQuest = $QuestCsv->at($Argument)['Name'];
                                        $CustomTalkArray[] = "";
                                        $InvolvedInQuestArray[] = $QuestCsv->at($Argument)['Name'];
                                    break;
                                    
                                    default:
                                        //$CustomTalkArray[] = "". $Instruction ." | ". $Argument ."";
                                        $CustomTalkArray[] = "";
                                    break;
                                }
                            }
                            //nesthandlers
                            if (!empty($CustomTalkNestHandlersCsv->at("". $DataValue. ".1")['NestHandler'])){
                                foreach(range(1,99) as $b) {
                                    if (empty($CustomTalkNestHandlersCsv->at("". $DataValue. ".". $b ."")['NestHandler'])) break;
                                    $NestDataValue = $CustomTalkNestHandlersCsv->at("". $DataValue. ".". $b ."")['NestHandler'];
                                    switch (true) {
                                        case ($NestDataValue > 2883500) && ($NestDataValue < 2889999)://ContentEntry
                                        break;
                                        case ($NestDataValue > 262100) && ($NestDataValue < 269999)://Gilshop
                                            $GilShopName = $GilShopCsv->at($NestDataValue)['Name'];
                                            $GilShopIcon = $GilShopCsv->at($NestDataValue)['Icon'];
                                            $GilShopRequiredQuest = "";
                                            if (!empty($QuestCsv->at($GilShopCsv->at($NestDataValue)['Quest'])['Name'])) {
                                                $GilShopRequiredQuest = "\nQuest : ". $QuestCsv->at($GilShopCsv->at($NestDataValue)['Quest'])['Name'];
                                            }
                                            $DefaultTalkAccept = $this->getDefaultTalk($DefaultTalkCsv, $GilShopCsv, $NestDataValue ,'AcceptTalk', '{{Dialoguebox3|Intro=Success Talk |Dialogue='). "\n}}";
                                            $DefaultTalkFail = $this->getDefaultTalk($DefaultTalkCsv, $GilShopCsv, $NestDataValue ,'FailTalk', '{{Dialoguebox3|Intro=Fail Talk |Dialogue='). "\n}}";
                                            $GilShopItemArray = [];
                                            foreach(range(0,50) as $c) {
                                                $GilShopSubArray = "". $NestDataValue . "." . $c ."";
                                                if (!empty($ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"])) {
                                                    $GilShopSellsItem = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"];
                                                    $GilShopSellsItemCost = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Price{Mid}"];
                                                    $RowRequiredArray = [];
                                                    foreach(range(0,2) as $d) {
                                                        if (empty($QuestCsv->at($GilShopItemCsv->at($GilShopSubArray)["Row{Required}[$d]"])["Name"])) continue;
                                                        $RequiredQuest = $QuestCsv->at($GilShopItemCsv->at($GilShopSubArray)["Row{Required}[$d]"])["Name"];
                                                        $RowRequiredArray[] = "|". $RequiredQuest;
                                                    }
                                                    $NumberItems = $c + 1;
                                                    $RowRequired = implode("\n", $RowRequiredArray);
                                                    $GilShopItemArray[] = "     {{Sells|" . $GilShopSellsItem . "|" . $GilShopSellsItemCost . "". $RowRequired. "}}";
                                                }
                                            }
                                            $GilShopItemArrayOutput = implode("\n", $GilShopItemArray);
                                            $ShopsLinkArray[] = $GilShopName;
                                            $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $GilShopName ."'''\n|NPC Name = ". $Name ."\n\n". $GilShopName ."\nIcon : ". $GilShopIcon ."". $GilShopRequiredQuest ."\n| TotalItems = ". $NumberItems ."\n". $GilShopItemArrayOutput ."\n|Dialogue=\n". $DefaultTalkAccept. "". $DefaultTalkFail. "\n}}\n{{-stop-}}";
                                        break;
                                        case ($NestDataValue > 1769000) && ($NestDataValue < 1779999)://SPECIALSHOP
                                            $SpecialShopID = $NestDataValue;
                                            $SpecialShopOutput = $this->getSpecialShop($ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $SpecialShopID);
                                            $SpecialShopName = $SpecialShopCsv->at($SpecialShopID)['Name'];
                                            $UnlockQuest = "";
                                            if (!empty($QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'])){
                                                $UnlockQuest = "\n|Unlock Quest =  ". $QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'];
                                            }
                                            $CompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $NestDataValue ,'CompleteText', '{{Dialoguebox3|Intro=Success Talk |Dialogue='). "\n}}";
                                            $NotCompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $NestDataValue ,'NotCompleteText', '{{Dialoguebox3|Intro=Fail Talk |Dialogue='). "\n}}";
                                            $ShopsLinkArray[] = $SpecialShopName;
                                            $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $SpecialShopName ."'''\n|NPC Name = ". $Name ."\n|Shop Name = ". $SpecialShopName ."\n". $UnlockQuest ."\n". $SpecialShopOutput ."\n|Dialogue=\n". $CompleteTextStringOut ."". $NotCompleteTextStringOut ."}}\n{{-stop-}}";
                                        break;
                                        case ($NestDataValue > 3407872) && ($NestDataValue < 3409999)://LotteryExchangeShop
                                            $NoItemsFail = "";
                                            $NoSpaceFail = "";
                                            $NoRankFail = "";
                                            $LotteryLuaName = $LotteryExchangeShopCsv->at($NestDataValue)["Lua"];
                                            if (!empty($LogMessageCsv->at($LotteryExchangeShopCsv->at($NestDataValue)["LogMessage[0]"])['Text'])) {
                                                $NoItemsFail = "\n|No Items = ". $LogMessageCsv->at($LotteryExchangeShopCsv->at($NestDataValue)["LogMessage[0]"])['Text'];
                                            }
                                            if (!empty($LogMessageCsv->at($LotteryExchangeShopCsv->at($NestDataValue)["LogMessage[1]"])['Text'])) {
                                                $NoSpaceFail = "\n|No Space = ". $LogMessageCsv->at($LotteryExchangeShopCsv->at($NestDataValue)["LogMessage[1]"])['Text'];
                                            }
                                            if (!empty($LogMessageCsv->at($LotteryExchangeShopCsv->at($NestDataValue)["Unknown_69"])['Text'])) {
                                                $NoRankFail = "\n|Rank Fail = ". $LogMessageCsv->at($LotteryExchangeShopCsv->at($NestDataValue)["Unknown_69"])['Text'];
                                            }
                                            $LotteryItemArray = [];
                                            foreach(range(0,15) as $c) {
                                                $LotItem = $ItemCsv->at($LotteryExchangeShopCsv->at($NestDataValue)["ItemAccepted[$c]"])['Name'];
                                                $AmountAccepted = $LotteryExchangeShopCsv->at($NestDataValue)["AmountAcceptedAccepted[$c]"];
                                                $LotteryItemArray[] = "|Item Accepted = $LotItem x $AmountAccepted";
                                            }
                                            $LotteryItems = implode("\n", $LotteryItemArray);
                                            //construct
                                            $ShopsLinkArray[] = "ExchangeShop";
                                            $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/ExchangeShop'''\n|NPC Name = ". $Name ."\n". $LotteryItems ."". $NoItemsFail ."". $NoSpaceFail ."". $NoRankFail ."\n{{-stop-}}";
                                        break;
                                        case ($NestDataValue > 3470000) && ($NestDataValue < 3479999)://disposal
                                            $ShopName = $DisposalShopCsv->at($NestDataValue)['ShopName'];
                                            $DisposalShopArray = [];
                                            foreach(range(0,400) as $c) {
                                                $SubDataValue = "". $NestDataValue .".". $c ."";
                                                if (empty($DisposalShopCsv->at($SubDataValue)['Item{Received}'])) break;
                                                $ItemReceived = $ItemCsv->at($DisposalShopCsv->at($SubDataValue)['Item{Received}'])['Name'];
                                                $QuantityReceived = $DisposalShopCsv->at($SubDataValue)['Quantity{Received}'];
                                                $ItemDisposed = $ItemCsv->at($DisposalShopCsv->at($SubDataValue)['Item{Disposed}'])['Name'];
                                                $DisposalShopArray[] = "Dispose 1 x ". $ItemDisposed ." to gain ". $QuantityReceived ." x ". $ItemReceived ."";
                                            }
                                            $DisposalOutput = implode("\n", $DisposalShopArray);
                                            $ShopsLinkArray[] = $ShopName;
                                            $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $ShopName ."'''\n". $ShopName ."\n". $DisposalOutput ."}}\n{{-stop-}}";
                                        break;
                                        default:
                                        break;
                                    }
                                }
                            }
                            $CustomTalkArrayImploded = implode("\n", $CustomTalkArray);
                            $SwitchOutput = "\n". $OptionsConstructor ."\n". $CustomTalkArrayImploded ."";
                        break;
                        case ($DataValue > 910000) && ($DataValue < 919999): //CRAFT LEVE
                            $SwitchOutput = "CRAFTLEVE -> ". $LeveCsv->at($CraftLeveCsv->at($DataValue)['Leve'])['Name']. "";
                        break;
                        case ($DataValue > 1179000) && ($DataValue < 1179999): //CHOCOBOTAXISTAND
                            $Routes = [];
                            foreach(range(0,7) as $b) {
                                if (empty($ChocoboTaxiStandCsv->at($ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($DataValue)["TargetLocations[$b]"])['Location'])['PlaceName'])) continue;
                                $Routes[] = "|Route ". $b ." = ". $ChocoboTaxiStandCsv->at($ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($DataValue)["TargetLocations[$b]"])['Location'])['PlaceName'] ."\n|Route ". $b ." Time = ". $ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($DataValue)["TargetLocations[$b]"])['Fare'] ."\n|Route ". $b ." Cost = ". $ChocoboTaxiCsv->at($ChocoboTaxiStandCsv->at($DataValue)["TargetLocations[$b]"])['TimeRequired'] ."";
                            }
                            $RouteOut = implode("\n", $Routes);
                            $ChocoboArray[] = "\n|Location = ". $ChocoboTaxiStandCsv->at($DataValue)['PlaceName'] ."\n". $RouteOut ."";
                        break;
                        case ($DataValue > 1440000) && ($DataValue < 1449999):
                            $SwitchOutput = "GCSHOP ". $DataValue ." ( ". $GrandCompanyCsv->at($GCShopCsv->at($DataValue)['GrandCompany'])['Name'] ." )";
                        break;
                        case ($DataValue > 1507000) && ($DataValue < 1509999):
                            $SwitchOutput = "GUILDORDERGUIDE";
                        break;
                        case ($DataValue > 1570000) && ($DataValue < 1579999):
                            $SwitchOutput = "GUILDORDEROFFICER";
                        break;
                        case ($DataValue > 1700000) && ($DataValue < 1709999):
                            //$SwitchOutput = "STORY (just gives a list of quests, not sure how it works)";
                        break;
                        case ($DataValue > 1769000) && ($DataValue < 1779999)://SPECIALSHOP
                            $SpecialShopID = $DataValue;
                            $SpecialShopOutput = $this->getSpecialShop($ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $SpecialShopID);
                            $SpecialShopName = $SpecialShopCsv->at($SpecialShopID)['Name'];
                            $UnlockQuest = "";
                            if (!empty($QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'])){
                                $UnlockQuest = "|Unlock Quest = ". $QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'];
                            }
                            $CompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'CompleteText', '{{Dialoguebox3|Intro=Success Talk |Dialogue='). "\n}}";
                            $NotCompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'NotCompleteText', '{{Dialoguebox3|Intro=Fail Talk |Dialogue='). "\n}}";
                            $ShopsLinkArray[] = $SpecialShopName;
                            $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $SpecialShopName ."'''\n|NPC Name = ". $Name ."\n|Shop Name = ". $SpecialShopName ."\n". $UnlockQuest ."\n". $SpecialShopOutput ."\n|Dialogue=\n". $CompleteTextStringOut ."". $NotCompleteTextStringOut ."}}\n{{-stop-}}";
                        break;
                        case ($DataValue > 2030000) && ($DataValue < 2039999)://SWITCHTALK
                            foreach(range(0,20) as $b) {
                                $SubDataValue = "". $DataValue .".". $b ."";
                                if (empty($SwitchTalkVariationCsv->at($SubDataValue)['DefaultTalk'])) break;
                                $Quest0 = "";
                                $Quest1 = "";
                                if (!empty($QuestCsv->at($SwitchTalkVariationCsv->at($SubDataValue)['Quest[0]'])['Name'])) {
                                    $Quest0 = "". $QuestCsv->at($SwitchTalkVariationCsv->at($SubDataValue)['Quest[0]'])['Name'];

                                }
                                $TextStringArray = [];
                                foreach(range(0,2) as $c) {
                                    if ($DefaultTalkCsv->at($SwitchTalkVariationCsv->at($SubDataValue)["DefaultTalk"])["Text[$c]"] === "0") continue;
                                    $TextStringArray[] = $DefaultTalkCsv->at($SwitchTalkVariationCsv->at($SubDataValue)["DefaultTalk"])["Text[$c]"];
                                }
                                $TextString = implode("\n", $TextStringArray);
                                if (empty($Quest0)) {
                                    $SwitchTalkArray[] = "{{Dialoguebox3|Intro=Default|Dialogue=\n". $TextString ."}}\n";
                                }
                                if (!empty($Quest0)) {
                                    $SwitchTalkArray[] = "{{Dialoguebox3|Intro=After|Quest=". $Quest0 ."|Dialogue=\n". $TextString ."}}\n";
                                }
                            }
                            
                            break;
                        break;
                        case ($DataValue > 2290000) && ($DataValue < 2299999)://TRIPLETRIAD
                            $RewardsArray = [];
                            foreach(range(0,3) as $b) {
                                if (empty($ItemCsv->at($TripleTriadCsv->at($DataValue)["Item{PossibleReward}[$b]"])['Name'])) continue;
                                $RewardsArray[] = str_replace(" Card", "", $ItemCsv->at($TripleTriadCsv->at($DataValue)["Item{PossibleReward}[$b]"])['Name']);
                            }
                            $Rewards = implode(",", $RewardsArray);
                            $PreviousQuestsArray = [];
                            foreach(range(0,2) as $b) {
                                if (empty($QuestCsv->at($TripleTriadCsv->at($DataValue)["PreviousQuest[$b]"])['Name'])) continue;
                                $PreviousQuestsArray[] = $QuestCsv->at($TripleTriadCsv->at($DataValue)["PreviousQuest[$b]"])['Name'];
                            }
                            if (!empty($PreviousQuestsArray)) {
                                $PreviousQuests = "\n|Required Quests = ". implode(",", $PreviousQuestsArray);
                            }
                            else {
                                $PreviousQuests = "";
                            }

                            //TALK
                            
                            $ChallengeTextStringArray = [];
                            foreach(range(0,2) as $b) {
                                if ($DefaultTalkCsv->at($TripleTriadCsv->at($DataValue)["DefaultTalk{Challenge}"])["Text[$b]"] === "0") continue;
                                //$ChallengeTextStringArray[0] = "\n| Challenge = ";
                                $ChallengeTextStringArray[] = "". $DefaultTalkCsv->at($TripleTriadCsv->at($DataValue)["DefaultTalk{Challenge}"])["Text[$b]"] ."\n";
                            }
                            $ChallengeTextString = implode("", $ChallengeTextStringArray);
                            
                            $UnavailableTextStringArray = [];
                            foreach(range(0,2) as $b) {
                                if ($DefaultTalkCsv->at($TripleTriadCsv->at($DataValue)["DefaultTalk{Unavailable}"])["Text[$b]"] === "0") continue;
                                //$UnavailableTextStringArray[0] = "\n| Unavailable = ";
                                $UnavailableTextStringArray[] = "". $DefaultTalkCsv->at($TripleTriadCsv->at($DataValue)["DefaultTalk{Unavailable}"])["Text[$b]"] ."\n";
                            }
                            $UnavailableTextString = implode("", $UnavailableTextStringArray);
                            
                            $NPCWinTextStringArray = [];
                            foreach(range(0,2) as $b) {
                                if ($DefaultTalkCsv->at($TripleTriadCsv->at($DataValue)["DefaultTalk{NPCWin}"])["Text[$b]"] === "0") continue;
                                //$NPCWinTextStringArray[0] = "\n| NPCWin = ";
                                $NPCWinTextStringArray[] = "". $DefaultTalkCsv->at($TripleTriadCsv->at($DataValue)["DefaultTalk{NPCWin}"])["Text[$b]"] ."\n";
                            }
                            $NPCWinTextString = implode("", $NPCWinTextStringArray);
                            
                            $DrawTextStringArray = [];
                            foreach(range(0,2) as $b) {
                                if ($DefaultTalkCsv->at($TripleTriadCsv->at($DataValue)["DefaultTalk{Draw}"])["Text[$b]"] === "0") continue;
                                //$DrawTextStringArray[0] = "\n| PCWin = ";
                                $DrawTextStringArray[] = "". $DefaultTalkCsv->at($TripleTriadCsv->at($DataValue)["DefaultTalk{Draw}"])["Text[$b]"] ."\n";
                            }
                            $DrawTextString = implode("", $DrawTextStringArray);
                            
                            $PCWinTextStringArray = [];
                            foreach(range(0,2) as $b) {
                                if ($DefaultTalkCsv->at($TripleTriadCsv->at($DataValue)["DefaultTalk{PCWin}"])["Text[$b]"] === "0") continue;
                                //$PCWinTextStringArray[0] = "\n| Draw = ";
                                $PCWinTextStringArray[] = "". $DefaultTalkCsv->at($TripleTriadCsv->at($DataValue)["DefaultTalk{PCWin}"])["Text[$b]"] ."\n";
                            }
                            $PCWinTextString = implode("", $PCWinTextStringArray);

                            $TextStringOutput = "|Challenge = ". $ChallengeTextString ."|Unavailable = ". $UnavailableTextString ."|NPCWin = ". $NPCWinTextString ."|PCWin = ". $DrawTextString ."|NPCDraw = ". $PCWinTextString ."";


                            //UsesCards : 
                            
                            $UsesCardsFixedArray = [];
                            foreach(range(0,4) as $b) {
                                if (empty($TripleTriadCardCsv->at($TripleTriadCsv->at($DataValue)["TripleTriadCard{Fixed}[$b]"])["Name"])) continue;
                                $UsesCardsFixedArray[] = $TripleTriadCardCsv->at($TripleTriadCsv->at($DataValue)["TripleTriadCard{Fixed}[$b]"])["Name"];
                            }
                            $UsesCardsFixed = implode(",", $UsesCardsFixedArray);

                            
                            $UsesCardsVariableArray = [];
                            foreach(range(0,4) as $b) {
                                if (empty($TripleTriadCardCsv->at($TripleTriadCsv->at($DataValue)["TripleTriadCard{Variable}[$b]"])["Name"])) continue;
                                $UsesCardsVariableArray[] = $TripleTriadCardCsv->at($TripleTriadCsv->at($DataValue)["TripleTriadCard{Variable}[$b]"])["Name"];
                            }
                            $UsesCardsVariable = implode(",", $UsesCardsVariableArray);

                            $Fee = $TripleTriadCsv->at($DataValue)["Fee"];
                            
                            $RegionalRules = $TripleTriadCsv->at($DataValue)["UsesRegionalRules"];
                            
                            //RULES
                            
                            $RulesArray = [];
                            foreach(range(0,1) as $b) {
                                if (empty($TripleTriadRuleCsv->at($TripleTriadCsv->at($DataValue)["TripleTriadRule[$b]"])["Name"])) continue;
                                $RulesArray[] = $TripleTriadRuleCsv->at($TripleTriadCsv->at($DataValue)["TripleTriadRule[$b]"])["Name"];
                            }
                            $Rules = implode(",", $RulesArray);
                            $SwitchOutput = "";
                            // THIS IS UNMERGED DECK ==== $TripleTriadArray[] = "{{TripleTriadTemplate?\n| Fee = ". $Fee ."\n| RegionalRules = ". $RegionalRules ."\n| Rules = ". $Rules ."\n| Rewards = ". $Rewards ."". $PreviousQuests ."\n| FixedCards = ". $UsesCardsFixed ."\n| VariableCards = ". $UsesCardsVariable ."\n". $TextStringOutput ."}}";

                            $TripleTriadArray[] = "{{TripleTriadTemplate?\n|Name=$NpcName\n|Fee = ". $Fee ."\n|RegionalRules = ". $RegionalRules ."\n|Rules = ". $Rules ."\n|Rewards = ". $Rewards ."". $PreviousQuests ."\n|Deck = ". $UsesCardsFixed .",". $UsesCardsVariable ."\n". $TextStringOutput ."}}";
                        break;
                        case ($DataValue > 2752000) && ($DataValue < 2752999)://FCCSHOP
                            $FCCShopItemArray = [];
                            $ShopName = $FCCShopCsv->at($DataValue)['Name'];
                            foreach(range(0,9) as $b) {
                                if (empty($ItemCsv->at($FCCShopCsv->at($DataValue)["Item[$b]"])['Name'])) continue;
                                $Item = $ItemCsv->at($FCCShopCsv->at($DataValue)["Item[$b]"])['Name'];
                                $Cost = $FCCShopCsv->at($DataValue)["Cost[$b]"];
                                $Rank = $FCCShopCsv->at($DataValue)["FCRank{Required}[$b]"];
                                $FCCShopItemArray[] = "Item = ". $Item ." x 1 | Rank Required = ". $Rank ." Costs = ". $Cost ."";
                            }
                            $FCCShopItems = implode("\n", $FCCShopItemArray);
                            $ShopsLinkArray[] = $ShopName;
                            $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $ShopName ."'''\n|NPC Name = ". $Name ."\n\n". $ShopName ." ->\n". $FCCShopItems ."}}\n{{-stop-}}";
                        break;
                        case ($DataValue > 3080000) && ($DataValue < 3089999): //DPSCHALLENGEOFFICER
                            $ChallengeArray = [];
                            foreach(range(0,20) as $b) {
                                if (empty($DpsChallengeCsv->at($DpsChallengeOfficerCsv->at($DataValue)["ChallengeName[$b]"])['Name'])) continue;
                                $ChallengeArray[] = $DpsChallengeCsv->at($DpsChallengeOfficerCsv->at($DataValue)["ChallengeName[$b]"])['Name'];
                            }
                            $ChallengeStrings = implode("\n", $ChallengeArray);
                            $SwitchOutput = "DPSCHALLENGEOFFICER ->\n". $ChallengeStrings. "";
                        break;
                        case ($DataValue > 3270000) && ($DataValue < 3279999)://TOPIC SELECT
                            $TopicSelectName = $TopicSelectCsv->at($DataValue)["Name"];
                            $TopicSelectArray = [];
                            $ShopOutput = false;
    
                            foreach(range(0,9) as $b) {
                                if ($TopicSelectCsv->at($DataValue)["Shop[$b]"] == 0) continue;
                                $ShopLink = $TopicSelectCsv->at($DataValue)["Shop[$b]"];
    
                                if ($ShopLink >= 262000 && $ShopLink < 264000) { // links to GilShop
                                    $ShopName = $GilShopCsv->at($ShopLink)["Name"];
                                    //$ShopNameItems = $ItemCsv->at($GilShopItemCsv->at($ShopLink)["Item"])["Name"];
                                    $GilShopItemArray = [];
                                    foreach(range(0,50) as $b) {
                                        $GilShopSubArray = "". $ShopLink . "." . $b ."";
                                        if (!empty($ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"])) {
                                            $GilShopSellsItem = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"];
                                            $GilShopSellsItemCost = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Price{Mid}"];
                                            $GilShopItemArray[] = "{{Sells|" . $GilShopSellsItem . "|" . $GilShopSellsItemCost . "}}";
                                        }
                                    }
                                    $GilShopItemArrayOutput = implode("\n", $GilShopItemArray);
                                    $ShopOutput = "|". $ShopName ." =\n". $GilShopItemArrayOutput ."\n";
                                }
    
                                if ($ShopLink >= 3538900 && $ShopLink < 3540000) { // links to PreHandler
                                    $ShopID = $PreHandlerCsv->at($ShopLink)["Target"];
                                    if ($ShopID > 262100 && $ShopID < 269999) { //Gilshop
                                        $ShopName = $GilShopCsv->at($ShopID)["Name"];
                                        $GilShopItemArray = [];
                                        foreach(range(0,50) as $c) {
                                            $GilShopSubArray = "". $ShopID . "." . $c ."";
                                            if (!empty($ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"])) {
                                                $GilShopSellsItem = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"];
                                                $GilShopSellsItemCost = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Price{Mid}"];
                                                $GilShopItemArray[] = "{{Sells|" . $GilShopSellsItem . "|" . $GilShopSellsItemCost . "}}";
                                            }
                                        }
                                        $GilShopItemArrayOutput = implode("\n", $GilShopItemArray);
                                        $ShopsLinkArray[] = $ShopName;
                                        $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $ShopName ."'''\n|NPC Name = ". $Name ."\n|Shop Name = ". $ShopName ." =\n{{Tabsells". $GilShopItemArrayOutput ."}}\n{{-stop-}}";
                                    }
                                    if ($ShopID >= 1769000 && $ShopID < 1779999) { //specialshop
                                        $SpecialShopID = $ShopID;
                                        $SpecialShopOutput = $this->getSpecialShop($ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $SpecialShopID);
                                        $SpecialShopName = $SpecialShopCsv->at($SpecialShopID)['Name'];
                                        $UnlockQuest = "";
                                        if (!empty($QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'])){
                                            $UnlockQuest = "\n|Unlock Quest =  ". $QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'];
                                        }
                                        $CompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'CompleteText', '{{Dialoguebox3|Intro=Success Talk |Dialogue='). "\n}}";
                                        $NotCompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'NotCompleteText', '{{Dialoguebox3|Intro=Success Fail |Dialogue='). "\n}}";
                                        
                                        $ShopsLinkArray[] = $SpecialShopName;
                                        $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $SpecialShopName ."'''\n|NPC Name = ". $Name ."\n|Shop Name = ". $SpecialShopName ."". $UnlockQuest ."\n". $SpecialShopOutput ."\n|Dialogue=\n". $CompleteTextStringOut ."". $NotCompleteTextStringOut ."}}\n{{-stop-}}";
                                    }
                                    if ($ShopID >= 3866620 && $ShopID < 3866999) { //COLLECTABLESHOPS
                                        $ShopName = $CollectablesShopCsv->at($ShopID)['Name'];
                                        $RequiredQuest = $QuestCsv->at($CollectablesShopCsv->at($ShopID)['Quest'])['Name'];
                                        $CollectableItemArray = [];
                                        foreach(range(0,10) as $c) {
                                            if (empty($ItemCsv->at($CollectablesShopItemCsv->at($CollectablesShopCsv->at($ShopID)["ShopItems[$c]"])['Item'])['Name'])) continue;
                                            $CollectableItemArray[] = $ItemCsv->at($CollectablesShopItemCsv->at($CollectablesShopCsv->at($ShopID)["ShopItems[$c]"])['Item'])['Name'];
                                        }
                                        $CollectableItems = implode("\n", $CollectableItemArray);
                                        
                                        $ShopsLinkArray[] = $ShopName;
                                        $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $ShopName ."'''\n|NPC Name = ". $Name ."\n|Shop Name = ". $ShopName ."\nRequired Quest = ". $RequiredQuest ."->\n". $CollectableItems ."}}\n{{-stop-}}";
                                    }
                                    if ($ShopID >= 3801000 && $ShopID < 3809999) { //INCLUSIONSHOP
                                        $InclusionShopArray = [];
                                        foreach(range(0,29) as $c) {
                                            if (empty($InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$c]"])['Name'])) continue;
                                            $CategoryName = $InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$c]"])['Name'];
                                            $ClassJobCategory = "";
                                            if (!empty($ClassJobCategoryCsv->at($InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$c]"])['ClassJobCategory'])['Name'])){
                                                $ClassJobCategory = $ClassJobCategoryCsv->at($InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$c]"])['ClassJobCategory'])['Name'];
                                            }
                                            $SeriesLink = $InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$c]"])['InclusionShopSeries'];
                                            $SeriesArray = [];
                                            foreach(range(0,20) as $d) {
                                                $SubDataValue = "". $DataValue .".". $d ."";
                                                if (empty($InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'])) break;
                                                $SpecialShopID = $SubDataValue;
                                                $SpecialShopOutput = $this->getSpecialShop($ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $SpecialShopID);
                                                $SpecialShopName = $SpecialShopCsv->at($SpecialShopID)['Name'];
                                                $UnlockQuest = "";
                                                if (!empty($QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'])){
                                                    $UnlockQuest = "\n|Unlock Quest =  ". $QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'];
                                                }
                                                $CompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'CompleteText', '{{Dialoguebox3|Intro=Success Talk |Dialogue='). "\n}}";
                                                $NotCompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'NotCompleteText', '{{Dialoguebox3|Intro=Fail Talk |Dialogue='). "\n}}";
                                                $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $SpecialShopName ."'''\n|NPC Name = ". $Name ."\n|Shop Name = ". $SpecialShopName ."". $UnlockQuest ."\n". $SpecialShopOutput ."\n|Dialogue=\n". $CompleteTextStringOut ."". $NotCompleteTextStringOut ."\n}}\n{{-stop-}}";
                                                
                                                $ShopsLinkArray[] = $SpecialShopName;
                                            }
                                            $SeriesOutput = implode("\n", $SeriesArray);

                                            $InclusionShopArray[] = "Name : ". $CategoryName ."\nClassJob : ". $ClassJobCategory. "\n". $SeriesOutput ."";
                                        }
                                        $SSOutput = implode("\n", $InclusionShopArray);
                                        //$ShopOutput = "TOPICSELECT -> PREHANDLER -> INCLUSIONSHOP ->  ". $SSOutput ."";
                                    }
                                    if ($ShopID >= 3604400 && $ShopID < 3609999) { //DESCRIPTION
                                        $Description = $DescriptionCsv->at($ShopID)['Text[Long]'];
                                    }
                                }
    
                                if ($ShopLink >= 1769000 && $ShopLink < 1779999) { // links to SpecialShop
                                    $SpecialShopID = $ShopLink;
                                    $SpecialShopOutput = $this->getSpecialShop($ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $SpecialShopID);
                                    $SpecialShopName = $SpecialShopCsv->at($SpecialShopID)['Name'];
                                    $UnlockQuest = "";
                                    if (!empty($QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'])){
                                        $UnlockQuest = "\n|Unlock Quest =  ". $QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'];
                                    }
                                    $CompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'CompleteText', '{{Dialoguebox3|Intro=Success Talk |Dialogue='). "\n}}";
                                    $NotCompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'NotCompleteText', '{{Dialoguebox3|Intro=Fail Talk |Dialogue='). "\n}}";
                                    $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $SpecialShopName ."'''\n|NPC Name = ". $Name ."\n|Shop Name = ". $SpecialShopName ."". $UnlockQuest ."\n". $SpecialShopOutput ."\n|Dialogue=\n". $CompleteTextStringOut ."". $NotCompleteTextStringOut ."\n}}\n{{-stop-}}";
                                    
                                    $ShopsLinkArray[] = $SpecialShopName;
                                }
                                $TopicSelectArray[] = $ShopOutput;
                            }
                            $TopicSelectOutputOld = implode("\n", $TopicSelectArray);
                            $ShopOutputData = "\n". $TopicSelectName ."= \n". $TopicSelectOutputOld ."";
                            //$ShopArray[] = "". $ShopOutputData ."";
                        break;
                        case ($DataValue > 3470000) && ($DataValue < 3479999):
                            $ShopName = $DisposalShopCsv->at($DataValue)['ShopName'];
                            $DisposalShopArray = [];
                            foreach(range(0,400) as $b) {
                                $SubDataValue = "". $DataValue .".". $b ."";
                                if (empty($DisposalShopCsv->at($SubDataValue)['Item{Received}'])) break;
                                $ItemReceived = $ItemCsv->at($DisposalShopCsv->at($SubDataValue)['Item{Received}'])['Name'];
                                $QuantityReceived = $DisposalShopCsv->at($SubDataValue)['Quantity{Received}'];
                                $ItemDisposed = $ItemCsv->at($DisposalShopCsv->at($SubDataValue)['Item{Disposed}'])['Name'];
                                $DisposalShopArray[] = "Dispose 1 x ". $ItemDisposed ." to gain ". $QuantityReceived ." x ". $ItemReceived ."";
                            }
                            $DisposalOutput = implode("\n", $DisposalShopArray);
                            $ShopsLinkArray[] = $ShopName;
                            $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $ShopName ."'''\n". $ShopName ."\n". $DisposalOutput ."}}\n{{-stop-}}";
                        break;
                        case ($DataValue > 3530000) && ($DataValue < 3539999)://PREHANDLER
                            $ShopID = $PreHandlerCsv->at($DataValue)["Target"];
                                    if ($ShopID > 262100 && $ShopID < 269999) { //Gilshop
                                        $ShopName = $GilShopCsv->at($ShopID)["Name"];
                                        $GilShopItemArray = [];
                                        foreach(range(0,50) as $c) {
                                            $GilShopSubArray = "". $ShopID . "." . $c ."";
                                            if (!empty($ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"])) {
                                                $GilShopSellsItem = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Name"];
                                                $GilShopSellsItemCost = $ItemCsv->at($GilShopItemCsv->at($GilShopSubArray)["Item"])["Price{Mid}"];
                                                $GilShopItemArray[] = "{{Sells|" . $GilShopSellsItem . "|" . $GilShopSellsItemCost . "}}";
                                            }
                                        }
                                        $GilShopItemArrayOutput = implode("\n", $GilShopItemArray);
                                        $ShopsLinkArray[] = $ShopName;
                                        $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $ShopName ."'''\n|NPC Name = ". $Name ."\n\n". $ShopName ." =\n{{Tabsells". $GilShopItemArrayOutput ."\n{{-stop-}}";
                                    }
                                    if ($ShopID >= 1769000 && $ShopID < 1779999) { //specialshop
                                        $SpecialShopID = $ShopID;
                                        $SpecialShopOutput = $this->getSpecialShop($ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $SpecialShopID);
                                        $SpecialShopName = $SpecialShopCsv->at($SpecialShopID)['Name'];
                                        $UnlockQuest = "";
                                        if (!empty($QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'])){
                                            $UnlockQuest = "\n|Unlock Quest =  ". $QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'];
                                        }
                                        $CompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'CompleteText', '{{Dialoguebox3|Intro=Success Talk |Dialogue='). "\n}}";
                                        $NotCompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'NotCompleteText', '{{Dialoguebox3|Intro=Fail Talk |Dialogue='). "\n}}";
                                        $ShopsLinkArray[] = $SpecialShopName;
                                        $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $SpecialShopName ."'''\n|NPC Name = ". $Name ."\n|Shop Name = ". $SpecialShopName ."". $UnlockQuest ."\n". $SpecialShopOutput ."\n|Dialogue=\n". $CompleteTextStringOut ."". $NotCompleteTextStringOut ."\n}}\n{{-stop-}}";
                                    }
                                    if ($ShopID >= 3866620 && $ShopID < 3866999) { //COLLECTABLESHOPS
                                        $ShopName = $CollectablesShopCsv->at($ShopID)['Name'];
                                        $RequiredQuest = $QuestCsv->at($CollectablesShopCsv->at($ShopID)['Quest'])['Name'];
                                        $CollectableItemArray = [];
                                        foreach(range(0,10) as $c) {
                                            if (empty($ItemCsv->at($CollectablesShopItemCsv->at($CollectablesShopCsv->at($ShopID)["ShopItems[$c]"])['Item'])['Name'])) continue;
                                            $CollectableItemArray[] = $ItemCsv->at($CollectablesShopItemCsv->at($CollectablesShopCsv->at($ShopID)["ShopItems[$c]"])['Item'])['Name'];
                                        }
                                        $CollectableItems = implode("\n", $CollectableItemArray);
                                        
                                        $ShopsLinkArray[] = $ShopName;
                                        $ShopArray[] = "\n{{-start-}}\n'''". $Name ."/". $ShopName ."'''\n|NPC Name = ". $Name ."\n|Shop Name = ". $ShopName ."\nRequired Quest = ". $RequiredQuest ."->\n". $CollectableItems ."}}\n{{-stop-}}";
                                    }
                                    if ($ShopID >= 3801000 && $ShopID < 3809999) { //INCLUSIONSHOP
                                        $InclusionShopArray = [];
                                        foreach(range(0,29) as $c) {
                                            if (empty($InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$c]"])['Name'])) continue;
                                            $CategoryName = $InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$c]"])['Name'];
                                            $ClassJobCategory = "";
                                            if (!empty($ClassJobCategoryCsv->at($InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$c]"])['ClassJobCategory'])['Name'])){
                                                $ClassJobCategory = $ClassJobCategoryCsv->at($InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$c]"])['ClassJobCategory'])['Name'];
                                            }
                                            $SeriesLink = $InclusionShopCategoryCsv->at($InclusionShopCsv->at($ShopID)["Category[$c]"])['InclusionShopSeries'];
                                            $SeriesArray = [];
                                            foreach(range(0,20) as $d) {
                                                $SubDataValue = "". $DataValue .".". $d ."";
                                                if (empty($InclusionShopSeriesCsv->at($SubDataValue)['SpecialShop'])) break;
                                                $SpecialShopID = $SubDataValue;
                                                $SpecialShopOutput = $this->getSpecialShop($ItemCsv, $AchievementCsv, $QuestCsv, $SpecialShopCsv, $SpecialShopID);
                                                $SpecialShopName = $SpecialShopCsv->at($SpecialShopID)['Name'];
                                                $UnlockQuest = "";
                                                if (!empty($QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'])){
                                                    $UnlockQuest = "\n|Unlock Quest =  ". $QuestCsv->at($SpecialShopCsv->at($SpecialShopID)['Quest{Unlock}'])['Name'];
                                                }
                                                $CompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'CompleteText', '{{Dialoguebox3|Intro=Success Talk |Dialogue='). "\n}}";
                                                $NotCompleteTextStringOut = $this->getDefaultTalk($DefaultTalkCsv, $SpecialShopCsv, $DataValue ,'NotCompleteText', '{{Dialoguebox3|Intro=Fail Talk |Dialogue='). "\n}}";
                                                $SeriesArray[] = "\n{{-start-}}\n'''". $Name ."/". $SpecialShopName ."'''\n|NPC Name = ". $Name ."\n|Shop Name =  ". $SpecialShopName ."". $UnlockQuest ."\n". $SpecialShopOutput ."\n|Dialogue=\n". $CompleteTextStringOut ."". $NotCompleteTextStringOut ."\n}}\n{{-stop-}}";
                                                $ShopsLinkArray[] = $SpecialShopName;
                                            }
                                            $SeriesOutput = implode("\n", $SeriesArray);

                                            $InclusionShopArray[] = "Name : ". $CategoryName ."\nClassJob : ". $ClassJobCategory. "\n". $SeriesOutput ."";
                                        }
                                        $SSOutput = implode("\n", $InclusionShopArray);
                                        
                                        $ShopArray[] = "". $SSOutput ."";
                                    }
                                    if ($ShopID >= 3604400 && $ShopID < 3609999) { //DESCRIPTION
                                        $Description = $DescriptionCsv->at($ShopID)['Text[Long]'];
                                        $ShopOutput = "". $Description ."";
                                    }
                                    if ($ShopID >= 3473400 && $ShopID < 3479999) { //DISPOSALSHOP
                                        $ShopName = $DisposalShopCsv->at($ShopID)['ShopName'];
                                        $DisposalShopArray = [];
                                        foreach(range(0,400) as $d) {
                                            $SubDataValue = "". $ShopID .".". $b ."";
                                            if (empty($DisposalShopCsv->at($SubDataValue)['Item{Received}'])) break;
                                            $ItemReceived = $ItemCsv->at($DisposalShopCsv->at($SubDataValue)['Item{Received}'])['Name'];
                                            $QuantityReceived = $DisposalShopCsv->at($SubDataValue)['Quantity{Received}'];
                                            $ItemDisposed = $ItemCsv->at($DisposalShopCsv->at($SubDataValue)['Item{Disposed}'])['Name'];
                                            $DisposalShopArray[] = "Dispose 1 x ". $ItemDisposed ." to gain ". $QuantityReceived ." x ". $ItemReceived ."";
                                        }
                                        $DisposalOutput = implode("\n", $DisposalShopArray);
                                        
                                        $ShopsLinkArray[] = $ShopName;
                                        $ShopArray[] =  "\n{{-start-}}\n'''". $Name ."/". $ShopName ."'''\n|NPC Name = ". $Name ."\n\n". $ShopName ."\n". $DisposalOutput ."}}\n{{-stop-}}";
                                    }
                            $SwitchOutput = "". $ShopOutput ."";
                        break;
                        case ($DataValue > 3604000) && ($DataValue < 3609999):
                            $Description = $DescriptionCsv->at($DataValue)['Text[Long]'];
                            $SwitchOutput = "DESCRIPTION -> ". $Description ."";
                        break;
                        
                        default:
                         $SwitchOutput = "UNKNOWN VALUE : " .$ENpcBase["ENpcData[$i]"] . "";
                        break;
                    }
                    $DataArray[] = "". $SwitchOutput ."";
                }
            }
            //contsructor
            if (empty($DataArray)) continue;
            $InvolvedInQuest = implode(",", array_unique($InvolvedInQuestArray));
            $DataArrayOutput = implode("\n", $DataArray);
            $DialogueCheck = "";
            $SwitchTalkOutput = "";
            if (strpos($DataArrayOutput, "<Switch(",)) {
                $DialogueCheck = "| Dialogue = ". $id ."\n";// TODO This needs to be an implode of an array eg. | Dialogue = 1010101, 1010102
                switch (true) {
                    case (strpos($DataArrayOutput, 'PlayerParameter(71)') !== false): //Player Race
                        $incorrectformattingarray = array("<Switch(PlayerParameter(71))>", "<Case(1)>", "<Case(2)>", "<Case(3)>", "<Case(4)>", "<Case(5)>", "<Case(6)>", "<Case(7)>", "<Case(8)>", "<Case(9)>","</Case>", "</Switch>");
                        $correctformattingarray = array("{{Loremtextconditional|", "", "|or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "", "', depending on Race}}");
                        $DataArrayOutput = str_replace($incorrectformattingarray, $correctformattingarray, $DataArrayOutput);
                    break;
                    case (strpos($SwitchTalkOutput, 'PlayerParameter(70)') !== false): //Town
                        $incorrectformattingarray = array("<Switch(PlayerParameter(70))>", "<Case(1)>", "<Case(2)>", "<Case(3)>", "<Case(4)>", "<Case(5)>", "<Case(6)>", "<Case(7)>", "<Case(8)>", "<Case(9)>","</Case>", "</Switch>");
                        $correctformattingarray = array("{{Loremtextconditional|", "", "|or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "", "', if Legacy player}}");
                        $DataArrayOutput = str_replace($incorrectformattingarray, $correctformattingarray, $SwitchTalkOutput);
                    break;
                    case (strpos($SwitchTalkOutput, 'IntegerParameter(1)') !== false): //unknown
                        $incorrectformattingarray = array("<Switch(IntegerParameter(1))>", "<Case(1)>", "<Case(2)>", "<Case(3)>", "<Case(4)>", "<Case(5)>", "<Case(6)>", "<Case(7)>", "<Case(8)>", "<Case(9)>","</Case>", "</Switch>");
                        $correctformattingarray = array("{{Loremtextconditional|", "", "|or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "", "'}}");
                        $DataArrayOutput = str_replace($incorrectformattingarray, $correctformattingarray, $SwitchTalkOutput);
                    break;
                    
                    default:
                        # code...
                    break;
                }
            }
            $InvolvedQuest = "";
            if (!empty($InvolvedInQuestArray) ) {
                $InvolvedQuest = "| InvolvedInQuests = ". $InvolvedInQuest ."\n";
            }
            if (!empty($SwitchTalkArray)) {
                $DialogueCheck = "| Dialogue = ". $id ."\n"; // TODO This needs to be an implode of an array eg. | Dialogue = 1010101, 1010102
                $SwitchTalkOutput = "\n{{-start-}}\n'''". $Name ."/". $id ."/Dialogue'''\n". implode("\n", $SwitchTalkArray). "\n{{-stop-}}";
                if (strpos($SwitchTalkOutput, "<Switch(",)) {
                    switch (true) {
                        case (strpos($SwitchTalkOutput, 'PlayerParameter(71)') !== false): //Player Race
                            $incorrectformattingarray = array("<Switch(PlayerParameter(71))>", "<Case(1)>", "<Case(2)>", "<Case(3)>", "<Case(4)>", "<Case(5)>", "<Case(6)>", "<Case(7)>", "<Case(8)>", "<Case(9)>","</Case>", "</Switch>");
                            $correctformattingarray = array("{{Loremtextconditional|", "", "|or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "", "', depending on Race}}");
                            $SwitchTalkOutput = str_replace($incorrectformattingarray, $correctformattingarray, $SwitchTalkOutput);
                        break;
                        case (strpos($SwitchTalkOutput, 'PlayerParameter(70)') !== false): //OG Player
                            $incorrectformattingarray = array("<Switch(PlayerParameter(70))>", "<Case(1)>", "<Case(2)>", "<Case(3)>", "<Case(4)>", "<Case(5)>", "<Case(6)>", "<Case(7)>", "<Case(8)>", "<Case(9)>","</Case>", "</Switch>");
                            $correctformattingarray = array("{{Loremtextconditional|", "", "|or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "' or '", "", "', if Legacy player}}");
                            $SwitchTalkOutput = str_replace($incorrectformattingarray, $correctformattingarray, $SwitchTalkOutput);
                        break;
                        case (strpos($SwitchTalkOutput, 'IntegerParameter(1)') !== false): //unknown
                            $incorrectformattingarray = array("<Switch(IntegerParameter(1))>", "<Case(1)>", "<Case(2)>", "<Case(3)>", "</Case>", "</Switch>");
                            $correctformattingarray = array("{{Loremtextconditional|", "", "|or '", "' or '", "' or '", "', depending on unknown case}}");
                            $SwitchTalkOutput = str_replace($incorrectformattingarray, $correctformattingarray, $SwitchTalkOutput);
                        break;
                        
                        default:
                            # code...
                        break;
                    }
                }
            }
            $HowToCheck = "";
            $HowToPage = "";
            if (!empty($HowToArray) ) {
                $HowToCheck = "| HowTo = ". $id ."\n";
                $HowToPage = "\n{{-start-}}\n'''". $Name ."/". $id ."/HowTo'''\n". implode("\n", $HowToArray). "\n{{-stop-}}";
            }

            $TripleTriadCheck = "";
            $TripleTriadPage = "";
            if (!empty($TripleTriadArray) ) {
                $TripleTriadCheck = "| TripleTriad = ". $id ."\n";
                $TripleTriadPage = "\n{{-start-}}\n'''". $Name ."/". $id ."/TripleTriad'''\n". implode("\n", $TripleTriadArray). "\n{{-stop-}}";
            }

            

            $ShopPage = "";
            if (!empty($ShopArray) ) {
                $ShopPage = "". implode("\n", $ShopArray). "";
            }
            $ShopsLink = "";
            if (!empty($ShopsLinkArray) ) {
                $ShopsLink = "\n| Shops = ". implode(",", $ShopsLinkArray). "";
            }

            $LotteryPage = "";
            if (!empty($LotteryExchangeShopArray) ) {
                $LotteryPage = "". implode("\n", $LotteryExchangeShopArray). "";
            }

            $WarpCheck = "";
            $WarpPage = "";
            if (!empty($WarpArray) ) {
                $WarpCheck = "| Warp = ". $id ."\n";
                $WarpPage = "\n{{-start-}}\n'''". $Name ."/". $id ."/Warp'''\n<tabber>\n". implode("\n|-|\n", $WarpArray). "\n</tabber>\n{{-stop-}}";
            }

            $ChocoboCheck = "";
            $ChocoboPage = "";
            if (!empty($ChocoboArray) ) {
                $ChocoboCheck = "| Porter = ". $id ."\n";
                $ChocoboPage = "\n{{-start-}}\n'''". $Name ."/". $id ."/Porter'''\n{{Porter\n". implode("\n", $ChocoboArray). "\n}}\n{{-stop-}}";
            }
            //$outputarray[] = "{{-start-}}\n'''". $Name ."'''\n{{NPC\n| NPC Name = ". $Name ."\n| ID = ". $id ."\n". $Race ."". $Gender ."". $Tribe ."\n\n". $BalloonOutput. "". $BalloonSingle ."". $DataArrayOutput ."". $ShopsLink ."". $DialogueCheck ."". $TripleTriadCheck ."". $WarpCheck ."". $ChocoboCheck ."". $HowToCheck ."\n}}\n{{-stop-}}". $SwitchTalkOutput ."". $HowToPage ."". $TripleTriadPage ."". $ChocoboPage ."". $WarpPage ."". $ShopPage ."". $LotteryPage ."\n------------------------------------------\n";

            $NpcOutputArray[$Name][0] = "{{-start-}}\n'''". $Name ."'''\n{{NPC\n| NPC Name = ". $Name ."\n". $Race ."". $Gender ."". $Tribe ."\n\n". $BalloonOutput. "". $BalloonSingle ."". $DataArrayOutput ."". $ShopsLink ."". $DialogueCheck ."". $TripleTriadCheck ."". $WarpCheck ."". $ChocoboCheck ."". $HowToCheck ."\n}}\n{{-stop-}}";
            $NpcOutputArray[$Name][] = "". $SwitchTalkOutput ."". $HowToPage ."". $TripleTriadPage ."". $ChocoboPage ."". $WarpPage ."". $ShopPage ."". $LotteryPage ."\n------------------------------------------\n";
            //$NpcOutputArray[$Name][] =
        }
        $OutputSub = [];

        foreach ($NpcOutputArray as $key => $value) {
            $OutputSub[$key] = implode("\n", $value);
        }
        $output = implode("\n\n\n",str_replace("\n\n\n\n\n", "", $OutputSub)); // because im messy and lazy
        $data = [
            '{output}' => $output,
        ];

        // format using Gamer Escape formatter and add to data array
        // need to look into using item-specific regex, if required.
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        

        // save our data to the filename: GeEventItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("$CurrentPatchOutput/TestNPC - ". $Patch .".txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}