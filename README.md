# API monbillet.ch
*By [monbillet.ch](https://monbillet.ch/)*


## Usage

```php
use Monbillet\ApiClient;

$token = ''; //your API Key
$client = new ApiClient($token);

$events = $client->getEvents();
$event_groups = $client->getEventGroups();

$id_event = ''; // the id of a specific event
$event = $client->getEvent($id_event);
```

## Running the example

1. Write your API key in the **.env.sample** file
2. Run ```composer dump-autoload```
3. Run ```composer run-script php-dev```

Visit and test [localhost:9000](http://localhost:9000/)
*Note: This web server is designed to aid application development. It should not be used on a public network.*

