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
$hasDesc = false;
if ($chk = $conn->query("SHOW COLUMNS FROM complaint_info LIKE 'Complaint_Description'")) {
    $hasDesc = $chk->num_rows > 0;
    $chk->close();
}
$descField = $hasDesc ? 'co.Complaint_Description' : 'co.Complaint_Title';

$sqlCases = "
    SELECT 
        ci.Case_ID, 
        " . $descField . " AS complaint_desc,
        co.Complaint_ID AS Complaint_ID,
        ci.Case_Status,
        COALESCE(NULLIF(TRIM(LOWER(co.case_type)), ''), '') AS case_type,
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
        s.mediator_name LIKE CONCAT('%', ?, '%') OR
        ci.lupon_assign LIKE CONCAT('%', ?, '%')
    )
    ORDER BY ci.Case_ID ASC
";

$stmt = $conn->prepare($sqlCases);
if ($stmt) {
    $stmt->bind_param("ssss", $luponName, $luponName, $luponName, $luponName);
    $stmt->execute();
    $resultCases = $stmt->get_result();
    $stmt->close();
} else {
    // Fallback simple query if prepare fails (e.g., due to schema diffs)
    $esc = $conn->real_escape_string($luponName);
    $resultCases = $conn->query(
        "SELECT ci.Case_ID, " . $descField . " AS complaint_desc, co.Complaint_ID AS Complaint_ID, ci.Case_Status, 
            COALESCE(NULLIF(TRIM(LOWER(co.case_type)), ''), '') AS case_type,
            (
                SELECT sl.hearingdatetime FROM schedule_list sl
                WHERE sl.case_id = ci.Case_ID
                ORDER BY sl.hearingdatetime ASC LIMIT 1
            ) AS next_hearing
         FROM case_info ci
         JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID
         LEFT JOIN mediation_info mi ON mi.case_id = ci.Case_ID
         LEFT JOIN resolution r ON r.case_id = ci.Case_ID
         LEFT JOIN settlement s ON s.case_id = ci.Case_ID
         WHERE (
            mi.mediator_name LIKE '%$esc%' OR
            r.mediator_name LIKE '%$esc%' OR
            s.mediator_name LIKE '%$esc%' OR
            ci.lupon_assign LIKE '%$esc%'
         )
         ORDER BY ci.Case_ID ASC"
    );
}

// Buffer results into array for easier processing and counts
$cases = [];
if ($resultCases) {
    while ($row = $resultCases->fetch_assoc()) {
        $cases[] = $row;
    }
}

// Compute counts and filters
$totalCount = count($cases);
$openCount = 0;
$ongoingCount = 0;
$closedCount = 0;
$hearingCount = 0;
$noHearingCount = 0;

$months = [];
$years = [];
$now = new DateTime();

foreach ($cases as $c) {
    $status = isset($c['Case_Status']) ? (string)$c['Case_Status'] : '';
    $st = strtolower($status);
    if (strpos($st, 'close') !== false) {
        $closedCount++;
    }
    if (strpos($st, 'open') !== false || strpos($st, 'assign') !== false || strpos($st, 'pend') !== false) {
        $openCount++;
    }
    if (strpos($st, 'ongo') !== false || strpos($st, 'progress') !== false || strpos($st, 'hearing') !== false || strpos($st, 'mediate') !== false || strpos($st, 'sched') !== false) {
        $ongoingCount++;
    }

    $nh = isset($c['next_hearing']) ? $c['next_hearing'] : '';
    if (!empty($nh)) {
        $hearingCount++;
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $nh);
        if ($dt) {
            $months[(int)$dt->format('n')] = $dt->format('F');
            $years[$dt->format('Y')] = true;
        }
    } else {
        $noHearingCount++;
    }
}

