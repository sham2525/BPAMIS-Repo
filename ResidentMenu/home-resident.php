<?php
session_start();
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
<body class="bg-gray-50 font-sans">    
    <?php include_once('../includes/resident_nav.php'); ?>
      <!-- Welcome Banner -->
    <section class="container mx-auto mt-10 px-4">
        <div class="gradient-bg rounded-2xl shadow-sm p-8 md:p-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h2 class="text-3xl font-light text-primary-800">Welcome, <span class="font-medium">Resident</span></h2>
                <p class="mt-3 text-gray-600 max-w-md">Easily manage your complaints and track case statuses through this streamlined dashboard.</p>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <div class="container mx-auto mt-8 px-4">
        <h3 class="text-lg font-medium text-gray-700 mb-4 px-2">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="view_complaints.php" class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-blue-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-clipboard-list text-primary-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">View Complaints</h4>
                    <p class="text-sm text-gray-500">Check status of your submissions</p>
                </div>
            </a>
            <a href="submit_complaints.php" class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-green-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-file-alt text-green-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Submit Complaint</h4>
                    <p class="text-sm text-gray-500">File a new complaint</p>
                </div>
            </a>
            <a href="view_cases.php" class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-yellow-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-gavel text-yellow-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">View Cases</h4>
                    <p class="text-sm text-gray-500">Monitor active cases</p>
                </div>
            </a>
        </div>
    </div>    <!-- Dashboard Content -->
    <div class="container mx-auto mt-10 px-4 pb-10">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
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
                            <span class="text-sm text-gray-600">Pending Complaints</span>
                            <span id="hearings-count" class="text-sm font-medium text-primary-600 bg-primary-50 px-2 py-0.5 rounded">0</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div id="hearings-progress" class="progress-bar bg-yellow-400 h-2 rounded-full"></div>
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
                            <i class="fas fa-clock text-yellow-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Pending Cases</p>
                                <p id="pending-count" class="text-lg font-medium text-yellow-600">0</p>
                            </div>
                        </div>
                        
                        <div class="bg-purple-50 rounded-lg p-4 flex items-center col-span-2">
                            <i class="fas fa-calendar-alt text-purple-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Scheduled Hearings</p>
                                <p id="mediated-count" class="text-lg font-medium text-purple-600">0</p>
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

        function animateStatistics() {
            // Data values
            const complaints = 10;
            const cases = 1;
            const mediated = 2;
            const resolved = 2;
            const pending = 0;
            const hearings = 3;
            
            // Maximum value for scaling the progress bars
            const max = Math.max(complaints, cases, hearings, resolved, pending, mediated, 1);
            
            // Set text values
            document.getElementById('complaints-count').textContent = complaints;
            document.getElementById('cases-count').textContent = cases;
            document.getElementById('hearings-count').textContent = hearings;
            document.getElementById('resolved-count').textContent = resolved;
            document.getElementById('pending-count').textContent = pending;
            document.getElementById('mediated-count').textContent = mediated;
            
            // Set progress bar widths with delay for animation effect
            setTimeout(() => {
                document.getElementById('complaints-progress').style.width = (complaints / max) * 100 + "%";
            }, 300);
            
            setTimeout(() => {
                document.getElementById('cases-progress').style.width = (cases / max) * 100 + "%";
            }, 600);
            
            setTimeout(() => {
                document.getElementById('hearings-progress').style.width = (hearings / max) * 100 + "%";
            }, 900);
        }

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
                    Hi there! I'm your Case Assistant. How can I help you with your barangay cases today?
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
            
            // Simple bot response function
            function getBotResponse(message) {
                message = message.toLowerCase();
                
                if (message.includes('hello') || message.includes('hi') || message.includes('hey')) {
                    return 'Hello! How can I assist you with your case today?';
                }
                else if (message.includes('case status') || message.includes('status')) {
                    return 'To check your case status, please go to the "View Cases" section where you can see all your active and resolved cases.';
                }
                else if (message.includes('hearing') || message.includes('schedule')) {
                    return 'Your next hearing is scheduled for May 20, 2025. You can view all upcoming hearings in the calendar section of your dashboard.';
                }
                else if (message.includes('mediation') || message.includes('mediator')) {
                    return 'Mediation sessions are conducted by trained Lupong Tagapamayapa members. Your upcoming mediation session is scheduled for May 18, 2025.';
                }
                else if (message.includes('complaint') || message.includes('file') || message.includes('submit')) {
                    return 'To file a new complaint, click on the "Submit Complaint" button in the Quick Actions section of your dashboard.';
                }
                else if (message.includes('contact') || message.includes('barangay') || message.includes('office')) {
                    return 'You can contact the Barangay Office at (123) 456-7890 or visit them during office hours: Monday to Friday, 8:00 AM to 5:00 PM.';
                }
                else if (message.includes('thank')) {
                    return 'You\'re welcome! Is there anything else I can help you with?';
                }
                else {
                    return 'I\'m not sure I understand. Could you please rephrase your question? You can ask about case status, hearings, complaints, or contact information.';
                }
            }
        });
    </script>
</body>
</html>
