<?php
// Session management and authentication functions

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

/**
 * Get current admin user info
 */
function getCurrentAdmin()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'],
        'full_name' => $_SESSION['admin_full_name'] ?? $_SESSION['admin_username']
    ];
}

/**
 * Set admin session
 */
function setAdminSession($adminData)
{
    $_SESSION['admin_id'] = $adminData['id'];
    $_SESSION['admin_username'] = $adminData['username'];
    $_SESSION['admin_full_name'] = $adminData['full_name'];
    $_SESSION['admin_login_time'] = time();
}

/**
 * Clear admin session (logout)
 */
function clearAdminSession()
{
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_full_name']);
    unset($_SESSION['admin_login_time']);

    // Destroy session completely
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Verify password
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Hash password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}
