<?php
/**
 * Database Migration Script: Complete Guest & Member Management System
 * Run via CLI: php database/migrate_guest_system.php
 */

require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

$db = Database::connect();

echo "========================================================\n";
echo "   STAYORA GUEST & MEMBER MANAGEMENT MIGRATION SCRIPT   \n";
echo "========================================================\n\n";

// Helper to check if a column exists in a table
function columnExists(mysqli $db, string $table, string $column): bool {
    $res = $db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    return ($res && $res->num_rows > 0);
}

// 1. ALTER TABLE `users` to extend Guest & Member fields
echo "1. Enhancing `users` table with Guest & Member fields...\n";

$userColumns = [
    'guest_code' => "ALTER TABLE `users` ADD COLUMN `guest_code` VARCHAR(30) UNIQUE NULL AFTER `id`",
    'guest_type' => "ALTER TABLE `users` ADD COLUMN `guest_type` ENUM('guest', 'registered_member', 'corporate_guest', 'vip') DEFAULT 'guest' AFTER `name`",
    'alternate_phone' => "ALTER TABLE `users` ADD COLUMN `alternate_phone` VARCHAR(20) NULL AFTER `phone`",
    'address_line1' => "ALTER TABLE `users` ADD COLUMN `address_line1` VARCHAR(255) NULL AFTER `gender`",
    'address_line2' => "ALTER TABLE `users` ADD COLUMN `address_line2` VARCHAR(255) NULL AFTER `address_line1`",
    'city' => "ALTER TABLE `users` ADD COLUMN `city` VARCHAR(100) NULL AFTER `address_line2`",
    'state' => "ALTER TABLE `users` ADD COLUMN `state` VARCHAR(100) NULL AFTER `city`",
    'pincode' => "ALTER TABLE `users` ADD COLUMN `pincode` VARCHAR(20) NULL AFTER `state`",
    'country' => "ALTER TABLE `users` ADD COLUMN `country` VARCHAR(100) DEFAULT 'India' AFTER `pincode`",
    'emergency_contact_name' => "ALTER TABLE `users` ADD COLUMN `emergency_contact_name` VARCHAR(100) NULL AFTER `country`",
    'emergency_contact_phone' => "ALTER TABLE `users` ADD COLUMN `emergency_contact_phone` VARCHAR(20) NULL AFTER `emergency_contact_name`",
    'emergency_contact_relation' => "ALTER TABLE `users` ADD COLUMN `emergency_contact_relation` VARCHAR(50) NULL AFTER `emergency_contact_phone`",
    'gov_id_type' => "ALTER TABLE `users` ADD COLUMN `gov_id_type` ENUM('aadhaar', 'pan', 'passport', 'driving_license', 'voter_id', 'other') NULL AFTER `emergency_contact_relation`",
    'gov_id_number' => "ALTER TABLE `users` ADD COLUMN `gov_id_number` VARCHAR(100) NULL AFTER `gov_id_type`",
    'kyc_status' => "ALTER TABLE `users` ADD COLUMN `kyc_status` ENUM('unverified', 'pending', 'verified', 'rejected') DEFAULT 'unverified' AFTER `gov_id_number`",
    'kyc_verified_at' => "ALTER TABLE `users` ADD COLUMN `kyc_verified_at` DATETIME NULL AFTER `kyc_status`",
    'kyc_verified_by' => "ALTER TABLE `users` ADD COLUMN `kyc_verified_by` INT(11) NULL AFTER `kyc_verified_at`",
    'kyc_notes' => "ALTER TABLE `users` ADD COLUMN `kyc_notes` TEXT NULL AFTER `kyc_verified_by`",
    'membership_tier' => "ALTER TABLE `users` ADD COLUMN `membership_tier` ENUM('none', 'silver', 'gold', 'platinum', 'vip') DEFAULT 'none' AFTER `kyc_notes`",
    'membership_number' => "ALTER TABLE `users` ADD COLUMN `membership_number` VARCHAR(50) UNIQUE NULL AFTER `membership_tier`",
    'membership_status' => "ALTER TABLE `users` ADD COLUMN `membership_status` ENUM('none', 'active', 'expired', 'suspended', 'cancelled') DEFAULT 'none' AFTER `membership_number`",
    'membership_start_date' => "ALTER TABLE `users` ADD COLUMN `membership_start_date` DATE NULL AFTER `membership_status`",
    'membership_expiry_date' => "ALTER TABLE `users` ADD COLUMN `membership_expiry_date` DATE NULL AFTER `membership_start_date`",
    'special_requests' => "ALTER TABLE `users` ADD COLUMN `special_requests` TEXT NULL AFTER `membership_expiry_date`",
    'admin_notes' => "ALTER TABLE `users` ADD COLUMN `admin_notes` TEXT NULL AFTER `special_requests`",
    'total_stays_count' => "ALTER TABLE `users` ADD COLUMN `total_stays_count` INT(11) DEFAULT 0 AFTER `admin_notes`",
    'total_nights_stayed' => "ALTER TABLE `users` ADD COLUMN `total_nights_stayed` INT(11) DEFAULT 0 AFTER `total_stays_count`",
    'total_spend' => "ALTER TABLE `users` ADD COLUMN `total_spend` DECIMAL(12,2) DEFAULT 0.00 AFTER `total_nights_stayed`",
    'last_visit_at' => "ALTER TABLE `users` ADD COLUMN `last_visit_at` DATETIME NULL AFTER `total_spend`",
    'updated_at' => "ALTER TABLE `users` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`"
];

