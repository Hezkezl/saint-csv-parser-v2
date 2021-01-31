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
        include(dirname(__DIR__) . '/Paths.php');

        // ensure output directory exists
        $outputDirectory = $this->getOutputFolder() . "/$CurrentPatchOutput/40pxitemicons";
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0777, true);
        }

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');

        // loop through data
        foreach ($ItemCsv->data as $id => $item) {
            // Skip shit with no name or icon
            if (!$item['Name'] || !$item['Icon']) {
                continue;
            }
            //only run this code if you need to get specific item ID#'s for icon copying.
            //$skip = array("30413", "30414", "30415", "30416", "30417", "30418", "30762", "30763", "30764", "30765", "30766", "30862", "30871", "30875", "31184", "31185", "31186");
            //Also need to add a new closing } at the very bottom of the file.
            //if (in_array($id, $skip)) {
            //Alternate code: the next line, OR the two after it
            //if (in_array('$item["id"]', $skip, FALSE)) continue;
            //$variable = FALSE;
            //if (in_array('$item["id"]', $skip, $variable)) continue;

            $item['Name'] = strip_tags($item['Name']);

            // build icon and hq icon input folder paths
            $itemIcon = $this->getInputFolder() .'/icon/'. $this->iconize($item['Icon']);
            //$itemIconHq = $this->getInputFolder() .'/icon/'. $this->iconize($item['Icon'], true);

            // if icon doesn't exist (not in the input folder icon list), then skip
            if (!file_exists($itemIcon)) {
                continue;
            }

            // inform console what item we're copying
            $this->io->text("Item: <comment>{$item['Name']}</comment>");

            // build output filenames for icon + hq icon
            // replace spaces with underscores, also replace any / in names with a -
            // (mainly for "Torn from the Heavens/The Dark Colossus orchestrion roll")
            $iconFileName = "{$outputDirectory}/". str_replace(" ", "_", str_replace("/", "-", $item['Name'])) ."_Icon.png";
            //$iconFileNameHq = "{$outputDirectory}/". str_replace(" ", "_", $item['Name']) ."_HQ_Icon.png";

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