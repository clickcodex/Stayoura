<?php
namespace App\Models\Admin;

use App\Config\Database;
use App\Helpers\SiteHelper;
use mysqli;

/**
 * Platform Settings Management Model
 * Centralized repository for platform configuration, validation, audit trail,
 * default fallbacks, and category grouping.
 */
class PlatformSettingsModel {
    protected mysqli $db;

    public function __construct(?mysqli $db = null) {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Category field definitions and mappings
     */
    public static function getCategories(): array {
        return [
            'general' => [
                'label'       => 'General Settings',
                'icon'        => 'tune',
                'description' => 'Platform identity, legal business details, and public presence.',
                'fields'      => ['site_name', 'tagline', 'website_url', 'footer_text', 'default_platform_status']
            ],
            'branding' => [
                'label'       => 'Branding & Appearance',
                'icon'        => 'palette',
                'description' => 'Logos, favicon, default placeholders, and luxury theme palette.',
                'fields'      => ['logo', 'logo_dark', 'favicon', 'default_property_image', 'default_avatar_image', 'primary_theme_color', 'secondary_theme_color', 'accent_theme_color']
            ],
            'contact' => [
                'label'       => 'Contact Information',
                'icon'        => 'contact_support',
                'description' => 'Customer concierge contacts, physical address, and social links.',
                'fields'      => ['email', 'mobile_number', 'whatsapp_number', 'address', 'google_map_link', 'facebook_link', 'instagram_link', 'youtube_link', 'twitter_link']
            ],
            'localization' => [
                'label'       => 'Regional & Currency',
                'icon'        => 'language',
                'description' => 'Timezones, date & time styling, and currency display preferences.',
                'fields'      => ['timezone', 'date_format', 'time_format', 'default_currency', 'currency_symbol', 'currency_position', 'decimal_precision', 'thousand_separator', 'default_language']
            ],
            'bookings' => [
                'label'       => 'Booking & Stay Rules',
                'icon'        => 'calendar_month',
                'description' => 'Check-in/out hours, stay duration limits, and reservation defaults.',
                'fields'      => ['default_checkin_time', 'default_checkout_time', 'min_booking_nights', 'max_booking_nights', 'advance_booking_days', 'cancellation_grace_hours', 'auto_confirm_bookings', 'default_booking_status']
            ],
            'properties' => [
                'label'       => 'Property Management',
                'icon'        => 'villa',
                'description' => 'Listing moderation, media limits, and default estate statuses.',
                'fields'      => ['property_approval_required', 'default_property_status', 'max_property_images', 'max_upload_size_mb']
            ],
            'guests' => [
                'label'       => 'Guest & Member Rules',
                'icon'        => 'group',
                'description' => 'Guest account sign-up rules, KYC verification, and membership tiers.',
                'fields'      => ['guest_registration_enabled', 'guest_id_verification_required', 'default_membership_tier', 'max_guest_documents']
            ],
            'reviews' => [
                'label'       => 'Review & Moderation',
                'icon'        => 'hotel_class',
                'description' => 'Guest feedback moderation policies, verified stay enforcement, and host replies.',
                'fields'      => ['review_auto_approve', 'review_verified_only', 'review_min_rating_auto_publish', 'review_allow_host_response', 'review_photos_allowed']
            ],
            'notifications' => [
                'label'       => 'Notification Settings',
                'icon'        => 'notifications_active',
                'description' => 'Master channel dispatch toggles and operational event subscriptions.',
                'fields'      => ['email_notifications_enabled', 'whatsapp_notifications_enabled', 'sms_notifications_enabled', 'notification_events']
            ],
            'email' => [
                'label'       => 'Email Communication',
                'icon'        => 'mail',
                'description' => 'Outgoing sender credentials, reply-to routing, signature, and SMTP test.',
                'fields'      => ['email_sender_name', 'email_sender_address', 'email_reply_to', 'email_signature_html']
            ],
            'maintenance' => [
                'label'       => 'System & Maintenance',
                'icon'        => 'build',
                'description' => 'Platform availability switch, scheduled downtime advisory, and IP whitelist.',
                'fields'      => ['maintenance_mode', 'maintenance_notice_title', 'maintenance_notice_message', 'maintenance_estimated_end', 'maintenance_allowed_ips', 'default_pagination_size']
            ],
            'security' => [
                'label'       => 'Security & Access',
                'icon'        => 'security',
                'description' => 'Session lifetime policies, lockout thresholds, and device session integrity.',
                'fields'      => ['session_timeout_minutes', 'max_login_attempts', 'account_lockout_minutes', 'enforce_single_session']
            ]
        ];
    }

    /**
     * Baseline factory defaults
     */
    public static function getFactoryDefaults(): array {
        return [
            // General
            'site_name'                   => 'Stayora',
            'tagline'                     => 'Luxury Farmhouses & Private Villa Escapes',
            'website_url'                 => 'https://stayora.com',
            'footer_text'                 => '© 2026 Stayora. All rights reserved. Handcrafted luxury stays across India.',
            'default_platform_status'     => 'Active',

            // Branding
            'logo'                        => '',
            'logo_dark'                   => '',
            'favicon'                     => 'favicon_stayora.png',
            'default_property_image'      => '',
            'default_avatar_image'        => '',
            'primary_theme_color'         => '#1D4ED8',
            'secondary_theme_color'       => '#0284C7',
            'accent_theme_color'          => '#C9A227',

            // Contact
            'email'                       => 'contact@stayora.com',
            'mobile_number'               => '+91-9111666415',
            'whatsapp_number'             => '+91-9111666415',
            'address'                     => 'Stayora Luxury Hospitality, Sector 54 Golf Course Road, Gurugram, India',
            'google_map_link'             => 'https://maps.google.com',
            'facebook_link'               => 'https://facebook.com/',
            'instagram_link'              => 'https://instagram.com/stayoraofficial',
            'youtube_link'                => 'https://instagram.com/farmleloofficial',
            'twitter_link'                => '',

            // Localization & Currency
            'timezone'                    => 'Asia/Kolkata',
            'date_format'                 => 'd M Y',
            'time_format'                 => '12h',
            'default_currency'            => 'INR',
            'currency_symbol'             => '₹',
            'currency_position'           => 'prefix',
            'decimal_precision'           => 0,
            'thousand_separator'          => 'indian',
            'default_language'            => 'en',

            // Bookings & Stays
            'default_checkin_time'        => '13:00',
            'default_checkout_time'       => '11:00',
            'min_booking_nights'          => 1,
            'max_booking_nights'          => 30,
            'advance_booking_days'        => 180,
            'cancellation_grace_hours'    => 24,
            'auto_confirm_bookings'       => 0,
            'default_booking_status'      => 'pending',

            // Property Management
            'property_approval_required'  => 1,
            'default_property_status'     => 'pending',
            'max_property_images'         => 15,
            'max_upload_size_mb'          => 5,

            // Guests & Members
            'guest_registration_enabled'  => 1,
            'guest_id_verification_required' => 1,
            'default_membership_tier'     => 'Regular',
            'max_guest_documents'         => 5,

            // Review Moderation
            'review_auto_approve'         => 0,
            'review_verified_only'        => 1,
            'review_min_rating_auto_publish' => 4.5,
            'review_allow_host_response'  => 1,
            'review_photos_allowed'       => 1,

            // Notifications
            'email_notifications_enabled' => 1,
            'whatsapp_notifications_enabled' => 1,
            'sms_notifications_enabled'   => 0,
            'notification_events'         => json_encode([
                'booking_created'  => true,
                'booking_approved' => true,
                'booking_cancelled'=> true,
                'checkin_reminder' => true,
                'review_submitted' => true,
                'payment_verified' => true
            ]),

            // Email Communication
            'email_sender_name'           => 'Stayora Luxury Hospitality',
            'email_sender_address'        => 'noreply@stayora.com',
            'email_reply_to'              => 'concierge@stayora.com',
            'email_signature_html'        => "<p style='font-size:12px;color:#64748b;'>Stayora Luxury Hospitality & Concierge Services.<br>Handcrafted Stays Across India.</p>",

            // Maintenance
            'maintenance_mode'            => 0,
            'maintenance_notice_title'    => 'Scheduled System Maintenance',
            'maintenance_notice_message'  => 'Stayora is currently undergoing scheduled platform upgrades to serve you better. We will be back online shortly.',
            'maintenance_estimated_end'   => null,
            'maintenance_allowed_ips'     => '',
            'default_pagination_size'     => 10,

            // Security
            'session_timeout_minutes'     => 120,
            'max_login_attempts'          => 5,
            'account_lockout_minutes'     => 15,
            'enforce_single_session'      => 1
        ];
    }

    /**
     * Get all settings merged with factory defaults
     */
    public function getSettings(): array {
        $defaults = self::getFactoryDefaults();
        $res = $this->db->query("SELECT * FROM site_settings WHERE id = 1 LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            foreach ($defaults as $k => $defVal) {
                if (!isset($row[$k]) || $row[$k] === null) {
                    $row[$k] = $defVal;
                }
            }
            return $row;
        }
        return $defaults;
    }

