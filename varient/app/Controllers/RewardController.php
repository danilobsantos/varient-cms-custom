<?php

namespace App\Controllers;

class RewardController extends BaseAdminController
{
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        checkPermission('reward_system');
    }

    /**
     * Reward System
     */
    public function rewardSystem()
    {
        $configModel = model('ConfigModel');

        $allowedTabs = ['payout_methods', 'currency', 'human_verification'];

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $config = $configModel->first();
            $config->fill($postData);

            if ($postData['submit'] == 'details') {
                $config->handleRewardSystemSettings($postData);
            }

            $configModel->trySave($config) ? setSuccessMessage("msg_updated") : setErrorMessage("msg_error");

            $activeTab = in_array($postData['active_tab'] ?? '', $allowedTabs, true) ? $postData['active_tab'] : $allowedTabs[0];

            return redirect()->to(adminUrl("reward-system?active_tab={$activeTab}"));
        }

        $activeTab = in_array(inputGet('active_tab'), $allowedTabs, true) ? inputGet('active_tab') : $allowedTabs[0];

        return view('admin/reward/reward_system', [
            'title'       => trans("reward_system"),
            'userSession' => getUserSession(),
            'activeTab'   => $activeTab
        ]);
    }

    /**
     * Earnings
     */
    public function earnings()
    {
        $model = model('UserModel');

        $filters = [
            'q' => cleanStr(inputGet('q')),
        ];

        return view('admin/reward/earnings', [
            'title'         => trans("earnings"),
            'panelSettings' => panelSettings(),
            'earnings'      => $model->findAllEarningsPaginated($filters, $this->perPage),
            'pager'         => $model->pager
        ]);
    }

    /**
     * Payouts
     */
    public function payouts()
    {
        $model = model('PayoutModel');

        $filters = ['q' => cleanStr(inputGet('q'))];

        return view('admin/reward/payouts', [
            'title'         => trans("payouts"),
            'panelSettings' => panelSettings(),
            'payouts'       => $model->findAllPaginated($filters, $this->perPage),
            'pager'         => $model->pager
        ]);
    }

    /**
     * Add Payout
     */
    public function addPayout()
    {
        if (isPostMethod()) {
            $postData = $this->request->getPost();
            $userId = $postData['user_id'] ?? null;
            $amount = $postData['amount'] ?? null;
            $backUrl = adminUrl('reward-system/payouts/add-payout');

            if (empty($userId) || empty($amount)) {
                setErrorMessage("invalid_attempt");
                return redirect()->to($backUrl);
            }

            $user = model('UserModel')->find($userId);
            if (empty($user) || $user->balance < $amount) {
                setErrorMessage("insufficient_balance");
                return redirect()->to($backUrl);
            }

            $model = model('PayoutModel');

            if ($model->create($postData, $user, true)) {
                setSuccessMessage("msg_payout_added");
            } else {
                setErrorMessage("msg_error");
            }

            return redirect()->to($backUrl);
        }

        return view('admin/reward/form_payout', [
            'title' => trans("add_payout")
        ]);
    }

    /**
     * AJAX Endpoint: Approve Payout
     *
     * @method POST
     */
    public function approvePayout()
    {
        $id = (int)inputPost('id');

        if (model('PayoutModel')->approve($id)) {
            setSuccessMessage("msg_updated");
        } else {
            setErrorMessage("msg_error");
        }

        return jsonResponse(true);
    }

    /**
     * AJAX Endpoint: Delete Payout
     *
     * @method POST
     */
    public function deletePayout()
    {
        $id = (int)inputPost('id');

        if (model('PayoutModel')->delete($id)) {
            setSuccessMessage("msg_deleted");
        } else {
            setErrorMessage("msg_error");
        }

        return jsonResponse(true);
    }

    /**
     * Pageviews
     */
    public function pageviews()
    {
        $model = model('PageViewsModel');

        $filters = ['q' => cleanStr(inputGet('q'))];

        return view('admin/reward/pageviews', [
            'title'         => trans("pageviews"),
            'panelSettings' => panelSettings(),
            'pageviews'     => $model->findAllPaginated($filters, $this->perPage),
            'pager'         => $model->pager
        ]);
    }
}
