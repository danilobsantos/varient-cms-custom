<?php

namespace App\Controllers;

use App\Libraries\GoogleLogin;
use App\Services\AuthService;
use App\Services\CaptchaService;
use App\Services\EmailService;

class AuthController extends BaseController
{
    protected $userModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->userModel = model('UserModel');
    }

    /**
     * Login
     */
    public function login()
    {
        if (authCheck()) {
            return redirect()->to(adminUrl());
        }

        if (isPostMethod()) {
            $isAjax = $this->request->isAJAX();

            if (!$isAjax) {
                $captchaService = new CaptchaService($this->config);
                if (!$captchaService->verify()) {
                    setErrorMessage("bot_verification_failed");
                    return redirect()->to(adminUrl('login'))->withInput();
                }
            }

            $authService = new AuthService();
            if (!$this->validate($authService->getRules('login'))) {
                if ($isAjax) {
                    return jsonResponse([
                        'status'      => 0,
                        'messageHtml' => loadCommonView('partials/_alert', ['alertErrors' => $this->validator->getErrors()])
                    ]);
                }

                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $email = inputPost('email');
            $password = inputPost('password');
            $ipAddress = $this->request->getIPAddress();

            $result = $authService->login($email, $password, $ipAddress);

            if (!empty($result['success'])) {
                if ($isAjax) {
                    return jsonResponse(['status' => 1]);
                }
                return redirect()->to(adminUrl());
            }

            $errorMessage = $this->getLoginErrorMessage($result);

            if ($isAjax) {
                return jsonResponse([
                    'status'      => 0,
                    'messageHtml' => loadCommonView('partials/_alert', ['alertError' => $errorMessage])
                ]);
            }

            setErrorMessage($errorMessage, false);
            return redirect()->to(adminUrl('login'))->withInput();
        }

        $data = setPageMeta(trans("log_in"));
        $data['disableToastr'] = true;
        $data['userSession'] = getUserSession();

        return view('admin/login', $data);
    }

    /**
     * Returns login error message
     */
    private function getLoginErrorMessage(array $result): string
    {
        switch ($result['reason'] ?? null) {
            case 'maintenance_mode':
                return trans('msg_site_under_construction');

            case 'user_banned':
                return trans('message_ban_error');

            case 'email_not_confirmed':
                return trans('email_verification_required');

            case 'email_blacklisted':
                return trans('msg_error_email_blacklisted');

            case 'too_many_attempts':
                $securitySettings = getSecuritySettings();
                $seconds = $securitySettings->lockout_time ?? 300;
                $minutes = ceil($seconds / 60);
                return trans('too_many_login_attempts') . ' (' . trans('wait_time') . ': ' . $minutes . ' ' . trans('minutes') . ')';

            default:
                return trans('log_in_error');
        }
    }

    /**
     * Sign Up
     */
    public function signUp()
    {
        if ((int)$this->config->registration_system !== 1 || authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        if (isPostMethod()) {
            $backUrl = generateURL('sign_up');

            $captchaService = new CaptchaService($this->config);
            if (!$captchaService->verify()) {
                setErrorMessage("bot_verification_failed");
                return redirect()->to($backUrl)->withInput();
            }

            $postData = $this->request->getPost();

            // Security: Check terms confirmed
            if ((int)($postData['terms_conditions'] ?? 0) !== 1) {
                setErrorMessage("must_agree_to_continue");
                return redirect()->to($backUrl)->withInput();
            }


            // Security: Check email blacklisted
            $email = $postData['email'] ?? '';
            if (model('EmailBlacklistModel')->isBlacklisted($email)) {
                setErrorMessage("msg_error_email_blacklisted");
                return redirect()->to($backUrl)->withInput();
            }

            $authService = new AuthService();
            if (!$this->validate($authService->getRules('signUp'))) {
                return redirect()->to($backUrl)->withInput()->with('errors', $this->validator->getErrors());
            }

            $user = new \App\Entities\User();
            $user->fill($postData);

            $emailStatus = (int)$this->config->email_verification !== 1;
            $userSlug = $this->userModel->generateUniqueSlug($postData['username'] ?? '');

            $user->handleSignUpForm($postData, $emailStatus, $userSlug);

            if ($this->userModel->trySave($user)) {

                $insertId = $this->userModel->getInsertID();
                $newUser = $this->userModel->find($insertId);

                // Login user
                if (!empty($newUser)) {
                    $authService->autoLogin($newUser);

                    // Newsletter
                    if ((int)($postData['newsletter'] ?? 0) === 1) {
                        $newsletterModel = model('NewsletterModel');
                        if (!$newsletterModel->findByEmail($newUser->email)) {
                            $newsletterModel->create($newUser->email);
                        }
                    }

                    // Send verification email
                    if ((int)$this->config->email_verification === 1) {

                        $emailService = new EmailService();
                        $emailService->sendVerificationEmail($newUser);

                        setSuccessMessage("msg_sign_up_success_email_verify");
                    } else {
                        setSuccessMessage("msg_sign_up_success");
                    }

                    return redirect()->to(generateURL('account-settings'));
                }

                setErrorMessage('msg_error');
                return redirect()->to($backUrl);
            }

            return redirect()->to($backUrl)->withInput();
        }

        $data = setPageMeta(trans("sign_up"));
        $data['userSession'] = getUserSession();
        $data['termsConditions'] = model('PageModel')->findByDefaultName('terms_conditions', (int)$this->activeLang->id);
        $data['robots'] = 'noindex, nofollow';

        return loadCommonView('auth/sign_up', $data);
    }

    /**
     * Logout
     */
    public function logout()
    {
        $authService = new AuthService();

        $authService->logout();

        if (inputPost('ref') === 'panel') {
            return redirect()->to(adminUrl('login'));
        }

        return redirect()->to(previous_url());
    }

    /**
     * Init Google login
     */
    public function initGoogleLogin()
    {
        try {
            $config = model('ConfigModel')->first();
            $googleLogin = new GoogleLogin($config->social_login_settings ?? null);

            $url = $googleLogin->getLoginUrl();

            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \Exception('Generated URL is invalid or empty.');
            }

            $prevUrl = previous_url();

            if (!str_contains($prevUrl, 'register')) {
                setSession('oauth_back_url', $prevUrl);
            }

            return redirect()->to($url);

        } catch (\Throwable $e) {
            return redirect()->to(langBaseUrl());
        }
    }

    /**
     * Handle Google login callback
     */
    public function handleGoogleCallback()
    {
        $code = $this->request->getGet('code');
        if (!$code) {
            return redirect()->to(langBaseUrl());
        }

        try {
            $config = model('ConfigModel')->first();
            $googleLib = new GoogleLogin($config->social_login_settings ?? null);

            $googleUser = $googleLib->getUserInfo((string)$code);
            if (!$googleUser) {
                throw new \Exception('Unable to fetch Google user data.');
            }

            $authService = new AuthService();

            if ($authService->handleGoogleLogin($googleUser)) {

                $backUrl = getSession('oauth_back_url');
                if (empty($backUrl)) {
                    $backUrl = langBaseUrl();
                }

                removeSession('oauth_back_url');

                return redirect()->to($backUrl);
            }

            throw new \Exception('Login process failed internally.');

        } catch (\Throwable $e) {
            log_message('error', '[GoogleLogin Error] ' . $e->getMessage());
            return redirect()->to(langBaseUrl());
        }
    }

    /**
     * Forgot Password
     */
    public function forgotPassword()
    {
        if (authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        if (isPostMethod()) {

            $email = inputPost('email');

            $user = $this->userModel->findByEmail($email);
            if (!empty($user)) {

                $resetToken = $this->userModel->generateResetToken($user->id, 'reset');

                $emailService = new EmailService();
                $emailService->sendPasswordResetEmail($user->email, $resetToken);

                setSuccessMessage("password_reset_sent_message");

            } else {
                setErrorMessage("email_not_found_message");
            }

            return redirect()->to(generateURL('forgot_password'));
        }

        $data = setPageMeta(trans("forgot_password"));
        $data['robots'] = 'noindex, nofollow';

        return loadCommonView('auth/forgot_password', $data);
    }

    /**
     * Reset Password
     */
    public function resetPassword()
    {
        if (authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        if (isPostMethod()) {

            $authService = new AuthService();
            $validationRules = $authService->getRules('passwordChange');
            if (isset($validationRules['old_password'])) {
                unset($validationRules['old_password']);
            }
            if (!$this->validate($validationRules)) {
                return redirect()->to(getBackUrl())->withInput()->with('errors', $this->validator->getErrors());
            }

            $token = inputPost('token');
            $user = $this->userModel->findByResetToken($token);
            if (empty($user)) {
                return redirect()->to(getBackUrl());
            }
            $authToken = generateSecureToken();

            $user->changePassword(inputPost('password') ?? '', $authToken, true);

            if ($this->userModel->trySave($user)) {
                session()->setFlashdata('pass_reset_completed', true);
                setSuccessMessage('password_reset_success');
            } else {
                setErrorMessage('msg_error');
            }

            return redirect()->to(getBackUrl());
        }

        // Show the page directly if the password has already been reset
        if (session()->getFlashdata('pass_reset_completed')) {
            $data = setPageMeta(trans("password_reset_title"), []);
            $data['passResetCompleted'] = true;
            return loadCommonView('auth/reset_password', $data);
        }

        $token = cleanStr(inputGet('token') ?? '');

        if (empty($token) || !str_starts_with($token, 'reset_')) {
            return redirect()->to(langBaseUrl());
        }

        $user = $this->userModel->findByResetToken($token);

        if (!$user || isTokenExpired($user->reset_token_created_at, 2)) {
            return redirect()->to(langBaseUrl());
        }

        $data = [
            'user'  => $user,
            'token' => $token
        ];

        $data = setPageMeta(trans("password_reset_title"), $data);
        $data['robots'] = 'noindex, nofollow';

        return loadCommonView('auth/reset_password', $data);
    }

    /**
     * Verify Email
     */
    public function verifyEmail()
    {
        $token = cleanStr(inputGet('token') ?? '');

        if (!str_starts_with($token, 'verify_')) {
            return redirect()->to(langBaseUrl());
        }

        $user = $this->userModel->findByResetToken($token);


        if (!$user || (int)$user->email_status === 1 || isTokenExpired($user->reset_token_created_at, 24)) {
            return redirect()->to(langBaseUrl());
        }

        $status = $this->userModel->verifyUserEmail($user->id);

        $data = [
            'user'     => $user,
            'status'   => $status,
            'pageType' => 'email_verification',
        ];

        $data = setPageMeta(trans("email_verification"), $data);
        $data['robots'] = 'noindex, nofollow';

        return loadCommonView('auth/verification', $data);
    }

}
