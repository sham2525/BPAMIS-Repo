<?php
session_start();
require_once('db-connect.php');
include('../../server/server.php'); 



// Determine External_Complainant_ID from session (prefer specific external_id, fallback to user_id)
$External_Complainant_ID = null;
if (isset($_SESSION['external_id']) && is_numeric($_SESSION['external_id'])) {
  $External_Complainant_ID = (int) $_SESSION['external_id'];
} elseif (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
  $External_Complainant_ID = (int) $_SESSION['user_id'];
}
if (!$External_Complainant_ID) {
  header('Location: ../../bpamis_website/login.php');
  exit;
}

// Verify that the external complainant exists (optional, but good for security)
$val = $conn->prepare("SELECT 1 FROM barangay_case_management.external_complainant WHERE External_Complaint_ID = ?");
$val->bind_param("i", $External_Complainant_ID);
$val->execute();
$val_result = $val->get_result();
if ($val_result->num_rows == 0) {
  echo "<script>alert('External complainant not found.'); window.location.href='../../bpamis_website/login.php';</script>";
  exit;
}
$val->close();

$events = [];
// Get hearings linked to cases where complaint_info.External_Complainant_ID = ?
$hearing_stmt = $conn->prepare("
    SELECT sl.hearingID, sl.hearingTitle, sl.hearingDateTime, sl.remarks, ci.Case_ID
    FROM barangay_case_management.schedule_list sl
    JOIN barangay_case_management.case_info ci ON sl.Case_ID = ci.Case_ID
    JOIN barangay_case_management.complaint_info co ON ci.Complaint_ID = co.Complaint_ID
    WHERE co.External_Complainant_ID = ?
");
$hearing_stmt->bind_param("i", $External_Complainant_ID);
$hearing_stmt->execute();
$hearing_res = $hearing_stmt->get_result();
while ($r = $hearing_res->fetch_assoc()) {
    $sdate = date("F d, Y h:i A", strtotime($r['hearingDateTime']));
    $events[] = [
        'id' => 'hearing_'.$r['hearingID'],
        'title' => $r['hearingTitle'],
        'start' => $r['hearingDateTime'],
        'type' => 'hearing',
        'description' => $r['remarks'],
        'sdate' => $sdate,
        'color' => 'rgba(124, 58, 237, 0.7)' // purple for hearing
    ];
}
$hearing_stmt->close();

$conn->close();

$events_json = json_encode($events);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Calendar — Cases & Hearings</title>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<!-- Icons for toolbar buttons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    /* Modern glassmorphism calendar container */
    #calendar {
      background: rgba(248, 250, 252, 0.85);
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
      border-radius: 22px;
      border: 1.5px solid #e0e7ef;
      padding: 2rem 1.5rem 1.5rem 1.5rem;
      transition: box-shadow 0.3s, transform 0.3s;
      backdrop-filter: blur(8px);
    }
    #calendar:hover {
      box-shadow: 0 16px 48px 0 rgba(31, 38, 135, 0.18);
      transform: translateY(-4px) scale(1.01);
    }
    /* Compact toolbar title (match resident calendar) */
    .fc .fc-toolbar-title {
      font-weight: 700;
      font-size: 1.05rem;
      color: #0281d4;
      letter-spacing: .4px;
      display: flex;
      align-items: center;
      gap: .4em;
    }
      .fc .fc-button { 
        box-shadow: none !important; 
        padding: .30rem .65rem; 
        border-radius: 7px !important; 
        font-weight: 600; 
        font-size: .78rem; 
        line-height: 1; 
        transition: all .18s; 
        text-transform: capitalize; 
        border: 1px solid #b6e1f7 !important; 
        background: linear-gradient(120deg,#e8f7ff 0%,#f3fbff 100%) !important; 
        color: #0276c4 !important; 
        margin: 0 .15rem; 
        display: flex; 
        align-items: center; 
        gap: .25em; 
        min-height: 30px; 
      }
      .fc .fc-prev-button,.fc .fc-next-button{ padding:.25rem .5rem !important; width:30px; justify-content:center; }
      .fc .fc-button-primary:hover { background:linear-gradient(120deg,#d5eefc 0%,#e8f7ff 100%) !important; color:#0369a1 !important; transform:translateY(-2px); }
      .fc .fc-button-primary:not(:disabled).fc-button-active,.fc .fc-button-primary:not(:disabled):active { background:#e4f6ff !important; color:#0b6fa2 !important; box-shadow:inset 0 0 0 1px #9ed3ed; }
    .fc .fc-daygrid-day-number {
      padding: 12px;
      font-size: 1rem;
      color: #0281d4;
      font-weight: 600;
    }
    .fc .fc-daygrid-day.fc-day-today {
      background: linear-gradient(90deg,rgba(199, 229, 248, 0.85) 0%, #f0f9ff 100%) !important;
      border-radius: 12px;
      box-shadow: 0 2px 8px 0 rgba(2, 129, 212, 0.08);
    }
    /* Highlight days that have hearings (distinct from current day; no border) */
    .fc .fc-daygrid-day.has-hearing:not(.fc-day-today) {
      background: linear-gradient(90deg, rgba(216, 180, 254, 0.35) 0%, rgba(243, 232, 255, 0.65) 100%) !important;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(124, 58, 237, 0.12);
    }
    .fc .fc-daygrid-day.has-hearing:not(.fc-day-today) .fc-daygrid-day-number { color: #7c3aed; }
    .fc .fc-event {
      border: none !important;
      padding: 10px 18px 10px 16px;
      font-size: 1.08rem !important;
      font-weight: 700;
      margin-top: 6px;
      border-radius: 14px !important;
      background: rgba(255, 255, 255, 0.95) !important;
      color: #1e293b !important;
      box-shadow: 0 4px 16px 0 rgba(2, 129, 212, 0.13);
      display: flex;
      align-items: center;
      gap: 0.7em;
      position: relative;
      transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
      backdrop-filter: blur(2px);
      overflow: hidden;
      animation: eventFadeIn 0.5s;
      line-height: 1.3;
      white-space: nowrap;
      text-overflow: ellipsis;
      border: 1px solid rgba(2, 129, 212, 0.2) !important;
    }
    .fc .fc-event:hover, .fc .fc-event:focus {
      transform: scale(1.06) translateY(-3px);
      box-shadow: 0 10px 28px 0 rgba(2, 129, 212, 0.22);
      z-index: 2;
      background: rgba(255, 255, 255, 1) !important;
      color: #0f172a !important;
      border-color: rgba(2, 129, 212, 0.4) !important;
    }
    @keyframes eventFadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    /* Modal glassmorphism style */
    #eventModal .relative {
      background: rgba(255,255,255,0.95);
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.13);
      border-radius: 22px;
      border: 1.5px solid #e0e7ef;
      backdrop-filter: blur(8px);
      position: relative;
      overflow: hidden;
    }
    
    #eventModal .relative::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #0281d4, #0ea5e9, #0281d4);
      background-size: 200% 100%;
      animation: gradientShift 3s ease-in-out infinite;
    }
    
    @keyframes gradientShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }
    
    #modalTitle {
      color: #0281d4;
      font-weight: 700;
      font-size: 1.3rem;
      margin-top: 0.5rem;
    }
    
    #modalContent {
      max-height: 70vh;
      overflow-y: auto;
      padding-right: 0.5rem;
    }
    
    #modalContent::-webkit-scrollbar {
      width: 6px;
    }
    
    #modalContent::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 3px;
    }
    
    #modalContent::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 3px;
    }
    
    #modalContent::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }
    /* Responsive tweaks */
    @media (max-width: 640px) {
      #calendar {
        padding: 0.5rem 0.1rem 0.5rem 0.1rem;
        min-width: 0;
        width: 100%;
        box-sizing: border-box;
      }
      .fc .fc-toolbar-title {
        font-size: 1.05rem;
        text-align: center;
        word-break: break-word;
      }
      .fc .fc-daygrid-day-number {
        font-size: 0.92rem;
        padding: 6px;
      }
      .fc .fc-event {
        font-size: 0.95rem !important;
        padding: 8px 4px 8px 8px;
        flex-direction: row;
        align-items: center;
        min-width: 0;
        max-width: 100%;
        white-space: nowrap;
        text-overflow: ellipsis;
        background: rgba(255, 255, 255, 0.98) !important;
      }
      .fc .fc-event-title {
        color: #1e293b !important;
        font-size: 0.95rem;
        font-weight: 700 !important;
        text-shadow: 0 1px 2px rgba(255, 255, 255, 0.9) !important;
      }
      .fc .fc-event-time {
        color: #475569 !important;
        font-weight: 600 !important;
        text-shadow: 0 1px 2px rgba(255, 255, 255, 0.9) !important;
      }
    }
    /* Ensure event time and title are black in week and list views */
    .fc-timeGridWeek-view .fc-event-time, 
    .fc-timeGridWeek-view .fc-event-title,
    .fc-listWeek-view .fc-event-time, 
    .fc-listWeek-view .fc-event-title {
      color: #111 !important;
    }
    .fc-list .fc-event-time, .fc-list .fc-event-title {
      color: #111 !important;
    }
    
    /* List view styling enhancements */
    .fc .fc-list {
      border: none;
    }
    .fc .fc-list-table td,
    .fc .fc-list-table th {
      border: none;
    }
    /* Day header chip */
    .fc .fc-list-day-cushion {
      margin: 10px 12px;
      padding: 10px 14px;
      background: linear-gradient(90deg, rgba(199,229,248,0.35) 0%, #f0f9ff 100%);
      border: 1px solid #dbeafe;
      border-radius: 12px;
      color: #0281d4;
      font-weight: 800;
      letter-spacing: .2px;
    }
    /* Event rows */
    .fc .fc-list-event td {
      padding: 12px 14px;
      background: #ffffff;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      box-shadow: 0 4px 12px rgba(2,129,212,0.08);
    }
    .fc .fc-list-event:hover td {
      background: #fff;
      box-shadow: 0 8px 18px rgba(2,129,212,0.14);
      transform: translateY(-1px);
    }
    /* Tighten table spacing */
    .fc .fc-list-table tbody tr + tr td { border-top: none; }
    .fc .fc-list-table > tbody > tr.fc-list-event > td { border-top: none; }
    /* Time badge */
    .fc .fc-list-event-time {
      background: #eef2ff;
      color: #3730a3 !important;
      padding: 3px 10px;
      border-radius: 9999px;
      font-weight: 800;
      font-size: .78rem;
      box-shadow: inset 0 0 0 1px #c7d2fe;
    }
    /* Dot color */
    .fc .fc-list-event-dot {
      border-color: #7c3aed; /* indigo/purple */
      background: #7c3aed;
      width: 9px;
      height: 9px;
    }
    /* Title emphasis */
    .fc .fc-list-event-title a {
      font-weight: 800;
      color: #0f172a !important;
    }
    /* Zebra subtle */
    .fc .fc-list-table tbody tr.fc-list-event:nth-child(odd) td {
      background: #fbfdff;
    }
    /* Empty state for list view */
    .fc .fc-list-empty td { border: none; padding: 0; }
    .fc .fc-list-empty .fc-list-empty-cushion {
      margin: 12px;
      padding: 20px 18px;
      border-radius: 16px;
      border: 1px solid #e5e7eb;
      background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
      color: #64748b;
      text-align: center;
      font-weight: 700;
      letter-spacing: .2px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 6px 18px rgba(2,129,212,0.08);
    }
    .fc .fc-list-empty .fc-list-empty-cushion::before {
      content: '\1F5D3'; /* calendar emoji 🗓 (fallback) */
      font-size: 1.2rem;
      color: #0281d4;
      opacity: .7;
      display: inline-block;
      transform: translateY(1px);
    }
    
    /* Enhanced event text readability */
    .fc-event-title {
      color: #1e293b !important;
      font-weight: 700 !important;
      text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8) !important;
    }
    
    .fc-event-time {
      color: #475569 !important;
      font-weight: 600 !important;
      text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8) !important;
    }
    
    /* Ensure event text is readable in all views */
    .fc-daygrid-event .fc-event-title,
    .fc-timegrid-event .fc-event-title,
    .fc-list-event .fc-event-title {
      color: #1e293b !important;
      font-weight: 700 !important;
      text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8) !important;
    }
    /* Icon-only hearing event styling (Month/Week views) */
    .fc .evt-hearing { 
      padding: 4px 6px !important; 
      min-height: 0 !important; 
      display: inline-flex; 
      align-items: center; 
      justify-content: center; 
    }
    .fc .evt-hearing .hearing-icon { 
      display: inline-flex; 
      align-items: center; 
      justify-content: center; 
      width: 22px; 
      height: 22px; 
      border-radius: 9999px; 
      background: #eef2ff; 
      color: #7c3aed; 
      border: 1px solid #c7d2fe; 
      box-shadow: inset 0 0 0 1px rgba(124,58,237,.08);
      font-size: 12px;
    }
    .fc .evt-hearing .fc-event-time, 
    .fc .evt-hearing .fc-event-title { display: none !important; }
    /* Highlight current day in week list view */
    .fc-listWeek-view .fc-list-day.fc-day-today {
      background: linear-gradient(90deg,rgba(199, 229, 248, 0.85) 0%, #f0f9ff 100%) !important;
    }
    /* In Week view, subtly highlight headers for days with hearings (distinct from today) */
    .fc-timeGridWeek-view .fc-col-header-cell.has-hearing-header:not(.fc-day-today) {
      background: linear-gradient(90deg, rgba(216, 180, 254, 0.35) 0%, rgba(243, 232, 255, 0.65) 100%) !important;
      border-radius: 10px;
      box-shadow: 0 1px 4px rgba(124, 58, 237, 0.12);
    }
    /* Highlight current day column in timeGrid views */
    .fc-timeGridWeek-view .fc-col-today,
    .fc-timeGridDay-view .fc-col-today,
    .fc-timeGridWeek-view .fc-timegrid-col.fc-day-today,
    .fc-timeGridDay-view .fc-timegrid-col.fc-day-today,
    .fc-timeGridWeek-view .fc-timegrid-slot-label.fc-day-today,
    .fc-timeGridDay-view .fc-timegrid-slot-label.fc-day-today {
      background: linear-gradient(90deg,rgba(199, 229, 248, 0.85) 0%, #f0f9ff 100%) !important;
    }
    
    /* Status indicator styling */
    .status-indicator {
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid #e0e7ef;
      border-radius: 12px;
      backdrop-filter: blur(4px);
      transition: all 0.3s ease;
    }
    .status-indicator:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    /* Case selector removed in external calendar */
    
    /* Phase indicator styling */
    .phase-indicator {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 0.75rem;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.875rem;
      transition: all 0.3s ease;
    }
    
    .phase-mediation {
      background-color: rgba(250, 204, 21, 0.15);
      color: #ca8a04;
      border: 1px solid rgba(250, 204, 21, 0.3);
    }
    
    .phase-resolution {
      background-color: rgba(34, 197, 94, 0.15);
      color: #16a34a;
      border: 1px solid rgba(34, 197, 94, 0.3);
    }
    
    .phase-settlement {
      background-color: rgba(168, 85, 247, 0.15);
      color: #9333ea;
      border: 1px solid rgba(168, 85, 247, 0.3);
    }
    
    /* Modal close button styling */
    #modalClose {
      background: rgba(255, 255, 255, 1);
      border: 2px solid #e5e7eb;
      border-radius: 50%;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      backdrop-filter: blur(8px);
      cursor: pointer;
      z-index: 70;
      position: absolute;
      top: 16px;
      right: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      font-size: 1.125rem;
      font-weight: bold;
      color: #6b7280;
      user-select: none;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
    }
    #modalClose:hover {
      background: rgba(239, 68, 68, 0.1);
      border-color: #ef4444;
      color: #dc2626;
      transform: scale(1.1);
      box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
    }
    
    /* Toolbar Layout: single row grid (left nav, center title, right view buttons) */
    .fc .fc-toolbar.fc-header-toolbar { display:grid; grid-template-columns:1fr auto 1fr; align-items:center; column-gap:.75rem; width:100%; }
    .fc .fc-toolbar { flex-wrap:nowrap; }
    .fc .fc-toolbar-chunk:nth-child(1){ justify-self:start; display:flex; align-items:center; }
    .fc .fc-toolbar-chunk:nth-child(2){ justify-self:center; text-align:center; }
    .fc .fc-toolbar-chunk:nth-child(3){ justify-self:end; display:flex; justify-content:flex-end; align-items:center; }
    .fc .fc-prev-button,.fc .fc-next-button{ position:relative; }
    .fc .fc-toolbar-chunk:nth-child(3) .fc-button-group{ flex-wrap:nowrap; }
    @media (max-width:480px){
      .fc .fc-toolbar.fc-header-toolbar { grid-template-columns:1fr 1fr; grid-auto-rows:auto; row-gap:.4rem; }
      .fc .fc-toolbar-chunk:nth-child(1){ order:1; }
      .fc .fc-toolbar-chunk:nth-child(2){ order:2; grid-column:1 / span 2; }
      .fc .fc-toolbar-chunk:nth-child(3){ order:3; }
    }
    
    /* Remove calendar grid lines (match resident) */
    .fc-theme-standard .fc-scrollgrid,
    .fc-theme-standard .fc-scrollgrid thead tr,
    .fc-theme-standard .fc-scrollgrid tbody tr,
    .fc-theme-standard td,
    .fc-theme-standard th {
      border: 0 !important;
    }
    .fc .fc-scrollgrid { border: 0 !important; }
    .fc .fc-col-header, .fc .fc-col-header-cell { border: 0 !important; }
    .fc .fc-daygrid-day,
    .fc .fc-daygrid-day-frame,
    .fc .fc-daygrid-day-top,
    .fc .fc-daygrid-day-bg { border: 0 !important; }
    .fc .fc-timegrid-slot,
    .fc .fc-timegrid-axis,
    .fc .fc-timegrid-divider,
    .fc .fc-timegrid-slot-label { border: 0 !important; }
    /* Keep today highlight and event cards visible despite removed lines */
    .fc .fc-daygrid-day.fc-day-today { box-shadow: 0 2px 8px 0 rgba(2, 129, 212, 0.08); }
    
