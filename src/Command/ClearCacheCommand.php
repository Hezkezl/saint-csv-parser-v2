<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends Command
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
            ->setName('app:parse:clear-cache')
            ->setDescription('Clear the cache folder.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<info>Clearing Parse Cache</info>',
        ]);

        // grab all files
        $root = $this->projectDirectory . getenv('CACHE_DIRECTORY');
        $files = array_diff(scandir($root), ['..', '.', '.gitkeep']);

        // delete them all
        foreach($files as $file) {
            $file = "{$root}/{$file}";
            $output->writeln('Deleting: '. $file);
            unlink($file);
        }

        $output->writeln('Complete!');
    }
}
