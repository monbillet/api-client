<?php
/**
 * This is a basic example to show how to use the ApiClient from the
 * monbillet.ch library. Make sure to configure your token in .env.sample
 *
 * THIS IS FOR DEMONSTRATION PURPOSES ONLY.
 * DO NOT BLINDLY COPY ALL OF THIS FOR USE ON YOUR WEBSITE.
 */
 
use Monbillet\ApiClient;
use Monbillet\NotFoundException;

$token = getenv('MB_API_KEY');
$base_url = getenv('MB_API_BASE_URL');
$client = new ApiClient($token, dirname(__DIR__) . "/cache", 0, $base_url);

$error = null;
$event = null;
$events = null;
$event_groups = null;

// Basic router
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 'events';
}

if (isset($_GET['cache']) && ($_GET['cache'] === 'delete')) {
    $client->deleteCache();
}

try {
    // Url: http://localhost:9000/page=event&q=<event_id_or_uniquename>
    if ($page === 'event') {
        if (!isset($_GET['q'])) {
            throw new Exception('Missing parameter "q".');
        }

        if (!is_string($_GET['q'])) {
            throw new Exception('Wrong parameter "q", expected string.');
        }

        $event = $client->getEvent($_GET['q']);

    // Url: http://localhost:9000/page=event-groups
    } elseif ($page === 'event-groups') {
        $event_groups = $client->getEventGroups();

    // Url: http://localhost:9000/page=events
    } elseif ($page === 'events') {
        $events = $client->getEvents();
    }else {
        throw new NotFoundException('Page ' . $page . ' not found', 404);
    }

} catch (Exception $e) {
    $code = $e->getCode();

    if ($code >= 400) {
        http_response_code($code);
    }

    $error = get_class($e) . ': ' . $e->getMessage();
}

(function () use ($page, $event, $events, $event_groups, $error) {
    require 'layout.php';
})();
