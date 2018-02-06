<?php

namespace App\Parsers\XIVDB;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use App\Parsers\ParseWrapper;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * This updates XIVDB Instance Content levels
 *
 *
 * Parse InstanceContent
 * - must implement "ParseInterface"
 */
class InstanceContent implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        $this->output->writeln([
            '------------------------------------------------------',
            '<comment>Parsing InstanceContent Data</comment>',
            '------------------------------------------------------',
        ]);

        // grab CSV files we want to use
        /** @var ParseWrapper $csv */
        $instanceContent = $this->getCsvFile('InstanceContent');
        $contentFinderCondition = $this->getCsvFile('ContentFinderCondition');
        $contentMemberType = $this->getCsvFile('ContentMemberType');


        // start a progress bar
        $progress = new ProgressBar($this->output, $instanceContent->total);

        // loop through instances
        foreach($instanceContent->data as $id => $row) {
            // ---
            $progress->advance();
            $this->output->write('  --  '. $row['Name']);

            // skip ones without a name
            if (empty($row['Name'])) {
                continue;
            }
            // ---

            // find content finder condition for this instance content
            $conditions = $contentFinderCondition->find('InstanceContent', $id);
            $conditions = $conditions[0] ?? false;

            // we don't care if no conditions
            if (!$conditions) {
                continue;
            }

            // get id of content member type
            $memberType = $contentMemberType->at($conditions['ContentMemberType']);


            // store
            $this->data[] = [
                'id' => $id,
                'level' => $conditions['ClassJobLevel{Required}'],
                'level_sync' => $conditions['ClassJobLevel{Sync}'],
                'item_level' => $conditions['ItemLevel{Required}'],
                'item_level_sync' => $conditions['ItemLevel{Sync}'],

                'players_per_party' => $memberType['unknown_7'],
                'tanks_per_party' => $memberType['TanksPerParty'],
                'healers_per_party' => $memberType['TanksPerParty'],
                'melees_per_party' => $memberType['TanksPerParty'],
                'ranged_per_party' => $memberType['TanksPerParty'],
            ];
        }

        $progress->finish();

        // save
        $this->dump('InstanceContentJson', json_encode($this->data, JSON_PRETTY_PRINT));

        // build sql - HAX
        $sql = [];
        foreach($this->data as $row) {
            $id = $row['id'];
            unset($row['id']);

            foreach($row as $column => $value) {
                $row[$column] = "{$column}={$value}";
            }

            $sql[] = 'UPDATE xiv_instances SET '. implode(',', $row) . " WHERE id = {$id};";
        }

        $this->dump('InstanceContentSql', implode("\n", $sql));

    }
}
