<?php
/**
 * Stayora - Mail & SMTP Configuration
 * Placeholders for email service and SMTP credentials.
 */

return [
    'mailer'      => getenv('MAIL_MAILER') ?: ($_ENV['MAIL_MAILER'] ?? 'smtp'),
    'host'        => getenv('MAIL_HOST') ?: ($_ENV['MAIL_HOST'] ?? 'smtp.hostinger.com'),
    'port'        => (int)(getenv('MAIL_PORT') ?: ($_ENV['MAIL_PORT'] ?? 465)),
    'username'    => getenv('MAIL_USERNAME') ?: ($_ENV['MAIL_USERNAME'] ?? 'yourdomain@email.com'),
    'password'    => getenv('MAIL_PASSWORD') ?: ($_ENV['MAIL_PASSWORD'] ?? 'your_email_password'),
    'encryption'  => getenv('MAIL_ENCRYPTION') ?: ($_ENV['MAIL_ENCRYPTION'] ?? 'ssl'), // 'ssl' or 'tls'
    'from' => [
        'address' => getenv('MAIL_FROM_ADDRESS') ?: ($_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@stayora.com'),
        'name'    => getenv('MAIL_FROM_NAME') ?: ($_ENV['MAIL_FROM_NAME'] ?? 'Stayora Support'),
    ],
    'reply_to' => [
        'address' => getenv('MAIL_REPLY_TO_ADDRESS') ?: ($_ENV['MAIL_REPLY_TO_ADDRESS'] ?? 'support@stayora.com'),
        'name'    => getenv('MAIL_REPLY_TO_NAME') ?: ($_ENV['MAIL_REPLY_TO_NAME'] ?? 'Stayora Helpdesk'),
    ],
];
