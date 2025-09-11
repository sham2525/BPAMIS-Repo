<?php
/**
 * View Cases Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
$pageTitle = "View Cases";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Cases</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae2fd',
                            300: '#7cccfd',
                            400: '#36b3f9',
                            500: '#0c9ced',
                            600: '#0281d4',
                            700: '#026aad',
                            800: '#065a8f',
                            900: '#0a4b76'
                        }
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(to right, #f0f7ff, #e0effe);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }       
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <?php include '../includes/barangay_official_cap_nav.php'; ?>

    <!-- Page Header -->
    <section class="container mx-auto mt-8 px-4">
        <div class="gradient-bg rounded-xl shadow-sm p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h1 class="text-2xl font-medium text-primary-800">Cases List</h1>
                <p class="mt-1 text-gray-600">View and manage all barangay cases and their progress</p>
            </div>
        </div>
    </section>
    
    <div class="container mx-auto mt-8 px-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">        
            <!-- Search Bar -->
            <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div class="relative w-full md:w-1/2">
                    <input type="text" id="searchInput" placeholder="Search cases..." class="w-full p-3 pl-10 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex gap-2">
                    <select id="statusFilter" class="p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300">
    <option value="">All Statuses</option>
    <option value="Open">Open</option>
    <option value="Mediation">Mediation</option>
    <option value="Resolution">Resolution</option>
    <option value="Settlement">Settlement</option>
    <option value="Resolved">Resolved</option>
    <option value="Closed">Closed</option>
</select>
<select id="monthFilter" class="p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300">
    <option value="">All Months</option>
    <?php
    for ($m = 1; $m <= 12; $m++) {
        $monthName = date('F', mktime(0, 0, 0, $m, 1));
        echo "<option value=\"$m\">$monthName</option>";
    }
    ?>
</select>

<select id="yearFilter" class="p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300">
    <option value="">All Years</option>
    <?php
    $currentYear = date("Y");
    for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
        echo "<option value=\"$y\">$y</option>";
    }
    ?>
</select>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');
    const rows = document.querySelectorAll('#casesTable tr');

    function filterTable() {
        const searchQuery = searchInput.value.toLowerCase();
        const statusQuery = statusFilter.value.toLowerCase();
        const selectedMonth = monthFilter.value;
        const selectedYear = yearFilter.value;

        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const statusText = row.querySelector('td:nth-child(6)')?.innerText.toLowerCase();
            const dateFiled = row.querySelector('td:nth-child(5)')?.innerText; // Expected format: YYYY-MM-DD

            let matchesMonth = true;
            let matchesYear = true;

            if (dateFiled) {
                const date = new Date(dateFiled);
                if (selectedMonth) {
                    matchesMonth = (date.getMonth() + 1) == parseInt(selectedMonth);
                }
                if (selectedYear) {
                    matchesYear = date.getFullYear() == parseInt(selectedYear);
                }
            }

            const matchesSearch = rowText.includes(searchQuery);
            const matchesStatus = !statusQuery || statusText.includes(statusQuery);

            row.style.display = (matchesSearch && matchesStatus && matchesMonth && matchesYear) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    monthFilter.addEventListener('change', filterTable);
    yearFilter.addEventListener('change', filterTable);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('#casesTable tr');

    function filterTable() {
        const searchQuery = searchInput.value.toLowerCase();
        const statusQuery = statusFilter.value.toLowerCase();

        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const statusText = row.querySelector('td:nth-child(6)')?.innerText.toLowerCase(); // Status column

            const matchesSearch = rowText.includes(searchQuery);
            const matchesStatus = !statusQuery || statusText.includes(statusQuery);

            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
});
</script>

                    <a href="appoint_hearing.php" class="card-hover flex items-center bg-primary-500 text-white py-3 px-4 rounded-lg hover:bg-primary-600 transition">
                        <i class="fas fa-calendar-plus mr-2"></i> Schedule Hearing
                    </a>
                </div>
            </div>        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full mt-4">
                <thead>
                    <tr>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Case ID</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Case Title</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Complainant</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Respondent</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Date Filed</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Status</th>
                        <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Action</th>
                    </tr>
            </thead>
            <tbody id="casesTable">
                <?php
                // In a real application, this would be populated from database
                // For now, we'll use sample data
                
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT 
    cs.Case_ID,
    cs.Case_Status,
    ci.Complaint_ID,
    ci.Complaint_Title,
    ci.Date_Filed,
    COALESCE(res_com.First_Name, ext_com.First_Name) AS Complainant_First,
    COALESCE(res_com.Last_Name, ext_com.Last_Name) AS Complainant_Last
FROM CASE_INFO cs
LEFT JOIN COMPLAINT_INFO ci ON cs.Complaint_ID = ci.Complaint_ID
LEFT JOIN RESIDENT_INFO res_com ON ci.Resident_ID = res_com.Resident_ID
LEFT JOIN external_complainant ext_com ON ci.External_Complainant_ID = ext_com.External_Complaint_ID
ORDER BY ci.Date_Filed DESC";
$result = $conn->query($sql);                                           
if ($result->num_rows > 0):
    while ($case = $result->fetch_assoc()):
        $complaint_id = (int)$case['Complaint_ID'];
$respondent_names = [];

// Main respondent 
$main_res_sql = "SELECT First_Name, Last_Name FROM RESIDENT_INFO WHERE Resident_ID = (
    SELECT Respondent_ID FROM COMPLAINT_INFO WHERE Complaint_ID = $complaint_id
)";
$main_res_result = $conn->query($main_res_sql);
if ($main_res_result && $main_res_result->num_rows > 0) {
    while ($row = $main_res_result->fetch_assoc()) {
        $respondent_names[] = $row['First_Name'] . ' ' . $row['Last_Name'];
    }
}

// Additional respondents
$other_res_sql = "SELECT ri.First_Name, ri.Last_Name 
    FROM COMPLAINT_RESPONDENTS cr
    JOIN RESIDENT_INFO ri ON cr.Respondent_ID = ri.Resident_ID
    WHERE cr.Complaint_ID = $complaint_id";
$other_res_result = $conn->query($other_res_sql);
if ($other_res_result && $other_res_result->num_rows > 0) {
    while ($row = $other_res_result->fetch_assoc()) {
        $respondent_names[] = $row['First_Name'] . ' ' . $row['Last_Name'];
    }
}

// Combine all
$respondents_display = !empty($respondent_names) ? implode(', ', $respondent_names) : 'N/A';

?>
<tr class="border-b border-gray-100 hover:bg-gray-50 transition">
    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($case['Case_ID']) ?></td>
    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($case['Complaint_Title']) ?></td>
    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($case['Complainant_First'] . ' ' . $case['Complainant_Last']) ?></td>
    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($respondents_display) ?></td>
    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($case['Date_Filed']) ?></td>
    <td class="p-3 text-sm">
        <?php
        $status = $case['Case_Status'];
        $badge = [
            'Open' => ['text-blue-700 bg-blue-50 border-blue-200', 'fa-folder-open'],
            'Pending Hearing' => ['text-amber-700 bg-amber-50 border-amber-200', 'fa-calendar'],
            'Mediation' => ['text-purple-700 bg-purple-50 border-purple-200', 'fa-handshake'],
            'Resolved' => ['text-green-700 bg-green-50 border-green-200', 'fa-check-circle'],
            'Closed' => ['text-gray-700 bg-gray-50 border-gray-200', 'fa-folder'],
        ];
        $class = $badge[$status][0] ?? 'text-gray-700 bg-gray-50 border-gray-200';
        $icon = $badge[$status][1] ?? 'fa-info-circle';
        ?>
        <span class="px-2 py-1 rounded-full text-xs border <?= $class ?>">
            <i class="fas <?= $icon ?> mr-1"></i><?= $status ?>
        </span>
    </td>
    <td class="p-3 text-center">
        <div class="flex justify-center gap-2">
            <a href="view_case_details.php?id=<?= urlencode($case['Case_ID']) ?>" class="text-primary-600 hover:text-primary-800 transition p-1" title="View Details">
                <i class="fas fa-eye"></i>
            </a>
            <a href="update_case_status.php?id=<?= urlencode($case['Case_ID']) ?>" class="text-yellow-600 hover:text-yellow-800 transition p-1" title="Update Status">
                <i class="fas fa-edit"></i>
            </a>
            <a href="appoint_hearing.php?id=<?= urlencode($case['Case_ID']) ?>" class="text-green-600 hover:text-green-800 transition p-1" title="Schedule Hearing">
                <i class="fas fa-calendar-plus"></i>
            </a>
        </div>
    </td>
</tr>
<?php
    endwhile;
else:
    echo '<tr><td colspan="7" class="p-3 text-center text-gray-500">No cases found.</td></tr>';
endif;
?>

                
            </tbody>
        </table>
          <div class="mt-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
            <div>
                Showing 1-4 of 4 entries
            </div>
            <div class="flex mt-4 md:mt-0">
                <a href="#" class="mx-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition disabled:opacity-50">
                    <i class="fas fa-chevron-left mr-1"></i> Previous
                </a>
                <a href="#" class="mx-1 px-4 py-2 bg-primary-500 text-white rounded-lg transition">1</a>
                <a href="#" class="mx-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition disabled:opacity-50">
                    Next <i class="fas fa-chevron-right ml-1"></i>
                </a>
            </div>
            <?php include 'sidebar_.php';?>
        </div>
        </div>
    </div>    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function() {
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll('#casesTable tr');

                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
            
            // Mobile navigation toggle
            if (typeof menuButton !== 'undefined' && typeof mobileMenu !== 'undefined') {
                menuButton.addEventListener('click', function() {
                    this.classList.toggle('active');
                    if (mobileMenu.style.transform === 'translateY(0%)') {
                        mobileMenu.style.transform = 'translateY(-100%)';
                    } else {
                        mobileMenu.style.transform = 'translateY(0%)';
                    }
                });
            }
        });
    </script>
</body>
</html>
