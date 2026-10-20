<?php
namespace App\Controllers\Admin;

require_once __DIR__ . '/../../Helpers/CsrfHelper.php';
require_once __DIR__ . '/../../Helpers/AdminAuth.php';
require_once __DIR__ . '/../../Models/Admin/ReviewManagementModel.php';

use App\Helpers\AdminAuth;
use App\Helpers\CsrfHelper;
use App\Models\Admin\ReviewManagementModel;
use Exception;

class ReviewController {
    protected ReviewManagementModel $reviewModel;

    public function __construct() {
        $this->reviewModel = new ReviewManagementModel();
    }

    /**
     * Display Review Management Dashboard & Moderation Queue
     */
    public function index(): void {
        $admin = AdminAuth::requireAuth();

        // 1. Read & Sanitize Filters
        $filters = [
            'search'       => trim($_GET['search'] ?? ''),
            'status'       => trim($_GET['status'] ?? 'all'),
            'farmhouse_id' => !empty($_GET['farmhouse_id']) ? (int)$_GET['farmhouse_id'] : null,
            'owner_id'     => !empty($_GET['owner_id']) ? (int)$_GET['owner_id'] : null,
            'rating'       => !empty($_GET['rating']) ? (int)$_GET['rating'] : null,
            'verified'     => isset($_GET['verified']) && $_GET['verified'] !== '' ? (int)$_GET['verified'] : '',
            'has_response' => isset($_GET['has_response']) && $_GET['has_response'] !== '' ? $_GET['has_response'] : '',
            'date_from'    => trim($_GET['date_from'] ?? ''),
            'date_to'      => trim($_GET['date_to'] ?? ''),
            'sort'         => trim($_GET['sort'] ?? 'newest'),
        ];

        // 2. Pagination
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;

        $results      = $this->reviewModel->getReviews($filters, $page, $perPage);
        $kpis         = $this->reviewModel->getStats($filters['farmhouse_id'], $filters['owner_id']);
        $properties   = $this->reviewModel->getPropertiesDropdown();

        $totalRecords = $results['total'];
        $totalPages   = $results['total_pages'];
        $reviews      = $results['data'];

        $startItem = $totalRecords > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $endItem   = min($totalRecords, $page * $perPage);

        $pageTitle   = "Guest Reviews & Moderation • Stayora Executive Console";
        $currentPage = "reviews";
        $headerTitle = "Guest Reviews & Feedback";

        require_once __DIR__ . '/../../Views/admin/reviews/index.php';
    }

    /**
     * Fetch complete review details (AJAX or direct)
     */
    public function details(int $id): void {
        $admin = AdminAuth::requireAuth();

        $review = $this->reviewModel->getReviewDetails($id);

        if (!$review) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Review not found.']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'review' => $review]);
    }

