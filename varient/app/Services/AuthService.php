<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Class AuthService
 *
 * Handles user authentication, registration, session management,
 * and security-related logic including validation rules.
 */
class AuthService
{
    /**
     * Active user status ID
     */
    protected const STATUS_ACTIVE = 1;

    /**
     * Verified email status ID
     */
    protected const EMAIL_VERIFIED = 1;

    /**
     * Super admin role ID
     */
    protected const SUPER_ADMIN_ROLE = 1;

    /**
     * Default role ID assigned to new registered users
     */
    protected const DEFAULT_ROLE_ID = 3;

    /**
     * Global application configuration settings
     * * @var object|null
     */
    protected $config;

    /**
     * Application security settings
     * * @var object|null
     */
    protected $securitySettings;

    /**
     * User model instance for database operations
     * * @var UserModel
     */
    protected $userModel;

    /**
     * Validation rules for the login scenario
     * * @var array
     */
    protected $loginRules = [
        'email'    => [
            'label' => 'trans:email',
            'rules' => 'required|valid_email|max_length[254]',
        ],
        'password' => [
            'label' => 'trans:password',
            'rules' => 'required|max_length[255]',
        ],
    ];

    /**
     * Validation rules for the sign up scenario (frontend)
     * * @var array
     */
    protected $signUpRules = [
        'username'         => [
            'label' => 'trans:username',
            'rules' => 'required|min_length[4]|max_length[50]|is_unique[users.username]',
        ],
        'email'            => [
            'label' => 'trans:email',
            'rules' => 'required|valid_email|max_length[254]|is_unique[users.email]',
        ],
        'password'         => [
            'label' => 'trans:password',
            'rules' => 'required|min_length[4]|max_length[255]',
        ],
        'confirm_password' => [
            'label' => 'trans:confirm_password',
            'rules' => 'required|matches[password]',
        ],
        'terms_conditions' => [
            'label' => 'trans:terms_conditions',
            'rules' => 'required',
        ],
    ];

    /**
     * Validation rules for the update scenario
     * * @var array
     */
    protected $updateRules = [
        // 'id' rule is necessary for the {id} placeholder in is_unique
        'id'       => 'permit_empty|is_natural_no_zero',
        'username' => [
            'label' => 'trans:username',
            'rules' => 'required|min_length[4]|max_length[50]|is_unique[users.username,id,{id}]',
        ],
        'slug'     => [
            'label' => 'trans:slug',
            'rules' => 'required|alpha_dash|max_length[255]|is_unique[users.slug,id,{id}]',
        ],
        'email'    => [
            'label' => 'trans:email',
            'rules' => 'required|valid_email|max_length[254]|is_unique[users.email,id,{id}]',
        ]
    ];

    /**
     * Validation rules for the password change scenario
     * * @var array
     */
    protected $passwordChangeRules = [
        'old_password'     => [
            'label' => 'trans:old_password',
            'rules' => 'required|min_length[4]|max_length[255]',
        ],
        'password'         => [
            'label' => 'trans:password',
            'rules' => 'required|min_length[4]|max_length[255]',
        ],
        'confirm_password' => [
            'label' => 'trans:confirm_password',
            'rules' => 'required|matches[password]',
        ],
    ];

    /**
     * AuthService constructor
     */
    public function __construct()
    {
        $this->config = getContextValue('config');
        $this->securitySettings = getSecuritySettings();
        $this->userModel = model('UserModel');
    }

    /**
     * Retrieves the validation rules for a specific scenario
     *
     * @param string $scenario The name of the rule set (e.g., 'login', 'signUp').
     * @return array The processed, ready-to-use validation rules.
     * @throws \Exception If the scenario is not found.
     */
    public function getRules(string $scenario): array
    {
        $rules = [];

        // Assign base rules based on the scenario
        switch ($scenario) {
            case 'login':
                $rules = $this->loginRules;
                break;
            case 'signUp':
                $rules = $this->signUpRules;
                break;
            case 'update':
                $rules = $this->updateRules;
                break;
            case 'passwordChange':
                $rules = $this->passwordChangeRules;
                break;
            default:
                throw new \Exception("Invalid validation scenario provided: $scenario");
        }

        // Dynamically override password rules for registration and password changes
        if (in_array($scenario, ['signUp', 'passwordChange'])) {

            $minLength = $this->securitySettings->password_min_length ?? 4;

            // Base password rules string
            $passwordRuleString = "required|min_length[{$minLength}]|max_length[255]";
            $passwordErrors = [];

            // Check Complexity Setting
            if (!empty($this->securitySettings->password_require_complex)) {
                $passwordRuleString .= "|regex_match[/^(?=.*\d)(?=.*[^\w\s]).+$/]";
                $passwordErrors['regex_match'] = trans("password_complexity_special_error", "raw");
            }

            // Apply rules to the array
            $rules['password'] = [
                'label' => 'trans:password',
                'rules' => $passwordRuleString,
            ];

            if (!empty($passwordErrors)) {
                $rules['password']['errors'] = $passwordErrors;
            }
        }

        // Process Translations
        foreach ($rules as $field => &$settings) {
            if (is_array($settings) && isset($settings['label']) && is_string($settings['label'])) {
                if (strpos($settings['label'], 'trans:') === 0) {
                    $settings['label'] = trans(substr($settings['label'], 6));
                }
            }
        }

        // Break the reference to prevent bugs later in the code
        unset($settings);

        return $rules;
    }

