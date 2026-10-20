<?php
/**
 * Stayora - SMS & Twilio Configuration
 * Loads settings from environment variables with sensible defaults.
 */

return [
    'default' => getenv('SMS_DRIVER') ?: ($_ENV['SMS_DRIVER'] ?? 'msg91'),

    'drivers' => [
        'msg91' => [
            'auth_key'    => getenv('MSG91_AUTH_KEY') ?: ($_ENV['MSG91_AUTH_KEY'] ?? ''),
            'template_id' => getenv('MSG91_TEMPLATE_ID') ?: ($_ENV['MSG91_TEMPLATE_ID'] ?? ''),
            'sender_id'   => getenv('MSG91_SENDER_ID') ?: ($_ENV['MSG91_SENDER_ID'] ?? 'STYORA'),
            'otp_expiry'  => 10, // Expiry in minutes
            'otp_length'  => 6,
        ],

        'twilio' => [
            'account_sid' => getenv('TWILIO_ACCOUNT_SID') ?: ($_ENV['TWILIO_ACCOUNT_SID'] ?? ''),
            'auth_token'  => getenv('TWILIO_AUTH_TOKEN') ?: ($_ENV['TWILIO_AUTH_TOKEN'] ?? ''),
            'api_key'     => getenv('TWILIO_API_KEY') ?: ($_ENV['TWILIO_API_KEY'] ?? ''),
            'api_secret'  => getenv('TWILIO_API_SECRET') ?: ($_ENV['TWILIO_API_SECRET'] ?? ''),
            'from'        => getenv('TWILIO_FROM_NUMBER') ?: ($_ENV['TWILIO_FROM_NUMBER'] ?? '+17372212163'),
        ],
    ],

    'otp' => [
        'length'          => 6,          // 6 digits OTP
        'expiry_seconds'  => 600,        // 10 minutes expiry
        'cooldown_seconds'=> 60,         // Wait 60 seconds before resend
        'max_resends_hour'=> 5,          // Maximum 5 resends per identifier per hour
        'max_attempts'    => 5,          // Lockout after 5 wrong attempts
        'rate_limit_ip'   => 15,         // Max 15 OTP requests per IP per 15 minutes
    ],
];
