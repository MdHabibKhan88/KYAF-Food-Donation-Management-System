<?php

// Defaults for values the SRS makes configurable. The system_settings table overrides these at runtime.
return [
    'defaults' => [
        'SESSION_TIMEOUT_MIN' => 30,
        'LOGIN_MAX_ATTEMPTS' => 5,
        'LOGIN_LOCK_MINUTES' => 15,
        'MIN_EXPIRY_LEAD_HOURS' => 2,
        'INVITE_VALID_HOURS' => 72,
        'RESET_VALID_MINUTES' => 30,
        'TEAM_SIZE' => 5,
        'MAX_MEALS' => 100000,
        'CONTACT_PHONE' => '01700-000000',
        'CONTACT_EMAIL' => 'info@kyaf.local',
        'CONTACT_ADDRESS' => 'ঢাকা, বাংলাদেশ',
    ],
];
