<?php
// Database configuration for admin login
// Update these values with your hosting panel details

define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_USER', 'if0_xxxxxx');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'if0_xxxxxx_editning');

// Master Admin Credentials (works without database)
// Login Username: jaiminparmarh
// Login Password: Jaimin@3!!0
// 
// TO SET UP: Visit http://jaiminparmar.xo.je/generate-hash.php in your browser
// Copy the hash shown and paste it below, then delete this comment
define('ADMIN_USERNAME', 'jaiminparmarh');
define('ADMIN_PASSWORD_HASH', '$2y$10$lRbxjZStkgo.Ox3n5FCQe.9gmCcCmrJV3A5vU/5/uWj1SnnQ7bfs2');

// Site and email settings for automatic thank-you messages
define('SITE_NAME', 'EditX Studio');
define('MAIL_FROM_EMAIL', 'jaiminparmar3110@gmail.com');
define('MAIL_FROM_NAME', 'EditX Studio');
define('MAIL_REPLY_TO', 'editxstudio@gmail.com');

// Email delivery method
// 'mail' = PHP mail() (often blocked on free hosting)
// 'brevo' = Brevo transactional API via HTTPS (recommended for InfinityFree)
define('EMAIL_PROVIDER', 'brevo');

// Brevo transactional email API key (create at https://app.brevo.com)
define('BREVO_API_KEY', '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');

// Discord admin notifications (fully free phone push via Discord app)
define('ENABLE_DISCORD_NOTIFICATIONS', true);
define('DISCORD_WEBHOOK_URL', 'https://discord.com/api/webhooks/1487698243595931819/Jc3WmWMlPj3-9KI9OpyQVUoINMrUYr3bHxVsh96JyR0S1zwLI1phOUWK4-U6w7JRdlGC');
// Keep false on shared hosting if SSL certificate bundle issues appear.
define('DISCORD_SSL_VERIFY', false);