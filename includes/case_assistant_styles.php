<?php
/**
 * Case Assistant Chatbot CSS
 * Barangay Panducot Adjudication Management Information System
 * This file contains all the necessary CSS for the chatbot
 */
?>
<style>
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
