<?php
// Secretary Home (Premium Glass UI)
include '../server/server.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===================== NUMERIC AGGREGATES ===================== //
$complaintsCount = $resolvedCount = $pendingCount = $rejectedCount = 0;
$casesCount = $mediatedCount = $unresolvedCount = $resolutionCount = $settlementCount = $closedCount = $resolvedCaseCount  = $resolutionCount = $openCount = 0;
$scheduledHearings = 0;

// Complaints distribution
if ($result = $conn->query("SELECT status, COUNT(*) as count FROM complaint_info GROUP BY status")) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower(trim($row['status']));
        $count = (int) $row['count'];
        $complaintsCount += $count;
        if ($status === 'resolved') $resolvedCount = $count;
        elseif ($status === 'pending') $pendingCount = $count;
        elseif ($status === 'rejected') $rejectedCount = $count;
        elseif ($status === 'mediation') $mediatedCount = $count;
        elseif ($status === 'open') $openCount = $count;
        elseif ($status === 'resolution') $resolutionCount = $count;
        elseif ($status === 'unresolved') $unresolvedCount = $count;
        elseif ($status === 'settlement') $settlementCount = $count;
    }
}

// Case distribution
if ($result = $conn->query("SELECT case_status as status, COUNT(*) as count FROM case_info GROUP BY case_status")) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower(trim($row['status']));
        $count = (int) $row['count'];
        $casesCount += $count;
        if ($status === 'mediation') $mediatedCount = $count;
        elseif ($status === 'resolution') $resolutionCount = $count;
        elseif ($status === 'settlement') $settlementCount = $count;
        elseif ($status === 'close') $closedCount = $count;
        elseif ($status === 'resolved') $resolvedCaseCount = $count;
        elseif ($status === 'open') $openCount = $count;
        elseif ($status === 'unresolved') $unresolvedCount = $count;
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

