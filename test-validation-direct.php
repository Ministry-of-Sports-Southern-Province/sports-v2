<?php
require_once 'config/database.php';

// Test registration numbers
$testNumbers = [
    'දපස/ක්‍රිඩා/ගා/110',
    'දපස/ක්‍රිඩා/මා/110',
    'දපස/ක්‍රිඩා/හ/110',
    ' දපස/ක්‍රිඩා/ගා/110',  // with leading space
    'දපස/ක්‍රිඩා/ගා/110 ',  // with trailing space
    'දපස / ක්‍රිඩා / ගා / 110',  // with spaces
];

echo "Testing Registration Number Validation\n";
echo str_repeat("=", 60) . "\n\n";

foreach ($testNumbers as $regNumber) {
    echo "Testing: '" . $regNumber . "'\n";
    echo "Length: " . mb_strlen($regNumber) . " chars, " . strlen($regNumber) . " bytes\n";

    $trimmed = trim($regNumber);
    echo "Trimmed: '" . $trimmed . "'\n";
    echo "Trimmed Length: " . mb_strlen($trimmed) . " chars, " . strlen($trimmed) . " bytes\n";

    $isValid = validateRegNumberFormat($trimmed);
    echo "Valid format: " . ($isValid ? "YES" : "NO") . "\n";

    if ($isValid) {
        // Check if exists in database
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM clubs WHERE reg_number = :reg_number");
        $stmt->execute(['reg_number' => $trimmed]);
        $exists = $stmt->fetch();
        echo "Exists in DB: " . ($exists ? "YES" : "NO") . "\n";
    }

    echo str_repeat("-", 60) . "\n\n";
}

echo "\nChecking database content:\n";
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, reg_number, name FROM clubs");
$clubs = $stmt->fetchAll();

if (empty($clubs)) {
    echo "Database is EMPTY (0 clubs)\n";
} else {
    echo "Found " . count($clubs) . " clubs:\n";
    foreach ($clubs as $club) {
        echo "  - ID: {$club['id']}, Reg: {$club['reg_number']}, Name: {$club['name']}\n";
    }
}
