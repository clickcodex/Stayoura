<?php
namespace App\Controllers\Admin;

use App\Helpers\AdminAuth;
use App\Helpers\CsrfHelper;
use App\Helpers\SiteHelper;
use App\Models\Admin\PlatformSettingsModel;
use App\Services\MailNotificationService;

/**
 * SettingsController — Executive Platform Configuration Hub
 * Centralizes all admin controls for platform branding, localization, booking policies,
 * guest/property rules, notification toggles, email communication, and maintenance mode.
 */
class SettingsController {
    protected PlatformSettingsModel $model;

    public function __construct() {
        $this->model = new PlatformSettingsModel();
    }

    /**
     * Dashboard view with categorized tabs
     */
    public function index(): void {
        $admin = AdminAuth::requireAuth();
        $tab = strtolower(trim($_GET['tab'] ?? 'general'));
        $categories = PlatformSettingsModel::getCategories();

        // Additional non-form tabs
        $validTabs = array_merge(array_keys($categories), ['audit', 'system']);
        if (!in_array($tab, $validTabs)) {
            $tab = 'general';
        }

        $settings = $this->model->getSettings();
        $auditLogs = [];
        $totalAuditCount = 0;
        $systemInfo = [];

        if ($tab === 'audit') {
            $auditCategory = !empty($_GET['audit_category']) ? trim($_GET['audit_category']) : null;
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 25;
            $offset = ($page - 1) * $perPage;
            $auditLogs = $this->model->getAuditHistory($perPage, $auditCategory, $offset);
            $totalAuditCount = $this->model->getAuditHistoryCount($auditCategory);
        } elseif ($tab === 'system') {
            $systemInfo = $this->model->getSystemInfo();
        }

        $csrfToken = CsrfHelper::getToken();
        $currentPage = 'settings';
        $pageTitle = 'Platform Settings — Stayora Executive Console';

        // Load view
        require_once ROOT_PATH . '/app/Views/admin/settings/index.php';
    }

    /**
     * Update category settings via POST
     */
    public function update(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/settings');
            exit;
        }

