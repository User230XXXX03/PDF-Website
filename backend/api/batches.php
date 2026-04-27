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
            getBatch($userId, $id);
        } else {
            getBatches($userId);
        }
        break;
    
    case 'POST':
        createBatch($userId);
        break;
    
    case 'PUT':
        if ($id) {
            updateBatch($userId, $id);
        } else {
            sendError('Batch ID required', 400);
        }
        break;
    
    case 'DELETE':
        if ($id) {
            deleteBatch($userId, $id);
        } else {
            sendError('Batch ID required', 400);
        }
        break;
    
    default:
        sendError('Method not allowed', 405);
}

function getBatches($userId) {
    $db = getDB();

    $isAdmin = isAdminUser($userId);

    $status = $_GET['status'] ?? '';

    if ($isAdmin) {
        $sql = "
            SELECT b.*, t.name as template_name, t.set_type, u.username as owner
            FROM batches b
            JOIN templates t ON b.template_id = t.id
            JOIN users u ON b.user_id = u.id
        ";
        $params = [];
    } else {
        $sql = "
            SELECT b.*, t.name as template_name, t.set_type
            FROM batches b
            JOIN templates t ON b.template_id = t.id
            WHERE b.user_id = ?
        ";
        $params = [$userId];
    }

    if ($status) {
        $sql .= ($isAdmin ? " WHERE" : " AND") . " b.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY b.created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $batches = $stmt->fetchAll();

    sendSuccess($batches);
}

function getBatch($userId, $id) {
    $db = getDB();

    $isAdmin = isAdminUser($userId);

    if ($isAdmin) {
        $stmt = $db->prepare("
            SELECT b.*, t.name as template_name, t.set_type, t.variables
            FROM batches b
            JOIN templates t ON b.template_id = t.id
            WHERE b.id = ?
        ");
        $stmt->execute([$id]);
    } else {
        $stmt = $db->prepare("
            SELECT b.*, t.name as template_name, t.set_type, t.variables
            FROM batches b
            JOIN templates t ON b.template_id = t.id
            WHERE b.id = ? AND b.user_id = ?
        ");
        $stmt->execute([$id, $userId]);
    }
    $batch = $stmt->fetch();
    
    if (!$batch) {
        sendError('Batch not found', 404);
    }
    
    // Get records
    $stmt = $db->prepare("SELECT * FROM batch_records WHERE batch_id = ? ORDER BY created_at ASC");
    $stmt->execute([$id]);
    $records = $stmt->fetchAll();
    
    // Decode JSON data for each record
    foreach ($records as &$record) {
        if ($record['data']) {
            $record['data'] = json_decode($record['data'], true);
        }
    }
    
    // Decode template variables
    if ($batch['variables']) {
        $batch['variables'] = json_decode($batch['variables'], true);
    }
    
    $batch['records'] = $records;
    
    sendSuccess($batch);
}

function createBatch($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    validateRequired(['name', 'template_id'], $data);
    
    $name = sanitizeInput($data['name']);
    $templateId = (int)$data['template_id'];
    $records = $data['records'] ?? [];
    $emailSubject = $data['email_subject'] ?? null;
    $emailBody = $data['email_body'] ?? null;
    
    $db = getDB();
    
    // Verify template exists. BatchEditor shows all templates, so any visible template can be used to create a batch.
    $stmt = $db->prepare("SELECT id FROM templates WHERE id = ?");
    $stmt->execute([$templateId]);
    if (!$stmt->fetch()) {
        sendError('Template not found', 404);
    }
    
    // Create batch
    $stmt = $db->prepare("
        INSERT INTO batches (user_id, template_id, name, total_count, email_subject, email_body) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $templateId, $name, count($records), $emailSubject, $emailBody]);
    
    $batchId = $db->lastInsertId();
    
    insertBatchRecords($db, $batchId, $records);
    
    logActivity($userId, $batchId, 'generation', "Batch created: $name", [
        'batch_id' => $batchId,
        'records_count' => count($records)
    ]);
    
    sendSuccess(['id' => $batchId], 'Batch created successfully');
}

function insertBatchRecords($db, $batchId, $records) {
    if (empty($records)) {
        return;
    }
    
    $stmt = $db->prepare("
        INSERT INTO batch_records (batch_id, student_name, student_email, data) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($records as $record) {
        $studentName = $record['student_name'] ?? '';
        $studentEmail = $record['student_email'] ?? '';
        
        $stmt->execute([
            $batchId,
            $studentName,
            $studentEmail,
            json_encode($record)
        ]);
    }
}

function updateBatch($userId, $id) {
    $data = json_decode(file_get_contents('php://input'), true);

    $db = getDB();

    $isAdmin = isAdminUser($userId);

    // Admin can view other users' batches in detail.
    $stmt = $db->prepare("SELECT * FROM batches WHERE id = ?");
    $stmt->execute([$id]);
    $batch = $stmt->fetch();
    
    if (!$batch) {
        sendError('Batch not found', 404);
    }

    if ((int)$batch['user_id'] !== (int)$userId) {
        if ($isAdmin) {
            sendError('Permission denied', 403);
        }
        sendError('Batch not found', 404);
    }
    
    $name = sanitizeInput($data['name'] ?? $batch['name']);
    $status = sanitizeInput($data['status'] ?? $batch['status']);
    $records = $data['records'] ?? null;
    
    // Email message fields are batch-level; recipients are stored per record.
    $emailSubject = isset($data['email_subject']) ? $data['email_subject'] : $batch['email_subject'];
    $emailBody = isset($data['email_body']) ? $data['email_body'] : $batch['email_body'];
    
    // Validate status
    $validStatuses = ['pending', 'processing', 'completed', 'failed'];
    if (!in_array($status, $validStatuses)) {
        sendError('Invalid status');
    }
    
    // Update batch.
    $stmt = $db->prepare("
        UPDATE batches 
        SET name = ?, status = ?, email_subject = ?, email_body = ? 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$name, $status, $emailSubject, $emailBody, $id, $userId]);
    
    // Update records if provided
    if ($records !== null) {
        // Delete existing records
        $stmt = $db->prepare("DELETE FROM batch_records WHERE batch_id = ?");
        $stmt->execute([$id]);
        
        insertBatchRecords($db, $id, $records);
        
        // Update total count
        $stmt = $db->prepare("UPDATE batches SET total_count = ? WHERE id = ?");
        $stmt->execute([count($records), $id]);
    }
    
    logActivity($userId, $id, 'generation', "Batch updated: $name");
    
    sendSuccess([], 'Batch updated successfully');
}

function deleteBatch($userId, $id) {
    $db = getDB();

    $isAdmin = isAdminUser($userId);

    // Admin can view other users' batches in detail.
    $stmt = $db->prepare("SELECT name, user_id FROM batches WHERE id = ?");
    $stmt->execute([$id]);
    $batch = $stmt->fetch();
    
    if (!$batch) {
        sendError('Batch not found', 404);
    }

    if ((int)$batch['user_id'] !== (int)$userId) {
        if ($isAdmin) {
            sendError('Permission denied', 403);
        }
        sendError('Batch not found', 404);
    }
    
    // Delete batch.
    $stmt = $db->prepare("DELETE FROM batches WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    
    logActivity($userId, null, 'generation', "Batch deleted: " . $batch['name'], ['batch_id' => $id]);
    
    sendSuccess([], 'Batch deleted successfully');
}

