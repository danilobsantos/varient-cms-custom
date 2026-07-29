<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'id'                     => 'integer',
        'role_id'                => 'integer',
        'total_pageviews'        => 'integer',
        'balance'                => 'double',
        'email_status'           => 'boolean',
        'status'                 => 'boolean',
        'show_email_on_profile'  => 'boolean',
        'show_rss_feeds'         => 'boolean',
        'reward_system'          => 'boolean',
        'social_media_data'      => 'json',
        'payout_methods'         => 'json',
        'billing_info'           => 'json',
        'username'               => 'string',
        'slug'                   => 'string',
        'email'                  => 'string',
        'auth_token'             => 'string',
        'password'               => 'string',
        'oauth_provider'         => 'string',
        'oauth_uid'              => 'string',
        'avatar'                 => 'string',
        'storage_avatar'         => 'string',
        'cover_image'            => 'string',
        'storage_cover'          => 'string',
        'about_me'               => 'string',
        'reset_token'            => 'string',
        'reset_token_created_at' => 'datetime',
        'last_login'             => 'datetime',
        'last_seen'              => 'datetime',
        'updated_at'             => 'datetime',
        'created_at'             => 'datetime'
    ];

    /**
     * Attributes that should be mutated to Time objects
     */
    protected $dates = [
        'reset_token_created_at',
        'last_login',
        'last_seen',
        'updated_at',
        'created_at'
    ];

    protected const DEFAULT_ROLE_ID = 3;

    /**
     * Handles sign up form data
     */
    public function handleSignUpForm(array $formData, bool $emailStatus, ?string $userSlug, ?int $roleId = null): void
    {
        $this->slug = $userSlug;
        $this->password = password_hash($formData['password'], PASSWORD_DEFAULT);
        $this->token = generateSecureToken();
        $this->role_id = !empty($roleId) ? (int)$roleId : self::DEFAULT_ROLE_ID;
        $this->status = 1;
        $this->email_status = $emailStatus;
        $this->last_seen = dateTimeNow();
    }

    /**
     * Handles update form data
     */
    public function handleUpdateForm(array $formData, ?array $uploadedAvatar, ?array $uploadedCover): void
    {
        if (!empty($uploadedAvatar)) {
            $this->avatar = $uploadedAvatar['path'] ?? '';
            $this->storage_avatar = $uploadedAvatar['storage'] ?? '';
        }

        if (!empty($uploadedCover)) {
            $this->cover_image = $uploadedCover['path'] ?? '';
            $this->storage_cover = $uploadedCover['storage'] ?? '';
        }
    }

    /**
     * Handles admin update form data
     */
    public function handleAdminUpdateForm(array $formData, ?array $uploadedAvatar): void
    {
        if (!empty($uploadedAvatar)) {
            $this->avatar = $uploadedAvatar['path'] ?? '';
            $this->storage_avatar = $uploadedAvatar['storage'] ?? '';
        }

        // Check if avatar removed
        if (!empty($formData['avatar_remove'])) {
            $this->avatar = '';
            $this->storage_avatar = '';
        }
    }

    /**
     * Handles social media data
     */
    public function handleSocialMediaData(array $formData, object $settings): void
    {
        $allowedKeys = (array)($settings->profile_social_media ?? []);

        $socialInput = $formData['social_media_data'] ?? [];

        $cleanData = [];

        if (!empty($socialInput) && is_array($socialInput) && !empty($allowedKeys)) {

            foreach ($socialInput as $key => $url) {

                if (in_array($key, $allowedKeys) && is_string($url)) {
                    $url = trim($url);

                    if ($url !== '') {
                        $cleanData[$key] = addHttpsToUrl($url);
                    }
                }
            }
        }

        $this->social_media_data = $cleanData;
    }

    /**
     * Hashes and assignes the given plain password
     */
    public function changePassword(string $plainPassword, string $authToken, bool $expireResetToken = false): void
    {
        $this->password = password_hash($plainPassword, PASSWORD_DEFAULT);
        $this->token = $authToken;

        if ($expireResetToken) {
            $this->reset_token = null;
        }
    }

    /**
     * Handles payout methods
     */
    public function handlePayoutMethods(array $formData, string $submit): void
    {
        $prevData = is_object($this->payout_methods ?? '') ? $this->payout_methods : new \stdClass();

        if ($submit === '') {
            return;
        }

        $map = [
            'paypal'  => [
                'paypal_email',
            ],
            'bitcoin' => [
                'btc_address',
            ],
            'iban'    => [
                'iban_full_name',
                'iban_country',
                'iban_bank_name',
                'iban_number',
            ],
            'swift'   => [
                'swift_full_name',
                'swift_address',
                'swift_state',
                'swift_city',
                'swift_postcode',
                'swift_country',
                'swift_bank_account_holder_name',
                'swift_iban',
                'swift_code',
                'swift_bank_name',
                'swift_bank_branch_city',
                'swift_bank_branch_country',
            ],
        ];

        if (!isset($map[$submit])) {
            return;
        }

        foreach ($map[$submit] as $field) {
            $prevData->$field = $formData[$field] ?? '';
        }

        $this->payout_methods = $prevData;
    }

    /**
     * Handles billing information
     */
    public function handleBillingInfo(array $formData): void
    {
        $this->billing_info = [
            'full_name'       => $formData['full_name'] ?? '',
            'email'           => $formData['email'] ?? '',
            'address'         => $formData['address'] ?? '',
            'country_id'      => $formData['country_id'] ?? '',
            'city_state'      => $formData['city_state'] ?? '',
            'zip_postal_code' => $formData['zip_postal_code'] ?? '',
            'company_name'    => $formData['company_name'] ?? '',
            'tax_vat_number'  => $formData['tax_vat_number'] ?? ''
        ];
    }

}