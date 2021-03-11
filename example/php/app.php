<?php
/**
 * This is a basic example to show how to use the ApiClient from the 
 * monbillet.ch library. Be sure to correctly set the right TOKEN to be able
 * to get the datas. 
 */
 
use Monbillet\ApiClient;

$token = getenv('MB_API_KEY');
$client = new ApiClient($token);

$event = null;
$events = null;
$event_groups = null;

// basic default router
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 'events';
}

// set $event, $events or $event_groups depending on the request
if ($page === 'event' && isset($_GET['q'])) {
    $event = $client->getEvent($_GET['q']);
} else if ($page === 'event-groups'){
    $event_groups = $client->getEventGroups();
} else if ($page === 'events'){
    $events = $client->getEvents();
}

(function() use ($page, $event, $events, $event_groups){
    require 'layout.php';
})();

