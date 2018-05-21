<?php

namespace App\Parsers\XIVDB;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use App\Parsers\ParseWrapper;

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
        // grab CSV files we want to use
        /** @var ParseWrapper $csv */
        $instanceContent = $this->csv('InstanceContent');
        $contentFinderCondition = $this->csv('ContentFinderCondition');
        $contentMemberType = $this->csv('ContentMemberType');

        // start a progress bar
        $this->io->progressStart($instanceContent->total);

        // loop through instances
        foreach($instanceContent->data as $id => $row) {
            $this->io->progressAdvance();

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

                'banner' => $conditions['Icon'],
            ];
        }

        $this->io->progressFinish();

        // save
        $this->save('InstanceContentJson');

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

        $this->save('InstanceContentSql');

    }
}
