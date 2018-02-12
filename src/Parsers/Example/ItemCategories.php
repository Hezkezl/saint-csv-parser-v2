<?php

namespace App\Parsers\Example;

use App\Parsers\CsvParseDataHandlerTrait;
use App\Parsers\CsvParseTrait;
use App\Parsers\ParseHandler;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Example:ItemCategories
 */
class ItemCategories extends ParseHandler implements ParseInterface
{
    use CsvParseTrait;
    use CsvParseDataHandlerTrait;

    public function parse()
    {
        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $ItemUiCategoryCsv = $this->csv('ItemUICategory');

        // (optional) start a progress bar
        $progress = new ProgressBar($this->output, $ItemCsv->total);

        // loop through data
        foreach($ItemCsv->data as $id => $item) {
            // (optional) increment progress bar
            $progress->advance();

            //
            // Your parse code here
            //

            // grab item ui category for this item
            $itemUiCategory = $ItemUiCategoryCsv->at($item['ItemUICategory']);

            // Save some data
            $this->save('ItemDescriptions', [
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
        $progress->finish();
    }
}
