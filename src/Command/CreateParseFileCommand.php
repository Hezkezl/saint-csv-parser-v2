<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateParseFileCommand extends Command
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
            ->setName('app:parse:create')
            ->setDescription('Parse a CSV File.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('SaintCoinach Parse Creator');

        $folder = $io->ask('What is your projects name? Eg: XIVDB');
        $filename = $io->ask('What would the parse be named? Eg: AchievementPoints');

        // sanitize
        $folder = preg_replace('/\PL/u', '', $folder);
        $filename = preg_replace('/\PL/u', '', $filename);

        // build
        $folderPath = "{$this->projectDirectory}/src/Parsers/{$folder}";
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        $filePath = "{$folderPath}/{$filename}.php";
        $fileSkeleton = file_get_contents(__DIR__.'/CreateParseFileSkeleton.txt');
        $fileSkeleton = str_ireplace(['{AUTO_FOLDER}','{AUTO_FILENAME}'], [$folder,$filename], $fileSkeleton);

        file_put_contents($filePath, $fileSkeleton);

        $io->text("Generated file: <comment>{$filePath}</comment>");
        $io->text("Run as command: <comment>php bin/console app:parse:csv {$folder}:{$filename}</comment>");
        $io->newLine();
    }
}
