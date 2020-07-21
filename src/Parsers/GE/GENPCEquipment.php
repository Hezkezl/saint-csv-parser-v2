<?php
namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:GENPCEquipment
 */
class GENPCEquipment implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{top}
{{NPC Appearance
{index}
{bodyoutput}{equipmentoutput}
}}
{test}
{bottom}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $ENpcBaseCsv = $this->csv("ENpcBase");
        $ENpcResidentCsv = $this->csv("ENpcResident");
        $TribeCsv = $this->csv("Tribe");
        $RaceCsv = $this->csv("Race");
        $StainCsv = $this->csv("Stain");
        $CharaMakeCustomizeCsv = $this->csv("CharaMakeCustomize");
        $CharaMakeTypeCsv = $this->csv("CharaMakeType");
        $ItemCsv = $this->csv("Item");
        $NpcEquipCsv = $this->csv("NpcEquip");


        // (optional) start a progress bar
        $this->io->progressStart($ENpcBaseCsv->total);

        $Bot = true;

        //color generator
        $CMPfile= "cache/human.cmp";
        $buffer = unpack("C*",file_get_contents($CMPfile));
        $buffer = array_chunk($buffer, 4);
        foreach ($buffer as $i => $rgba) {
            [$r, $g, $b, $a] = $rgba;

            $hex = sprintf("%02x%02x%02x", $r, $g, $b,);

            $buffer[$i] = $hex;
        }
        //file_put_contents(__DIR__.'/human.cmp.json', json_encode($buffer, JSON_PRETTY_PRINT));

        //hair style array
        $hairStyles = [];

        foreach ($CharaMakeCustomizeCsv->data as $id => $CharaMakeCustomize) {
            $roundId = floor($CharaMakeCustomize['id'] / 100) * 100;
            $featureId = $CharaMakeCustomize['FeatureID'];

            $hairStyles[$roundId][$featureId] = $CharaMakeCustomize;
        }

        //loop through Item.csv to make a model array
        $itemArray = [];
        $weaponArray = [];

        foreach ($ItemCsv->data as $id => $ItemData) {
            if ($ItemData['EquipSlotCategory'] = 0) continue;
            $Category = $ItemData['ItemUICategory'];
            $Weapon = $ItemData['EquipSlotCategory'];
            $ModelMainBase = str_replace(", ", "-", $ItemData['Model{Main}']);

            $name = $ItemData['Name'];
            $itemArray[$Category][$ModelMainBase] = $ItemData;
            $weaponArray[$ModelMainBase] = $ItemData;
        }


        // loop through data
        foreach ($ENpcBaseCsv->data as $id => $EnpcBase) {
            $this->io->progressAdvance();
            $Index = $EnpcBase['id'];

            $debug = false;
            //if ($Index != 1009183) continue; // for debug
            //var_dump($Index);


            $Name = $ENpcResidentCsv->at($Index)['Singular'];
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
            $NpcMiqoCheck = $EnpcBase['Race']; //see if miqote
            $NpcName = ucwords(strtolower($ENpcResidentCsv->at($Index)['Singular']));
            //this explodes miqote's names into 2 words, capitalizes them and then puts it back together with a hyphen
            if ($NpcMiqoCheck == 4) {
                $NpcName = ucwords(strtolower($ENpcResidentCsv->at($Index)['Singular']));
                $NpcName = implode('-', array_map('ucfirst', explode('-', $NpcName)));
            }
            $Name = str_replace($IncorrectNames, $correctnames, $NpcName);
            $Name = preg_replace('/[^\x00-\x7F]+/', '', $Name);
            //comment out the below line to allow for all humanoid npcs
            if (empty($Name)) continue;
            if (empty($ENpcResidentCsv->at($Index)['Title'])) {
                $Title = false;
            } elseif (!empty($ENpcResidentCsv->at($Index)['Title'])) {
                $Title = "\n< ". $ENpcResidentCsv->at($Index)['Title'] ." >";
            }
            $Race = $RaceCsv->at($EnpcBase['Race'])['Masculine'];
            if (empty($Race)) continue;

            $genderBase = $EnpcBase['Gender'];
            $Gender = $EnpcBase['Gender'];
            if ($Gender == 0) {
                $Gender = "Male";
            } elseif ($Gender == 1) {
                $Gender = "Female";
            }

            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name/Appearance'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name/Appearance?action=edit\n";
                $Bottom = false;
            };

            $BaseFace = $EnpcBase['Face'];
            $face = null;
            $face = $BaseFace % 100; // Value matches the asset number, % 100 approximate face # nicely.

            $BodyTypeBase = $EnpcBase['BodyType'];
            switch ($BodyTypeBase)
            {
                case 1:
                    $BodyType = "Adult";
                    break;
                case 2:
                    $BodyType = "Adult";
                    break;
                case 3:
                    $BodyType = "Elderly";
                    break;
                case 4:
                    $BodyType = "Child";
                    break;
                default:
                    $BodyType = "Unknown";
                    break;
            }
            $Height = $EnpcBase['Height'];
            $Tribe = $TribeCsv->at($EnpcBase['Tribe'])['Masculine'];

            $HairStyle = $EnpcBase['HairStyle'];

            $GenderCalc = $EnpcBase['Gender'];
            $TribeCalc = $EnpcBase['Tribe'];
            if (($GenderCalc = 0) && ($TribeCalc = 1)) {
                $Calc = false;
            }
            if (($GenderCalc = 1) && ($TribeCalc = 1)) {
                $Calc = "10";
            }


            $extraFeatureShape = $EnpcBase['ExtraFeature1'];
            $extraFeatureSize = $EnpcBase['ExtraFeature2OrBust'];

            $isMale = boolval($genderBase) ? 'false' : 'true';
            $tribeKey = $EnpcBase['Tribe'];
            $tailIconIndex = null;


            switch ($tribeKey)
            {
                case 1: // Midlander
                    $tribeKeyCalc = ($isMale == "true") ? 0 : 1;
                    break;
                case 2: // Highlander
                    $tribeKeyCalc = ($isMale == "true") ? 2 : 3;
                    break;
                case 3: // Wildwood
                    $tribeKeyCalc = ($isMale == "true") ? 4 : 5;
                    break;
                case 4: // Duskwight
                    $tribeKeyCalc = ($isMale == "true") ? 6 : 7;
                    break;
                case 5: // Plainsfolks
                    $tribeKeyCalc = ($isMale == "true") ? 8 : 9;
                    break;
                case 6: // Dunesfolk
                    $tribeKeyCalc = ($isMale == "true") ? 10 : 11;
                    break;
                case 7: // Seeker of the Sun
                    $tribeKeyCalc = ($isMale == "true") ? 12 : 13;
                    break;
                case 8: // Keeper of the Moon
                    $tribeKeyCalc = ($isMale == "true") ? 14 : 15;
                    break;
                case 9: // Sea Wolf
                    $tribeKeyCalc = ($isMale == "true") ? 16 : 17;
                    break;
                case 10: // Hellsguard
                    $tribeKeyCalc = ($isMale == "true") ? 18 : 19;
                    break;
                case 11: // Raen
                    $tribeKeyCalc = ($isMale == "true") ? 20 : 21;
                    break;
                case 12: // Xaela
                    $tribeKeyCalc = ($isMale == "true") ? 22 : 23;
                    break;
                case 13: // Helions
                    $tribeKeyCalc = ($isMale == "true") ? 24 : 25;
                    break;
                case 14: // The Lost
                    $tribeKeyCalc = ($isMale == "true") ? 26 : 27;
                    break;
                case 15: // Rava
                    $tribeKeyCalc = ($isMale == "true") ? 28 : 29;
                    break;
                case 16: // Veena
                    $tribeKeyCalc = ($isMale == "true") ? 30 : 31;
                    break;
            }

            //face/fur/tail icons
            $BaseFaceCalc = $face - 1;
            $race = $EnpcBase['Race'];
            //var_dump($race);
            $warning = false;
            $warningGen = false;
            if ($face > 6) {
                $warning = "\n|Custom Face = yes";
                $BaseFaceCalc = 1;
            }
            if ($BaseFaceCalc < 1) {
                $warning = "\n|Custom Face = yes";
                $BaseFaceCalc = 1;
            }
            $tailOrEarShape = $extraFeatureShape -1;
            if ($tailOrEarShape > 50) {
                $warningGen = " - Custom ?";
                $tailOrEarShape = 1;
            }
            if ($tailOrEarShape < 1) {
                $warningGen = " - Custom ?";
                $tailOrEarShape = 1;
            }
            switch ($tribeKey)
            {
                case 1: // Midlander
                    $tribeCode = ($isMale == "true") ? 0 : 100;
                    $headIconIndex = ($isMale == "true") ? 5 : 6;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."";
                    break;
                case 2: // Highlander
                    $tribeCode = ($isMale == "true") ? 200 : 300;
                    $headIconIndex = ($isMale == "true") ? 5 : 6;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."";
                    break;
                case 3: // Wildwood
                case 4: // Duskwight
                    $tribeCode = ($isMale == "true") ? 400 : 500;
                    $headIconIndex = ($isMale == "true") ? 4 : 5;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $EarShape = $extraFeatureShape;
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."\n|Ear Shape = ". $EarShape ."";
                    break;
                case 5: // Plainsfolks
                case 6: // Dunesfolk
                    $tribeCode = ($isMale == "true") ? 600 : 700;
                    $headIconIndex = ($isMale == "true") ? 4 : 5;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $EarShape = $extraFeatureShape;
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."\n|Ear Shape = ". $EarShape ."";
                    break;
                case 7: // Seeker of the Sun
                case 8: // Keeper of the Moon
                    $tribeCode = ($isMale == "true") ? 800 : 900;
                    $headIconIndex = ($isMale == "true") ? 6 : 7;
                    $tailIconIndex = ($isMale == "true") ? 2 : 3;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $tailIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$tailIconIndex][$tailOrEarShape]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."\n|Tail Shape = ". $tailIcon .".png";
                    break;
                case 9: // Sea Wolf
                case 10: // Hellsguard
                    $tribeCode = ($isMale == "true") ? 1000 : 1100;
                    $headIconIndex = ($isMale == "true") ? 5 : 6;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."";
                    break;
                case 11: // Raen
                case 12: // Xaela
                    $tribeCode = ($isMale == "true") ? 1200 : 1300;
                    $headIconIndex = ($isMale == "true") ? 6 : 7;
                    $tailIconIndex = ($isMale == "true") ? 2 : 3;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $tailIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$tailIconIndex][$tailOrEarShape]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."\n|Tail Shape = ". $tailIcon .".png";
                    break;

                // No alternate genders for Hrothgar, Viera.
                // For Hrothgar, these might be faces too?
                case 13: // Helions
                case 14: // The Lost
                    $tribeCode = 1400;
                    $furIconIndex = 2;
                    $tailIconIndex = 4;
                    $furIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$furIconIndex][$BaseFaceCalc]"];
                    $tailIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$tailIconIndex][$tailOrEarShape]"];
                    $extraIcons = "|Fur Type = ". $furIcon .".png\n|Tail Shape = ". $tailIcon .".png";
                    break;
                case 15: // Rava
                case 16: // Veena
                    $tribeCode = 1500;
                    $headIconIndex = 5;
                    $earIconIndex = 14;
                    $headIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$headIconIndex][$BaseFaceCalc]"];
                    $earIcon = $CharaMakeTypeCsv->at($tribeKeyCalc)["SubMenuParam[$earIconIndex][$tailOrEarShape]"];
                    $extraIcons = "|Face = ". $headIcon .".png". $warning ."\n|Ear Shape = ". $earIcon .".png";
                    break;
            }


            //HairColor
            $GenderValue = ($isMale == "true") ? 0 : 1;
            $listIndex = ($tribeKey * 2 + $GenderValue) * 5 + 4;
            $hairIndex = $listIndex * 256;

            $hairColorBase = $EnpcBase['HairColor'];
            $hairColorIndex = $hairIndex + $hairColorBase;
            $hairColor = $buffer[$hairColorIndex];

            $hairHighlightColor = false;
            if ($EnpcBase['HairHighlightColor'] != 0) {
                $HairHighlightColorOffset = 1 * 256;
                $hairHighlightColorBase = $EnpcBase['HairHighlightColor'];
                $hairHighlightColorIndex = $HairHighlightColorOffset + $hairHighlightColorBase;
                $hairHighlightColor = $buffer[$hairHighlightColorIndex];
            }

            //HairStyle
            $hairStyleBase = $EnpcBase['HairStyle'];
            $warningHair = false;
            if ($hairStyleBase > 200) {
                $hairStyleBase = 1;
                $warningHair = "\n|Custom Hair = yes";
            }
            $hairStyleRaw = $hairStyles[$tribeCode][$hairStyleBase];

            $hairStyleIcon = "".$hairStyleRaw['Icon'] .".png".$warningHair ."";

            //Skin Colour
            $listIndex = ($tribeKey * 2 + $GenderValue) * 5 + 3;
            $skinIndex = $listIndex * 256;

            $skinColorBase = $EnpcBase['SkinColor'];
            $skinColorIndex = $skinIndex + $skinColorBase;
            $skinColor = $buffer[$skinColorIndex];

            //Eyes
            $EyeColorOffset = 0 * 256;
            $eyeColorBase = $EnpcBase['EyeColor'];
            $eyeColorBuffer = $eyeColorBase + $EyeColorOffset;
            $eyeColor = $buffer[$eyeColorBuffer];

            $heterochromiaColor ="";
            $eyeHeterochromia = $EnpcBase['EyeHeterochromia'];
            if ($eyeHeterochromia != $eyeColorBase) {
                $heterochromiaBuffer = $eyeHeterochromia + $EyeColorOffset;
                $heterochromiaColor = $buffer[$heterochromiaBuffer];
            }
            $eyeSize = "Large";
            $eyeShapeBase = $EnpcBase['EyeShape'];
            $eyeShape = $eyeShapeBase + 1;
            if ($eyeShapeBase >= 128) {
                $eyeShape = ($eyeShapeBase - 128) + 1;
                $eyeSize = "Small";
            }

            //Mouth and Lips
            $LightLipFacePaintColorOffset = 7 * 256;
            $DarkLipFacePaintColorOffset = 2 * 256;



            $mouthShape = $EnpcBase['Mouth'];
            if ($tribeKey == 13 || 14) {
                $lipColourBase = $EnpcBase['LipColor'];
                $mouthData = "|Mouth = ". $mouthShape ."\n|FurType = ". $lipColourBase ."";
            }
            if ($mouthShape >= 128) {
                $mouthShape = 1 + ($mouthShape - 128);
                if ($EnpcBase['LipColor'] >= 128) {
                    $lipShade = "Light";
                    $lipColourCalc = $EnpcBase['LipColor'] + $LightLipFacePaintColorOffset;
                    $lipColour = $buffer[$lipColourCalc];
                } elseif ($EnpcBase['LipColor'] < 128){
                    $lipShade = "Dark";
                    $lipColourCalc = $EnpcBase['LipColor'] + $DarkLipFacePaintColorOffset;
                    $lipColour = $buffer[$lipColourCalc];
                }
                $mouthData = "|Mouth = ". $mouthShape ."\n|Lip Color = ". $lipColour ."\n|Lip Shade = ". $lipShade ."";
            } elseif ($mouthShape < 128) {
                $mouthShape = $mouthShape + 1;
                $lipShade = false;
                $lipColour = false;
                $mouthData = "|Mouth = ". $mouthShape ."";
            }

            //Face Paint
            //get facepaint keys based on gender/race
            $baseRowKey = 1600;
            switch ($tribeKey)
            {
                case 1: // Midlander
                case 2: // Highlander
                case 3: // Wildwood
                case 4: // Duskwight
                case 5: // Plainsfolks
                case 6: // Dunesfolk
                case 7: // Seeker of the Sun
                case 8: // Keeper of the Moon
                case 9: // Sea Wolf
                case 10: // Hellsguard
                case 11: // Raen
                case 12: // Xaela
                    $tribeOffset = $baseRowKey + (($tribeKey - 1) * 100);
                    $FacePaintCustomizeIndex = ($isMale == "true") ? $tribeOffset : $tribeOffset + 50;
                    break;

                case 13: // Helions
                    $FacePaintCustomizeIndex = 2800;
                    break;
                case 14: // The Lost
                    $FacePaintCustomizeIndex = 2850;
                    break;
                case 15: // Rava
                    $FacePaintCustomizeIndex = 2900;
                    break;
                case 16: // Veena
                    $FacePaintCustomizeIndex = 2950;
                    break;
            }
            //Face Paint Color

            $facePaintColorBase = $EnpcBase['FacePaintColor'];
            $facePaintColor = false;
            $facePaintColorShade = "Light";
            if ($facePaintColorBase >= 128) {
                $facePaintColorShade = "Light";
                $facePaintColourIndex = 1 + ($facePaintColorBase - 128);
                $facePaintColourCalc = $EnpcBase['FacePaintColor'] + $LightLipFacePaintColorOffset;
                $facePaintColorRGB = $buffer[$facePaintColourCalc];
                $facePaintColor = "|Face Paint Color = ". $facePaintColorRGB ."\n|Face Paint Shade = ". $facePaintColorShade ."";
            } elseif ($facePaintColorBase < 128) {
                $facePaintColorShade = "Dark";
                $facePaintColourCalc = $EnpcBase['FacePaintColor'] + $DarkLipFacePaintColorOffset;
                $facePaintColorRGB = $buffer[$facePaintColourCalc];
                $facePaintColor = "|Face Paint Color = ". $facePaintColorRGB ."\n|Face Paint Shade = ". $facePaintColorShade ."";
            }

            //Face Paint Icon
            $facePaintBase = $EnpcBase['FacePaint'] + 1;
            $facePaintIcon = false;
            if ($facePaintBase >= 128) {
                $facePaint = 1 + ($facePaintBase - 128);
                $facePaintReverse = "|Face Paint Reversed = True";
                $facePaintIconIndex = $FacePaintCustomizeIndex + $facePaint;
                $facePaintIconImage = $CharaMakeCustomizeCsv->at($facePaintIconIndex)['Icon'];
                if ($facePaintIconImage > 0) {
                    $facePaint = $facePaintIconImage;
                    $facePaintIcon = "|Face Paint = ". $facePaintIconImage ."\n". $facePaintReverse ."";
                }
            } elseif ($facePaintBase < 128) {
                $facePaintIconIndex = $FacePaintCustomizeIndex + $facePaintBase;
                $facePaintIconImage = $CharaMakeCustomizeCsv->at($facePaintIconIndex)['Icon'];
                $facePaint = $facePaintIconImage;
                $facePaintReverse = "|Face Paint Reversed = False";
                if ($facePaintIconImage > 0) {
                    $facePaint = $facePaintIconImage;
                    $facePaintIcon = "|Face Paint = ". $facePaintIconImage ."\n". $facePaintReverse ."\n". $facePaintColor ."";
                }
            }

            //Extra Features
            $raceKey = $EnpcBase['Race'];
            switch ($raceKey)
            {
                case 1: // Hyur
                case 5: // Roegadyn
                    $extraFeatureName = null;
                    break;

                case 2: // Elezen
                case 3: // Lalafell
                case 8: // Viera
                    $extraFeatureName = "Ear";
                    break;

                case 4: // Miqo'te
                case 6: // Au Ra
                case 7: // Hrothgar
                    $extraFeatureName = "Tail";
                    break;
            }
            // Bust & Muscles - flex fields.
            $bust = false;
            $bustAndMuscle = false;
            if ($raceKey == 5 || $raceKey == 1)
            {
                // Roegadyn & Hyur
                $bust = false;
                $muscle = $EnpcBase["BustOrTone1"];
                if ($isMale == "false"){
                    $bust = "\n|BustSize = ". $EnpcBase["ExtraFeature2OrBust"] ."";
                }
                $bustAndMuscle = "\n|Muscles = ". $muscle ."". $bust ."";
            }
            else if ($raceKey == 6 && $isMale == "true")
            {
                // Au Ra male muscles
                $muscle = $EnpcBase["BustOrTone1"];
                $bustAndMuscle = "\n|Muscles = ". $muscle ."";
            }
            else if ($isMale == "false")
            {
                // Other female bust sizes
                $bust = $EnpcBase["BustOrTone1"];
                $bustAndMuscle = "\n|BustSize = ". $bust ."";
            }
            $extraFeature = false;
            if ($extraFeatureName != null) {
                $extraFeature = "\n|". $extraFeatureName ." Length = ". $extraFeatureSize ."";
            }

            //Facial Feature
            $facialFeature = null;
            $facialFeatureArray = null;
            $facialFeatureArray = [];
            $facialFeatureBase =  null;
            $facialFeatureBasePad = null;
            $facialFeatureIcon = null;
            $facialFeatureIcon = [];
            // ^ i couldn't find the cause so i emptied out all values ^
            $facialFeatureBase = $EnpcBase['FacialFeature'];
            $facialFeatureArray = array(($facialFeatureBase & 1) == 1, ($facialFeatureBase & 2) == 2, ($facialFeatureBase & 4) == 4, ($facialFeatureBase & 8) == 8, ($facialFeatureBase & 16) == 16, ($facialFeatureBase & 32) == 32, ($facialFeatureBase & 64) == 64, ($facialFeatureBase & 128) == 128);
            $facialFeatureArraysplit = str_split($facialFeatureBasePad);


            //facial features
            // colors
            $listIndex = ($tribeKey * 2 + $GenderValue) * 5 + 4;
            $facialFeatureIndex = $listIndex * 256;
            $facialFeatureColorBase = $EnpcBase['FacialFeatureColor'];
            $facialFeatureColorIndex = $facialFeatureIndex + $facialFeatureColorBase;
            $facialFeatureColor = $buffer[$facialFeatureColorIndex];

            $tribe = $EnpcBase['Tribe'];
            switch ($tribeKey)
            {
                case 1: // Midlander
                case 2: // Highlander
                case 3: // Wildwood
                case 4: // Duskwight
                case 5: // Plainsfolks
                case 6: // Dunesfolk
                case 7: // Seeker of the Sun
                case 8: // Keeper of the Moon
                case 9: // Sea Wolf
                case 10: // Hellsguard
                case 11: // Raen
                case 12: // Xaela
                    $facialFace = $face - 1;
                    break;
                case 13: // Helions
                case 14: // The Lost
                    $facialFace = $face;
                    break;
                case 15: // Rava
                case 16: // Veena
                    $facialFace = $face - 1;
                    break;
            }
            if ($face < 7) {
                for ($i=0; $i < 5; $i++) {
                    if ($facialFeatureArray[$i] == 1) {
                        $facialFeatureIcon[$i] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                    }
                }
            } elseif ($face > 6) {
                $facialFeatureicon = [];
            }


            if (!empty($facialFeatureIcon)) {
                $facialFeature = implode(",", $facialFeatureIcon);
            }
            //tattoos
            $facialFeatureExtraPre = false;
            $facialFeatureExtraImplode = false;
            $facialFeatureExtraColor = false;
            $facialFeatureExtra = [];
            if ($face < 7) {
                for ($i=5; $i < 7; $i++) {
                    if ($facialFeatureArray[$i] == 1) {

                        switch ($tribeKey)
                        {
                            case 1: // Midlander
                            case 2: // Highlander
                                $facialFeatureExtraPre = "\n|Tattoos = ";
                                $facialFeatureExtraColor = "\n|Tattoo Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 3: // Wildwood
                                $facialFeatureExtraPre = "\n|Ear Clasp = ";
                                $facialFeatureExtraColor = "\n|Ear Clasp Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 4: // Duskwight
                            case 5: // Plainsfolks
                            case 6: // Dunesfolk
                            case 7: // Seeker of the Sun
                                $facialFeatureExtraPre = "\n|Tattoos = ";
                                $facialFeatureExtraColor = "\n|Tattoo Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 8: // Keeper of the Moon
                                if ($GenderCalc == 0) {
                                    $facialFeatureExtraPre = "\n|Tattoos = ";
                                    $facialFeatureExtraColor = "\n|Tattoo Color = ". $facialFeatureColor ."";
                                    $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                } elseif ($GenderCalc == 1) {
                                    $facialFeatureExtraPre = "\n|Ear Clasp = ";
                                    $facialFeatureExtraColor = "\n|Ear Clasp Color = ". $facialFeatureColor ."";
                                    $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                }
                                break;
                            case 9: // Sea Wolf
                            case 10: // Hellsguard
                                $facialFeatureExtraPre = "\n|Tattoos = ";
                                $facialFeatureExtraColor = "\n|Tattoo Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 11: // Raen
                            case 12: // Xaela
                                $facialFeatureExtraPre = "\n|Limbal Rings = ";
                                $facialFeatureExtraColor = "\n|Limbal Ring Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 13: // Helions
                            case 14: // The Lost
                                $facialFeatureExtraPre = "\n|Tattoos = ";
                                $facialFeatureExtraColor = false;
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                            case 15: // Rava
                            case 16: // Veena
                                $facialFeatureExtraPre = "\n|Tattoos = ";
                                $facialFeatureExtraColor = "\n|Tattoo Color = ". $facialFeatureColor ."";
                                $facialFeatureExtra[] = $CharaMakeTypeCsv->at($tribeKeyCalc)["FacialFeatureOption[$facialFace][$i]"];
                                break;
                        }
                    }
                }
            } elseif ($face > 6) {
                $facialFeatureicon = [];
            }
            if (!empty($facialFeatureExtra)) {
                $facialFeatureExtraImplode = implode(",", $facialFeatureExtra);
            }
            $facialFeatureExtra = "". $facialFeatureExtraPre ."". $facialFeatureExtraImplode ."". $facialFeatureExtraColor ."";
            if ($headIcon < 1) {
                $headIcon = "CustomFace";
            }


            //pure debugging of certain strings
            if ($debug == true) {
                var_dump($facialFeatureBase);
                var_dump($facialFeatureArray);
                var_dump($facialFeatureBasePad);

                $ex = $EnpcBase['FacialFeature'];
                $cusomizekeystring = "
tribeCode > ". $tribeCode ."
isMale  > ". $isMale ."
FacePaintCustomizeIndex > ". $FacePaintCustomizeIndex ."
genderBase > ". $genderBase ."
face > ". $face ."
tribeKey > ". $tribeKey ."
hairColorIndex > ". $hairColorIndex ."
extraFeatureName > ". $extraFeatureName ."
hairColorIndex > ". $extraFeatureShape ."
hairColorIndex > ". $extraFeatureSize ."
bustAndMuscle > ". $bustAndMuscle ."
FacialFeature > ". $facialFeature ."". $facialFeatureExtra ."
FacePaintCustomizeIndex > ". $FacePaintCustomizeIndex ."
facePaint > ". $facePaint ."
facePaintIconIndex > ". $facePaintIconIndex ."
extrafea > ". $ex ."
extraIcons > ". $extraIcons . "
BaseFace > ". $BaseFace ."
hairStyleBase > ". $hairStyleBase ."
hairStyleIcon > ". $hairStyleIcon ."
BaseFaceCalc > ". $BaseFaceCalc ."
";



            }
            if ($debug == false) {
                $cusomizekeystring = false;
            }



            $eyebrowsBase = $EnpcBase['Eyebrows'];
            $Eyebrows = $eyebrowsBase + 1;
            $noseBase = $EnpcBase['Nose'];
            $Nose = $noseBase + 1;
            $jawBase = $EnpcBase['Jaw'];
            $Jaw = $jawBase + 1;


            //Equipment
            //Mainhand/Offhand
            $MainHandBase = str_replace(", ", "-", $EnpcBase['Model{MainHand}']);
            $guess = false;
            if ($MainHandBase == 0) {
                $MainHand = false;
                if ($NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{MainHand}'] != 0) {
                    $MainHandBase = str_replace(", ", "-", $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{MainHand}']);
                    $MainHandDyeBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Dye{MainHand}'];
                }
            }
            if ($MainHandBase > 0) {
                $MainHandmodmain = explode("-", $MainHandBase);
                $MainHanda = $MainHandmodmain[0];
                $MainHandb = $MainHandmodmain[1];
                $MainHandc = $MainHandmodmain[2];
                $MainHandd = $MainHandmodmain[3];
                $MainHandModela = $MainHanda;
                if ($MainHandModela < 8999) {
                    $ModelbOrigin = $MainHandb;
                    $MainHandModel = "". $MainHanda ."-". $MainHandb ."-". $MainHandc ."-". $MainHandd ."";
                    if (empty($weaponArray[$MainHandModel]["Name"])) {
                        do {
                            $MainHandb++;
                            $MainHandModel = "". $MainHanda ."-". $MainHandb ."-". $MainHandc ."-". $MainHandd ."";
                            $guess = "\n|Main Hand Guess = yes";
                            if ($MainHandb > 300) {
                                break;
                            }
                        } while (empty($weaponArray[$MainHandModel]["Name"]));
                    }
                    if (empty($weaponArray[$MainHandModel]["Name"])) {
                        $MainHandb = $ModelbOrigin;
                        do {
                            $MainHandb--;
                            if ($MainHandb < 0) {
                                break;
                            }
                            $MainHandModel = "". $MainHanda ."-". $MainHandb ."-". $MainHandc ."-". $MainHandd ."";
                            $guess = "\n|Main Hand Guess = yes";
                            if ($MainHandb > $ModelbOrigin) {
                                break;
                            }
                        } while (empty($weaponArray[$MainHandModel]["Name"]));
                    }
                    if ($MainHanda < 8999) {
                        if ($MainHandb >= 0) {
                            $MainHandModel = "". $MainHanda ."-". $MainHandb ."-". $MainHandc ."-". $MainHandd ."";
                            $MainHand = "". $weaponArray[$MainHandModel]["Name"] ."". $guess ."";
                        }
                        if ($MainHandb < 0) {
                            $MainHand = "Custom Main Hand";
                        }
                    }
                }
                if ($MainHanda > 8999) {
                    $MainHand = "Custom Main Hand";
                }
            }

            //OffHand
            $OffHandBase = str_replace(", ", "-", $EnpcBase['Model{OffHand}']);
            $guess = false;
            if ($OffHandBase == 0) {
                $OffHand = false;
                if ($NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{OffHand}'] != 0) {
                    $OffHandBase = str_replace(", ", "-", $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{OffHand}']);
                    $OffHandDyeBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Dye{OffHand}'];
                }
            }
            if ($OffHandBase > 0) {
                $OffHandmodmain = explode("-", $OffHandBase);
                $OffHanda = $OffHandmodmain[0];
                $OffHandb = $OffHandmodmain[1];
                $OffHandc = $OffHandmodmain[2];
                $OffHandd = $OffHandmodmain[3];
                $OffHandModela = $OffHanda;
                if ($OffHandModela < 8999) {
                    $ModelbOrigin = $OffHandb;
                    $OffHandModel = "". $OffHanda ."-". $OffHandb ."-". $OffHandc ."-". $OffHandd ."";
                    if (empty($weaponArray[$OffHandModel]["Name"])) {
                        do {
                            $OffHandb++;
                            $OffHandModel = "". $OffHanda ."-". $OffHandb ."-". $OffHandc ."-". $OffHandd ."";
                            $guess = "\n|Off Hand Guess = yes";
                            if ($OffHandb > 300) {
                                break;
                            }
                        } while (empty($weaponArray[$OffHandModel]["Name"]));
                    }
                    if (empty($weaponArray[$OffHandModel]["Name"])) {
                        $OffHandb = $ModelbOrigin;
                        do {
                            $OffHandb--;
                            if ($OffHandb < 0) {
                                break;
                            }
                            $OffHandModel = "". $OffHanda ."-". $OffHandb ."-". $OffHandc ."-". $OffHandd ."";
                            $guess = "\n|Off Hand Guess = yes";
                            if ($OffHandb > $ModelbOrigin) {
                                break;
                            }
                        } while (empty($weaponArray[$OffHandModel]["Name"]));
                    }
                    if ($OffHanda < 8999) {
                        if ($OffHandb >= 0) {
                            $OffHandModel = "". $OffHanda ."-". $OffHandb ."-". $OffHandc ."-". $OffHandd ."";
                            $OffHand = "". $weaponArray[$OffHandModel]["Name"] ."". $guess ."";
                        }
                        if ($OffHandb < 0) {
                            $OffHand = "Custom Off Hand";
                        }
                    }
                }
                if ($OffHanda > 8999) {
                    $OffHand = "Custom Off Hand";
                }
            }

            //Visor
            $Visor = $EnpcBase['Visor'];

            //Head
            $HeadCat = "34";
            $guess = false;
            $Head = false;
            $Modela = null;
            $Modelb = null;
            $HeadBase = $EnpcBase['Model{Head}'];

            if ($HeadBase == 0) {
                $Head = false;
                if ($NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{Head}'] != 0) {
                    $HeadBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{Head}'];
                    $HeadDyeBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Dye{Head}'];
                }
            }
            if ($HeadBase == 4294967295) {
                $Head = false;
                $HeadBase = 0;
            }
            //for test HeadBase outcome here is 2494967295
            if ($HeadBase > 0) {
                $Modela = $HeadBase & 0xFFFF;
                if ($Modela < 8999) {
                    $Modelb = ($HeadBase >> 16) & 0xFFFF;
                    $Modelc = ($HeadBase >> 32) & 0xFFFF;
                    $Modeld = ($HeadBase >> 48) & 0xFFFF;
                    $ModelbOrigin = ($HeadBase >> 16) & 0xFFFF;
                    //$HeadModel = "".($HeadBase & 0xFFFF) ."-". (($HeadBase >> 16) & 0xFFFF). "-". (($HeadBase >> 32) & 0xFFFF) ."-". (($HeadBase >> 48) & 0xFFFF) ."";
                    $HeadModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                    if (empty($itemArray[$HeadCat][$HeadModel]["Name"])) {
                        do {
                            $Modelb++;
                            $HeadModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|Head Guess = yes";
                            if ($Modelb > 300) {
                                break;
                            }
                        } while (empty($itemArray[$HeadCat][$HeadModel]["Name"]));
                    }
                    if (empty($itemArray[$HeadCat][$HeadModel]["Name"])) {
                        $Modelb = $ModelbOrigin;
                        do {
                            $Modelb--;
                            if ($Modelb < 0) {
                                break;
                            }
                            $HeadModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|Head Guess = yes";
                            if ($Modelb > $ModelbOrigin) {
                                break;
                            }
                        } while (empty($itemArray[$HeadCat][$HeadModel]["Name"]));
                    }
                    if ($Modela < 8999) {
                        if ($Modelb >= 0) {
                            $HeadModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $Head = "". $itemArray[$HeadCat][$HeadModel]["Name"] ."". $guess ."";
                        }
                        if ($Modelb < 0) {
                            $Head = "Custom Head";
                        }
                    }
                }
                if ($Modela > 8999) {
                    $Head = "Custom Head";
                }
            }
            $HeadDye = $StainCsv->at($EnpcBase['Dye{Head}'])['Name'];

            //Body
            $BodyCat = "35";
            $guess = false;
            $Body = false;
            $Modela = null;
            $Modelb = null;
            $BodyBase = $EnpcBase['Model{Body}'];
            if ($BodyBase == 0) {
                $Body = false;
                if ($NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{Body}'] != 0) {
                    $BodyBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{Body}'];
                    $BodyDyeBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Dye{Body}'];
                }
            }
            if ($BodyBase == 4294967295) {
                $Body = false;
                $BodyBase = 0;
            }
            if ($BodyBase > 0) {
                $Modela = $BodyBase & 0xFFFF;
                if ($Modela < 8999) {
                    $Modelb = ($BodyBase >> 16) & 0xFFFF;
                    $Modelc = ($BodyBase >> 32) & 0xFFFF;
                    $Modeld = ($BodyBase >> 48) & 0xFFFF;
                    $ModelbOrigin = ($BodyBase >> 16) & 0xFFFF;
                    //$BodyModel = "".($BodyBase & 0xFFFF) ."-". (($BodyBase >> 16) & 0xFFFF). "-". (($BodyBase >> 32) & 0xFFFF) ."-". (($BodyBase >> 48) & 0xFFFF) ."";
                    $BodyModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                    if (empty($itemArray[$BodyCat][$BodyModel]["Name"])) {
                        do {
                            $Modelb++;
                            $BodyModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|Body Guess = yes";
                            if ($Modelb > 300) {
                                break;
                            }
                        } while (empty($itemArray[$BodyCat][$BodyModel]["Name"]));
                    }
                    if (empty($itemArray[$BodyCat][$BodyModel]["Name"])) {
                        $Modelb = $ModelbOrigin;
                        do {
                            $Modelb--;
                            if ($Modelb < 0) {
                                break;
                            }
                            $BodyModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|Body Guess = yes";
                            if ($Modelb > $ModelbOrigin) {
                                break;
                            }
                        } while (empty($itemArray[$BodyCat][$BodyModel]["Name"]));
                    }
                    if ($Modela < 8999) {
                        if ($Modelb >= 0) {
                            $BodyModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $Body = "". $itemArray[$BodyCat][$BodyModel]["Name"] ."". $guess ."";
                        }
                        if ($Modelb < 0) {
                            $Body = "Custom Body";
                        }
                    }
                }
                if ($Modela > 8999) {
                    $Body = "Custom Body";
                }
            }
            $BodyDye = $StainCsv->at($EnpcBase['Dye{Body}'])['Name'];

            //Hands
            $HandsCat = "37";
            $guess = false;
            $Hands = false;
            $Modela = null;
            $Modelb = null;
            $HandsBase = $EnpcBase['Model{Hands}'];
            if ($HandsBase == 0) {
                $Hands = false;
                if ($NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{Hands}'] != 0) {
                    $HandsBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{Hands}'];
                    $HandsDyeBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Dye{Hands}'];
                }
            }
            if ($HandsBase == 4294967295) {
                $Hands = false;
                $HandsBase = 0;
            }
            if ($HandsBase > 0) {
                $Modela = $HandsBase & 0xFFFF;
                if ($Modela < 8999) {
                    $Modelb = ($HandsBase >> 16) & 0xFFFF;
                    $Modelc = ($HandsBase >> 32) & 0xFFFF;
                    $Modeld = ($HandsBase >> 48) & 0xFFFF;
                    $ModelbOrigin = ($HandsBase >> 16) & 0xFFFF;
                    //$HandsModel = "".($HandsBase & 0xFFFF) ."-". (($HandsBase >> 16) & 0xFFFF). "-". (($HandsBase >> 32) & 0xFFFF) ."-". (($HandsBase >> 48) & 0xFFFF) ."";
                    $HandsModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                    if (empty($itemArray[$HandsCat][$HandsModel]["Name"])) {
                        do {
                            $Modelb++;
                            $HandsModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|Hands Guess = yes";
                            if ($Modelb > 300) {
                                break;
                            }
                        } while (empty($itemArray[$HandsCat][$HandsModel]["Name"]));
                    }
                    if (empty($itemArray[$HandsCat][$HandsModel]["Name"])) {
                        $Modelb = $ModelbOrigin;
                        do {
                            $Modelb--;
                            if ($Modelb < 0) {
                                break;
                            }
                            $HandsModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|Hands Guess = yes";
                            if ($Modelb > $ModelbOrigin) {
                                break;
                            }
                        } while (empty($itemArray[$HandsCat][$HandsModel]["Name"]));
                    }
                    if ($Modela < 8999) {
                        if ($Modelb >= 0) {
                            $HandsModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $Hands = "". $itemArray[$HandsCat][$HandsModel]["Name"] ."". $guess ."";
                        }
                        if ($Modelb < 0) {
                            $Hands = "Custom Hands";
                        }
                    }
                }
                if ($Modela > 8999) {
                    $Hands = "Custom Hands";
                }
                if ($Modela == 9903) {
                    $Hands = false;
                }
                if ($Modela == 0) {
                    $Hands = false;
                }
            }
            $HandsDye = $StainCsv->at($EnpcBase['Dye{Hands}'])['Name'];

            //Legs
            $LegsCat = "36";
            $guess = false;
            $Legs = false;
            $Modela = null;
            $Modelb = null;
            $LegsBase = $EnpcBase['Model{Legs}'];
            if ($LegsBase == 0) {
                $Legs = false;
                if ($NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{Legs}'] != 0) {
                    $LegsBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{Legs}'];
                    $LegsDyeBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Dye{Legs}'];
                }
            }
            if ($LegsBase == 4294967295) {
                $Legs = false;
                $LegsBase = 0;
            }
            if ($LegsBase  > 0) {
                $Modela = $LegsBase & 0xFFFF;
                if ($Modela < 8999) {
                    $Modelb = ($LegsBase >> 16) & 0xFFFF;
                    $Modelc = ($LegsBase >> 32) & 0xFFFF;
                    $Modeld = ($LegsBase >> 48) & 0xFFFF;
                    $ModelbOrigin = ($LegsBase >> 16) & 0xFFFF;
                    //$LegsModel = "".($LegsBase & 0xFFFF) ."-". (($LegsBase >> 16) & 0xFFFF). "-". (($LegsBase >> 32) & 0xFFFF) ."-". (($LegsBase >> 48) & 0xFFFF) ."";
                    $LegsModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                    if (empty($itemArray[$LegsCat][$LegsModel]["Name"])) {
                        do {
                            $Modelb++;
                            $LegsModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|Legs Guess = yes";
                            if ($Modelb > 300) {
                                break;
                            }
                        } while (empty($itemArray[$LegsCat][$LegsModel]["Name"]));
                    }
                    if (empty($itemArray[$LegsCat][$LegsModel]["Name"])) {
                        $Modelb = $ModelbOrigin;
                        do {
                            $Modelb--;
                            if ($Modelb < 0) {
                                break;
                            }
                            $LegsModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|Legs Guess = yes";
                            if ($Modelb > $ModelbOrigin) {
                                break;
                            }
                        } while (empty($itemArray[$LegsCat][$LegsModel]["Name"]));
                    }
                    if ($Modela < 8999) {
                        if ($Modelb >= 0) {
                            $LegsModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $Legs = "". $itemArray[$LegsCat][$LegsModel]["Name"] ."". $guess ."";
                        }
                        if ($Modelb < 0) {
                            $Legs = "Custom Legs";
                        }
                    }
                }
                if ($Modela > 8999) {
                    $Legs = "Custom Legs";
                }
            }
            $LegsDye = $StainCsv->at($EnpcBase['Dye{Legs}'])['Name'];

            //Feet
            $FeetCat = "38";
            $guess = false;
            $Feet = false;
            $Modela = null;
            $Modelb = null;
            $FeetBase = $EnpcBase['Model{Feet}'];
            if ($FeetBase == 0) {
                $Feet = false;
                if ($NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{Feet}'] != 0) {
                    $FeetBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Model{Feet}'];
                    $FeetDyeBase = $NpcEquipCsv->at($EnpcBase['NpcEquip'])['Dye{Feet}'];
                }
            }
            if ($FeetBase == 4294967295) {
                $Feet = false;
                $FeetBase = 0;
            }
            if ($FeetBase > 0) {
                $Modela = $FeetBase & 0xFFFF;
                if ($Modela < 8999) {
                    $Modelb = ($FeetBase >> 16) & 0xFFFF;
                    $Modelc = ($FeetBase >> 32) & 0xFFFF;
                    $Modeld = ($FeetBase >> 48) & 0xFFFF;
                    $ModelbOrigin = ($FeetBase >> 16) & 0xFFFF;
                    //$FeetModel = "".($FeetBase & 0xFFFF) ."-". (($FeetBase >> 16) & 0xFFFF). "-". (($FeetBase >> 32) & 0xFFFF) ."-". (($FeetBase >> 48) & 0xFFFF) ."";
                    $FeetModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                    if (empty($itemArray[$FeetCat][$FeetModel]["Name"])) {
                        do {
                            $Modelb++;
                            $FeetModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|Feet Guess = yes";
                            if ($Modelb > 300) {
                                break;
                            }
                        } while (empty($itemArray[$FeetCat][$FeetModel]["Name"]));
                    }
                    if (empty($itemArray[$FeetCat][$FeetModel]["Name"])) {
                        $Modelb = $ModelbOrigin;
                        do {
                            $Modelb--;
                            if ($Modelb < 0) {
                                break;
                            }
                            $FeetModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $guess = "\n|Feet Guess = yes";
                            if ($Modelb > $ModelbOrigin) {
                                break;
                            }
                        } while (empty($itemArray[$FeetCat][$FeetModel]["Name"]));
                    }
                    if ($Modela < 8999) {
                        if ($Modelb >= 0) {
                            $FeetModel = "". $Modela ."-". $Modelb ."-". $Modelc ."-". $Modeld ."";
                            $Feet = "". $itemArray[$FeetCat][$FeetModel]["Name"] ."". $guess ."";
                        }
                        if ($Modelb < 0) {
                            $Feet = "Custom Feet";
                        }
                    }
                }
                if ($Modela > 8999) {
                    $Feet = "Custom Feet";
                }
            }
            $FeetDye = $StainCsv->at($EnpcBase['Dye{Feet}'])['Name'];


            $BodyOutput = "\n|Race = ". $Race ."\n|Gender = ". $Gender ."\n|Body Type = ". $BodyType ."\n|Height = ". $Height ."\n|Clan = ". $Tribe ."\n". $extraIcons ."\n|Hair Style = ". $hairStyleIcon ."\n|Skin Color = ". $skinColor ."\n|Hair Color = ". $hairColor ."\n|Hair Highlight Color = ". $hairHighlightColor ."\n|Facial Feature = ". $facialFeature ."". $facialFeatureExtra ."". $extraFeature ."". $bustAndMuscle ."\n|Eyebrows = ". $Eyebrows ."\n|Eye Shape = ". $eyeShape ."\n|Eye Size = ". $eyeSize ."\n|Eye Color = ". $eyeColor ."\n|Eye Heterochromia = ". $heterochromiaColor ."\n|Nose = ". $Nose ."\n|Jaw = ". $Jaw ."\n". $mouthData ."\n". $facePaintIcon ."\n";

            $EquipmentOutput = "\n|Head = ". $Head ."\n|Head Dye = ". $HeadDye ."\n|Visor = ". $Visor ."\n|Body = ". $Body ."\n|Body Dye = ". $BodyDye ."\n|Hands = ". $Hands ."\n|Hands Dye = ". $HandsDye ."\n|Legs = ". $Legs ."\n|Legs Dye = ". $LegsDye ."\n|Feet = ". $Feet ."\n|Feet Dye = ". $FeetDye ."\n|Main Hand = ". $MainHand ."\n|Off Hand = ". $OffHand ."";

            // Save some data
            $data = [
                '{top}' => $Top,
                '{bottom}' => $Bottom,
                '{index}' => $Index,
                '{name}' => $Name,
                '{title}' => $Title,
                '{bodyoutput}' => $BodyOutput,
                '{equipmentoutput}' => $EquipmentOutput,
                '{test}' => $cusomizekeystring,
                '{index}' => "|Index = ". $Index,
                '{indexid}' => $Index,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("$CurrentPatchOutput/NPCEquipment - ". $Patch .".txt", 999999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
