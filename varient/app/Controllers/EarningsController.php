<?php

namespace App\Controllers;

class EarningsController extends BaseAdminController
{
    protected $payoutModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        if ((int)($this->config->reward_system_status ?? 0) !== 1 || (int)(user()->reward_system ?? 0) !== 1) {
            redirect()->to(langBaseUrl())->send();
            exit;
        }

        $this->payoutModel = model('PayoutModel');
        $this->perPage = 8;
    }

    /**
     * Author Earnings Page
     */
    public function authorEarnings()
    {
        $userId = user()->id;

        $user = model('UserModel')->find($userId);
        if (empty($user)) {
            return redirect()->to(adminUrl());
        }

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $submit = $postData['submit'] ?? '';

            if ($submit === 'new_payout') {
                return $this->handleNewPayoutRequest($postData, $user);
            } else {
                return $this->handlePayoutMethods($postData, $submit, $user);
            }

            return redirect()->to(adminUrl('author-earnings'));
        }

        return view('admin/author-earnings/author_earnings', [
            'title'                => trans("my_earnings"),
            'userSession'          => getUserSession(),
            'userPayoutMethods'    => $user->payout_methods,
            'payouts'              => $this->payoutModel->findAllPaginated(['user_id' => $userId], $this->perPage),
            'pager'                => $this->payoutModel->pager
        ]);
    }

    /**
     * Handles Payout Methods
     */
    private function handlePayoutMethods($postData, $submit, $user)
    {
        $user->handlePayoutMethods($postData, $submit);

        model('UserModel')->trySave($user) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

        $backUrl = adminUrl('author-earnings');
        if (in_array($submit, ['paypal', 'bitcoin', 'iban', 'swift'])) {
            $backUrl = $backUrl . '?method=' . $submit;
        }

        return redirect()->to($backUrl);
    }

    /**
     * Handles New Payout Request
     */
    private function handleNewPayoutRequest($postData, $user)
    {
        $amount = (double)($postData['amount'] ?? 0);
        $postData['amount'] = $amount;

        $payoutMethod = $postData['payout_method'] ?? '';
        $backUrl = adminUrl('author-earnings');

        if (!empty($this->payoutModel->getUserPayouts($user->id, true))) {
            setErrorMessage("active_payment_request_error");
            return redirect()->to($backUrl);
        }

        $validMethods = ['paypal', 'bitcoin', 'iban', 'swift'];

        if (!in_array($payoutMethod, $validMethods)) {
            setErrorMessage("msg_error");
            return redirect()->to($backUrl);
        }

        $minLimit = payoutMethod($payoutMethod . '_min_amount');

        if ($amount <= 0) {
            setErrorMessage("msg_error");
            return redirect()->to($backUrl);
        }

        if ($amount < $minLimit) {
            setErrorMessage("invalid_withdrawal_amount");
            return redirect()->to($backUrl);
        }

        if ($amount > $user->balance) {
            setErrorMessage("invalid_withdrawal_amount");
            return redirect()->to($backUrl);
        }

        if ($this->payoutModel->create($postData, $user, false)) {
            setSuccessMessage("msg_request_sent");
        } else {
            setErrorMessage("msg_error");
        }

        return redirect()->to($backUrl);
    }
}