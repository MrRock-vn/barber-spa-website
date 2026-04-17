<?php
return [
    'partner_code' => 'MOMOBKUN20180529',
    'access_key'   => 'klm05TvNBzhg7h7j',
    'secret_key'   => 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa',

    'endpoint'     => 'https://test-payment.momo.vn/v2/gateway/api/create',

    'redirect_url' => 'https://greedless-napped-greyhound.ngrok-free.dev/barber-spa/payment/momo-return',
    'ipn_url'      => 'https://greedless-napped-greyhound.ngrok-free.dev/barber-spa/payment/momo-ipn',

    'request_type' => 'captureWallet',
    'lang'         => 'vi',
];