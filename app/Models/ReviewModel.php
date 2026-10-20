<?php
namespace App\Models;

use mysqli;

class ReviewModel {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Submit a customer review
     */
    public function submitReview(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO reviews (
                farmhouse_id, user_id, booking_id, guest_name, guest_email,
                overall_rating, cleanliness_rating, location_rating, value_rating, hospitality_rating,
                review_title, review_text, is_verified_stay, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        if (!$stmt) return false;

        $overall = ($data['cleanliness'] + $data['location'] + $data['value'] + $data['hospitality']) / 4.0;
        $isVerified = !empty($data['is_verified']) ? 1 : 0;

        $stmt->bind_param(
            "iiissdiiiissi",
            $data['farmhouse_id'],
            $data['user_id'],
            $data['booking_id'],
            $data['guest_name'],
            $data['guest_email'],
            $overall,
            $data['cleanliness'],
            $data['location'],
            $data['value'],
            $data['hospitality'],
            $data['review_title'],
            $data['review_text'],
            $isVerified
        );

        return $stmt->execute();
    }

    /**
     * Get approved reviews for a specific farmhouse with any host responses
     */
    public function getApprovedReviews(int $farmhouseId): array {
        $stmt = $this->db->prepare("
            SELECT r.*,
                   u.membership_tier,
                   u.avatar_url,
                   u.profile_image
            FROM reviews r 
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.farmhouse_id = ? AND r.status = 'approved'
            ORDER BY r.created_at DESC
        ");
        if (!$stmt) return [];
        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Attach published host responses
        if (!empty($reviews)) {
            $rIds = array_column($reviews, 'id');
            $idPlaceholders = implode(',', array_fill(0, count($rIds), '?'));
            $types = str_repeat('i', count($rIds));
            
            $respSql = "SELECT * FROM review_responses WHERE review_id IN ($idPlaceholders) AND status = 'published' ORDER BY created_at ASC";
            $respStmt = $this->db->prepare($respSql);
            if ($respStmt) {
                $respStmt->bind_param($types, ...$rIds);
                $respStmt->execute();
                $responses = $respStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $respStmt->close();

                $respGrouped = [];
                foreach ($responses as $resp) {
                    $respGrouped[$resp['review_id']][] = $resp;
                }

                foreach ($reviews as &$rev) {
                    $rev['responses'] = $respGrouped[$rev['id']] ?? [];
                }
                unset($rev);
            }
        }

        return $reviews;
    }

    /**
     * Get aggregate multi-criteria ratings summary for a property
     */
    public function getReviewSummary(int $farmhouseId): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_reviews,
                AVG(overall_rating) as avg_overall,
                AVG(cleanliness_rating) as avg_cleanliness,
                AVG(location_rating) as avg_location,
                AVG(value_rating) as avg_value,
                AVG(hospitality_rating) as avg_hospitality
            FROM reviews
            WHERE farmhouse_id = ? AND status = 'approved'
        ");
        if (!$stmt) {
            return [
                'total_reviews'   => 0,
                'has_reviews'     => false,
                'avg_overall'     => 0.0,
                'avg_cleanliness' => 0.0,
                'avg_location'    => 0.0,
                'avg_value'       => 0.0,
                'avg_hospitality' => 0.0,
            ];
        }
        $stmt->bind_param("i", $farmhouseId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $total = (int)($row['total_reviews'] ?? 0);
        if ($total === 0) {
            return [
                'total_reviews'   => 0,
                'has_reviews'     => false,
                'avg_overall'     => 0.0,
                'avg_cleanliness' => 0.0,
                'avg_location'    => 0.0,
                'avg_value'       => 0.0,
                'avg_hospitality' => 0.0,
            ];
        }

        return [
            'total_reviews'   => $total,
            'has_reviews'     => true,
            'avg_overall'     => round((float)($row['avg_overall'] ?? 5.0), 1),
            'avg_cleanliness' => round((float)($row['avg_cleanliness'] ?? 5.0), 1),
            'avg_location'    => round((float)($row['avg_location'] ?? 5.0), 1),
            'avg_value'       => round((float)($row['avg_value'] ?? 5.0), 1),
            'avg_hospitality' => round((float)($row['avg_hospitality'] ?? 5.0), 1),
        ];
    }

    /**
     * Admin: get all reviews with optional status filter
     */
    public function getAllReviews(?string $status = null): array {
        $sql = "
            SELECT r.*, f.title as farmhouse_title
            FROM reviews r
            JOIN farmhouses f ON r.farmhouse_id = f.id
        ";
        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $sql .= " WHERE r.status = ? ORDER BY r.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $status);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $sql .= " ORDER BY r.created_at DESC";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Admin: get review status counters
     */
    public function getReviewStats(): array {
        $stats = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        $res = $this->db->query("SELECT status, COUNT(*) as cnt FROM reviews GROUP BY status");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $s = strtolower($row['status']);
                if (isset($stats[$s])) {
                    $stats[$s] = (int)$row['cnt'];
                }
                $stats['all'] += (int)$row['cnt'];
            }
        }
        return $stats;
    }

    /**
     * Admin: update review status
     */
    public function updateStatus(int $id, string $status, ?string $adminNotes = null): bool {
        if (!in_array($status, ['pending', 'approved', 'rejected'])) return false;
        $stmt = $this->db->prepare("UPDATE reviews SET status = ?, admin_notes = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $adminNotes, $id);
        return $stmt->execute();
    }

    /**
     * Admin: delete review
     */
    public function deleteReview(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
