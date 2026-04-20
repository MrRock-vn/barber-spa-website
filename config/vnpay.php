<?php
return [
    'version'      => env('VNPAY_VERSION', '2.1.0'),
    'tmn_code'     => env('VNPAY_TMN_CODE', ''),
    'hash_secret'  => env('VNPAY_HASH_SECRET', ''),
    'pay_url'      => env('VNPAY_PAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'return_url'   => env('VNPAY_RETURN_URL', ''),
    'ipn_url'      => env('VNPAY_IPN_URL', ''),
];