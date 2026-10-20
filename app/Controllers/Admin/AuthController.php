<?php
namespace App\Controllers\Admin;

use App\Models\Admin\AdminModel;

class AuthController {
    protected AdminModel $adminModel;

    public function __construct() {
        $this->adminModel = new AdminModel();
    }

    /**
     * Show Admin Login Page
     */
    public function loginForm(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // If already authenticated as administrator, redirect directly to dashboard
        if (!empty($_SESSION['is_logged_in']) && ($_SESSION['role'] ?? '') === 'admin') {
            redirect('admin/dashboard');
            return;
        }

        $pageTitle = "Admin Login • Stayora Executive Console";
        require_once __DIR__ . '/../../Views/admin/login.php';
    }

    /**
     * Process Admin Authentication Submission
     */
    public function loginSubmit(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/login');
            return;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $remember = !empty($_POST['remember']);

        // Input validation
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Please provide both administrative email and password.";
            redirect('admin/login');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please enter a valid administrative email address.";
            redirect('admin/login');
            return;
        }

        // Fetch admin user from DB
        $admin = $this->adminModel->findByEmail($email);

        if (!$admin) {
            $_SESSION['error'] = "Access denied. No administrative account matches this email.";
            redirect('admin/login');
            return;
        }

        // Check active status
        $status = strtolower((string)($admin['status'] ?? 'active'));
        if ($status !== 'active') {
            $_SESSION['error'] = "Your administrative account is currently {$status}. Please contact the Super Admin.";
            redirect('admin/login');
            return;
        }

        // Verify password hash
        if (!password_verify($password, $admin['password'])) {
            $_SESSION['error'] = "Invalid credentials. Please verify your password.";
            redirect('admin/login');
            return;
        }

        // Generate high-entropy session token for single active device enforcement
        $sessionToken = bin2hex(random_bytes(32));

        // Update active session in DB
        $this->adminModel->updateLoginSession((int)$admin['id'], $sessionToken);

        // Store session data
        $_SESSION['is_logged_in']        = true;
        $_SESSION['user_id']             = (int)$admin['id'];
        $_SESSION['user_name']           = $admin['name'] ?? 'Stayora Administrator';
        $_SESSION['user_email']          = $admin['email'];
        $_SESSION['role']                = 'admin';
        $_SESSION['profile_image']       = $admin['profile_image'] ?? '';
        $_SESSION['admin_session_token'] = $sessionToken;

        // Remember Me cookie (30 days)
        if ($remember) {
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $expiry = date('Y-m-d H:i:s', time() + (86400 * 30));
            $this->adminModel->setRememberToken((int)$admin['id'], $tokenHash, $expiry);
            setcookie('remember_me', $rawToken . ':admins', time() + (86400 * 30), '/', '', false, true);
        }

        $_SESSION['success'] = "Welcome back, " . htmlspecialchars($admin['name'] ?? 'Admin') . "!";
        redirect('admin/dashboard');
    }

    /**
     * Admin Sign Out
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!empty($_SESSION['user_id'])) {
            $this->adminModel->clearLoginSession((int)$_SESSION['user_id']);
            $this->adminModel->clearRememberToken((int)$_SESSION['user_id']);
        }

        // Clear remember me cookie
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }

        // Destroy session
        $_SESSION = [];
        session_destroy();

        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['success'] = "You have been securely signed out of the Admin Console.";
        redirect('admin/login');
    }
}
