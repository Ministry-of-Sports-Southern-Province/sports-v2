<?php
require_once 'config/database.php';

$pdo = getDBConnection();

// First, clean up old districts and recreate with proper IDs starting from 1
echo "Cleaning up and recreating location data...\n";
echo str_repeat("=", 60) . "\n\n";

// Start transaction
$pdo->beginTransaction();

try {
    // Delete all existing data (cascading will handle related records)
    $pdo->exec("DELETE FROM clubs");
    $pdo->exec("DELETE FROM grama_niladhari_divisions");
    $pdo->exec("DELETE FROM divisions");
    $pdo->exec("DELETE FROM districts");

    // Reset auto-increment
    $pdo->exec("ALTER TABLE districts AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE divisions AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE grama_niladhari_divisions AUTO_INCREMENT = 1");

    // Insert districts with proper Sinhala letters
    $stmt = $pdo->prepare("INSERT INTO districts (name, sinhala_letter) VALUES (?, ?)");

    $districts = [
        ['Galle', 'ගා'],
        ['Matara', 'මා'],
        ['Hambantota', 'හ']
    ];

    foreach ($districts as $district) {
        $stmt->execute($district);
        echo "✓ Created district: {$district[0]} ({$district[1]})\n";
    }

    $pdo->commit();

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "Success! All location data cleaned and districts recreated.\n";
    echo "\nYou can now:\n";
    echo "1. Register clubs and create divisions/GN divisions as needed\n";
    echo "2. Or manually add divisions through the locations management page\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
