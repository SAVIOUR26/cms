<?php
/**
 * KandaNews Africa CMS - Configuration File (Harmonized)
 * Version: 2.0 - Simplified + Working
 */

// Prevent direct access
if (!defined('KANDA_CMS')) {
    define('KANDA_CMS', true);
}

// ==============================================
// DATABASE CONFIGURATION (Keep Your Settings!)
// ==============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'kandan_cms');
define('DB_USER', 'kandan_cms');
define('DB_PASS', 'Daphne@24');
define('DB_CHARSET', 'utf8mb4');

// ==============================================
// PATHS CONFIGURATION
// ==============================================
// Base paths
define('BASE_PATH', dirname(__FILE__));
define('UPLOADS_PATH', BASE_PATH . '/uploads/');
define('OUTPUT_PATH', BASE_PATH . '/output/');

// NEW: Simplified template paths
define('TEMPLATES_PATH', BASE_PATH . '/templates/pages/');

// OLD: Keep for backward compatibility (if needed)
define('GENERATOR_PATH', BASE_PATH . '/generator/');
define('ASSETS_PATH', BASE_PATH . '/assets/');

// URLs
define('BASE_URL', 'https://cms.kandanews.africa');
define('UPLOADS_URL', BASE_URL . '/uploads/');
define('OUTPUT_URL', BASE_URL . '/output/');
define('ASSETS_URL', BASE_URL . '/assets/');

// Edition delivery paths
define('EDITIONS_BASE_PATH', '/home/kandan/domains/ug.kandanews.africa/public_html/editions/');
define('EDITIONS_BASE_URL', 'https://ug.kandanews.africa/editions/');

// ==============================================
// CLAUDE AI CONFIGURATION (Keep for future use)
// ==============================================
define('CLAUDE_API_KEY', getenv('ANTHROPIC_API_KEY') ?: 'sk-ant-api03-XXXXX');
define('CLAUDE_MODEL', 'claude-sonnet-4-20250514');
define('CLAUDE_MAX_TOKENS', 4096);

// ==============================================
// SECURITY SETTINGS (Keep Your Settings!)
// ==============================================
define('SESSION_LIFETIME', 28800); // 8 hours
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 1800); // 30 minutes

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

// ==============================================
// FILE UPLOAD SETTINGS (Keep Your Settings!)
// ==============================================
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'webm', 'mov']);
define('ALLOWED_AUDIO_TYPES', ['mp3', 'wav', 'ogg', 'm4a']);
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx']);

// ==============================================
// EDITION SETTINGS
// ==============================================
define('DEFAULT_TEMPLATE', 'Modern Magazine');
define('DEFAULT_PAGES', 16);
define('MAX_PAGES', 30); // Updated to 30 as discussed
define('ARTICLES_PER_EDITION', 10);

// Daily themes
define('DAILY_THEMES', [
    'monday' => '💼 Money Moves Monday',
    'tuesday' => '🎓 Tech Tuesday',
    'wednesday' => '🎬 Culture Wednesday',
    'thursday' => '🏫 Campus Thursday',
    'friday' => '🎉 Freedom Friday',
    'saturday' => '🌟 Weekend Special',
    'sunday' => '🌟 Weekend Special'
]);

// ==============================================
// SUBSCRIPTION PRICING (UGX)
// ==============================================
define('PRICING', [
    'daily' => 500,
    'weekly' => 2500,
    'monthly' => 7500
]);

// ==============================================
// TIMEZONE & LOCALIZATION
// ==============================================
date_default_timezone_set('Africa/Kampala');
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'F j, Y');

// ==============================================
// ERROR REPORTING
// ==============================================
define('DEBUG_MODE', true); // Set to false in production

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}

// ==============================================
// DATABASE CONNECTION FUNCTION
// ==============================================
function getDatabase() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database Connection Failed: " . $e->getMessage());
            } else {
                die("Database Connection Failed. Please contact support.");
            }
        }
    }
    
    return $pdo;
}

// ==============================================
// HELPER FUNCTIONS (Keep All Your Functions!)
// ==============================================

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log activity
 */
function logActivity($user_id, $action, $entity_type = null, $entity_id = null, $description = null) {
    try {
        $db = getDatabase();
        $stmt = $db->prepare("
            INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $action,
            $entity_type,
            $entity_id,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        // Fail silently if activity_log table doesn't exist yet
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Get current country based on subdomain or session
 */
function getCurrentCountry() {
    // Check session first
    if (isset($_SESSION['country_code'])) {
        return $_SESSION['country_code'];
    }
    
    // Default to Uganda
    return 'ug';
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require login (redirect if not logged in)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Check if user table exists and has users
        try {
            $db = getDatabase();
            $stmt = $db->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            
            if ($result['count'] == 0) {
                // No users exist - auto-create admin for development
                if (DEBUG_MODE) {
                    createDefaultAdmin();
                }
            }
        } catch (PDOException $e) {
            // Tables might not exist yet - that's okay
        }
        
        // If still not logged in, redirect to login
        if (!isLoggedIn()) {
            // For now, auto-login as admin (development only)
            if (DEBUG_MODE && !isLoggedIn()) {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'admin';
                $_SESSION['user_role'] = 'admin';
                $_SESSION['country_code'] = 'ug';
            } else {
                header('Location: ' . BASE_URL . '/index.php');
                exit;
            }
        }
    }
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = getDatabase();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            return $user;
        }
    } catch (PDOException $e) {
        // If database query fails, return session data
    }
    
    // Fallback to session data
    return [
        'id' => $_SESSION['user_id'] ?? 1,
        'username' => $_SESSION['username'] ?? 'admin',
        'full_name' => $_SESSION['full_name'] ?? 'KandaNews Admin',
        'role' => $_SESSION['user_role'] ?? 'admin',
        'email' => 'admin@kandanews.africa'
    ];
}

/**
 * Create default admin user (for development)
 */
function createDefaultAdmin() {
    try {
        $db = getDatabase();
        
        // Check if admin already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = 'admin'");
        $stmt->execute();
        if ($stmt->fetch()) {
            return; // Admin already exists
        }
        
        // Create admin user
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, full_name, role, status)
            VALUES ('admin', 'admin@kandanews.africa', ?, 'System Administrator', 'admin', 'active')
        ");
        $stmt->execute([$password]);
        
        error_log("Default admin user created: admin/admin123");
    } catch (PDOException $e) {
        error_log("Could not create default admin: " . $e->getMessage());
    }
}

// ==============================================
// INITIALIZATION
// ==============================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create necessary directories if they don't exist
$required_dirs = [
    UPLOADS_PATH,
    OUTPUT_PATH,
    TEMPLATES_PATH,
    BASE_PATH . '/logs'
];

foreach ($required_dirs as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Test database connection on first load
if (DEBUG_MODE) {
    try {
        $db = getDatabase();
        // Connection successful
    } catch (Exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        // Don't die - let the system work in file-based mode
    }
}

// ==============================================
// END OF CONFIGURATION
// ==============================================
?>