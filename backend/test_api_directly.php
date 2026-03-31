<?php
/**
 * Direct API call test
 * Simulates frontend calling the generate_batch API
 */

echo "<h1>Direct API Call Test</h1>";
echo "<hr>";

// Simulate logged-in user
$_SESSION['user_id'] = 1; // Assume user ID is 1

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'generate_batch';

// Get a batch ID
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/includes/db.php';

    try {
        $db = getDB();
        $stmt = $db->query("SELECT id, name FROM batches LIMIT 1");
        $batch = $stmt->fetch();
        
        if (!$batch) {
            die("❌ No batch found");
        }
        
        echo "<h2>Test Batch</h2>";
        echo "Batch ID: {$batch['id']}<br>";
        echo "Batch Name: {$batch['name']}<br>";
        echo "<hr>";
        
        $_GET['id'] = $batch['id'];
        
        echo "<h2>Generating PDF...</h2>";
        echo "<p>Calling API code directly...</p>";
    
    // Include API file
    ob_start();
    include __DIR__ . '/api/pdf.php';
    $output = ob_get_clean();
    
    echo "<h3>API Response:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Parse JSON response
    $response = json_decode($output, true);
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "<h3 style='color: green;'>✓ Success!</h3>";
        echo "<p>Generated: {$response['data']['success_count']}</p>";
        echo "<p>Failed: {$response['data']['fail_count']}</p>";
        
        // Check generated PDFs
        echo "<hr>";
        echo "<h3>Checking generated PDF files:</h3>";
        $stmt = $db->prepare("SELECT * FROM batch_records WHERE batch_id = ? AND pdf_generated = 1 LIMIT 3");
        $stmt->execute([$batch['id']]);
        $records = $stmt->fetchAll();
        
        echo "<ul>";
        foreach ($records as $record) {
            if ($record['pdf_path']) {
                $filepath = __DIR__ . '/uploads/pdfs/' . $record['pdf_path'];
                $exists = file_exists($filepath);
                $size = $exists ? filesize($filepath) : 0;
                
                echo "<li>";
                echo "Record ID: {$record['id']}<br>";
                echo "PDF File: " . ($exists ? "✓ Exists" : "❌ Not found") . "<br>";
                echo "File Size: " . number_format($size) . " bytes<br>";
                
                if ($exists) {
                    echo "<a href='uploads/pdfs/{$record['pdf_path']}' target='_blank' style='display: inline-block; margin-top: 5px; padding: 5px 10px; background: #409EFF; color: white; text-decoration: none; border-radius: 3px;'>Download & View</a>";
                }
                echo "</li><br>";
            }
        }
        echo "</ul>";
    } else {
        echo "<h3 style='color: red;'>❌ Failed!</h3>";
        if ($response && isset($response['message'])) {
            echo "<p>Error: {$response['message']}</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error occurred</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Notes</h2>";
echo "<p>This script directly calls the code in pdf.php, fully simulating the frontend API call.</p>";
echo "<p><strong>If the PDF generated here is correct, the backend is working fine.</strong></p>";
echo "<p><strong>If there are still garbled characters, try restarting the PHP server.</strong></p>";

