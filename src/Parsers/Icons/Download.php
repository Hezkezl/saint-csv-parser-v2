<?php

namespace App\Parsers\Icons;

use PHPHtmlParser\Dom;
use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv Icons:Download
 */
class Download implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {
        $dom = new Dom;
        $list = json_decode(file_get_contents(__DIR__ .'/IconData.json'));
        $baseUrl  = 'https://na.finalfantasyxiv.com/lodestone/playguide/db/item/?patch=&db_search_category=item&category2=&q={ITEM_NAME}';
        $total = count($list);

        foreach ($list as $i => $icon) {
            $current = ($i + 1);
            $this->io->section("{$current}/{$total} - {$icon->id} {$icon->name_en}");

            // grab search results
            $this->io->text('Getting search html ...');
            $searchUrl  = str_ireplace(['{ITEM_NAME}',' '], [$icon->name_en, '+'], $baseUrl);
            $searchHtml = file_get_contents($searchUrl);

            // parse html
            $dom->load($searchHtml);
            $this->io->text('Loaded Html, looking for rows');

            // grab result rows
            $rows = $dom->find('.db-table__txt--detail_link');

            // find results
            $this->io->text('Looping through rows');


            /** @var Dom\HtmlNode $row */
            foreach ($rows as $row) {
                $name = trim($row->innerHtml());
                $link = 'https://na.finalfantasyxiv.com/'. $row->getAttribute('href');

                if ($name === $icon->name_en) {
                    // grab content html
                    $this->io->text('Parsing content page: '. $link);

                    if (!$link) {
                        break;
                    }

                    $contentHtml = file_get_contents($link);
                    $dom->load($contentHtml);

                    // grab icon
                    $iconUrl = $dom->find('.db-view__item__icon__item_image')[0]->getAttribute('src');
                    $this->io->text('Icon url = '. $iconUrl);

                    if (!is_dir(__DIR__.'/imgs/')) {
                        mkdir(__DIR__.'/imgs/', 0777, true);
                    }

                    // download
                    $this->io->text('Downloading ...');
                    $iconData = file_get_contents($iconUrl);
                    $filename = __DIR__.'/imgs/'. $icon->name_en .' Icon.png';
                    file_put_contents($filename, $iconData);
                    $this->io->text('Saved to: '. $filename);
                    break;
                }
            }
        }
    }
}
