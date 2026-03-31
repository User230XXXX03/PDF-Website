<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    $db = getDB();
    
    echo "=== Check Database Templates ===\n\n";
    
    $stmt = $db->query("SELECT id, name, set_type, LENGTH(content) as content_length FROM templates");
    $templates = $stmt->fetchAll();
    
    foreach ($templates as $template) {
        echo "Template ID: {$template['id']}\n";
        echo "Name: {$template['name']}\n";
        echo "Type: {$template['set_type']}\n";
        echo "Content length: {$template['content_length']} bytes\n";
        
        if ($template['content_length'] == 0) {
            echo "⚠️ Warning: Template content is empty!\n";
        }
        
        echo "---\n\n";
    }
    
    echo "=== Check Batch Records ===\n\n";
    
    $stmt = $db->query("SELECT id, batch_id, LENGTH(data) as data_length FROM batch_records LIMIT 5");
    $records = $stmt->fetchAll();
    
    foreach ($records as $record) {
        echo "Record ID: {$record['id']}\n";
        echo "Batch ID: {$record['batch_id']}\n";
        echo "Data length: {$record['data_length']} bytes\n";
        
        if ($record['data_length'] == 0) {
            echo "⚠️ Warning: Record data is empty!\n";
        }
        
        echo "---\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

