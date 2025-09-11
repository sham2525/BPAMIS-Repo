<?php
// Secretary Home (Premium Glass UI)
include '../server/server.php';
session_start();

// ===================== NUMERIC AGGREGATES ===================== //
$complaintsCount = $resolvedCount = $pendingCount = $rejectedCount = 0;
$casesCount = $mediatedCount = $resolutionCount = $settlementCount = $closedCount = $resolvedCaseCount = 0;
$scheduledHearings = 0;

// Complaints distribution
if ($result = $conn->query("SELECT status, COUNT(*) as count FROM complaint_info GROUP BY status")) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower(trim($row['status']));
        $count = (int) $row['count'];
        $complaintsCount += $count;
        if ($status === 'resolved')
            $resolvedCount = $count;
        elseif ($status === 'pending')
            $pendingCount = $count;
        elseif ($status === 'rejected')
            $rejectedCount = $count;
    }
}

// Case distribution
if ($result = $conn->query("SELECT case_status as status, COUNT(*) as count FROM case_info GROUP BY case_status")) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower(trim($row['status']));
        $count = (int) $row['count'];
        $casesCount += $count;
        if ($status === 'mediation')
            $mediatedCount = $count;
        elseif ($status === 'resolution')
            $resolutionCount = $count;
        elseif ($status === 'settlement')
            $settlementCount = $count;
        elseif ($status === 'close')
            $closedCount = $count;
        elseif ($status === 'resolved')
            $resolvedCaseCount = $count;
    }
}

// Hearing total
if ($result = $conn->query("SELECT COUNT(*) as count FROM schedule_list")) {
    if ($row = $result->fetch_assoc())
        $scheduledHearings = (int) $row['count'];
}

// Progress percentages (guard division by zero)
$complaintResolvedPercent = $complaintsCount ? round(($resolvedCount / $complaintsCount) * 100) : 0;
$complaintPendingPercent = $complaintsCount ? round(($pendingCount / $complaintsCount) * 100) : 0;
$complaintRejectedPercent = $complaintsCount ? round(($rejectedCount / $complaintsCount) * 100) : 0;
$caseResolvedPercent = $casesCount ? round(($resolvedCaseCount / $casesCount) * 100) : 0;
// Treat hearing progress as proportion of cases that have at least one scheduled hearing (approximation)
$hearingPercent = $casesCount ? min(100, round(($scheduledHearings / max($casesCount, 1)) * 100)) : 0;

// ===================== RECENT ACTIVITY ===================== //
function getComplainantName($conn, $resident_id, $external_id)
{
    if (!empty($resident_id)) {
        $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM resident_info WHERE resident_id = ?");
        $stmt->bind_param("i", $resident_id);
    } else {
        $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM external_complainant WHERE external_complaint_id = ?");
        $stmt->bind_param("i", $external_id);
    }
    $stmt->execute();
    $person = $stmt->get_result()->fetch_assoc();
    return $person ? trim($person['first_name'] . ' ' . ($person['middle_name'] ? substr($person['middle_name'], 0, 1) . '. ' : '') . $person['last_name']) : 'Unknown';
}

$recentActivities = [];
if ($result = $conn->query("SELECT complaint_id, resident_id, external_complainant_id, date_filed FROM complaint_info ORDER BY date_filed DESC LIMIT 6")) {
    while ($row = $result->fetch_assoc()) {
        $recentActivities[] = [
            'message' => 'New complaint filed by ' . htmlspecialchars(getComplainantName($conn, $row['resident_id'], $row['external_complainant_id'])),
            'time' => $row['date_filed']
        ];
    }
}

