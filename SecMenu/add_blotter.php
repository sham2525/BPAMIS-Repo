<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['blotter_description'];
    $reported_by = $_POST['reported_by'];
    $date_reported = $_POST['date_reported'];

    $stmt = $conn->prepare("INSERT INTO BLOTTER_INFO (Blotter_Description, Reported_By, Date_Reported) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $description, $reported_by, $date_reported);

    if ($stmt->execute()) {
        $success = true;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Blotter Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</head>
<body class="bg-gray-50">
<?php include '../includes/barangay_official_sec_nav.php'; ?>
    <!-- Page Header (copied from view_cases.php) -->
    <section class="container mx-auto mt-8 px-4">
        <div class="gradient-bg rounded-xl shadow-sm p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h1 class="text-2xl font-medium text-primary-800">Add Blotter Report</h1>
                <p class="mt-1 text-gray-600">File a new blotter report for barangay records</p>
            </div>
        </div>
    </section>
    <div class="container mx-auto mt-8 px-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <?php if ($success): ?>
                <div class="mb-6 p-4 rounded bg-green-100 border border-green-300 text-green-800">
                    Blotter report has been successfully recorded.
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="blotter_description">Blotter Description</label>
                    <textarea name="blotter_description" id="blotter_description" rows="4" required class="w-full border border-gray-300 rounded p-2"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="reported_by">Reported By</label>
                    <input type="text" name="reported_by" id="reported_by" required class="w-full border border-gray-300 rounded p-2">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="date_reported">Date Reported</label>
                    <input type="date" name="date_reported" id="date_reported" required class="w-full border border-gray-300 rounded p-2">
                </div>
                <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded hover:bg-primary-700 transition">
                    Submit Blotter Report
                </button>
            </form>
        </div>
    </div>
    <?php include 'sidebar_.php';?>
</body>
</html>
