<?php
/**
 * Clear PHP cache script
 */

echo "<h1>Clear PHP Cache</h1>";
echo "<hr>";

// 1. Clear opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ Opcache cleared<br>";
} else {
    echo "⚠ Opcache not enabled<br>";
}

// 2. Clear APCu cache
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✓ APCu cache cleared<br>";
} else {
    echo "⚠ APCu not enabled<br>";
}

// 3. Clear file status cache
clearstatcache(true);
echo "✓ File status cache cleared<br>";

// 4. Force reload pdf.php
if (file_exists(__DIR__ . '/api/pdf.php')) {
    $mtime = filemtime(__DIR__ . '/api/pdf.php');
    echo "✓ pdf.php last modified: " . date('Y-m-d H:i:s', $mtime) . "<br>";
    touch(__DIR__ . '/api/pdf.php');
    echo "✓ pdf.php timestamp updated<br>";
}

echo "<hr>";
echo "<h2>Cache cleared!</h2>";
echo "<p><strong>Please restart the PHP server now:</strong></p>";
echo "<ol>";
echo "<li>Press Ctrl+C in the PowerShell window to stop the server</li>";
echo "<li>Re-run: php -S localhost:8000</li>";
echo "</ol>";
echo "<p><a href='test_actual_generation.php' style='display: inline-block; padding: 10px 20px; background: #409EFF; color: white; text-decoration: none; border-radius: 4px;'>Test PDF Generation</a></p>";

