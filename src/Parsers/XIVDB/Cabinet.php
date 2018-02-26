<?php

namespace App\Parsers\XIVDB;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * XIVDB:Cabinet
 */
class Cabinet implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        $this->output->writeln('<comment>XIVDB:Cabinet</comment>');

        // grab CSV files we want to use
        $CabinetCsv = $this->csv('Cabinet');

        // (optional) start a progress bar
        $progress = new ProgressBar($this->output, $CabinetCsv->total);

        // loop through data
        foreach($CabinetCsv->data as $id => $cabinet) {
            // (optional) increment progress bar
            $progress->advance();

            //
            // Your parse code here
            //


            // add to array
            $this->data[] = [
                'id' => "'{$cabinet['id']}'",
                'item' => "'{$cabinet['Item']}'",
                'order' => "'{$cabinet['Order']}'",
                'category' => "'{$cabinet['Category']}'",
            ];
        }

        // (optional) finish progress bar
        $progress->finish();

        // build sql - HAX
        $sql = [];
        foreach($this->data as $row) {
            $sql[] = 'INSERT INTO xiv_cabinet (`id`, `item`, `order`, `category`) VALUES ('. implode(',', $row) .');';
        }

        $this->dump('CabinetSql', implode("\n", $sql));
    }
}
