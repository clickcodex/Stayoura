<?php
namespace App\Controllers\Admin;

use App\Config\Database;
use App\Models\Admin\GuestModel;
use App\Helpers\AdminAuth;

/**
 * GuestController: Executive Management of Guests, Members & Verification
 * Stayora Executive Console
 */
class GuestController {
    private GuestModel $guestModel;

    public function __construct() {
        $db = Database::connect();
        $this->guestModel = new GuestModel($db);
    }

    /**
     * Master Guest & Member Directory
     */
    public function index(): void {
        $admin = AdminAuth::requireAuth();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(5, min(100, (int)($_GET['per_page'] ?? 15)));
        $sortBy = trim($_GET['sort_by'] ?? 'created_at');
        $sortOrder = trim($_GET['sort_order'] ?? 'DESC');

        $filters = [
            'search'          => trim($_GET['search'] ?? ''),
            'status'          => trim($_GET['status'] ?? 'all'),
            'guest_type'      => trim($_GET['guest_type'] ?? 'all'),
            'membership_tier' => trim($_GET['membership_tier'] ?? 'all'),
            'kyc_status'      => trim($_GET['kyc_status'] ?? 'all'),
            'city'            => trim($_GET['city'] ?? ''),
            'in_house'        => trim($_GET['in_house'] ?? ''),
            'expiring_soon'   => trim($_GET['expiring_soon'] ?? '')
        ];

        $stats = $this->guestModel->getStats();
        $totalGuests = $this->guestModel->countAll($filters);
        $totalPages = max(1, (int)ceil($totalGuests / $perPage));
        $guests = $this->guestModel->getAll($filters, $page, $perPage, $sortBy, $sortOrder);

        $pageTitle = "Guests & Members • Stayora Executive Console";
        $currentPage = "guests";
        $headerTitle = "Guest & Member Management";

        require_once __DIR__ . '/../../Views/admin/guests/index.php';
    }

    /**
     * Guest Registration Form
     */
    public function create(): void {
        $admin = AdminAuth::requireAuth();

        $suggestedGuestCode = $this->guestModel->generateGuestCode();
        $pageTitle = "Register New Guest • Stayora Executive Console";
        $currentPage = "guests";
        $headerTitle = "Register Guest / Member";

        require_once __DIR__ . '/../../Views/admin/guests/create.php';
    }

    /**
     * Store New Guest / Member (POST)
     */
    public function store(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/guests');
        }

