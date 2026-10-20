<?php
/**
 * Database Migration: Complete Platform Settings Management System
 * Adds all required columns to `site_settings` and creates `settings_audit_logs`.
 * Also ensures `settings` table row 1 is synchronized.
 */

require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

$db = Database::connect();
echo "--- [1/3] Checking and migrating columns in `site_settings` ---\n";

// Helper function to check if column exists
function columnExists(mysqli $db, string $table, string $column): bool {
    $res = $db->query("SELECT COUNT(*) as cnt FROM information_schema.COLUMNS 
                       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$column}'");
    $row = $res ? $res->fetch_assoc() : null;
    return !empty($row['cnt']) && (int)$row['cnt'] > 0;
}

// Columns definition to add
$columnsToAdd = [
    // Branding & Theme
    'logo_dark' => "VARCHAR(255) DEFAULT NULL AFTER `logo`",
    'default_property_image' => "VARCHAR(255) DEFAULT NULL AFTER `favicon`",
    'default_avatar_image' => "VARCHAR(255) DEFAULT NULL AFTER `default_property_image`",
    'primary_theme_color' => "VARCHAR(10) DEFAULT '#1D4ED8' AFTER `default_avatar_image`",
    'secondary_theme_color' => "VARCHAR(10) DEFAULT '#0284C7' AFTER `primary_theme_color`",
    'accent_theme_color' => "VARCHAR(10) DEFAULT '#C9A227' AFTER `secondary_theme_color`",

    // Localization & Currency
    'timezone' => "VARCHAR(50) DEFAULT 'Asia/Kolkata' AFTER `accent_theme_color`",
    'date_format' => "VARCHAR(20) DEFAULT 'd M Y' AFTER `timezone`",
    'time_format' => "VARCHAR(10) DEFAULT '12h' AFTER `date_format`",
    'default_currency' => "VARCHAR(10) DEFAULT 'INR' AFTER `time_format`",
    'currency_symbol' => "VARCHAR(10) DEFAULT '₹' AFTER `default_currency`",
    'currency_position' => "ENUM('prefix', 'suffix') DEFAULT 'prefix' AFTER `currency_symbol`",
    'decimal_precision' => "TINYINT(1) DEFAULT 0 AFTER `currency_position`",
    'thousand_separator' => "ENUM('indian', 'standard') DEFAULT 'indian' AFTER `decimal_precision`",
    'default_language' => "VARCHAR(10) DEFAULT 'en' AFTER `thousand_separator`",

    // Operational Booking & Stay Rules
    'default_checkin_time' => "VARCHAR(10) DEFAULT '13:00' AFTER `default_language`",
    'default_checkout_time' => "VARCHAR(10) DEFAULT '11:00' AFTER `default_checkin_time`",
    'min_booking_nights' => "INT(11) DEFAULT 1 AFTER `default_checkout_time`",
    'max_booking_nights' => "INT(11) DEFAULT 30 AFTER `min_booking_nights`",
    'advance_booking_days' => "INT(11) DEFAULT 180 AFTER `max_booking_nights`",
    'cancellation_grace_hours' => "INT(11) DEFAULT 24 AFTER `advance_booking_days`",
    'auto_confirm_bookings' => "TINYINT(1) DEFAULT 0 AFTER `cancellation_grace_hours`",
    'default_booking_status' => "VARCHAR(20) DEFAULT 'pending' AFTER `auto_confirm_bookings`",

    // Property & Guest Management
    'property_approval_required' => "TINYINT(1) DEFAULT 1 AFTER `default_booking_status`",
    'default_property_status' => "VARCHAR(20) DEFAULT 'pending' AFTER `property_approval_required`",
    'max_property_images' => "INT(11) DEFAULT 15 AFTER `default_property_status`",
    'max_upload_size_mb' => "INT(11) DEFAULT 5 AFTER `max_property_images`",
    'guest_registration_enabled' => "TINYINT(1) DEFAULT 1 AFTER `max_upload_size_mb`",
    'guest_id_verification_required' => "TINYINT(1) DEFAULT 1 AFTER `guest_registration_enabled`",
    'default_membership_tier' => "VARCHAR(30) DEFAULT 'Regular' AFTER `guest_id_verification_required`",
    'max_guest_documents' => "INT(11) DEFAULT 5 AFTER `default_membership_tier`",

    // Review Moderation
    'review_auto_approve' => "TINYINT(1) DEFAULT 0 AFTER `max_guest_documents`",
    'review_verified_only' => "TINYINT(1) DEFAULT 1 AFTER `review_auto_approve`",
    'review_min_rating_auto_publish' => "DECIMAL(3,1) DEFAULT 4.5 AFTER `review_verified_only`",
    'review_allow_host_response' => "TINYINT(1) DEFAULT 1 AFTER `review_min_rating_auto_publish`",
    'review_photos_allowed' => "TINYINT(1) DEFAULT 1 AFTER `review_allow_host_response`",

    // Notification Channel & Event Toggles
    'email_notifications_enabled' => "TINYINT(1) DEFAULT 1 AFTER `review_photos_allowed`",
    'whatsapp_notifications_enabled' => "TINYINT(1) DEFAULT 1 AFTER `email_notifications_enabled`",
    'sms_notifications_enabled' => "TINYINT(1) DEFAULT 0 AFTER `whatsapp_notifications_enabled`",
    'notification_events' => "TEXT DEFAULT NULL AFTER `sms_notifications_enabled`",

    // Email Communication
    'email_sender_name' => "VARCHAR(100) DEFAULT 'Stayora Luxury Hospitality' AFTER `notification_events`",
    'email_sender_address' => "VARCHAR(100) DEFAULT 'noreply@stayora.com' AFTER `email_sender_name`",
    'email_reply_to' => "VARCHAR(100) DEFAULT 'concierge@stayora.com' AFTER `email_sender_address`",
    'email_signature_html' => "TEXT DEFAULT NULL AFTER `email_reply_to`",

    // Maintenance Mode & System
    'maintenance_mode' => "TINYINT(1) DEFAULT 0 AFTER `email_signature_html`",
    'maintenance_notice_title' => "VARCHAR(255) DEFAULT 'Scheduled System Maintenance' AFTER `maintenance_mode`",
    'maintenance_notice_message' => "TEXT DEFAULT NULL AFTER `maintenance_notice_title`",
    'maintenance_estimated_end' => "DATETIME DEFAULT NULL AFTER `maintenance_notice_message`",
    'maintenance_allowed_ips' => "TEXT DEFAULT NULL AFTER `maintenance_estimated_end`",
    'default_pagination_size' => "INT(11) DEFAULT 10 AFTER `maintenance_allowed_ips`",

    // Security Policy
    'session_timeout_minutes' => "INT(11) DEFAULT 120 AFTER `default_pagination_size`",
    'max_login_attempts' => "INT(11) DEFAULT 5 AFTER `session_timeout_minutes`",
    'account_lockout_minutes' => "INT(11) DEFAULT 15 AFTER `max_login_attempts`",
    'enforce_single_session' => "TINYINT(1) DEFAULT 1 AFTER `account_lockout_minutes`",
    'default_platform_status' => "VARCHAR(30) DEFAULT 'Active' AFTER `enforce_single_session`",
    'website_url' => "VARCHAR(255) DEFAULT 'https://stayora.com' AFTER `default_platform_status`",
];

