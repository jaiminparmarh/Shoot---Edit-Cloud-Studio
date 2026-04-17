<?php
// Database Setup Script for EditX Studio
echo "<h2>EditX Studio - Database Setup</h2>";

try {
    // Connect to MySQL without database name first
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Connected to MySQL server</p>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS editing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✅ Database 'editing' created successfully</p>";
    
    // Select the database
    $pdo->exec("USE editing");
    
    // Create admins table (ONLY THIS - No bookings or contact_messages table)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p style='color: green;'>✅ Admins table created</p>";
    
    // Insert default admin user
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $hashed_password]);
    echo "<p style='color: green;'>✅ Default admin user created (username: admin, password: admin123)</p>";
    
    // Create indexes for better performance
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_admin_username ON admins(username)");
    echo "<p style='color: green;'>✅ Database indexes created</p>";
    
    echo "<h3 style='color: green;'>🎉 Database setup completed successfully!</h3>";
    echo "<p><strong>✅ Important Notes:</strong></p>";
    echo "<ul>";
    echo "<li><strong style='color: #27ae60;'>JSON Storage:</strong> Bookings and contact messages are stored in JSON files (bookings.json, contact_messages.json)</li>";
    echo "<li><strong style='color: #27ae60;'>No MySQL Tables:</strong> Only the ADMINS table exists in MySQL for login</li>";
    echo "<li><strong style='color: #27ae60;'>File Permissions:</strong> Ensure bookings.json and contact_messages.json have write permissions</li>";
    echo "</ul>";
    
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
h2 { color: #333; }
h3 { color: #27ae60; }
code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
a { color: #3498db; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