    /**
     * Update review moderation status (approve, reject, hide, flag)
     */
    public function updateStatus(): void {
        $admin = AdminAuth::requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
                return;
            }
            redirect('admin/reviews');
            return;
        }

        // CSRF Verification
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CsrfHelper::verify($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid or expired CSRF token.']);
                return;
            }
            $_SESSION['error'] = 'Session security token expired. Please try again.';
            redirect('admin/reviews');
            return;
        }

        $id              = (int)($_POST['review_id'] ?? 0);
        $status          = trim($_POST['status'] ?? '');
        $rejectionReason = trim($_POST['rejection_reason'] ?? '');
        $adminNotes      = isset($_POST['admin_notes']) ? trim($_POST['admin_notes']) : null;

        if ($id <= 0 || empty($status)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Missing review reference or status.']);
                return;
            }
            $_SESSION['error'] = 'Invalid review parameters.';
            redirect('admin/reviews');
            return;
        }

        $adminId   = (int)($admin['id'] ?? 1);
        $adminName = $admin['name'] ?? 'Admin';
        $ip        = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->reviewModel->updateStatus($id, $status, $rejectionReason, $adminNotes, $adminId, $adminName, $ip);

        $statusLabels = [
            'approved' => 'published and approved',
            'rejected' => 'rejected',
            'hidden'   => 'hidden from public view',
            'flagged'  => 'flagged for investigation',
            'pending'  => 'reset to pending',
        ];
        $label = $statusLabels[$status] ?? $status;

        if ($success) {
            $msg = "Review #{$id} successfully {$label}.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg, 'status' => $status]);
                return;
            }
            $_SESSION['success'] = $msg;
        } else {
            $msg = "Failed to update review status. Please try again.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
        }

        redirect('admin/reviews');
    }

    /**
     * Bulk moderate reviews
     */
    public function bulkUpdate(): void {
        $admin = AdminAuth::requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/reviews');
            return;
        }

        // CSRF Verification
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CsrfHelper::verify($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
                return;
            }
            $_SESSION['error'] = 'Security token invalid.';
            redirect('admin/reviews');
            return;
        }

        $ids    = $_POST['review_ids'] ?? [];
        $action = trim($_POST['bulk_action'] ?? '');
        $reason = trim($_POST['bulk_reason'] ?? '');

        if (empty($ids) || !is_array($ids) || empty($action)) {
            $msg = 'Please select at least one review and a moderation action.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            redirect('admin/reviews');
            return;
        }

        $targetStatus = match ($action) {
            'approve' => 'approved',
            'reject'  => 'rejected',
            'hide'    => 'hidden',
            'flag'    => 'flagged',
            default   => null
        };

        if (!$targetStatus) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid bulk action.']);
                return;
            }
            $_SESSION['error'] = 'Invalid bulk action.';
            redirect('admin/reviews');
            return;
        }

        $adminId   = (int)($admin['id'] ?? 1);
        $adminName = $admin['name'] ?? 'Admin';
        $ip        = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $count = $this->reviewModel->bulkUpdateStatus($ids, $targetStatus, $reason, $adminId, $adminName, $ip);

        $msg = "{$count} review(s) successfully marked as {$targetStatus}.";
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $msg, 'count' => $count]);
            return;
        }

        $_SESSION['success'] = $msg;
        redirect('admin/reviews');
    }

    /**
     * Edit review text or title (with audit trail)
     */
    public function editContent(): void {
        $admin = AdminAuth::requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/reviews');
            return;
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CsrfHelper::verify($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
                return;
            }
            $_SESSION['error'] = 'Security token invalid.';
            redirect('admin/reviews');
            return;
        }

        $id          = (int)($_POST['review_id'] ?? 0);
        $reviewTitle = trim($_POST['review_title'] ?? '');
        $reviewText  = trim($_POST['review_text'] ?? '');
        $adminNotes  = trim($_POST['admin_notes'] ?? '');

        if ($id <= 0 || empty($reviewText)) {
            $msg = 'Review text cannot be empty.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            redirect('admin/reviews');
            return;
        }

        $adminId   = (int)($admin['id'] ?? 1);
        $adminName = $admin['name'] ?? 'Admin';
        $ip        = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->reviewModel->updateContent($id, $reviewTitle, $reviewText, $adminNotes, $adminId, $adminName, $ip);

        if ($success) {
            $msg = "Review #{$id} updated with audit trail.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg]);
                return;
            }
            $_SESSION['success'] = $msg;
        } else {
            $msg = "Failed to update review content.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
        }

        redirect('admin/reviews');
    }

    /**
     * Delete review permanently
     */
    public function delete(): void {
        $admin = AdminAuth::requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/reviews');
            return;
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CsrfHelper::verify($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
                return;
            }
            $_SESSION['error'] = 'Security token invalid.';
            redirect('admin/reviews');
            return;
        }

        $id = (int)($_POST['review_id'] ?? 0);
        if ($id <= 0) {
            redirect('admin/reviews');
            return;
        }

        $adminId   = (int)($admin['id'] ?? 1);
        $adminName = $admin['name'] ?? 'Admin';
        $ip        = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->reviewModel->deleteReview($id, $adminId, $adminName, $ip);

        if ($success) {
            $msg = "Review #{$id} has been permanently removed.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg]);
                return;
            }
            $_SESSION['success'] = $msg;
        } else {
            $msg = "Failed to delete review.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
        }

        redirect('admin/reviews');
    }

    /**
     * Add host/management response to review
     */
    public function addResponse(): void {
        $admin = AdminAuth::requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/reviews');
            return;
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CsrfHelper::verify($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
                return;
            }
            $_SESSION['error'] = 'Security token invalid.';
            redirect('admin/reviews');
            return;
        }

        $reviewId      = (int)($_POST['review_id'] ?? 0);
        $responseText  = trim($_POST['response_text'] ?? '');
        $responderName = trim($_POST['responder_name'] ?? ($admin['name'] ?? 'Stayora Management'));
        $responderType = in_array($_POST['responder_type'] ?? '', ['admin', 'owner', 'staff']) ? $_POST['responder_type'] : 'admin';
        $status        = in_array($_POST['status'] ?? '', ['published', 'pending', 'hidden']) ? $_POST['status'] : 'published';

        if ($reviewId <= 0 || empty($responseText)) {
            $msg = 'Response message cannot be empty.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            redirect('admin/reviews');
            return;
        }

        $adminId   = (int)($admin['id'] ?? 1);
        $adminName = $admin['name'] ?? 'Admin';
        $ip        = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->reviewModel->addResponse(
            $reviewId,
            $responderType,
            $adminId,
            $responderName,
            $responseText,
            $status,
            $adminId,
            $adminName,
            $ip
        );

        if ($success) {
            $msg = "Official response published for Review #{$reviewId}.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg]);
                return;
            }
            $_SESSION['success'] = $msg;
        } else {
            $msg = "Failed to publish response.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
        }

        redirect('admin/reviews');
    }

    /**
     * Delete response
     */
    public function deleteResponse(): void {
        $admin = AdminAuth::requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/reviews');
            return;
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CsrfHelper::verify($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
                return;
            }
            $_SESSION['error'] = 'Security token invalid.';
            redirect('admin/reviews');
            return;
        }

        $responseId = (int)($_POST['response_id'] ?? 0);
        if ($responseId <= 0) {
            redirect('admin/reviews');
            return;
        }

        $success = $this->reviewModel->deleteResponse($responseId);

        if ($success) {
            $msg = "Response removed successfully.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg]);
                return;
            }
            $_SESSION['success'] = $msg;
        } else {
            $msg = "Failed to remove response.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
        }

        redirect('admin/reviews');
    }

    /**
     * Export reviews to CSV
     */
    public function export(): void {
        $admin = AdminAuth::requireAuth();

        $filters = [
            'search'       => trim($_GET['search'] ?? ''),
            'status'       => trim($_GET['status'] ?? 'all'),
            'farmhouse_id' => !empty($_GET['farmhouse_id']) ? (int)$_GET['farmhouse_id'] : null,
            'owner_id'     => !empty($_GET['owner_id']) ? (int)$_GET['owner_id'] : null,
            'rating'       => !empty($_GET['rating']) ? (int)$_GET['rating'] : null,
            'verified'     => isset($_GET['verified']) && $_GET['verified'] !== '' ? (int)$_GET['verified'] : '',
            'date_from'    => trim($_GET['date_from'] ?? ''),
            'date_to'      => trim($_GET['date_to'] ?? ''),
            'sort'         => trim($_GET['sort'] ?? 'newest'),
        ];

        $reviews = $this->reviewModel->getExportData($filters);

        $filename = 'stayora_guest_reviews_' . date('Y_m_d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility
        fputs($out, "\xEF\xBB\xBF");

        // CSV Header
        fputcsv($out, [
            'Review ID',
            'Date Submitted',
            'Property Name',
            'Location',
            'Guest Name',
            'Guest Email',
            'Booking Reference',
            'Overall Rating',
            'Cleanliness (1-5)',
            'Location (1-5)',
            'Value (1-5)',
            'Hospitality (1-5)',
            'Verified Stay',
            'Status',
            'Review Title',
            'Review Content',
            'Rejection Reason',
            'Moderator Notes',
            'Host Response Count'
        ]);

        foreach ($reviews as $r) {
            fputcsv($out, [
                '#REV-' . str_pad($r['id'], 5, '0', STR_PAD_LEFT),
                $r['created_at'],
                $r['property_title'] ?? 'N/A',
                $r['property_location'] ?? 'N/A',
                $r['guest_name'],
                $r['guest_email'] ?? 'N/A',
                $r['booking_reference'] ?? 'N/A',
                number_format((float)$r['overall_rating'], 1),
                $r['cleanliness_rating'],
                $r['location_rating'],
                $r['value_rating'],
                $r['hospitality_rating'],
                $r['is_verified_stay'] ? 'Yes' : 'No',
                ucfirst($r['status']),
                $r['review_title'] ?? '',
                $r['review_text'] ?? '',
                $r['rejection_reason'] ?? '',
                $r['admin_notes'] ?? '',
                $r['response_count'] ?? 0
            ]);
        }

        fclose($out);
        exit;
    }
}
