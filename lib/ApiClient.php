<?php

declare(strict_types=1);

namespace Monbillet;

use DateTime;
use Exception;
use Monbillet\ForbiddenException;
use UnexpectedValueException;

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
     * @var string
     */
    private $cachePath;

    /**
     * @var int
     */
    private $cacheMinutes;
    
    /**
     * @param string $api_key 
     * @param string|null $cachePath absolute path (optional)
     * @param int $minutes_cache (optional)
     * @return void 
     */
    public function __construct(string $api_key, string $cachePath=null, int $cacheMinutes=10)
    {
        $this->auth = self::HEADER_NAME . ':' . $api_key;
        $this->cachePath = $cachePath;
        $this->cacheMinutes = $cacheMinutes;
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
        return array_map([$this, 'convertDatesEvent'], $data['events']);
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
                array_map([$this, 'convertDatesEvent'], $event_group['events'])
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
        if (empty($event_id)) {
            throw new UnexpectedValueException('Event id must not be empty');
        }

        $url = self::BASE_URL . 'events/' .  $event_id;
        $data = $this->getJson($url);

        $data = $this->convertDatesEvent($data['event']);
        $data['shows'] = array_map([$this, 'convertDateShow'], $data['shows']);

        return $data;
    }

    /**
     * Get the list of events order by groups
     *
     * @return array
     * @throws Exception
     */
    public function getEventGroup(string $group_id): array
    {
        if (empty($group_id)) {
            throw new UnexpectedValueException('event id must not be empty');
        }

        $url = self::BASE_URL . 'event-groups/' .  $group_id;
        $data = $this->getJson($url);

        $data['event-groups'] = array_merge(
            $data['event-groups'],
            array_map([$this, 'convertDatesEvent'], $data['event-groups']['events'])
        );

        return $data;
    }

    /** 
     * Delete all cached files
     * 
     * @return void  
     */
    public function deleteCache() {
        if (is_dir($this->cachePath)) {
            $this->deleteDirectory($this->cachePath);
        }
    }

    /**
     * Remove a directory and all its contents
     * 
     * @param mixed $dir 
     * @return bool 
     */
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }

    /**
     * Convert date properties to DateTime instances
     *
     * @param mixed $event
     * @return array
     */
    private function convertDatesEvent($event): array
    {
        return array_merge($event, [
            'dateFirstShow' => new DateTime($event['dateFirstShow']),
            'dateLastShow' => new DateTime($event['dateLastShow']),
        ]);
    }

    /**
     * Convert date properties to DateTime instances
     *
     * @param mixed $show
     * @return array
     */
    private function convertDateShow($show): array
    {
        return array_merge($show, [
            'dateHappens' => new DateTime($show['dateHappens']),
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
        if (isset($this->cachePath)) {
            $file_path = $this->cachePath . substr($url, strlen(self::BASE_URL) -1);    
            $cache_path = $file_path . "/cache.json";
            if ( file_exists($cache_path) && ( filemtime($cache_path) + ($this->cacheMinutes*60) > time()) ){
                $cache = file_get_contents($cache_path);
                $cache_array = json_decode($cache, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Error decoding JSON', 500);
                }
                return $cache_array;
            }
        }

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
            throw new ForbiddenException('Access to resource ' . $url . ' is forbidden.', 403);
        }

        if ($httpcode === 404) {
            throw new NotFoundException('Resource ' . $url . ' not found.', 404);
        }
    
        if ($httpcode >= 400 && $httpcode < 500) {
            throw new HttpException('Error trying to access resource ' . $url, $httpcode);
        }

        if ($httpcode >= 500) {
            throw new InternalServerException('Server error trying to access resource ' . $url, $httpcode);
        }

        $result_array = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error decoding JSON', 500);
        }

        if (isset($this->cachePath)) {
            $file_path = $this->cachePath . substr($url, strlen(self::BASE_URL) -1);    
            if (!is_dir($file_path)) {
                mkdir($file_path, 0777, true);
            }
            file_put_contents ($file_path.'/cache.json' , $result);
        }
        
        return $result_array;

    }
}
