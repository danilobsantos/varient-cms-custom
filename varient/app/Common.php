<?php

if (!function_exists('getContextValue')) {
    /**
     * Fetch a value from the AppContext service
     */
    function getContextValue(string $key, mixed $default = null): mixed
    {
        $ctx = service('appContext');

        // Service may be null or invalid
        if (!$ctx || !is_object($ctx)) {
            return $default;
        }

        // Property may not exist or be null
        return $ctx->{$key} ?? $default;
    }
}

if (!function_exists('getAppDefault')) {
    /**
     * Fetch a default configuration value from \Config\Defaults
     */
    function getAppDefault(string $key): mixed
    {
        $class = \Config\Defaults::class;

        // Ensure the class actually exists
        if (!class_exists($class)) {
            return [];
        }

        // Check for static property existence
        return property_exists($class, $key) ? $class::$$key : [];
    }
}

if (!function_exists('getSocialPlatforms')) {
    /**
     * Fetch the social media platforms configuration
     */
    function getSocialPlatforms(): array
    {
        $class = \Config\SocialPlatforms::class;

        // Ensure the class and the static property exist
        if (class_exists($class) && property_exists($class, 'socialPlatforms')) {
            return $class::$socialPlatforms;
        }

        // Return an empty array as a fallback
        return [];
    }
}

if (!function_exists('getCurrencies')) {
    /**
     * Fetch the currencies configuration
     */
    function getCurrencies(): array
    {
        $class = \Config\Currencies::class;

        // Ensure the class and the static property exist
        if (class_exists($class) && property_exists($class, 'currencies')) {
            return $class::$currencies;
        }

        // Return an empty array as a fallback
        return [];
    }
}

if (!function_exists('getDefaultCurrency')) {
    /**
     * Get default currency as an object with DB override support
     */
    function getDefaultCurrency(): object
    {
        $currencies = getCurrencies();

        $settings = getContextValue('config')->currency_settings ?? (object)[];

        $code = $settings->code ?? null;

        if (!$code || !isset($currencies[$code])) {
            $code = $currencies ? array_key_first($currencies) : 'USD';
        }

        $standard = $currencies[$code] ?? [];

        return (object)[
            'code'         => $code,
            'symbol'       => $settings->symbol ?? ($standard['symbol'] ?? '$'),
            'direction'    => $settings->symbol_direction ?? ($standard['direction'] ?? 'left'),
            'thousand_sep' => $settings->thousand_separator ?? ($standard['thousand_sep'] ?? ','),
            'decimal_sep'  => $settings->decimal_separator ?? ($standard['decimal_sep'] ?? '.'),
        ];
    }
}

if (!function_exists('escMeta')) {
    /**
     * Escapes a string for HTML meta tags
     *
     * @param mixed $str Input string
     * @return string Escaped string
     */
    function escMeta(mixed $str): string
    {
        if (empty($str) || !is_string($str)) {
            return '';
        }

        return esc($str, 'html');
    }
}

if (!function_exists('trans')) {
    /**
     * Retrieve the translated value with flexible escaping options
     *
     * Usage examples:
     * - trans('key')          -> Returns raw string (no escaping).
     * - trans('key', true)    -> Returns standard escaped string (HTML body).
     * - trans('key', 'attr')  -> Returns escaped string for HTML attributes.
     * - trans('key', 'js')    -> Returns escaped string for JavaScript contexts.
     *
     * @param string $key Translation key.
     * @param string|bool $mode Escaping mode.
     * @return string The processed translation string.
     */
    function trans(string $key, string|bool $mode = false): string
    {
        // Retrieve the translation from the context
        $translations = getContextValue('languageTranslations') ?? [];
        $value = $translations[$key] ?? '';

        // Return the value based on the requested mode
        return match ($mode) {
            true => esc($value),
            'attr' => esc($value, 'attr'),
            'js' => esc($value, 'js'),
            default => $value,
        };
    }
}

if (!function_exists('characterLimiter')) {
    /**
     * Limit a string by character length (UTF-8 safe)
     */
    function characterLimiter(?string $str = '', int $limit = 200, string $endChar = ''): string
    {
        $str = $str ?? '';

        if ($limit <= 0) {
            return $str;
        }

        if (mb_strlen($str, 'UTF-8') <= $limit) {
            return $str;
        }

        return mb_strimwidth($str, 0, $limit, $endChar, 'UTF-8');
    }
}

if (!function_exists('getDisplayTitle')) {
    /**
     * Truncates the post title to a standard or specified length
     *
     * @param string $title The title text to be truncated
     * @param int|null $limit Optional custom limit. If null, uses the global constant
     * * @return string
     */
    function getDisplayTitle(string $title, ?int $limit = null): string
    {
        $effectiveLimit = $limit ?? (defined('POST_DISPLAY_TITLE_LIMIT') ? POST_DISPLAY_TITLE_LIMIT : 50);

        return character_limiter($title, $effectiveLimit, '...');
    }
}

if (!function_exists('setSuccessMessage')) {
    /**
     * Set a success message to session flashdata
     */
    function setSuccessMessage($message, bool $trans = true): void
    {
        if (empty($message)) {
            return;
        }

        // Ensure message is a string
        if (!is_string($message)) {
            $message = (string)$message;
        }

        // Translate if enabled
        if ($trans) {
            $translated = trans($message);
            if (!empty($translated)) {
                $message = $translated;
            }
        }

        service('session')->setFlashdata('success', $message);
    }
}

if (!function_exists('setErrorMessage')) {
    /**
     * Set an error message to session flashdata
     */
    function setErrorMessage($message, bool $trans = true): void
    {
        if (empty($message)) {
            return;
        }

        // Ensure the message is a string
        if (!is_string($message)) {
            $message = (string)$message;
        }

        // Translate if enabled
        if ($trans) {
            $translated = trans($message);
            if (!empty($translated)) {
                $message = $translated;
            }
        }

        service('session')->setFlashdata('error', $message);
    }
}

if (!function_exists('clearFlashdata')) {
    /**
     * Clear all flashdata from the current session
     *
     * @return void
     */
    function clearFlashdata(): void
    {
        $session = session();

        $flashData = $session->getFlashdata();

        if (empty($flashData)) {
            return;
        }

        foreach (array_keys($flashData) as $key) {
            $session->remove($key);
        }
    }
}

if (!function_exists('getThemePath')) {
    /**
     * Returns active theme directory path
     */
    function getThemePath(): string
    {
        $activeTheme = getContextValue('activeTheme');

        if (empty($activeTheme) || empty($activeTheme->theme_folder)) {
            return 'themes/classic';
        }

        return 'themes/' . $activeTheme->theme_folder;
    }
}

if (!function_exists('loadView')) {
    /**
     * Load a view from the active theme
     *
     * @param string $view
     * @param array|null $data
     * @return string
     */
    function loadView(string $view, ?array $data = null): string
    {
        $themePath = getThemePath();
        if (!is_string($themePath) || trim($themePath) === '') {
            $themePath = 'themes/classic';
        }

        // Normalize both parts to avoid double slashes
        $themePath = rtrim($themePath, '/');
        $view = ltrim($view, '/');

        $path = $themePath . '/' . $view;

        return view($path, $data ?? []);
    }
}

if (!function_exists('loadCommonView')) {
    /**
     * Load a common view
     *
     * @param string $view
     * @param array|null $data
     * @return string
     */
    function loadCommonView(string $view, ?array $data = null): string
    {
        $commonPath = 'themes/common';

        $view = ltrim($view, '/');

        $path = $commonPath . '/' . $view;

        return view($path, $data ?? []);
    }
}

if (!function_exists('langBaseUrl')) {
    /**
     * Returns the base URL for the active language
     *
     * @param string|null $route Optional route to append to the base URL
     * @return string Normalized language-based URL
     */
    function langBaseUrl(?string $route = null): string
    {
        $base = rtrim(getContextValue('langBaseUrl') ?: base_url(), '/');

        if ($route !== null) {
            return $base . '/' . ltrim($route, '/');
        }

        return $base . '/';
    }
}

if (!function_exists('generateBaseURLByLangId')) {
    /**
     * Generates a language-based base URL using a language ID
     *
     * @param int $langId Target language ID
     * @return string      Normalized base URL with language short form
     */
    function generateBaseURLByLangId(int $langId): string
    {
        $config = getContextValue('config');
        $languages = getContextValue('languages');

        if (empty($config) || empty($languages)) {
            return base_url();
        }

        if ($langId === (int)$config->site_lang) {
            return rtrim(base_url(), '/') . '/';
        }

        $shortForm = null;
        foreach ($languages as $language) {
            if ((int)$language->id === $langId) {
                $shortForm = trim($language->short_form ?? '');
                break;
            }
        }

        // If found, return language-based URL
        if (!empty($shortForm)) {
            return rtrim(base_url(), '/') . '/' . $shortForm . '/';
        }

        return rtrim(base_url(), '/') . '/';
    }
}

if (!function_exists('generateBaseURLByLang')) {
    /**
     * Generates a base URL for the given language object
     *
     * @param object|null $lang Language object (must contain id & short_form)
     * @return string Normalized base URL
     */
    function generateBaseURLByLang(?object $lang): string
    {
        $config = getContextValue('config');

        if ($lang === null || empty($config)) {
            return rtrim(base_url(), '/') . '/';
        }

        if ((int)$lang->id === (int)$config->site_lang) {
            return rtrim(base_url(), '/') . '/';
        }

        $short = $lang->short_form ?? '';
        if ($short === '') {
            return rtrim(base_url(), '/') . '/';
        }

        return rtrim(base_url(), '/') . '/' . trim($short, '/') . '/';
    }
}

if (!function_exists('getBackUrl')) {
    /**
     * Returns a safe "back" URL by validating POSTed URL or HTTP referrer
     *
     * @return string Safe back URL or fallback base_url()
     */
    function getBackUrl(): string
    {
        $appHost = parse_url(base_url(), PHP_URL_HOST);

        // Check POST-provided back URL
        $postedUrl = request()->getPost('frm_back_url');

        if (!empty($postedUrl)) {
            $postedHost = parse_url($postedUrl, PHP_URL_HOST);

            // Allow only local/internal URLs
            if ($postedHost === null || $postedHost === $appHost) {
                return $postedUrl;
            }
        }

        // Check HTTP referrer
        $referrer = request()->getServer('HTTP_REFERER') ?? '';

        if (!empty($referrer)) {
            $referrerHost = parse_url($referrer, PHP_URL_HOST);

            if ($referrerHost !== null && $referrerHost === $appHost) {
                return $referrer;
            }
        }

        return rtrim(base_url(), '/') . '/';
    }
}

if (!function_exists('buildCanonicalUrl')) {
    /**
     * Builds a normalized canonical URL
     *
     * Handles:
     * - Port support for local/staging environments
     * - Enforcing a trailing slash for the homepage
     * - Stripping the trailing slash for inner pages
     * - Removing tracking query parameters via whitelist
     * - Preserving valid pagination (?page=2)
     * - RFC3986 compliant query string encoding
     *
     * @param string $url
     * @return string
     */
    function buildCanonicalUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $parts = parse_url($url);

        if (!isset($parts['host'])) {
            return '';
        }

        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'];
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';

        // Normalize Path
        $path = trim($path);

        if ($path === '' || $path === '/') {
            $path = '/';
        } else {
            $path = '/' . trim($path, '/');
        }

        // Build Base Canonical URL
        $canonical = $scheme . '://' . $host . $port . $path;

        // Query Parameter Handling
        $request = \Config\Services::request();

        // Cast to array for safety against unexpected getGet() returns
        $queryParams = (array)$request->getGet();

        if (!empty($queryParams)) {
            $allowedParams = ['page'];
            $filtered = [];

            foreach ($queryParams as $key => $value) {
                if (!in_array($key, $allowedParams, true)) {
                    continue;
                }

                if ($key === 'page') {
                    $page = (int)$value;

                    if ($page > 1) {
                        $filtered['page'] = $page;
                    }
                }
            }

            if (!empty($filtered)) {
                // Use RFC3986 for modern encoding
                $canonical .= '?' . http_build_query($filtered, '', '&', PHP_QUERY_RFC3986);
            }
        }

        return $canonical;
    }
}

