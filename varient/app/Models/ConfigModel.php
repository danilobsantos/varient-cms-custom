<?php

namespace App\Models;

use App\Entities\AppConfig;

class ConfigModel extends BaseModel
{
    protected $table = 'config';
    protected $allowedFields = ['show_home_link', 'active_storage', 'storage_settings', 'currency_settings', 'captcha_settings', 'premium_membership_settings', 'security_settings', 'newsletter_settings', 'email_settings', 'email_verification', 'mail_contact_status', 'mail_contact',
        'reward_system_status', 'reward_amount', 'human_verification', 'payout_methods', 'adsense_activation_code', 'g_analytics_status', 'g_analytics_id', 'sitemap_settings',
        'content_cache_system', 'app_cache_system', 'auto_cache_refresh', 'content_cache_ttl', 'multilingual_system', 'registration_system', 'delete_images_with_post', 'audio_download_button',
        'show_user_email_on_profile', 'pwa_status', 'show_rss', 'rss_content_type', 'rss_max_posts_per_feed', 'file_manager_show_all_files', 'show_featured_section', 'show_latest_posts', 'post_url_structure', 'bulk_post_upload_for_authors', 'comment_system', 'comment_approval_system', 'emoji_reactions',
        'show_post_author', 'show_post_date', 'show_pageviews', 'require_approval_new_posts', 'require_approval_edited_posts', 'redirect_rss_posts_to_original', 'post_formats', 'google_news_settings',
        'ai_writer', 'image_file_format', 'allowed_file_extensions', 'file_upload_limits', 'popular_posts_limit', 'related_posts_limit', 'auto_post_deletion_settings', 'app_icon', 'app_key', 'timezone', 'logo',
        'logo_size', 'custom_header_codes', 'custom_footer_codes', 'site_lang', 'routes', 'maintenance_mode_settings', 'social_login_settings', 'featured_content_settings',
        'pagination_per_page', 'google_maps_status', 'google_maps_api_key', 'cron_secret_key', 'sticky_sidebar', 'invoice_prefix', 'last_system_cron_run'
    ];
    protected $returnType = AppConfig::class;
    protected $cacheGroup = 'app';

    public const CONFIG_ID = 1;

    /**
     * Update global configurations
     */
    public function updateConfig(array $data)
    {
        return $this->update(self::CONFIG_ID, $data);
    }

    /**
     * Retrieves the application's unique secret key
     */
    public function getAppKey(): string
    {
        if (!empty($this->config->app_key)) {
            return (string)$this->config->app_key;
        }

        $candidateKey = bin2hex(random_bytes(16));
        $id = $this->config->id;

        // Atomic Update
        $this->where('id', $id)
            ->groupStart()
            ->where('app_key', '')->orWhere('app_key', null)
            ->groupEnd()
            ->set(['app_key' => $candidateKey])
            ->update();

        // Check Who Won the Race
        if ($this->db->affectedRows() > 0) {
            $this->config->app_key = $candidateKey;
            return $candidateKey;
        }

        $freshConfig = $this->asArray()->find($id);

        // Update in-memory object to be in sync
        $winnerKey = (string)($freshConfig['app_key'] ?? '');
        $this->config->app_key = $winnerKey;

        return $winnerKey;
    }
}