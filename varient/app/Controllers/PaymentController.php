<?php

namespace App\Controllers;

use App\Services\PaymentProcessorService;

class PaymentController extends BaseController
{
    protected PaymentProcessorService $paymentProcessorService;
    protected object $orderModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->paymentProcessorService = new PaymentProcessorService();
        $this->orderModel = model('OrderModel');
    }

    /**
     * Process PayPal payment callback
     *
     * @method POST
     */
    public function paypal()
    {
        if (!authCheck()) {
            return $this->response->setJSON(['status' => 0, 'message' => trans("msg_error")]);
        }

        $orderToken = $this->request->getPost('order_token');
        $paymentId = $this->request->getPost('payment_id');
        $subscriptionId = $this->request->getPost('subscription_id');

        if (empty($orderToken)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Invalid request token.']);
        }

        $order = $this->orderModel->where('order_token', $orderToken)->first();
        if (empty($order)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Order not found.']);
        }

        // Validate order ownership
        if ($order->user_id != user()->id) {
            log_message('warning', '[PayPal Frontend] User ID mismatch for order token: ' . $orderToken);
            return $this->response->setJSON(['status' => 0, 'message' => 'Unauthorized action on this order.']);
        }

        // Prevent duplicate processing if webhook fired first
        if ($order->status === 'completed') {
            return $this->response->setJSON([
                'status'      => 1,
                'redirectUrl' => generateURL('checkout', 'success') . '?order_token=' . $order->order_token
            ]);
        }

        // Delegate processing to the PaymentProcessorService
        $result = $this->paymentProcessorService->processPaypalPayment($order, $paymentId, $subscriptionId);

        if ($result['success']) {
            return $this->response->setJSON([
                'status'      => 1,
                'redirectUrl' => generateURL('checkout', 'success') . "?order_token=" . $order->order_token,
            ]);
        }

        return $this->response->setJSON(['status' => 0, 'message' => $result['message']]);
    }

    /**
     * Process Stripe payment callback
     *
     * @method GET
     */
    public function stripe()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $sessionId = $this->request->getGet('session_id');
        $orderToken = $this->request->getGet('order_token');

        if (empty($sessionId) || empty($orderToken)) {
            return redirect()->to(langBaseUrl());
        }

        $order = $this->orderModel->where('order_token', $orderToken)->first();

        // Verify if order exists and belongs to the current user
        if (empty($order) || $order->user_id != user()->id) {
            setErrorMessage(trans("msg_error"));
            return redirect()->to(langBaseUrl());
        }

        // Prevent duplicate processing if webhook fired first
        if ($order->status === 'completed') {
            return redirect()->to(generateURL('checkout', 'success') . '?order_token=' . $order->order_token);
        }

        // Delegate processing to the PaymentProcessorService
        $result = $this->paymentProcessorService->processStripePayment($order, $sessionId, $this->settings->application_name);

        if ($result['success']) {
            return redirect()->to(generateURL('checkout', 'success') . '?order_token=' . $order->order_token);
        }

        setErrorMessage($result['message']);
        return redirect()->to(generateURL('checkout', 'payment'));
    }

    /**
     * Process Razorpay payment callback
     *
     * @method POST
     */
    public function razorpay()
    {
        if (!authCheck()) {
            return $this->response->setJSON(['status' => 0, 'message' => trans("msg_error")]);
        }

        $orderToken = $this->request->getPost('order_token');
        $razorpayPaymentId = $this->request->getPost('razorpay_payment_id');
        $razorpayOrderId = $this->request->getPost('razorpay_order_id');
        $razorpaySignature = $this->request->getPost('razorpay_signature');

        if (empty($orderToken) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Invalid payment parameters.']);
        }

        $order = $this->orderModel->where('order_token', $orderToken)->first();

        if (empty($order)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Order not found.']);
        }

        // Validate order ownership
        if ($order->user_id != user()->id) {
            log_message('warning', '[Razorpay Frontend] User ID mismatch for order token: ' . $orderToken);
            return $this->response->setJSON(['status' => 0, 'message' => 'Unauthorized action on this order.']);
        }

        // Prevent duplicate processing if webhook fired first
        if ($order->status === 'completed') {
            return $this->response->setJSON([
                'status'      => 1,
                'redirectUrl' => generateURL('checkout', 'success') . '?order_token=' . $order->order_token
            ]);
        }

        // Delegate processing to the PaymentProcessorService
        $result = $this->paymentProcessorService->processRazorpayPayment($order, $razorpayPaymentId, $razorpayOrderId, $razorpaySignature, false);

        if ($result['success']) {
            return $this->response->setJSON([
                'status'      => 1,
                'redirectUrl' => generateURL('checkout', 'success') . '?order_token=' . $order->order_token
            ]);
        }

        return $this->response->setJSON(['status' => 0, 'message' => $result['message']]);
    }

    /**
     * Process Iyzico payment callback
     *
     * @method GET
     */
    public function iyzico()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $token = $this->request->getGet('token');
        $orderToken = $this->request->getGet('order_token');

        if (empty($token) || empty($orderToken)) {
            return redirect()->to(langBaseUrl());
        }

        $order = $this->orderModel->where('order_token', $orderToken)->first();

        // Verify if order exists and belongs to the current user
        if (empty($order) || $order->user_id != user()->id) {
            setErrorMessage(trans("msg_error"));
            return redirect()->to(langBaseUrl());
        }

        // Prevent duplicate processing if the user refreshes the page
        if ($order->status === 'completed') {
            return redirect()->to(generateURL('checkout', 'success') . '?order_token=' . $order->order_token);
        }

        // Delegate processing to the PaymentProcessorService
        $result = $this->paymentProcessorService->processIyzicoPayment($order, $token, $this->settings->application_name);

        if ($result['success']) {
            return redirect()->to(generateURL('checkout', 'success') . '?order_token=' . $order->order_token);
        }

        setErrorMessage($result['message']);
        return redirect()->to(generateURL('checkout', 'payment'));
    }

    /**
     * Process Mercado Pago payment callback
     *
     * @method GET
     */
    public function mercadopago()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $paymentId = $this->request->getGet('payment_id');
        $orderToken = $this->request->getGet('order_token');

        if (empty($paymentId) || empty($orderToken)) {
            return redirect()->to(langBaseUrl());
        }

        $order = $this->orderModel->where('order_token', $orderToken)->first();

        // Verify if order exists and belongs to the current user
        if (empty($order) || $order->user_id != user()->id) {
            setErrorMessage(trans("msg_error"));
            return redirect()->to(langBaseUrl());
        }

        // Prevent duplicate processing if the user refreshes the page
        if ($order->status === 'completed') {
            return redirect()->to(generateURL('checkout', 'success') . '?order_token=' . $order->order_token);
        }

        // Delegate processing to the PaymentProcessorService
        $result = $this->paymentProcessorService->processMercadoPagoPayment($order, $paymentId);

        if ($result['success']) {
            return redirect()->to(generateURL('checkout', 'success') . '?order_token=' . $order->order_token);
        }

        setErrorMessage($result['message']);
        return redirect()->to(generateURL('checkout', 'payment'));
    }

    /**
     * Process PayTabs payment callback
     *
     * @method GET
     */
    public function paytabs()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $orderToken = $this->request->getGet('order_token');
        $postData = $this->request->getGet('post_data');

        if (empty($orderToken) || empty($postData)) {
            return redirect()->to(langBaseUrl());
        }

        $order = $this->orderModel->where('order_token', $orderToken)->first();

        // Verify if order exists and belongs to the current user
        if (empty($order) || $order->user_id != user()->id) {
            setErrorMessage(trans("msg_error"));
            return redirect()->to(langBaseUrl());
        }

        // Prevent duplicate processing if the user refreshes the page
        if ($order->status === 'completed') {
            return redirect()->to(generateURL('checkout', 'success') . '?order_token=' . $order->order_token);
        }

        $decodedData = json_decode(base64_decode($postData), true);
        $tranRef = $decodedData['tranRef'] ?? ($decodedData['tran_ref'] ?? null);

        if (empty($tranRef)) {
            setErrorMessage(trans("payment_option_load_error"));
            return redirect()->to(generateURL('checkout', 'payment'));
        }

        // Delegate processing to the PaymentProcessorService
        $result = $this->paymentProcessorService->processPaytabsPayment($order, $tranRef, $this->settings->application_name);

        if ($result['success']) {
            return redirect()->to(generateURL('checkout', 'success') . '?order_token=' . $order->order_token);
        }

        setErrorMessage($result['message']);
        return redirect()->to(generateURL('checkout', 'payment'));
    }

    /**
     * Invoice for a specific transaction
     */
    public function invoice($id)
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $transactionModel = model('TransactionModel');
        $orderModel = model('OrderModel');

        $transaction = $transactionModel->find($id);
        if (empty($transaction)) {
            return redirect()->to(langBaseUrl());
        }

        if (!isSuperAdmin() && (int)$transaction->user_id !== (int)user()->id) {
            return redirect()->to(langBaseUrl());
        }

        $order = $orderModel->find($transaction->order_id);
        if (empty($order)) {
            return redirect()->to(langBaseUrl());
        }
        return view('common/invoice', [
            'transaction' => $transaction,
            'order'       => $order
        ]);
    }
}