// ===================== MONTHLY SERIES (6 months) ===================== //
$monthlyLabels = $monthlyComplaints = $monthlyCases = $monthlyMediation = $monthlyResolution = $monthlySettlement = $monthlyClosed = $monthlyResolved = [];
for ($i = 5; $i >= 0; $i--) {
    $monthKey = date('Y-m', strtotime("-{$i} months"));
    $monthlyLabels[] = date('M Y', strtotime($monthKey . '-01'));
    // Complaint counts
    $val = $conn->query("SELECT COUNT(*) AS c FROM complaint_info WHERE DATE_FORMAT(date_filed,'%Y-%m')='$monthKey'");
    $monthlyComplaints[] = $val ? (int) $val->fetch_assoc()['c'] : 0;
    // Case breakdown
    $caseBreak = ['mediation' => 0, 'resolution' => 0, 'settlement' => 0, 'close' => 0, 'resolved' => 0];
    if ($resCase = $conn->query("SELECT case_status, COUNT(*) c FROM case_info c JOIN complaint_info ci ON c.complaint_id=ci.complaint_id WHERE DATE_FORMAT(ci.date_filed,'%Y-%m')='$monthKey' GROUP BY case_status")) {
        while ($r = $resCase->fetch_assoc()) {
            $k = strtolower($r['case_status']);
            $caseBreak[$k] = (int) $r['c'];
        }
    }
    $monthlyCases[] = array_sum($caseBreak);
    $monthlyMediation[] = $caseBreak['mediation'];
    $monthlyResolution[] = $caseBreak['resolution'];
    $monthlySettlement[] = $caseBreak['settlement'];
    $monthlyClosed[] = $caseBreak['close'];
    $monthlyResolved[] = $caseBreak['resolved'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretary Dashboard</title>
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
        body {
            background: radial-gradient(circle at 20% 20%, #e0f2ff 0%, #f5f9ff 50%, #ffffff 100%);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            opacity: .55;
            mix-blend-mode: multiply;
        }

        .orb.one {
            width: 480px;
            height: 480px;
            background: linear-gradient(135deg, #0c9ced, #7cccfd);
            top: -140px;
            right: -120px;
            animation: float 14s ease-in-out infinite;
        }

        .orb.two {
            width: 360px;
            height: 360px;
            background: linear-gradient(135deg, #bae2fd, #e0effe);
            bottom: -120px;
            left: -100px;
            animation: float 11s ease-in-out reverse infinite;
        }

        .glass {
            backdrop-filter: blur(14px);
            background: linear-gradient(135deg, rgba(255, 255, 255, .65), rgba(255, 255, 255, .35));
            border: 1px solid rgba(255, 255, 255, .45);
            box-shadow: 0 10px 40px -12px rgba(12, 156, 237, .25), 0 4px 18px -6px rgba(12, 156, 237, .18);
        }

        .stat-chip {
            @apply inline-flex items-center text-xs font-medium px-2 py-1 rounded-md bg-white/60 backdrop-blur border border-white/40 shadow-sm;
        }

        .progress-wrap {
            height: 12px; /* enlarged for better visibility */
        }
        .progress-wrap .progress-bar { box-shadow: 0 0 0 1px rgba(255,255,255,.4), 0 2px 6px -1px rgba(12,156,237,.25); }
        /* Equal height cards */
        .dashboard-equal-row { display:flex; flex-wrap:wrap; gap:2rem; }
        .dashboard-equal-row > .card-flex { display:flex; flex-direction:column; }
        @media (min-width:1024px){
            .dashboard-equal-row > .card-flex { flex:1 1 0; }
            .dashboard-equal-row { align-items:stretch; }
            .card-flex .card-body-grow { flex:1 1 auto; display:flex; flex-direction:column; }
        }

        .progress-bar {
            transition: width 1s cubic-bezier(.4, .0, .2, 1);
        }

        .section-label {
            font-size: .65rem;
            letter-spacing: .09em;
            font-weight: 600;
            text-transform: uppercase;
            color: #0369a1;
        }

        .quick-btn {
            position: relative;
            overflow: hidden;
        }

        .quick-btn:before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255, 255, 255, .6), rgba(255, 255, 255, 0));
            opacity: 0;
            transition: opacity .4s;
        }

        .quick-btn:hover:before {
            opacity: 1;
        }

        .quick-btn:hover {
            transform: translateY(-4px);
        }

        .fade-in {
            animation: fade .6s ease;
        }

        @keyframes fade {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loader retained */
        .loader-wrapper {
            position: fixed;
            inset: 0;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            transition: opacity .6s;
        }

        .loader {
            position: relative;
            width: 120px;
            height: 120px;
        }

        .loader-gradient {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: conic-gradient(#60a5fa 0deg, rgba(37, 100, 235, .66) 120deg, rgba(30, 64, 175, .34) 240deg, #60a5fa 360deg);
            animation: spin 1.2s linear infinite;
        }

        .loader-inner {
            position: absolute;
            inset: 10px;
            background: rgba(255, 255, 255, .85);
            border-radius: 50%;
        }

        .loader-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 58px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .fade-out {
            opacity: 0;
            pointer-events: none;
        }

        /* Calendar refine */
        .calendar-container {
            --fc-border-color: transparent;
        }

        .calendar-container .fc-theme-standard th {
            border: none;
            font-size: .65rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #0c4a6e;
            background: transparent;
        }

        .calendar-container .fc-daygrid-day {
            background: rgba(255, 255, 255, .55);
            backdrop-filter: blur(6px);
        }
    </style>
</head>

<body class="font-sans text-gray-700 relative overflow-x-hidden">
    <div class="orb one"></div>
    <div class="orb two"></div>

    <!-- Add this at the very top of the body -->
    <div class="loader-wrapper">
        <div class="loader">
            <div class="loader-gradient"></div>
            <div class="loader-inner"></div>
            <img src="logo.png" alt="BPAMIS Logo" class="loader-logo">
        </div>
    </div>

    <?php include '../includes/barangay_official_sec_nav.php'; ?>

    <!-- HEADER / INTRO -->
    <div class="max-w-7xl mx-auto px-5 pt-10 relative">
        <div class="glass rounded-3xl p-8 md:p-12 overflow-hidden fade-in">
            <div class="absolute inset-0 pointer-events-none">
                <div
                    class="absolute -top-20 -right-10 w-80 h-80 bg-gradient-to-br from-primary-200/70 to-primary-400/40 rounded-full blur-3xl opacity-60">
                </div>
                <div
                    class="absolute -bottom-24 -left-10 w-72 h-72 bg-gradient-to-tr from-primary-100/60 via-white/40 to-primary-300/40 rounded-full blur-3xl">
                </div>
            </div>
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                    <div>
                        <p class="section-label mb-2">Secretary Dashboard</p>
                        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-sky-900">Welcome back<span
                                class="font-light">,</span></h1>
                        <p class="mt-2 text-sky-800 text-lg font-medium">
                            <?= isset($_SESSION['official_name']) ? htmlspecialchars($_SESSION['official_name']) : 'Barangay Secretary' ?>
                        </p>
                        <p class="mt-3 max-w-xl text-sm md:text-base text-sky-700/80">Manage complaints, guide disputes
                            through mediation, and keep community justice moving with real‑time intelligence.</p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4 w-full md:w-auto">
                        <a href="add_complaints.php"
                            class="quick-btn glass group rounded-xl px-4 py-3 flex flex-col items-start gap-1 hover:shadow-lg transition">
                            <div class="flex items-center gap-2 text-sky-700"><i
                                    class="fa-solid fa-square-plus text-sky-600"></i><span
                                    class="text-xs font-semibold tracking-wide uppercase">New</span></div>
                            <span class="text-[13px] font-medium text-sky-900">Complaint</span>
                        </a>
                        <a href="view_cases.php"
                            class="quick-btn glass group rounded-xl px-4 py-3 flex flex-col items-start gap-1">
                            <div class="flex items-center gap-2 text-emerald-700"><i
                                    class="fa-solid fa-gavel text-emerald-600"></i><span
                                    class="text-xs font-semibold tracking-wide uppercase">View</span></div>
                            <span class="text-[13px] font-medium text-emerald-900">Cases</span>
                        </a>
                        <a href="meeting_log.php"
                            class="quick-btn glass group rounded-xl px-4 py-3 flex flex-col items-start gap-1">
                            <div class="flex items-center gap-2 text-indigo-700"><i
                                    class="fa-solid fa-file-lines text-indigo-600"></i><span
                                    class="text-xs font-semibold tracking-wide uppercase">Logs</span></div>
                            <span class="text-[13px] font-medium text-indigo-900">Meetings</span>
                        </a>
                        <a href="appoint_hearing.php"
                            class="quick-btn glass group rounded-xl px-4 py-3 flex flex-col items-start gap-1">
                            <div class="flex items-center gap-2 text-rose-700"><i
                                    class="fa-solid fa-calendar-days text-rose-600"></i><span
                                    class="text-xs font-semibold tracking-wide uppercase">Set</span></div>
                            <span class="text-[13px] font-medium text-rose-900">Hearing</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="max-w-7xl mx-auto px-5 mt-10 pb-16 space-y-10">
        <div class="dashboard-equal-row">
            <!-- Left Column: KPIs -->
            <div class="card-flex lg:col-span-5 space-y-8 w-full">
                <div class="glass rounded-2xl p-6 md:p-7 fade-in card-body-grow">
                    <div class="flex items-center gap-2 mb-5">
                        <i class="fa-solid fa-chart-simple text-sky-600"></i>
                        <h2 class="text-sky-900 font-semibold tracking-tight">Statistics</h2>
                    </div>
                    <div class="space-y-6">
                        <!-- Complaints (Multi-bar by Status) -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-sky-800 tracking-wide">Total Complaints</span>
                                <span
                                    class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-sky-100 text-sky-700"><?= $complaintsCount ?></span>
                            </div>
                            <div class="space-y-2">
                                <!-- Resolved -->
                                <div>
                                    <div class="flex justify-between text-[10px] font-medium text-emerald-700 mb-0.5">
                                        <span>Resolved</span><span><?= $resolvedCount ?> </span></div>
                                    <div class="w-full h-2.5 rounded-full bg-emerald-50 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 progress-bar"
                                            style="width: <?= $complaintResolvedPercent ?>%"></div>
                                    </div>
                                </div>
                                <!-- Pending -->
                                <div>
                                    <div class="flex justify-between text-[10px] font-medium text-amber-700 mb-0.5">
                                        <span>Pending</span><span><?= $pendingCount ?> </span></div>
                                    <div class="w-full h-2.5 rounded-full bg-amber-50 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 progress-bar"
                                            style="width: <?= $complaintPendingPercent ?>%"></div>
                                    </div>
                                </div>
                                <!-- Rejected -->
                                <div>
                                    <div class="flex justify-between text-[10px] font-medium text-rose-700 mb-0.5">
                                        <span>Rejected</span><span><?= $rejectedCount ?> </span></div>
                                    <div class="w-full h-2.5 rounded-full bg-rose-50 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-rose-400 to-rose-500 progress-bar"
                                            style="width: <?= $complaintRejectedPercent ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Cases -->
                        <div>
                            <div class="flex justify-between mb-1.5 text-xs font-medium text-emerald-800"><span>Total
                                    Cases</span><span
                                    class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700"><?= $casesCount ?></span>
                            </div>
                            <div class="w-full bg-white/60 rounded-full progress-wrap overflow-hidden">
                                <div class="progress-bar bg-gradient-to-r from-emerald-400 to-emerald-500 h-full"
                                    style="width: <?= $caseResolvedPercent ?>%"></div>
                            </div>
                            <p class="text-[10px] mt-1 text-emerald-800/70">Mediation: <?= $mediatedCount ?> •
                                Resolution: <?= $resolutionCount ?> • Settlement: <?= $settlementCount ?> • Closed:
                                <?= $closedCount ?> • Resolved: <?= $resolvedCaseCount ?></p>
                        </div>
                        <!-- Hearings -->
                        <div>
                            <div class="flex justify-between mb-1.5 text-xs font-medium text-rose-800"><span>Scheduled
                                    Hearings</span><span
                                    class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-700"><?= $scheduledHearings ?></span>
                            </div>
                            <div class="w-full bg-white/60 rounded-full progress-wrap overflow-hidden">
                                <div class="progress-bar bg-gradient-to-r from-rose-400 to-rose-500 h-full"
                                    style="width: <?= $hearingPercent ?>%"></div>
                            </div>
                            <p class="text-[10px] mt-1 text-rose-800/70">Relative to open cases</p>
                        </div>
                        <!-- Summary Chips -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div
                                    class="h-9 w-9 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                                    <i class="fa-solid fa-circle-check"></i></div>
                                <div>
                                    <p class="text-[10px] tracking-wide uppercase font-semibold text-emerald-700">
                                        Resolved Cases</p>
                                    <p class="text-lg leading-snug font-semibold text-emerald-800">
                                        <?= $resolvedCaseCount ?></p>
                                </div>
                            </div>
                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div
                                    class="h-9 w-9 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                                    <i class="fa-solid fa-hourglass-half"></i></div>
                                <div>
                                    <p class="text-[10px] tracking-wide uppercase font-semibold text-amber-700">Pending
                                        Complaints</p>
                                    <p class="text-lg leading-snug font-semibold text-amber-800"><?= $pendingCount ?>
                                    </p>
                                </div>
                            </div>
                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div
                                    class="h-9 w-9 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                                    <i class="fa-solid fa-handshake"></i></div>
                                <div>
                                    <p class="text-[10px] tracking-wide uppercase font-semibold text-indigo-700">
                                        Mediated</p>
                                    <p class="text-lg leading-snug font-semibold text-indigo-800"><?= $mediatedCount ?>
                                    </p>
                                </div>
                            </div>
                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div
                                    class="h-9 w-9 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600">
                                    <i class="fa-solid fa-ban"></i></div>
                                <div>
                                    <p class="text-[10px] tracking-wide uppercase font-semibold text-rose-700">Rejected
                                    </p>
                                    <p class="text-lg leading-snug font-semibold text-rose-800"><?= $rejectedCount ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right Column: Calendar -->
            <div class="card-flex lg:col-span-7 glass rounded-2xl p-6 md:p-7 fade-in card-body-grow">
                <div class="flex items-center gap-2 mb-5">
                    <i class="fa-solid fa-calendar-days text-sky-600"></i>
                    <h2 class="text-sky-900 font-semibold tracking-tight">Upcoming Hearings</h2>
                </div>
                <iframe src="./schedule/CalendarSec.php"
                    class="w-full rounded-xl border border-white/40 h-[640px] bg-white/50"></iframe>
            </div>
        </div>
        <!-- Activity & Trends -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-5 glass rounded-2xl p-6 md:p-7 fade-in">
                <div class="flex items-center gap-2 mb-5">
                    <i class="fa-solid fa-bell text-sky-600"></i>
                    <h2 class="text-sky-900 font-semibold tracking-tight">Recent Activity</h2>
                </div>
                <div class="space-y-4">
                    <?php if (empty($recentActivities)): ?>
                        <p class="text-xs text-sky-700/70">No recent activity recorded.</p>
                    <?php else:
                        foreach ($recentActivities as $a): ?>
                            <div class="flex items-start gap-3">
                                <div class="h-8 w-8 rounded-lg bg-sky-100 flex items-center justify-center text-sky-600"><i
                                        class="fa-solid fa-plus"></i></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-sky-900 leading-tight"><?= $a['message'] ?></p>
                                    <p class="text-[10px] mt-1 text-sky-700/60 tracking-wide">
                                        <?= date('M d, Y • h:i A', strtotime($a['time'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                </div>
            </div>
            <div class="lg:col-span-7 glass rounded-2xl p-6 md:p-7 fade-in">
                <div class="flex items-center gap-2 mb-5">
                    <i class="fa-solid fa-chart-line text-sky-600"></i>
                    <h2 class="text-sky-900 font-semibold tracking-tight">6‑Month Trends</h2>
                </div>
                <canvas id="statsChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('statsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($monthlyLabels) ?>,
                datasets: [
                    { label: 'Complaints', data: <?= json_encode($monthlyComplaints) ?>, borderColor: '#0c9ced', backgroundColor: 'rgba(12,156,237,.12)', fill: true, tension: .4 },
                    { label: 'Cases', data: <?= json_encode($monthlyCases) ?>, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.12)', fill: true, tension: .4 },
                    { label: 'Mediation', data: <?= json_encode($monthlyMediation) ?>, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,.10)', fill: true, tension: .4 },
                    { label: 'Resolution', data: <?= json_encode($monthlyResolution) ?>, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,.10)', fill: true, tension: .4 },
                    { label: 'Settlement', data: <?= json_encode($monthlySettlement) ?>, borderColor: '#14b8a6', backgroundColor: 'rgba(20,184,166,.10)', fill: true, tension: .4 },
                    { label: 'Closed', data: <?= json_encode($monthlyClosed) ?>, borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,.10)', fill: true, tension: .4 },
                    { label: 'Resolved Cases', data: <?= json_encode($monthlyResolved) ?>, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.10)', fill: true, tension: .4 }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 14, usePointStyle: true } } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } }, x: { grid: { display: false } } }
            }
        });
    </script>

    </div>
    <?php include 'sidebar_.php'; ?>
    </div>
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            // Increased loading time to 3.5 seconds total
            setTimeout(() => {
                const loader = document.querySelector('.loader-wrapper');
                loader.classList.add('fade-out');

                // Increased fade-out transition to 1 second
                setTimeout(() => {
                    loader.remove();
                }, 5000); // Increased from 500ms to 1000ms
            }, 3500); // Increased from 2000ms to 2500ms
        });

        // Optional: Hide loader when all content is fully loaded
        window.addEventListener('load', function () {
            const loader = document.querySelector('.loader-wrapper');
            if (loader) {
                loader.classList.add('fade-out');
                setTimeout(() => {
                    loader.remove();
                }, 5000); // Increased fade-out time
            }
        });

        document.querySelectorAll('.toggle-menu').forEach(button => {
            button.addEventListener('click', () => {
                const submenu = button.nextElementSibling;
                submenu.classList.toggle('hidden');
            });
        });
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.submenu').forEach(submenu => {
                if (submenu.classList.contains('hidden')) {
                    submenu.classList.remove('active');
                }
            });



            document.getElementById('close-sidebar').addEventListener('click', function () {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.add('-translate-x-full');
                // Remove overlay when sidebar is closed
                removeSidebarOverlay();
            });            // Toggle submenu items with animation
            document.querySelectorAll('.toggle-menu').forEach(button => {
                button.addEventListener('click', function () {
                    let submenu = this.nextElementSibling;

                    // Use both hidden and active classes for better animation control
                    submenu.classList.toggle('hidden');

                    // Add a slight delay before adding/removing active class
                    if (!submenu.classList.contains('hidden')) {
                        setTimeout(() => {
                            submenu.classList.add('active');
                        }, 10);
                    } else {
                        submenu.classList.remove('active');
                    }

                    // Rotate chevron icon when clicked
                    const chevron = this.querySelector('.fa-chevron-down');
                    if (chevron) {
                        chevron.classList.toggle('rotate-180');
                    }

                    // Add active state to the clicked menu item
                    this.classList.toggle('bg-primary-50');
                    this.classList.toggle('text-primary-700');
                });
            });

            // Function to add overlay when sidebar is open
            function addSidebarOverlay() {
                // Check if overlay already exists
                if (!document.getElementById('sidebar-overlay')) {
                    const overlay = document.createElement('div');
                    overlay.id = 'sidebar-overlay';
                    overlay.className = 'fixed inset-0 bg-black bg-opacity-30 z-40';
                    document.body.appendChild(overlay);

                    // Close sidebar when overlay is clicked
                    overlay.addEventListener('click', function () {
                        document.getElementById('sidebar').classList.add('-translate-x-full');
                        removeSidebarOverlay();
                    });
                }
            }

            // Function to remove overlay
            function removeSidebarOverlay() {
                const overlay = document.getElementById('sidebar-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }


        });



        // Removed legacy dummy chart init & loadStatistics call (not needed)
    </script>


    <?php include '../chatbot/bpamis_case_assistant.php' ?>
    </body>

</html>