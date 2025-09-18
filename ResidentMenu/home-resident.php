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

// Total Cases linked to this Resident (via complaint_info)
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
    WHERE co.Resident_ID = $resident_id AND Case_Status = 'Resolved'
")->fetch_assoc()['total'] ?? 0;

// Pending Cases
$pendingCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.Resident_ID = $resident_id AND Case_Status = 'Pending'
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
    WHERE co.Resident_ID = $resident_id AND Case_Status = 'Open'
")->fetch_assoc()['total'] ?? 0;

$mediationCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.Resident_ID = $resident_id AND Case_Status = 'mediation'
")->fetch_assoc()['total'] ?? 0;

$resolutionCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.Resident_ID = $resident_id AND Case_Status = 'resolution'
")->fetch_assoc()['total'] ?? 0;

$settlementCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.Resident_ID = $resident_id AND Case_Status = 'settlement'
")->fetch_assoc()['total'] ?? 0;

// Percentages (guard division)
$complaintsPercent = $complaintsCount ? 100 : 0; // base bar for total
$casesPercent = ($complaintsCount && $casesCount) ? min(100, round(($casesCount / $complaintsCount) * 100)) : 0;
$pendingComplaintsPercent = $complaintsCount ? round(($pendingComplaints / $complaintsCount) * 100) : 0;
$resolvedCasesPercent = $casesCount ? round(($resolvedCases / $casesCount) * 100) : 0;
$hearingPercent = $casesCount ? min(100, round(($scheduledHearings / max($casesCount, 1)) * 100)) : 0;

// Upcoming vs Past Hearings logic
// Definition per requirement: Upcoming = hearings from TOMORROW onward (hide today & past)
// Past button will list previous hearings (including today and earlier) when toggled.
$upcomingHearings = [];
$pastHearings = [];

