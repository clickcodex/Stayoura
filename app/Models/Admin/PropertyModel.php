<?php
namespace App\Models\Admin;

use App\Config\Database;
use App\Helpers\HtmlSanitizerHelper;
use mysqli;

class PropertyModel {
    protected mysqli $db;

    public function __construct(?mysqli $db = null) {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Get distinct KPI counts for properties
     */
    public function getKPIStats(): array {
        $stats = [
            'total'    => 0,
            'active'   => 0,
            'pending'  => 0,
            'rejected' => 0,
            'verified' => 0
        ];

        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified
                FROM farmhouses";

        $res = $this->db->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            $stats['total']    = (int)($row['total'] ?? 0);
            $stats['active']   = (int)($row['active'] ?? 0);
            $stats['pending']  = (int)($row['pending'] ?? 0);
            $stats['rejected'] = (int)($row['rejected'] ?? 0);
            $stats['verified'] = (int)($row['verified'] ?? 0);
        }

        return $stats;
    }

    /**
     * Get filtered properties with pagination
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = 10): array {
        $where = [];
        $types = "";
        $params = [];

        // Keyword Search (Title, Location, Address)
        if (!empty($filters['search'])) {
            $searchTerm = "%" . trim($filters['search']) . "%";
            $where[] = "(f.title LIKE ? OR f.location LIKE ? OR f.address LIKE ?)";
            $types .= "sss";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Status filter
        if (!empty($filters['status']) && in_array($filters['status'], ['active', 'pending', 'rejected'])) {
            $where[] = "f.status = ?";
            $types .= "s";
            $params[] = $filters['status'];
        }

        // Property Type filter
        if (!empty($filters['property_type_id']) && is_numeric($filters['property_type_id'])) {
            $where[] = "f.property_type_id = ?";
            $types .= "i";
            $params[] = (int)$filters['property_type_id'];
        }

        // Location filter
        if (!empty($filters['location'])) {
            $where[] = "f.location = ?";
            $types .= "s";
            $params[] = $filters['location'];
        }

        // Verification filter
        if (isset($filters['is_verified']) && $filters['is_verified'] !== '') {
            $where[] = "f.is_verified = ?";
            $types .= "i";
            $params[] = (int)$filters['is_verified'];
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Sorting
        $orderBy = match ($filters['sort'] ?? 'newest') {
            'oldest'     => "f.id ASC",
            'price_asc'  => "f.price ASC",
            'price_desc' => "f.price DESC",
            'title_asc'  => "f.title ASC",
            default      => "f.id DESC"
        };

        // Offset & Limit
        $offset = max(0, ($page - 1) * $perPage);
        $types .= "ii";
        $params[] = $perPage;
        $params[] = $offset;

        $sql = "SELECT f.*, 
                       pt.name AS property_type_name,
                       pt.slug AS property_type_slug,
                       o.name AS owner_name,
                       o.business_name AS owner_business_name,
                       o.owner_type AS owner_type,
                       o.profile_image AS owner_profile_image,
                       (SELECT image_url FROM images WHERE farmhouse_id = f.id ORDER BY is_featured DESC, sort_order ASC, id ASC LIMIT 1) AS featured_image,
                       (SELECT COUNT(*) FROM images WHERE farmhouse_id = f.id) AS image_count,
                       (SELECT COUNT(*) FROM bookings WHERE farmhouse_id = f.id) AS total_bookings_count,
                       (SELECT COUNT(*) FROM farmhouse_room_types WHERE farmhouse_id = f.id AND status = 'active') AS active_room_types_count
                FROM farmhouses f
                LEFT JOIN property_types pt ON f.property_type_id = pt.id
                LEFT JOIN owners o ON f.owner_id = o.id
                {$whereClause}
                ORDER BY {$orderBy}
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $properties = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $properties;
    }

    /**
     * Count total properties matching filters
     */
    public function countAll(array $filters = []): int {
        $where = [];
        $types = "";
        $params = [];

        if (!empty($filters['search'])) {
            $searchTerm = "%" . trim($filters['search']) . "%";
            $where[] = "(f.title LIKE ? OR f.location LIKE ? OR f.address LIKE ?)";
            $types .= "sss";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['active', 'pending', 'rejected'])) {
            $where[] = "f.status = ?";
            $types .= "s";
            $params[] = $filters['status'];
        }

        if (!empty($filters['property_type_id']) && is_numeric($filters['property_type_id'])) {
            $where[] = "f.property_type_id = ?";
            $types .= "i";
            $params[] = (int)$filters['property_type_id'];
        }

        if (!empty($filters['location'])) {
            $where[] = "f.location = ?";
            $types .= "s";
            $params[] = $filters['location'];
        }

