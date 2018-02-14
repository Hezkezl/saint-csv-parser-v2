<?php

namespace App\Parsers\XIVDB;

use PhpParser\Error;
use PhpParser\ParserFactory;

class ActionDescriptionFormatter
{
    private $description;

    public function format($description)
    {
        $this->description = $description;

        $this->formatColors();
        $this->formatLogic();
        $this->formatHtml();

        return $this->description;
    }

    /**
     * Replace color entries with hex value and add SPAN placeholders
     */
    private function formatColors()
    {
        // easy one
        $this->description = str_ireplace('</Color>', '{{END_SPAN}}', $this->description);

        // replace all colour entries with hex values
        preg_match_all("#<Color(.*?)>#is", $this->description, $matches);

        foreach($matches[1] as $number) {
            $number = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
            $hex = substr(str_pad(dechex($number), 6, '0', STR_PAD_LEFT), -6);

            $this->description = str_ireplace("<Color({$number})>", "{{START_SPAN style=\"color:#{$hex};\"}}", $this->description);
        }
    }

    /**
     * Format any placeholder html into real html
     */
    private function formatHtml()
    {
        $this->description = json_encode($this->description);
        $this->description = str_ireplace(
            ['{{START_SPAN', '{{END_SPAN}}', '}}'],
            ['<span', '</span>', '>'],
            $this->description
        );
    }

    /**
     * Formats the description into logic
     */
    private function formatLogic()
    {
        // make it easier to split up each line when logic begins and ends
        $this->description = str_ireplace(['<','>'], ['###<','>###'], $this->description);

        // split logic by each code block
        $lines = array_values(array_filter(explode('###', $this->description)));

        /**
         * Convert logic into PHP code, this is so we can parse it using an abstract tree syntax parser.
         */
        foreach($lines as $i => $line) {
            $state = false;

            $state = substr($line, 0, 3) === '<If' ? 'if_open' : $state;
            $state = substr($line, 0, 4) === '</If' ? 'if_close' : $state;
            $state = $line === '<Else/>' ? 'if_else' : $state;

            switch($state) {
                case 'if_open':
                    $line = $this->convertIfOpenToPhpLogic($line);
                    break;

                case 'if_close':
                    $line = '<?php } ?>';
                    break;

                case 'if_else':
                    $line = '<?php } else { ?>';
                    break;
            }

            // replace line with formatted version
            $lines[$i] = $line;
        }

        try {
            $codestring = implode("\n", $lines);

            $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
            $logic = $parser->parse($codestring);

            // strips down everything
            $json = json_decode(json_encode($logic));

            // format the description into a minified format
            $json = $this->simpleJsonFormat($json);

            // fin
            $this->description = $json;
        } catch (Error $error) {
            die("\n\narse error: {$error->getMessage()}\n\n");
        }
    }

    /**
     * Builds a simple json format
     */
    private function simpleJsonFormat($json)
    {
        foreach($json as $i => $code) {
            switch($code->nodeType) {
                case 'Stmt_InlineHTML';
                    $json[$i] = trim($code->value);
                    break;

                case 'Stmt_If':
                    $json[$i] = $this->simpleJsonIfStatementFormat($code);
                    break;
            }
        }

        return $json;
    }

    /**
     * Simple json formatter
     */
    private function simpleJsonIfStatementFormat($code)
    {
        $operands = [
            'Expr_BinaryOp_GreaterOrEqual' => '>=',
            'Expr_BinaryOp_SmallerOrEqual' => '<=',
            'Expr_BinaryOp_NotEqual' => '!=',
            'Expr_BinaryOp_Equal' => '==',
        ];

        $stmt = (Object)[
            'condition' => (Object)[
                'left' => $code->cond->left->name,
                'right' => $code->cond->right->value,
                'operator' => $operands[$code->cond->nodeType],
            ],
            'true' => $this->simpleJsonConditionFormat($code->stmts[0]),
            'false' => $this->simpleJsonConditionFormat($code->else),
        ];

        return $stmt;
    }

    /**
     * Format if statement condition
     */
    private function simpleJsonConditionFormat($stmt)
    {
        // if not type is an if statement, recursively throw it back
        if ($stmt->nodeType == 'Stmt_If')
        {
            $stmt = $this->simpleJsonIfStatementFormat($stmt);
        }

        // if node type is an else statement, handle each one individually
        else if ($stmt->nodeType == 'Stmt_Else') {
            $stmt = $this->simpleJsonFormat($stmt->stmts);

            // if statement is just a string, set that
            if (isset($stmt[0]) && is_string($stmt[0]))
            {
                $stmt = trim($stmt[0]);
            }

            // if no statements, return empty
            elseif (empty($stmt))
            {
                $stmt = '';
            }
        }

        // if node type an inline html, use that value
        else if ($stmt->nodeType == 'Stmt_InlineHTML')
        {
            $stmt = trim($stmt->value);
        }

        // if no statements, return empty
        else if (empty($stmt->stmts))
        {
            $stmt = '';
        }

        return $stmt;
    }

    /**
     * Convert an "if" line to PHP logic
     */
    private function convertIfOpenToPhpLogic($line)
    {
        // Thank @Hez for this!
        preg_match_all('/\<If\((?P<operator>\w+)\((?P<parameter>\w+)\((?P<x>\d+)\),(?P<y>\d+)\)\)>/', $line, $matches);

        $statement = (Object)[
            'operator' => $matches['operator'][0],
            'parameter' => $matches['parameter'][0],
            'x' => $matches['x'][0],
            'y' => $matches['y'][0]
        ];

        $operators = [
            'GreaterThanOrEqualTo' => '>=',
            'LessThanOrEqualTo' => '<=',
            'NotEqual' => '!=',
            'Equal' => '==',
        ];

        $s = (Object)[
            'left'      => $this->getPlayerParameterContext($statement->parameter, $statement->x),
            'operator'  => $operators[$statement->operator],
            'right'     => $statement->y,
        ];

        return sprintf(
            '<?php if ($%s %s %s) { ?>',
            $s->left,
            $s->operator,
            $s->right
        );
    }

    /**
     * List of PlayerParameters
     */
    private function getPlayerParameterContext($param, $value)
    {
        $key = "{$param}_{$value}";

        switch($key) {
            default: return "UNKNOWN_{$key}";

            case 'PlayerParameter_68': return 'class_job_id';
            case 'PlayerParameter_69': return 'class_job_level';
            case 'PlayerParameter_70': return 'starting_city_id';
            case 'PlayerParameter_71': return 'race';
            case 'PlayerParameter_72': return 'class_job_level';
        }
    }
}
