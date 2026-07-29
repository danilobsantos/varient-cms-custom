<?php

namespace App\Models;

use App\Entities\Settings;

class SettingsModel extends BaseModel
{
    protected $table = 'settings';
    protected $allowedFields = ['lang_id', 'site_title', 'home_title', 'site_description', 'keywords', 'application_name', 'primary_font', 'secondary_font', 'content_font',
        'font_size', 'site_social_media', 'profile_social_media', 'optional_url_button_name', 'about_footer', 'contact_text', 'contact_address', 'contact_email', 'contact_phone',
        'invoice_details', 'copyright', 'cookies_warning', 'cookies_warning_data'];
    protected $returnType = Settings::class;
    protected $cacheGroup = 'app';

    protected $validationRules = [
        'application_name' => [
            'label' => 'trans:app_name',
            'rules' => 'required|max_length[255]',
        ]
    ];

    /**
     * Find settings by language
     */
    public function findByLangId(int $langId): ?object
    {
        return $this->where('lang_id', $langId)
            ->first();
    }

    /**
     * Duplicate default settings for a new language
     */
    public function addDefaultSettings(int $langId): void
    {
        $appName = 'App';
        $defaultLangId = (int)$this->config->site_lang;

        $defaultSettings = $this->where('lang_id', $defaultLangId)->get()->getRow();
        if (empty($defaultSettings)) {
            $defaultSettings = $this->orderBy('id')->limit(1)->get()->getRow();
        }

        if (!empty($defaultSettings) && !empty($defaultSettings->application_name)) {
            $appName = $defaultSettings->application_name;
        }

        $this->insert([
            'lang_id'                  => $langId,
            'home_title'               => 'Index',
            'application_name'         => $appName,
            'primary_font'             => 13,
            'secondary_font'           => 14,
            'content_font'             => 14,
            'font_size'                => '{"fs_base":15,"fs_post-title":36,"fs_post-summary":18,"fs_content":17,"fs_main-nav":15,"fs_tiny":11,"fs_xs":12,"fs_sm":13,"fs_md":14,"fs_lg":16,"fs_xl":18,"fs_title-xs":15,"fs_title-sm":16,"fs_title-md":18,"fs_title-lg":20,"fs_title-xl":22,"fs_title-2xl":24,"fs_title-3xl":26,"fs_title-4xl":30,"fs_title-5xl":32}',
            'optional_url_button_name' => 'Click Here To See More',
            'copyright'                => 'Copyright 2026 - All Rights Reserved.',
            'cookies_warning_data'     => '{"title":"We Value Your Privacy","desc":"We use strictly necessary cookies to ensure our website functions correctly and securely. With your consent, we also use Google Analytics to analyze traffic and improve your user experience. For more information, please read our","url_title":"Privacy Policy","url":""}',
        ]);
    }
}