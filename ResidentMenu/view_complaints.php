<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaints</title>
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
    <style>
        .status-badge {
            transition: all 0.3s ease;
        }
        .table-row {
            transition: all 0.2s ease;
        }
        .table-row:hover {
            background-color: #f0f7ff;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">    <?php include_once('../includes/resident_nav.php'); ?>    <!-- Page Header -->
    <div class="w-full mt-10 px-4">
        <div class="gradient-bg rounded-2xl shadow-sm p-8 md:p-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-light text-primary-800">Your <span class="font-medium">Complaints</span></h2>
                    <p class="mt-3 text-gray-600 max-w-md">View and track the status of all your submitted complaints.</p>
                </div>
                <div class="hidden md:flex space-x-2">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium flex items-center">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span> Resolved: 1
                    </span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium flex items-center">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span> Pending: 0
                    </span>
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
                    <button class="px-3 py-1 text-gray-500 rounded-lg text-sm hover:bg-gray-50">Pending</button>
                    <button class="px-3 py-1 text-gray-500 rounded-lg text-sm hover:bg-gray-50">Resolved</button>
                    <button class="px-3 py-1 text-gray-500 rounded-lg text-sm hover:bg-gray-50">Recent</button>
                </div>
                
                <div class="relative">
                    <input 
                        type="text" 
                        placeholder="Search complaints..." 
                        class="pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-100 focus:border-primary-300 w-full"
                    >
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaints List -->
    <div class="w-full mt-6 px-4 pb-10">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="py-4 px-6 text-sm font-medium text-gray-600">Complaint ID</th>
                            <th class="py-4 px-6 text-sm font-medium text-gray-600">Title</th>
                            <th class="py-4 px-6 text-sm font-medium text-gray-600">Date Filed</th>
                            <th class="py-4 px-6 text-sm font-medium text-gray-600">Status</th>
                            <th class="py-4 px-6 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t border-gray-100 table-row">
                            <td class="py-4 px-6 text-sm">COMP-001</td>
                            <td class="py-4 px-6 text-sm font-medium">Noise disturbance</td>
                            <td class="py-4 px-6 text-sm text-gray-500">May 10, 2025</td>
                            <td class="py-4 px-6">
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium status-badge">
                                    Resolved
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <button class="px-3 py-1 bg-primary-50 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-100 transition-colors flex items-center">
                                    <i class="fas fa-eye mr-1"></i> View Details
                                </button>
                            </td>
                        </tr>
                        <tr class="border-t border-gray-100 table-row">
                            <td class="py-4 px-6 text-sm">COMP-002</td>
                            <td class="py-4 px-6 text-sm font-medium">Property damage claim</td>
                            <td class="py-4 px-6 text-sm text-gray-500">May 12, 2025</td>
                            <td class="py-4 px-6">
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium status-badge">
                                    Pending
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <button class="px-3 py-1 bg-primary-50 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-100 transition-colors flex items-center">
                                    <i class="fas fa-eye mr-1"></i> View Details
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile View - Card Layout -->
            <div class="md:hidden divide-y divide-gray-100">
                <!-- Complaint Item -->
                <div class="p-4 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-medium text-gray-500">COMP-001</span>
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                            Resolved
                        </span>
                    </div>
                    <h3 class="font-medium">Noise disturbance</h3>
                    <p class="text-sm text-gray-500">Filed on: May 10, 2025</p>
                    <div class="pt-2">
                        <button class="w-full px-3 py-2 bg-primary-50 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-100 transition-colors flex items-center justify-center">
                            <i class="fas fa-eye mr-2"></i> View Details
                        </button>
                    </div>
                </div>
                
                <!-- Complaint Item -->
                <div class="p-4 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-medium text-gray-500">COMP-002</span>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                            Pending
                        </span>
                    </div>
                    <h3 class="font-medium">Property damage claim</h3>
                    <p class="text-sm text-gray-500">Filed on: May 12, 2025</p>
                    <div class="pt-2">
                        <button class="w-full px-3 py-2 bg-primary-50 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-100 transition-colors flex items-center justify-center">
                            <i class="fas fa-eye mr-2"></i> View Details
                        </button>
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
                });
            });
            
            // Search functionality
            const searchInput = document.querySelector('input[type="text"]');
            const tableRows = document.querySelectorAll('.table-row');
            const mobileCards = document.querySelectorAll('.md\\:hidden .p-4');
            
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                
                // Filter desktop rows
                tableRows.forEach(row => {
                    const title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const id = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                    
                    if (title.includes(query) || id.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Filter mobile cards
                mobileCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const id = card.querySelector('.text-xs.font-medium').textContent.toLowerCase();
                    
                    if (title.includes(query) || id.includes(query)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
            
            // Modal functionality for "View Details"
            const viewButtons = document.querySelectorAll('button:not([onclick])');
            const body = document.body;
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const isDesktop = this.closest('tr') !== null;
                    let complaintId, complaintTitle, complaintStatus;
                    
                    if (isDesktop) {
                        const row = this.closest('tr');
                        complaintId = row.querySelector('td:nth-child(1)').textContent;
                        complaintTitle = row.querySelector('td:nth-child(2)').textContent;
                        complaintStatus = row.querySelector('td:nth-child(4) span').textContent.trim();
                    } else {
                        const card = this.closest('.p-4');
                        complaintId = card.querySelector('.text-xs.font-medium').textContent;
                        complaintTitle = card.querySelector('h3').textContent;
                        complaintStatus = card.querySelector('.px-2.py-1').textContent.trim();
                    }
                    
                    // Create modal
                    const modal = createModal(complaintId, complaintTitle, complaintStatus);
                    body.appendChild(modal);
                    
                    // Show modal with animation
                    setTimeout(() => {
                        modal.querySelector('.fixed').classList.remove('opacity-0');
                        modal.querySelector('.transform').classList.remove('scale-95');
                        modal.querySelector('.transform').classList.add('scale-100');
                    }, 10);
                    
                    // Close button functionality
                    modal.querySelector('button').addEventListener('click', () => {
                        closeModal(modal);
                    });
                    
                    // Close on click outside
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal.querySelector('.fixed')) {
                            closeModal(modal);
                        }
                    });
                });
            });
            
            function createModal(id, title, status) {
                const modal = document.createElement('div');
                modal.innerHTML = `
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4 z-50 transition-opacity duration-300 opacity-0">
                        <div class="bg-white rounded-xl shadow-xl max-w-md w-full transform transition-transform duration-300 scale-95">
                            <div class="border-b border-gray-100 px-5 py-4 flex justify-between items-center">
                                <h3 class="font-medium text-lg">Complaint Details</h3>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="p-5 space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Complaint ID</p>
                                    <p class="font-medium">${id}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Title</p>
                                    <p class="font-medium">${title}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Status</p>
                                    <span class="px-3 py-1 ${status === 'Resolved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'} rounded-full text-xs font-medium inline-block mt-1">
                                        ${status}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Description</p>
                                    <p class="text-sm mt-1">This is a detailed description of the complaint that was filed. It includes all the information provided by the resident when submitting the complaint.</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Date Filed</p>
                                    <p class="font-medium">May ${status === 'Resolved' ? '10' : '12'}, 2025</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Resolution Notes</p>
                                    <p class="text-sm mt-1">${status === 'Resolved' ? 'This complaint was resolved on May 12, 2025. The issue was addressed by the barangay officials.' : 'This complaint is still being processed by the barangay officials.'}</p>
                                </div>
                            </div>
                            <div class="border-t border-gray-100 px-5 py-4 flex justify-end">
                                <button class="px-4 py-2 bg-primary-50 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-100 transition-colors">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                return modal;
            }
            
            function closeModal(modal) {
                modal.querySelector('.fixed').classList.add('opacity-0');
                modal.querySelector('.transform').classList.remove('scale-100');
                modal.querySelector('.transform').classList.add('scale-95');
                
                setTimeout(() => {
                    modal.remove();
                }, 300);
            }
            
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
                else {
                    return 'I\'m not sure I understand. Could you please rephrase your question? You can ask about case status, hearings, complaints, or contact information.';
                }
            }
        });
    </script>
</body>
</html>