        if (isset($filters['is_verified']) && $filters['is_verified'] !== '') {
            $where[] = "f.is_verified = ?";
            $types .= "i";
            $params[] = (int)$filters['is_verified'];
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $sql = "SELECT COUNT(*) as total FROM farmhouses f {$whereClause}";

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
     * Find single property by ID with gallery images, room types, amenities, and rules
     */
    public function findById(int $id): ?array {
        $sql = "SELECT f.*, 
                       pt.name AS property_type_name,
                       pt.slug AS property_type_slug,
                       o.name AS owner_name,
                       o.email AS owner_email,
                       o.phone AS owner_phone
                FROM farmhouses f
                LEFT JOIN property_types pt ON f.property_type_id = pt.id
                LEFT JOIN owners o ON f.owner_id = o.id
                WHERE f.id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $property = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();

        if ($property) {
            // Normalize rental_type
            if (empty($property['rental_type'])) {
                $property['rental_type'] = !empty($property['allow_room_booking']) ? 'both' : 'complete';
            }

            // Load associated gallery images
            $imgStmt = $this->db->prepare("SELECT id, image_url, category, caption, is_featured, sort_order FROM images WHERE farmhouse_id = ? ORDER BY is_featured DESC, sort_order ASC, id ASC");
            if ($imgStmt) {
                $imgStmt->bind_param("i", $id);
                $imgStmt->execute();
                $property['images'] = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $imgStmt->close();
            } else {
                $property['images'] = [];
            }

            // Load room types
            $property['room_types'] = $this->getFarmhouseRoomTypes($id);

            // Load selected amenities
            $property['amenities'] = $this->getFarmhouseAmenities($id);
            $property['amenity_ids'] = array_map('intval', array_column($property['amenities'], 'id'));

            // Load property rules
            $property['rules'] = $this->getFarmhouseRules($id);
        }

        return $property;
    }

    /**
     * Get all active property types
     */
    public function getPropertyTypes(): array {
        $res = $this->db->query("SELECT id, name, slug, description FROM property_types WHERE status = 'active' ORDER BY display_order ASC, name ASC");
        if (!$res) return [];
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get distinct locations for filter dropdown
     */
    public function getDistinctLocations(): array {
        $res = $this->db->query("SELECT DISTINCT location FROM farmhouses WHERE location IS NOT NULL AND location != '' ORDER BY location ASC");
        if (!$res) return [];
        $locs = [];
        while ($row = $res->fetch_assoc()) {
            if (!empty($row['location'])) {
                $locs[] = $row['location'];
            }
        }
        return $locs;
    }

    /**
     * Create a new property record with rich text description, booking mode, amenities, rules & rooms
     */
    public function create(array $data, array $imageUrls = []): int {
        $sql = "INSERT INTO farmhouses (
                    title, description, location, address, google_map_link,
                    category, property_type_id, price, weekend_price,
                    security_deposit, cleaning_fee, weekly_discount_percent, monthly_discount_percent,
                    bedrooms, bedroom_capacity, day_capacity, night_capacity,
                    is_negotiable, status, booking_mode, admin_approval_status,
                    is_verified, verification_notes, contact_phone, whatsapp_number,
                    rental_type, allow_room_booking, owner_id,
                    created_by
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    'admin'
                )";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare create farmhouse SQL: " . $this->db->error);
            return 0;
        }

        $title                  = (string)($data['title'] ?? '');
        // Sanitize rich text description with HtmlSanitizerHelper
        $description            = HtmlSanitizerHelper::clean($data['description'] ?? '');
        $location               = (string)($data['location'] ?? '');
        $address                = (string)($data['address'] ?? '');
        $googleMapLink          = (string)($data['google_map_link'] ?? '');
        $category               = (string)($data['category'] ?? 'Farmhouse');
        $propertyTypeId         = !empty($data['property_type_id']) ? (int)$data['property_type_id'] : null;
        $price                  = (float)($data['price'] ?? 0);
        $weekendPrice           = !empty($data['weekend_price']) ? (float)$data['weekend_price'] : $price;
        $securityDeposit        = (float)($data['security_deposit'] ?? 0);
        $cleaningFee            = (float)($data['cleaning_fee'] ?? 0);
        $weeklyDiscount         = (float)($data['weekly_discount_percent'] ?? 10);
        $monthlyDiscount        = (float)($data['monthly_discount_percent'] ?? 20);
        $bedrooms               = max(1, (int)($data['bedrooms'] ?? 1));
        $bedroomCapacity        = max(1, (int)($data['bedroom_capacity'] ?? 2));
        $dayCapacity            = max(1, (int)($data['day_capacity'] ?? 10));
        $nightCapacity          = max(1, (int)($data['night_capacity'] ?? 6));
        $isNegotiable           = !empty($data['is_negotiable']) ? 1 : 0;
        $status                 = in_array($data['status'] ?? '', ['active', 'pending', 'rejected']) ? $data['status'] : 'active';
        $bookingMode            = in_array($data['booking_mode'] ?? '', ['request', 'instant']) ? $data['booking_mode'] : 'request';
        $adminApprovalStatus    = 'approved';
        $isVerified             = !empty($data['is_verified']) ? 1 : 0;
        $verificationNotes      = (string)($data['verification_notes'] ?? 'Physically Inspected & Host Verified');
        $contactPhone           = (string)($data['contact_phone'] ?? '');
        $whatsappNumber         = (string)($data['whatsapp_number'] ?? '');
        $ownerId                = !empty($data['owner_id']) ? (int)$data['owner_id'] : null;