foreach ($userColumns as $col => $sql) {
    if (!columnExists($db, 'users', $col)) {
        if ($db->query($sql)) {
            echo "  + Added column `users`.`{$col}`\n";
        } else {
            echo "  ! Error adding `{$col}`: " . $db->error . "\n";
        }
    } else {
        echo "  - Column `users`.`{$col}` already exists.\n";
    }
}

// 2. CREATE TABLE `guest_documents`
echo "\n2. Creating `guest_documents` table...\n";
$createGuestDocsSql = "CREATE TABLE IF NOT EXISTS `guest_documents` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(11) NOT NULL,
  `document_type` ENUM('aadhaar', 'pan', 'passport', 'driving_license', 'voter_id', 'visa', 'corporate_id', 'other') NOT NULL,
  `document_name` VARCHAR(150) NOT NULL,
  `document_number` VARCHAR(100) NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` INT(11) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `status` ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
  `rejection_reason` TEXT NULL,
  `verified_by` INT(11) NULL,
  `verified_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_docs` (`user_id`),
  INDEX `idx_doc_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($db->query($createGuestDocsSql)) {
    echo "  + `guest_documents` table verified/created.\n";
} else {
    echo "  ! Error creating `guest_documents`: " . $db->error . "\n";
}

// 3. CREATE TABLE `booking_stay_guests` (Multi-occupant management)
echo "\n3. Creating `booking_stay_guests` table...\n";
$createStayGuestsSql = "CREATE TABLE IF NOT EXISTS `booking_stay_guests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT(11) NOT NULL,
  `user_id` INT(11) NULL,
  `guest_name` VARCHAR(100) NOT NULL,
  `guest_phone` VARCHAR(20) NULL,
  `guest_email` VARCHAR(100) NULL,
  `guest_gender` ENUM('male', 'female', 'other') NULL,
  `age` INT(11) NULL,
  `id_type` VARCHAR(50) NULL,
  `id_number` VARCHAR(100) NULL,
  `id_document` VARCHAR(255) NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `checked_in_at` DATETIME NULL,
  `checked_out_at` DATETIME NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_stay_booking` (`booking_id`),
  INDEX `idx_stay_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($db->query($createStayGuestsSql)) {
    echo "  + `booking_stay_guests` table verified/created.\n";
} else {
    echo "  ! Error creating `booking_stay_guests`: " . $db->error . "\n";
}

