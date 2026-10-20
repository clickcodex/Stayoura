<?php
namespace App\Models\User;

class MyBookingModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Fetch all booking requests made by a specific user.
     * This includes Farmhouse details and the primary image.
     */
    /**
     * Fetch all booking requests made by a specific user.
     * This includes Farmhouse details and the primary image.
     */
    public function getUserRequests($userId, $status = 'all', $search = '') {
        $sql = "SELECT 
                    br.*, 
                    COALESCE(f.title, CONCAT('Farmhouse #', br.farmhouse_id)) as farm_title, 
                    COALESCE(f.location, '—') as location, 
                    COALESCE(f.category, 'Farmhouse') as farm_category,
                    (SELECT image_url FROM images WHERE farmhouse_id = f.id LIMIT 1) as main_image
                FROM booking_requests br
                LEFT JOIN farmhouses f ON br.farmhouse_id = f.id
                WHERE br.user_id = ?";

        $types = "i";
        $params = [$userId];

        if (!empty($status) && strtolower($status) !== 'all') {
            $st = strtolower(trim($status));
            if ($st === 'confirmed' || $st === 'approved') {
                $sql .= " AND br.status IN ('approved', 'confirmed', 'checked_in')";
            } elseif ($st === 'completed') {
                $sql .= " AND br.status IN ('checked_out', 'completed')";
            } else {
                $sql .= " AND br.status = ?";
                $types .= "s";
                $params[] = $st;
            }
        }

        if (!empty($search)) {
            $q = '%' . strtolower(trim($search)) . '%';
            $sql .= " AND (LOWER(COALESCE(f.title, '')) LIKE ? OR LOWER(COALESCE(f.location, '')) LIKE ?)";
            $types .= "ss";
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY br.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Fetch confirmed bookings (Booking requests that have been approved by admin/host).
     */
    public function getUserConfirmedBookings($userId) {
        $sql = "SELECT 
                    br.*, 
                    COALESCE(f.title, CONCAT('Farmhouse #', br.farmhouse_id)) as farm_title, 
                    COALESCE(f.location, '—') as location, 
                    COALESCE(f.category, 'Farmhouse') as farm_category,
                    COALESCE(f.price, br.price) as farmhouse_price,
                    (SELECT image_url FROM images WHERE farmhouse_id = f.id LIMIT 1) as main_image
                FROM booking_requests br
                LEFT JOIN farmhouses f ON br.farmhouse_id = f.id
                WHERE br.user_id = ? AND br.status IN ('approved', 'confirmed', 'checked_in', 'checked_out', 'completed')
                ORDER BY br.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Cancel a pending request by setting status to cancelled
     */
    public function cancelRequest($requestId, $userId) {
        // Ensure the request belongs to the user before updating
        $sql = "UPDATE booking_requests SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("ii", $requestId, $userId);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
}