        // Configurable Booking Mode Architecture
        $rentalType             = in_array($data['rental_type'] ?? '', ['complete', 'room_wise', 'both']) ? $data['rental_type'] : 'complete';
        $allowRoomBooking       = ($rentalType === 'complete') ? 0 : 1;

        $stmt->bind_param(
            "ssssssiddddddiiiiisssissssii",
            $title, $description, $location, $address, $googleMapLink,
            $category, $propertyTypeId, $price, $weekendPrice,
            $securityDeposit, $cleaningFee, $weeklyDiscount, $monthlyDiscount,
            $bedrooms, $bedroomCapacity, $dayCapacity, $nightCapacity,
            $isNegotiable, $status, $bookingMode, $adminApprovalStatus,
            $isVerified, $verificationNotes, $contactPhone, $whatsappNumber,
            $rentalType, $allowRoomBooking, $ownerId
        );

        if (!$stmt->execute()) {
            error_log("Failed to execute create farmhouse: " . $stmt->error);
            $stmt->close();
            return 0;
        }

        $farmhouseId = $stmt->insert_id;
        $stmt->close();

        if ($farmhouseId > 0) {
            // Initial ownership history log
            if (!empty($ownerId)) {
                $histStmt = $this->db->prepare("INSERT INTO `property_ownership_history` 
                    (farmhouse_id, previous_owner_id, new_owner_id, transferred_by, transfer_reason, notes) 
                    VALUES (?, NULL, ?, 1, 'Initial property registration', 'Assigned at property creation')");
                if ($histStmt) {
                    $histStmt->bind_param("ii", $farmhouseId, $ownerId);
                    $histStmt->execute();
                    $histStmt->close();
                }
            }

            // 1. Insert gallery images
            if (!empty($imageUrls)) {
                $this->addImages($farmhouseId, $imageUrls);
            }

            // 2. Synchronize amenities
            $this->syncAmenities($farmhouseId, $data['amenities'] ?? []);

            // 3. Synchronize rules
            $this->syncRules($farmhouseId, $data['rules'] ?? []);

            // 4. Synchronize room types if room booking is enabled
            if ($rentalType !== 'complete' && !empty($data['room_types'])) {
                $this->syncRoomTypes($farmhouseId, $data['room_types']);
            }
        }

        return $farmhouseId;
    }

    /**
     * Update an existing property
     */
    public function update(int $id, array $data, array $newImageUrls = []): bool {
        // Fetch current owner to detect reassignment
        $prevOwnerId = null;
        $prevStmt = $this->db->prepare("SELECT owner_id FROM farmhouses WHERE id = ?");
        if ($prevStmt) {
            $prevStmt->bind_param("i", $id);
            $prevStmt->execute();
            $prevRow = $prevStmt->get_result()->fetch_assoc();
            $prevOwnerId = !empty($prevRow['owner_id']) ? (int)$prevRow['owner_id'] : null;
            $prevStmt->close();
        }

        $sql = "UPDATE farmhouses SET
                    title = ?, description = ?, location = ?, address = ?, google_map_link = ?,
                    category = ?, property_type_id = ?, price = ?, weekend_price = ?,
                    security_deposit = ?, cleaning_fee = ?, weekly_discount_percent = ?, monthly_discount_percent = ?,
                    bedrooms = ?, bedroom_capacity = ?, day_capacity = ?, night_capacity = ?,
                    is_negotiable = ?, status = ?, booking_mode = ?,
                    is_verified = ?, verification_notes = ?, contact_phone = ?, whatsapp_number = ?,
                    rental_type = ?, allow_room_booking = ?, owner_id = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare update farmhouse SQL: " . $this->db->error);
            return false;
        }

        $title                  = (string)($data['title'] ?? '');
        // Sanitize rich text description with HtmlSanitizerHelper
        $description            = HtmlSanitizerHelper::clean($data['description'] ?? '');
        $location               = (string)($data['location'] ?? '');
        $address                = (string)($data['address'] ?? '');
        $googleMapLink          = (string)($data['google_map_link'] ?? '');
        $category               = (string)($data['category'] ?? 'Farmhouse');
        $propertyTypeId         = !empty($data['property_type_id']) ? (int)$data['property_type_id'] : null;
        $price                  = (float)($data['price'] ?? 0);
        $weekendPrice           = !empty($data['weekend_price']) ? (float)$data['weekend_price'] : $price;
        $securityDeposit        = (float)($data['security_deposit'] ?? 0);
        $cleaningFee            = (float)($data['cleaning_fee'] ?? 0);
        $weeklyDiscount         = (float)($data['weekly_discount_percent'] ?? 10);
        $monthlyDiscount        = (float)($data['monthly_discount_percent'] ?? 20);
        $bedrooms               = max(1, (int)($data['bedrooms'] ?? 1));
        $bedroomCapacity        = max(1, (int)($data['bedroom_capacity'] ?? 2));
        $dayCapacity            = max(1, (int)($data['day_capacity'] ?? 10));
        $nightCapacity          = max(1, (int)($data['night_capacity'] ?? 6));
        $isNegotiable           = !empty($data['is_negotiable']) ? 1 : 0;
        $status                 = in_array($data['status'] ?? '', ['active', 'pending', 'rejected']) ? $data['status'] : 'active';
        $bookingMode            = in_array($data['booking_mode'] ?? '', ['request', 'instant']) ? $data['booking_mode'] : 'request';
        $isVerified             = !empty($data['is_verified']) ? 1 : 0;
        $verificationNotes      = (string)($data['verification_notes'] ?? 'Physically Inspected & Host Verified');
        $contactPhone           = (string)($data['contact_phone'] ?? '');
        $whatsappNumber         = (string)($data['whatsapp_number'] ?? '');
        $ownerId                = !empty($data['owner_id']) ? (int)$data['owner_id'] : null;

