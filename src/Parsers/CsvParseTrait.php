<?php

namespace App\Parsers;

use Symfony\Component\Console\Output\OutputInterface;

trait CsvParseTrait
{
    private $projectDirectory;

    public function setProjectDirectory(string $projectDirectory)
    {
        $this->projectDirectory = $projectDirectory;
        return $this;
    }

    /** @var OutputInterface */
    protected $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    // ---------------------------------------------

    protected $ex;

    public function init()
    {
        //
        // Check for ex.json file
        //

        $cacheDirectory = $this->projectDirectory . getenv('CACHE_DIRECTORY');
        $exJsonFilename = "$cacheDirectory/ex.json";

        if (!file_exists($exJsonFilename)) {
            $this->output->writeln('ex.json file does not exist, downloading from github ...');

            $ex = file_get_contents(getenv('GITHUB_EX_JSON'));
            if (!$ex) {
                $this->output->writeln('<error>Failed to download ex.json from: '. getenv('GITHUB_EX_JSON'));die;
            }

            file_put_contents($exJsonFilename, $ex);
            $this->ex = json_decode($ex);
            $this->output->writeln('✓ Download complete');
        }

        return $this;
    }

    public function getCsvFile($content): ParseWrapper
    {
        $cache = $this->projectDirectory . getenv('CACHE_DIRECTORY');
        $filename = "{$cache}/{$content}.csv";

        // check cache and download if it does not exist
        if (!file_exists($filename)) {
            $this->output->writeln("Downloading: '{$content}.csv' for the first time ...");

            $githubFilename = str_ireplace('{content}', $content, getenv('GITHUB_CSV_FILE'));
            $githubFiledata = file_get_contents($githubFilename);

            if (!$githubFiledata) {
                $this->output->writeln('<error>Could not download file from github: '. $githubFilename);die;
            }

            file_put_contents($filename, $githubFiledata);
            $this->output->writeln('✓ Download complete');
        }

        // grab wrapper
        $parser = new ParseWrapper($content, $filename);
        file_put_contents($filename.'.columns', json_encode($parser->columns, JSON_PRETTY_PRINT));
        file_put_contents($filename.'.offsets', json_encode($parser->offsets, JSON_PRETTY_PRINT));
        file_put_contents($filename.'.data', json_encode($parser->data, JSON_PRETTY_PRINT));

        return $parser;
    }

    public function dump($filename, $data)
    {
        $cache = $this->projectDirectory . getenv('CACHE_DIRECTORY');
        $filename = "{$cache}/dump_{$filename}";

        file_put_contents($filename, $data);
    }
}
