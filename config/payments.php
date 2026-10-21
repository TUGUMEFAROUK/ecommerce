<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Driver
    |--------------------------------------------------------------------------
    |
    | Which gateway the store is configured to use. "cod" keeps the store
    | cash-on-delivery only. Gateways are also offered at checkout when
    | they are configured and support the store currency, regardless of
    | this default.
    |
    */

    'driver' => env('PAYMENTS_DRIVER', 'cod'),

    'flutterwave' => [
        'base_url' => env('FLW_BASE_URL', 'https://api.flutterwave.com/v3'),

        // Currencies the hosted checkout may charge in. Must include the
        // store's currency (USD) for the option to appear at checkout.
        'currencies' => ['USD'],
    ],

];
