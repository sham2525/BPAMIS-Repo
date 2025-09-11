<?php
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$blotter_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM BLOTTER_INFO WHERE Blotter_ID = ?");
$stmt->bind_param("i", $blotter_id);
$stmt->execute();
$result = $stmt->get_result();
$blotter = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blotter Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold text-blue-800 mb-4">Blotter Report Details</h2>
        <?php if ($blotter): ?>
            <p><strong class="text-gray-700">Reported By:</strong> <?= htmlspecialchars($blotter['Reported_By']) ?></p>
            <p><strong class="text-gray-700">Date Reported:</strong> <?= htmlspecialchars($blotter['Date_Reported']) ?></p>
            <div class="mt-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Description:</h3>
                <p class="whitespace-pre-wrap text-gray-800"><?= nl2br(htmlspecialchars($blotter['Blotter_Description'])) ?></p>
            </div>
        <?php else: ?>
            <p class="text-red-600">Blotter report not found.</p>
        <?php endif; ?>
        <div class="mt-6">
            <a href="view_blotter.php" class="text-blue-600 hover:underline">&larr; Back to List</a>
        </div>
    </div>
</body>
</html>
