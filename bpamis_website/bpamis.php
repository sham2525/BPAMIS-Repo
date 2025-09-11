<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPAMIS - Barangay Panducot Adjudication Management Information System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro&display=swap" rel="stylesheet">
    <?php include_once('../includes/case_assistant_styles.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            overflow-x: hidden;
        }

        .hero-section {
            /* background: linear-gradient(180deg, rgba(62, 131, 249, 0.68) 0%, rgba(96, 165, 250, 0.64) 60%, rgb(255, 255, 255)), url('brgyhall.png');
             */
            background-size: cover;
            background-position: center;
            height: 860px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .hero-section::before {
            /* display: none; */
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, rgba(62, 131, 249, 0.83) 0%, rgba(96, 165, 250, 0.64) 60%, rgb(255, 255, 255)), url('brgyhall.png');
            
            filter: blur(8px);
            z-index: 0;
            opacity: 1;
            
        }

        .hero-section .container {
            position: relative;
            z-index: 1;
        }

        .news-card {
            transition: transform 0.3s ease;
        }

        .news-card:hover {
            transform: translateY(-5px);
        }

        .service-card {
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

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

        .loader-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .loader {
            position: relative;
            width: 120px;
            height: 120px;
        }

        .loader-circle {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid transparent;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loader-circle:nth-child(2) {
            border-top-color: transparent;
            border-right-color: #2563eb;
            animation-duration: 0.8s;
        }

        .loader-circle:nth-child(3) {
            border-top-color: transparent;
            border-right-color: transparent;
            border-bottom-color: #2563eb;
            animation-duration: 0.6s;
        }

        .loader-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards 0.5s;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        .fade-out {
            opacity: 0;
            pointer-events: none;
        }

        /* Map container styles */
        .map-container {
            position: relative;
            transition: all 0.3s ease;
        }

        .map-container:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        #modalContent {
            transition: all 0.3s ease-out;
        }

        .scale-95 {
            transform: scale(0.95);
        }

        .scale-100 {
            transform: scale(1);
        }

        #appointmentModal {
            backdrop-filter: blur(4px);
        }

        #contactModal {
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        #contactModalContent {
            transition: all 0.3s ease-out;
        }

        .fade-in-element {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }

        .fade-in-element.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .stagger-fade-delay-1 {
            transition-delay: 0.2s;
        }

        .stagger-fade-delay-2 {
            transition-delay: 0.4s;
        }

        .stagger-fade-delay-3 {
            transition-delay: 0.6s;
        }

        .stagger-fade-delay-4 {
            transition-delay: 0.8s;
        }

        /* Update other sections to maintain consistency */
        #about-bpamis {
            background: linear-gradient(rgba(255, 255, 255, 0.45), rgba(255, 255, 255, 0.45)), url('Assets/Img/brgyhall.png');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        #featured-services {
            background: linear-gradient(rgba(255, 255, 255, 0.97), rgba(255, 255, 255, 0.97)), url('Assets/Img/brgyhall.png');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .service-details {
            margin-bottom: 2rem;
        }

        /* Icon Animation Styles */
        .service-icon {
            transition: all 0.5s ease;
            transform-origin: center;
        }

        /* Gavel Animation */
        .service-card:hover .fa-gavel {
            animation: hammerEffect 1s ease infinite;
        }

        @keyframes hammerEffect {
            0%,
            100% {
                transform: rotate(0deg);
            }

            50% {
                transform: rotate(-20deg);
            }
        }

        /* Document Animation */
        .service-card:hover .fa-file-alt {
            animation: floatFile 1s ease infinite;
        }

        @keyframes floatFile {
            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* Users Animation */
        .service-card:hover .fa-users {
            animation: popUsers 1s ease infinite;
        }

        @keyframes popUsers {
            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        /* Chevron Animation */
        .chevron-icon {
            transition: transform 0.3s ease;
        }

        .service-card:hover .chevron-icon {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        /* Hero Button Animations */
        .hero-btn {
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            transition: all 0.3s ease;
            padding: 1rem 2.5rem;
            font-size: 1rem;
            border-radius: 9999px;
            font-weight: 600;
        }

        .hero-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-100%) rotate(45deg);
            transition: transform 0.6s ease;
        }

        .hero-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .hero-btn:hover::before {
            transform: translateX(100%) rotate(45deg);
        }

        .hero-btn-primary {
            background: #2563eb;
            color: white;
        }

        .hero-btn-secondary {
            background: white;
            color: #2563eb;
        }

        .hero-btn-primary:hover {
            background: #1d4ed8;
        }

        .hero-btn-secondary:hover {
            background: #f8fafc;
        }

        .hero-btn:active {
            transform: translateY(-1px) scale(0.98);
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

        .contact-card {
        transition: transform 0.25s cubic-bezier(.4,2,.6,1), box-shadow 0.25s;
        will-change: transform, box-shadow;
}
        .contact-card:hover {
          transform: scale(1.01);
           box-shadow: 0 12px 32px 0 rgba(37,99,235,0.18);
           z-index: 2;
        }
        .contact-card .contact-icon {
           transition: transform 0.25s cubic-bezier(.4,2,.6,1);
}
         .contact-card:hover .contact-icon {
         transform: scale(1.18) rotate(-12deg);
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

        /* Learn More Button Styles */
        .learn-more-btn {
            position: relative;
            overflow: hidden;
        }

        .learn-more-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-100%) rotate(45deg);
            transition: transform 0.6s ease;
        }

        .learn-more-btn:hover::before {
            transform: translateX(100%) rotate(45deg);
        }

        .learn-more-btn:active {
            transform: translateY(-1px) scale(0.98);
        }

        /* Enhance the service details animation */
        .service-details {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }

        .service-details.show {
            max-height: 500px;
            opacity: 1;
        }

        /* Navigation Button Styles */
        .nav-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            z-index: 1;
        }

        .nav-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: 0.6s;
            z-index: -1;
        }

        .nav-btn:hover::before {
            transform: translateX(100%);
        }

        .nav-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .nav-btn-secondary:hover {
            color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
            background: rgba(37, 99, 235, 0.05);
        }

        .welcome-pill {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .hero-btn {
            padding: 1rem 2.5rem;
            font-size: 1rem;
            border-radius: 9999px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            border: 2px solid transparent;
            text-decoration: none;
        }

        .hero-btn-primary {
            background-color: white;
            color: #2563eb;
            border-color: white;
        }

        .hero-btn-primary:hover {
            background-color: rgba(255, 255, 255, 0.9);
            transform: translateY(-3px);
        }

        .hero-btn-secondary {
            background-color: transparent;
            color: white;
            border-color: white;
        }
        
        .hero-btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }

        /* New Feature Card Styles */
        .feature-card {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-out;
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .feature-card:hover, .feature-card.active {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border-color: transparent;
        }

        .feature-card .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .feature-card .icon-wrapper {
            background-color:#0c9ced; /* Teal 500 */
            color: white;
            border-radius: 0.75rem;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            transition: all 0.3s ease-out;
        }

        .feature-card:hover .icon-wrapper {
            transform: rotate(-15deg) scale(1.1);
        }

        .feature-card .tag {
            background-color: rgba(20, 184, 166, 0.1); /* Teal 500 with 10% opacity */
            color: #0c9ced; /* Teal 600 */
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .feature-card .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937; /* Gray 800 */
            margin-bottom: 0.5rem;
        }

        .feature-card .card-description {
            font-size: 1rem;
            color: #4b5563; /* Gray 600 */
            line-height: 1.6;
            flex-grow: 1; /* Pushes the learn more link down */
        }

        .feature-card .learn-more {
            margin-top: 1.5rem;
            font-weight: 400;
            color: #489a59; /* Teal 500 */
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            opacity: 0;
        }

        .feature-card:hover .learn-more, .feature-card.active .learn-more {
            opacity: 1;
        }

        .feature-card .learn-more .arrow {
            transition: transform 0.2s ease;
        }

        .feature-card:hover .learn-more .arrow {
            transform: translateX(4px);
        }

        /* New Service Icons Section Animation */
        .service-icon-anim {
            background: #dbeafe; /* bg-blue-100 */
            color: #2563eb;      /* text-blue-600 */
            transition: background 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
            box-shadow: 0 4px 16px 0 rgba(37,99,235,0.10);
        }
        .service-icon-anim:hover {
            background: #2563eb; /* bg-blue-600 */
            color: #fff;         /* text-white */
            box-shadow: 0 12px 32px 0 rgba(37,99,235,0.25);
            transform: scale(1.08);
        }

        /* Marquee Animation for Service Icons */
        .marquee-container {
            overflow: hidden;
            width: 100%;
            position: relative;
            padding: 2.5rem 0;
            white-space: nowrap;
        }
        .marquee-track {
            display: inline-flex;
            align-items: center;
            gap: 2.5rem;
            animation: marquee-move 40s linear infinite;
        }
        .marquee-container:hover .marquee-track {
            animation-play-state: paused;
        }
        @keyframes marquee-move {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .marquee-icon {
            background: #dbeafe;
            color: #2563eb;
            border-radius: 9999px;
            width: 5rem;
            height: 5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: 0 4px 16px 0 rgba(37,99,235,0.10);
            transition: box-shadow 0.3s, background 0.3s, color 0.3s, transform 0.3s;
        }
        .marquee-icon:hover {
            background: #2563eb;
            color: #fff;
            box-shadow: 0 12px 32px 0 rgba(37,99,235,0.25);
            transform: scale(1.08);
        }
        .marquee-label {
            margin-top: 0.5rem;
            font-size: 1rem;
            color:rgb(0, 0, 0);
            text-align: center;
            font-weight: 500;
        }

        .info-container {
            display: flex;
            align-items: center;
        }

        .info-icon {
            margin-left: 0.5rem;
            cursor: pointer;
        }

        .tooltip-content {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .bg-white-blue-white {
          background: linear-gradient(180deg, #fff 0%, #2563eb 40%, #fff 100%);
        }
    </style>
</head>

<body>
    <!-- Add this at the very top of the body -->
    <div class="loader-wrapper">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-circle"></div>
            <div class="loader-circle"></div>
            <img src="logo.png" alt="BPAMIS Logo" class="loader-logo">
        </div>
    </div>

    <?php include_once('includes/bpamis_nav.php'); ?>

    <!-- Hero Section -->
    <section class="hero-section flex items-center justify-center text-white text-center">
        <div class="container mx-auto px-4 flex flex-col items-center">
            <div class="welcome-pill" style="margin-bottom: 50px;">Welcome to BPAMIS</div>
            <h1 class="text-7xl font-bold max-w-4xl" style="margin-bottom: 30px;">Empowering Communities Digitally</h1>
            <p class="text-lg mb-8 max-w-2xl font-light">Enhancing Local Justice Delivery with the Barangay Panducot Adjudication Management Information System (BPAMIS)</p>
            <div class="flex justify-center gap-4">
                <a href="register.php" class="hero-btn hero-btn-primary">Get Started</a>
                <a href="about.php" class="hero-btn hero-btn-secondary">Learn More</a>
            </div>

            <!-- Demographics Card -->
            <div id="demographicsWrapper">
                <div class="grid grid-cols-1 md:grid-cols-4 rounded-lg shadow-lg overflow-hidden demographics-card demographics-collapsed" style="margin: 50px;">
                    <div class="text-center p-6 text-white" style="background: #2563eb;">
                        <div class="text-4xl font-bold mb-2" id="totalPopulation">0</div>
                        <p>Total Population</p>
                    </div>
                    <div class="text-center p-6 bg-white">
                        <div class="text-4xl font-bold text-indigo-900 mb-2" id="childrenCount">0</div>
                        <p class="text-gray-500">Children (0-17 years)</p>
                    </div>
                    <div class="text-center p-6 bg-white">
                        <div class="text-4xl font-bold text-indigo-900 mb-2" id="adultCount">0</div>
                        <p class="text-gray-500">Adults (18-59 years)</p>
                    </div>
                    <div class="text-center p-6 bg-white">
                        <div class="text-4xl font-bold text-indigo-900 mb-2" id="seniorCount">0</div>
                        <p class="text-gray-500">Senior Citizens</p>
                    </div>
                </div>
            </div>
            <!-- Chart Canvas -->
            <!-- <div class="w-full h-full min-h-[300px]">
                <canvas id="demographicsChart"></canvas>
            </div> -->
          
        </div>
    </section>

    <!-- Vision, Mission, Goals, Objectives Section -->
    <section class="py-20" style="background: linear-gradient(180deg, #fff 0%, #2563eb 50%, #fff 100%);">
      <div class="max-w-5xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
          <!-- Vision -->
          <div class="text-center bg-blue-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 fade-in-element stagger-fade-delay-1">
            <div class="absolute top-0 left-0 w-20 h-20 bg-teal-400 rounded-br-3xl"></div>
            <span class="relative z-10 mt-4 mb-2">
              <!-- Eye Icon -->
              <svg class="w-10 h-10 text-teal-500 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <ellipse cx="12" cy="12" rx="9" ry="5" stroke="currentColor" fill="none"/>
                <circle cx="12" cy="12" r="2" fill="currentColor"/>
              </svg>
            </span>
            <h3 class="text-2xl font-bold text-teal-600 mb-2 relative z-10">VISION</h3>
            <p class="text-gray-700 mb-4 relative z-10">A Barangay that cares for everyone, is united in raising the standard of living, and has a common goal of maintaining honesty, order, and peace to build a healthy community.</p>
          </div>
          <!-- Mission -->
          <div class="text-center bg-blue-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 fade-in-element stagger-fade-delay-1">
            <div class="absolute top-0 left-0 w-20 h-20 bg-yellow-400 rounded-br-3xl"></div>
            <span class="relative z-10 mt-4 mb-2">
              <!-- Target Icon -->
              <svg class="w-10 h-10 text-yellow-500 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke="currentColor" fill="none"/>
                <circle cx="12" cy="12" r="5" stroke="currentColor" fill="none"/>
                <circle cx="12" cy="12" r="2" fill="currentColor"/>
              </svg>
            </span>
            <h3 class="text-2xl font-bold text-yellow-600 mb-2 relative z-10">MISSION</h3>
            <p class="text-gray-700 mb-4 relative z-10">To unite all citizens with a common goal and bring everyone together in action toward the fulfillment of community projects related to the economic, developmental, and organizational needs of the barangay.</p>
          </div>
          <!-- Goals -->
          <div class="text-center bg-blue-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 fade-in-element stagger-fade-delay-2">
            <div class="absolute top-0 left-0 w-20 h-20 bg-blue-400 rounded-br-3xl"></div>
            <span class="relative z-10 mt-4 mb-2">
              <!-- Flag Icon -->
              <svg class="w-10 h-10 text-blue-500 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 22V4h16l-2 5 2 5H4" stroke="currentColor" fill="none"/>
              </svg>
            </span>
            <h3 class="text-2xl font-bold text-blue-600 mb-2 relative z-10">GOALS</h3>
            <p class="text-gray-700 mb-4 relative z-10">To provide a clear and systematic plan for the citizens of Barangay Panducot to follow in case a disaster occurs.</p>
          </div>
          <!-- Objectives -->
          <div class="text-center bg-blue-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 fade-in-element stagger-fade-delay-2">
            <div class="absolute top-0 left-0 w-20 h-20 bg-purple-400 rounded-br-3xl"></div>
            <span class="relative z-10 mt-4 mb-2">
              <!-- List Icon -->
              <svg class="w-10 h-10 text-purple-500 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="6" cy="7" r="2" fill="currentColor"/>
                <circle cx="6" cy="12" r="2" fill="currentColor"/>
                <circle cx="6" cy="17" r="2" fill="currentColor"/>
                <line x1="10" y1="7" x2="20" y2="7" stroke="currentColor"/>
                <line x1="10" y1="12" x2="20" y2="12" stroke="currentColor"/>
                <line x1="10" y1="17" x2="20" y2="17" stroke="currentColor"/>
              </svg>
            </span>
            <h3 class="text-2xl font-bold text-purple-600 mb-2 relative z-10">OBJECTIVES</h3>
            <ul class="text-gray-700 text-left max-w-xs mx-auto list-disc list-inside space-y-1 relative z-10">
              <li>Enhance Community Preparedness</li>
              <li>Promote Environmental Sustainability</li>
              <li>Improve Infrastructure Resilience</li>
              <li>Foster Strong Community Engagement</li>
              <li>Develop Effective Communication Systems</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Services -->
    <section class="py-20 bg-white-blue-white">
        <div class="container mx-auto px-4">
            <h2 class="text-6xl font-bold text-center mb-12 fade-in-element">Our Services</h2>
            <div class="text-center max-w-2xl mx-auto mb-8 fade-in-element">
                    <p class="text-lg text-gray-600">Innovative digital solutions tailored for efficient barangay adjudication</p>
                </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1: Barangay Cases -->
                <div class="feature-card fade-in-element stagger-fade-delay-1">
                    <div class="card-top">
                        <div class="icon-wrapper">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <div class="tag">Dispute Resolution</div>
                    </div>
                    <h3 class="card-title">Barangay Cases</h3>
                    <p class="card-description">Efficient management and resolution of barangay disputes, from filing to automated hearing schedules and digital documentation.</p>
                    <a href="services.php" class="learn-more">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right arrow"></i>
                    </a>
                </div>

                <!-- Card 2: Document Processing -->
                <div class="feature-card fade-in-element stagger-fade-delay-2">
                    <div class="card-top">
                        <div class="icon-wrapper">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <div class="tag">Tech-Supported</div>
                    </div>
                    <h3 class="card-title">Document Processing</h3>
                    <p class="card-description">Easy and secure online access to barangay certificates and other official documents with real-time status updates.</p>
                    <a href="services.php" class="learn-more">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right arrow"></i>
                    </a>
                </div>

                <!-- Card 3: Resident Services -->
                <div class="feature-card fade-in-element stagger-fade-delay-3">
                    <div class="card-top">
                        <div class="icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="tag">Community-Centered</div>
                    </div>
                    <h3 class="card-title">Resident Services</h3>
                    <p class="card-description">Manage resident profiles, receive community announcements, and schedule appointments, all in one place.</p>
                    <a href="services.php" class="learn-more">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right arrow"></i>
                    </a>
                </div>
            </div>
            <!-- New Service Icons Section -->
            <div class="mt-16">
                <div class="text-center max-w-2xl mx-auto mb-8">
                    <p class="text-lg text-white">BPAMIS offers a wide range of digital services to make barangay processes easier, faster, and more reliable for everyone.</p>
                </div>
                <div style="background: linear-gradient(135deg, rgb(255, 255, 255) 0%, rgb(255, 255, 255) 100%); padding: 2.5rem 1.5rem; border-radius: 2rem;" class="shadow-md">
                    <div class="marquee-container">
                        <div class="marquee-track">
                            <!-- First set of icons -->
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-gavel"></i></div>
                                <span class="marquee-label">Case Filing</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-file-alt"></i></div>
                                <span class="marquee-label">Document Requests</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-calendar-alt"></i></div>
                                <span class="marquee-label">Appointment Scheduling</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-bullhorn"></i></div>
                                <span class="marquee-label">Announcements</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-users"></i></div>
                                <span class="marquee-label">Resident Profiles</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-shield-alt"></i></div>
                                <span class="marquee-label">Clearance & Security</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-search-location"></i></div>
                                <span class="marquee-label">Track Case</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-chart-bar"></i></div>
                                <span class="marquee-label">Generate Report</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-robot"></i></div>
                                <span class="marquee-label">Case Assistant</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-user-shield"></i></div>
                                <span class="marquee-label">Role-Based Login</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-calendar"></i></div>
                                <span class="marquee-label">Calendar View</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-user-secret"></i></div>
                                <span class="marquee-label">Guest Access</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-paperclip"></i></div>
                                <span class="marquee-label">File Attachment</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-file-signature"></i></div>
                                <span class="marquee-label">KP Form Access</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-chart-pie"></i></div>
                                <span class="marquee-label">Monthly Statistics</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-user-tie"></i></div>
                                <span class="marquee-label">Captain Tools</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-user-cog"></i></div>
                                <span class="marquee-label">Secretary Tools</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-balance-scale"></i></div>
                                <span class="marquee-label">Lupon Tools</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-sync-alt"></i></div>
                                <span class="marquee-label">Status Updates</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-file-upload"></i></div>
                                <span class="marquee-label">Complaint Filing</span>
                            </div>
                            <!-- Duplicate set for seamless loop -->
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-gavel"></i></div>
                                <span class="marquee-label">Case Filing</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-file-alt"></i></div>
                                <span class="marquee-label">Document Requests</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-calendar-alt"></i></div>
                                <span class="marquee-label">Appointment Scheduling</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-bullhorn"></i></div>
                                <span class="marquee-label">Announcements</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-users"></i></div>
                                <span class="marquee-label">Resident Profiles</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-shield-alt"></i></div>
                                <span class="marquee-label">Clearance & Security</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-search-location"></i></div>
                                <span class="marquee-label">Track Case</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-chart-bar"></i></div>
                                <span class="marquee-label">Generate Report</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-robot"></i></div>
                                <span class="marquee-label">Case Assistant</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-user-shield"></i></div>
                                <span class="marquee-label">Role-Based Login</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-calendar"></i></div>
                                <span class="marquee-label">Calendar View</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-user-secret"></i></div>
                                <span class="marquee-label">Guest Access</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-paperclip"></i></div>
                                <span class="marquee-label">File Attachment</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-file-signature"></i></div>
                                <span class="marquee-label">KP Form Access</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-chart-pie"></i></div>
                                <span class="marquee-label">Monthly Statistics</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-user-tie"></i></div>
                                <span class="marquee-label">Captain Tools</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-user-cog"></i></div>
                                <span class="marquee-label">Secretary Tools</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-balance-scale"></i></div>
                                <span class="marquee-label">Lupon Tools</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-sync-alt"></i></div>
                                <span class="marquee-label">Status Updates</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="marquee-icon"><i class="fas fa-file-upload"></i></div>
                                <span class="marquee-label">Complaint Filing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       
    <!-- BPAMIS Users and Roles -->
    <div class="container mx-auto px-4">
         <!-- BPAMIS Users -->
         <h3 class="text-6xl font-semibold mb-8 text-gray-800 text-center fade-in-element" style="margin-top: 100px; color: white;">BPAMIS Users and Roles</h3>
         <div class="text-center max-w-2xl mx-auto mb-8 fade-in-element">
                    <p class="text-lg text-white">This section outlines the different user roles within BPAMIS and their corresponding responsibilities and access levels.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Barangay Secretary Card -->
                <div class="team-card group">
                    <div class="relative bg-white rounded-xl shadow-lg p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-purple-100 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-user-tie text-3xl text-blue-600"></i>
                            </div>
                            <h4 class="text-xl font-bold text-center text-gray-800 mb-4">Barangay Secretary</h4>
                            <div class="space-y-3 text-gray-600">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                    <span>Manages case documentation and resident records</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                    <span>Schedules and coordinates hearings</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                    <span>Primary administrator of BPAMIS platform</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barangay Captain Card -->
                <div class="team-card group">
                    <div class="relative bg-white rounded-xl shadow-lg p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                        <div class="absolute -inset-1 bg-gradient-to-r from-green-100 to-blue-100 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-user-shield text-3xl text-green-600"></i>
                            </div>
                            <h4 class="text-xl font-bold text-center text-gray-800 mb-4">Barangay Captain</h4>
                            <div class="space-y-3 text-gray-600">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-green-600 mt-1"></i>
                                    <span>Oversees case resolutions and approvals</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-green-600 mt-1"></i>
                                    <span>Approves official documents</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-green-600 mt-1"></i>
                                    <span>Monitors barangay activities through BPAMIS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lupon Tagapamayapa Card -->
                <div class="team-card group">
                    <div class="relative bg-white rounded-xl shadow-lg p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                        <div class="absolute -inset-1 bg-gradient-to-r from-purple-100 to-pink-100 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-balance-scale text-3xl text-purple-600"></i>
                            </div>
                            <h4 class="text-xl font-bold text-center text-gray-800 mb-4">Lupon Tagapamayapa</h4>
                            <div class="space-y-3 text-gray-600">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-purple-600 mt-1"></i>
                                    <span>Handles dispute mediation processes</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-purple-600 mt-1"></i>
                                    <span>Accesses and manages case files</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-purple-600 mt-1"></i>
                                    <span>Updates resolution status through BPAMIS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Residents Card -->
                <div class="team-card group">
                    <div class="relative bg-white rounded-xl shadow-lg p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                        <div class="absolute -inset-1 bg-gradient-to-r from-green-100 to-blue-100 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-house-user text-3xl text-green-600"></i>
                            </div>
                            <h4 class="text-xl font-bold text-center text-gray-800 mb-4">Residents</h4>
                            <div class="space-y-3 text-gray-600">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-green-600 mt-1"></i>
                                    <span>File complaints and track case status online</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-green-600 mt-1"></i>
                                    <span>Request and access official documents</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-green-600 mt-1"></i>
                                    <span>Manage personal BPAMIS accounts</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- External Complainant Card -->
                 <div class="team-card group">
                    <div class="relative bg-white rounded-xl shadow-lg p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-purple-100 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-user-friends text-3xl text-black-600"></i>
                            </div>
                            <h4 class="text-xl font-bold text-center text-gray-800 mb-4">External Complainant</h4>
                            <div class="space-y-3 text-gray-600">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-amber-600 mt-1"></i>
                                    <span>File complaints against Panducot residents</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-amber-600 mt-1"></i>
                                    <span>Monitor case progress remotely</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-amber-600 mt-1"></i>
                                    <span>Access case-related notifications</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Why Use BPAMIS Section -->
    <section class="py-20 bg-white-blue-white">
        <div class="container mx-auto px-4">
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 fade-in-element">
                <h2 class="text-6xl font-bold mb-4 text-white">Why Use BPAMIS?</h2>
                <p class="mt-4 text-white">The Barangay Panducot Adjudication Management Information System
                    modernizes our justice system by bringing transparency, accuracy, and efficiency to case management.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-12">
                <!-- Digital Case Records -->
                <div
                    class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 fade-in-element stagger-fade-delay-1">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-folder-open text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Digital Case Records</h3>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Centralized digital case storage</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Quick access to case histories</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Enhanced data security</span>
                        </li>
                    </ul>
                </div>

                <!-- Online Complaints -->
                <div
                    class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 fade-in-element stagger-fade-delay-2">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-laptop text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Online Complaints</h3>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>File complaints remotely</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Real-time case updates</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Reduced waiting time</span>
                        </li>
                    </ul>
                </div>

                <!-- Live Dashboard -->
                <div
                    class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 fade-in-element stagger-fade-delay-3">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Live Dashboard</h3>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Track active cases</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Generate instant reports</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Monitor resolution rates</span>
                        </li>
                    </ul>
                </div>

                <!-- Case Management -->
                <div
                    class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 fade-in-element stagger-fade-delay-4">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-tasks text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Case Management</h3>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Smart case categorization</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Automated scheduling</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Step-by-step tracking</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Data Security and Privacy Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 fade-in-element">
                <h2 class="text-6xl font-bold text-gray-800 mb-4">Keep Your Data Safe with BPAMIS</h2>
                <p class="mt-4 text-gray-600">Our platform ensures the security of Barangay Panducot's records while
                    maintaining transparency and trust in local governance.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                <!-- Legal Framework -->
                <div class="bg-blue-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 fade-in-element stagger-fade-delay-1">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-balance-scale text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Legal Framework</h3>
                    <ul class="space-y-4">
                        <!-- Data Privacy Act -->
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-200 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-shield-alt text-blue-600"></i>
                            </div>
                            <div>
                                <div class="flex items-center">
                                    <p class="font-semibold text-gray-800 mr-2">Data Privacy Act</p>
                                    <div class="relative info-container">
                                        <span class="info-icon text-purple-500 bg-purple-100 rounded-full flex items-center justify-center w-6 h-6 cursor-pointer transition-colors duration-200 hover:bg-purple-500 hover:text-white">
                                            <i class="fas fa-info text-xs pointer-events-none"></i>
                                        </span>
                                        <div class="tooltip-content absolute hidden bottom-full mb-3 w-72 left-1/2 -translate-x-1/2 z-20">
                                           
                                                <h4 class="font-bold text-lg mb-2 text-gray-800 flex items-center">
                                                    <span class="text-purple-500 bg-purple-100 rounded-full flex items-center justify-center w-6 h-6 mr-2">
                                                        <i class="fas fa-info text-xs"></i>
                                                    </span>
                                                    Helpful tip
                                                </h4>
                                                <p class="text-sm text-gray-600">
                                                   You can visit <a href="https://privacy.gov.ph/data-privacy-act/" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">this link</a> for more details.
                                                </p>
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 w-4 h-4">
                                                    <div class="w-full h-full bg-white transform rotate-45 -mt-2 shadow-inner"></div>
                                                </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">Protected under RA 10173</p>
                            </div>
                        </li>
                        <!-- Cybercrime Prevention Act -->
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-200 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-lock text-blue-600"></i>
                            </div>
                            <div>
                                <div class="flex items-center">
                                    <p class="font-semibold text-gray-800 mr-2">Cybercrime Prevention Act</p>
                                    <div class="relative info-container">
                                        <span class="info-icon text-purple-500 bg-purple-100 rounded-full flex items-center justify-center w-6 h-6 cursor-pointer transition-colors duration-200 hover:bg-purple-500 hover:text-white">
                                            <i class="fas fa-info text-xs pointer-events-none"></i>
                                        </span>
                                        <div class="tooltip-content absolute hidden bottom-full mb-3 w-72 left-1/2 -translate-x-1/2 z-20">
                                            
                                                <h4 class="font-bold text-lg mb-2 text-gray-800 flex items-center">
                                                    <span class="text-purple-500 bg-purple-100 rounded-full flex items-center justify-center w-6 h-6 mr-2">
                                                        <i class="fas fa-info text-xs"></i>
                                                    </span>
                                                    Helpful tip
                                                </h4>
                                                <p class="text-sm text-gray-600">
                                                   You can visit <a href="https://www.officialgazette.gov.ph/2012/09/12/republic-act-no-10175/" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">this link</a> for more details.
                                                </p>
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 w-4 h-4">
                                                    <div class="w-full h-full bg-white transform rotate-45 -mt-2 shadow-inner"></div>
                                                </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">Secured under RA 10175</p>
                            </div>
                        </li>
                        <!-- Local Government Code -->
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-200 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-landmark text-blue-600"></i>
                            </div>
                            <div>
                                <div class="flex items-center">
                                    <p class="font-semibold text-gray-800 mr-2">Local Government Code</p>
                                    <div class="relative info-container">
                                        <span class="info-icon text-purple-500 bg-purple-100 rounded-full flex items-center justify-center w-6 h-6 cursor-pointer transition-colors duration-200 hover:bg-purple-500 hover:text-white">
                                            <i class="fas fa-info text-xs pointer-events-none"></i>
                                        </span>
                                        <div class="tooltip-content absolute hidden bottom-full mb-3 w-72 left-1/2 -translate-x-1/2 z-20">
                                           
                                                <h4 class="font-bold text-lg mb-2 text-gray-800 flex items-center">
                                                    <span class="text-purple-500 bg-purple-100 rounded-full flex items-center justify-center w-6 h-6 mr-2">
                                                        <i class="fas fa-info text-xs"></i>
                                                    </span>
                                                    Helpful tip
                                                </h4>
                                                <p class="text-sm text-gray-600">
                                                   You can visit <a href="https://www.officialgazette.gov.ph/1991/10/10/republic-act-no-7160/" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">this link</a> for more details.
                                                </p>
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 w-4 h-4">
                                                    <div class="w-full h-full bg-white transform rotate-45 -mt-2 shadow-inner"></div>
                                                </div>
                                         
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">Compliant with RA 7160</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Security Features -->
                <div
                    class="bg-blue-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 fade-in-element stagger-fade-delay-2">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Security Features</h3>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>End-to-end data encryption</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Role-based access control</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Secure cloud storage</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Data backup and recovery</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Two-factor authentication</span>
                        </li>
                    </ul>
                </div>

                <!-- Trust & Transparency -->
                <div class="bg-blue-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 fade-in-element stagger-fade-delay-3">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-handshake text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Trust & Transparency</h3>
                    <div class="space-y-4 text-gray-600">
                        <p class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Registered with National Privacy Commission</span>
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Regular security audits and updates</span>
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Transparent case management</span>
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                            <span>Community-focused governance</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="py-20 bg-gradient-to-b from-white to-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-6xl font-bold text-center mb-12 text-black fade-in-element">Quick Links</h2>
                <div class="text-center max-w-2xl mx-auto mb-8 fade-in-element">
                    <p class="text-lg text-white">Easily access scheduling, support, and answers to common questions through these helpful quick links.</p>
                </div>
                <div class="max-w-3xl mx-auto"> <!-- Added container with max width and auto margins -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center"> <!-- Changed to 3 columns -->
                    <a href="#" onclick="openModal(event)"
                        class="bg-blue-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 fade-in-element stagger-fade-delay-1">
                        <i class="fas fa-calendar-alt text-2xl text-blue-600 mb-3"></i>
                        <p>Schedule Appointment</p>
                    </a>
                    <a href="#" onclick="openContactModal(event)"
                        class="bg-blue-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 fade-in-element stagger-fade-delay-2">
                        <i class="fas fa-phone-alt text-2xl text-blue-600 mb-3"></i>
                        <p>Contact BPAMIS</p>
                    </a>
                    <a href="https://www.dilg.gov.ph/faqs/" target="_blank" rel="noopener noreferrer"
                        class="bg-blue-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 fade-in-element stagger-fade-delay-3">
                        <i class="fas fa-question-circle text-2xl text-blue-600 mb-3"></i>
                        <p>FAQs</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

   <!-- Footer -->
<footer class="bg-gray-800">
    <div class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-12">
            
            <!-- Column 1: Company -->
            <div class="col-span-1">
                <h4 class="text-sm font-semibold text-white tracking-wider uppercase mb-5">Company</h4>
                <ul class="space-y-5">
                    <li><a href="#" class="text-gray-500 hover:text-gray-900 transition-colors">About</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-gray-900 transition-colors">Contact Us</a></li>
                </ul>
            </div>

            <!-- Column 2: Resources -->
            <div class="col-span-1">
                <h4 class="text-sm font-semibold text-white tracking-wider uppercase mb-5">Resources</h4>
                <ul class="space-y-5">
                    <li><a href="#" class="text-gray-500 hover:text-gray-900 transition-colors">User Guide</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-gray-900 transition-colors">FAQs</a></li>
                </ul>
            </div>

            <!-- Column 3: Legal -->
            <div class="col-span-1">
                <h4 class="text-sm font-semibold text-white tracking-wider uppercase mb-5">Legal</h4>
                <ul class="space-y-5">
                    <li><a href="#" class="text-gray-500 hover:text-gray-900 transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-gray-900 transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-gray-900 transition-colors">Cookies</a></li>
                </ul>
            </div>

            <!-- Column 4: Follow Us -->
             <div class="col-span-1">
                <h4 class="text-sm font-semibold text-white tracking-wider uppercase mb-5">Follow Us</h4>
                <ul class="space-y-5">
                    <li><a href="#" class="text-gray-500 hover:text-gray-900 transition-colors">Facebook</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-gray-900 transition-colors">Twitter</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-gray-900 transition-colors">Instagram</a></li>
                </ul>
            </div>

            <!-- Subscribe Column -->
            <div class="col-span-2">
                <h4 class="text-sm font-semibold text-white tracking-wider uppercase mb-5">Subscribe to our newsletter</h4>
                <p class="text-gray-500 text-sm mb-4">The latest news, articles, and resources, sent to your inbox weekly.</p>
                <form class="flex">
                    <input type="email" placeholder="Enter your email" class="w-full px-4 py-2 border border-gray-300 rounded-l-md focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 transition-colors">Subscribe</button>
                </form>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="mt-16 pt-8 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center">
            <p class="text-gray-400 text-sm">&copy; <?php echo date('Y'); ?> BPAMIS. All Rights Reserved.</p>
            <div class="flex space-x-6 mt-4 sm:mt-0">
                <a href="#" class="text-gray-400 hover:text-gray-500"><i class="fab fa-facebook"></i></a>
                <a href="#" class="text-gray-400 hover:text-gray-500"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-gray-400 hover:text-gray-500"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-gray-400 hover:text-gray-500"><i class="fab fa-github"></i></a>
                <a href="#" class="text-gray-400 hover:text-gray-500"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
</footer>
    <!-- Add this modal markup just before the closing </body> tag -->
    <div id="appointmentModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 transform transition-all opacity-0 scale-95"
            id="modalContent">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">Schedule Appointment</h3>
                    <button class="text-gray-400 hover:text-gray-600" onclick="closeModal()">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="appointmentForm" class="space-y-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Purpose of Visit</label>
                        <select
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Select purpose</option>
                            <option value="document">Document Request</option>
                            <option value="complaint">File a Complaint</option>
                            <option value="mediation">Mediation Session</option>
                            <option value="clearance">Barangay Clearance</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Preferred Date</label>
                        <input type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Preferred Time</label>
                        <select
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Select time</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="16:00">4:00 PM</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Additional Notes</label>
                        <textarea
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            rows="3" placeholder="Any additional information..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium"
                            onclick="closeModal()">Cancel</button>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Contact Modal -->
    <div id="contactModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center backdrop-blur-[4px]">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 transform transition-all opacity-0 scale-95"
            id="contactModalContent">
            <div class="grid md:grid-cols-2">
                <!-- Left side - Contact Information -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-8 text-white rounded-l-lg">
                    <h3 class="text-2xl font-bold mb-6">Get in Touch</h3>
                    <p class="mb-8">We'd love to hear from you. Send us a message and we'll respond as soon as possible.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="bg-white/10 p-3 rounded-lg">
                                <i class="fas fa-map-marker-alt text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold">Address</h4>
                                <p class="text-blue-100">Panducot, Calumpit, Bulacan</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="bg-white/10 p-3 rounded-lg">
                                <i class="fas fa-phone text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold">Phone</h4>
                                <p class="text-blue-100">+63 912 345 6789</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="bg-white/10 p-3 rounded-lg">
                                <i class="fas fa-envelope text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold">Email</h4>
                                <p class="text-blue-100">info@bpamis.gov.ph</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side - Contact Form -->
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Send Message</h3>
                        <button class="text-gray-400 hover:text-gray-600" onclick="closeContactModal()">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="contactForm" class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required placeholder="Juan Dela Cruz">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="email"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required placeholder="juan@example.com">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Subject</label>
                            <div class="relative">
                                <i class="fas fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <select
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                                    <option value="">Select a subject</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="technical">Technical Support</option>
                                    <option value="documents">Document Request</option>
                                    <option value="complaint">File a Complaint</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Message</label>
                            <div class="relative">
                                <i class="fas fa-comment absolute left-3 top-3 text-gray-400"></i>
                                <textarea rows="4"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required placeholder="How can we help you?"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium"
                                onclick="closeContactModal()">Cancel</button>
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Send
                                Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

  

    <!-- Add this JavaScript before the closing body tag -->
    <script>
        function toggleCaseAssistant() {
            const frame = document.getElementById('caseAssistantFrame');
            frame.classList.toggle('hidden');
            
            if (!frame.classList.contains('hidden')) {
                // Show animation
                frame.style.opacity = '0';
                frame.style.transform = 'translateY(20px) scale(0.95)';
                setTimeout(() => {
                    frame.style.opacity = '1';
                    frame.style.transform = 'translateY(0) scale(1)';
                }, 50);
            }
        }

        // Close case assistant when clicking outside
        document.addEventListener('click', function(event) {
            const frame = document.getElementById('caseAssistantFrame');
            const button = document.getElementById('chatbotButton');
            
            if (!frame.contains(event.target) && !button.contains(event.target) && !frame.classList.contains('hidden')) {
                toggleCaseAssistant();
            }
        });

        // Prevent clicks inside the iframe from closing it
        document.getElementById('caseAssistantFrame').addEventListener('click', function(event) {
            event.stopPropagation();
        });
    </script>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
        card.addEventListener('click', function() {
            const container = this.closest('.service-container');
            const details = container.querySelector('.service-details');
            const chevron = this.querySelector('.fa-chevron-down');
            
            // Toggle the details visibility
            details.classList.toggle('hidden');
            details.classList.toggle('show');
            
            // Rotate the chevron icon
            chevron.style.transform = details.classList.contains('hidden') 
                ? 'rotate(0deg)' 
                : 'rotate(180deg)';
        });
    });

    // Prevent button click from triggering card collapse
    const learnMoreBtns = document.querySelectorAll('.learn-more-btn');
    learnMoreBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    });
});

        document.addEventListener('DOMContentLoaded', function () {
            // Increased loading time to 3.5 seconds total
            setTimeout(() => {
                const loader = document.querySelector('.loader-wrapper');
                loader.classList.add('fade-out');

                // Increased fade-out transition to 1 second
                setTimeout(() => {
                    loader.remove();
                }, 1000); // Increased from 500ms to 1000ms
            }, 2500); // Increased from 2000ms to 2500ms
        });

        // Optional: Hide loader when all content is fully loaded
        window.addEventListener('load', function () {
            const loader = document.querySelector('.loader-wrapper');
            if (loader) {
                loader.classList.add('fade-out');
                setTimeout(() => {
                    loader.remove();
                }, 1000); // Increased fade-out time
            }
        });

        // Initialize and add the map
        function initMap() {
            // The location of Brgy Panducot (replace with actual coordinates)
            const panducot = { lat: 14.9144, lng: 120.7674 }; // Replace with actual coordinates

            // The map, centered at Brgy Panducot
            const map = new google.Maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: panducot,
                mapId: 'YOUR_MAP_ID', // Optional: for styled maps
                streetViewControl: true,
                mapTypeControl: true,
                fullscreenControl: true,
            });

            // The marker, positioned at Brgy Panducot
            const marker = new google.Maps.Marker({
                position: panducot,
                map: map,
                title: "Barangay Panducot"
            });
        }

        function openModal(event) {
            event.preventDefault();
            const modal = document.getElementById('appointmentModal');
            const modalContent = document.getElementById('modalContent');

            modal.classList.remove('hidden');
            // Trigger reflow
            void modalContent.offsetWidth;
            modalContent.classList.remove('opacity-0', 'scale-95');
            modalContent.classList.add('opacity-100', 'scale-100');
        }

        function closeModal() {
            const modal = document.getElementById('appointmentModal');
            const modalContent = document.getElementById('modalContent');

            modalContent.classList.remove('opacity-100', 'scale-100');
            modalContent.classList.add('opacity-0', 'scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('appointmentForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // Add your form submission logic here
            alert('Appointment scheduled successfully!');
            closeModal();
        });

        function openContactModal(event) {
            event.preventDefault();
            const modal = document.getElementById('contactModal');
            const modalContent = document.getElementById('contactModalContent');

            modal.classList.remove('hidden');
            // Trigger reflow
            void modalContent.offsetWidth;
            modalContent.classList.remove('opacity-0', 'scale-95');
            modalContent.classList.add('opacity-100', 'scale-100');
        }

        function closeContactModal() {
            const modal = document.getElementById('contactModal');
            const modalContent = document.getElementById('contactModalContent');

            modalContent.classList.remove('opacity-100', 'scale-100');
            modalContent.classList.add('opacity-0', 'scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('contactForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // Add your form submission logic here
            alert('Message sent successfully!');
            closeContactModal();
        });

        // Close modal when clicking outside
        document.getElementById('appointmentModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.getElementById('contactModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeContactModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Intersection Observer for fade-in animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1, // Trigger when 10% of the element is visible
                rootMargin: '0px 0px -50px 0px' // Slightly offset trigger point
            });

            // Observe all fade-in elements
            document.querySelectorAll('.fade-in-element').forEach(element => {
                observer.observe(element);
            });
        });

        // Add this before the demographics chart initialization
