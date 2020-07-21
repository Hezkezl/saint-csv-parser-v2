<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseDataHandlerTrait;
use App\Parsers\CsvParseTrait;
use App\Parsers\ParseHandler;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * GE:SmallIconConverter
 */
class SmallIconConverter implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // ensure output directory exists
        $outputDirectory = $this->getOutputFolder() . "/$CurrentPatchOutput/40pxitemicons";
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0777, true);
        }

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');

        // loop through data
        foreach($ItemCsv->data as $id => $item)
        {
            // Skip shit with no name or icon
            $item['Name']  = strip_tags($item['Name']);
            if (!$item['Name'] || !$item['Icon']) {
                continue;
            }

            // build icon and hq icon input folder paths
            $itemIcon = $this->getInputFolder() .'/icon/'. $this->iconize($item['Icon']);
            $itemIconHq = $this->getInputFolder() .'/icon/'. $this->iconize($item['Icon'], true);

            // if icon doesn't exist (not in the input folder icon list), then skip
            if (!file_exists($itemIcon)) {
                continue;
            }

            // inform console what item we're copying
            $this->io->text("Item: <comment>{$item['Name']}</comment>");

            // build output filenames for icon + hq icon
            $iconFileName = "{$outputDirectory}/{$item['Name']}_Icon.png";
            $iconFileNameHq = "{$outputDirectory}/{$item['Name']}_HQ_Icon.png";

            // console output
            $this->io->text(
                sprintf(
                    '- copy <info>%s</info> to <info>%s</info>', $itemIcon, $iconFileName
                )
            );

            // copy the input icon to the output filename
            copy($itemIcon, $iconFileName);

            // if hq exists, copy that
            //if (file_exists($itemIconHq)) {
            //    //console output
            //    $this->io->text(
            //        sprintf(
            //            '- copy <info>%s</info> to <info>%s</info>', $itemIconHq, $itemIconHq
            //        )
            //    );

            //    // copy the input icon to the output filename
            //    copy($itemIconHq, $iconFileNameHq);
            //}
        }
    }
}
