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

            if ($id == 182) {
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
        // fix colours
        $description = $this->formatColor($description);

        // build text trees
        $adc = new ActionDescriptionsConditions();
        $description = $adc->parse($description);

        return $description;
    }

    /**
     * Replace color entries with hex value
     */
    private function formatColor($description)
    {
        // easy one
        $description = str_ireplace('</Color>', '[END_SPAN]', $description);

        // replace all colour entries with hex values
        preg_match_all("#<Color(.*?)>#is", $description, $matches);

        foreach($matches[1] as $number) {
            $number = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
            $hex = substr(str_pad(dechex($number), 6, '0', STR_PAD_LEFT), -6);

            $description = str_ireplace("<Color({$number})>", "[START_SPAN style=\"color:#{$hex};\"]", $description);
        }

        return $description;
    }
}
