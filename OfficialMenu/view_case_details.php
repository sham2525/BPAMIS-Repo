<?php
session_start();
include '../server/server.php';

if (!isset($_GET['id'])) {
    echo "<p class='text-center text-red-500'>Case ID not provided.</p>";
    exit;
}

$case_id = intval($_GET['id']);

$sql = "SELECT 
            cs.Case_ID,
            cs.Case_Status,
            cs.Date_Opened,
            ci.Complaint_Title,
            ci.Complaint_Details,
            ci.Date_Filed,
            comp.First_Name AS Complainant_First,
            comp.Last_Name AS Complainant_Last,
            resp.First_Name AS Respondent_First,
            resp.Last_Name AS Respondent_Last
        FROM CASE_INFO cs
        LEFT JOIN COMPLAINT_INFO ci ON cs.Complaint_ID = ci.Complaint_ID
        LEFT JOIN RESIDENT_INFO comp ON ci.Resident_ID = comp.Resident_ID
        LEFT JOIN RESIDENT_INFO resp ON ci.Respondent_ID = resp.Resident_ID
        WHERE cs.Case_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $case_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-center text-red-500'>Case not found.</p>";
    exit;
}

$case = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 p-6">
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-xl p-6">
        <h2 class="text-2xl font-bold text-blue-800 mb-4">Case Details</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><strong>Case ID:</strong> <?= htmlspecialchars($case['Case_ID']) ?></p>
                <p><strong>Case Status:</strong> <?= htmlspecialchars($case['Case_Status']) ?></p>
                <p><strong>Date Opened:</strong> <?= htmlspecialchars($case['Date_Opened']) ?></p>
            </div>
            <div>
                <p><strong>Complainant:</strong> <?= htmlspecialchars($case['Complainant_First'] . ' ' . $case['Complainant_Last']) ?></p>
                <p><strong>Respondent:</strong> <?= htmlspecialchars($case['Respondent_First'] . ' ' . $case['Respondent_Last']) ?></p>
            </div>
        </div>

        <div class="mt-4">
            <p><strong>Complaint Title:</strong> <?= htmlspecialchars($case['Complaint_Title']) ?></p>
            <p><strong>Date Filed:</strong> <?= htmlspecialchars($case['Date_Filed']) ?></p>
        </div>

        <div class="mt-4">
            <p class="font-semibold">Complaint Details:</p>
            <div class="border p-3 rounded bg-gray-50 text-sm text-gray-700">
                <?= nl2br(htmlspecialchars($case['Complaint_Details'])) ?>
            </div>
        </div>

        <div class="mt-6">
            <a href="view_cases.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Back to Cases</a>
            <a href="update_case_status.php?id=<?= urlencode($case['Case_ID']) ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded ml-2">Update Case Status</a>
        </div>
    </div>
</body>
</html>
