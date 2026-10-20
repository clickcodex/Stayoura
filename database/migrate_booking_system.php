<?php
/**
 * Migration Script: Booking and Stay Management System
 * Applies safe, non-destructive schema additions and backfills existing records.
 */
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../vendor/autoload.php';
if (file_exists(__DIR__ . '/../.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
}
foreach ($_ENV as $k => $v) {
    if (is_scalar($v) && $k !== '') putenv("{$k}={$v}");
}
require_once __DIR__ . '/../app/Config/Database.php';
$db = App\Config\Database::connect();

echo "=== STAGE 1: MIGRATING DATABASE FOR BOOKING & STAY MANAGEMENT ===\n";

// Helper to check if column exists
function columnExists($db, $table, $column) {
    $res = $db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    return ($res && $res->num_rows > 0);
}

// Helper to check if index exists
function indexExists($db, $table, $indexName) {
    $res = $db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");
    return ($res && $res->num_rows > 0);
}

// 1. Enhance booking_requests table
echo "1. Checking `booking_requests` table...\n";

// Modify status enum to include all standard hospitality states
$db->query("ALTER TABLE `booking_requests` MODIFY COLUMN `status` ENUM(
    'pending',
    'confirmed',
    'checked_in',
    'checked_out',
    'cancelled',
    'rejected',
    'no_show',
    'approved',
    'completed',
    'expired'
) NOT NULL DEFAULT 'pending'");
echo "  - Updated `status` enum column.\n";

$brCols = [
    'booking_reference'     => "VARCHAR(30) NULL AFTER `id`",
    'amount_paid'           => "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `price`",
    'balance_amount'        => "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `amount_paid`",
    'actual_check_in_at'    => "DATETIME NULL AFTER `check_in_time`",
    'actual_check_out_at'   => "DATETIME NULL AFTER `check_out_time`",
    'checked_in_by'         => "INT(11) NULL AFTER `actual_check_in_at`",
    'checked_out_by'        => "INT(11) NULL AFTER `actual_check_out_at`",
    'assigned_room_number'  => "VARCHAR(50) NULL AFTER `room_type_name`",
    'guest_id_type'         => "VARCHAR(50) NULL AFTER `assigned_room_number`",
    'guest_id_number'       => "VARCHAR(100) NULL AFTER `guest_id_type`",
    'guest_id_document'     => "VARCHAR(255) NULL AFTER `guest_id_number`",
    'cancellation_reason'   => "TEXT NULL AFTER `status`",
    'cancelled_at'          => "DATETIME NULL AFTER `cancellation_reason`",
    'cancelled_by'          => "ENUM('customer','admin','system') NULL AFTER `cancelled_at`",
    'admin_notes'           => "TEXT NULL AFTER `message`"
];

foreach ($brCols as $col => $definition) {
    if (!columnExists($db, 'booking_requests', $col)) {
        $q = "ALTER TABLE `booking_requests` ADD COLUMN `{$col}` {$definition}";
        if ($db->query($q)) {
            echo "  - Added column `{$col}`.\n";
        } else {
            echo "  ! Error adding `{$col}`: " . $db->error . "\n";
        }
    } else {
        echo "  - Column `{$col}` already exists.\n";
    }
}

// Add indexes if missing
if (!indexExists($db, 'booking_requests', 'idx_bk_ref')) {
    $db->query("ALTER TABLE `booking_requests` ADD INDEX `idx_bk_ref` (`booking_reference`)");
    echo "  - Added index `idx_bk_ref`.\n";
}
if (!indexExists($db, 'booking_requests', 'idx_bk_dates_status')) {
    $db->query("ALTER TABLE `booking_requests` ADD INDEX `idx_bk_dates_status` (`farmhouse_id`, `check_in`, `check_out`, `status`)");
    echo "  - Added index `idx_bk_dates_status`.\n";
}

// 2. Create booking_audit_logs table
echo "2. Checking `booking_audit_logs` table...\n";
$db->query("CREATE TABLE IF NOT EXISTS `booking_audit_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `booking_id` INT(11) NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `from_status` VARCHAR(30) NULL,
    `to_status` VARCHAR(30) NULL,
    `actor_type` ENUM('admin','customer','system') NOT NULL DEFAULT 'admin',
    `actor_id` INT(11) NULL,
    `actor_name` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_audit_bk` (`booking_id`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "  - `booking_audit_logs` ready.\n";

// 3. Create booking_extra_charges table
echo "3. Checking `booking_extra_charges` table...\n";
$db->query("CREATE TABLE IF NOT EXISTS `booking_extra_charges` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `booking_id` INT(11) NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `added_by` INT(11) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_extra_bk` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "  - `booking_extra_charges` ready.\n";

// 4. Enhance payments table
echo "4. Checking `payments` table...\n";
if (!columnExists($db, 'payments', 'payment_type')) {
    $db->query("ALTER TABLE `payments` ADD COLUMN `payment_type` ENUM('advance','balance','extra_charge','refund','full') NOT NULL DEFAULT 'full' AFTER `amount`");
    echo "  - Added `payment_type` to payments.\n";
}
if (!columnExists($db, 'payments', 'recorded_by')) {
    $db->query("ALTER TABLE `payments` ADD COLUMN `recorded_by` INT(11) NULL AFTER `verified_by`");
    echo "  - Added `recorded_by` to payments.\n";
}

// 5. Backfill existing records in booking_requests
echo "5. Backfilling existing records...\n";
$res = $db->query("SELECT id, price, payment_status, booking_reference FROM `booking_requests`");
$backfilled = 0;
while ($row = $res->fetch_assoc()) {
    $id = (int)$row['id'];
    $price = (float)($row['price'] ?? 0);
    $payStatus = strtolower(trim($row['payment_status'] ?? ''));
    $ref = $row['booking_reference'];

    $updates = [];
    if (empty($ref)) {
        $newRef = 'STAY-BK-' . str_pad($id, 5, '0', STR_PAD_LEFT);
        $updates[] = "`booking_reference` = '{$newRef}'";
    }

    if ($payStatus === 'paid') {
        $updates[] = "`amount_paid` = {$price}";
        $updates[] = "`balance_amount` = 0.00";
    } else {
        $updates[] = "`amount_paid` = 0.00";
        $updates[] = "`balance_amount` = {$price}";
    }

    if (!empty($updates)) {
        $sql = "UPDATE `booking_requests` SET " . implode(", ", $updates) . " WHERE `id` = {$id}";
        $db->query($sql);
        $backfilled++;
    }
}
echo "  - Backfilled {$backfilled} existing booking records.\n";

echo "\n=== MIGRATION COMPLETED SUCCESSFULLY! ===\n";
