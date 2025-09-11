<?php
/**
 * Case Assistant Chatbot Component
 * Barangay Panducot Adjudication Management Information System
 * This file contains the HTML and JavaScript for the floating chatbot button
 */
?>
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
        
        // Simple bot response function
        function getBotResponse(message) {
            message = message.toLowerCase();
            
            if (message.includes('hello') || message.includes('hi') || message.includes('hey')) {
                return 'Hello! How can I assist you with case management today?';
            }
            else if (message.includes('case status') || message.includes('status')) {
                return 'You can view all case statuses in the "View Cases" section. Would you like me to help you navigate there?';
            }
            else if (message.includes('hearing') || message.includes('schedule')) {
                return 'To schedule a new hearing, go to "Appoint Hearing" under the Schedule menu. To view all upcoming hearings, check the calendar page.';
            }
            else if (message.includes('mediation') || message.includes('mediator')) {
                return 'Mediation sessions are conducted by trained members of the Lupong Tagapamayapa. You can schedule these through the appointment system.';
            }
            else if (message.includes('complaint') || message.includes('file') || message.includes('new complaint')) {
                return 'To add a new complaint to the system, click on "Add Complaints" from the menu or use the quick action on the dashboard.';
            }
            else if (message.includes('kp form') || message.includes('form') || message.includes('print form')) {
                return 'You can access KP Forms under "KP Forms" menu. You can view templates or print pre-filled forms for specific cases.';
            }
            else if (message.includes('report') || message.includes('statistics')) {
                return 'For detailed case reports and statistics, check "View Case Reports" or "View Complaints Report" under the Reports menu.';
            }
            else if (message.includes('thank')) {
                return 'You\'re welcome! Is there anything else I can help you with regarding case management?';
            }
            else {
                return 'I can help you with case management tasks. You can ask about schedules, complaints, cases, forms, or reports. What specific information do you need?';
            }
        }
    });
</script>
