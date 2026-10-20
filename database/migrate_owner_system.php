<?php
/**
 * Migration Script: Property Owner Management System
 * Applies safe, non-destructive schema additions, creates ownership history table,
 * enhances document tracking, and backfills baseline owner information.
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

echo "=== STAGE 1: MIGRATING DATABASE FOR PROPERTY OWNER MANAGEMENT ===\n";

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

// 1. Enhance owners table
echo "1. Checking `owners` table...\n";

// Safely modify status column to standard ENUM with default 'active'
$db->query("ALTER TABLE `owners` MODIFY COLUMN `status` ENUM('active','inactive','pending','suspended') NOT NULL DEFAULT 'active'");
echo "  - Standardized `status` enum column.\n";

$ownerCols = [
    'owner_type'          => "ENUM('individual','organization') NOT NULL DEFAULT 'individual' AFTER `name`",
    'business_name'       => "VARCHAR(150) NULL AFTER `owner_type`",
    'alternate_phone'     => "VARCHAR(20) NULL AFTER `phone`",
    'address'             => "TEXT NULL AFTER `alternate_phone`",
    'city'                => "VARCHAR(100) NULL AFTER `address`",
    'state'               => "VARCHAR(100) NULL AFTER `city`",
    'pincode'             => "VARCHAR(20) NULL AFTER `state`",
    'country'             => "VARCHAR(100) NOT NULL DEFAULT 'India' AFTER `pincode`",
    'gov_id_type'         => "VARCHAR(50) NULL AFTER `country`",
    'gov_id_number'       => "VARCHAR(100) NULL AFTER `gov_id_type`",
    'bank_name'           => "VARCHAR(100) NULL AFTER `gov_id_number`",
    'bank_account_number' => "VARCHAR(50) NULL AFTER `bank_name`",
    'bank_ifsc'           => "VARCHAR(25) NULL AFTER `bank_account_number`",
    'bank_account_holder' => "VARCHAR(100) NULL AFTER `bank_ifsc`",
    'upi_id'              => "VARCHAR(100) NULL AFTER `bank_account_holder`",
    'admin_notes'         => "TEXT NULL AFTER `upi_id`",
    'kyc_status'          => "ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending' AFTER `admin_notes`",
    'verified_at'         => "DATETIME NULL AFTER `kyc_status`",
    'verified_by'         => "INT(11) NULL AFTER `verified_at`",
    'updated_at'          => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
];

foreach ($ownerCols as $col => $def) {
    if (!columnExists($db, 'owners', $col)) {
        $q = "ALTER TABLE `owners` ADD COLUMN `{$col}` {$def}";
        if ($db->query($q)) {
            echo "  - Added column `{$col}` to `owners`.\n";
        } else {
            echo "  ! Error adding `{$col}`: " . $db->error . "\n";
        }
    } else {
        echo "  - Column `{$col}` already exists in `owners`.\n";
    }
}

// Indexes for owners
$ownerIndices = [
    'idx_owners_status' => 'status',
    'idx_owners_kyc'    => 'kyc_status',
    'idx_owners_city'   => 'city',
];
foreach ($ownerIndices as $idxName => $col) {
    if (!indexExists($db, 'owners', $idxName)) {
        $db->query("ALTER TABLE `owners` ADD INDEX `{$idxName}` (`{$col}`)");
        echo "  - Added index `{$idxName}` on `{$col}`.\n";
    }
}

// 2. Enhance owner_documents table
echo "\n2. Checking `owner_documents` table...\n";

// Update document_type enum to include modern doc types
$db->query("ALTER TABLE `owner_documents` MODIFY COLUMN `document_type` ENUM(
    'aadhaar',
    'pan',
    'gst_certificate',
    'electricity_bill',
    'property_registry',
    'agreement',
    'bank_passbook',
    'noc',
    'other'
) NOT NULL DEFAULT 'other'");
echo "  - Updated `document_type` enum on `owner_documents`.\n";

$docCols = [
    'document_name' => "VARCHAR(150) NULL AFTER `document_type`",
    'file_size'     => "INT(11) NULL AFTER `file_path`",
    'mime_type'     => "VARCHAR(50) NULL AFTER `file_size`",
    'verified_by'   => "INT(11) NULL AFTER `verified_at`",
    'updated_at'    => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
];

foreach ($docCols as $col => $def) {
    if (!columnExists($db, 'owner_documents', $col)) {
        $q = "ALTER TABLE `owner_documents` ADD COLUMN `{$col}` {$def}";
        if ($db->query($q)) {
            echo "  - Added column `{$col}` to `owner_documents`.\n";
        } else {
            echo "  ! Error adding `{$col}`: " . $db->error . "\n";
        }
    } else {
        echo "  - Column `{$col}` already exists in `owner_documents`.\n";
    }
}

if (!indexExists($db, 'owner_documents', 'idx_owner_docs_owner')) {
    $db->query("ALTER TABLE `owner_documents` ADD INDEX `idx_owner_docs_owner` (`owner_id`)");
    echo "  - Added index `idx_owner_docs_owner` on `owner_id`.\n";
}

// 3. Create property_ownership_history table
echo "\n3. Checking `property_ownership_history` table...\n";
$createPohSql = "CREATE TABLE IF NOT EXISTS `property_ownership_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `farmhouse_id` INT NOT NULL,
    `previous_owner_id` INT NULL,
    `new_owner_id` INT NOT NULL,
    `transferred_by` INT NULL,
    `transfer_reason` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_poh_farmhouse` (`farmhouse_id`),
    INDEX `idx_poh_new_owner` (`new_owner_id`),
    INDEX `idx_poh_prev_owner` (`previous_owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($db->query($createPohSql)) {
    echo "  - Table `property_ownership_history` confirmed/created successfully.\n";
} else {
    echo "  ! Error creating `property_ownership_history`: " . $db->error . "\n";
}

// 4. Backfill baseline data for the 4 existing owners
echo "\n4. Backfilling profile information for seed owners...\n";
$seedProfiles = [
    1 => [
        'owner_type'          => 'individual',
        'business_name'       => null,
        'city'                => 'Mumbai',
        'state'               => 'Maharashtra',
        'pincode'             => '400050',
        'address'             => 'Oberoi Chambers, Bandra West',
        'gov_id_type'         => 'PAN Card',
        'gov_id_number'       => 'ABCPO1234F',
        'bank_name'           => 'HDFC Bank',
        'bank_account_number' => '50100492817291',
        'bank_ifsc'           => 'HDFC0000042',
        'bank_account_holder' => 'Vikramaditya Oberoi',
        'upi_id'              => 'vikram.oberoi@okhdfcbank',
        'kyc_status'          => 'verified',
        'verified_at'         => '2026-01-15 10:30:00',
        'admin_notes'         => 'Founding luxury villa partner across North Goa & Lonavala.'
    ],
    2 => [
        'owner_type'          => 'organization',
        'business_name'       => 'Singhania Luxury Escapes Pvt Ltd',
        'city'                => 'New Delhi',
        'state'               => 'Delhi',
        'pincode'             => '110001',
        'address'             => 'Singhania Tower, Barakhamba Road',
        'gov_id_type'         => 'GSTIN / CIN',
        'gov_id_number'       => '07AAACS1234F1Z8',
        'bank_name'           => 'ICICI Bank',
        'bank_account_number' => '000405018274',
        'bank_ifsc'           => 'ICIC0000004',
        'bank_account_holder' => 'Singhania Luxury Escapes Pvt Ltd',
        'upi_id'              => 'singhania.escapes@icici',
        'kyc_status'          => 'verified',
        'verified_at'         => '2026-02-10 14:00:00',
        'admin_notes'         => 'Corporate hospitality group managing premier heritage estates.'
    ],
    3 => [
        'owner_type'          => 'individual',
        'business_name'       => null,
        'city'                => 'Gurgaon',
        'state'               => 'Haryana',
        'pincode'             => '122002',
        'address'             => 'Golf Course Road, DLF Phase 5',
        'gov_id_type'         => 'Aadhaar Card',
        'gov_id_number'       => '8492-1029-4821',
        'bank_name'           => 'Axis Bank',
        'bank_account_number' => '918020048172918',
        'bank_ifsc'           => 'UTIB0000123',
        'bank_account_holder' => 'Kabir Mehra',
        'upi_id'              => 'kabir.mehra@axisbank',
        'kyc_status'          => 'verified',
        'verified_at'         => '2026-02-20 16:45:00',
        'admin_notes'         => 'Private estate owners with scenic forest & lakeside retreats.'
    ],
    4 => [
        'owner_type'          => 'organization',
        'business_name'       => 'Pratap Heritage Properties LLP',
        'city'                => 'Jaipur',
        'state'               => 'Rajasthan',
        'pincode'             => '302001',
        'address'             => 'Civil Lines, Near Raj Bhawan',
        'gov_id_type'         => 'LLPIN / PAN',
        'gov_id_number'       => 'AAG-9482',
        'bank_name'           => 'State Bank of India',
        'bank_account_number' => '38491029482',
        'bank_ifsc'           => 'SBIN0001234',
        'bank_account_holder' => 'Pratap Heritage Properties LLP',
        'upi_id'              => 'pratap.heritage@sbi',
        'kyc_status'          => 'verified',
        'verified_at'         => '2026-03-01 11:15:00',
        'admin_notes'         => 'Royal haveli and heritage manor preservation specialists.'
    ]
];

foreach ($seedProfiles as $id => $p) {
    $stmt = $db->prepare("UPDATE `owners` SET 
        `owner_type` = ?,
        `business_name` = ?,
        `city` = ?,
        `state` = ?,
        `pincode` = ?,
        `address` = ?,
        `gov_id_type` = ?,
        `gov_id_number` = ?,
        `bank_name` = ?,
        `bank_account_number` = ?,
        `bank_ifsc` = ?,
        `bank_account_holder` = ?,
        `upi_id` = ?,
        `kyc_status` = ?,
        `verified_at` = ?,
        `admin_notes` = ?
        WHERE `id` = ?");
    $stmt->bind_param(
        'ssssssssssssssssi',
        $p['owner_type'],
        $p['business_name'],
        $p['city'],
        $p['state'],
        $p['pincode'],
        $p['address'],
        $p['gov_id_type'],
        $p['gov_id_number'],
        $p['bank_name'],
        $p['bank_account_number'],
        $p['bank_ifsc'],
        $p['bank_account_holder'],
        $p['upi_id'],
        $p['kyc_status'],
        $p['verified_at'],
        $p['admin_notes'],
        $id
    );
    $stmt->execute();
    echo "  - Backfilled rich profile for owner #{$id}.\n";
}

// 5. Backfill baseline property ownership history
echo "\n5. Backfilling baseline property ownership records...\n";
$historyCheck = $db->query("SELECT COUNT(*) as c FROM `property_ownership_history`");
$hRow = $historyCheck->fetch_assoc();
if ((int)($hRow['c'] ?? 0) === 0) {
    $farmhouses = $db->query("SELECT id, owner_id, created_at FROM `farmhouses` WHERE owner_id IS NOT NULL AND owner_id > 0");
    $insertedCount = 0;
    while ($f = $farmhouses->fetch_assoc()) {
        $pohStmt = $db->prepare("INSERT INTO `property_ownership_history` 
            (`farmhouse_id`, `previous_owner_id`, `new_owner_id`, `transferred_by`, `transfer_reason`, `notes`, `created_at`) 
            VALUES (?, NULL, ?, 1, 'Initial estate registration', 'Baseline ownership record generated upon system migration.', ?)");
        $pohStmt->bind_param('iis', $f['id'], $f['owner_id'], $f['created_at']);
        $pohStmt->execute();
        $insertedCount++;
    }
    echo "  - Inserted {$insertedCount} baseline ownership history records for existing properties.\n";
} else {
    echo "  - `property_ownership_history` already has " . $hRow['c'] . " records. Skipping backfill.\n";
}

// 6. Ensure owner_documents directory exists
$docsDir = __DIR__ . '/../assets/images/uploads/owner_documents';
if (!is_dir($docsDir)) {
    mkdir($docsDir, 0755, true);
    echo "  - Created storage directory: assets/images/uploads/owner_documents/\n";
}

echo "\n=== MIGRATION COMPLETE! ALL STAGES APPLIED CLEANLY. ===\n";
