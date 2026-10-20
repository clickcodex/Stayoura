<?php
namespace App\Models\Admin;

use mysqli;
use Exception;

/**
 * GuestModel: Core Data Access Layer for Guest & Member Management
 * Stayora Executive Console
 */
class GuestModel {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Compute Executive KPI Statistics for the Guest & Member Dashboard
     */
    public function getStats(): array {
        $stats = [
            'total_guests'       => 0,
            'active_guests'      => 0,
            'active_members'     => 0,
            'in_house_guests'    => 0,
            'pending_kyc'        => 0,
            'expiring_members'   => 0,
            'blocked_guests'     => 0,
            'total_spend_all'    => 0.00
        ];

        // 1. Total & Active & Blocked Guests
        $res = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked,
                SUM(CASE WHEN membership_tier != 'none' AND membership_status = 'active' THEN 1 ELSE 0 END) as active_members,
                SUM(CASE WHEN kyc_status = 'pending' THEN 1 ELSE 0 END) as pending_kyc,
                SUM(CASE WHEN membership_expiry_date IS NOT NULL AND membership_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_members,
                COALESCE(SUM(total_spend), 0.00) as total_spend
            FROM users
        ");
        if ($res && $row = $res->fetch_assoc()) {
            $stats['total_guests']     = (int)($row['total'] ?? 0);
            $stats['active_guests']    = (int)($row['active'] ?? 0);
            $stats['blocked_guests']   = (int)($row['blocked'] ?? 0);
            $stats['active_members']   = (int)($row['active_members'] ?? 0);
            $stats['pending_kyc']      = (int)($row['pending_kyc'] ?? 0);
            $stats['expiring_members'] = (int)($row['expiring_members'] ?? 0);
            $stats['total_spend_all']  = (float)($row['total_spend'] ?? 0);
        }

        // 2. Currently In-House Guests (from active check-ins)
        $resStay = $this->db->query("SELECT COUNT(DISTINCT user_id) as in_house FROM booking_requests WHERE status = 'checked_in'");
        if ($resStay && $r = $resStay->fetch_assoc()) {
            $stats['in_house_guests'] = (int)($r['in_house'] ?? 0);
        }

        return $stats;
    }

    /**
     * Query Guests & Members with dynamic search, multi-dimensional filters, and pagination
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = 15, string $sortBy = 'created_at', string $sortOrder = 'DESC'): array {
        $allowedSorts = ['id', 'name', 'created_at', 'total_spend', 'total_stays_count', 'membership_tier', 'kyc_status', 'status'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $whereClause = $this->buildWhereClause($filters);
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT 
                    u.*,
                    (SELECT COUNT(*) FROM booking_requests br WHERE br.user_id = u.id) as real_bookings_count,
                    (SELECT COUNT(*) FROM booking_requests br WHERE br.user_id = u.id AND br.status IN ('checked_in', 'checked_out', 'completed')) as real_stays_count,
                    (SELECT COALESCE(SUM(p.amount), 0.00) FROM payments p WHERE p.user_id = u.id AND p.payment_status = 'paid') as real_total_paid,
                    (SELECT COUNT(*) FROM guest_documents gd WHERE gd.user_id = u.id) as docs_count
                FROM users u
                {$whereClause['sql']}
                ORDER BY u.{$sortBy} {$sortOrder}
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $types = $whereClause['types'] . "ii";
        $params = array_merge($whereClause['params'], [$perPage, $offset]);

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Count total guests matching active filters
     */
    public function countAll(array $filters = []): int {
        $whereClause = $this->buildWhereClause($filters);
        $sql = "SELECT COUNT(*) as total FROM users u {$whereClause['sql']}";

        if (!empty($whereClause['params'])) {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return 0;
            $stmt->bind_param($whereClause['types'], ...$whereClause['params']);
            $stmt->execute();
            $res = $stmt->get_result();
            $count = (int)($res->fetch_assoc()['total'] ?? 0);
            $stmt->close();
            return $count;
        }

        $res = $this->db->query($sql);
        return $res ? (int)($res->fetch_assoc()['total'] ?? 0) : 0;
    }

    /**
     * Helper to construct sanitized WHERE clause
     */
    private function buildWhereClause(array $filters): array {
        $where = [];
        $params = [];
        $types = "";

        // Keyword Search (Name, Email, Phone, Guest Code, Member Number, City)
        if (!empty($filters['search'])) {
            $searchTerm = "%" . trim($filters['search']) . "%";
            $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.alternate_phone LIKE ? OR u.guest_code LIKE ? OR u.membership_number LIKE ? OR u.city LIKE ?)";
            $types .= "sssssss";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Account Status filter (active, blocked)
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = "u.status = ?";
            $types .= "s";
            $params[] = trim($filters['status']);
        }

        // Guest Category / Type filter (guest, registered_member, corporate_guest, vip)
        if (!empty($filters['guest_type']) && $filters['guest_type'] !== 'all') {
            $where[] = "u.guest_type = ?";
            $types .= "s";
            $params[] = trim($filters['guest_type']);
        }

        // Membership Tier filter (none, silver, gold, platinum, vip)
        if (!empty($filters['membership_tier']) && $filters['membership_tier'] !== 'all') {
            $where[] = "u.membership_tier = ?";
            $types .= "s";
            $params[] = trim($filters['membership_tier']);
        }

        // KYC Status filter (unverified, pending, verified, rejected)
        if (!empty($filters['kyc_status']) && $filters['kyc_status'] !== 'all') {
            $where[] = "u.kyc_status = ?";
            $types .= "s";
            $params[] = trim($filters['kyc_status']);
        }

        // City filter
        if (!empty($filters['city'])) {
            $where[] = "u.city = ?";
            $types .= "s";
            $params[] = trim($filters['city']);
        }

        // In-House Only filter
        if (!empty($filters['in_house']) && $filters['in_house'] === '1') {
            $where[] = "u.id IN (SELECT user_id FROM booking_requests WHERE status = 'checked_in')";
        }

        // Expiring Members filter
        if (!empty($filters['expiring_soon']) && $filters['expiring_soon'] === '1') {
            $where[] = "u.membership_expiry_date IS NOT NULL AND u.membership_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        }

        $sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        return [
            'sql'    => $sql,
            'types'  => $types,
            'params' => $params
        ];
    }

    /**
     * Retrieve a single guest profile by Primary ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $guest = $res->fetch_assoc();
        $stmt->close();
        return $guest ?: null;
    }

    /**
     * Retrieve a guest by Unique Guest Code
     */
    public function getByGuestCode(string $code): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE guest_code = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $res = $stmt->get_result();
        $guest = $res->fetch_assoc();
        $stmt->close();
        return $guest ?: null;
    }

