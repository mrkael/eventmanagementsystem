<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Account Registration
    |--------------------------------------------------------------------------
    |
    | Enterprise users should normally be provisioned by an administrator.
    | Public event participant registration belongs to a later module and should
    | not create back-office accounts by default.
    |
    */
    'self_registration_enabled' => env('EMS_SELF_REGISTRATION_ENABLED', false),

    'confirmation_email_test_mode' => env('EMS_CONFIRMATION_EMAIL_TEST_MODE', true),
    'confirmation_email_test_recipient' => env('EMS_CONFIRMATION_EMAIL_TEST_RECIPIENT', 'ntwtestai@yopmail.com'),
];
