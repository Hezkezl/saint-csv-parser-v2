<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * php bin/console app:parse:csv GE:PatchPopulate
 */


class PatchPopulate implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '{item}';

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');
        /** 
         * CHANGE THE BELOW TO THE SHEET YOU NEED:::::
         */
        $SheetArray = array("Achievement",
        "AchievementCategory",
        "AchievementHideCondition",
        "AchievementKind",
        "Action",
        "ActionCastTimeline",
        "ActionCastVFX",
        "ActionCategory",
        "ActionComboRoute",
        "ActionComboRouteTransient",
        "ActionCostType",
        "ActionIndirection",
        "ActionInit",
        "ActionParam",
        "ActionProcStatus",
        "ActionTimeline",
        "ActionTimelineMove",
        "ActionTimelineReplace",
        "ActionTransient",
        "ActivityFeedButtons",
        "ActivityFeedCaptions",
        "ActivityFeedGroupCaptions",
        "ActivityFeedImages",
        "Addon",
        "AddonHudSize",
        "AddonLayout",
        "AddonParam",
        "AddonTalkParam",
        "AddonTransient",
        "Adventure",
        "AdventureExPhase",
        "AetherCurrent",
        "AetherCurrentCompFlgSet",
        "AetherialWheel",
        "Aetheryte",
        "AetheryteSystemDefine",
        "AirshipExplorationLevel",
        "AirshipExplorationLog",
        "AirshipExplorationParamType",
        "AirshipExplorationPart",
        "AirshipExplorationPoint",
        "AirshipSkyIsland",
        "AnimationLOD",
        "AnimaWeapon5",
        "AnimaWeapon5Param",
        "AnimaWeapon5PatternGroup",
        "AnimaWeapon5SpiritTalk",
        "AnimaWeapon5SpiritTalkParam",
        "AnimaWeapon5SpiritTalkType",
        "AnimaWeapon5TradeItem",
        "AnimaWeaponFUITalk",
        "AnimaWeaponFUITalkParam",
        "AnimaWeaponIcon",
        "AnimaWeaponItem",
        "AozAction",
        "AozActionTransient",
        "AOZArrangement",
        "AOZBoss",
        "AOZContent",
        "AOZContentBriefingBNpc",
        "AOZReport",
        "AOZReportReward",
        "AOZScore",
        "AOZWeeklyReward",
        "AquariumFish",
        "AquariumWater",
        "ArrayEventHandler",
        "AttackType",
        "Attract",
        "Attributive",
        "BacklightColor",
        "Ballista",
        "Balloon",
        "BaseParam",
        "Battalion",
        "BattleLeve",
        "BattleLeveRule",
        "BeastRankBonus",
        "BeastReputationRank",
        "BeastTribe",
        "Behavior",
        "BehaviorMove",
        "BehaviorPath",
        "BenchmarkCutSceneTable",
        "BenchmarkOverrideEquipment",
        "BgcArmyAction",
        "BgcArmyActionTransient",
        "BGM",
        "BGMFade",
        "BGMFadeType",
        "BGMScene",
        "BGMSituation",
        "BGMSwitch",
        "BGMSystemDefine",
        "BKJEObj",
        "BKJLivestock",
        "BKJPouch",
        "BKJSeed",
        "BKJShipment",
        "BKJSpecialtyGoods",
        "BNpcAnnounceIcon",
        "BNpcBase",
        "BNpcCustomize",
        "BNpcName",
        "BNpcParts",
        "BNpcState",
        "Buddy",
        "BuddyAction",
        "BuddyEquip",
        "BuddyItem",
        "BuddyRank",
        "BuddySkill",
        "Cabinet",
        "CabinetCategory",
        "Calendar",
        "Carry",
        "Channeling",
        "CharaMakeClassEquip",
        "CharaMakeCustomize",
        "CharaMakeName",
        "CharaMakeType",
        "ChocoboRace",
        "ChocoboRaceAbility",
        "ChocoboRaceAbilityType",
        "ChocoboRaceCalculateParam",
        "ChocoboRaceChallenge",
        "ChocoboRaceItem",
        "ChocoboRaceRank",
        "ChocoboRaceRanking",
        "ChocoboRaceStatus",
        "ChocoboRaceTerritory",
        "ChocoboRaceTutorial",
        "ChocoboRaceWeather",
        "ChocoboTaxi",
        "ChocoboTaxiStand",
        "CircleActivity",
        "ClassJob",
        "ClassJobCategory",
        "ClassJobResident",
        "CollectablesShop",
        "CollectablesShopItem",
        "CollectablesShopItemGroup",
        "CollectablesShopRefine",
        "CollectablesShopRewardItem",
        "CollectablesShopRewardScrip",
        "CollisionIdPallet",
        "ColorFilter",
        "Colosseum",
        "ColosseumMatchRank",
        "Companion",
        "CompanionMove",
        "CompanionTransient",
        "CompanyAction",
        "CompanyCraftDraft",
        "CompanyCraftDraftCategory",
        "CompanyCraftManufactoryState",
        "CompanyCraftPart",
        "CompanyCraftProcess",
        "CompanyCraftSequence",
        "CompanyCraftSupplyItem",
        "CompanyCraftType",
        "CompanyLeve",
        "CompanyLeveRule",
        "CompleteJournal",
        "CompleteJournalCategory",
        "Completion",
        "Condition",
        "ConfigKey",
        "ContentAttributeRect",
        "ContentCloseCycle",
        "ContentDirectorManagedSG",
        "ContentEffectiveTime",
        "ContentEntry",
        "ContentExAction",
        "ContentFinderCondition",
        "ContentFinderConditionTransient",
        "ContentGauge",
        "ContentGaugeColor",
        "ContentMemberType",
        "ContentNpcTalk",
        "ContentRandomSelect",
        "ContentRewardCondition",
        "ContentRoulette",
        "ContentRouletteOpenRule",
        "ContentRouletteRoleBonus",
        "ContentsNote",
        "ContentsNoteCategory",
        "ContentsNoteLevel",
        "ContentsNoteRewardEurekaEXP",
        "ContentsTutorial",
        "ContentsTutorialPage",
        "ContentTalk",
        "ContentTalkParam",
        "ContentTodo",
        "ContentTourismConstruct",
        "ContentType",
        "ContentUICategory",
        "CraftAction",
        "CraftLeve",
        "CraftLevelDifference",
        "CraftLeveTalk",
        "CraftType",
        "Credit",
        "CreditBackImage",
        "CreditCast",
        "CreditDataSet",
        "CreditFont",
        "CreditList",
        "CreditListText",
        "CreditVersion",
        "CurrencyScripConvert",
        "CustomTalk",
        "CustomTalkDynamicIcon",
        "CustomTalkNestHandlers",
        "CustomTalkResident",
        "CutActionTimeline",
        "Cutscene",
        "CutsceneActorSize",
        "CutsceneEventMotion",
        "CutsceneMotion",
        "CutsceneName",
        "CutsceneWorkIndex",
        "CutScreenImage",
        "CycleTime",
        "DailySupplyItem",
        "DawnContent",
        "DawnGrowMember",
        "DawnMemberUIParam",
        "DawnQuestAnnounce",
        "DawnQuestMember",
        "DeepDungeon",
        "DeepDungeonBan",
        "DeepDungeonDanger",
        "DeepDungeonEquipment",
        "DeepDungeonFloorEffectUI",
        "DeepDungeonGrowData",
        "DeepDungeonItem",
        "DeepDungeonLayer",
        "DeepDungeonMagicStone",
        "DeepDungeonMap5X",
        "DeepDungeonRoom",
        "DeepDungeonStatus",
        "DefaultTalk",
        "DefaultTalkLipSyncType",
        "DeliveryQuest",
        "Description",
        "DescriptionPage",
        "DescriptionSection",
        "DescriptionStandAlone",
        "DescriptionStandAloneTransient",
        "DescriptionString",
        "DirectorSystemDefine",
        "DirectorType",
        "DisposalShop",
        "DisposalShopFilterType",
        "DisposalShopItem",
        "DomaStoryProgress",
        "DpsChallenge",
        "DpsChallengeOfficer",
        "DpsChallengeTransient",
        "DynamicEvent",
        "DynamicEventEnemyType",
        "DynamicEventManager",
        "DynamicEventSet",
        "DynamicEventSingleBattle",
        "DynamicEventType",
        "EmjAddon",
        "EmjCharaViewCamera",
        "EmjDani",
        "Emote",
        "EmoteCategory",
        "EmoteMode",
        "EmoteTransient",
        "ENpcBase",
        "ENpcDressUp",
        "ENpcDressUpDress",
        "ENpcResident",
        "EObj",
        "EObjName",
        "EquipRaceCategory",
        "EquipSlotCategory",
        "Error",
        "Eureka",
        "EurekaAetherItem",
        "EurekaAethernet",
        "EurekaDungeonPortal",
        "EurekaGrowData",
        "EurekaLogosMixerProbability",
        "EurekaMagiaAction",
        "EurekaMagiciteItem",
        "EurekaMagiciteItemType",
        "EurekaSphereElementAdjust",
        "EurekaStoryProgress",
        "EventAction",
        "EventIconPriority",
        "EventIconType",
        "EventItem",
        "EventItemCastTimeline",
        "EventItemCategory",
        "EventItemHelp",
        "EventItemTimeline",
        "EventSystemDefine",
        "EventVfx",
        "ExHotbarCrossbarIndexType",
        "ExportedSG",
        "ExtraCommand",
        "ExVersion",
        "FashionCheckThemeCategory",
        "FashionCheckWeeklyTheme",
        "Fate",
        "FateEvent",
        "FateMode",
        "FateProgressUI",
        "FateRuleEx",
        "FateTokenType",
        "FCActivity",
        "FCActivityCategory",
        "FCAuthority",
        "FCAuthorityCategory",
        "FCChestName",
        "FCCrestSymbol",
        "FccShop",
        "FCDefine",
        "FCHierarchy",
        "FCProfile",
        "FCRank",
        "FCReputation",
        "FCRights",
        "Festival",
        "FieldMarker",
        "FishingRecordType",
        "FishingRecordTypeTransient",
        "FishingSpot",
        "FishParameter",
        "FishParameterReverse",
        "Frontline",
        "Frontline01",
        "Frontline02",
        "Frontline03",
        "Frontline04",
        "FurnitureCatalogCategory",
        "FurnitureCatalogItemList",
        "GardeningSeed",
        "GatheringCondition",
        "GatheringExp",
        "GatheringItem",
        "GatheringItemLevelConvertTable",
        "GatheringItemPoint",
        "GatheringLeve",
        "GatheringLeveBNpcEntry",
        "GatheringLeveRoute",
        "GatheringLeveRule",
        "GatheringNotebookItem",
        "GatheringNotebookList",
        "GatheringPoint",
        "GatheringPointBase",
        "GatheringPointBonus",
        "GatheringPointBonusType",
        "GatheringPointName",
        "GatheringPointTransient",
        "GatheringRarePopTimeTable",
        "GatheringSubCategory",
        "GatheringType",
        "GcArmyCandidateCategory",
        "GcArmyCapture",
        "GcArmyCaptureTactics",
        "GcArmyEquipPreset",
        "GcArmyExpedition",
        "GcArmyExpeditionMemberBonus",
        "GcArmyExpeditionTrait",
        "GcArmyExpeditionTraitCond",
        "GcArmyExpeditionType",
        "GcArmyMember",
        "GcArmyMemberGrow",
        "GcArmyMemberGrowExp",
        "GcArmyProgress",
        "GcArmyTraining",
        "GCRankGridaniaFemaleText",
        "GCRankGridaniaMaleText",
        "GCRankLimsaFemaleText",
        "GCRankLimsaMaleText",
        "GCRankUldahFemaleText",
        "GCRankUldahMaleText",
        "GCScripShopCategory",
        "GCScripShopItem",
        "GCShop",
        "GCShopItemCategory",
        "GCSupplyDefine",
        "GCSupplyDuty",
        "GCSupplyDutyReward",
        "GeneralAction",
        "GFATE",
        "GFateClimbing",
        "GFateClimbing2",
        "GFateClimbing2Content",
        "GFateClimbing2TotemType",
        "GFateDance",
        "GFateHiddenObject",
        "GFateRideShooting",
        "GFateRoulette",
        "GFateStelth",
        "GilShop",
        "GilShopInfo",
        "GilShopItem",
        "GimmickAccessor",
        "GimmickBill",
        "GimmickJump",
        "GimmickRect",
        "GimmickTalk",
        "GimmickYesNo",
        "GoldSaucerArcadeMachine",
        "GoldSaucerContent",
        "GoldSaucerTalk",
        "GoldSaucerTextData",
        "GrandCompany",
        "GrandCompanyRank",
        "GroupPoseCharacterShowPreset",
        "GroupPoseCharaStatus",
        "GroupPoseFrame",
        "GroupPoseStamp",
        "GroupPoseStampCategory",
        "GuardianDeity",
        "Guide",
        "GuidePage",
        "GuidePageString",
        "GuideTitle",
        "GuildleveAssignment",
        "GuildleveAssignmentCategory",
        "GuildleveAssignmentTalk",
        "GuildleveEvaluation",
        "GuildOrder",
        "GuildOrderGuide",
        "GuildOrderOfficer",
        "HairMakeType",
        "HouseRetainerPose",
        "HousingAethernet",
        "HousingAppeal",
        "HousingEmploymentNpcList",
        "HousingEmploymentNpcRace",
        "HousingExterior",
        "HousingFurniture",
        "HousingInterior",
        "HousingLandSet",
        "HousingMapMarkerInfo",
        "HousingMateAuthority",
        "HousingMerchantPose",
        "HousingPileLimit",
        "HousingPlacement",
        "HousingPreset",
        "HousingTrainingDoll",
        "HousingUnitedExterior",
        "HousingUnplacement",
        "HousingYardObject",
        "HowTo",
        "HowToCategory",
        "HowToPage",
        "Hud",
        "HudTransient",
        "HugeCraftworksNpc",
        "HugeCraftworksRank",
        "HWDAnnounce",
        "HWDCrafterSupply",
        "HWDCrafterSupplyReward",
        "HWDCrafterSupplyTerm",
        "HWDDevLayerControl",
        "HWDDevLevelUI",
        "HWDDevLevelWebText",
        "HWDDevLively",
        "HWDDevProgress",
        "HWDGathereInspectTerm",
        "HWDGathererInspection",
        "HWDGathererInspectionReward",
        "HWDInfoBoardArticle",
        "HWDInfoBoardArticleTransient",
        "HWDInfoBoardArticleType",
        "HWDInfoBoardBackNumber",
        "HWDLevelChangeDeception",
        "HWDSharedGroup",
        "HWDSharedGroupControlParam",
        "IKDContentBonus",
        "IKDFishParam",
        "IKDPlayerMissionCondition",
        "IKDRoute",
        "IKDRouteTable",
        "IKDSpot",
        "IKDTimeDefine",
        "InclusionShop",
        "InclusionShopCategory",
        "InclusionShopSeries",
        "IndividualWeather",
        "InstanceContent",
        "InstanceContentBuff",
        "InstanceContentCSBonus",
        "InstanceContentGuide",
        "InstanceContentRewardItem",
        "InstanceContentTextData",
        "InstanceContentType",
        "Item",
        "ItemAction",
        "ItemActionTelepo",
        "ItemBarterCheck",
        "ItemFood",
        "ItemLevel",
        "ItemOnceHqMasterpiece",
        "ItemSearchCategory",
        "ItemSeries",
        "ItemSortCategory",
        "ItemSpecialBonus",
        "ItemUICategory",
        "JigsawScore",
        "JigsawTimeBonus",
        "Jingle",
        "JobHudManual",
        "JobHudManualPriority",
        "JournalCategory",
        "JournalGenre",
        "JournalSection",
        "Knockback",
        "LegacyQuest",
        "Leve",
        "LeveAssignmentType",
        "LeveClient",
        "Level",
        "LeveRewardItem",
        "LeveRewardItemGroup",
        "LeveString",
        "LeveSystemDefine",
        "LeveVfx",
        "LFGExtensionContent",
        "LinkRace",
        "LoadingImage",
        "LoadingTips",
        "LoadingTipsSub",
        "Lobby",
        "Lockon",
        "LogFilter",
        "LogKind",
        "LogMessage",
        "LootModeType",
        "LotteryExchangeShop",
        "MacroIcon",
        "MacroIconRedirectOld",
        "MainCommand",
        "MainCommandCategory",
        "Maneuvers",
        "ManeuversArmor",
        "Map",
        "MapCondition",
        "MapMarker",
        "MapMarkerRegion",
        "MapSymbol",
        "Marker",
        "MasterpieceSupplyDuty",
        "MasterpieceSupplyMultiplier",
        "MateAuthorityCategory",
        "Materia",
        "MateriaJoinRate",
        "MateriaJoinRateGatherCraft",
        "MateriaParam",
        "MateriaTomestoneRate",
        "McGuffin",
        "McGuffinUIData",
        "MiniGameRA",
        "MiniGameRANotes",
        "MinionRace",
        "MinionRules",
        "MinionSkillType",
        "MinionStage",
        "MobHuntOrder",
        "MobHuntOrderType",
        "MobHuntReward",
        "MobHuntRewardCap",
        "MobHuntTarget",
        "ModelAttribute",
        "ModelChara",
        "ModelScale",
        "ModelSkeleton",
        "ModelState",
        "MonsterNote",
        "MonsterNoteTarget",
        "MotionTimeline",
        "MotionTimelineAdvanceBlend",
        "MotionTimelineBlendTable",
        "Mount",
        "MountAction",
        "MountCustomize",
        "MountFlyingCondition",
        "MountSpeed",
        "MountTransient",
        "MoveControl",
        "MoveTimeline",
        "MoveVfx",
        "MovieStaffList",
        "MovieSubtitle",
        "MovieSubtitle500",
        "MovieSubtitleVoyage",
        "MYCTemporaryItem",
        "MYCTemporaryItemUICategory",
        "MYCWarResultNotebook",
        "NotebookDivision",
        "NotebookDivisionCategory",
        "NotebookList",
        "NotoriousMonster",
        "NotoriousMonsterTerritory",
        "NpcEquip",
        "NpcYell",
        "Omen",
        "OnlineStatus",
        "OpenContent",
        "OpenContentCandidateName",
        "Opening",
        "OpeningSystemDefine",
        "OpenLuaUI",
        "Orchestrion",
        "OrchestrionCategory",
        "OrchestrionPath",
        "OrchestrionUiparam",
        "Ornament",
        "OrnamentCustomize",
        "OrnamentCustomizeGroup",
        "OrnamentTransient",
        "ParamGrow",
        "PartyContent",
        "PartyContentCutscene",
        "PartyContentTextData",
        "PartyContentTransient",
        "PatchMark",
        "Perform",
        "PerformGuideScore",
        "PerformTransient",
        "Permission",
        "Pet",
        "PetAction",
        "PetMirage",
        "PhysicsGroup",
        "PhysicsOffGroup",
        "PhysicsWind",
        "Picture",
        "PlaceName",
        "PlantPotFlowerSeed",
        "PreHandler",
        "PreHandlerMovement",
        "PresetCamera",
        "PresetCameraAdjust",
        "PublicContent",
        "PublicContentCutscene",
        "PublicContentTextData",
        "PublicContentType",
        "Purify",
        "PvPAction",
        "PvPActionSort",
        "PvPInitialSelectActionTrait",
        "PvPRank",
        "PvPRankTransient",
        "PvPSelectTrait",
        "PvPSelectTraitTransient",
        "PvPTrait",
        "QTE",
        "Quest",
        "QuestAcceptAdditionCondition",
        "QuestBattle",
        "QuestBattleResident",
        "QuestBattleSystemDefine",
        "QuestChapter",
        "QuestClassJobReward",
        "QuestClassJobSupply",
        "QuestDerivedClass",
        "QuestEffect",
        "QuestEffectDefine",
        "QuestEffectType",
        "QuestEquipModel",
        "QuestHideReward",
        "QuestRedo",
        "QuestRedoChapter",
        "QuestRedoChapterUI",
        "QuestRedoChapterUICategory",
        "QuestRedoChapterUITab",
        "QuestRedoIncompChapter",
        "QuestRepeatFlag",
        "QuestRewardOther",
        "QuestStatusParam",
        "QuestSystemDefine",
        "QuickChat",
        "QuickChatTransient",
        "Race",
        "RacingChocoboGrade",
        "RacingChocoboItem",
        "RacingChocoboName",
        "RacingChocoboNameCategory",
        "RacingChocoboNameInfo",
        "RacingChocoboParam",
        "RecastNavimesh",
        "Recipe",
        "RecipeLevelTable",
        "RecipeLookup",
        "RecipeNotebookList",
        "RecommendContents",
        "Relic",
        "Relic3",
        "Relic3Materia",
        "Relic3Rate",
        "Relic3RatePattern",
        "Relic6Magicite",
        "RelicItem",
        "RelicMateria",
        "RelicNote",
        "RelicNoteCategory",
        "Resident",
        "ResidentMotionType",
        "ResistanceWeaponAdjust",
        "RetainerFortuneRewardRange",
        "RetainerTask",
        "RetainerTaskLvRange",
        "RetainerTaskNormal",
        "RetainerTaskParameter",
        "RetainerTaskParameterLvDiff",
        "RetainerTaskRandom",
        "RideShooting",
        "RideShootingScheduler",
        "RideShootingTarget",
        "RideShootingTargetScheduler",
        "RideShootingTargetType",
        "RideShootingTextData",
        "Role",
        "RPParameter",
        "SatisfactionArbitration",
        "SatisfactionNpc",
        "SatisfactionSupply",
        "SatisfactionSupplyReward",
        "SatisfactionSupplyRewardExp",
        "ScenarioTree",
        "ScenarioTreeTips",
        "ScenarioTreeTipsClassQuest",
        "ScenarioType",
        "ScreenImage",
        "SE",
        "SEBattle",
        "SecretRecipeBook",
        "SequentialEvent",
        "Skirmish",
        "SkyIsland",
        "SkyIsland2",
        "SkyIsland2Mission",
        "SkyIsland2MissionDetail",
        "SkyIsland2MissionType",
        "SkyIsland2RangeType",
        "SkyIslandMapMarker",
        "SkyIslandSubject",
        "Snipe",
        "SnipeCollision",
        "SnipeElementId",
        "SnipeHitEvent",
        "SnipePerformanceCamera",
        "SnipeTalk",
        "SnipeTalkName",
        "SpearfishingEcology",
        "SpearfishingItem",
        "SpearfishingItemReverse",
        "SpearfishingNotebook",
        "SpearfishingRecordPage",
        "SpecialShop",
        "SpecialShopItemCategory",
        "Spectator",
        "Stain",
        "StainTransient",
        "StanceChange",
        "Status",
        "StatusHitEffect",
        "StatusLoopVFX",
        "Story",
        "StorySystemDefine",
        "SubmarineExploration",
        "SubmarineExplorationLog",
        "SubmarineMap",
        "SubmarinePart",
        "SubmarineRank",
        "SubmarineSpecCategory",
        "SwitchTalk",
        "SwitchTalkVariation",
        "SystemGraphicPreset",
        "TerritoryChatRule",
        "TerritoryIntendedUse",
        "TerritoryType",
        "TerritoryTypeTransient",
        "TextCommand",
        "TextCommandParam",
        "Title",
        "TomestoneConvert",
        "Tomestones",
        "TomestonesItem",
        "TopicSelect",
        "Town",
        "Trait",
        "TraitRecast",
        "TraitTransient",
        "Transformation",
        "Treasure",
        "TreasureHuntRank",
        "TreasureHuntTexture",
        "TreasureModel",
        "TreasureSpot",
        "Tribe",
        "TripleTriad",
        "TripleTriadCard",
        "TripleTriadCardRarity",
        "TripleTriadCardResident",
        "TripleTriadCardType",
        "TripleTriadCompetition",
        "TripleTriadDefine",
        "TripleTriadResident",
        "TripleTriadRule",
        "TripleTriadTournament",
        "Tutorial",
        "TutorialDPS",
        "TutorialHealer",
        "TutorialTank",
        "UIColor",
        "Vase",
        "VaseFlower",
        "VFX",
        "Warp",
        "WarpCondition",
        "WarpLogic",
        "WeaponTimeline",
        "Weather",
        "WeatherGroup",
        "WeatherRate",
        "WeatherReportReplace",
        "WebGuidance",
        "WebURL",
        "WeddingBGM",
        "WeddingFlowerColor",
        "WeddingPlan",
        "WeeklyBingoOrderData",
        "WeeklyBingoRewardData",
        "WeeklyBingoText",
        "WeeklyLotBonus",
        "WeeklyLotBonusThreshold",
        "World",
        "WorldDCGroupType",
        "XPVPGroupActivity",
        "YardCatalogCategory",
        "YardCatalogItemList",
        "YKW",
        "ZoneSharedGroup",
        "ZoneTimeline");
        
        // $PSheet = "Achievement";
         $KeyName = "id";

        foreach ($SheetArray as $PSheet) {
        // grab CSV files
        $SheetCsv = $this->csv("$PSheet");

        $this->io->progressStart($SheetCsv->total);

        //get patch data into array
        $PatchListURL = "https://xivapi.com/patchlist";
        $PatchListContents = file_get_contents($PatchListURL);
        $PatchListJdata = json_decode($PatchListContents);
        $PatchJSON = [];
        foreach ($PatchListJdata as $JData) {
            $JDataCode = $JData->ID;
            $JDataPatch = $JData->Version;
            $PatchJSON[$JDataCode] = $JDataPatch;
        }
        //Grab the required sheet data
        $SheetURL = "https://raw.githubusercontent.com/xivapi/ffxiv-datamining-patches/master/patchdata/$PSheet.json";
        $SheetContents = file_get_contents($SheetURL);
        $SheetJdata = json_decode($SheetContents);
        $SheetJSON = [];
        foreach ($SheetJdata as $SheetData => $Value) {
            $SheetJSON[$SheetData] = $PatchJSON[$Value];
        }

        if (!file_exists("output/PatchData/$PSheet.json")) { 
            $MakeFile = fopen("output/PatchData/$PSheet.json", 'w');
            fwrite($MakeFile, NULL);
            fclose($MakeFile);
        }
        $jdata = file_get_contents("output/PatchData/$PSheet.json");
        $PatchArray = json_decode($jdata, true);
        $CSV = $SheetCsv;
        foreach ($CSV->data as $id => $CsvData) {
            if (empty($SheetJSON[$id])) continue;
            $Key = $CsvData[$KeyName];
            if (empty($Key)) continue;
            $PatchNo = $SheetJSON[$id];
            if (isset($PatchArray[$Key])) continue;
            if (!isset($PatchArray[$Key])) {
                $PatchArray[$Key] = $PatchNo;
            }
        }
        $JSONOUTPUT = json_encode($PatchArray, JSON_PRETTY_PRINT);
        //write Api file
        if (!file_exists("output/PatchData/")) { mkdir("output/PatchData/", 0777, true); }
        $JSON_File = fopen("output/PatchData/$PSheet.json", 'w');
        fwrite($JSON_File, $JSONOUTPUT);
        fclose($JSON_File);
        $this->io->progressFinish();
        }

        // loop through test data
        //foreach ($SheetCsv->data as $id => $CsvSheetData) {
        //    // ---------------------------------------------------------
        //    $this->io->progressAdvance();
        //    if (empty($CsvSheetData['Name'])) continue;
//
        //    //---------------------------------------------------------------------------------
        //
        //    $data = [
        //        '{item}' => $PatchString,
        //    ];
        //
        //    // format using Gamer Escape formatter and add to data array
        //    $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        //}

        // save our data to the filename: GeSatisfactionWiki.txt
        //$this->io->text('Saving ...');
        //$info = $this->save("$CurrentPatchOutput/PatchPopulate - ". $Patch .".txt", 999999);
//
        //$this->io->table(
        //    ['Filename', 'Data Count', 'File Size'],
        //    $info
        //);
    }
}