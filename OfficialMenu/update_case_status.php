<?php
session_start();
date_default_timezone_set('Asia/Manila');
// Redirect if no case ID is provided
if (!isset($_GET['id'])) {
    header("Location: view_cases.php");
    exit();
}

$caseId = $_GET['id'];
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch case details with extra info
$sql = "SELECT 
            cs.Case_ID, cs.Case_Status,
            ci.Complaint_Title, ci.Date_Filed,
            comp.First_Name AS Complainant_First, comp.Last_Name AS Complainant_Last,
            resp.First_Name AS Respondent_First, resp.Last_Name AS Respondent_Last
        FROM CASE_INFO cs
        LEFT JOIN COMPLAINT_INFO ci ON cs.Complaint_ID = ci.Complaint_ID
        LEFT JOIN RESIDENT_INFO comp ON ci.Resident_ID = comp.Resident_ID
        LEFT JOIN RESIDENT_INFO resp ON ci.Respondent_ID = resp.Resident_ID
        WHERE cs.Case_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $caseId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-center text-red-600'>Case not found.</p>";
    exit();
}

$case = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'];
    $update = $conn->prepare("UPDATE CASE_INFO SET Case_Status = ? WHERE Case_ID = ?");
    $update->bind_param("si", $newStatus, $caseId);

    if ($update->execute()) {
    // Fetch resident_id and external_id
    $notifQuery = "
        SELECT co.Resident_ID, co.External_Complainant_ID
        FROM case_info cs
        JOIN complaint_info co ON cs.Complaint_ID = co.Complaint_ID
        WHERE cs.Case_ID = $caseId
    ";
    $notifResult = $conn->query($notifQuery);

    if ($notifResult && $notifResult->num_rows > 0) {
        $notifData = $notifResult->fetch_assoc();
        $resident_id = $notifData['Resident_ID'];
        $external_id = $notifData['External_Complainant_ID'];

        $title = "Case Status Updated";
        $message = "The status of your case (ID: $caseId) has been updated to \"$newStatus\".";
        $created_at = date('Y-m-d H:i:s');

        if (!empty($resident_id)) {
            $conn->query("
                INSERT INTO notifications (resident_id, title, message, is_read, created_at)
                VALUES ($resident_id, '$title', '$message', 0, '$created_at')
            ");
        }

        if (!empty($external_id)) {
            $conn->query("
                INSERT INTO notifications (external_complaint_id, title, message, is_read, created_at)
                VALUES ($external_id, '$title', '$message', 0, '$created_at')
            ");
        }
    }

    header("Location: view_cases.php?status_updated=1");
    exit();
}

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Case Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-bold mb-4 text-gray-700">Update Case Status</h2>

        <div class="mb-6 space-y-2">
            <p><strong>Case ID:</strong> <?= htmlspecialchars($case['Case_ID']) ?></p>
            <p><strong>Title:</strong> <?= htmlspecialchars($case['Complaint_Title']) ?></p>
            <p><strong>Complainant:</strong> <?= htmlspecialchars($case['Complainant_First'] . ' ' . $case['Complainant_Last']) ?></p>
            <p><strong>Respondent:</strong> <?= htmlspecialchars($case['Respondent_First'] . ' ' . $case['Respondent_Last']) ?></p>
            <p><strong>Date Filed:</strong> <?= date("F j, Y", strtotime($case['Date_Filed'])) ?></p>
            <p><strong>Current Status:</strong> <?= htmlspecialchars($case['Case_Status']) ?></p>
        </div>

        <form method="POST">
            <div class="mb-4">
                <label class="block font-semibold text-gray-700 mb-1">New Status:</label>
                <?php
                    $current = $case['Case_Status'];
                    $transitions = [
                        "Open" => ["Mediation", "Resolved", "Closed"],
                        "Mediation" => ["Resolution", "Resolved", "Closed"],
                        "Resolution" => ["Settlement", "Resolved", "Closed"],
                        "Settlement" => ["Resolved", "Closed"],
                        "Resolved" => ["Closed"],
                        "Closed" => []
                    ];
                    $available = $transitions[$current];
                ?>
                <?php if (count($available) > 0): ?>
                <select name="status" class="w-full p-2 border rounded">
                    <?php foreach ($available as $status): ?>
                        <option value="<?= $status ?>"><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                    <p class="bg-gray-100 p-2 rounded text-red-600">This case is already closed. No further updates allowed.</p>
                <?php endif; ?>
            </div>

            <div class="text-right">
                <a href="view_cases.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    <?= count($available) === 0 ? 'disabled class="opacity-50 cursor-not-allowed"' : '' ?>>
                    Update
                </button>
            </div>
        </form>
    </div>
</body>
</html>
