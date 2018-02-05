# saint-csv-parser-v2

A microservice for parsing SaintCoinach CSV files

___

### Usage

To use this tool you need to write a CSV Parsing file. Once you have cloned the repository make a new folder in the parsers, eg:

- `src/Parsers/MyAwesomeParser`

In here you will want to make a PHP file for the type of content you want to parse, eg:

- `Item.php`

Populate the file with the skeleton code:

```php
<?php

namespace App\Parsers\MyAwesomeParser;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

class MyItemParser implements ParseInterface
{
    use CsvParseTrait;

    public function parse()
    {

    }
}

```

Now we need to assign it a command, open: `src/Parsers/CsvParse` and you will see a list of parsers, add yours:

- `case 'MyItemParser': return \App\Parsers\MyAwesomeParser\MyItemParser::class;`

Now you can run:

- `php bin/console app:parse:csv MyItemParser`

This will run your `parse()` function. here is where the meat of your data will be.

#### Functions

**getCsvFile(<filename>)**

Get a CSV file based on its exact filename, eg:

```php
$itemCsv = $this->getCsvFile('Item');
```

**loop**

Loop through data:

```php
foreach($itemCsv->data as $id => $row) {
    $this->output->write('Item Name: '. $row['Name']);
}
```

**at(<id>)**

If you are extending to other files and need a row at a specific id, you can use `at()`, eg:

```php
$memberType = $contentMemberTypeCsv->at($conditions['ContentMemberType']);
```

**find(<column>, <value>)**

If you need to find a row(s) to a specific value, you can use `find()`, eg:

```php
$conditions = $contentFinderConditionCsv->find('InstanceContent', $id);
```

**dump(filename, data)**

Save data to the cache directory as `dump_<filename>`, eg:

```php
$this->dump('InstanceContentJson', json_encode($data, JSON_PRETTY_PRINT));
```
___

Fetches data from:

- https://github.com/viion/ffxiv-datamining

Example command:

- php bin/console app:parse:csv XIVDB_InstanceContent
