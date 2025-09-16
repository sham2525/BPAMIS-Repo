<?php
session_start();
include '../server/server.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Notification ID is missing.');
}

$notificationId = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM notifications WHERE notification_id = ?");
$stmt->bind_param("i", $notificationId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Notification not found.');
}

$notification = $result->fetch_assoc();

// Mark as read
$conn->query("UPDATE notifications SET is_read = 1 WHERE notification_id = $notificationId");

// Fetch complaint info if this is a complaint-type notification
$complaint = null;

if ($notification['type'] === 'Complaint' && !empty($notification['reference_id'])) {
    $complaint_id = intval($notification['reference_id']);
    $query = "SELECT c.*, r.First_Name, r.Last_Name
              FROM complaint_info c
              JOIN resident_info r ON c.Resident_ID = r.Resident_ID
              WHERE c.Complaint_ID = $complaint_id";
    $result_complaint = $conn->query($query);
    if ($result_complaint && $result_complaint->num_rows > 0) {
        $complaint = $result_complaint->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notification Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="max-w-3xl mx-auto mt-10 bg-white shadow-md rounded-xl p-6 border border-gray-200">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-blue-700 flex items-center mb-2">
                <i class="fas fa-bell mr-2"></i>
                Notification Details
            </h1>
            <p class="text-sm text-gray-500">Posted on <?= date("F d, Y \\a\\t h:i A", strtotime($notification['created_at'])) ?></p>
        </div>

        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-1">Title:</h2>
            <p class="text-gray-700"><?= htmlspecialchars($notification['title']) ?></p>
        </div>

        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-1">Message:</h2>
            <p class="text-gray-700"><?= nl2br(htmlspecialchars($notification['message'])) ?></p>
        </div>

        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-1">Type:</h2>
            <span class="inline-block px-3 py-1 rounded-full bg-gray-200 text-sm font-medium text-gray-700">
                <?= htmlspecialchars($notification['type']) ?>
            </span>
        </div>

        <?php if ($complaint): ?>
            <div class="mt-8 border-t pt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Complaint Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><strong>Complaint ID:</strong> C<?= date('Y') ?>-<?= str_pad($complaint['Complaint_ID'], 3, '0', STR_PAD_LEFT) ?></div>
                    <div><strong>Complainant:</strong> <?= htmlspecialchars($complaint['First_Name'] . ' ' . $complaint['Last_Name']) ?></div>
                    <div><strong>Title:</strong> <?= htmlspecialchars($complaint['Complaint_Title']) ?></div>
                    <div><strong>Status:</strong> <?= htmlspecialchars($complaint['Status']) ?></div>
                    <div><strong>Date Filed:</strong> <?= date("F d, Y", strtotime($complaint['Date_Filed'])) ?></div>
                    <?php if (!empty($complaint['Description'])): ?>
                        <div class="md:col-span-2"><strong>Description:</strong><br><?= nl2br(htmlspecialchars($complaint['Description'])) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-6">
            <a href="javascript:history.back()" 
   class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
    ← Back to Notifications
</a>
        </div>
    </div>
</body>
</html>
