<?php

namespace App\Libraries;

require_once APPPATH . 'ThirdParty/iyzipay/vendor/autoload.php';

use Exception;
use InvalidArgumentException;
use Iyzipay\Options;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Locale;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use Iyzipay\Model\CheckoutForm;

/**
 * Iyzico Integration Library
 *
 * Handles the creation of the Iyzico checkout form and verifies payment results
 * upon user redirection. Completely decoupled from global session helpers
 */
class Iyzico
{
    /**
     * @var Options Iyzico SDK options object.
     */
    private Options $options;

    /**
     * @var string Application name used for basket item descriptions.
     */
    private string $appName;

    /**
     * Initializes the Iyzico SDK options.
     *
     * @param object $config Gateway configuration object containing keys and mode.
     * @param string $appName Application name for basket item descriptions.
     * @throws InvalidArgumentException
     */
    public function __construct(object $config, string $appName = 'Varient')
    {
        if (empty($config->public_key) || empty($config->secret_key)) {
            throw new InvalidArgumentException('Iyzico Public Key and Secret Key are required.');
        }

        $this->appName = $appName;

        $this->options = new Options();
        $this->options->setApiKey($config->public_key);
        $this->options->setSecretKey($config->secret_key);

        // Map environment to Iyzico Base URLs
        $mode = $config->mode ?? 'sandbox';
        if ($mode === 'sandbox') {
            $this->options->setBaseUrl('https://sandbox-api.iyzipay.com');
        } else {
            $this->options->setBaseUrl('https://api.iyzipay.com');
        }
    }

    /**
     * Initializes the Iyzico Checkout Form request.
     *
     * @param object $order The current order object containing payment details.
     * @param object $customer The customer object containing billing and shipping details.
     * @param string $callbackUrl The URL Iyzico will redirect the user to after payment.
     * @return CheckoutFormInitialize The initialized checkout form object.
     * @throws Exception If Iyzico API returns a failure status.
     */
    public function createCheckoutForm(object $order, object $customer, string $callbackUrl): CheckoutFormInitialize
    {
        // Iyzico expects amounts formatted strictly with 2 decimals
        $price = number_format((float)$order->total_amount, 2, '.', '');
        $currencyCode = strtoupper($order->currency ?? 'TRY');

        $request = new CreateCheckoutFormInitializeRequest();
        $request->setLocale(Locale::TR);
        $request->setConversationId($order->order_token);
        $request->setPrice($price);
        $request->setPaidPrice($price);
        $request->setCurrency($currencyCode);
        $request->setPaymentGroup(PaymentGroup::PRODUCT);
        $request->setCallbackUrl($callbackUrl);
        $request->setEnabledInstallments([2, 3, 6, 9]);

        $request->setBuyer($this->getBuyerData($customer));
        $request->setShippingAddress($this->getAddressData($customer));
        $request->setBillingAddress($this->getAddressData($customer));
        $request->setBasketItems([$this->getBasketItem($order, $price)]);

        $checkoutForm = CheckoutFormInitialize::create($request, $this->options);

        if ($checkoutForm->getStatus() === 'failure') {
            throw new Exception($checkoutForm->getErrorMessage());
        }

        return $checkoutForm;
    }

    /**
     * Retrieves and verifies the payment result from Iyzico.
     *
     * @param string $token The token returned by Iyzico to the callback URL.
     * @return CheckoutForm The retrieved checkout form details.
     * @throws Exception If API request fails.
     */
    public function retrieveCheckoutResult(string $token): CheckoutForm
    {
        $request = new RetrieveCheckoutFormRequest();
        $request->setLocale(Locale::TR);
        $request->setToken($token);

        try {
            return CheckoutForm::retrieve($request, $this->options);
        } catch (Exception $e) {
            log_message('error', '[Iyzico] Checkout result retrieval failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepares the Buyer object using the provided customer data.
     *
     * @param object $customer Customer details.
     * @return Buyer
     */
    private function getBuyerData(object $customer): Buyer
    {
        $request = \Config\Services::request();

        $buyer = new Buyer();
        $buyer->setId((string)($customer->id ?? 'guest_' . uniqid()));
        $buyer->setName($customer->first_name ?? 'Guest');
        $buyer->setSurname($customer->last_name ?? 'User');
        $buyer->setGsmNumber($customer->phone_number ?? '+905555555555');
        $buyer->setEmail($customer->email ?? 'guest@example.com');
        $buyer->setIdentityNumber('11111111111'); // Required by Iyzico, fallback to 1s if unknown
        $buyer->setRegistrationAddress($customer->address ?? 'Not set');

        // Iyzico blocks local IPs, fallback to a standard IP if on localhost
        $buyer->setIp($request->getIPAddress() !== '::1' ? $request->getIPAddress() : '85.34.78.112');

        $buyer->setCity($customer->city ?? 'Not set');
        $buyer->setCountry($customer->country ?? 'Turkey');
        $buyer->setZipCode($customer->zip_code ?? 'Not set');

        return $buyer;
    }

    /**
     * Prepares the Address object using the provided customer data.
     * Required by Iyzico even for digital goods.
     *
     * @param object $customer Customer details.
     * @return Address
     */
    private function getAddressData(object $customer): Address
    {
        $address = new Address();
        $address->setContactName(($customer->first_name ?? 'Guest') . ' ' . ($customer->last_name ?? 'User'));
        $address->setCity($customer->city ?? 'Not set');
        $address->setCountry($customer->country ?? 'Turkey');
        $address->setAddress($customer->address ?? 'Not set');
        $address->setZipCode($customer->zip_code ?? 'Not set');

        return $address;
    }

    /**
     * Prepares the Basket Item.
     * Uses VIRTUAL item type since this is meant for digital content or subscriptions.
     *
     * @param object $order The current order object.
     * @param string $price Formatted price amount.
     * @return BasketItem
     */
    private function getBasketItem(object $order, string $price): BasketItem
    {
        $itemName = $this->appName . ' - ' . ($order->item_type === 'subscription' ? 'Subscription' : 'Content') . ' #' . $order->order_token;

        $basketItem = new BasketItem();
        $basketItem->setId((string)$order->item_id);
        $basketItem->setName($itemName);
        $basketItem->setCategory1($order->item_type === 'subscription' ? 'Subscription' : 'Digital Content');

        // VIRTUAL is crucial here to avoid strict physical shipping checks by banks
        $basketItem->setItemType(BasketItemType::VIRTUAL);
        $basketItem->setPrice($price);

        return $basketItem;
    }
}