if (!function_exists('getAdminRoute')) {
    /**
     * Returns configured admin route slug
     *
     * @return string
     */
    function getAdminRoute(): string
    {
        $customRoutes = getContextValue('customRoutes');

        $adminSlug = $customRoutes->admin ?? 'admin';

        return trim($adminSlug, '/');
    }
}

if (!function_exists('adminUrl')) {
    /**
     * Generates a normalized admin URL using configured custom admin route
     *
     * @param string|null $route Optional sub-route to append
     * @return string Fully qualified admin URL
     */
    function adminUrl(?string $route = null): string
    {
        $base = rtrim(base_url(), '/');
        $slug = getAdminRoute();

        if ($route !== null) {
            $route = ltrim($route, '/');
            return "{$base}/{$slug}/{$route}";
        }

        return "{$base}/{$slug}/";
    }
}

if (!function_exists('getMaxUploadLimit')) {
    /**
     * Retrieves the file upload limit (in MB) from the cached configuration
     *
     * @param string $type 'image', 'video', 'audio', or 'file'
     * @return int The limit in MB. Returns 0 if not found.
     */
    function getMaxUploadLimit(string $type): int
    {
        $config = getContextValue('config');
        if (empty($config) || empty($config->file_upload_limits)) {
            return 5;
        }

        $limits = $config->file_upload_limits;

        if (isset($limits->$type)) {
            return (int)$limits->$type;
        }

        return 0;
    }
}

if (!function_exists('inputPost')) {
    /**
     * Retrieves POST data. Trims if string, returns raw otherwise
     *
     * @param string $name The name of the input field
     * @param mixed $default The default value if input is missing
     * @return mixed
     */
    function inputPost(string $name, mixed $default = null): mixed
    {
        $input = service('request')->getPost($name);

        if ($input === null) {
            return $default;
        }

        // Trim ONLY if it is a string
        if (is_string($input)) {
            return trim($input);
        }

        return $input;
    }
}

if (!function_exists('inputGet')) {
    /**
     * Retrieves GET parameter. Trims if string, returns raw otherwise
     *
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter is missing
     * @return mixed
     */
    function inputGet(string $name, mixed $default = null): mixed
    {
        $input = service('request')->getGet($name);

        if ($input === null) {
            return $default;
        }

        // Trim ONLY if it is a string
        if (is_string($input)) {
            return trim($input);
        }

        return $input;
    }
}

if (!function_exists('isPostMethod')) {
    /**
     * Checks if the current HTTP request method is POST
     *
     * @return bool
     */
    function isPostMethod(): bool
    {
        return strtolower(request()->getMethod()) === 'post';
    }
}

if (!function_exists('authCheck')) {
    /**
     * Returns true if the user is authenticated
     *
     * @return bool
     */
    function authCheck(): bool
    {
        return (bool)getContextValue('authCheck');
    }
}

if (!function_exists('user')) {
    /**
     * Returns the authenticated user object or null
     *
     * @return object|null
     */
    function user(): ?object
    {
        return getContextValue('authUser');

        return is_object($user) ? $user : null;
    }
}

if (!function_exists('getUserById')) {
    /**
     * Retrieves a user object by their unique identifier
     *
     * @param int $id The unique ID of the user
     * @return object|null Returns the user object if found, or null if not found
     */
    function getUserById(?int $id = null): ?object
    {
        if (!$id) return null;

        return model('UserModel')->getUserWithRole($id);
    }
}