// Fetch UPCOMING (tomorrow onward)
if (
    $stmtUpcoming = $conn->prepare("SELECT sl.hearingID, sl.hearingTitle, sl.hearingDateTime, sl.remarks, sl.place, ci.Case_ID
    FROM barangay_case_management.schedule_list sl
    JOIN barangay_case_management.case_info ci ON sl.Case_ID = ci.Case_ID
    JOIN barangay_case_management.complaint_info co ON ci.Complaint_ID = co.Complaint_ID
    WHERE co.Resident_ID = ? AND DATE(sl.hearingDateTime) >= (CURDATE() + INTERVAL 1 DAY)
    ORDER BY sl.hearingDateTime ASC")
) {
    $stmtUpcoming->bind_param('i', $resident_id);
    if ($stmtUpcoming->execute()) {
        $resUp = $stmtUpcoming->get_result();
        while ($r = $resUp->fetch_assoc()) {
            $upcomingHearings[] = $r;
        }
    }
    $stmtUpcoming->close();
}

// Fetch PAST (including today & earlier) – limited recent 15 for performance
if (
    $stmtPast = $conn->prepare("SELECT sl.hearingID, sl.hearingTitle, sl.hearingDateTime, sl.remarks, sl.place, ci.Case_ID
    FROM barangay_case_management.schedule_list sl
    JOIN barangay_case_management.case_info ci ON sl.Case_ID = ci.Case_ID
    JOIN barangay_case_management.complaint_info co ON ci.Complaint_ID = co.Complaint_ID
    WHERE co.Resident_ID = ? AND DATE(sl.hearingDateTime) <= CURDATE()
    ORDER BY sl.hearingDateTime DESC LIMIT 15")
) {
    $stmtPast->bind_param('i', $resident_id);
    if ($stmtPast->execute()) {
        $resPast = $stmtPast->get_result();
        while ($r = $resPast->fetch_assoc()) {
            $pastHearings[] = $r;
        }
    }
    $stmtPast->close();
}

// Default tab is upcoming; switching handled client-side (no full page reload)
$hearingTab = 'upcoming';

// Fetch resident cases for Case Timeline selector (Complaint Date_Filed based)
$residentCases = [];
// Ensure resident_id is available (fallback to session if not set)
if ((!isset($resident_id) || !$resident_id) && isset($_SESSION['Resident_ID'])) {
    $resident_id = (int) $_SESSION['Resident_ID'];
}
if (
    $stmtCases = $conn->prepare("SELECT ci.Case_ID, co.Complaint_ID, co.Complaint_Title, co.Date_Filed, ci.Case_Status
    FROM case_info ci
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID
    WHERE co.Resident_ID = ?
    ORDER BY co.Date_Filed DESC, ci.Case_ID DESC")
) {
    $stmtCases->bind_param('i', $resident_id);
    if ($stmtCases->execute()) {
        $resCases = $stmtCases->get_result();
        while ($rc = $resCases->fetch_assoc()) {
            // Normalize date format to ISO if possible
            if (!empty($rc['Date_Filed'])) {
                $dt = new DateTime($rc['Date_Filed']);
                $rc['Date_Filed'] = $dt->format('Y-m-d H:i:s');
            }
            $residentCases[] = $rc;
        }
    }
    $stmtCases->close();
}
// Current server time for consistent calculations client-side
$serverNowIso = (new DateTime())->format('Y-m-d\TH:i:sP');
?>


<!DOCTYPE html>
<!-- Case Timeline Card -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: radial-gradient(circle at 20% 20%, #e0f2ff 0%, #f5f9ff 50%, #ffffff 100%);
        }

        /* Floating orbs in background */
        .orb {
            position: absolute;
            pointer-events: none;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.5;
        }
        .orb.one {
            top: -120px;
            right: -120px;
            width: 480px;
            height: 480px;
            background: linear-gradient(135deg, #0c9ced, #7cccfd);
            animation: float 14s ease-in-out infinite;
        }
        .orb.two {
            bottom: -120px;
            left: -120px;
            width: 360px;
            height: 360px;
            background: radial-gradient(circle at 40% 40%, #93c5fd, #dbeafe);
            animation: float 11s ease-in-out reverse infinite;
        }

        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(16px); }
            100% { transform: translateY(0); }
        }

        /* Generic glass card */
        .glass {
            backdrop-filter: blur(14px);
            background: linear-gradient(135deg, rgba(255, 255, 255, .65), rgba(255, 255, 255, .35));
            border: 1px solid rgba(255, 255, 255, .45);
        }

        /* Section label */
        .section-label {
            font-size: .65rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #0369a1;
        }

        .progress-wrap { }

        /* Add soft blush gradient for hero container */
        .hero-blush {
            background: linear-gradient(135deg, rgba(242, 249, 253, 0.7), rgba(249, 251, 253, 0.65));
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

        /* Premium stat card animations */
        .premium-stat-card {
            transition: transform .5s cubic-bezier(.34, 1.56, .4, 1), box-shadow .4s ease;
            will-change: transform;
        }

        .premium-stat-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 18px 40px -15px rgba(14, 116, 144, 0.25), 0 8px 22px -8px rgba(14, 116, 144, .18);
        }

        .premium-stat-card .premium-icon-container {
            transition: transform .55s cubic-bezier(.34, 1.56, .4, 1), filter .4s;
        }

        .premium-stat-card:hover .premium-icon-container {
            transform: rotate(-6deg) scale(1.1) translateY(-3px);
            filter: drop-shadow(0 6px 10px rgba(0, 0, 0, .15));
            /* subtle ring glow around circular icon on hover */
            box-shadow: 0 0 0 6px rgba(59, 130, 246, 0.12), 0 10px 18px -6px rgba(0, 0, 0, 0.18);
        }

        .premium-icon-light {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, .8), rgba(255, 255, 255, 0));
            opacity: .35;
            mix-blend-mode: overlay;
            pointer-events: none;
        }

        .premium-stat-inner:before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255, 255, 255, .55), rgba(255, 255, 255, 0));
            opacity: 0;
            transition: opacity .6s;
            pointer-events: none;
        }

        .premium-stat-card:hover .premium-stat-inner:before {
            opacity: .6;
        }

        /* Highlight the inner card when hovering the stat card/icon */
        .premium-stat-card:hover .premium-stat-inner {
            background: linear-gradient(135deg, rgba(255, 255, 255, .82), rgba(224, 242, 254, .52));
            border-color: rgba(255, 255, 255, .8);
            box-shadow: 0 16px 38px -14px rgba(14, 116, 144, .26), 0 0 0 2px rgba(59, 130, 246, .14) inset;
        }

        .premium-stat-border {
            background: linear-gradient(125deg, rgba(255, 255, 255, .85), rgba(255, 255, 255, 0) 60%);
            box-shadow: 0 0 0 1px rgba(255, 255, 255, .5) inset, 0 0 0 1px rgba(255, 255, 255, .25);
        }

        .premium-stat-particles {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .premium-stat-particles:before,
        .premium-stat-particles:after {
            content: "";
            position: absolute;
            width: 140%;
            height: 140%;
            top: -20%;
            left: -20%;
            background:
                radial-gradient(circle at 20% 30%, rgba(255, 255, 255, .55) 0, rgba(255, 255, 255, 0) 55%),
                radial-gradient(circle at 80% 70%, rgba(255, 255, 255, .45) 0, rgba(255, 255, 255, 0) 60%),
                radial-gradient(circle at 40% 80%, rgba(255, 255, 255, .35) 0, rgba(255, 255, 255, 0) 65%);
            animation: drift 18s linear infinite;
            opacity: .35;
        }

        .premium-stat-particles:after {
            animation-direction: reverse;
            animation-duration: 24s;
        }

        @keyframes drift {
            to {
                transform: translate3d(8%, -6%, 0) rotate(1deg);
            }
        }

        .count-up {
            opacity: 0;
            transform: translateY(4px);
            transition: opacity .4s ease .2s, transform .55s cubic-bezier(.34, 1.56, .4, 1) .15s;
        }

        .count-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Scrollbar styling for hearings list */
        .styled-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .styled-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .styled-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, rgba(14, 116, 144, .35), rgba(14, 116, 144, .15));
            border-radius: 20px;
        }

        .styled-scroll::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, rgba(14, 116, 144, .55), rgba(14, 116, 144, .25));
        }

        /* Loader (mirrors secretary implementation) */
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
            <img src="logo.png" alt="BPAMIS Logo" class="loader-logo" />
        </div>
    </div>


    <?php include_once('../includes/resident_nav.php'); ?>
    <!-- HERO (Refactored: Left welcome + quick actions, Right stats) -->
    <div class="max-w-screen-2xl mx-auto px-5 pt-10 relative">
        <div class="glass hero-blush rounded-3xl p-8 md:p-10 overflow-hidden fade-in">
            <div class="absolute inset-0 pointer-events-none">
                <div
                    class="absolute -top-20 -right-10 w-80 h-80 bg-gradient-to-br from-primary-200/70 to-primary-400/40 rounded-full blur-3xl opacity-60">
                </div>
                <div
                    class="absolute -bottom-24 -left-10 w-72 h-72 bg-gradient-to-tr from-primary-100/60 via-white/40 to-primary-300/40 rounded-full blur-3xl">
                </div>
            </div>
            <div class="relative z-10 grid lg:grid-cols-2 gap-10 items-start">
                <!-- Left Column (full width within its half) -->
                <div class="flex flex-col gap-8">
                    <div>
                        <p class="section-label mb-2">Resident Dashboard</p>
                        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-sky-900">Welcome<span
                                class="font-light">,</span></h1>
                        <p class="mt-2 text-sky-800 text-lg font-medium"><?= htmlspecialchars($full_name) ?></p>
                        <p class="mt-3 max-w-xl text-sm md:text-base text-sky-700/80">Monitor your complaints, hearings
                            and settlement progress with real‑time updates tailored for residents.</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold tracking-wide uppercase text-sky-700 mb-2">Actions Buttons
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4">
                            <!-- New Complaint button-like action -->
                            <a href="submit_complaints.php" role="button" aria-label="New Complaint"
                                class="quick-btn glass group rounded-xl px-3.5 py-2.5 min-h-[44px] border border-white/60 bg-white/50 hover:bg-white/70 shadow-sm hover:shadow-lg transition flex items-center gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-300/60">
                                <span
                                    class="relative h-9 w-9 rounded-full bg-gradient-to-br from-sky-500 to-sky-600 text-white flex items-center justify-center shadow-inner ring-1 ring-white/30">
                                    <i class="fa-solid fa-square-plus"></i>
                                </span>
                                <span class="text-[13px] sm:text-sm font-semibold text-sky-900">New Complaint</span>
                                <i
                                    class="fa-solid fa-chevron-right ml-auto text-sky-600 opacity-0 translate-x-[-2px] group-hover:opacity-100 group-hover:translate-x-0 transition"></i>
                            </a>

                            <!-- View Complaints button-like action -->
                            <a href="view_complaints.php" role="button" aria-label="View Complaints"
                                class="quick-btn glass group rounded-xl px-3.5 py-2.5 min-h-[44px] border border-white/60 bg-white/50 hover:bg-white/70 shadow-sm hover:shadow-lg transition flex items-center gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300/60">
                                <span
                                    class="relative h-9 w-9 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center shadow-inner ring-1 ring-white/30">
                                    <i class="fa-solid fa-clipboard-list"></i>
                                </span>
                                <span class="text-[13px] sm:text-sm font-semibold text-emerald-900">View
                                    Complaints</span>
                                <i
                                    class="fa-solid fa-chevron-right ml-auto text-emerald-600 opacity-0 translate-x-[-2px] group-hover:opacity-100 group-hover:translate-x-0 transition"></i>
                            </a>

                            <!-- View Cases button-like action -->
                            <a href="view_cases.php" role="button" aria-label="View Cases"
                                class="quick-btn glass group rounded-xl px-3.5 py-2.5 min-h-[44px] border border-white/60 bg-white/50 hover:bg-white/70 shadow-sm hover:shadow-lg transition flex items-center gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300/60">
                                <span
                                    class="relative h-9 w-9 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center shadow-inner ring-1 ring-white/30">
                                    <i class="fa-solid fa-gavel"></i>
                                </span>
                                <span class="text-[13px] sm:text-sm font-semibold text-indigo-900">View Cases</span>
                                <i
                                    class="fa-solid fa-chevron-right ml-auto text-indigo-600 opacity-0 translate-x-[-2px] group-hover:opacity-100 group-hover:translate-x-0 transition"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Right Column: Premium Stat Cards -->
                <div>
                    <div class="relative border-white">

                        <div class="relative z-10 mb-6 flex items-center justify-between">
                            <h2 class="text-sky-900 font-semibold tracking-tight flex items-center gap-2"><i
                                    class="fa-solid fa-chart-line text-sky-600"></i> Overview</h2>
                            <span
                                class="px-2 py-1 rounded-md text-[10px] font-medium bg-white/40 text-sky-700 border border-white/50">Updated
                                <?= date('H:i') ?></span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <!-- Cases -->
                            <div class="premium-stat-card relative group">
                                <div
                                    class="premium-stat-border absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition">
                                </div>
                                <div
                                    class="premium-stat-inner relative rounded-2xl p-4 bg-white/55 border border-white/60 flex flex-col gap-2 overflow-hidden">
                                    <div class="premium-stat-particles"></div>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="premium-icon-container relative h-11 w-11 rounded-full bg-gradient-to-br from-sky-400 to-sky-600 text-white flex items-center justify-center shadow-inner">
                                            <i class="fa-solid fa-gavel"></i>
                                            <div class="premium-icon-light rounded-full"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700">
                                                Cases</p>
                                            <p class="text-lg font-semibold text-sky-900 leading-none count-up"
                                                data-target="<?= $casesCount ?>">0</p>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-sky-700/70">Open: <span
                                            class="font-medium text-sky-800"><?= $openCases ?></span></p>
                                </div>
                            </div>
                            <!-- Complaints -->
                            <div class="premium-stat-card relative group">
                                <div
                                    class="premium-stat-border absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition">
                                </div>
                                <div
                                    class="premium-stat-inner relative rounded-2xl p-4 bg-white/55 border border-white/60 flex flex-col gap-2 overflow-hidden">
                                    <div class="premium-stat-particles"></div>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="premium-icon-container relative h-11 w-11 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center shadow-inner">
                                            <i class="fa-solid fa-file-circle-plus"></i>
                                            <div class="premium-icon-light rounded-full"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700">
                                                Complaints</p>
                                            <p class="text-lg font-semibold text-amber-900 leading-none count-up"
                                                data-target="<?= $complaintsCount ?>">0</p>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-amber-700/70">Pending: <span
                                            class="font-medium text-amber-800"><?= $pendingComplaints ?></span></p>
                                </div>
                            </div>
                            <!-- Resolved -->
                            <div class="premium-stat-card relative group">
                                <div
                                    class="premium-stat-border absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition">
                                </div>
                                <div
                                    class="premium-stat-inner relative rounded-2xl p-4 bg-white/55 border border-white/60 flex flex-col gap-2 overflow-hidden">
                                    <div class="premium-stat-particles"></div>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="premium-icon-container relative h-11 w-11 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center shadow-inner">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <div class="premium-icon-light rounded-full"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700">
                                                Resolved</p>
                                            <p class="text-lg font-semibold text-emerald-900 leading-none count-up"
                                                data-target="<?= $resolvedCases ?>">0</p>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-emerald-700/70">Closed cases</p>
                                </div>
                            </div>
                            <!-- Hearings -->
                            <div class="premium-stat-card relative group">
                                <div
                                    class="premium-stat-border absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition">
                                </div>
                                <div
                                    class="premium-stat-inner relative rounded-2xl p-4 bg-white/55 border border-white/60 flex flex-col gap-2 overflow-hidden">
                                    <div class="premium-stat-particles"></div>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="premium-icon-container relative h-11 w-11 rounded-full bg-gradient-to-br from-rose-400 to-rose-600 text-white flex items-center justify-center shadow-inner">
                                            <i class="fa-solid fa-calendar-day"></i>
                                            <div class="premium-icon-light rounded-full"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-700">
                                                Hearings</p>
                                            <p class="text-lg font-semibold text-rose-900 leading-none count-up"
                                                data-target="<?= $scheduledHearings ?>">0</p>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-rose-700/70">Scheduled sessions</p>
                                </div>
                            </div>
                            <!-- Mediation -->
                            <div class="premium-stat-card relative group">
                                <div
                                    class="premium-stat-border absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition">
                                </div>
                                <div
                                    class="premium-stat-inner relative rounded-2xl p-4 bg-white/55 border border-white/60 flex flex-col gap-2 overflow-hidden">
                                    <div class="premium-stat-particles"></div>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="premium-icon-container relative h-11 w-11 rounded-full bg-gradient-to-br from-yellow-300 to-yellow-500 text-white flex items-center justify-center shadow-inner">
                                            <i class="fa-solid fa-handshake"></i>
                                            <div class="premium-icon-light rounded-full"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-[10px] font-semibold uppercase tracking-wide text-yellow-700">
                                                Mediation</p>
                                            <p class="text-lg font-semibold text-yellow-800 leading-none count-up"
                                                data-target="<?= $mediationCases ?>">0</p>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-yellow-700/70">Ongoing phase</p>
                                </div>
                            </div>
                            <!-- Resolution / Settlement grouped if space -->
                            <div class="premium-stat-card relative group">
                                <div
                                    class="premium-stat-border absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition">
                                </div>
                                <div
                                    class="premium-stat-inner relative rounded-2xl p-4 bg-white/55 border border-white/60 flex flex-col gap-2 overflow-hidden">
                                    <div class="premium-stat-particles"></div>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="premium-icon-container relative h-11 w-11 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 text-white flex items-center justify-center shadow-inner">
                                            <i class="fa-solid fa-scale-balanced"></i>
                                            <div class="premium-icon-light rounded-full"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-[10px] font-semibold uppercase tracking-wide text-indigo-700">
                                                Settlement</p>
                                            <p class="text-lg font-semibold text-indigo-900 leading-none count-up"
                                                data-target="<?= $settlementCases ?>">0</p>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-indigo-700/70">Finalized forms</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CALENDAR + UPCOMING HEARINGS SPLIT -->
    <div class="max-w-screen-2xl mx-auto px-5 pt-10 relative">
        <div class="grid lg:grid-cols-12 gap-8 items-start">
            <!-- Calendar (3/4 width) -->
            <div class="lg:col-span-7">
                <div class="glass rounded-2xl p-0 lg:p-0 fade-in h-full flex flex-col relative overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none">
                        <div
                            class="absolute -top-24 -left-16 w-72 h-72 bg-gradient-to-tr from-sky-200/70 to-sky-400/30 rounded-full blur-3xl">
                        </div>
                        <div
                            class="absolute -bottom-28 -right-20 w-80 h-80 bg-gradient-to-br from-white/40 via-sky-100/50 to-sky-300/30 rounded-full blur-3xl">
                        </div>
                    </div>
                    <div class="relative z-10 flex items-center gap-2 px-5 pt-5 mb-4">
                        <i class="fa-solid fa-calendar-days text-sky-600"></i>
                        <h2 class="text-sky-900 font-semibold tracking-tight">Hearing Calendar</h2>
                        <a href="view_hearing_calendar_resident.php" title="Open full calendar"
                            class="ml-auto inline-flex items-center justify-center h-8 w-8 rounded-lg bg-white/60 hover:bg-white/80 border border-white/60 text-sky-700 hover:text-sky-900 shadow-sm transition"
                            aria-label="Open full calendar">
                            <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                        </a>
                    </div>
                    <div class="relative z-10 flex-1 h-full px-5 pb-5">
                        <iframe id="resident-calendar" src="../SecMenu/schedule/CalendarResident.php"
                            class="w-full h-full rounded-2xl border border-white/40 bg-white/60 shadow-inner"></iframe>
                    </div>
                    <div
                        class="relative z-10 px-5 pb-5 grid grid-cols-2 sm:grid-cols-4 gap-3 text-[11px] text-sky-800/80">
                        <div class="flex items-center gap-2"><span
                                class="w-3 h-3 inline-block bg-purple-100 border-l-4 border-purple-600"></span><span><i
                                    class="fas fa-gavel text-purple-600"></i> Hearing</span></div>
                        <div class="flex items-center gap-2"><span
                                class="w-3 h-3 inline-block bg-amber-100 border-l-4 border-amber-500"></span><span><i
                                    class="fas fa-handshake text-amber-500"></i> Mediation</span></div>
                        <div class="flex items-center gap-2"><span
                                class="w-3 h-3 inline-block bg-emerald-100 border-l-4 border-emerald-600"></span><span><i
                                    class="fas fa-balance-scale text-emerald-600"></i> Resolution</span></div>
                        <div class="flex items-center gap-2"><span
                                class="w-3 h-3 inline-block bg-pink-100 border-l-4 border-pink-600"></span><span><i
                                    class="fas fa-file-signature text-pink-600"></i> Settlement</span></div>
                    </div>
                </div>
            </div>
            <!-- Upcoming / Past Hearings List (1/4 width) -->
            <div class="lg:col-span-5">
                <!-- Case Timeline Card -->
                <div
                    class="relative rounded-2xl p-5 lg:p-6 mb-6 bg-gradient-to-br from-white/70 via-white/60 to-sky-50/60 backdrop-blur-xl border border-white/50 shadow-[0_10px_35px_-10px_rgba(14,116,144,0.18)] overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none">
                        <div
                            class="absolute -top-14 -left-10 w-48 h-48 bg-gradient-to-tr from-sky-300/30 to-sky-500/20 rounded-full blur-3xl">
                        </div>
                        <div
                            class="absolute -bottom-16 -right-14 w-56 h-56 bg-gradient-to-br from-sky-100/40 via-white/40 to-sky-200/30 rounded-full blur-2xl">
                        </div>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-3 gap-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <h3
                                    class="text-sky-900 font-semibold tracking-tight flex items-center gap-2 whitespace-nowrap">
                                    <i class="fa-solid fa-timeline text-sky-600"></i>Case Timeline</h3>
                                <span
                                    class="hidden sm:inline px-2 py-1 rounded-lg text-[10px] font-medium bg-sky-600/10 text-sky-700 border border-white/50">LGC
                                    1991 · Secs. 399–422</span>
                            </div>
                            <div class="ml-auto flex items-center gap-2 min-w-[50%]">
                                <label for="case-select" class="text-[11px] text-sky-700 whitespace-nowrap">Select
                                    case:</label>
                                <select id="case-select"
                                    class="flex-1 min-w-0 text-[12px] px-2 py-1 rounded-md bg-white/70 border border-white/60 text-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-300">
                                    <!-- options injected by JS -->
                                </select>
                            </div>
                        </div>
                        <div id="case-phase-summary" class="mb-3 text-[11px] text-sky-700/90"></div>
                        <div class="space-y-4 relative">
                            <!-- Vertical guide line -->
                            <div class="absolute left-3 top-1 bottom-1 w-px bg-sky-200/70"></div>

                            <!-- Filing of Complaint -->
                            <div class="relative pl-8 timeline-step" data-step="filing">
                                <div
                                    class="absolute left-0 top-0 w-6 h-6 rounded-full bg-purple-500 text-white flex items-center justify-center shadow ring-2 ring-white">
                                    <i class="fa-solid fa-file-circle-plus text-[11px]"></i>
                                </div>
                                <div class="rounded-xl border border-white/60 bg-white/60 p-3">
                                    <div class="flex items-center gap-2">
                                        <p class="text-[13px] font-semibold text-sky-900">Filing of Complaint</p>
                                        <span id="badge-filing"
                                            class="ml-auto px-2 py-0.5 rounded-md text-[10px] bg-purple-500/15 text-purple-700 border border-purple-500/20">Day
                                            0</span>
                                    </div>
                                    <p class="mt-1 text-[12px] text-sky-700/80">Starts when complaint is filed with the
                                        Punong Barangay. <span id="date-filing" class="font-medium text-sky-800"></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Mediation by Punong Barangay -->
                            <div class="relative pl-8 timeline-step" data-step="mediation">
                                <div
                                    class="absolute left-0 top-0 w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center shadow ring-2 ring-white">
                                    <i class="fa-solid fa-handshake text-[11px]"></i>
                                </div>
                                <div class="rounded-xl border border-white/60 bg-white/60 p-3">
                                    <div class="flex items-center gap-2">
                                        <p class="text-[13px] font-semibold text-sky-900">Mediation by Punong Barangay
                                        </p>
                                        <span id="badge-mediation"
                                            class="ml-auto px-2 py-0.5 rounded-md text-[10px] bg-amber-500/15 text-amber-700 border border-amber-500/20">Up
                                            to 15 days</span>
                                    </div>
                                    <p class="mt-1 text-[12px] text-sky-700/80">Attempt to amicably settle the dispute
                                        within 15 days. <span id="range-mediation"
                                            class="font-medium text-sky-800"></span></p>
                                </div>
                            </div>

                            <!-- Pangkat Conciliation -->
                            <div class="relative pl-8 timeline-step" data-step="pangkat">
                                <div
                                    class="absolute left-0 top-0 w-6 h-6 rounded-full bg-sky-500 text-white flex items-center justify-center shadow ring-2 ring-white">
                                    <i class="fa-solid fa-people-group text-[11px]"></i>
                                </div>
                                <div class="rounded-xl border border-white/60 bg-white/60 p-3">
                                    <div class="flex items-center gap-2">
                                        <p class="text-[13px] font-semibold text-sky-900">Pangkat ng Tagapagkasundo
                                            (Conciliation)</p>
                                        <span id="badge-pangkat"
                                            class="ml-auto px-2 py-0.5 rounded-md text-[10px] bg-sky-500/15 text-sky-700 border border-sky-500/20">15–30
                                            days</span>
                                    </div>
                                    <p class="mt-1 text-[12px] text-sky-700/80">Conciliation within 15 days; may extend
                                        up to another 15 days upon agreement. <span id="range-pangkat"
                                            class="font-medium text-sky-800"></span></p>
                                </div>
                            </div>

                            <!-- Arbitration (optional) -->
                            <div class="relative pl-8 timeline-step" data-step="arbitration">
                                <div
                                    class="absolute left-0 top-0 w-6 h-6 rounded-full bg-indigo-500 text-white flex items-center justify-center shadow ring-2 ring-white">
                                    <i class="fa-solid fa-scale-balanced text-[11px]"></i>
                                </div>
                                <div class="rounded-xl border border-white/60 bg-white/60 p-3">
                                    <div class="flex items-center gap-2">
                                        <p class="text-[13px] font-semibold text-sky-900">Arbitration (if agreed)</p>
                                        <span id="badge-arbitration"
                                            class="ml-auto px-2 py-0.5 rounded-md text-[10px] bg-indigo-500/15 text-indigo-700 border border-indigo-500/20">Within
                                            10 days</span>
                                    </div>
                                    <p class="mt-1 text-[12px] text-sky-700/80">If parties agree, an arbitration award
                                        is rendered within 10 days. <span id="note-arbitration"
                                            class="text-[11px] italic text-sky-600/80"></span></p>
                                </div>
                            </div>

                            <!-- Execution / Finality -->
                            <div class="relative pl-8 timeline-step" data-step="finality">
                                <div
                                    class="absolute left-0 top-0 w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow ring-2 ring-white">
                                    <i class="fa-solid fa-file-signature text-[11px]"></i>
                                </div>
                                <div class="rounded-xl border border-white/60 bg-white/60 p-3">
                                    <div class="flex items-center gap-2">
                                        <p class="text-[13px] font-semibold text-sky-900">Execution of Settlement/Award
                                        </p>
                                        <span id="badge-finality"
                                            class="ml-auto px-2 py-0.5 rounded-md text-[10px] bg-emerald-500/15 text-emerald-700 border border-emerald-500/20">Final
                                            in 10 days</span>
                                    </div>
                                    <p class="mt-1 text-[12px] text-sky-700/80">Becomes final after 10 days unless
                                        repudiated. <span id="note-finality"
                                            class="text-[11px] italic text-sky-600/80"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointments Card -->
                <div
                    class="relative rounded-2xl p-5 lg:p-6 bg-gradient-to-br from-white/70 via-white/60 to-sky-50/60 backdrop-blur-xl border border-white/50 shadow-[0_10px_35px_-10px_rgba(14,116,144,0.18)] overflow-hidden flex flex-col h-full">
                    <div class="absolute inset-0 pointer-events-none">
                        <div
                            class="absolute -top-14 -right-10 w-48 h-48 bg-gradient-to-tr from-sky-300/40 to-sky-500/30 rounded-full blur-3xl">
                        </div>
                        <div
                            class="absolute -bottom-16 -left-14 w-56 h-56 bg-gradient-to-br from-sky-100/50 via-white/40 to-sky-200/40 rounded-full blur-2xl">
                        </div>
                    </div>
                    <div class="relative z-10 flex items-center justify-between mb-4 gap-3">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sky-900 font-semibold tracking-tight flex items-center gap-2"><i
                                    class="fa-solid fa-clock text-sky-600"></i>Appointments</h3>
                            <span
                                class="px-2 py-1 rounded-lg text-[10px] font-medium bg-sky-600/10 text-sky-700 border border-white/50">U:
                                <?= count($upcomingHearings) ?> · P: <?= count($pastHearings) ?></span>
                        </div>
                        <div class="flex gap-1 bg-white/50 rounded-lg p-1 border border-white/60">
                            <button id="btn-upcoming" type="button"
                                class="px-3 py-1.5 rounded-md text-[11px] font-semibold tracking-wide transition bg-sky-600 text-white shadow">Upcoming</button>
                            <button id="btn-past" type="button"
                                class="px-3 py-1.5 rounded-md text-[11px] font-semibold tracking-wide transition text-sky-700 hover:bg-white/70">Past</button>
                        </div>
                    </div>
                    <div id="hearings-upcoming-list"
                        class="relative z-10 space-y-3 overflow-y-auto pr-1 styled-scroll flex-1"
                        style="scrollbar-width: thin;">
                        <?php if (empty($upcomingHearings)): ?>
                            <div
                                class="rounded-xl border border-white/60 bg-white/60 p-4 text-center text-[12px] text-sky-700/70 min-h-[400px] flex items-center justify-center">
                                No upcoming hearings starting tomorrow.</div>
                        <?php else: ?>
                            <?php foreach ($upcomingHearings as $h):
                                $dt = new DateTime($h['hearingDateTime']);
                                $day = $dt->format('M d');
                                $time = $dt->format('g:i A');
                                $nowDt = new DateTime();
                                $interval = $nowDt->diff($dt);
                                $diffHours = $interval->days * 24 + $interval->h;
                                $relLabel = '';
                                if ($diffHours < 1) {
                                    $mins = max(1, (int) floor($interval->i));
                                    $relLabel = 'in ' . $mins . 'm';
                                } elseif ($diffHours < 24) {
                                    $relLabel = 'in ' . ($diffHours) . 'h';
                                } elseif ($diffHours < 48) {
                                    $relLabel = 'Tomorrow';
                                } else {
                                    $relLabel = $interval->days . 'd';
                                }
                                $soon = $diffHours <= 48; // highlight if within 48h
                                ?>
                                <div class="group relative rounded-2xl overflow-hidden">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-br from-sky-200/40 via-white/30 to-sky-100/30 opacity-0 group-hover:opacity-100 transition">
                                    </div>
                                    <div
                                        class="relative flex gap-3 p-4 rounded-2xl border border-white/60 bg-white/55 backdrop-blur-md shadow-sm group-hover:shadow-lg transition">
                                        <div class="flex flex-col items-center justify-start min-w-[50px] pt-0.5">
                                            <div
                                                class="text-[11px] font-semibold uppercase tracking-wide text-sky-700 flex flex-col items-center">
                                                <span><?= htmlspecialchars($day) ?></span>
                                                <span
                                                    class="mt-0.5 text-[10px] text-sky-600/80 font-medium"><?= htmlspecialchars($time) ?></span>
                                                <span
                                                    class="mt-0.5 text-[9px] font-medium <?= $soon ? 'text-rose-600' : 'text-sky-500' ?>"><?= htmlspecialchars($relLabel) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-[13px] font-semibold text-sky-900 leading-snug truncate flex items-center gap-1">
                                                <i class="fa-solid fa-gavel text-sky-600"></i>
                                                <?= htmlspecialchars($h['hearingTitle']) ?>
                                            </p>
                                            <p class="mt-1 text-[11px] text-sky-700/80 line-clamp-2">Case
                                                #<?= htmlspecialchars($h['Case_ID']) ?> •
                                                <?= htmlspecialchars($h['place'] ?? 'TBD') ?></p>
                                            <?php if (!empty($h['remarks'])): ?>
                                                <p class="mt-1 text-[10px] italic text-sky-600/70 line-clamp-2">
                                                    “<?= htmlspecialchars($h['remarks']) ?>”</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <?php if ($soon): ?>
                                                <span
                                                    class="px-2 py-1 rounded-md bg-rose-500/15 text-rose-600 text-[9px] font-semibold tracking-wide">SOON</span>
                                            <?php else: ?>
                                                <span
                                                    class="px-2 py-1 rounded-md bg-emerald-500/15 text-emerald-600 text-[9px] font-semibold tracking-wide">SET</span>
                                            <?php endif; ?>
                                            <a href="case_details.php?case_id=<?= urlencode($h['Case_ID']) ?>"
                                                class="mt-auto text-[10px] text-sky-600 hover:text-sky-800 font-medium inline-flex items-center gap-1 group/link">
                                                View <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="hearings-past-list"
                        class="relative z-10 space-y-3 overflow-y-auto pr-1 styled-scroll flex-1 hidden"
                        style="scrollbar-width: thin;">
                        <?php if (empty($pastHearings)): ?>
                            <div
                                class="rounded-xl border border-white/60 bg-white/60 p-4 text-center text-[12px] text-sky-700/70 min-h-[1000px] flex items-center justify-center">
                                No past hearings found.</div>
                        <?php else: ?>
                            <?php foreach ($pastHearings as $h):
                                $dt = new DateTime($h['hearingDateTime']);
                                $day = $dt->format('M d');
                                $time = $dt->format('g:i A');
                                $nowDt = new DateTime();
                                $interval = $nowDt->diff($dt);
                                $diffHours = $interval->days * 24 + $interval->h;
                                $relLabel = '';
                                if ($diffHours < 1) {
                                    $mins = max(1, (int) floor($interval->i));
                                    $relLabel = 'in ' . $mins . 'm';
                                } elseif ($diffHours < 24) {
                                    $relLabel = 'in ' . ($diffHours) . 'h';
                                } elseif ($diffHours < 48) {
                                    $relLabel = 'Tomorrow';
                                } else {
                                    $relLabel = $interval->days . 'd';
                                }
                                $soon = $diffHours <= 48; // highlight if within 48h (kept for stylistic consistency)
                                ?>
                                <div class="group relative rounded-2xl overflow-hidden">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-br from-sky-200/40 via-white/30 to-sky-100/30 opacity-0 group-hover:opacity-100 transition">
                                    </div>
                                    <div
                                        class="relative flex gap-3 p-4 rounded-2xl border border-white/60 bg-white/55 backdrop-blur-md shadow-sm group-hover:shadow-lg transition">
                                        <div class="flex flex-col items-center justify-start min-w-[50px] pt-0.5">
                                            <div
                                                class="text-[11px] font-semibold uppercase tracking-wide text-sky-700 flex flex-col items-center">
                                                <span><?= htmlspecialchars($day) ?></span>
                                                <span
                                                    class="mt-0.5 text-[10px] text-sky-600/80 font-medium"><?= htmlspecialchars($time) ?></span>
                                                <span
                                                    class="mt-0.5 text-[9px] font-medium <?= $soon ? 'text-rose-600' : 'text-sky-500' ?>"><?= htmlspecialchars($relLabel) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-[13px] font-semibold text-sky-900 leading-snug truncate flex items-center gap-1">
                                                <i class="fa-solid fa-gavel text-sky-600"></i>
                                                <?= htmlspecialchars($h['hearingTitle']) ?>
                                            </p>
                                            <p class="mt-1 text-[11px] text-sky-700/80 line-clamp-2">Case
                                                #<?= htmlspecialchars($h['Case_ID']) ?> •
                                                <?= htmlspecialchars($h['place'] ?? 'TBD') ?></p>
                                            <?php if (!empty($h['remarks'])): ?>
                                                <p class="mt-1 text-[10px] italic text-sky-600/70 line-clamp-2">
                                                    “<?= htmlspecialchars($h['remarks']) ?>”</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <span
                                                class="px-2 py-1 rounded-md bg-sky-500/10 text-sky-600 text-[9px] font-semibold tracking-wide">PAST</span>
                                            <a href="case_details.php?case_id=<?= urlencode($h['Case_ID']) ?>"
                                                class="mt-auto text-[10px] text-sky-600 hover:text-sky-800 font-medium inline-flex items-center gap-1 group/link">
                                                View <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="relative z-10 mt-6 pt-2 border-t border-white/50">
                        <a href="view_cases.php"
                            class="group w-full inline-flex items-center justify-center gap-2 text-[11px] font-semibold tracking-wide uppercase px-4 py-2 rounded-xl bg-sky-600/90 hover:bg-sky-700 text-white shadow transition">
                            <i class="fa-solid fa-layer-group text-white/90"></i> All Cases
                            <span
                                class="opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 transition text-[10px]">›</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inject case data for timeline
        window.__residentCases = <?php echo json_encode($residentCases, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        window.__serverNowIso = '<?php echo $serverNowIso; ?>';

        // Loader fade-out logic (match secretary timing behavior)
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(() => {
                const loader = document.querySelector('.loader-wrapper');
                if (!loader) return;
                loader.classList.add('fade-out');
                setTimeout(() => loader.remove(), 5000); // mirrors secretary extended fade removal
            }, 3500); // mirrors secretary initial delay
        });
        window.addEventListener('load', function () {
            const loader = document.querySelector('.loader-wrapper');
            if (loader) {
                loader.classList.add('fade-out');
                setTimeout(() => loader.remove(), 5000);
            }
        });
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

        // Count-up animation (activates when stats panel enters viewport)
        document.addEventListener('DOMContentLoaded', () => {
            const counters = document.querySelectorAll('.count-up');
            const duration = 1300; // ms
            const easeOutCubic = t => 1 - Math.pow(1 - t, 3);

            function animateCount(el) {
                const target = parseInt(el.getAttribute('data-target') || '0', 10);
                if (isNaN(target)) return;
                const startTime = performance.now();
                function frame(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(1, elapsed / duration);
                    const eased = easeOutCubic(progress);
                    const value = Math.floor(eased * target);
                    el.textContent = value.toLocaleString();
                    if (progress < 1) requestAnimationFrame(frame); else el.textContent = target.toLocaleString();
                }
                requestAnimationFrame(frame);
            }

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const el = entry.target;
                            el.classList.add('visible');
                            animateCount(el);
                            obs.unobserve(el);
                        }
                    });
                }, { threshold: 0.4 });
                counters.forEach(c => observer.observe(c));
            } else {
                // Fallback: animate immediately
                counters.forEach(c => { c.classList.add('visible'); animateCount(c); });
            }
        });

        // Hearings list toggle (no reload)
        document.addEventListener('DOMContentLoaded', () => {
            const btnUpcoming = document.getElementById('btn-upcoming');
            const btnPast = document.getElementById('btn-past');
            const listUpcoming = document.getElementById('hearings-upcoming-list');
            const listPast = document.getElementById('hearings-past-list');
            if (!btnUpcoming || !btnPast || !listUpcoming || !listPast) return;
            function setActive(tab) {
                const activeClasses = ['bg-sky-600', 'text-white', 'shadow'];
                const inactiveClasses = ['text-sky-700'];
                if (tab === 'upcoming') {
                    listUpcoming.classList.remove('hidden');
                    listPast.classList.add('hidden');
                    btnUpcoming.classList.add(...activeClasses);
                    btnUpcoming.classList.remove(...inactiveClasses);
                    btnPast.classList.remove(...activeClasses);
                    btnPast.classList.add(...inactiveClasses);
                } else {
                    listUpcoming.classList.add('hidden');
                    listPast.classList.remove('hidden');
                    btnPast.classList.add(...activeClasses);
                    btnPast.classList.remove(...inactiveClasses);
                    btnUpcoming.classList.remove(...activeClasses);
                    btnUpcoming.classList.add(...inactiveClasses);
                }
            }
            btnUpcoming.addEventListener('click', () => setActive('upcoming'));
            btnPast.addEventListener('click', () => setActive('past'));
            setActive('upcoming');
        });

        // Case Timeline logic (selector + phase computation)
        document.addEventListener('DOMContentLoaded', () => {
            const cases = Array.isArray(window.__residentCases) ? window.__residentCases : [];
            const now = new Date(window.__serverNowIso || Date.now());
            const select = document.getElementById('case-select');
            const summary = document.getElementById('case-phase-summary');
            if (!select || !summary) return;

            // Populate selector
            if (cases.length === 0) {
                select.innerHTML = '<option value="">No cases available</option>';
                summary.textContent = 'No case selected. Filing and phase dates will appear here once you have a case.';
                return;
            } else {
                select.innerHTML = cases.map(c => {
                    const label = `Case #${c.Case_ID} — ${escapeHtml(c.Complaint_Title || 'Untitled')}`;
                    return `<option value="${c.Case_ID}">${label}</option>`;
                }).join('');
            }

            const steps = {
                filing: document.querySelector('.timeline-step[data-step="filing"]'),
                mediation: document.querySelector('.timeline-step[data-step="mediation"]'),
                pangkat: document.querySelector('.timeline-step[data-step="pangkat"]'),
                arbitration: document.querySelector('.timeline-step[data-step="arbitration"]'),
                finality: document.querySelector('.timeline-step[data-step="finality"]')
            };
            const spans = {
                filing: document.getElementById('date-filing'),
                mediation: document.getElementById('range-mediation'),
                pangkat: document.getElementById('range-pangkat'),
                arbNote: document.getElementById('note-arbitration'),
                finNote: document.getElementById('note-finality')
            };

            function addDays(date, days) {
                const d = new Date(date);
                d.setDate(d.getDate() + days);
                return d;
            }
            function fmt(d) {
                try { return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }); } catch { return d.toISOString().split('T')[0]; }
            }
            function daysBetween(a, b) {
                const ms = b.getTime() - a.getTime();
                return Math.floor(ms / (1000 * 60 * 60 * 24));
            }
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
            function updateTimeline(selectedCase) {
                if (!selectedCase || !selectedCase.Date_Filed) return;
                const f = new Date(selectedCase.Date_Filed);
                const medEnd = addDays(f, 15);
                const pangStart = addDays(medEnd, 0); // same day after mediation window
                const pangEnd = addDays(f, 30);
                const pangMax = addDays(f, 45);

                // Update text ranges
                if (spans.filing) spans.filing.textContent = `Filed: ${fmt(f)}`;
                if (spans.mediation) spans.mediation.textContent = `${fmt(f)} → ${fmt(medEnd)} (15d)`;
                if (spans.pangkat) spans.pangkat.textContent = `${fmt(pangStart)} → ${fmt(pangEnd)} (15d) · Max: ${fmt(pangMax)} (45d)`;
                if (spans.arbNote) spans.arbNote.textContent = `Optional phase — only if both parties agree`;
                if (spans.finNote) spans.finNote.textContent = `Applies once a settlement/award is reached`;

                // Phase marking
                clearStepClasses();
                const status = computePhase(selectedCase.Date_Filed);
                let phaseLabel = 'Unknown';
                if (status.phase === 'filing') {
                    mark(steps.filing, 'current'); mark(steps.mediation, 'upcoming'); mark(steps.pangkat, 'upcoming'); mark(steps.arbitration, 'upcoming'); mark(steps.finality, 'upcoming');
                    phaseLabel = 'Filing of Complaint';
                } else if (status.phase === 'mediation') {
                    mark(steps.filing, 'completed'); mark(steps.mediation, 'current'); mark(steps.pangkat, 'upcoming'); mark(steps.arbitration, 'upcoming'); mark(steps.finality, 'upcoming');
                    phaseLabel = 'Mediation by Punong Barangay';
                } else if (status.phase === 'pangkat') {
                    mark(steps.filing, 'completed'); mark(steps.mediation, 'completed'); mark(steps.pangkat, 'current'); mark(steps.arbitration, 'upcoming'); mark(steps.finality, 'upcoming');
                    phaseLabel = status.ext ? 'Pangkat Conciliation (Extended)' : 'Pangkat Conciliation';
                } else if (status.phase === 'beyond') {
                    mark(steps.filing, 'completed'); mark(steps.mediation, 'completed'); mark(steps.pangkat, 'completed'); mark(steps.arbitration, 'upcoming'); mark(steps.finality, 'upcoming');
                    phaseLabel = 'Beyond 45 days — escalate or finalize';
                }

                // Summary line
                const filedStr = fmt(f);
                const dayN = Math.max(0, computePhase(selectedCase.Date_Filed).days);
                summary.innerHTML = `Selected: <span class="font-semibold">Case #${selectedCase.Case_ID}</span> · Filed <span class="font-medium">${filedStr}</span> · <span class="font-semibold">Day ${dayN}</span> · Current phase: <span class="font-semibold">${phaseLabel}</span>`;
            }

            function escapeHtml(str) {
                if (str == null) return '';
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
                return String(str).replace(/[&<>"']/g, ch => map[ch] || ch);
            }

            // Initialize
            const initial = cases[0];
            updateTimeline(initial);
            select.value = initial ? String(initial.Case_ID) : '';
            select.addEventListener('change', () => {
                const id = select.value;
                const found = cases.find(c => String(c.Case_ID) === id);
                updateTimeline(found);
            });
        });
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