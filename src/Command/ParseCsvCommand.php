<?php

namespace App\Command;

use App\Parsers\CsvParse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCsvCommand extends Command
{
    /** @var CsvParse */
    private $csvParse;
    private $projectDirectory;

    public function __construct(CsvParse $csvParse, string $projectDirectory, $name = null)
    {
        $this->csvParse = $csvParse;
        $this->projectDirectory = $projectDirectory;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('app:parse:csv')
            ->setDescription('Parse a CSV File.')
            ->addArgument('csv_parser', InputArgument::REQUIRED, 'The csv parser to run.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<info>Starting CSV Parser</info>',
            '<info>- MEMORY: '. ini_get('memory_limit') .'</info>',
            '<info>- START: '. date('Y-m-d H:i:s') .'</info>',
            '<info>- PARSER: '. $input->getArgument('csv_parser') .'</info>',
        ]);


        $stopWatchStart = microtime(true);

        // get provided parser
        $parserName = $input->getArgument('csv_parser');
        $parserClass = $this->csvParse->get($parserName);

        if (!$parserClass) {
            $output->writeln("<error>Could not find a parser for: {$parserName}</error>");
            return;
        }

        $parser = new $parserClass();
        $parser
            ->setOutput($output)
            ->setProjectDirectory($this->projectDirectory)
            ->init()
            ->parse();

        $stopWatchFinish = microtime(true);
        $stopWatchDuration = round($stopWatchFinish-$stopWatchStart, 3);

        $output->writeln([
            '',
            '<info>Finished!</info>',
            '<info>- Duration: '. $stopWatchDuration .' seconds</info>'
        ]);
    }
}