$addedCount = 0;
foreach ($columnsToAdd as $col => $definition) {
    if (!columnExists($db, 'site_settings', $col)) {
        $alterSql = "ALTER TABLE `site_settings` ADD COLUMN `{$col}` {$definition}";
        if ($db->query($alterSql)) {
            echo "  + Added column `{$col}` to `site_settings`\n";
            $addedCount++;
        } else {
            echo "  ! Error adding column `{$col}`: " . $db->error . "\n";
        }
    } else {
        echo "  . Column `{$col}` already exists\n";
    }
}
echo "Total new columns added: {$addedCount}\n";

// --- [2/3] Create `settings_audit_logs` table ---
echo "\n--- [2/3] Creating `settings_audit_logs` table ---\n";
$auditTableSql = "CREATE TABLE IF NOT EXISTS `settings_audit_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admin_id` INT(11) DEFAULT NULL,
  `admin_name` VARCHAR(100) DEFAULT NULL,
  `category` VARCHAR(50) NOT NULL,
  `setting_key` VARCHAR(100) NOT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_category` (`category`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($db->query($auditTableSql)) {
    echo "  ✓ `settings_audit_logs` table verified/created successfully.\n";
} else {
    echo "  ! Error creating `settings_audit_logs`: " . $db->error . "\n";
}

// --- [3/3] Synchronize default values and `settings` table ---
echo "\n--- [3/3] Initializing baseline values in `site_settings` and syncing `settings` table ---\n";

// Ensure row 1 exists in `site_settings`
$checkRow = $db->query("SELECT id, mobile_number, whatsapp_number, site_name FROM site_settings WHERE id = 1");
if (!$checkRow || $checkRow->num_rows === 0) {
    $insertSql = "INSERT INTO site_settings (id, site_name, tagline, mobile_number, whatsapp_number, email, address, created_at)
                  VALUES (1, 'Stayora', 'Luxury Farmhouses & Private Villa Escapes', '+91-9111666415', '+91-9111666415', 'contact@stayora.com', 'Stayora Luxury Hospitality, Sector 54 Golf Course Road, Gurugram, India', NOW())";
    $db->query($insertSql);
    echo "  ✓ Created baseline row in `site_settings` (ID: 1)\n";
} else {
    $existing = $checkRow->fetch_assoc();
    echo "  ✓ Existing `site_settings` row 1 confirmed ({$existing['site_name']})\n";

    // Set standard non-null defaults for new fields if currently null
    $db->query("UPDATE site_settings SET 
        primary_theme_color = COALESCE(primary_theme_color, '#1D4ED8'),
        secondary_theme_color = COALESCE(secondary_theme_color, '#0284C7'),
        accent_theme_color = COALESCE(accent_theme_color, '#C9A227'),
        timezone = COALESCE(timezone, 'Asia/Kolkata'),
        date_format = COALESCE(date_format, 'd M Y'),
        time_format = COALESCE(time_format, '12h'),
        default_currency = COALESCE(default_currency, 'INR'),
        currency_symbol = COALESCE(currency_symbol, '₹'),
        currency_position = COALESCE(currency_position, 'prefix'),
        decimal_precision = COALESCE(decimal_precision, 0),
        thousand_separator = COALESCE(thousand_separator, 'indian'),
        default_language = COALESCE(default_language, 'en'),
        default_checkin_time = COALESCE(default_checkin_time, '13:00'),
        default_checkout_time = COALESCE(default_checkout_time, '11:00'),
        min_booking_nights = COALESCE(min_booking_nights, 1),
        max_booking_nights = COALESCE(max_booking_nights, 30),
        advance_booking_days = COALESCE(advance_booking_days, 180),
        cancellation_grace_hours = COALESCE(cancellation_grace_hours, 24),
        auto_confirm_bookings = COALESCE(auto_confirm_bookings, 0),
        default_booking_status = COALESCE(default_booking_status, 'pending'),
        property_approval_required = COALESCE(property_approval_required, 1),
        default_property_status = COALESCE(default_property_status, 'pending'),
        max_property_images = COALESCE(max_property_images, 15),
        max_upload_size_mb = COALESCE(max_upload_size_mb, 5),
        guest_registration_enabled = COALESCE(guest_registration_enabled, 1),
        guest_id_verification_required = COALESCE(guest_id_verification_required, 1),
        default_membership_tier = COALESCE(default_membership_tier, 'Regular'),
        max_guest_documents = COALESCE(max_guest_documents, 5),
        review_auto_approve = COALESCE(review_auto_approve, 0),
        review_verified_only = COALESCE(review_verified_only, 1),
        review_min_rating_auto_publish = COALESCE(review_min_rating_auto_publish, 4.5),
        review_allow_host_response = COALESCE(review_allow_host_response, 1),
        review_photos_allowed = COALESCE(review_photos_allowed, 1),
        email_notifications_enabled = COALESCE(email_notifications_enabled, 1),
        whatsapp_notifications_enabled = COALESCE(whatsapp_notifications_enabled, 1),
        sms_notifications_enabled = COALESCE(sms_notifications_enabled, 0),
        email_sender_name = COALESCE(email_sender_name, 'Stayora Luxury Hospitality'),
        email_sender_address = COALESCE(email_sender_address, 'noreply@stayora.com'),
        email_reply_to = COALESCE(email_reply_to, 'concierge@stayora.com'),
        maintenance_mode = COALESCE(maintenance_mode, 0),
        maintenance_notice_title = COALESCE(maintenance_notice_title, 'Scheduled System Maintenance'),
        maintenance_notice_message = COALESCE(maintenance_notice_message, 'Stayora is currently undergoing scheduled platform upgrades to serve you better. We will be back online shortly.'),
        default_pagination_size = COALESCE(default_pagination_size, 10),
        session_timeout_minutes = COALESCE(session_timeout_minutes, 120),
        max_login_attempts = COALESCE(max_login_attempts, 5),
        account_lockout_minutes = COALESCE(account_lockout_minutes, 15),
        enforce_single_session = COALESCE(enforce_single_session, 1),
        default_platform_status = COALESCE(default_platform_status, 'Active'),
        website_url = COALESCE(website_url, 'https://stayora.com')
        WHERE id = 1");
    echo "  ✓ Default values confirmed and seeded.\n";
}

// Synchronize legacy `settings` table
$settingsCheck = $db->query("SELECT id FROM settings WHERE id = 1");
if (!$settingsCheck || $settingsCheck->num_rows === 0) {
    $syncSql = "INSERT INTO settings (id, admin_phone, admin_whatsapp)
                SELECT 1, mobile_number, whatsapp_number FROM site_settings WHERE id = 1";
    if ($db->query($syncSql)) {
        echo "  ✓ Synchronized row 1 into legacy `settings` table\n";
    }
} else {
    $db->query("UPDATE settings s, site_settings ss 
                SET s.admin_phone = ss.mobile_number, s.admin_whatsapp = ss.whatsapp_number 
                WHERE s.id = 1 AND ss.id = 1");
    echo "  ✓ Synchronized phone & whatsapp into legacy `settings` table row 1\n";
}

echo "\nMigration complete! Platform Settings database is production-ready.\n";
