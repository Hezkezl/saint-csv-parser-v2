<?php

namespace App\Parsers\XIVDB;

use PhpParser\Error;
use PhpParser\ParserFactory;

class ActionDescriptionsConditions
{

    public function parse($description)
    {
        $description = str_ireplace(['<','>'], ['##<','>##'], $description);

        print_r("\n\n". $description . "\n\n");

        $lines = explode('##', $description);
        $lines = array_values(array_filter($lines));

        $indent = 0;
        foreach($lines as $i => $line) {
            $ifOpen = substr($line, 0, 3) === '<If';
            $ifClose = substr($line, 0, 4) === '</If';
            $ifElse = $line === '<Else/>';

            $indent = $ifOpen ? $indent += 1 : $indent;
            $indent = $ifClose ? $indent -= 1 : $indent;

            // convert if open condition
            if ($ifOpen) {
                $line = $this->conditionConverter($line);
            } elseif ($ifClose) {
                $line = '<?php } ?>';
            } elseif ($ifElse) {
                $line = '<?php } else { ?>';
            }

            $lines[$i] = $line;
        }

        // ------------------------------------------

        print_r($lines);

        $codestring = implode("\n", $lines);

        try {

            $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
            $logic = $parser->parse($codestring);

            // strips down everything
            $json = json_decode(json_encode($logic));
            $json = $this->format($json);

        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return false;
        }

        print_r($json);


        file_put_contents(
            __DIR__ .'/ActionDescriptionsConditions.json',
            json_encode($json, JSON_PRETTY_PRINT)
        );

        die;
        return $description;
    }


    private function format($json)
    {
        foreach($json as $i => $code) {
            switch($code->nodeType) {
                case 'Stmt_InlineHTML';
                    $json[$i] = trim($code->value);
                    break;

                case 'Stmt_If':
                    $json[$i] = $this->formatIf($code);
                    break;
            }
        }

        return $json;
    }

    private function formatIf($code)
    {
        $operands = [
            'Expr_BinaryOp_GreaterOrEqual' => '>=',
            'Expr_BinaryOp_SmallerOrEqual' => '<=',
            'Expr_BinaryOp_NotEqual' => '!=',
            'Expr_BinaryOp_Equal' => '==',
        ];

        $stmt = (Object)[
            'condition' => (Object)[
                'left' => '',
                'operator' => '',
                'right' => ''
            ],
            'true' => '',
            'false' => '',
        ];

        $stmt->condition->left = $code->cond->left->name;
        $stmt->condition->right = $code->cond->right->value;
        $stmt->condition->operator = $operands[$code->cond->nodeType];

        $stmt->true = $code->stmts[0];
        $stmt->false = $code->else;

        if ($stmt->true->nodeType == 'Stmt_If') {
            $stmt->true = $this->formatIf($stmt->true);
        } else if ($stmt->true->nodeType == 'Stmt_Else') {
            $stmt->true = $this->format($stmt->true->stmts);
            if (isset($stmt->true[0]) && is_string($stmt->true[0])) {
                $stmt->true = trim($stmt->true[0]);
            } elseif (!$stmt->true) {
                $stmt->true = '';
            }
        } else if ($stmt->true->nodeType == 'Stmt_InlineHTML') {
            $stmt->true = trim($stmt->true->value);
        } else if (empty($stmt->true->stmts)) {
            $stmt->true = '';
        }

        if ($stmt->false->nodeType == 'Stmt_If') {
            $stmt->false = $this->formatIf($stmt->false);
        } else if ($stmt->false->nodeType == 'Stmt_Else') {
            $stmt->false = $this->format($stmt->false->stmts);
            if (isset($stmt->false[0]) && is_string($stmt->false[0])) {
                $stmt->false = trim($stmt->false[0]);
            } elseif (!$stmt->false) {
                $stmt->false = '';
            }
        } else if ($stmt->false->nodeType == 'Stmt_InlineHTML') {
            $stmt->false = trim($stmt->false->value);
        } else if (empty($stmt->false->stmts)) {
            $stmt->false = '';
        }

        return $stmt;
    }

    /**
     * Convert conditions
     */
    private function conditionConverter($line)
    {
        preg_match_all('/\<If\((?P<condition>\w+)\((?P<parameter>\w+)\((?P<x>\d+)\),(?P<y>\d+)\)\)>/', $line, $matches);

        $statement = (Object)[
            'condition' => $matches['condition'][0],
            'parameter' => $matches['parameter'][0],
            'x' => $matches['x'][0],
            'y' => $matches['y'][0]
        ];

        $conditions = [
            'GreaterThanOrEqualTo' => '>=',
            'LessThanOrEqualTo' => '<=',
            'NotEqual' => '!=',
            'Equal' => '==',
        ];

        $s = (Object)[
            'left'  => $this->getPlayerParameterContext($statement->parameter, $statement->x),
            'op'    => $conditions[$statement->condition],
            'right' => $statement->y,
        ];

        return sprintf(
            '<?php if ($%s %s %s) { ?>',
            $s->left,
            $s->op,
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
