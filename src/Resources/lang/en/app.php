<?php

return [
    'stripe' => [
        'info' => 'Stripe payment method secure and fast payment option.',
        'name' => 'Stripe',
        'payment' => 'Stripe Payment Gateway',
        'title' => 'Debit or Credit Card',
        'description' => 'Stripe',

        'system' => [
            'title' => 'Title',
            'description' => 'Description',
            'image' => 'Logo',
            'status' => 'Status',
            'client-secret' => 'Client Secret',
            'client-secret-info' => 'Add your secret key here',
            'sandbox-client-secret' => 'Sandbox Client Secret',
            'sandbox-client-secret-info' => 'Add your sandbox secret key here',
            'sandbox' => 'Sandbox',
            'sandbox-users' => 'Sandbox users',
            'sandbox-users-info' => 'enter User Ids in comma separated which are allowed to use sandbox mode in production for debugging.',
        ],
        'shop' => [
            'payment_failed' => 'Your payment couldn’t be processed. Please try again or contact support if the issue persists.',
        ]
    ],

    'resources' => [
        'title' => 'Pay',
    ],
];
