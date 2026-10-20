<?php
namespace App\Controllers\Admin;

use App\Helpers\AdminAuth;
use App\Models\Admin\BookingModel;
use App\Models\FarmdetailModel;
use App\Config\Database;

class BookingController {
    protected BookingModel $bookingModel;

    public function __construct() {
        $this->bookingModel = new BookingModel();
    }

    /**
     * Master Bookings & Stays Dashboard List
     */
    public function index(): void {
        $admin = AdminAuth::requireAuth();

        // 1. Read Filters
        $filters = [
            'status'         => trim($_GET['status'] ?? 'all'),
            'farmhouse_id'   => !empty($_GET['farmhouse_id']) ? (int)$_GET['farmhouse_id'] : '',
            'payment_status' => trim($_GET['payment_status'] ?? 'all'),
            'check_in_from'  => trim($_GET['check_in_from'] ?? ''),
            'check_in_to'    => trim($_GET['check_in_to'] ?? ''),
            'search'         => trim($_GET['search'] ?? ''),
            'sort'           => trim($_GET['sort'] ?? 'newest'),
        ];

        // 2. Pagination
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $totalRecords = $this->bookingModel->countAll($filters);
        $totalPages   = max(1, (int)ceil($totalRecords / $perPage));
        if ($page > $totalPages && $totalRecords > 0) {
            $page = $totalPages;
        }

        $bookings   = $this->bookingModel->getAll($filters, $page, $perPage);
        $kpis       = $this->bookingModel->getKPIs();
        $farmhouses = $this->bookingModel->getAllActiveFarmhouses();

        $startItem = $totalRecords > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $endItem   = min($totalRecords, $page * $perPage);

        $pageTitle   = "Bookings & Stays Operations • Stayora Executive Console";
        $currentPage = "bookings";
        $headerTitle = "Bookings & Stay Management";

        require_once __DIR__ . '/../../Views/admin/bookings/index.php';
    }

    /**
     * 360-Degree Single Booking Overview Page
     */
    public function show(): void {
        $admin = AdminAuth::requireAuth();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0 && !empty($_GET['ref'])) {
            $b = $this->bookingModel->getByReference($_GET['ref']);
            $id = $b ? (int)$b['id'] : 0;
        }

        if ($id <= 0) {
            redirect('admin/bookings');
        }

        $booking = $this->bookingModel->getById($id);
        if (!$booking) {
            http_response_code(404);
            die("Booking record not found.");
        }

        $pageTitle   = "Booking #{$booking['booking_reference']} • Stayora Executive Console";
        $currentPage = "bookings";
        $headerTitle = "Booking Details & Stay Operations";

