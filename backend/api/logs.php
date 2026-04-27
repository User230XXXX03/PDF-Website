<?php
/**
 * Logs Management API
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_middleware.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = requireAuth();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    getLogs($userId);
} else {
    sendError('Method not allowed', 405);
}

function getLogs($userId) {
    $db = getDB();
    
    // Get query parameters
    $page = $_GET['page'] ?? 1;
    $pageSize = $_GET['page_size'] ?? 20;
    $type = $_GET['type'] ?? '';
    $search = $_GET['search'] ?? '';
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    $export = $_GET['export'] ?? '';
    
    // Calculate offset
    $offset = ($page - 1) * $pageSize;
    
    $isAdmin = isAdminUser($userId);

    $whereClauses = [];
    $params = [];

    if (!$isAdmin) {
        $whereClauses[] = 'user_id = ?';
        $params[] = $userId;
    }
    
    if ($type) {
        $whereClauses[] = 'type = ?';
        $params[] = $type;
    }
    
    if ($search) {
        $whereClauses[] = 'message LIKE ?';
        $params[] = "%$search%";
    }
    
    if ($startDate) {
        $whereClauses[] = 'DATE(created_at) >= ?';
        $params[] = $startDate;
    }
    
    if ($endDate) {
        $whereClauses[] = 'DATE(created_at) <= ?';
        $params[] = $endDate;
    }
    
    $whereClause = $whereClauses ? implode(' AND ', $whereClauses) : '1=1';
    
    // Export as CSV if requested
    if ($export === 'csv') {
        exportLogsAsCSV($db, $whereClause, $params);
        return;
    }
    
    // Get total count
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM logs WHERE $whereClause");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Get logs list
    $sql = "SELECT id, user_id, batch_id, record_id, type, message, recipient_name, recipient_email, details, created_at 
            FROM logs 
            WHERE $whereClause 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = (int)$pageSize;
    $params[] = (int)$offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
    
    // Format details field
    foreach ($logs as &$log) {
        if (!empty($log['details'])) {
            $log['details'] = json_decode($log['details'], true);
        }
    }
    
    sendSuccess([
        'logs' => $logs,
        'total' => (int)$total,
        'page' => (int)$page,
        'page_size' => (int)$pageSize
    ]);
}

function exportLogsAsCSV($db, $whereClause, $params) {
    // Get all logs (no pagination)
    $sql = "SELECT id, user_id, batch_id, record_id, type, message, recipient_name, recipient_email, created_at 
            FROM logs 
            WHERE $whereClause 
            ORDER BY created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
    
    // Set CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="logs_' . date('Y-m-d_His') . '.csv"');
    
    // Output BOM for Excel UTF-8 compatibility
    echo "\xEF\xBB\xBF";
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write header row
    fputcsv($output, ['ID', 'User ID', 'Batch ID', 'Record ID', 'Type', 'Message', 'Recipient Name', 'Recipient Email', 'Time']);
    
    // Write data rows
    foreach ($logs as $log) {
        $typeNames = [
            'generation' => 'PDF Generation',
            'email' => 'Email Sent',
            'error' => 'Error'
        ];
        
        fputcsv($output, [
            $log['id'],
            $log['user_id'] ?: '-',
            $log['batch_id'] ?: '-',
            $log['record_id'] ?: '-',
            $typeNames[$log['type']] ?? $log['type'],
            $log['message'],
            $log['recipient_name'] ?: '-',
            $log['recipient_email'] ?: '-',
            $log['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}
