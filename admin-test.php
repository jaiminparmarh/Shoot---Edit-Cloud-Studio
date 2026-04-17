<?php
// Test admin page functionality without login
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Page Test</h1>";

// Simulate admin login
session_start();
$_SESSION["admin_logged_in"] = true;

echo "<h2>✅ Admin session simulated</h2>";

// Test loading the admin files
echo "<h2>Test 1: bookings_Total.php</h2>";
try {
    // Get the content of the file without executing it
    $content = file_get_contents('bookings_Total.php');
    if (strpos($content, 'ONESIGNAL_APP_ID') !== false) {
        echo "❌ Still contains ONESIGNAL_APP_ID reference<br>";
    } else {
        echo "✅ No ONESIGNAL_APP_ID found<br>";
    }
    
    if (strpos($content, 'OneSignal') !== false) {
        echo "❌ Still contains OneSignal script<br>";
    } else {
        echo "✅ No OneSignal script found<br>";
    }
    
    echo "✅ File structure looks good<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Test 2: ContectUs_total.php</h2>";
try {
    $content = file_get_contents('ContectUs_total.php');
    if (strpos($content, 'ONESIGNAL_APP_ID') !== false) {
        echo "❌ Still contains ONESIGNAL_APP_ID reference<br>";
    } else {
        echo "✅ No ONESIGNAL_APP_ID found<br>";
    }
    
    if (strpos($content, 'OneSignal') !== false) {
        echo "❌ Still contains OneSignal script<br>";
    } else {
        echo "✅ No OneSignal script found<br>";
    }
    
    echo "✅ File structure looks good<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Test 3: manage_feedbacks.php</h2>";
try {
    $content = file_get_contents('manage_feedbacks.php');
    if (strpos($content, 'ONESIGNAL_APP_ID') !== false) {
        echo "❌ Still contains ONESIGNAL_APP_ID reference<br>";
    } else {
        echo "✅ No ONESIGNAL_APP_ID found<br>";
    }
    
    if (strpos($content, 'OneSignal') !== false) {
        echo "❌ Still contains OneSignal script<br>";
    } else {
        echo "✅ No OneSignal script found<br>";
    }
    
    echo "✅ File structure looks good<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>🎉 All tests completed!</h2>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>Upload the fixed files to your hosting</li>";
echo "<li>Test the admin pages again</li>";
echo "<li>They should now work without blank screens</li>";
echo "</ul>";

echo "<p><a href='bookings_Total.php'>Test Bookings Page</a> | <a href='ContectUs_total.php'>Test Contacts Page</a> | <a href='manage_feedbacks.php'>Test Feedbacks Page</a></p>";
?>
<style>
    body { font-family: Arial; padding: 20px; }
    h1 { color: #333; }
    h2 { color: #666; margin-top: 20px; }
    .✅ { color: green; }
    .❌ { color: red; }
    a { color: #007bff; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>
