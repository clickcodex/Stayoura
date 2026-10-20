<?php
namespace App\Helpers;

use App\Models\Admin\AdminModel;

class AdminAuth {
    /**
     * Strict Server-Side Authentication & Single-Active-Session Enforcement.
     * Validates that the current browser session matches the unique active terminal session
     * registered in MySQL for this admin account.
     * 
     * If invalid, expired, or superseded by a login on another device, the local session is
     * immediately terminated and the user is redirected to the login page with a descriptive error.
     *
     * @return array The authenticated admin data.
     */
    public static function requireAuth(): array {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Verify session credentials exist and role is admin
        if (empty($_SESSION['is_logged_in']) || ($_SESSION['role'] ?? '') !== 'admin' || empty($_SESSION['user_id'])) {
            $_SESSION['error'] = "Access denied. Please log in with administrative privileges.";
            redirect('admin/login');
            exit;
        }

        $adminId = (int)$_SESSION['user_id'];
        $sessionToken = (string)($_SESSION['admin_session_token'] ?? '');
        $adminModel = new AdminModel();

        // 2. Enforce Single Active Session in Database
        if (empty($sessionToken) || !$adminModel->validateActiveSession($adminId, $sessionToken)) {
            // Invalidate current session
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }

            // Clear remember me cookie
            if (isset($_COOKIE['remember_me'])) {
                setcookie('remember_me', '', time() - 3600, '/');
            }

            // Start fresh session to pass the revocation flash message
            session_start();
            $_SESSION['error'] = "Your session has ended because your account was logged in from another device or browser.";
            redirect('admin/login');
            exit;
        }

        $admin = $adminModel->findById($adminId);
        if (!$admin || strtolower((string)($admin['status'] ?? '')) !== 'active') {
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            session_start();
            $_SESSION['error'] = "Your administrative account is inactive or not found.";
            redirect('admin/login');
            exit;
        }

        return $admin;
    }

    /**
     * Get the authenticated admin details without exiting (or null if not authenticated)
     */
    public static function user(): ?array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['is_logged_in']) || ($_SESSION['role'] ?? '') !== 'admin' || empty($_SESSION['user_id'])) {
            return null;
        }

        $adminModel = new AdminModel();
        return $adminModel->findById((int)$_SESSION['user_id']);
    }
}
