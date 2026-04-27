<?php

function sendResponse($success, $data = [], $message = '') {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit();
}

function sendError($message, $code = 400) {
    http_response_code($code);
    sendResponse(false, [], $message);
}

function sendSuccess($data = [], $message = '') {
    sendResponse(true, $data, $message);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function validateRequired($fields, $data) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendError('Missing required fields: ' . implode(', ', $missing), 400);
    }
}

function isAdminUser($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    return $user && $user['username'] === 'admin';
}

function logActivity($userId, $batchId, $type, $message, $details = [], $recordId = null, $recipientName = null, $recipientEmail = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO logs (user_id, batch_id, record_id, type, message, recipient_name, recipient_email, details) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $batchId,
            $recordId,
            $type,
            $message,
            $recipientName,
            $recipientEmail,
            json_encode($details)
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

function generateFileName($prefix = 'file') {
    return $prefix . '_' . time() . '_' . uniqid() . '.pdf';
}

function replacePlaceholders($template, $data) {
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    return $template;
}