        $adminId = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($phone)) {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Please provide at least a full name and primary phone number.'];
            redirect('admin/guests/create');
        }

        $res = $this->guestModel->create($_POST, $adminId, $adminName);

        if ($res['success']) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => $res['message']];
            redirect('admin/guests/view/' . $res['id']);
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => $res['message']];
            redirect('admin/guests/create');
        }
    }

    /**
     * 360° Comprehensive Profile Dossier
     */
    public function show(): void {
        $admin = AdminAuth::requireAuth();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            redirect('admin/guests');
        }

        $guest = $this->guestModel->getById($id);
        if (!$guest) {
            http_response_code(404);
            die("Guest record not found.");
        }

        $activeTab = trim($_GET['tab'] ?? 'overview');
        $allowedTabs = ['overview', 'bookings', 'stays', 'payments', 'documents', 'reviews', 'activity'];
        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'overview';
        }

        // Load tab-specific datasets
        $bookings = $this->guestModel->getBookings($id);
        $stays = $this->guestModel->getStays($id);
        $payments = $this->guestModel->getPayments($id);
        $documents = $this->guestModel->getDocuments($id);
        $reviews = $this->guestModel->getReviews($id);
        $inquiries = $this->guestModel->getInquiries($id);
        $activityLogs = $this->guestModel->getActivityLogs($id);
        $membershipHistory = $this->guestModel->getMembershipHistory($id);

        $pageTitle = "{$guest['name']} ({$guest['guest_code']}) • Stayora Executive Console";
        $currentPage = "guests";
        $headerTitle = "Guest Profile & Stay Dossier";

        require_once __DIR__ . '/../../Views/admin/guests/show.php';
    }

    /**
     * Profile Edit Form
     */
    public function edit(): void {
        $admin = AdminAuth::requireAuth();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            redirect('admin/guests');
        }

        $guest = $this->guestModel->getById($id);
        if (!$guest) {
            http_response_code(404);
            die("Guest record not found.");
        }

        $pageTitle = "Edit Profile: {$guest['name']} • Stayora Executive Console";
        $currentPage = "guests";
        $headerTitle = "Edit Guest Profile";

        require_once __DIR__ . '/../../Views/admin/guests/edit.php';
    }

    /**
     * Update Guest Profile (POST)
     */
    public function update(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/guests');
        }

        $id = (int)($_POST['id'] ?? 0);
        $adminId = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        if ($id <= 0) {
            redirect('admin/guests');
        }

        $res = $this->guestModel->update($id, $_POST, $adminId, $adminName);

        if ($res['success']) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => $res['message']];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => $res['message']];
        }

        redirect('admin/guests/view/' . $id);
    }

    /**
     * AJAX/POST Status Update (active, blocked)
     */
    public function updateStatus(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $adminId = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        if ($id <= 0 || empty($status)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
            return;
        }

        $res = $this->guestModel->updateStatus($id, $status, $reason, $adminId, $adminName);
        echo json_encode($res);
    }

    /**
     * AJAX/POST KYC Status Update
     */
    public function updateKycStatus(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $kycStatus = trim($_POST['kyc_status'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $adminId = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        if ($id <= 0 || empty($kycStatus)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
            return;
        }

        $res = $this->guestModel->updateKycStatus($id, $kycStatus, $notes, $adminId, $adminName);
        echo json_encode($res);
    }

    /**
     * AJAX/POST Membership Upgrade / Renewal Workflow
     */
    public function updateMembership(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $adminId = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing guest ID.']);
            return;
        }

        $res = $this->guestModel->updateMembership($id, $_POST, $adminId, $adminName);
        echo json_encode($res);
    }

    /**
     * Upload KYC Document
     */
    public function uploadDocument(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/guests');
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $adminId = (int)($admin['id'] ?? 0);

        if ($userId <= 0 || !isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Please select a valid document file (PDF, JPG, PNG, WEBP).'];
            redirect('admin/guests/view/' . $userId . '?tab=documents');
        }

        $res = $this->guestModel->addDocument($userId, $_POST, $_FILES['document_file'], $adminId);

        if ($res['success']) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => $res['message']];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => $res['message']];
        }

        redirect('admin/guests/view/' . $userId . '?tab=documents');
    }

    /**
     * AJAX/POST Verify or Reject Document
     */
    public function verifyDocument(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $docId = (int)($_POST['doc_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $adminId = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        if ($docId <= 0 || empty($status)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing document parameters.']);
            return;
        }

        $res = $this->guestModel->verifyDocument($docId, $status, $reason, $adminId, $adminName);
        echo json_encode($res);
    }

    /**
     * AJAX/POST Delete Document
     */
    public function deleteDocument(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $docId = (int)($_POST['doc_id'] ?? 0);
        $adminId = (int)($admin['id'] ?? 0);

        if ($docId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing document ID.']);
            return;
        }

        $res = $this->guestModel->deleteDocument($docId, $adminId);
        echo json_encode($res);
    }

    /**
     * Append Administrative Staff Note
     */
    public function addNote(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        $adminId = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        if ($userId <= 0 || empty($note)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Note text cannot be empty.']);
            return;
        }

        $res = $this->guestModel->addAdminNote($userId, $note, $adminId, $adminName);
        echo json_encode($res);
    }

    /**
     * Safe Account Deletion (Orphan-Blocked)
     */
    public function delete(): void {
        $admin = AdminAuth::requireAuth();

        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            redirect('admin/guests');
        }

        $res = $this->guestModel->delete($id);

        if ($res['success']) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => $res['message']];
            redirect('admin/guests');
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => $res['message']];
            redirect('admin/guests/view/' . $id);
        }
    }

    /**
     * CSV Data Export matching active filters
     */
    public function export(): void {
        $admin = AdminAuth::requireAuth();

        $filters = [
            'search'          => trim($_GET['search'] ?? ''),
            'status'          => trim($_GET['status'] ?? 'all'),
            'guest_type'      => trim($_GET['guest_type'] ?? 'all'),
            'membership_tier' => trim($_GET['membership_tier'] ?? 'all'),
            'kyc_status'      => trim($_GET['kyc_status'] ?? 'all'),
            'city'            => trim($_GET['city'] ?? ''),
            'in_house'        => trim($_GET['in_house'] ?? ''),
            'expiring_soon'   => trim($_GET['expiring_soon'] ?? '')
        ];

        $guests = $this->guestModel->getAll($filters, 1, 5000, 'name', 'ASC');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="stayora_guests_' . date('Y_m_d_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Guest Code',
            'Full Name',
            'Category',
            'Phone',
            'Alternate Phone',
            'Email',
            'Gender',
            'City',
            'State',
            'Country',
            'Account Status',
            'KYC Status',
            'Government ID Type',
            'Government ID Number',
            'Membership Tier',
            'Membership Number',
            'Membership Status',
            'Membership Expiry',
            'Total Bookings',
            'Total Stays',
            'Total Spend (INR)',
            'Registration Date'
        ]);

        foreach ($guests as $g) {
            fputcsv($out, [
                $g['guest_code'],
                $g['name'],
                ucwords(str_replace('_', ' ', $g['guest_type'] ?? 'guest')),
                $g['phone'],
                $g['alternate_phone'] ?? '',
                $g['email'] ?? '',
                ucfirst($g['gender'] ?? ''),
                $g['city'] ?? '',
                $g['state'] ?? '',
                $g['country'] ?? '',
                ucfirst($g['status']),
                ucfirst($g['kyc_status'] ?? 'unverified'),
                strtoupper(str_replace('_', ' ', $g['gov_id_type'] ?? '')),
                $g['gov_id_number'] ?? '',
                strtoupper($g['membership_tier'] ?? 'NONE'),
                $g['membership_number'] ?? '',
                ucfirst($g['membership_status'] ?? 'none'),
                $g['membership_expiry_date'] ?? '',
                $g['real_bookings_count'] ?? 0,
                $g['real_stays_count'] ?? 0,
                number_format((float)($g['real_total_paid'] ?? 0), 2, '.', ''),
                date('Y-m-d H:i', strtotime($g['created_at']))
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Fast AJAX Endpoint for Booking Autocomplete
     */
    public function searchAjax(): void {
        AdminAuth::requireAuth();
        header('Content-Type: application/json');

        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode([]);
            return;
        }

        $results = $this->guestModel->searchGuestsAjax($q);
        echo json_encode($results);
    }
}