    /**
     * Attempts to log in a user with the given credentials
     *
     * @param string $email The user's email address.
     * @param string $password The user's plain-text password.
     * @param string $ipAddress The user's IP address for rate limiting.
     * @return array Returns an array containing the success status and reason/userId.
     */
    public function login(string $email, string $password, string $ipAddress): array
    {
        $maxAttempts = (int)($this->securitySettings->max_login_attempts ?? 5);
        $lockoutTime = (int)($this->securitySettings->lockout_time ?? 300);

        $attemptModel = model('LoginAttemptModel');
        $attemptRecord = $attemptModel->getAttempt($ipAddress);
        $lockoutThreshold = date('Y-m-d H:i:s', time() - $lockoutTime);

        if ($attemptRecord && $attemptRecord->last_attempt > $lockoutThreshold && $attemptRecord->attempts >= $maxAttempts) {
            return ['success' => false, 'reason' => 'too_many_attempts'];
        }

        // Security: Check if email is blacklisted
        if (model('EmailBlacklistModel')->isBlacklisted($email)) {
            return ['success' => false, 'reason' => 'email_blacklisted'];
        }

        $user = $this->userModel->where('email', $email)->first();

        // Verify User and Password
        if (!empty($user) && $this->verifyUserPassword($password, $user->password)) {
            $attemptModel->clearAttempts($ipAddress);
            $updateData = [];

            if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $updateData['password'] = $newHash;
            }

            if (empty($user->auth_token)) {
                $newToken = generateSecureToken();
                $user->auth_token = $newToken;
                $updateData['auth_token'] = $newToken;
            }

            $updateData['last_login'] = dateTimeNow();

            $role = model('RoleModel')->where('id', $user->role_id)->first();

            if (!empty($role) && (int)$role->is_super_admin !== self::SUPER_ADMIN_ROLE) {
                $maintenanceMode = $this->config->maintenance_mode_settings ?? null;

                if (!empty($maintenanceMode) && !empty($maintenanceMode->status)) {
                    return ['success' => false, 'reason' => 'maintenance_mode'];
                }
            }

            if ((int)$user->status !== self::STATUS_ACTIVE) {
                return ['success' => false, 'reason' => 'user_banned'];
            }

            if (!empty($updateData)) {
                $this->userModel->update($user->id, $updateData);
            }

            $this->setAuthSession((int)$user->id, $user->auth_token);

            return ['success' => true, 'userId' => $user->id];
        }

        $attemptModel->recordAttempt($ipAddress, $email, $lockoutTime);

