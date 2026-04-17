<?php
// Local development configuration (used only on localhost by login.php)
// Do not upload this file to hosting.

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'editing');

// Optional local master admin fallback (works without database)
define('ADMIN_USERNAME', 'admin');
// Local login password: admin123
define('ADMIN_PASSWORD_HASH', '$2y$10$x.HxHxydCVoaVxZGTtd0cev82Sw9ZWj0NIwQTbpnNUSpHebl4/LP.');

// Local site and email settings for automatic thank-you messages
define('SITE_NAME', 'EditX Studio');
define('MAIL_FROM_EMAIL', 'no-reply@localhost');
define('MAIL_FROM_NAME', 'EditX Studio');
define('MAIL_REPLY_TO', 'editxstudio@gmail.com');

// Email delivery method for local testing
define('EMAIL_PROVIDER', 'mail');
define('BREVO_API_KEY', '');

// Discord admin notifications
define('ENABLE_DISCORD_NOTIFICATIONS', false);
define('DISCORD_WEBHOOK_URL', '');
define('DISCORD_SSL_VERIFY', true);
