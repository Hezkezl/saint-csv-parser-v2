<?php

namespace App\Parsers;

trait CsvParseDataHandlerTrait
{
    protected $settings = [
        'filename' => '{projectDirectory}{outputDirectory}/{filename}',
        'data' => [],
        'chunks' => [
            'data' => [],
            'count' => 1,
            'max' => 200,
        ]
    ];

    public function getInputFolder()
    {
        return $this->projectDirectory . getenv('INPUT_DIRECTORY');
    }

    public function getOutputFolder()
    {
        return $this->projectDirectory . getenv('OUTPUT_DIRECTORY');
    }

    /**
     * Set the maximum chunk size
     */
    public function setMaxChunks(int $lines)
    {
        $this->settings->chunks->max = $lines;
    }

    /**
     * Save some data
     */
    public function save(string $filename, $data)
    {
        $filename = $this->getFullFilename($filename);

        // if data is empty, delete file
        if (empty($this->settings->data) && file_exists($filename)) {
            unlink($filename);
        }

        // json encode the data if it's an array
        $data = is_array($data) ? json_encode($data) : $data;

        // append
        file_put_contents($filename, $data . PHP_EOL, FILE_APPEND);
        $this->settings->data[] = $data;
    }

    /**
     * Save some data into chunks
     */
    public function chunk(string $filename, $data, $force = false)
    {
        $filename = $this->getFullFilename($filename);

        $this->settings->chunks->data[] = $data;

        $dataSize = count($this->settings->chunks->data);
        $maxSize = $this->settings->chunks->max;

        // skip if not at the chunk size
        if (!$force && $dataSize < $maxSize) {
            return;
        }

        // save chunks
        $chunkCount = $this->settings->chunks->count;
        foreach($$this->settings->chunks->data as $entry) {
            $this->save("chunk_{$chunkCount}_{$filename}", $entry);
        }

        // reset
        $this->settings->chunks->count++;
        $this->settings->chunks->data = [];
    }

    /**
     * Gets the fully qualified filename
     */
    private function getFullFilename(string $filename)
    {
        return str_ireplace(
            [
                '{projectDirectory}',
                '{outputDirectory}',
                '{filename}'
            ], [
                $this->projectDirectory,
                getenv('OUTPUT_DIRECTORY'),
                $filename
            ],
            $this->settings->filename
        );
    }
}
