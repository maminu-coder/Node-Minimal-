<?php
/**
 * Application Configuration File
 * 
 * Central configuration for the Industrial Portfolio application.
 * Contains constants, helper functions, and security settings.
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// ============================================
// Application Constants
// ============================================

define('APP_NAME', 'Dr. Muhammed Amin Abdullah - Portfolio');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost');

// Paths
define('BASE_PATH', APP_ROOT);
define('ASSETS_PATH', BASE_PATH . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/assets/uploads');
define('TEMPLATES_PATH', BASE_PATH . '/templates');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx']);

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'industrial_portfolio_session');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('HASH_COST', 12); // For password_hash

// Pagination
define('POSTS_PER_PAGE', 10);
define('PROJECTS_PER_PAGE', 9);
define('PUBLICATIONS_PER_PAGE', 15);

// ============================================
// Include Database Configuration
// ============================================

require_once BASE_PATH . '/config/db.php';

// ============================================
// Start Session Securely
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    
    session_name(SESSION_NAME);
    session_start();
}

// ============================================
// Helper Functions
// ============================================

/**
 * Sanitize input data
 * 
 * @param string $data The data to sanitize
 * @return string The sanitized data
 */
function sanitize(string $data): string {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Escape output for HTML
 * 
 * @param string $data The data to escape
 * @return string The escaped data
 */
function e(string $data): string {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 * 
 * @return string The generated token
 */
function generateCsrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 * 
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCsrfToken(string $token): bool {
    if (empty($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Redirect to a URL
 * 
 * @param string $url The URL to redirect to
 * @param int $statusCode HTTP status code
 */
function redirect(string $url, int $statusCode = 302): void {
    header("Location: {$url}", true, $statusCode);
    exit;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool True if admin, false otherwise
 */
function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Require login
 * Redirects to login page if not logged in
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('/admin/login.php');
    }
}

/**
 * Require admin
 * Redirects to login page if not admin
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        redirect('/admin/login.php');
    }
}

/**
 * Get current user ID
 * 
 * @return int|null The user ID or null if not logged in
 */
function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Format date for display
 * 
 * @param string $date The date to format
 * @param string $format The format string
 * @return string The formatted date
 */
function formatDate(string $date, string $format = 'M d, Y'): string {
    if (empty($date)) {
        return '';
    }
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : '';
}

/**
 * Generate slug from string
 * 
 * @param string $string The string to convert
 * @return string The slug
 */
function generateSlug(string $string): string {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Truncate text to specified length
 * 
 * @param string $text The text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add if truncated
 * @return string The truncated text
 */
function truncate(string $text, int $length = 150, string $suffix = '...'): string {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Validate email address
 * 
 * @param string $email The email to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Get file extension
 * 
 * @param string $filename The filename
 * @return string The extension
 */
function getFileExtension(string $filename): string {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Validate file upload
 * 
 * @param array $file The $_FILES array element
 * @param array $allowedTypes Allowed MIME types
 * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function validateFileUpload(array $file, array $allowedTypes = []): array {
    if (!isset($file['error']) || !is_int($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed'];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes, true)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $extension = getFileExtension($file['name']);
    if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
        return ['success' => false, 'message' => 'Invalid file extension'];
    }
    
    return [
        'success' => true,
        'message' => 'File uploaded successfully',
        'filename' => $file['name'],
        'mime_type' => $mimeType
    ];
}

/**
 * Generate unique filename
 * 
 * @param string $originalName The original filename
 * @return string The unique filename
 */
function generateUniqueFilename(string $originalName): string {
    $extension = getFileExtension($originalName);
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    $basename = preg_replace('/[^a-z0-9_-]/i', '', $basename);
    return uniqid($basename . '_', true) . '.' . $extension;
}

/**
 * Handle file upload
 * 
 * @param array $file The $_FILES array element
 * @param string $uploadDir The target directory
 * @param array $allowedTypes Allowed MIME types
 * @return array ['success' => bool, 'message' => string, 'filepath' => string|null]
 */
function handleFileUpload(array $file, string $uploadDir, array $allowedTypes = []): array {
    // Validate first
    $validation = validateFileUpload($file, $allowedTypes);
    if (!$validation['success']) {
        return $validation;
    }
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $newFilename = generateUniqueFilename($file['name']);
    $targetPath = rtrim($uploadDir, '/') . '/' . $newFilename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
    
    return [
        'success' => true,
        'message' => 'File uploaded successfully',
        'filepath' => $targetPath,
        'filename' => $newFilename,
        'url' => str_replace(BASE_PATH, '', $targetPath)
    ];
}

/**
 * Delete file
 * 
 * @param string $filepath The file path
 * @return bool True on success, false on failure
 */
function deleteFile(string $filepath): bool {
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * JSON response helper
 * 
 * @param mixed $data The data to encode
 * @param int $statusCode HTTP status code
 * @param string $message Optional message
 */
function jsonResponse($data, int $statusCode = 200, string $message = ''): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = [
        'success' => $statusCode >= 200 && $statusCode < 300,
        'data' => $data
    ];
    
    if (!empty($message)) {
        $response['message'] = $message;
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Log activity
 * 
 * @param string $action The action performed
 * @param string $details Additional details
 */
function logActivity(string $action, string $details = ''): void {
    $logFile = BASE_PATH . '/logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userId = getCurrentUserId() ?? 0;
    
    $logEntry = sprintf(
        "[%s] IP: %s | User: %d | Action: %s | Details: %s\n",
        $timestamp,
        $ip,
        $userId,
        $action,
        $details
    );
    
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