        // Configurable Booking Mode Architecture
        $rentalType             = in_array($data['rental_type'] ?? '', ['complete', 'room_wise', 'both']) ? $data['rental_type'] : 'complete';
        $allowRoomBooking       = ($rentalType === 'complete') ? 0 : 1;

        $stmt->bind_param(
            "ssssssiddddddiiiiississssiii",
            $title, $description, $location, $address, $googleMapLink,
            $category, $propertyTypeId, $price, $weekendPrice,
            $securityDeposit, $cleaningFee, $weeklyDiscount, $monthlyDiscount,
            $bedrooms, $bedroomCapacity, $dayCapacity, $nightCapacity,
            $isNegotiable, $status, $bookingMode,
            $isVerified, $verificationNotes, $contactPhone, $whatsappNumber,
            $rentalType, $allowRoomBooking, $ownerId,
            $id
        );

        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            // Log ownership change if owner was modified
            if ($ownerId !== $prevOwnerId && !empty($ownerId)) {
                $histStmt = $this->db->prepare("INSERT INTO `property_ownership_history` 
                    (farmhouse_id, previous_owner_id, new_owner_id, transferred_by, transfer_reason, notes) 
                    VALUES (?, ?, ?, 1, 'Updated during property edit', 'Owner reassigned via property update form')");
                if ($histStmt) {
                    $histStmt->bind_param("iii", $id, $prevOwnerId, $ownerId);
                    $histStmt->execute();
                    $histStmt->close();
                }
            }

            // Add any newly provided images
            if (!empty($newImageUrls)) {
                $this->addImages($id, $newImageUrls);
            }

            // Synchronize amenities
            $this->syncAmenities($id, $data['amenities'] ?? []);

            // Synchronize rules
            $this->syncRules($id, $data['rules'] ?? []);

            // Synchronize room types
            if (isset($data['room_types'])) {
                $this->syncRoomTypes($id, $data['room_types']);
            }
        }