        return ['success' => false, 'reason' => 'invalid_credentials'];
    }

    /**
     * Automatically logs in a user without requiring a password
     *
     * @param object $user The user object
     * @return void
     */
    public function autoLogin(object $user): void
    {
        $updateData = [];

        // Ensure the user has a secure auth token for the session
        if (empty($user->auth_token)) {
            $newToken = generateSecureToken();
            $user->auth_token = $newToken;
            $updateData['auth_token'] = $newToken;
        }

        // Update the last login timestamp
        $updateData['last_login'] = dateTimeNow();

        // Execute updates if there is any data to update
        if (!empty($updateData)) {
            $this->userModel->update($user->id, $updateData);
        }

        // Set the secure session
        $this->setAuthSession((int)$user->id, $user->auth_token);
    }

    /**
     * Handles the entire Google Login flow (Login/Merge/Register)
     *
     * @param object|null $googleUser The user object retrieved from Google OAuth.
     * @return bool True if login/registration is successful, false otherwise.
     */
    public function handleGoogleLogin(?object $googleUser): bool
    {
        if (empty($googleUser) || empty($googleUser->id) || empty($googleUser->email)) {
            log_message('error', '[AuthService] GoogleUser data is empty/null, or object is missing id or email.');
            return false;
        }

        $avatarUrl = $googleUser->picture ?? '';
        if (!empty($avatarUrl)) {
            $avatarUrl = preg_replace('/=s\d+(-c)?$/', '=s400', $avatarUrl);
        }

        // Try Login by Google ID
        $user = $this->userModel->where('oauth_uid', $googleUser->id)->first();
        if ($user) {
            if (empty($user->auth_token)) {
                $user->auth_token = generateSecureToken();
                $this->userModel->update($user->id, ['auth_token' => $user->auth_token]);
            }

            $this->setAuthSession($user->id, $user->auth_token);
            return true;
        }

        // Try Account Merge by Email
        $user = $this->userModel->where('email', $googleUser->email)->first();
        if ($user) {
            $updateData = [
                'oauth_provider' => 'google',
                'oauth_uid'      => $googleUser->id
            ];

            $token = $user->auth_token;
            if (empty($token)) {
                $token = generateSecureToken();
                $updateData['auth_token'] = $token;
            }

            $this->userModel->update($user->id, $updateData);

            $this->processAvatarDownload($user->id, $avatarUrl);

            $this->setAuthSession($user->id, $token);
            return true;
        }

        $dateNow = dateTimeNow();

        // Register New User
        $newUser = new \App\Entities\User();
        $newUser->username = $this->userModel->generateUniqueUsername($googleUser->name ?? '');
        $newUser->slug = $this->userModel->generateUniqueSlug($newUser->username);
        $newUser->password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $newUser->email = $googleUser->email;
        $newUser->email_status = self::EMAIL_VERIFIED;
        $newUser->auth_token = generateSecureToken();
        $newUser->oauth_provider = 'google';
        $newUser->oauth_uid = $googleUser->id;
        $newUser->avatar = '';
        $newUser->role_id = self::DEFAULT_ROLE_ID;
        $newUser->last_login = $dateNow;
        $newUser->last_seen = $dateNow;
        $newUser->created_at = $dateNow;

        if ($this->userModel->trySave($newUser)) {

            $newUserId = $this->userModel->getInsertID();

            $this->processAvatarDownload($newUserId, $avatarUrl);

            $this->setAuthSession($newUserId, $newUser->auth_token);
            return true;
        }

        return false;
    }

    /**
     * Helper: Downloads the Google avatar and assigns it to the user
     *
     * @param int $userId The ID of the user.
     * @param string $url The Google profile image URL.
     * @return void
     */
    private function processAvatarDownload(int $userId, string $url): void
    {
        if (empty($url)) {
            return;
        }

        try {
            $downloadService = new \App\Services\DownloadService();
            $tempFileName = $downloadService->downloadImage($url);

            if (!$tempFileName) {
                return;
            }

            $uploadService = new \App\Services\UploadService($this->config);
            $uploadedAvatar = $uploadService->uploadAvatar(null, $tempFileName);

            if (!empty($uploadedAvatar['path'])) {
                $this->userModel->update($userId, [
                    'avatar'         => $uploadedAvatar['path'],
                    'storage_avatar' => $uploadedAvatar['storage'] ?? 'local',
                ]);
            }

        } catch (\Throwable $e) {
            log_message('error', '[AuthService] Avatar process failed for User ID ' . $userId . ': ' . $e->getMessage());
        }
    }

    /**
     * Regenerates the session and sets authenticated user data
     *
     * @param int $userId The ID of the authenticated user.
     * @param string $authToken The user's secure authentication token.
     * @return void
     */
    public function setAuthSession(int $userId, string $authToken): void
    {
        $session = session();

        // Regenerate ID to prevent session fixation attacks
        $session->regenerate(true);

        $session->set([
            'auth_user_id' => $userId,
            'auth_token'   => $authToken
        ]);
    }

    /**
     * Verifies a plain password against a hashed password
     *
     * @param string|null $plainPassword The password entered by the user.
     * @param string|null $hashedPassword The hashed password stored in the database.
     * @return bool True if passwords match, false otherwise.
     */
    public function verifyUserPassword(?string $plainPassword, ?string $hashedPassword): bool
    {
        if (empty($plainPassword) || empty($hashedPassword)) {
            return false;
        }

        return (bool)password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Logs the user out by destroying their session data
     *
     * @return void
     */
    public function logout(): void
    {
        session()->remove(['auth_user_id', 'auth_token']);
        session()->destroy();
    }
}