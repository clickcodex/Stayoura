<?php
namespace App\Models\Admin;

use App\Config\Database;
use mysqli;
use Exception;

/**
 * ReviewManagementModel: Complete Data Access & Business Logic for Guest Review Management
 */
class ReviewManagementModel {
    protected mysqli $db;

    public function __construct(?mysqli $db = null) {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Get aggregate KPI statistics for the Review Management Dashboard
     */
    public function getStats(?int $farmhouseId = null, ?int $ownerId = null): array {
        $stats = [
            'total_reviews'       => 0,
            'approved_reviews'    => 0, // Published
            'pending_reviews'     => 0, // Awaiting moderation
            'rejected_reviews'    => 0,
            'hidden_reviews'      => 0,
            'flagged_reviews'     => 0,
            'average_rating'      => 0.0,
            'verified_stays'      => 0,
            'with_responses'      => 0,
            'recent_30_days'      => 0,
            'star_counts'         => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
            'star_percentages'    => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
            'avg_cleanliness'     => 0.0,
            'avg_location'        => 0.0,
            'avg_value'           => 0.0,
            'avg_hospitality'     => 0.0,
        ];

        $whereClause = "WHERE 1=1";
        $params = [];
        $types = "";

        if ($farmhouseId) {
            $whereClause .= " AND r.farmhouse_id = ?";
            $params[] = $farmhouseId;
            $types .= "i";
        } elseif ($ownerId) {
            $whereClause .= " AND f.owner_id = ?";
            $params[] = $ownerId;
            $types .= "i";
        }

        // Base counts by status
        $sql = "SELECT 
            COUNT(r.id) as total_count,
            SUM(CASE WHEN r.status = 'approved' THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN r.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN r.status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
            SUM(CASE WHEN r.status = 'hidden' THEN 1 ELSE 0 END) as hidden_count,
            SUM(CASE WHEN r.status = 'flagged' THEN 1 ELSE 0 END) as flagged_count,
            SUM(CASE WHEN r.is_verified_stay = 1 THEN 1 ELSE 0 END) as verified_count,
            SUM(CASE WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as recent_count,
            AVG(CASE WHEN r.status = 'approved' THEN r.overall_rating ELSE NULL END) as avg_rating,
            AVG(CASE WHEN r.status = 'approved' THEN r.cleanliness_rating ELSE NULL END) as avg_cleanliness,
            AVG(CASE WHEN r.status = 'approved' THEN r.location_rating ELSE NULL END) as avg_location,
            AVG(CASE WHEN r.status = 'approved' THEN r.value_rating ELSE NULL END) as avg_value,
            AVG(CASE WHEN r.status = 'approved' THEN r.hospitality_rating ELSE NULL END) as avg_hospitality
        FROM reviews r
        LEFT JOIN farmhouses f ON r.farmhouse_id = f.id
        {$whereClause}";

        $stmt = $this->db->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($row) {
                $stats['total_reviews']    = (int)($row['total_count'] ?? 0);
                $stats['approved_reviews'] = (int)($row['approved_count'] ?? 0);
                $stats['pending_reviews']  = (int)($row['pending_count'] ?? 0);
                $stats['rejected_reviews'] = (int)($row['rejected_count'] ?? 0);
                $stats['hidden_reviews']   = (int)($row['hidden_count'] ?? 0);
                $stats['flagged_reviews']  = (int)($row['flagged_count'] ?? 0);
                $stats['verified_stays']   = (int)($row['verified_count'] ?? 0);
                $stats['recent_30_days']   = (int)($row['recent_count'] ?? 0);

                $stats['average_rating']   = $row['avg_rating'] !== null ? round((float)$row['avg_rating'], 2) : 0.0;
                $stats['avg_cleanliness']  = $row['avg_cleanliness'] !== null ? round((float)$row['avg_cleanliness'], 1) : 0.0;
                $stats['avg_location']     = $row['avg_location'] !== null ? round((float)$row['avg_location'], 1) : 0.0;
                $stats['avg_value']        = $row['avg_value'] !== null ? round((float)$row['avg_value'], 1) : 0.0;
                $stats['avg_hospitality']  = $row['avg_hospitality'] !== null ? round((float)$row['avg_hospitality'], 1) : 0.0;
            }
        }

        // Star breakdown from approved/published reviews
        $starSql = "SELECT 
            FLOOR(r.overall_rating) as star,
            COUNT(*) as count
        FROM reviews r
        LEFT JOIN farmhouses f ON r.farmhouse_id = f.id
        {$whereClause} AND r.status = 'approved'
        GROUP BY FLOOR(r.overall_rating)";

        $sStmt = $this->db->prepare($starSql);
        if ($sStmt) {
            if (!empty($params)) {
                $sStmt->bind_param($types, ...$params);
            }
            $sStmt->execute();
            $res = $sStmt->get_result();
            while ($sRow = $res->fetch_assoc()) {
                $s = (int)$sRow['star'];
                if ($s >= 1 && $s <= 5) {
                    $stats['star_counts'][$s] = (int)$sRow['count'];
                }
            }
            $sStmt->close();
        }

        // Calculate star percentages
        $approvedTotal = $stats['approved_reviews'];
        if ($approvedTotal > 0) {
            foreach ($stats['star_counts'] as $star => $count) {
                $stats['star_percentages'][$star] = round(($count / $approvedTotal) * 100, 1);
            }
        }

        // Reviews with responses count
        $respSql = "SELECT COUNT(DISTINCT r.id) as with_responses 
            FROM reviews r 
            INNER JOIN review_responses resp ON r.id = resp.review_id 
            LEFT JOIN farmhouses f ON r.farmhouse_id = f.id
            {$whereClause} AND resp.status = 'published'";
        
        $rStmt = $this->db->prepare($respSql);
        if ($rStmt) {
            if (!empty($params)) {
                $rStmt->bind_param($types, ...$params);
            }
            $rStmt->execute();
            $rRow = $rStmt->get_result()->fetch_assoc();
            $stats['with_responses'] = (int)($rRow['with_responses'] ?? 0);
            $rStmt->close();
        }

        return $stats;
    }

    /**
     * Get paginated reviews with rich filtering, search, and sorting
     */
    public function getReviews(array $filters = [], int $page = 1, int $perPage = 15): array {
        $where = ["1=1"];
        $params = [];
        $types = "";

        // Status Filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        // Search Filter (Guest name, email, property title, review text, review title, booking ref)
        if (!empty($filters['search'])) {
            $s = "%" . trim($filters['search']) . "%";
            $where[] = "(
                r.guest_name LIKE ? OR 
                r.guest_email LIKE ? OR 
                f.title LIKE ? OR 
                r.review_title LIKE ? OR 
                r.review_text LIKE ? OR 
                b.booking_reference LIKE ? OR 
                CAST(r.id AS CHAR) = ?
            )";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = trim($filters['search']);
            $types .= "sssssss";
        }

        // Property Filter
        if (!empty($filters['farmhouse_id'])) {
            $where[] = "r.farmhouse_id = ?";
            $params[] = (int)$filters['farmhouse_id'];
            $types .= "i";
        }

        // Owner Filter
        if (!empty($filters['owner_id'])) {
            $where[] = "f.owner_id = ?";
            $params[] = (int)$filters['owner_id'];
            $types .= "i";
        }

        // Star Rating Filter
        if (!empty($filters['rating']) && is_numeric($filters['rating'])) {
            $rVal = (float)$filters['rating'];
            $where[] = "FLOOR(r.overall_rating) = ?";
            $params[] = (int)$rVal;
            $types .= "i";
        }

        // Verified Stay Filter
        if (isset($filters['verified']) && $filters['verified'] !== '') {
            $where[] = "r.is_verified_stay = ?";
            $params[] = (int)$filters['verified'];
            $types .= "i";
        }

        // Date Range
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(r.created_at) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(r.created_at) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        // Has Response Filter
        if (isset($filters['has_response']) && $filters['has_response'] !== '') {
            if ($filters['has_response'] == '1') {
                $where[] = "EXISTS (SELECT 1 FROM review_responses resp WHERE resp.review_id = r.id AND resp.status = 'published')";
            } elseif ($filters['has_response'] == '0') {
                $where[] = "NOT EXISTS (SELECT 1 FROM review_responses resp WHERE resp.review_id = r.id AND resp.status = 'published')";
            }
        }

        $whereClause = implode(" AND ", $where);

        // Sorting
        $sortOption = $filters['sort'] ?? 'newest';
        $orderBy = match ($sortOption) {
            'oldest'        => "r.created_at ASC",
            'rating_high'   => "r.overall_rating DESC, r.created_at DESC",
            'rating_low'    => "r.overall_rating ASC, r.created_at DESC",
            'pending_first' => "CASE WHEN r.status = 'pending' THEN 0 ELSE 1 END, r.created_at DESC",
            default         => "r.created_at DESC"
        };

        // 1. Get total record count
        $countSql = "SELECT COUNT(r.id) as total
            FROM reviews r
            LEFT JOIN farmhouses f ON r.farmhouse_id = f.id
            LEFT JOIN booking_requests b ON r.booking_id = b.id
            LEFT JOIN users u ON r.user_id = u.id
            WHERE {$whereClause}";

        $cStmt = $this->db->prepare($countSql);
        $totalRecords = 0;
        if ($cStmt) {
            if (!empty($params)) {
                $cStmt->bind_param($types, ...$params);
            }
            $cStmt->execute();
            $totalRecords = (int)($cStmt->get_result()->fetch_assoc()['total'] ?? 0);
            $cStmt->close();
        }

        $totalPages = $perPage > 0 ? (int)ceil($totalRecords / $perPage) : 1;
        $page = max(1, min($page, max(1, $totalPages)));
        $offset = ($page - 1) * $perPage;

        // 2. Fetch records
        $sql = "SELECT 
            r.*,
            f.title as property_title,
            f.location as property_location,
            (SELECT image_url FROM images WHERE farmhouse_id = f.id ORDER BY is_featured DESC, id ASC LIMIT 1) as property_image,
            o.id as owner_id,
            o.name as owner_name,
            u.id as registered_user_id,
            u.name as registered_user_name,
            u.email as registered_user_email,
            u.membership_tier,
            u.kyc_status,
            u.avatar_url,
            u.profile_image,
            b.id as booking_request_id,
            b.booking_reference,
            b.check_in,
            b.check_out,
            b.status as booking_status,
            b.guests as booking_guests,
            (SELECT COUNT(*) FROM review_responses resp WHERE resp.review_id = r.id) as response_count,
            (SELECT response_text FROM review_responses resp WHERE resp.review_id = r.id ORDER BY resp.id DESC LIMIT 1) as latest_response_text,
            (SELECT responder_name FROM review_responses resp WHERE resp.review_id = r.id ORDER BY resp.id DESC LIMIT 1) as latest_responder_name,
            (SELECT created_at FROM review_responses resp WHERE resp.review_id = r.id ORDER BY resp.id DESC LIMIT 1) as latest_response_at
        FROM reviews r
        LEFT JOIN farmhouses f ON r.farmhouse_id = f.id
        LEFT JOIN owners o ON f.owner_id = o.id
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN booking_requests b ON r.booking_id = b.id
        WHERE {$whereClause}
        ORDER BY {$orderBy}
        LIMIT ? OFFSET ?";

        $pParams = $params;
        $pTypes = $types . "ii";
        $pParams[] = $perPage;
        $pParams[] = $offset;

        $stmt = $this->db->prepare($sql);
        $data = [];
        if ($stmt) {
            $stmt->bind_param($pTypes, ...$pParams);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        return [
            'data'        => $data,
            'total'       => $totalRecords,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * Get complete 360-degree review details including linked entities, responses, and audit logs
     */
    public function getReviewDetails(int $id): ?array {
        $sql = "SELECT 
            r.*,
            f.id as property_id,
            f.title as property_title,
            f.location as property_location,
            f.address as property_address,
            (SELECT image_url FROM images WHERE farmhouse_id = f.id ORDER BY is_featured DESC, id ASC LIMIT 1) as property_image,
            f.owner_id,
            o.name as owner_name,
            o.phone as owner_phone,
            o.email as owner_email,
            u.id as user_profile_id,
            u.name as user_name,
            u.email as user_email,
            u.phone as user_phone,
            u.membership_tier,
            u.kyc_status,
            u.avatar_url,
            u.profile_image,
            b.id as booking_id_ref,
            b.booking_reference,
            b.check_in,
            b.check_out,
            b.booking_type,
            b.guests,
            b.price as booking_price,
            b.status as booking_status,
            b.payment_status as booking_payment_status,
            admin_u.name as moderator_name
        FROM reviews r
        LEFT JOIN farmhouses f ON r.farmhouse_id = f.id
        LEFT JOIN owners o ON f.owner_id = o.id
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN booking_requests b ON r.booking_id = b.id
        LEFT JOIN users admin_u ON r.moderated_by = admin_u.id
        WHERE r.id = ?
        LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $review = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$review) return null;

        // Fetch host / staff responses
        $review['responses'] = $this->getResponsesForReview($id);

        // Fetch moderation audit logs
        $review['audit_logs'] = $this->getAuditLogsForReview($id);

        return $review;
    }

    /**
     * Update review moderation status with audit trail
     */
    public function updateStatus(
        int $id,
        string $status,
        ?string $rejectionReason = null,
        ?string $adminNotes = null,
        int $moderatorId = 1,
        string $moderatorName = 'Admin',
        string $ip = '127.0.0.1'
    ): bool {
        $validStatuses = ['pending', 'approved', 'rejected', 'hidden', 'flagged'];
        if (!in_array($status, $validStatuses, true)) {
            return false;
        }

        // Fetch current status
        $curStmt = $this->db->prepare("SELECT status, admin_notes, rejection_reason FROM reviews WHERE id = ?");
        if (!$curStmt) return false;
        $curStmt->bind_param("i", $id);
        $curStmt->execute();
        $cur = $curStmt->get_result()->fetch_assoc();
        $curStmt->close();

        if (!$cur) return false;
        $oldStatus = $cur['status'];

        $this->db->begin_transaction();

        try {
            $notes = $adminNotes !== null ? $adminNotes : $cur['admin_notes'];
            $reason = ($status === 'rejected') ? ($rejectionReason ?? $cur['rejection_reason']) : null;

            $stmt = $this->db->prepare("UPDATE reviews SET 
                status = ?, 
                rejection_reason = ?, 
                admin_notes = ?, 
                moderated_by = ?, 
                moderated_at = NOW(),
                updated_at = NOW()
                WHERE id = ?");

            if (!$stmt) throw new Exception("Failed to prepare review status update statement.");

            $stmt->bind_param("sssii", $status, $reason, $notes, $moderatorId, $id);
            $stmt->execute();
            $stmt->close();

            // Insert audit log
            $action = match ($status) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                'hidden'   => 'hidden',
                'flagged'  => 'flagged',
                default    => 'status_changed'
            };

            $summary = "Status changed from '{$oldStatus}' to '{$status}'.";
            if ($status === 'rejected' && !empty($reason)) {
                $summary .= " Reason: " . $reason;
            }
            if (!empty($notes) && $notes !== $cur['admin_notes']) {
                $summary .= " Notes updated.";
            }

            $this->addAuditLog($id, $action, $oldStatus, $status, $summary, $moderatorId, $moderatorName, $ip);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Edit review content with full audit trail
     */
    public function updateContent(
        int $id,
        string $reviewTitle,
        string $reviewText,
        ?string $adminNotes = null,
        int $moderatorId = 1,
        string $moderatorName = 'Admin',
        string $ip = '127.0.0.1'
    ): bool {
        $curStmt = $this->db->prepare("SELECT review_title, review_text, admin_notes, status FROM reviews WHERE id = ?");
        if (!$curStmt) return false;
        $curStmt->bind_param("i", $id);
        $curStmt->execute();
        $cur = $curStmt->get_result()->fetch_assoc();
        $curStmt->close();

        if (!$cur) return false;

        $this->db->begin_transaction();
        try {
            $notes = $adminNotes !== null ? $adminNotes : $cur['admin_notes'];

            $stmt = $this->db->prepare("UPDATE reviews SET 
                review_title = ?, 
                review_text = ?, 
                admin_notes = ?, 
                moderated_by = ?, 
                updated_at = NOW()
                WHERE id = ?");

            if (!$stmt) throw new Exception("Failed to prepare content edit statement.");

            $stmt->bind_param("sssii", $reviewTitle, $reviewText, $notes, $moderatorId, $id);
            $stmt->execute();
            $stmt->close();

            // Log change diff in audit log
            $diffNotes = [];
            if ($cur['review_title'] !== $reviewTitle) {
                $diffNotes[] = "Title updated: '{$cur['review_title']}' -> '{$reviewTitle}'";
            }
            if ($cur['review_text'] !== $reviewText) {
                $diffNotes[] = "Review text edited (length {$cur['review_text']} -> " . strlen($reviewText) . " chars)";
            }
            $summary = !empty($diffNotes) ? implode("; ", $diffNotes) : "Review content edited by admin.";

            $this->addAuditLog($id, 'content_edited', $cur['status'], $cur['status'], $summary, $moderatorId, $moderatorName, $ip);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Perform bulk moderation action on multiple reviews
     */
    public function bulkUpdateStatus(
        array $ids,
        string $status,
        ?string $reason = null,
        int $moderatorId = 1,
        string $moderatorName = 'Admin',
        string $ip = '127.0.0.1'
    ): int {
        $successCount = 0;
        foreach ($ids as $id) {
            $rId = (int)$id;
            if ($rId > 0) {
                if ($this->updateStatus($rId, $status, $reason, null, $moderatorId, $moderatorName, $ip)) {
                    $successCount++;
                }
            }
        }
        return $successCount;
    }

    /**
     * Permanently delete a review (or mark hidden if compliance retains)
     */
    public function deleteReview(int $id, int $moderatorId = 1, string $moderatorName = 'Admin', string $ip = '127.0.0.1'): bool {
        $this->db->begin_transaction();
        try {
            // Delete associated responses and audit logs
            $this->db->query("DELETE FROM review_responses WHERE review_id = {$id}");
            $this->db->query("DELETE FROM review_audit_logs WHERE review_id = {$id}");
            $delStmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
            if ($delStmt) {
                $delStmt->bind_param("i", $id);
                $delStmt->execute();
                $delStmt->close();
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // HOST / MANAGEMENT RESPONSES
    // ─────────────────────────────────────────────────────────────

    /**
     * Add a host/management response to a review
     */
    public function addResponse(
        int $reviewId,
        string $responderType,
        ?int $responderId,
        string $responderName,
        string $responseText,
        string $status = 'published',
        int $moderatorId = 1,
        string $moderatorName = 'Admin',
        string $ip = '127.0.0.1'
    ): bool {
        $stmt = $this->db->prepare("INSERT INTO review_responses 
            (review_id, responder_type, responder_id, responder_name, response_text, status, moderated_by, moderated_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

        if (!$stmt) return false;

        $stmt->bind_param("isisssi", $reviewId, $responderType, $responderId, $responderName, $responseText, $status, $moderatorId);
        $res = $stmt->execute();
        $stmt->close();

        if ($res) {
            $this->addAuditLog(
                $reviewId,
                'response_added',
                null,
                null,
                "{$responderType} response added by {$responderName}: " . substr($responseText, 0, 100) . "...",
                $moderatorId,
                $moderatorName,
                $ip
            );
        }

        return $res;
    }

    /**
     * Update an existing response
     */
    public function updateResponse(int $responseId, string $responseText, string $status = 'published'): bool {
        $stmt = $this->db->prepare("UPDATE review_responses SET response_text = ?, status = ?, updated_at = NOW() WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ssi", $responseText, $status, $responseId);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Delete a response
     */
    public function deleteResponse(int $responseId): bool {
        $stmt = $this->db->prepare("DELETE FROM review_responses WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $responseId);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Get all responses for a given review
     */
    public function getResponsesForReview(int $reviewId, bool $publishedOnly = false): array {
        $sql = "SELECT * FROM review_responses WHERE review_id = ?";
        if ($publishedOnly) {
            $sql .= " AND status = 'published'";
        }
        $sql .= " ORDER BY created_at ASC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $reviewId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // ─────────────────────────────────────────────────────────────
    // AUDIT LOGGING
    // ─────────────────────────────────────────────────────────────

    /**
     * Insert record into review_audit_logs
     */
    public function addAuditLog(
        int $reviewId,
        string $action,
        ?string $oldStatus,
        ?string $newStatus,
        ?string $changeSummary,
        ?int $performedBy = 1,
        ?string $performedByName = 'Admin',
        ?string $ip = '127.0.0.1'
    ): bool {
        $stmt = $this->db->prepare("INSERT INTO review_audit_logs 
            (review_id, action, old_status, new_status, change_summary, performed_by, performed_by_name, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) return false;

        $stmt->bind_param("isssisss", $reviewId, $action, $oldStatus, $newStatus, $changeSummary, $performedBy, $performedByName, $ip);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Fetch all audit logs for a review
     */
    public function getAuditLogsForReview(int $reviewId): array {
        $stmt = $this->db->prepare("SELECT * FROM review_audit_logs WHERE review_id = ? ORDER BY created_at DESC");
        if (!$stmt) return [];
        $stmt->bind_param("i", $reviewId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // ─────────────────────────────────────────────────────────────
    // VERIFIED STAY ELIGIBILITY & DUPLICATE PREVENTION
    // ─────────────────────────────────────────────────────────────

    /**
     * Validate whether a guest is eligible to submit a verified review for a farmhouse
     */
    public function validateReviewEligibility(int $userId, int $farmhouseId, ?int $bookingId = null): array {
        // If bookingId provided, strictly check ownership and completion
        if ($bookingId) {
            $stmt = $this->db->prepare("SELECT id, user_id, farmhouse_id, status, check_in, check_out 
                FROM booking_requests 
                WHERE id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("i", $bookingId);
                $stmt->execute();
                $b = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$b) {
                    return ['eligible' => false, 'is_verified' => false, 'reason' => 'Booking reference does not exist.'];
                }

                if ((int)$b['farmhouse_id'] !== $farmhouseId) {
                    return ['eligible' => false, 'is_verified' => false, 'reason' => 'Booking reference does not match this property.'];
                }

                if ((int)$b['user_id'] !== $userId) {
                    return ['eligible' => false, 'is_verified' => false, 'reason' => 'Booking belongs to a different guest account.'];
                }

                $validStayStatuses = ['checked_out', 'completed', 'confirmed'];
                if (!in_array(strtolower($b['status']), $validStayStatuses)) {
                    return ['eligible' => false, 'is_verified' => false, 'reason' => 'Reviews can only be submitted after your stay is confirmed or completed.'];
                }

                // Check for duplicate review for this booking
                if ($this->checkDuplicateReview($farmhouseId, $bookingId, $userId)) {
                    return ['eligible' => false, 'is_verified' => false, 'reason' => 'A review has already been submitted for this stay.'];
                }

                return ['eligible' => true, 'is_verified' => true, 'booking_id' => $bookingId, 'reason' => 'Verified stay eligible.'];
            }
        }

        // If no bookingId provided, look for any completed stay by this user for this farmhouse
        $stmt = $this->db->prepare("SELECT id FROM booking_requests 
            WHERE user_id = ? AND farmhouse_id = ? AND status IN ('checked_out', 'completed')
            ORDER BY check_out DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("ii", $userId, $farmhouseId);
            $stmt->execute();
            $b = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($b) {
                $matchedBookingId = (int)$b['id'];
                if (!$this->checkDuplicateReview($farmhouseId, $matchedBookingId, $userId)) {
                    return ['eligible' => true, 'is_verified' => true, 'booking_id' => $matchedBookingId, 'reason' => 'Matched verified completed stay.'];
                }
            }
        }

        // Standard guest feedback (non-verified)
        return ['eligible' => true, 'is_verified' => false, 'booking_id' => null, 'reason' => 'General guest feedback.'];
    }

    /**
     * Check if a duplicate review exists for a booking or user+property
     */
    public function checkDuplicateReview(int $farmhouseId, ?int $bookingId = null, ?int $userId = null): bool {
        if ($bookingId) {
            $stmt = $this->db->prepare("SELECT id FROM reviews WHERE booking_id = ? AND status != 'rejected' LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("i", $bookingId);
                $stmt->execute();
                $exists = ($stmt->get_result()->num_rows > 0);
                $stmt->close();
                if ($exists) return true;
            }
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────
    // CROSS-MODULE INTEGRATION HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Get reviews submitted by a specific guest
     */
    public function getGuestReviews(int $userId): array {
        $sql = "SELECT r.*, f.title as property_title, f.location as property_location,
            (SELECT image_url FROM images WHERE farmhouse_id = f.id ORDER BY is_featured DESC, id ASC LIMIT 1) as property_image,
            (SELECT COUNT(*) FROM review_responses resp WHERE resp.review_id = r.id AND resp.status = 'published') as response_count
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
     * Get reviews for a specific property
     */
    public function getPropertyReviews(int $farmhouseId, ?string $status = null): array {
        $sql = "SELECT r.*, u.membership_tier, u.avatar_url, u.profile_image 
            FROM reviews r 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE r.farmhouse_id = ?";
        if ($status) {
            $sql .= " AND r.status = '{$status}'";
        }
        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Get reviews for properties belonging to a specific owner
     */
    public function getOwnerReviews(int $ownerId, ?string $status = null): array {
        $sql = "SELECT r.*, f.title as property_title, f.location as property_location,
            (SELECT image_url FROM images WHERE farmhouse_id = f.id ORDER BY is_featured DESC, id ASC LIMIT 1) as property_image,
            (SELECT COUNT(*) FROM review_responses resp WHERE resp.review_id = r.id AND resp.status = 'published') as response_count
            FROM reviews r
            INNER JOIN farmhouses f ON r.farmhouse_id = f.id
            WHERE f.owner_id = ?";
        if ($status) {
            $sql .= " AND r.status = '{$status}'";
        }
        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $ownerId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Fetch all farmhouses for dropdown filter
     */
    public function getPropertiesDropdown(): array {
        $res = $this->db->query("SELECT id, title, location FROM farmhouses WHERE status = 'active' ORDER BY title ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Export reviews to array for CSV generation
     */
    public function getExportData(array $filters = []): array {
        $res = $this->getReviews($filters, 1, 10000);
        return $res['data'] ?? [];
    }
}
