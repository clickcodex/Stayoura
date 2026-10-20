<?php
namespace App\Models\Admin;

use App\Config\Database;
use App\Models\FarmdetailModel;
use mysqli;
use Exception;

class BookingModel {
    protected mysqli $db;

    public function __construct(?mysqli $db = null) {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Get paginated and filtered bookings for admin management
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = 10): array {
        $where = [];
        $params = [];
        $types = "";

        // Status Filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $st = strtolower(trim($filters['status']));
            if ($st === 'active_stays' || $st === 'checked_in') {
                $where[] = "br.status = 'checked_in'";
            } elseif ($st === 'confirmed') {
                $where[] = "br.status IN ('confirmed', 'approved')";
            } elseif ($st === 'completed' || $st === 'checked_out') {
                $where[] = "br.status IN ('checked_out', 'completed')";
            } elseif ($st === 'cancelled_rejected') {
                $where[] = "br.status IN ('cancelled', 'rejected', 'no_show')";
            } else {
                $where[] = "br.status = ?";
                $types .= "s";
                $params[] = $st;
            }
        }

        // Property Filter
        if (!empty($filters['farmhouse_id']) && (int)$filters['farmhouse_id'] > 0) {
            $where[] = "br.farmhouse_id = ?";
            $types .= "i";
            $params[] = (int)$filters['farmhouse_id'];
        }

