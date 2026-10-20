<?php
namespace App\Models;

use mysqli;

class FarmdetailModel {

    private $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Get a single active farmhouse by ID, joined with owner info and dynamic property type.
     */
    public function getFarmhouseById(int $id): ?array {
        $sql = "
            SELECT
                f.id, f.title, f.description, f.location, f.address, f.google_map_link, f.category,
                f.property_type_id, f.is_verified, f.verification_notes,
                COALESCE(pt.name, f.category) AS property_type_name,
                COALESCE(pt.icon_class, 'villa') AS property_type_icon,
                f.price, f.room_price, f.rental_type, f.allow_room_booking, f.bedrooms, 
                f.bedroom_capacity, f.day_capacity, f.night_capacity,
                f.is_negotiable, f.created_at, f.status,
                f.contact_phone  AS owner_phone,
                f.whatsapp_number AS owner_whatsapp,
                u.name  AS owner_name,
                u.email AS owner_email
            FROM farmhouses f
            LEFT JOIN users u ON f.contact_phone = u.phone
            LEFT JOIN property_types pt ON f.property_type_id = pt.id
            WHERE f.id = ?
              AND f.status = 'active'
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("getFarmhouseById primary prepare failed: " . $this->db->error);
            $fallbackSql = "
                SELECT
                    f.id, f.title, f.description, f.location, f.address, f.google_map_link, f.category,
                    f.price, f.room_price, f.rental_type, f.allow_room_booking, f.bedrooms, 
                    f.bedroom_capacity, f.day_capacity, f.night_capacity,
                    f.is_negotiable, f.created_at, f.status,
                    1 AS is_verified, 'Verified Host' AS verification_notes,
                    f.category AS property_type_name, 'villa' AS property_type_icon,
                    f.contact_phone  AS owner_phone,
                    f.whatsapp_number AS owner_whatsapp,
                    u.name  AS owner_name,
                    u.email AS owner_email
                FROM farmhouses f
                LEFT JOIN users u ON f.contact_phone = u.phone
                WHERE f.id = ?
                  AND f.status = 'active'
                LIMIT 1
            ";
            $stmt = $this->db->prepare($fallbackSql);
            if (!$stmt) return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    /**
     * All images for a farmhouse with category, featured status, and caption.
     */
    public function getFarmhouseImages(int $farmhouseId): array {
        $stmt = $this->db->prepare(
            "SELECT id, image_url, COALESCE(category, 'exterior') AS category, caption, is_featured, sort_order 
             FROM images 
             WHERE farmhouse_id = ? 
             ORDER BY is_featured DESC, sort_order ASC, id ASC"
        );
        if (!$stmt) {
            $fallback = $this->db->prepare("SELECT id, image_url, 'exterior' AS category FROM images WHERE farmhouse_id = ? ORDER BY id ASC");
            if (!$fallback) return [];
            $fallback->bind_param("i", $farmhouseId);
            $fallback->execute();
            return $fallback->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Assign intelligent category tags if default exterior
        foreach ($res as &$img) {
            if (empty($img['category']) || $img['category'] === 'exterior') {
                $lower = strtolower($img['image_url'] ?? '');
                if (str_contains($lower, 'pool') || str_contains($lower, 'swim')) {
                    $img['category'] = 'pool';
                } elseif (str_contains($lower, 'bed') || str_contains($lower, 'room')) {
                    $img['category'] = 'bedroom';
                } elseif (str_contains($lower, 'living') || str_contains($lower, 'interior') || str_contains($lower, 'hall')) {
                    $img['category'] = 'interior';
                } elseif (str_contains($lower, 'kitchen') || str_contains($lower, 'din')) {
                    $img['category'] = 'kitchen';
                } elseif (str_contains($lower, 'bath')) {
                    $img['category'] = 'bathroom';
                } elseif (str_contains($lower, 'lawn') || str_contains($lower, 'garden')) {
                    $img['category'] = 'garden';
                }
            }
        }
        return $res;
    }

    /**
     * Similar Properties Recommendation Engine (F77)
     */
    public function getSimilarFarmhouses(int $currentId, string $location, ?int $propertyTypeId = null, int $limit = 4): array {
        $sql = "
            SELECT 
                f.id, f.title, f.location, f.price, f.room_price, f.allow_room_booking,
                f.bedrooms, f.night_capacity, f.day_capacity, f.is_verified, f.verification_notes,
                COALESCE(pt.name, f.category) AS property_type_name,
                COALESCE(pt.icon_class, 'villa') AS property_type_icon,
                (SELECT image_url FROM images WHERE farmhouse_id = f.id ORDER BY is_featured DESC, sort_order ASC, id ASC LIMIT 1) AS primary_image
            FROM farmhouses f
            LEFT JOIN property_types pt ON f.property_type_id = pt.id
            WHERE f.id != ?
              AND f.status = 'active'
              AND f.admin_approval_status = 'approved'
            ORDER BY 
              (f.location LIKE CONCAT('%', ?, '%')) DESC,
              (f.property_type_id = ?) DESC,
              f.id DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $pId = $propertyTypeId ?: 0;
        $stmt->bind_param("isii", $currentId, $location, $pId, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $similar = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $similar;
    }

    /**
     * All amenities for a farmhouse
     */
    public function getFarmhouseAmenities(int $farmhouseId): array {
        $sql = "
            SELECT
                a.id,
                a.name,
                a.category,
                a.icon_class,
                fa.bedroom_number
            FROM amenities a
            JOIN farmhouse_amenities fa ON a.id = fa.amenity_id
            WHERE fa.farmhouse_id = ?
            ORDER BY a.category, fa.bedroom_number, a.name
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * House rules for a farmhouse
     */
public function getFarmhouseRules(int $farmhouseId): array {
    $stmt = $this->db->prepare(
        "SELECT fr.rule_name,
                COALESCE(fr.icon_class, rp.icon_class, 'fas fa-shield-alt') AS icon_class,
                fr.description,
                fr.is_allowed
         FROM farmhouse_rules fr
         LEFT JOIN rule_presets rp
            ON fr.rule_name = rp.rule_name
         WHERE fr.farmhouse_id = ?
         ORDER BY fr.sort_order ASC, fr.is_allowed DESC, fr.rule_name ASC"
    );

    if (!$stmt) return [];

    $stmt->bind_param("i", $farmhouseId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

    /**
     * Release expired temporary 15-minute booking holds across the platform (F34)
     */
    public function releaseExpiredHolds(): int {
        $stmt = $this->db->prepare("UPDATE booking_requests SET status = 'expired' WHERE status = 'pending' AND hold_expires_at IS NOT NULL AND hold_expires_at < NOW()");
        if ($stmt) {
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            return $affected;
        }
        return 0;
    }

    /**
     * All occupied dates (for the calendar display — approved bookings + active held bookings + blocked dates).
     */
    public function getOccupiedDates(int $farmhouseId): array {
        $this->releaseExpiredHolds();

        $sql = "
            SELECT start_date, end_date
              FROM bookings
             WHERE farmhouse_id = ? AND status IN ('approved', 'active')
            UNION
            SELECT check_in AS start_date, check_out AS end_date
              FROM booking_requests
             WHERE farmhouse_id = ? 
               AND (status IN ('approved', 'completed') OR (status = 'pending' AND hold_expires_at > NOW()))
            UNION
            SELECT start_date, end_date
              FROM blocked_dates
             WHERE farmhouse_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("iii", $farmhouseId, $farmhouseId, $farmhouseId);
        $stmt->execute();
        $result = $stmt->get_result();

        $days = [];
        while ($row = $result->fetch_assoc()) {
            if (empty($row['start_date']) || empty($row['end_date'])) continue;

            $begin = new \DateTime($row['start_date']);
            $end   = new \DateTime($row['end_date']);
            // ✅ Checkout date is NOT occupied, new guest can check in

            foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $date) {
                $days[] = $date->format('Y-m-d');
            }
        }
        $stmt->close();

        return array_values(array_unique($days));
    }

    /**
     * Dates the logged-in user has pending requests for.
     */
    public function getUserRequestedDates(int $farmhouseId, int $userId): array {
    if (!$userId) return [];

    $stmt = $this->db->prepare(
        "SELECT check_in AS start_date, check_out AS end_date
           FROM booking_requests
          WHERE farmhouse_id = ? AND user_id = ? AND status = 'pending'"
    );
    if (!$stmt) return [];

    $stmt->bind_param("ii", $farmhouseId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $days = [];
    while ($row = $result->fetch_assoc()) {
        if (empty($row['start_date']) || empty($row['end_date'])) continue;

        $begin = new \DateTime($row['start_date']);
        $end   = new \DateTime($row['end_date']);
        // ✅ NO +1 day

        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $date) {
            $days[] = $date->format('Y-m-d');
        }
    }

    return array_values(array_unique($days));
}

    /**
     * List of pending booking requests by the current user for this farmhouse.
     */
    public function getPendingRequestsByUser(int $farmhouseId, int $userId): array {
        if (!$userId) return [];

        $stmt = $this->db->prepare(
            "SELECT check_in, check_in_time, check_out, check_out_time, guests, price, created_at
               FROM booking_requests
              WHERE farmhouse_id = ? AND user_id = ? AND status = 'pending'
              ORDER BY check_in ASC"
        );
        if (!$stmt) return [];

        $stmt->bind_param("ii", $farmhouseId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check whether the user already has an active pending or approved booking for overlapping dates.
     */
    public function hasDuplicatePendingRequest(int $userId, int $farmhouseId, string $checkIn, string $checkOut): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS cnt
             FROM booking_requests
             WHERE user_id = ? 
               AND farmhouse_id = ?
               AND status IN ('pending', 'approved')
               AND check_in < ?
               AND check_out > ?"
        );
        if (!$stmt) return false;
        $stmt->bind_param("iiss", $userId, $farmhouseId, $checkOut, $checkIn);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['cnt'] ?? 0) > 0;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CAPACITY & OVERLAP VALIDATION
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Check whether any admin-blocked date range overlaps the requested window.
     *
     * A blocked_dates row with start_date ≤ check_out AND end_date ≥ check_in
     * overlaps (standard interval overlap test).
     *
     * @param int    $farmhouseId
     * @param string $checkIn   'Y-m-d'
     * @param string $checkOut  'Y-m-d'
     * @return bool  true if the window is blocked by admin
     */
    public function isBlockedForDates(int $farmhouseId, string $checkIn, string $checkOut): bool {
    $sql = "
        SELECT COUNT(*) AS cnt
          FROM blocked_dates
         WHERE farmhouse_id = ?
           AND start_date   < ?
           AND end_date     > ?
    ";
    // ✅ strict < and > — touching boundaries (same day checkout/checkin) is allowed

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        error_log("isBlockedForDates prepare failed: " . $this->db->error);
        return false;
    }

    $stmt->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['cnt'] ?? 0) > 0;
}

    /**
     * Return the maximum number of guests already committed (approved) for ANY
     * single day within the requested window.
     *
     * We look at two sources:
     *   1. `bookings`         – approved legacy bookings (no guest column, count as 1 each).
     *   2. `booking_requests` – approved requests (have a `guests` column).
     *
     * Rather than expanding every date into individual rows in PHP (slow for
     * long date ranges), we use a "does this row overlap the window?" filter
     * and take the SUM of guests for that window. This gives a conservative
     * upper bound — the actual peak on any single day can only be equal to or
     * less than this sum, which is safe for our purposes.
     *
     * Note: The `bookings` table has no `guests` column, so we count each
     * approved booking record as 1 guest-slot consumed to be safe.
     *
     * @param int    $farmhouseId
     * @param string $checkIn   'Y-m-d'
     * @param string $checkOut  'Y-m-d'
     * @return int   Total overlapping guest count
     */
    public function getOverlappingGuestCount(int $farmhouseId, string $checkIn, string $checkOut): int {
        // ── From `bookings` (approved, no guest column — treat each record as 1) ──
        $sql1 = "
            SELECT COUNT(*) AS total
              FROM bookings
             WHERE farmhouse_id = ?
               AND status       = 'approved'
               AND start_date   < ?
               AND end_date     > ?
        ";

        $stmt1 = $this->db->prepare($sql1);
        if (!$stmt1) {
            error_log("getOverlappingGuestCount (bookings) prepare failed: " . $this->db->error);
            return 0;
        }
        $stmt1->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
        $stmt1->execute();
        $row1    = $stmt1->get_result()->fetch_assoc();
        $legacy  = (int)($row1['total'] ?? 0);

        // ── From `booking_requests` (approved/completed, has guest count) ──
        $sql2 = "
            SELECT COALESCE(SUM(guests), 0) AS total
              FROM booking_requests
             WHERE farmhouse_id = ?
               AND status       IN ('approved', 'completed')
               AND check_in     < ?
               AND check_out    > ?
        ";

        $stmt2 = $this->db->prepare($sql2);
        if (!$stmt2) {
            error_log("getOverlappingGuestCount (booking_requests) prepare failed: " . $this->db->error);
            return $legacy;
        }
        $stmt2->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
        $stmt2->execute();
        $row2     = $stmt2->get_result()->fetch_assoc();
        $requests = (int)($row2['total'] ?? 0);

        return $legacy + $requests;
    }

    /**
     * Fetch active room types for a farmhouse with independent galleries and room-specific amenities (F12, F13, F14).
     * @param int $farmhouseId
     * @return array
     */
    public function getFarmhouseRoomTypes(int $farmhouseId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM farmhouse_room_types 
             WHERE farmhouse_id = ? AND status = 'active' 
             ORDER BY price_per_room ASC, id ASC"
        );
        if (!$stmt) return [];
        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $roomTypes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($roomTypes as &$rt) {
            $rtId = (int)$rt['id'];
            $rt['images'] = $this->getRoomImages($farmhouseId, $rtId);
            $rt['amenities'] = $this->getRoomAmenities($farmhouseId, $rtId);
            if (empty($rt['weekend_price']) || (float)$rt['weekend_price'] <= 0) {
                $rt['weekend_price'] = round((float)$rt['price_per_room'] * 1.2, 0);
            }
            if (empty($rt['image_url']) && !empty($rt['images'])) {
                $rt['image_url'] = $rt['images'][0]['image_url'] ?? null;
            }
        }

        return $roomTypes;
    }

    /**
     * Room-Wise Independent Photo Galleries (F13)
     */
    public function getRoomImages(int $farmhouseId, int $roomTypeId): array {
        $stmt = $this->db->prepare(
            "SELECT id, image_url, category, caption FROM images 
             WHERE farmhouse_id = ? AND room_type_id = ? 
             ORDER BY is_featured DESC, sort_order ASC, id ASC"
        );
        if (!$stmt) return [];
        $stmt->bind_param("ii", $farmhouseId, $roomTypeId);
        $stmt->execute();
        $imgs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // If no dedicated room images, fallback to bedroom photos tagged for this farmhouse
        if (empty($imgs)) {
            $stmt2 = $this->db->prepare(
                "SELECT id, image_url, category, caption FROM images 
                 WHERE farmhouse_id = ? AND category = 'bedroom' 
                 ORDER BY is_featured DESC, sort_order ASC, id ASC LIMIT 5"
            );
            if ($stmt2) {
                $stmt2->bind_param("i", $farmhouseId);
                $stmt2->execute();
                $imgs = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt2->close();
            }
        }

        return $imgs;
    }

    /**
     * Room-Specific Amenities Assignment (F14)
     */
    public function getRoomAmenities(int $farmhouseId, int $roomTypeId): array {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.name, a.category, a.icon_class
             FROM farmhouse_amenities fa
             JOIN amenities a ON a.id = fa.amenity_id
             WHERE fa.farmhouse_id = ? AND fa.room_type_id = ?
             ORDER BY a.name ASC"
        );
        if (!$stmt) return [];
        $stmt->bind_param("ii", $farmhouseId, $roomTypeId);
        $stmt->execute();
        $amenities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // If none specifically bound by room_type_id, fallback to bedroom amenities for the property
        if (empty($amenities)) {
            $stmt2 = $this->db->prepare(
                "SELECT a.id, a.name, a.category, a.icon_class
                 FROM farmhouse_amenities fa
                 JOIN amenities a ON a.id = fa.amenity_id
                 WHERE fa.farmhouse_id = ? AND (a.category = 'bedroom' OR a.name LIKE '%Bed%' OR a.name LIKE '%AC%' OR a.name LIKE '%Bath%')
                 ORDER BY a.name ASC LIMIT 6"
            );
            if ($stmt2) {
                $stmt2->bind_param("i", $farmhouseId);
                $stmt2->execute();
                $amenities = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt2->close();
            }
        }

        return $amenities;
    }

    /**
     * Fetch active seasonal / festive surge prices (F21)
     */
    public function getSeasonalPrices(int $farmhouseId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM seasonal_prices 
             WHERE farmhouse_id = ? AND status = 'active' AND end_date >= CURDATE()
             ORDER BY start_date ASC"
        );
        if (!$stmt) return [];
        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Fetch optional property addons (F24)
     */
    public function getPropertyAddons(int $farmhouseId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM property_addons 
             WHERE farmhouse_id = ? AND status = 'active'
             ORDER BY id ASC"
        );
        if (!$stmt) return [];
        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Validate and retrieve coupon by code (F74)
     */
    public function getCoupon(string $code): ?array {
        $code = strtoupper(trim($code));
        if (empty($code)) return null;

        $stmt = $this->db->prepare(
            "SELECT * FROM coupons 
             WHERE code = ? AND status = 'active' 
               AND (valid_from IS NULL OR valid_from <= CURDATE())
               AND (valid_until IS NULL OR valid_until >= CURDATE())
             LIMIT 1"
        );
        if (!$stmt) return null;
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Unified Marketplace Dynamic Pricing Engine (F19, F20, F21, F22, F23, F24, F25, F74)
     * Calculates transparent quote with weekend tariff, seasonal surge, long-stay discounts,
     * addons, security deposit, platform commission, and coupon deductions.
     */
    public function calculateQuote(array $params): array {
        $farmhouseId = (int)($params['farmhouse_id'] ?? 0);
        $farmhouse   = $this->getFarmhouseById($farmhouseId);
        if (!$farmhouse) {
            return ['error' => 'Property not found'];
        }

        $bookingType = strtolower($params['booking_type'] ?? 'complete') === 'per_room' ? 'per_room' : 'complete';
        $rooms       = max(1, (int)($params['rooms'] ?? 1));
        $guests      = max(1, (int)($params['guests'] ?? 2));
        $roomTypeId  = !empty($params['room_type_id']) ? (int)$params['room_type_id'] : null;

        $checkInStr  = trim($params['check_in'] ?? '');
        $checkOutStr = trim($params['check_out'] ?? '');
        if (empty($checkInStr) || empty($checkOutStr)) {
            $checkInStr  = date('Y-m-d');
            $checkOutStr = date('Y-m-d', strtotime('+1 day'));
        }

        $cIn  = new \DateTime($checkInStr);
        $cOut = new \DateTime($checkOutStr);
        if ($cOut <= $cIn) {
            $cOut = (clone $cIn)->modify('+1 day');
        }

        $nights = $cIn->diff($cOut)->days;
        if ($nights <= 0) $nights = 1;

        // Base property/room rate
        $weekdayPrice = 0.0;
        $weekendPrice = 0.0;

        if ($bookingType === 'per_room' && $roomTypeId) {
            $room = $this->getRoomTypeById($roomTypeId, $farmhouseId);
            if ($room) {
                $weekdayPrice = (float)$room['price_per_room'];
                $weekendPrice = !empty($room['weekend_price']) ? (float)$room['weekend_price'] : round($weekdayPrice * 1.2, 0);
            }
        }
        if ($weekdayPrice <= 0) {
            $weekdayPrice = (float)($farmhouse['price'] ?? 0);
            $weekendPrice = !empty($farmhouse['weekend_price']) ? (float)$farmhouse['weekend_price'] : round($weekdayPrice * 1.2, 0);
        }

        // Fetch seasonal surge dates
        $seasonalRules = $this->getSeasonalPrices($farmhouseId);

        // Day by day calculation
        $baseStayTotal = 0.0;
        $weekdayNights = 0;
        $weekendNights = 0;
        $seasonalNights = 0;
        $dailyBreakdown = [];

        $curr = clone $cIn;
        while ($curr < $cOut) {
            $dateStr = $curr->format('Y-m-d');
            $dayOfWeek = (int)$curr->format('w'); // 0 = Sun, 5 = Fri, 6 = Sat
            $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 5 || $dayOfWeek === 6);

            $nightPrice = $isWeekend ? $weekendPrice : $weekdayPrice;
            $rateType = $isWeekend ? 'weekend' : 'weekday';

            // Check seasonal pricing match
            foreach ($seasonalRules as $sr) {
                if ($dateStr >= $sr['start_date'] && $dateStr <= $sr['end_date']) {
                    if ((float)$sr['price_per_night'] > 0) {
                        $nightPrice = (float)$sr['price_per_night'];
                    } elseif ((float)$sr['multiplier'] > 1.0) {
                        $nightPrice = round($nightPrice * (float)$sr['multiplier'], 0);
                    }
                    $rateType = 'seasonal (' . $sr['season_name'] . ')';
                    $seasonalNights++;
                    break;
                }
            }

            if ($rateType === 'weekend') $weekendNights++;
            elseif ($rateType === 'weekday') $weekdayNights++;

            $subNight = ($bookingType === 'per_room') ? ($nightPrice * $rooms) : $nightPrice;
            $baseStayTotal += $subNight;

            $dailyBreakdown[] = [
                'date' => $dateStr,
                'rate_type' => $rateType,
                'price' => $subNight
            ];

            $curr->modify('+1 day');
        }

        // Long-stay discount tier (F22)
        $weeklyDiscPct  = (float)($farmhouse['weekly_discount_percent'] ?? 10.0);
        $monthlyDiscPct = (float)($farmhouse['monthly_discount_percent'] ?? 20.0);
        $longStayDiscount = 0.0;
        $longStayLabel = '';

        if ($nights >= 28 && $monthlyDiscPct > 0) {
            $longStayDiscount = round($baseStayTotal * ($monthlyDiscPct / 100), 2);
            $longStayLabel = "Monthly Stay Discount ({$monthlyDiscPct}%)";
        } elseif ($nights >= 7 && $weeklyDiscPct > 0) {
            $longStayDiscount = round($baseStayTotal * ($weeklyDiscPct / 100), 2);
            $longStayLabel = "Weekly Stay Discount ({$weeklyDiscPct}%)";
        }

        // Fixed fees
        $cleaningFee     = (float)($farmhouse['cleaning_fee'] ?? 0.0);
        $securityDeposit = (float)($farmhouse['security_deposit'] ?? 2500.0);

        // Addon services (F24)
        $addonCharges = 0.0;
        $selectedAddons = [];
        $rawAddonIds = $params['addons'] ?? [];
        if (!is_array($rawAddonIds) && is_string($rawAddonIds)) {
            $rawAddonIds = explode(',', $rawAddonIds);
        }
        $availAddons = $this->getPropertyAddons($farmhouseId);
        $availAddonsMap = [];
        foreach ($availAddons as $ad) {
            $availAddonsMap[(int)$ad['id']] = $ad;
        }

        foreach ($rawAddonIds as $adId) {
            $adId = (int)$adId;
            if (isset($availAddonsMap[$adId])) {
                $ad = $availAddonsMap[$adId];
                $adPrice = (float)$ad['price'];
                $calcPrice = $adPrice;
                if ($ad['price_type'] === 'per_night') {
                    $calcPrice = $adPrice * $nights;
                } elseif ($ad['price_type'] === 'per_guest') {
                    $calcPrice = $adPrice * $guests;
                }
                $addonCharges += $calcPrice;
                $selectedAddons[] = [
                    'id' => $ad['id'],
                    'name' => $ad['name'],
                    'price' => $calcPrice,
                    'price_type' => $ad['price_type']
                ];
            }
        }

        // Subtotal before coupon
        $subtotal = ($baseStayTotal - $longStayDiscount) + $cleaningFee + $addonCharges;

        // Promotional Coupon Discount (F74)
        $couponCode = strtoupper(trim($params['coupon_code'] ?? ''));
        $couponDiscount = 0.0;
        $couponValid = false;
        $couponMessage = '';

        if (!empty($couponCode)) {
            $coupon = $this->getCoupon($couponCode);
            if ($coupon) {
                if ($subtotal >= (float)$coupon['min_booking_amount']) {
                    if ($coupon['discount_type'] === 'percentage') {
                        $couponDiscount = round($subtotal * ((float)$coupon['discount_value'] / 100), 2);
                        if (!empty($coupon['max_discount_amount']) && $couponDiscount > (float)$coupon['max_discount_amount']) {
                            $couponDiscount = (float)$coupon['max_discount_amount'];
                        }
                    } else {
                        $couponDiscount = min($subtotal, (float)$coupon['discount_value']);
                    }
                    $couponValid = true;
                    $couponMessage = "Coupon '{$couponCode}' applied: ₹" . number_format($couponDiscount) . " savings!";
                } else {
                    $couponMessage = "Minimum booking amount of ₹" . number_format((float)$coupon['min_booking_amount']) . " required for code {$couponCode}.";
                }
            } else {
                $couponMessage = "Invalid or expired promo code.";
            }
        }

        // Platform commission architecture (F25 - Zero initial fee)
        $platformFeeRate = 0.0; // 0% initial
        $platformFee = round($subtotal * $platformFeeRate, 2);

        // Net total payable
        $totalPayable = max(0, $subtotal - $couponDiscount) + $securityDeposit;
        $ownerAmount  = max(0, ($subtotal - $couponDiscount) - $platformFee);

        return [
            'farmhouse_id'           => $farmhouseId,
            'booking_type'           => $bookingType,
            'rooms'                  => $rooms,
            'guests'                 => $guests,
            'check_in'               => $checkInStr,
            'check_out'              => $checkOutStr,
            'nights'                 => $nights,
            'weekday_nights'         => $weekdayNights,
            'weekend_nights'         => $weekendNights,
            'seasonal_nights'        => $seasonalNights,
            'weekday_price'          => $weekdayPrice,
            'weekend_price'          => $weekendPrice,
            'base_stay_total'        => round($baseStayTotal, 2),
            'avg_per_night'          => round($baseStayTotal / $nights, 2),
            'long_stay_discount'     => $longStayDiscount,
            'long_stay_label'        => $longStayLabel,
            'cleaning_fee'           => $cleaningFee,
            'security_deposit'       => $securityDeposit,
            'addon_charges'          => round($addonCharges, 2),
            'selected_addons'        => $selectedAddons,
            'coupon_code'            => $couponCode,
            'coupon_valid'           => $couponValid,
            'coupon_message'         => $couponMessage,
            'coupon_discount'        => $couponDiscount,
            'subtotal'               => round($subtotal, 2),
            'platform_fee'           => $platformFee,
            'owner_amount'           => round($ownerAmount, 2),
            'total_payable'          => round($totalPayable, 2),
            'daily_breakdown'        => $dailyBreakdown
        ];
    }

    /**
     * Get a specific room type by ID and farmhouse ID.
     */
    public function getRoomTypeById(int $roomTypeId, int $farmhouseId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM farmhouse_room_types WHERE id = ? AND farmhouse_id = ? AND status = 'active' LIMIT 1"
        );
        if (!$stmt) return null;
        $stmt->bind_param("ii", $roomTypeId, $farmhouseId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ?: null;
    }

    /**
     * Calculate remaining inventory of a specific room type for a date range.
     */
    public function getAvailableRoomInventory(int $farmhouseId, int $roomTypeId, string $checkIn, string $checkOut): int {
        $rt = $this->getRoomTypeById($roomTypeId, $farmhouseId);
        if (!$rt) return 0;
        $totalRooms = (int)$rt['total_rooms'];

        // Sum booked rooms overlapping the window for this specific room type
        $sql = "
            SELECT COALESCE(SUM(rooms), 0) AS booked_rooms
              FROM booking_requests
             WHERE farmhouse_id = ?
               AND room_type_id = ?
               AND status IN ('pending', 'approved', 'confirmed', 'completed')
               AND check_in < ?
               AND check_out > ?
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return $totalRooms;
        $stmt->bind_param("iiss", $farmhouseId, $roomTypeId, $checkOut, $checkIn);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $booked = (int)($row['booked_rooms'] ?? 0);

        return max(0, $totalRooms - $booked);
    }

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Insert a new booking request and return insert ID.
     */
    public function createBookingRequest(array $data): array|int|bool {
        $bookingType = in_array(strtolower($data['booking_type'] ?? ''), ['complete', 'per_room'], true)
            ? strtolower($data['booking_type'])
            : 'complete';
        $rooms        = max(1, (int)($data['rooms'] ?? 1));
        $message      = trim($data['message'] ?? '');
        $startDate    = $data['check_in'];
        $endDate      = $data['check_out'];
        $roomTypeId   = !empty($data['room_type_id']) ? (int)$data['room_type_id'] : null;
        $roomTypeName = !empty($data['room_type_name']) ? trim($data['room_type_name']) : null;
        $pricePerRoom = isset($data['price_per_room']) && $data['price_per_room'] !== null ? floatval($data['price_per_room']) : null;

        $weekendNights   = (int)($data['weekend_nights'] ?? 0);
        $securityDeposit = (float)($data['security_deposit'] ?? 0.0);
        $cleaningFee     = (float)($data['cleaning_fee'] ?? 0.0);
        $addonCharges    = (float)($data['addon_charges'] ?? 0.0);
        $addonsSelected  = !empty($data['addons_selected']) ? (is_string($data['addons_selected']) ? $data['addons_selected'] : json_encode($data['addons_selected'])) : null;
        $discountAmount  = (float)($data['discount_amount'] ?? 0.0);
        $couponCode      = !empty($data['coupon_code']) ? trim($data['coupon_code']) : null;
        $platformFee     = (float)($data['platform_fee'] ?? 0.0);
        $ownerAmount     = (float)($data['owner_amount'] ?? 0.0);

        // Concurrency Protection & Conflict Matrix (F32, F33)
        $this->releaseExpiredHolds();
        $this->db->begin_transaction();

        try {
            // Row-level lock FOR UPDATE on property to prevent race conditions during concurrent bookings
            $lockStmt = $this->db->prepare("SELECT id, rental_type, allow_room_booking, booking_mode, night_capacity, bedrooms FROM farmhouses WHERE id = ? FOR UPDATE");
            if (!$lockStmt) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Database lock failed: ' . $this->db->error];
            }
            $lockStmt->bind_param("i", $data['farmhouse_id']);
            $lockStmt->execute();
            $farmRow = $lockStmt->get_result()->fetch_assoc();
            $lockStmt->close();

            if (!$farmRow) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Property not found.'];
            }

            // Verify requested booking mode matches property rental_type
            $propRentalType = $farmRow['rental_type'] ?? (!empty($farmRow['allow_room_booking']) ? 'both' : 'complete');
            if ($bookingType === 'complete' && $propRentalType === 'room_wise') {
                $this->db->rollback();
                return ['success' => false, 'error' => 'This property only supports room-wise bookings. Please select a specific room type.'];
            }
            if ($bookingType === 'per_room' && $propRentalType === 'complete') {
                $this->db->rollback();
                return ['success' => false, 'error' => 'This property only supports complete property bookings.'];
            }

            // Conflict Check inside the atomic transaction
            $conflict = $this->checkBookingConflict(
                (int)$data['farmhouse_id'],
                $startDate,
                $endDate,
                $bookingType,
                $roomTypeId,
                $rooms
            );

            if ($conflict['conflict']) {
                $this->db->rollback();
                return ['success' => false, 'error' => $conflict['message']];
            }

            // Instant Booking Mode vs Request-to-Book Mode Toggle (F35)
            $isInstant = (!empty($farmRow['booking_mode']) && $farmRow['booking_mode'] === 'instant');
            $initialStatus = $isInstant ? 'approved' : 'pending';

            // 15-Minute Temporary Inventory Hold Timer (F34 - synced with MySQL database clock)
            $holdExpiresAt = null;
            if (!$isInstant) {
                $holdQ = $this->db->query("SELECT DATE_ADD(NOW(), INTERVAL 15 MINUTE) AS hold");
                $holdExpiresAt = $holdQ ? $holdQ->fetch_assoc()['hold'] : null;
            }

            $paymentMethod = !empty($data['payment_method']) ? strtolower(trim($data['payment_method'])) : 'upi';
            $paymentStatus = !empty($data['payment_status']) ? strtolower(trim($data['payment_status'])) : 'unpaid';
            $utrNumber     = !empty($data['utr_number']) ? trim($data['utr_number']) : null;
            $paymentProof  = !empty($data['payment_proof']) ? trim($data['payment_proof']) : null;

            $sql = "INSERT INTO booking_requests
                    (user_id, farmhouse_id, booking_type, room_type_id, room_type_name, price_per_room, 
                     check_in, check_in_time, check_out, check_out_time, guests, rooms, price,
                     weekend_nights, security_deposit, cleaning_fee, addon_charges, addons_selected, 
                     discount_amount, coupon_code, platform_fee, owner_amount,
                     message, start_date, end_date, status, hold_expires_at,
                     payment_method, payment_status, utr_number, payment_proof)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param(
                    "iisisdssssiididddsdsssdssssssss",
                    $data['user_id'],
                    $data['farmhouse_id'],
                    $bookingType,
                    $roomTypeId,
                    $roomTypeName,
                    $pricePerRoom,
                    $data['check_in'],
                    $data['check_in_time'],
                    $data['check_out'],
                    $data['check_out_time'],
                    $data['guests'],
                    $rooms,
                    $data['price'],
                    $weekendNights,
                    $securityDeposit,
                    $cleaningFee,
                    $addonCharges,
                    $addonsSelected,
                    $discountAmount,
                    $couponCode,
                    $platformFee,
                    $ownerAmount,
                    $message,
                    $startDate,
                    $endDate,
                    $initialStatus,
                    $holdExpiresAt,
                    $paymentMethod,
                    $paymentStatus,
                    $utrNumber,
                    $paymentProof
                );

                if ($stmt->execute()) {
                    $newId = $this->db->insert_id;
                    $stmt->close();

                    // Record initial payment ledger entry (F40)
                    $payStmt = $this->db->prepare("INSERT INTO payments (booking_id, farmhouse_id, user_id, amount, payment_method, utr_number, payment_proof, payment_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    if ($payStmt) {
                        $payStmt->bind_param(
                            "iiidssss",
                            $newId,
                            $data['farmhouse_id'],
                            $data['user_id'],
                            $data['price'],
                            $paymentMethod,
                            $utrNumber,
                            $paymentProof,
                            $paymentStatus
                        );
                        $payStmt->execute();
                        $payStmt->close();
                    }

                    $this->db->commit();
                    return [
                        'success'         => true,
                        'id'              => $newId,
                        'status'          => $initialStatus,
                        'is_instant'      => $isInstant,
                        'hold_expires_at' => $holdExpiresAt
                    ];
                } else {
                    $err = $stmt->error;
                    $stmt->close();
                    $this->db->rollback();
                    return ['success' => false, 'error' => 'Database insert failed: ' . $err];
                }
            } else {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Prepare failed: ' . $this->db->error];
            }
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Complete Property vs Room Booking Conflict Matrix (F32)
     */
    public function checkBookingConflict(int $farmhouseId, string $checkIn, string $checkOut, string $bookingType = 'complete', ?int $roomTypeId = null, int $requestedRooms = 1): array {
        $this->releaseExpiredHolds();

        // 1. Check admin / owner blocked dates
        if ($this->isBlockedForDates($farmhouseId, $checkIn, $checkOut)) {
            return ['conflict' => true, 'message' => 'The selected dates are blocked by the property owner/admin for maintenance or private events.'];
        }

        // 2. Check legacy bookings table (which are whole property bookings)
        $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM bookings WHERE farmhouse_id = ? AND status IN ('approved', 'active') AND start_date < ? AND end_date > ?");
        if ($stmt) {
            $stmt->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ((int)($row['cnt'] ?? 0) > 0) {
                return ['conflict' => true, 'message' => 'The property is already booked for these dates.'];
            }
        }

        // 3. Strict Conflict Matrix
        if ($bookingType === 'complete') {
            // Entire Farmhouse mode: Cannot book if ANY booking exists (whole estate OR individual rooms)
            $sql = "SELECT COUNT(*) AS cnt 
                    FROM booking_requests 
                    WHERE farmhouse_id = ? 
                      AND (status IN ('approved', 'completed') OR (status = 'pending' AND (hold_expires_at IS NULL OR hold_expires_at > NOW())))
                      AND check_in < ? 
                      AND check_out > ?";
            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ((int)($row['cnt'] ?? 0) > 0) {
                    return ['conflict' => true, 'message' => 'Entire property cannot be booked because one or more rooms (or the full stay) are already booked or held on these dates.'];
                }
            }
        } else {
            // Per Room mode:
            // A) Cannot book if the entire property is booked
            $sql = "SELECT COUNT(*) AS cnt 
                    FROM booking_requests 
                    WHERE farmhouse_id = ? 
                      AND booking_type = 'complete'
                      AND (status IN ('approved', 'completed') OR (status = 'pending' AND (hold_expires_at IS NULL OR hold_expires_at > NOW())))
                      AND check_in < ? 
                      AND check_out > ?";
            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ((int)($row['cnt'] ?? 0) > 0) {
                    return ['conflict' => true, 'message' => 'The entire estate is already reserved by another guest for these dates.'];
                }
            }

