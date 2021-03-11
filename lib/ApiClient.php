<?php

namespace Monbillet;

use DateTime;
use Exception;

/**
 * This class allows you to quickly and easily use the monbillet api
 */
class ApiClient
{
    const HEADER_NAME = "X-Monbillet-Api-Token";

    /**
     * @var string
     */
    private $auth;

    /**
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->auth = self::HEADER_NAME .':'. $apiKey;
    }

    /**
     * Get the list of events
     * 
     * @return array 
     * @throws Exception 
     */
    public function getEvents(): array
    {
        $host = 'https://monbillet.test/api/v1/events';
        $data = $this->http_get($host, $this->auth);
        return array_map([$this, 'convertDates'], $data['events']);
    }

    /**
     * Get the list of events order by groups
     * 
     * @return array 
     * @throws Exception 
     */
    public function getEventGroups(): array
    {
        $host = 'https://monbillet.test/api/v1/event-groups';
        $data = $this->http_get($host, $this->auth);

        return array_map(function($event_group) {
            return array_merge(
                $event_group, 
                array_map([$this, 'convertDates'], $event_group['events'])
            );
        } , $data['event-groups']);
    }

    /**
     * Get the informations about a specific event
     * 
     * @param string $event 
     * @return array 
     */
    public function getEvent(string $event): array
    {
        $host = 'https://monbillet.test/api/v1/events/' . $event;

        try {
            $data = $this->http_get($host, $this->auth);
        } catch (Exception $e) {
            // trying to access a wrong ressource
            return [];
        }

        return $data['event'];
    }

    /**
     * Function to convert dates to the Date format
     * 
     * @param mixed $event 
     * @return array 
     */
    private function convertDates($event): array 
    {
        return array_merge($event, [
            'dateFirstShow' => new DateTime($event['dateFirstShow']),
            'dateLastShow' => new DateTime($event['dateLastShow']),
        ]);
    }

    /**
     * HTTP GET request
     * 
     * @param mixed $url 
     * @param mixed $auth 
     * @return array 
     * @throws Exception 
     */
    private function http_get($url, $auth): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        // (start Debug only) Ignore missing certificats
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // (end debug only)
        curl_setopt($ch, CURLOPT_HTTPHEADER, [$auth]);
        $result=curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode >= 400) {
            throw new Exception('Serveur responds with status ' . $httpcode);
        }
        try {
            $result = json_decode($result, true);
        }catch (exception $e) {
            throw new Exception('Invalid data from serveur');
        }
        
        return $result;
    }

}
