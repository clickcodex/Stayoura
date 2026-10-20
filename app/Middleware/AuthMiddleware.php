<?php
namespace App\Middleware;

use App\Models\UserModel;

class AuthMiddleware {

 public static function check(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['is_logged_in'])) {
        self::attemptAutoLogin();
    }

    if (isset($_SESSION['is_logged_in']) && isset($_SESSION['user_id'])) {
        $model = new UserModel();
        $role  = $_SESSION['role'] ?? 'user';
        $table = $role . 's';

        // ── Single Active Device Session Check (EXCLUSIVELY FOR ADMIN) ──
        if ($role === 'admin') {
            $sessionToken = $_SESSION['admin_session_token'] ?? '';
            if (!$model->validateAdminActiveSession((int)$_SESSION['user_id'], $sessionToken)) {
                $_SESSION = [];
                session_destroy();

                if (isset($_COOKIE['remember_me'])) {
                    setcookie('remember_me', '', time() - 3600, '/');
                }

                session_start();
                $_SESSION['error'] = "You have been logged out because your account was logged into from another device.";
                redirect('/admin/login');
            }
        }

        $dbLoginStatus = $model->getLoginStatus($_SESSION['user_id'], $table);

        if ($dbLoginStatus === 0) {
            $_SESSION = [];
            session_destroy();

            if (isset($_COOKIE['remember_me'])) {
                setcookie('remember_me', '', time() - 3600, '/');
            }

            session_start(); // clean session for redirect

            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (stripos($uri, 'admin') !== false) {
                redirect('/admin/login');
            } elseif (stripos($uri, 'owner') !== false) {
                redirect('/owner/login');
            } else {
                redirect('/login');
            }
        }
    }

    // If still not logged in after auto-login attempt
    if (!isset($_SESSION['is_logged_in'])) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (stripos($uri, 'admin') !== false) {
            redirect('/admin/login');
        } elseif (stripos($uri, 'owner') !== false) {
            redirect('/owner/login');
        } else {
            redirect('/login');
        }
    }

    if (($_SESSION['role'] ?? '') === 'user' && isset($_SESSION['user_id'])) {
        $model = new UserModel();
        $model->refreshSessionToken((int) $_SESSION['user_id']);
    }
}
    private static function attemptAutoLogin(): void
    {
        if (!isset($_COOKIE['remember_me'])) return;

        $parts = explode(':', $_COOKIE['remember_me']);
        if (count($parts) !== 2) return;

        [$token, $table] = $parts;

        $model       = new UserModel();
        $hashedToken = hash('sha256', $token);
        $user        = $model->findByToken($hashedToken, $table);

        // Modified: Double-check auto login also adheres to `is_logged_in == 1` policy 
       if ($user && $user['is_logged_in'] == 1 && ($user['status'] ?? 'active') === 'active') {
            $_SESSION['is_logged_in']  = true;
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['user_name']     = $user['name'];
            $_SESSION['user_email']    = $user['email'] ?? '';
            $_SESSION['profile_image'] = $user['profile_image'] ?? 'default.png';
            $_SESSION['role']          = rtrim($table, 's'); // users → user, admins → admin

            // If an admin auto-logs in via remember cookie, assign active session token to claim sole device
            if ($table === 'admins') {
                $sessionToken = bin2hex(random_bytes(32));
                $_SESSION['admin_session_token'] = $sessionToken;
                $model->updateAdminActiveSession((int)$user['id'], $sessionToken);
            }
        } else {
            setcookie('remember_me', '', time() - 3600, '/');
        }
    }

    public static function guestOnly(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['is_logged_in'])) {
            self::redirectDashboard();
        }
    }

    public static function adminOnly(): void
    {
        \App\Helpers\AdminAuth::requireAuth();
    }

    public static function ownerOnly(): void
    {
        self::check();
        if (($_SESSION['role'] ?? '') !== 'owner') {
            die("Unauthorized Owner Access");
        }
    }

    public static function userOnly(): void
    {
        self::check();
        if (($_SESSION['role'] ?? '') !== 'user') {
            die("Unauthorized User Access");
        }
    }

    private static function redirectDashboard(): void
    {
        $role = $_SESSION['role'] ?? 'user';
        if ($role === 'admin') {
            redirect('/admin/dashboard');
        } elseif ($role === 'owner') {
            redirect('/owner/dashboard');
        } else {
            redirect('/dashboard');
        }
    }
}