            // B) Check specific room type inventory limits
            if ($roomTypeId) {
                $rtStmt = $this->db->prepare("SELECT total_rooms, room_type_name FROM farmhouse_room_types WHERE id = ? AND farmhouse_id = ?");
                if ($rtStmt) {
                    $rtStmt->bind_param("ii", $roomTypeId, $farmhouseId);
                    $rtStmt->execute();
                    $rtInfo = $rtStmt->get_result()->fetch_assoc();
                    $rtStmt->close();

                    $totalUnits = (int)($rtInfo['total_rooms'] ?? 1);
                    $rtName = $rtInfo['room_type_name'] ?? 'Selected Room';

                    $bookedStmt = $this->db->prepare(
                        "SELECT COALESCE(SUM(rooms), 0) AS booked_rooms 
                         FROM booking_requests 
                         WHERE farmhouse_id = ? 
                           AND room_type_id = ?
                           AND (status IN ('approved', 'completed') OR (status = 'pending' AND (hold_expires_at IS NULL OR hold_expires_at > NOW())))
                           AND check_in < ? 
                           AND check_out > ?"
                    );
                    if ($bookedStmt) {
                        $bookedStmt->bind_param("iiss", $farmhouseId, $roomTypeId, $checkOut, $checkIn);
                        $bookedStmt->execute();
                        $bookedRow = $bookedStmt->get_result()->fetch_assoc();
                        $bookedStmt->close();

                        $alreadyBooked = (int)($bookedRow['booked_rooms'] ?? 0);
                        $available = max(0, $totalUnits - $alreadyBooked);

                        if ($requestedRooms > $available) {
                            return [
                                'conflict' => true, 
                                'message' => "Only {$available} unit(s) of '{$rtName}' available for these dates (you requested {$requestedRooms})."
                            ];
                        }
                    }
                }
            }
        }

        return ['conflict' => false, 'message' => 'Dates are available'];
    }

    /**
     * Room-Wise Granular Inventory Availability Check (F29)
     */
    public function getRoomWiseAvailability(int $farmhouseId, string $checkIn, string $checkOut): array {
        $this->releaseExpiredHolds();

        $isWholeBlocked = $this->isBlockedForDates($farmhouseId, $checkIn, $checkOut);

        // Check if Entire Farmhouse is booked
        $isWholeBooked = false;
        $stmt1 = $this->db->prepare("SELECT COUNT(*) AS cnt FROM bookings WHERE farmhouse_id = ? AND status IN ('approved', 'active') AND start_date < ? AND end_date > ?");
        if ($stmt1) {
            $stmt1->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
            $stmt1->execute();
            $isWholeBooked = ((int)($stmt1->get_result()->fetch_assoc()['cnt'] ?? 0) > 0);
            $stmt1->close();
        }
        if (!$isWholeBooked) {
            $stmt2 = $this->db->prepare("SELECT COUNT(*) AS cnt FROM booking_requests WHERE farmhouse_id = ? AND booking_type = 'complete' AND (status IN ('approved', 'completed') OR (status = 'pending' AND (hold_expires_at IS NULL OR hold_expires_at > NOW()))) AND check_in < ? AND check_out > ?");
            if ($stmt2) {
                $stmt2->bind_param("iss", $farmhouseId, $checkOut, $checkIn);
                $stmt2->execute();
                $isWholeBooked = ((int)($stmt2->get_result()->fetch_assoc()['cnt'] ?? 0) > 0);
                $stmt2->close();
            }
        }

        $roomTypes = $this->getFarmhouseRoomTypes($farmhouseId);
        $inventory = [];

        foreach ($roomTypes as $rt) {
            $rtId = (int)$rt['id'];
            $totalUnits = (int)($rt['total_rooms'] ?? 1);

            if ($isWholeBlocked || $isWholeBooked) {
                $bookedUnits = $totalUnits;
                $availableUnits = 0;
                $status = $isWholeBlocked ? 'blocked' : 'sold_out';
            } else {
                $bStmt = $this->db->prepare(
                    "SELECT COALESCE(SUM(rooms), 0) AS booked_rooms 
                     FROM booking_requests 
                     WHERE farmhouse_id = ? 
                       AND room_type_id = ?
                       AND (status IN ('approved', 'completed') OR (status = 'pending' AND (hold_expires_at IS NULL OR hold_expires_at > NOW())))
                       AND check_in < ? 
                       AND check_out > ?"
                );
                $bookedUnits = 0;
                if ($bStmt) {
                    $bStmt->bind_param("iiss", $farmhouseId, $rtId, $checkOut, $checkIn);
                    $bStmt->execute();
                    $bRow = $bStmt->get_result()->fetch_assoc();
                    $bStmt->close();
                    $bookedUnits = (int)($bRow['booked_rooms'] ?? 0);
                }
                $availableUnits = max(0, $totalUnits - $bookedUnits);
                if ($availableUnits === 0) {
                    $status = 'sold_out';
                } elseif ($availableUnits <= 1) {
                    $status = 'limited';
                } else {
                    $status = 'available';
                }
            }

            $inventory[] = [
                'room_type_id'     => $rtId,
                'room_type_name'   => $rt['room_type_name'],
                'total_units'      => $totalUnits,
                'booked_units'     => $bookedUnits,
                'available_units'  => $availableUnits,
                'capacity'         => (int)$rt['capacity_per_room'],
                'weekday_price'    => (float)$rt['price_per_room'],
                'weekend_price'    => (float)$rt['weekend_price'],
                'status'           => $status
            ];
        }

        return [
            'check_in'           => $checkIn,
            'check_out'          => $checkOut,
            'is_whole_blocked'   => $isWholeBlocked,
            'is_whole_booked'    => $isWholeBooked,
            'room_inventory'     => $inventory
        ];
    }

    /**
     * Visual Room-by-Room Calendar Availability Matrix Grid (F29)
     */
    public function getRoomAvailabilityCalendarMatrix(int $farmhouseId, int $year, int $month): array {
        $this->releaseExpiredHolds();

        $numDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $roomTypes = $this->getFarmhouseRoomTypes($farmhouseId);

        $matrix = [];
        for ($day = 1; $day <= $numDays; $day++) {
            $curDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $nextDate = date('Y-m-d', strtotime($curDate . ' +1 day'));

            $dayAvail = $this->getRoomWiseAvailability($farmhouseId, $curDate, $nextDate);
            $matrix[$curDate] = [
                'date'             => $curDate,
                'day'              => $day,
                'is_whole_booked'  => $dayAvail['is_whole_booked'],
                'is_whole_blocked' => $dayAvail['is_whole_blocked'],
                'rooms'            => $dayAvail['room_inventory']
            ];
        }

        return [
            'year'       => $year,
            'month'      => $month,
            'room_types' => $roomTypes,
            'days'       => $matrix
        ];
    }

    /**
     * Fetch full booking request by ID with user and stay dossier for vouchers/invoices (F73)
     */
    public function getBookingRequestById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT br.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
                    f.title AS farmhouse_title, f.location AS farmhouse_location, f.address AS farmhouse_address,
                    f.google_map_link, f.primary_phone, f.bedrooms, f.bedroom_capacity
             FROM booking_requests br
             LEFT JOIN users u ON br.user_id = u.id
             LEFT JOIN farmhouses f ON br.farmhouse_id = f.id
             WHERE br.id = ? LIMIT 1"
        );
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Log a call or WhatsApp inquiry
     */
    public function logInquiry(int $userId, int $farmhouseId, string $type): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO inquiries (user_id, farmhouse_id, type) VALUES (?, ?, ?)"
        );
        if (!$stmt) return false;

        $stmt->bind_param("iis", $userId, $farmhouseId, $type);
        return $stmt->execute();
    }
}