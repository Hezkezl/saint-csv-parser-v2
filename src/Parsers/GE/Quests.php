<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class Quests implements ParseInterface
{
    use CsvParseTrait;

    // the wiki format we shall use
    const WIKI_FORMAT = '
        {{ARR Infobox Quest
        |Patch = {patch}
        |Name = {name}{types}{repeatable}{faction}{eventicon}
        {smallimage}
        |Level = {level}
        {requiredclass}
        |Required Affiliation =
        |Quest Number =
        {instancecontent1}{instancecontent2}{instancecontent3}
        {prevquest1}{prevquest2}{prevquest3}
        |Unlocks Quests =
        |Objectives =
        {objectives}
        |Description =
        |EXPReward ={gilreward}{sealsreward}
        {tomestones}{relations}{instanceunlock}{questrewards}{catalystrewards}{guaranteeditem7}{guaranteeditem8}{guaranteeditem9}{guaranteeditem10}{guaranteeditem11}{questoptionrewards}
        |Issuing NPC = {questgiver}
        |NPC Location =
        |NPCs Involved =
        |Mobs Involved =
        |Items Involved =
        |Journal =
        {journal}
        |Strategy =
        |Walkthrough =
        |Dialogue =
        {dialogue}
        |Etymology =
        |Images =
        |Notes =
        }}';

    public function parse()
    {
        // i should pull this from xivdb :D
        $patch = '4.2';

        // grab quest CSV file
        $questCsv = $this->csv('Quest');

        $this->io->progressStart($questCsv->total);

        // loop through quest data
        foreach($questCsv->data as $id => $quest) {
            // ---------------------------------------------------------
            $this->io->progressAdvance();

            // skip ones without a name
            if (empty($quest['Name'])) {
                continue;
            }
            // ---------------------------------------------------------

            $data = [
                '{patch}' => $patch,
                '{name}' => $quest['Name'],
            ];

            // format using GamerEscape Formater and add to data array
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeQuestWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save('GeQuestWiki.txt');

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
