<?php
/**
 * SURATIN Application Configuration
 * Central configuration for application settings
 */

// Set timezone for entire application
date_default_timezone_set('Asia/Makassar');

// Application settings
define('APP_NAME', 'SURATIN');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, staging, production

// Timezone settings
define('APP_TIMEZONE', 'Asia/Makassar');
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y H:i');
define('DISPLAY_DATE_ONLY', 'd/m/Y');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);
define('UPLOAD_PATH', __DIR__ . '/../../uploads/');

// Pagination settings
define('DEFAULT_PAGE_SIZE', 10);
define('MAX_PAGE_SIZE', 100);

// Session settings
define('SESSION_LIFETIME', 3600 * 8); // 8 hours in seconds
define('SESSION_NAME', 'SURATIN_SESSION');

// Email settings (will be moved to .env in production)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@suratin.local');
define('FROM_NAME', 'SURATIN System');

// Debug settings
define('DEBUG_MODE', APP_ENV === 'development');
define('LOG_ERRORS', true);

// Initialize error reporting based on environment
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set session configuration only if session is not active
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true in production with HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

/**
 * Helper function to format date according to app settings
 */
function formatAppDate($date, $format = null) {
    if (empty($date)) return '-';
    
    $format = $format ?: DISPLAY_DATE_FORMAT;
    
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    $date->setTimezone(new DateTimeZone(APP_TIMEZONE));
    return $date->format($format);
}

/**
 * Helper function to get current datetime in app timezone
 */
function getCurrentDateTime($format = DATE_FORMAT) {
    $date = new DateTime('now', new DateTimeZone(APP_TIMEZONE));
    return $date->format($format);
}

/**
 * Helper function to convert UTC to app timezone
 */
function convertToAppTimezone($utcDateTime, $format = DISPLAY_DATE_FORMAT) {
    if (empty($utcDateTime)) return '-';
    
    $date = new DateTime($utcDateTime, new DateTimeZone('UTC'));
    $date->setTimezone(new DateTimeZone(APP_TIMEZONE));
    return $date->format($format);
}

/**
 * Helper function to get time ago format
 */
function getTimeAgo($datetime) {
    if (empty($datetime)) return '-';
    
    $date = new DateTime($datetime);
    $date->setTimezone(new DateTimeZone(APP_TIMEZONE));
    $now = new DateTime('now', new DateTimeZone(APP_TIMEZONE));
    
    $diff = $now->diff($date);
    
    if ($diff->days > 7) {
        return $date->format('d/m/Y');
    } elseif ($diff->days > 0) {
        return $diff->days . ' hari yang lalu';
    } elseif ($diff->h > 0) {
        return $diff->h . ' jam yang lalu';
    } elseif ($diff->i > 0) {
        return $diff->i . ' menit yang lalu';
    } else {
        return 'Baru saja';
    }
}
?>
