<?php

namespace App\Parsers\XIVDB;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class Achievement implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        $this->output->writeln([
            '------------------------------------------------------',
            '<comment>Parsing Achievement Data</comment>',
            '------------------------------------------------------',
        ]);

        $achievementCsv = $this->getCsvFile('Achievement');
        $achievementCategoryCsv = $this->getCsvFile('AchievementCategory');

        // start a progress bar
        $progress = new ProgressBar($this->output, $achievementCsv->total);

        // loop through instances
        foreach($achievementCsv->data as $id => $row) {
            // ---
            $progress->advance();
            $this->output->write('  --  '. $row['Name']);

            // skip ones without a name
            if (empty($row['Name'])) {
                continue;
            }
            // ---

            $catId = $row['AchievementCategory'];
            if ($catId < 1) {
                continue;
            }

            $catRow = $achievementCategoryCsv->at($catId);
            $kindId = $catRow['AchievementKind'];

            $this->data[] = [
                'id' => $id,
                'achievement_kind' => $kindId,
            ];
        }

        $progress->finish();

        // save
        $this->dump('AchievementKindIdsJson', json_encode($this->data, JSON_PRETTY_PRINT));

        // build sql - HAX
        $sql = [];
        foreach($this->data as $row) {
            $id = $row['id'];
            unset($row['id']);

            foreach($row as $column => $value) {
                $row[$column] = "{$column}={$value}";
            }

            $sql[] = 'UPDATE xiv_achievements SET '. implode(',', $row) . " WHERE id = {$id};";
        }

        $this->dump('AchievementKindIdsSql', implode("\n", $sql));
    }
}
