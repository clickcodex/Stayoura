<?php
namespace App\Helpers;

use App\Config\Database;

/**
 * SiteHelper — Centralized Platform Configuration & Localization Helper
 * Provides request-level memoized access to site settings, dynamic currency
 * formatting, date/time localization, branding asset resolution, and maintenance status.
 */
class SiteHelper {
    // Memoized settings cache per request
    private static $settings = null;

    /**
     * Retrieve all platform settings from database or fallback defaults
     */
    public static function getSettings(): array {
        if (self::$settings !== null) {
            return self::$settings;
        }

        try {
            $db = Database::connect();
            $result = $db->query("SELECT * FROM site_settings WHERE id = 1 LIMIT 1");
            
            if ($result && $result->num_rows > 0) {
                self::$settings = $result->fetch_assoc();
            } else {
                self::$settings = [];
            }
        } catch (\Throwable $e) {
            self::$settings = [];
        }

        return self::$settings;
    }

    /**
     * Clear the static in-memory cache
     */
    public static function clearCache(): void {
        self::$settings = null;
    }

    /**
     * Retrieve a specific setting value with fallback
     */
    public static function get(string $key, $default = null) {
        $settings = self::getSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Currency Display Formatter
     * Formats numeric amounts using configured symbol, position, precision, and separator style.
     * NEVER mutates the database price or historical transactions.
     *
     * @param float|int|numeric-string $amount
     */
    public static function formatCurrency($amount): string {
        $settings = self::getSettings();
        $symbol    = $settings['currency_symbol'] ?? '₹';
        $position  = $settings['currency_position'] ?? 'prefix';
        $decimals  = (int)($settings['decimal_precision'] ?? 0);
        $sepStyle  = $settings['thousand_separator'] ?? 'indian';

        $num = (float)$amount;

        if ($sepStyle === 'indian') {
            // Format integer part using Indian numbering system (Lakhs / Crores)
            $isNeg = $num < 0;
            $absNum = abs($num);
            $intPart = (int)floor($absNum);
            $fracPart = round($absNum - $intPart, $decimals);

            $strInt = (string)$intPart;
            if (strlen($strInt) > 3) {
                $lastThree = substr($strInt, -3);
                $rest = substr($strInt, 0, -3);
                $restFormatted = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
                $formattedInt = $restFormatted . ',' . $lastThree;
            } else {
                $formattedInt = $strInt;
            }

            if ($decimals > 0) {
                $fracStr = substr(number_format($fracPart, $decimals, '.', ''), 1);
                $formattedNumber = ($isNeg ? '-' : '') . $formattedInt . $fracStr;
            } else {
                $formattedNumber = ($isNeg ? '-' : '') . $formattedInt;
            }
        } else {
            // Standard Western thousands separator (125,000)
            $formattedNumber = number_format($num, $decimals, '.', ',');
        }

        if ($position === 'suffix') {
            return $formattedNumber . ' ' . $symbol;
        }
        return $symbol . $formattedNumber;
    }

    /**
     * Date Formatter based on Platform Settings
     */
    public static function formatDate($date, ?string $customFormat = null): string {
        if (empty($date)) return '';
        $settings = self::getSettings();
        $format = $customFormat ?: ($settings['date_format'] ?? 'd M Y');
        $timestamp = is_numeric($date) ? (int)$date : strtotime($date);
        if (!$timestamp) return (string)$date;
        return date($format, $timestamp);
    }

    /**
     * Time Formatter based on Platform Settings
     */
    public static function formatTime($time, ?string $customFormat = null): string {
        if (empty($time)) return '';
        $settings = self::getSettings();
        $timeFormatPref = $settings['time_format'] ?? '12h';
        $format = $customFormat ?: ($timeFormatPref === '24h' ? 'H:i' : 'h:i A');
        $timestamp = is_numeric($time) ? (int)$time : strtotime($time);
        if (!$timestamp) return (string)$time;
        return date($format, $timestamp);
    }

    /**
     * Date + Time Formatter based on Platform Settings
     */
    public static function formatDateTime($dateTime): string {
        if (empty($dateTime)) return '';
        return self::formatDate($dateTime) . ' • ' . self::formatTime($dateTime);
    }

    /**
     * Get Light / Standard Logo URL
     */
    public static function getLogoUrl(): string {
        $settings = self::getSettings();
        $logo = $settings['logo'] ?? '';
        if (!empty($logo) && file_exists(ROOT_PATH . '/assets/images/uploads/settings/' . $logo)) {
            return asset('assets/images/uploads/settings/' . $logo);
        }
        return asset('assets/images/logo.webp');
    }

    /**
     * Get Dark / Inverted Logo URL (for dark footers and dark mode)
     */
    public static function getDarkLogoUrl(): string {
        $settings = self::getSettings();
        $logoDark = $settings['logo_dark'] ?? '';
        if (!empty($logoDark) && file_exists(ROOT_PATH . '/assets/images/uploads/settings/' . $logoDark)) {
            return asset('assets/images/uploads/settings/' . $logoDark);
        }
        if (!empty($settings['logo']) && file_exists(ROOT_PATH . '/assets/images/uploads/settings/' . $settings['logo'])) {
            return asset('assets/images/uploads/settings/' . $settings['logo']);
        }
        return asset('assets/images/logo-dark.webp');
    }

    /**
     * Get Favicon URL
     */
    public static function getFaviconUrl(): string {
        $settings = self::getSettings();
        $fav = $settings['favicon'] ?? '';
        if (!empty($fav)) {
            if (file_exists(ROOT_PATH . '/assets/images/uploads/settings/' . $fav)) {
                return asset('assets/images/uploads/settings/' . $fav);
            }
            if (file_exists(ROOT_PATH . '/assets/images/' . $fav)) {
                return asset('assets/images/' . $fav);
            }
        }
        return asset('assets/images/favicon.png');
    }

    /**
     * Get Default Property Placeholder Image URL
     */
    public static function getDefaultPropertyImageUrl(): string {
        $settings = self::getSettings();
        $img = $settings['default_property_image'] ?? '';
        if (!empty($img) && file_exists(ROOT_PATH . '/assets/images/uploads/settings/' . $img)) {
            return asset('assets/images/uploads/settings/' . $img);
        }
        return asset('assets/images/luxury_pool_hero.jpg');
    }

    /**
     * Get Default Avatar Placeholder Image URL
     */
    public static function getDefaultAvatarUrl(): string {
        $settings = self::getSettings();
        $img = $settings['default_avatar_image'] ?? '';
        if (!empty($img) && file_exists(ROOT_PATH . '/assets/images/uploads/settings/' . $img)) {
            return asset('assets/images/uploads/settings/' . $img);
        }
        return 'https://ui-avatars.com/api/?name=Stayora+Guest&background=1D4ED8&color=fff&bold=true';
    }

    /**
     * Check if Maintenance Mode is active for public traffic
     * Authenticated admins and whitelisted IPs are strictly bypassed.
     */
    public static function isMaintenanceActive(): bool {
        $settings = self::getSettings();
        if (empty($settings['maintenance_mode'])) {
            return false;
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        // Authenticated admin bypass
        if (!empty($_SESSION['is_logged_in']) && ($_SESSION['role'] ?? '') === 'admin') {
            return false;
        }

        // URI admin path bypass
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        if (str_contains($uri, 'admin') || str_contains($uri, 'login')) {
            return false;
        }

        // IP Whitelist bypass
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        $allowedIps = array_filter(array_map('trim', explode(',', $settings['maintenance_allowed_ips'] ?? '')));
        if (in_array($clientIp, $allowedIps)) {
            return false;
        }

        return true;
    }
}