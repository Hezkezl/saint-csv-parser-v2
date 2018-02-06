<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCsvCommand extends Command
{
    private $projectDirectory;

    public function __construct(string $projectDirectory, $name = null)
    {
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

    /**
     * todo - refactor the logic in here into a managed file service
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<info>Starting CSV Parser</info>',
            '<info>- MEMORY: '. ini_get('memory_limit') .'</info>',
            '<info>- START: '. date('Y-m-d H:i:s') .'</info>',
            '<info>- PARSER: '. $input->getArgument('csv_parser') .'</info>',
        ]);

        $stopWatchStart = microtime(true);

        // grab project and fiename
        [$project, $filename] = explode(':', $input->getArgument('csv_parser'));


        // get a list of parsers
        $root = $this->projectDirectory . '/src/Parsers';
        $directories = array_diff(scandir($root), ['..', '.']);
        $projectPath = false;
        foreach($directories as $directory) {
            if ($directory != $project) {
                continue;
            }

            $path = "{$root}/{$directory}";

            if ($directory == $project && is_dir($path)) {
                $projectPath = $path;
                break;
            }
        }

        if (empty($projectPath)) {
            $output->writeln("<error>Could not find the project folder: {$project} in src/Parsers");die;
        }

        $parserFile = "{$projectPath}/{$filename}.php";
        if (!is_file($parserFile)) {
            $output->writeln("<error>Could not find parsing file: {$projectPath}/{$filename}.php");die;
        }
        require_once $parserFile;

        $classname = "\App\Parsers\\". $project ."\\". $filename;
        $output->writeln("Intializing parsing class: {$classname}");

        // create class
        $parser = new $classname();

        if (!$parser) {
            $output->writeln("<error>Could not create class from parse file: {$filename}</error>");
            return;
        }

        // run parser
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
