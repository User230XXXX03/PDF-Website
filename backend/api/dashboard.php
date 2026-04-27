<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_middleware.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = requireAuth();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    getDashboardStats($userId);
} else {
    sendError('Method not allowed', 405);
}

function getDashboardStats($userId) {
    $db = getDB();
    
    $isAdmin = isAdminUser($userId);
    
    // Get total templates
    if ($isAdmin) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM templates");
        $templatesCount = $stmt->fetch()['count'];
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM templates WHERE user_id = ?");
        $stmt->execute([$userId]);
        $templatesCount = $stmt->fetch()['count'];
    }
    
    // Get total batches
    if ($isAdmin) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM batches");
        $batchesCount = $stmt->fetch()['count'];
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM batches WHERE user_id = ?");
        $stmt->execute([$userId]);
        $batchesCount = $stmt->fetch()['count'];
    }
    
    // Get total PDFs generated
    if ($isAdmin) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM batch_records WHERE pdf_generated = 1");
        $pdfsGenerated = $stmt->fetch()['count'];
    } else {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM batch_records br
            JOIN batches b ON br.batch_id = b.id
            WHERE b.user_id = ? AND br.pdf_generated = 1
        ");
        $stmt->execute([$userId]);
        $pdfsGenerated = $stmt->fetch()['count'];
    }
    
    // Get total emails sent
    if ($isAdmin) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM batch_records WHERE email_sent = 1");
        $emailsSent = $stmt->fetch()['count'];
    } else {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM batch_records br
            JOIN batches b ON br.batch_id = b.id
            WHERE b.user_id = ? AND br.email_sent = 1
        ");
        $stmt->execute([$userId]);
        $emailsSent = $stmt->fetch()['count'];
    }
    
    // Get recent batches
    if ($isAdmin) {
        $stmt = $db->query("
            SELECT b.*, t.name as template_name, u.username
            FROM batches b
            JOIN templates t ON b.template_id = t.id
            JOIN users u ON b.user_id = u.id
            ORDER BY b.created_at DESC
            LIMIT 5
        ");
        $recentBatches = $stmt->fetchAll();
    } else {
        $stmt = $db->prepare("
            SELECT b.*, t.name as template_name
            FROM batches b
            JOIN templates t ON b.template_id = t.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $recentBatches = $stmt->fetchAll();
    }
    
    // Get recent logs
    if ($isAdmin) {
        $stmt = $db->query("
            SELECT l.*, u.username
            FROM logs l
            JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT 10
        ");
        $recentLogs = $stmt->fetchAll();
    } else {
        $stmt = $db->prepare("
            SELECT * FROM logs
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $recentLogs = $stmt->fetchAll();
    }
    
    $stats = [
        'templates_count' => (int)$templatesCount,
        'batches_count' => (int)$batchesCount,
        'pdfs_generated' => (int)$pdfsGenerated,
        'emails_sent' => (int)$emailsSent,
        'recent_batches' => $recentBatches,
        'recent_logs' => $recentLogs,
        'is_admin' => $isAdmin
    ];
    
    sendSuccess($stats);
}

