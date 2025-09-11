<?php
/**
 * View Case Reports Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
$pageTitle = "View Case Reports";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case Reports</title>
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
        .gradient-bg {
            background: linear-gradient(to right, #f0f7ff, #e0effe);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }        /* Chatbot Button Styles */
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
    
    <!-- Page Header -->
    <section class="container mx-auto mt-8 px-4">
        <div class="gradient-bg rounded-xl shadow-sm p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h1 class="text-2xl font-medium text-primary-800">Case Reports</h1>
                <p class="mt-1 text-gray-600">Generate and view analytics on case resolution and statistics</p>
            </div>
        </div>
    </section>
    
    <div class="container mx-auto mt-8 px-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">        <h2 class="text-lg font-medium text-gray-800 mb-5 flex items-center">
            <i class="fas fa-chart-bar text-primary-500 mr-2"></i>
            Report Controls
        </h2>
        
        <!-- Filter Options -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="dateRange" class="block text-sm font-medium text-gray-600 mb-1.5">Date Range</label>
                <select id="dateRange" class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300 transition">
                    <option value="all">All Time</option>
                    <option value="this_month" selected>This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            
            <div>
                <label for="caseStatus" class="block text-sm font-medium text-gray-600 mb-1.5">Case Status</label>
                <select id="caseStatus" class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300 transition">
                    <option value="all">All Statuses</option>
                    <option value="open">Open</option>
                    <option value="pending">Pending Hearing</option>
                    <option value="mediation">Mediation</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            
            <div class="flex flex-col justify-end">
                <div class="flex gap-2">
                    <button id="printReport" class="card-hover bg-gray-700 text-white px-4 py-3 rounded-lg hover:bg-gray-800 transition flex items-center justify-center flex-1">
                        <i class="fas fa-print"></i> Print
                    </button>                    <button id="exportPDF" class="card-hover bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 transition flex items-center justify-center flex-1">
                        <i class="fas fa-file-pdf mr-2"></i> PDF
                    </button>
                    <button id="exportExcel" class="card-hover bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition flex items-center justify-center flex-1">
                        <i class="fas fa-file-excel mr-2"></i> Excel
                    </button>
                </div>
            </div>
        </div>
          <!-- Charts Section -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-pie text-primary-500 mr-2"></i>
                    Cases by Status
                </h3>
                <canvas id="statusChart" height="200"></canvas>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-line text-primary-500 mr-2"></i>
                    Monthly Case Trends
                </h3>
                <canvas id="trendsChart" height="200"></canvas>
            </div>
        </div>
          <!-- Data Table -->
        <h2 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
            <i class="fas fa-table text-primary-500 mr-2"></i>
            Case Details
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full mt-4">
                <thead>
                    <tr>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Case ID</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Case Title</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Complainant</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Date Filed</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Status</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Resolution Time</th>
                        <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // In a real application, this would be populated from database
                    // For now, we'll use sample data
                    $sampleCases = [
                        [
                            'id' => 'KP-2025-001',
                            'title' => 'Property Boundary Dispute',
                            'complainant' => 'Maria Garcia',
                            'date_filed' => '2025-05-03',
                            'status' => 'Open',
                            'resolution_time' => 'N/A'
                        ],
                        [
                            'id' => 'KP-2025-002',
                            'title' => 'Unpaid Debt',
                            'complainant' => 'Elena Ramos',
                            'date_filed' => '2025-04-28',
                            'status' => 'Pending Hearing',
                            'resolution_time' => 'N/A'
                        ],
                        [
                            'id' => 'KP-2025-003',
                            'title' => 'Noise Complaint',
                            'complainant' => 'Juan Dela Cruz',
                            'date_filed' => '2025-05-01',
                            'status' => 'Mediation',
                            'resolution_time' => 'N/A'
                        ],
                        [
                            'id' => 'KP-2025-004',
                            'title' => 'Trespassing Case',
                            'complainant' => 'Antonio Lim',
                            'date_filed' => '2025-04-15',
                            'status' => 'Resolved',
                            'resolution_time' => '15 days'
                        ],
                        [
                            'id' => 'KP-2025-005',
                            'title' => 'Physical Injury Case',
                            'complainant' => 'Eduardo Santos',
                            'date_filed' => '2025-04-10',
                            'status' => 'Closed',
                            'resolution_time' => '12 days'
                        ]
                    ];
                      foreach ($sampleCases as $case) {
                        echo '<tr class="border-b border-gray-100 hover:bg-gray-50 transition">';
                        echo '<td class="p-3 text-sm text-gray-700">' . $case['id'] . '</td>';
                        echo '<td class="p-3 text-sm text-gray-700">' . $case['title'] . '</td>';
                        echo '<td class="p-3 text-sm text-gray-700">' . $case['complainant'] . '</td>';
                        echo '<td class="p-3 text-sm text-gray-700">' . $case['date_filed'] . '</td>';
                        
                        // Enhanced status badge with better styling
                        $statusClass = '';
                        $statusIcon = '';
                        switch ($case['status']) {
                            case 'Open':
                                $statusClass = 'text-blue-700 bg-blue-50 border-blue-200';
                                $statusIcon = '<i class="fas fa-folder-open mr-1"></i>';
                                break;
                            case 'Pending Hearing':
                                $statusClass = 'text-amber-700 bg-amber-50 border-amber-200';
                                $statusIcon = '<i class="fas fa-calendar mr-1"></i>';
                                break;
                            case 'Mediation':
                                $statusClass = 'text-purple-700 bg-purple-50 border-purple-200';
                                $statusIcon = '<i class="fas fa-handshake mr-1"></i>';
                                break;
                            case 'Resolved':
                                $statusClass = 'text-green-700 bg-green-50 border-green-200';
                                $statusIcon = '<i class="fas fa-check-circle mr-1"></i>';
                                break;
                            case 'Closed':
                                $statusClass = 'text-gray-700 bg-gray-50 border-gray-200';
                                $statusIcon = '<i class="fas fa-folder mr-1"></i>';
                                break;
                            default:
                                $statusClass = 'text-gray-700 bg-gray-50 border-gray-200';
                                $statusIcon = '<i class="fas fa-info-circle mr-1"></i>';
                        }
                        
                        echo '<td class="p-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs border ' . $statusClass . '">
                                ' . $statusIcon . $case['status'] . '
                            </span>
                          </td>';
                        
                        echo '<td class="p-3 text-sm text-gray-700">' . $case['resolution_time'] . '</td>';
                        echo '<td class="p-3 text-center">
                                <a href="case_report_details.php?id=' . $case['id'] . '" class="card-hover bg-primary-500 text-white px-3 py-1.5 rounded-lg hover:bg-primary-600 transition inline-flex items-center text-sm">
                                    <i class="fas fa-file-alt mr-1.5"></i> View Report
                                </a>
                              </td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
          <!-- Summary Statistics -->
        <div class="mt-8">
            <h2 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-bar text-primary-500 mr-2"></i>
                Summary Statistics
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="card-hover bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
                    <div class="bg-primary-100 p-3 rounded-full mr-4">
                        <i class="fas fa-clipboard-list text-primary-600 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-xs text-gray-500 uppercase tracking-wider">Total Cases</h4>
                        <p class="text-2xl font-semibold text-gray-800">5</p>
                    </div>
                </div>
                <div class="card-hover bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
                    <div class="bg-amber-100 p-3 rounded-full mr-4">
                        <i class="fas fa-folder-open text-amber-600 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-xs text-gray-500 uppercase tracking-wider">Active Cases</h4>
                        <p class="text-2xl font-semibold text-gray-800">3</p>
                    </div>
                </div>
                <div class="card-hover bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
                    <div class="bg-green-100 p-3 rounded-full mr-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-xs text-gray-500 uppercase tracking-wider">Resolved/Closed</h4>
                        <p class="text-2xl font-semibold text-gray-800">2</p>
                    </div>
                </div>
                <div class="card-hover bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
                    <div class="bg-purple-100 p-3 rounded-full mr-4">
                        <i class="fas fa-clock text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-xs text-gray-500 uppercase tracking-wider">Avg Resolution Time</h4>
                        <p class="text-2xl font-semibold text-gray-800">13.5 days</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusChart = new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['Open', 'Pending Hearing', 'Mediation', 'Resolved', 'Closed'],
                    datasets: [{
                        label: 'Cases by Status',
                        data: [1, 1, 1, 1, 1],
                        backgroundColor: [
                            'rgba(12, 156, 237, 0.7)',  // Primary blue
                            'rgba(251, 191, 36, 0.7)',  // Amber
                            'rgba(153, 102, 255, 0.7)', // Purple
                            'rgba(16, 185, 129, 0.7)',  // Green
                            'rgba(107, 114, 128, 0.7)'  // Gray
                        ],
                        borderWidth: 0,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
            
            // Trends Chart
            const trendsCtx = document.getElementById('trendsChart').getContext('2d');
            const trendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Number of Cases',
                        data: [1, 2, 3, 2, 5, 0],
                        borderColor: '#0c9ced',  // Primary color
                        backgroundColor: 'rgba(12, 156, 237, 0.1)',
                        tension: 0.4,
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            },
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
              // Print button functionality
            document.getElementById('printReport').addEventListener('click', function() {
                window.print();
            });
            
            // For a real application, you would implement PDF and Excel export functionality
            // This could use libraries like jsPDF and SheetJS, or make requests to server-side code
            
            // Mobile navigation toggle
            if (typeof menuButton !== 'undefined' && typeof mobileMenu !== 'undefined') {
                menuButton.addEventListener('click', function() {
                    this.classList.toggle('active');
                    if (mobileMenu.style.transform === 'translateY(0%)') {
                        mobileMenu.style.transform = 'translateY(-100%)';
                    } else {
                        mobileMenu.style.transform = 'translateY(0%)';
                    }
                });
            }
            
            // Handle export actions
            document.getElementById('exportPDF').addEventListener('click', function() {
                alert('Exporting to PDF... (This is a placeholder. In a real application, this would generate a PDF)');
            });
              document.getElementById('exportExcel').addEventListener('click', function() {
                alert('Exporting to Excel... (This is a placeholder. In a real application, this would generate an Excel file)');
            });
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
                    Hi there! I'm your Case Assistant. How can I help you with case reports and analytics today?
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
            
            // Simple bot response function for case reports
            function getBotResponse(message) {
                message = message.toLowerCase();
                
                if (message.includes('hello') || message.includes('hi') || message.includes('hey')) {
                    return 'Hello! How can I assist you with case reports and analytics today?';
                }
                else if (message.includes('export') || message.includes('download') || message.includes('save')) {
                    return 'You can export reports using the buttons at the top right: "Print Report," "Export to PDF," or "Export to Excel."';
                }
                else if (message.includes('filter') || message.includes('date range')) {
                    return 'To filter reports by a specific date range, use the date pickers at the top of the page and click "Apply Filter."';
                }
                else if (message.includes('chart') || message.includes('graph') || message.includes('visual')) {
                    return 'The charts show case distribution by type, status, and monthly trends. Hover over chart elements to see detailed information.';
                }
                else if (message.includes('analysis') || message.includes('insight') || message.includes('trend')) {
                    return 'Based on current data, property disputes are the most common case type, with 40% of all cases. The resolution rate has improved by 15% in the last quarter.';
                }
                else if (message.includes('print')) {
                    return 'To print the current report, click the "Print Report" button at the top right of the page, or press Ctrl+P (Cmd+P on Mac).';
                }
                else if (message.includes('statistic') || message.includes('number') || message.includes('count')) {
                    return 'This year, there have been 25 total cases filed, with 15 resolved cases and 10 pending cases. The average resolution time is 21 days.';
                }
                else if (message.includes('thank')) {
                    return 'You\'re welcome! Is there anything else I can help you with regarding case reports or analytics?';
                }
                else {
                    return 'I can help you with case reports and analytics. You can ask about filtering data, exporting reports, understanding charts, or viewing specific statistics.';
                }
            }
        });
    </script>
</body>
</html>
