<?php
include '../server/server.php';

$caseId = $_GET['id'] ?? null;

if (!$caseId) {
    echo "Invalid request.";
    exit;
}

// Fetch case & complaint info
$query = "SELECT 
    c.Case_ID,
    ci.Complaint_Title,
    ci.Complaint_Details,
    ci.Date_Filed,
    c.Case_Status,
    c.Date_Opened,
    c.Date_Closed,
    c.Next_Hearing_Date,
    ri.First_Name AS resident_fname,
    ri.Last_Name AS resident_lname,
    eci.first_name AS external_fname,
    eci.last_name AS external_lname
FROM case_info c
JOIN complaint_info ci ON c.Complaint_ID = ci.Complaint_ID
LEFT JOIN resident_info ri ON ci.Resident_ID = ri.Resident_ID
LEFT JOIN external_complainant eci ON c.Complaint_ID = eci.external_Complaint_ID
WHERE c.Case_ID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $caseId);
$stmt->execute();
$caseResult = $stmt->get_result();

if ($caseResult->num_rows === 0) {
    echo "Case not found.";
    exit;
}

$case = $caseResult->fetch_assoc();

// Fetch hearing schedule
$hearingsQuery = "SELECT hearingTitle, hearingDateTime, place, participant, remarks 
                  FROM schedule_list 
                  WHERE Case_ID = ? 
                  ORDER BY hearingDateTime ASC";

$stmt2 = $conn->prepare($hearingsQuery);
$stmt2->bind_param("i", $caseId);
$stmt2->execute();
$hearingsResult = $stmt2->get_result();

$hearings = [];
while ($row = $hearingsResult->fetch_assoc()) {
    $hearings[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Report Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 p-8">
    <div class="max-w-5xl mx-auto bg-white shadow-lg rounded-xl p-6">
        <h1 class="text-2xl font-bold mb-4 text-primary-600">📄 Case Report Details</h1>

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">📝 Complaint Information</h2>
            <p><strong>Title:</strong> <?= htmlspecialchars($case['Complaint_Title']) ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($case['Complaint_Details']) ?></p>
            <p><strong>Date Filed:</strong> <?= htmlspecialchars($case['Date_Filed']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($case['Case_Status']) ?></p>
            <p><strong>Date Opened:</strong> <?= htmlspecialchars($case['Date_Opened']) ?></p>
            <p><strong>Date Closed:</strong> <?= htmlspecialchars($case['Date_Closed'] ?? 'N/A') ?></p>
            <p><strong>Next Hearing:</strong> <?= htmlspecialchars($case['Next_Hearing_Date'] ?? 'N/A') ?></p>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">🙋 Complainant</h2>
            <p>
                <?= !empty($case['resident_fname']) 
                    ? htmlspecialchars($case['resident_fname'] . ' ' . $case['resident_lname']) 
                    : htmlspecialchars($case['external_fname'] . ' ' . $case['external_lname']) ?>
            </p>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-2">📅 Hearing Schedules</h2>
            <?php if (count($hearings) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 shadow-sm rounded">
                        <thead class="bg-gray-50 text-gray-700 text-sm font-semibold">
                            <tr>
                                <th class="py-2 px-4 border-b">Title</th>
                                <th class="py-2 px-4 border-b">Date & Time</th>
                                <th class="py-2 px-4 border-b">Place</th>
                                <th class="py-2 px-4 border-b">Participants</th>
                                <th class="py-2 px-4 border-b">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-600">
                            <?php foreach ($hearings as $hearing): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($hearing['hearingTitle']) ?></td>
                                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($hearing['hearingDateTime']) ?></td>
                                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($hearing['place']) ?></td>
                                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($hearing['participant']) ?></td>
                                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($hearing['remarks']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No hearings recorded for this case.</p>
            <?php endif; ?>
        </div>

        <div class="mt-6">
            <a href="javascript:history.back()" class="text-blue-600 hover:underline">&larr; Back to Case List</a>
        </div>
    </div>
</body>
</html>
