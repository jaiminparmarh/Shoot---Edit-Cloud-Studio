<?php
/**
 * JSON Database Functions
 * Handles all JSON file operations for bookings and contact messages
 */

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'config.php')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
}

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'EditX Studio');
}

if (!defined('MAIL_FROM_EMAIL')) {
    define('MAIL_FROM_EMAIL', 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
}

if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', SITE_NAME);
}

if (!defined('MAIL_REPLY_TO')) {
    define('MAIL_REPLY_TO', 'editxstudio@gmail.com');
}

if (!defined('ENABLE_DISCORD_NOTIFICATIONS')) {
    define('ENABLE_DISCORD_NOTIFICATIONS', false);
}

if (!defined('DISCORD_WEBHOOK_URL')) {
    define('DISCORD_WEBHOOK_URL', '');
}


function jsonFilePath($filename) {
    return __DIR__ . DIRECTORY_SEPARATOR . $filename;
}

function readJsonArrayFile($filename) {
    $path = jsonFilePath($filename);

    if (!file_exists($path)) {
        if (file_put_contents($path, "[]") === false) {
            return [];
        }
    }

    $content = file_get_contents($path);
    if ($content === false || trim($content) === '') {
        return [];
    }

    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [];
}

function writeJsonArrayFile($filename, $data) {
    $path = jsonFilePath($filename);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }

    return file_put_contents($path, $json, LOCK_EX) !== false;
}

function appendJsonLogFile($filename, $entry) {
    $rows = readJsonArrayFile($filename);
    $rows[] = $entry;
    // Keep log bounded so file does not grow forever.
    if (count($rows) > 500) {
        $rows = array_slice($rows, -500);
    }
    return writeJsonArrayFile($filename, $rows);
}

function boolConfigEnabled($value) {
    if (is_bool($value)) {
        return $value;
    }

    $normalized = strtolower(trim((string)$value));
    return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
}

function postJsonHttp($url, $payload, $headers = [], $options = []) {
    $jsonBody = json_encode($payload);
    if ($jsonBody === false) {
        return [false, 'json_encode_failed'];
    }

    $sslVerify = true;
    if (array_key_exists('ssl_verify', $options)) {
        $sslVerify = (bool)$options['ssl_verify'];
    }

    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $allHeaders = array_merge($defaultHeaders, $headers);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify ? 2 : 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'EditX-Studio-Webhook/1.0');

        $responseBody = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        // Retry once without SSL verification for hosts with outdated CA bundles.
        if (($responseBody === false || $httpCode < 200 || $httpCode >= 300) &&
            $sslVerify &&
            stripos($error, 'SSL') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $responseBody = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
        }

        curl_close($ch);

        if ($responseBody === false || $httpCode < 200 || $httpCode >= 300) {
            $bodySnippet = is_string($responseBody) ? trim(substr($responseBody, 0, 300)) : '';
            $errorText = $error !== '' ? $error : ('http_' . $httpCode);
            if ($bodySnippet !== '') {
                $errorText .= ' | ' . $bodySnippet;
            }
            return [false, $errorText];
        }

        return [true, 'ok'];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $allHeaders) . "\r\n",
            'content' => $jsonBody,
            'timeout' => 20,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer' => $sslVerify,
            'verify_peer_name' => $sslVerify,
        ],
    ]);

    $responseBody = @file_get_contents($url, false, $context);
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\s(\d{3})\s/', $statusLine, $matches);
    $httpCode = isset($matches[1]) ? (int)$matches[1] : 0;

    if ($responseBody === false || $httpCode < 200 || $httpCode >= 300) {
        $bodySnippet = is_string($responseBody) ? trim(substr($responseBody, 0, 300)) : '';
        $errorText = $httpCode > 0 ? ('http_' . $httpCode) : 'request_failed';
        if ($bodySnippet !== '') {
            $errorText .= ' | ' . $bodySnippet;
        }
        return [false, $errorText];
    }

    return [true, 'ok'];
}