// 4. CREATE TABLE `membership_history`
echo "\n4. Creating `membership_history` table...\n";
$createMemHistorySql = "CREATE TABLE IF NOT EXISTS `membership_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(11) NOT NULL,
  `action` ENUM('enrolled', 'upgraded', 'downgraded', 'renewed', 'expired', 'suspended', 'cancelled') NOT NULL,
  `previous_tier` ENUM('none', 'silver', 'gold', 'platinum', 'vip') DEFAULT 'none',
  `new_tier` ENUM('none', 'silver', 'gold', 'platinum', 'vip') NOT NULL,
  `start_date` DATE NOT NULL,
  `expiry_date` DATE NULL,
  `notes` TEXT NULL,
  `performed_by` INT(11) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_member_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($db->query($createMemHistorySql)) {
    echo "  + `membership_history` table verified/created.\n";
} else {
    echo "  ! Error creating `membership_history`: " . $db->error . "\n";
}

// 5. CREATE TABLE `guest_activity_logs`
echo "\n5. Creating `guest_activity_logs` table...\n";
$createActLogsSql = "CREATE TABLE IF NOT EXISTS `guest_activity_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(11) NOT NULL,
  `activity_type` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `performed_by` VARCHAR(100) DEFAULT 'system',
  `metadata` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_activity_user` (`user_id`),
  INDEX `idx_activity_type` (`activity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($db->query($createActLogsSql)) {
    echo "  + `guest_activity_logs` table verified/created.\n";
} else {
    echo "  ! Error creating `guest_activity_logs`: " . $db->error . "\n";
}

// 6. Ensure upload directory exists
$uploadDir = __DIR__ . '/../assets/images/uploads/guest_documents';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    echo "\n  + Created directory `assets/images/uploads/guest_documents`\n";
}

// 7. Seed & Backfill Baseline Data for Existing Users
echo "\n6. Backfilling rich baseline data for existing users...\n";

