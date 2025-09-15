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
                    boxShadow: { glow:'0 0 0 1px rgba(12,156,237,0.10), 0 10px 24px -8px rgba(6,90,143,0.25)' },
                    animation: { 'float':'float 6s ease-in-out infinite' },
                    keyframes: { float: { '0%, 100%': { transform:'translateY(0)' }, '50%': { transform:'translateY(-8px)' } } }
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
        .chip { display:inline-flex; align-items:center; gap:.4rem; font-size:.75rem; font-weight:600; padding:.35rem .7rem; border-radius:9999px; border:1px solid #e5e7eb; background:#fff; color:#374151; transition:all .2s ease; }
        .chip.active, .chip:hover { border-color:#bae2fd; background:#f0f7ff; color:#065a8f; }
        .shadow-glow { box-shadow: 0 0 0 1px rgba(12,156,237,0.08), 0 10px 24px -8px rgba(6,90,143,0.20); }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <?php include '../includes/barangay_official_cap_nav.php'; ?>

    <!-- Global Background Blush (floating orbs) -->
    <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
        <div class="absolute -top-16 -right-24 w-[28rem] h-[28rem] bg-primary-100 rounded-full blur-3xl opacity-60 animate-float"></div>
        <div class="absolute top-1/3 -left-24 w-[22rem] h-[22rem] bg-primary-200 rounded-full blur-3xl opacity-50 animate-float"></div>
        <div class="absolute -bottom-24 right-1/4 w-[20rem] h-[20rem] bg-primary-50 rounded-full blur-2xl opacity-60 animate-float" style="animation-delay:1.2s"></div>
    </div>

    <!-- Page Header -->
    <section class="container mx-auto mt-8 px-4">
        <div class="gradient-bg rounded-2xl shadow-sm p-6 md:p-8 relative overflow-hidden border border-primary-100/60 shadow-glow">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70 animate-float"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60 animate-float"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-medium text-primary-800">Cases List</h1>
                    <p class="mt-1 text-gray-600">View and manage all barangay cases and their progress</p>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-600">
                    <span class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fas fa-shield-halved text-primary-500"></i> Secure Data</span>
                    <span class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fas fa-database text-primary-500"></i> Active Records</span>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container mx-auto mt-8 px-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <!-- Enhanced Filter Panel -->
            <div class="relative mb-6 rounded-2xl border border-primary-100/60 bg-gradient-to-r from-white to-primary-50/40 p-4 md:p-6 shadow-glow overflow-hidden">
                <div class="absolute -top-16 -right-10 w-48 h-48 bg-primary-100 rounded-full opacity-70 blur-2xl animate-float"></div>
                <div class="absolute -bottom-10 -left-10 w-36 h-36 bg-primary-200 rounded-full opacity-60 blur-2xl animate-float" style="animation-delay:.8s"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                    <div class="relative w-full md:w-1/2">
                        <input type="text" id="searchInput" placeholder="Search cases..." class="w-full p-3 pl-10 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300 bg-white/80 backdrop-blur">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="flex gap-2" id="dateFilter">
                        <select id="monthFilter" class="p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300 bg-white/80 backdrop-blur">
                            <option value="">All Months</option>
                            <?php
                            for ($m = 1; $m <= 12; $m++) {
                                $monthName = date('F', mktime(0, 0, 0, $m, 1));
                                echo "<option value=\"$m\">$monthName</option>";
                            }
                            ?>
                        </select>
                        <select id="yearFilter" class="p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300 bg-white/80 backdrop-blur">
                            <option value="">All Years</option>
                            <?php
                            $currentYear = date("Y");
                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                echo "<option value=\"$y\">$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <a href="appoint_hearing.php" class="card-hover flex items-center bg-primary-600 text-white py-3 px-4 rounded-lg hover:bg-primary-700 transition shadow">
                            <i class="fas fa-calendar-plus mr-2"></i> Schedule Hearing
                        </a>
                    </div>
                </div>
                <!-- Status Filter Chips -->
                <div class="relative z-10 mt-4 flex flex-wrap gap-2">
                    <button class="chip active" data-status="all"><i class="fa-solid fa-layer-group"></i> All</button>
                    <button class="chip" data-status="open"><i class="fa-solid fa-folder-open"></i> Open</button>
                    <button class="chip" data-status="pending hearing"><i class="fa-solid fa-calendar"></i> Pending Hearing</button>
                    <button class="chip" data-status="mediation"><i class="fa-solid fa-handshake"></i> Mediation</button>
                    <button class="chip" data-status="resolution"><i class="fa-solid fa-scale-balanced"></i> Resolution</button>
                    <button class="chip" data-status="settlement"><i class="fa-solid fa-hand-holding-heart"></i> Settlement</button>
                    <button class="chip" data-status="resolved"><i class="fa-solid fa-circle-check"></i> Resolved</button>
                    <button class="chip" data-status="closed"><i class="fa-solid fa-folder"></i> Closed</button>
                    <button class="chip" id="resetFilters"><i class="fa-solid fa-rotate"></i> Reset</button>
                </div>
            </div>
            <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full mt-4">
                <thead>
                    <tr>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Case ID</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Case Description</th>
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
<tr class="border-b border-gray-100 hover:bg-gray-50 transition" data-datefiled="<?= htmlspecialchars($case['Date_Filed']) ?>" data-status="<?= htmlspecialchars(strtolower(trim($case['Case_Status']))) ?>">
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
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('searchInput');
                const monthSelect = document.getElementById('monthFilter');
                const yearSelect = document.getElementById('yearFilter');
                const chips = document.querySelectorAll('.chip[data-status]');
                const resetBtn = document.getElementById('resetFilters');
                const rows = Array.from(document.querySelectorAll('#casesTable tr'));

                let selectedStatus = 'all';

                function rowMatches(row, search, month, year, status) {
                    // Search
                    const text = row.textContent.toLowerCase();
                    const okSearch = !search || text.includes(search);
                    // Date
                    const dateFiled = row.getAttribute('data-datefiled') || '';
                    const parts = dateFiled.split('-');
                    const yr = parseInt(parts[0]) || 0;
                    const mo = parseInt(parts[1]) || 0;
                    const okMonth = !month || mo === parseInt(month);
                    const okYear = !year || yr === parseInt(year);
                    // Status
                    const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
                    const okStatus = status === 'all' || rowStatus === status;
                    return okSearch && okMonth && okYear && okStatus;
                }

                function applyFilters() {
                    const search = (searchInput.value || '').toLowerCase();
                    const month = monthSelect.value;
                    const year = yearSelect.value;
                    rows.forEach(row => {
                        row.style.display = rowMatches(row, search, month, year, selectedStatus) ? '' : 'none';
                    });
                }

                searchInput.addEventListener('input', applyFilters);
                monthSelect.addEventListener('change', applyFilters);
                yearSelect.addEventListener('change', applyFilters);

                chips.forEach(chip => {
                    chip.addEventListener('click', () => {
                        chips.forEach(c => c.classList.remove('active'));
                        chip.classList.add('active');
                        selectedStatus = (chip.getAttribute('data-status') || 'all').toLowerCase();
                        applyFilters();
                    });
                });

                if (resetBtn) {
                    resetBtn.addEventListener('click', () => {
                        searchInput.value = '';
                        monthSelect.value = '';
                        yearSelect.value = '';
                        selectedStatus = 'all';
                        chips.forEach(c => c.classList.remove('active'));
                        const allChip = document.querySelector('.chip[data-status="all"]');
                        if (allChip) allChip.classList.add('active');
                        applyFilters();
                    });
                }

                applyFilters();

                // Optional: Mobile navigation toggle if available globally
                if (typeof menuButton !== 'undefined' && typeof mobileMenu !== 'undefined') {
                    menuButton.addEventListener('click', function() {
                        this.classList.toggle('active');
                        mobileMenu.style.transform = (mobileMenu.style.transform === 'translateY(0%)') ? 'translateY(-100%)' : 'translateY(0%)';
                    });
                }
            });
        </script>
</body>
</html>
