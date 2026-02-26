<?php

/**
 * Database Configuration
 * PDO connection with UTF-8 support for Sinhala/Tamil characters
 */

// Database credentials
define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'scms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection
 * @return PDO Database connection object
 * @throws PDOException if connection fails
 */
function getDBConnection()
{
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log error (in production, log to file instead of displaying)
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed. Please try again later.");
    }
}

/**
 * Set UTF-8 headers for proper character encoding
 */
function setUTF8Headers()
{
    header('Content-Type: application/json; charset=UTF-8');
}

/**
 * Send JSON response
 * @param bool $success Success status
 * @param mixed $data Data to return
 * @param string $message Optional message
 * @param int $httpCode HTTP status code (default 200)
 * @param array $extra Optional extra top-level fields to include in response
 */
function sendJSONResponse($success, $data = null, $message = '', $httpCode = 200, $extra = [])
{
    http_response_code($httpCode);
    setUTF8Headers();

    $response = [
        'success' => $success,
        'message' => $message
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    if (is_array($extra) && !empty($extra)) {
        // Prevent accidental overwrite of core fields
        unset($extra['success'], $extra['message'], $extra['data']);
        $response = array_merge($response, $extra);
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Validate and sanitize input
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate phone number (must be exactly 10 digits)
 * @param string $phone Phone number to validate
 * @return bool True if valid
 */
function validatePhone($phone)
{
    return preg_match('/^[0-9]{10}$/', $phone);
}

/**
 * Validate date format and ensure it's not in the future
 * @param string $date Date string
 * @param bool $allowFuture Allow future dates (default false)
 * @return bool True if valid
 */
function validateDate($date, $allowFuture = false)
{
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
        return false;
    }

    if (!$allowFuture) {
        $today = new DateTime();
        if ($dateObj > $today) {
            return false;
        }
    }

    return true;
}

/**
 * Validate registration number format
 * Format: දපස/ක්රීඩා/{ග|ම|හ}-{digits}
 * @param string $regNumber Registration number
 * @return bool True if valid format
 */
function validateRegNumberFormat($regNumber)
{
    // Pattern: දපස/ක්රීඩා/ followed by ග OR ම OR හ then - and digits (no whitespaces)
    $pattern = '/^දපස\/ක්‍රීඩා\/(ගා|මා|හ)\-[0-9]+$/u';
    return preg_match($pattern, $regNumber);
}
