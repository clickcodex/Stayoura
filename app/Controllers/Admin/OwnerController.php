<?php
namespace App\Controllers\Admin;

use App\Helpers\AdminAuth;
use App\Models\Admin\OwnerModel;
use Exception;

class OwnerController {
    protected OwnerModel $ownerModel;

    public function __construct() {
        $this->ownerModel = new OwnerModel();
    }

    /**
     * Display Property Owners Dashboard & Directory
     */
    public function index(): void {
        $admin = AdminAuth::requireAuth();

        // ── 1. Read & Sanitize Filters ──
        $filters = [
            'search'     => trim($_GET['search'] ?? ''),
            'status'     => trim($_GET['status'] ?? 'all'),
            'kyc_status' => trim($_GET['kyc_status'] ?? 'all'),
            'owner_type' => trim($_GET['owner_type'] ?? 'all'),
            'city'       => trim($_GET['city'] ?? 'all'),
            'sort'       => trim($_GET['sort'] ?? 'created_desc'),
        ];

        // ── 2. Pagination ──
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $results = $this->ownerModel->getAll($filters, $page, $perPage);
        $kpis    = $this->ownerModel->getStats();
        $cities  = $this->ownerModel->getDistinctCities();

        $totalRecords = $results['total'];
        $totalPages   = $results['totalPages'];
        $owners       = $results['data'];

        $startItem = $totalRecords > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $endItem   = min($totalRecords, $page * $perPage);

        $pageTitle   = "Property Owners Hub • Stayora Executive Console";
        $currentPage = "owners";
        $headerTitle = "Property Owners";

        require_once __DIR__ . '/../../Views/admin/owners/index.php';
    }

    /**
     * Show Add Owner Registration Form
     */
    public function create(): void {
        $admin = AdminAuth::requireAuth();

        $formData = $_SESSION['owner_form_data'] ?? [];
        unset($_SESSION['owner_form_data']);

        $pageTitle   = "Add New Property Owner • Stayora Executive Console";
        $currentPage = "owners";
        $headerTitle = "Register Property Owner";

        require_once __DIR__ . '/../../Views/admin/owners/create.php';
    }

