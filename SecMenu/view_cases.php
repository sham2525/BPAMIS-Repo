<?php
/**
 * View Cases Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
$pageTitle = "View Cases";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Cases</title>
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
                <h1 class="text-2xl font-medium text-primary-800">Cases List</h1>
                <p class="mt-1 text-gray-600">View and manage all barangay cases and their progress</p>
            </div>
        </div>
    </section>
    
    <div class="container mx-auto mt-8 px-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">        
            <!-- Search Bar -->
            <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div class="relative w-full md:w-1/2">
                    <input type="text" id="searchInput" placeholder="Search cases..." class="w-full p-3 pl-10 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex gap-2">
                    <select class="p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300">
                        <option value="">All Statuses</option>
                        <option value="Open">Open</option>
                        <option value="Pending Hearing">Pending Hearing</option>
                        <option value="Mediation">Mediation</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Closed">Closed</option>
                    </select>
                    <a href="appoint_hearing.php" class="card-hover flex items-center bg-primary-500 text-white py-3 px-4 rounded-lg hover:bg-primary-600 transition">
                        <i class="fas fa-calendar-plus mr-2"></i> Schedule Hearing
                    </a>
                </div>
            </div>        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full mt-4">
                <thead>
                    <tr>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Case ID</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Case Title</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Complainant</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Respondent</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Date Filed</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Status</th>
                        <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Action</th>
                    </tr>
            </thead>
            <tbody id="casesTable">
                <?php
                // In a real application, this would be populated from database
                // For now, we'll use sample data
                $sampleCases = [
                    [
                        'id' => 'KP-2025-001',
                        'title' => 'Property Boundary Dispute',
                        'complainant' => 'Maria Garcia',
                        'respondent' => 'Roberto Reyes',
                        'date_filed' => '2025-05-03',
                        'status' => 'Open'
                    ],
                    [
                        'id' => 'KP-2025-002',
                        'title' => 'Unpaid Debt',
                        'complainant' => 'Elena Ramos',
                        'respondent' => 'Jose Mendoza',
                        'date_filed' => '2025-04-28',
                        'status' => 'Pending Hearing'
                    ],
                    [
                        'id' => 'KP-2025-003',
                        'title' => 'Noise Complaint',
                        'complainant' => 'Juan Dela Cruz',
                        'respondent' => 'Pedro Santos',
                        'date_filed' => '2025-05-01',
                        'status' => 'Mediation'
                    ],
                    [
                        'id' => 'KP-2025-004',
                        'title' => 'Trespassing Case',
                        'complainant' => 'Antonio Lim',
                        'respondent' => 'Ricardo Tan',
                        'date_filed' => '2025-04-15',
                        'status' => 'Resolved'
                    ]
                ];
                  foreach ($sampleCases as $case) {
                    echo '<tr class="border-b border-gray-100 hover:bg-gray-50 transition">';
                    echo '<td class="p-3 text-sm text-gray-700">' . $case['id'] . '</td>';
                    echo '<td class="p-3 text-sm text-gray-700">' . $case['title'] . '</td>';
                    echo '<td class="p-3 text-sm text-gray-700">' . $case['complainant'] . '</td>';
                    echo '<td class="p-3 text-sm text-gray-700">' . $case['respondent'] . '</td>';
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
                    
                    echo '<td class="p-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="view_case_details.php?id=' . $case['id'] . '" class="text-primary-600 hover:text-primary-800 transition p-1" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="update_case_status.php?id=' . $case['id'] . '" class="text-yellow-600 hover:text-yellow-800 transition p-1" title="Update Status">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="appoint_hearing.php?id=' . $case['id'] . '" class="text-green-600 hover:text-green-800 transition p-1" title="Schedule Hearing">
                                    <i class="fas fa-calendar-plus"></i>
                                </a>
                            </div>
                          </td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
          <div class="mt-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
            <div>
                Showing 1-4 of 4 entries
            </div>
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
    </div>    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function() {
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll('#casesTable tr');

                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
            
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
                    Hi there! I'm your Case Assistant. How can I help you with case management today?
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
            
            // Simple bot response function for case management
            function getBotResponse(message) {
                message = message.toLowerCase();
                
                if (message.includes('hello') || message.includes('hi') || message.includes('hey')) {
                    return 'Hello! How can I assist you with case management today?';
                }
                else if (message.includes('status') || message.includes('filter')) {
                    return 'You can filter cases by status using the filter options at the top of the page. The statuses include Filed, Mediation, Hearing, Resolved, and Dismissed.';
                }
                else if (message.includes('search') || message.includes('find')) {
                    return 'Use the search box above the table to search for specific cases by case number, complainant name, respondent name, or case details.';
                }
                else if (message.includes('view') || message.includes('details')) {
                    return 'To view complete details of a case, click on the view icon (eye) in the Actions column for the specific case.';
                }
                else if (message.includes('schedule') || message.includes('hearing')) {
                    return 'To schedule a hearing for a case, click on the calendar-plus icon in the Actions column. This will take you to the Appoint Hearing page.';
                }
                else if (message.includes('edit') || message.includes('update')) {
                    return 'To edit case details, click on the edit icon (pencil) in the Actions column for the case you want to modify.';
                }
                else if (message.includes('print') || message.includes('pdf') || message.includes('export')) {
                    return 'To generate a PDF report for a case, click on the PDF icon in the Actions column for that specific case.';
                }
                else if (message.includes('thank')) {
                    return 'You\'re welcome! Is there anything else I can help you with regarding case management?';
                }
                else {
                    return 'I can help you with case management. You can ask about searching, filtering, viewing details, scheduling hearings, or generating reports for cases.';
                }
            }
        });
    </script>
</body>
</html>