// Normalize months and years for selects
ksort($months);
$yearKeys = array_keys($years);
sort($yearKeys);

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
                    animation: { 'float': 'float 3s ease-in-out infinite' },
                    keyframes: { float: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-10px)' } } }
                }
            }
        }
    </script>
    <style>
        .gradient-bg { background: linear-gradient(to right, #f0f7ff, #e0effe); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans relative overflow-x-hidden">
    <?php include '../includes/barangay_official_lupon_nav.php'; ?>
    <?php include 'sidebar_lupon.php'; ?>
    <!-- Global Blue Blush Background Orbs -->
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[480px] h-[480px] rounded-full bg-blue-200/40 blur-3xl animate-[float_14s_ease-in-out_infinite]"></div>
        <div class="absolute top-1/3 -right-52 w-[560px] h-[560px] rounded-full bg-cyan-200/40 blur-[160px] animate-[float_18s_ease-in-out_infinite]"></div>
        <div class="absolute -bottom-52 left-1/3 w-[520px] h-[520px] rounded-full bg-indigo-200/30 blur-3xl animate-[float_16s_ease-in-out_infinite]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[900px] rounded-full bg-gradient-to-br from-blue-50 via-white to-cyan-50 opacity-70 blur-[200px]"></div>
    </div>

    <!-- Page Header (Enhanced Hero) -->
            <div class="w-full mt-8 px-4">
                <div class="relative gradient-bg rounded-2xl shadow-sm p-8 md:p-10 overflow-hidden max-w-7xl mx-auto">
                    <div class="absolute top-0 right-0 w-72 h-72 bg-primary-100 rounded-full -mr-28 -mt-28 opacity-70 animate-[float_10s_ease-in-out_infinite]"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-primary-200 rounded-full -ml-16 -mb-16 opacity-60 animate-[float_7s_ease-in-out_infinite]"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[620px] h-[620px] bg-gradient-to-br from-primary-50 via-white to-primary-100 opacity-30 blur-3xl rounded-full pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
                        <div class="max-w-2xl">
                            <h1 class="text-3xl md:text-4xl font-light text-primary-900 tracking-tight">Your <span class="font-semibold">Assigned Cases</span></h1>
                            <p class="mt-4 text-gray-600 leading-relaxed">Review details and monitor progress. Use the smart filters below to quickly narrow your assigned workload.</p>
                            <div class="mt-5 flex flex-wrap gap-3 text-xs text-primary-700/80 font-medium">
                                <span class="px-3 py-1.5 rounded-full bg-white/70 backdrop-blur border border-primary-100 shadow-sm flex items-center gap-1"><i class="fa-solid fa-filter text-primary-500"></i> Smart Filters</span>
                                <span class="px-3 py-1.5 rounded-full bg-white/70 backdrop-blur border border-primary-100 shadow-sm flex items-center gap-1"><i class="fa-solid fa-chart-line text-primary-500"></i> Status Insights</span>
                                <span class="px-3 py-1.5 rounded-full bg-white/70 backdrop-blur border border-primary-100 shadow-sm flex items-center gap-1"><i class="fa-solid fa-clock-rotate-left text-primary-500"></i> Recent Focus</span>
                            </div>
                        </div>
                        <div class="hidden md:flex flex-col gap-2 min-w-[250px] text-sm text-gray-600">
                            <div>Lupon: <span class="font-medium text-gray-700"><?= htmlspecialchars($luponName) ?></span></div>
                        </div>
                    </div>
                </div>
            </div>

    <div class="w-full mt-8 px-4">
        <div class="max-w-7xl mx-auto">
            
                    <!-- Summary cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm card-hover">
                            <div class="text-xs uppercase text-gray-500">Total</div>
                            <div class="text-2xl font-semibold text-gray-800"><?= (int)$totalCount ?></div>
                        </div>
                        <div class="bg-white border border-blue-100 rounded-2xl p-4 shadow-sm card-hover">
                            <div class="text-xs uppercase text-blue-600">Open</div>
                            <div class="text-2xl font-semibold text-blue-700"><?= (int)$openCount ?></div>
                        </div>
                        <div class="bg-white border border-amber-100 rounded-2xl p-4 shadow-sm card-hover">
                            <div class="text-xs uppercase text-amber-600">On-going</div>
                            <div class="text-2xl font-semibold text-amber-700"><?= (int)$ongoingCount ?></div>
                        </div>
                        <div class="bg-white border border-emerald-100 rounded-2xl p-4 shadow-sm card-hover">
                            <div class="text-xs uppercase text-emerald-600">With Hearing</div>
                            <div class="text-2xl font-semibold text-emerald-700"><?= (int)$hearingCount ?></div>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm card-hover">
                            <div class="text-xs uppercase text-gray-600">Closed</div>
                            <div class="text-2xl font-semibold text-gray-700"><?= (int)$closedCount ?></div>
                        </div>
                    </div>
        </div>

        <?php if (!empty($cases)): ?>
        <!-- Filters / Search Card (copied style) -->
        <div class="relative max-w-7xl mx-auto bg-white/90 backdrop-blur-sm border border-gray-100 rounded-2xl shadow-sm p-6 md:p-7 overflow-hidden mb-4">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-primary-50 to-primary-100 rounded-full opacity-70"></div>
            <div class="absolute -bottom-12 -left-12 w-40 h-40 bg-gradient-to-tr from-primary-50 to-primary-100 rounded-full opacity-60"></div>
            <div class="relative z-10 space-y-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-3 text-primary-700/80 text-sm font-medium">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-50/70 border border-primary-100"><i class="fa-solid fa-magnifying-glass text-primary-500"></i> Search & Filter</span>
                        <span class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-50/70 border border-primary-100"><i class="fa-solid fa-sliders text-primary-500"></i> Refine Results</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="button" data-status="" class="status-chip active px-3 py-1.5 text-xs font-medium rounded-full bg-primary-600 text-white shadow-sm transition hover:shadow">All</button>
                    <button type="button" data-status="Open" class="status-chip px-3 py-1.5 text-xs font-medium rounded-full bg-primary-50 text-primary-600 border border-primary-100 hover:bg-primary-100 transition">Open</button>
                    <button type="button" data-status="Pending Hearing" class="status-chip px-3 py-1.5 text-xs font-medium rounded-full bg-amber-50 text-amber-600 border border-amber-100 hover:bg-amber-100 transition">Pending Hearing</button>
                    <button type="button" data-status="Mediation" class="status-chip px-3 py-1.5 text-xs font-medium rounded-full bg-purple-50 text-purple-600 border border-purple-100 hover:bg-purple-100 transition">Mediation</button>
                    <button type="button" data-status="Resolved" class="status-chip px-3 py-1.5 text-xs font-medium rounded-full bg-green-50 text-green-700 border border-green-100 hover:bg-green-100 transition">Resolved</button>
                    <button type="button" data-status="Closed" class="status-chip px-3 py-1.5 text-xs font-medium rounded-full bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 transition">Closed</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-7 relative group">
                        <input type="text" id="searchInput" placeholder="Search by case ID, description, or type..." class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200/80 bg-white/70 focus:ring-2 focus:ring-primary-200 focus:border-primary-400 placeholder:text-gray-400 text-sm transition" />
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-primary-400 group-focus-within:text-primary-500 transition"></i>
                    </div>
                    <div class="md:col-span-2 relative">
                        <select id="monthFilter" class="w-full pl-3 pr-8 py-3 rounded-xl border border-gray-200 bg-white/70 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 appearance-none">
                            <option value="">All Months</option>
                            <?php for ($m = 1; $m <= 12; $m++): $monthName = date('F', mktime(0,0,0,$m,1)); ?>
                                <option value="<?= $m ?>"><?= $monthName ?></option>
                            <?php endfor; ?>
                        </select>
                        <i class="fa-solid fa-caret-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-primary-400"></i>
                    </div>
                    <div class="md:col-span-2 relative">
                        <select id="yearFilter" class="w-full pl-3 pr-8 py-3 rounded-xl border border-gray-200 bg-white/70 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 appearance-none">
                            <option value="">All Years</option>
                            <?php $currentYear = date('Y'); for ($y = $currentYear; $y >= $currentYear - 5; $y--): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                        <i class="fa-solid fa-caret-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-primary-400"></i>
                    </div>
                    <div class="md:col-span-1 flex">
                        <button id="resetFilters" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-xl border border-primary-100 bg-primary-50/60 text-primary-600 text-sm font-medium hover:bg-primary-100 transition"><i class="fa-solid fa-rotate-left"></i><span class="hidden xl:inline">Reset</span></button>
                    </div>
                </div>
            </div>
        
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-table text-primary-500"></i> Case Records</h2>
                <span id="visibleCount" class="text-xs px-2.5 py-1 rounded-full bg-primary-50 text-primary-600 font-medium border border-primary-100">0 Showing</span>
            </div>
            <div class="overflow-x-auto rounded-lg border border-gray-100">
                <table id="casesTable" class="w-full mt-0">
                    <thead class="bg-primary-50/60">
                        <tr>
                            <th class="p-3 text-left text-[11px] font-semibold text-primary-700 uppercase tracking-wider border-b border-primary-100">Case ID</th>
                            <th class="p-3 text-left text-[11px] font-semibold text-primary-700 uppercase tracking-wider border-b border-primary-100">Complaint Description</th>
                            <th class="p-3 text-left text-[11px] font-semibold text-primary-700 uppercase tracking-wider border-b border-primary-100">Case Type</th>
                            <th class="p-3 text-left text-[11px] font-semibold text-primary-700 uppercase tracking-wider border-b border-primary-100">Status</th>
                            <th class="p-3 text-left text-[11px] font-semibold text-primary-700 uppercase tracking-wider border-b border-primary-100">Next Hearing</th>
                            <th class="p-3 text-center text-[11px] font-semibold text-primary-700 uppercase tracking-wider border-b border-primary-100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $case): 
                            $status = isset($case['Case_Status']) ? (string)$case['Case_Status'] : '';
                            $st = strtolower($status);
                            $hasHearing = !empty($case['next_hearing']);
                            $ctype = isset($case['case_type']) ? strtolower(trim($case['case_type'])) : '';
                            $ctypeLabel = $ctype;
                            $ctypeClass = 'text-gray-700 bg-gray-50 border-gray-200';
                            if ($ctype === 'civil case') { $ctypeLabel = 'Civil Case'; $ctypeClass = 'text-sky-700 bg-sky-50 border-sky-200'; }
                            elseif ($ctype === 'criminal case') { $ctypeLabel = 'Criminal Case'; $ctypeClass = 'text-rose-700 bg-rose-50 border-rose-200'; }
                            elseif ($ctype === 'blotter') { $ctypeLabel = 'Blotter'; $ctypeClass = 'text-slate-700 bg-slate-50 border-slate-200'; }
                            $statusClass = 'text-gray-700 bg-gray-50 border-gray-200';
                            $icon = 'fa-info-circle';
                            if (stripos($status,'Open') !== false) { $statusClass = 'text-blue-700 bg-blue-50 border-blue-200'; $icon = 'fa-folder-open'; }
                            elseif (stripos($status,'Pending Hearing') !== false) { $statusClass = 'text-amber-700 bg-amber-50 border-amber-200'; $icon = 'fa-calendar'; }
                            elseif (stripos($status,'Mediation') !== false) { $statusClass = 'text-purple-700 bg-purple-50 border-purple-200'; $icon = 'fa-handshake'; }
                            elseif (stripos($status,'Resolved') !== false) { $statusClass = 'text-green-700 bg-green-50 border-green-200'; $icon = 'fa-check-circle'; }
                            elseif (stripos($status,'Closed') !== false) { $statusClass = 'text-gray-700 bg-gray-50 border-gray-200'; $icon = 'fa-folder'; }
                            $desc = isset($case['complaint_desc']) ? $case['complaint_desc'] : '';
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-primary-50/40 transition" 
                            data-status="<?= htmlspecialchars($status) ?>"
                            data-has-hearing="<?= $hasHearing ? '1' : '0' ?>"
                            data-case-type="<?= htmlspecialchars($ctype) ?>"
                            data-title="<?= htmlspecialchars(strtolower($desc)) ?>"
                            data-caseid="<?= (int)$case['Case_ID'] ?>"
                            data-hearing="<?= htmlspecialchars($case['next_hearing'] ?? '') ?>"
                        >
                            <td class="p-3 text-sm text-gray-700 font-mono text-[11px] tracking-wide">#<?= (int)$case['Case_ID'] ?></td>
                            <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($desc) ?></td>
                            <td class="p-3 text-sm">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] border font-semibold <?= $ctypeClass ?>">
                                    <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($ctypeLabel ?: 'Not set') ?>
                                </span>
                            </td>
                            <td class="p-3 text-sm">
                                <span class="px-2.5 py-1 rounded-full text-[11px] border font-semibold <?= $statusClass ?>">
                                    <i class="fas <?= $icon ?> mr-1"></i><?= htmlspecialchars($status ?: '—') ?>
                                </span>
                            </td>
                            <td class="p-3 text-sm text-gray-700">
                                <?php if (!empty($case['next_hearing'])): ?>
                                    <?= date("M d, Y h:i A", strtotime($case['next_hearing'])) ?>
                                <?php else: ?>
                                    <span class="text-gray-500 italic">No hearing scheduled</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-sm text-center">
                                <a href="view_complaint_details.php?id=<?= urlencode($case['Complaint_ID']) ?>" 
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-primary-600 hover:text-white hover:bg-primary-600 transition" 
                                   title="View Complaint Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
                <div id="rangeDisplay">Showing 0 entries</div>
                <div class="flex mt-4 md:mt-0">
                    <a href="#" class="mx-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition disabled:opacity-50">
                        <i class="fas fa-chevron-left mr-1"></i> Previous
                    </a>
                    <a href="#" class="mx-1 px-4 py-2 bg-primary-500 text-white rounded-lg transition">1</a>
                    <a href="#" class="mx-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition disabled:opacity-50">
                        Next <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <?php else: ?>
            <p class="text-gray-600">No cases currently assigned to you.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');
    const resetBtn = document.getElementById('resetFilters');
    const rows = document.querySelectorAll('#casesTable tbody tr');
    const visibleCount = document.getElementById('visibleCount');
    const rangeDisplay = document.getElementById('rangeDisplay');
    const chips = document.querySelectorAll('.status-chip');
    let chipStatusOverride = '';

    function filterTable() {
        const searchQuery = (searchInput.value || '').toLowerCase();
        const statusQuery = (chipStatusOverride || '').toLowerCase();
        const selectedMonth = monthFilter.value;
        const selectedYear = yearFilter.value;
        let shown = 0;

        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const statusCell = row.querySelector('td:nth-child(4)');
            const statusText = statusCell ? statusCell.innerText.toLowerCase() : '';
            const hearingCell = row.querySelector('td:nth-child(5)');
            const hearingText = hearingCell ? hearingCell.innerText : '';
            let matchesMonth = true, matchesYear = true;
            if (selectedMonth || selectedYear) {
                if (!hearingText || hearingText.toLowerCase().includes('no hearing')) {
                    matchesMonth = matchesYear = false;
                } else {
                    const d = new Date(hearingText);
                    if (selectedMonth) matchesMonth = (d.getMonth()+1) == parseInt(selectedMonth);
                    if (selectedYear) matchesYear = d.getFullYear() == parseInt(selectedYear);
                }
            }
            const matchesSearch = rowText.includes(searchQuery);
            const matchesStatus = !statusQuery || (statusText && statusText.includes(statusQuery));
            const show = matchesSearch && matchesStatus && matchesMonth && matchesYear;
            row.style.display = show ? '' : 'none';
            if (show) shown++;
        });
        if (visibleCount) visibleCount.textContent = shown + ' Showing';
        if (rangeDisplay) rangeDisplay.textContent = 'Showing ' + shown + ' entr' + (shown === 1 ? 'y' : 'ies');
    }

    function resetFilters() {
        if (searchInput) searchInput.value = '';
        if (monthFilter) monthFilter.value = '';
        if (yearFilter) yearFilter.value = '';
        chipStatusOverride = '';
        chips.forEach(c => c.classList.remove('active','bg-primary-600','text-white','shadow'));
        const allChip = document.querySelector('.status-chip[data-status=""]');
        if (allChip) allChip.classList.add('active','bg-primary-600','text-white','shadow');
        filterTable();
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (monthFilter) monthFilter.addEventListener('change', filterTable);
    if (yearFilter) yearFilter.addEventListener('change', filterTable);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chips.forEach(c => c.classList.remove('active','bg-primary-600','text-white','shadow'));
            chip.classList.add('active','bg-primary-600','text-white','shadow');
            chipStatusOverride = chip.dataset.status || '';
            filterTable();
        });
    });

    resetFilters(); // sets initial state and calls filterTable
});
</script>

<?php $conn->close(); ?>
