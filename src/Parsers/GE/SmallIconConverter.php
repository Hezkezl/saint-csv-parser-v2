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
class SmallIconConverter extends ParseHandler implements ParseInterface
{
    use CsvParseTrait;
    use CsvParseDataHandlerTrait;

    public function parse()
    {
        // ensure output directory exists
        $outputDirectory = $this->getOutputFolder() . '/icons';
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0777, true);
        }

        // grab CSV files we want to use
        $ItemCsv = $this->csv('Item');

        // loop through data
        foreach($ItemCsv->data as $id => $item)
        {
            if (!$item['Name'] || !$item['Icon']) {
                continue;
            }

            // build item icon path
            $itemIcon = $this->getInputFolder() .'/icons/'. $this->iconize($item['Icon']);
            $itemIconHq = $this->getInputFolder() .'/icons/'. $this->iconize($item['Icon'], true);

            // if icon doesn't exist (not in the input folder icon list), then skip
            if (!file_exists($itemIcon)) {
                continue;
            }

            $this->output->writeln("Item: <comment>{$item['Name']}</comment>");

            // filename format
            $iconFileName = "{$outputDirectory}/{$item['Name']}_Icon.png";
            $iconFileNameHq = "{$outputDirectory}/{$item['Name']}_HQ_Icon.png";

            $this->output->writeln(
                sprintf(
                    '- copy <info>%s</info> to <info>%s</info>',
                    $itemIcon, $iconFileName
                )
            );

            copy($itemIcon, $iconFileName);

            // if hq exists, copy that
            if (file_exists($itemIconHq)) {
                $this->output->writeln(
                    sprintf(
                        '- copy <info>%s</info> to <info>%s</info>',
                        $itemIconHq, $itemIconHq
                    )
                );

                copy($itemIconHq, $iconFileNameHq);
            }
        }
    }

    /**
     * Converts SE icon "number" into a proper path
     */
    private function iconize($number, $hq = false)
    {
        $number = intval($number);
        $extended = (strlen($number) >= 6);

        if ($number == 0) {
            return null;
        }

        // create icon filename
        $icon = $extended ? str_pad($number, 5, "0", STR_PAD_LEFT) : '0' . str_pad($number, 5, "0", STR_PAD_LEFT);

        // create icon path
        $path = [];
        $path[] = $extended ? $icon[0] . $icon[1] . $icon[2] .'000' : '0'. $icon[1] . $icon[2] .'000';

        if ($hq) {
            $path[] = 'hq';
        }

        $path[] = $icon;

        // combine
        $icon = implode('/', $path) .'.png';

        return $icon;
    }
}
