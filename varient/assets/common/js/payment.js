document.addEventListener('DOMContentLoaded', function () {
    // Check for PayPal configuration and initialize if it exists
    if (typeof configPaypal !== 'undefined') {
        initializePayPal(configPaypal);
    }

    // Check for Razorpay  configuration and initialize if it exists
    if (typeof configRazorpay !== 'undefined') {
        initializeRazorpay(configRazorpay);
    }

    // Check for Mercado Pago configuration and initialize if it exists
    if (typeof configMercadoPago !== 'undefined') {
        initializeMercadoPago(configMercadoPago);
    }
});

/**
 * Initializes all PayPal related logic
 *
 * @param {object} config The configuration object passed from the server
 */
function initializePayPal(config) {
    const pageLoader = document.getElementById('page-loader');
    const paymentContainer = document.getElementById('payment-container');
    const processingLoader = document.getElementById('paypal-processing-loader');
    const buttonContainer = document.getElementById('paypal-button-container');

    const displayError = function (message) {
        let errorDiv = document.getElementById('paypal-error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'paypal-error-message';
            errorDiv.className = 'alert alert-danger mt-3';
            buttonContainer.parentNode.insertBefore(errorDiv, buttonContainer);
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    };

    if (typeof paypal === 'undefined') {
        pageLoader.classList.add('d-none');
        paymentContainer.classList.remove('d-none');
        displayError('PayPal SDK could not be loaded. Please check your connection and try again.');
        return;
    }

    let paypalConfig = {
        onApprove: function (data, actions) {
            processingLoader.style.display = 'flex';
            buttonContainer.style.display = 'none';

            let errorDiv = document.getElementById('paypal-error-message');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }

            let postData = {
                order_token: config.orderToken
            };

            if (config.isSubscription === 1) {
                postData.subscription_id = data.subscriptionID;
            } else {
                postData.payment_id = data.orderID;
            }

            $.ajax({
                type: 'POST',
                url: config.paymentPostUrl,
                data: postData,
                success: function (response) {
                    if (response.status == 1 && response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    } else {
                        displayError(response.message || 'There was an issue processing your transaction on our server.');
                        processingLoader.style.display = 'none';
                        buttonContainer.style.display = 'block';
                    }
                },
                error: function () {
                    displayError('A communication error occurred. Please check your internet connection.');
                    processingLoader.style.display = 'none';
                    buttonContainer.style.display = 'block';
                }
            });
        },

        onError: function (err) {
            displayError('An error occurred during the PayPal transaction. Please try again.');
            processingLoader.style.display = 'none';
            buttonContainer.style.display = 'block';
        }
    };

    if (config.isSubscription === 1) {
        paypalConfig.createSubscription = function (data, actions) {
            return actions.subscription.create({
                'plan_id': config.planId,
                'custom_id': config.orderToken
            });
        };
    } else {
        paypalConfig.createOrder = function (data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: config.totalAmount,
                        currency_code: config.currencyCode
                    },
                    custom_id: config.orderToken
                }]
            });
        };
    }

    paypal.Buttons(paypalConfig).render('#paypal-button-container').then(() => {
        pageLoader.classList.add('d-none');
        paymentContainer.classList.remove('d-none');
    }).catch((err) => {
        pageLoader.classList.add('d-none');
        paymentContainer.classList.remove('d-none');
        displayError('Could not display PayPal buttons. Please refresh the page.');
    });
}

/**
 * Initializes all Razorpay related logic
 *
 * @param {object} config The configuration object passed from the server
 */
function initializeRazorpay(config) {
    const razorpayButton = document.getElementById('rzp-button1');
    const paymentLoader = document.getElementById('razorpay-processing-loader');

    const displayError = function (message) {
        let errorDiv = document.getElementById('razorpay-error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'razorpay-error-message';
            errorDiv.className = 'alert alert-danger mt-3';
            if (razorpayButton && razorpayButton.parentNode) {
                razorpayButton.parentNode.insertBefore(errorDiv, razorpayButton);
            }
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    };

    if (!razorpayButton) {
        console.error('Razorpay button not found.');
        return;
    }

    if (typeof Razorpay === 'undefined') {
        displayError("Razorpay SDK could not be loaded. Please check your connection and try again.");
        return;
    }

    const options = {
        key: config.key,
        amount: config.amount,
        currency: config.currency,
        name: config.name,
        description: config.description,
        image: config.image,
        order_id: config.razorpayOrderId,
        handler: function (response) {
            razorpayButton.classList.add('d-none');
            if (paymentLoader) {
                paymentLoader.classList.remove('d-none');
            }

            const csrfTokenName = document.querySelector('meta[name="X-CSRF-TOKEN-NAME"]')?.getAttribute('content') || 'csrf_token_name';
            const csrfTokenValue = document.querySelector('meta[name="X-CSRF-TOKEN"]')?.getAttribute('content') || document.querySelector('input[name="csrf_token_name"]')?.value;

            let requestData = {
                'razorpay_payment_id': response.razorpay_payment_id,
                'razorpay_order_id': response.razorpay_order_id,
                'razorpay_signature': response.razorpay_signature,
                'order_token': config.orderToken
            };

            if (csrfTokenValue) {
                requestData[csrfTokenName] = csrfTokenValue;
            }

            $.ajax({
                type: 'POST',
                url: config.paymentPostUrl,
                data: requestData,
                dataType: 'json',
                success: function (res) {
                    if (res.status == 1 && res.redirectUrl) {
                        window.location.href = res.redirectUrl;
                    } else {
                        if (paymentLoader) paymentLoader.classList.add('d-none');
                        razorpayButton.classList.remove('d-none');
                        displayError(res.message || 'An unknown error occurred. Please try again.');
                    }
                },
                error: function () {
                    if (paymentLoader) paymentLoader.classList.add('d-none');
                    razorpayButton.classList.remove('d-none');
                    displayError('A communication error occurred. Please check your internet connection and try again.');
                }
            });
        },
        theme: {
            color: "#528FF0"
        }
    };

    const rzp = new Razorpay(options);

    razorpayButton.addEventListener('click', function (e) {
        e.preventDefault();
        let errorDiv = document.getElementById('razorpay-error-message');
        if (errorDiv) errorDiv.style.display = 'none';
        rzp.open();
    });

    rzp.on('payment.failed', function (response) {
        displayError(`Payment Failed: ${response.error.description}`);
    });
}

/**
 * Initializes and renders the Mercado Pago Wallet Brick
 *
 * @param {object} config - Configuration object for the brick
 */
function initializeMercadoPago(config) {
    if (!config || !config.publicKey || !config.preferenceId || !config.locale || !config.containerId) {
        console.error('Mercado Pago Brick Error: Missing required configuration.');
        const container = document.getElementById(config.containerId);
        if (container) {
            container.innerText = 'Error loading payment button. Configuration is missing.';
        }
        return;
    }

    try {
        const mp = new MercadoPago(config.publicKey, {
            locale: config.locale
        });

        const bricksBuilder = mp.bricks();

        const renderWalletBrick = async (builder) => {
            const settings = {
                initialization: {
                    preferenceId: config.preferenceId,
                },
                customization: {
                    texts: {
                        'action': 'pay'
                    },
                },
            };
            window.walletBrickContainer = await builder.create('wallet', config.containerId, settings);
        };

        renderWalletBrick(bricksBuilder);

    } catch (error) {
        console.error('Error initializing Mercado Pago SDK:', error);
        const container = document.getElementById(config.containerId);
        if (container) {
            container.innerText = 'Could not load payment button. Please refresh the page and try again.';
        }
    }
}