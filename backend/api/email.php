<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_middleware.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$userId = requireAuth();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'send_batch':
        if ($method === 'POST' && $id) {
            sendBatchEmails($userId, $id);
        } else {
            sendError('Invalid request', 400);
        }
        break;
    
    case 'send_record':
        if ($method === 'POST' && $id) {
            sendRecordEmail($userId, $id);
        } else {
            sendError('Invalid request', 400);
        }
        break;
    
    case 'settings':
        if ($method === 'GET') {
            getEmailSettings($userId);
        } elseif ($method === 'PUT') {
            updateEmailSettings($userId);
        } else {
            sendError('Method not allowed', 405);
        }
        break;
    
    default:
        sendError('Invalid action', 400);
}

function sendBatchEmails($userId, $batchId) {
    $db = getDB();
    
    // Get batch (including email_recipients for non-personalized templates)
    $stmt = $db->prepare("
        SELECT b.*, t.name as template_name, t.set_type as template_type
        FROM batches b
        JOIN templates t ON b.template_id = t.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$batchId, $userId]);
    $batch = $stmt->fetch();
    
    if (!$batch) {
        sendError('Batch not found', 404);
    }
    
    // Get email settings
    $settings = getEmailSettingsData($userId);
    if (!$settings) {
        sendError('Email settings not configured. Please configure SMTP settings first.', 400);
    }
    
    // Get all records with generated PDFs
    $stmt = $db->prepare("
        SELECT * FROM batch_records 
        WHERE batch_id = ? AND pdf_generated = 1
    ");
    $stmt->execute([$batchId]);
    $records = $stmt->fetchAll();
    
    if (empty($records)) {
        sendError('No PDFs found. Please generate PDFs first.', 400);
    }
    
    $successCount = 0;
    $failCount = 0;
    
    foreach ($records as $record) {
        // Parse record data
        $recordData = [];
        if (!empty($record['data'])) {
            $recordData = is_string($record['data']) ? json_decode($record['data'], true) : $record['data'];
        }
        
        // Check if this is a class transcript (contains multiple students)
        $isClassTranscript = isset($recordData['STUDENT_1_ID']);
        
        if ($isClassTranscript) {
            // Class transcript: send individual email to each student
            for ($i = 1; $i <= 10; $i++) {
                $studentEmailKey = "STUDENT_{$i}_EMAIL";
                $studentEmail = $recordData[$studentEmailKey] ?? '';
                
                if (empty($studentEmail)) {
                    continue; // Skip students without email
                }
                
                // Create a copy of the record for this student
                $studentRecord = $record;
                $studentRecord['student_email'] = $studentEmail;
                $studentRecord['student_name'] = $recordData["STUDENT_{$i}_NAME"] ?? '';
                $studentRecord['student_id'] = $recordData["STUDENT_{$i}_ID"] ?? '';
                
                try {
                    sendEmail($settings, $studentRecord, $batch);
                    $successCount++;
                    
                    // Log successful email send
                    logActivity(
                        $userId, 
                        $batchId, 
                        'email', 
                        "Email sent to {$studentRecord['student_name']}",
                        ['record_id' => $record['id'], 'student_index' => $i],
                        $record['id'],
                        $studentRecord['student_name'],
                        $studentEmail
                    );
                } catch (Exception $e) {
                    $failCount++;
                    logActivity(
                        $userId, 
                        $batchId, 
                        'error', 
                        "Email send failed for {$studentRecord['student_name']}", 
                        [
                            'error' => $e->getMessage(),
                            'record_id' => $record['id'],
                            'student_index' => $i
                        ],
                        $record['id'],
                        $studentRecord['student_name'],
                        $studentEmail
                    );
                }
            }
            
            // Mark record as sent (if at least one succeeded)
            if ($successCount > 0) {
                $stmt = $db->prepare("UPDATE batch_records SET email_sent = 1 WHERE id = ?");
                $stmt->execute([$record['id']]);
            }
        } else {
            // Standard template: single record
            // Get correct email and name fields based on template type
            $templateType = $batch['template_type'] ?? '';
            $personEmail = '';
            $personName = '';
            $personId = '';
            
            // Select variables based on template type
            switch ($templateType) {
                case 'Payroll':
                    $personEmail = $recordData['EMPLOYEE_EMAIL'] ?? $record['student_email'] ?? '';
                    $personName = $recordData['EMPLOYEE_NAME'] ?? $record['student_name'] ?? '';
                    $personId = $recordData['EMPLOYEE_ID'] ?? $record['student_id'] ?? '';
                    break;
                case 'Transcript':
                    $personEmail = $recordData['STUDENT_EMAIL'] ?? $record['student_email'] ?? '';
                    $personName = $recordData['STUDENT_NAME'] ?? $record['student_name'] ?? '';
                    $personId = $recordData['STUDENT_ID'] ?? $record['student_id'] ?? '';
                    break;
                case 'Certificate':
                    $personEmail = $recordData['USER_EMAIL'] ?? $recordData['RECIPIENT_EMAIL'] ?? $record['student_email'] ?? '';
                    $personName = $recordData['USER_FULL_NAME'] ?? $recordData['RECIPIENT_NAME'] ?? $record['student_name'] ?? '';
                    $personId = $recordData['USER_ID'] ?? $record['student_id'] ?? '';
                    break;
                default:
                    // Try all possible email fields
                    $personEmail = $recordData['EMPLOYEE_EMAIL'] ?? $recordData['STUDENT_EMAIL'] ?? $recordData['USER_EMAIL'] ?? $record['student_email'] ?? '';
                    $personName = $recordData['EMPLOYEE_NAME'] ?? $recordData['STUDENT_NAME'] ?? $recordData['USER_FULL_NAME'] ?? $record['student_name'] ?? '';
                    $personId = $recordData['EMPLOYEE_ID'] ?? $recordData['STUDENT_ID'] ?? $recordData['USER_ID'] ?? $record['student_id'] ?? '';
            }
            
            if (empty($personEmail)) {
                $failCount++;
                error_log("Email send skipped: No email found for record {$record['id']}, template type: {$templateType}");
                continue;
            }
            
            // Update record object to ensure email is sent with correct info
            $record['student_email'] = $personEmail;
            $record['student_name'] = $personName;
            $record['student_id'] = $personId;
            
            try {
                sendEmail($settings, $record, $batch);
                
                // Update record
                $stmt = $db->prepare("UPDATE batch_records SET email_sent = 1 WHERE id = ?");
                $stmt->execute([$record['id']]);
                
                // Log successful email send
                logActivity(
                    $userId, 
                    $batchId, 
                    'email', 
                    "Email sent to {$personName}",
                    ['record_id' => $record['id']],
                    $record['id'],
                    $personName,
                    $personEmail
                );
                
                $successCount++;
            } catch (Exception $e) {
                $failCount++;
                logActivity(
                    $userId, 
                    $batchId, 
                    'error', 
                    "Email send failed for {$personName}", 
                    [
                        'error' => $e->getMessage(),
                        'record_id' => $record['id']
                    ],
                    $record['id'],
                    $personName,
                    $personEmail
                );
            }
        }
    }
    
    // Update batch
    $stmt = $db->prepare("UPDATE batches SET sent_count = ? WHERE id = ?");
    $stmt->execute([$successCount, $batchId]);
    
    // No batch summary log needed since each record has detailed logs
    
    sendSuccess([
        'success_count' => $successCount,
        'fail_count' => $failCount
    ], 'Email sending completed');
}

