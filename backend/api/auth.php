<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_middleware.php';
require_once __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';

switch ($path) {
    case 'register':
        if ($method === 'POST') {
            register();
        }
        break;
    
    case 'login':
        if ($method === 'POST') {
            loginUser();
        }
        break;
    
    case 'logout':
        if ($method === 'POST') {
            logoutUser();
        }
        break;
    
    case 'user':
        if ($method === 'GET') {
            getUser();
        }
        break;
    
    default:
        sendError('Invalid endpoint', 404);
}

function register() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    validateRequired(['username', 'email', 'password'], $data);
    
    $username = sanitizeInput($data['username']);
    $email = sanitizeInput($data['email']);
    $password = $data['password'];
    
    // Validate email
    if (!validateEmail($email)) {
        sendError('Invalid email format');
    }
    
    // Validate password length
    if (strlen($password) < 6) {
        sendError('Password must be at least 6 characters');
    }
    
    $db = getDB();
    
    // Check if username exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        sendError('Username already exists');
    }
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        sendError('Email already exists');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword]);
    
    $userId = $db->lastInsertId();
    
    // Auto login
    login($userId);
    
    $user = [
        'id' => $userId,
        'username' => $username,
        'email' => $email
    ];
    
    sendSuccess($user, 'Registration successful');
}

function loginUser() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    validateRequired(['username', 'password'], $data);
    
    $username = sanitizeInput($data['username']);
    $password = $data['password'];
    
    $db = getDB();
    
    // Find user by username or email
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        sendError('Invalid credentials', 401);
    }
    
    // Login
    login($user['id']);
    
    unset($user['password']);
    
    sendSuccess($user, 'Login successful');
}

function logoutUser() {
    logout();
    sendSuccess([], 'Logout successful');
}

function getUser() {
    $userId = requireAuth();
    $user = getCurrentUser();
    
    if (!$user) {
        sendError('User not found', 404);
    }
    
    sendSuccess($user);
}