    /**
     * Check if email is already taken by another user
     */
    public function isEmailTaken(string $email, int $excludeId = 0): bool {
        $email = trim($email);
        if (empty($email)) return false;

        $sql = "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("si", $email, $excludeId);
        $stmt->execute();
        $res = $stmt->get_result();
        $taken = ($res && $res->num_rows > 0);
        $stmt->close();
        return $taken;
    }

    /**
     * Check if phone is already taken by another user
     */
    public function isPhoneTaken(string $phone, int $excludeId = 0): bool {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($phone)) return false;

        // Check last 10 digits to normalize country codes
        $last10 = substr($phone, -10);
        $sql = "SELECT id FROM users WHERE (phone LIKE ? OR phone LIKE ?) AND id != ? LIMIT 1";
        $like1 = "%" . $last10;
        $like2 = $phone;

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("ssi", $like1, $like2, $excludeId);
        $stmt->execute();
        $res = $stmt->get_result();
        $taken = ($res && $res->num_rows > 0);
        $stmt->close();
        return $taken;
    }

    /**
     * Generate next sequential Guest Code (e.g. STY-G-00006)
     */
    public function generateGuestCode(): string {
        $res = $this->db->query("SELECT MAX(id) as max_id FROM users");
        $nextId = 1;
        if ($res && $row = $res->fetch_assoc()) {
            $nextId = (int)($row['max_id'] ?? 0) + 1;
        }
        return 'STY-G-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate next Membership Number (e.g. STY-GLD-006)
     */
    public function generateMembershipNumber(string $tier): string {
        $tierPrefix = [
            'silver'   => 'SLV',
            'gold'     => 'GLD',
            'platinum' => 'PLT',
            'vip'      => 'VIP'
        ];
        $prefix = $tierPrefix[strtolower($tier)] ?? 'MBR';
        $res = $this->db->query("SELECT COUNT(*) as c FROM users WHERE membership_tier != 'none'");
        $seq = 1;
        if ($res && $row = $res->fetch_assoc()) {
            $seq = (int)($row['c'] ?? 0) + 1;
        }
        return 'STY-' . $prefix . '-' . str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Register a New Guest / Member Profile
     */
    public function create(array $data, int $adminId = 0, ?string $adminName = null): array {
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if ($this->isEmailTaken($email)) {
            return ['success' => false, 'message' => "Email address '{$email}' is already registered to another guest."];
        }
        if ($this->isPhoneTaken($phone)) {
            return ['success' => false, 'message' => "Phone number '{$phone}' is already registered to another guest."];
        }

        $guestCode = !empty($data['guest_code']) ? trim($data['guest_code']) : $this->generateGuestCode();
        $dummyPassword = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        // Handle Avatar Upload if provided
        $avatarImage = 'default_profile.png';
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = $this->uploadAvatar($_FILES['profile_image']);
            if ($uploadRes['success']) {
                $avatarImage = $uploadRes['filename'];
            }
        }

        // Handle Membership initial fields
        $memTier = in_array($data['membership_tier'] ?? '', ['silver', 'gold', 'platinum', 'vip']) ? $data['membership_tier'] : 'none';
        $memStatus = ($memTier !== 'none') ? 'active' : 'none';
        $memNum = ($memTier !== 'none') ? (!empty($data['membership_number']) ? trim($data['membership_number']) : $this->generateMembershipNumber($memTier)) : null;
        $memStart = ($memTier !== 'none') ? (!empty($data['membership_start_date']) ? $data['membership_start_date'] : date('Y-m-d')) : null;
        $memExpiry = ($memTier !== 'none') ? (!empty($data['membership_expiry_date']) ? $data['membership_expiry_date'] : date('Y-m-d', strtotime('+1 year'))) : null;

        $sql = "INSERT INTO users (
                    guest_code, name, guest_type, email, phone, alternate_phone, gender, date_of_birth,
                    password, profile_image, address_line1, address_line2, city, state, pincode, country,
                    emergency_contact_name, emergency_contact_phone, emergency_contact_relation,
                    gov_id_type, gov_id_number, kyc_status,
                    membership_tier, membership_number, membership_status, membership_start_date, membership_expiry_date,
                    special_requests, admin_notes, status, is_phone_verified, is_email_verified, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, 1, 1, NOW()
                )";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => "Database prepare error: " . $this->db->error];
        }

        $name = trim($data['name'] ?? '');
        $guestType = in_array($data['guest_type'] ?? '', ['guest', 'registered_member', 'corporate_guest', 'vip']) ? $data['guest_type'] : 'guest';
        $altPhone = !empty($data['alternate_phone']) ? trim($data['alternate_phone']) : null;
        $gender = in_array($data['gender'] ?? '', ['male', 'female', 'other']) ? $data['gender'] : null;
        $dob = !empty($data['date_of_birth']) ? $data['date_of_birth'] : null;
        $addr1 = !empty($data['address_line1']) ? trim($data['address_line1']) : null;
        $addr2 = !empty($data['address_line2']) ? trim($data['address_line2']) : null;
        $city = !empty($data['city']) ? trim($data['city']) : null;
        $state = !empty($data['state']) ? trim($data['state']) : null;
        $pincode = !empty($data['pincode']) ? trim($data['pincode']) : null;
        $country = !empty($data['country']) ? trim($data['country']) : 'India';
        $emName = !empty($data['emergency_contact_name']) ? trim($data['emergency_contact_name']) : null;
        $emPhone = !empty($data['emergency_contact_phone']) ? trim($data['emergency_contact_phone']) : null;
        $emRel = !empty($data['emergency_contact_relation']) ? trim($data['emergency_contact_relation']) : null;
        $idType = in_array($data['gov_id_type'] ?? '', ['aadhaar', 'pan', 'passport', 'driving_license', 'voter_id', 'other']) ? $data['gov_id_type'] : null;
        $idNum = !empty($data['gov_id_number']) ? trim($data['gov_id_number']) : null;
        $kycStatus = in_array($data['kyc_status'] ?? '', ['unverified', 'pending', 'verified', 'rejected']) ? $data['kyc_status'] : 'unverified';
        $specialReq = !empty($data['special_requests']) ? trim($data['special_requests']) : null;
        $adminNotes = !empty($data['admin_notes']) ? trim($data['admin_notes']) : null;
        $status = in_array($data['status'] ?? '', ['active', 'blocked']) ? $data['status'] : 'active';

        $stmt->bind_param(
            "ssssssssssssssssssssssssssssss",
            $guestCode, $name, $guestType, $email, $phone, $altPhone, $gender, $dob,
            $dummyPassword, $avatarImage, $addr1, $addr2, $city, $state, $pincode, $country,
            $emName, $emPhone, $emRel,
            $idType, $idNum, $kycStatus,
            $memTier, $memNum, $memStatus, $memStart, $memExpiry,
            $specialReq, $adminNotes, $status
        );

        $ok = $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        if ($ok && $newId > 0) {
            // Log creation activity
            $this->logActivity($newId, 'guest_created', "Registered new guest profile '{$name}' ({$guestCode})", $adminName ?? 'Administrator');

            // Log membership enrollment if enrolled
            if ($memTier !== 'none') {
                $this->db->query("INSERT INTO membership_history (user_id, action, previous_tier, new_tier, start_date, expiry_date, notes, performed_by, created_at) 
                                  VALUES ({$newId}, 'enrolled', 'none', '{$memTier}', '{$memStart}', '{$memExpiry}', 'Initial enrollment on registration', {$adminId}, NOW())");
            }

            return ['success' => true, 'id' => $newId, 'guest_code' => $guestCode, 'message' => "Guest profile '{$name}' created successfully."];
        }

        return ['success' => false, 'message' => "Failed to insert guest record: " . $this->db->error];
    }

    /**
     * Update Existing Guest / Member Profile
     */
    public function update(int $id, array $data, int $adminId = 0, ?string $adminName = null): array {
        $guest = $this->getById($id);
        if (!$guest) return ['success' => false, 'message' => 'Guest record not found.'];

        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (!empty($email) && $this->isEmailTaken($email, $id)) {
            return ['success' => false, 'message' => "Email address '{$email}' is already used by another guest."];
        }
        if (!empty($phone) && $this->isPhoneTaken($phone, $id)) {
            return ['success' => false, 'message' => "Phone number '{$phone}' is already used by another guest."];
        }

        // Handle Avatar update if provided
        $avatarSql = "";
        $avatarParams = [];
        $avatarType = "";
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = $this->uploadAvatar($_FILES['profile_image']);
            if ($uploadRes['success']) {
                $avatarSql = ", profile_image = ?";
                $avatarParams[] = $uploadRes['filename'];
                $avatarType = "s";
            }
        }

        $sql = "UPDATE users SET 
                    name = ?,
                    guest_type = ?,
                    email = ?,
                    phone = ?,
                    alternate_phone = ?,
                    gender = ?,
                    date_of_birth = ?,
                    address_line1 = ?,
                    address_line2 = ?,
                    city = ?,
                    state = ?,
                    pincode = ?,
                    country = ?,
                    emergency_contact_name = ?,
                    emergency_contact_phone = ?,
                    emergency_contact_relation = ?,
                    gov_id_type = ?,
                    gov_id_number = ?,
                    special_requests = ?,
                    admin_notes = ?
                    {$avatarSql}
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => 'Prepare error: ' . $this->db->error];

        $name = trim($data['name'] ?? $guest['name']);
        $guestType = in_array($data['guest_type'] ?? '', ['guest', 'registered_member', 'corporate_guest', 'vip']) ? $data['guest_type'] : $guest['guest_type'];
        $altPhone = !empty($data['alternate_phone']) ? trim($data['alternate_phone']) : null;
        $gender = in_array($data['gender'] ?? '', ['male', 'female', 'other']) ? $data['gender'] : null;
        $dob = !empty($data['date_of_birth']) ? $data['date_of_birth'] : null;
        $addr1 = !empty($data['address_line1']) ? trim($data['address_line1']) : null;
        $addr2 = !empty($data['address_line2']) ? trim($data['address_line2']) : null;
        $city = !empty($data['city']) ? trim($data['city']) : null;
        $state = !empty($data['state']) ? trim($data['state']) : null;
        $pincode = !empty($data['pincode']) ? trim($data['pincode']) : null;
        $country = !empty($data['country']) ? trim($data['country']) : 'India';
        $emName = !empty($data['emergency_contact_name']) ? trim($data['emergency_contact_name']) : null;
        $emPhone = !empty($data['emergency_contact_phone']) ? trim($data['emergency_contact_phone']) : null;
        $emRel = !empty($data['emergency_contact_relation']) ? trim($data['emergency_contact_relation']) : null;
        $idType = in_array($data['gov_id_type'] ?? '', ['aadhaar', 'pan', 'passport', 'driving_license', 'voter_id', 'other']) ? $data['gov_id_type'] : $guest['gov_id_type'];
        $idNum = !empty($data['gov_id_number']) ? trim($data['gov_id_number']) : $guest['gov_id_number'];
        $specialReq = !empty($data['special_requests']) ? trim($data['special_requests']) : null;
        $adminNotes = !empty($data['admin_notes']) ? trim($data['admin_notes']) : null;

        $types = "ssssssssssssssssssss" . $avatarType . "i";
        $params = array_merge([
            $name, $guestType, $email, $phone, $altPhone, $gender, $dob,
            $addr1, $addr2, $city, $state, $pincode, $country,
            $emName, $emPhone, $emRel,
            $idType, $idNum, $specialReq, $adminNotes
        ], $avatarParams, [$id]);

        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $this->logActivity($id, 'profile_updated', "Profile coordinates updated by {$adminName}", $adminName ?? 'Administrator');
            return ['success' => true, 'message' => 'Guest profile updated successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to update profile: ' . $this->db->error];
    }

    /**
     * Update Account Status (active, blocked)
     */
    public function updateStatus(int $id, string $status, ?string $reason = null, int $adminId = 0, ?string $adminName = null): array {
        $guest = $this->getById($id);
        if (!$guest) return ['success' => false, 'message' => 'Guest record not found.'];

        $status = strtolower(trim($status));
        if (!in_array($status, ['active', 'blocked'], true)) {
            return ['success' => false, 'message' => 'Invalid account status.'];
        }

        $noteAppend = $reason ? "\n[Status Change to {$status}: {$reason}]" : "";
        $sql = "UPDATE users SET status = ?, admin_notes = CONCAT(COALESCE(admin_notes, ''), ?) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => $this->db->error];
        $stmt->bind_param("ssi", $status, $noteAppend, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $this->logActivity($id, 'status_changed', "Account status transitioned to '{$status}'. Note: {$reason}", $adminName ?? 'Administrator');
            return ['success' => true, 'message' => "Guest status updated to " . ucfirst($status) . "."];
        }

        return ['success' => false, 'message' => 'Failed to update status.'];
    }

    /**
     * Update Overall KYC Verification Status
     */
    public function updateKycStatus(int $id, string $kycStatus, ?string $notes = null, int $adminId = 0, ?string $adminName = null): array {
        $guest = $this->getById($id);
        if (!$guest) return ['success' => false, 'message' => 'Guest record not found.'];

        $kycStatus = strtolower(trim($kycStatus));
        if (!in_array($kycStatus, ['unverified', 'pending', 'verified', 'rejected'], true)) {
            return ['success' => false, 'message' => 'Invalid KYC status value.'];
        }

        $verifiedAt = in_array($kycStatus, ['verified', 'rejected']) ? date('Y-m-d H:i:s') : null;
        $verifiedBy = in_array($kycStatus, ['verified', 'rejected']) ? $adminId : null;

        $sql = "UPDATE users SET kyc_status = ?, kyc_notes = ?, kyc_verified_at = ?, kyc_verified_by = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => $this->db->error];
        $stmt->bind_param("sssii", $kycStatus, $notes, $verifiedAt, $verifiedBy, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $this->logActivity($id, 'kyc_status_updated', "KYC status updated to '" . ucfirst($kycStatus) . "'. Notes: {$notes}", $adminName ?? 'Administrator');
            return ['success' => true, 'message' => "KYC status updated to " . ucfirst($kycStatus) . "."];
        }

        return ['success' => false, 'message' => 'Failed to update KYC status.'];
    }

    /**
     * Update Membership Tier, Validity & Renewal
     */
    public function updateMembership(int $id, array $data, int $adminId = 0, ?string $adminName = null): array {
        $guest = $this->getById($id);
        if (!$guest) return ['success' => false, 'message' => 'Guest record not found.'];

        $newTier = in_array($data['membership_tier'] ?? '', ['none', 'silver', 'gold', 'platinum', 'vip']) ? $data['membership_tier'] : 'none';
        $prevTier = $guest['membership_tier'] ?? 'none';
        $memStatus = ($newTier !== 'none') ? (in_array($data['membership_status'] ?? '', ['active', 'expired', 'suspended', 'cancelled']) ? $data['membership_status'] : 'active') : 'none';
        $memNum = ($newTier !== 'none') ? (!empty($data['membership_number']) ? trim($data['membership_number']) : ($guest['membership_number'] ?: $this->generateMembershipNumber($newTier))) : null;
        $startDate = !empty($data['membership_start_date']) ? $data['membership_start_date'] : ($guest['membership_start_date'] ?: date('Y-m-d'));
        $expiryDate = !empty($data['membership_expiry_date']) ? $data['membership_expiry_date'] : date('Y-m-d', strtotime('+1 year'));
        $action = trim($data['action'] ?? ($prevTier === 'none' ? 'enrolled' : ($prevTier === $newTier ? 'renewed' : 'upgraded')));
        $notes = trim($data['notes'] ?? "Membership tier set to " . ucfirst($newTier));

        $sql = "UPDATE users SET 
                    membership_tier = ?,
                    membership_number = ?,
                    membership_status = ?,
                    membership_start_date = ?,
                    membership_expiry_date = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => $this->db->error];
        $stmt->bind_param("sssssi", $newTier, $memNum, $memStatus, $startDate, $expiryDate, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            // Record history
            $histStmt = $this->db->prepare("INSERT INTO membership_history (user_id, action, previous_tier, new_tier, start_date, expiry_date, notes, performed_by, created_at) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            if ($histStmt) {
                $histStmt->bind_param("issssssi", $id, $action, $prevTier, $newTier, $startDate, $expiryDate, $notes, $adminId);
                $histStmt->execute();
                $histStmt->close();
            }

            $this->logActivity($id, 'membership_updated', "Membership {$action} to " . ucfirst($newTier) . " (Valid till {$expiryDate})", $adminName ?? 'Administrator');
            return ['success' => true, 'message' => "Membership updated to " . ucfirst($newTier) . " successfully."];
        }

        return ['success' => false, 'message' => 'Failed to update membership: ' . $this->db->error];
    }

    /**
     * Retrieve Membership Event History
     */
    public function getMembershipHistory(int $userId): array {
        $stmt = $this->db->prepare("SELECT mh.*, a.name as admin_name 
                                    FROM membership_history mh 
                                    LEFT JOIN admins a ON mh.performed_by = a.id 
                                    WHERE mh.user_id = ? 
                                    ORDER BY mh.created_at DESC, mh.id DESC");
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Retrieve KYC & Identity Documents for a guest
     */
    public function getDocuments(int $userId): array {
        $stmt = $this->db->prepare("SELECT gd.*, a.name as verified_by_name 
                                    FROM guest_documents gd 
                                    LEFT JOIN admins a ON gd.verified_by = a.id 
                                    WHERE gd.user_id = ? 
                                    ORDER BY gd.created_at DESC");
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Upload & Register a KYC Document
     */
    public function addDocument(int $userId, array $data, array $file, int $adminId = 0): array {
        $guest = $this->getById($userId);
        if (!$guest) return ['success' => false, 'message' => 'Guest not found.'];

        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes, true)) {
            return ['success' => false, 'message' => 'Invalid file format. Allowed: PDF, JPG, PNG, WEBP.'];
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File size exceeds maximum limit of 10MB.'];
        }

        $uploadDir = __DIR__ . '/../../../assets/images/uploads/guest_documents';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'gdoc_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;

        $storedPath = 'assets/images/uploads/guest_documents/' . $filename;
        $moved = is_uploaded_file($file['tmp_name']) ? move_uploaded_file($file['tmp_name'], $targetPath) : copy($file['tmp_name'], $targetPath);

        if (!$moved) {
            return ['success' => false, 'message' => 'Failed to save uploaded document to server.'];
        }

        $docType = in_array($data['document_type'] ?? '', ['aadhaar', 'pan', 'passport', 'driving_license', 'voter_id', 'visa', 'corporate_id', 'other']) ? $data['document_type'] : 'other';
        $docName = !empty($data['document_name']) ? trim($data['document_name']) : ucfirst(str_replace('_', ' ', $docType));
        $docNum  = !empty($data['document_number']) ? trim($data['document_number']) : null;
        $fileSize = (int)$file['size'];

        $sql = "INSERT INTO guest_documents (user_id, document_type, document_name, document_number, file_path, file_size, mime_type, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => $this->db->error];
        $stmt->bind_param("issssis", $userId, $docType, $docName, $docNum, $storedPath, $fileSize, $mimeType);
        $ok = $stmt->execute();
        $docId = $stmt->insert_id;
        $stmt->close();

        if ($ok) {
            // Set guest KYC status to pending if unverified
            if ($guest['kyc_status'] === 'unverified') {
                $this->db->query("UPDATE users SET kyc_status = 'pending' WHERE id = {$userId}");
            }

            $this->logActivity($userId, 'document_uploaded', "Uploaded document: {$docName} ({$docType})", 'Admin/Staff');
            return ['success' => true, 'id' => $docId, 'message' => 'Document uploaded successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to record document in database.'];
    }

    /**
     * Verify or Reject a KYC Document
     */
    public function verifyDocument(int $docId, string $status, ?string $reason = null, int $adminId = 0, ?string $adminName = null): array {
        $status = strtolower(trim($status));
        if (!in_array($status, ['verified', 'rejected', 'pending'], true)) {
            return ['success' => false, 'message' => 'Invalid verification status.'];
        }

        $dRes = $this->db->query("SELECT * FROM guest_documents WHERE id = {$docId} LIMIT 1");
        if (!$dRes || $dRes->num_rows === 0) return ['success' => false, 'message' => 'Document not found.'];
        $doc = $dRes->fetch_assoc();

        $verifiedAt = ($status === 'verified' || $status === 'rejected') ? date('Y-m-d H:i:s') : null;
        $verifiedBy = ($status === 'verified' || $status === 'rejected') ? $adminId : null;

        $sql = "UPDATE guest_documents SET status = ?, rejection_reason = ?, verified_by = ?, verified_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => $this->db->error];
        $stmt->bind_param("ssisi", $status, $reason, $verifiedBy, $verifiedAt, $docId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $this->logActivity($doc['user_id'], 'document_reviewed', "Document '{$doc['document_name']}' status marked as " . ucfirst($status), $adminName ?? 'Administrator');

            // If verified, also update user kyc_status to verified
            if ($status === 'verified') {
                $this->db->query("UPDATE users SET kyc_status = 'verified', kyc_verified_at = NOW(), kyc_verified_by = {$adminId} WHERE id = {$doc['user_id']}");
            }

            return ['success' => true, 'message' => "Document has been marked as " . ucfirst($status) . "."];
        }

        return ['success' => false, 'message' => 'Failed to update document verification.'];
    }

    /**
     * Delete a KYC Document
     */
    public function deleteDocument(int $docId, int $adminId = 0): array {
        $dRes = $this->db->query("SELECT * FROM guest_documents WHERE id = {$docId} LIMIT 1");
        if (!$dRes || $dRes->num_rows === 0) return ['success' => false, 'message' => 'Document not found.'];
        $doc = $dRes->fetch_assoc();

        // Remove file from disk
        $fullPath = __DIR__ . '/../../../' . $doc['file_path'];
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        $del = $this->db->query("DELETE FROM guest_documents WHERE id = {$docId}");
        if ($del) {
            $this->logActivity($doc['user_id'], 'document_deleted', "Deleted document '{$doc['document_name']}'", 'Admin/Staff');
            return ['success' => true, 'message' => 'Document deleted successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to delete document from database.'];
    }

    /**
     * Fetch all bookings associated with this guest
     */
    public function getBookings(int $userId): array {
        $sql = "SELECT 
                    br.*,
                    f.title as property_title,
                    f.location as property_location,
                    f.rental_type,
                    (SELECT image_url FROM images WHERE farmhouse_id = f.id ORDER BY is_featured DESC, sort_order ASC, id ASC LIMIT 1) as property_image,
                    DATEDIFF(br.check_out, br.check_in) as total_nights
                FROM booking_requests br
                LEFT JOIN farmhouses f ON br.farmhouse_id = f.id
                WHERE br.user_id = ?
                ORDER BY br.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Fetch stay history (completed or currently checked-in stays)
     */
    public function getStays(int $userId): array {
        $sql = "SELECT 
                    br.*,
                    f.title as property_title,
                    f.location as property_location,
                    a_in.name as checked_in_by_name,
                    a_out.name as checked_out_by_name
                FROM booking_requests br
                LEFT JOIN farmhouses f ON br.farmhouse_id = f.id
                LEFT JOIN admins a_in ON br.checked_in_by = a_in.id
                LEFT JOIN admins a_out ON br.checked_out_by = a_out.id
                WHERE br.user_id = ? AND br.status IN ('checked_in', 'checked_out', 'completed')
                ORDER BY br.actual_check_in_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Fetch occupants registered on a stay
     */
    public function getStayOccupants(int $bookingId): array {
        $stmt = $this->db->prepare("SELECT * FROM booking_stay_guests WHERE booking_id = ? ORDER BY is_primary DESC, id ASC");
        if (!$stmt) return [];
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Record an additional occupant on a stay
     */
    public function addStayOccupant(int $bookingId, array $data): array {
        $name = trim($data['guest_name'] ?? '');
        if (empty($name)) return ['success' => false, 'message' => 'Occupant name is required.'];

        $sql = "INSERT INTO booking_stay_guests (
                    booking_id, user_id, guest_name, guest_phone, guest_email, guest_gender, age,
                    id_type, id_number, is_primary, notes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => $this->db->error];

        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : null;
        $phone = !empty($data['guest_phone']) ? trim($data['guest_phone']) : null;
        $email = !empty($data['guest_email']) ? trim($data['guest_email']) : null;
        $gender = in_array($data['guest_gender'] ?? '', ['male', 'female', 'other']) ? $data['guest_gender'] : null;
        $age = !empty($data['age']) ? (int)$data['age'] : null;
        $idType = !empty($data['id_type']) ? trim($data['id_type']) : null;
        $idNum = !empty($data['id_number']) ? trim($data['id_number']) : null;
        $isPrimary = !empty($data['is_primary']) ? 1 : 0;
        $notes = !empty($data['notes']) ? trim($data['notes']) : null;

        $stmt->bind_param("iissssissss", $bookingId, $userId, $name, $phone, $email, $gender, $age, $idType, $idNum, $isPrimary, $notes);
        $ok = $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        return $ok ? ['success' => true, 'id' => $newId] : ['success' => false, 'message' => $this->db->error];
    }

    /**
     * Fetch authoritative financial ledger records for this guest
     */
    public function getPayments(int $userId): array {
        $sql = "SELECT 
                    p.*,
                    br.booking_reference,
                    f.title as property_title,
                    a.name as verified_by_name
                FROM payments p
                LEFT JOIN booking_requests br ON p.booking_id = br.id
                LEFT JOIN farmhouses f ON p.farmhouse_id = f.id
                LEFT JOIN admins a ON p.verified_by = a.id
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Fetch reviews submitted by this guest
     */
    public function getReviews(int $userId): array {
        $sql = "SELECT r.*, f.title as property_title 
                FROM reviews r 
                LEFT JOIN farmhouses f ON r.farmhouse_id = f.id 
                WHERE r.user_id = ? 
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Fetch inquiries submitted by this guest
     */
    public function getInquiries(int $userId): array {
        $sql = "SELECT i.*, f.title as property_title 
                FROM inquiries i 
                LEFT JOIN farmhouses f ON i.farmhouse_id = f.id 
                WHERE i.user_id = ? 
                ORDER BY i.created_at DESC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Chronological Activity Logs
     */
    public function getActivityLogs(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM guest_activity_logs WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 50");
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Helper to log guest activity
     */
    public function logActivity(int $userId, string $type, string $description, string $performedBy = 'system', ?string $metadata = null): bool {
        $stmt = $this->db->prepare("INSERT INTO guest_activity_logs (user_id, activity_type, description, performed_by, metadata, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$stmt) return false;
        $stmt->bind_param("issss", $userId, $type, $description, $performedBy, $metadata);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Append a timestamped administrative note
     */
    public function addAdminNote(int $userId, string $note, int $adminId, string $adminName): array {
        $note = trim($note);
        if (empty($note)) return ['success' => false, 'message' => 'Note content cannot be empty.'];

        $timestamp = date('d M Y, h:i A');
        $formatted = "\n[{$timestamp} • {$adminName}]: {$note}";

        $stmt = $this->db->prepare("UPDATE users SET admin_notes = CONCAT(COALESCE(admin_notes, ''), ?) WHERE id = ?");
        if (!$stmt) return ['success' => false, 'message' => $this->db->error];
        $stmt->bind_param("si", $formatted, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $this->logActivity($userId, 'note_added', "Staff note appended by {$adminName}", $adminName);
            return ['success' => true, 'message' => 'Staff note saved successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to save note.'];
    }

    /**
     * Strict Orphan Prevention: Check if guest can be safely deleted
     */
    public function canDeleteGuest(int $id): array {
        // 1. Check booking requests
        $bRes = $this->db->query("SELECT COUNT(*) as c FROM booking_requests WHERE user_id = {$id}");
        $bCount = $bRes ? (int)$bRes->fetch_assoc()['c'] : 0;
        if ($bCount > 0) {
            return [
                'can_delete' => false,
                'reason' => "This guest account has {$bCount} associated booking(s) or stay records. Deleting it would violate historical audit and financial integrity. Please deactivate or block the account instead."
            ];
        }

        // 2. Check payment transactions
        $pRes = $this->db->query("SELECT COUNT(*) as c FROM payments WHERE user_id = {$id}");
        $pCount = $pRes ? (int)$pRes->fetch_assoc()['c'] : 0;
        if ($pCount > 0) {
            return [
                'can_delete' => false,
                'reason' => "This guest account has {$pCount} historical payment record(s) in the financial ledger. Please block the account instead."
            ];
        }

        return ['can_delete' => true, 'reason' => ''];
    }

    /**
     * Delete a guest profile safely (only allowed if 0 historical bookings/payments)
     */
    public function delete(int $id): array {
        $check = $this->canDeleteGuest($id);
        if (!$check['can_delete']) {
            return ['success' => false, 'message' => $check['reason']];
        }

        // Clean up documents
        $docs = $this->getDocuments($id);
        foreach ($docs as $d) {
            $fullPath = __DIR__ . '/../../../' . $d['file_path'];
            if (file_exists($fullPath)) @unlink($fullPath);
        }
        $this->db->query("DELETE FROM guest_documents WHERE user_id = {$id}");
        $this->db->query("DELETE FROM membership_history WHERE user_id = {$id}");
        $this->db->query("DELETE FROM guest_activity_logs WHERE user_id = {$id}");
        $this->db->query("DELETE FROM booking_stay_guests WHERE user_id = {$id}");

        $del = $this->db->query("DELETE FROM users WHERE id = {$id}");
        if ($del) {
            return ['success' => true, 'message' => 'Guest account deleted successfully.'];
        }

        return ['success' => false, 'message' => 'Database error while deleting account: ' . $this->db->error];
    }

    /**
     * Fast AJAX Search for Booking Autocomplete
     */
    public function searchGuestsAjax(string $query): array {
        $term = "%" . trim($query) . "%";
        $sql = "SELECT id, guest_code, name, email, phone, city, membership_tier, kyc_status, status 
                FROM users 
                WHERE name LIKE ? OR phone LIKE ? OR email LIKE ? OR guest_code LIKE ?
                ORDER BY name ASC 
                LIMIT 10";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ssss", $term, $term, $term, $term);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Internal helper to upload profile avatar safely
     */
    private function uploadAvatar(array $file): array {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed, true)) {
            return ['success' => false, 'message' => 'Avatar must be a valid image (JPG, PNG, WEBP).'];
        }

        $uploadDir = __DIR__ . '/../../../assets/images/uploads/profiles';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = $uploadDir . '/' . $filename;

        $moved = is_uploaded_file($file['tmp_name']) ? move_uploaded_file($file['tmp_name'], $target) : copy($file['tmp_name'], $target);

        if ($moved) {
            return ['success' => true, 'filename' => $filename];
        }

        return ['success' => false, 'message' => 'Could not save avatar file.'];
    }
}
