<?php

declare(strict_types=1);

namespace Monbillet;

use DateTime;
use Exception;
use Monbillet\ForbiddenException;

/**
 * This class allows you to quickly and easily use the monbillet api
 */
class ApiClient
{
    const HEADER_NAME = 'X-Monbillet-Api-Token';
    const BASE_URL = 'https://monbillet.ch/api/v1/';

    /**
     * @var string
     */
    private $auth;

    /**
     * @param string $api_key
     */
    public function __construct(string $api_key)
    {
        $this->auth = self::HEADER_NAME . ':' . $api_key;
    }

    /**
     * Get the list of events
     *
     * @return array
     * @throws Exception
     */
    public function getEvents(): array
    {
        $url = self::BASE_URL . 'events';
        $data = $this->getJson($url);
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
        $url = self::BASE_URL . 'event-groups';
        $data = $this->getJson($url);

        return array_map(function ($event_group) {
            return array_merge(
                $event_group,
                array_map([$this, 'convertDates'], $event_group['events'])
            );
        }, $data['event-groups']);
    }

    /**
     * Get the informations about a specific event
     *
     * @param string $event_id The id or unique name of the event
     * @return array
     */
    public function getEvent(string $event_id): array
    {
        $url = self::BASE_URL . 'events/' .  $event_id;

        $data = $this->getJson($url);

        return $data['event'];
    }

    /**
     * Convert date properties to DateTime instances
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
     * Get the resource at the given url, and try to convert it to JSON
     *
     * @param string $url
     * @return array
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws HttpException
     * @throws InternalServerException
     * @throws Exception
     */
    private function getJson(string $url): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [$this->auth]
        ]);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpcode === 403) {
            throw new ForbiddenException('Access to resource ' . $url . ' is forbidden.');
        }

        if ($httpcode === 404) {
            throw new NotFoundException('Resource ' . $url . ' not found.');
        }
    
        if ($httpcode >= 400 && $httpcode < 500) {
            throw new HttpException('Error trying to access resource ' . $url . '. The server responded with ' . $httpcode);
        }

        if ($httpcode >= 500) {
            throw new InternalServerException('Server error ' . $httpcode . ' trying to access resource ' . $url);
        }

        $result = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error decoding JSON');
        }
        
        return $result;
    }
}
