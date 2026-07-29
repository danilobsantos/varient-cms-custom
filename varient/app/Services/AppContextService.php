<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\SmartObject;
use Config\Database;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Session\Session;

/**
 * Class AppContextService
 *
 * Handles global application state, including configuration, languages,
 * themes, settings, active user authentication, and routing context.
 *
 * Designed to use direct Query Builder to avoid circular dependencies with Models.
 */
class AppContextService
{
    // Dependencies
    protected BaseConnection $db;
    protected Session $session;

    // Cache Control
    protected bool $cacheEnabled = false;
    protected int $cacheTTL = 86400; // Default 1 day

    // Application Data Properties
    protected ?object $config = null;
    protected array $themes = [];
    protected ?object $activeTheme = null;
    protected array $languages = [];
    protected array $activeLanguages = [];
    protected array $roles = [];
    protected ?object $defaultLang = null;
    protected ?object $activeLang = null;
    protected array $languageTranslations = [];
    protected ?object $settings = null;
    protected ?object $customRoutes = null;

    // State Variables
    protected string $langSegment = '';
    protected string $langBaseUrl = '';

    // Auth State
    protected bool $authCheck = false;
    protected ?object $authUser = null;

    // Premium Membership
    protected ?object $premiumMembership = null;
    protected array $purchasedContentIds = [];

    // User Badges
    protected ?object $userBadges = null;

    public function __construct()
    {
        // Ensure critical helpers are loaded before any service calls.
        // During early boot (Routes phase), the autoloader may not have loaded them yet.
        // Auto-detect directory casing (Linux is case-sensitive: Helpers vs helpers)
        if (!function_exists('base_url') || !function_exists('dot_array_search')) {
            $helpersDir = is_dir(SYSTEMPATH . 'Helpers') ? 'Helpers' : 'helpers';
            if (!function_exists('base_url')) {
                require_once SYSTEMPATH . $helpersDir . '/url_helper.php';
            }
            if (!function_exists('dot_array_search')) {
                require_once SYSTEMPATH . $helpersDir . '/array_helper.php';
            }
        }

        $this->db = Database::connect();
        $this->session = session();

        // Load all necessary data immediately upon instantiation
        $this->initialize();
    }

    /**
     * Initializes the service and sets up the global application state
     */
    protected function initialize(): void
    {
        $this->loadConfig();

        if (!empty($this->config->timezone)) {
            date_default_timezone_set($this->config->timezone);
            config('App')->appTimezone = $this->config->timezone;
        }

        $this->themes = $this->getThemes();
        $this->setActiveTheme();

        $this->roles = $this->getRoles();
        $this->languages = $this->getLanguages();
        $this->activeLanguages = array_filter($this->languages, fn($lang) => (int)$lang->status === 1);

        $this->detectLanguage();
        $this->setCustomRoutes();
        $this->initAuth();
        $this->setPremiumMembershipConfig();
        $this->setPurchasedContentsArray();
        $this->setUserBadges();
    }

    /**
     * Loads the main configuration directly from the database and processes JSON fields
     */
    protected function loadConfig(): void
    {
        $this->config = $this->db->table('config')->get()->getFirstRow();

        if (empty($this->config)) {
            return;
        }

        $jsonFields = [
            'app_icon', 'logo', 'post_formats', 'human_verification', 'payout_methods', 'currency_settings', 'captcha_settings', 'premium_membership_settings', 'newsletter_settings',
            'email_settings', 'storage_settings', 'maintenance_mode_settings', 'social_login_settings', 'file_upload_limits', 'allowed_file_extensions', 'ai_writer', 'routes', 'featured_content_settings',
            'sitemap_settings', 'google_news_settings', 'auto_post_deletion_settings'
        ];

        foreach ($jsonFields as $field) {
            if (isset($this->config->$field)) {
                $this->config->$field = new SmartObject(safeDecode($this->config->$field));
            } else {
                $this->config->$field = new SmartObject([]);
            }
        }

        $defaultRoutes = getAppDefault('routes') ?? [];
        $configRoutes = (isset($this->config->routes) && method_exists($this->config->routes, 'toArray'))
            ? $this->config->routes->toArray()
            : [];

        $this->config->routes = array_merge($defaultRoutes, $configRoutes);

        $this->cacheEnabled = (isset($this->config->app_cache_system) && (int)$this->config->app_cache_system === 1);
        $this->cacheTTL = defined('APP_CACHE_TTL') ? APP_CACHE_TTL : 86400;
    }