function sendRecordEmail($userId, $recordId) {
    $db = getDB();
    
    // Get record with batch info (including template type)
    $stmt = $db->prepare("
        SELECT br.*, b.*, t.name as template_name, t.set_type as template_type
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
    
    if (!$record['pdf_generated']) {
        sendError('PDF not generated yet', 400);
    }
    
    // Parse record data
    $recordData = [];
    if (!empty($record['data'])) {
        $recordData = is_string($record['data']) ? json_decode($record['data'], true) : $record['data'];
    }
    
    // Get correct email and name fields based on template type
    $templateType = $record['template_type'] ?? '';
    $personEmail = '';
    $personName = '';
    $personId = '';
    
    switch ($templateType) {
        case 'Payroll':
            $personEmail = $recordData['EMPLOYEE_EMAIL'] ?? $record['student_email'] ?? '';
            $personName = $recordData['EMPLOYEE_NAME'] ?? $record['student_name'] ?? '';
            $personId = $recordData['EMPLOYEE_ID'] ?? $record['student_id'] ?? '';
            break;
        case 'Transcript':
            $personEmail = $recordData['STUDENT_EMAIL'] ?? $record['student_email'] ?? '';
            $personName = $recordData['STUDENT_NAME'] ?? $record['student_name'] ?? '';
            $personId = $recordData['STUDENT_ID'] ?? $record['student_id'] ?? '';
            break;
        case 'Certificate':
            $personEmail = $recordData['USER_EMAIL'] ?? $recordData['RECIPIENT_EMAIL'] ?? $record['student_email'] ?? '';
            $personName = $recordData['USER_FULL_NAME'] ?? $recordData['RECIPIENT_NAME'] ?? $record['student_name'] ?? '';
            $personId = $recordData['USER_ID'] ?? $record['student_id'] ?? '';
            break;
        default:
            // Try all possible email fields
            $personEmail = $recordData['EMPLOYEE_EMAIL'] ?? $recordData['STUDENT_EMAIL'] ?? $recordData['USER_EMAIL'] ?? $record['student_email'] ?? '';
            $personName = $recordData['EMPLOYEE_NAME'] ?? $recordData['STUDENT_NAME'] ?? $recordData['USER_FULL_NAME'] ?? $record['student_name'] ?? '';
            $personId = $recordData['EMPLOYEE_ID'] ?? $recordData['STUDENT_ID'] ?? $recordData['USER_ID'] ?? $record['student_id'] ?? '';
    }
    
    if (empty($personEmail)) {
        sendError('No email address found for this record (template type: ' . $templateType . ')', 400);
    }
    
    // Update record object
    $record['student_email'] = $personEmail;
    $record['student_name'] = $personName;
    $record['student_id'] = $personId;
    
    // Get email settings
    $settings = getEmailSettingsData($userId);
    if (!$settings) {
        sendError('Email settings not configured', 400);
    }
    
    // Build batch object (with email config)
    $batch = [
        'email_subject' => $record['email_subject'],
        'email_body' => $record['email_body'],
        'template_name' => $record['template_name']
    ];
    
    try {
        sendEmail($settings, $record, $batch);
        
        // Update record
        $stmt = $db->prepare("UPDATE batch_records SET email_sent = 1 WHERE id = ?");
        $stmt->execute([$recordId]);
        
        logActivity(
            $userId, 
            $record['batch_id'], 
            'email', 
            "Email sent to {$personName}",
            ['record_id' => $recordId],
            $recordId,
            $personName,
            $personEmail
        );
        
        sendSuccess([], 'Email sent successfully');
    } catch (Exception $e) {
        logActivity(
            $userId, 
            $record['batch_id'], 
            'error', 
            "Email send failed for {$personName}",
            [
                'error' => $e->getMessage(),
                'record_id' => $recordId
            ],
            $recordId,
            $personName,
            $personEmail
        );
        sendError('Email send failed: ' . $e->getMessage(), 500);
    }
}

function sendEmail($settings, $record, $batch) {
    $mail = new PHPMailer(true);
    
    // Enable debug (can be disabled in production)
    // $mail->SMTPDebug = 2; // 0=off, 1=client messages, 2=client+server messages
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $settings['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $settings['smtp_username'];
    $mail->Password = $settings['smtp_password'];
    
    // Set encryption based on smtp_secure setting
    if (!empty($settings['smtp_secure'])) {
        // Enable SSL/TLS
        if ($settings['smtp_port'] == 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
        }
    } else {
        // No encryption
        $mail->SMTPSecure = false;
        $mail->SMTPAutoTLS = false;
    }
    
    $mail->Port = $settings['smtp_port'];
    
    // Set timeout
    $mail->Timeout = 30;
    
    // Recipients
    $mail->setFrom($settings['from_email'], $settings['from_name']);
    
    // Prefer record's student_email, fall back to batch email_recipients
    if (!empty($record['student_email'])) {
        // Personalized send: use student_email from record
        $mail->addAddress($record['student_email'], $record['student_name'] ?? '');
    } elseif (!empty($batch['email_recipients'])) {
        // Batch send: use batch email_recipients
        $toEmails = array_map('trim', explode(';', $batch['email_recipients']));
        foreach ($toEmails as $toEmail) {
            if (!empty($toEmail) && filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($toEmail);
            }
        }
    } else {
        throw new Exception('No recipients configured: neither student email nor batch recipients found');
    }
    
    // Attach PDF
    $filepath = UPLOAD_DIR . $record['pdf_path'];
    if (file_exists($filepath)) {
        $mail->addAttachment($filepath);
        error_log("PDF attachment added: $filepath");
    } else {
        error_log("WARNING: PDF file not found: $filepath");
    }
    
    // Parse record data for variable substitution
    $recordData = [];
    if (!empty($record['data'])) {
        $recordData = is_string($record['data']) ? json_decode($record['data'], true) : $record['data'];
    }
    
    // Get email subject and body, using batch config or defaults
    $subject = !empty($batch['email_subject']) ? $batch['email_subject'] : $batch['template_name'] . ' - ' . ($record['student_name'] ?? 'Document');
    $body = !empty($batch['email_body']) ? $batch['email_body'] : "
        <p>Dear " . htmlspecialchars($record['student_name'] ?? 'Student') . ",</p>
        <p>Please find attached your <strong>{$batch['template_name']}</strong> document.</p>
        <p>If you have any questions, please don't hesitate to contact us.</p>
        <p>Best regards,<br>" . htmlspecialchars($settings['from_name']) . "</p>
    ";
    
    // Add common person info variables (ensure all template types can use them)
    // These variables may come from different fields (student_name, student_email, etc.),
    // but we ensure all template types can find the corresponding variables
    if (!empty($record['student_name'])) {
        // Add all possible name variables (support different template types)
        if (!isset($recordData['STUDENT_NAME'])) $recordData['STUDENT_NAME'] = $record['student_name'];
        if (!isset($recordData['EMPLOYEE_NAME'])) $recordData['EMPLOYEE_NAME'] = $record['student_name'];
        if (!isset($recordData['USER_FULL_NAME'])) $recordData['USER_FULL_NAME'] = $record['student_name'];
    }
    
    if (!empty($record['student_id'])) {
        if (!isset($recordData['STUDENT_ID'])) $recordData['STUDENT_ID'] = $record['student_id'];
        if (!isset($recordData['EMPLOYEE_ID'])) $recordData['EMPLOYEE_ID'] = $record['student_id'];
        if (!isset($recordData['USER_ID'])) $recordData['USER_ID'] = $record['student_id'];
    }
    
    if (!empty($record['student_email'])) {
        if (!isset($recordData['STUDENT_EMAIL'])) $recordData['STUDENT_EMAIL'] = $record['student_email'];
        if (!isset($recordData['EMPLOYEE_EMAIL'])) $recordData['EMPLOYEE_EMAIL'] = $record['student_email'];
        if (!isset($recordData['USER_EMAIL'])) $recordData['USER_EMAIL'] = $record['student_email'];
    }
    
    // Replace template variables
    if ($recordData) {
        foreach ($recordData as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }
    }
    
    // Content
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8'; // Set charset to UTF-8 for international character support
    $mail->Subject = $subject;
    $mail->Body = nl2br(htmlspecialchars_decode($body)); // Convert newlines to <br> tags
    $mail->AltBody = strip_tags(str_replace('<br>', "\n", $mail->Body));
    
    // Log send details (for debugging)
    error_log("Sending email to: {$record['student_email']}, Subject: $subject");
    error_log("SMTP Host: {$settings['smtp_host']}, Port: {$settings['smtp_port']}, Username: {$settings['smtp_username']}");
    
    try {
        $mail->send();
        error_log("Email sent successfully to: {$record['student_email']}");
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        error_log("Exception: " . $e->getMessage());
        throw new Exception("Email send failed: " . $mail->ErrorInfo);
    }
}

function getEmailSettings($userId) {
    $settings = getEmailSettingsData($userId);
    
    if ($settings) {
        // Don't send password to frontend
        unset($settings['smtp_password']);
        sendSuccess($settings);
    } else {
        sendSuccess([]);
    }
}

function getEmailSettingsData($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM email_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function updateEmailSettings($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    validateRequired(['smtp_host', 'smtp_port', 'smtp_username', 'from_email', 'from_name'], $data);
    
    $smtpHost = sanitizeInput($data['smtp_host']);
    $smtpPort = (int)$data['smtp_port'];
    $smtpSecure = isset($data['smtp_secure']) ? (int)(bool)$data['smtp_secure'] : 0; // Convert boolean to int
    $smtpUsername = sanitizeInput($data['smtp_username']);
    $smtpPassword = $data['smtp_password'] ?? null; // Optional update
    $fromEmail = sanitizeInput($data['from_email']);
    $fromName = sanitizeInput($data['from_name']);
    
    if (!validateEmail($fromEmail)) {
        sendError('Invalid from email');
    }
    
    $db = getDB();
    
    // Check if settings exist
    $stmt = $db->prepare("SELECT id FROM email_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update
        if ($smtpPassword) {
            $stmt = $db->prepare("
                UPDATE email_settings 
                SET smtp_host = ?, smtp_port = ?, smtp_secure = ?, smtp_username = ?, smtp_password = ?, from_email = ?, from_name = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$smtpHost, $smtpPort, $smtpSecure, $smtpUsername, $smtpPassword, $fromEmail, $fromName, $userId]);
        } else {
            $stmt = $db->prepare("
                UPDATE email_settings 
                SET smtp_host = ?, smtp_port = ?, smtp_secure = ?, smtp_username = ?, from_email = ?, from_name = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$smtpHost, $smtpPort, $smtpSecure, $smtpUsername, $fromEmail, $fromName, $userId]);
        }
    } else {
        // Insert
        if (!$smtpPassword) {
            sendError('SMTP password required for initial setup');
        }
        
        $stmt = $db->prepare("
            INSERT INTO email_settings (user_id, smtp_host, smtp_port, smtp_secure, smtp_username, smtp_password, from_email, from_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $smtpHost, $smtpPort, $smtpSecure, $smtpUsername, $smtpPassword, $fromEmail, $fromName]);
    }
    
    sendSuccess([], 'Email settings updated successfully');
}

