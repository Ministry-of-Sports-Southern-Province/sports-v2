<?php

/**
 * Admin Setup Script
 * Run this script once to create/reset the admin user
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

try {
    $pdo = getDBConnection();

    // Create admin_users table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($createTable);
    echo "✓ Admin users table created/verified<br>";

    // Hash the password for admin123
    $username = 'admin';
    $password = 'admin123';
    $hashedPassword = hashPassword($password);
    $fullName = 'System Administrator';
    $email = 'admin@sports.gov.lk';

    // Check if admin user exists
    $checkStmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $checkStmt->execute([$username]);

    if ($checkStmt->fetch()) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE admin_users SET password = ?, full_name = ?, email = ?, is_active = 1 WHERE username = ?");
        $stmt->execute([$hashedPassword, $fullName, $email, $username]);
        echo "✓ Admin user updated successfully<br>";
    } else {
        // Insert new admin
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, $fullName, $email]);
        echo "✓ Admin user created successfully<br>";
    }

    echo "<br><strong>Admin credentials:</strong><br>";
    echo "Username: <code>admin</code><br>";
    echo "Password: <code>admin123</code><br><br>";
    echo "✓ Setup complete! You can now <a href='login.php'>login here</a><br><br>";
    echo "<em style='color: red;'>⚠️ For security, delete this setup-admin.php file after first login!</em>";
} catch (Exception $e) {
    echo "<strong style='color: red;'>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<br><strong>Troubleshooting:</strong><br>";
    echo "1. Check database connection in config/database.php<br>";
    echo "2. Ensure database exists<br>";
    echo "3. Verify database user has CREATE and INSERT permissions<br>";
}
?>


<!-- http://localhost/sports-v2/setup-admin.php -->