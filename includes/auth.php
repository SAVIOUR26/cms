<?php
/**
 * KandaNews CMS — Authentication Helpers
 */
if (!defined('KANDA_CMS')) exit;

function authenticateUser($username, $password) {
    $db = getDatabase();
    $stmt = $db->prepare("SELECT id, username, password, full_name, role, status FROM users WHERE username = ? AND status = 'active'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['user_role'] = $user['role'];

    // Update last login
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);

    logActivity($user['id'], 'login', 'user', $user['id'], 'User logged in');

    return $user;
}

function checkLoginAttempts($identifier) {
    $db = getDatabase();
    $stmt = $db->prepare("
        SELECT COUNT(*) as attempts FROM activity_log
        WHERE action = 'login_failed'
        AND ip_address = ?
        AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");
    $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? 'unknown', LOCKOUT_TIME]);
    $result = $stmt->fetch();
    return ($result['attempts'] ?? 0) < MAX_LOGIN_ATTEMPTS;
}

function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'logout', 'user', $_SESSION['user_id'], 'User logged out');
    }
    session_destroy();
    session_start();
}