$baselineUsers = [
    1 => [
        'guest_code' => 'STY-G-00001',
        'guest_type' => 'vip',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'address_line1' => 'Flat 14B, Sea Face Enclave, Worli',
        'pincode' => '400018',
        'country' => 'India',
        'alternate_phone' => '022-24987654',
        'emergency_contact_name' => 'Kavita Sharma',
        'emergency_contact_phone' => '+91 9820011223',
        'emergency_contact_relation' => 'Spouse',
        'gov_id_type' => 'aadhaar',
        'gov_id_number' => 'XXXX-XXXX-4589',
        'kyc_status' => 'verified',
        'kyc_verified_at' => '2026-10-12 11:30:00',
        'kyc_verified_by' => 1,
        'kyc_notes' => 'Aadhaar biometric verified. High net-worth individual.',
        'membership_tier' => 'vip',
        'membership_number' => 'STY-VIP-001',
        'membership_status' => 'active',
        'membership_start_date' => '2026-10-11',
        'membership_expiry_date' => '2027-10-11',
        'special_requests' => 'Prefers poolside cabana, strictly vegan breakfast options, extra firm pillows.',
        'admin_notes' => 'Chairman at Sharma Global. Frequent luxury retreat guest.',
        'total_stays_count' => 1,
        'total_nights_stayed' => 3,
        'total_spend' => 105000.00,
        'last_visit_at' => '2026-10-17 11:00:00'
    ],
    2 => [
        'guest_code' => 'STY-G-00002',
        'guest_type' => 'registered_member',
        'city' => 'Pune',
        'state' => 'Maharashtra',
        'address_line1' => 'Penthouse 7, Koregaon Park',
        'pincode' => '411001',
        'country' => 'India',
        'alternate_phone' => '+91 9123456780',
        'emergency_contact_name' => 'Sanjay Deshmukh',
        'emergency_contact_phone' => '+91 9823456789',
        'emergency_contact_relation' => 'Father',
        'gov_id_type' => 'passport',
        'gov_id_number' => 'Z5489621',
        'kyc_status' => 'verified',
        'kyc_verified_at' => '2026-10-13 14:00:00',
        'kyc_verified_by' => 1,
        'kyc_notes' => 'Passport copy verified. Premium traveler.',
        'membership_tier' => 'gold',
        'membership_number' => 'STY-GLD-002',
        'membership_status' => 'active',
        'membership_start_date' => '2026-10-11',
        'membership_expiry_date' => '2027-10-11',
        'special_requests' => 'Allergic to shellfish. Requests early check-in whenever possible.',
        'admin_notes' => 'Architect and interior designer. Loves modern aesthetic villas.',
        'total_stays_count' => 0,
        'total_nights_stayed' => 0,
        'total_spend' => 0.00,
        'last_visit_at' => null
    ],
    3 => [
        'guest_code' => 'STY-G-00003',
        'guest_type' => 'registered_member',
        'city' => 'Bangalore',
        'state' => 'Karnataka',
        'address_line1' => 'Plot 42, Indiranagar 100ft Road',
        'pincode' => '560038',
        'country' => 'India',
        'alternate_phone' => '+91 9988776650',
        'emergency_contact_name' => 'Nikhil Kapoor',
        'emergency_contact_phone' => '+91 9845012345',
        'emergency_contact_relation' => 'Brother',
        'gov_id_type' => 'driving_license',
        'gov_id_number' => 'KA-01-2018-004589',
        'kyc_status' => 'pending',
        'kyc_verified_at' => null,
        'kyc_verified_by' => null,
        'kyc_notes' => 'Driving license document uploaded, awaiting front-desk validation.',
        'membership_tier' => 'silver',
        'membership_number' => 'STY-SLV-003',
        'membership_status' => 'active',
        'membership_start_date' => '2026-10-11',
        'membership_expiry_date' => '2027-10-11',
        'special_requests' => 'High speed Wi-Fi for remote tech work.',
        'admin_notes' => 'Tech founder. Frequently books weekend team offsites.',
        'total_stays_count' => 0,
        'total_nights_stayed' => 0,
        'total_spend' => 0.00,
        'last_visit_at' => null
    ],
    4 => [
        'guest_code' => 'STY-G-00004',
        'guest_type' => 'guest',
        'city' => 'Kolkata',
        'state' => 'West Bengal',
        'address_line1' => '8B Alipore Avenue',
        'pincode' => '700027',
        'country' => 'India',
        'alternate_phone' => null,
        'emergency_contact_name' => 'Aniket Sen Gupta',
        'emergency_contact_phone' => '+91 9831098765',
        'emergency_contact_relation' => 'Spouse',
        'gov_id_type' => 'aadhaar',
        'gov_id_number' => 'XXXX-XXXX-9912',
        'kyc_status' => 'unverified',
        'kyc_verified_at' => null,
        'kyc_verified_by' => null,
        'kyc_notes' => null,
        'membership_tier' => 'none',
        'membership_number' => null,
        'membership_status' => 'none',
        'membership_start_date' => null,
        'membership_expiry_date' => null,
        'special_requests' => null,
        'admin_notes' => 'Registered via web portal. No stays yet.',
        'total_stays_count' => 0,
        'total_nights_stayed' => 0,
        'total_spend' => 0.00,
        'last_visit_at' => null
    ],
    5 => [
        'guest_code' => 'STY-G-00005',
        'guest_type' => 'corporate_guest',
        'city' => 'New Delhi',
        'state' => 'Delhi',
        'address_line1' => 'Villa 3, Westend Greens, Rajokri',
        'pincode' => '110038',
        'country' => 'India',
        'alternate_phone' => '+91 9711009980',
        'emergency_contact_name' => 'Pooja Singhal',
        'emergency_contact_phone' => '+91 9811099887',
        'emergency_contact_relation' => 'Spouse',
        'gov_id_type' => 'pan',
        'gov_id_number' => 'AAFPS9988C',
        'kyc_status' => 'verified',
        'kyc_verified_at' => '2026-10-14 16:00:00',
        'kyc_verified_by' => 1,
        'kyc_notes' => 'Corporate GST and PAN verified.',
        'membership_tier' => 'platinum',
        'membership_number' => 'STY-PLT-005',
        'membership_status' => 'active',
        'membership_start_date' => '2026-10-11',
        'membership_expiry_date' => '2027-10-11',
        'special_requests' => 'Dedicated bonfire arrangement, late night banquet setup.',
        'admin_notes' => 'Managing Director at Singhal Holdings. Multiple property bookings.',
        'total_stays_count' => 2,
        'total_nights_stayed' => 4,
        'total_spend' => 49000.00,
        'last_visit_at' => '2026-10-16 18:00:00'
    ]
];

