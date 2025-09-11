<?php

$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
$pageTitle = "View Complaints";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaints</title>
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
                <h1 class="text-2xl font-medium text-primary-800">Complaints List</h1>
                <p class="mt-1 text-gray-600">View and manage all complaints filed in the barangay</p>
            </div>
        </div>
    </section>
        
    <!-- Complaints Table -->
    <div class="container mx-auto mt-8 px-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">        
            <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div class="relative w-full md:w-1/2">
                    <input type="text" id="searchInput" placeholder="Search complaints..." class="w-full p-3 pl-10 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">

                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex gap-2" id="dateFilter">
                    <select id="monthFilter" class="p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="">All Months</option>
                        <?php
                        foreach (range(1, 12) as $m) {
                            $monthName = date("F", mktime(0, 0, 0, $m, 10));
                            echo "<option value='$m'>$monthName</option>";
                        }
                        ?>
                    </select>

                    <select id="yearFilter" class="p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="">All Years</option>
                        <?php
                        $currentYear = date('Y');
                        for ($y = $currentYear; $y >= $currentYear - 10; $y--) {
                            echo "<option value='$y'>$y</option>";
                        }
                        ?>
                    </select>
                </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const monthSelect = document.getElementById('monthFilter');
                                const yearSelect = document.getElementById('yearFilter');
                                const tableRows = document.querySelectorAll('tbody tr');

                                function filterByDate() {
                                    const selectedMonth = monthSelect.value;
                                    const selectedYear = yearSelect.value;

                                    tableRows.forEach(row => {
                                        const dateFiled = row.getAttribute('data-datefiled');
                                        const [year, month] = dateFiled.split('-');

                                        const showByMonth = selectedMonth === '' || parseInt(month) === parseInt(selectedMonth);
                                        const showByYear = selectedYear === '' || parseInt(year) === parseInt(selectedYear);

                                        row.style.display = (showByMonth && showByYear) ? '' : 'none';
                                    });
                                }

                                monthSelect.addEventListener('change', filterByDate);
                                yearSelect.addEventListener('change', filterByDate);
                            });
                            </script>

                <div class="flex gap-2">
                    <select class="p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="">All Types</option>
                        <option value="Noise">Noise Complaint</option>
                        <option value="Property">Property Dispute</option>
                        <option value="Debt">Unpaid Debt</option>
                        <option value="Others">Others</option>
                    </select>
                    <a href="add_complaints.php" class="bg-blue-500 text-white py-3 px-4 rounded-lg hover:bg-blue-600 transition">
                        <i class="fas fa-plus mr-2"></i> Add New
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full mt-4">
                    <thead>
                        <tr>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2">Complaint ID</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2">Complainant</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2">Respondent</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2">Complaint Title</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2">Date Filed</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2">Status</th>
                            <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "
    SELECT 
        c.Complaint_ID,
        c.Complaint_Title,
        c.Date_Filed,
        c.Status,
        c.Resident_ID,
        c.External_Complainant_ID,
        r.First_Name AS Resident_First_Name,
        r.Last_Name AS Resident_Last_Name,
        e.First_Name AS External_First_Name,
        e.Last_Name AS External_Last_Name
    FROM COMPLAINT_INFO c
    LEFT JOIN RESIDENT_INFO r ON c.Resident_ID = r.Resident_ID
    LEFT JOIN external_complainant e ON c.External_Complainant_ID = e.External_Complaint_ID
    ORDER BY c.Date_Filed DESC
";



$result = $conn->query($sql);
$total = $result->num_rows;

