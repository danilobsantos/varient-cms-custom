<?php

namespace Config;

use App\Services\AppContextService;
use App\Services\AuthService;
use App\Services\CacheService;
use App\Services\DataService;
use App\Services\PostService;
use App\Services\StorageService;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * AppContext Service
     *
     * This service acts as a global state manager. It loads and holds
     * application-wide settings and configuration (like $this->config)
     * that other services and controllers might need.
     *
     * @param bool $getShared
     * @return AppContextService
     */
    public static function appContext(bool $getShared = true): AppContextService
    {
        if ($getShared) {
            // Return the same instance every time (Singleton pattern)
            return static::getSharedInstance('appContext');
        }

        return new AppContextService();
    }

    /**
     * Auth Service
     *
     * @param bool $getShared
     * @return AuthService
     */
    public static function authService(bool $getShared = true): AuthService
    {
        if ($getShared) {
            return static::getSharedInstance('authService');
        }

        return new AuthService();
    }

    /**
     * Storage Service
     *
     * This service abstracts file system operations. It provides a single
     * interface for handling storage, whether it's on the 'local' disk
     * or a cloud provider like 's3'.
     *
     * @param bool $getShared
     * @return StorageService
     */
    public static function storage(bool $getShared = true): StorageService
    {
        if ($getShared) {
            return static::getSharedInstance('storage');
        }

        $config = service('appContext')->config;

        $storageSettings = (object)($config->storage_settings ?? []);
        $activeStorage = $config->active_storage ?? 'local';

        return new StorageService($storageSettings, $activeStorage);
    }

    /**
     * Cache Service (The "Cache Worker")
     *
     * This service is the core caching engine. It wraps the low-level
     * CI4 cache driver and provides our standardized get(), save(), delete()
     * methods. It doesn't know *what* it's caching, only *how*.
     *
     * @param bool $getShared
     * @return CacheService
     */
    public static function cacheService(bool $getShared = true): CacheService
    {
        if ($getShared) {
            return static::getSharedInstance('cacheService');
        }

        $config = service('appContext')->config;

        return new CacheService($config);
    }

    /**
     * Data Service (The "Orchestra Conductor" / Repository)
     *
     * This is the main data access layer for the application.
     * It holds all business logic (e.g., getLatestPosts()).
     * It coordinates both the CacheService and the Models to return data.
     *
     * @param bool $getShared
     * @return DataService
     */
    public static function dataService(bool $getShared = true): DataService
    {
        if ($getShared) {
            return static::getSharedInstance('dataService');
        }

        $cacheService = service('cacheService');

        return new DataService($cacheService);
    }

    /**
     * Post Service
     *
     * This is the main data access layer for posts.
     *
     * @param bool $getShared
     * @return PostService
     */
    public static function postService(bool $getShared = true): PostService
    {
        if ($getShared) {
            return static::getSharedInstance('postService');
        }

        $cacheService = service('cacheService');

        return new PostService($cacheService);
    }
}