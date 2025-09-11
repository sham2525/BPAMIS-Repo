<?php
session_start();
include '../server/server.php';

// Redirect if not logged in
if (!isset($_SESSION['official_id'])) {
    header("Location: ../login.php");
    exit();
}

$luponId = $_SESSION['official_id'];

// First, get the Lupon full name from barangay_officials to match with mediator_name
$stmt = $conn->prepare("SELECT Name FROM barangay_officials WHERE Official_ID = ?");
$stmt->bind_param("i", $luponId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $luponName = $row['Name'];
} else {
    $luponName = ''; // fallback
}
$stmt->close();

// Query cases assigned to this Lupon (check mediator_name contains the Lupon's name)
$sqlCases = "
    SELECT ci.Case_ID, co.Complaint_Title, ci.Case_Status,
    (
        SELECT sl.hearingdatetime
        FROM schedule_list sl
        WHERE sl.case_id = ci.Case_ID
        ORDER BY sl.hearingdatetime ASC
        LIMIT 1
    ) AS next_hearing
    FROM case_info ci
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID
    LEFT JOIN mediation_info mi ON mi.case_id = ci.Case_ID
    LEFT JOIN resolution r ON r.case_id = ci.Case_ID
    LEFT JOIN settlement s ON s.case_id = ci.Case_ID
    WHERE (
        mi.mediator_name LIKE CONCAT('%', ?, '%') OR
        r.mediator_name LIKE CONCAT('%', ?, '%') OR
        s.mediator_name LIKE CONCAT('%', ?, '%')
    )
    ORDER BY ci.Case_ID ASC
";

$stmt = $conn->prepare($sqlCases);
$stmt->bind_param("sss", $luponName, $luponName, $luponName);
$stmt->execute();
$resultCases = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Assigned Cases - Lupon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <?php include '../includes/barangay_official_lupon_nav.php'; ?>
    <?php include 'sidebar_lupon.php'; ?>
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-6 text-primary-700">Assigned Cases for Lupon: <?= htmlspecialchars($luponName) ?></h1>

        <?php if ($resultCases && $resultCases->num_rows > 0): ?>
        <div class="overflow-x-auto bg-white rounded-lg shadow-md border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-primary-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-primary-800 uppercase tracking-wider">Case ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-primary-800 uppercase tracking-wider">Complaint Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-primary-800 uppercase tracking-wider">Case Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-primary-800 uppercase tracking-wider">Next Hearing</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php while ($case = $resultCases->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($case['Case_ID']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($case['Complaint_Title']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($case['Case_Status']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php 
                            if (!empty($case['next_hearing'])) {
                                echo date("M d, Y h:i A", strtotime($case['next_hearing']));
                            } else {
                                echo '<span class="text-gray-500 italic">No hearing scheduled</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <?php else: ?>
            <p class="text-gray-600">No cases currently assigned to you.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
