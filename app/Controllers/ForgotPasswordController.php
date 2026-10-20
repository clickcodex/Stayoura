<?php
namespace App\Controllers;

use App\Config\Database;
use App\Services\SmsService;
use App\Services\OtpService;
use App\Services\EmailService;
use mysqli;

class ForgotPasswordController {

    private mysqli $db;
    private SmsService $smsService;
    private OtpService $otpService;
    private EmailService $emailService;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db           = Database::connect();
        $this->smsService   = new SmsService();
        $this->otpService   = new OtpService($this->db);
        $this->emailService = new EmailService();
    }

    // ── User Forgot Password Page ──
    public function showForgotForm() {
        $roles = 'users';
        require_once __DIR__ . '/../Views/forgot-password.php';
    }


    // ── Handle Submission: Send OTP to Registered Email ──
    public function sendResetLink() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('forgot-password');
        }

        $email = trim($_POST['identity'] ?? ($_POST['email'] ?? ''));
        $roles = $_POST['role'] ?? 'users';

        if (!in_array($roles, ['users', 'owners'])) {
            $roles = 'users';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please enter a valid registered email address.";
            redirect($_SERVER['HTTP_REFERER'] ?? 'forgot-password');
        }

        // Search primary role first, then fallback to other role (users <-> owners)
        $searchRoles = [$roles, ($roles === 'users' ? 'owners' : 'users')];
        $user = null;
        $activeRole = $roles;

        foreach ($searchRoles as $targetRole) {
            $stmt = $this->db->prepare("SELECT id, name, email, phone FROM `$targetRole` WHERE email = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows > 0) {
                    $user = $res->fetch_assoc();
                    $activeRole = $targetRole;
                    $stmt->close();
                    break;
                }
                $stmt->close();
            }
        }

        if (!$user) {
            $_SESSION['error'] = "No account found registered with this email address.";
            redirect($_SERVER['HTTP_REFERER'] ?? 'forgot-password');
        }

        $roles = $activeRole;
        $name  = trim($user['name'] ?? 'User');
        $email = trim($user['email']);

        // Send OTP to Email Address
        $otpRes = $this->otpService->generateOtp($email, 'forgot_password_email', (int)$user['id'], $roles);
        if (!$otpRes['success']) {
            $_SESSION['error'] = $otpRes['error'];
            redirect($_SERVER['HTTP_REFERER'] ?? 'forgot-password');
        }

        $emailSend = $this->emailService->sendPasswordResetOtpEmail($email, $name, $otpRes['otp']);

        $parts = explode('@', $email);
        $masked = substr($parts[0], 0, 2) . '*****@' . ($parts[1] ?? 'domain.com');
        $_SESSION['password_reset_recovery'] = [
            'user_id'       => (int)$user['id'],
            'role'          => $roles,
            'channel'       => 'email',
            'purpose'       => 'forgot_password_email',
            'identifier'    => $email,
            'name'          => $name,
            'masked_target' => $masked,
        ];

        if (!empty($emailSend['is_simulated'])) {
            $_SESSION['success'] = "Verification code sent to {$masked}. (Test Code: {$otpRes['otp']})";
        } else {
            $_SESSION['success'] = "Verification code has been sent to your email {$masked}.";
        }

        redirect('forgot-password-verify');
        exit();
    }

    // ── Show Verification Form ──
    public function showDualVerifyForm() {
        if (empty($_SESSION['password_reset_recovery'])) {
            redirect('forgot-password');
        }
        require_once __DIR__ . '/../Views/auth/forgot-password-verify.php';
    }

    // ── Process OTP Verification ──
    public function processDualVerification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('forgot-password-verify');
        }

        if (empty($_SESSION['password_reset_recovery'])) {
            $_SESSION['error'] = "Recovery session expired. Please start over.";
            redirect('forgot-password');
        }

        $recovery   = $_SESSION['password_reset_recovery'];
        $identifier = $recovery['identifier'] ?? '';
        $purpose    = $recovery['purpose'] ?? 'forgot_password_email';
        $userId     = (int)($recovery['user_id'] ?? 0);
        $roles      = $recovery['role'] ?? 'users';

        // Read OTP from single field or dual fields
        $otpInput = trim($_POST['otp'] ?? ($_POST['sms_otp'] ?? ($_POST['email_otp'] ?? '')));

        if (empty($otpInput)) {
            $_SESSION['error'] = "Please enter the 6-digit verification code.";
            redirect('forgot-password-verify');
            exit();
        }

        $val = $this->otpService->validateOtp($identifier, $purpose, $otpInput);
        if (!$val['success']) {
            $_SESSION['error'] = $val['error'];
            redirect('forgot-password-verify');
            exit();
        }

        // Verified! Generate 30-minute one-time reset token
        $token  = bin2hex(random_bytes(32));
        $hashed = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', time() + 1800);

        $stmt = $this->db->prepare(
            "UPDATE `$roles` SET reset_token = ?, reset_token_expiry = ? WHERE id = ?"
        );
        if ($stmt) {
            $stmt->bind_param("ssi", $hashed, $expiry, $userId);
            $stmt->execute();
            $stmt->close();
        }

        unset($_SESSION['password_reset_recovery']);
        $_SESSION['success'] = "Verification successful! You can now set your new password.";
        redirect("reset-password?token=$token&role=$roles");
        exit();
    }

    // ── Resend OTP ──
    public function resendDualOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('forgot-password-verify');
        }

        if (empty($_SESSION['password_reset_recovery'])) {
            redirect('forgot-password');
        }

        $recovery   = $_SESSION['password_reset_recovery'];
        $identifier = $recovery['identifier'] ?? '';
        $purpose    = $recovery['purpose'] ?? 'forgot_password_email';
        $userId     = (int)($recovery['user_id'] ?? 0);
        $roles      = $recovery['role'] ?? 'users';
        $channel    = $recovery['channel'] ?? 'email';
        $name       = $recovery['name'] ?? 'User';

        $otpRes = $this->otpService->generateOtp($identifier, $purpose, $userId, $roles);
        if (!$otpRes['success']) {
            $_SESSION['error'] = $otpRes['error'];
            redirect('forgot-password-verify');
            exit();
        }

        $this->emailService->sendPasswordResetOtpEmail($identifier, $name, $otpRes['otp']);
        $_SESSION['success'] = "A new verification code has been sent to your email address.";

        redirect('forgot-password-verify');
        exit();
    }

    // ── Show Reset Password Form ──
    public function showResetForm() {
        $token = $_GET['token'] ?? '';
        $roles = $_GET['role']  ?? 'users';

        if (!in_array($roles, ['users', 'owners'])) {
            $_SESSION['error'] = "Invalid link.";
            redirect('login');
        }

        $hashed = hash('sha256', $token);
        $now    = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare(
            "SELECT id FROM `$roles` WHERE reset_token = ? AND reset_token_expiry > ? LIMIT 1"
        );
        $stmt->bind_param("ss", $hashed, $now);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            $_SESSION['error'] = "This reset link is invalid or has expired.";
            redirect('forgot-password');
        }

        require_once __DIR__ . '/../Views/reset-password.php';
    }

    // ── Process New Password ──
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('login');
        }

        $token    = $_POST['token']    ?? '';
        $roles    = $_POST['role']     ?? 'users';
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm']  ?? '';

        if (!in_array($roles, ['users', 'owners'])) {
            $_SESSION['error'] = "Invalid request.";
            redirect('login');
        }

        if (strlen($password) < 8 || $password !== $confirm) {
            $_SESSION['error'] = "Passwords must match and be at least 8 characters.";
            redirect($_SERVER['HTTP_REFERER'] ?? 'forgot-password');
        }

        $hashed = hash('sha256', $token);
        $now    = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare(
            "SELECT id FROM `$roles` WHERE reset_token = ? AND reset_token_expiry > ? LIMIT 1"
        );
        $stmt->bind_param("ss", $hashed, $now);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['error'] = "This reset link is invalid or has expired.";
            redirect('forgot-password');
        }

        $user        = $result->fetch_assoc();
        $newPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt2 = $this->db->prepare(
            "UPDATE `$roles` SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?"
        );
        $stmt2->bind_param("si", $newPassword, $user['id']);
        $stmt2->execute();

        $_SESSION['success'] = "Password reset successfully! You can now log in.";
        redirect('login');
    }
}