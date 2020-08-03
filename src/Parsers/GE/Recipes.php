<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:Recipes
 */
class Recipes implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
'''{result}/Recipe/{skill}'''
{{ARR Infobox Recipe
|Recipe ID           = {index}
|Result              = {result}{resultcount}{unlockbook}{specialist}
|Primary Skill       = {skill}
|Primary Skill Level = {level}
|Recipe Level        = {recipelevel}
|Durability          = {durability}
|Difficulty          = {difficulty}
|Quality             = {quality}{maxquality}{requiredcrafts}{requiredcontrol}
|Quick Synthesis     = {quicksynth}{quicksynthcrafts}{quicksynthcontrol}{status}{equipment}{ingredient1}{ingredient2}
{ingredients}{Special}
}}{{-stop-}}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $RecipeCsv = $this->csv('Recipe');
        $ItemCsv = $this->csv('Item');
        $RecipeLevelCsv = $this->csv('RecipeLevelTable');
        $RecipeSecretCsv = $this->csv('SecretRecipeBook');
        $StatusCsv = $this->csv('Status');
        $QuestClassJobSupplyCsv = $this->csv('QuestClassJobSupply');
        $QuestCsv = $this->csv('Quest');
        $BeastTribeCsv = $this->csv('BeastTribe');
        $ENpcResidentCsv = $this->csv('ENpcResident');
        $HugeCraftworksNpcCsv = $this->csv('HugeCraftworksNpc');
        $SatisfactionSupplyCsv = $this->csv('SatisfactionSupply');

        // (optional) start a progress bar
        $this->io->progressStart($RecipeCsv->total);

        //make arrays for QuestClassJobSupply and Quest to re-order index
        $SupplyItemArray = [];

        foreach ($QuestClassJobSupplyCsv->data as $id => $SupplyItemData) {
            $ItemIDSupply = $SupplyItemData['Item'];
            $SupplyItemArray[$ItemIDSupply] = $SupplyItemData;
            // example = var_dump($SupplyItemArray["22720"]["id"]);
        }

        $SatisfactionItemArray = [];

        foreach ($SatisfactionSupplyCsv->data as $id => $SatisfactionItemData) {
            $SatisfactionItemArray[] = $SatisfactionItemData['Item'];
        }

        $QuestArray = [];

        foreach ($QuestCsv->data as $id => $QuestData) {
            $QuestIDSupply = $QuestData['QuestClassJobSupply'];
            $QuestArray[$QuestIDSupply] = $QuestData;
            // example = var_dump($QuestArray["25"]["id"]);
        }

        $CrystariumArray = [];

        foreach ($HugeCraftworksNpcCsv->data as $id => $HugeCraftworksNpcData) {
            foreach(range(0,3) as $a) {
                $CrystariumArray[] = $HugeCraftworksNpcData["ItemRequested[$a]"];
            };
        }

        // loop through data
        foreach ($RecipeCsv->data as $id => $recipe) {
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($recipe['Item{Result}'])) {
                continue;
            }
            //sort through arrays to find if it's limited to a beast tribe etc

            $ResultItemID = $recipe['Item{Result}'];
            $SpecialString = "";
            if (!empty($SupplyItemArray["$ResultItemID"]["id"])) {  
                $LinkToSupplyItemSheet = $SupplyItemArray["$ResultItemID"]["id"];
                $HandInNpc = ucwords($ENpcResidentCsv->at($SupplyItemArray["$ResultItemID"]["ENpcResident"])['Singular']);
                //split "25.0" into "25" and "0" so we can link in quest array
                $keyExplode = explode(".", $LinkToSupplyItemSheet);
                //only pick the first in array (25 for example)
                $keyID = $keyExplode[0];
                $QuestID = $QuestArray["$keyID"]["id"];
                $QuestName = preg_replace('/[^\x00-\x7F]+/', '',$QuestArray["$keyID"]["Name"]);
                $BeastTribe = $BeastTribeCsv->at($QuestArray["$keyID"]["BeastTribe"])['Name'];
                $SpecialString = "\n|Special Recipe           = ". $BeastTribe ." Quest\n|Special Recipe Quest     = ". $QuestName ."\n|Special Recipe HandInNPC = ". $HandInNpc ."";
            }

            //is it Crystarium Item?
            if (in_array($ResultItemID, $CrystariumArray)) {
                $SpecialString = "\n|Special Recipe           = Crystarium Deliveries";
            }
            //is it for Collectable?
            if (in_array($ResultItemID, $SatisfactionItemArray)) {
                $SpecialString = "\n|Special Recipe           = Custom Delivery";
            }

            //use description for |special Recipe
            if (empty($SpecialString)) {
                $ItemDescription = $ItemCsv->at($ResultItemID)['Description'];
                if (strpos($ItemDescription, '※Only for use in ') !== false) {
                    switch (true) {
                        case (strpos($ItemDescription, 'moogle')) !== false:
                            $SpecialString = "\n|Special Recipe           = Moogle Quest";
                        break;
                        case (strpos($ItemDescription, 'Ixal')) !== false:
                            $SpecialString = "\n|Special Recipe           = Ixal Quest";
                        break;
                        
                        default:
                            $SpecialString = "\n|Special Recipe           = FIX IN PHP";
                        break;
                    }
                }
            }

            $skill = [
                0 => 'Carpenter',
                1 => 'Blacksmith',
                2 => 'Armorer',
                3 => 'Goldsmith',
                4 => 'Leatherworker',
                5 => 'Weaver',
                6 => 'Alchemist',
                7 => 'Culinarian',
            ];

            $level = $RecipeLevelCsv->at($recipe['RecipeLevelTable'])['ClassJobLevel'];
            $star = str_repeat("{{Star}}", $RecipeLevelCsv->at($recipe['RecipeLevelTable'])['Stars']);
            $levelstar = "$level $star";
            //$suggestedcrafts = $RecipeLevelCsv->at($recipe['RecipeLevelTable'])['SuggestedCraftsmanship'];

            // ingredient info
            $Ingredients = [];
            foreach(range(0,7) as $i) {
                if(!empty($recipe["Item{Ingredient}[$i]"])) {
                    $Ingredients[] = "|Ingredient ". ($i+3) ."        = ". $ItemCsv->at($recipe["Item{Ingredient}[$i]"])['Name'];
                    $Ingredients[] .= "|Ingredient ". ($i+3) ." Amount = ". $recipe["Amount{Ingredient}[$i]"];
                }
            }
            $Ingredients = implode("\n", $Ingredients);

            // Save some data
            $data = [
                '{index}' => $recipe['id'],
                '{result}' => $ItemCsv->at($recipe['Item{Result}'])['Name'],
                '{resultcount}' => ($recipe['Amount{Result}'] > 1) ? "\n|Result Count        = ". $recipe['Amount{Result}'] : "",
                '{unlockbook}' => ($recipe['SecretRecipeBook'] > 0) ? "\n|Acquired            = ". $RecipeSecretCsv->at($recipe['SecretRecipeBook'])['Name'] : "",
                '{specialist}' => ($recipe['IsSpecializationRequired'] == "True") ? "\n|Specialist Only     = Yes" : "",
                '{skill}' => $skill[$recipe['CraftType']],
                '{level}' => ($RecipeLevelCsv->at($recipe['RecipeLevelTable'])['Stars'] > 0) ? $levelstar :  $level,
                '{recipelevel}' => $recipe['RecipeLevelTable'],
                '{durability}' => floor($RecipeLevelCsv->at($recipe['RecipeLevelTable'])['Durability']*($recipe['DurabilityFactor'])/100),
                '{difficulty}' => floor($RecipeLevelCsv->at($recipe['RecipeLevelTable'])['Difficulty']*($recipe['DifficultyFactor'])/100),
                '{quality}' => floor($RecipeLevelCsv->at($recipe['RecipeLevelTable'])['Quality']*($recipe['QualityFactor'])/100),
                '{maxquality}' => $recipe['MaterialQualityFactor'] ? "\n|Max Initial Quality = ". $recipe['MaterialQualityFactor'] : "",
                '{status}' => ($recipe['Status{Required}'] > 0) ? "\n|Status Required     = ". $StatusCsv->at($recipe['Status{Required}'])['Name'] : "",
                '{equipment}' => ($recipe['Item{Required}'] > 0) ? "\n|Equipment Required  = ". $ItemCsv->at($recipe['Item{Required}'])['Name'] : "",
                '{requiredcrafts}' => ($recipe['RequiredCraftsmanship'] > 0) ? "\n|Craftsmanship Required = ". $recipe['RequiredCraftsmanship'] : "",
                '{requiredcontrol}' => ($recipe['RequiredControl'] > 0) ? "\n|Control Required    = ". $recipe['RequiredControl'] : "",
                '{quicksynth}' => ($recipe['CanQuickSynth'] == "True") ? 'Yes' : 'No',
                '{quicksynthcrafts}' => ($recipe['QuickSynthCraftsmanship'] > 0) ? "\n|Quick Synthesis Craftsmanship = ". $recipe['QuickSynthCraftsmanship'] : "",
                '{quicksynthcontrol}' => ($recipe['QuickSynthControl'] > 0) ? "\n|Quick Synthesis Control = ". $recipe['QuickSynthControl'] : "",
                '{ingredient1}' => "\n|Ingredient 1        = ". $ItemCsv->at($recipe['Item{Ingredient}[8]'])['Name'] ."\n|Ingredient 1 Amount = ". $recipe['Amount{Ingredient}[8]'],
                '{ingredient2}' => ($recipe['Item{Ingredient}[9]'] > 0) ? "\n|Ingredient 2        = ". $ItemCsv->at($recipe['Item{Ingredient}[9]'])['Name'] ."\n|Ingredient 2 Amount = ". $recipe['Amount{Ingredient}[9]'] : "",
                '{ingredients}' => $Ingredients,
                '{Special}' => $SpecialString,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("$CurrentPatchOutput/Recipes - ". $Patch .".txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}

/*
1st Aug 2020 - Added checks and data for "Special Recipe" (used in beast tribes etc)
3rd Aug 2020 - Added checks for collectable
*/
