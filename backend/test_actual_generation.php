<?php
/**
 * Test actual PDF generation flow
 * Simulates reading data from the database and generating a PDF
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/libs/tcpdf/tcpdf.php';

echo "<h1>Test Actual PDF Generation Flow</h1>";
echo "<hr>";

try {
    $db = getDB();
    
    // 1. Fetch a batch record
    echo "<h2>1. Fetch Batch Record from Database</h2>";
    $stmt = $db->query("SELECT * FROM batch_records LIMIT 1");
    $record = $stmt->fetch();
    
    if (!$record) {
        die("❌ No batch record found");
    }
    
    echo "✓ Record ID: {$record['id']}<br>";
    echo "✓ Batch ID: {$record['batch_id']}<br>";
    
    // 2. Fetch template
    echo "<h2>2. Fetch Template Content</h2>";
    $stmt = $db->prepare("
        SELECT t.content, t.name 
        FROM templates t
        JOIN batches b ON t.id = b.template_id
        WHERE b.id = ?
    ");
    $stmt->execute([$record['batch_id']]);
    $template = $stmt->fetch();
    
    if (!$template) {
        die("❌ Template not found");
    }
    
    echo "✓ Template name: {$template['name']}<br>";
    echo "✓ Template content length: " . strlen($template['content']) . " bytes<br>";
    
    // 3. Parse data
    echo "<h2>3. Parse Record Data</h2>";
    $data = json_decode($record['data'], true);
    echo "✓ Data: <pre>" . print_r($data, true) . "</pre>";
    
    // 4. Replace placeholders
    echo "<h2>4. Replace Placeholders</h2>";
    $html = $template['content'];
    foreach ($data as $key => $value) {
        $html = str_replace('{{' . $key . '}}', $value, $html);
    }
    echo "✓ Replaced HTML length: " . strlen($html) . " bytes<br>";
    echo "✓ HTML preview:<br>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; max-height: 300px; overflow: auto;'>";
    echo "<pre>" . htmlspecialchars(substr($html, 0, 500)) . "...</pre>";
    echo "</div>";
    
    // 5. Generate PDF (using the exact same logic as pdf.php)
    echo "<h2>5. Generate PDF</h2>";
    
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('PDF Generator');
    $pdf->SetAuthor('Test');
    $pdf->SetTitle('Test Document');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page first
    $pdf->AddPage();
    
    // Set font (same logic as pdf.php)
    $fontSet = false;
    $fontUsed = '';
    
    $windowsFonts = [
        'C:/Windows/Fonts/simhei.ttf',
        'C:/Windows/Fonts/simsun.ttc',
        'C:/Windows/Fonts/msyh.ttc',
    ];
    
    foreach ($windowsFonts as $fontPath) {
        if (file_exists($fontPath)) {
            try {
                $fontName = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 96);
                if ($fontName) {
                    $pdf->SetFont($fontName, '', 12);
                    $fontUsed = $fontName;
                    $fontSet = true;
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }
    }
    
    if (!$fontSet) {
        $builtinFonts = ['stsongstdlight', 'cid0cs', 'msungstdlight', 'freeserif', 'dejavusans'];
        foreach ($builtinFonts as $font) {
            try {
                $pdf->SetFont($font, '', 12);
                $fontUsed = $font;
                $fontSet = true;
                break;
            } catch (Exception $e) {
                continue;
            }
        }
    }
    
    if (!$fontSet) {
        $pdf->SetFont('helvetica', '', 12);
        $fontUsed = 'helvetica';
    }
    
    echo "✓ Font used: {$fontUsed}<br>";
    
    // Wrap HTML with font style
    $styledHtml = '<style>
        * { font-family: "' . $fontUsed . '", sans-serif; }
        body, p, div, span, td, th, h1, h2, h3, h4, h5, h6, li, ul, ol { 
            font-family: "' . $fontUsed . '", sans-serif !important; 
        }
    </style>' . $html;
    
    // Write HTML content
    $pdf->writeHTML($styledHtml, true, false, true, false, '');
    echo "✓ HTML content written to PDF<br>";
    
    // Save file
    $uploadDir = __DIR__ . '/uploads/pdfs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $testFile = $uploadDir . 'actual_test_' . time() . '.pdf';
    $pdf->Output($testFile, 'F');
    
    if (file_exists($testFile)) {
        $fileSize = filesize($testFile);
        echo "✓ PDF file generated successfully<br>";
        echo "File path: {$testFile}<br>";
        echo "File size: " . number_format($fileSize) . " bytes<br>";
        
        echo "<br>";
        echo "<a href='uploads/pdfs/" . basename($testFile) . "' target='_blank' style='display: inline-block; padding: 10px 20px; background: #409EFF; color: white; text-decoration: none; border-radius: 4px;'>Download Test PDF</a>";
    } else {
        echo "❌ PDF file generation failed<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Location: " . $e->getFile() . " line " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>This script uses the exact same logic as the actual system:</p>";
echo "<ul>";
echo "<li>Reads real batch records from the database</li>";
echo "<li>Reads real templates from the database</li>";
echo "<li>Uses the same placeholder replacement logic</li>";
echo "<li>Uses the same PDF generation code</li>";
echo "</ul>";
echo "<p><strong>If this test PDF renders correctly but the system-generated one is still garbled, it is a PHP cache issue.</strong></p>";

