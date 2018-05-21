<?php

namespace App\Parsers\Hello;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * Hello:World
 */
class World implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $ItemUiCategoryCsv = $this->csv('ItemUICategory');

        // (optional) start a progress bar
        $this->io->progressStart($ItemCsv->total);

        // loop through data
        foreach ($ItemCsv->data as $id => $item) {
            $this->io->progressAdvance();

            //
            // Your parse code here
            //

            // grab item ui category for this item
            $itemUiCategory = $ItemUiCategoryCsv->at($item['ItemUICategory']);

            // Save some data
            $this->data[] = json_encode([
                'id' => $item['id'],
                'name' => $item['Name'],
                'description' => $item['Description'],
                'item_ui_category_name' => $itemUiCategory['Name']
            ]);

            //
            // ---------------------------
            //
        }

        // (optional) finish progress bar
        $this->io->progressFinish();

        // save
        $this->io->text('Saving data ...');
        $this->save('XIVDB_ItemCategories.txt');
    }
}
