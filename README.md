# API monbillet.ch
*By [monbillet.ch](https://monbillet.ch/)*

## Installation

Use [Composer](https://getcomposer.org/) to install the library.

``` bash
$ composer require monbillet/api:dev-main
```

## Usage

```php
use Monbillet\ApiClient;

$token = ''; // Your API key
$client = new ApiClient($token);

$events = $client->getEvents();
$event_groups = $client->getEventGroups();

$id = ''; // The id or unique name of the event
$event = $client->getEvent($id);
```

## Running the example

1. Write your API key in the **example/.env.sample** file
2. Run ```composer dump-autoload```
3. Run ```composer run-script php-dev```

Visit and test [localhost:9000](http://localhost:9000/)
*Note: This web server is designed to aid application development. It should not be used on a public network.*

