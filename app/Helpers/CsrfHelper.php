<?php
namespace App\Helpers;

/**
 * Standard CSRF Security Helper for Stayora
 */
class CsrfHelper {
    /**
     * Generate or return existing CSRF token
     */
    public static function generate(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Get the active CSRF token
     */
    public static function getToken(): string {
        return self::generate();
    }

    /**
     * Verify submitted CSRF token
     */
    public static function verify(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
