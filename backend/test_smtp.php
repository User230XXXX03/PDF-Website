<?php
/**
 * Test SMTP email sending configuration
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<h1>SMTP Email Sending Test</h1>";
echo "<hr>";

try {
    $db = getDB();
    
    // Get email settings (using the first user's settings)
    $stmt = $db->query("SELECT * FROM email_settings LIMIT 1");
    $settings = $stmt->fetch();
    
    if (!$settings) {
        echo "<p style='color: red;'>❌ No email settings found. Please configure SMTP in the Settings page first.</p>";
        exit;
    }
    
    echo "<h2>📧 Current SMTP Configuration:</h2>";
    echo "<ul>";
    echo "<li><strong>SMTP Host:</strong> {$settings['smtp_host']}</li>";
    echo "<li><strong>SMTP Port:</strong> {$settings['smtp_port']}</li>";
    echo "<li><strong>SSL/TLS:</strong> " . ($settings['smtp_secure'] ? '✅ Enabled' : '❌ Disabled') . "</li>";
    echo "<li><strong>Username:</strong> {$settings['smtp_username']}</li>";
    echo "<li><strong>From Email:</strong> {$settings['from_email']}</li>";
    echo "<li><strong>From Name:</strong> {$settings['from_name']}</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h2>🧪 Sending test email...</h2>";
    
    $mail = new PHPMailer(true);
    
    // Enable debug output
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $settings['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $settings['smtp_username'];
    $mail->Password = $settings['smtp_password'];
    
    // Set encryption method based on smtp_secure
    if (!empty($settings['smtp_secure'])) {
        if ($settings['smtp_port'] == 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
        }
    } else {
        $mail->SMTPSecure = false;
        $mail->SMTPAutoTLS = false;
    }
    
    $mail->Port = $settings['smtp_port'];
    $mail->Timeout = 30;
    
    // Recipients
    $mail->setFrom($settings['from_email'], $settings['from_name']);
    $mail->addAddress($settings['smtp_username'], 'Test User'); // Send to self
    
    // Content
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Test Email - PDF Generator System';
    $mail->Body = '
        <h2>This is a test email</h2>
        <p>If you received this email, your SMTP configuration is correct!</p>
        <p>Sent at: ' . date('Y-m-d H:i:s') . '</p>
        <p>Enjoy using the system!</p>
    ';
    $mail->AltBody = 'This is a test email. If you received this, your SMTP configuration is correct!';
    
    $mail->send();
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✅ Email sent successfully!</h2>";
    echo "<p>Please check your inbox (it may be in the spam folder)</p>";
    echo "<p><strong>Recipient:</strong> {$settings['smtp_username']}</p>";
    
} catch (Exception $e) {
    echo "<hr>";
    echo "<h2 style='color: red;'>❌ Email sending failed!</h2>";
    echo "<p><strong>Error message:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";
    
    echo "<h3>💡 Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Some providers (e.g. 163.com) require an <strong>app-specific password</strong>, not the login password</li>";
    echo "<li>Make sure <strong>SMTP service is enabled</strong> in your email provider settings</li>";
    echo "<li>Port 465 requires SSL; port 25 does not require SSL</li>";
    echo "<li>Check if a firewall is blocking the SMTP port</li>";
    echo "<li>Ensure the Username is the full email address (e.g. user@example.com)</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='javascript:history.back()'>← Back</a> | <a href='javascript:location.reload()'>🔄 Retest</a></p>";

