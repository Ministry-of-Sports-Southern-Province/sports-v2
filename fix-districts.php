<?php
require_once 'config/database.php';

$pdo = getDBConnection();

// Delete existing districts
$pdo->exec("DELETE FROM districts");

// Insert with proper UTF-8 characters
$stmt = $pdo->prepare("INSERT INTO districts (name, sinhala_letter) VALUES (?, ?)");

$districts = [
    ['Galle', 'ගා'],      // ග + ා
    ['Matara', 'මා'],     // ම + ා  
    ['Hambantota', 'හ']   // හ (single character)
];

foreach ($districts as $district) {
    $stmt->execute($district);
    echo "Inserted: {$district[0]} - {$district[1]}\n";
}

// Verify insertion
echo "\n" . str_repeat("=", 60) . "\n";
echo "Verification:\n";
echo str_repeat("=", 60) . "\n";

$stmt = $pdo->query("SELECT id, name, sinhala_letter, 
                     HEX(sinhala_letter) as hex_code,
                     CHAR_LENGTH(sinhala_letter) as char_len,
                     LENGTH(sinhala_letter) as byte_len
                     FROM districts");

while ($row = $stmt->fetch()) {
    echo "\nID: {$row['id']}\n";
    echo "Name: {$row['name']}\n";
    echo "Letter: {$row['sinhala_letter']}\n";
    echo "Hex: {$row['hex_code']}\n";
    echo "Char Length: {$row['char_len']}\n";
    echo "Byte Length: {$row['byte_len']}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Done!\n";
