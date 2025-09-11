<?php
include 'db-connect.php';

if (!isset($_GET['Case_ID'])) {
    echo json_encode(['error' => 'No Case_ID provided']);
    exit;
}

$caseId = $_GET['Case_ID'];

// Prepare and execute the query to get the complainant's full name
$complainantQuery = "
    SELECT CONCAT(r.First_Name, ' ', r.Last_Name) AS Complainant_Name
    FROM case_info ci
    JOIN complaint_info ci2 ON ci.Complaint_ID = ci2.Complaint_ID
    JOIN resident_info r ON ci2.Resident_ID = r.Resident_ID
    WHERE ci.Case_ID = ?
";

$complainantStmt = $conn->prepare($complainantQuery);
$complainantStmt->bind_param("i", $caseId);
$complainantStmt->execute();
$complainantResult = $complainantStmt->get_result();

$complainantName = "";
if ($complainantRow = $complainantResult->fetch_assoc()) {
    $complainantName = $complainantRow['Complainant_Name'];
}

// Prepare and execute the query to get respondents' names
$respondentsQuery = "
    SELECT CONCAT(r.First_Name, ' ', r.Last_Name) AS Respondent_Name
    FROM case_info ci
    JOIN complaint_respondents cr ON ci.Complaint_ID = cr.Complaint_ID
    JOIN resident_info r ON cr.Respondent_ID = r.Resident_ID
    WHERE ci.Case_ID = ?
";

$respondentsStmt = $conn->prepare($respondentsQuery);
$respondentsStmt->bind_param("i", $caseId);
$respondentsStmt->execute();
$respondentsResult = $respondentsStmt->get_result();

$respondents = [];
while ($row = $respondentsResult->fetch_assoc()) {
    $respondents[] = $row['Respondent_Name'];
}

echo json_encode([
    'complainant' => $complainantName,
    'respondents' => $respondents
]);
?>
