<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->addArgument('project', InputArgument::REQUIRED, 'Your projects name.')
            ->addArgument('filename', InputArgument::REQUIRED, 'Filename for your parse file.')
        ;
    }

    /**
     * todo - refactor the logic in here into a managed file service
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<info>Creating Parse File</info>',
        ]);

        $folder = $input->getArgument('project');
        $filename = $input->getArgument('filename');

        $output->writeln([
            "- Folder: {$folder}",
            "- Filename: {$filename}"
        ]);

        $folderPath = "{$this->projectDirectory}/src/Parsers/{$folder}";
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        $filePath = "{$folderPath}/{$filename}.php";
        $fileSkeleton = file_get_contents(__DIR__.'/CreateParseFileSkeleton.txt');
        $fileSkeleton = str_ireplace(['{AUTO_FOLDER}','{AUTO_FILENAME}'], [$folder,$filename], $fileSkeleton);

        file_put_contents($filePath, $fileSkeleton);
        $output->writeln("Generated file: {$filePath}");
        $output->writeln("Run as command: php bin/console app:parse:csv {$folder}:{$filename}");
    }
}