foreach ($baselineUsers as $userId => $fields) {
    // Check if user exists
    $uCheck = $db->query("SELECT id, guest_code FROM users WHERE id = {$userId}");
    if ($uCheck && $uCheck->num_rows > 0) {
        $uRow = $uCheck->fetch_assoc();
        if (empty($uRow['guest_code'])) {
            $setParts = [];
            foreach ($fields as $k => $v) {
                if ($v === null) {
                    $setParts[] = "`{$k}` = NULL";
                } elseif (is_numeric($v)) {
                    $setParts[] = "`{$k}` = {$v}";
                } else {
                    $setParts[] = "`{$k}` = '" . $db->real_escape_string($v) . "'";
                }
            }
            $updateSql = "UPDATE `users` SET " . implode(", ", $setParts) . " WHERE id = {$userId}";
            if ($db->query($updateSql)) {
                echo "  + Seeded guest profile for User #{$userId} ({$fields['guest_code']})\n";

                // Log initial activity
                $actSql = "INSERT INTO `guest_activity_logs` (`user_id`, `activity_type`, `description`, `performed_by`, `created_at`) 
                           VALUES ({$userId}, 'account_setup', 'Guest profile created with ID {$fields['guest_code']}', 'System Migration', NOW())";
                $db->query($actSql);

                // If member, log membership history
                if ($fields['membership_tier'] !== 'none') {
                    $memSql = "INSERT INTO `membership_history` (`user_id`, `action`, `previous_tier`, `new_tier`, `start_date`, `expiry_date`, `notes`, `performed_by`, `created_at`)
                               VALUES ({$userId}, 'enrolled', 'none', '{$fields['membership_tier']}', '{$fields['membership_start_date']}', '{$fields['membership_expiry_date']}', 'Initial membership tier assigned during system provisioning', 1, NOW())";
                    $db->query($memSql);
                }
            } else {
                echo "  ! Error updating User #{$userId}: " . $db->error . "\n";
            }
        } else {
            echo "  - User #{$userId} already has guest_code ({$uRow['guest_code']}).\n";
        }
    }
}