</style>
</head>
<body class="bg-white min-h-screen flex flex-col items-center justify-start py-10 px-4">
  <div class="container max-w-7xl mx-auto">

    <div id="calendar"></div>
    

    <!-- Modal -->
    <div id="eventModal" class="fixed inset-0 hidden flex items-center justify-center z-50">
      <div class="absolute inset-0 bg-black opacity-40 backdrop-blur-sm"></div>
      <div class="relative bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl w-full max-w-md mx-auto p-6 z-60 transform transition-all duration-300 border border-gray-200">
        <button id="modalClose" class="absolute top-4 right-4 text-gray-600 hover:text-gray-900 transition-colors duration-200 bg-white hover:bg-red-50 border border-gray-200 rounded-full w-8 h-8 flex items-center justify-center backdrop-blur-sm shadow-lg text-lg font-bold">&times;</button>
        <h3 id="modalTitle" class="text-xl font-semibold mb-4 text-blue-700 border-b border-gray-200 pb-2"></h3>
        <div id="modalContent" class="text-sm text-gray-700 space-y-3"></div>
      </div>
    </div>

  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const rawEvents = <?= $events_json ?>;
  
  // Build a set of dates (YYYY-MM-DD) with at least one hearing
  const hearingDateSet = new Set();
  (rawEvents || []).forEach(ev => {
    if (ev && ev.type === 'hearing' && ev.start) {
      const d = new Date(ev.start);
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      hearingDateSet.add(`${y}-${m}-${day}`);
    }
  });

  const fcEvents = rawEvents
    // In case any legacy case events slip in, filter to only hearings
    .filter(ev => ev.type === 'hearing')
    .map(ev => {
        const base = {
            id: ev.id,
            title: ev.title,
            start: ev.start,
            end: ev.end || null,
            color: ev.color || undefined,
            extendedProps: {}
        };

    base.extendedProps = {
      type: 'hearing',
      description: ev.description || '',
      sdate: ev.sdate || '',
    };
    if (ev.end) base.end = ev.end;
    base.display = 'block';
        return base;
  });

    const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    noEventsText: 'No hearings scheduled for this period',
    eventClassNames: function(arg){
      if (arg.event.extendedProps && arg.event.extendedProps.type === 'hearing') {
        if (!arg.view.type.startsWith('list')) return ['evt-hearing'];
      }
      return [];
    },
    eventContent: function(arg){
      const isList = arg.view.type.startsWith('list');
      if (arg.event.extendedProps && arg.event.extendedProps.type === 'hearing') {
        const title = escapeHtml(arg.event.title || 'Hearing');
        if (isList) {
          // Explicitly show hearing title in List view
          return { html: `<span class="font-semibold text-slate-900">${title}</span>` };
        }
        // Icon-only in Month/Week with hover tooltip
        return { html: `<span class="hearing-icon" title="${title}" aria-label="${title}"><i class="fas fa-gavel"></i></span>` };
      }
      return undefined;
    },
    eventDidMount: function(info) {
      if (info.event.extendedProps.type === 'hearing') {
        const timeEl = info.el.querySelector('.fc-event-time');
        const isMonth = info.view && info.view.type && info.view.type.startsWith('dayGrid');
        if (timeEl) timeEl.style.display = isMonth ? 'none' : '';
      }
    },
        initialView: 'dayGridMonth',
    dayCellClassNames: function(arg){
      const d = arg.date;
      const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
      if (hearingDateSet.has(key)) return ['has-hearing'];
      return [];
    },
    dayHeaderClassNames: function(arg){
      const d = arg.date; if (!d) return [];
      const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
      if (hearingDateSet.has(key)) return ['has-hearing-header'];
      return [];
    },
    headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
  views: { dayGridMonth:{ buttonText:'Month' }, timeGridWeek:{ buttonText:'Week' }, listMonth:{ buttonText:'List' } },
        events: fcEvents,
        height: 650,
    eventClick: function(info) {
            const e = info.event;
            const p = e.extendedProps;
            const modal = document.getElementById('eventModal');
            const title = document.getElementById('modalTitle');
            const content = document.getElementById('modalContent');

      title.textContent = e.title;
      content.innerHTML = `
        <p class="mb-2"><strong class="text-gray-700">Hearing Title:</strong> <span class="text-indigo-600">${escapeHtml(e.title)}</span></p>
        <p class="mb-2"><strong class="text-gray-700">Start:</strong> <span class="text-gray-800">${moment(e.start).format('MMMM D, YYYY h:mm A')}</span></p>
        <p class="mb-2"><strong class="text-gray-700">Remarks:</strong> <span class="text-gray-800">${escapeHtml(p.description)}</span></p>
        <div class="flex items-center mt-4">
          <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2"></div>
          <span class="text-xs text-gray-500">Tap anywhere outside to close</span>
        </div>
      `;
            // Add animation to modal
            modal.classList.remove('hidden');
            const modalContent = modal.querySelector('.relative');
            modalContent.style.transform = 'scale(0.9)';
            modalContent.style.opacity = '0';
            setTimeout(() => {
                modalContent.style.transform = 'scale(1)';
                modalContent.style.opacity = '1';
            }, 50);
        }
    });
    // Render calendar (no icon injection; show text labels)
    calendar.render();

  // Case selector and status mini removed in external calendar

    function getStatusClass(status) {
        if (!status) return 'bg-gray-100 text-gray-800';
        const s = status.toString().toLowerCase();
        if (s === 'resolved') return 'bg-green-100 text-green-800';
        if (s === 'closed') return 'bg-red-100 text-red-800';
        if (s === 'pending') return 'bg-yellow-100 text-yellow-800';
        if (s === 'mediation') return 'bg-yellow-100 text-orange-800';
        if (s === 'open') return 'bg-blue-100 text-blue-800';
        return 'bg-gray-100 text-gray-800';
    }

  // Modal close functionality
    const modalClose = document.getElementById('modalClose');
    const eventModal = document.getElementById('eventModal');
    
    if (modalClose) {
        modalClose.addEventListener('click', function() {
            eventModal.classList.add('hidden');
        });
    }
    
    // Close modal when clicking outside
    if (eventModal) {
        eventModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !eventModal.classList.contains('hidden')) {
            eventModal.classList.add('hidden');
        }
    });
    
    // Additional modal close button event listener
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'modalClose') {
            const modal = document.getElementById('eventModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }
    });

    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    // Removed icon injection and toolbar observer to keep textual labels

    // Responsive toolbar tweaks (match resident)
    function adjustCalendarToolbar(){
      const w = window.innerWidth;
      const titleEl = document.querySelector('.fc-toolbar-title');
      if (titleEl) {
        // Always get fresh title from the current FullCalendar view
        const full = (calendar && calendar.view && calendar.view.title)
          ? calendar.view.title
          : titleEl.textContent.trim();
        titleEl.dataset.full = full;
        if (w < 420) {
          titleEl.textContent = full.length > 20 ? full.slice(0, 17) + '…' : full;
        } else if (w < 560) {
          titleEl.textContent = full.length > 28 ? full.slice(0, 25) + '…' : full;
        } else {
          titleEl.textContent = full;
        }
      }
      const navBtns = document.querySelectorAll('.fc-prev-button, .fc-next-button');
      navBtns.forEach(btn => {
        if (w < 420) { btn.style.padding = '.20rem .45rem'; btn.style.fontSize = '.65rem'; }
        else if (w < 560) { btn.style.padding = '.24rem .5rem'; btn.style.fontSize = '.7rem'; }
        else if (w < 768) { btn.style.padding = '.26rem .55rem'; btn.style.fontSize = '.72rem'; }
        else { btn.style.padding = '.28rem .6rem'; btn.style.fontSize = '.75rem'; }
      });
    }
    adjustCalendarToolbar();
    window.addEventListener('resize', () => {
      if (window.__fcTbRaf) cancelAnimationFrame(window.__fcTbRaf);
      window.__fcTbRaf = requestAnimationFrame(adjustCalendarToolbar);
    });
    calendar.on('datesSet', adjustCalendarToolbar);

});
</script>
</body>
</html>
