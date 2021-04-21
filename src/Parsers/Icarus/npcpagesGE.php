<?php


namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
//use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:npcpagesGE
 */
class npcpagesGE implements ParseInterface
{
    
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = '
--------------------------------------------------------------
https://ffxiv.gamerescape.com/w/index.php?title={urlname}&action=edit

{{ARR Infobox NPC
| Patch = {patch}
| Image = {npcname}.png

| Move Image Left = 
| Move Image Up   = 

| Name = {npcname}

| Level = 

| Gender      = {gender}
| Race        = {race}
| Clan        = {clan}

| Affiliation = 
| Occupation  = 
| Title       = {title}

| Location 1 = {placename}
| Location 1 Coordinates = {coords}
| Location 1 Quest = 

| Dialogue = {dialogue}
| Additional Dialogue = 

| Pre-Calamity Dialogue = 

| Biography = 
| Images = 
| Etymology = 
| Notes = 
}}
--------------------------------------------------------------
     ';


 public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');
        // grab CSV files we want to use
        $enpcresidentcsv = $this->csv('ENpcResident');
        $enpcbasecsv = $this->csv('ENpcBase');
        $racecsv = $this->csv('Race');
        $tribecsv = $this->csv('Tribe');
        $ballooncsv = $this->csv('Balloon');
        $PlaceNameCsv = $this->csv('PlaceName');

        // (optional) start a progress bar
        $this->io->progressStart($enpcresidentcsv->total);

        // loop through our sequences
            foreach ($enpcresidentcsv->data as $id => $enpcnamedata){
            $this->io->progressAdvance();
            $id = $enpcnamedata['id'];
            $npcname =  ucwords(strtolower($enpcnamedata['Singular']));
            if (empty($npcname)) continue;
            $title = $enpcnamedata['Title'];

            $genderno = $enpcbasecsv->at($id)['Gender'];
            if ($genderno != 0) {
                $gender = 'Female';
            } elseif ($genderno != 1) {
                $gender = 'Male';
            }
            $race = $racecsv->at($enpcbasecsv->at($id)['Race'])['Masculine'];
            $clan = $tribecsv->at($enpcbasecsv->at($id)['Tribe'])['Masculine'];

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

            $NpcMiqoCheck = $enpcbasecsv->at($id)['Race']; //see if miqote
            //this explodes miqote's names into 2 words, capitalizes them and then puts it back together with a hyphen
            if ($NpcMiqoCheck == 4) {
                $npcname = ucwords(strtolower($npcname));
                $npcname = implode('-', array_map('ucfirst', explode('-', $npcname)));
            }
            $urlname = str_replace(' ', '_', $npcname);
           
            
            
            $balloon = $ballooncsv->at($enpcbasecsv->at($id)['Balloon'])['Dialogue'];

            $url = "https://garlandtools.org/db/doc/npc/en/2/". $id .".json";
            //slow, but grabs the header of the page to check it exists, if 404 is found then use blank values
            $file_headers = @get_headers($url);
            if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {    
                $coords = "";
                $placename = "";
                $Dialogue = "";
                $patch = "";
            }
            else {
                $jdata = file_get_contents($url);
                $decodeJdata = json_decode($jdata);
                $patch = number_format($decodeJdata->npc->patch, 1);
                if ($decodeJdata->npc->patch == 1) {
                    $patch = "2.0";
                }
                $coords = "";
                if (!empty($decodeJdata->npc->coords)) {
                    $x = round($decodeJdata->npc->coords[0], 1);
                    $y = round($decodeJdata->npc->coords[1], 1);
                    $coords = "". $x ."-". $y ."";
                }
                $placename = "";
                if (!empty($decodeJdata->npc->areaid)) {
                    $placenameid = ($decodeJdata->npc->areaid);
                    $placename = $PlaceNameCsv->at($placenameid)['Name'];
                }
                $Dialogue = "";
                if (!empty($decodeJdata->npc->talk)) {
                    $Dialogue = ($decodeJdata->npc->talk[0]);
                }
            }
            // Save some data
            $data = [
                '{npcname}' => $npcname,
                '{urlname}' => $urlname,
                '{title}' => $title,
                '{gender}' => $gender,
                '{race}' => $race,
                '{clan}' => $clan,
                '{dialogue}' => $balloon,
                '{placename}' => $placename,
                '{coords}' => $coords,
                '{patch}' => $patch,

            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("2020.03.27.0000.0000/Npcs - ". $Patch .".txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}