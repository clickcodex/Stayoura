<?php

namespace App\Models\User;

use App\Config\Database;
use Exception;

class FarmhouseModel
{
    private \mysqli $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Auto-detect mysqli bind types ('i', 'd', 's') from param values.
     */
    private function buildTypes(array $params): string
    {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param))        $types .= 'i';
            elseif (is_float($param))  $types .= 'd';
            else                       $types .= 's';
        }
        return $types;
    }

    /**
     * Run a SELECT and return all matching rows as associative arrays.
     */
    private function query(string $sql, array $params = [], string $types = ''): array
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            throw new Exception("Database query error.");
        }

        if (!empty($params)) {
            if ($types === '') $types = $this->buildTypes($params);
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows   = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    /**
     * Run a SELECT and return only the first row (or null).
     */
    private function queryOne(string $sql, array $params = [], string $types = ''): ?array
    {
        $rows = $this->query($sql, $params, $types);
        return $rows[0] ?? null;
    }

    // =========================================================================
    // LISTING PAGE METHODS
    // =========================================================================

    /**
     * Paginated farmhouse listing with optional filters.
     *
     * $filters keys: search, min_price, max_price, amenities (int[])
     * Returns: ['data', 'total', 'pages', 'current_page', 'per_page']
     */
    public function getListing(array $filters = [], int $page = 1, int $perPage = 9): array
    {
        $conditions = ["f.status = 'active'"];
        $params     = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(f.title LIKE ? OR f.location LIKE ?)";
            $keyword      = '%' . trim($filters['search']) . '%';
            $params[]     = $keyword;
            $params[]     = $keyword;
        }

        if (!empty($filters['category'])) {
            $conditions[] = "f.category = ?";
            $params[]     = trim($filters['category']);
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $conditions[] = "f.price >= ?";
            $params[]     = (float) $filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $conditions[] = "f.price <= ?";
            $params[]     = (float) $filters['max_price'];
        }

        $whereClause   = implode(' AND ', $conditions);
        $amenityJoin   = '';
        $amenityParams = [];
        $havingClause  = '';

        if (!empty($filters['amenities']) && is_array($filters['amenities'])) {
            $amenityIds   = array_map('intval', $filters['amenities']);
            $amenityCount = count($amenityIds);
            $placeholders = implode(',', array_fill(0, $amenityCount, '?'));
            $amenityJoin  = "INNER JOIN farmhouse_amenities fa ON fa.farmhouse_id = f.id
                                AND fa.amenity_id IN ($placeholders)";
            $amenityParams = $amenityIds;
            $havingClause  = "HAVING COUNT(DISTINCT fa.amenity_id) = $amenityCount";
        }

        $countParams = array_merge($amenityParams, $params);
        $countResult = $this->queryOne("
            SELECT COUNT(DISTINCT f.id) AS total
            FROM farmhouses f {$amenityJoin}
            WHERE {$whereClause}
        ", $countParams);

        $total      = (int) ($countResult['total'] ?? 0);
        $page       = max(1, $page);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = min($page, $totalPages);
        $offset     = ($page - 1) * $perPage;

        $queryParams = array_merge($amenityParams, $params, [$perPage, $offset]);

        $farmhouses = $this->query("
            SELECT
                f.id, f.title, f.location, f.address, f.category,
                f.price, f.is_negotiable, f.description,
                (SELECT img.image_url FROM images img WHERE img.farmhouse_id = f.id LIMIT 1) AS primary_image,
                GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ') AS amenities
            FROM farmhouses f
            LEFT JOIN farmhouse_amenities fa2 ON fa2.farmhouse_id = f.id
            LEFT JOIN amenities a ON a.id = fa2.amenity_id
            {$amenityJoin}
            WHERE {$whereClause}
            GROUP BY f.id, f.title, f.location, f.address, f.category, f.price, f.is_negotiable, f.description
            {$havingClause}
            ORDER BY f.created_at DESC
            LIMIT ? OFFSET ?
        ", $queryParams);

        return [
            'data'         => $farmhouses,
            'total'        => $total,
            'pages'        => $totalPages,
            'current_page' => $page,
            'per_page'     => $perPage,
        ];
    }

    /** All amenities for filter checkboxes */
    public function getAllAmenities(): array
    {
        return $this->query("SELECT id, name FROM amenities ORDER BY name ASC");
    }

    /** Global min/max price for price range inputs */
    public function getPriceRange(): array
    {
        $result = $this->queryOne("
            SELECT MIN(price) AS min_price, MAX(price) AS max_price
            FROM farmhouses WHERE status = 'active'
        ");
        return [
            'min' => (float) ($result['min_price'] ?? 0),
            'max' => (float) ($result['max_price'] ?? 0),
        ];
    }

    // =========================================================================
    // DETAIL PAGE METHODS
    // =========================================================================

    /**
     * Single farmhouse with all images and amenities.
     * Returns null if not found or inactive.
     */
    public function getById(int $id): ?array
    {
        $farmhouse = $this->queryOne("
            SELECT
                f.*,
                GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR '||') AS amenities
            FROM farmhouses f
            LEFT JOIN farmhouse_amenities fa ON fa.farmhouse_id = f.id
            LEFT JOIN amenities a ON a.id = fa.amenity_id
            WHERE f.id = ? AND f.status = 'active'
            GROUP BY f.id
        ", [$id], 'i');

        if (!$farmhouse) return null;

        // Convert amenities string to array
        $farmhouse['amenities'] = !empty($farmhouse['amenities'])
            ? explode('||', $farmhouse['amenities'])
            : [];

        // All images
        $farmhouse['images'] = $this->query(
            "SELECT image_url FROM images WHERE farmhouse_id = ? ORDER BY id ASC",
            [$id], 'i'
        );

        return $farmhouse;
    }

    /**
     * Get booked and blocked date ranges for a farmhouse (for calendar rendering).
     * Returns array of ['start_date', 'end_date', 'type'] where type = 'booked' | 'blocked'
     */
    public function getUnavailableDates(int $farmhouseId): array
    {
        $booked = $this->query("
            SELECT COALESCE(check_in, start_date) AS start_date, COALESCE(check_out, end_date) AS end_date, 'booked' AS type
            FROM booking_requests
            WHERE farmhouse_id = ? AND status IN ('approved', 'completed')
              AND COALESCE(check_out, end_date) >= CURDATE()
        ", [$farmhouseId], 'i');

        $blocked = $this->query("
            SELECT start_date, end_date, 'blocked' AS type
            FROM blocked_dates
            WHERE farmhouse_id = ?
              AND end_date >= CURDATE()
        ", [$farmhouseId], 'i');

        return array_merge($booked, $blocked);
    }

    /**
     * Get admin contact info from settings table.
     */
    public function getAdminSettings(): ?array
    {
        $settings = \App\Helpers\SiteHelper::getSettings();
        if (!empty($settings['mobile_number'])) {
            return [
                'admin_phone'    => $settings['mobile_number'],
                'admin_whatsapp' => $settings['whatsapp_number'] ?? $settings['mobile_number']
            ];
        }
        return $this->queryOne("SELECT admin_phone, admin_whatsapp FROM settings LIMIT 1");
    }

    // =========================================================================
    // INQUIRY SUBMISSION
    // =========================================================================

    /**
     * Save an inquiry from the detail page form.
     * Maps to: inquiries (user_id, farmhouse_id, type, name, phone, message, status)
     *
     * @param int    $farmhouseId
     * @param array  $data        Keys: name, phone, message, type
     * @param int|null $userId    Logged-in user ID (null if guest)
     * @return bool
     */
    public function submitInquiry(int $farmhouseId, array $data, ?int $userId = null): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO inquiries (user_id, farmhouse_id, type, name, phone, message, status)
            VALUES (?, ?, ?, ?, ?, ?, 'new')
        ");

        if (!$stmt) {
            error_log("Inquiry prepare failed: " . $this->db->error);
            return false;
        }

        $type    = in_array($data['type'], ['call', 'whatsapp']) ? $data['type'] : 'call';
        $name    = trim($data['name']);
        $phone   = trim($data['phone']);
        $message = trim($data['message'] ?? '');

        $stmt->bind_param('iissss', $userId, $farmhouseId, $type, $name, $phone, $message);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    // =========================================================================
    // AVAILABILITY CHECK (reusable for booking flow)
    // =========================================================================

    public function isAvailable(int $farmhouseId, string $startDate, string $endDate): bool
    {
        $booking = $this->queryOne("
            SELECT id FROM booking_requests
            WHERE farmhouse_id = ? AND status IN ('approved', 'completed')
              AND NOT (COALESCE(check_out, end_date) <= ? OR COALESCE(check_in, start_date) >= ?)
            LIMIT 1
        ", [$farmhouseId, $startDate, $endDate], 'iss');

        if ($booking) return false;

        $blocked = $this->queryOne("
            SELECT id FROM blocked_dates
            WHERE farmhouse_id = ?
              AND NOT (end_date <= ? OR start_date >= ?)
            LIMIT 1
        ", [$farmhouseId, $startDate, $endDate], 'iss');

        return !$blocked;
    }
}