    /**
     * Update an entire category of settings
     * Validates inputs, compares diff, logs audit records, updates database
     *
     * @return array [success => bool, errors => array, changedCount => int]
     */
    public function updateCategory(string $category, array $data, int $adminId, string $adminName, string $ip = '', string $userAgent = ''): array {
        $categories = self::getCategories();
        if (!isset($categories[$category])) {
            return ['success' => false, 'errors' => ["Invalid settings category: {$category}"], 'changedCount' => 0];
        }

        $allowedFields = $categories[$category]['fields'];
        $currentSettings = $this->getSettings();
        $errors = [];
        $updates = [];
        $auditLogs = [];

        foreach ($allowedFields as $field) {
            if (!array_key_exists($field, $data)) {
                // If it's a boolean toggle that was unchecked in HTML form, treat as 0
                if (in_array($field, [
                    'auto_confirm_bookings', 'property_approval_required', 'guest_registration_enabled',
                    'guest_id_verification_required', 'review_auto_approve', 'review_verified_only',
                    'review_allow_host_response', 'review_photos_allowed', 'email_notifications_enabled',
                    'whatsapp_notifications_enabled', 'sms_notifications_enabled', 'maintenance_mode',
                    'enforce_single_session'
                ])) {
                    $val = 0;
                } else {
                    continue; // Skip if field not supplied
                }
            } else {
                $val = $data[$field];
            }

            // Clean & Validate
            $validationResult = $this->validateAndSanitizeField($field, $val);
            if (!empty($validationResult['error'])) {
                $errors[] = $validationResult['error'];
                continue;
            }

            $cleanVal = $validationResult['value'];
            $oldVal = $currentSettings[$field] ?? null;

            // Normalize for comparison
            $oldValStr = is_null($oldVal) ? '' : (string)$oldVal;
            $newValStr = is_null($cleanVal) ? '' : (string)$cleanVal;

            if ($oldValStr !== $newValStr) {
                $updates[$field] = $cleanVal;
                $auditLogs[] = [
                    'category'    => $category,
                    'setting_key' => $field,
                    'old_value'   => $oldValStr,
                    'new_value'   => $newValStr
                ];
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'changedCount' => 0];
        }

        if (empty($updates)) {
            return ['success' => true, 'errors' => [], 'changedCount' => 0, 'message' => 'No changes were detected.'];
        }

        // Build dynamic UPDATE query
        $setClauses = [];
        $types = '';
        $params = [];

        foreach ($updates as $col => $val) {
            $setClauses[] = "`{$col}` = ?";
            if (is_int($val)) {
                $types .= 'i';
                $params[] = $val;
            } elseif (is_float($val)) {
                $types .= 'd';
                $params[] = $val;
            } else {
                $types .= 's';
                $params[] = (string)$val;
            }
        }

        $sql = "UPDATE site_settings SET " . implode(', ', $setClauses) . " WHERE id = 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'errors' => ["Database error: " . $this->db->error], 'changedCount' => 0];
        }

        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            return ['success' => false, 'errors' => ["Failed to update database: " . $this->db->error], 'changedCount' => 0];
        }

        // Keep legacy `settings` table in sync if mobile or whatsapp changed
        if (isset($updates['mobile_number']) || isset($updates['whatsapp_number'])) {
            $mobile = $updates['mobile_number'] ?? ($currentSettings['mobile_number'] ?? '');
            $wa = $updates['whatsapp_number'] ?? ($currentSettings['whatsapp_number'] ?? '');
            $syncStmt = $this->db->prepare("UPDATE settings SET admin_phone = ?, admin_whatsapp = ? WHERE id = 1");
            if ($syncStmt) {
                $syncStmt->bind_param("ss", $mobile, $wa);
                $syncStmt->execute();
                $syncStmt->close();
            }
        }

        // Record audit logs
        foreach ($auditLogs as $log) {
            $this->recordAuditLog($adminId, $adminName, $log['category'], $log['setting_key'], $log['old_value'], $log['new_value'], $ip, $userAgent);
        }

        // Invalidate SiteHelper static cache
        SiteHelper::clearCache();

        return [
            'success'      => true,
            'errors'       => [],
            'changedCount' => count($updates),
            'message'      => count($updates) . ' setting(s) updated successfully.'
        ];
    }

    /**
     * Validate and sanitize individual field values
     */
    protected function validateAndSanitizeField(string $field, $value): array {
        // String trims
        if (is_string($value)) {
            $value = trim($value);
        }

        switch ($field) {
            // Email checks
            case 'email':
            case 'email_sender_address':
            case 'email_reply_to':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return ['error' => "Invalid email address format for '{$field}'."];
                }
                return ['value' => (string)$value];

            // URL checks
            case 'website_url':
            case 'google_map_link':
            case 'facebook_link':
            case 'instagram_link':
            case 'youtube_link':
            case 'twitter_link':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL) && $value !== '#') {
                    return ['error' => "Invalid URL format for '{$field}'. Must start with http:// or https://."];
                }
                return ['value' => (string)$value];

            // Color hex checks
            case 'primary_theme_color':
            case 'secondary_theme_color':
            case 'accent_theme_color':
                if (!empty($value) && !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
                    return ['error' => "Invalid hexadecimal color format for '{$field}' (e.g. #1D4ED8)."];
                }
                return ['value' => (string)$value];

            // Booleans / Flags
            case 'auto_confirm_bookings':
            case 'property_approval_required':
            case 'guest_registration_enabled':
            case 'guest_id_verification_required':
            case 'review_auto_approve':
            case 'review_verified_only':
            case 'review_allow_host_response':
            case 'review_photos_allowed':
            case 'email_notifications_enabled':
            case 'whatsapp_notifications_enabled':
            case 'sms_notifications_enabled':
            case 'maintenance_mode':
            case 'enforce_single_session':
                return ['value' => (!empty($value) && $value !== '0' && $value !== false) ? 1 : 0];

            // Numeric Integers
            case 'min_booking_nights':
                $v = max(1, (int)$value);
                return ['value' => $v];
            case 'max_booking_nights':
                $v = max(1, min(365, (int)$value));
                return ['value' => $v];
            case 'advance_booking_days':
                $v = max(1, min(730, (int)$value));
                return ['value' => $v];
            case 'cancellation_grace_hours':
                $v = max(0, min(168, (int)$value));
                return ['value' => $v];
            case 'max_property_images':
                $v = max(1, min(50, (int)$value));
                return ['value' => $v];
            case 'max_upload_size_mb':
                $v = max(1, min(50, (int)$value));
                return ['value' => $v];
            case 'max_guest_documents':
                $v = max(1, min(20, (int)$value));
                return ['value' => $v];
            case 'default_pagination_size':
                $v = in_array((int)$value, [10, 20, 25, 50, 100]) ? (int)$value : 10;
                return ['value' => $v];
            case 'session_timeout_minutes':
                $v = max(5, min(1440, (int)$value));
                return ['value' => $v];
            case 'max_login_attempts':
                $v = max(1, min(20, (int)$value));
                return ['value' => $v];
            case 'account_lockout_minutes':
                $v = max(1, min(1440, (int)$value));
                return ['value' => $v];
            case 'decimal_precision':
                $v = max(0, min(2, (int)$value));
                return ['value' => $v];

            // Decimals
            case 'review_min_rating_auto_publish':
                $v = max(1.0, min(5.0, (float)$value));
                return ['value' => $v];

            // Times
            case 'default_checkin_time':
            case 'default_checkout_time':
                if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
                    return ['error' => "Invalid time format for '{$field}' (HH:MM required)."];
                }
                return ['value' => (string)$value];

            // JSON payloads
            case 'notification_events':
                if (is_array($value)) {
                    return ['value' => json_encode($value)];
                }
                return ['value' => (string)$value];

            default:
                return ['value' => (string)$value];
        }
    }

    /**
     * Record entry in `settings_audit_logs`
     */
    public function recordAuditLog(?int $adminId, string $adminName, string $category, string $key, ?string $oldVal, ?string $newVal, string $ip = '', string $userAgent = ''): bool {
        $stmt = $this->db->prepare("
            INSERT INTO settings_audit_logs (admin_id, admin_name, category, setting_key, old_value, new_value, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) return false;

        $stmt->bind_param("isssssss", $adminId, $adminName, $category, $key, $oldVal, $newVal, $ip, $userAgent);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Reset a single category to factory defaults
     */
    public function resetCategory(string $category, int $adminId, string $adminName, string $ip = ''): array {
        $categories = self::getCategories();
        if (!isset($categories[$category])) {
            return ['success' => false, 'message' => "Invalid category: {$category}"];
        }

        $fields = $categories[$category]['fields'];
        $defaults = self::getFactoryDefaults();
        $current = $this->getSettings();
        $resetData = [];

        foreach ($fields as $f) {
            // Branding filenames should not be erased on reset to prevent breaking image links,
            // only color themes are reset in branding.
            if ($category === 'branding' && in_array($f, ['logo', 'logo_dark', 'favicon', 'default_property_image', 'default_avatar_image'])) {
                continue;
            }
            if (array_key_exists($f, $defaults)) {
                $resetData[$f] = $defaults[$f];
            }
        }

        $result = $this->updateCategory($category, $resetData, $adminId, $adminName, $ip, 'Reset To Factory Defaults');
        if ($result['success']) {
            $this->recordAuditLog($adminId, $adminName, $category, 'CATEGORY_RESET', 'Custom Values', 'Factory Baseline Defaults', $ip, 'Console Reset');
        }

        return $result;
    }

    /**
     * Fetch chronological audit history
     */
    public function getAuditHistory(int $limit = 50, ?string $category = null, int $offset = 0): array {
        $where = "1=1";
        $params = [];
        $types = "";

        if (!empty($category)) {
            $where .= " AND category = ?";
            $types .= "s";
            $params[] = $category;
        }

        $sql = "SELECT * FROM settings_audit_logs WHERE {$where} ORDER BY id DESC LIMIT ? OFFSET ?";
        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        $stmt->close();
        return $logs;
    }

    /**
     * Return total count of audit logs
     */
    public function getAuditHistoryCount(?string $category = null): int {
        if (!empty($category)) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM settings_audit_logs WHERE category = ?");
            if ($stmt) {
                $stmt->bind_param("s", $category);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                return (int)($res['cnt'] ?? 0);
            }
        }
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM settings_audit_logs");
        if ($res && $r = $res->fetch_assoc()) {
            return (int)$r['cnt'];
        }
        return 0;
    }

    /**
     * Server Telemetry & System Diagnostics (Read-Only)
     */
    public function getSystemInfo(): array {
        $dbVer = 'Unknown';
        $res = $this->db->query("SELECT VERSION() as v");
        if ($res && $row = $res->fetch_assoc()) {
            $dbVer = $row['v'];
        }

        return [
            'php_version'        => PHP_VERSION,
            'mysql_version'      => $dbVer,
            'server_os'          => PHP_OS . ' (' . php_uname('s') . ' ' . php_uname('r') . ')',
            'server_software'    => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache/XAMPP',
            'server_time'        => date('Y-m-d H:i:s T'),
            'timezone'           => date_default_timezone_get(),
            'memory_limit'       => ini_get('memory_limit'),
            'upload_max_filesize'=> ini_get('upload_max_filesize'),
            'post_max_size'      => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'curl_installed'     => function_exists('curl_init') ? 'Enabled' : 'Disabled',
            'gd_installed'       => extension_loaded('gd') ? 'Enabled' : 'Disabled',
            'mbstring_installed' => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
            'openssl_installed'  => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
            'pdo_installed'      => extension_loaded('pdo') ? 'Enabled' : 'Disabled',
            'root_path'          => defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../../..')
        ];
    }
}