if (!function_exists('getUserSessionKey')) {
    /**
     * Generates a secure session key for the given user object
     *
     * @param object|null $user
     * @return string|null
     */
    function getUserSessionKey(?object $user): ?string
    {
        if (empty($user) || !isset($user->password, $user->id)) {
            return null;
        }

        return hash('sha256', $user->password . $user->id);
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Check if the authenticated user is a super admin
     */
    function isSuperAdmin(): bool
    {
        if (!authCheck()) {
            return false;
        }

        $u = user();

        return !empty($u) && !empty($u->is_super_admin);
    }
}

if (!function_exists('isAdmin')) {
    /**
     * Determines whether the currently authenticated user has admin access
     *
     * @return bool Returns true if the user is authenticated and has admin access
     */
    function isAdmin(): bool
    {
        if (!authCheck()) {
            return false;
        }

        $user = user();
        if (!$user) {
            return false;
        }

        // Super admin always passes
        if (!empty($user->is_super_admin)) {
            return true;
        }

        // Required permissions for admin access
        $required = ['add_post', 'admin_panel'];

        // User permissions missing or empty
        if (empty($user->permissions)) {
            return false;
        }

        // Convert CSV permissions into array
        $userPerms = array_filter(array_map('trim', explode(',', $user->permissions)));

        // Check intersection between user's permissions and required permissions
        foreach ($required as $perm) {
            if (in_array($perm, $userPerms, true)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('getLanguageClient')) {
    /**
     * Retrieve a language object by its ID from the application context
     *
     * @param int $id Language ID to search for
     * @return object|null Returns the language object or null if not found
     */
    function getLanguageClient(int $id): ?object
    {
        static $cache = [];

        if (array_key_exists($id, $cache)) {
            return $cache[$id];
        }

        $languages = getContextValue('languages') ?? [];

        foreach ($languages as $lang) {
            if (!empty($lang) && isset($lang->id) && (int)$lang->id === $id) {
                $cache[$id] = $lang;
                return $lang;
            }
        }

        $cache[$id] = null;
        return null;
    }
}

if (!function_exists('getAppIcon')) {
    /**
     * Retrieve the application's icon URL for a given size
     *
     * @param string|int $size The desired icon size (default: '512')
     * @return string Returns the full URL to the icon, or a default icon if not found
     */
    function getAppIcon(string|int $size = '512'): string
    {
        $config = getContextValue('config');
        $size = (string)$size;

        if (!empty($config->app_icon)) {
            $key = 'icon_' . $size;

            if (!empty($config->app_icon->$key)) {
                $path = $config->app_icon->$key;
                if (file_exists($path)) {
                    return base_url($path);
                }
            }
        }

        return base_url("assets/media/app_icon.png");
    }
}

if (!function_exists('getLogo')) {
    /**
     * Retrieves the logo URL using Object syntax
     *
     * @param string $mode 'light' (default) or 'dark'
     * @param string $format 'original' (default) or 'png'
     * @return string Full URL of the logo or the default asset
     */
    function getLogo(string $mode = 'light', string $format = 'original'): string
    {
        $config = getContextValue('config');
        $logoObj = $config->logo ?? null;

        // Determine DB Property
        if ($mode === 'dark') {
            $targetProp = ($format === 'png') ? 'logo_dark_png' : 'logo_dark';
        } else {
            $targetProp = ($format === 'png') ? 'logo_png' : 'logo';
        }

        $dbPath = $logoObj->{$targetProp} ?? null;

        // Fallbacks for PNG
        if (empty($dbPath) && $format === 'png') {
            $fallbackProp = ($mode === 'dark') ? 'logo_dark' : 'logo';
            $dbPath = $logoObj->{$fallbackProp} ?? null;
        }

        // Fallbacks for Dark mode to Light mode
        if (empty($dbPath) && $mode === 'dark') {
            $lightProp = ($format === 'png') ? 'logo_png' : 'logo';
            $dbPath = $logoObj->{$lightProp} ?? null;

            if (empty($dbPath) && $format === 'png') {
                $dbPath = $logoObj->logo ?? null;
            }
        }

        $dbPath = $dbPath ? ltrim($dbPath, '/') : null;

        if ($dbPath && is_file(FCPATH . $dbPath)) {
            return base_url($dbPath);
        }

        // Dynamically append '_dark' suffix if mode is dark
        $suffix = ($mode === 'dark') ? '_dark' : '';
        $ext = ($format === 'png') ? 'png' : 'svg';

        $defaultAsset = "assets/media/logo{$suffix}.{$ext}";

        // Fallback to SVG if the requested PNG does not exist in the folder
        if ($format === 'png' && !is_file(FCPATH . $defaultAsset)) {
            $defaultAsset = "assets/media/logo{$suffix}.svg";
        }

        if (!is_file(FCPATH . $defaultAsset)) {
            $defaultAsset = 'assets/media/logo.svg';
        }

        return base_url($defaultAsset);
    }
}

if (!function_exists('getLogoSize')) {
    /**
     * Retrieves logo dimensions from the object configuration
     *
     * @return object Returns object with width/height
     */
    function getLogoSize(): object
    {
        // Default values
        $width = 180;
        $height = 60;

        $config = getContextValue('config');
        $logoObj = $config->logo ?? null;

        if (!empty($logoObj->width) && !empty($logoObj->height)) {

            $w = (int)$logoObj->width;
            $h = (int)$logoObj->height;

            // Validate range
            if ($w >= 10 && $w <= 300) $width = $w;
            if ($h >= 10 && $h <= 300) $height = $h;
        }

        return (object)[
            'width'  => $width,
            'height' => $height,
            'string' => "{$width}x{$height}",
        ];
    }
}

if (!function_exists('getUserAvatar')) {
    /**
     * Get the full URL of a user's avatar
     *
     * @param string|null $path Path to the avatar file
     * @param string|null $storage Storage location
     * @return string Full URL to the avatar or default avatar if not found
     */
    function getUserAvatar(?string $path, ?string $storage): string
    {
        return getStorageFileUrl($path, $storage, 'assets/media/user.png');
    }
}

if (!function_exists('getRoute')) {
    /**
     * Retrieve a custom route by key from the application context
     *
     * @param string $key Route key to look up
     * @param bool $slash Append trailing slash if true
     * @return string Returns the route value or the key itself if not found
     */
    function getRoute(string $key, bool $slash = false): string
    {
        $customRoutes = getContextValue('customRoutes');

        $route = $key;

        if (!empty($customRoutes) && !empty($customRoutes->$key)) {
            $route = $customRoutes->$key;
            if ($slash) {
                $route = rtrim($route, '/') . '/';
            }
        }

        return $route;
    }
}

if (!function_exists('generateURL')) {
    /**
     * Generate a full URL based on one or two route keys
     *
     * @param string $route1 First route key
     * @param string|null $route2 Optional second route key
     * @return string Full URL with language prefix
     */
    function generateURL(string $route1, ?string $route2 = null): string
    {
        if (!empty($route2)) {
            $url = getRoute($route1, true) . getRoute($route2);
        } else {
            $url = getRoute($route1);
        }

        return langBaseUrl($url);
    }
}

if (!function_exists('generatePostUrl')) {
    /**
     * Generate the URL for a post
     *
     * @param object|null $post Post object containing slug and post_url
     * @param string|null $baseURL Optional base URL, defaults to the current language base URL
     * @return string Full URL to the post or "#" if invalid
     */
    function generatePostUrl(?object $post, ?string $baseURL = null): string
    {
        $config = getContextValue('config');

        $baseURL = $baseURL ?? langBaseUrl();

        if (!empty($post)) {
            if (!empty($post->post_url) && !empty($config->redirect_rss_posts_to_original)) {
                return $post->post_url;
            }

            if (!empty($post->slug)) {
                return rtrim($baseURL, '/') . '/' . ltrim($post->slug, '/');
            }
        }

        return "#";
    }
}

if (!function_exists('generateTagURL')) {
    /**
     * Generate a full URL for a tag page
     *
     * @param string|null $tagSlug Slug of the tag
     * @return string Full URL to the tag page or "#" if slug is empty
     */
    function generateTagURL(?string $tagSlug): string
    {
        if (!empty($tagSlug)) {
            $url = getRoute('tag', true) . ltrim($tagSlug, '/');
            return langBaseUrl($url);
        }

        return "#";
    }
}

if (!function_exists('postUrlNewTab')) {
    /**
     * Determine if a post link should open in a new tab
     *
     * @param object|null $post Post object containing post_url
     * @return string Returns ' target="_blank"' if the post redirects externally, otherwise an empty string
     */
    function postUrlNewTab(?object $post): string
    {
        $config = getContextValue('config');

        if (!empty($post) && !empty($post->post_url) && !empty($config->redirect_rss_posts_to_original)) {
            return ' target="_blank"';
        }

        return '';
    }
}

if (!function_exists('generateNavItemUrl')) {
    /**
     * Generate URL for a navigation menu item based on its type
     *
     * @param mixed $item Navigation item object (must contain type, slug, etc.)
     * @param bool $escape Whether to escape the final result for URL output
     *
     * @return string Generated URL
     */
    function generateNavItemUrl(mixed $item, bool $escape = true): string
    {
        $url = langBaseUrl('#');

        if (empty($item) || !is_object($item)) {
            return $escape ? esc($url) : $url;
        }

        $type = $item->type ?? '';
        $slug = $item->slug ?? '';

        if ($type === 'page') {
            if (!empty($item->link)) {
                $url = $item->link;
            } else {
                $url = langBaseUrl($slug);
            }
        } elseif ($type === 'category') {
            $parentSlug = $item->parent_slug ?? '';

            $path = ($parentSlug !== '') ? $parentSlug . '/' . $slug : $slug;

            $url = langBaseUrl($path);
        }

        return $escape ? esc($url) : $url;
    }
}

if (!function_exists('generateCategoryUrl')) {
    /**
     * Generates a localized URL for a category or post object automatically
     *
     * @param object $obj
     * @return string
     */
    function generateCategoryUrl(object $obj): string
    {
        if (empty($obj)) {
            return '#';
        }

        $slug = '';
        $parentSlug = '';

        // Check if it's a Post object (has joined category fields)
        if (isset($obj->cat_slug)) {
            $slug = $obj->cat_slug;
            $parentSlug = $obj->cat_parent_slug ?? '';
        } // Otherwise, assume it's a standard Category object
        else {
            $slug = $obj->slug ?? '';
            $parentSlug = $obj->parent_slug ?? '';
        }

        if (empty($slug)) {
            return '#';
        }

        $path = !empty($parentSlug) ? ($parentSlug . '/' . $slug) : $slug;

        return langBaseUrl($path);
    }
}

if (!function_exists('generateProfileURL')) {
    /**
     * Generates a localized URL for a user profile based on their slug
     *
     * @param mixed $userSlug The user's slug string
     * @return string The generated URL or a fallback ('#')
     */
    function generateProfileURL(mixed $userSlug): string
    {
        if (empty($userSlug) || !is_string($userSlug)) {
            return "#";
        }

        return langBaseUrl(getRoute('profile', true) . $userSlug);
    }
}

if (!function_exists('getSubMenuLinks')) {
    /**
     * Filters menu links to find submenu items by parent ID and type
     *
     * @param mixed $menuLinks Array of menu objects
     * @param mixed $parentId Parent item ID
     * @param string $type Menu item type
     * @return array Filtered submenu items
     */
    function getSubMenuLinks(mixed $menuLinks, mixed $parentId, string $type): array
    {
        if (empty($menuLinks) || !is_array($menuLinks)) {
            return [];
        }

        return array_filter($menuLinks, function ($item) use ($parentId, $type) {
            if (!is_object($item)) {
                return false;
            }

            return ($item->item_type ?? '') === $type && ($item->item_parent_id ?? null) == $parentId;
        });
    }
}

if (!function_exists('isUserOnline')) {
    /**
     * Checks if a user is considered online based on timestamp
     *
     * @param mixed $timestamp Last activity timestamp string
     * @return bool True if active within 3 minutes
     */
    function isUserOnline(mixed $timestamp): bool
    {
        if (empty($timestamp)) {
            return false;
        }

        $timeAgo = strtotime($timestamp);
        if ($timeAgo === false) {
            return false;
        }

        // 3 minutes = 180 seconds. Comparing seconds directly is more precise
        return (time() - $timeAgo) <= 180;
    }
}

if (!function_exists('strSlug')) {
    /**
     * Generates a URL-friendly "slug" from a string
     *
     * @param mixed $str Input string
     * @return string The slugified string
     */
    function strSlug(mixed $str): string
    {
        if (empty($str) || (!is_string($str) && !is_numeric($str))) {
            return '';
        }

        $str = trim((string)$str);

        if ($str === '') {
            return '';
        }

        $str = convert_accented_characters($str);

        return url_title($str, '-', true);
    }
}

if (!function_exists('cleanSlug')) {
    /**
     * Cleans and sanitizes a URL slug string
     *
     * @param mixed $slug The raw slug string (e.g. from URL)
     * @return string The cleaned slug
     */
    function cleanSlug(mixed $slug): string
    {
        if (empty($slug) || (!is_string($slug) && !is_numeric($slug))) {
            return '';
        }

        $slug = trim((string)$slug);

        if ($slug === '') {
            return '';
        }

        $slug = rawurldecode($slug);

        $slug = strip_tags($slug);

        return removeSpecialCharacters($slug);
    }
}

if (!function_exists('cleanStr')) {
    /**
     * General purpose string cleaner for output
     *
     * @param mixed $str Input string (or null/numeric)
     * @return string The cleaned and escaped string
     */
    function cleanStr(mixed $str): string
    {
        if ($str === null) {
            return '';
        }

        $str = trim((string)$str);
        if ($str === '') {
            return '';
        }

        $str = removeSpecialCharacters($str);

        return esc($str);
    }
}

if (!function_exists('removeForbiddenCharacters')) {
    /**
     * Removes a predefined set of forbidden characters from a string
     *
     * @param mixed $str Input string (or null/numeric)
     * @return string The sanitized string
     */
    function removeForbiddenCharacters(mixed $str): string
    {
        if ($str === null) {
            return '';
        }

        $str = trim((string)$str);

        if ($str === '') {
            return '';
        }

        static $forbiddenChars = [
            ';', '"', '$', '%', '*', '/', '\'', '<', '>',
            '=', '?', '[', ']', '\\', '^', '`', '{', '}',
            '|', '~', '+'
        ];

        return str_replace($forbiddenChars, '', $str);
    }
}

if (!function_exists('removeSpecialCharacters')) {
    /**
     * Removes an extended set of special characters from a string
     *
     * @param mixed $str Input string
     * @return string The sanitized string
     */
    function removeSpecialCharacters(mixed $str): string
    {
        $str = removeForbiddenCharacters($str);

        if ($str === '') {
            return '';
        }

        static $extraForbiddenChars = ['#', '!', '(', ')'];

        return str_replace($extraForbiddenChars, '', $str);
    }
}

if (!function_exists('priceFormatted')) {
    /**
     * Formats a price value according to currency configuration settings
     *
     * @param mixed $price The price value
     * @param int $decimalPoint Decimal places for floats
     * @return string The formatted HTML price string
     */
    function priceFormatted(mixed $price, int $decimalPoint = 2): string
    {
        if (!is_numeric($price)) {
            $price = 0;
        }

        $currencySettings = getContextValue('config')->currency_settings;

        $symbolDirection = $currencySettings->symbol_direction ?? 'left';
        $thousandSep = $currencySettings->thousand_separator ?? ',';
        $decimalSep = $currencySettings->decimal_separator ?? '.';

        if (floor((float)$price) == (float)$price) {
            $formattedPrice = number_format((float)$price, 0, $decimalSep, $thousandSep);
        } else {
            $formattedPrice = number_format((float)$price, $decimalPoint, $decimalSep, $thousandSep);
        }

        $escapedSymbol = esc($currencySettings->symbol ?? '');
        $symbolHtml = "<span>" . $escapedSymbol . "</span>";

        if ($symbolDirection === 'left') {
            return $symbolHtml . $formattedPrice;
        }

        return $formattedPrice . $symbolHtml;
    }
}

if (!function_exists('getRewardPriceDecimal')) {
    /**
     * Determines the decimal precision for reward prices
     *
     * @return int The decimal precision (5 or 6)
     */
    function getRewardPriceDecimal(): int
    {
        $config = getContextValue('config');

        $rewardAmount = (float)($config?->reward_amount ?? 0);

        if ($rewardAmount >= 0.1) {
            return 5;
        }
        return 6;
    }
}

if (!function_exists('setSession')) {
    /**
     * Sets a session value using the CodeIgniter Session Service
     *
     * @param string $name The session key
     * @param mixed $value The value to store
     * @return void
     */
    function setSession(string $name, mixed $value): void
    {
        service('session')->set($name, $value);
    }
}

if (!function_exists('getSession')) {
    /**
     * Retrieves a session value using the CodeIgniter Session Service
     *
     * @param string $name The session key
     * @return mixed The session value, or null if not found
     */
    function getSession(string $name): mixed
    {
        return service('session')->get($name);
    }
}

if (!function_exists('removeSession')) {
    /**
     * Removes a session value using the CodeIgniter Session Service
     *
     * @param string $name The session key to remove
     * @return void
     */
    function removeSession(string $name): void
    {
        service('session')->remove($name);
    }
}

if (!function_exists('getSegmentValue')) {
    /**
     * Safely retrieves a specific URI segment
     *
     * @param int $segmentNumber The 1-based segment index
     * @return string|null The segment value or null
     */
    function getSegmentValue(int $segmentNumber): ?string
    {
        $uri = service('uri');

        if ($uri->getTotalSegments() >= $segmentNumber) {
            return $uri->getSegment($segmentNumber);
        }

        return null;
    }
}

if (!function_exists('getStorageFileUrl')) {
    /**
     * Generates a fully qualified URL for a file based on the storage provider
     *
     * @param string|null $path The relative path to the file
     * @param string|null $storage Storage provider ('local', 'aws_s3', etc.). Default: 'local'
     * @param string|null $defaultImage Default image filename (without extension) if path is invalid
     * @return string|null The full URL or an empty string
     */
    function getStorageFileUrl(?string $path, ?string $storage = 'local', ?string $defaultImage = ''): ?string
    {
        static $settings = null;

        // Helper closure to return the default image URL
        $getDefault = fn() => !empty($defaultImage) ? base_url("{$defaultImage}") : '';

        if (empty($path)) {
            return $getDefault();
        }

        $cleanPath = ltrim($path, '/');
        $storage = $storage ?? 'local';

        if ($storage === 'local') {
            if (file_exists(FCPATH . $cleanPath)) {
                return base_url($cleanPath);
            }
            return $getDefault();
        }

        if ($settings === null) {
            $settings = getContextValue('config')->storage_settings ?? null;
        }

        if (empty($settings)) {
            return $getDefault();
        }

        $baseUrl = match ($storage) {
            'aws_s3' => (!empty($settings->aws_bucket) && !empty($settings->aws_region))
                ? "https://{$settings->aws_bucket}.s3.{$settings->aws_region}.amazonaws.com"
                : null,

            'cloudflare_r2' => !empty($settings->r2_public_url)
                ? rtrim($settings->r2_public_url, '/')
                : null,

            'backblaze_b2' => !empty($settings->b2_public_url)
                ? rtrim($settings->b2_public_url, '/')
                : null,

            default => null
        };

        if ($baseUrl) {
            return "{$baseUrl}/{$path}";
        }

        return $getDefault();
    }
}

if (!function_exists('deleteStorageFile')) {
    /**
     * Deletes a file from the specified storage provider
     *
     * @param string|null $key The file path or object key
     * @param string|null $storageType 'local', 'aws_s3', 'cloudflare_r2', etc
     * @return bool                    True on success, False on failure
     */
    function deleteStorageFile(?string $key, ?string $storageType): bool
    {
        if (empty($key)) {
            return false;
        }

        // Handle Local Storage
        if ($storageType === 'local') {
            $fullPath = FCPATH . ltrim($key, '/');

            if (is_file($fullPath)) {
                try {
                    return unlink($fullPath);
                } catch (\Throwable $e) {
                    return false;
                }
            }

            return false;
        }

        // Handle Remote Storage (AWS, R2, etc.)
        try {
            return service('storage')->deleteObject($key, $storageType);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('countItems')) {
    /**
     * Safely counts items in an array or a Countable object
     *
     * @param mixed $items The variable to count
     * @return int The count
     */
    function countItems(mixed $items): int
    {

        if (is_countable($items)) {
            return count($items);
        }

        return 0;
    }
}

if (!function_exists('dateTimeNow')) {

    /**
     * Get the current date and time as a formatted string
     *
     * @return string Current datetime in 'Y-m-d H:i:s' format.
     */
    function dateTimeNow(): string
    {
        $timezone = getContextValue('config')?->timezone ?? null;

        return \CodeIgniter\I18n\Time::now($timezone ?? app_timezone())->toDateTimeString();
    }
}

if (!function_exists('timeNowObject')) {
    /**
     * Get the current Time object respecting the application timezone
     * Useful for date math (e.g., timeObject()->addDays(3))
     *
     * @return \CodeIgniter\I18n\Time
     */
    function timeNowObject(): \CodeIgniter\I18n\Time
    {
        $timezone = getContextValue('config')?->timezone ?? null;
        return \CodeIgniter\I18n\Time::now($timezone ?? app_timezone());
    }
}

if (!function_exists('formatDateClient')) {
    /**
     * Formats a date for the frontend with localized month names
     *
     * @param string|int|null $timestamp The date input (string or unix timestamp)
     * @return string|null The formatted/localized date or null if invalid
     */
    function formatDateClient(string|int|null $timestamp): ?string
    {
        if (empty($timestamp)) {
            return null;
        }

        $ts = is_numeric($timestamp) ? (int)$timestamp : strtotime($timestamp);

        if (!$ts) {
            return null;
        }

        $date = date('M d, Y', $ts);

        return replaceMonthName($date);
    }
}

if (!function_exists('formatDate')) {
    /**
     * Formats a date in a standard numerical format with time
     *
     * @param string|int|null $timestamp The date input
     * @return string|null The formatted string or null if invalid
     */
    function formatDate(string|int|null $timestamp): ?string
    {
        if (empty($timestamp)) {
            return null;
        }

        $ts = is_numeric($timestamp) ? (int)$timestamp : strtotime($timestamp);

        if (!$ts) {
            return null;
        }

        return date("Y-m-d / H:i", $ts);
    }
}

if (!function_exists('formatHour')) {
    /**
     * Extracts and formats only the time part of a date
     *
     * @param string|int|null $timestamp The date input
     * @return string|null The formatted time or null if invalid
     */
    function formatHour(string|int|null $timestamp): ?string
    {
        if (empty($timestamp)) {
            return null;
        }

        $ts = is_numeric($timestamp) ? (int)$timestamp : strtotime($timestamp);

        if (!$ts) {
            return null;
        }

        return date("H:i", $ts);
    }
}

if (!function_exists('replaceMonthName')) {
    /**
     * Replaces English short month names with their translated counterparts
     *
     * @param string|null $str The date string containing English month names (e.g., "Jan 15")
     * @return string The translated string
     */
    function replaceMonthName(?string $str): string
    {
        if (empty($str)) {
            return '';
        }

        static $months = null;

        if ($months === null) {
            $months = [
                'Jan' => trans("January"),
                'Feb' => trans("February"),
                'Mar' => trans("March"),
                'Apr' => trans("April"),
                'May' => trans("May"),
                'Jun' => trans("June"),
                'Jul' => trans("July"),
                'Aug' => trans("August"),
                'Sep' => trans("September"),
                'Oct' => trans("October"),
                'Nov' => trans("November"),
                'Dec' => trans("December"),
            ];
        }

        return strtr($str, $months);
    }
}

if (!function_exists('dateDifference')) {
    /**
     * Calculates the difference between two dates
     *
     * @param string|null $date1 Start date
     * @param string|null $date2 End date
     * @param string $format DateInterval format (default: %a for total days)
     * @return string|null Formatted difference or null on failure
     */
    function dateDifference(?string $date1, ?string $date2, string $format = '%a'): ?string
    {
        if (empty($date1) || empty($date2)) {
            return null;
        }

        try {
            $datetime1 = new DateTime($date1);
            $datetime2 = new DateTime($date2);

            $interval = $datetime1->diff($datetime2);

            return $interval->format($format);
        } catch (Exception $e) {
            return null;
        }
    }
}

if (!function_exists('dateDifferenceInHours')) {
    /**
     * Calculates the absolute difference between two dates in hours
     *
     * @param string|null $date1 Start date
     * @param string|null $date2 End date
     * @return int Total hours (returns 0 on failure or empty input)
     */
    function dateDifferenceInHours(?string $date1, ?string $date2): int
    {
        if (empty($date1) || empty($date2)) {
            return 0;
        }

        try {
            $dt1 = new DateTime($date1);
            $dt2 = new DateTime($date2);

            // Absolute difference in seconds (Timestamp math is robust)
            $seconds = abs($dt2->getTimestamp() - $dt1->getTimestamp());

            // Convert seconds to hours (3600 seconds = 1 hour)
            return (int)floor($seconds / 3600);

        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('timeAgo')) {
    /**
     * Calculates the time elapsed exactly like the legacy version but with cleaner code
     *
     * @param string|int|null $timestamp
     * @return string
     */
    function timeAgo(string|int|null $timestamp): string
    {
        if (empty($timestamp)) {
            return '';
        }

        $time = is_numeric($timestamp) ? (int)$timestamp : strtotime((string)$timestamp);

        if (!$time) {
            return '';
        }

        $seconds = time() - $time;

        $minutes = (int)round($seconds / 60);
        $hours = (int)round($seconds / 3600);
        $days = (int)round($seconds / 86400);
        $months = (int)round($seconds / 2629440);
        $years = (int)round($seconds / 31553280);

        if ($seconds <= 60) {
            return trans("just_now");
        }

        [$value, $unit] = match (true) {
            $minutes <= 60 => [$minutes, 'minute'],
            $hours <= 24 => [$hours, 'hour'],
            $days <= 30 => [$days, 'day'],
            $months <= 12 => [$months, 'month'],
            default => [$years, 'year'],
        };

        $transKey = ($value === 1) ? $unit : $unit . 's';

        return $value . ' ' . trans($transKey) . ' ' . trans('ago');
    }
}

if (!function_exists('showNewsletterModal')) {
    /**
     * Checks if the newsletter modal *feature* is globally enabled
     *
     * Logic for "Show after 10 seconds" or "Don't show if seen"
     * is handled entirely by the client-side JavaScript
     * @return bool Returns true if the modal HTML should be rendered
     */
    function showNewsletterModal()
    {
        static $isEnabled = null;
        if ($isEnabled !== null) {
            return $isEnabled;
        }

        if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET') {
            return $isEnabled = false;
        }

        $newsletter = getContextValue('config')->newsletter_settings ?? '';
        if (empty($newsletter)) {
            return $isEnabled = false;
        }

        if (authCheck() || $newsletter->status != 1 || $newsletter->popup_status != 1) {
            return $isEnabled = false;
        }

        return $isEnabled = true;
    }
}

if (!function_exists('getCategoryById')) {
    /**
     * Retrieves a category object by their unique identifier
     *
     * @param int $id The unique ID of the category
     * @return object|null Returns the category object if found, or null if not found
     */
    function getCategoryById(?int $id = null): ?object
    {
        if (!$id) return null;

        return model('CategoryModel')->find($id);
    }
}

if (!function_exists('getCategoryWidgets')) {
    /**
     * Retrieves widgets and advertisements associated with a specific category
     *
     * @param int|null $categoryId The ID of the category to filter widgets/ads for
     * @param array $widgets The complete list of widget objects
     * @param array $adSpaces The list of advertisement space objects
     * @param int $langId The active language ID used to filter widgets
     *
     * @return object Returns an object with three properties
     */
    function getCategoryWidgets($categoryId, $widgets, $adSpaces, $langId)
    {
        $widgetArray = [];
        $widgetIds = [];
        $ads = [];

        if (!empty($widgets)) {
            foreach ($widgets as $widget) {
                if ((int)$widget->status === 1) {

                    if (empty($categoryId)) {
                        $valid = empty($widget->display_category_id);
                    } else {
                        $valid = ($widget->display_category_id == $categoryId);
                    }

                    if ($valid && !in_array($widget->id, $widgetIds) && $widget->lang_id == $langId) {
                        $widgetArray[] = $widget;
                        $widgetIds[] = $widget->id;
                    }
                }
            }
        }

        if (!empty($adSpaces)) {
            foreach ($adSpaces as $item) {
                if ($item->display_category_id == $categoryId) {
                    $ads[] = $item;
                }
            }
        }

        $hasWidgets = (!empty($widgetArray) || !empty($ads));

        return (object)[
            'widgets'    => $widgetArray,
            'ads'        => $ads,
            'hasWidgets' => $hasWidgets,
        ];
    }
}

if (!function_exists('getPostById')) {
    /**
     * Retrieves a post object by their unique identifier
     *
     * @param int $id The unique ID of the post
     * @return object|null Returns the post object if found, or null if not found
     */
    function getPostById(?int $id = null): ?object
    {
        if (!$id) return null;

        return model('PostModel')->find($id);
    }
}

if (!function_exists('reactionSessionKey')) {
    /**
     * Generates a unique session key for a user's reaction to a specific post
     *
     * @param string $reaction The reaction type (e.g., "like", "heart")
     * @param int $postId The ID of the post
     * @return string The formatted session key string
     */
    function reactionSessionKey(string $reaction, int $postId): string
    {
        return 'rx_' . $reaction . '_' . $postId;
    }
}

if (!function_exists('hasUserReacted')) {
    /**
     * Check whether the current user has already reacted to a specific post
     *
     * @param string $reaction The reaction type
     * @param int $postId The post identifier
     * @return bool Returns true if the session exists, false otherwise
     */
    function hasUserReacted(string $reaction, int $postId): bool
    {
        $key = reactionSessionKey($reaction, $postId);

        return !empty(getSession($key));
    }
}

if (!function_exists('clearContentCache')) {
    /**
     * Clears content-related caches after content changes
     *
     * @return void
     */
    function clearContentCache(): void
    {
        service('cacheService')->clearContentCache();
    }
}

if (!function_exists('getPostImageUrl')) {
    /**
     * Returns the post image URL for the given size
     *
     * @param object|null $post Post object
     * @param string $imageSize Image size key (e.g. "thumb", "medium")
     * @return string Image URL or empty string if not found
     */
    function getPostImageUrl(?object $post, string $imageSize): string
    {
        if (!$post) {
            return '';
        }

        if (!empty($post->image_url)) {
            return (string)$post->image_url;
        }

        $sizeKey = "image_{$imageSize}";

        if (!empty($post->{$sizeKey})) {
            $storage = $post->storage ?? 'local';
            return getStorageFileUrl($post->{$sizeKey}, $storage);
        }

        return '';
    }
}

if (!function_exists('postHasImage')) {
    /**
     * Determines whether a post has an associated image
     *
     * @param object|null $post The post object containing image properties
     * @return bool True if the post has an image; otherwise false
     */
    function postHasImage(?object $post): bool
    {
        if (empty($post)) {
            return false;
        }

        return !empty($post->image_id) || !empty($post->image_url);
    }
}

if (!function_exists('checkPostImg')) {
    /**
     * Checks if a post has an associated image
     * If the $type is 'class', it echoes a CSS class when the image is missing
     *
     * @param object|array|null $post The post object or array to check
     * @param string $type The expected output format (e.g., 'class')
     * @return bool|void Returns boolean if checking status, voids if echoing class
     */
    function checkPostImg($post, $type = '')
    {
        // Evaluate existence cleanly in a single line
        $isExist = !empty($post) && (!empty($post->image_id) || !empty($post->image_url));

        // Handle the 'class' type (View Helper Mode)
        if ($type === 'class') {
            if (!$isExist) {
                echo ' post-item-no-image';
            }
            return;
        }

        return $isExist;
    }
}

if (!function_exists('getPollQuestionAnswerByUser')) {
    /**
     * Returns the selected answer ID for a specific poll question by the user
     *
     * @param array|null $userPollAnswers List of user's poll answer objects
     * @param int $questionId The poll question ID to check
     *
     * @return int|null The selected answer ID, or null if no answer exists
     */
    function getPollQuestionAnswerByUser($userPollAnswers, $questionId)
    {
        if (!empty($userPollAnswers)) {
            foreach ($userPollAnswers as $item) {
                if ($item->question_id == $questionId) {
                    return $item->answer_id;
                }
            }
        }

        if (!empty(getSession('pollAnswer' . $questionId))) {
            return getSession('pollAnswer' . $questionId);
        }

        return null;
    }
}

if (!function_exists('calculatePercentage')) {
    /**
     * Calculates the percentage of a value relative to a total sum
     *
     * @param float|int $total The total sum (denominator)
     * @param float|int $value The partial value (numerator)
     * @return string Returns the percentage formatted to 1 decimal place
     */
    function calculatePercentage($total, $value): string
    {
        if (empty($total) || $total <= 0 || empty($value)) {
            return '0';
        }

        // Calculate
        $percentage = ($value * 100) / $total;

        // Return formatted result (e.g., "12.5" or "100.0")
        return number_format($percentage, 1);
    }
}

if (!function_exists('numToDecimal')) {
    /**
     * Normalize a numeric value to a decimal string using a dot (.) as the decimal separator
     *
     * @param string|int|float|null $price The input value to normalize
     * @return string The normalized decimal number as a string using a dot separator
     */
    function numToDecimal($price): string
    {
        if ($price === null || trim((string)$price) === '') {
            return '0';
        }

        // Replace comma with dot
        $price = str_replace(',', '.', (string)$price);

        // Remove all characters except digits, dot and minus
        $price = preg_replace('/[^0-9.\-]/', '', $price);

        if (!is_numeric($price)) {
            return '0';
        }

        return (string)((float)$price);
    }
}

if (!function_exists('setPageMeta')) {
    /**
     * Builds and returns page meta information
     *
     * @param string $pageTitle Page title
     * @param array|null $data Existing meta data (optional)
     * @return array
     */
    function setPageMeta($pageTitle, $data = null)
    {
        $settings = getContextValue('settings');

        if ($data === null) {
            $data = [];
        }

        $data['title'] = $pageTitle;
        $data['description'] = $pageTitle . ' - ' . ($settings->site_title ?? '');
        $data['keywords'] = $pageTitle . ', ' . ($settings->application_name ?? '');

        return $data;
    }
}

if (!function_exists('isPostPublished')) {
    /**
     * Checks if a post is fully published and visible to the public
     *
     * @param object|null $post The post object to check
     * @return bool True if the post meets all publication criteria, false otherwise
     */
    function isPostPublished($post): bool
    {
        if (empty($post)) {
            return false;
        }

        return (int)$post->status === 1 && (int)$post->is_scheduled !== 1 && (int)$post->visibility === 1;
    }
}

if (!function_exists('generateKeywords')) {
    /**
     * Generates a comma-separated string of keywords from a given title
     *
     * @param string|null $title The title or text to process
     * @return string Returns a comma-separated string of keywords
     */
    function generateKeywords(?string $title): string
    {
        if (empty($title)) {
            return "";
        }

        $words = preg_split('/\s+/', trim($title), -1, PREG_SPLIT_NO_EMPTY);
        $keywordList = [];

        foreach ($words as $word) {
            $word = trim($word, ", \t\n\r\0\x0B");
            $cleanWord = removeSpecialCharacters($word);

            if (!empty($cleanWord) && mb_strlen($cleanWord) > 2) {
                $keywordList[] = $cleanWord;
            }
        }

        return implode(', ', $keywordList);
    }
}

if (!function_exists('filterValidIds')) {
    /**
     * Validates and filters a list of IDs
     *
     * @param mixed $ids The input data to validate (expected to be an array of IDs)
     * @return array<int> A re-indexed array containing only unique, positive integers
     */
    function filterValidIds($ids): array
    {
        if (empty($ids) || !is_array($ids)) {
            return [];
        }

        // Cast all items to integers
        $ids = array_map('intval', $ids);

        // Filter out non-positive integers (assuming IDs must be > 0)
        $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });

        // Return unique values and re-index the array
        return array_values(array_unique($ids));
    }
}

if (!function_exists('getMediaIcon')) {
    /**
     * Returns the appropriate media icon (SVG) for a given post type
     *
     * @param object|null $post The post object containing at least the 'post_format' property
     * @param string $class Optional additional CSS classes to append to the wrapper
     *
     * @return string Returns the HTML string
     */
    function getMediaIcon($post, string $class = ''): string
    {
        $icons = [
            'video' => 'vri-play',
            'audio' => 'vri-music',
        ];

        $postObj = (object)$post;
        $format = $postObj->post_format ?? null;

        if (empty($format)) {
            return '';
        }

        // Event Format
        if ($format === 'event') {
            $extraData = $postObj->extra_data ?? [];

            if (is_string($extraData)) {
                $extraData = json_decode($extraData, true);
            } else {
                $extraData = (array)$extraData;
            }

            if (is_array($extraData) && !empty($extraData['event_date'])) {
                $timestamp = strtotime($extraData['event_date']);

                // Format pieces: 26, Sep, 2026
                $day = date('d', $timestamp);
                $month = date('M', $timestamp);
                $year = date('Y', $timestamp);

                $finalClass = trim('list-event-badge ' . esc($class));

                // Vertical stacked date structure
                return '<div class="' . $finalClass . '">
                        <span class="le-day">' . $day . '</span>
                        <span class="le-month">' . $month . '</span>
                        <span class="le-year">' . $year . '</span>
                    </div>';
            }
            return '';
        }

        // Video/Audio Icon
        if (!isset($icons[$format])) {
            return '';
        }

        $finalClass = trim('media-icon media-icon-' . esc($format) . ' ' . esc($class));
        $iconClass = esc($icons[$format]);

        return '<div class="' . $finalClass . '"><i class="vr-icon ' . $iconClass . '"></i></div>';
    }
}

if (!function_exists('getPostListStyle')) {
    /**
     * Returns list style data for a post item by index
     *
     * @param object|null $post Post object
     * @param int|string $index Style index key
     * @return object
     */
    function getPostListStyle($post, $index)
    {
        $cssListStyles = getAppDefault('cssListStyles');

        // Default response
        $result = (object)[
            'style'  => 'none',
            'status' => 0,
        ];

        if (
            empty($post->link_list_style) ||
            !is_object($post->link_list_style) ||
            !isset($post->link_list_style->{$index})
        ) {
            return $result;
        }

        $item = $post->link_list_style->{$index};

        if (!empty($item->style) && in_array($item->style, $cssListStyles, true)) {
            $result->style = $item->style;
        }

        $result->status = !empty($item->status) ? (int)$item->status : 0;

        return $result;
    }
}

if (!function_exists('addHttpsToUrl')) {
    /**
     * Prepends 'https://' to a URL if it doesn't already have a scheme
     *
     * @param string|null $url The raw URL
     * @return string|null The normalized URL or null if empty
     */
    function addHttpsToUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            return "https://" . $url;
        }

        return $url;
    }
}

if (!function_exists('numberFormatShort')) {
    /**
     * Formats a number into a short metric format (K, M, B)
     *
     * @param int|float $n The number to format
     * @param int $precision Decimal precision
     * @return string
     */
    function numberFormatShort(int|float|string|null $n, int $precision = 1): string
    {
        if (!is_numeric($n)) {
            return '0';
        }

        $n = (float)$n;

        if ($n < 1000) {
            return number_format($n);
        }

        $units = [
            1000000000 => trans('number_short_billion'),
            1000000    => trans('number_short_million'),
            1000       => trans('number_short_thousand')
        ];

        foreach ($units as $divisor => $suffix) {
            // Check if number is greater than or equal to the unit
            if ($n >= $divisor) {
                $value = $n / $divisor;
                $formatted = number_format($value, $precision);

                // Clean trailing zeros (e.g., "1.0K" -> "1K")
                if ($precision > 0) {
                    $dotZero = '.' . str_repeat('0', $precision);
                    $formatted = str_replace($dotZero, '', $formatted);
                }

                return $formatted . ($suffix ?? '');
            }
        }

        return (string)$n;
    }
}

if (!function_exists('getLocalizedObjectValue')) {
    /**
     * Returns a localized field value from a localized object array
     *
     * @param string|array|null $data A JSON string or an array of stdClass objects containing localized fields
     * @param int $langIdThe active language ID to retrieve the value for
     * @param string $field The field name to retrieve from the localized object
     * @return string|null The localized field value if found, otherwise null
     */
    function getLocalizedObjectValue($data, int $langId, string $field = 'name'): ?string
    {
        if (empty($data)) {
            return null;
        }

        if (is_string($data)) {
            $data = json_decode($data);
        }

        if (!is_array($data)) {
            return null;
        }

        foreach ($data as $item) {
            if (isset($item->lang_id, $item->{$field}) && (int)$item->lang_id === $langId) {
                return (string)$item->{$field};
            }
        }

        $fallbackLangId = getContextValue('config')->defaultLang ?? 1;
        if ($fallbackLangId !== null) {
            foreach ($data as $item) {
                if (isset($item->lang_id, $item->{$field}) && (int)$item->lang_id === $fallbackLangId) {
                    return (string)$item->{$field};
                }
            }
        }

        return null;
    }
}

if (!function_exists('hasPermission')) {
    /**
     * Checks if the user has the specified permission
     *
     * @param string $permission
     * @param object|null $user
     * @return bool
     */
    function hasPermission(string $permission, $user = null): bool
    {
        if (empty($user) && authCheck()) {
            $user = user();
        }

        if (empty($user)) {
            return false;
        }

        // Super Admin bypass
        if ((int)$user->is_super_admin === 1) {
            return true;
        }

        // Check permission list
        if (!empty($user->permissions)) {
            $permissions = explode(',', $user->permissions);
            return in_array($permission, $permissions);
        }

        return false;
    }
}

if (!function_exists('checkPermission')) {
    /**
     * Verifies permission.
     * - AJAX request: returns JSON response
     * - Normal request: redirects to base URL
     *
     * @param string $permission
     * @return void
     */
    function checkPermission(string $permission): void
    {
        if (hasPermission($permission)) {
            return;
        }

        $request = service('request');
        $response = service('response');

        // AJAX
        if ($request->isAJAX()) {
            $response->setStatusCode(403)
                ->setJSON(jsonResponse(false))
                ->send();
            exit;
        }

        // Normal browser request
        redirect()->to(adminUrl())->send();
        exit;
    }
}

if (!function_exists('hasAdminPanelAccess')) {
    /**
     * Checks if the user has valid access to the admin panel
     *
     * @param object|null $user
     * @return bool
     */
    function hasAdminPanelAccess($user = null): bool
    {
        if (empty($user) && authCheck()) {
            $user = user();
        }

        if (empty($user)) {
            return false;
        }

        if (isset($user->is_super_admin) && (int)$user->is_super_admin === 1) {
            return true;
        }

        if (!empty($user->permissions)) {
            $permissionsArray = array_filter(array_map('trim', explode(',', $user->permissions)));

            if (in_array('admin_panel', $permissionsArray) && count($permissionsArray) > 1) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('checkPostOwnership')) {
    /**
     * Checks whether the currently authenticated user owns the given post
     *
     * @param int|string $ownerId The user ID of the post owner
     * @return bool Returns true or false
     */
    function checkPostOwnership($ownerId): bool
    {
        if (!authCheck()) {
            return false;
        }

        if (hasPermission('manage_all_posts')) {
            return true;
        }

        return (int)$ownerId === (int)user()->id;
    }
}

if (!function_exists('checkFileOwnership')) {
    /**
     * Checks if the current user owns the file or has the required global permissions
     *
     * @param int|string $userId The ID of the user who originally uploaded or owns the file.
     * @return bool Returns true if the user has 'manage_all_posts' permission or is the owner, false otherwise.
     */
    function checkFileOwnership(int|string $userId): bool
    {
        if (hasPermission('manage_all_posts')) {
            return true;
        }

        if ((int)$userId === (int)user()->id) {
            return true;
        }

        return false;
    }
}

if (!function_exists('getSubscriptionBadgeIcon')) {
    /**
     * Retrieves the SVG string for a specific subscription badge icon
     *
     * @param string $iconKey The unique identifier key for the icon
     * @return string The SVG markup of the icon, or an empty string
     */
    function getSubscriptionBadgeIcon(string $iconKey): string
    {
        if (empty($iconKey)) {
            return '';
        }

        // Fetch the predefined icons array
        $icons = getAppDefault('subscriptionBadgeIcons');

        // Verify the array exists and contains the requested key
        if (is_array($icons) && array_key_exists($iconKey, $icons)) {
            return (string)$icons[$iconKey];
        }

        return '';
    }
}

if (!function_exists('getNewsletterImage')) {
    /**
     * Returns the newsletter image URL
     *
     * @param object|null $config Configuration object
     * @return string The newsletter image URL
     */
    function getNewsletterImage(?object $config): string
    {
        $default = base_url('assets/media/newsletter.webp');

        if (empty($config)) {
            return $default;
        }

        if (!empty($config->newsletter_settings->image)) {
            return getStorageFileUrl(
                $config->newsletter_settings->image,
                $config->newsletter_settings->storage
            );
        }

        return $default;
    }
}

if (!function_exists('createAdCode')) {
    /**
     * Generates an HTML snippet for an advertisement banner
     *
     * @param string|null $url The target URL
     * @param string|null $imgPath The image file path
     * @param string|null $storage Storage provider key
     * @param int $width Image width
     * @param int $height Image height
     * @return string
     */
    function createAdCode(?string $url, ?string $imgPath, ?string $storage, int $width, int $height): string
    {
        if (empty($imgPath)) {
            return '';
        }

        $safeUrl = !empty($url) ? esc($url) : 'javascript:void(0);';
        $imgSrc = getStorageFileUrl($imgPath, $storage);
        $altText = "block-{$width}";

        return <<<HTML
        <a href="{$safeUrl}" target="_blank" rel="noopener noreferrer sponsored" aria-label="{$altText}">
            <img src="{$imgSrc}" alt="{$altText}" width="{$width}" height="{$height}" loading="lazy">
        </a>
        HTML;
    }
}

if (!function_exists('payoutMethod')) {
    /**
     * Retrieves a specific payout method setting by key
     *
     * @param string $key The key of the payout method item
     * @return string|array|null The value or empty string if not found
     */
    function payoutMethod(string $key): string|array|null
    {
        static $payoutMethods = null;

        if ($payoutMethods === null) {
            $config = getContextValue('config');

            $payoutMethods = $config->payout_methods ?? (object)[];
        }

        return $payoutMethods->$key ?? '';
    }
}

if (!function_exists('userPayoutMethod')) {
    /**
     * Retrieves a specific key from the user's serialized payout settings
     *
     * @param object $user The user object containing 'payout_methods' and 'id'
     * @param string $key The specific setting key to retrieve
     * @return string|mixed
     */
    function userPayoutMethod(object $user, string $key): mixed
    {
        if (empty($user->payout_methods)) {
            return '';
        }

        return $user->payout_methods->$key ?? '';
    }
}

if (!function_exists('convertTagifyToString')) {
    /**
     * Converts Tagify JSON input to a comma-separated string
     *
     * @param mixed $input
     * @return string
     */
    function convertTagifyToString($input): string
    {
        if (empty($input)) {
            return '';
        }

        $items = json_decode($input, true);

        if (!is_array($items)) {
            return '';
        }

        $values = array_column($items, 'value');
        $values = array_filter($values, fn($v) => !empty($v));

        return implode(', ', $values);
    }
}

if (!function_exists('jsonResponse')) {
    /**
     * Universal JSON Response Handler
     *
     * @param mixed $payload Array (data), True (success), or False (error)
     * @param int $statusCode HTTP Status Code (auto-switches to 400 if false is sent)
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function jsonResponse(mixed $payload = true, int $statusCode = 200)
    {
        $response = service('response');
        $output = [];

        if ($payload === false) {
            $output['status'] = 0;
        } elseif ($payload === true) {
            $output['status'] = 1;
        } else {
            $output = $payload;
        }

        $json = json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $response
            ->setStatusCode($statusCode)
            ->setContentType('application/json')
            ->setBody($json);
    }
}

if (!function_exists('validationError')) {
    /**
     * Renders a styled validation error message for a specific form field
     *
     * @param string $field The input field name to check
     * @param string $class CSS class for styling (default: text-danger)
     * @return string HTML output or empty string
     */
    function validationError(string $field, string $class = 'text-danger'): string
    {
        $errors = session('errors');
        if (empty($errors) || !isset($errors[$field])) {
            return '';
        }

        $message = esc($errors[$field]);
        $cssClass = esc($class);

        return <<<HTML
            <div class="d-flex align-items-center mt-1 {$cssClass}">
                <i class="ki-duotone ki-information-2 fs-2 me-1 text-danger">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <span>{$message}</span>
            </div>
        HTML;
    }
}

if (!function_exists('generateUuidV4')) {
    /**
     * Generates a cryptographically secure UUID version 4
     *
     * @return string The 36-character UUID string
     * @throws \Exception If source of randomness fails
     */
    function generateUuidV4(): string
    {
        $data = random_bytes(16);

        // Set version to 0100 (UUID v4) and variant to 10xx (RFC 4122)
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Format into standard 8-4-4-4-12 hexadecimal string
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('generateSecureToken')) {
    /**
     * Generates a cryptographically secure pseudo-random hex token
     *
     * @param int $length length of bytes (output string length will be double)
     * @return string The generated hex token
     * @throws \Exception If an appropriate source of randomness cannot be found
     */
    function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}

if (!function_exists('isTokenExpired')) {
    /**
     * Checks if a token has expired based on its creation time
     *
     * @param string|null $createdAt The timestamp when the token was created (Y-m-d H:i:s)
     * @param int $expirationHours The validity period in hours (default: 1 hour)
     * @return bool True if expired or invalid, False if still valid
     */
    function isTokenExpired(?string $createdAt, int $expirationHours = 1): bool
    {
        // If no timestamp exists, it's expired/invalid
        if (empty($createdAt)) {
            return true;
        }

        $createdTime = strtotime($createdAt);

        // If date parsing fails, treat as expired
        if ($createdTime === false) {
            return true;
        }

        $currentTime = time();
        $expirationSeconds = $expirationHours * 3600;

        // Check difference
        return ($currentTime - $createdTime) > $expirationSeconds;
    }
}

if (!function_exists('safeEncode')) {
    /**
     * Safely encodes data into a JSON string
     *
     * @param mixed $data Input data to encode
     * @param int $options Encode options (defaults to Unicode unescaped)
     * @param int $depth Max recursion depth
     * @return string JSON string or empty string on failure
     */
    function safeEncode(mixed $data, int $options = JSON_UNESCAPED_UNICODE, int $depth = 512): string
    {
        try {
            return json_encode($data, $options | JSON_THROW_ON_ERROR, $depth);
        } catch (Throwable $e) {
            return '';
        }
    }
}

if (!function_exists('safeDecode')) {
    /**
     * Safely decodes a JSON string, returning a default value on failure
     *
     * @param string|null $json The JSON string
     * @param bool $assoc True for associative array, false for object
     * @return mixed Array/Object on success, or []/null on failure based on $assoc
     */
    function safeDecode(?string $json, bool $assoc = false): mixed
    {
        $fallback = $assoc ? [] : null;

        if ($json === null || trim($json) === '') {
            return $fallback;
        }

        try {
            $data = json_decode($json, $assoc, 512, JSON_THROW_ON_ERROR);

            return $data ?? $fallback;

        } catch (JsonException|Throwable $e) {
            return $fallback;
        }
    }
}

if (!function_exists('isPostFormatActive')) {
    /**
     * Checks if a specific post format is active based on configuration settings
     *
     * @param string $key The format key to check
     * @param object|null $configThe configuration object containing post_formats JSON string
     * @return bool True if active, false otherwise
     */
    function isPostFormatActive(string $key, ?object $config = null): bool
    {
        // Static array to cache the results for each key during the request lifecycle
        static $cache = [];

        // Return the cached result immediately if it exists
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        if ($config === null) {
            return $cache[$key] = false;
        }

        $formats = $config->post_formats ?? null;

        // Calculate the status and store it in the cache array before returning
        $isActive = isset($formats) && isset($formats->$key) && (int)$formats->$key === 1;

        return $cache[$key] = $isActive;
    }
}

if (!function_exists('getAllowedExtensionsBySource')) {
    /**
     * Retrieves comma-separated allowed file extensions based on media source type
     *
     * @param string $source The media source (image, audio, video, file)
     * @return string|null Comma-separated extensions or null if source is invalid
     */
    function getAllowedExtensionsBySource(string $source): ?string
    {
        switch ($source) {
            case 'image':
                return implode(',', getAppDefault('allowedExtensions')['image']);

            case 'audio':
                return implode(',', getAppDefault('allowedExtensions')['audio']);

            case 'video':
                return implode(',', getAppDefault('allowedExtensions')['video']);

            case 'file':
                $ext = getContextValue('config')->allowed_file_extensions;

                $extensions = match (true) {
                    is_array($ext) => $ext,
                    is_object($ext) && method_exists($ext, 'toArray') => $ext->toArray(),
                    is_object($ext) && method_exists($ext, 'all') => $ext->all(),
                    default => [],
                };

                $extensions = is_array($extensions) ? $extensions : [];

                return implode(',', $extensions ?: ['jpg', 'png']);

            default:
                return null;
        }
    }
}

if (!function_exists('csvToArray')) {
    /**
     * Converts a comma-separated string into a clean PHP array
     *
     * @param string|null $csv The comma-separated string input (may be null)
     * @return array The resulting cleaned array of values
     */
    function csvToArray(?string $csv): array
    {
        return array_values(
            array_filter(
                array_map(
                    static fn($v) => trim((string)$v),
                    explode(',', (string)($csv ?? ''))
                ),
                static fn($v) => $v !== ''
            )
        );
    }
}

if (!function_exists('cleanXmlData')) {
    /**
     * Cleans and prepares a value for safe use inside XML / RSS documents
     *
     * @param mixed $data The input value to be cleaned
     * @return string A UTF-8 encoded, XML-safe string suitable for use inside CDATA sections
     */
    function cleanXmlData($data): string
    {
        if ($data === null || $data === '') {
            return '';
        }

        $string = (string)$data;

        // Guarantee UTF-8 encoding
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8', 'auto');
        }

        // Remove invalid XML 1.0 control characters
        $string = preg_replace(
            '/[^\x09\x0A\x0D\x20-\xD7FF\xE000-\xFFFD\x{10000}-\x{10FFFF}]/u',
            '',
            $string
        );

        // Break CDATA closing tags to prevent XML breakage
        return str_replace(']]>', ']]]]><![CDATA[>', $string);
    }
}

if (!function_exists('xmlEscape')) {
    /**
     * Escapes a string for safe use in XML attributes or non-CDATA XML nodes
     *
     * @param string $value The raw string value to be escaped
     * @return string An XML-safe, escaped string
     */
    function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('getSecuritySettings')) {
    /**
     * Retrieves security settings as an object
     *
     * * @return object Returns an object containing all security settings
     */
    function getSecuritySettings(): object
    {
        static $cachedSettings = null;

        if ($cachedSettings !== null) {
            return $cachedSettings;
        }

        $defaults = getAppDefault('security');
        $globalConfig = getContextValue('config');

        $dbSettings = [];

        if (!empty($globalConfig)) {
            $rawSecurity = $globalConfig->security_settings ?? null;

            if (is_array($rawSecurity)) {
                $dbSettings = $rawSecurity;
            } elseif (is_string($rawSecurity)) {

                $decoded = json_decode($rawSecurity, true);
                $dbSettings = is_array($decoded) ? $decoded : [];
            }
        }

        $finalArray = array_merge(
            is_array($defaults) ? $defaults : [],
            $dbSettings
        );

        $cachedSettings = (object)$finalArray;

        return $cachedSettings;
    }
}

if (!function_exists('getCaptchaProvider')) {
    /**
     * Returns frontend-ready captcha configuration data
     * based on the selected captcha provider
     *
     * @param object $config Captcha configuration object
     * @return object Frontend captcha provider data
     */
    function getCaptchaProvider(object $config): object
    {
        $captchaService = new App\Services\CaptchaService($config);

        return (object)$captchaService->getFrontendData();
    }
}

if (!function_exists('getAvatarColor')) {
    /**
     * Generates a deterministic, consistent color class based on the input string
     *
     * @param string|null $name The input string (e.g., username)
     * @return string Returns a Bootstrap/Metronic color class (e.g., 'primary', 'success')
     */
    function getAvatarColor(?string $name): string
    {
        // Default colors supported by Metronic/Bootstrap
        $colors = ['primary', 'success', 'info', 'warning', 'danger', 'dark', 'secondary'];

        // Fallback for empty strings
        if (empty($name)) {
            return $colors[0];
        }

        // Generate a checksum of the string (crc32 is fast and deterministic)
        $hash = abs(crc32($name));

        // Use modulo to map the hash to an array index
        $index = $hash % count($colors);

        return $colors[$index];
    }
}

if (!function_exists('isFontValid')) {
    /**
     * Checks if a font file physically exists on the server
     *
     * @param string|null $path Relative path from public folder
     * @return bool True if file exists, False otherwise
     */
    function isFontValid(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }
        return is_file(FCPATH . ltrim($path, '/'));
    }
}

if (!function_exists('pxToRem')) {
    /**
     * Converts a pixel value to REM unit
     *
     * @param int|float|string $px Pixel value (e.g., 18)
     * @return string Converted string (e.g., "1.125rem")
     */
    function pxToRem(int|float|string $px): string
    {
        $baseSize = 16;
        $remValue = (float)$px / $baseSize;
        return round($remValue, 4) . 'rem';
    }
}

if (!function_exists('getFontLineHeight')) {
    /**
     * Calculates the ideal line-height based on font size
     *
     * @param int|float|string $px Font size in pixels
     * @return float Unitless line-height ratio
     */
    function getFontLineHeight(int|float|string $px, bool $isContent = false): float
    {
        $size = (float)$px;

        $ratio = match (true) {
            $size >= 28 => 1.2,
            $size >= 20 => 1.3,
            $size >= 16 => 1.4,
            default => 1.50,
        };

        if ($isContent) {
            return $ratio + 0.15;
        }

        return $ratio;
    }
}

if (!function_exists('getFontScaleFactor')) {
    /**
     * Determines the font scale factor for mobile screens (Fluid Typography)
     *
     * @param string $key Font size key (e.g., 'title_5xl' or 'title-5xl')
     * @return float Scale factor ratio
     */
    function getFontScaleFactor(string $key): float
    {
        $normalized = str_replace('_', '-', $key);

        return match (true) {
            // Giant titles
            in_array($normalized, ['title-5xl', 'title-4xl', 'post-title']) => 0.75,

            // 3xl and 2xl
            in_array($normalized, ['title-3xl', 'title-2xl']) => 0.82,

            // xl and lg
            in_array($normalized, ['title-xl', 'title-lg']) => 0.88,

            // md
            in_array($normalized, ['title-md']) => 0.90,

            // content
            in_array($normalized, ['content']) => 0.96,

            // Small titles & base text
            default => 1.0,
        };
    }
}

if (!function_exists('getSiteSocialLinks')) {
    /**
     * Retrieves the active social media links for the website
     *
     * @param object $settings The settings object containing the 'site_social_media' property
     * @return array Returns an array of active platforms including 'url', 'svg', 'name', and 'color'
     */
    function getSiteSocialLinks(object $settings): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $definedPlatforms = getSocialPlatforms();

        $savedSocials = (array)($settings->site_social_media ?? []);

        $activeLinks = [];

        if (!empty($savedSocials) && is_array($savedSocials)) {
            foreach ($savedSocials as $key => $url) {
                if (!isset($definedPlatforms[$key]) || empty($url)) {
                    continue;
                }

                $platformData = $definedPlatforms[$key];

                $platformData['url'] = $url;
                $platformData['key'] = $key;

                $activeLinks[] = (object)$platformData;
            }
        }

        $cache = $activeLinks;

        return $cache;
    }
}

if (!function_exists('getUserSocialLinks')) {
    /**
     * Retrieves the active and allowed social media links for a specific user
     *
     * @param object $user The user object containing 'social_media_data'
     * @param object $settings The global settings object containing 'profile_social_media'
     * @return array Returns an array of objects containing 'url', 'svg', 'name', 'color', 'text_color'
     */
    function getUserSocialLinks(object $user, object $settings): array
    {
        $definedPlatforms = getSocialPlatforms();

        $userSocials = $user->social_media_data ?? [];

        if (is_string($userSocials)) {
            $userSocials = json_decode($userSocials, true);
        } elseif (is_object($userSocials)) {
            $userSocials = (array)$userSocials;
        }

        $allowedKeys = (array)($settings->profile_social_media ?? []);

        if (empty($userSocials) || !is_array($userSocials) || empty($allowedKeys) || !is_array($allowedKeys)) {
            return [];
        }

        $activeLinks = [];

        foreach ($allowedKeys as $key) {
            if (isset($definedPlatforms[$key]) && !empty($userSocials[$key])) {

                $meta = $definedPlatforms[$key];

                $linkData = [
                    'key'        => $key,
                    'name'       => trans($key),
                    'url'        => $userSocials[$key],
                    'svg'        => $meta['svg'],
                    'color'      => $meta['color'],
                    'text_color' => $meta['text_color'] ?? '#ffffff'
                ];

                $activeLinks[] = (object)$linkData;
            }
        }

        return $activeLinks;
    }
}

if (!function_exists('sortArrayOfObjects')) {
    /**
     * Sorts an array of objects by a specific property key
     *
     * @param array $array The array of objects to sort
     * @param string $key The property to sort by (e.g., 'slider_order', 'created_at')
     * @param string $direction 'asc' or 'desc'
     * @param string $fallbackKey Property to use if primary values are equal (always sorts DESC)
     * @return array
     */
    function sortArrayOfObjects(array $array, string $key, string $direction = 'asc', string $fallbackKey = 'id'): array
    {
        usort($array, function ($a, $b) use ($key, $direction, $fallbackKey) {
            $valA = $a->$key ?? null;
            $valB = $b->$key ?? null;

            // Stable sorting fallback: if primary values are the same, sort by fallback key DESC
            if ($valA === $valB) {
                $fallA = (int)($a->$fallbackKey ?? 0);
                $fallB = (int)($b->$fallbackKey ?? 0);

                return $fallB <=> $fallA; // Newest first
            }

            // Primary sorting
            if ($direction === 'asc') {
                return $valA <=> $valB;
            }

            return $valB <=> $valA;
        });

        return $array;
    }
}

if (!function_exists('extractIframeSrcWithDom')) {
    /**
     * Extracts and returns the `src` attribute from an iframe embed code
     *
     * @param string|null $embedCode The iframe embed code or raw user input
     * @return string|null The extracted iframe `src` URL or null if not valid
     */
    function extractIframeSrcWithDom(?string $embedCode): ?string
    {
        if (!$embedCode || trim($embedCode) === '') {
            return null;
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($embedCode, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        foreach ($dom->getElementsByTagName('iframe') as $iframe) {
            $src = trim($iframe->getAttribute('src'));

            if (!$src) {
                continue;
            }

            if (!filter_var($src, FILTER_VALIDATE_URL)) {
                continue;
            }

            $scheme = parse_url($src, PHP_URL_SCHEME);
            if (!in_array($scheme, ['http', 'https'], true)) {
                continue;
            }

            return $src;
        }

        return null;
    }
}

if (!function_exists('cleanExternalLinks')) {
    /**
     * Handles external links based on 3 modes: Allow, Block, Sanitize
     *
     * @param string $content The HTML content to process
     * @param string $contentType Context: 'post' (editorial) or 'public' (UGC)
     * @param int|null $userId The ID of the content owner (Author)
     * @return string
     */
    function cleanExternalLinks(string $content, string $contentType = 'public', ?int $userId = null): string
    {
        if (trim($content) === '') {
            return $content;
        }

        $settings = getSecuritySettings();

        $mode = 'allow';

        if ($contentType === 'post') {
            $mode = $settings->spam_protection_mode_post ?? 'sanitize';
        } else {
            $mode = $settings->spam_protection_mode_public ?? 'block';
        }

        // Permission Check (Content Owner Exemption)
        if ($mode !== 'allow' && !empty($userId)) {
            $contentOwner = getUserById($userId);
            if (!empty($contentOwner)) {
                if ((int)$contentOwner->is_super_admin === 1 || hasPermission('manage_all_posts', $contentOwner)) {
                    $mode = 'allow';
                }
            }
        }

        if ($mode === 'allow') {
            return $content;
        }

        // Prepare Current Domain
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $currentDomain = parse_url('http://' . $host, PHP_URL_HOST);
        $currentDomain = preg_replace('/^www\./i', '', $currentDomain ?? '');

        // Process Links via Regex Callback
        return preg_replace_callback(
            '/<a\b([^>]*)>(.*?)<\/a>/is',
            function ($matches) use ($currentDomain, $mode) {
                $fullTag = $matches[0];
                $attributes = $matches[1];
                $innerHtml = $matches[2];

                // Extract href attribute
                if (!preg_match('/href\s*=\s*["\']([^"\']+)["\']/i', $attributes, $hrefMatch)) {
                    return $fullTag;
                }

                $url = trim($hrefMatch[1]);

                // Block XSS vectors, if URL contains javascript:, data:, or vbscript:, remove the tag immediately.
                if (preg_match('/^(javascript:|data:|vbscript:)/i', $url)) {
                    return $innerHtml;
                }

                // Skip anchors (#) or empty links
                if ($url === '' || $url[0] === '#') {
                    return $fullTag;
                }

                // Check for Absolute URLs (http/https) or Protocol-Relative URLs (//)
                $isAbsolute = preg_match('#^https?://#i', $url) || (substr($url, 0, 2) === '//');

                // Check for Special Protocols (mailto, tel, whatsapp)
                $isSpecialProtocol = preg_match('#^(mailto:|tel:|whatsapp:|sms:|callto:)#i', $url);

                // If it's NOT absolute and NOT a special protocol, it's a relative internal link (e.g., /contact).
                if (!$isAbsolute && !$isSpecialProtocol) {
                    return $fullTag;
                }

                // Parse Host to check for Internal Domain
                $host = '';
                if ($isAbsolute) {
                    $parsed = parse_url($url);
                    $host = $parsed['host'] ?? '';
                    // Fix for protocol-relative URLs (parse_url might return null host)
                    if (!$host && substr($url, 0, 2) === '//') {
                        $parsed = parse_url('http:' . $url);
                        $host = $parsed['host'] ?? '';
                    }
                }

                // Check the host
                if ($host) {
                    $host = preg_replace('/^www\./i', '', $host);

                    // Exact match or Subdomain match
                    $isInternal = ($host === $currentDomain);
                    if (!$isInternal) {
                        $len = strlen($host);
                        $dLen = strlen($currentDomain);
                        // Check if host ends with .currentDomain
                        if ($len > $dLen + 1 && substr($host, -($dLen + 1)) === '.' . $currentDomain) {
                            $isInternal = true;
                        }
                    }

                    // If it is internal, keep the tag as is
                    if ($isInternal) {
                        return $fullTag;
                    }
                }

                // Strip the <a> tag completely, keep the inner text/image.
                if ($mode === 'block') {
                    return $innerHtml;
                }

                // Rebuild the tag with secure attributes.
                if ($mode === 'sanitize') {
                    // Remove existing rel and target attributes to enforce policy
                    $cleanAttrs = preg_replace('/(rel|target)\s*=\s*["\'][^"\']*["\']/i', '', $attributes);
                    $cleanAttrs = trim($cleanAttrs);

                    // Add space only if attributes exist
                    $attrPrefix = $cleanAttrs !== '' ? $cleanAttrs . ' ' : '';

                    // Enforce: nofollow, ugc, noopener, noreferrer, and target blank
                    return '<a ' . $attrPrefix . 'rel="nofollow ugc noopener noreferrer" target="_blank">' . $innerHtml . '</a>';
                }

                return $fullTag;
            },
            $content
        );
    }
}

if (!function_exists('hasPremiumAccess')) {
    /**
     * Determine whether a user has an active premium subscription
     *
     * @param object|int|null $user User entity/object or user ID
     * @return bool True if the user has an active subscription, false otherwise
     */
    function hasPremiumAccess($user = null): bool
    {
        $currentUser = authCheck() ? user() : null;

        $userId = is_object($user)
            ? ($user->id ?? null)
            : ($user ?? ($currentUser->id ?? null));

        if (empty($userId)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $resolvedUser = is_object($user) ? $user : $currentUser;

        if ($resolvedUser && (int)($resolvedUser->id ?? 0) === (int)$userId && isset($resolvedUser->sub_status)) {
            if ($resolvedUser->sub_status !== \App\Models\UserSubscriptionModel::STATUS_ACTIVE) {
                return false;
            }

            return empty($resolvedUser->sub_expires_at) || $resolvedUser->sub_expires_at > $now;
        }

        $subscriptionModel = model('UserSubscriptionModel');

        return !empty($subscriptionModel->getActiveSubscription((int)$userId));
    }
}

if (!function_exists('hasExclusiveAccess')) {
    /**
     * Determine whether the given user has exclusive access to a specific content
     *
     * @param int $postId The ID of the post/content to check
     * @param int|null $userId The user ID. If null, the currently authenticated user ID will be used
     * @return bool Returns true if the user has purchased access to the content, false otherwise
     */
    function hasExclusiveAccess(int $postId, ?int $userId = null): bool
    {
        $userId = $userId ?? (authCheck() ? user()->id : null);

        if (empty($userId)) {
            return false;
        }

        // Check if verifying access for the currently authenticated user
        if (authCheck() && (int)user()->id === (int)$userId) {
            $config = getContextValue('config');

            // Check if the property exists and is an array to prevent errors
            if (isset($config->purchasedContentIds) && is_array($config->purchasedContentIds)) {
                return in_array($postId, $config->purchasedContentIds, true);
            }
        }

        // Fallback to model if the array is not set or checking for a different user
        $purchaseModel = model('UserPurchaseModel');

        return $purchaseModel->hasPurchased((int)$userId, $postId);
    }
}

if (!function_exists('getContentAccessStatus')) {
    /**
     * Determine a user's access status (premium and exclusive) for a given post
     *
     * @param object $post The post entity containing access-related flags (e.g. is_premium, is_exclusive)
     * @param object|int|null $user User entity/object or user ID. Defaults to current authenticated user
     */
    function getContentAccessStatus(object $post, $user = null): object
    {
        $premiumMembership = getContextValue('premiumMembership');
        $currentUser = authCheck() ? user() : null;
        $userId = is_object($user) ? ($user->id ?? null) : ($user ?? ($currentUser->id ?? null));

        $isAuthor = !empty($userId) && (int)$userId === (int)$post->user_id;
        $canBypass = $isAuthor || hasPermission('manage_all_posts');

        if ($canBypass) {
            return (object)['hasAccess' => 1, 'restrictionType' => ''];
        }

        $isExclusive = !empty($premiumMembership->exclusiveSaleStatus) && (!empty($post->is_exclusive) || !empty($post->cat_is_exclusive));

        if ($isExclusive) {
            return (object)[
                'hasAccess'       => (int)hasExclusiveAccess($post->id, $userId),
                'restrictionType' => 'exclusive'
            ];
        }

        $isPremium = false;

        if (!empty($premiumMembership->subscriptionStatus)) {
            if ($premiumMembership->subscriptionMode === 'all' || !empty($post->is_premium) || !empty($post->cat_is_premium)) {
                $isPremium = true;
            }
        }

        if ($isPremium) {
            return (object)[
                'hasAccess'       => (int)hasPremiumAccess($user),
                'restrictionType' => 'premium'
            ];
        }

        return (object)['hasAccess' => 1, 'restrictionType' => ''];
    }
}

if (!function_exists('getContentExclusivePrice')) {
    /**
     * Retrieve the exclusive price for a given content item
     *
     * @param object|null $post The content object containing pricing information
     * @return float The resolved exclusive price
     */
    function getContentExclusivePrice(?object $post): float
    {
        if (empty($post)) {
            return 0.0;
        }

        $postPrice = isset($post->exclusive_price) ? (float)$post->exclusive_price : 0.0;

        if ($postPrice > 0.0) {
            return $postPrice;
        }

        $categoryPrice = isset($post->cat_exclusive_price) ? (float)$post->cat_exclusive_price : 0.0;

        if ($categoryPrice > 0.0) {
            return $categoryPrice;
        }

        return 0.0;
    }
}

if (!function_exists('renderUserBadge')) {
    /**
     * Renders the HTML for a user badge
     *
     * @param array $params Accepts 'badge_id' OR 'plan_id', and optionally 'lang_id'
     * @return string The rendered HTML badge or an empty string
     */
    function renderUserBadge(array $params = []): string
    {
        $badgeId = $params['badge_id'] ?? null;
        $planId = $params['plan_id'] ?? null;
        $langId = $params['lang_id'] ?? null;
        $size = $params['size'] ?? null;
        $isSolid = (int)($params['solid'] ?? 0);

        if (empty($badgeId) && empty($planId)) {
            return '';
        }

        if (empty($langId)) {
            return '';
        }

        $userBadges = getContextValue('userBadges');
        $badge = null;

        // Automatically determine which map to use based on the provided keys
        if (!empty($badgeId) && !empty($userBadges->mapById)) {
            $badge = $userBadges->mapById[$badgeId] ?? null;
        } elseif (!empty($planId) && !empty($userBadges->mapByPlanId)) {
            $badge = $userBadges->mapByPlanId[$planId] ?? null;
        }

        if (empty($badge)) {
            return '';
        }

        $badgeName = getLocalizedObjectValue($badge->badge_name ?? '', $langId, 'name');

        $color = esc($badge->color);
        $icon = getSubscriptionBadgeIcon($badge->icon);
        $name = esc($badgeName);

        // Determine extra CSS classes
        $sizeClass = ($size === 'sm') ? ' premium-badge-sm' : '';
        $sizeClass .= ($isSolid) ? ' premium-badge-solid' : '';

        return <<<HTML
<span class="premium-badge{$sizeClass}" style="--premium-badge-color: {$color};">
    <span class="badge-icon">
        {$icon}
    </span>
    <span class="badge-text">
        {$name}
    </span>
</span>
HTML;
    }
}

if (!function_exists('sendPremiumEmail')) {
    /**
     * Sends premium emails
     *
     * @param string $email The recipient's email address
     * @param string $actionType Available options:
     *                           - 'new_subscription'
     *                           - 'subscription_cancelled'
     *                           - 'subscription_expired'
     *                           - 'subscription_renewed'
     *                           - 'payment_failed'
     *                           - 'expiring_soon'
     *                           - 'expiring_today'
     *                           - 'content_purchase'
     * @param string $buttonUrl Optional dynamic URL (overrides default routing)
     * @return bool
     */
    function sendPremiumEmail(string $email, string $actionType, string $buttonUrl = ''): bool
    {
        if ($buttonUrl === '') {
            $buttonUrl = match ($actionType) {
                'new_subscription' => base_url(),
                'subscription_cancelled',
                'payment_failed',
                'expiring_soon',
                'expiring_today' => generateURL('account_settings', 'manage_subscription'),
                'subscription_expired' => generateURL('subscription', 'plans'),
                'subscription_renewed' => generateURL('account_settings', 'payment_history'),
                default => base_url(),
            };
        }

        $emailService = new \App\Services\EmailService();
        return $emailService->sendPremiumEmail($email, $actionType, $buttonUrl);
    }
}