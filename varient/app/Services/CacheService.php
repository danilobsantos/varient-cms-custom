<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Cache\CacheInterface;
use Config\Services;
use Throwable;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

/**
 * Class CacheService
 *
 * An enterprise-grade caching layer optimized for safety and performance.
 * Implements a "Soft Lock" (Cache Mutex) to prevent Cache Stampede (Dogpiling)
 * and utilizes a Payload Wrapper to safely handle native null/boolean values.
 */
class CacheService
{
    /**
     * Cache Time-To-Live (TTL) Map
     * Defines standard expiration times in seconds. Keys act as cache file prefixes
     */
    private const TTL_MAP = [
        'app_long'   => 86400,  // 24 Hours
        'app_medium' => 10800,  // 3 Hours
        'app_short'  => 600,    // 10 Minutes
        'app_micro'  => 60,     // 1 Minute
        'content'    => null,   // Dynamic TTL
    ];

    /** @var CacheInterface Core CodeIgniter cache handler */
    protected CacheInterface $cache;

    /** @var object|null Optional application-level config object */
    protected ?object $config;

    /**
     * CacheService Constructor
     *
     * @param object|null $config Configuration object providing caching settings
     */
    public function __construct(?object $config = null)
    {
        $this->cache = Services::cache();
        $this->config = $config;
    }

    /**
     * Retrieves a cached value or securely executes the given callback
     * Implements a safe Cache Mutex (Soft Lock) to mitigate database spikes
     *
     * @param string $key Base key (without prefix).
     * @param callable $callback Callback to execute and cache if not found.
     * @param string $type Cache type defined in TTL_MAP.
     *
     * @return mixed The cached or freshly computed data.
     */
    public function get(string $key, callable $callback, string $type = 'app_medium'): mixed
    {
        if (!$this->isCacheActive($type)) {
            return $callback();
        }

        $cacheKey = $this->buildKey($key, $type);
        $ttl = $this->determineTtl($type);

        // Try cache first (Fast Path with Payload Wrapper)
        if ($cached = $this->getRaw($cacheKey)) {
            return $cached['payload'];
        }

        // Stampede Protection (Soft Mutex Lock)
        $lockKey = $cacheKey . '_mutex';
        $startTime = microtime(true);
        $maxWait = 2.0; // Maximum wait time in seconds to prevent process hanging

        // Wait loop if another process is currently generating the data
        while ($this->cache->get($lockKey) && (microtime(true) - $startTime) < $maxWait) {
            usleep(200000); // Sleep for 200ms

            // Double-check if the cache was populated during our wait
            if ($cached = $this->getRaw($cacheKey)) {
                return $cached['payload'];
            }
        }

        // Attempt to acquire the soft lock (expires in 10s to prevent permanent deadlocks)
        $lockAcquired = $this->cache->save($lockKey, true, 10);

        try {
            // Execute the heavy callback and generate data
            $data = $callback();

            // Save safely using the Payload Wrapper
            $this->saveRaw($cacheKey, $data, $ttl);

            return $data;
        } finally {
            // Guarantee lock release, but only if this process actually acquired it
            if ($lockAcquired) {
                $this->cache->delete($lockKey);
            }
        }
    }

    /**
     * Saves data directly into the cache using the Payload Wrapper
     *
     * @param string $key Base cache key
     * @param mixed $data Data to cache
     * @param string $type Cache type
     * @param int|null $ttl Optional custom TTL
     *
     * @return bool True on success, false on failure
     */
    public function save(string $key, mixed $data, string $type = 'app_medium', ?int $ttl = null): bool
    {
        if (!$this->isCacheActive($type)) {
            return false;
        }

        $cacheKey = $this->buildKey($key, $type);
        $finalTtl = $ttl ?? $this->determineTtl($type);

        return $this->saveRaw($cacheKey, $data, $finalTtl);
    }

