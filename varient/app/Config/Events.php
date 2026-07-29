<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function () {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            return true;
            //throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        // Handle Iyzico Callback
        if (strpos($uri, '/checkout/payment/iyzico') !== false) {
            $token = $_POST['token'] ?? null;

            if (!empty($token)) {
                $query = $_GET; // Preserve existing GET variables like order_token
                $query['token'] = $token;

                $parsedUrl = parse_url($uri);
                $redirectUrl = $parsedUrl['path'] . '?' . http_build_query($query);

                // 303 See Other is the strict HTTP standard for POST-to-GET redirects
                header("Location: " . $redirectUrl, true, 303);
                exit();
            }
        }

        // Handle PayTabs Callback
        if (strpos($uri, '/checkout/payment/paytabs') !== false) {
            if (!empty($_POST)) {
                $query = $_GET;
                $query['post_data'] = base64_encode(json_encode($_POST));

                $parsedUrl = parse_url($uri);
                $redirectUrl = $parsedUrl['path'] . '?' . http_build_query($query);

                header("Location: " . $redirectUrl, true, 303);
                exit();
            }
        }
    }

});
