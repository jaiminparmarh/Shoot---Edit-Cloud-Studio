<?php
/**
 * Notification Functions - DISABLED
 * This file is kept for compatibility but all notifications are disabled
 */

// All notification functions are disabled - no notifications will be sent
function sendNotification($title, $message, $data = []) {
    return false;
}

function notifyNewBooking($booking) {
    return false;
}

function notifyNewContactMessage($message_data) {
    return false;
}

function notifyNewFeedback($feedback) {
    return false;
}

?>
