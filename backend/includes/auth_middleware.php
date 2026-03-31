<?php
require_once __DIR__ . '/db.php';

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireAuth() {
    startSession();
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
        exit();
    }
    
    return $_SESSION['user_id'];
}

function getCurrentUser() {
    startSession();
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function login($userId) {
    startSession();
    $_SESSION['user_id'] = $userId;
    $_SESSION['login_time'] = time();
}

function logout() {
    startSession();
    session_unset();
    session_destroy();
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

