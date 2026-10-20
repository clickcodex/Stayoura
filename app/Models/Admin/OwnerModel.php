<?php
namespace App\Models\Admin;

use App\Config\Database;
use mysqli;
use Exception;

/**
 * OwnerModel: Complete Data Access & Business Logic for Property Owner Management
 */
class OwnerModel {
    protected mysqli $db;

    public function __construct(?mysqli $db = null) {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Get aggregate KPI statistics for the Owner Dashboard
     */
    public function getStats(): array {
        $stats = [
            'total_owners'            => 0,
            'active_owners'           => 0,
            'inactive_owners'         => 0,
            'suspended_owners'        => 0,
            'verified_kyc'            => 0,
            'pending_kyc'             => 0,
            'total_properties_linked' => 0,
            'multi_property_owners'   => 0,
        ];

        // Owner counts
        $res = $this->db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
            SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended,
            SUM(CASE WHEN kyc_status = 'verified' THEN 1 ELSE 0 END) as verified_kyc,
            SUM(CASE WHEN kyc_status = 'pending' THEN 1 ELSE 0 END) as pending_kyc
        FROM `owners`");

        if ($res && $row = $res->fetch_assoc()) {
            $stats['total_owners']     = (int)($row['total'] ?? 0);
            $stats['active_owners']    = (int)($row['active'] ?? 0);
            $stats['inactive_owners']  = (int)($row['inactive'] ?? 0);
            $stats['suspended_owners'] = (int)($row['suspended'] ?? 0);
            $stats['verified_kyc']     = (int)($row['verified_kyc'] ?? 0);
            $stats['pending_kyc']      = (int)($row['pending_kyc'] ?? 0);
        }

        // Total properties linked to owners
        $resProp = $this->db->query("SELECT COUNT(*) as c FROM `farmhouses` WHERE owner_id IS NOT NULL AND owner_id > 0");
        if ($resProp && $pRow = $resProp->fetch_assoc()) {
            $stats['total_properties_linked'] = (int)($pRow['c'] ?? 0);
        }

        // Owners with multiple properties
        $resMulti = $this->db->query("SELECT COUNT(*) as c FROM (
            SELECT owner_id FROM `farmhouses` WHERE owner_id IS NOT NULL AND owner_id > 0 GROUP BY owner_id HAVING COUNT(*) > 1
        ) as t");
        if ($resMulti && $mRow = $resMulti->fetch_assoc()) {
            $stats['multi_property_owners'] = (int)($mRow['c'] ?? 0);
        }

        return $stats;
    }

    /**
     * Get paginated and filtered owners list for admin table and mobile cards
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = 10): array {
        $where = [];
        $params = [];
        $types = "";

        // Status Filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = "o.status = ?";
            $types .= "s";
            $params[] = trim($filters['status']);
        }

        // KYC Status Filter
        if (!empty($filters['kyc_status']) && $filters['kyc_status'] !== 'all') {
            $where[] = "o.kyc_status = ?";
            $types .= "s";
            $params[] = trim($filters['kyc_status']);
        }

        // Owner Type Filter (Individual / Organization)
        if (!empty($filters['owner_type']) && $filters['owner_type'] !== 'all') {
            $where[] = "o.owner_type = ?";
            $types .= "s";
            $params[] = trim($filters['owner_type']);
        }

        // City Filter
        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            $where[] = "LOWER(o.city) = ?";
            $types .= "s";
            $params[] = strtolower(trim($filters['city']));
        }

        // Search Query
        if (!empty($filters['search'])) {
            $q = '%' . strtolower(trim($filters['search'])) . '%';
            $where[] = "(
                LOWER(o.name) LIKE ? OR 
                LOWER(COALESCE(o.business_name, '')) LIKE ? OR 
                LOWER(COALESCE(o.email, '')) LIKE ? OR 
                LOWER(COALESCE(o.phone, '')) LIKE ? OR 
                LOWER(COALESCE(o.city, '')) LIKE ?
            )";
            $types .= "sssss";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Count total matching records
        $countSql = "SELECT COUNT(*) as total FROM `owners` o {$whereClause}";
        $stmt = $this->db->prepare($countSql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $totalResult = $stmt->get_result();
        $total = (int)($totalResult->fetch_assoc()['total'] ?? 0);

        // Sorting
        $sort = $filters['sort'] ?? 'created_desc';
        $orderBy = match($sort) {
            'name_asc'        => "o.name ASC",
            'name_desc'       => "o.name DESC",
            'properties_desc' => "properties_count DESC, o.id DESC",
            'created_asc'     => "o.created_at ASC",
            default           => "o.created_at DESC",
        };

        $offset = max(0, ($page - 1) * $perPage);

        // Main Query: aggregates associated properties and real booking revenue
        $sql = "SELECT 
            o.*,
            COUNT(DISTINCT f.id) as properties_count,
            COALESCE(SUM(CASE WHEN br.status NOT IN ('cancelled', 'rejected') THEN br.price ELSE 0 END), 0) as gross_booking_value,
            COALESCE(SUM(br.amount_paid), 0) as total_collected
        FROM `owners` o
        LEFT JOIN `farmhouses` f ON f.owner_id = o.id
        LEFT JOIN `booking_requests` br ON br.farmhouse_id = f.id
        {$whereClause}
        GROUP BY o.id
        ORDER BY {$orderBy}
        LIMIT ? OFFSET ?";

        $types .= "ii";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }

        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        return [
            'data'        => $rows,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'totalPages'  => max(1, $totalPages),
        ];
    }

    /**
     * Get distinct cities for filter dropdown
     */
    public function getDistinctCities(): array {
        $cities = [];
        $res = $this->db->query("SELECT DISTINCT city FROM `owners` WHERE city IS NOT NULL AND city != '' ORDER BY city ASC");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $cities[] = $r['city'];
            }
        }
        return $cities;
    }

    /**
     * Get active owners list for property dropdown selection
     */
    public function getActiveOwnersList(): array {
        $owners = [];
        $res = $this->db->query("SELECT id, name, business_name, owner_type, city, phone, email 
            FROM `owners` 
            WHERE status = 'active' 
            ORDER BY name ASC");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $owners[] = $r;
            }
        }
        return $owners;
    }

    /**
     * Get single owner details by ID with aggregates
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT 
            o.*,
            COUNT(DISTINCT f.id) as properties_count
        FROM `owners` o
        LEFT JOIN `farmhouses` f ON f.owner_id = o.id
        WHERE o.id = ?
        GROUP BY o.id");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $owner = $res->fetch_assoc();
        return $owner ?: null;
    }

    /**
     * Create a new property owner
     */
    public function create(array $data, ?array $avatarFile = null): int {
        // Validation: required fields
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (empty($name)) {
            throw new Exception("Owner name is required.");
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("A valid email address is required.");
        }
        if (empty($phone)) {
            throw new Exception("Primary phone number is required.");
        }

        // Duplicate checks
        $this->ensureUniqueEmail($email);
        $this->ensureUniquePhone($phone);

        // Handle avatar upload
        $avatarFilename = '';
        if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
            $avatarFilename = $this->uploadAvatar($avatarFile);
        }

        // Password hash or secure default token
        $password = !empty($data['password']) 
            ? password_hash($data['password'], PASSWORD_DEFAULT) 
            : password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        $ownerType         = in_array($data['owner_type'] ?? '', ['individual', 'organization']) ? $data['owner_type'] : 'individual';
        $businessName      = !empty($data['business_name']) ? trim($data['business_name']) : null;
        $alternatePhone    = !empty($data['alternate_phone']) ? trim($data['alternate_phone']) : null;
        $address           = !empty($data['address']) ? trim($data['address']) : null;
        $city              = !empty($data['city']) ? trim($data['city']) : null;
        $state             = !empty($data['state']) ? trim($data['state']) : null;
        $pincode           = !empty($data['pincode']) ? trim($data['pincode']) : null;
        $country           = !empty($data['country']) ? trim($data['country']) : 'India';
        $govIdType         = !empty($data['gov_id_type']) ? trim($data['gov_id_type']) : null;
        $govIdNumber       = !empty($data['gov_id_number']) ? trim($data['gov_id_number']) : null;
        $bankName          = !empty($data['bank_name']) ? trim($data['bank_name']) : null;
        $bankAccountNumber = !empty($data['bank_account_number']) ? trim($data['bank_account_number']) : null;
        $bankIfsc          = !empty($data['bank_ifsc']) ? strtoupper(trim($data['bank_ifsc'])) : null;
        $bankAccountHolder = !empty($data['bank_account_holder']) ? trim($data['bank_account_holder']) : null;
        $upiId             = !empty($data['upi_id']) ? trim($data['upi_id']) : null;
        $adminNotes        = !empty($data['admin_notes']) ? trim($data['admin_notes']) : null;
        $status            = in_array($data['status'] ?? '', ['active', 'inactive', 'pending', 'suspended']) ? $data['status'] : 'active';
        $kycStatus         = in_array($data['kyc_status'] ?? '', ['pending', 'verified', 'rejected']) ? $data['kyc_status'] : 'pending';

        $stmt = $this->db->prepare("INSERT INTO `owners` (
            name, owner_type, business_name, email, phone, alternate_phone, 
            address, city, state, pincode, country, 
            gov_id_type, gov_id_number, 
            bank_name, bank_account_number, bank_ifsc, bank_account_holder, upi_id, 
            admin_notes, status, kyc_status, profile_image, password
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssssssssssssssssssssss",
            $name, $ownerType, $businessName, $email, $phone, $alternatePhone,
            $address, $city, $state, $pincode, $country,
            $govIdType, $govIdNumber,
            $bankName, $bankAccountNumber, $bankIfsc, $bankAccountHolder, $upiId,
            $adminNotes, $status, $kycStatus, $avatarFilename, $password
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to create owner: " . $stmt->error);
        }

        return (int)$this->db->insert_id;
    }

    /**
     * Update an existing property owner
     */
    public function update(int $id, array $data, ?array $avatarFile = null): bool {
        $current = $this->getById($id);
        if (!$current) {
            throw new Exception("Owner #{$id} not found.");
        }

        $name = trim($data['name'] ?? $current['name']);
        $email = trim($data['email'] ?? $current['email']);
        $phone = trim($data['phone'] ?? $current['phone']);

        if (empty($name)) {
            throw new Exception("Owner name cannot be empty.");
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("A valid email address is required.");
        }
        if (empty($phone)) {
            throw new Exception("Primary phone number is required.");
        }

        // Duplicate checks (excluding current owner)
        $this->ensureUniqueEmail($email, $id);
        $this->ensureUniquePhone($phone, $id);

        // Handle avatar upload if new file provided
        $avatarFilename = $current['profile_image'];
        if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
            $avatarFilename = $this->uploadAvatar($avatarFile);
        }

        $ownerType         = in_array($data['owner_type'] ?? '', ['individual', 'organization']) ? $data['owner_type'] : $current['owner_type'];
        $businessName      = isset($data['business_name']) ? trim($data['business_name']) : $current['business_name'];
        $alternatePhone    = isset($data['alternate_phone']) ? trim($data['alternate_phone']) : $current['alternate_phone'];
        $address           = isset($data['address']) ? trim($data['address']) : $current['address'];
        $city              = isset($data['city']) ? trim($data['city']) : $current['city'];
        $state             = isset($data['state']) ? trim($data['state']) : $current['state'];
        $pincode           = isset($data['pincode']) ? trim($data['pincode']) : $current['pincode'];
        $country           = !empty($data['country']) ? trim($data['country']) : ($current['country'] ?: 'India');
        $govIdType         = isset($data['gov_id_type']) ? trim($data['gov_id_type']) : $current['gov_id_type'];
        $govIdNumber       = isset($data['gov_id_number']) ? trim($data['gov_id_number']) : $current['gov_id_number'];
        $bankName          = isset($data['bank_name']) ? trim($data['bank_name']) : $current['bank_name'];
        $bankAccountNumber = isset($data['bank_account_number']) ? trim($data['bank_account_number']) : $current['bank_account_number'];
        $bankIfsc          = isset($data['bank_ifsc']) ? strtoupper(trim($data['bank_ifsc'])) : $current['bank_ifsc'];
        $bankAccountHolder = isset($data['bank_account_holder']) ? trim($data['bank_account_holder']) : $current['bank_account_holder'];
        $upiId             = isset($data['upi_id']) ? trim($data['upi_id']) : $current['upi_id'];
        $adminNotes        = isset($data['admin_notes']) ? trim($data['admin_notes']) : $current['admin_notes'];
        $status            = in_array($data['status'] ?? '', ['active', 'inactive', 'pending', 'suspended']) ? $data['status'] : $current['status'];
        $kycStatus         = in_array($data['kyc_status'] ?? '', ['pending', 'verified', 'rejected']) ? $data['kyc_status'] : $current['kyc_status'];

        $sql = "UPDATE `owners` SET 
            name = ?, owner_type = ?, business_name = ?, email = ?, phone = ?, alternate_phone = ?,
            address = ?, city = ?, state = ?, pincode = ?, country = ?,
            gov_id_type = ?, gov_id_number = ?,
            bank_name = ?, bank_account_number = ?, bank_ifsc = ?, bank_account_holder = ?, upi_id = ?,
            admin_notes = ?, status = ?, kyc_status = ?, profile_image = ?
            WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "ssssssssssssssssssssssi",
            $name, $ownerType, $businessName, $email, $phone, $alternatePhone,
            $address, $city, $state, $pincode, $country,
            $govIdType, $govIdNumber,
            $bankName, $bankAccountNumber, $bankIfsc, $bankAccountHolder, $upiId,
            $adminNotes, $status, $kycStatus, $avatarFilename, $id
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to update owner: " . $stmt->error);
        }

        return true;
    }

    /**
     * Update owner status (active, inactive, pending, suspended)
     */
    public function updateStatus(int $id, string $status): bool {
        if (!in_array($status, ['active', 'inactive', 'pending', 'suspended'], true)) {
            throw new Exception("Invalid status '{$status}'.");
        }

        $stmt = $this->db->prepare("UPDATE `owners` SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    /**
     * Update owner KYC verification status
     */
    public function updateKycStatus(int $id, string $kycStatus, int $adminId, ?string $notes = null): bool {
        if (!in_array($kycStatus, ['pending', 'verified', 'rejected'], true)) {
            throw new Exception("Invalid KYC status '{$kycStatus}'.");
        }

        $verifiedAt = ($kycStatus === 'verified') ? date('Y-m-d H:i:s') : null;
        $verifiedBy = ($kycStatus === 'verified') ? $adminId : null;

        $stmt = $this->db->prepare("UPDATE `owners` SET 
            kyc_status = ?,
            verified_at = ?,
            verified_by = ?
            WHERE id = ?");
        $stmt->bind_param("ssii", $kycStatus, $verifiedAt, $verifiedBy, $id);
        return $stmt->execute();
    }

    /**
     * Get all properties owned by this owner
     */
    public function getProperties(int $ownerId): array {
        $sql = "SELECT 
            f.*,
            pt.name as property_type_name,
            (SELECT image_url FROM images WHERE farmhouse_id = f.id ORDER BY is_featured DESC, sort_order ASC, id ASC LIMIT 1) as primary_image,
            (SELECT COUNT(*) FROM images WHERE farmhouse_id = f.id) as image_count,
            (SELECT COUNT(*) FROM booking_requests WHERE farmhouse_id = f.id) as total_bookings,
            (SELECT COUNT(*) FROM booking_requests WHERE farmhouse_id = f.id AND status IN ('confirmed', 'checked_in', 'approved')) as active_bookings
        FROM `farmhouses` f
        LEFT JOIN `property_types` pt ON f.property_type_id = pt.id
        WHERE f.owner_id = ?
        ORDER BY f.id DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("OwnerModel::getProperties prepare failed: " . $this->db->error);
            return [];
        }
        $stmt->bind_param("i", $ownerId);
        $stmt->execute();
        $res = $stmt->get_result();

        $properties = [];
        while ($row = $res->fetch_assoc()) {
            $properties[] = $row;
        }
        return $properties;
    }

    /**
     * Get stay bookings associated with this owner's properties
     */
    public function getBookings(int $ownerId, array $filters = [], int $limit = 20): array {
        $where = ["f.owner_id = ?"];
        $params = [$ownerId];
        $types = "i";

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = "br.status = ?";
            $types .= "s";
            $params[] = trim($filters['status']);
        }

        if (!empty($filters['farmhouse_id'])) {
            $where[] = "br.farmhouse_id = ?";
            $types .= "i";
            $params[] = (int)$filters['farmhouse_id'];
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT 
            br.*,
            f.title as property_title,
            f.location as property_location,
            u.name as guest_name,
            u.email as guest_email,
            u.phone as guest_phone
        FROM `booking_requests` br
        JOIN `farmhouses` f ON br.farmhouse_id = f.id
        LEFT JOIN `users` u ON br.user_id = u.id
        WHERE {$whereClause}
        ORDER BY br.created_at DESC
        LIMIT ?";

        $types .= "i";
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $bookings = [];
        while ($row = $res->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    }

    /**
     * Get financial summary derived strictly from real bookings and payout records
     */
    public function getFinancialSummary(int $ownerId): array {
        $summary = [
            'gross_booking_value' => 0.00,
            'total_collected'     => 0.00,
            'outstanding_balance' => 0.00,
            'total_bookings'      => 0,
            'completed_stays'     => 0,
            'total_settled'       => 0.00,
            'pending_settlements' => 0.00,
            'payouts_list'        => [],
        ];

        // 1. From booking_requests linked via farmhouses
        $stmt = $this->db->prepare("SELECT 
            COUNT(br.id) as total_bookings,
            SUM(CASE WHEN br.status NOT IN ('cancelled', 'rejected') THEN br.price ELSE 0 END) as gross_value,
            SUM(CASE WHEN br.status NOT IN ('cancelled', 'rejected') THEN br.amount_paid ELSE 0 END) as collected,
            SUM(CASE WHEN br.status NOT IN ('cancelled', 'rejected') THEN br.balance_amount ELSE 0 END) as balance,
            SUM(CASE WHEN br.status IN ('checked_out', 'completed') THEN 1 ELSE 0 END) as completed_stays
        FROM `booking_requests` br
        JOIN `farmhouses` f ON br.farmhouse_id = f.id
        WHERE f.owner_id = ?");
        $stmt->bind_param("i", $ownerId);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $summary['total_bookings']      = (int)($row['total_bookings'] ?? 0);
            $summary['gross_booking_value'] = (float)($row['gross_value'] ?? 0);
            $summary['total_collected']     = (float)($row['collected'] ?? 0);
            $summary['outstanding_balance'] = (float)($row['balance'] ?? 0);
            $summary['completed_stays']     = (int)($row['completed_stays'] ?? 0);
        }

        // 2. From owner_payouts table
        $pStmt = $this->db->prepare("SELECT * FROM `owner_payouts` WHERE owner_id = ? ORDER BY created_at DESC");
        $pStmt->bind_param("i", $ownerId);
        $pStmt->execute();
        $pRes = $pStmt->get_result();

        while ($pRow = $pRes->fetch_assoc()) {
            $summary['payouts_list'][] = $pRow;
            if ($pRow['payout_status'] === 'processed') {
                $summary['total_settled'] += (float)$pRow['net_payout'];
            } elseif ($pRow['payout_status'] === 'pending') {
                $summary['pending_settlements'] += (float)$pRow['net_payout'];
            }
        }

        return $summary;
    }

    /**
     * Get documents uploaded for this owner
     */
    public function getDocuments(int $ownerId): array {
        $stmt = $this->db->prepare("SELECT 
            od.*,
            f.title as property_title,
            a.name as verified_by_name
        FROM `owner_documents` od
        LEFT JOIN `farmhouses` f ON od.farmhouse_id = f.id
        LEFT JOIN `admins` a ON od.verified_by = a.id
        WHERE od.owner_id = ?
        ORDER BY od.created_at DESC");
        $stmt->bind_param("i", $ownerId);
        $stmt->execute();
        $res = $stmt->get_result();

        $docs = [];
        while ($row = $res->fetch_assoc()) {
            $docs[] = $row;
        }
        return $docs;
    }

    /**
     * Upload and attach a document to an owner
     */
    public function addDocument(int $ownerId, array $data, array $file, int $adminId): int {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed with error code: " . $file['error']);
        }

        // Validate size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            throw new Exception("Document exceeds maximum allowed size of 10 MB.");
        }

        // Validate MIME type & extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowedExts, true)) {
            throw new Exception("Invalid file extension '.{$ext}'. Allowed formats: PDF, JPG, PNG, WEBP.");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp'
        ];
        if (!in_array($mime, $allowedMimes, true)) {
            throw new Exception("Disallowed file MIME type: {$mime}.");
        }

        // Safe unique file destination
        $uploadDir = dirname(__DIR__, 3) . '/assets/images/uploads/owner_documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uniqueFilename = 'doc_' . $ownerId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $uniqueFilename;

        if (is_uploaded_file($file['tmp_name'])) {
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                throw new Exception("Failed to store uploaded document file.");
            }
        } else {
            if (!copy($file['tmp_name'], $destPath)) {
                throw new Exception("Failed to store uploaded document file.");
            }
        }

        $relativePath   = 'assets/images/uploads/owner_documents/' . $uniqueFilename;
        $documentType   = $data['document_type'] ?? 'other';
        $documentName   = !empty($data['document_name']) ? trim($data['document_name']) : pathinfo($file['name'], PATHINFO_FILENAME);
        $documentNumber = !empty($data['document_number']) ? trim($data['document_number']) : null;
        $farmhouseId    = !empty($data['farmhouse_id']) ? (int)$data['farmhouse_id'] : null;
        $fileSize       = (int)$file['size'];

        $stmt = $this->db->prepare("INSERT INTO `owner_documents` (
            owner_id, farmhouse_id, document_type, document_name, document_number, 
            file_path, file_size, mime_type, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

        $stmt->bind_param(
            "iissssis",
            $ownerId,
            $farmhouseId,
            $documentType,
            $documentName,
            $documentNumber,
            $relativePath,
            $fileSize,
            $mime
        );

        if (!$stmt->execute()) {
            // Cleanup file on error
            if (file_exists($destPath)) unlink($destPath);
            throw new Exception("Failed to record document: " . $stmt->error);
        }

        return (int)$this->db->insert_id;
    }

    /**
     * Verify or reject a document
     */
    public function verifyDocument(int $docId, string $status, ?string $reason, int $adminId): bool {
        if (!in_array($status, ['verified', 'rejected', 'pending'], true)) {
            throw new Exception("Invalid document verification status.");
        }

        $verifiedAt = ($status === 'verified') ? date('Y-m-d H:i:s') : null;

        $stmt = $this->db->prepare("UPDATE `owner_documents` SET 
            status = ?,
            rejection_reason = ?,
            verified_at = ?,
            verified_by = ?
            WHERE id = ?");
        $stmt->bind_param("sssii", $status, $reason, $verifiedAt, $adminId, $docId);
        return $stmt->execute();
    }

    /**
     * Delete a document
     */
    public function deleteDocument(int $docId): bool {
        $stmt = $this->db->prepare("SELECT file_path FROM `owner_documents` WHERE id = ?");
        $stmt->bind_param("i", $docId);
        $stmt->execute();
        $res = $stmt->get_result();
        $doc = $res->fetch_assoc();

        if ($doc && !empty($doc['file_path'])) {
            $fullPath = dirname(__DIR__, 3) . '/' . ltrim($doc['file_path'], '/');
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        $delStmt = $this->db->prepare("DELETE FROM `owner_documents` WHERE id = ?");
        $delStmt->bind_param("i", $docId);
        return $delStmt->execute();
    }

    /**
     * Get ownership transfer history involving this owner
     */
    public function getOwnershipHistory(int $ownerId): array {
        $stmt = $this->db->prepare("SELECT 
            poh.*,
            f.title as property_title,
            f.location as property_location,
            prev.name as prev_owner_name,
            newo.name as new_owner_name,
            a.name as transferred_by_admin
        FROM `property_ownership_history` poh
        JOIN `farmhouses` f ON poh.farmhouse_id = f.id
        LEFT JOIN `owners` prev ON poh.previous_owner_id = prev.id
        LEFT JOIN `owners` newo ON poh.new_owner_id = newo.id
        LEFT JOIN `admins` a ON poh.transferred_by = a.id
        WHERE poh.new_owner_id = ? OR poh.previous_owner_id = ?
        ORDER BY poh.created_at DESC");
        $stmt->bind_param("ii", $ownerId, $ownerId);
        $stmt->execute();
        $res = $stmt->get_result();

        $history = [];
        while ($row = $res->fetch_assoc()) {
            $history[] = $row;
        }
        return $history;
    }

    /**
     * Reassign a property to another owner atomically with audit record
     */
    public function reassignProperty(int $farmhouseId, int $newOwnerId, int $adminId, string $reason, ?string $notes = null): bool {
        // Validate target owner
        $newOwner = $this->getById($newOwnerId);
        if (!$newOwner) {
            throw new Exception("Target owner #{$newOwnerId} does not exist.");
        }
        if ($newOwner['status'] === 'suspended' || $newOwner['status'] === 'inactive') {
            throw new Exception("Cannot reassign property to an {$newOwner['status']} owner. Please activate the owner first.");
        }

        // Get current property details
        $fStmt = $this->db->prepare("SELECT id, title, owner_id FROM `farmhouses` WHERE id = ?");
        $fStmt->bind_param("i", $farmhouseId);
        $fStmt->execute();
        $property = $fStmt->get_result()->fetch_assoc();

        if (!$property) {
            throw new Exception("Property #{$farmhouseId} not found.");
        }

        $prevOwnerId = !empty($property['owner_id']) ? (int)$property['owner_id'] : null;

        if ($prevOwnerId === $newOwnerId) {
            throw new Exception("Property is already assigned to this owner.");
        }

        // Begin transaction
        $this->db->begin_transaction();

        try {
            // 1. Update farmhouses table
            $upStmt = $this->db->prepare("UPDATE `farmhouses` SET owner_id = ? WHERE id = ?");
            $upStmt->bind_param("ii", $newOwnerId, $farmhouseId);
            if (!$upStmt->execute()) {
                throw new Exception("Failed to update property owner: " . $upStmt->error);
            }

            // 2. Insert into property_ownership_history
            $histStmt = $this->db->prepare("INSERT INTO `property_ownership_history` 
                (farmhouse_id, previous_owner_id, new_owner_id, transferred_by, transfer_reason, notes) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $histStmt->bind_param("iiiiss", $farmhouseId, $prevOwnerId, $newOwnerId, $adminId, $reason, $notes);
            if (!$histStmt->execute()) {
                throw new Exception("Failed to record ownership history: " . $histStmt->error);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Check whether an owner can be deleted safely (Orphan Prevention)
     */
    public function canDeleteOwner(int $id): array {
        // 1. Properties check
        $pStmt = $this->db->prepare("SELECT COUNT(*) as c FROM `farmhouses` WHERE owner_id = ?");
        $pStmt->bind_param("i", $id);
        $pStmt->execute();
        $pCount = (int)($pStmt->get_result()->fetch_assoc()['c'] ?? 0);

        if ($pCount > 0) {
            return [
                'can_delete'        => false,
                'reason'            => "Owner currently has {$pCount} associated propert" . ($pCount === 1 ? 'y' : 'ies') . ". Reassign or archive these properties before deleting the owner.",
                'properties_count'  => $pCount,
                'bookings_count'    => 0,
            ];
        }

        // 2. Bookings check
        $bStmt = $this->db->prepare("SELECT COUNT(*) as c FROM `booking_requests` br 
            JOIN `farmhouses` f ON br.farmhouse_id = f.id 
            WHERE f.owner_id = ?");
        $bStmt->bind_param("i", $id);
        $bStmt->execute();
        $bCount = (int)($bStmt->get_result()->fetch_assoc()['c'] ?? 0);

        if ($bCount > 0) {
            return [
                'can_delete'        => false,
                'reason'            => "Owner is linked to {$bCount} booking record(s). To preserve historical and financial integrity, this owner cannot be hard-deleted. Set their status to 'Inactive' or 'Suspended' instead.",
                'properties_count'  => 0,
                'bookings_count'    => $bCount,
            ];
        }

        return [
            'can_delete'       => true,
            'reason'           => 'Owner has no linked properties or bookings and can be safely deleted.',
            'properties_count' => 0,
            'bookings_count'   => 0,
        ];
    }

    /**
     * Delete owner after safety check
     */
    public function delete(int $id): bool {
        $check = $this->canDeleteOwner($id);
        if (!$check['can_delete']) {
            throw new Exception($check['reason']);
        }

        // Delete documents
        $docs = $this->getDocuments($id);
        foreach ($docs as $doc) {
            $this->deleteDocument((int)$doc['id']);
        }

        // Delete owner
        $stmt = $this->db->prepare("DELETE FROM `owners` WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ── Internal Helpers ──

    private function ensureUniqueEmail(string $email, ?int $excludeId = null): void {
        $sql = "SELECT id FROM `owners` WHERE LOWER(email) = LOWER(?)";
        if ($excludeId !== null) {
            $sql .= " AND id != " . (int)$excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("An owner with email '{$email}' already exists.");
        }
    }

    private function ensureUniquePhone(string $phone, ?int $excludeId = null): void {
        $sql = "SELECT id FROM `owners` WHERE phone = ?";
        if ($excludeId !== null) {
            $sql .= " AND id != " . (int)$excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("An owner with phone number '{$phone}' already exists.");
        }
    }

    private function uploadAvatar(array $file): string {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            throw new Exception("Profile image must be a JPG, PNG, or WEBP file.");
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception("Profile image cannot exceed 5 MB.");
        }

        $dir = dirname(__DIR__, 3) . '/assets/images/uploads/avatars/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'owner_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (is_uploaded_file($file['tmp_name'])) {
            if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
                throw new Exception("Failed to upload profile image.");
            }
        } else {
            if (!copy($file['tmp_name'], $dir . $filename)) {
                throw new Exception("Failed to upload profile image.");
            }
        }

        return $filename;
    }
}
