<?php

/**
 * Translation API Endpoint
 * Provides a server-side fallback for loading translation files
 * Useful when direct static file access is having issues (e.g., 500 errors)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Get language parameter
$lang = isset($_GET['lang']) ? preg_replace('/[^a-z]/', '', $_GET['lang']) : 'si';

// Validate language code (whitelist)
$validLanguages = ['en', 'si', 'ta'];
if (!in_array($lang, $validLanguages)) {
    $lang = 'si';
}

// Build path to language file
$langFile = __DIR__ . '/../assets/lang/' . $lang . '.json';

// Check if file exists
if (!file_exists($langFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'Translation file not found']);
    exit;
}

// Read and serve the JSON file
$jsonContent = file_get_contents($langFile);

if ($jsonContent === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to read translation file']);
    exit;
}

// Verify it's valid JSON
$decoded = json_decode($jsonContent, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid JSON in translation file']);
    exit;
}

// Output the JSON
echo $jsonContent;
