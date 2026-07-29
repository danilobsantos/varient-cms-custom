<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class AppConfig extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'id'                             => 'integer',
        'site_lang'                      => 'integer',
        'content_cache_ttl'              => 'integer',
        'reward_amount'                  => 'double',
        'multilingual_system'            => 'boolean',
        'show_rss'                       => 'boolean',
        'captcha_status'                 => 'boolean',
        'g_analytics_status'             => 'boolean',
        'show_featured_section'          => 'boolean',
        'show_latest_posts'              => 'boolean',
        'pwa_status'                     => 'boolean',
        'registration_system'            => 'boolean',
        'comment_system'                 => 'boolean',
        'comment_approval_system'        => 'boolean',
        'show_post_author'               => 'boolean',
        'show_post_date'                 => 'boolean',
        'show_pageviews'                 => 'boolean',
        'emoji_reactions'                => 'boolean',
        'mail_contact_status'            => 'boolean',
        'content_cache_system'           => 'boolean',
        'app_cache_system'               => 'boolean',
        'auto_cache_refresh'             => 'boolean',
        'email_verification'             => 'boolean',
        'file_manager_show_all_files'    => 'boolean',
        'audio_download_button'          => 'boolean',
        'require_approval_new_posts'     => 'boolean',
        'require_approval_edited_posts'  => 'boolean',
        'show_home_link'                 => 'boolean',
        'show_user_email_on_profile'     => 'boolean',
        'reward_system_status'           => 'boolean',
        'redirect_rss_posts_to_original' => 'boolean',
        'delete_images_with_post'        => 'boolean',
        'sticky_sidebar'                 => 'boolean',
        'bulk_post_upload_for_authors'   => 'boolean',
        'google_maps_status'             => 'boolean',
        'app_icon'                       => 'json',
        'logo'                           => 'json',
        'post_formats'                   => 'json',
        'human_verification'             => 'json',
        'payout_methods'                 => 'json',
        'premium_membership_settings'    => 'json',
        'currency_settings'              => 'json',
        'security_settings'              => 'json',
        'captcha_settings'               => 'json',
        'newsletter_settings'            => 'json',
        'email_settings'                 => 'json',
        'storage_settings'               => 'json',
        'maintenance_mode_settings'      => 'json',
        'social_login_settings'          => 'json',
        'featured_content_settings'      => 'json',
        'sitemap_settings'               => 'json',
        'google_news_settings'           => 'json',
        'auto_post_deletion_settings'    => 'json',
        'file_upload_limits'             => 'json',
        'allowed_file_extensions'        => 'json',
        'ai_writer'                      => 'json',
        'routes'                         => 'json',
        'theme_mode'                     => 'string',
        'timezone'                       => 'string',
        'app_key'                        => 'string',
        'rss_content_type'               => 'string',
        'g_analytics_id'                 => 'string',
        'post_url_structure'             => 'string',
        'mail_contact'                   => 'string',
        'active_storage'                 => 'string',
        'image_file_format'              => 'string',
        'google_maps_api_key'            => 'string',
        'cron_secret_key'                => 'string',
        'version'                        => 'string'
    ];

    /**
     * Handles storage settings
     */
    public function handleStorageSettings(array $formData): void
    {
        $keys = [
            'aws_key', 'aws_secret', 'aws_bucket', 'aws_region',
            'b2_key', 'b2_secret', 'b2_bucket', 'b2_endpoint_url', 'b2_public_url',
            'r2_key', 'r2_secret', 'r2_bucket', 'r2_endpoint_url', 'r2_public_url',
        ];

        $data = [];

        foreach ($keys as $key) {
            $data[$key] = trim((string)($formData[$key] ?? ''));
        }

        $this->active_storage = $formData['storage'] ?? 'local';
        $this->storage_settings = $data;
    }

    /**
     * Handles email settings
     */
    public function handleEmailSettings(array $formData): void
    {
        $keys = [
            'mail_service', 'mail_protocol', 'mail_encryption', 'mail_host', 'mail_port',
            'mail_username', 'mail_password', 'mail_reply_to', 'mail_title',
            'mailgun_api_key', 'mailgun_region', 'mailgun_domain', 'mailgun_sender_email', 'brevo_api_key',
            'email_template'
        ];

        $currentData = $this->email_settings;

        if (is_object($currentData)) {
            $currentData = (array)$currentData;
        } elseif (!is_array($currentData)) {
            $currentData = [];
        }

        foreach ($keys as $key) {
            if (isset($formData[$key])) {
                $value = trim((string)$formData[$key]);

                if ($key === 'mail_port' && $value !== '') {
                    $value = (int)$value;
                }

                $currentData[$key] = $value;
            }
        }

        $this->email_settings = $currentData;
    }

    /**
     * Handles featured content settings
     */
    public function handleFeaturedContentSettings(array $formData): void
    {
        $defaults = [
            'slider_source'        => 'manuel',
            'slider_sorting'       => 'slider_order',
            'slider_duration'      => 10,
            'slider_limit'         => 15,
            'featured_status'      => false,
            'featured_source'      => 'manuel',
            'featured_sorting'     => 'featured_order',
            'featured_duration'    => 10,
            'exclude_slider_posts' => false,
            'recommended_sorting'  => 'by_date',
            'recommended_duration' => 20,
            'recommended_limit'    => 5,
            'br_news_status'       => 1,
            'br_news_source'       => 'manuel',
            'br_news_sorting'      => 'by_date',
            'br_news_duration'     => 5,
            'br_news_limit'        => 15,
        ];

        $data = array_merge($defaults, array_intersect_key($formData, $defaults));

        $intFields = ['featured_duration', 'slider_duration', 'slider_limit', 'recommended_duration', 'recommended_limit', 'br_news_duration', 'br_news_limit'];

        foreach ($intFields as $field) {
            if (((int)$data[$field]) <= 0) {
                $data[$field] = $defaults[$field];
            } else {
                $data[$field] = (int)$data[$field];
            }
        }

        $this->featured_content_settings = $data;
    }

    /**
     * Handles post formats preferences
     */
    public function handlePostFormatsPreferences(array $formData, array $availableFormats): void
    {
        $formats = [];
        foreach ($availableFormats as $format) {
            $inputName = 'post_format_' . $format;
            $formats[$format] = !empty($formData[$inputName]) ? 1 : 0;
        }

        $this->post_formats = $formats;
    }

    /**
     * Handles AI writer settings
     */
    public function handleAiWriterSettings(array $formData): void
    {
        $data = [
            'status'          => $formData['status'] ?? false,
            'provider'        => $formData['provider'] ?? 'chatgpt',
            'chatgpt_api_key' => trim($formData['chatgpt_api_key'] ?? ''),
            'gemini_api_key'  => trim($formData['gemini_api_key'] ?? ''),
            'chatgpt_model'   => $formData['chatgpt_model'] ?? 'gpt-5-nano',
            'gemini_model'    => $formData['gemini_model'] ?? 'gemini-3-pro-preview',
        ];

        $this->ai_writer = $data;
    }

    /**
     * Handles global settings
     */
    public function handleGlobalSettings(array $formData, ?array $newIconData): void
    {
        $this->timezone = $formData['timezone'] ?? 'Europe/Amsterdam';

        if (!empty($newIconData)) {
            $this->app_icon = $newIconData;
        }
    }

    /**
     * Handles logo settings
     */
    public function handleLogoSettings(array $formData, ?array $logoPaths): void
    {
        $data = (array)($this->logo ?? []);

        $paths = $logoPaths ?? [];

        if (isset($paths['logo'])) $data['logo'] = $paths['logo'];
        if (isset($paths['logo_png'])) $data['logo_png'] = $paths['logo_png'];
        if (isset($paths['logo_dark'])) $data['logo_dark'] = $paths['logo_dark'];
        if (isset($paths['logo_dark_png'])) $data['logo_dark_png'] = $paths['logo_dark_png'];

        $inputWidth = (int)($formData['logo_width'] ?? 0);
        $inputHeight = (int)($formData['logo_height'] ?? 0);

        $data['width'] = ($inputWidth >= 10 && $inputWidth <= 300) ? $inputWidth : 160;
        $data['height'] = ($inputHeight >= 10 && $inputHeight <= 300) ? $inputHeight : 60;

        $this->logo = $data;
    }

    /**
     * Handles file upload settings
     */
    public function handleFileUploadSettings(array $formData): void
    {
        $fileFormat = $formData['image_file_format'] ?? '';
        if (!in_array($fileFormat, ['JPG', 'WEBP', 'PNG', 'original'])) {
            $fileFormat = 'JPG';
        }

        $this->image_file_format = $fileFormat;

        $extensionsString = convertTagifyToString($formData['allowed_file_extensions'] ?? '');

        $this->allowed_file_extensions = csvToArray($extensionsString);

        $this->file_upload_limits = [
            'image' => (int)($formData['max_image_file_size'] ?? 20),
            'video' => (int)($formData['max_video_file_size'] ?? 100),
            'audio' => (int)($formData['max_audio_file_size'] ?? 20),
            'file'  => (int)($formData['max_file_size'] ?? 20)
        ];
    }

    /**
     * Handles route settings
     */
    public function handleRouteSettings(array $formData)
    {
        $defaults = getAppDefault('routes') ?? [];

        $dbRoutes = $this->routes;

        if (is_object($dbRoutes)) {
            $dbRoutes = (array)$dbRoutes;
        } elseif (!is_array($dbRoutes)) {
            $dbRoutes = [];
        }

        $finalRoutes = array_merge($defaults, $dbRoutes);

        foreach ($defaults as $key => $defaultValue) {

            if (isset($formData[$key])) {
                $postValue = trim($formData[$key]);

                if ($postValue !== '') {
                    $finalRoutes[$key] = $postValue;
                }
            }
        }

        $this->routes = $finalRoutes;
    }

    /**
     * Handles maintenance mode settings
     */
    public function handleMaintenanceModeSettings(array $formData, ?array $uploadedImage): void
    {
        $data = (array)($this->maintenance_mode_settings ?? []);

        $data['status'] = (int)($formData['status'] ?? 0);
        $data['title'] = $formData['title'] ?? '';
        $data['description'] = $formData['description'] ?? '';

        if (!empty($uploadedImage)) {
            $data['image'] = $uploadedImage['path'];
            $data['storage'] = $uploadedImage['storage'];
        }

        $this->maintenance_mode_settings = $data;
    }

    /**
     * Handles newsletter settings
     */
    public function handleNewsletterSettings(array $formData, ?array $uploadedImage): void
    {
        $data = (array)($this->newsletter_settings ?? []);

        $data['status'] = (int)($formData['status'] ?? 0);
        $data['popup_status'] = (int)($formData['popup_status'] ?? 0);

        if (!empty($uploadedImage)) {
            $data['image'] = $uploadedImage['path'];
            $data['storage'] = $uploadedImage['storage'];
        }

        $this->newsletter_settings = $data;
    }

    /**
     * Handles social login settings
     */
    public function handleSocialLoginSettings(array $formData): void
    {
        $this->social_login_settings = [
            'g_client_id'     => $formData['google_client_id'] ?? '',
            'g_client_secret' => $formData['google_client_secret'] ?? ''
        ];
    }

    /**
     * Handles security settings
     */
    public function handleSecuritySettings(array $formData): void
    {
        $defaults = getAppDefault('security');

        $this->security_settings = [
            'max_login_attempts'          => (int)($formData['max_login_attempts'] ?? $defaults['max_login_attempts']),
            'lockout_time'                => isset($formData['lockout_time']) ? (int)$formData['lockout_time'] * 60 : (int)$defaults['lockout_time'],
            'password_min_length'         => (int)($formData['password_min_length'] ?? $defaults['password_min_length']),
            'password_require_complex'    => (int)($formData['password_require_complex'] ?? 0),
            'spam_protection_mode_post'   => $formData['spam_protection_mode_post'] ?? $defaults['spam_protection_mode_post'],
            'spam_protection_mode_public' => $formData['spam_protection_mode_public'] ?? $defaults['spam_protection_mode_public'],
        ];
    }

    /**
     * Handles captcha settings
     */
    public function handleCaptchaSettings(array $formData): void
    {
        $this->captcha_settings = [
            'status'       => (int)($formData['captcha_status'] ?? 0),
            'provider'     => (string)($formData['captcha_provider'] ?? 'google'),
            'g_site_key'   => trim((string)($formData['captcha_g_site_key'] ?? '')),
            'g_secret_key' => trim((string)($formData['captcha_g_secret_key'] ?? '')),
            'c_site_key'   => trim((string)($formData['captcha_c_site_key'] ?? '')),
            'c_secret_key' => trim((string)($formData['captcha_c_secret_key'] ?? '')),
        ];
    }

    /**
     * Handles reward system settings
     */
    public function handleRewardSystemSettings(array $formData): void
    {
        $methods = getAppDefault('payoutMethods');

        $array = [];
        foreach ($methods as $method) {
            $statusKey = $method . '_status';
            $amountKey = $method . '_min_amount';

            $array[$statusKey] = !empty($formData[$statusKey]) ? 1 : 0;
            $array[$amountKey] = isset($formData[$amountKey]) ? (float)$formData[$amountKey] : 0;
        }

        $this->payout_methods = $array;

        $this->human_verification = [
            'status'     => (int)($formData['human_verification_status'] ?? 0),
            'time_spent' => (int)($formData['time_spent'] ?? 0),
            'mouse'      => (int)($formData['mouse'] ?? 0),
            'scroll'     => (int)($formData['scroll'] ?? 0)
        ];
    }

    /**
     * Handles sitemap settings
     */
    public function handleSitemapSettings(array $formData): void
    {
        $this->sitemap_settings = [
            'frequency'         => $formData['sitemap_frequency'] ?? 'auto',
            'last_modification' => $formData['sitemap_last_modification'] ?? 'auto',
            'priority'          => $formData['sitemap_priority'] ?? 'auto'
        ];
    }

    /**
     * Handles cron secret key
     */
    public function handleCronSecretKey(array $formData): void
    {
        $action = $formData['cronAction'] ?? 'generate';

        if ($action === 'generate') {
            $this->cron_secret_key = bin2hex(random_bytes(16));
        } elseif ($action === 'delete') {
            $this->cron_secret_key = '';
        }
    }

    /**
     * Handles google new settings
     */
    public function handleGoogleNewsSettings(array $formData): void
    {
        $this->google_news_settings = [
            'status'       => (int)($formData['status'] ?? 0),
            'site_name'    => (string)($formData['site_name'] ?? 'My News'),
            'content_type' => (string)($formData['content_type'] ?? 'content'),
            'post_limit'   => (int)($formData['post_limit'] ?? 50)
        ];
    }

    /**
     * Handles auto post deletion settings
     */
    public function handleAutoPostDeletionSettings(array $formData): void
    {
        $allowedMethods = ['all', 'only_rss'];

        $method = $formData['deletion_method'] ?? 'all';

        if (!in_array($method, $allowedMethods, true)) {
            $method = 'all';
        }

        $this->auto_post_deletion_settings = [
            'status'          => (int)($formData['status'] ?? 0),
            'days'            => max(1, (int)($formData['days'] ?? 30)),
            'deletion_method' => $method,
        ];
    }

    /**
     * Handles premium membership settings
     */
    public function handlePremiumMembershipSettings(array $formData): void
    {
        $paywall = $formData['paywall_appearance'] ?? '';
        $paywallAppearance = in_array($paywall, ['strict', 'hard', 'fade']) ? $paywall : 'hard';

        $price = numToDecimal($formData['default_content_price'] ?? 0);

        $subscriptionMode = ($formData['subscription_mode'] ?? '') == 'selective' ? 'selective' : 'all';

        $this->premium_membership_settings = [
            'subscription_status'            => (int)($formData['subscription_status'] ?? 0),
            'subscription_mode'              => $subscriptionMode,
            'exclusive_sale_status'          => (int)($formData['exclusive_sale_status'] ?? 0),
            'default_content_price'          => $price < 0 ? 0.00 : $price,
            'paywall_appearance'             => $paywallAppearance,
            'subscription_button_color'      => $formData['subscription_button_color'] ?? '#18181b',
            'subscription_button_visibility' => (int)($formData['subscription_button_visibility'] ?? 0),
        ];
    }

    /**
     * Handles currency settings
     */
    public function handleCurrencySettings(array $formData, ?array $default): void
    {
        $default = $default ?? ['symbol' => '$', 'direction' => 'left', 'thousand_sep' => ',', 'decimal_sep' => '.'];

        $this->currency_settings = [
            'code'               => $formData['currency_code'] ?? 'USD',
            'symbol'             => $default['symbol'] ?? '$',
            'symbol_direction'   => $formData['symbol_direction'] ?? ($default['direction'] ?? 'left'),
            'thousand_separator' => $formData['thousand_separator'] ?? ($default['thousand_sep'] ?? ','),
            'decimal_separator'  => $formData['decimal_separator'] ?? ($default['decimal_sep'] ?? '.')
        ];
    }
}