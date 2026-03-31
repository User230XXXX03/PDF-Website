<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_middleware.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = requireAuth();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        if ($id) {
            getTemplate($userId, $id);
        } else {
            getTemplates($userId);
        }
        break;
    
    case 'POST':
        createTemplate($userId);
        break;
    
    case 'PUT':
        if ($id) {
            updateTemplate($userId, $id);
        } else {
            sendError('Template ID required', 400);
        }
        break;
    
    case 'DELETE':
        if ($id) {
            deleteTemplate($userId, $id);
        } else {
            sendError('Template ID required', 400);
        }
        break;
    
    default:
        sendError('Method not allowed', 405);
}

function getTemplates($userId) {
    $db = getDB();
    
    // Check if user is admin
    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    $isAdmin = ($user && $user['username'] === 'admin');
    
    // Check if requesting all templates (for batch creation)
    $allTemplates = isset($_GET['all']) && $_GET['all'] === 'true';
    
    $search = $_GET['search'] ?? '';
    $setType = $_GET['set_type'] ?? '';
    
    // If requesting all templates or user is admin, show all templates
    // Otherwise, show only user's own templates
    if ($allTemplates || $isAdmin) {
        $sql = "SELECT * FROM templates WHERE 1=1";
        $params = [];
    } else {
        $sql = "SELECT * FROM templates WHERE user_id = ?";
        $params = [$userId];
    }
    
    if ($search) {
        $sql .= " AND name LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($setType) {
        $sql .= " AND set_type = ?";
        $params[] = $setType;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $templates = $stmt->fetchAll();
    
    sendSuccess($templates);
}

function getTemplate($userId, $id) {
    $db = getDB();
    
    // Check if user is admin
    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    $isAdmin = ($user && $user['username'] === 'admin');
    
    // Admin can access any template, others can only access their own
    if ($isAdmin) {
        $stmt = $db->prepare("SELECT * FROM templates WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $db->prepare("SELECT * FROM templates WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
    }
    
    $template = $stmt->fetch();
    
    if (!$template) {
        sendError('Template not found', 404);
    }
    
    // Decode JSON variables
    if ($template['variables']) {
        $template['variables'] = json_decode($template['variables'], true);
    }
    
    sendSuccess($template);
}

function createTemplate($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    validateRequired(['name', 'content', 'set_type'], $data);
    
    $name = sanitizeInput($data['name']);
    $content = $data['content']; // Keep HTML as is
    $setType = sanitizeInput($data['set_type']);
    $variables = $data['variables'] ?? [];
    
    // Validate set_type
    $validTypes = ['Course Feedback', 'Certificate', 'Transcript', 'Payroll'];
    if (!in_array($setType, $validTypes)) {
        sendError('Invalid set type');
    }
    
    $db = getDB();
    
    $stmt = $db->prepare("
        INSERT INTO templates (user_id, name, content, variables, set_type) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        $name,
        $content,
        json_encode($variables),
        $setType
    ]);
    
    $templateId = $db->lastInsertId();
    
    logActivity($userId, null, 'generation', "Template created: $name", ['template_id' => $templateId]);
    
    sendSuccess(['id' => $templateId], 'Template created successfully');
}

function updateTemplate($userId, $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $db = getDB();
    
    // Check ownership
    $stmt = $db->prepare("SELECT id FROM templates WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) {
        sendError('Template not found', 404);
    }
    
    validateRequired(['name', 'content', 'set_type'], $data);
    
    $name = sanitizeInput($data['name']);
    $content = $data['content'];
    $setType = sanitizeInput($data['set_type']);
    $variables = $data['variables'] ?? [];
    
    // Validate set_type
    $validTypes = ['Course Feedback', 'Certificate', 'Transcript', 'Payroll'];
    if (!in_array($setType, $validTypes)) {
        sendError('Invalid set type');
    }
    
    $stmt = $db->prepare("
        UPDATE templates 
        SET name = ?, content = ?, variables = ?, set_type = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([
        $name,
        $content,
        json_encode($variables),
        $setType,
        $id,
        $userId
    ]);
    
    logActivity($userId, null, 'generation', "Template updated: $name", ['template_id' => $id]);
    
    sendSuccess([], 'Template updated successfully');
}

function deleteTemplate($userId, $id) {
    $db = getDB();
    
    // Check ownership
    $stmt = $db->prepare("SELECT name FROM templates WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    $template = $stmt->fetch();
    
    if (!$template) {
        sendError('Template not found', 404);
    }
    
    // Check if template is used in any batches
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM batches WHERE template_id = ?");
    $stmt->execute([$id]);
    $batchCount = $stmt->fetch()['count'];
    
    if ($batchCount > 0) {
        sendError("Cannot delete template. It is used in $batchCount batch(es)", 400);
    }
    
    $stmt = $db->prepare("DELETE FROM templates WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    
    logActivity($userId, null, 'generation', "Template deleted: " . $template['name'], ['template_id' => $id]);
    
    sendSuccess([], 'Template deleted successfully');
}

