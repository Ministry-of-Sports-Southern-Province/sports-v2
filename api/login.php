<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Check if already logged in
if (isLoggedIn()) {
    header('Location: ../public/dashboard.php');
    exit();
}

// Check if form submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    header('Location: ../login.php?error=required');
    exit();
}

try {
    $pdo = getDBConnection();

    // Get admin user
    $stmt = $pdo->prepare("SELECT id, username, password, full_name, is_active, role FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and password is correct
    if (!$admin || !verifyPassword($password, $admin['password'])) {
        header('Location: ../login.php?error=invalid');
        exit();
    }

    // Check if account is active
    if (!$admin['is_active']) {
        header('Location: ../login.php?error=inactive');
        exit();
    }

    // Update last login
    $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$admin['id']]);

    // Set session
    setAdminSession([
        'id' => $admin['id'],
        'username' => $admin['username'],
        'full_name' => $admin['full_name'],
        'role' => $admin['role'] ?? 'admin'
    ]);

    // Redirect to dashboard
    header('Location: ../public/dashboard.php');
    exit();
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    header('Location: ../login.php?error=system');
    exit();
}
