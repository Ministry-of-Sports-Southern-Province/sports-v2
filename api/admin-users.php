<?php

/**
 * Admin Users Management API
 * Handles CRUD operations for admin and viewer accounts
 */

header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';
require_once '../includes/auth.php';

// Require login and admin role for all operations
requireLogin();
requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDBConnection();

    switch ($method) {
        case 'GET':
            handleGetUsers($pdo);
            break;
        case 'POST':
            handleCreateUser($pdo);
            break;
        case 'PUT':
            handleUpdateUser($pdo);
            break;
        case 'DELETE':
            handleDeleteUser($pdo);
            break;
        default:
            sendJSONResponse(false, null, 'Invalid request method', 405);
    }
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}

/**
 * Get all users (excluding password)
 */
function handleGetUsers($pdo)
{
    $stmt = $pdo->query("SELECT id, username, full_name, email, role, is_active, created_at, last_login FROM admin_users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendJSONResponse(true, $users);
}

/**
 * Create new user
 */
function handleCreateUser($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    $fullName = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $role = $data['role'] ?? 'viewer';
    $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

    // Validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if (empty($fullName)) {
        $errors[] = 'Full name is required';
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (!in_array($role, ['admin', 'viewer'])) {
        $errors[] = 'Invalid role';
    }
    
    if (!empty($errors)) {
        sendJSONResponse(false, null, implode(', ', $errors), 400);
    }
    
    // Check if username already exists
    $checkStmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $checkStmt->execute([$username]);
    if ($checkStmt->fetch()) {
        sendJSONResponse(false, null, 'Username already exists', 409);
    }
    
    // Hash password
    $hashedPassword = hashPassword($password);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, full_name, email, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $hashedPassword, $fullName, $email ?: null, $role, $isActive]);
    
    $userId = $pdo->lastInsertId();
    
    // Return user data (without password)
    $stmt = $pdo->prepare("SELECT id, username, full_name, email, role, is_active, created_at, last_login FROM admin_users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    sendJSONResponse(true, $user, 'User created successfully');
}

/**
 * Update user
 */
function handleUpdateUser($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = $data['id'] ?? null;
    $fullName = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $role = $data['role'] ?? null;
    $isActive = isset($data['is_active']) ? (int)$data['is_active'] : null;
    $password = $data['password'] ?? null;
    
    if (!$userId) {
        sendJSONResponse(false, null, 'User ID is required', 400);
    }
    
    // Check if user exists
    $checkStmt = $pdo->prepare("SELECT id FROM admin_users WHERE id = ?");
    $checkStmt->execute([$userId]);
    if (!$checkStmt->fetch()) {
        sendJSONResponse(false, null, 'User not found', 404);
    }
    
    $errors = [];
    
    if (!empty($fullName) && strlen($fullName) < 2) {
        $errors[] = 'Full name must be at least 2 characters';
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if ($role !== null && !in_array($role, ['admin', 'viewer'])) {
        $errors[] = 'Invalid role';
    }
    
    if ($password !== null && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if (!empty($errors)) {
        sendJSONResponse(false, null, implode(', ', $errors), 400);
    }
    
    // Build update query dynamically
    $updates = [];
    $params = [];
    
    if (!empty($fullName)) {
        $updates[] = "full_name = ?";
        $params[] = $fullName;
    }
    
    if ($email !== null) {
        $updates[] = "email = ?";
        $params[] = $email ?: null;
    }
    
    if ($role !== null) {
        $updates[] = "role = ?";
        $params[] = $role;
    }
    
    if ($isActive !== null) {
        $updates[] = "is_active = ?";
        $params[] = $isActive;
    }
    
    if ($password !== null) {
        $updates[] = "password = ?";
        $params[] = hashPassword($password);
    }
    
    if (empty($updates)) {
        sendJSONResponse(false, null, 'No fields to update', 400);
    }
    
    $params[] = $userId;
    
    $sql = "UPDATE admin_users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Return updated user data
    $stmt = $pdo->prepare("SELECT id, username, full_name, email, role, is_active, created_at, last_login FROM admin_users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    sendJSONResponse(true, $user, 'User updated successfully');
}

/**
 * Delete user
 */
function handleDeleteUser($pdo)
{
    $userId = $_GET['id'] ?? null;
    
    if (!$userId) {
        sendJSONResponse(false, null, 'User ID is required', 400);
    }
    
    // Prevent deleting yourself
    $currentAdmin = getCurrentAdmin();
    if ($currentAdmin && $currentAdmin['id'] == $userId) {
        sendJSONResponse(false, null, 'You cannot delete your own account', 400);
    }
    
    // Check if user exists
    $checkStmt = $pdo->prepare("SELECT id, username FROM admin_users WHERE id = ?");
    $checkStmt->execute([$userId]);
    $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        sendJSONResponse(false, null, 'User not found', 404);
    }
    
    // Delete user
    $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
    $stmt->execute([$userId]);
    
    sendJSONResponse(true, ['id' => $userId], 'User deleted successfully');
}
