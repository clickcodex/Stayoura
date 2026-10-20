<?php
/**
 * Migration: Property Enhancements (Booking Modes, Amenities, Rules, Descriptions)
 *
 * Adds rental_type ENUM('complete', 'room_wise', 'both') to farmhouses
 * Adds description, sort_order, icon_class to farmhouse_rules
 * Populates standard amenities and rule presets
 */
require_once __DIR__ . '/../app/Config/Database.php';

$conn = \App\Config\Database::connect();

echo "Running Property Enhancements Migration...\n";

// 1. rental_type in farmhouses
$colCheck = $conn->query("SHOW COLUMNS FROM farmhouses LIKE 'rental_type'")->fetch_assoc();
if (!$colCheck) {
    $conn->query("ALTER TABLE farmhouses ADD COLUMN rental_type ENUM('complete', 'room_wise', 'both') NOT NULL DEFAULT 'complete' AFTER allow_room_booking");
    echo "✓ Added 'rental_type' column to farmhouses.\n";
}

// Keep rental_type in sync with allow_room_booking
$conn->query("UPDATE farmhouses SET rental_type = 'both' WHERE allow_room_booking = 1 AND (rental_type IS NULL OR rental_type = 'complete')");
$conn->query("UPDATE farmhouses SET rental_type = 'complete' WHERE allow_room_booking = 0 AND (rental_type IS NULL OR rental_type = '')");

// 2. description, sort_order, icon_class in farmhouse_rules
$colDesc = $conn->query("SHOW COLUMNS FROM farmhouse_rules LIKE 'description'")->fetch_assoc();
if (!$colDesc) {
    $conn->query("ALTER TABLE farmhouse_rules ADD COLUMN description VARCHAR(255) NULL DEFAULT NULL AFTER is_allowed");
    echo "✓ Added 'description' column to farmhouse_rules.\n";
}

$colOrder = $conn->query("SHOW COLUMNS FROM farmhouse_rules LIKE 'sort_order'")->fetch_assoc();
if (!$colOrder) {
    $conn->query("ALTER TABLE farmhouse_rules ADD COLUMN sort_order INT(11) NOT NULL DEFAULT 0 AFTER description");
    echo "✓ Added 'sort_order' column to farmhouse_rules.\n";
}

$colIcon = $conn->query("SHOW COLUMNS FROM farmhouse_rules LIKE 'icon_class'")->fetch_assoc();
if (!$colIcon) {
    $conn->query("ALTER TABLE farmhouse_rules ADD COLUMN icon_class VARCHAR(50) NULL DEFAULT NULL AFTER sort_order");
    echo "✓ Added 'icon_class' column to farmhouse_rules.\n";
}

echo "Property Enhancements Migration completed successfully.\n";