    /**
     * Allows read access to protected properties
     */
    public function __get(string $key)
    {
        return $this->{$key} ?? null;
    }

    /**
     * Sets properties dynamically if they exist.
     */
    public function set(string $key, $value): void
    {
        if (property_exists($this, $key)) {
            $this->{$key} = $value;
        }
    }

    /**
     * Initializes user authentication from session data
     */
    protected function initAuth(): void
    {
        $userId = (int)$this->session->get('auth_user_id');
        $authToken = (string)$this->session->get('auth_token');

        if (!empty($userId) && !empty($authToken)) {
            $user = $this->db->table('users')
                ->select('users.*, 
                    roles.role_name AS role_name_data, roles.permissions, roles.is_super_admin, roles.badge_id, 
                    user_subscriptions.plan_id AS sub_plan_id, user_subscriptions.status AS sub_status, user_subscriptions.expires_at AS sub_expires_at,
                    subscription_plans.is_ad_free AS sub_is_ad_free')
                ->join('roles', 'roles.id = users.role_id')
                ->join('user_subscriptions', "user_subscriptions.user_id = users.id AND user_subscriptions.status = 'active'", 'left')
                ->join('subscription_plans', 'subscription_plans.id = user_subscriptions.plan_id', 'left')
                ->where('users.id', $userId)
                ->get()
                ->getRow();

            if (!empty($user) && (int)$user->status === 1) {
                if (hash_equals((string)$user->auth_token, $authToken)) {
                    $this->authCheck = true;
                    $this->authUser = $user;
                }
            }
        }
    }

    /**
     * Sets the active theme based on database status
     */
    public function setActiveTheme(): void
    {
        if (empty($this->themes)) {
            return;
        }

        foreach ($this->themes as $theme) {
            if ((int)$theme->is_active === 1) {
                $this->activeTheme = $theme;
                return;
            }
        }

        $this->activeTheme = $this->themes[0] ?? null;
    }

    /**
     * Detects the language from the URL segment or falls back to default
     */
    protected function detectLanguage(): void
    {
        $siteLangId = $this->config->site_lang ?? 0;

        foreach ($this->languages as $lang) {
            if ($lang->id == $siteLangId) {
                $this->defaultLang = $lang;
                break;
            }
        }

        if (empty($this->defaultLang) && !empty($this->languages)) {
            $this->defaultLang = $this->languages[0];
        }

        $segment = getSegmentValue(1);
        $targetLangId = $this->defaultLang->id ?? 1;

        foreach ($this->languages as $lang) {
            if ($segment === $lang->short_form && (int)$lang->status === 1) {
                $this->langSegment = $lang->short_form;
                $targetLangId = $lang->id;
                break;
            }
        }

        $this->setActiveLanguageById((int)$targetLangId);
    }

    /**
     * Manually switches the active language and updates related context
     *
     * @param int $langId
     */
    public function setActiveLanguageById(int $langId): void
    {
        $selectedLang = null;

        foreach ($this->languages as $lang) {
            if ($lang->id == $langId) {
                $selectedLang = $lang;
                break;
            }
        }

        if (!$selectedLang) {
            $selectedLang = $this->defaultLang;
        }

        $this->activeLang = $selectedLang;
        $this->languageTranslations = $this->getLanguageTranslations($selectedLang->id);
        $this->settings = $this->getSettings($selectedLang->id);

        $this->langBaseUrl = (!empty($this->defaultLang) && $this->activeLang->id == $this->defaultLang->id)
            ? base_url()
            : base_url($this->activeLang->short_form);
    }

    /**
     * Merges default app routes with custom routes from the database
     */
    protected function setCustomRoutes(): void
    {
        $defaultRoutes = getAppDefault('routes') ?? [];
        $customRoutes = (array)($this->config->routes ?? []);

        $this->customRoutes = (object)array_merge($defaultRoutes, $customRoutes);
    }

    /**
     * Creates premium membership config object
     */
    protected function setPremiumMembershipConfig(): void
    {
        $settings = $this->config->premium_membership_settings;

        $subscriptionStatus = (int)($settings->subscription_status ?? 0);
        $exclusiveSaleStatus = (int)($settings->exclusive_sale_status ?? 0);

        $paywall = $settings->paywall_appearance ?? '';
        $paywallAppearance = in_array($paywall, ['strict', 'hard', 'fade']) ? $paywall : 'hard';

        $this->premiumMembership = (object)[
            'status'                       => $subscriptionStatus || $exclusiveSaleStatus,
            'subscriptionStatus'           => $subscriptionStatus,
            'exclusiveSaleStatus'          => $exclusiveSaleStatus,
            'subscriptionMode'             => ($settings->subscription_mode ?? '') == 'selective' ? 'selective' : 'all',
            'paywallAppearance'            => $paywallAppearance,
            'subscriptionButtonColor'      => $settings->subscription_button_color ?? '#18181b',
            'subscriptionButtonVisibility' => (int)($settings->subscription_button_visibility ?? 0),
        ];
    }

    /**
     * Sets array for purchased contents by the current user
     */
    protected function setPurchasedContentsArray(): void
    {
        if (!$this->premiumMembership->exclusiveSaleStatus) {
            return;
        }

        if (!$this->authCheck || empty($this->authUser)) {
            return;
        }

        $userId = (int)$this->authUser->id;

        $purchases = $this->db->table('user_purchases')
            ->select('post_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        $this->purchasedContentIds = !empty($purchases) ? array_map('intval', array_column($purchases, 'post_id')) : [];
    }

    /**
     * Set user badges
     */
    protected function setUserBadges(): void
    {
        $this->userBadges = $this->getCache('user_badges_mapped', function () {

            $results = $this->db->table('user_badges')
                ->select('user_badges.*, subscription_plans.id AS plan_id')
                ->join('subscription_plans', 'subscription_plans.badge_id = user_badges.id', 'left')
                ->get()->getResult();

            $mapById = [];
            $mapByPlanId = [];

            if (!empty($results)) {
                foreach ($results as $row) {
                    // Map by badge ID
                    if (!isset($mapById[$row->id])) {
                        $mapById[$row->id] = $row;
                    }

                    // Map by plan ID
                    if (!empty($row->plan_id)) {
                        $mapByPlanId[$row->plan_id] = $row;
                    }
                }
            }

            return (object)[
                'mapById'     => $mapById,
                'mapByPlanId' => $mapByPlanId
            ];
        });
    }

    /*
     * --------------------------------------------------------------------
     * Data Retrieval Methods (Wrapped in Cache)
     * --------------------------------------------------------------------
     */
    protected function getSettings($langId)
    {
        return $this->getCache('settings_' . $langId, function () use ($langId) {

            $settings = $this->db->table('settings')->where('lang_id', (int)$langId)->get()->getFirstRow();

            if (!empty($settings)) {
                $jsonFields = ['site_social_media', 'profile_social_media', 'font_size', 'cookies_warning_data'];
                foreach ($jsonFields as $field) {
                    if (isset($settings->$field)) {
                        $settings->$field = safeDecode($settings->$field);
                    }
                }
            }

            return $settings;
        });
    }

    protected function getThemes()
    {
        return $this->getCache('themes', function () {
            return $this->db->table('themes')->get()->getResult();
        });
    }

    protected function getRoles()
    {
        return $this->getCache('roles', function () {
            return $this->db->table('roles')->get()->getResult();
        });
    }

    protected function getLanguages()
    {
        return $this->getCache('languages', function () {
            return $this->db->table('languages')->get()->getResult();
        });
    }

    protected function getLanguageTranslations($langId): array
    {
        return $this->getCache('language_translations_' . $langId, function () use ($langId) {
            $rows = $this->db->table('language_translations')
                ->where('lang_id', (int)$langId)
                ->get()->getResult();

            if (!empty($rows)) {
                return array_column($rows, 'translation', 'label');
            }
            return [];
        });
    }

    /**
     * Generic Cache Wrapper
     * Uses the $cacheEnabled flag determined during loadConfig()
     *
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    private function getCache(string $key, callable $callback)
    {
        if (!$this->cacheEnabled) {
            return $callback();
        }

        $fullKey = 'app_long_' . $key;
        $cache = cache();
        $data = $cache->get($fullKey);

        if ($data !== null) {
            return $data;
        }

        $data = $callback();

        if ($data !== null) {
            $cache->save($fullKey, $data, $this->cacheTTL);
        }

        return $data;
    }
}