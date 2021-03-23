*By [monbillet.ch](https://monbillet.ch/)*

## Documentation

Please refer to our [documentation](https://monbillet.ch/api/v1/doc) for more information.

## Installation

Use [Composer](https://getcomposer.org/) to install the library.

```bash
$ composer require monbillet/api-client
```

## Usage

```php
use Monbillet\ApiClient;

$token = ''; // your API key
$cache_path = ''; // path to a writable directory to store the cache
$cache_expire_minutes = 10;
$client = new ApiClient($token, $cache_path, $cache_expire_minutes);

$events = $client->getEvents();
$event_groups = $client->getEventGroups();

$event_id = ''; // the id or unique name of an event
$event = $client->getEvent($event_id);

$group_id = ''; // the id or unique name of a group
$event_group = $client->getGroup($group_id);
```

## Running the example

1. Write your API key in the **example/.env.sample** file
2. Run ```composer dump-autoload```
3. Run ```composer run-script example```

Visit and test [localhost:9000](http://localhost:9000/)
*Note: This web server is designed to aid application development. It should not be used on a public network.*

