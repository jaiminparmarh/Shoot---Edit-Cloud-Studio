<?php
// Simple test file to check if PHP is working
echo "<h1>PHP Test - EditX Studio</h1>";
echo "<p>If you can see this, PHP is working!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test file permissions
$portal_file = 'index.php';
if (file_exists($portal_file)) {
    echo "<p style='color: green;'>✅ index.php exists</p>";
    echo "<p>File size: " . filesize($portal_file) . " bytes</p>";
    echo "<p>Last modified: " . date('Y-m-d H:i:s', filemtime($portal_file)) . "</p>";
} else {
    echo "<p style='color: red;'>❌ index.php not found</p>";
}

// Test database connection
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "<p style='color: green;'>✅ Database connection working</p>";
} catch (PDOException $e) {
    echo "<p style='color: orange;'>⚠️ Database connection: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
h1 { color: #333; }
p { margin: 10px 0; }
</style>
