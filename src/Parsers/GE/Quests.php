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
        $this->output->writeln([
            '------------------------------------------------------',
            '<comment>Parsing Quest Data</comment>',
            '------------------------------------------------------',
        ]);

        // i should pull this from xivdb :D
        $patch = '4.2';

        // grab quest CSV file
        $questCsv = $this->getCsvFile('Quest');

        // start a progress bar
        $progress = new ProgressBar($this->output, $questCsv->total);

        // loop through quest data
        foreach($questCsv->data as $id => $quest) {
            // ---------------------------------------------------------
            // advance the progress bar and output the quest name
            $progress->advance();
            $this->output->write('  --  '. $quest['Name']);

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

            // dump chunks (will save if the chunk count amount has reached)
            $this->dumpChunks('GeQuestWiki');
        }

        // dump any remaining chunks (hence 'true' on end)
        $this->dumpChunks('GeQuestWiki', true);
        $progress->finish();
    }
}
