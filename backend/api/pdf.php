<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_middleware.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../libs/tcpdf/tcpdf.php';

$userId = requireAuth();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

if ($method === 'POST') {
    if ($action === 'generate_batch' && $id) {
        generateBatchPDFs($userId, $id);
    } elseif ($action === 'generate_record' && $id) {
        generateRecordPDF($userId, $id);
    } else {
        sendError('Invalid action', 400);
    }
} elseif ($method === 'GET') {
    if ($action === 'preview' && $id) {
        previewPDF($userId, $id);
    } elseif ($action === 'download' && $id) {
        downloadPDF($userId, $id);
    } else {
        sendError('Invalid action', 400);
    }
} else {
    sendError('Method not allowed', 405);
}

function generateBatchPDFs($userId, $batchId) {
    $db = getDB();
    
    // Get batch with template
    $stmt = $db->prepare("
        SELECT b.*, t.content, t.name as template_name
        FROM batches b
        JOIN templates t ON b.template_id = t.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$batchId, $userId]);
    $batch = $stmt->fetch();
    
    if (!$batch) {
        sendError('Batch not found', 404);
    }
    
    // Update batch status
    $stmt = $db->prepare("UPDATE batches SET status = 'processing' WHERE id = ?");
    $stmt->execute([$batchId]);
    
    // Get all records
    $stmt = $db->prepare("SELECT * FROM batch_records WHERE batch_id = ?");
    $stmt->execute([$batchId]);
    $records = $stmt->fetchAll();
    
    $successCount = 0;
    $failCount = 0;
    
    foreach ($records as $record) {
        try {
            $pdfPath = generatePDF($record, $batch['content']);
            
            // Update record
            $stmt = $db->prepare("
                UPDATE batch_records 
                SET pdf_path = ?, pdf_generated = 1 
                WHERE id = ?
            ");
            $stmt->execute([$pdfPath, $record['id']]);
            
            [$recipientName, $recipientEmail] = getRecipientInfo($record);
            
            // Log successful PDF generation with details
            logActivity(
                $userId, 
                $batchId, 
                'generation', 
                "PDF generated" . ($recipientName ? " for {$recipientName}" : ""),
                ['record_id' => $record['id'], 'pdf_path' => $pdfPath],
                $record['id'],
                $recipientName,
                $recipientEmail
            );
            
            $successCount++;
        } catch (Exception $e) {
            $failCount++;
            
            [$recipientName, $recipientEmail] = getRecipientInfo($record);
            
            logActivity(
                $userId, 
                $batchId, 
                'error', 
                "PDF generation failed" . ($recipientName ? " for {$recipientName}" : ""),
                ['error' => $e->getMessage(), 'record_id' => $record['id']],
                $record['id'],
                $recipientName,
                $recipientEmail
            );
        }
    }
    
    $finalStatus = $failCount > 0 ? 'failed' : 'completed';
    
    $stmt = $db->prepare("
        UPDATE batches 
        SET status = ?, generated_count = ? 
        WHERE id = ?
    ");
    $stmt->execute([$finalStatus, $successCount, $batchId]);
    
    // No longer logging batch summary, as each record has detailed logs
    
    sendSuccess([
        'success_count' => $successCount,
        'fail_count' => $failCount
    ], 'PDF generation completed');
}

function generateRecordPDF($userId, $recordId) {
    $db = getDB();
    
    // Get record with batch and template
    $stmt = $db->prepare("
        SELECT br.*, b.user_id, t.content
        FROM batch_records br
        JOIN batches b ON br.batch_id = b.id
        JOIN templates t ON b.template_id = t.id
        WHERE br.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$recordId, $userId]);
    $record = $stmt->fetch();
    
    if (!$record) {
        sendError('Record not found', 404);
    }
    
    try {
        $pdfPath = generatePDF($record, $record['content']);
        
        // Update record
        $stmt = $db->prepare("
            UPDATE batch_records 
            SET pdf_path = ?, pdf_generated = 1 
            WHERE id = ?
        ");
        $stmt->execute([$pdfPath, $recordId]);
        
        [$recipientName, $recipientEmail] = getRecipientInfo($record);
        
        logActivity(
            $userId, 
            $record['batch_id'], 
            'generation', 
            "PDF generated" . ($recipientName ? " for {$recipientName}" : ""),
            ['record_id' => $recordId, 'pdf_path' => $pdfPath],
            $recordId,
            $recipientName,
            $recipientEmail
        );
        
        sendSuccess(['pdf_path' => $pdfPath], 'PDF generated successfully');
    } catch (Exception $e) {
        [$recipientName, $recipientEmail] = getRecipientInfo($record);
        
        logActivity(
            $userId, 
            $record['batch_id'], 
            'error', 
            "PDF generation failed" . ($recipientName ? " for {$recipientName}" : ""),
            ['error' => $e->getMessage(), 'record_id' => $recordId],
            $recordId,
            $recipientName,
            $recipientEmail
        );
        sendError('PDF generation failed: ' . $e->getMessage(), 500);
    }
}

function generatePDF($record, $templateContent) {
    $data = json_decode($record['data'], true);
    
    // Replace placeholders
    $html = replacePlaceholders($templateContent, $data);
    
    // Ensure HTML content is not empty
    if (empty($html)) {
        throw new Exception('Template content is empty');
    }
    
    // Create PDF with UTF-8 encoding
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator(APP_NAME);
    $pdf->SetAuthor(APP_NAME);
    $pdf->SetTitle('Generated Document');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Use standard font (no extra Chinese support)
    $pdf->SetFont('helvetica', '', 12);
    
    // Add page after setting font
    $pdf->AddPage();
    
    // Write HTML content with UTF-8 support
    // Use true parameter to ensure proper UTF-8 handling
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Generate filename
    $filename = generateFileName('doc');
    $filepath = UPLOAD_DIR . $filename;

    
    // Ensure upload directory exists
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
    
    // Output PDF to file
    $pdf->Output($filepath, 'F');
    
    return $filename;
}

function getRecipientInfo($record) {
    $recordData = json_decode($record['data'], true);
    
    return [
        $recordData['STUDENT_NAME'] ?? $recordData['EMPLOYEE_NAME'] ?? $recordData['USER_FULL_NAME'] ?? $recordData['USER_NAME'] ?? $recordData['RECIPIENT_NAME'] ?? $recordData['NAME'] ?? null,
        $recordData['STUDENT_EMAIL'] ?? $recordData['EMPLOYEE_EMAIL'] ?? $recordData['USER_EMAIL'] ?? $recordData['RECIPIENT_EMAIL'] ?? $recordData['EMAIL'] ?? null
    ];
}

function getPDFRecord($userId, $recordId, $includeStudentName = false) {
    $db = getDB();
    $selectFields = $includeStudentName ? 'br.pdf_path, br.student_name' : 'br.pdf_path';
    
    $stmt = $db->prepare("
        SELECT {$selectFields}
        FROM batch_records br
        JOIN batches b ON br.batch_id = b.id
        WHERE br.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$recordId, $userId]);
    $record = $stmt->fetch();
    
    if (!$record || !$record['pdf_path']) {
        sendError('PDF not found', 404);
    }
    
    $record['filepath'] = UPLOAD_DIR . $record['pdf_path'];
    
    if (!file_exists($record['filepath'])) {
        sendError('PDF file not found', 404);
    }
    
    return $record;
}

function previewPDF($userId, $recordId) {
    $record = getPDFRecord($userId, $recordId);
    $filepath = $record['filepath'];
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit();
}

function downloadPDF($userId, $recordId) {
    $record = getPDFRecord($userId, $recordId, true);
    $filepath = $record['filepath'];
    
    $downloadName = ($record['student_name'] ? sanitizeInput($record['student_name']) . '_' : '') . 'document.pdf';
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit();
}
