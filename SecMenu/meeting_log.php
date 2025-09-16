<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$currentYear = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Meeting Logs</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</head>
<body class="bg-gray-50 font-sans">
<?php include '../includes/barangay_official_sec_nav.php'; ?>

<section class="container mx-auto mt-8 px-4">
    <div class="gradient-bg rounded-xl shadow-sm p-6 md:p-8 relative overflow-hidden">
        <div class="relative z-10">
            <h1 class="text-2xl font-medium text-primary-800">Meeting Log</h1>
            <p class="mt-1 text-gray-600">Record and view minutes of barangay meetings</p>
        </div>
    </div>
</section>

<div class="container mx-auto mt-8 px-4">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">

        <!-- Filters -->
        <div class="mb-4 flex items-center gap-4 flex-wrap">
            <!-- Status Filter -->
            <div>
                <label for="status" class="text-sm font-medium text-gray-700">Status:</label>
                <select id="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="All">All</option>
                    <option value="Open">Open</option>
                    <option value="Mediation">Mediation</option>
                    <option value="Resolution">Resolution</option>
                    <option value="Settlement">Settlement</option>
                </select>
            </div>

            <!-- Month Filter -->
            <div>
                <label for="month" class="text-sm font-medium text-gray-700">Month:</label>
                <select id="month" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="All">All</option>
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $monthName = date("F", mktime(0, 0, 0, $m, 1));
                        echo "<option value='$m'>$monthName</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Year Filter -->
            <div>
                <label for="year" class="text-sm font-medium text-gray-700">Year:</label>
                <select id="year" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="All">All</option>
                    <?php
                    for ($y = $currentYear; $y >= 2000; $y--) {
                        echo "<option value='$y'>$y</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Table -->
        <table class="w-full mt-4">
            <thead>
              <tr>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase border-b-2">Case ID</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase border-b-2">Complaint Title</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase border-b-2">Date Filed</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase border-b-2">Hearing Date</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase border-b-2">Hearing Time</th> 
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase border-b-2">Status</th>
                  <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase border-b-2">Action</th>
              </tr>
          </thead>

            <tbody id="tableBody">
                <tr>
                    <td colspan="5" class="text-center px-4 py-4 text-gray-500">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'sidebar_.php';?>

<script>
function loadTable() {
    let status = document.getElementById('status').value;
    let month = document.getElementById('month').value;
    let year = document.getElementById('year').value;

    fetch(`fetch_meeting_logs.php?status=${encodeURIComponent(status)}&month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('tableBody').innerHTML = data;
        });
}

// Load default table data
loadTable();

// Reload when any filter changes
document.getElementById('status').addEventListener('change', loadTable);
document.getElementById('month').addEventListener('change', loadTable);
document.getElementById('year').addEventListener('change', loadTable);
</script>

</body>
</html>
