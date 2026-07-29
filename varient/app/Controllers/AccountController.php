<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\EmailService;
use App\Services\UploadService;

class AccountController extends BaseController
{
    protected $userModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->userModel = model('UserModel');
    }

    /**
     * Profile Page
     */
    public function profile($slug)
    {
        $slug = cleanSlug($slug ?? '');

        $user = model('UserModel')->findBySlug($slug);
        if (empty($user)) {
            return $this->error404();
        }

        $followerModel = model("FollowerModel");
        $postModel = model("PostModel");
        $userId = (int)$user->id;

        $data = [
            'user'           => $user,
            'following'      => $followerModel->getFollowing($userId),
            'followers'      => $followerModel->getFollowers($userId),
            'isUserFollows'  => authCheck() ? $followerModel->isUserFollows($userId, user()->id) : false,
            'userSession'    => getUserSession(),
            'headerNoMargin' => true,
            'posts'          => $postModel->findAllPaginated((int)$this->activeLang->id, ['user_id' => $userId], $this->postsPerPage),
            'pager'          => $postModel->pager,
            'bodyClass'      => 'body-profile-page'
        ];

        $data = setPageMeta($data['user']->username, $data);

        if (countItems($data['posts']) < 1) {
            $data['robots'] = 'noindex, follow';
        }

        return loadView('profile/profile', $data);
    }

    /**
     * Account Settings
     */
    public function accountSettings()
    {
        $data = setPageMeta(trans("edit_profile"));

        $data['userSession'] = getUserSession();
        $data["user"] = user();
        $data["activeTab"] = 'edit_profile';
        $data['robots'] = 'noindex, nofollow';

        return loadCommonView('account/edit_profile', $data);
    }

    /**
     * Account Settings Post
     */
    public function accountSettingsPost()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $userId = (int)user()->id;

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to(langBaseUrl());
        }

        $submit = inputPost('submit');

        if ($submit === 'send_verification_email') {

            $emailService = new EmailService();
            $emailService->sendVerificationEmail($user);

            setSuccessMessage("email_verification_sent_message");

            return redirect()->to(getBackUrl());
        }

        $authService = new AuthService();
        if (!$this->validate($authService->getRules('update'))) {
            return redirect()->to(getBackUrl())->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = $this->request->getPost();

        // Clean external links
        if (!empty($postData['about_me'])) {
            $postData['about_me'] = cleanExternalLinks($postData['about_me'], 'public');
        }

        $oldUser = clone $user;

        $user->fill($postData);

        $uploadService = new UploadService($this->config);
        $uploadedAvatar = $uploadService->uploadAvatar('file');
        $uploadedCover = $uploadService->uploadProfileCover('image_cover');

        $user->handleUpdateForm($postData, $uploadedAvatar, $uploadedCover);

        if ($this->userModel->trySave($user)) {
            setSuccessMessage("msg_updated");

            // Delete old avatar
            if (!empty($oldUser->avatar) && !empty($uploadedAvatar)) {
                deleteStorageFile($oldUser->avatar, $oldUser->storage_avatar);
            }

            // Delete old cover image
            if (!empty($oldUser->cover_image) && !empty($uploadedCover)) {
                deleteStorageFile($oldUser->cover_image, $oldUser->storage_cover);
            }

            // Send email verification
            if ($oldUser->email !== $user->email) {

                $this->userModel->resetEmailVerification($user->id);

                $emailService = new EmailService();
                $emailService->sendVerificationEmail($user);

                setSuccessMessage("email_verification_sent_message");
            }

        } else {
            setErrorMessage("msg_error");
        }

        return redirect()->to(getBackUrl());
    }

    /**
     * Manage Subscription
     */
    public function manageSubscription()
    {
        if ((int)$this->premiumMembership->subscriptionStatus !== 1) {
            return redirect()->to(langBaseUrl());
        }

        $userId = (int)user()->id;

        $user = $this->userModel->find($userId);
        if (empty($user)) {
            return redirect()->to(langBaseUrl());
        }

        $model = model('UserSubscriptionModel');

        $userSubscription = $model->getUserLatestSubscription($userId);
        $subscriptionPlan = null;

        if (!empty($userSubscription) && !empty($userSubscription->plan_id)) {
            $subscriptionPlan = model('SubscriptionPlanModel')->getActivePlan($userSubscription->plan_id);
        }

        $data = setPageMeta(trans("manage_subscription"));
        $data['userSession'] = getUserSession();
        $data['activeTab'] = 'manage_subscription';
        $data['robots'] = 'noindex, nofollow';
        $data['userSubscription'] = $userSubscription;
        $data['subscriptionPlan'] = $subscriptionPlan;

        return loadCommonView('account/manage_subscription', $data);
    }

    /**
     * Purchased Content
     */
    public function purchasedContent()
    {
        if ((int)$this->premiumMembership->exclusiveSaleStatus !== 1) {
            return redirect()->to(langBaseUrl());
        }

        $userId = (int)user()->id;

        $user = $this->userModel->find($userId);
        if (empty($user)) {
            return redirect()->to(langBaseUrl());
        }

        $model = model('UserPurchaseModel');

        $data = setPageMeta(trans("purchased_content"));
        $data['userSession'] = getUserSession();
        $data['activeTab'] = 'purchased_content';
        $data['robots'] = 'noindex, nofollow';
        $data['contents'] = $model->findAllPaginated($this->tablePerPage, $user->id);
        $data['pager'] = $model->pager;

        return loadCommonView('account/purchased_content', $data);
    }

    /**
     * Billing
     */
    public function billing()
    {
        if ((int)$this->premiumMembership->status !== 1) {
            return redirect()->to(langBaseUrl());
        }

        if (isPostMethod()) {
            $userId = (int)user()->id;

            $user = $this->userModel->find($userId);
            if (!$user) {
                return redirect()->to(langBaseUrl());
            }

            $postData = $this->request->getPost();

            $user->handleBillingInfo($postData);

            $this->userModel->trySave($user) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

            return redirect()->to(getBackUrl());
        }

        $data = setPageMeta(trans("billing_details"));
        $data['userSession'] = getUserSession();
        $data['activeTab'] = 'billing_details';
        $data['robots'] = 'noindex, nofollow';
        $data['billing'] = safeDecode(user()->billing_info ?? '');
        $data['countries'] = model('CountryModel')->getCountries(false);

        return loadCommonView('account/billing', $data);
    }

    /**
     * Payment History
     */
    public function paymentHistory()
    {
        if ((int)$this->premiumMembership->status !== 1) {
            return redirect()->to(langBaseUrl());
        }

        $userId = (int)user()->id;

        $user = $this->userModel->find($userId);
        if (empty($user)) {
            return redirect()->to(langBaseUrl());
        }

        $model = model('TransactionModel');

        if (isPostMethod()) {

            $postData = $this->request->getPost();

            $user->handleBillingInfo($postData);

            $this->userModel->trySave($user) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

            return redirect()->to(getBackUrl());
        }

        $data = setPageMeta(trans("payment_history"));
        $data['userSession'] = getUserSession();
        $data['activeTab'] = 'payment_history';
        $data['robots'] = 'noindex, nofollow';
        $data['transactions'] = $model->findAllPaginated([], $this->tablePerPage, $user->id);
        $data['pager'] = $model->pager;

        return loadCommonView('account/payment_history', $data);
    }

    /**
     * Social Accounts
     */
    public function socialAccounts()
    {
        if (isPostMethod()) {
            $userId = (int)user()->id;

            $user = $this->userModel->find($userId);
            if (!$user) {
                return redirect()->to(langBaseUrl());
            }

            $postData = $this->request->getPost();

            $user->fill($postData);

            $user->handleSocialMediaData($postData, $this->settings);

            $this->userModel->trySave($user) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

            return redirect()->to(getBackUrl());
        }

        $data = setPageMeta(trans("social_accounts"));
        $data['userSession'] = getUserSession();
        $data['activeTab'] = 'social_accounts';
        $data['robots'] = 'noindex, nofollow';

        return loadCommonView('account/social_accounts', $data);
    }

    /**
     * Change Password
     */
    public function changePassword()
    {
        if (isPostMethod()) {

            $user = $this->userModel->find((int)user()->id);
            if (empty($user)) {
                return redirect()->to(getBackUrl());
            }

            $postData = $this->request->getPost();
            $authService = new AuthService();

            $rules = $authService->getRules('passwordChange');

            // Does this user have a password in the DB?
            $verifyOldPass = !empty($user->password);

            // If they don't have a password, remove the 'old_password' rule so validation doesn't fail
            if (!$verifyOldPass && isset($rules['old_password'])) {
                unset($rules['old_password']);
            }

            if (!$this->validate($rules)) {
                return redirect()->to(getBackUrl())->withInput()->with('errors', $this->validator->getErrors());
            }

            // Verify old password only if they actually have one
            if ($verifyOldPass) {
                if (!$authService->verifyUserPassword($postData['old_password'] ?? '', $user->password)) {
                    setErrorMessage("wrong_password_error");
                    return redirect()->to(getBackUrl())->withInput();
                }
            }

            $authToken = generateSecureToken();

            $user->changePassword($postData['password'] ?? '', $authToken);

            $user->auth_token = $authToken;

            if ($this->userModel->trySave($user)) {
                $authService->setAuthSession((int)$user->id, $authToken);
                setSuccessMessage('message_change_password_success');
            } else {
                setErrorMessage('msg_error');
            }

            return redirect()->to(getBackUrl());
        }

        $data = setPageMeta(trans("change_password"));
        $data['userSession'] = getUserSession();
        $data['activeTab'] = 'change_password';
        $data['robots'] = 'noindex, nofollow';

        return loadCommonView('account/change_password', $data);
    }

    /**
     * Delete Account
     */
    public function deleteAccount()
    {
        if (isPostMethod()) {

            $confirm = (bool)inputPost('confirm');
            if (!$confirm) {
                setErrorMessage("msg_error");
                return redirect()->to(getBackUrl())->withInput();
            }

            $authService = new AuthService();

            // Verify password
            if (!$authService->verifyUserPassword(inputPost('password') ?? '', user()->password)) {
                setErrorMessage("wrong_password_error");
                return redirect()->to(getBackUrl())->withInput();
            }

            $this->userModel->deleteUsers([user()->id], true);

            return redirect()->to(langBaseUrl());
        }

        $data = setPageMeta(trans("delete_account"));
        $data['userSession'] = getUserSession();
        $data['activeTab'] = 'delete_account';
        $data['robots'] = 'noindex, nofollow';

        return loadCommonView('account/delete_account', $data);
    }

    /**
     * Toggle Follow Status (Follow / Unfollow)
     */
    public function toggleFollowPost()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $followingId = (int)inputPost('profile_id');
        $followerId = user()->id;

        if ($followingId === $followerId) {
            return redirect()->to(getBackUrl());
        }

        model('FollowerModel')->toggleFollow($followingId, $followerId);

        return redirect()->to(getBackUrl());
    }
}