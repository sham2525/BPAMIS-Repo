<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint</title>
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
        .form-input:focus {
            border-color: #0c9ced;
            box-shadow: 0 0 0 3px rgba(12, 156, 237, 0.1);
            outline: none;
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
<body class="bg-gray-50 font-sans">    <?php include_once('../includes/resident_nav.php'); ?>    <!-- Page Header -->
    <div class="container-fluid w-full mt-10 px-4">
        <div class="gradient-bg rounded-2xl shadow-sm p-8 md:p-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-light text-primary-800">Submit <span class="font-medium">Complaint</span></h2>
                    <p class="mt-3 text-gray-600 max-w-md">Fill out the form below to file a new complaint with the barangay office.</p>
                </div>
                <div class="hidden md:block">
                    <img src="../Assets/Img/complaint.svg" alt="Complaint Illustration" class="h-32 w-auto" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </div>    <!-- Form Section -->
    <div class="w-full mt-8 px-4 pb-10">
        <div class="w-full bg-white rounded-xl border border-gray-100 shadow-sm p-8">
            <form action="#" method="POST" class="space-y-6">                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label for="complaint-title" class="block text-sm font-medium text-gray-700">Complaint Title</label>
                        <div class="relative">
                            <div class="absolute left-3 text-gray-400 input-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <input 
                                type="text" 
                                id="complaint-title" 
                                name="complaint_title" 
                                placeholder="Enter a brief title for your complaint" 
                                class="w-full pl-10 py-3 pr-3 border border-gray-200 rounded-lg form-input focus:ring-2 focus:ring-primary-100 transition-all duration-300" 
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <label for="complaint-type" class="block text-sm font-medium text-gray-700">Complaint Type</label>
                        <div class="relative">
                            <div class="absolute left-3 text-gray-400 input-icon">
                                <i class="fas fa-folder"></i>
                            </div>
                            <select 
                                id="complaint-type" 
                                name="complaint_type" 
                                class="w-full pl-10 py-3 pr-3 border border-gray-200 rounded-lg form-input bg-white focus:ring-2 focus:ring-primary-100 transition-all duration-300"
                                required
                            >
                                <option value="" disabled selected>Select complaint type</option>
                                <option value="noise">Noise Complaint</option>
                                <option value="property">Property Dispute</option>
                                <option value="public">Public Disturbance</option>
                                <option value="services">Public Services</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                    </div>
                </div>
                  <div class="space-y-1">
                    <label for="complaint-description" class="block text-sm font-medium text-gray-700">Description</label>
                    <div class="relative">
                        <textarea 
                            id="complaint-description" 
                            name="complaint_description" 
                            rows="6" 
                            placeholder="Provide a detailed description of your complaint" 
                            class="w-full py-3 px-3 border border-gray-200 rounded-lg form-input focus:ring-2 focus:ring-primary-100 transition-all duration-300" 
                            required
                        ></textarea>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Please include all relevant details about your complaint including date, time, location, and people involved.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label for="incident-date" class="block text-sm font-medium text-gray-700">Incident Date</label>
                        <div class="relative">
                            <div class="absolute left-3 text-gray-400 input-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <input 
                                type="date" 
                                id="incident-date" 
                                name="incident_date" 
                                class="w-full pl-10 py-3 pr-3 border border-gray-200 rounded-lg form-input focus:ring-2 focus:ring-primary-100 transition-all duration-300" 
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <label for="incident-time" class="block text-sm font-medium text-gray-700">Incident Time (Optional)</label>
                        <div class="relative">
                            <div class="absolute left-3 text-gray-400 input-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <input 
                                type="time" 
                                id="incident-time" 
                                name="incident_time" 
                                class="w-full pl-10 py-3 pr-3 border border-gray-200 rounded-lg form-input focus:ring-2 focus:ring-primary-100 transition-all duration-300" 
                            >
                        </div>
                    </div>
                </div>
                
                <div class="space-y-1">
                    <label for="complaint-attachment" class="block text-sm font-medium text-gray-700">Attachments (Optional)</label>
                    <div class="relative">
                        <div class="flex justify-center items-center w-full">
                            <label for="complaint-attachment" class="flex flex-col justify-center items-center w-full h-32 bg-gray-50 rounded-lg border-2 border-gray-200 border-dashed cursor-pointer hover:bg-gray-100 transition-colors">
                                <div class="flex flex-col justify-center items-center pt-5 pb-6">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl mb-3"></i>
                                    <p class="text-sm text-gray-500">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-400">PNG, JPG or PDF (max. 5MB)</p>
                                </div>
                                <input id="complaint-attachment" type="file" name="complaint_attachment" class="hidden" multiple />
                            </label>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">You can upload multiple files as evidence for your complaint.</p>
                </div>                <div class="border-t border-gray-200 mt-8 pt-6 flex flex-col sm:flex-row gap-3 sm:justify-between items-center">
                    <div class="text-sm text-gray-500 mb-4 sm:mb-0">
                        <i class="fas fa-info-circle mr-1 text-primary-500"></i> 
                        All information submitted will be reviewed by the barangay officials.
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button 
                            type="button" 
                            class="py-3 px-6 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors duration-300 flex items-center justify-center" 
                            onclick="window.location.href='home-resident.php'"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </button>
                        
                        <button 
                            type="submit" 
                            class="py-3 px-8 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors duration-300 flex items-center justify-center"
                        >
                            <i class="fas fa-paper-plane mr-2"></i>
                            Submit Complaint
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        // File input preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('complaint-attachment');
            const fileLabel = document.querySelector('label[for="complaint-attachment"]');
              if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        let fileInfoHtml = '';
                        
                        if (this.files.length === 1) {
                            const fileName = this.files[0].name;
                            fileInfoHtml = `
                                <div class="flex flex-col justify-center items-center pt-5 pb-6">
                                    <i class="fas fa-file-alt text-primary-500 text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-700 font-medium">${fileName}</p>
                                    <p class="text-xs text-gray-400 mt-1">Click to change file</p>
                                </div>
                            `;
                        } else {
                            fileInfoHtml = `
                                <div class="flex flex-col justify-center items-center pt-5 pb-6">
                                    <i class="fas fa-file-alt text-primary-500 text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-700 font-medium">${this.files.length} files selected</p>
                                    <p class="text-xs text-gray-400 mt-1">Click to change files</p>
                                </div>
                            `;
                        }
                        
                        // Update the label to show file information
                        fileLabel.innerHTML = fileInfoHtml;
                    }
                });
            }
            
            // Support for drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                fileLabel.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                fileLabel.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                fileLabel.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                fileLabel.classList.add('border-primary-300', 'bg-primary-50');
            }
            
            function unhighlight() {
                fileLabel.classList.remove('border-primary-300', 'bg-primary-50');
            }
            
            fileLabel.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                if (fileInput) {
                    fileInput.files = e.dataTransfer.files;
                    
                    // Trigger change event manually
                    const event = new Event('change', { bubbles: true });
                    fileInput.dispatchEvent(event);
                }
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
