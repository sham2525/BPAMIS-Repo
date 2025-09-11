<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
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
                        'pulse-subtle': 'pulse-subtle 2s infinite',
                        'bell-ring': 'bell-ring 1s ease-in-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        'pulse-subtle': {
                            '0%, 100%': { opacity: 1 },
                            '50%': { opacity: 0.8 }
                        },
                        'bell-ring': {
                            '0%, 100%': { transform: 'rotate(0)' },
                            '20%, 60%': { transform: 'rotate(8deg)' },
                            '40%, 80%': { transform: 'rotate(-8deg)' }
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
        }
        .notification-card {
            transition: all 0.2s ease;
        }
        .notification-card:hover {
            background-color: #f9fafc;
        }
        .unread-indicator {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #0c9ced;
            top: 22px;
            right: 22px;
        }
        
        /* Notification item styles */
        .notification-item {
            transition: all 0.2s ease;
        }
        .notification-item:hover {
            background-color: #f9fafb;
        }
        .notification-dot {
            transition: all 0.2s ease;
        }
        .notification-item:hover .notification-dot {
            transform: scale(1.2);
        }
        .gradient-bg {
            background: linear-gradient(to right, #f0f7ff, #e0effe);
        }
        
        /* Empty state animation */
        .empty-icon-container {
            animation: float 4s ease-in-out infinite;
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

    <!-- Page Header -->
    <div class="w-full mt-10 px-4">
        <div class="gradient-bg rounded-2xl shadow-sm p-8 md:p-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-light text-primary-800">Your <span class="font-medium">Notifications</span></h2>
                    <p class="mt-3 text-gray-600 max-w-md">Stay updated with the latest activity in your cases and complaints.</p>
                </div>
                <div class="hidden md:flex items-center">
                    <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center animate-bell-ring">
                        <i class="fas fa-bell text-primary-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters & Search -->
    <div class="w-full mt-6 px-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-wrap justify-between items-center">
                <div class="flex flex-wrap items-center gap-2 mb-2 md:mb-0">
                    <button class="px-3 py-1 bg-primary-50 text-primary-700 rounded-lg text-sm font-medium border border-primary-100">All</button>
                    <button class="px-3 py-1 text-gray-500 rounded-lg text-sm hover:bg-gray-50">Unread</button>
                    <button class="px-3 py-1 text-gray-500 rounded-lg text-sm hover:bg-gray-50">Cases</button>
                    <button class="px-3 py-1 text-gray-500 rounded-lg text-sm hover:bg-gray-50">Complaints</button>
                    <button class="px-3 py-1 text-gray-500 rounded-lg text-sm hover:bg-gray-50">Hearings</button>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input 
                            type="text" 
                            placeholder="Search notifications..." 
                            class="pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-100 focus:border-primary-300 w-full"
                        >
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    <button class="text-primary-600 hover:text-primary-700 text-sm font-medium whitespace-nowrap">
                        <i class="fas fa-check-double mr-1"></i> Mark all as read
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="w-full mt-6 px-4 pb-10">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-100">
                <!-- Today Section -->
                <div class="p-4 bg-gray-50">
                    <h3 class="text-sm font-medium text-gray-500">Today</h3>
                </div>
                
                <!-- New notification with unread indicator -->
                <div class="notification-card p-5 relative cursor-pointer" data-id="notif-1">
                    <div class="unread-indicator animate-pulse-subtle"></div>
                    <div class="flex">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-primary-600"></i>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <p class="text-sm font-medium">Hearing Scheduled</p>
                            <p class="text-sm text-gray-600 mt-1">Your case CASE-001 (Property Dispute) has been scheduled for a hearing on May 20, 2025 at 10:00 AM.</p>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs text-gray-500">Today at 9:15 AM</p>
                                <div class="flex gap-2">
                                    <button class="text-primary-600 hover:text-primary-700 text-sm">View Case</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- New notification with unread indicator -->
                <div class="notification-card p-5 relative cursor-pointer" data-id="notif-2">
                    <div class="unread-indicator animate-pulse-subtle"></div>
                    <div class="flex">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-alt text-green-600"></i>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <p class="text-sm font-medium">Complaint Status Updated</p>
                            <p class="text-sm text-gray-600 mt-1">Your complaint COMP-002 (Property damage claim) has been reviewed by the barangay officials and was forwarded to the barangay captain.</p>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs text-gray-500">Today at 8:30 AM</p>
                                <div class="flex gap-2">
                                    <button class="text-primary-600 hover:text-primary-700 text-sm">View Complaint</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Yesterday Section -->
                <div class="p-4 bg-gray-50">
                    <h3 class="text-sm font-medium text-gray-500">Yesterday</h3>
                </div>
                
                <!-- Read notification -->
                <div class="notification-card p-5 cursor-pointer" data-id="notif-3">
                    <div class="flex">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-gavel text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <p class="text-sm font-medium">Document Request</p>
                            <p class="text-sm text-gray-600 mt-1">Please bring a copy of your property title to the upcoming mediation session for CASE-003 (Boundary Dispute).</p>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs text-gray-500">May 12, 2025 at 4:45 PM</p>
                                <div class="flex gap-2">
                                    <button class="text-primary-600 hover:text-primary-700 text-sm">View Case</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Earlier Section -->
                <div class="p-4 bg-gray-50">
                    <h3 class="text-sm font-medium text-gray-500">Earlier</h3>
                </div>
                
                <!-- Read notification -->
                <div class="notification-card p-5 cursor-pointer" data-id="notif-4">
                    <div class="flex">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-purple-600"></i>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <p class="text-sm font-medium">Case Resolution</p>
                            <p class="text-sm text-gray-600 mt-1">Case CASE-002 (Noise Complaint) has been successfully resolved. Both parties have reached an agreement.</p>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs text-gray-500">Apr 30, 2025 at 2:20 PM</p>
                                <div class="flex gap-2">
                                    <button class="text-primary-600 hover:text-primary-700 text-sm">View Case</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Read notification -->
                <div class="notification-card p-5 cursor-pointer" data-id="notif-5">
                    <div class="flex">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-circle text-red-600"></i>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <p class="text-sm font-medium">Reminder: Upcoming Hearing</p>
                            <p class="text-sm text-gray-600 mt-1">This is a reminder for your upcoming hearing for CASE-002 (Noise Complaint) scheduled for Apr 25, 2025 at 1:30 PM.</p>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs text-gray-500">Apr 23, 2025 at 10:00 AM</p>
                                <div class="flex gap-2">
                                    <button class="text-primary-600 hover:text-primary-700 text-sm">View Case</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            <nav class="flex items-center space-x-1">
                <button class="p-2 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="px-3 py-1 rounded-md bg-primary-50 text-primary-600 font-medium">1</button>
                <button class="px-3 py-1 rounded-md text-gray-500 hover:bg-gray-100">2</button>
                <button class="p-2 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </nav>
        </div>
        
        <div class="mt-6 flex justify-center">
            <button onclick="window.location.href='home-resident.php'" class="px-4 py-2 text-gray-500 hover:text-gray-700 flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </button>
        </div>
    </div>
    
    <!-- No notifications state (hidden by default) -->
    <div id="no-notifications" class="hidden w-full mt-10 px-4 pb-10">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-10 text-center">
            <div class="flex justify-center mb-4">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-bell-slash text-gray-300 text-3xl"></i>
                </div>
            </div>
            <h3 class="text-lg font-medium text-gray-700 mb-2">No notifications yet</h3>
            <p class="text-gray-500 max-w-md mx-auto">When you receive new notifications about your cases or complaints, they will appear here.</p>
            <div class="mt-6">
                <button onclick="window.location.href='home-resident.php'" class="px-4 py-2 bg-primary-50 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-100 transition-colors">
                    Return to Dashboard
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter buttons functionality
            const filterButtons = document.querySelectorAll('.px-3.py-1.rounded-lg.text-sm');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Reset all buttons
                    filterButtons.forEach(btn => {
                        btn.classList.remove('bg-primary-50', 'text-primary-700', 'border', 'border-primary-100');
                        btn.classList.add('text-gray-500');
                    });
                    
                    // Set active button
                    this.classList.remove('text-gray-500');
                    this.classList.add('bg-primary-50', 'text-primary-700', 'border', 'border-primary-100');
                    
                    // Toggle empty state for demo purposes when "Hearings" is clicked
                    if (this.textContent.trim() === "Hearings") {
                        document.querySelector('.divide-y.divide-gray-100').parentElement.classList.add('hidden');
                        document.querySelector('.mt-6.flex.justify-center').classList.add('hidden');
                        document.getElementById('no-notifications').classList.remove('hidden');
                    } else {
                        document.querySelector('.divide-y.divide-gray-100').parentElement.classList.remove('hidden');
                        document.querySelector('.mt-6.flex.justify-center').classList.remove('hidden');
                        document.getElementById('no-notifications').classList.add('hidden');
                    }
                });
            });
            
            // Search functionality
            const searchInput = document.querySelector('input[type="text"]');
            const notificationCards = document.querySelectorAll('.notification-card');
            
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                
                // Filter notifications
                let hasResults = false;
                notificationCards.forEach(card => {
                    const content = card.textContent.toLowerCase();
                    
                    if (content.includes(query)) {
                        card.style.display = '';
                        hasResults = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Check if sections are empty
                document.querySelectorAll('.bg-gray-50').forEach(section => {
                    let nextElement = section.nextElementSibling;
                    let hasVisibleCards = false;
                    
                    while (nextElement && !nextElement.classList.contains('bg-gray-50')) {
                        if (nextElement.classList.contains('notification-card') && nextElement.style.display !== 'none') {
                            hasVisibleCards = true;
                            break;
                        }
                        nextElement = nextElement.nextElementSibling;
                    }
                    
                    // Hide section header if no visible cards
                    section.style.display = hasVisibleCards ? '' : 'none';
                });
                
                // If no results, show empty state
                if (!hasResults) {
                    document.querySelector('.divide-y.divide-gray-100').parentElement.classList.add('hidden');
                    document.querySelector('.mt-6.flex.justify-center').classList.add('hidden');
                    document.getElementById('no-notifications').classList.remove('hidden');
                    document.getElementById('no-notifications').querySelector('h3').textContent = 'No matching notifications';
                    document.getElementById('no-notifications').querySelector('p').textContent = 'Try adjusting your search or filter to find what you\'re looking for.';
                } else {
                    document.querySelector('.divide-y.divide-gray-100').parentElement.classList.remove('hidden');
                    document.querySelector('.mt-6.flex.justify-center').classList.remove('hidden');
                    document.getElementById('no-notifications').classList.add('hidden');
                }
            });
            
            // Notification click functionality
            notificationCards.forEach(card => {
                card.addEventListener('click', function() {
                    const notifId = this.getAttribute('data-id');
                    
                    // Remove unread indicator if present
                    const unreadIndicator = this.querySelector('.unread-indicator');
                    if (unreadIndicator) {
                        unreadIndicator.classList.add('opacity-0');
                        setTimeout(() => {
                            unreadIndicator.remove();
                        }, 300);
                    }
                    
                    // Determine destination based on notification type
                    let destination = 'home-resident.php';
                    
                    if (notifId === 'notif-1' || notifId === 'notif-3' || notifId === 'notif-4' || notifId === 'notif-5') {
                        destination = 'view_cases.php';
                    } else if (notifId === 'notif-2') {
                        destination = 'view_complaints.php';
                    }
                    
                    // Navigate after a small delay to show the read effect
                    setTimeout(() => {
                        window.location.href = destination;
                    }, 300);
                });
            });
            
            // Mark all as read functionality
            const markAllButton = document.querySelector('button:has(.fa-check-double)');
            markAllButton.addEventListener('click', function() {
                document.querySelectorAll('.unread-indicator').forEach(indicator => {
                    indicator.classList.add('opacity-0');
                    setTimeout(() => {
                        indicator.remove();
                    }, 300);
                });
            });
            
            // Mobile menu toggle
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
                else if (message.includes('notification') || message.includes('notifications')) {
                    return 'You can view all your notifications on this page. These include updates about your cases, scheduled hearings, and other important information.';
                }
                else {
                    return 'I\'m not sure I understand. Could you please rephrase your question? You can ask about case status, hearings, complaints, or contact information.';
                }
            }
        });
    </script>
</body>
</html>
