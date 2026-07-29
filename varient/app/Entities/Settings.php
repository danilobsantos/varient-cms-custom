<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Settings extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'id'                       => 'integer',
        'lang_id'                  => 'integer',
        'primary_font'             => 'integer',
        'secondary_font'           => 'integer',
        'content_font'             => 'integer',
        'cookies_warning'          => 'boolean',
        'site_social_media'        => 'json',
        'profile_social_media'     => 'json',
        'font_size'                => 'json',
        'cookies_warning_data'     => 'json',
        'social_media_data'        => 'json',
        'site_title'               => 'string',
        'home_title'               => 'string',
        'site_description'         => 'string',
        'keywords'                 => 'string',
        'application_name'         => 'string',
        'optional_url_button_name' => 'string',
        'about_footer'             => 'string',
        'contact_text'             => 'string',
        'contact_address'          => 'string',
        'contact_email'            => 'string',
        'contact_phone'            => 'string',
        'copyright'                => 'string',
        'invoice_details'          => 'json',
    ];

    /**
     * Handles localized settings
     */
    public function handleLocalizedSettings(array $formData): void
    {
        // Format keywords
        $this->keywords = convertTagifyToString($formData['keywords'] ?? '');
    }

    /**
     * Handles social media
     */
    public function handleSocialMedia(array $formData): void
    {
        $definedPlatforms = getSocialPlatforms();

        $siteLinksRaw = $formData['site_social_media'] ?? [];
        $cleanSiteLinks = [];

        if (is_array($siteLinksRaw)) {
            foreach ($siteLinksRaw as $key => $url) {
                if (isset($definedPlatforms[$key]) && !empty(trim($url))) {
                    $cleanSiteLinks[$key] = addHttpsToUrl(trim($url));
                }
            }
        }

        $profileOptionsRaw = $formData['profile_social_media'] ?? [];
        $allowedProfileKeys = [];

        if (is_array($profileOptionsRaw)) {
            foreach ($profileOptionsRaw as $key => $value) {
                if (isset($definedPlatforms[$key])) {
                    $allowedProfileKeys[] = $key;
                }
            }
        }

        $this->site_social_media = $cleanSiteLinks;
        $this->profile_social_media = $allowedProfileKeys;
    }

    /**
     * Handles font settings
     */
    public function handleFontSettings(array $formData): void
    {
        $this->primary_font = $formData['primary_font'] ?? 1;
        $this->secondary_font = $formData['secondary_font'] ?? 1;
        $this->content_font = $formData['content_font'] ?? 1;
    }

    /**
     * Handles and sanitizes font size settings from the form
     */
    public function handleFontSizeSettings(array $formData): void
    {
        $configGroups = getAppDefault('fontSize');

        $flatDefaults = [];
        foreach ($configGroups as $groupItems) {
            foreach ($groupItems as $key => $val) {
                $flatDefaults[$key] = $val;
            }
        }

        $postedSizes = $formData['size'] ?? [];
        $cleanSettings = [];

        foreach ($flatDefaults as $key => $defaultVal) {
            $dbKey = 'fs_' . $key;

            if (isset($postedSizes[$dbKey]) && is_numeric($postedSizes[$dbKey]) && (int)$postedSizes[$dbKey] > 0) {
                $cleanSettings[$dbKey] = (int)$postedSizes[$dbKey];
            } else {
                $cleanSettings[$dbKey] = $defaultVal;
            }
        }

        $this->font_size = $cleanSettings;
    }

    /**
     * Handles cookies warning settings
     */
    public function handleCookiesWarningSettings(array $formData): void
    {
        $this->cookies_warning_data = [
            'title'     => $formData['cookies_warning_title'] ?? '',
            'desc'      => $formData['cookies_warning_desc'] ?? '',
            'url_title' => $formData['cookies_warning_url_title'] ?? '',
            'url'       => $formData['cookies_warning_url'] ?? '',
        ];
    }

    /**
     * Handles invoice details settings
     */
    public function handleInvoiceDetails(array $formData, int $langId): void
    {
        $companyName = $formData['invoice_details'][$langId]['company_name'] ?? '';
        $details = $formData['invoice_details'][$langId]['details'] ?? '';
        $invoiceFooterNote = $formData['invoice_details'][$langId]['invoice_footer_note'] ?? '';

        $this->invoice_details = [
            'company_name'        => $companyName,
            'details'             => trim($details),
            'invoice_footer_note' => trim($invoiceFooterNote)
        ];
    }

}