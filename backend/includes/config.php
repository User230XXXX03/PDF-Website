<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pdf_generator');

// Application Configuration
define('APP_NAME', 'PDF Generator System');
define('UPLOAD_DIR', __DIR__ . '/../uploads/pdfs/');

// Session Configuration
define('SESSION_LIFETIME', 3600 * 24); // 24 hours

// CORS Configuration
define('ALLOWED_ORIGIN', 'http://localhost:5173'); // Vite dev server

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Enable CORS
header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

