<?php

namespace App\Command;

use App\Parsers\Example\ItemCategories;
use App\Parsers\Hello\World;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('SaintCoinach CSV Parser');
        $io->table(
            ['MEMORY LIMIT', 'START TIME', 'PARSER'],
            [
                [
                    ini_get('memory_limit'),
                    date('Y-m-d H:i:s'),
                    $input->getArgument('csv_parser')
                ]
            ]
        );

        $stopWatchStart = microtime(true);

        // grab project and fiename
        [$project, $filename] = explode(':', $input->getArgument('csv_parser'));

        // get a list of parsers for supplied project
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

        // check project path
        if (empty($projectPath)) {
            $io->error("Could not find the project folder: {$project} in src/Parsers");
            return;
        }

        // build parser path
        $parserFile = "{$projectPath}/{$filename}.php";
        if (!is_file($parserFile)) {
            $io->error("Could not find parsing file: {$projectPath}/{$filename}.php");
            return;
        }

        // include parser
        require_once $parserFile;

        // build class
        $parserClassName = "\App\Parsers\\". $project ."\\". $filename;
        $io->text("RUN :: {$parserClassName}");

        // create class (World set here for auto-complete)
        /** @var World $parser */
        $parser = new $parserClassName();

        if (!$parser) {
            $io->error("Could not create class from parse file: {$filename} via: {$parserClassName}");
            return;
        }

        // run parser
        $parser
            ->setInputOutput($input, $output)
            ->setProjectDirectory($this->projectDirectory)
            //->init()
            ->parse();

        $stopWatchFinish = microtime(true);
        $stopWatchDuration = round($stopWatchFinish-$stopWatchStart, 3);

        $io->text([
            '',
            '<info>Finished!</info>',
            '<comment>Duration: '. $stopWatchDuration .' seconds</comment>',
            '<comment>Find your data in the /output folder</comment>',
            '',
        ]);
    }
}
