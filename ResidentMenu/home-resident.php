<?php

session_start();
include '../server/server.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../bpamis_website/login.php");
    exit();
}

$resident_id = $_SESSION['user_id'];

// Get the resident's name
$query = "SELECT first_name, last_name FROM resident_info WHERE resident_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$result = $stmt->get_result();

$full_name = "Resident";
if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $full_name = $row['first_name'] . ' ' . $row['last_name'];
}


// Total Complaints by  Resident
$complaintsCount = $conn->query("SELECT COUNT(*) AS total FROM complaint_info WHERE Resident_ID = $resident_id")->fetch_assoc()['total'] ?? 0;

// Total Cases linked to Resident (via complaint_info)
$casesCount = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.Resident_ID = $resident_id
")->fetch_assoc()['total'] ?? 0;

// Pending Complaints
$pendingComplaints = $conn->query("
    SELECT COUNT(*) AS total 
    FROM complaint_info 
    WHERE Resident_ID = $resident_id AND Status = 'Pending'
")->fetch_assoc()['total'] ?? 0;

// Resolved Cases
$resolvedCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.Resident_ID = $resident_id AND Status = 'Resolved'
")->fetch_assoc()['total'] ?? 0;

// Pending Cases
$pendingCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.Resident_ID = $resident_id AND Status = 'Pending'
")->fetch_assoc()['total'] ?? 0;

// Scheduled Hearings
$scheduledHearings = $conn->query("
    SELECT COUNT(*) AS total 
    FROM schedule_list sl
    JOIN case_info ci ON sl.Case_ID = ci.Case_ID
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID
    WHERE co.Resident_ID = $resident_id
")->fetch_assoc()['total'] ?? 0;

// Open Cases
$openCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.Resident_ID = $resident_id AND Status = 'IN CASE'
")->fetch_assoc()['total'] ?? 0;

// Percentages (guard division)
$complaintsPercent = $complaintsCount ? 100 : 0; // base bar for total
$casesPercent = ($complaintsCount && $casesCount) ? min(100, round(($casesCount / $complaintsCount) * 100)) : 0;
$pendingComplaintsPercent = $complaintsCount ? round(($pendingComplaints / $complaintsCount) * 100) : 0;
$resolvedCasesPercent = $casesCount ? round(($resolvedCases / $casesCount) * 100) : 0;
$hearingPercent = $casesCount ? min(100, round(($scheduledHearings / max($casesCount, 1)) * 100)) : 0;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="tailwind.js"></script>
    <link rel="stylesheet" href="main.css">
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

        .section-label {
            font-size: .65rem;
            letter-spacing: .09em;
            font-weight: 600;
            text-transform: uppercase;
            color: #0369a1;
        }

        .progress-wrap {
            height: 12px;
        }

        .progress-bar {
            transition: width 1s cubic-bezier(.4, .0, .2, 1);
        }

        .quick-btn {
            position: relative;
            overflow: hidden;
            transition: .35s;
        }

        .quick-btn:before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255, 255, 255, .6), rgba(255, 255, 255, 0));
            opacity: 0;
            transition: opacity .5s;
        }

        .quick-btn:hover:before {
            opacity: 1;
        }

        .quick-btn:hover {
            transform: translateY(-4px);
        }

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

        .progress-bar {
            transition: width 1s ease-in-out;
        }


        .calendar-container {
            --fc-border-color: #edf2f7;
            --fc-daygrid-event-dot-width: 6px;
            --fc-event-border-radius: 6px;
            --fc-small-font-size: 0.75rem;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .fc-daygrid-event-dot {
            /* the actual dot */
            margin-left: 50px;
        }

        .calendar-container .fc-theme-standard th {
            padding: 12px 0;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: #4b5563;
            border: none;
        }

        .calendar-container .fc-theme-standard td {
            border-color: #f3f4f6;
        }

        .calendar-container .fc-col-header-cell {
            background: #f9fafb;
        }

        .calendar-container .fc-toolbar-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #4338ca;
        }

        .calendar-container .fc-button {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem !important;
            font-weight: 500;
            transition: all 0.3s ease;
            text-transform: capitalize;
            border: 1px solid #e5e7eb !important;
        }

        .calendar-container .fc-button-primary {
            background-color: white !important;
            color: #4b5563 !important;
        }

        .calendar-container .fc-button-primary:hover {
            background-color: #eef2ff !important;
            color: #4338ca !important;
            transform: translateY(-1px);
        }

        .calendar-container .fc-button-primary:not(:disabled).fc-button-active,
        .calendar-container .fc-button-primary:not(:disabled):active {
            background-color: #4f46e5 !important;
            color: white !important;
            border-color: #4338ca !important;
        }

        .calendar-container .fc-daygrid-day-number {
            padding: 8px;
            font-size: 0.875rem;
            color: #374151;
            font-weight: 500;
        }

        .calendar-container .fc-daygrid-day.fc-day-today {
            background-color: rgba(79, 70, 229, 0.1) !important;
        }

        .calendar-container .fc-event {
            border: none !important;
            padding: 2px 4px;
            font-size: 0.75rem !important;
            margin-top: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
        }

        .calendar-container .fc-event:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.12) !important;
            z-index: 10;
        }

        .calendar-container .fc-toolbar.fc-header-toolbar {
            margin-bottom: 1.25em;
            flex-wrap: wrap;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .calendar-container .fc-view-harness {
            border-radius: 8px;
            overflow: hidden;
        }

        .calendar-container .fc-event-title {
            font-weight: 500 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .calendar-container .fc-daygrid-day-frame {
            transition: background-color 0.2s ease;
        }

        .calendar-container .fc-daygrid-day-frame:hover {
            background-color: rgba(79, 70, 229, 0.05);
        }

        @media (max-width: 640px) {
            .calendar-container .fc-toolbar.fc-header-toolbar {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                align-items: center;
            }

            .calendar-container .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                transition: transform 0.3s ease;
            }

            .calendar-container .fc-toolbar-chunk:hover {
                transform: translateY(-2px);
            }
        }

        /* Calendar animation effects */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Status colors */
        .bg-green-100.text-green-800 {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: #065f46 !important;
        }

        .bg-red-100.text-red-800 {
            background-color: rgba(239, 68, 68, 0.15) !important;
            color: #991b1b !important;
        }

        .bg-yellow-100.text-yellow-800 {
            background-color: rgba(245, 158, 11, 0.15) !important;
            color: #92400e !important;
        }

        .bg-blue-100.text-blue-800 {
            background-color: rgba(59, 130, 246, 0.15) !important;
            color: #1e40af !important;
        }

        .responsive-calendar-wrapper {
            position: relative;
            width: 100%;
            transition: height .35s ease;
        }

        .responsive-calendar-wrapper iframe {
            width: 100% !important;
            border: none;
            display: block;
            background: transparent;
            min-height: 700px;
        }

        @media (max-width:1280px) {
            .responsive-calendar-wrapper iframe {
                min-height: 620px;
            }
        }

        @media (max-width:1024px) {
            .responsive-calendar-wrapper iframe {
                min-height: 560px;
            }
        }

        @media (max-width:900px) {
            .responsive-calendar-wrapper iframe {
                min-height: 520px;
            }
        }

        @media (max-width:768px) {
            .responsive-calendar-wrapper iframe {
                min-height: 480px;
            }
        }

        @media (max-width:640px) {
            .responsive-calendar-wrapper iframe {
                min-height: 440px;
            }
        }

        @media (max-width:640px) {
            #calendar-legend.collapsed .legend-body {
                display: none;
            }

            #calendar-legend .legend-toggle {
                display: inline-flex;
            }
        }

        @media (min-width:641px) {
            #calendar-legend .legend-toggle {
                display: none;
            }
        }
    </style>

    </style>
