<?php

return [
    'pay_now' => 'Pay now',
    'try_again' => 'Try again',
    'contact_us' => 'Contact us',
    'order_saved' => 'Your order :order_no is saved. Payment can be completed below.',
    'processing' => 'Processing payment…',

    'errors' => [
        'gateway_not_configured' => 'Online payment isn\'t available right now. Your order is saved — our team will contact you shortly.',
        'gateway_unreachable' => 'We couldn\'t connect to the payment service. Please try again in a moment.',
        'payment_cancelled' => 'Payment was cancelled. Your order is still waiting — you can pay whenever you\'re ready.',
        'payment_failed' => 'Your payment didn\'t go through. Please check your card or UPI details and try again.',
        'signature_invalid' => 'We couldn\'t confirm your payment. If money was deducted, it will be refunded automatically — please try again.',
        'amount_mismatch' => 'There was a problem matching your payment amount. Your order is safe — please try paying again.',
        'currency_mismatch' => 'There was a problem matching your payment currency. Your order is safe — please try paying again.',
        'order_already_paid' => 'Good news — this order is already paid!',
        'order_not_payable' => 'This order can\'t be paid online right now. Please contact us for help.',
        'duplicate_payment' => 'This payment has already been recorded.',
        'theme_not_supported' => 'Online payment is not available for this storefront.',
        'network_error' => 'Something went wrong on our end. Your order is saved — please try again.',
        'session_expired' => 'Your session expired. Please refresh the page and try again.',
        'rate_limited' => 'Too many attempts. Please wait a minute and try again.',
        'unknown' => 'Something unexpected happened. Your order is saved — please try again or contact us.',
    ],

    'success' => [
        'verified' => 'Payment successful! We are processing your order.',
    ],
];
