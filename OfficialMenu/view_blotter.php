<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$blotters = [];
$result = $conn->query("SELECT * FROM BLOTTER_INFO ORDER BY Date_Reported DESC");
while ($row = $result->fetch_assoc()) {
    $blotters[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Blotter Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10 px-4">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-semibold text-blue-800 mb-6">All Blotter Reports</h2>

        <?php if (count($blotters) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300">
                    <thead class="bg-blue-100 text-blue-900">
    <tr>
        <th class="py-2 px-4 border-b">#</th>
        <th class="py-2 px-4 border-b text-left">Description</th>
        <th class="py-2 px-4 border-b text-left">Reported By</th>
        <th class="py-2 px-4 border-b">Date Reported</th>
        <th class="py-2 px-4 border-b text-center">Action</th>
    </tr>
</thead>
<tbody class="text-gray-700">
    <?php foreach ($blotters as $index => $b): ?>
        <tr class="hover:bg-gray-50">
            <td class="py-2 px-4 border-b text-center"><?= $index + 1 ?></td>
            <td class="py-2 px-4 border-b truncate max-w-[250px]"><?= htmlspecialchars($b['Blotter_Description']) ?></td>
            <td class="py-2 px-4 border-b"><?= htmlspecialchars($b['Reported_By']) ?></td>
            <td class="py-2 px-4 border-b text-center"><?= htmlspecialchars($b['Date_Reported']) ?></td>
            <td class="py-2 px-4 border-b text-center">
                <a href="view_blotter_details.php?id=<?= $b['Blotter_ID'] ?>" class="text-blue-600 hover:underline">View</a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No blotter reports found.</p>
        <?php endif; ?>
    </div>
    <?php include '../chatbot/bpamis_case_assistant.php'?>
</body>
</html>
