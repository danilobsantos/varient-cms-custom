<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\PaymentService;
use App\Services\UploadService;

class UserController extends BaseAdminController
{
    protected $userModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->userModel = model('UserModel');
    }

    /**
     * Users
     */
    public function users($type = null)
    {
        checkPermission('users');

        $id = (int)inputGet('id');
        $status = inputGet('user_status');
        $emailStatus = inputGet('email_status');
        $rewardSystem = inputGet('reward_system');

        $filters = [
            'id'            => $id,
            'status'        => in_array($status, ['1', '0'], true) ? (int)$status : null,
            'email_status'  => in_array($emailStatus, ['1', '0'], true) ? (int)$emailStatus : null,
            'reward_system' => in_array($rewardSystem, ['1', '0'], true) ? (int)$rewardSystem : null,
            'role_id'       => (int)inputGet('role_id'),
            'q'             => cleanStr(inputGet('q')),
        ];

        $listType = $type === 'premium' ? 'premium' : 'all';

        $data = [
            'title'             => $listType === 'premium' ? trans("premium_users") : trans("users"),
            'panelSettings'     => panelSettings(),
            'users'             => $this->userModel->findAllPaginated($filters, $this->perPage, $listType),
            'roles'             => model('RoleModel')->findAll(100),
            'subscriptionPlans' => model('SubscriptionPlanModel')->getPlans(true),
            'pager'             => $this->userModel->pager,
            'listType'          => $listType
        ];

        return view('admin/user/users', $data);
    }

    /**
     * User
     */
    public function user($id)
    {
        checkPermission('users');

        $user = $this->userModel->find($id);
        if (empty($user)) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('users'));
        }

        $role = model('RoleModel')->find($user->role_id);
        $userSubscription = model('UserSubscriptionModel')->getUserLatestSubscription($user->id);
        $subscriptionPlan = !empty($userSubscription) ? model('SubscriptionPlanModel')->find($userSubscription->plan_id) : null;

        $userPurchaseModel = model('UserPurchaseModel');
        $transactionModel = model('TransactionModel');

        return view('admin/user/user_details', [
            'title'             => trans("user_details"),
            'user'              => $user,
            'role'              => $role,
            'userSubscription'  => $userSubscription,
            'subscriptionPlan'  => $subscriptionPlan,
            'purchasedContents' => $userPurchaseModel->findAllPaginated(10, $user->id),
            'purchasedPager'    => $userPurchaseModel->pager,
            'transactions'      => $transactionModel->findAllPaginated([], 10, $user->id),
            'pager'             => $transactionModel->pager,
            'breadcrumbLink'    => ['label' => trans("users"), 'url' => adminUrl('users')]
        ]);
    }

    /**
     * Add User
     */
    public function addUser()
    {
        checkPermission('users');

        if (isPostMethod()) {

            $authService = new AuthService();
            if (!$this->validate($authService->getRules('signUp'))) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $validatedData = $this->validator->getValidated();
            $roleId = (int)inputPost('role_id');

            $user = new \App\Entities\User();
            $user->fill($validatedData);

            $emailStatus = (int)$this->config->email_verification !== 1;
            $userSlug = $this->userModel->generateUniqueSlug($validatedData['username'] ?? '');

            $user->handleSignUpForm($validatedData, $emailStatus, $userSlug, $roleId);

            if ($this->userModel->trySave($user)) {
                setSuccessMessage("msg_added");
            } else {
                setErrorMessage('msg_error');
            }

            return redirect()->to(getBackUrl());
        }

        return view('admin/user/form_add_user', [
            'title'          => trans("add_user"),
            'roles'          => model('RoleModel')->findAll(100),
            'breadcrumbLink' => ['label' => trans("users"), 'url' => adminUrl('users')]
        ]);
    }

    /**
     * Edit User
     */
    public function editUser($id)
    {
        checkPermission('users');

        $user = $this->userModel->find($id);
        if (!$user) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('users'));
        }

        if ((int)$user->is_super_admin === 1 && !isSuperAdmin()) {
            return redirect()->to(adminUrl('users'));
        }

        return view('admin/user/form_edit_user', [
            'title'          => trans("update_profile"),
            'user'           => $user,
            'roles'          => model('RoleModel')->findAll(100),
            'breadcrumbLink' => ['label' => trans("users"), 'url' => adminUrl('users')]
        ]);
    }

    /**
     * Edit User Post
     */
    public function editUserPost()
    {
        checkPermission('users');

        $postData = $this->request->getPost();

        $id = $postData['id'] ?? '';

        if (empty($id)) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('users'));
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('users'));
        }

        $authService = new AuthService();
        if (!$this->validate($authService->getRules('update'))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $oldUser = clone $user;

        $user->fill($postData);

        // Upload avatar
        $uploadService = new UploadService($this->config);
        $uploadedAvatar = $uploadService->uploadAvatar('avatar');

        $user->handleAdminUpdateForm($postData, $uploadedAvatar);

        $user->handleSocialMediaData($postData, $this->settings);

        if ($this->userModel->trySave($user)) {

            // Delete old avatar
            if (!empty($postData['avatar_remove']) || (!empty($oldUser->avatar) && !empty($uploadedAvatar))) {
                deleteStorageFile($oldUser->avatar, $oldUser->storage_avatar);
            }

            setSuccessMessage("msg_updated");
            return redirect()->to(getBackUrl());
        } else {
            setErrorMessage('msg_error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Change User Role Post
     */
    public function changeUserRole()
    {
        checkPermission('users');

        $postData = $this->request->getPost();

        if (!$this->checkSudoAccess($postData['id'])) {
            setErrorMessage('msg_error');
            return redirect()->to(getBackUrl());
        }

        $this->userModel->changeUserRole($postData) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

        return redirect()->to(getBackUrl());
    }

    /**
     * Assign or Cancel Subscription
     */
    public function assignSubscription()
    {
        checkPermission('users');

        $postData = $this->request->getPost();
        $userId = (int)($postData['user_id'] ?? 0);
        $action = $postData['action'] ?? '';

        if (!$this->checkSudoAccess($userId)) {
            setErrorMessage('msg_error');
            return $action === 'cancel' ? jsonResponse(false) : redirect()->to(getBackUrl());
        }

        if ($action === 'cancel') {
            $userId = (int)$postData['id'] ?? 0;
            if (model('UserSubscriptionModel')->cancelSubscriptionByUserId($userId)) {
                $user = getUserById($userId);
                if (!empty($user)) {
                    // Send email
                    sendPremiumEmail($user->email, 'subscription_cancelled');
                }
                setSuccessMessage('msg_updated');
                return jsonResponse(true);
            }

            setErrorMessage('msg_error');
            return jsonResponse(false);
        }

        $planId = (int)($postData['plan_id'] ?? 0);

        (new PaymentService())->assignManualSubscription($userId, $planId);

        setSuccessMessage('msg_updated');
        return redirect()->to(getBackUrl());
    }

    /**
     * AJAX Endpoint: Toggle Reward System
     *
     * @method POST
     */
    public function toggleRewardSystem()
    {
        checkPermission('users');

        $id = (int)inputPost('id');

        if (!$this->checkSudoAccess($id)) {
            setErrorMessage('msg_error');
            return jsonResponse(false);
        }

        $this->userModel->toggleRewardSystem($id) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

        return jsonResponse(true);
    }

    /**
     * AJAX Endpoint: Verify user email
     *
     * @method POST
     */
    public function verifyUserEmail()
    {
        checkPermission('users');

        $id = (int)inputPost('id');

        if (!$this->checkSudoAccess($id)) {
            setErrorMessage('msg_error');
            return jsonResponse(false);
        }

        $this->userModel->verifyUserEmail($id) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

        return jsonResponse(true);
    }

    /**
     * AJAX Endpoint: Toggle User Ban
     *
     * @method POST
     */
    public function toggleUserBan()
    {
        checkPermission('users');

        $id = (int)inputPost('id');

        if (!$this->checkSudoAccess($id)) {
            setErrorMessage('msg_error');
            return jsonResponse(false);
        }

        $this->userModel->toggleUserBan($id) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

        return jsonResponse(true);
    }

    /**
     * AJAX Endpoint: Delete user(s)
     *
     * @method POST
     */
    public function deleteUser()
    {
        checkPermission('users');

        $idInput = inputPost('id');
        $idsInput = inputPost('ids');

        $userIds = [];
        if (!empty($idInput)) {
            $userIds[] = $idInput;
        }

        if (!empty($idsInput) && is_array($idsInput)) {
            $userIds = array_merge($userIds, $idsInput);
        }

        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($userIds)) {
            setErrorMessage('msg_error');
            return jsonResponse(false);
        }

        foreach ($userIds as $userId) {
            if (!$this->checkSudoAccess($userId)) {
                setErrorMessage('msg_error');
                return jsonResponse(false);
            }
        }

        $result = $this->userModel->deleteUsers($userIds, false);

        if ($result) {
            setSuccessMessage('msg_deleted');
        } else {
            setErrorMessage('msg_error');
        }

        return jsonResponse(true);
    }

    /**
     * Roles Permissions
     */
    public function roles()
    {
        checkPermission('roles_permissions');

        $model = model('RoleModel');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $role = new \App\Entities\Role();
            if (!empty($postData['id'])) {
                $role = $model->find($postData['id']);

                if (!isSuperAdmin() && (int)$role->is_super_admin === 1) {
                    return redirect()->to(adminUrl('roles'));
                }
            }

            $role->fill($postData);

            $role->processForm($postData, $this->activeLanguages);

            if ($model->trySave($role)) {
                setSuccessMessage(!empty($postData['id']) ? 'msg_updated' : 'msg_added');
            } else {
                setErrorMessage('msg_error');
            }

            return redirect()->to(adminUrl('roles'));
        }

        return view('admin/user/roles', [
            'title'         => trans("roles_permissions"),
            'panelSettings' => panelSettings(),
            'roles'         => $model->findAllPaginated($this->perPage),
            'pager'         => $model->pager,
            'badges'        => model('UserBadgeModel')->findAll(),
        ]);
    }

    /**
     * AJAX Endpoint: Delete Role
     *
     * @method POST
     */
    public function deleteRole()
    {
        checkPermission('roles_permissions');

        $id = (int)inputPost('id');

        $model = model('RoleModel');

        $role = $model->find($id);

        if (empty($role)) return jsonResponse(false);

        if ((int)$role->is_default === 1) return jsonResponse(false);

        if (!$model->delete($id)) {
            setErrorMessage('msg_error');
            return jsonResponse(false);
        }

        setSuccessMessage('msg_deleted');
        return jsonResponse(true);
    }

    /**
     * Badges
     */
    public function badges()
    {
        checkPermission('roles_permissions');

        $model = model('UserBadgeModel');

        if (isPostMethod()) {
            $postData = $this->request->getPost();
            $id = (int)($postData['id'] ?? 0);
            $action = $postData['action'] ?? '';

            // Delete
            if ($action === 'delete') {
                $model->delete($id) ? setSuccessMessage('msg_deleted') : setErrorMessage('msg_error');
                return jsonResponse(true);
            }

            // Add or Update
            if ($id > 0) {
                $result = $model->updateBadge($id, $postData);
                $message = 'msg_updated';
            } else {
                $result = $model->createBadge($postData);
                $message = 'msg_added';
            }

            $result ? setSuccessMessage($message) : setErrorMessage('msg_error');

            return redirect()->to(getBackUrl());
        }

        $data = [
            'title'      => trans("badges"),
            'badges'     => $model->orderBy('id DESC')->findAll(),
            'badgeIcons' => getAppDefault('subscriptionBadgeIcons')
        ];

        return view('admin/premium/badges', $data);
    }

    /**
     * Checks if the current admin can modify the target user
     */
    private function checkSudoAccess(int $targetUserId): bool
    {
        if (isSuperAdmin()) {
            return true;
        }

        $targetUser = $this->userModel->find($targetUserId);
        if (!$targetUser) {
            return false;
        }

        if ((int)$targetUser->is_super_admin === 1) {
            return false;
        }

        return true;
    }

}