        // Verify CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!CsrfHelper::verify($token)) {
            $_SESSION['error'] = 'Security validation failed (invalid CSRF token). Please try again.';
            redirect('admin/settings?tab=' . urlencode($_POST['category'] ?? 'general'));
            exit;
        }

        $category = strtolower(trim($_POST['category'] ?? 'general'));
        $adminId = (int)$admin['id'];
        $adminName = (string)($admin['name'] ?? 'Admin');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $result = $this->model->updateCategory($category, $_POST, $adminId, $adminName, $ip, $userAgent);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = implode('<br>', $result['errors']);
        }

        redirect('admin/settings?tab=' . urlencode($category));
        exit;
    }

    /**
     * Handle branding asset file upload (AJAX or form post)
     */
    public function uploadBranding(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CsrfHelper::verify($token)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
            exit;
        }

        $type = trim($_POST['type'] ?? '');
        $allowedTypes = ['logo', 'logo_dark', 'favicon', 'default_property_image', 'default_avatar_image', 'payment_qr_code'];
        if (!in_array($type, $allowedTypes)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid branding asset type.']);
            exit;
        }

        if (empty($_FILES['asset_file']) || $_FILES['asset_file']['error'] !== UPLOAD_ERR_OK) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error occurred.']);
            exit;
        }

        $file = $_FILES['asset_file'];
        $maxBytes = 5 * 1024 * 1024; // 5MB limit
        if ($file['size'] > $maxBytes) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'File size exceeds maximum limit of 5MB.']);
            exit;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg'        => 'jpg',
            'image/png'         => 'png',
            'image/webp'        => 'webp',
            'image/svg+xml'     => 'svg',
            'image/x-icon'      => 'ico',
            'image/vnd.microsoft.icon' => 'ico'
        ];

        if (!array_key_exists($mime, $allowedMimes)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unsupported image format. Allowed: WEBP, PNG, JPG, SVG, ICO.']);
            exit;
        }

        $ext = $allowedMimes[$mime];
        $uploadDir = ROOT_PATH . '/assets/images/uploads/settings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFilename = $type . '_' . bin2hex(random_bytes(6)) . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $newFilename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file to storage.']);
            exit;
        }

        // Update database column
        $currentSettings = $this->model->getSettings();
        $oldFile = $currentSettings[$type] ?? '';

        $updateResult = $this->model->updateCategory('branding', [$type => $newFilename], (int)$admin['id'], (string)$admin['name'], $_SERVER['REMOTE_ADDR'] ?? '', 'Branding Upload');

        header('Content-Type: application/json');
        echo json_encode([
            'success'   => true,
            'filename'  => $newFilename,
            'file_url'  => asset('assets/images/uploads/settings/' . $newFilename),
            'message'   => ucfirst(str_replace('_', ' ', $type)) . ' updated successfully!'
        ]);
        exit;
    }

    /**
     * Quick toggle for Maintenance Mode
     */
    public function toggleMaintenance(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/settings?tab=maintenance');
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!CsrfHelper::verify($token)) {
            $_SESSION['error'] = 'Security validation failed (invalid CSRF token).';
            redirect('admin/settings?tab=maintenance');
            exit;
        }

        $current = $this->model->getSettings();
        $newMode = empty($current['maintenance_mode']) ? 1 : 0;

        $res = $this->model->updateCategory('maintenance', ['maintenance_mode' => $newMode], (int)$admin['id'], (string)$admin['name'], $_SERVER['REMOTE_ADDR'] ?? '', 'Maintenance Quick Toggle');

        if ($res['success']) {
            $_SESSION['success'] = $newMode === 1
                ? '⚠️ Maintenance Mode is now ACTIVE. Public visitors will see the maintenance screen.'
                : '✓ Maintenance Mode has been DEACTIVATED. Platform is live for all visitors.';
        } else {
            $_SESSION['error'] = 'Failed to toggle maintenance mode.';
        }

        redirect('admin/settings?tab=maintenance');
        exit;
    }

    /**
     * Trigger a verified test email to the current administrator
     */
    public function testEmail(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/settings?tab=email');
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!CsrfHelper::verify($token)) {
            $_SESSION['error'] = 'Security validation failed (invalid CSRF token).';
            redirect('admin/settings?tab=email');
            exit;
        }

        $recipientEmail = trim($_POST['recipient_email'] ?? $admin['email'] ?? '');
        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid test recipient email address.';
            redirect('admin/settings?tab=email');
            exit;
        }

        $settings = $this->model->getSettings();
        $senderName = $settings['email_sender_name'] ?? 'Stayora Support';
        $senderEmail = $settings['email_sender_address'] ?? 'noreply@stayora.com';
        $currentTime = date('D, d M Y H:i:s T');

        $mailService = new MailNotificationService();
        $subject = "✅ Stayora SMTP Test Transmission: Successful Configuration Verification";
        $body = "
            <div style='font-family:sans-serif;max-width:600px;margin:auto;padding:28px;border:1px solid #e2e8f0;border-radius:16px;background:#ffffff;'>
                <div style='border-bottom:2px solid #1D4ED8;padding-bottom:12px;margin-bottom:20px;'>
                    <h2 style='color:#1D4ED8;margin:0;font-size:22px;'>Stayora Platform Communication Test</h2>
                    <p style='color:#64748b;font-size:12px;margin:4px 0 0;'>Executive Configuration & Dispatch Ledger</p>
                </div>
                <p style='color:#334155;font-size:15px;line-height:1.6;'>Hello <strong>" . htmlspecialchars($admin['name'] ?? 'Administrator') . "</strong>,</p>
                <p style='color:#334155;line-height:1.6;'>This is a verified test email sent from the Stayora Platform Settings Management Console.</p>
                
                <div style='background:#f8fafc;border-left:4px solid #10b981;padding:16px;border-radius:8px;margin:20px 0;'>
                    <p style='margin:4px 0;font-size:13px;'><strong>Configured Sender:</strong> {$senderName} &lt;{$senderEmail}&gt;</p>
                    <p style='margin:4px 0;font-size:13px;'><strong>Test Recipient:</strong> {$recipientEmail}</p>
                    <p style='margin:4px 0;font-size:13px;'><strong>Timestamp:</strong> {$currentTime}</p>
                    <p style='margin:4px 0;font-size:13px;'><strong>Ledger Status:</strong> Successfully recorded in <code>notification_logs</code></p>
                </div>

                <div style='margin-top:24px;padding-top:16px;border-top:1px solid #f1f5f9;'>
                    " . ($settings['email_signature_html'] ?? '') . "
                </div>
            </div>
        ";

        $mailService->send($recipientEmail, $subject, $body, 'admin_settings_test_email');

        // Record in settings audit trail
        $this->model->recordAuditLog((int)$admin['id'], (string)$admin['name'], 'email', 'TEST_EMAIL_DISPATCH', null, "Sent test to {$recipientEmail}", $_SERVER['REMOTE_ADDR'] ?? '', 'Test Action');

        $_SESSION['success'] = "✓ Test email initiated to <strong>{$recipientEmail}</strong>. Delivery status has been logged in the notification dispatch ledger.";
        redirect('admin/settings?tab=email');
        exit;
    }

    /**
     * Reset a single category to factory defaults
     */
    public function resetCategory(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/settings');
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!CsrfHelper::verify($token)) {
            $_SESSION['error'] = 'Security validation failed (invalid CSRF token).';
            redirect('admin/settings');
            exit;
        }

        $category = strtolower(trim($_POST['category'] ?? ''));
        $res = $this->model->resetCategory($category, (int)$admin['id'], (string)$admin['name'], $_SERVER['REMOTE_ADDR'] ?? '');

        if ($res['success']) {
            $_SESSION['success'] = "✓ Category '{$category}' has been reset to factory baseline defaults.";
        } else {
            $_SESSION['error'] = "Failed to reset category: " . ($res['message'] ?? 'Unknown error');
        }

        redirect('admin/settings?tab=' . urlencode($category));
        exit;
    }

    /**
     * Purge in-memory settings cache
     */
    public function clearCache(): void {
        AdminAuth::requireAuth();
        SiteHelper::clearCache();
        $_SESSION['success'] = '✓ Platform settings cache purged and reloaded.';
        redirect('admin/settings?tab=' . urlencode($_GET['tab'] ?? 'general'));
        exit;
    }

    /**
     * Export non-sensitive settings configuration as JSON download
     */
    public function export(): void {
        AdminAuth::requireAuth();
        $settings = $this->model->getSettings();

        // Strip internal IDs and timestamps
        unset($settings['id'], $settings['created_at'], $settings['updated_at']);

        $filename = 'stayora_platform_settings_' . date('Y-m-d_His') . '.json';
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
