<?php
/**
 * View Hearing Calendar Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Hearing Calendar</title>

    <!-- FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <!-- Font Awesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
        }
        #calendar {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            background: white;
        }
        .fc-event {
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-blue-50">

    <!-- Navigation -->
    <?php include '../includes/barangay_official_cap_nav.php'; ?>

    <!-- Calendar Container -->
    <div class="container mx-auto mt-10 p-5 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-semibold text-blue-800 mb-4">Hearing Calendar</h2>
        <div class="mb-4 flex justify-end">
            <a href="appoint_hearing.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow text-sm font-medium transition">
                <i class="fa fa-calendar-plus"></i> Schedule Hearing
            </a>
        </div>
        
        <iframe id="calendarFrame" src="../SecMenu/schedule/CalendarCaptain.php" style="width:100%; height:800px; border:none;"></iframe>
    </div>
    <?php include '../chatbot/bpamis_case_assistant.php'?>
    <?php include 'sidebar_.php';?>
</body>
</html>
