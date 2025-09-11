<?php
session_start();
include '../server/server.php';

// Update all unread notifications for the secretary to read
$sql = "UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND type IN ('Unverified', 'Hearing', 'Complaint', 'Case')";
$conn->query($sql);

echo json_encode(['success' => true]);
?> 