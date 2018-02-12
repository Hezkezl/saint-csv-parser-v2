<?php

namespace App\Parsers\XIVDB;

use App\Parsers\CsvParseDataHandlerTrait;
use App\Parsers\CsvParseTrait;
use App\Parsers\ParseHandler;
use App\Parsers\ParseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * XIVDB:ActionDescriptions
 */
class ActionDescriptions extends ParseHandler implements ParseInterface
{
    use CsvParseTrait;
    use CsvParseDataHandlerTrait;

    public function parse()
    {
        // grab CSV files we want to use
        $ActionTransientCsv = $this->csv('ActionTransient');
        $this->output->writeln(['','']);

        // loop through data
        foreach($ActionTransientCsv->data as $id => $desc) {

            $this->output->writeln("Action: {$id}");

            if ($id == 27) {
                $description = $this->formatDescription($desc['Description']);

                die("\n\n\n\n");
            }


            $this->save('ActionDescriptions', [
                'id' => $desc['id'],
            ]);
        }
    }

    private function formatDescription(string $description)
    {
        $lines = [];
        foreach(explode("\n", $description) as $line) {
            $lines[] = $this->formatDescriptionCleaner(trim($line));
        }

        $lines = '<p>'. implode("</p><p>", $lines) .'</p>';

        print_r($lines);
    }

    private function formatDescriptionCleaner($line)
    {
        /**
         * These conditions are quite hacky but they help make things easier.
         *
         * Types: https://github.com/Rogueadyn/SaintCoinach/blob/f969b441584688c02dde2fadac548c4a5aaa3faa/SaintCoinach/Text/DecodeExpressionType.cs
         *
         *  GreaterThanOrEqualTo
         *  UnknownComparisonE1
         *  LessThanOrEqualTo
         *  NotEqual
         *  Equal
         *
         *  IntegerParameter
         *  PlayerParameter
         *  StringParameter
         *  ObjectParameter
         */

        $conditions = [
            '<Color(' => 'formatColor',
        ];

        foreach($conditions as $needle => $function) {
            if (stripos($line, $needle) !== false) {
                $line = $this->$function($line);
            }
        }

        return $line;
    }

    /**
     * Replace color entries with hex value
     */
    private function formatColor($line)
    {
        // easy one
        $line = str_ireplace('</Color>', '</span>', $line);

        // replace all colour entries with hex values
        preg_match_all("#<Color(.*?)>#is", $line, $matches);

        foreach($matches[1] as $number) {
            $number = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
            $hex = substr(str_pad(dechex($number), 6, '0', STR_PAD_LEFT), -6);

            $line = str_ireplace("<Color({$number})>", "<span style=\"color:#{$hex};\">", $line);
        }

        return $line;
    }
}
