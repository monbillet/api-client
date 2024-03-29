<?php

declare(strict_types=1);

namespace Monbillet;

use DateTime;
use Exception;
use UnexpectedValueException;
use Monbillet\ForbiddenException;
use Monbillet\InternalServerException;
use Monbillet\NotFoundException;

/**
 * This class allows you to quickly and easily use the monbillet api
 */
class ApiClient
{
    const HEADER_NAME = 'X-Monbillet-Api-Token';
    const BASE_URL = 'https://monbillet.ch/api/v1/';
    const CACHE_DIR_NAME = 'monbillet-api-client';

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
    private $cacheExpireMinutes;

    /**
     * @param string $api_key
     * @param string|null $cache_path Absolute path (optional)
     * @param int $cache_expire_minutes (optional)
     * @return void
     */
    public function __construct(string $api_key, string $cache_path = null, int $cache_expire_minutes = 10)
    {
        $this->auth = self::HEADER_NAME . ':' . $api_key;
        $this->cachePath = isset($cache_path) ? rtrim($cache_path, '/') . '/' . self::CACHE_DIR_NAME : null;
        $this->cacheExpireMinutes = $cache_expire_minutes;
    }

    /**
     * Get the list of events
     *
     * @return array
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws HttpException
     * @throws InternalServerException
     * @throws Exception
     */
    public function getEvents(): array
    {
        $url = self::BASE_URL . 'events';
        $data = $this->getResource($url);
        return $this->convertDates($data['events']);
    }

    /**
     * Get the list of events order by groups
     *
     * @return array
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws HttpException
     * @throws InternalServerException
     * @throws Exception
     */
    public function getEventGroups(): array
    {
        $url = self::BASE_URL . 'event-groups';
        $data = $this->getResource($url);
        return $this->convertDates($data['event-groups']);
    }

    /**
     * Get the informations about a specific event
     *
     * @param string $event_id The id or unique name of the event
     * @return array
     * @throws UnexpectedValueException
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws HttpException
     * @throws InternalServerException
     * @throws Exception
     */
    public function getEvent(string $event_id): array
    {
        if (empty($event_id)) {
            throw new UnexpectedValueException('Event id must not be empty');
        }
        if (!$this->isValidUniqueNameOrId($event_id)) {
            throw new UnexpectedValueException('Forbidden chars');
        }

        $url = self::BASE_URL . 'events/' .  $event_id;
        $data = $this->getResource($url);

        return $this->convertDates($data['event']);
    }

    /**
     * Get the list of events order by groups
     *
     * @param string $group_id The id or unique name of a group
     * @return array
     * @throws UnexpectedValueException
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws HttpException
     * @throws InternalServerException
     * @throws Exception
     */
    public function getEventGroup(string $group_id): array
    {
        if (empty($group_id)) {
            throw new UnexpectedValueException('Event group id must not be empty');
        }
        if (!$this->isValidUniqueNameOrId($group_id)) {
            throw new UnexpectedValueException('Forbidden chars');
        }

        $url = self::BASE_URL . 'event-groups/' .  $group_id;
        $data = $this->getResource($url);

        return $this->convertDates($data['event-groups']);
    }

    /**
     * Convert date properties to DateTime instances
     *
     * @param array $from
     * @return array
     */
    private function convertDates(array $from): array
    {
        $convert_keys = ['dateFirstShow', 'dateLastShow', 'dateHappens'];
        $out = [];
        foreach ($from as $k => $v) {
            if (is_array($v)) {
                $out[$k] = $this->convertDates($v);
            } else if (in_array($k, $convert_keys, true)) {
                $out[$k] = new DateTime($v);
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    /**
     * Check if the unique name or the id is valid
     *
     * @param string $param
     * @return bool
     */
    private function isValidUniqueNameOrId($param): bool
    {
        return preg_replace('/([^a-z0-9-]+)/', '', $param) === $param;
    }

    /**
     * Delete all cached files
     *
     * @return void
     */
    public function deleteCache()
    {
        if (is_dir($this->cachePath)) {
            self::rrmdir($this->cachePath);
        }
    }

    /**
     * Remove a directory and all its contents
     *
     * @param mixed $dir
     * @return void
     */
    private static function rrmdir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === "." || $object === "..") {
                continue;
            }

            if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object)) {
                self::rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                continue;
            }

            unlink($dir . DIRECTORY_SEPARATOR . $object);
        }

        rmdir($dir);
    }

    /**
     * Get the resource at the given url
     *
     * @param string $url
     * @return array
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws HttpException
     * @throws InternalServerException
     * @throws Exception
     */
    private function getResource(string $url): array
    {
        $data = null;
        $is_from_remote = false;

        if ($this->isCacheEnabled()) {
            $data = $this->getJsonFromCache($url);
        }

        if (!$data) {
            $data = $this->getJsonFromRemote($url);
            $is_from_remote = true;
        }

        $result = json_decode($data, true);

        if ($is_from_remote) {
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error decoding JSON from ' . $url, 500);
            }
            if ($this->isCacheEnabled()) {
                $this->saveJsonInCache($url, $data);
            }
        }

        return $result;
    }

    /**
     * True if the cache is enabled
     *
     * @return bool
     */
    private function isCacheEnabled(): bool
    {
        return isset($this->cachePath);
    }

    /**
     * True if the cache is expired
     *
     * @param string $file
     * @return bool
     */
    private function isCacheExpired(string $file): bool
    {
        return ((filemtime($file) + ($this->cacheExpireMinutes * 60)) < time());
    }

    /**
     * Get the path of a file in cache depending on the url given
     *
     * @param string $url
     * @return string
     */
    private function getFilePathCache($url): string
    {
        return $this->cachePath . substr($url, strlen(self::BASE_URL) - 1);
    }

    /**
     * Get the json from the cache
     *
     * @param string $url
     * @return string|null
     * @throws Exception
     */
    private function getJsonFromCache(string $url): ?string
    {
        $file_path = $this->getFilePathCache($url);
        $cache_path = $file_path . "/cache.json";
        if (file_exists($cache_path) && !$this->isCacheExpired($cache_path)) {
            return file_get_contents($cache_path);
        } else {
            return null;
        }
    }

    /**
     * Get the json from the remote
     *
     * @param string $url
     * @return string
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws HttpException
     * @throws InternalServerException
     */
    private function getJsonFromRemote(string $url): string
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

        return $result;
    }

    /**
     * Save the json in the cache
     *
     * @param string $url
     * @param string $json
     * @return void
     */
    private function saveJsonInCache(string $url, string $json)
    {
        $file_path = $this->getFilePathCache($url);
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        file_put_contents($file_path . '/cache.json', $json);
    }
}
