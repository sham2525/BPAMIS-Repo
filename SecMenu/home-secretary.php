<?php
/**
 * Home/Dashboard Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Dashboard</title>
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
    <style>        .gradient-bg {
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
        .stat-card {
            border-radius: 12px;
            overflow: hidden;
        }        /* Enhanced Sidebar Styles */
        .toggle-menu .fa-chevron-down {
            transition: transform 0.3s ease;
        }
        .toggle-menu .rotate-180 {
            transform: rotate(180deg);
        }
        .submenu {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.3s ease, opacity 0.2s ease;
        }
        .submenu.active {
            max-height: 500px;
            opacity: 1;
        }
        
        /* Modern Calendar Styles */
        .calendar-container {
            --fc-border-color: #f0f0f0;
            --fc-daygrid-event-dot-width: 6px;
            --fc-event-border-radius: 6px;
            --fc-small-font-size: 0.75rem;
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
<body class="bg-gray-50 font-sans">
    <?php include '../includes/barangay_official_nav.php'; ?>
    
    <!-- Welcome Banner -->
    <section class="container mx-auto mt-10 px-4">
        <div class="gradient-bg rounded-2xl shadow-sm p-8 md:p-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h2 class="text-3xl font-light text-primary-800">Welcome, <span class="font-medium">Barangay Official - Secretary</span></h2>
                <p class="mt-3 text-gray-600 max-w-md">Manage complaints, handle cases, and oversee the Barangay Panducot Adjudication System with these powerful tools.</p>
            </div>
        </div>
    </section>
    
    <!-- Quick Actions -->
    <div class="container mx-auto mt-8 px-4">
        <h3 class="text-lg font-medium text-gray-700 mb-4 px-2">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="add_complaints.php" class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-blue-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-plus-circle text-primary-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800 text-sm">Add Complaint</h4>
                    <p class="text-xs text-gray-500">Register new</p>
                </div>
            </a>
            <a href="view_cases.php" class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-yellow-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-gavel text-yellow-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800 text-sm">View Cases</h4>
                    <p class="text-xs text-gray-500">Monitor cases</p>
                </div>
            </a>
            <a href="minutes_meeting.php" class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-blue-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-file-alt text-primary-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800 text-sm">Meeting Logs</h4>
                    <p class="text-xs text-gray-500">Record minutes</p>
                </div>
            </a>
            <a href="appoint_hearing.php" class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-green-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-calendar-alt text-green-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800 text-sm">Schedule</h4>
                    <p class="text-xs text-gray-500">Set hearings</p>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Dashboard Content -->
    <div class="container mx-auto mt-10 px-4 pb-10">        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Statistics Section -->
            <div class="md:col-span-5 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-5 flex items-center">
                    <i class="fas fa-chart-bar text-primary-500 mr-2"></i>
                    Statistics
                </h2>
                
                <div class="space-y-6">
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Total Complaints</span>
                            <span id="complaints-count" class="text-sm font-medium text-primary-600 bg-primary-50 px-2 py-0.5 rounded">0</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div id="complaints-progress" class="progress-bar bg-primary-400 h-2 rounded-full"></div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Total Cases</span>
                            <span id="cases-count" class="text-sm font-medium text-primary-600 bg-primary-50 px-2 py-0.5 rounded">0</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div id="cases-progress" class="progress-bar bg-green-400 h-2 rounded-full"></div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Scheduled Hearings</span>
                            <span id="hearings-count" class="text-sm font-medium text-primary-600 bg-primary-50 px-2 py-0.5 rounded">0</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div id="hearings-progress" class="progress-bar bg-blue-400 h-2 rounded-full"></div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-green-50 rounded-lg p-4 flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Resolved Cases</p>
                                <p id="resolved-count" class="text-lg font-medium text-green-600">0</p>
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 rounded-lg p-4 flex items-center">
                            <i class="fas fa-hourglass-half text-yellow-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Pending Cases</p>
                                <p id="pending-count" class="text-lg font-medium text-yellow-600">0</p>
                            </div>
                        </div>
                          <div class="bg-purple-50 rounded-lg p-4 flex items-center">
                            <i class="fas fa-handshake text-purple-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Mediated Disputes</p>
                                <p id="mediated-count" class="text-lg font-medium text-purple-600">0</p>
                            </div>
                        </div>
                        
                        <div class="bg-red-50 rounded-lg p-4 flex items-center">
                            <i class="fas fa-ban text-red-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Rejected Cases</p>
                                <p id="rejected-count" class="text-lg font-medium text-red-600">0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Calendar Section -->
            <div class="md:col-span-7 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-5 flex items-center">
                    <i class="fas fa-calendar text-primary-500 mr-2"></i>
                    Upcoming Hearings
                </h2>
                <div id='calendar' class="calendar-container mt-2"></div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mt-6 mb-8">
            <div class="md:col-span-5 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-5 flex items-center">
                    <i class="fas fa-bell text-primary-500 mr-2"></i>
                    Recent Activity
                </h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-blue-100 p-2 rounded-full mr-3 mt-1">
                            <i class="fas fa-plus text-blue-500 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium">New complaint filed</p>
                            <p class="text-xs text-gray-500">by Juan Dela Cruz • 2 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-green-100 p-2 rounded-full mr-3 mt-1">
                            <i class="fas fa-check text-green-500 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Case resolved</p>
                            <p class="text-xs text-gray-500">Property Boundary Dispute • 5 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-purple-100 p-2 rounded-full mr-3 mt-1">
                            <i class="fas fa-calendar text-purple-500 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Hearing scheduled</p>
                            <p class="text-xs text-gray-500">Unpaid Debt Case • Yesterday</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-yellow-100 p-2 rounded-full mr-3 mt-1">
                            <i class="fas fa-file-alt text-yellow-500 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium">KP Form generated</p>
                            <p class="text-xs text-gray-500">Amicable Settlement • Yesterday</p>
                        </div>
                    </div>
                </div>
                <a href="#" class="block text-center text-primary-600 hover:text-primary-700 text-sm mt-4">View All Activity</a>
            </div>
            
            <!-- Chart Section -->
            <div class="md:col-span-7 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-5 flex items-center">
                    <i class="fas fa-chart-line text-primary-500 mr-2"></i>
                    Monthly Statistics
                </h2>
                <canvas id="statsChart" height="250"></canvas>
            </div>
        </div>
    </div>
      <div id="sidebar" class="fixed left-0 top-0 w-72 h-full bg-white shadow-lg p-5 transform -translate-x-full transition-transform duration-300 z-50 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <a href="home.php">
                    <img src="logo.png" alt="BPAMIS Logo" width="50" height="50" class="mr-3">
                </a>
                <h2 class="text-lg font-bold text-primary-700">BPAMIS</h2>
            </div>
            <button id="close-sidebar" class="text-gray-500 hover:text-primary-600 text-xl p-2 rounded-full hover:bg-gray-100 transition focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="border-b border-gray-200 mb-4 pb-4">
            <div class="flex items-center">
                <div class="bg-primary-100 rounded-full p-3">
                    <i class="fas fa-user-shield text-primary-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">Barangay Official - Secretary</p>
                    <p class="text-xs text-gray-500">Adjudication Panel</p>
                </div>
            </div>
        </div>
        
        <nav>
            <ul class="space-y-1">
                <li>
                    <a href="home.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-primary-50 hover:text-primary-700 rounded-lg transition group">
                        <i class="fas fa-home w-5 h-5 mr-3 text-gray-400 group-hover:text-primary-600"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="pt-2">
                    <p class="px-4 py-1 text-xs font-medium text-gray-400 uppercase tracking-wider">Case Management</p>
                </li>
                <li>
                    <button class="toggle-menu w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-primary-50 hover:text-primary-700 rounded-lg transition group">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle w-5 h-5 mr-3 text-gray-400 group-hover:text-primary-600"></i>
                            <span>Complaints</span>
                        </div>
                        <i class="fas fa-chevron-down text-sm text-gray-400"></i>
                    </button>                    <ul class="submenu hidden space-y-1 pl-12 mt-1">
                        <li><a href="add_complaints.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">Add Complaints</a></li>
                        <li><a href="view_complaints.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">View Complaints</a></li>
                    </ul>
                </li>
                <li>
                    <button class="toggle-menu w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-primary-50 hover:text-primary-700 rounded-lg transition group">
                        <div class="flex items-center">
                            <i class="fas fa-folder w-5 h-5 mr-3 text-gray-400 group-hover:text-primary-600"></i>
                            <span>Cases</span>
                        </div>
                        <i class="fas fa-chevron-down text-sm text-gray-400"></i>
                    </button>                    <ul class="submenu hidden space-y-1 pl-12 mt-1">
                        <li><a href="view_cases.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">View Cases</a></li>
                        <li><a href="case_status.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">Case Status</a></li>
                    </ul>
                </li>
                <li>
                    <button class="toggle-menu w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-primary-50 hover:text-primary-700 rounded-lg transition group">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt w-5 h-5 mr-3 text-gray-400 group-hover:text-primary-600"></i>
                            <span>Schedule</span>
                        </div>
                        <i class="fas fa-chevron-down text-sm text-gray-400"></i>
                    </button>                    <ul class="submenu hidden space-y-1 pl-12 mt-1">
                        <li><a href="appoint_hearing.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">Appoint Hearing</a></li>
                        <li><a href="reschedule_hearing.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">Reschedule Hearing</a></li>
                        <li><a href="view_hearing_calendar.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">View Calendar</a></li>
                    </ul>
                </li>
                <li class="pt-2">
                    <p class="px-4 py-1 text-xs font-medium text-gray-400 uppercase tracking-wider">Reports & Forms</p>
                </li>
                <li>
                    <button class="toggle-menu w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-primary-50 hover:text-primary-700 rounded-lg transition group">
                        <div class="flex items-center">
                            <i class="fas fa-chart-bar w-5 h-5 mr-3 text-gray-400 group-hover:text-primary-600"></i>
                            <span>Reports</span>
                        </div>
                        <i class="fas fa-chevron-down text-sm text-gray-400"></i>
                    </button>                    <ul class="submenu hidden space-y-1 pl-12 mt-1">
                        <li><a href="view_complaints_report.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">Complaints Report</a></li>
                        <li><a href="view_case_reports.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">Case Reports</a></li>
                    </ul>
                </li>
                <li>
                    <button class="toggle-menu w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-primary-50 hover:text-primary-700 rounded-lg transition group">
                        <div class="flex items-center">
                            <i class="fas fa-file w-5 h-5 mr-3 text-gray-400 group-hover:text-primary-600"></i>
                            <span>KP Forms</span>
                        </div>
                        <i class="fas fa-chevron-down text-sm text-gray-400"></i>
                    </button>                    <ul class="submenu hidden space-y-1 pl-12 mt-1">
                        <li><a href="view_kp_forms.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">View Templates</a></li>
                        <li><a href="print_kp_forms.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition">Print Form</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        
        <div class="absolute bottom-0 left-0 right-0 p-5 border-t border-gray-200">
            <a href="../login.php" class="flex items-center text-gray-600 hover:text-primary-700">
                <i class="fas fa-sign-out-alt mr-2"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure submenu classes are correctly initialized
            document.querySelectorAll('.submenu').forEach(submenu => {
                if (submenu.classList.contains('hidden')) {
                    submenu.classList.remove('active');
                }
            });
            
            // Calendar initialization
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
                        title: 'Property Dispute Hearing', 
                        start: '2025-05-22T14:30:00',
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgba(16, 185, 129, 0)',
                        textColor: '#ffffff',
                        extendedProps: {
                            type: 'hearing'
                        }
                    },
                    { 
                        title: 'Unpaid Debt Case', 
                        start: '2025-05-18T09:00:00',
                        backgroundColor: 'rgba(245, 158, 11, 0.8)',
                        borderColor: 'rgba(245, 158, 11, 0)',
                        textColor: '#ffffff',
                        extendedProps: {
                            type: 'hearing'
                        }
                    }
                ]
            });
            calendar.render();
              // Sidebar toggle
            document.getElementById('menu-btn').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.remove('-translate-x-full');
                // Add overlay when sidebar is open
                addSidebarOverlay();
            });

            document.getElementById('close-sidebar').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.add('-translate-x-full');
                // Remove overlay when sidebar is closed
                removeSidebarOverlay();
            });            // Toggle submenu items with animation
            document.querySelectorAll('.toggle-menu').forEach(button => {
                button.addEventListener('click', function() {
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
                    overlay.addEventListener('click', function() {
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
            
            // Statistics loading
            loadStatistics();
        });
        
        // Fetch statistics dynamically (this would typically come from a database)
        function loadStatistics() {
            <?php
            // You would typically fetch these values from a database in PHP
            // For now we'll keep the static example but note how this could be replaced with PHP code
            ?>            let complaints = 20;
            let cases = 12;
            let hearings = 8;
            let resolved = 10;
            let pending = 5;
            let mediated = 7;
            let rejected = 3;

            document.getElementById('complaints-count').textContent = complaints;
            document.getElementById('cases-count').textContent = cases;
            document.getElementById('hearings-count').textContent = hearings;
            document.getElementById('resolved-count').textContent = resolved;
            document.getElementById('pending-count').textContent = pending;
            document.getElementById('mediated-count').textContent = mediated;
            document.getElementById('rejected-count').textContent = rejected;

            let max = Math.max(complaints, cases, hearings, resolved, pending, mediated, rejected, 1);            document.getElementById('complaints-progress').style.width = (complaints / max) * 100 + "%";
            document.getElementById('cases-progress').style.width = (cases / max) * 100 + "%";
            document.getElementById('hearings-progress').style.width = (hearings / max) * 100 + "%";
            document.getElementById('resolved-progress').style.width = (resolved / max) * 100 + "%";
            document.getElementById('pending-progress').style.width = (pending / max) * 100 + "%";
            document.getElementById('mediated-progress').style.width = (mediated / max) * 100 + "%";
            document.getElementById('rejected-progress').style.width = (rejected / max) * 100 + "%";
            
            // Initialize Chart
            initChart();
        }
        
        function initChart() {
            const ctx = document.getElementById('statsChart').getContext('2d');
            const monthlyData = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Complaints',
                        data: [5, 8, 12, 15, 20, 23],
                        borderColor: '#0c9ced',
                        backgroundColor: 'rgba(12, 156, 237, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Cases',
                        data: [3, 5, 7, 9, 12, 15],
                        borderColor: '#FBBF24',
                        backgroundColor: 'rgba(251, 191, 36, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Resolved',
                        data: [1, 3, 5, 6, 8, 10],
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }
                ]
            };
            
            new Chart(ctx, {
                type: 'line',
                data: monthlyData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: '#f3f4f6'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }          // Call loadStatistics on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
        });
    </script>
    
    <!-- Chatbot Button and Container -->
    <button class="chatbot-button" id="chatbotButton" aria-label="Open case assistant chatbot">
        <div class="pulse"></div>
        <i class="fas fa-robot"></i>
    </button>
    
    <div class="chatbot-container" id="chatbotContainer">
        <div class="chatbot-header">
            <h3><i class="fas fa-robot"></i> Case Assistant</h3>
            <button class="chatbot-close" id="chatbotClose" aria-label="Close chatbot">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="chatbot-body" id="chatbotBody">
            <!-- Bot welcome message -->
            <div class="chat-message bot-message">
                <div class="bot-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    Hi there! I'm your Case Assistant. How can I help you with managing barangay cases today?
                    <div class="message-time">Just now</div>
                </div>
            </div>
        </div>
        <div class="chatbot-footer">
            <input type="text" class="chatbot-input" id="chatbotInput" placeholder="Type your question here..." aria-label="Type your message">
            <button class="send-button" id="sendButton" aria-label="Send message">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
    
    <script>
        // Chatbot functionality
        document.addEventListener('DOMContentLoaded', function() {
            const chatbotButton = document.getElementById('chatbotButton');
            const chatbotContainer = document.getElementById('chatbotContainer');
            const chatbotClose = document.getElementById('chatbotClose');
            const chatbotInput = document.getElementById('chatbotInput');
            const sendButton = document.getElementById('sendButton');
            const chatbotBody = document.getElementById('chatbotBody');
            
            // Toggle chatbot visibility
            chatbotButton.addEventListener('click', function() {
                chatbotContainer.classList.toggle('active');
                chatbotInput.focus();
            });
            
            // Close chatbot
            chatbotClose.addEventListener('click', function() {
                chatbotContainer.classList.remove('active');
            });
            
            // Send message function
            function sendMessage() {
                const message = chatbotInput.value.trim();
                if (message === '') return;
                
                // Add user message to chat
                const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const userMessageHTML = `
                    <div class="chat-message user-message">
                        <div class="message-content">
                            ${message}
                            <div class="message-time">${timestamp}</div>
                        </div>
                    </div>
                `;
                
                chatbotBody.innerHTML += userMessageHTML;
                chatbotInput.value = '';
                chatbotBody.scrollTop = chatbotBody.scrollHeight;
                
                // Simulate bot typing
                setTimeout(() => {
                    const botResponse = getBotResponse(message);
                    const botMessageHTML = `
                        <div class="chat-message bot-message">
                            <div class="bot-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="message-content">
                                ${botResponse}
                                <div class="message-time">${timestamp}</div>
                            </div>
                        </div>
                    `;
                    
                    chatbotBody.innerHTML += botMessageHTML;
                    chatbotBody.scrollTop = chatbotBody.scrollHeight;
                }, 800);
            }
            
            // Send message on button click
            sendButton.addEventListener('click', sendMessage);
            
            // Send message on Enter key
            chatbotInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
            
            // Simple bot response function for barangay officials
            function getBotResponse(message) {
                message = message.toLowerCase();
                
                if (message.includes('hello') || message.includes('hi') || message.includes('hey')) {
                    return 'Hello! How can I assist you with managing cases today?';
                }
                else if (message.includes('case status') || message.includes('status')) {
                    return 'You can view all case statuses in the "View Cases" section. Would you like me to help you navigate there?';
                }
                else if (message.includes('hearing') || message.includes('schedule')) {
                    return 'To schedule a new hearing, go to "Appoint Hearing" under the Schedule menu. To view all upcoming hearings, check the calendar on your dashboard.';
                }
                else if (message.includes('mediation') || message.includes('mediator')) {
                    return 'Mediation sessions are conducted by trained members of the Lupong Tagapamayapa. Upcoming sessions can be found in the calendar.';
                }
                else if (message.includes('complaint') || message.includes('file') || message.includes('new complaint')) {
                    return 'To add a new complaint to the system, click on "Add Complaint" from the Quick Actions section or navigate to Complaints > Add Complaints from the menu.';
                }
                else if (message.includes('kp form') || message.includes('form') || message.includes('print form')) {
                    return 'You can access KP Forms under KP Forms > View KP Form Templates or KP Forms > Print KP Form in the navigation menu.';
                }
                else if (message.includes('report') || message.includes('statistics')) {
                    return 'For detailed case reports and statistics, check "View Case Reports" or "View Complaints Report" under the Reports menu.';
                }
                else if (message.includes('thank')) {
                    return 'You\'re welcome! Is there anything else I can help you with regarding case management?';
                }
                else {
                    return 'I\'m here to help with case management tasks. You can ask about scheduling hearings, filing complaints, checking case status, KP forms, or generating reports.';
                }
            }
        });
    </script>
</body>
</html>