        // Payment Status Filter
        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $where[] = "br.payment_status = ?";
            $types .= "s";
            $params[] = trim($filters['payment_status']);
        }

        // Date Range Filters
        if (!empty($filters['check_in_from'])) {
            $where[] = "br.check_in >= ?";
            $types .= "s";
            $params[] = trim($filters['check_in_from']);
        }
        if (!empty($filters['check_in_to'])) {
            $where[] = "br.check_in <= ?";
            $types .= "s";
            $params[] = trim($filters['check_in_to']);
        }

        // Free Text Search
        if (!empty($filters['search'])) {
            $q = '%' . strtolower(trim($filters['search'])) . '%';
            $where[] = "(
                LOWER(br.booking_reference) LIKE ? OR 
                LOWER(u.name) LIKE ? OR 
                LOWER(u.email) LIKE ? OR 
                LOWER(u.phone) LIKE ? OR 
                LOWER(f.title) LIKE ? OR
                LOWER(br.assigned_room_number) LIKE ?
            )";
            $types .= "ssssss";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Sorting
        $orderBy = match ($filters['sort'] ?? 'newest') {
            'oldest'        => "br.id ASC",
            'check_in_asc'  => "br.check_in ASC, br.id ASC",
            'check_in_desc' => "br.check_in DESC, br.id DESC",
            'amount_desc'   => "br.price DESC",
            'amount_asc'    => "br.price ASC",
            default         => "br.id DESC"
        };

        $offset = max(0, ($page - 1) * $perPage);
        $types .= "ii";
        $params[] = $perPage;
        $params[] = $offset;

        $sql = "SELECT 
                    br.*,
                    u.name AS customer_name,
                    u.email AS customer_email,
                    u.phone AS customer_phone,
                    u.profile_image AS customer_avatar,
                    f.title AS farmhouse_title,
                    f.location AS farmhouse_location,
                    f.rental_type AS farmhouse_rental_type,
                    COALESCE(rt.room_type_name, br.room_type_name) AS resolved_room_name,
                    (SELECT image_url FROM images WHERE farmhouse_id = f.id ORDER BY is_featured DESC, sort_order ASC, id ASC LIMIT 1) AS farmhouse_image,
                    DATEDIFF(br.check_out, br.check_in) AS total_nights
                FROM booking_requests br
                LEFT JOIN users u ON br.user_id = u.id
                LEFT JOIN farmhouses f ON br.farmhouse_id = f.id
                LEFT JOIN farmhouse_room_types rt ON br.room_type_id = rt.id
                {$whereClause}
                ORDER BY {$orderBy}
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Count total bookings matching filters
     */
    public function countAll(array $filters = []): int {
        $where = [];
        $params = [];
        $types = "";

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $st = strtolower(trim($filters['status']));
            if ($st === 'active_stays' || $st === 'checked_in') {
                $where[] = "br.status = 'checked_in'";
            } elseif ($st === 'confirmed') {
                $where[] = "br.status IN ('confirmed', 'approved')";
            } elseif ($st === 'completed' || $st === 'checked_out') {
                $where[] = "br.status IN ('checked_out', 'completed')";
            } elseif ($st === 'cancelled_rejected') {
                $where[] = "br.status IN ('cancelled', 'rejected', 'no_show')";
            } else {
                $where[] = "br.status = ?";
                $types .= "s";
                $params[] = $st;
            }
        }

        if (!empty($filters['farmhouse_id']) && (int)$filters['farmhouse_id'] > 0) {
            $where[] = "br.farmhouse_id = ?";
            $types .= "i";
            $params[] = (int)$filters['farmhouse_id'];
        }

        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $where[] = "br.payment_status = ?";
            $types .= "s";
            $params[] = trim($filters['payment_status']);
        }

        if (!empty($filters['check_in_from'])) {
            $where[] = "br.check_in >= ?";
            $types .= "s";
            $params[] = trim($filters['check_in_from']);
        }
        if (!empty($filters['check_in_to'])) {
            $where[] = "br.check_in <= ?";
            $types .= "s";
            $params[] = trim($filters['check_in_to']);
        }

        if (!empty($filters['search'])) {
            $q = '%' . strtolower(trim($filters['search'])) . '%';
            $where[] = "(
                LOWER(br.booking_reference) LIKE ? OR 
                LOWER(u.name) LIKE ? OR 
                LOWER(u.email) LIKE ? OR 
                LOWER(u.phone) LIKE ? OR 
                LOWER(f.title) LIKE ? OR
                LOWER(br.assigned_room_number) LIKE ?
            )";
            $types .= "ssssss";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT COUNT(*) AS total
                FROM booking_requests br
                LEFT JOIN users u ON br.user_id = u.id
                LEFT JOIN farmhouses f ON br.farmhouse_id = f.id
                {$whereClause}";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return 0;

        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int)($res['total'] ?? 0);
    }

    /**
     * Get single booking with all rich details, payments, extra charges, and audit trail
     */
    public function getById(int $id): ?array {
        $sql = "SELECT 
                    br.*,
                    u.name AS customer_name,
                    u.email AS customer_email,
                    u.phone AS customer_phone,
                    u.profile_image AS customer_avatar,
                    u.created_at AS customer_since,
                    f.title AS farmhouse_title,
                    f.location AS farmhouse_location,
                    f.address AS farmhouse_address,
                    f.contact_phone AS farmhouse_contact,
                    f.rental_type AS farmhouse_rental_type,
                    f.price AS farmhouse_base_price,
                    f.weekend_price AS farmhouse_weekend_price,
                    COALESCE(rt.room_type_name, br.room_type_name) AS resolved_room_name,
                    COALESCE(rt.price_per_room, br.price_per_room) AS resolved_room_price,
                    (SELECT image_url FROM images WHERE farmhouse_id = f.id ORDER BY is_featured DESC, sort_order ASC, id ASC LIMIT 1) AS farmhouse_image,
                    DATEDIFF(br.check_out, br.check_in) AS total_nights,
                    admin_in.name AS checked_in_by_name,
                    admin_out.name AS checked_out_by_name
                FROM booking_requests br
                LEFT JOIN users u ON br.user_id = u.id
                LEFT JOIN farmhouses f ON br.farmhouse_id = f.id
                LEFT JOIN farmhouse_room_types rt ON br.room_type_id = rt.id
                LEFT JOIN admins admin_in ON br.checked_in_by = admin_in.id
                LEFT JOIN admins admin_out ON br.checked_out_by = admin_out.id
                WHERE br.id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$booking) return null;

        // Ensure booking_reference exists
        if (empty($booking['booking_reference'])) {
            $booking['booking_reference'] = 'STAY-BK-' . str_pad($booking['id'], 5, '0', STR_PAD_LEFT);
        }

        // Attach payments, extra charges, and audit logs
        $booking['payments']      = $this->getPayments($id);
        $booking['extra_charges'] = $this->getExtraCharges($id);
        $booking['audit_logs']    = $this->getAuditLogs($id);

        return $booking;
    }

    /**
     * Get booking by reference code
     */
    public function getByReference(string $ref): ?array {
        $ref = trim($ref);
        $stmt = $this->db->prepare("SELECT id FROM booking_requests WHERE booking_reference = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("s", $ref);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            // Also check if numeric ID is given e.g. STAY-BK-00001
            if (preg_match('/(\d+)$/', $ref, $m)) {
                return $this->getById((int)$m[1]);
            }
            return null;
        }

        return $this->getById((int)$row['id']);
    }

    /**
     * Executive KPI Statistics for Bookings & Stay Management Dashboard
     */
    public function getKPIs(): array {
        $today = date('Y-m-d');
        $kpis = [
            'total_bookings'          => 0,
            'pending_requests'        => 0,
            'confirmed_upcoming'      => 0,
            'active_in_house'         => 0,
            'expected_checkouts_today'=> 0,
            'completed_stays'         => 0,
            'cancelled_bookings'      => 0,
            'total_gross_revenue'     => 0.00,
            'total_amount_collected'  => 0.00,
            'total_outstanding_due'   => 0.00,
        ];

        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_cnt,
                    SUM(CASE WHEN status IN ('confirmed', 'approved') AND check_in >= '{$today}' THEN 1 ELSE 0 END) AS confirmed_cnt,
                    SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) AS in_house_cnt,
                    SUM(CASE WHEN status = 'checked_in' AND check_out = '{$today}' THEN 1 ELSE 0 END) AS departures_today_cnt,
                    SUM(CASE WHEN status IN ('checked_out', 'completed') THEN 1 ELSE 0 END) AS completed_cnt,
                    SUM(CASE WHEN status IN ('cancelled', 'rejected', 'no_show') THEN 1 ELSE 0 END) AS cancelled_cnt,
                    SUM(CASE WHEN status NOT IN ('cancelled', 'rejected') THEN price ELSE 0 END) AS gross_rev,
                    SUM(CASE WHEN status NOT IN ('cancelled', 'rejected') THEN amount_paid ELSE 0 END) AS paid_rev,
                    SUM(CASE WHEN status NOT IN ('cancelled', 'rejected') THEN balance_amount ELSE 0 END) AS balance_rev
                FROM booking_requests";

        $res = $this->db->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            $kpis['total_bookings']           = (int)($row['total'] ?? 0);
            $kpis['pending_requests']         = (int)($row['pending_cnt'] ?? 0);
            $kpis['confirmed_upcoming']       = (int)($row['confirmed_cnt'] ?? 0);
            $kpis['active_in_house']          = (int)($row['in_house_cnt'] ?? 0);
            $kpis['expected_checkouts_today'] = (int)($row['departures_today_cnt'] ?? 0);
            $kpis['completed_stays']          = (int)($row['completed_cnt'] ?? 0);
            $kpis['cancelled_bookings']       = (int)($row['cancelled_cnt'] ?? 0);
            $kpis['total_gross_revenue']      = (float)($row['gross_rev'] ?? 0.00);
            $kpis['total_amount_collected']   = (float)($row['paid_rev'] ?? 0.00);
            $kpis['total_outstanding_due']    = (float)($row['balance_rev'] ?? 0.00);
        }

        return $kpis;
    }

    /**
     * Strict Mathematical Availability & Conflict Verification Matrix
     */
    public function checkAvailability(
        int $farmhouseId, 
        string $checkIn, 
        string $checkOut, 
        string $bookingType = 'complete', 
        ?int $roomTypeId = null, 
        int $requestedRooms = 1,
        ?int $excludeBookingId = null
    ): array {
        // Validate dates
        $cInTs = strtotime($checkIn);
        $cOutTs = strtotime($checkOut);
        if (!$cInTs || !$cOutTs || $cOutTs <= $cInTs) {
            return ['available' => false, 'message' => 'Check-out date must be later than check-in date.'];
        }

        // 1. Check admin / owner blocked dates
        $bSql = "SELECT COUNT(*) AS cnt FROM blocked_dates WHERE farmhouse_id = ? AND start_date < ? AND end_date > ?";
        $bStmt = $this->db->prepare($bSql);
        if ($bStmt) {
            $bStmt->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
            $bStmt->execute();
            $bRow = $bStmt->get_result()->fetch_assoc();
            $bStmt->close();
            if ((int)($bRow['cnt'] ?? 0) > 0) {
                return ['available' => false, 'message' => 'The selected dates are blocked by the property owner/admin for maintenance or private events.'];
            }
        }

        // 2. Fetch property details
        $fStmt = $this->db->prepare("SELECT rental_type, allow_room_booking, night_capacity, bedrooms FROM farmhouses WHERE id = ?");
        if (!$fStmt) return ['available' => false, 'message' => 'Database error checking property.'];
        $fStmt->bind_param("i", $farmhouseId);
        $fStmt->execute();
        $farmhouse = $fStmt->get_result()->fetch_assoc();
        $fStmt->close();

        if (!$farmhouse) {
            return ['available' => false, 'message' => 'Property not found.'];
        }

        $propRentalType = $farmhouse['rental_type'] ?? (!empty($farmhouse['allow_room_booking']) ? 'both' : 'complete');
        if ($bookingType === 'complete' && $propRentalType === 'room_wise') {
            return ['available' => false, 'message' => 'This property only supports room-wise bookings. Please choose a room type.'];
        }
        if ($bookingType === 'per_room' && $propRentalType === 'complete') {
            return ['available' => false, 'message' => 'This property only supports complete property bookings.'];
        }

        // Build exclusion clause if updating an existing booking
        $excludeSql = $excludeBookingId && $excludeBookingId > 0 ? " AND id != {$excludeBookingId}" : "";

        // 3. Double Booking Conflict Matrix
        if ($bookingType === 'complete') {
            // Complete Estate Booking: Clashes if ANY active booking exists (complete OR any room)
            $sql = "SELECT COUNT(*) AS cnt 
                    FROM booking_requests 
                    WHERE farmhouse_id = ? 
                      AND status IN ('confirmed', 'approved', 'checked_in')
                      AND check_in < ? 
                      AND check_out > ?
                      {$excludeSql}";
            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ((int)($row['cnt'] ?? 0) > 0) {
                    return [
                        'available' => false,
                        'message' => 'The entire property cannot be booked because overlapping reservations already occupy the estate for these dates.'
                    ];
                }
            }
        } else {
            // Room-Wise Booking:
            // A) Clashes if the entire property is booked
            $sql = "SELECT COUNT(*) AS cnt 
                    FROM booking_requests 
                    WHERE farmhouse_id = ? 
                      AND booking_type = 'complete'
                      AND status IN ('confirmed', 'approved', 'checked_in')
                      AND check_in < ? 
                      AND check_out > ?
                      {$excludeSql}";
            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ((int)($row['cnt'] ?? 0) > 0) {
                    return [
                        'available' => false,
                        'message' => 'The entire estate is reserved by a whole-property booking for these dates.'
                    ];
                }
            }

            // B) Clashes if requested room type inventory is depleted
            if ($roomTypeId && $roomTypeId > 0) {
                $rtStmt = $this->db->prepare("SELECT total_rooms, room_type_name FROM farmhouse_room_types WHERE id = ? AND farmhouse_id = ?");
                if ($rtStmt) {
                    $rtStmt->bind_param("ii", $roomTypeId, $farmhouseId);
                    $rtStmt->execute();
                    $rt = $rtStmt->get_result()->fetch_assoc();
                    $rtStmt->close();

                    $totalUnits = (int)($rt['total_rooms'] ?? 1);
                    $rtName = $rt['room_type_name'] ?? 'Selected Room';

                    $bkStmt = $this->db->prepare(
                        "SELECT COALESCE(SUM(rooms), 0) AS booked_rooms 
                         FROM booking_requests 
                         WHERE farmhouse_id = ? 
                           AND room_type_id = ?
                           AND status IN ('confirmed', 'approved', 'checked_in')
                           AND check_in < ? 
                           AND check_out > ?
                           {$excludeSql}"
                    );
                    if ($bkStmt) {
                        $bkStmt->bind_param("iiss", $farmhouseId, $roomTypeId, $checkOut, $checkIn);
                        $bkStmt->execute();
                        $bkRow = $bkStmt->get_result()->fetch_assoc();
                        $bkStmt->close();

                        $alreadyBooked = (int)($bkRow['booked_rooms'] ?? 0);
                        $availableUnits = max(0, $totalUnits - $alreadyBooked);

                        if ($requestedRooms > $availableUnits) {
                            return [
                                'available' => false,
                                'message' => "Only {$availableUnits} unit(s) of '{$rtName}' available for the selected dates (you requested {$requestedRooms})."
                            ];
                        }
                    }
                }
            }
        }

        return ['available' => true, 'message' => 'Dates are fully available.'];
    }

    /**
     * Create a new booking directly by Administrator (Walk-In / Phone Reservation)
     */
    public function createBooking(array $data, int $adminId, ?string $adminName = null): array {
        $this->db->begin_transaction();

        try {
            $farmhouseId  = (int)($data['farmhouse_id'] ?? 0);
            $bookingType  = in_array($data['booking_type'] ?? '', ['complete', 'per_room']) ? $data['booking_type'] : 'complete';
            $roomTypeId   = !empty($data['room_type_id']) ? (int)$data['room_type_id'] : null;
            $rooms        = max(1, (int)($data['rooms'] ?? 1));
            $guests       = max(1, (int)($data['guests'] ?? 2));
            $checkIn      = trim($data['check_in'] ?? '');
            $checkOut     = trim($data['check_out'] ?? '');
            $checkInTime  = !empty($data['check_in_time']) ? trim($data['check_in_time']) : '14:00:00';
            $checkOutTime = !empty($data['check_out_time']) ? trim($data['check_out_time']) : '11:00:00';

            // 1. Concurrency Row Lock
            $lockStmt = $this->db->prepare("SELECT id FROM farmhouses WHERE id = ? FOR UPDATE");
            if ($lockStmt) {
                $lockStmt->bind_param("i", $farmhouseId);
                $lockStmt->execute();
                $lockStmt->close();
            }

            // 2. Availability Check
            $avail = $this->checkAvailability($farmhouseId, $checkIn, $checkOut, $bookingType, $roomTypeId, $rooms);
            if (!$avail['available']) {
                $this->db->rollback();
                return ['success' => false, 'error' => $avail['message']];
            }

            // 3. User Resolution: Find by email/phone or create on the fly
            $userId = !empty($data['user_id']) ? (int)$data['user_id'] : 0;
            if ($userId <= 0) {
                $cName  = trim($data['customer_name'] ?? 'Guest Customer');
                $cEmail = trim($data['customer_email'] ?? '');
                $cPhone = trim($data['customer_phone'] ?? '');

                if (!empty($cEmail) || !empty($cPhone)) {
                    $uCheck = $this->db->prepare("SELECT id FROM users WHERE (email != '' AND email = ?) OR (phone != '' AND phone = ?) LIMIT 1");
                    if ($uCheck) {
                        $uCheck->bind_param("ss", $cEmail, $cPhone);
                        $uCheck->execute();
                        $uRow = $uCheck->get_result()->fetch_assoc();
                        $uCheck->close();
                        if ($uRow) {
                            $userId = (int)$uRow['id'];
                        }
                    }
                }

                if ($userId <= 0) {
                    $dummyPass = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
                    $uIns = $this->db->prepare("INSERT INTO users (name, email, phone, password, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
                    if ($uIns) {
                        $uIns->bind_param("ssss", $cName, $cEmail, $cPhone, $dummyPass);
                        $uIns->execute();
                        $userId = $uIns->insert_id;
                        $uIns->close();
                    }
                }
            }

            // 4. Resolve room type name & pricing
            $roomTypeName = null;
            $pricePerRoom = null;
            if ($bookingType === 'per_room' && $roomTypeId) {
                $rtStmt = $this->db->prepare("SELECT room_type_name, price_per_room FROM farmhouse_room_types WHERE id = ?");
                if ($rtStmt) {
                    $rtStmt->bind_param("i", $roomTypeId);
                    $rtStmt->execute();
                    $rt = $rtStmt->get_result()->fetch_assoc();
                    $rtStmt->close();
                    if ($rt) {
                        $roomTypeName = $rt['room_type_name'];
                        $pricePerRoom = (float)$rt['price_per_room'];
                    }
                }
            }

            // Calculate Price using quote engine or manual override
            $price = isset($data['price']) && (float)$data['price'] > 0 ? (float)$data['price'] : 0.00;
            if ($price <= 0) {
                $farmDetailModel = new FarmdetailModel($this->db);
                $quote = $farmDetailModel->calculateQuote([
                    'farmhouse_id' => $farmhouseId,
                    'booking_type' => $bookingType,
                    'room_type_id' => $roomTypeId,
                    'rooms'        => $rooms,
                    'guests'       => $guests,
                    'check_in'     => $checkIn,
                    'check_out'    => $checkOut
                ]);
                $price = (float)($quote['total_payable'] ?? 0);
            }

            $advancePaid = max(0, (float)($data['amount_paid'] ?? 0));
            $balanceDue  = max(0, $price - $advancePaid);
            $payStatus   = $advancePaid >= $price ? 'paid' : ($advancePaid > 0 ? 'pending_verification' : 'unpaid');
            $payMethod   = $data['payment_method'] ?? 'cash';
            $status      = in_array($data['status'] ?? '', ['confirmed', 'pending', 'checked_in']) ? $data['status'] : 'confirmed';

            // Insert booking request record
            $sql = "INSERT INTO booking_requests (
                        user_id, farmhouse_id, booking_type, room_type_id, room_type_name, price_per_room,
                        check_in, check_in_time, check_out, check_out_time, start_date, end_date,
                        guests, rooms, price, amount_paid, balance_amount, status, payment_method, payment_status,
                        assigned_room_number, admin_notes, message, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Database prepare failed: ' . $this->db->error];
            }

            $msg = $data['message'] ?? 'Admin Front-Desk Reservation';
            $adminNotes = $data['admin_notes'] ?? '';
            $assignedRoom = $data['assigned_room_number'] ?? '';

            $stmt->bind_param(
                "iisisdssssssiidddssssss",
                $userId,
                $farmhouseId,
                $bookingType,
                $roomTypeId,
                $roomTypeName,
                $pricePerRoom,
                $checkIn,
                $checkInTime,
                $checkOut,
                $checkOutTime,
                $checkIn,
                $checkOut,
                $guests,
                $rooms,
                $price,
                $advancePaid,
                $balanceDue,
                $status,
                $payMethod,
                $payStatus,
                $assignedRoom,
                $adminNotes,
                $msg
            );

            $stmt->execute();
            $newId = $stmt->insert_id;
            $stmt->close();

            // Generate unique reference
            $ref = 'STAY-BK-' . str_pad($newId, 5, '0', STR_PAD_LEFT);
            $this->db->query("UPDATE booking_requests SET booking_reference = '{$ref}' WHERE id = {$newId}");

            // Record initial payment ledger entry if advance is paid
            if ($advancePaid > 0) {
                $payStmt = $this->db->prepare("INSERT INTO payments (booking_id, farmhouse_id, user_id, amount, payment_type, payment_method, payment_status, verified_by, verified_at, created_at) VALUES (?, ?, ?, ?, 'advance', ?, 'paid', ?, NOW(), NOW())");
                if ($payStmt) {
                    $payStmt->bind_param("iiidsi", $newId, $farmhouseId, $userId, $advancePaid, $payMethod, $adminId);
                    $payStmt->execute();
                    $payStmt->close();
                }
            }

            // Log Audit Entry
            $this->logAudit(
                $newId,
                'created',
                null,
                $status,
                'admin',
                $adminId,
                $adminName ?? 'Administrator',
                "Created new reservation {$ref} (Total: ₹{$price}, Paid: ₹{$advancePaid})"
            );

            $this->db->commit();
            return ['success' => true, 'id' => $newId, 'reference' => $ref];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update Booking status with strict state machine validation and audit trail
     */
    public function updateStatus(int $id, string $newStatus, ?string $reason = null, int $adminId = 0, ?string $adminName = null): array {
        $booking = $this->getById($id);
        if (!$booking) {
            return ['success' => false, 'message' => 'Booking record not found.'];
        }

        $currentStatus = $booking['status'];
        $newStatus     = strtolower(trim($newStatus));

        // State Machine Guardrails
        $allowedTransitions = [
            'pending'     => ['confirmed', 'approved', 'rejected', 'cancelled'],
            'approved'    => ['confirmed', 'checked_in', 'cancelled', 'no_show'],
            'confirmed'   => ['checked_in', 'cancelled', 'no_show'],
            'checked_in'  => ['checked_out', 'completed'],
            'checked_out' => [], // terminal
            'completed'   => [], // terminal
            'cancelled'   => [], // terminal
            'rejected'    => [], // terminal
            'no_show'     => []  // terminal
        ];

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus], true)) {
            return [
                'success' => false,
                'message' => "Invalid status transition from '{$currentStatus}' to '{$newStatus}'."
            ];
        }

        // If transitioning to Confirmed, re-verify availability
        if ($newStatus === 'confirmed' || $newStatus === 'approved') {
            $avail = $this->checkAvailability(
                (int)$booking['farmhouse_id'],
                $booking['check_in'],
                $booking['check_out'],
                $booking['booking_type'],
                $booking['room_type_id'],
                (int)$booking['rooms'],
                $id
            );
            if (!$avail['available']) {
                return ['success' => false, 'message' => 'Cannot confirm booking: ' . $avail['message']];
            }
        }

        $cancellationFields = "";
        if ($newStatus === 'cancelled' || $newStatus === 'rejected') {
            $cancellationFields = ", cancellation_reason = " . ($reason ? "'" . $this->db->real_escape_string($reason) . "'" : "NULL") . ", cancelled_at = NOW(), cancelled_by = 'admin'";
        }

        $sql = "UPDATE booking_requests SET status = '{$newStatus}' {$cancellationFields} WHERE id = {$id}";
        $ok = $this->db->query($sql);

        if ($ok) {
            $this->logAudit(
                $id,
                'status_change',
                $currentStatus,
                $newStatus,
                'admin',
                $adminId,
                $adminName ?? 'Administrator',
                $reason ?: "Status transitioned to {$newStatus}"
            );

            return ['success' => true, 'message' => "Booking status updated to " . ucfirst(str_replace('_', ' ', $newStatus)) . "."];
        }

        return ['success' => false, 'message' => 'Failed to update booking status.'];
    }

    /**
     * Front-Desk Operational Check-In Workflow
     */
    public function checkInGuest(int $id, array $data, int $adminId, ?string $adminName = null): array {
        $booking = $this->getById($id);
        if (!$booking) return ['success' => false, 'message' => 'Booking not found.'];

        if (!in_array($booking['status'], ['confirmed', 'approved'])) {
            return ['success' => false, 'message' => 'Only confirmed bookings are eligible for guest check-in.'];
        }

        $assignedRoom = trim($data['assigned_room_number'] ?? $booking['assigned_room_number'] ?? 'Standard Suite');
        $idType       = trim($data['guest_id_type'] ?? 'Aadhaar Card');
        $idNumber     = trim($data['guest_id_number'] ?? '');
        $idDoc        = !empty($data['guest_id_document']) ? trim($data['guest_id_document']) : null;
        $actualTime   = !empty($data['actual_check_in_at']) ? trim($data['actual_check_in_at']) : date('Y-m-d H:i:s');
        $notes        = trim($data['notes'] ?? '');

        $sql = "UPDATE booking_requests SET 
                    status = 'checked_in',
                    actual_check_in_at = ?,
                    checked_in_by = ?,
                    assigned_room_number = ?,
                    guest_id_type = ?,
                    guest_id_number = ?,
                    guest_id_document = COALESCE(?, guest_id_document),
                    admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n[Check-In Note: ', ?, ']')
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => 'Statement error: ' . $this->db->error];

        $stmt->bind_param("sisssssi", $actualTime, $adminId, $assignedRoom, $idType, $idNumber, $idDoc, $notes, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            // Sync guest verification to users profile if ID provided
            $userId = (int)($booking['user_id'] ?? 0);
            if ($userId > 0 && !empty($idNumber)) {
                $eIdType = $this->db->real_escape_string($idType);
                $eIdNum  = $this->db->real_escape_string($idNumber);
                $this->db->query("UPDATE users SET gov_id_type = COALESCE(gov_id_type, '{$eIdType}'), gov_id_number = COALESCE(gov_id_number, '{$eIdNum}'), kyc_status = CASE WHEN kyc_status = 'unverified' THEN 'pending' ELSE kyc_status END WHERE id = {$userId}");
            }

            // Check if payment was collected during check-in
            $collected = (float)($data['payment_collected'] ?? 0);
            if ($collected > 0) {
                $payMethod = $data['payment_method'] ?? 'cash';
                $utr = $data['utr_number'] ?? null;
                $this->recordPayment($id, $collected, $payMethod, 'balance', $utr, null, $adminId, $adminName, 'Collected at Front-Desk Check-In');
            }

            $this->logAudit(
                $id,
                'check_in',
                $booking['status'],
                'checked_in',
                'admin',
                $adminId,
                $adminName ?? 'Administrator',
                "Guest checked in. Room: {$assignedRoom}, ID: {$idType} ({$idNumber})"
            );

            return ['success' => true, 'message' => 'Guest successfully checked in.'];
        }

        return ['success' => false, 'message' => 'Could not complete check-in.'];
    }

    /**
     * Front-Desk Operational Check-Out & Settlement Workflow
     */
    public function checkOutGuest(int $id, array $data, int $adminId, ?string $adminName = null): array {
        $booking = $this->getById($id);
        if (!$booking) return ['success' => false, 'message' => 'Booking not found.'];

        if ($booking['status'] !== 'checked_in') {
            return ['success' => false, 'message' => 'Only currently checked-in guests can be checked out.'];
        }

        $actualTime = !empty($data['actual_check_out_at']) ? trim($data['actual_check_out_at']) : date('Y-m-d H:i:s');
        $notes      = trim($data['notes'] ?? '');

        // Record any settlement payment at departure
        $settledAmount = (float)($data['payment_collected'] ?? 0);
        if ($settledAmount > 0) {
            $payMethod = $data['payment_method'] ?? 'cash';
            $utr = $data['utr_number'] ?? null;
            $this->recordPayment($id, $settledAmount, $payMethod, 'balance', $utr, null, $adminId, $adminName, 'Final Settlement at Check-Out');
        }

        // Security deposit handling
        $depositStatus = $data['deposit_status'] ?? 'refunded'; // refunded or retained
        $depositNotes  = trim($data['deposit_notes'] ?? '');

        $sql = "UPDATE booking_requests SET 
                    status = 'checked_out',
                    actual_check_out_at = ?,
                    checked_out_by = ?,
                    admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n[Check-Out Note: ', ?, ', Deposit: ', ?, ']')
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => 'Statement error: ' . $this->db->error];

        $stmt->bind_param("sissi", $actualTime, $adminId, $notes, $depositStatus, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            // Update guest total_stays_count & last_visit_at
            $userId = (int)($booking['user_id'] ?? 0);
            if ($userId > 0) {
                $this->db->query("UPDATE users SET total_stays_count = total_stays_count + 1, last_visit_at = NOW() WHERE id = {$userId}");
            }

            $this->logAudit(
                $id,
                'check_out',
                'checked_in',
                'checked_out',
                'admin',
                $adminId,
                $adminName ?? 'Administrator',
                "Guest checked out. Security Deposit: {$depositStatus}. Notes: {$notes}"
            );

            return ['success' => true, 'message' => 'Guest checked out successfully and room released.'];
        }

        return ['success' => false, 'message' => 'Could not complete check-out.'];
    }

    /**
     * In-House Stay Extension with dynamic availability verification and price recalculation
     */
    public function extendStay(int $id, string $newCheckOut, int $adminId, ?string $adminName = null): array {
        $booking = $this->getById($id);
        if (!$booking) return ['success' => false, 'message' => 'Booking not found.'];

        if (!in_array($booking['status'], ['checked_in', 'confirmed', 'approved'])) {
            return ['success' => false, 'message' => 'Stay extension is only permitted on active or confirmed bookings.'];
        }

        $currentCheckOut = $booking['check_out'];
        if (strtotime($newCheckOut) <= strtotime($currentCheckOut)) {
            return ['success' => false, 'message' => 'New check-out date must be later than current check-out date (' . $currentCheckOut . ').'];
        }

        // 1. Check availability for the extended dates
        $avail = $this->checkAvailability(
            (int)$booking['farmhouse_id'],
            $booking['check_in'],
            $newCheckOut,
            $booking['booking_type'],
            $booking['room_type_id'],
            (int)$booking['rooms'],
            $id
        );

        if (!$avail['available']) {
            return ['success' => false, 'message' => 'Cannot extend stay: ' . $avail['message']];
        }

        // 2. Recalculate Quote for the new total duration
        $farmDetailModel = new FarmdetailModel($this->db);
        $quote = $farmDetailModel->calculateQuote([
            'farmhouse_id' => $booking['farmhouse_id'],
            'booking_type' => $booking['booking_type'],
            'room_type_id' => $booking['room_type_id'],
            'rooms'        => $booking['rooms'],
            'guests'       => $booking['guests'],
            'check_in'     => $booking['check_in'],
            'check_out'    => $newCheckOut
        ]);

        $newTotalPrice = (float)($quote['total_payable'] ?? $booking['price']);
        $amountPaid    = (float)$booking['amount_paid'];
        $newBalance    = max(0, $newTotalPrice - $amountPaid);

        $sql = "UPDATE booking_requests SET 
                    check_out = ?, 
                    end_date = ?, 
                    price = ?, 
                    balance_amount = ?,
                    weekend_nights = ?,
                    admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n[Stay Extended from ', ?, ' to ', ?, ' by admin]')
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => 'Database prepare error: ' . $this->db->error];

        $weekendNights = (int)($quote['weekend_nights'] ?? 0);
        $stmt->bind_param("ssddissi", $newCheckOut, $newCheckOut, $newTotalPrice, $newBalance, $weekendNights, $currentCheckOut, $newCheckOut, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $this->logAudit(
                $id,
                'stay_extended',
                $booking['status'],
                $booking['status'],
                'admin',
                $adminId,
                $adminName ?? 'Administrator',
                "Stay extended to {$newCheckOut}. New Total: ₹{$newTotalPrice}, Balance Due: ₹{$newBalance}"
            );

            return [
                'success'        => true,
                'message'        => "Stay successfully extended to {$newCheckOut}.",
                'new_price'      => $newTotalPrice,
                'balance_amount' => $newBalance
            ];
        }

        return ['success' => false, 'message' => 'Failed to extend stay.'];
    }

    /**
     * Add an incidental extra charge to an active booking
     */
    public function addExtraCharge(int $id, string $title, float $amount, ?string $notes = null, int $adminId = 0, ?string $adminName = null): array {
        if ($amount <= 0 || empty($title)) {
            return ['success' => false, 'message' => 'Title and a positive amount are required.'];
        }

        $booking = $this->getById($id);
        if (!$booking) return ['success' => false, 'message' => 'Booking not found.'];

        $ins = $this->db->prepare("INSERT INTO booking_extra_charges (booking_id, title, amount, added_by, notes, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$ins) return ['success' => false, 'message' => 'Failed to prepare extra charge statement.'];

        $ins->bind_param("isdis", $id, $title, $amount, $adminId, $notes);
        $ins->execute();
        $ins->close();

        // Increment price and balance
        $newPrice = (float)$booking['price'] + $amount;
        $newBalance = (float)$booking['balance_amount'] + $amount;

        $this->db->query("UPDATE booking_requests SET price = {$newPrice}, balance_amount = {$newBalance} WHERE id = {$id}");

        $this->logAudit(
            $id,
            'extra_charge_added',
            $booking['status'],
            $booking['status'],
            'admin',
            $adminId,
            $adminName ?? 'Administrator',
            "Added extra charge: '{$title}' (₹" . number_format($amount, 2) . ")"
        );

        return [
            'success'        => true,
            'message'        => "Extra charge of ₹" . number_format($amount, 2) . " added successfully.",
            'new_price'      => $newPrice,
            'balance_amount' => $newBalance
        ];
    }

    /**
     * Record a payment installment into ledger
     */
    public function recordPayment(
        int $bookingId, 
        float $amount, 
        string $method = 'cash', 
        string $type = 'balance', 
        ?string $utr = null, 
        ?string $proof = null, 
        int $adminId = 0, 
        ?string $adminName = null, 
        ?string $notes = null
    ): array {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Payment amount must be greater than zero.'];
        }

        $booking = $this->getById($bookingId);
        if (!$booking) return ['success' => false, 'message' => 'Booking not found.'];

        $stmt = $this->db->prepare(
            "INSERT INTO payments (
                booking_id, farmhouse_id, user_id, amount, payment_type, payment_method, 
                utr_number, payment_proof, payment_status, verified_by, verified_at, 
                recorded_by, admin_notes, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'paid', ?, NOW(), ?, ?, NOW())"
        );
        if (!$stmt) return ['success' => false, 'message' => 'Payment prepare error: ' . $this->db->error];

        $farmhouseId = (int)$booking['farmhouse_id'];
        $userId      = (int)$booking['user_id'];
        $stmt->bind_param("iiidssssiis", $bookingId, $farmhouseId, $userId, $amount, $type, $method, $utr, $proof, $adminId, $adminId, $notes);
        $stmt->execute();
        $stmt->close();

        // Update booking ledger
        $newAmountPaid = (float)$booking['amount_paid'] + $amount;
        $newBalance    = max(0, (float)$booking['price'] - $newAmountPaid);
        $payStatus     = $newBalance <= 0 ? 'paid' : 'pending_verification';

        $this->db->query("UPDATE booking_requests SET amount_paid = {$newAmountPaid}, balance_amount = {$newBalance}, payment_status = '{$payStatus}' WHERE id = {$bookingId}");

        $this->logAudit(
            $bookingId,
            'payment_recorded',
            $booking['status'],
            $booking['status'],
            'admin',
            $adminId,
            $adminName ?? 'Administrator',
            "Payment of ₹" . number_format($amount, 2) . " recorded via " . strtoupper($method) . " ({$type})"
        );

        return [
            'success'        => true,
            'message'        => "Payment of ₹" . number_format($amount, 2) . " recorded successfully.",
            'amount_paid'    => $newAmountPaid,
            'balance_amount' => $newBalance,
            'payment_status' => $payStatus
        ];
    }

    /**
     * Immutable Audit Trail Logging
     */
    public function logAudit(
        int $bookingId, 
        string $action, 
        ?string $fromStatus, 
        ?string $toStatus, 
        string $actorType = 'admin', 
        ?int $actorId = null, 
        ?string $actorName = null, 
        ?string $notes = null
    ): void {
        $stmt = $this->db->prepare(
            "INSERT INTO booking_audit_logs (booking_id, action, from_status, to_status, actor_type, actor_id, actor_name, notes, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        if ($stmt) {
            $stmt->bind_param("isssisss", $bookingId, $action, $fromStatus, $toStatus, $actorType, $actorId, $actorName, $notes);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * Get all audit trail logs for a booking
     */
    public function getAuditLogs(int $bookingId): array {
        $stmt = $this->db->prepare("SELECT * FROM booking_audit_logs WHERE booking_id = ? ORDER BY id DESC");
        if (!$stmt) return [];
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $res;
    }

    /**
     * Get all extra incidental charges for a booking
     */
    public function getExtraCharges(int $bookingId): array {
        $stmt = $this->db->prepare("SELECT * FROM booking_extra_charges WHERE booking_id = ? ORDER BY id ASC");
        if (!$stmt) return [];
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $res;
    }

    /**
     * Get all payment ledger receipts for a booking
     */
    public function getPayments(int $bookingId): array {
        $stmt = $this->db->prepare("SELECT p.*, a.name AS recorded_by_name FROM payments p LEFT JOIN admins a ON p.recorded_by = a.id WHERE p.booking_id = ? ORDER BY p.id ASC");
        if (!$stmt) return [];
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $res;
    }

    /**
     * Get Bookings for Visual Occupancy Calendar
     */
    public function getCalendarBookings(?int $farmhouseId, string $startDate, string $endDate): array {
        $where = ["br.status NOT IN ('cancelled', 'rejected')"];
        $params = [];
        $types = "";

        if ($farmhouseId && $farmhouseId > 0) {
            $where[] = "br.farmhouse_id = ?";
            $types .= "i";
            $params[] = $farmhouseId;
        }

        $where[] = "br.check_in <= ? AND br.check_out >= ?";
        $types .= "ss";
        $params[] = $endDate;
        $params[] = $startDate;

        $whereClause = "WHERE " . implode(" AND ", $where);

        $sql = "SELECT 
                    br.id,
                    br.booking_reference,
                    br.farmhouse_id,
                    f.title AS farmhouse_title,
                    br.booking_type,
                    COALESCE(rt.room_type_name, br.room_type_name, 'Whole Estate') AS room_name,
                    br.check_in,
                    br.check_out,
                    br.status,
                    br.price,
                    br.amount_paid,
                    br.balance_amount,
                    br.guests,
                    u.name AS customer_name,
                    u.phone AS customer_phone
                FROM booking_requests br
                LEFT JOIN farmhouses f ON br.farmhouse_id = f.id
                LEFT JOIN users u ON br.user_id = u.id
                LEFT JOIN farmhouse_room_types rt ON br.room_type_id = rt.id
                {$whereClause}
                ORDER BY br.check_in ASC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Also fetch blocked dates
        $blocked = [];
        $bWhere = ["start_date <= ? AND end_date >= ?"];
        $bTypes = "ss";
        $bParams = [$endDate, $startDate];
        if ($farmhouseId && $farmhouseId > 0) {
            $bWhere[] = "farmhouse_id = ?";
            $bTypes .= "i";
            $bParams[] = $farmhouseId;
        }
        $bSql = "SELECT id, farmhouse_id, start_date AS check_in, end_date AS check_out, reason FROM blocked_dates WHERE " . implode(" AND ", $bWhere);
        $bStmt = $this->db->prepare($bSql);
        if ($bStmt) {
            $bStmt->bind_param($bTypes, ...$bParams);
            $bStmt->execute();
            $blocked = $bStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $bStmt->close();
        }

        return [
            'bookings' => $bookings,
            'blocked'  => $blocked
        ];
    }

    /**
     * Financial & Occupancy Reports Generation
     */
    public function getReportsData(string $startDate, string $endDate, ?int $farmhouseId = null): array {
        $where = ["br.created_at >= '{$startDate} 00:00:00' AND br.created_at <= '{$endDate} 23:59:59'"];
        if ($farmhouseId && $farmhouseId > 0) {
            $where[] = "br.farmhouse_id = {$farmhouseId}";
        }
        $whereClause = "WHERE " . implode(" AND ", $where);

        // Revenue Breakdown
        $revSql = "SELECT 
                    COUNT(*) AS total_bookings,
                    SUM(CASE WHEN status NOT IN ('cancelled', 'rejected') THEN price ELSE 0 END) AS total_gross,
                    SUM(CASE WHEN status NOT IN ('cancelled', 'rejected') THEN amount_paid ELSE 0 END) AS total_collected,
                    SUM(CASE WHEN status NOT IN ('cancelled', 'rejected') THEN balance_amount ELSE 0 END) AS total_outstanding,
                    SUM(CASE WHEN status IN ('cancelled', 'rejected') THEN 1 ELSE 0 END) AS total_cancelled
                   FROM booking_requests br {$whereClause}";
        $revRes = $this->db->query($revSql);
        $summary = $revRes ? $revRes->fetch_assoc() : [];

        // Daily Trend
        $trendSql = "SELECT 
                        DATE(br.created_at) AS booking_date,
                        COUNT(*) AS bookings_count,
                        SUM(CASE WHEN status NOT IN ('cancelled', 'rejected') THEN price ELSE 0 END) AS daily_revenue,
                        SUM(CASE WHEN status NOT IN ('cancelled', 'rejected') THEN amount_paid ELSE 0 END) AS daily_collected
                     FROM booking_requests br
                     {$whereClause}
                     GROUP BY DATE(br.created_at)
                     ORDER BY booking_date ASC";
        $trendRes = $this->db->query($trendSql);
        $trends = $trendRes ? $trendRes->fetch_all(MYSQLI_ASSOC) : [];

        // Breakdown by Property
        $propSql = "SELECT 
                        f.id AS farmhouse_id,
                        f.title AS farmhouse_title,
                        COUNT(br.id) AS bookings_count,
                        SUM(CASE WHEN br.status NOT IN ('cancelled', 'rejected') THEN br.price ELSE 0 END) AS gross_revenue,
                        SUM(CASE WHEN br.status NOT IN ('cancelled', 'rejected') THEN br.amount_paid ELSE 0 END) AS collected_revenue
                    FROM farmhouses f
                    LEFT JOIN booking_requests br ON f.id = br.farmhouse_id AND br.created_at >= '{$startDate} 00:00:00' AND br.created_at <= '{$endDate} 23:59:59'
                    GROUP BY f.id, f.title
                    ORDER BY gross_revenue DESC";
        $propRes = $this->db->query($propSql);
        $propertyBreakdown = $propRes ? $propRes->fetch_all(MYSQLI_ASSOC) : [];

        return [
            'summary'            => $summary,
            'trends'             => $trends,
            'property_breakdown' => $propertyBreakdown
        ];
    }

    /**
     * All active farmhouses for dropdown selectors
     */
    public function getAllActiveFarmhouses(): array {
        $res = $this->db->query("SELECT id, title, location, rental_type, price FROM farmhouses WHERE status = 'active' ORDER BY title ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Active room types for a farmhouse
     */
    public function getFarmhouseRoomTypes(int $farmhouseId): array {
        $stmt = $this->db->prepare("SELECT id, room_type_name, total_rooms, price_per_room, weekend_price, capacity_per_room FROM farmhouse_room_types WHERE farmhouse_id = ? AND status = 'active' ORDER BY id ASC");
        if (!$stmt) return [];
        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $res;
    }
}
