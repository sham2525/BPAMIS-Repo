<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_case = false;
$editing = isset($_GET['edit']); // Only editable when ?edit is in URL

// Check if already in CASE_INFO
$case_result = $conn->query("SELECT * FROM CASE_INFO WHERE Complaint_ID = $complaint_id");
$is_case = $case_result->num_rows > 0;

// Handle update/save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_complaint']) && !$is_case) {
    $title = $conn->real_escape_string($_POST['complaint_title']);
    $details = $conn->real_escape_string($_POST['complaint_details']);
    $conn->query("UPDATE COMPLAINT_INFO SET Complaint_Title = '$title', Complaint_Details = '$details' WHERE Complaint_ID = $complaint_id");
    header("Location: view_complaint_details.php?id=$complaint_id");
    exit;
}

// Handle case validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validate_case']) && !$is_case) {
    $date_opened = date('Y-m-d');

    $conn->query("INSERT INTO CASE_INFO (Complaint_ID, Case_Status, Date_Opened) VALUES ($complaint_id, 'Open', '$date_opened')");
    $conn->query("UPDATE COMPLAINT_INFO SET Status = 'IN CASE' WHERE Complaint_ID = $complaint_id");

    $complainantRes = $conn->query("SELECT Resident_ID, External_Complainant_ID FROM COMPLAINT_INFO WHERE Complaint_ID = $complaint_id");
    if ($complainantRes && $complainantRes->num_rows > 0) {
        $row = $complainantRes->fetch_assoc();
        $resident_id = $row['Resident_ID'];
        $external_id = $row['External_Complainant_ID'];

        $title = "Complaint Converted to Case";
        $message = "Your complaint with ID #$complaint_id has been validated and is now an official case.";
        $created_at = date('Y-m-d H:i:s');

        $type = 'Case';

if (!empty($resident_id)) {
    $conn->query("INSERT INTO notifications (resident_id, title, message, type, is_read, created_at)
                  VALUES ($resident_id, '$title', '$message', '$type', 0, '$created_at')");
} elseif (!empty($external_id)) {
    $conn->query("INSERT INTO notifications (external_complaint_id, title, message, type, is_read, created_at)
                  VALUES ($external_id, '$title', '$message', '$type', 0, '$created_at')");
}

    }

    header("Location: view_complaints.php?success=validated");
    exit;
}


// Handle complaint rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_complaint']) && !$is_case) {
    $conn->query("UPDATE COMPLAINT_INFO SET Status = 'Rejected' WHERE Complaint_ID = $complaint_id");

    $complainantRes = $conn->query("SELECT Resident_ID, External_Complainant_ID FROM COMPLAINT_INFO WHERE Complaint_ID = $complaint_id");
    if ($complainantRes && $complainantRes->num_rows > 0) {
        $row = $complainantRes->fetch_assoc();
        $resident_id = $row['Resident_ID'];
        $external_id = $row['External_Complainant_ID'];

        $title = "Complaint Rejected";
        $message = "Your complaint with ID #$complaint_id has been rejected after evaluation.";
        $created_at = date('Y-m-d H:i:s');

        if (!empty($resident_id)) {
            $conn->query("INSERT INTO notifications (resident_id, title, message, is_read, created_at)
                          VALUES ($resident_id, '$title', '$message', 0, '$created_at')");
        } elseif (!empty($external_id)) {
            $conn->query("INSERT INTO notifications (external_complaint_id, title, message, is_read, created_at)
                          VALUES ($external_id, '$title', '$message', 0, '$created_at')");
        }
    }

    header("Location: view_complaints.php?success=rejected");
    exit;
}


// Fetch complaint (with resident or external complainant name)
$sql = "SELECT c.*, r.First_Name AS Res_First_Name, r.Last_Name AS Res_Last_Name, 
               e.First_Name AS Ext_First_Name, e.Last_Name AS Ext_Last_Name
        FROM COMPLAINT_INFO c
        LEFT JOIN RESIDENT_INFO r ON c.Resident_ID = r.Resident_ID
        LEFT JOIN EXTERNAL_COMPLAINANT e ON c.External_Complainant_ID = e.External_Complaint_ID
        WHERE c.Complaint_ID = $complaint_id";

$result = $conn->query($sql);
if ($result->num_rows === 0) {
    echo "Complaint not found."; exit;
}
$complaint = $result->fetch_assoc();
$is_rejected = strtolower($complaint['Status']) === 'rejected';

$complainant_name = !empty($complaint['Res_First_Name'])
    ? $complaint['Res_First_Name'] . ' ' . $complaint['Res_Last_Name']
    : (!empty($complaint['Ext_First_Name']) ? $complaint['Ext_First_Name'] . ' ' . $complaint['Ext_Last_Name'] : 'Unknown');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complaint Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 p-6">
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-blue-800 mb-4">Complaint Details</h2>

        <form method="POST" class="space-y-4">
            <p><strong>Complaint ID:</strong> <?= htmlspecialchars($complaint['Complaint_ID']) ?></p>
            <p><strong>Complainant:</strong> <?= htmlspecialchars($complainant_name) ?></p>

            <div>
                <label class="font-semibold">Complaint Title:</label>
                <input type="text" name="complaint_title" value="<?= htmlspecialchars($complaint['Complaint_Title']) ?>" 
                       class="w-full border p-2 rounded" <?= ($editing && !$is_case && !$is_rejected) ? '' : 'disabled' ?>>
            </div>

            <div>
                <label class="font-semibold">Complaint Details:</label>
                <textarea name="complaint_details" rows="4" class="w-full border p-2 rounded" 
                          <?= ($editing && !$is_case && !$is_rejected) ? '' : 'disabled' ?>><?= htmlspecialchars($complaint['Complaint_Details']) ?></textarea>
            </div>

            <p><strong>Date Filed:</strong> <?= htmlspecialchars($complaint['Date_Filed']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($complaint['Status']) ?></p>

            <div class="flex flex-wrap gap-2 mt-4">
                <a href="view_complaints.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back</a>

                <?php if (!$is_case && !$editing && !$is_rejected): ?>
                    <a href="?id=<?= $complaint_id ?>&edit=1" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Validate (Edit Info)</a>
                <?php endif; ?>

                <?php if (!$is_case && $editing && !$is_rejected): ?>
                    <button type="submit" name="update_complaint" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
                <?php endif; ?>

                <?php if (!$is_case && !$editing && !$is_rejected): ?>
                    <form method="POST" style="display:inline;">
                        <button type="submit" name="validate_case" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Complete Validation (Convert to Case)
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (!$is_case && !$editing && !$is_rejected): ?>
                    <form method="POST" style="display:inline;">
                        <button type="submit" name="reject_complaint" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                            Reject Complaint
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($is_case): ?>
                    <button disabled class="bg-green-600 text-white px-4 py-2 rounded">Already Converted to Case</button>
                <?php endif; ?>

                <?php if ($is_rejected): ?>
                    <button disabled class="bg-red-600 text-white px-4 py-2 rounded">Complaint Rejected</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>
