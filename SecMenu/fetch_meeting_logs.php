<?php
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$status_filter = $_GET['status'] ?? 'All';
$month_filter = $_GET['month'] ?? 'All';
$year_filter = $_GET['year'] ?? 'All';

$sql = "SELECT ci.Case_ID, ci.Case_Status, co.Complaint_Title, co.Date_Filed, sl.hearingDateTime
        FROM CASE_INFO ci
        JOIN COMPLAINT_INFO co ON ci.Complaint_ID = co.Complaint_ID
        LEFT JOIN schedule_list sl ON ci.Case_ID = sl.Case_ID
        WHERE ci.Case_Status NOT IN ('Resolved', 'Dismissed')";

// Status filter
if ($status_filter !== 'All') {
    $sql .= " AND ci.Case_Status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Month filter
if ($month_filter !== 'All') {
    $sql .= " AND MONTH(co.Date_Filed) = " . intval($month_filter);
}

// Year filter
if ($year_filter !== 'All') {
    $sql .= " AND YEAR(co.Date_Filed) = " . intval($year_filter);
}

$sql .= " ORDER BY co.Date_Filed DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = $row['Case_Status'];
        $badge = [
            'Open' => ['text-blue-700 bg-blue-50 border-blue-200', 'fa-folder-open'],
            'Pending Hearing' => ['text-amber-700 bg-amber-50 border-amber-200', 'fa-calendar'],
            'Mediation' => ['text-purple-700 bg-purple-50 border-purple-200', 'fa-handshake'],
            'Resolution' => ['text-green-700 bg-green-50 border-green-200', 'fa-check-circle'],
            'Settlement' => ['text-pink-700 bg-pink-50 border-pink-200', 'fa-scale-balanced'],
            'Closed' => ['text-gray-700 bg-gray-50 border-gray-200', 'fa-folder'],
        ];
        $class = $badge[$status][0] ?? 'text-gray-700 bg-gray-50 border-gray-200';
        $icon = $badge[$status][1] ?? 'fa-info-circle';

        // Split date & time
        $hearingDate = '—';
        $hearingTime = '—';
        if (!empty($row['hearingDateTime'])) {
            $timestamp = strtotime($row['hearingDateTime']);
            $hearingDate = date("Y-m-d", $timestamp);  // Example: 2025-08-11
            $hearingTime = date("h:i A", $timestamp);  // Example: 02:45 PM
        }

        echo "<tr class='border-b border-gray-100 hover:bg-gray-50 transition'>
                <td class='p-3 text-sm text-gray-700'>C" . htmlspecialchars($row['Case_ID']) . "</td>
                <td class='p-3 text-sm text-gray-700'>" . htmlspecialchars($row['Complaint_Title']) . "</td>
                <td class='p-3 text-sm text-gray-700'>" . htmlspecialchars($row['Date_Filed']) . "</td>
                <td class='p-3 text-sm text-gray-700'>" . htmlspecialchars($hearingDate) . "</td>
                <td class='p-3 text-sm text-gray-700'>" . htmlspecialchars($hearingTime) . "</td>
                <td class='px-4 py-2'>
                    <span class='px-2 py-1 rounded-full text-xs border {$class}'>
                        <i class='fas {$icon} mr-1'></i>" . htmlspecialchars($status) . "
                    </span>
                </td>
                <td class='p-3 text-center'>
                    <a href='meeting_cases_log.php?id=" . urlencode($row['Case_ID']) . "' 
                       class='text-yellow-600 hover:text-yellow-800 transition p-1' title='Fill Log'>
                       <i class='fas fa-edit'></i>
                    </a>
                </td>
              </tr>";
    }
} else {
    echo "<tr>
            <td colspan='7' class='text-center px-4 py-4 text-gray-500'>No cases found.</td>
          </tr>";
}

$conn->close();
?>
