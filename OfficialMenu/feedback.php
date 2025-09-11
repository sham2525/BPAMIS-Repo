<?php
session_start();
include '../server/server.php';

// Redirect if not logged in
if (!isset($_SESSION['official_id'])) {
    header("Location: ../login.php");
    exit();
}

$official_id = $_SESSION['official_id'];
$official_name = $_SESSION['official_name'] ?? 'Unknown';

$success = '';
$error = '';
$cases = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $case_id = $_POST['case_id'] ?? '';
    $message = trim($_POST['message'] ?? '');

    if (!empty($case_id) && !empty($message)) {
        $stmt = $conn->prepare("
            INSERT INTO feedback (official_id, official_name, case_id, message, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isis", $official_id, $official_name, $case_id, $message);

        if ($stmt->execute()) {
            $success = "Feedback successfully submitted.";
        } else {
            $error = "Error inserting feedback: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Please select a case and enter your feedback.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Write Feedback</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
</head>
<body class="bg-gray-50">
    <?php include '../includes/barangay_official_cap_nav.php'; ?>

    <div class="container mx-auto px-4 py-10">
        <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-md border border-gray-100">
            <div class="mb-6 flex items-center gap-2">
                <i class="fas fa-comment-dots text-blue-600 text-2xl"></i>
                <h2 class="text-2xl font-semibold text-gray-800">Write Feedback</h2>
            </div>

            <!-- Success or Error Message -->
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Feedback Form -->
            <form method="POST">
    <div class="mb-4">
        <label for="case_id" class="block text-gray-700 font-medium mb-1">Select Case</label>
        <select id="case-id" name="case_id" class="w-full p-2 border border-gray-300 rounded-lg" required>
                    <option value="">-- Select a Case --</option>
                    <?php
                   $sql = "SELECT ci.Case_ID, co.Complaint_Title
                    FROM case_info ci
                    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID";

                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['Case_ID'] . '">' . $row['Case_ID'] . ' - ' . htmlspecialchars($row['Complaint_Title']) . '</option>';
                            }
                        } else {
                            echo '<option disabled>No cases found</option>';
                        }

                    ?>
        </select>
    </div>

    <div class="mb-4">
        <label for="message" class="block text-gray-700 font-medium mb-1">Your Feedback</label>
        <textarea id="message" name="message" rows="6" required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300"
            placeholder="Enter your feedback here..."></textarea>
    </div>

    <button type="submit"
        class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition-all shadow-md">
        <i class="fas fa-paper-plane mr-2"></i> Submit Feedback
    </button>
</form>

        </div>
    </div>
</body>
</html>