if ($total > 0) {
    while ($complaint = $result->fetch_assoc()) {
        // Determine complainant name
       if (!empty($complaint['Resident_ID'])) {
    $complainantName = $complaint['Resident_First_Name'] . ' ' . $complaint['Resident_Last_Name'];
} elseif (!empty($complaint['External_Complainant_ID'])) {
    $complainantName = $complaint['External_First_Name'] . ' ' . $complaint['External_Last_Name'];
} else {
    $complainantName = 'Unknown';
}


        echo '<tr class="border-b hover:bg-gray-50" data-datefiled="' . $complaint['Date_Filed'] . '">';
        echo '<td class="p-3 text-sm text-gray-700">' . $complaint['Complaint_ID'] . '</td>';
        echo '<td class="p-3 text-sm text-gray-700">' . $complainantName . '</td>';
        
$complaint_id = $complaint['Complaint_ID'];
$respondent_names = [];

// Get main respondent
$mainResQuery = $conn->query("
    SELECT First_Name, Last_Name 
    FROM RESIDENT_INFO 
    WHERE Resident_ID = (SELECT Respondent_ID FROM COMPLAINT_INFO WHERE Complaint_ID = $complaint_id)
");
if ($mainResQuery && $mainResQuery->num_rows > 0) {
    $row = $mainResQuery->fetch_assoc();
    $respondent_names[] = $row['First_Name'] . ' ' . $row['Last_Name'];
}

// Get additional respondents
$additionalResQuery = $conn->query("
    SELECT r.First_Name, r.Last_Name 
    FROM COMPLAINT_RESPONDENTS cr
    JOIN RESIDENT_INFO r ON cr.Respondent_ID = r.Resident_ID
    WHERE cr.Complaint_ID = $complaint_id
");

if ($additionalResQuery && $additionalResQuery->num_rows > 0) {
    while ($row = $additionalResQuery->fetch_assoc()) {
        $respondent_names[] = $row['First_Name'] . ' ' . $row['Last_Name'];
    }
}

// Combine all respondent names
$respondents_str = count($respondent_names) > 0 ? implode(', ', $respondent_names) : 'N/A';

echo '<td class="p-3 text-sm text-gray-700">' . htmlspecialchars($respondents_str) . '</td>';

        echo '<td class="p-3 text-sm text-gray-700">' . $complaint['Complaint_Title'] . '</td>';
        echo '<td class="p-3 text-sm text-gray-700">' . $complaint['Date_Filed'] . '</td>';

        // Status badge
        $status = $complaint['Status'];
        switch ($status) {
            case 'Pending':
                $statusClass = 'text-amber-700 bg-amber-50 border border-amber-200';
                $statusIcon = '<i class="fas fa-clock mr-1"></i>';
                break;
            case 'Scheduled for Hearing':
                $statusClass = 'text-blue-700 bg-blue-50 border border-blue-200';
                $statusIcon = '<i class="fas fa-calendar-check mr-1"></i>';
                break;
            case 'Resolved':
                $statusClass = 'text-green-700 bg-green-50 border border-green-200';
                $statusIcon = '<i class="fas fa-check-circle mr-1"></i>';
                break;
            default:
                $statusClass = 'text-gray-700 bg-gray-50 border border-gray-200';
                $statusIcon = '<i class="fas fa-info-circle mr-1"></i>';
        }

        echo '<td class="p-3 text-sm">
                <span class="px-2 py-1 rounded-full text-xs ' . $statusClass . '">
                    ' . $statusIcon . $status . '
                </span>
              </td>';

        echo '<td class="p-3 text-center">
                <div class="flex justify-center gap-2">
                    <a href="view_complaint_details.php?id=' . $complaint['Complaint_ID'] . '" class="text-blue-600 hover:text-blue-800 transition p-1">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="view_complaint_details.php?id=' . $complaint['Complaint_ID'] . '&edit=true" class="text-yellow-600 hover:text-yellow-800 transition p-1">
                        <i class="fas fa-edit"></i>
                    </a>
                   
                </div>
              </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7" class="p-4 text-center text-gray-500">No complaints found.</td></tr>';
}

                    ?>
                    </tbody>
                </table>
            </div>

            <!-- Entries info -->
            <div class="mt-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
                <div>
                    <?php echo "Showing 1-$total of $total entries"; ?>
                </div>
                <div class="flex mt-4 md:mt-0">
                    <a href="#" class="mx-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition">
                        <i class="fas fa-chevron-left mr-1"></i> Previous
                    </a>
                    <a href="#" class="mx-1 px-4 py-2 bg-blue-500 text-white rounded-lg transition">1</a>
                    <a href="#" class="mx-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition">
                        Next <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

<script>
        document.addEventListener('DOMContentLoaded', function() {
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', function () {
            const searchValue = this.value.toLowerCase();

            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(searchValue) ? '' : 'none';
            });
        });
    });
</script> 
    <?php include 'sidebar_.php';?>
</body>
</html>