    /**
     * Deletes a specific item from the cache
     *
     * @param string $key The base key
     * @param string $type The cache type
     *
     * @return bool True on success, false on failure
     */
    public function delete(string $key, string $type = 'app_medium'): bool
    {
        try {
            return $this->cache->delete($this->buildKey($key, $type));
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Clears cached entries by group prefix directly from the file system
     * Supports broad clearing ('app', 'content', 'all') or specific map keys ('app_short')
     *
     * @param string $group Target group to clear
     *
     * @return bool True on success, false on failure
     */
    public function reset(string $group): bool
    {
        $cachePath = WRITEPATH . 'cache/';

        if (!is_dir($cachePath)) {
            return false;
        }

        if ($group === 'all') {
            $targetPrefix = '*';
        } elseif ($group === 'app' || $group === 'content') {
            $targetPrefix = $group . '_';
        } elseif (array_key_exists($group, self::TTL_MAP)) {
            $targetPrefix = $group . '_';
        } else {
            return false;
        }

        try {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cachePath, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $fileName = $file->getFilename();

                // Protect core CodeIgniter system files
                if ($fileName === 'index.html' || $fileName === '.htaccess') {
                    continue;
                }

                if ($targetPrefix === '*' || str_starts_with($fileName, $targetPrefix)) {
                    @unlink($file->getRealPath());
                }
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Clears all cache files specifically prefixed for dynamic content
     */
    public function clearContentCache(): void
    {
        $autoRefresh = (bool)($this->config->auto_cache_refresh ?? false);

        if ($autoRefresh) {
            $this->reset('content');
        }
    }

    /**
     * Safely reads the payload wrapper to properly handle native null returns
     *
     * @param string $cacheKey The fully qualified cache key
     * @return array|null The wrapped data array on hit, null on miss
     */
    protected function getRaw(string $cacheKey): ?array
    {
        try {
            $cached = $this->cache->get($cacheKey);

            if (is_array($cached) && array_key_exists('payload', $cached)) {
                return $cached;
            }
        } catch (Throwable $e) {
        }

        return null;
    }

    /**
     * Safely writes the payload wrapper to the cache engine
     *
     * @param string $cacheKey The fully qualified cache key
     * @param mixed $data The data to cache
     * @param int $ttl Time-to-live in seconds
     * @return bool True on success, false on failure
     */
    protected function saveRaw(string $cacheKey, mixed $data, int $ttl): bool
    {
        try {
            return $this->cache->save($cacheKey, ['payload' => $data], $ttl);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Checks if a specific cache type is active based on the configuration
     *
     * @param string $type The cache type
     * @return bool
     */
    protected function isCacheActive(string $type): bool
    {
        if ($type === 'content') {
            return (bool)($this->config?->content_cache_system ?? false);
        }

        if (str_starts_with($type, 'app_')) {
            return (bool)($this->config?->app_cache_system ?? false);
        }

        return false;
    }

    /**
     * Determines the correct TTL based on the type map and configuration
     *
     * @param string $type The cache type
     * @return int The TTL in seconds
     */
    protected function determineTtl(string $type): int
    {
        if ($type === 'content') {
            $ttl = $this->config?->content_cache_ttl ?? 10800;
            return is_numeric($ttl) ? (int)$ttl : 10800;
        }

        return self::TTL_MAP[$type] ?? 10800;
    }

    /**
     * Builds a sanitized, fully-qualified cache key
     *
     * @param string $key The raw key
     * @param string $type The cache type
     * @return string The formatted cache key
     */
    protected function buildKey(string $key, string $type): string
    {
        $safeKey = preg_replace('/[^A-Za-z0-9_\-:]/', '_', $key);

        return $type . '_' . ltrim((string)$safeKey, '_');
    }

    /**
     * Retrieves the current cache buster version
     *
     * @return string
     */
    public function getBrowserCacheVersion(): string
    {
        // Use the global constant defined in Constants.php
        if (!BROWSER_CACHE) {
            return 'disabled';
        }

        $version = $this->cache->get(BROWSER_CACHE_VERSION_KEY);

        if (!$version) {
            $version = (string)time();
            $this->cache->save(BROWSER_CACHE_VERSION_KEY, $version, 31536000);
        }

        return $version;
    }

    /**
     * Refreshes the browser cache buster version
     *
     * @return void
     */
    public function refreshBrowserCacheVersion(): void
    {
        if (!BROWSER_CACHE) {
            return;
        }

        $newVersion = (string)time();
        $this->cache->save(BROWSER_CACHE_VERSION_KEY, $newVersion, 31536000);
    }
}