    /**
     * Store New Owner
     */
    public function store(): void {
        $admin = AdminAuth::requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
                return;
            }
            redirect('admin/owners');
            return;
        }

        try {
            $avatarFile = (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) ? $_FILES['avatar'] : null;
            $ownerId = $this->ownerModel->create($_POST, $avatarFile);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => true,
                    'message'  => 'Property owner registered successfully.',
                    'owner_id' => $ownerId,
                    'redirect' => url('admin/owners/view/' . $ownerId),
                ]);
                return;
            }

            $_SESSION['success'] = "Owner registered successfully.";
            redirect('admin/owners/view/' . $ownerId);
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }

            $_SESSION['error'] = $e->getMessage();
            $_SESSION['owner_form_data'] = $_POST;
            redirect('admin/owners/create');
        }
    }

    /**
     * Show 360° Dedicated Owner Profile Dossier
     */
    public function show(): void {
        $admin = AdminAuth::requireAuth();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "Invalid owner reference.";
            redirect('admin/owners');
            return;
        }

        $owner = $this->ownerModel->getById($id);
        if (!$owner) {
            $_SESSION['error'] = "Owner #{$id} not found.";
            redirect('admin/owners');
            return;
        }

        $tab = trim($_GET['tab'] ?? 'properties');
        if (!in_array($tab, ['properties', 'bookings', 'financials', 'documents', 'history', 'reviews'], true)) {
            $tab = 'properties';
        }

        $properties       = $this->ownerModel->getProperties($id);
        $bookings         = $this->ownerModel->getBookings($id, ['status' => $_GET['booking_status'] ?? 'all'], 30);
        $financials       = $this->ownerModel->getFinancialSummary($id);
        $documents        = $this->ownerModel->getDocuments($id);
        $ownershipHistory = $this->ownerModel->getOwnershipHistory($id);
        $activeOwners     = $this->ownerModel->getActiveOwnersList();

        // ── Reviews & Social Proof for Owner's Properties ──
        require_once __DIR__ . '/../../Models/Admin/ReviewManagementModel.php';
        $reviewMgmtModel  = new \App\Models\Admin\ReviewManagementModel();
        $ownerReviews     = $reviewMgmtModel->getOwnerReviews($id);
        $ownerReviewStats = $reviewMgmtModel->getStats(null, $id);

        $pageTitle   = htmlspecialchars($owner['name']) . " • Owner Dossier";
        $currentPage = "owners";
        $headerTitle = "Owner Dossier: " . htmlspecialchars($owner['name']);

        require_once __DIR__ . '/../../Views/admin/owners/show.php';
    }

    /**
     * Show Edit Owner Form
     */
    public function edit(): void {
        $admin = AdminAuth::requireAuth();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "Invalid owner reference.";
            redirect('admin/owners');
            return;
        }

        $owner = $this->ownerModel->getById($id);
        if (!$owner) {
            $_SESSION['error'] = "Owner #{$id} not found.";
            redirect('admin/owners');
            return;
        }

        $pageTitle   = "Edit Owner: " . htmlspecialchars($owner['name']) . " • Stayora Console";
        $currentPage = "owners";
        $headerTitle = "Edit Owner Profile";

        require_once __DIR__ . '/../../Views/admin/owners/edit.php';
    }

    /**
     * Update Owner Profile
     */
    public function update(): void {
        $admin = AdminAuth::requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
                return;
            }
            redirect('admin/owners');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid owner reference.']);
                return;
            }
            $_SESSION['error'] = "Invalid owner reference.";
            redirect('admin/owners');
            return;
        }

        try {
            $avatarFile = (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) ? $_FILES['avatar'] : null;
            $this->ownerModel->update($id, $_POST, $avatarFile);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => true,
                    'message'  => 'Owner profile updated successfully.',
                    'owner_id' => $id,
                    'redirect' => url('admin/owners/view/' . $id),
                ]);
                return;
            }

            $_SESSION['success'] = "Owner profile updated successfully.";
            redirect('admin/owners/view/' . $id);
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }

            $_SESSION['error'] = $e->getMessage();
            redirect('admin/owners/edit/' . $id);
        }
    }

    /**
     * AJAX Endpoint: Update Owner Status (active, inactive, suspended, pending)
     */
    public function updateStatus(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id     = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if ($id <= 0 || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Owner ID and status are required.']);
            return;
        }

        try {
            $ok = $this->ownerModel->updateStatus($id, $status);
            if ($ok) {
                echo json_encode([
                    'success' => true,
                    'message' => "Owner status updated to " . ucfirst($status) . ".",
                    'status'  => $status,
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update owner status.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX Endpoint: Update Owner KYC Verification Status
     */
    public function updateKycStatus(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id        = (int)($_POST['id'] ?? 0);
        $kycStatus = trim($_POST['kyc_status'] ?? '');
        $notes     = trim($_POST['notes'] ?? '');

        if ($id <= 0 || empty($kycStatus)) {
            echo json_encode(['success' => false, 'message' => 'Owner ID and KYC status are required.']);
            return;
        }

        try {
            $ok = $this->ownerModel->updateKycStatus($id, $kycStatus, (int)$admin['id'], $notes);
            if ($ok) {
                echo json_encode([
                    'success'    => true,
                    'message'    => "Owner KYC status updated to " . ucfirst($kycStatus) . ".",
                    'kyc_status' => $kycStatus,
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update KYC status.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Upload and Attach Owner Document
     */
    public function uploadDocument(): void {
        $admin = AdminAuth::requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
                return;
            }
            redirect('admin/owners');
            return;
        }

        $ownerId = (int)($_POST['owner_id'] ?? 0);
        if ($ownerId <= 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Owner ID is required.']);
                return;
            }
            $_SESSION['error'] = "Owner ID is required.";
            redirect('admin/owners');
            return;
        }

        if (empty($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
            $msg = "Please select a valid document file to upload.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            redirect('admin/owners/view/' . $ownerId . '?tab=documents');
            return;
        }

        try {
            $docId = $this->ownerModel->addDocument($ownerId, $_POST, $_FILES['document_file'], (int)$admin['id']);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'     => true,
                    'message'     => 'Document uploaded and logged successfully.',
                    'document_id' => $docId,
                ]);
                return;
            }

            $_SESSION['success'] = "Document uploaded successfully.";
            redirect('admin/owners/view/' . $ownerId . '?tab=documents');
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }

            $_SESSION['error'] = $e->getMessage();
            redirect('admin/owners/view/' . $ownerId . '?tab=documents');
        }
    }

    /**
     * AJAX Endpoint: Verify or Reject Document
     */
    public function verifyDocument(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $docId  = (int)($_POST['document_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $reason = trim($_POST['rejection_reason'] ?? '');

        if ($docId <= 0 || !in_array($status, ['verified', 'rejected', 'pending'], true)) {
            echo json_encode(['success' => false, 'message' => 'Valid document ID and verification status required.']);
            return;
        }

        try {
            $ok = $this->ownerModel->verifyDocument($docId, $status, $reason ?: null, (int)$admin['id']);
            if ($ok) {
                echo json_encode([
                    'success' => true,
                    'message' => "Document marked as " . ucfirst($status) . ".",
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update document status.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX Endpoint: Delete Document
     */
    public function deleteDocument(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $docId = (int)($_POST['document_id'] ?? 0);
        if ($docId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Valid document ID required.']);
            return;
        }

        try {
            $ok = $this->ownerModel->deleteDocument($docId);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Document deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete document.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX Endpoint: Reassign Property to another owner
     */
    public function reassignProperty(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $farmhouseId = (int)($_POST['farmhouse_id'] ?? 0);
        $newOwnerId  = (int)($_POST['new_owner_id'] ?? 0);
        $reason      = trim($_POST['transfer_reason'] ?? '');
        $notes       = trim($_POST['notes'] ?? '');

        if ($farmhouseId <= 0 || $newOwnerId <= 0 || empty($reason)) {
            echo json_encode([
                'success' => false,
                'message' => 'Property, target owner, and transfer reason are mandatory.'
            ]);
            return;
        }

        try {
            $ok = $this->ownerModel->reassignProperty($farmhouseId, $newOwnerId, (int)$admin['id'], $reason, $notes ?: null);
            if ($ok) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Property ownership successfully reassigned and recorded.',
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reassign property.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete Owner (Orphan Prevention Protected)
     */
    public function delete(): void {
        $admin = AdminAuth::requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_GET['format']) && $_GET['format'] === 'json');

        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid owner ID.']);
                return;
            }
            $_SESSION['error'] = "Invalid owner ID.";
            redirect('admin/owners');
            return;
        }

        try {
            $this->ownerModel->delete($id);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => true,
                    'message'  => 'Owner record deleted successfully.',
                    'redirect' => url('admin/owners'),
                ]);
                return;
            }

            $_SESSION['success'] = "Owner record deleted successfully.";
            redirect('admin/owners');
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }

            $_SESSION['error'] = $e->getMessage();
            redirect('admin/owners');
        }
    }

    /**
     * Export Owners to CSV
     */
    public function export(): void {
        $admin = AdminAuth::requireAuth();

        $filters = [
            'search'     => trim($_GET['search'] ?? ''),
            'status'     => trim($_GET['status'] ?? 'all'),
            'kyc_status' => trim($_GET['kyc_status'] ?? 'all'),
            'owner_type' => trim($_GET['owner_type'] ?? 'all'),
            'city'       => trim($_GET['city'] ?? 'all'),
            'sort'       => trim($_GET['sort'] ?? 'created_desc'),
        ];

        // Fetch all matching without pagination limit
        $results = $this->ownerModel->getAll($filters, 1, 10000);
        $data = $results['data'];

        $filename = "stayora_property_owners_" . date('Y-m-d_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // CSV Header Row
        fputcsv($out, [
            'Owner ID',
            'Name',
            'Business Name',
            'Type',
            'Email',
            'Phone',
            'Alternate Phone',
            'City',
            'State',
            'Country',
            'Status',
            'KYC Status',
            'Properties Count',
            'Gross Booking Value (INR)',
            'Total Collected (INR)',
            'Bank Name',
            'Bank Account',
            'IFSC',
            'Registered Date'
        ]);

        foreach ($data as $row) {
            fputcsv($out, [
                $row['id'],
                $row['name'],
                $row['business_name'] ?? 'N/A',
                ucfirst($row['owner_type']),
                $row['email'],
                $row['phone'],
                $row['alternate_phone'] ?? '',
                $row['city'] ?? '',
                $row['state'] ?? '',
                $row['country'] ?? 'India',
                ucfirst($row['status']),
                ucfirst($row['kyc_status']),
                (int)$row['properties_count'],
                number_format((float)$row['gross_booking_value'], 2, '.', ''),
                number_format((float)$row['total_collected'], 2, '.', ''),
                $row['bank_name'] ?? '',
                $row['bank_account_number'] ?? '',
                $row['bank_ifsc'] ?? '',
                $row['created_at'],
            ]);
        }

        fclose($out);
        exit;
    }
}
