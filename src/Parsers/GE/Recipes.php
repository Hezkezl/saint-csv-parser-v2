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
    const WIKI_FORMAT = 'http://ffxiv.gamerescape.com/wiki/{result}/Recipe?action=edit
{{ARR Infobox Recipe
|Recipe ID           = {index}
|Result              = {result}{resultcount}{unlockbook}{specialist}
|Primary Skill       = {skill}
|Primary Skill Level = {level}
|Recipe Level        = {recipelevel}
|Durability          = {durability}
|Difficulty          = {difficulty}
|Quality             = {quality}{requiredcrafts}{requiredcontrol}
|Quick Synthesis     = {quicksynth}{quicksynthcrafts}{quicksynthcontrol}{status}{aspect}{ingredient1}{ingredient2}
{ingredients}
}}';

    public function parse()
    {
        // grab CSV files we want to use
        $RecipeCsv = $this->csv('Recipe');
        $ItemCsv = $this->csv('Item');
        $RecipeLevelCsv = $this->csv('RecipeLevelTable');
        $RecipeSecretCsv = $this->csv('SecretRecipeBook');
        $StatusCsv = $this->csv('Status');

        // (optional) start a progress bar
        $this->io->progressStart($RecipeCsv->total);

        // loop through data
        foreach ($RecipeCsv->data as $id => $recipe) {
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($recipe['Item{Result}'])) {
                continue;
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

            $aspect = [
                0 => NULL,
                1 => 'Fire',
                2 => 'Ice',
                3 => 'Wind',
                4 => 'Earth',
                5 => 'Lightning',
                6 => 'Water',
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
                '{durability}' => floor($RecipeLevelCsv->at($recipe['RecipeLevelTable'])['Durability']*($recipe['DurabilityFactor']/100)),
                '{difficulty}' => floor($RecipeLevelCsv->at($recipe['RecipeLevelTable'])['Difficulty']*($recipe['DifficultyFactor']/100)),
                '{quality}' => floor($RecipeLevelCsv->at($recipe['RecipeLevelTable'])['Quality']*($recipe['QualityFactor']/100)),
                '{status}' => ($recipe['Status{Required}'] > 0) ? "\n|Status Required     = ". $StatusCsv->at($recipe['Status{Required}'])['Name'] : "",
                '{aspect}' => ($recipe['RecipeElement'] > 0) ? "\n|Aspect              = ". $aspect[$recipe['RecipeElement']] : "",
                '{requiredcrafts}' => ($recipe['RequiredCraftsmanship'] > 0) ? "\n|Craftsmanship Required = ". $recipe['RequiredCraftsmanship'] : "",
                '{requiredcontrol}' => ($recipe['RequiredControl'] > 0) ? "\n|Control Required    = ". $recipe['RequiredControl'] : "",
                '{quicksynth}' => ($recipe['CanQuickSynth'] == "True") ? 'Yes' : 'No',
                '{quicksynthcrafts}' => ($recipe['QuickSynthCraftsmanship'] > 0) ? "\n|Quick Synthesis Craftsmanship = ". $recipe['QuickSynthCraftsmanship'] : "",
                '{quicksynthcontrol}' => ($recipe['QuickSynthControl'] > 0) ? "\n|Quick Synthesis Control = ". $recipe['QuickSynthControl'] : "",
                '{ingredient1}' => "\n|Ingredient 1        = ". $ItemCsv->at($recipe['Item{Ingredient}[8]'])['Name'] ."\n|Ingredient 1 Amount = ". $recipe['Amount{Ingredient}[8]'],
                '{ingredient2}' => ($recipe['Item{Ingredient}[9]'] > 0) ? "\n|Ingredient 2        = ". $ItemCsv->at($recipe['Item{Ingredient}[9]'])['Name'] ."\n|Ingredient 2 Amount = ". $recipe['Amount{Ingredient}[9]'] : "",
                '{ingredients}' => $Ingredients,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeRecipeWiki.txt');

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