function sendAdminDiscordNotification($formType, $payload = []) {
    if (!boolConfigEnabled(ENABLE_DISCORD_NOTIFICATIONS) || trim((string)DISCORD_WEBHOOK_URL) === '') {
        return false;
    }

    $formLabelMap = [
        'booking' => 'New Booking',
        'contact' => 'New Contact Message',
        'feedback' => 'New Feedback',
    ];

    $formLabel = $formLabelMap[$formType] ?? 'New Form Submission';

    $name = trim((string)($payload['name'] ?? ''));
    $email = trim((string)($payload['email'] ?? ''));
    $phone = trim((string)($payload['phone'] ?? ''));
    $message = trim((string)($payload['message'] ?? $payload['description'] ?? ''));
    $service = trim((string)($payload['service'] ?? ''));
    $date = trim((string)($payload['booking_date'] ?? ''));
    $time = trim((string)($payload['booking_time'] ?? ''));

    $lines = [];
    $lines[] = '**' . $formLabel . '**';
    if ($name !== '') $lines[] = 'Name: ' . $name;
    if ($phone !== '') $lines[] = 'Phone: ' . $phone;
    if ($email !== '') $lines[] = 'Email: ' . $email;
    if ($service !== '') $lines[] = 'Service: ' . $service;
    if ($date !== '') $lines[] = 'Date: ' . $date;
    if ($time !== '') $lines[] = 'Time: ' . $time;
    if ($message !== '') $lines[] = 'Message: ' . $message;
    $lines[] = 'Submitted: ' . date('Y-m-d H:i:s');

    $discordBody = [
        'content' => implode("\n", $lines),
    ];

    $discordSslVerify = true;
    if (defined('DISCORD_SSL_VERIFY')) {
        $discordSslVerify = boolConfigEnabled(DISCORD_SSL_VERIFY);
    }

    $webhookUrl = trim((string)DISCORD_WEBHOOK_URL);

    list($ok, $errorCode) = postJsonHttp(
        $webhookUrl,
        $discordBody,
        [],
        ['ssl_verify' => $discordSslVerify]
    );

    if (!$ok && stripos($webhookUrl, 'discord.com/') !== false) {
        $fallbackUrl = str_replace('discord.com/', 'discordapp.com/', $webhookUrl);
        list($fallbackOk, $fallbackErrorCode) = postJsonHttp(
            $fallbackUrl,
            $discordBody,
            [],
            ['ssl_verify' => $discordSslVerify]
        );

        if ($fallbackOk) {
            return true;
        }

        $errorCode = $errorCode . ' || fallback: ' . $fallbackErrorCode;
    }

    if (!$ok) {
        appendJsonLogFile('discord_failed_log.json', [
            'form_type' => $formType,
            'payload' => $payload,
            'webhook_host' => parse_url($webhookUrl, PHP_URL_HOST),
            'ssl_verify' => $discordSslVerify,
            'curl_available' => function_exists('curl_init'),
            'allow_url_fopen' => (bool)ini_get('allow_url_fopen'),
            'openssl_loaded' => extension_loaded('openssl'),
            'error' => $errorCode,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    return $ok;
}

// ============================================================================
// EMAIL HELPERS
// ============================================================================

/**
 * Send thank-you email after successful form submissions.
 * Returns true when the mail() call succeeds, false otherwise.
 */
function sendFormThankYouEmail($toName, $toEmail, $formType, $context = []) {
    $toName = trim((string)$toName);
    $toEmail = trim((string)$toEmail);

    if ($toName === '' || $toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $templates = getThankYouEmailTemplate($toName, $formType, $context);

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_EMAIL . ">\r\n";
    $headers .= 'Reply-To: ' . MAIL_REPLY_TO . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";

    $provider = defined('EMAIL_PROVIDER') ? strtolower(trim((string)EMAIL_PROVIDER)) : 'mail';

    if ($provider === 'brevo') {
        $result = sendEmailViaBrevoApi($toName, $toEmail, $templates['subject'], $templates['html']);
    } else {
        $result = @mail($toEmail, $templates['subject'], $templates['html'], $headers);
    }

    if (!$result) {
        appendJsonLogFile('email_failed_log.json', [
            'to' => $toEmail,
            'name' => $toName,
            'form_type' => $formType,
            'provider' => $provider,
            'subject' => $templates['subject'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    return $result;
}

function sendEmailViaBrevoApi($toName, $toEmail, $subject, $htmlContent) {
    if (!defined('BREVO_API_KEY') || trim((string)BREVO_API_KEY) === '') {
        return false;
    }

    $payload = [
        'sender' => [
            'name' => MAIL_FROM_NAME,
            'email' => MAIL_FROM_EMAIL,
        ],
        'to' => [[
            'email' => $toEmail,
            'name' => $toName,
        ]],
        'replyTo' => [
            'email' => MAIL_REPLY_TO,
            'name' => MAIL_FROM_NAME,
        ],
        'subject' => $subject,
        'htmlContent' => $htmlContent,
    ];

    list($ok, $errorCode) = postJsonHttp('https://api.brevo.com/v3/smtp/email', $payload, [
        'accept: application/json',
        'api-key: ' . BREVO_API_KEY,
        'content-type: application/json'
    ]);

    if (!$ok) {
        appendJsonLogFile('email_failed_log.json', [
            'to' => $toEmail,
            'name' => $toName,
            'form_type' => 'mail_api',
            'provider' => 'brevo',
            'subject' => $subject,
            'error' => $errorCode,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    return $ok;
}

function getThankYouEmailTemplate($toName, $formType, $context = []) {
    $safeName = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
    $siteName = htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8');

    $subject = 'Thank you for reaching out - ' . SITE_NAME;
    $intro = "Hi {$safeName},<br><br>Thank you for connecting with {$siteName}.";
    $details = '';

    if ($formType === 'booking') {
        $service = htmlspecialchars($context['service'] ?? 'Selected service', ENT_QUOTES, 'UTF-8');
        $bookingDate = htmlspecialchars($context['booking_date'] ?? 'your selected date', ENT_QUOTES, 'UTF-8');
        $bookingTime = htmlspecialchars($context['booking_time'] ?? 'your selected time', ENT_QUOTES, 'UTF-8');

        $subject = 'Booking received - ' . SITE_NAME;
        $intro = "Hi {$safeName},<br><br>Thank you for booking with {$siteName}. We have received your request.";
        $details = "<strong>Service:</strong> {$service}<br><strong>Date:</strong> {$bookingDate}<br><strong>Time:</strong> {$bookingTime}<br><br>";
    } elseif ($formType === 'contact') {
        $subject = 'We received your message - ' . SITE_NAME;
        $intro = "Hi {$safeName},<br><br>Thank you for contacting {$siteName}. Our team will reply to your message shortly.";
    } elseif ($formType === 'feedback') {
        $subject = 'Thank you for your feedback - ' . SITE_NAME;
        $intro = "Hi {$safeName},<br><br>Thank you for sharing your feedback with {$siteName},It will be visible after admin approval.";
    }

    $html = "
        <html>
            <body style=\"font-family:Arial,sans-serif;line-height:1.5;color:#1f2937;\">
                {$intro}<br><br>
                {$details}
                We appreciate your trust in us.<br><br>
                Regards,<br>
                <strong>{$siteName}</strong>
            </body>
        </html>
    ";

    return [
        'subject' => $subject,
        'html' => $html,
    ];
}

// ============================================================================
// BOOKINGS JSON FUNCTIONS
// ============================================================================

/**
 * Get all bookings from JSON file
 */
function getBookings() {
    return readJsonArrayFile('bookings.json');
}

/**
 * Add new booking to JSON file
 */
function addBooking($data) {
    $bookings = getBookings();
    
    // Generate next ID
    $nextId = 1;
    if (!empty($bookings)) {
        $nextId = max(array_column($bookings, 'id')) + 1;
    }
    
    $newBooking = [
        'id' => $nextId,
        'full_name' => $data['full_name'] ?? '',
        'email' => $data['email'] ?? '',
        'phone' => $data['phone'] ?? '',
        'service' => $data['service'] ?? '',
        'message' => $data['message'] ?? '',
        'booking_date' => $data['booking_date'] ?? '',
        'booking_time' => $data['booking_time'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $bookings[] = $newBooking;
    $result = writeJsonArrayFile('bookings.json', $bookings);
    
    return $result;
}

/**
 * Get booking count
 */
function getBookingCount() {
    return count(getBookings());
}

/**
 * Delete booking by ID
 */
function deleteBooking($id) {
    $bookings = getBookings();
    $bookings = array_filter($bookings, function($booking) use ($id) {
        return $booking['id'] != $id;
    });
    return writeJsonArrayFile('bookings.json', array_values($bookings));
}

// ============================================================================
// CONTACT MESSAGES JSON FUNCTIONS
// ============================================================================

/**
 * Get all contact messages from JSON file
 */
function getContactMessages() {
    return readJsonArrayFile('contact_messages.json');
}

/**
 * Add new contact message to JSON file
 */
function addContactMessage($data) {
    $messages = getContactMessages();
    
    // Generate next ID
    $nextId = 1;
    if (!empty($messages)) {
        $nextId = max(array_column($messages, 'id')) + 1;
    }
    
    $newMessage = [
        'id' => $nextId,
        'name' => $data['name'] ?? '',
        'email' => $data['email'] ?? '',
        'phone' => $data['phone'] ?? '',
        'message' => $data['message'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $messages[] = $newMessage;
    $result = writeJsonArrayFile('contact_messages.json', $messages);
    
    return $result;
}

/**
 * Get contact message count
 */
function getContactMessageCount() {
    return count(getContactMessages());
}

/**
 * Delete contact message by ID
 */
function deleteContactMessage($id) {
    $messages = getContactMessages();
    $messages = array_filter($messages, function($msg) use ($id) {
        return $msg['id'] != $id;
    });
    return writeJsonArrayFile('contact_messages.json', array_values($messages));
}

// ============================================================================
// FEEDBACKS JSON FUNCTIONS
// ============================================================================

/**
 * Get all feedbacks from JSON file
 */
function getFeedbacks() {
    return readJsonArrayFile('feedback.json');
}

/**
 * Add new feedback to JSON file (status: pending by default)
 */
function addFeedback($data) {
    $feedbacks = getFeedbacks();
    
    // Generate next ID
    $nextId = 1;
    if (!empty($feedbacks)) {
        $nextId = max(array_column($feedbacks, 'id')) + 1;
    }
    
    $newFeedback = [
        'id' => $nextId,
        'name' => $data['name'] ?? '',
        'email' => $data['email'] ?? '',
        'phone' => $data['phone'] ?? '',
        'description' => $data['description'] ?? '',
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $feedbacks[] = $newFeedback;
    $result = writeJsonArrayFile('feedback.json', $feedbacks);
    
    return $result;
}

/**
 * Get only pending feedbacks
 */
function getPendingFeedbacks() {
    $feedbacks = getFeedbacks();
    return array_filter($feedbacks, function($feedback) {
        return $feedback['status'] === 'pending';
    });
}

/**
 * Get only approved feedbacks
 */
function getApprovedFeedbacks() {
    $feedbacks = getFeedbacks();
    return array_filter($feedbacks, function($feedback) {
        return $feedback['status'] === 'approved';
    });
}

/**
 * Approve feedback by ID
 */
function approveFeedback($id) {
    $feedbacks = getFeedbacks();
    foreach ($feedbacks as &$feedback) {
        if ($feedback['id'] == $id) {
            $feedback['status'] = 'approved';
            break;
        }
    }
    return writeJsonArrayFile('feedback.json', $feedbacks);
}

/**
 * Reject/Delete feedback by ID
 */
function rejectFeedback($id) {
    $feedbacks = getFeedbacks();
    $feedbacks = array_filter($feedbacks, function($feedback) use ($id) {
        return $feedback['id'] != $id;
    });
    return writeJsonArrayFile('feedback.json', array_values($feedbacks));
}

/**
 * Update feedback (edit description)
 */
function updateFeedback($id, $data) {
    $feedbacks = getFeedbacks();
    foreach ($feedbacks as &$feedback) {
        if ($feedback['id'] == $id) {
            $feedback['description'] = $data['description'] ?? $feedback['description'];
            break;
        }
    }
    return writeJsonArrayFile('feedback.json', $feedbacks);
}

/**
 * Get feedback count
 */
function getFeedbackCount() {
    return count(getFeedbacks());
}

/**
 * Get pending feedback count
 */
function getPendingFeedbackCount() {
    return count(getPendingFeedbacks());
}

?>
