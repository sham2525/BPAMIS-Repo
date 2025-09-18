<?php
session_start();
include '../server/server.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../bpamis_website/login.php");
    exit();
}

$external_id = $_SESSION['user_id'];
$full_name = "External Complainant";

// Get full name
$stmt = $conn->prepare("SELECT first_name, last_name FROM external_complainant WHERE external_complaint_id = ?");
$stmt->bind_param("i", $external_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $full_name = $row['first_name'] . ' ' . $row['last_name'];
}

// Count Complaints
$complaintsCount = $conn->query("SELECT COUNT(*) AS total FROM complaint_info WHERE external_complainant_id = $external_id")->fetch_assoc()['total'] ?? 0;

// Count Cases via complaint_info
$casesCount = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.external_complainant_id = $external_id
")->fetch_assoc()['total'] ?? 0;

// Pending Complaints
$pendingComplaints = $conn->query("
    SELECT COUNT(*) AS total 
    FROM complaint_info 
    WHERE external_complainant_id = $external_id AND LOWER(TRIM(Status)) = 'pending'
")->fetch_assoc()['total'] ?? 0;


// Resolved Cases
$resolvedCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.external_complainant_id = $external_id AND Case_Status = 'Resolved'
")->fetch_assoc()['total'] ?? 0;

// Pending Cases
$pendingCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.external_complainant_id = $external_id AND Case_Status = 'Pending'
")->fetch_assoc()['total'] ?? 0;

$resolutionCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.external_complainant_id = $external_id AND Case_Status = 'resolution'
")->fetch_assoc()['total'] ?? 0;

$mediationCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.external_complainant_id = $external_id AND Case_Status = 'mediation'
")->fetch_assoc()['total'] ?? 0;

$settlementCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.external_complainant_id = $external_id AND Case_Status = 'settlement'
")->fetch_assoc()['total'] ?? 0;

// Scheduled Hearings
$scheduledHearings = $conn->query("
    SELECT COUNT(*) AS total 
    FROM schedule_list sl
    JOIN case_info ci ON sl.Case_ID = ci.Case_ID
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID
    WHERE co.external_complainant_id = $external_id
")->fetch_assoc()['total'] ?? 0;

// Open Cases
$openCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.external_complainant_id = $external_id AND Case_Status = 'open'
")->fetch_assoc()['total'] ?? 0;



// Percentages (guard divisions)
$pendingComplaintsPercent = $complaintsCount ? round(($pendingComplaints / $complaintsCount) * 100) : 0;
$caseResolvedPercent = $casesCount ? round(($resolvedCases / $casesCount) * 100) : 0;
$hearingPercent = $casesCount ? min(100, round(($scheduledHearings / max($casesCount,1)) * 100)) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title> External Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="tailwind.js"></script>
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
    body { background: radial-gradient(circle at 20% 20%, #e0f2ff 0%, #f5f9ff 50%, #ffffff 100%); }
    .orb { position:absolute; border-radius:50%; filter:blur(40px); opacity:.55; mix-blend-mode:multiply; }
    .orb.one { width:480px; height:480px; background:linear-gradient(135deg,#0c9ced,#7cccfd); top:-140px; right:-120px; animation:float 14s ease-in-out infinite; }
    .orb.two { width:360px; height:360px; background:linear-gradient(135deg,#bae2fd,#e0effe); bottom:-120px; left:-100px; animation:float 11s ease-in-out reverse infinite; }
    .glass { backdrop-filter:blur(14px); background:linear-gradient(135deg,rgba(255,255,255,.65),rgba(255,255,255,.35)); border:1px solid rgba(255,255,255,.45); box-shadow:0 10px 40px -12px rgba(12,156,237,.25),0 4px 18px -6px rgba(12,156,237,.18); }
    .section-label { font-size:.65rem; letter-spacing:.09em; font-weight:600; text-transform:uppercase; color:#0369a1; }
    .progress-wrap { height:12px; }
    .progress-bar { transition: width 1s cubic-bezier(.4,.0,.2,1); }
    .quick-btn { position:relative; overflow:hidden; transition:.35s; }
    .quick-btn:before { content:""; position:absolute; inset:0; background:linear-gradient(120deg,rgba(255,255,255,.6),rgba(255,255,255,0)); opacity:0; transition:opacity .5s; }
    .quick-btn:hover:before { opacity:1; }
    .quick-btn:hover { transform:translateY(-4px); }
        .gradient-bg {
            background: linear-gradient(to right, #f0f7ff, #e0effe);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }        .progress-bar {
            transition: width 1s ease-in-out;
        }
        
        /* Modern Calendar Styles */
        .calendar-container {
            --fc-border-color: #f0f0f0;
            --fc-daygrid-event-dot-width: 6px;
            --fc-event-border-radius: 6px;
            --fc-small-font-size: 0.75rem;
        }

          .fc-daygrid-event-dot { /* the actual dot */
  margin-left: 50px;
  }
        
        .calendar-container .fc-theme-standard th {
            padding: 12px 0;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: #6b7280;
            border: none;
        }
        
        .calendar-container .fc-theme-standard td {
            border-color: #f5f5f5;
        }
        
        .calendar-container .fc-col-header-cell {
            background: transparent;
        }
        
        .calendar-container .fc-toolbar-title {
            font-weight: 500;
            font-size: 1.1rem;
        }
        
        .calendar-container .fc-button {
            box-shadow: none !important;
            padding: 0.5rem 0.75rem;
            border-radius: 6px !important;
            font-weight: 500;
            transition: all 0.2s ease;
            text-transform: capitalize;
            border: 1px solid #e5e7eb !important;
        }
        
        .calendar-container .fc-button-primary {
            background-color: white !important;
            color: #4b5563 !important;
        }
        
        .calendar-container .fc-button-primary:hover {
            background-color: #f9fafb !important;
            color: #111827 !important;
        }
        
        .calendar-container .fc-button-primary:not(:disabled).fc-button-active, 
        .calendar-container .fc-button-primary:not(:disabled):active {
            background-color: #f0f7ff !important;
            color: #0281d4 !important;
        }
        
        .calendar-container .fc-daygrid-day-number {
            padding: 8px;
            font-size: 0.875rem;
            color: #374151;
        }
        
        .calendar-container .fc-daygrid-day.fc-day-today {
            background-color: #f0f7ff !important;
        }
        
        .calendar-container .fc-event {
            border: none !important;
            padding: 2px 4px;
            font-size: 0.75rem !important;
            margin-top: 1px;
            transition: transform 0.2s ease;
        }
        
        .calendar-container .fc-event:hover {
            transform: translateY(-1px);
        }
        
        .calendar-container .fc-toolbar.fc-header-toolbar {
            margin-bottom: 1.25em;
            flex-wrap: wrap;
        }
          .calendar-container .fc-view-harness {
            border-radius: 8px;
            overflow: hidden;
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
            }
        }
        
        /* Chatbot Button Styles */
        .chatbot-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0281d4, #0c9ced);
            box-shadow: 0 4px 15px rgba(2, 129, 212, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            outline: none;
        }
        
        .chatbot-button:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 6px 20px rgba(2, 129, 212, 0.35);
        }
        
        .chatbot-button i {
            font-size: 24px;
            color: white;
            transition: transform 0.3s ease;
        }
        
        .chatbot-button:hover i {
            transform: rotate(10deg);
        }
        
        .pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: rgba(2, 129, 212, 0.7);
            opacity: 0;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(0.95);
                opacity: 0.7;
            }
            70% {
                transform: scale(1.1);
                opacity: 0;
            }
            100% {
                transform: scale(0.95);
                opacity: 0;
            }
        }
        
        .chatbot-container {
            position: fixed;
            bottom: 5.5rem;
            right: 2rem;
            width: 350px;
            max-height: 500px;
            border-radius: 16px;
            background: white;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
            z-index: 999;
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px) scale(0.95);
            pointer-events: none;
            transition: all 0.3s ease;
        }
        
        .chatbot-container.active {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: all;
        }
        
        .chatbot-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, #0281d4, #0c9ced);
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .chatbot-header h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .chatbot-close {
            background: transparent;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .chatbot-close:hover {
            transform: rotate(90deg);
        }
        
        .chatbot-body {
            height: 340px;
            overflow-y: auto;
            padding: 20px;
        }
        
        .chatbot-footer {
            padding: 12px 15px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
        }
        
        .chatbot-input {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 10px 15px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s ease;
        }
        
        .chatbot-input:focus {
            border-color: #0c9ced;
            box-shadow: 0 0 0 2px rgba(12, 156, 237, 0.1);
        }
        
        .send-button {
            background: #0c9ced;
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-left: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .send-button:hover {
            background: #0281d4;
        }
        
        .chat-message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .user-message {
            justify-content: flex-end;
        }
        
        .bot-message {
            justify-content: flex-start;
        }
        
        .message-content {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
            position: relative;
        }
        
        .user-message .message-content {
            background-color: #0c9ced;
            color: white;
            border-bottom-right-radius: 4px;
            margin-right: 10px;
        }
        
        .bot-message .message-content {
            background-color: #f0f7ff;
            color: #333;
            border-bottom-left-radius: 4px;
            margin-left: 10px;
        }
        
        .bot-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e0effe;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .bot-avatar i {
            color: #0281d4;
            font-size: 16px;
        }
        
        .message-time {
            font-size: 10px;
            color: #888;
            margin-top: 4px;
            text-align: right;
        }
        
        /* Mobile responsiveness for chatbot */
        @media (max-width: 640px) {
            .chatbot-container {
                width: calc(100% - 32px);
                right: 16px;
                left: 16px;
                bottom: 5rem;
            }
            
            .chatbot-button {
                bottom: 1.5rem;
                right: 1.5rem;
            }
        }
    </style>
</head>
<body class="font-sans text-gray-700 relative overflow-x-hidden">
    <div class="orb one"></div>
    <div class="orb two"></div>

    <?php include_once('../includes/external_nav.php'); ?>

    <!-- HERO / INTRO -->
    <div class="max-w-7xl mx-auto px-5 pt-10 relative">
        <div class="glass rounded-3xl p-8 md:p-12 overflow-hidden fade-in">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-20 -right-10 w-80 h-80 bg-gradient-to-br from-primary-200/70 to-primary-400/40 rounded-full blur-3xl opacity-60"></div>
                <div class="absolute -bottom-24 -left-10 w-72 h-72 bg-gradient-to-tr from-primary-100/60 via-white/40 to-primary-300/40 rounded-full blur-3xl"></div>
            </div>
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                    <div>
                        <p class="section-label mb-2">External Complainant</p>
                        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-sky-900">Welcome<span class="font-light">,</span></h1>
                        <p class="mt-2 text-sky-800 text-lg font-medium"><?= htmlspecialchars($full_name) ?></p>
                        <p class="mt-3 max-w-xl text-sm md:text-base text-sky-700/80">Track your filed complaints, monitor hearings and stay updated on case progress in real time.</p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4 w-full md:w-auto">
                        <a href="submit_complaints.php" class="quick-btn glass group rounded-xl px-4 py-3 flex flex-col items-start gap-1 hover:shadow-lg transition">
                            <div class="flex items-center gap-2 text-sky-700"><i class="fa-solid fa-square-plus text-sky-600"></i><span class="text-xs font-semibold tracking-wide uppercase">New</span></div>
                            <span class="text-[13px] font-medium text-sky-900">Complaint</span>
                        </a>
                        <a href="view_complaints.php" class="quick-btn glass group rounded-xl px-4 py-3 flex flex-col items-start gap-1">
                            <div class="flex items-center gap-2 text-emerald-700"><i class="fa-solid fa-clipboard-list text-emerald-600"></i><span class="text-xs font-semibold tracking-wide uppercase">View</span></div>
                            <span class="text-[13px] font-medium text-emerald-900">Complaints</span>
                        </a>
                        <a href="view_cases.php" class="quick-btn glass group rounded-xl px-4 py-3 flex flex-col items-start gap-1">
                            <div class="flex items-center gap-2 text-indigo-700"><i class="fa-solid fa-gavel text-indigo-600"></i><span class="text-xs font-semibold tracking-wide uppercase">View</span></div>
                            <span class="text-[13px] font-medium text-indigo-900">Cases</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN GRID: Two-column (left stats, right upcoming hearings) -->
    <div class="max-w-7xl mx-auto px-5 mt-10 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Statistics (Left) -->
            <div class="lg:col-span-5 space-y-8 w-full">
                <div class="glass rounded-2xl p-6 md:p-7 fade-in card-body-grow">
                    <div class="flex items-center gap-2 mb-5">
                        <i class="fa-solid fa-chart-simple text-sky-600"></i>
                        <h2 class="text-sky-900 font-semibold tracking-tight">Statistics</h2>
                    </div>
                    <div class="space-y-6">
                        <!-- Complaints (Pending vs Total) -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-sky-800 tracking-wide">Total Complaints</span>
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-sky-100 text-sky-700"><?= $complaintsCount ?></span>
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <div class="flex justify-between text-[10px] font-medium text-amber-700 mb-0.5"><span>Pending</span><span><?= $pendingComplaints ?></span></div>
                                    <div class="w-full h-2.5 rounded-full bg-amber-50 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 progress-bar" style="width: <?= $pendingComplaintsPercent ?>%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] font-medium text-emerald-700 mb-0.5"><span>Resolved Cases</span><span><?= $resolvedCases ?></span></div>
                                    <div class="w-full h-2.5 rounded-full bg-emerald-50 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 progress-bar" style="width: <?= $caseResolvedPercent ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Cases Summary Row -->
                        <div>
                            <div class="flex justify-between mb-1.5 text-xs font-medium text-indigo-800"><span>Case Phases</span><span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700"><?= $casesCount ?></span></div>
                            <p class="text-[10px] mt-1 text-indigo-800/70">Mediation: <?= $mediationCases ?> • Resolution: <?= $resolutionCases ?> • Settlement: <?= $settlementCases ?> • Open: <?= $openCases ?></p>
                        </div>
                        <!-- Hearings -->
                        <div>
                            <div class="flex justify-between mb-1.5 text-xs font-medium text-rose-800"><span>Scheduled Hearings</span><span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-700"><?= $scheduledHearings ?></span></div>
                            <div class="w-full bg-white/60 rounded-full progress-wrap overflow-hidden h-2.5">
                                <div class="progress-bar bg-gradient-to-r from-rose-400 to-rose-500 h-full" style="width: <?= $hearingPercent ?>%"></div>
                            </div>
                            <p class="text-[10px] mt-1 text-rose-800/70">Relative to total cases</p>
                        </div>
                        <!-- Chips -->
                         
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            
                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600"><i class="fa-solid fa-folder-open"></i></div>
                                <div><p class="text-[10px] tracking-wide uppercase font-semibold text-blue-700">Open Cases</p><p class="text-lg leading-snug font-semibold text-blue-800"><?= $openCases ?></p></div>
                            </div>

                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600"><i class="fa-solid fa-calendar-alt"></i></div>
                                <div><p class="text-[10px] tracking-wide uppercase font-semibold text-pruple-700">Hearings</p><p class="text-lg leading-snug font-semibold text-purple-800"><?= $scheduledHearings ?></p></div>
                            </div>

                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600"><i class="fa-solid fa-hourglass-half"></i></div>
                                <div><p class="text-[10px] tracking-wide uppercase font-semibold text-amber-700">Pending Complaints</p><p class="text-lg leading-snug font-semibold text-amber-800"><?= $pendingComplaints ?></p></div>
                            </div>
                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-yellow-100 flex items-center justify-center text-yellow-600"><i class="fa-solid fa-handshake"></i></div>
                                <div><p class="text-[10px] tracking-wide uppercase font-semibold text-yellow-700">Mediation</p><p class="text-lg leading-snug font-semibold text-yellow-800"><?= $mediationCases ?></p></div>
                            </div>
                             
                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600"><i class="fa-solid fa-balance-scale"></i></div>
                                <div><p class="text-[10px] tracking-wide uppercase font-semibold text-emerald-700">Resolution</p><p class="text-lg leading-snug font-semibold text-emerald-800"><?= $resolutionCases ?></p></div>
                            </div>

                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-pink-100 flex items-center justify-center text-pink-600"><i class="fa-solid fa-file"></i></div>
                                <div><p class="text-[10px] tracking-wide uppercase font-semibold text-pink-700">Settlement</p><p class="text-lg leading-snug font-semibold text-pink-800"><?= $settlementCases ?></p></div>
                            </div>
                            <div class="glass rounded-xl p-4 flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600"><i class="fa-solid fa-circle-check"></i></div>
                                <div><p class="text-[10px] tracking-wide uppercase font-semibold text-emerald-700">Resolved</p><p class="text-lg leading-snug font-semibold text-emerald-800"><?= $resolvedCases ?></p></div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <!-- Upcoming Hearings (Right) -->
            <div class="lg:col-span-7 glass rounded-2xl p-6 md:p-7 fade-in card-body-grow">
                <div class="flex items-center gap-2 mb-5">
                    <i class="fa-solid fa-calendar-days text-sky-600"></i>
                    <h2 class="text-sky-900 font-semibold tracking-tight">Upcoming Hearings</h2>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 inline-block bg-purple-100 border-l-4 border-purple-600"></span>
                        <span><i class="fas fa-gavel text-purple-600"></i> Scheduled Hearings</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 inline-block bg-amber-100 border-l-4 border-amber-500"></span>
                        <span><i class="fas fa-handshake text-amber-500"></i> Mediation Phase</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 inline-block bg-emerald-100 border-l-4 border-emerald-600"></span>
                        <span><i class="fas fa-balance-scale text-emerald-600"></i> Resolution Phase</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 inline-block bg-pink-100 border-l-4 border-pink-600"></span>
                        <span><i class="fas fa-file-signature text-pink-600"></i> Settlement Phase</span>
                    </div>
                </div>
                <iframe src="../SecMenu/schedule/CalendarExternal.php" class="w-full rounded-xl border border-white/40 h-[640px] bg-white/50"></iframe>
            </div>
        </div>
    </div>
      <script>        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                },
                buttonText: {
                    today: 'Today'
                },
                dayHeaderFormat: { weekday: 'short' },
                eventTimeFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short'
                },
                eventOrder: 'start',
                eventDisplay: 'block',
                displayEventTime: true,
                events: [
                    { 
                        title: 'Noise Complaint', 
                        start: '2025-05-20T10:00:00',
                        backgroundColor: 'rgba(79, 70, 229, 0.8)',
                        borderColor: 'rgba(79, 70, 229, 0)',
                        textColor: '#ffffff',
                        extendedProps: {
                            type: 'hearing'
                        }
                    },
                    { 
                        title: 'Property Dispute', 
                        start: '2025-05-22T14:00:00',
                        backgroundColor: 'rgba(79, 70, 229, 0.8)',
                        borderColor: 'rgba(79, 70, 229, 0)',
                        textColor: '#ffffff',
                        extendedProps: {
                            type: 'hearing'
                        }
                    },
                    { 
                        title: 'Mediation Session',
                        start: '2025-05-18T09:00:00',
                        backgroundColor: 'rgba(3, 105, 161, 0.8)',
                        borderColor: 'rgba(3, 105, 161, 0)',
                        textColor: '#ffffff',
                        extendedProps: {
                            type: 'mediation'
                        }
                    }
                ],
                eventClassNames: function(arg) {
                    return ['shadow-sm'];
                },
                eventDidMount: function(info) {
                    // Add tooltip with improved formatting
                    const eventType = info.event.extendedProps.type === 'hearing' ? 'Hearing' : 'Mediation';
                    info.el.setAttribute('title', 
                        eventType + ': ' + info.event.title + '\n' + 
                        'Time: ' + info.event.start.toLocaleTimeString('en-US', {hour: 'numeric', minute:'2-digit', hour12: true})
                    );
                    
                    // Add subtle hover effect
                    info.el.addEventListener('mouseover', function() {
                        this.style.transform = 'translateY(-2px)';
                        this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
                    });
                    
                    info.el.addEventListener('mouseout', function() {
                        this.style.transform = '';
                        this.style.boxShadow = '';
                    });
                }
            });
            calendar.render();
            
            // Show animations on load
            animateStatistics();
        });

     
    document.getElementById('complaints-count').textContent = <?= $complaintsCount ?>;
    document.getElementById('cases-count').textContent = <?= $casesCount ?>;
    document.getElementById('hearings-count').textContent = <?= $pendingComplaints ?>;
    document.getElementById('resolved-count').textContent = <?= $resolvedCases ?>;
    document.getElementById('pending-count').textContent = <?= $pendingCases ?>;
    document.getElementById('mediated-count').textContent = <?= $scheduledHearings ?>;

    // Optional: Calculate % for progress bars
    const totalComplaints = <?= $complaintsCount ?> || 1; // Avoid divide-by-zero
    const totalCases = <?= $casesCount ?> || 1;

    const pendingPercent = Math.min(100, (<?= $pendingComplaints ?> / totalComplaints) * 100);
    const casesPercent = Math.min(100, (<?= $casesCount ?> / totalComplaints) * 100);

    document.getElementById('complaints-progress').style.width = pendingPercent + '%';
    document.getElementById('cases-progress').style.width = casesPercent + '%';
    document.getElementById('hearings-progress').style.width = (<?= $pendingCases ?> / totalCases) * 100 + '%';



        // Initialize animation on page load
        window.onload = animateStatistics;
        
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (menuButton && mobileMenu) {
                menuButton.addEventListener('click', function() {
                    this.classList.toggle('active');
                    if (mobileMenu.style.transform === 'translateY(0%)') {
                        mobileMenu.style.transform = 'translateY(-100%)';
                    } else {
                        mobileMenu.style.transform = 'translateY(0%)';
                    }
                });            }
        });
    </script>
    <?php include('../chatbot/bpamis_case_assistant.php'); ?>
</body>
</html>