        return $ok;
    }

    /**
     * Add multiple images to a farmhouse
     */
    public function addImages(int $farmhouseId, array $imageUrls): void {
        $featCheck = $this->db->query("SELECT id FROM images WHERE farmhouse_id = {$farmhouseId} AND is_featured = 1 LIMIT 1");
        $hasFeatured = ($featCheck && $featCheck->num_rows > 0);

        $stmt = $this->db->prepare("INSERT INTO images (farmhouse_id, image_url, category, caption, is_featured, sort_order) VALUES (?, ?, 'general', '', ?, ?)");
        if (!$stmt) return;

        $sort = 10;
        foreach ($imageUrls as $url) {
            $url = trim($url);
            if (empty($url)) continue;
            
            $isFeatured = (!$hasFeatured) ? 1 : 0;
            $hasFeatured = true;

            $stmt->bind_param("isii", $farmhouseId, $url, $isFeatured, $sort);
            $stmt->execute();
            $sort += 10;
        }
        $stmt->close();
    }

    /**
     * Delete an individual image record
     */
    public function deleteImage(int $imageId, int $farmhouseId): bool {
        $stmt = $this->db->prepare("DELETE FROM images WHERE id = ? AND farmhouse_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ii", $imageId, $farmhouseId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Set an image as the primary featured photo
     */
    public function setFeaturedImage(int $imageId, int $farmhouseId): bool {
        $this->db->query("UPDATE images SET is_featured = 0 WHERE farmhouse_id = {$farmhouseId}");
        $stmt = $this->db->prepare("UPDATE images SET is_featured = 1 WHERE id = ? AND farmhouse_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ii", $imageId, $farmhouseId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Quick status update
     */
    public function updateStatus(int $id, string $status): bool {
        if (!in_array($status, ['active', 'pending', 'rejected'])) return false;
        $stmt = $this->db->prepare("UPDATE farmhouses SET status = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Delete a property and clean all associated records
     */
    public function delete(int $id): bool {
        $this->db->query("DELETE FROM images WHERE farmhouse_id = {$id}");
        $this->db->query("DELETE FROM farmhouse_amenities WHERE farmhouse_id = {$id}");
        $this->db->query("DELETE FROM farmhouse_rules WHERE farmhouse_id = {$id}");
        $this->db->query("DELETE FROM farmhouse_room_types WHERE farmhouse_id = {$id}");
        $this->db->query("DELETE FROM blocked_dates WHERE farmhouse_id = {$id}");
        
        $stmt = $this->db->prepare("DELETE FROM farmhouses WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // ═════════════════════════════════════════════════════════════════════════════
    // ── AMENITIES MANAGEMENT MODULE ──
    // ═════════════════════════════════════════════════════════════════════════════

    /**
     * Get all master amenities grouped into logical categories
     */
    public function getAllAmenitiesGrouped(): array {
        $sql = "SELECT id, name, category, icon_class FROM amenities ORDER BY category ASC, name ASC";
        $res = $this->db->query($sql);
        if (!$res) return [];

        $grouped = [];
        while ($row = $res->fetch_assoc()) {
            $catKey = strtolower(trim($row['category'] ?? 'general'));
            
            // Map category key to display group title
            $groupTitle = match ($catKey) {
                'bedroom', 'bathroom', 'bed', 'bath' => 'Bedrooms & Bath',
                'kitchen', 'dining'                  => 'Kitchen & Dining',
                'leisure', 'pool', 'outdoor'          => 'Outdoor, Pool & Leisure',
                'wellness', 'entertainment'          => 'Entertainment & Wellness',
                'technology', 'access', 'service'    => 'Technology & Concierge',
                default                              => 'General & Living'
            };

            if (!isset($grouped[$groupTitle])) {
                $grouped[$groupTitle] = [];
            }
            $grouped[$groupTitle][] = $row;
        }

        // Sort groups in desired presentation order
        $order = [
            'Bedrooms & Bath',
            'General & Living',
            'Kitchen & Dining',
            'Outdoor, Pool & Leisure',
            'Entertainment & Wellness',
            'Technology & Concierge'
        ];

        $sorted = [];
        foreach ($order as $title) {
            if (isset($grouped[$title])) {
                $sorted[$title] = $grouped[$title];
            }
        }
        foreach ($grouped as $title => $items) {
            if (!isset($sorted[$title])) {
                $sorted[$title] = $items;
            }
        }

        return $sorted;
    }

    /**
     * Get all amenities selected for a specific property
     */
    public function getFarmhouseAmenities(int $farmhouseId): array {
        $sql = "SELECT a.id, a.name, a.category, a.icon_class
                FROM amenities a
                JOIN farmhouse_amenities fa ON a.id = fa.amenity_id
                WHERE fa.farmhouse_id = ?
                ORDER BY a.category ASC, a.name ASC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Get array of amenity IDs assigned to a property
     */
    public function getFarmhouseAmenityIds(int $farmhouseId): array {
        return array_map('intval', array_column($this->getFarmhouseAmenities($farmhouseId), 'id'));
    }

    /**
     * Synchronize amenities for a property (idempotent multi-select)
     */
    public function syncAmenities(int $farmhouseId, array $amenityIds): bool {
        // Normalize IDs
        $cleanedIds = array_unique(array_filter(array_map('intval', $amenityIds), fn($id) => $id > 0));

        // Delete currently attached amenities not in the new list (or all if empty)
        if (empty($cleanedIds)) {
            $this->db->query("DELETE FROM farmhouse_amenities WHERE farmhouse_id = {$farmhouseId}");
            return true;
        }

        // Remove unselected
        $idList = implode(',', $cleanedIds);
        $this->db->query("DELETE FROM farmhouse_amenities WHERE farmhouse_id = {$farmhouseId} AND amenity_id NOT IN ({$idList})");

        // Find which ones are already linked
        $existing = [];
        $res = $this->db->query("SELECT amenity_id FROM farmhouse_amenities WHERE farmhouse_id = {$farmhouseId}");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $existing[] = (int)$r['amenity_id'];
            }
        }

        $insertStmt = $this->db->prepare("INSERT INTO farmhouse_amenities (farmhouse_id, amenity_id) VALUES (?, ?)");
        if (!$insertStmt) return false;

        foreach ($cleanedIds as $amId) {
            if (!in_array($amId, $existing, true)) {
                $insertStmt->bind_param("ii", $farmhouseId, $amId);
                $insertStmt->execute();
            }
        }
        $insertStmt->close();
        return true;
    }

    /**
     * Add a new amenity to master table (or reuse existing by name)
     */
    public function addCustomAmenity(string $name, string $category = 'general', string $iconClass = 'check_circle'): int {
        $name = trim($name);
        if (empty($name)) return 0;

        // Check if existing
        $chk = $this->db->prepare("SELECT id FROM amenities WHERE LOWER(name) = LOWER(?) LIMIT 1");
        if ($chk) {
            $chk->bind_param("s", $name);
            $chk->execute();
            $row = $chk->get_result()->fetch_assoc();
            $chk->close();
            if (!empty($row['id'])) {
                return (int)$row['id'];
            }
        }

        $category = in_array($category, ['bedroom', 'kitchen', 'general', 'leisure', 'wellness', 'technology', 'dining', 'outdoor']) ? $category : 'general';
        $iconClass = !empty($iconClass) ? trim($iconClass) : 'check_circle';

        $ins = $this->db->prepare("INSERT INTO amenities (name, category, icon_class) VALUES (?, ?, ?)");
        if (!$ins) return 0;
        $ins->bind_param("sss", $name, $category, $iconClass);
        $ins->execute();
        $newId = $ins->insert_id;
        $ins->close();
        return $newId;
    }

    /**
     * Check if an amenity name already exists (case-insensitive)
     */
    public function checkAmenityNameExists(string $name, ?int $excludeId = null): bool {
        $name = trim($name);
        if (empty($name)) return false;
        if ($excludeId && $excludeId > 0) {
            $stmt = $this->db->prepare("SELECT id FROM amenities WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND id != ? LIMIT 1");
            if (!$stmt) return false;
            $stmt->bind_param("si", $name, $excludeId);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM amenities WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1");
            if (!$stmt) return false;
            $stmt->bind_param("s", $name);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    /**
     * Get all amenities with their property usage counts for admin management
     */
    public function getAllAmenitiesList(): array {
        $sql = "SELECT a.id, a.name, a.category, a.icon_class,
                       COUNT(DISTINCT fa.farmhouse_id) AS usage_count
                FROM amenities a
                LEFT JOIN farmhouse_amenities fa ON a.id = fa.amenity_id
                GROUP BY a.id, a.name, a.category, a.icon_class
                ORDER BY a.category ASC, a.name ASC";
        $res = $this->db->query($sql);
        if (!$res) return [];
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single amenity by ID
     */
    public function getAmenityById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, name, category, icon_class FROM amenities WHERE id = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row) {
            $row['usage_count'] = $this->getAmenityUsageCount($id);
        }
        return $row ?: null;
    }

    /**
     * Get the count of properties currently using a specific amenity
     */
    public function getAmenityUsageCount(int $amenityId): int {
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT farmhouse_id) AS cnt FROM farmhouse_amenities WHERE amenity_id = ?");
        if (!$stmt) return 0;
        $stmt->bind_param("i", $amenityId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($res['cnt'] ?? 0);
    }

    /**
     * Update an existing amenity in the master catalog
     */
    public function updateAmenity(int $id, string $name, string $category = 'general', string $iconClass = 'check_circle'): bool {
        $name = trim($name);
        if (empty($name) || $id <= 0) return false;

        $category = in_array($category, ['bedroom', 'kitchen', 'general', 'leisure', 'wellness', 'technology', 'dining', 'outdoor']) ? $category : 'general';
        $iconClass = !empty($iconClass) ? trim($iconClass) : 'check_circle';

        $stmt = $this->db->prepare("UPDATE amenities SET name = ?, category = ?, icon_class = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("sssi", $name, $category, $iconClass, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Delete an amenity and unlink it from any properties
     */
    public function deleteAmenity(int $id): bool {
        if ($id <= 0) return false;
        // Unlink from properties first
        $this->db->query("DELETE FROM farmhouse_amenities WHERE amenity_id = {$id}");
        // Remove from master table
        $stmt = $this->db->prepare("DELETE FROM amenities WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // ═════════════════════════════════════════════════════════════════════════════
    // ── RULES MANAGEMENT MODULE ──
    // ═════════════════════════════════════════════════════════════════════════════

    /**
     * Get all rule presets with optional description
     */
    public function getRulePresets(): array {
        $res = $this->db->query("SELECT id, rule_name, icon_class, description FROM rule_presets ORDER BY id ASC");
        if (!$res) return [];
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all rule presets with property usage stats
     */
    public function getRulePresetsWithStats(): array {
        $sql = "SELECT rp.id, rp.rule_name, rp.icon_class, rp.description,
                       COUNT(DISTINCT fr.farmhouse_id) AS usage_count
                FROM rule_presets rp
                LEFT JOIN farmhouse_rules fr ON LOWER(TRIM(rp.rule_name)) = LOWER(TRIM(fr.rule_name))
                GROUP BY rp.id, rp.rule_name, rp.icon_class, rp.description
                ORDER BY rp.rule_name ASC";
        $res = $this->db->query($sql);
        if (!$res) return [];
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get single rule preset by ID
     */
    public function getRulePresetById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, rule_name, icon_class, description FROM rule_presets WHERE id = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row) {
            $row['usage_count'] = $this->getRulePresetUsageCount($row['rule_name']);
        }
        return $row ?: null;
    }

    /**
     * Check if a rule preset name already exists (case-insensitive)
     */
    public function checkRulePresetNameExists(string $ruleName, ?int $excludeId = null): bool {
        $ruleName = trim($ruleName);
        if (empty($ruleName)) return false;
        if ($excludeId && $excludeId > 0) {
            $stmt = $this->db->prepare("SELECT id FROM rule_presets WHERE LOWER(TRIM(rule_name)) = LOWER(TRIM(?)) AND id != ? LIMIT 1");
            if (!$stmt) return false;
            $stmt->bind_param("si", $ruleName, $excludeId);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM rule_presets WHERE LOWER(TRIM(rule_name)) = LOWER(TRIM(?)) LIMIT 1");
            if (!$stmt) return false;
            $stmt->bind_param("s", $ruleName);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    /**
     * Get rule preset usage count across properties
     */
    public function getRulePresetUsageCount(string $ruleName): int {
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT farmhouse_id) AS cnt FROM farmhouse_rules WHERE LOWER(TRIM(rule_name)) = LOWER(TRIM(?))");
        if (!$stmt) return 0;
        $stmt->bind_param("s", $ruleName);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($res['cnt'] ?? 0);
    }

    /**
     * Add a new rule preset to master table
     */
    public function addRulePreset(string $ruleName, ?string $iconClass = 'gavel', ?string $description = null): int {
        $ruleName = trim($ruleName);
        if (empty($ruleName)) return 0;
        if ($this->checkRulePresetNameExists($ruleName)) return 0;

        $iconClass = !empty($iconClass) ? trim($iconClass) : 'gavel';
        $description = !empty($description) ? trim($description) : null;

        $stmt = $this->db->prepare("INSERT INTO rule_presets (rule_name, icon_class, description) VALUES (?, ?, ?)");
        if (!$stmt) return 0;
        $stmt->bind_param("sss", $ruleName, $iconClass, $description);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Update an existing rule preset
     */
    public function updateRulePreset(int $id, string $ruleName, ?string $iconClass = 'gavel', ?string $description = null): bool {
        $ruleName = trim($ruleName);
        if ($id <= 0 || empty($ruleName)) return false;
        if ($this->checkRulePresetNameExists($ruleName, $id)) return false;

        $iconClass = !empty($iconClass) ? trim($iconClass) : 'gavel';
        $description = !empty($description) ? trim($description) : null;

        $stmt = $this->db->prepare("UPDATE rule_presets SET rule_name = ?, icon_class = ?, description = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("sssi", $ruleName, $iconClass, $description, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Delete a rule preset from master catalog
     */
    public function deleteRulePreset(int $id): bool {
        if ($id <= 0) return false;
        $stmt = $this->db->prepare("DELETE FROM rule_presets WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Get rules configured for a specific farmhouse
     */
    public function getFarmhouseRules(int $farmhouseId): array {
        $sql = "SELECT fr.id, fr.rule_name, fr.is_allowed, fr.description, fr.sort_order,
                       COALESCE(fr.icon_class, rp.icon_class, 'info') AS icon_class
                FROM farmhouse_rules fr
                LEFT JOIN rule_presets rp ON LOWER(TRIM(fr.rule_name)) = LOWER(TRIM(rp.rule_name))
                WHERE fr.farmhouse_id = ?
                ORDER BY fr.sort_order ASC, fr.id ASC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $rules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rules;
    }

    /**
     * Synchronize rules for a property
     */
    public function syncRules(int $farmhouseId, array $rulesData): bool {
        // Delete previous rules for clean slate sync
        $del = $this->db->prepare("DELETE FROM farmhouse_rules WHERE farmhouse_id = ?");
        if ($del) {
            $del->bind_param("i", $farmhouseId);
            $del->execute();
            $del->close();
        }

        if (empty($rulesData)) return true;

        $ins = $this->db->prepare("INSERT INTO farmhouse_rules (farmhouse_id, rule_name, is_allowed, description, sort_order, icon_class) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$ins) return false;

        $order = 1;
        foreach ($rulesData as $rule) {
            $name = trim($rule['rule_name'] ?? $rule['name'] ?? '');
            if (empty($name)) continue;

            $isAllowed   = !empty($rule['is_allowed']) ? 1 : 0;
            $description = !empty($rule['description']) ? trim((string)$rule['description']) : null;
            $sortOrder   = isset($rule['sort_order']) ? (int)$rule['sort_order'] : $order;
            $iconClass   = !empty($rule['icon_class']) ? trim((string)$rule['icon_class']) : null;

            $ins->bind_param("isisis", $farmhouseId, $name, $isAllowed, $description, $sortOrder, $iconClass);
            $ins->execute();
            $order++;
        }
        $ins->close();
        return true;
    }

    // ═════════════════════════════════════════════════════════════════════════════
    // ── ROOM-WISE INVENTORY & DUAL BOOKING MODULE ──
    // ═════════════════════════════════════════════════════════════════════════════

    /**
     * Get all room types configured for a farmhouse
     */
    public function getFarmhouseRoomTypes(int $farmhouseId, bool $activeOnly = false): array {
        $statusClause = $activeOnly ? "AND status = 'active'" : "";
        $sql = "SELECT rt.*,
                       (SELECT COUNT(*) FROM booking_requests WHERE room_type_id = rt.id) AS total_requests_count,
                       (SELECT COUNT(*) FROM booking_requests WHERE room_type_id = rt.id AND status IN ('approved', 'completed', 'pending')) AS active_requests_count
                FROM farmhouse_room_types rt
                WHERE rt.farmhouse_id = ? {$statusClause}
                ORDER BY rt.price_per_room ASC, rt.id ASC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $roomTypes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($roomTypes as &$rt) {
            $rtId = (int)$rt['id'];
            $rt['has_bookings'] = ((int)($rt['total_requests_count'] ?? 0) > 0) || $this->hasRoomTypeBookings($rtId);
        }

        return $roomTypes;
    }

    /**
     * Check if a room type has active or historical booking records
     */
    public function hasRoomTypeBookings(int $roomTypeId): bool {
        // 1. Check booking_requests
        $chk1 = $this->db->prepare("SELECT COUNT(*) AS cnt FROM booking_requests WHERE room_type_id = ?");
        if ($chk1) {
            $chk1->bind_param("i", $roomTypeId);
            $chk1->execute();
            $cnt1 = (int)($chk1->get_result()->fetch_assoc()['cnt'] ?? 0);
            $chk1->close();
            if ($cnt1 > 0) return true;
        }

        // 2. Check owner_offline_bookings
        $chk2 = $this->db->prepare("SELECT COUNT(*) AS cnt FROM owner_offline_bookings WHERE room_type_id = ?");
        if ($chk2) {
            $chk2->bind_param("i", $roomTypeId);
            $chk2->execute();
            $cnt2 = (int)($chk2->get_result()->fetch_assoc()['cnt'] ?? 0);
            $chk2->close();
            if ($cnt2 > 0) return true;
        }

        return false;
    }

    /**
     * Save an individual room type (Insert or Update)
     */
    public function saveRoomType(int $farmhouseId, array $data, ?int $roomTypeId = null): int {
        $name         = trim((string)($data['room_type_name'] ?? 'Standard Room'));
        $totalRooms   = max(1, (int)($data['total_rooms'] ?? 1));
        $pricePerRoom = max(0, (float)($data['price_per_room'] ?? 0));
        $weekendPrice = !empty($data['weekend_price']) ? (float)$data['weekend_price'] : round($pricePerRoom * 1.25, 2);
        $capacity     = max(1, (int)($data['capacity_per_room'] ?? 2));
        $description  = trim((string)($data['description'] ?? ''));
        $status       = in_array($data['status'] ?? '', ['active', 'inactive']) ? $data['status'] : 'active';
        $imageUrl     = !empty($data['image_url']) ? trim((string)$data['image_url']) : null;

        if ($roomTypeId && $roomTypeId > 0) {
            $sql = "UPDATE farmhouse_room_types SET
                        room_type_name = ?, total_rooms = ?, price_per_room = ?, weekend_price = ?,
                        capacity_per_room = ?, description = ?, status = ?, image_url = ?
                    WHERE id = ? AND farmhouse_id = ?";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return 0;
            $stmt->bind_param("siddssssii", $name, $totalRooms, $pricePerRoom, $weekendPrice, $capacity, $description, $status, $imageUrl, $roomTypeId, $farmhouseId);
            $stmt->execute();
            $stmt->close();
            return $roomTypeId;
        } else {
            $sql = "INSERT INTO farmhouse_room_types (
                        farmhouse_id, room_type_name, total_rooms, price_per_room, weekend_price,
                        capacity_per_room, description, status, image_url
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return 0;
            $stmt->bind_param("isiddssss", $farmhouseId, $name, $totalRooms, $pricePerRoom, $weekendPrice, $capacity, $description, $status, $imageUrl);
            $stmt->execute();
            $newId = $stmt->insert_id;
            $stmt->close();
            return $newId;
        }
    }

    /**
     * Delete a room type with safeguard against historical/active bookings
     */
    public function deleteRoomType(int $roomTypeId, int $farmhouseId): array {
        if ($this->hasRoomTypeBookings($roomTypeId)) {
            return [
                'success' => false,
                'message' => 'Safeguard Activated: This room type cannot be deleted because active or historical booking records exist for it. You can set its status to "Inactive" instead.'
            ];
        }

        $stmt = $this->db->prepare("DELETE FROM farmhouse_room_types WHERE id = ? AND farmhouse_id = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database statement preparation failed.'];
        }
        $stmt->bind_param("ii", $roomTypeId, $farmhouseId);
        $ok = $stmt->execute();
        $stmt->close();

        return [
            'success' => $ok,
            'message' => $ok ? 'Room type removed successfully.' : 'Failed to delete room type.'
        ];
    }

    /**
     * Batch synchronize room types from property form submission
     */
    public function syncRoomTypes(int $farmhouseId, array $roomTypesData): bool {
        if (empty($roomTypesData) || !is_array($roomTypesData)) return true;

        $keptIds = [];
        foreach ($roomTypesData as $rt) {
            $name = trim($rt['room_type_name'] ?? '');
            if (empty($name)) continue;

            $rtId = !empty($rt['id']) ? (int)$rt['id'] : null;
            $savedId = $this->saveRoomType($farmhouseId, $rt, $rtId);
            if ($savedId > 0) {
                $keptIds[] = $savedId;
            }
        }

        return true;
    }
}