function animateValue(obj, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = Math.floor(progress * (end - start) + start).toLocaleString();
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Create an Intersection Observer for the demographics section
const observerDemographics = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // Start animations when section is visible
            const totalPopulation = document.getElementById('totalPopulation');
            const childrenCount = document.getElementById('childrenCount');
            const adultCount = document.getElementById('adultCount');
            const seniorCount = document.getElementById('seniorCount');

            animateValue(totalPopulation, 0, 3318, 2000);
            animateValue(childrenCount, 0, 677, 2000);
            animateValue(adultCount, 0, 1973, 2000);
            animateValue(seniorCount, 0, 522, 2000);
          
            // Disconnect observer after animation starts
            observerDemographics.disconnect();
        }
    });
}, {
    threshold: 0.2
});

// Start observing the demographics section
document.addEventListener('DOMContentLoaded', function() {
    const demographicsSection = document.querySelector('.demographics-card');
    if (demographicsSection) {
        observerDemographics.observe(demographicsSection);
    }
});

    </script>

    <!-- Google Maps JavaScript API -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap">
    </script>

    <?php include_once('../includes/case_assistant.php'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const infoContainers = document.querySelectorAll('.info-container');

            infoContainers.forEach(container => {
                const icon = container.querySelector('.info-icon');
                const tooltip = container.querySelector('.tooltip-content');

                if (icon && tooltip) {
                    icon.addEventListener('click', (event) => {
                        event.stopPropagation();
                        // Hide all other tooltips first
                        document.querySelectorAll('.tooltip-content').forEach(t => {
                            if (t !== tooltip) {
                                t.classList.add('hidden');
                            }
                        });
                        tooltip.classList.toggle('hidden');
                    });
                }
            });

            // Close tooltips when clicking outside
            window.addEventListener('click', function (e) {
                document.querySelectorAll('.info-container').forEach(container => {
                    if (!container.contains(e.target)) {
                        const tooltip = container.querySelector('.tooltip-content');
                        if (tooltip) {
                            tooltip.classList.add('hidden');
                        }
                    }
                });
            });
        });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      var wrapper = document.getElementById('demographicsWrapper');
      var card = wrapper ? wrapper.querySelector('.demographics-card') : null;
      if (wrapper && card) {
        wrapper.addEventListener('mouseenter', function() {
          card.classList.remove('demographics-collapsed');
        });
        wrapper.addEventListener('mouseleave', function() {
          card.classList.add('demographics-collapsed');
        });
      }
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Existing fade-in logic for fade-in-element
      var fadeEls = document.querySelectorAll('.fade-in-element, .slide-up-element, .flip-in-element');
      function checkFadeIn() {
        var windowBottom = window.innerHeight + window.scrollY;
        fadeEls.forEach(function(el) {
          var rect = el.getBoundingClientRect();
          var elTop = rect.top + window.scrollY;
          if (windowBottom > elTop + 60) {
            el.classList.add('visible');
          }
        });
      }
      window.addEventListener('scroll', checkFadeIn);
      checkFadeIn();
    });
    </script>
</body>

</html>