</head>

<body class="font-sans text-gray-700 relative overflow-x-hidden">
    <div class="orb one"></div>
    <div class="orb two"></div>
    <?php include_once('../includes/resident_nav.php'); ?>
    <!-- HERO -->
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
                        <p class="section-label mb-2">Resident Dashboard</p>
                        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-sky-900">Welcome<span
                                class="font-light">,</span></h1>
                        <p class="mt-2 text-sky-800 text-lg font-medium"><?= htmlspecialchars($full_name) ?></p>
                        <p class="mt-3 max-w-xl text-sm md:text-base text-sky-700/80">Monitor your complaints, hearings
                            and settlement progress with real‑time updates tailored for residents.</p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4 w-full md:w-auto">
                        <a href="submit_complaints.php"
                            class="quick-btn glass group rounded-xl px-4 py-3 flex flex-col items-start gap-1 hover:shadow-lg transition">
                            <div class="flex items-center gap-2 text-sky-700"><i
                                    class="fa-solid fa-square-plus text-sky-600"></i><span
                                    class="text-xs font-semibold tracking-wide uppercase">New</span></div>
                            <span class="text-[13px] font-medium text-sky-900">Complaint</span>
                        </a>
                        <a href="view_complaints.php"
                            class="quick-btn glass group rounded-xl px-4 py-3 flex flex-col items-start gap-1">
                            <div class="flex items-center gap-2 text-emerald-700"><i
                                    class="fa-solid fa-clipboard-list text-emerald-600"></i><span
                                    class="text-xs font-semibold tracking-wide uppercase">View</span></div>
                            <span class="text-[13px] font-medium text-emerald-900">Complaints</span>
                        </a>
                        <a href="view_cases.php"
                            class="quick-btn glass group rounded-xl px-4 py-3 flex flex-col items-start gap-1">
                            <div class="flex items-center gap-2 text-indigo-700"><i
                                    class="fa-solid fa-gavel text-indigo-600"></i><span
                                    class="text-xs font-semibold tracking-wide uppercase">View</span></div>
                            <span class="text-[13px] font-medium text-indigo-900">Cases</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TWO COLUMN CONTENT -->
    <div class="max-w-7xl mx-auto px-5 mt-10 pb-16">
        <!-- Grid: stats (left) | hearings (right) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <!-- Statistics Card (Left Column) -->
            <div class="glass rounded-2xl p-6 md:p-7 fade-in h-full flex flex-col">
                <div class="flex items-center gap-2 mb-5"><i class="fa-solid fa-chart-simple text-sky-600"></i>
                    <h2 class="text-sky-900 font-semibold tracking-tight">Statistics</h2>
                </div>
                <div class="space-y-6">
                    <div>
                        <div class="flex items-center justify-between mb-2"><span
                                class="text-xs font-semibold text-sky-800 tracking-wide">Total Complaints</span><span
                                class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-sky-100 text-sky-700"><?= $complaintsCount ?></span>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <div class="flex justify-between text-[10px] font-medium text-amber-700 mb-0.5">
                                    <span>Pending</span><span><?= $pendingComplaints ?></span></div>
                                <div class="w-full h-2.5 rounded-full bg-amber-50 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 progress-bar"
                                        style="width: <?= $pendingComplaintsPercent ?>%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-[10px] font-medium text-emerald-700 mb-0.5">
                                    <span>Resolved Cases</span><span><?= $resolvedCases ?></span></div>
                                <div class="w-full h-2.5 rounded-full bg-emerald-50 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 progress-bar"
                                        style="width: <?= $resolvedCasesPercent ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1.5 text-xs font-medium text-indigo-800"><span>Case
                                Overview</span><span
                                class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700"><?= $casesCount ?></span>
                        </div>
                        <p class="text-[10px] mt-1 text-indigo-800/70">Pending: <?= $pendingCases ?> • Hearings:
                            <?= $scheduledHearings ?> • Open: <?= $openCases ?></p>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1.5 text-xs font-medium text-rose-800"><span>Hearings
                                Scheduled</span><span
                                class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-700"><?= $scheduledHearings ?></span>
                        </div>
                        <div class="w-full bg-white/60 rounded-full progress-wrap overflow-hidden h-2.5">
                            <div class="progress-bar bg-gradient-to-r from-rose-400 to-rose-500 h-full"
                                style="width: <?= $hearingPercent ?>%"></div>
                        </div>
                        <p class="text-[10px] mt-1 text-rose-800/70">Relative to total cases</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="glass rounded-xl p-4 flex items-start gap-3">
                            <div
                                class="h-9 w-9 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <i class="fa-solid fa-circle-check"></i></div>
                            <div>
                                <p class="text-[10px] tracking-wide uppercase font-semibold text-emerald-700">Resolved
                                </p>
                                <p class="text-lg leading-snug font-semibold text-emerald-800"><?= $resolvedCases ?></p>
                            </div>
                        </div>
                        <div class="glass rounded-xl p-4 flex items-start gap-3">
                            <div
                                class="h-9 w-9 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                                <i class="fa-solid fa-hourglass-half"></i></div>
                            <div>
                                <p class="text-[10px] tracking-wide uppercase font-semibold text-amber-700">Pending
                                    Complaints</p>
                                <p class="text-lg leading-snug font-semibold text-amber-800"><?= $pendingComplaints ?>
                                </p>
                            </div>
                        </div>
                        <div class="glass rounded-xl p-4 flex items-start gap-3">
                            <div
                                class="h-9 w-9 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                                <i class="fa-solid fa-calendar-days"></i></div>
                            <div>
                                <p class="text-[10px] tracking-wide uppercase font-semibold text-indigo-700">Hearings
                                </p>
                                <p class="text-lg leading-snug font-semibold text-indigo-800"><?= $scheduledHearings ?>
                                </p>
                            </div>
                        </div>
                        <div class="glass rounded-xl p-4 flex items-start gap-3">
                            <div class="h-9 w-9 rounded-lg bg-sky-100 flex items-center justify-center text-sky-600"><i
                                    class="fa-solid fa-folder-open"></i></div>
                            <div>
                                <p class="text-[10px] tracking-wide uppercase font-semibold text-sky-700">Open Cases</p>
                                <p class="text-lg leading-snug font-semibold text-sky-800"><?= $openCases ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Calendar Card (Right Column) -->
            <div class="glass rounded-2xl p-6 md:p-7 fade-in h-full flex flex-col">
                <div class="flex items-center gap-2 mb-5"><i class="fa-solid fa-calendar-days text-sky-600"></i>
                    <h2 class="text-sky-900 font-semibold tracking-tight">Upcoming Hearings</h2>
                </div>
                <iframe id="resident-calendar" src="../SecMenu/schedule/CalendarResident.php"
                    class="w-full rounded-xl border border-white/40 h-[640px] bg-white/50"></iframe>
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3 text-[11px] text-sky-800/80">
                    
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dynamic iframe height (best effort same-origin assumption)
        function adjustResidentCalendarHeight() {
            const iframe = document.getElementById('resident-calendar');
            if (!iframe) return;
            try {
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                if (!doc) return;
                const innerRoot = doc.querySelector('.fc') || doc.body;
                const desired = innerRoot.scrollHeight + 40;
                if (desired > 0) iframe.style.height = desired + 'px';
            } catch (e) {
                const vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
                iframe.style.height = (vw < 640 ? 480 : 640) + 'px';
            }
        }
        window.addEventListener('resize', () => { cancelAnimationFrame(window.__rcResizeReq); window.__rcResizeReq = requestAnimationFrame(adjustResidentCalendarHeight); });
        setTimeout(adjustResidentCalendarHeight, 900);
    </script>
    <?php include('../chatbot/bpamis_case_assistant.php'); ?>
    <?php if (isset($_GET['out_of_scope']) && $_GET['out_of_scope'] == 1): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                alert("⚠️ This complaint may be out of the barangay's scope.\n\nPlease consider contacting the local police.\n\nPolice Hotline: 0990-598-5380\n\nYour complaint has still been recorded.");
            });
        </script>
    <?php endif; ?>
</body>

</html>