// ===================== SECRETARY CASES FOR TIMELINE ===================== //
$secretaryCases = [];
if ($stmt = $conn->prepare("SELECT ci.Case_ID, co.Complaint_ID, co.Complaint_Title, co.Date_Filed, ci.Case_Status, co.case_type
                             FROM case_info ci
                             JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID
                             ORDER BY co.Date_Filed DESC, ci.Case_ID DESC")) {
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $secretaryCases[] = $row;
        }
    }
    $stmt->close();
}
$serverNowIso = (new DateTime())->format('Y-m-d\TH:i:sP');
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
    <div class="max-w-screen-2xl mx-auto px-5 pt-10 relative">
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
    <div class="max-w-screen-2xl mx-auto px-5 mt-10 pb-16 space-y-10">
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
                                    class="h-9 w-9 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fa-solid fa-folder-open"></i></div>
                                <div>
                                    <p class="text-[10px] tracking-wide uppercase font-semibold text-blue-700">Open Cases
                                    </p>
                                    <p class="text-lg leading-snug font-semibold text-blue-800"><?= $openCount ?>
                                    </p>
                                </div>
                            </div>

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
                                    class="h-9 w-9 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                                    <i class="fa-solid fa-balance-scale"></i></div>
                                <div>
                                    <p class="text-[10px] tracking-wide uppercase font-semibold text-emerald-700">
                                        Resolution Cases</p>
                                    <p class="text-lg leading-snug font-semibold text-emerald-800"><?= $resolutionCount ?>
                                    </p>
                                </div>
                            </div>
                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div
                                    class="h-9 w-9 rounded-lg bg-pink-100 flex items-center justify-center text-pink-600">
                                    <i class="fa-solid fa-file-alt"></i></div>
                                <div>
                                    <p class="text-[10px] tracking-wide uppercase font-semibold text-pink-700">Settlement Cases
                                    </p>
                                    <p class="text-lg leading-snug font-semibold text-pink-800"><?= $settlementCount ?>
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
                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div
                                    class="h-9 w-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600">
                                    <i class="fa-solid fa-ban"></i></div>
                                <div>
                                    <p class="text-[10px] tracking-wide uppercase font-semibold text-gray-700">Unresolved Cases
                                    </p>
                                    <p class="text-lg leading-snug font-semibold text-gray-800"><?= $unresolvedCount ?>
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
                    <a href="view_hearing_calendar.php" title="Open full calendar"
                       class="ml-auto inline-flex items-center justify-center h-8 w-8 rounded-lg bg-white/60 hover:bg-white/80 border border-white/60 text-sky-700 hover:text-sky-900 shadow-sm transition"
                       aria-label="Open full calendar">
                        <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                    </a>
                </div>
                <iframe src="./schedule/CalendarSec.php"
                    class="w-full rounded-xl border border-white/40 h-[640px] bg-white/50"></iframe>
            </div>
        </div>
        <!-- Activity & Trends -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Case Timeline (copied style from Resident/External) -->
            <div class="lg:col-span-5 glass rounded-2xl p-6 md:p-7 fade-in">
                <div class="flex items-center justify-between mb-3 gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <h2 class="text-sky-900 font-semibold tracking-tight flex items-center gap-2 whitespace-nowrap">
                            <i class="fa-solid fa-timeline text-sky-600"></i>Case Timeline
                        </h2>
                        <span class="hidden sm:inline px-2 py-1 rounded-lg text-[10px] font-medium bg-sky-600/10 text-sky-700 border border-white/50">LGC 1991 · Secs. 399–422</span>
                    </div>
                    <div class="ml-auto flex items-center gap-2 min-w-0">
                        <label for="sec-case-select" class="text-[11px] text-sky-700 whitespace-nowrap">Select case:</label>
                        <select id="sec-case-select" class="w-44 text-[12px] px-2 py-1 rounded-md bg-white/70 border border-white/60 text-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-300"></select>
                    </div>
                </div>
                <div id="sec-case-phase-summary" class="mb-3 text-[11px] text-sky-700/90"></div>
                <div class="space-y-4 relative">
                    <div class="absolute left-3 top-1 bottom-1 w-px bg-sky-200/70"></div>
                    <!-- Filing -->
                    <div class="relative pl-8 timeline-step" data-step="filing">
                        <div class="absolute left-0 top-0 w-6 h-6 rounded-full bg-purple-500 text-white flex items-center justify-center shadow ring-2 ring-white">
                            <i class="fa-solid fa-file-circle-plus text-[11px]"></i>
                        </div>
                        <div class="rounded-xl border border-white/60 bg-white/60 p-3">
                            <div class="flex items-center gap-2">
                                <p class="text-[13px] font-semibold text-sky-900">Filing of Complaint</p>
                                <span id="sec-badge-filing" class="ml-auto px-2 py-0.5 rounded-md text-[10px] bg-purple-500/15 text-purple-700 border border-purple-500/20">Day 0</span>
                            </div>
                            <p class="mt-1 text-[12px] text-sky-700/80">Starts when complaint is filed. <span id="sec-date-filing" class="font-medium text-sky-800"></span></p>
                        </div>
                    </div>
                    <!-- Mediation -->
                    <div class="relative pl-8 timeline-step" data-step="mediation">
                        <div class="absolute left-0 top-0 w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center shadow ring-2 ring-white">
                            <i class="fa-solid fa-handshake text-[11px]"></i>
                        </div>
                        <div class="rounded-xl border border-white/60 bg-white/60 p-3">
                            <div class="flex items-center gap-2">
                                <p class="text-[13px] font-semibold text-sky-900">Mediation by Punong Barangay</p>
                                <span id="sec-badge-mediation" class="ml-auto px-2 py-0.5 rounded-md text-[10px] bg-amber-500/15 text-amber-700 border border-amber-500/20">Up to 15 days</span>
                            </div>
                            <p class="mt-1 text-[12px] text-sky-700/80">Attempt to amicably settle within 15 days. <span id="sec-range-mediation" class="font-medium text-sky-800"></span></p>
                        </div>
                    </div>
                    <!-- Pangkat -->
                    <div class="relative pl-8 timeline-step" data-step="pangkat">
                        <div class="absolute left-0 top-0 w-6 h-6 rounded-full bg-sky-500 text-white flex items-center justify-center shadow ring-2 ring-white">
                            <i class="fa-solid fa-people-group text-[11px]"></i>
                        </div>
                        <div class="rounded-xl border border-white/60 bg-white/60 p-3">
                            <div class="flex items-center gap-2">
                                <p class="text-[13px] font-semibold text-sky-900">Pangkat ng Tagapagkasundo (Conciliation)</p>
                                <span id="sec-badge-pangkat" class="ml-auto px-2 py-0.5 rounded-md text-[10px] bg-sky-500/15 text-sky-700 border border-sky-500/20">15–30 days</span>
                            </div>
                            <p class="mt-1 text-[12px] text-sky-700/80">Conciliation within 15 days; may extend another 15. <span id="sec-range-pangkat" class="font-medium text-sky-800"></span></p>
                        </div>
                    </div>
                    <!-- Arbitration -->
                    <div class="relative pl-8 timeline-step" data-step="arbitration">
                        <div class="absolute left-0 top-0 w-6 h-6 rounded-full bg-indigo-500 text-white flex items-center justify-center shadow ring-2 ring-white">
                            <i class="fa-solid fa-scale-balanced text-[11px]"></i>
                        </div>
                        <div class="rounded-xl border border-white/60 bg-white/60 p-3">
                            <div class="flex items-center gap-2">
                                <p class="text-[13px] font-semibold text-sky-900">Arbitration (if agreed)</p>
                                <span id="sec-badge-arbitration" class="ml-auto px-2 py-0.5 rounded-md text-[10px] bg-indigo-500/15 text-indigo-700 border border-indigo-500/20">Within 10 days</span>
                            </div>
                            <p class="mt-1 text-[12px] text-sky-700/80">If parties agree, award is rendered within 10 days. <span id="sec-note-arbitration" class="text-[11px] italic text-sky-600/80"></span></p>
                        </div>
                    </div>
                    <!-- Finality -->
                    <div class="relative pl-8 timeline-step" data-step="finality">
                        <div class="absolute left-0 top-0 w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow ring-2 ring-white">
                            <i class="fa-solid fa-file-signature text-[11px]"></i>
                        </div>
                        <div class="rounded-xl border border-white/60 bg-white/60 p-3">
                            <div class="flex items-center gap-2">
                                <p class="text-[13px] font-semibold text-sky-900">Execution of Settlement/Award</p>
                                <span id="sec-badge-finality" class="ml-auto px-2 py-0.5 rounded-md text-[10px] bg-emerald-500/15 text-emerald-700 border border-emerald-500/20">Final in 10 days</span>
                            </div>
                            <p class="mt-1 text-[12px] text-sky-700/80">Becomes final after 10 days unless repudiated. <span id="sec-note-finality" class="text-[11px] italic text-sky-600/80"></span></p>
                        </div>
                    </div>
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
        // Expose cases for Case Timeline (Secretary)
        window.__secCases = <?= json_encode($secretaryCases, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        window.__secServerNowIso = '<?= $serverNowIso ?>';

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
    <script>
        // Case Timeline logic (Secretary)
        document.addEventListener('DOMContentLoaded', () => {
            const cases = Array.isArray(window.__secCases) ? window.__secCases : [];
            const now = new Date(window.__secServerNowIso || Date.now());
            const select = document.getElementById('sec-case-select');
            const summary = document.getElementById('sec-case-phase-summary');
            const fmt = (d) => new Date(d).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
            const addDays = (date, days) => { const dt = new Date(date); dt.setDate(dt.getDate() + days); return dt; };
            const daysBetween = (a, b) => Math.floor((new Date(b) - new Date(a)) / 86400000);

            // Step elements for highlighting
            const steps = {
                filing: document.querySelector('.timeline-step[data-step="filing"]'),
                mediation: document.querySelector('.timeline-step[data-step="mediation"]'),
                pangkat: document.querySelector('.timeline-step[data-step="pangkat"]'),
                arbitration: document.querySelector('.timeline-step[data-step="arbitration"]'),
                finality: document.querySelector('.timeline-step[data-step="finality"]')
            };

            function clearStepClasses() {
                Object.values(steps).forEach(el => {
                    if (!el) return;
                    el.classList.remove('opacity-60', 'ring-2', 'ring-sky-400', 'ring-emerald-400');
                });
            }
            function mark(el, type) {
                if (!el) return;
                if (type === 'current') el.classList.add('ring-2', 'ring-sky-400');
                if (type === 'completed') el.classList.add('ring-2', 'ring-emerald-400');
                if (type === 'upcoming') el.classList.add('opacity-60');
            }
            function computePhase(dateFiledStr) {
                const filed = new Date(dateFiledStr);
                if (isNaN(filed)) return { phase: 'unknown', days: 0, filed: null };
                const days = daysBetween(filed, now);
                if (days <= 0) return { phase: 'filing', days, filed };
                if (days <= 15) return { phase: 'mediation', days, filed };
                if (days <= 30) return { phase: 'pangkat', days, filed, ext: false };
                if (days <= 45) return { phase: 'pangkat', days, filed, ext: true };
                return { phase: 'beyond', days, filed };
            }

            function escapeHtml(unsafe) {
                if (unsafe === null || unsafe === undefined) return '';
                return String(unsafe)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function updateTimeline(c) {
                if (!c) {
                    summary.innerHTML = '<span class="text-[12px] text-sky-700/70">No case selected.</span>';
                    ['filing','mediation','pangkat','arbitration','finality'].forEach(step => {
                        // clear badges
                        const ids = {
                            filing: ['sec-badge-filing','sec-date-filing'],
                            mediation: ['sec-badge-mediation','sec-range-mediation'],
                            pangkat: ['sec-badge-pangkat','sec-range-pangkat'],
                            arbitration: ['sec-badge-arbitration','sec-note-arbitration'],
                            finality: ['sec-badge-finality','sec-note-finality']
                        }[step];
                        if (!ids) return;
                        ids.forEach(id => { const el = document.getElementById(id); if (el) el.textContent = ''; });
                    });
                    clearStepClasses();
                    return;
                }
                const filed = c.Date_Filed;
                const caseType = escapeHtml(c.case_type || c.Case_Type || '');
                const title = escapeHtml(c.Complaint_Title || 'Untitled');
                const caseId = escapeHtml(c.Case_ID);
                const status = escapeHtml(c.Case_Status || '');

                // Phase windows (based on Resident timeline semantics)
                const filingDate = new Date(filed);
                const mediationEnd = addDays(filed, 15);
                const pangkatStart = addDays(filed, 15);
                const pangkatEnd = addDays(filed, 30); // extendable to 30
                const arbitrationEnd = addDays(filed, 40); // indicative window

                // Determine current phase and day count
                const phaseInfo = computePhase(filed);
                let phaseLabel = 'Unknown';
                if (phaseInfo.phase === 'filing') phaseLabel = 'Filing of Complaint';
                else if (phaseInfo.phase === 'mediation') phaseLabel = 'Mediation by Punong Barangay';
                else if (phaseInfo.phase === 'pangkat') phaseLabel = phaseInfo.ext ? 'Pangkat (Extended)' : 'Pangkat ng Tagapagkasundo';
                else if (phaseInfo.phase === 'beyond') phaseLabel = 'Beyond 45 days — escalate/finalize';

                const dayN = Math.max(0, phaseInfo.days || 0);

                // Top summary
                summary.innerHTML = `
                    <span class="px-2 py-1 rounded-md bg-white/60 border border-white/60 text-[11px] text-sky-800 font-medium">Case #${caseId}</span>
                    <span class="ml-2 text-[11px] text-sky-700/80">${title}${caseType ? ' • ' + caseType : ''}</span>
                    <span class="ml-2 px-2 py-0.5 rounded-md text-[10px] bg-blue-100 text-blue-700 border border-blue-200">${status}</span>
                    <span class="ml-2 text-[11px] text-sky-700/80">· Day <span class="font-semibold">${dayN}</span> · Phase: <span class="font-semibold">${phaseLabel}</span></span>
                `;

                // Filing
                const badgeFiling = document.getElementById('sec-badge-filing');
                const dateFiling = document.getElementById('sec-date-filing');
                if (badgeFiling) badgeFiling.textContent = 'Day 0';
                if (dateFiling) dateFiling.textContent = fmt(filed);

                // Mediation (0-15 days)
                const badgeMed = document.getElementById('sec-badge-mediation');
                const rangeMed = document.getElementById('sec-range-mediation');
                if (badgeMed) badgeMed.textContent = 'Up to 15 days';
                if (rangeMed) rangeMed.textContent = `${fmt(filed)} – ${fmt(mediationEnd)}`;

                // Pangkat (15–30 days)
                const badgeP = document.getElementById('sec-badge-pangkat');
                const rangeP = document.getElementById('sec-range-pangkat');
                if (badgeP) badgeP.textContent = '15–30 days';
                if (rangeP) rangeP.textContent = `${fmt(pangkatStart)} – ${fmt(pangkatEnd)}`;

                // Arbitration (if agreed) – indicative (within 10 days)
                const badgeA = document.getElementById('sec-badge-arbitration');
                const noteA = document.getElementById('sec-note-arbitration');
                if (badgeA) badgeA.textContent = 'Within 10 days';
                if (noteA) noteA.textContent = 'Optional – applies only if parties agree to arbitrate.';

                // Finality – 10 days after award/settlement (display guidance)
                const badgeF = document.getElementById('sec-badge-finality');
                const noteF = document.getElementById('sec-note-finality');
                if (badgeF) badgeF.textContent = 'Final in 10 days';
                if (noteF) noteF.textContent = 'Final and executory 10 days after settlement/award unless repudiated.';

                // Highlight phases (completed/current/upcoming)
                clearStepClasses();
                if (phaseInfo.phase === 'filing') {
                    mark(steps.filing, 'current');
                    mark(steps.mediation, 'upcoming');
                    mark(steps.pangkat, 'upcoming');
                    mark(steps.arbitration, 'upcoming');
                    mark(steps.finality, 'upcoming');
                } else if (phaseInfo.phase === 'mediation') {
                    mark(steps.filing, 'completed');
                    mark(steps.mediation, 'current');
                    mark(steps.pangkat, 'upcoming');
                    mark(steps.arbitration, 'upcoming');
                    mark(steps.finality, 'upcoming');
                } else if (phaseInfo.phase === 'pangkat') {
                    mark(steps.filing, 'completed');
                    mark(steps.mediation, 'completed');
                    mark(steps.pangkat, 'current');
                    mark(steps.arbitration, 'upcoming');
                    mark(steps.finality, 'upcoming');
                } else if (phaseInfo.phase === 'beyond') {
                    mark(steps.filing, 'completed');
                    mark(steps.mediation, 'completed');
                    mark(steps.pangkat, 'completed');
                    mark(steps.arbitration, 'upcoming');
                    mark(steps.finality, 'upcoming');
                }
            }

            if (select) {
                if (!cases.length) {
                    select.innerHTML = '<option value="">No cases found</option>';
                } else {
                    select.innerHTML = cases.map(c => {
                        const type = (c.case_type || c.Case_Type || '').toString().trim();
                        const label = `Case #${c.Case_ID} — ${type || 'N/A'}`;
                        return `<option value="${c.Case_ID}">${escapeHtml(label)}</option>`;
                    }).join('');
                    const initial = cases[0];
                    updateTimeline(initial);
                    select.value = initial ? String(initial.Case_ID) : '';
                    select.addEventListener('change', () => {
                        const id = select.value;
                        const found = cases.find(c => String(c.Case_ID) === id);
                        updateTimeline(found);
                    });
                }
            }
        });
    </script>

    <?php include '../chatbot/bpamis_case_assistant.php' ?>
    </body>

</html>