        require_once __DIR__ . '/../../Views/admin/bookings/show.php';
    }

    /**
     * Manual Reservation Creation Form (Walk-in / Phone Call)
     */
    public function create(): void {
        $admin = AdminAuth::requireAuth();

        $farmhouses  = $this->bookingModel->getAllActiveFarmhouses();
        $pageTitle   = "Create New Reservation • Stayora Executive Console";
        $currentPage = "bookings";
        $headerTitle = "New Reservation";

        require_once __DIR__ . '/../../Views/admin/bookings/create.php';
    }

    /**
     * Store Manual Reservation (POST)
     */
    public function store(): void {
        $admin = AdminAuth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/bookings');
        }

        $adminId   = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        $data = [
            'farmhouse_id'         => (int)($_POST['farmhouse_id'] ?? 0),
            'customer_name'        => trim($_POST['customer_name'] ?? ''),
            'customer_email'       => trim($_POST['customer_email'] ?? ''),
            'customer_phone'       => trim($_POST['customer_phone'] ?? ''),
            'booking_type'         => in_array($_POST['booking_type'] ?? '', ['complete', 'per_room']) ? $_POST['booking_type'] : 'complete',
            'room_type_id'         => !empty($_POST['room_type_id']) ? (int)$_POST['room_type_id'] : null,
            'rooms'                => max(1, (int)($_POST['rooms'] ?? 1)),
            'guests'               => max(1, (int)($_POST['guests'] ?? 2)),
            'check_in'             => trim($_POST['check_in'] ?? ''),
            'check_out'            => trim($_POST['check_out'] ?? ''),
            'check_in_time'        => trim($_POST['check_in_time'] ?? '14:00:00'),
            'check_out_time'       => trim($_POST['check_out_time'] ?? '11:00:00'),
            'price'                => (float)($_POST['price'] ?? 0),
            'amount_paid'          => (float)($_POST['amount_paid'] ?? 0),
            'payment_method'       => trim($_POST['payment_method'] ?? 'cash'),
            'status'               => in_array($_POST['status'] ?? '', ['confirmed', 'pending', 'checked_in']) ? $_POST['status'] : 'confirmed',
            'assigned_room_number' => trim($_POST['assigned_room_number'] ?? ''),
            'admin_notes'          => trim($_POST['admin_notes'] ?? ''),
            'message'              => trim($_POST['message'] ?? 'Front-Desk Direct Reservation')
        ];

        // Validation
        if (empty($data['farmhouse_id']) || empty($data['customer_name']) || empty($data['check_in']) || empty($data['check_out'])) {
            if ($this->isAjax()) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Please provide property, guest name, and stay dates.']);
                return;
            }
            $_SESSION['error'] = 'Please fill all required booking parameters.';
            redirect('admin/bookings/create');
        }

        $res = $this->bookingModel->createBooking($data, $adminId, $adminName);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode($res);
            return;
        }

        if (!empty($res['success'])) {
            $_SESSION['success'] = "Reservation #{$res['reference']} created successfully.";
            redirect('admin/bookings/view?id=' . $res['id']);
        } else {
            $_SESSION['error'] = $res['error'] ?? 'Could not create booking.';
            redirect('admin/bookings/create');
        }
    }

    /**
     * AJAX Status Transition (Confirm, Reject, Cancel, No-Show)
     */
    public function updateStatus(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id        = (int)($_POST['id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');
        $reason    = trim($_POST['reason'] ?? '');
        $adminId   = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        if ($id <= 0 || empty($newStatus)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing booking ID or target status.']);
            return;
        }

        $res = $this->bookingModel->updateStatus($id, $newStatus, $reason, $adminId, $adminName);
        echo json_encode($res);
    }

    /**
     * AJAX Front-Desk Guest Check-In Workflow
     */
    public function checkIn(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id        = (int)($_POST['id'] ?? 0);
        $adminId   = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        $data = [
            'assigned_room_number' => trim($_POST['assigned_room_number'] ?? ''),
            'guest_id_type'         => trim($_POST['guest_id_type'] ?? 'Aadhaar Card'),
            'guest_id_number'       => trim($_POST['guest_id_number'] ?? ''),
            'actual_check_in_at'    => trim($_POST['actual_check_in_at'] ?? date('Y-m-d H:i:s')),
            'payment_collected'     => (float)($_POST['payment_collected'] ?? 0),
            'payment_method'        => trim($_POST['payment_method'] ?? 'cash'),
            'utr_number'            => trim($_POST['utr_number'] ?? ''),
            'notes'                 => trim($_POST['notes'] ?? '')
        ];

        // Handle ID proof file upload if provided
        if (isset($_FILES['guest_id_document']) && $_FILES['guest_id_document']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['guest_id_document'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (in_array($ext, $allowed, true) && $file['size'] <= 5 * 1024 * 1024) {
                $dir = __DIR__ . '/../../../assets/images/uploads/guest_ids/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $fn = 'id_' . $id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $dir . $fn)) {
                    $data['guest_id_document'] = 'assets/images/uploads/guest_ids/' . $fn;
                }
            }
        }

        $res = $this->bookingModel->checkInGuest($id, $data, $adminId, $adminName);
        echo json_encode($res);
    }

    /**
     * AJAX Front-Desk Guest Check-Out Workflow
     */
    public function checkOut(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id        = (int)($_POST['id'] ?? 0);
        $adminId   = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        $data = [
            'actual_check_out_at' => trim($_POST['actual_check_out_at'] ?? date('Y-m-d H:i:s')),
            'payment_collected'   => (float)($_POST['payment_collected'] ?? 0),
            'payment_method'      => trim($_POST['payment_method'] ?? 'cash'),
            'utr_number'          => trim($_POST['utr_number'] ?? ''),
            'deposit_status'      => in_array($_POST['deposit_status'] ?? '', ['refunded', 'retained']) ? $_POST['deposit_status'] : 'refunded',
            'deposit_notes'       => trim($_POST['deposit_notes'] ?? ''),
            'notes'               => trim($_POST['notes'] ?? '')
        ];

        $res = $this->bookingModel->checkOutGuest($id, $data, $adminId, $adminName);
        echo json_encode($res);
    }

    /**
     * AJAX In-House Stay Extension
     */
    public function extendStay(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id          = (int)($_POST['id'] ?? 0);
        $newCheckOut = trim($_POST['new_check_out'] ?? '');
        $adminId     = (int)($admin['id'] ?? 0);
        $adminName   = $admin['name'] ?? 'Administrator';

        if ($id <= 0 || empty($newCheckOut)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters for stay extension.']);
            return;
        }

        $res = $this->bookingModel->extendStay($id, $newCheckOut, $adminId, $adminName);
        echo json_encode($res);
    }

    /**
     * AJAX Add Extra Incidental Charge
     */
    public function addExtraCharge(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id        = (int)($_POST['id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $amount    = (float)($_POST['amount'] ?? 0);
        $notes     = trim($_POST['notes'] ?? '');
        $adminId   = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        $res = $this->bookingModel->addExtraCharge($id, $title, $amount, $notes, $adminId, $adminName);
        echo json_encode($res);
    }

    /**
     * AJAX Record Payment Installment
     */
    public function recordPayment(): void {
        $admin = AdminAuth::requireAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $id        = (int)($_POST['id'] ?? 0);
        $amount    = (float)($_POST['amount'] ?? 0);
        $method    = trim($_POST['payment_method'] ?? 'cash');
        $type      = trim($_POST['payment_type'] ?? 'balance');
        $utr       = trim($_POST['utr_number'] ?? '');
        $notes     = trim($_POST['notes'] ?? '');
        $adminId   = (int)($admin['id'] ?? 0);
        $adminName = $admin['name'] ?? 'Administrator';

        $proofPath = null;
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['payment_proof'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (in_array($ext, $allowed, true) && $file['size'] <= 5 * 1024 * 1024) {
                $dir = __DIR__ . '/../../../assets/images/uploads/payments/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $fn = 'proof_' . $id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $dir . $fn)) {
                    $proofPath = 'assets/images/uploads/payments/' . $fn;
                }
            }
        }

        $res = $this->bookingModel->recordPayment($id, $amount, $method, $type, $utr, $proofPath, $adminId, $adminName, $notes);
        echo json_encode($res);
    }

    /**
     * Visual Occupancy Calendar View
     */
    public function calendar(): void {
        $admin = AdminAuth::requireAuth();

        $farmhouses  = $this->bookingModel->getAllActiveFarmhouses();
        $selectedId  = !empty($_GET['farmhouse_id']) ? (int)$_GET['farmhouse_id'] : 0;
        $pageTitle   = "Booking Availability Calendar • Stayora Executive Console";
        $currentPage = "bookings";
        $headerTitle = "Occupancy & Availability Calendar";

        require_once __DIR__ . '/../../Views/admin/bookings/calendar.php';
    }

    /**
     * Calendar JSON Data Feed (AJAX)
     */
    public function calendarData(): void {
        AdminAuth::requireAuth();
        header('Content-Type: application/json');

        $farmhouseId = !empty($_GET['farmhouse_id']) ? (int)$_GET['farmhouse_id'] : null;
        $start = trim($_GET['start'] ?? date('Y-m-01'));
        $end   = trim($_GET['end'] ?? date('Y-m-t', strtotime('+1 month')));

        $data = $this->bookingModel->getCalendarBookings($farmhouseId, $start, $end);
        echo json_encode($data);
    }

    /**
     * Analytics & Financial Reports View
     */
    public function reports(): void {
        $admin = AdminAuth::requireAuth();

        $startDate  = trim($_GET['start_date'] ?? date('Y-m-01'));
        $endDate    = trim($_GET['end_date'] ?? date('Y-m-d'));
        $farmId     = !empty($_GET['farmhouse_id']) ? (int)$_GET['farmhouse_id'] : null;

        $farmhouses = $this->bookingModel->getAllActiveFarmhouses();
        $reports    = $this->bookingModel->getReportsData($startDate, $endDate, $farmId);

        $pageTitle   = "Booking & Financial Reports • Stayora Executive Console";
        $currentPage = "bookings";
        $headerTitle = "Reports & Revenue Analytics";

        require_once __DIR__ . '/../../Views/admin/bookings/reports.php';
    }

    /**
     * Official Printable Booking Voucher & Tax Invoice
     */
    public function invoice(): void {
        $admin = AdminAuth::requireAuth();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) redirect('admin/bookings');

        $booking = $this->bookingModel->getById($id);
        if (!$booking) {
            http_response_code(404);
            die("Booking record not found.");
        }

        // Fetch site settings for branding
        $db = Database::connect();
        $siteSettingsRes = $db->query("SELECT * FROM site_settings LIMIT 1");
        $siteSettings = ($siteSettingsRes && $siteSettingsRes->num_rows > 0) ? $siteSettingsRes->fetch_assoc() : [];

        require_once __DIR__ . '/../../Views/booking_voucher.php';
    }

    /**
     * Room types for selected property (AJAX dropdown)
     */
    public function getPropertyRooms(): void {
        AdminAuth::requireAuth();
        header('Content-Type: application/json');

        $farmId = (int)($_GET['farmhouse_id'] ?? 0);
        $rooms = $this->bookingModel->getFarmhouseRoomTypes($farmId);
        echo json_encode(['success' => true, 'rooms' => $rooms]);
    }

    private function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
