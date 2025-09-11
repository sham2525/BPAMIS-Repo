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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        body {
            color: black;
        }

        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('brgyhall.png');
            background-size: cover;
            background-position: center;
            height: 600px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
            filter: blur(8px);
            -webkit-filter: blur(8px);
            z-index: 0;
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
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            to { opacity: 1; }
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

        .fade-in-element {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }

        .fade-in-element.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .stagger-fade-delay-1 { transition-delay: 0.2s; }
        .stagger-fade-delay-2 { transition-delay: 0.4s; }
        .stagger-fade-delay-3 { transition-delay: 0.6s; }
        .stagger-fade-delay-4 { transition-delay: 0.8s; }

        .about-hero {
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('brgyhall.png') center/cover no-repeat;
            filter: blur(8px);
            -webkit-filter: blur(8px);
            transform: scale(1.1);
            z-index: 0;
        }

        .about-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            /* background: rgba(255, 255, 255, 0.9); Changed from gradient to solid white with opacity */
            z-index: 1;
        }

        .content-wrapper {
            position: relative;
            z-index: 2; /* Increased z-index to appear above both pseudo-elements */
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(229, 231, 235, 0.5);
            opacity: 0;
            animation: fadeIn 0.8s ease-out 0.5s forwards;
        }

        .text-block {
            line-height: 1.8;
            color:rgba(39, 39, 39, 0.83);
        }

        /* Add these styles in your existing <style> tag */
        .paragraph-wrapper {
            position: relative;
            padding-left: 80px; /* Space for icon */
            margin-bottom: 2rem;
        }

        .section-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.8s ease-out forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .section-icon:nth-child(1) { animation-delay: 0.2s; }
        .section-icon:nth-child(2) { animation-delay: 0.4s; }
        .section-icon:nth-child(3) { animation-delay: 0.6s; }
        .section-icon:nth-child(4) { animation-delay: 0.8s; }
        .section-icon:nth-child(5) { animation-delay: 1.0s; }
        .section-icon:nth-child(6) { animation-delay: 1.2s; }

        .section-icon i {
            font-size: 24px;
            color: #2563eb;
        }

        @keyframes gradientFlow {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .text-gradient {
            background-size: 200% auto;
            animation: gradientFlow 5s ease infinite;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 1s ease-out forwards;
        }

        .text-transparent.bg-clip-text {
            -webkit-background-clip: text;
            background-clip: text;
        }

        @keyframes logoPulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        .logo-pulse {
            animation: logoPulse 2s ease-in-out infinite;
            transform-origin: center;
            transition: all 0.3s ease;
        }

        .logo-pulse:hover {
            animation-play-state: paused;
            transform: scale(1.15);
        }

        .glow-effect {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.1);
            filter: blur(8px);
            animation: glowPulse 3s ease-in-out infinite;
            position: absolute;
            inset: 0;
        }

        @keyframes glowPulse {
            0% {
                transform: scale(1);
                opacity: 0.5;
                box-shadow: 0 0 20px 10px rgba(37, 99, 235, 0.2);
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
                box-shadow: 0 0 40px 20px rgba(37, 99, 235, 0.4);
            }
            100% {
                transform: scale(1);
                opacity: 0.5;
                box-shadow: 0 0 20px 10px rgba(37, 99, 235, 0.2);
            }
        }

        /* Update existing logo-pulse animation to work with glow */
        .logo-pulse {
            position: relative;
            z-index: 2;
        }

        .logo-pulse:hover + .glow-effect {
            animation-play-state: paused;
            transform: translate(-50%, -50%) scale(1.2);
            box-shadow: 0 0 60px 30px rgba(37, 99, 235, 0.6);
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
            <img src="Assets/Img/logo.png" alt="BPAMIS Logo" class="loader-logo">
        </div>
    </div>

    <?php include_once('includes/bpamis_nav.php'); ?>

    <!-- Title Section -->
    <section class="py-24 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center opacity-0 animate-fade-in">
                <!-- Logo -->
                <div class="mb-8 relative flex justify-center items-center">
                    <div class="absolute w-24 h-24">
                        <div class="glow-effect absolute"></div>
                    </div>
                    <img src="logo.png" alt="BPAMIS Logo" class="w-24 h-24 logo-pulse relative z-10">
                </div>
                
                <!-- Acronym -->
                <div class="mb-6">
                    <h1 class="text-6xl md:text-7xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
                        BPAMIS
                    </h1>
                </div>
                
                <!-- Separator Line -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-4 bg-gradient-to-r from-blue-50 to-indigo-50 text-sm text-gray-500 uppercase tracking-widest">
                            Empowering Communities
                        </span>
                    </div>
                </div>
                
                <!-- Full Name -->
                <h2 class="text-2xl md:text-3xl text-gray-700 font-light leading-relaxed">
                    Barangay Panducot Adjudication
                    <br>
                    Management Information System
                </h2>
            </div>
        </div>
    </section>

    <!-- About BPAMIS -->
    <section id="about-bpamis" class="about-hero py-20">
        <div class="container mx-auto px-4">
            <div class="content-wrapper max-w-4xl mx-auto rounded-2xl p-8 shadow-lg">
                <div class="space-y-12">
                    <!-- Introduction to Philippine Adjudication -->
                    <div class="paragraph-wrapper">
                        <div class="text-center mb-8">
                            <h3 class="inline-block px-6 py-2 bg-blue-50 rounded-full text-blue-600 text-lg font-semibold">
                                Philippine Adjudication System
                            </h3>
                        </div>
                        <div class="section-icon fade-in-element">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <div class="text-block">
                            <p class="mb-4 font-medium text-gray-800">
                                The Philippine adjudication is a legal process wherein disputes are judged by courts or tribunals based on laws and evidence. This is an essential administrative process with laws, as it keeps the system fair, legally compliant, and resolves the problem appropriately. At the community level, adjudication is governed by Presidential Decree No. 1508 which institutionalizes a barangay-based mechanism for the amicable settlement of disputes before bringing the case to court.
                            </p>
                        </div>
                    </div>

                    <!-- Digital Transformation -->
                    <div class="paragraph-wrapper">
                        <div class="text-center mb-8">
                            <h3 class="inline-block px-6 py-2 bg-green-50 rounded-full text-green-600 text-lg font-semibold">
                                Digital Transformation Impact
                            </h3>
                        </div>
                        <div class="section-icon fade-in-element stagger-fade-delay-1">
                            <i class="fas fa-digital-tachograph"></i>
                        </div>
                        <div class="text-block">
                            <p class="mb-4 font-medium text-gray-800">
                                The importance of digitizing barangay case management lies upon its potential to improve handling documents, increase documentation accuracy, shorten resolution time, and ensure legal standards. By switching to a digital case management system, barangays with its perks with more smoother operations, improvement for accessibility to case management, and better coordination between barangay officials and concerned parties. By using a digital case management system process will allow for better accountability and transparency in the resolution of disputes.
                            </p>
                        </div>
                    </div>

                    <!-- Barangay Profile -->
                    <div class="paragraph-wrapper">
                        <div class="text-center mb-8">
                            <h3 class="inline-block px-6 py-2 bg-purple-50 rounded-full text-purple-600 text-lg font-semibold">
                                About Barangay Panducot
                            </h3>
                        </div>
                        <div class="section-icon fade-in-element stagger-fade-delay-2">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="text-block">
                            <p class="mb-4 font-medium text-gray-800">
                                The Barangay Panducot is a Local Government Unit located in Calumpit, Bulacan, which manages community governance, conflict resolution, and public service delivery. The most recent data indicates up to 3,318 inhabitants—including the elderly, adults, and children. The priorities in livelihood, education, healthcare, and peacekeeping initiatives of the barangay are influenced by this demographic structure. Like most of the barangays, it acts as the community level that mediates in local disputes such as disagreements between neighbors, minor legal issues and community relations. The advancement of governance at this level is essential to promote civic involvement and ensure that serving justice remains accessible and efficient.
                            </p>
                        </div>
                    </div>

                    <!-- System Purpose -->
                    <div class="paragraph-wrapper">
                        <div class="text-center mb-8">
                            <h3 class="inline-block px-6 py-2 bg-indigo-50 rounded-full text-indigo-600 text-lg font-semibold">
                                Purpose & Implementation
                            </h3>
                        </div>
                        <div class="section-icon fade-in-element stagger-fade-delay-3">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="text-block">
                            <p class="mb-4 font-medium text-gray-800">
                                To support and strengthen these initiatives, the proposed system was primarily designed to optimize the efficiency of a desired functionality of barangay justice system. This system is designed mainly to enhance the documentation, organization and coordination of adjudication-related proceedings within the barangay. The primary users of this system were barangay secretary, barangay officials—which include the Barangay Captain and the Lupon Tagapamayapa—and lastly the residents in Barangay Panducot.
                            </p>
                        </div>
                    </div>

                    <!-- Study Area -->
                    <div class="paragraph-wrapper">
                        <div class="text-center mb-8">
                            <h3 class="inline-block px-6 py-2 bg-red-50 rounded-full text-red-600 text-lg font-semibold">
                                Study Area & Scope
                            </h3>
                        </div>
                        <div class="section-icon fade-in-element stagger-fade-delay-4">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="text-block">
                            <p class="mb-4 font-medium text-gray-800">
                                The Barangay Panducot serves as the area of study of the system. Located in the municipality of Calumpit, province of Bulacan, the barangay has been using the traditional manual procedure of managing community cases, as well as public service records. The barangay officials and staff who are involved in adjudicating disputes and preserving the peace—who therefore would be the primary clients for implementation of the proposed digital case management system.
                            </p>
                        </div>
                    </div>

                    <!-- BPAMIS Goals -->
                    <div class="paragraph-wrapper">
                        <div class="text-center mb-8">
                             <h3 class="inline-block px-6 py-2 bg-blue-50 rounded-full text-blue-600 text-lg font-semibold">
                                BPAMIS Goals
                            </h3>
                        </div>
                        <div class="section-icon fade-in-element stagger-fade-delay-5">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="text-block">
                            <p class="mb-4 font-medium text-gray-800">
                                The Barangay Panducot Adjudication Management Information System (BPAMIS) main goal is to improve case tracking, reporting accuracy, and expedite paperwork. The barangay would increase the accuracy of the recorded data up to producing reports, this will eliminate the need for manual documentation and provide barangay officials with a case management platform that is organized and simple to use. The BPAMIS offers a structured digital platform that would enhance and organize the barangay adjudication processes. The solution is capable of enhancing the efficiency and accuracy of case management without requiring high-level infrastructure or excessive training, making it an ideal choice for Barangay Panducot. Even if traditional processes have been employed at the barangay, a digital automated system will significantly increase the accessibility, accountability and efficiency of case management.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h4 class="text-xl font-semibold mb-4">About BPAMIS</h4>
                    <p class="text-gray-400">Modernizing barangay services through digital solutions.</p>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-4">Contact Us</h4>
                    <p class="text-gray-400">Email: info@bpamis.gov.ph</p>
                    <p class="text-gray-400">Phone: (123) 456-7890</p>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-4">Newsletter</h4>
                    <form class="flex">
                        <input type="email" placeholder="Your email" class="px-4 py-2 rounded-l-lg w-full">
                        <button class="bg-blue-600 px-4 py-2 rounded-r-lg hover:bg-blue-700">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        window.addEventListener('load', function() {
            const loader = document.querySelector('.loader-wrapper');
            if (loader) {
                loader.classList.add('fade-out');
                setTimeout(() => {
                    loader.remove();
                }, 1000); // Increased fade-out time
            }
        });

        // Demographics Chart
        const ctx = document.getElementById('demographicsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Children (0-17)', 'Adults (18-59)', 'Senior Citizens (60+)'],
                datasets: [{
                    data: [1245, 1648, 425], // Calculated adults: 3318 - 1245 - 425 = 1648
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)', // Blue
                        'rgba(16, 185, 129, 0.8)', // Green
                        'rgba(245, 158, 11, 0.8)'  // Orange
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Population Distribution by Age Group',
                        font: {
                            size: 16
                        }
                    }
                }
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

        document.addEventListener('DOMContentLoaded', function() {
            AOS.init();
        });
    </script>

    <!-- Google Maps JavaScript API -->
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap">
    </script>
</body>
</html>