// 8. Seed sample documents for User #1 and User #2
echo "\n7. Seeding sample KYC documents...\n";
$sampleDocs = [
    [
        'user_id' => 1,
        'document_type' => 'aadhaar',
        'document_name' => 'Aadhaar Card Front & Back',
        'document_number' => 'XXXX-XXXX-4589',
        'file_path' => 'assets/images/uploads/guest_documents/sample_aadhaar_01.pdf',
        'file_size' => 450200,
        'mime_type' => 'application/pdf',
        'status' => 'verified',
        'verified_by' => 1,
        'verified_at' => '2026-10-12 11:30:00'
    ],
    [
        'user_id' => 1,
        'document_type' => 'pan',
        'document_name' => 'Permanent Account Number (PAN)',
        'document_number' => 'ABCPS1234F',
        'file_path' => 'assets/images/uploads/guest_documents/sample_pan_01.pdf',
        'file_size' => 280100,
        'mime_type' => 'application/pdf',
        'status' => 'verified',
        'verified_by' => 1,
        'verified_at' => '2026-10-12 11:35:00'
    ],
    [
        'user_id' => 2,
        'document_type' => 'passport',
        'document_name' => 'Indian Passport Bio Page',
        'document_number' => 'Z5489621',
        'file_path' => 'assets/images/uploads/guest_documents/sample_passport_02.pdf',
        'file_size' => 512000,
        'mime_type' => 'application/pdf',
        'status' => 'verified',
        'verified_by' => 1,
        'verified_at' => '2026-10-13 14:00:00'
    ],
    [
        'user_id' => 3,
        'document_type' => 'driving_license',
        'document_name' => 'Karnataka Driving License',
        'document_number' => 'KA-01-2018-004589',
        'file_path' => 'assets/images/uploads/guest_documents/sample_dl_03.jpg',
        'file_size' => 310500,
        'mime_type' => 'image/jpeg',
        'status' => 'pending',
        'verified_by' => null,
        'verified_at' => null
    ]
];

foreach ($sampleDocs as $doc) {
    $dCheck = $db->query("SELECT id FROM guest_documents WHERE user_id = {$doc['user_id']} AND document_type = '{$doc['document_type']}'");
    if ($dCheck && $dCheck->num_rows == 0) {
        $insDoc = $db->prepare("INSERT INTO guest_documents (user_id, document_type, document_name, document_number, file_path, file_size, mime_type, status, verified_by, verified_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($insDoc) {
            $insDoc->bind_param("issssisiss", $doc['user_id'], $doc['document_type'], $doc['document_name'], $doc['document_number'], $doc['file_path'], $doc['file_size'], $doc['mime_type'], $doc['status'], $doc['verified_by'], $doc['verified_at']);
            $insDoc->execute();
            $insDoc->close();
            echo "  + Added sample document: {$doc['document_name']} for User #{$doc['user_id']}\n";
        }
    }
}

// 9. Backfill existing booking occupants into booking_stay_guests for Booking #1
echo "\n8. Backfilling sample stay occupants into `booking_stay_guests`...\n";
$stayCheck = $db->query("SELECT id FROM booking_stay_guests WHERE booking_id = 1");
if ($stayCheck && $stayCheck->num_rows == 0) {
    $db->query("INSERT INTO `booking_stay_guests` (`booking_id`, `user_id`, `guest_name`, `guest_phone`, `guest_email`, `guest_gender`, `age`, `id_type`, `id_number`, `is_primary`, `checked_in_at`, `checked_out_at`, `notes`, `created_at`) 
                VALUES 
                (1, 1, 'Aarav Sharma', '9876543210', 'user@stayora.com', 'male', 38, 'Aadhaar Card', 'XXXX-XXXX-4589', 1, '2026-10-14 14:15:00', '2026-10-17 11:00:00', 'Primary Booking Guest', NOW()),
                (1, NULL, 'Kavita Sharma', '+91 9820011223', 'kavita@sharmaglobal.com', 'female', 35, 'Aadhaar Card', 'XXXX-XXXX-4590', 0, '2026-10-14 14:15:00', '2026-10-17 11:00:00', 'Accompanying Spouse', NOW()),
                (1, NULL, 'Aryan Sharma', NULL, NULL, 'male', 10, 'Birth Certificate', 'BC-MH-2016-88', 0, '2026-10-14 14:15:00', '2026-10-17 11:00:00', 'Child Occupant', NOW())");
    echo "  + Seeded 3 occupants for historical Booking #1\n";
} else {
    echo "  - Occupants for Booking #1 already exist.\n";
}

echo "\n========================================================\n";
echo "   MIGRATION & SEEDING COMPLETED SUCCESSFULLY!          \n";
echo "========================================================\n";
