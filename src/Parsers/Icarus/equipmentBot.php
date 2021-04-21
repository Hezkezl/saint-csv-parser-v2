<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * php bin/console app:parse:csv GE:equipmentBot
 */
class equipmentBot implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{index},{name},{cat},{restriction}";

    public function parse()
    {
        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');
        $ItemUiCategoryCsv = $this->csv('ItemUiCategory');
        include (dirname(__DIR__) . '/Paths.php');

        // if I want to use pywikibot to create these pages, this should be true. Otherwise if I want to create pages
        // manually, set to false
        $Bot = "false";

        // (optional) start a progress bar
        $this->io->progressStart($ItemCsv->total);
        $this->PatchCheck($Patch, "Item", $ItemCsv);
        $PatchNumber = $this->getPatch("Item");

        // loop through data
        foreach ($ItemCsv->data as $id => $item) {
            $this->io->progressAdvance();
            $index = $item['id'];

            $Name = $item['Name'];
            $Patch = $PatchNumber[$id];

            if ($Bot == "true") {
                $Top = "{{-start-}}\n'''$Name/Patch'''\n$Patch\n<noinclude>[[Category:Patch Subpages]]</noinclude>\n{{-stop-}}{{-start-}}\n'''$Name'''\n";
                $Bottom = "{{-stop-}}";
            } else {
                $Top = "http://ffxiv.gamerescape.com/wiki/$Name?action=edit\n";
                $Bottom = "";
            };
            // grab item ui category for this item
            $itemUiCategory = $ItemUiCategoryCsv->at($item['ItemUICategory'])['Name'];

            // grab item ui category for this item
            $EquipRestriction = $item['EquipRestriction'];


            // Save some data
            $data = [
                //'{top}' => $Top,
                //'{bottom}' => $Bottom,
                '{index}' => $index,
                '{name}' => $Name,
                '{cat}' => $itemUiCategory,
                '{restriction}' => $EquipRestriction,
            ];

            // format using Gamer Escape formatter and add to data array
            // need to look into using item-specific regex, if required.
            $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        }

        // save our data to the filename: GeRecipeWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("NEWEQUIPMENT.txt", 999999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}
