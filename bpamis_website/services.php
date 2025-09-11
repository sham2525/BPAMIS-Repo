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

    <style>
        body {
        }

        .hero-section {
            background: linear-gradient(rgba(62, 131, 249, 0.72), rgba(0,0,0,0.5)), url('brgyhall.png');
            background-size: cover;
            background-position: center;
            height: 600px;
            position: relative;
            overflow: hidden;
            z-index: 1;
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

        .service-block {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }

        .service-block.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .service-block:hover .relative > div:first-child {
            transform: rotate(0);
        }

        @media (max-width: 768px) {
            .service-block {
                padding: 2rem 1rem;
            }
        }

        .process-steps {
            padding: 2rem;
            position: relative;
        }

        .step-container {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .step-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .step-content {
            margin-left: 1rem;
            flex-grow: 1;
        }

        .step-connector {
            position: absolute;
            left: 1.75rem;
            top: 3.5rem;
            width: 2px;
            height: 2rem;
            background-color: #e5e7eb;
            z-index: 1;
        }

        .step-container:last-child .step-connector {
            display: none;
        }

        .step-container:hover .step-icon {
            transform: scale(1.1);
            box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.1);
        }

        @media (max-width: 768px) {
            .process-steps {
                padding: 1rem;
            }
            
            .step-icon {
                width: 3rem;
                height: 3rem;
            }
            
            .step-content h4 {
                font-size: 0.9rem;
            }
            
            .step-content p {
                font-size: 0.8rem;
            }
            
            .step-connector {
                height: 1.5rem;
            }
        }

        .service-item {
            transition: all 0.3s ease;
            padding: 1.5rem;
            border-radius: 0.75rem;
        }

        .service-item:hover {
            background-color: #F3E8FF;
            transform: translateX(10px);
        }

        .service-item:hover .w-12 {
            background-color: #9333EA;
        }

        .service-item:hover .w-12 i {
            color: white;
        }

        @media (max-width: 768px) {
            .service-item {
                padding: 1rem;
            }
            
            .service-item h4 {
                font-size: 1rem;
            }
            
            .service-item p {
                font-size: 0.875rem;
            }
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

    <!-- Hero Section -->
    <section class="hero-section flex items-center justify-center text-white text-center">
        <div class="container mx-auto px-4">
            <h1 class="text-7xl font-bold mb-6">Community-Centered Services</h1>
            <p class="text-xl mb-8">Modernizing Barangay Adjudication Through Integrated Digital Solutions</p>
            <div class="flex justify-center gap-4">
                <a href="register.php" class="hero-btn hero-btn-primary">Get Started</a>
                <a href="user-guide.php" class="hero-btn hero-btn-secondary">View User Guide</a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-6xl font-bold text-center mb-16 text-gray-800">Our Services</h2>

        <!-- Services Grid -->
        <div class="space-y-24">
            <!-- Barangay Cases -->
            <div class="service-block" data-aos="fade-up">
                <div class="flex flex-col lg:flex-row items-center gap-12">
                    <div class="lg:w-1/2">
                        <div class="relative">
                            <div class="absolute -inset-4 bg-blue-100 rounded-2xl transform -rotate-3 transition-transform group-hover:rotate-0"></div>
                            <div class="relative bg-white p-8 rounded-xl shadow-lg">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-gavel text-2xl text-white"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-800">Barangay Cases</h3>
                                </div>
                                <p class="text-gray-600 mb-8">Efficient management and resolution of barangay disputes and cases</p>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-blue-600"></i>
                                        <span>Online filing of complaints and disputes</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-blue-600"></i>
                                        <span>Real-time case tracking and updates</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-blue-600"></i>
                                        <span>Automated scheduling of hearings</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-blue-600"></i>
                                        <span>Digital documentation and record-keeping</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Process Steps for Barangay Cases -->
            <div class="lg:w-1/2">
                <div class="process-steps">
                    <div class="step-container">
                        <div class="step-icon bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white">
                            <i class="fas fa-file-signature text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">File Complaint</h4>
                            <p class="text-sm text-gray-600">Submit your case details online</p>
                        </div>
                        <div class="step-connector"></div>
                    </div>
                    
                    <div class="step-container">
                        <div class="step-icon bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white">
                            <i class="fas fa-clipboard-check text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">Case Review</h4>
                            <p class="text-sm text-gray-600">Initial assessment by officials</p>
                        </div>
                        <div class="step-connector"></div>
                    </div>
                    
                    <div class="step-container">
                        <div class="step-icon bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white">
                            <i class="fas fa-calendar-alt text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">Schedule Hearing</h4>
                            <p class="text-sm text-gray-600">Set mediation date and time</p>
                        </div>
                        <div class="step-connector"></div>
                    </div>
                    
                    <div class="step-container">
                        <div class="step-icon bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white">
                            <i class="fas fa-handshake text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">Mediation</h4>
                            <p class="text-sm text-gray-600">Resolve disputes through dialogue</p>
                        </div>
                        <div class="step-connector"></div>
                    </div>
                    
                    <div class="step-container">
                        <div class="step-icon bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">Resolution</h4>
                            <p class="text-sm text-gray-600">Case closed with documentation</p>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>

            <!-- Document Processing -->
            <div class="service-block" data-aos="fade-up" data-aos-delay="100">
                <div class="flex flex-col lg:flex-row-reverse items-center gap-12">
                    <div class="lg:w-1/2">
                        <div class="relative">
                            <div class="absolute -inset-4 bg-green-100 rounded-2xl transform rotate-3 transition-transform group-hover:rotate-0"></div>
                            <div class="relative bg-white p-8 rounded-xl shadow-lg">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-alt text-2xl text-white"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-800">Document Processing</h3>
                                </div>
                                <p class="text-gray-600 mb-8">Easy access to barangay certificates and other official documents</p>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                        <span>Online document request submission</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                        <span>Digital certificate verification</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                        <span>Secure document storage</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                        <span>Real-time processing status updates</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   <!-- Process Steps for Document Processing -->
            <div class="lg:w-1/2">
                <div class="process-steps">
                    <div class="step-container">
                        <div class="step-icon bg-green-100 text-green-600 hover:bg-green-600 hover:text-white">
                            <i class="fas fa-file-alt text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">Request Document</h4>
                            <p class="text-sm text-gray-600">Submit a request for barangay certificates or other official documents through the online portal</p>
                        </div>
                        <div class="step-connector"></div>
                    </div>
                    
                    <div class="step-container">
                        <div class="step-icon bg-green-100 text-green-600 hover:bg-green-600 hover:text-white">
                            <i class="fas fa-clipboard-list text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">Verify Requirements</h4>
                            <p class="text-sm text-gray-600">System checks and validates uploaded documents or prerequisites (e.g., valid ID, purpose)</p>
                        </div>
                        <div class="step-connector"></div>
                    </div>
                    
                    <div class="step-container">
                        <div class="step-icon bg-green-100 text-green-600 hover:bg-green-600 hover:text-white">
                            <i class="fas fa-cogs text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">Process Request</h4>
                            <p class="text-sm text-gray-600">Barangay staff reviews the submission and begins processing the request</p>
                        </div>
                        <div class="step-connector"></div>
                    </div>
                    
                    <div class="step-container">
                        <div class="step-icon bg-green-100 text-green-600 hover:bg-green-600 hover:text-white">
                            <i class="fas fa-certificate text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">Digital Certificate Issuance</h4>
                            <p class="text-sm text-gray-600">A verified digital copy of the certificate is issued and stored securely in the system</p>
                        </div>
                        <div class="step-connector"></div>
                    </div>
                    
                    <div class="step-container">
                        <div class="step-icon bg-green-100 text-green-600 hover:bg-green-600 hover:text-white">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">Track Status</h4>
                            <p class="text-sm text-gray-600">Users receive real-time updates on request status — from received to ready for pickup or download</p>
                        </div>
                        <div class="step-connector"></div>
                    </div>

                    <div class="step-container">
                        <div class="step-icon bg-green-100 text-green-600 hover:bg-green-600 hover:text-white">
                            <i class="fas fa-shield-alt text-2xl"></i>
                        </div>
                        <div class="step-content">
                            <h4 class="font-semibold text-gray-800">Secure Access & Archiving</h4>
                            <p class="text-sm text-gray-600">All documents are stored in a secure database for future reference and retrieval</p>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>

            <!-- Resident Services -->
            <div class="service-block" data-aos="fade-up" data-aos-delay="200">
                <div class="flex flex-col lg:flex-row items-center gap-12">
                    <div class="lg:w-1/2">
                        <div class="relative">
                            <div class="absolute -inset-4 bg-purple-100 rounded-2xl transform -rotate-3 transition-transform group-hover:rotate-0"></div>
                            <div class="relative bg-white p-8 rounded-xl shadow-lg">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-users text-2xl text-white"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-800">Resident Services</h3>
                                </div>
                                <p class="text-gray-600 mb-8">Comprehensive resident information and assistance management</p>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-purple-600"></i>
                                        <span>Online resident profile management</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-purple-600"></i>
                                        <span>Community announcements and updates</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-purple-600"></i>
                                        <span>Digital assistance requests</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-700">
                                        <i class="fas fa-check-circle text-purple-600"></i>
                                        <span>Online appointment scheduling</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Process Steps for Resident Services -->
            <div class="lg:w-1/2">
                <div class="space-y-6 p-6 bg-white rounded-xl shadow-lg">
                    <!-- Online Resident Profile Management -->
                    <div class="service-item">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user-circle text-2xl text-purple-600"></i>
                                </div>
                            </div>
                            <div class="flex-grow">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Online Resident Profile Management</h4>
                                <p class="text-gray-600">Maintain accurate resident information including household details, personal data, and residency status in a centralized, secure system.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Community Announcements -->
                    <div class="service-item">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-bullhorn text-2xl text-purple-600"></i>
                                </div>
                            </div>
                            <div class="flex-grow">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Community Announcements and Updates</h4>
                                <p class="text-gray-600">Stay informed about barangay news, events, public advisories, and emergency alerts through a centralized announcements platform.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Digital Assistance -->
                    <div class="service-item">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-hands-helping text-2xl text-purple-600"></i>
                                </div>
                            </div>
                            <div class="flex-grow">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Digital Assistance Requests</h4>
                                <p class="text-gray-600">Submit requests for services such as barangay clearances, certifications, and other support directly from your device without needing to visit in person.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Online Appointment -->
                    <div class="service-item">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-calendar-check text-2xl text-purple-600"></i>
                                </div>
                            </div>
                            <div class="flex-grow">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Online Appointment Scheduling</h4>
                                <p class="text-gray-600">Book appointments with barangay officials or staff for services like consultations, document processing, or dispute hearings with real-time availability tracking.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-16 text-gray-800">Our Team</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Barangay Secretary -->
            <div class="team-card group">
                <div class="relative bg-white rounded-xl shadow-lg p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-purple-100 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-user-tie text-3xl text-blue-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-center text-gray-800 mb-4">Barangay Secretary</h3>
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

            <!-- Barangay Captain -->
            <div class="team-card group">
                <div class="relative bg-white rounded-xl shadow-lg p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                    <div class="absolute -inset-1 bg-gradient-to-r from-green-100 to-blue-100 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-user-shield text-3xl text-green-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-center text-gray-800 mb-4">Barangay Captain</h3>
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

            <!-- Lupon Tagapamayapa -->
            <div class="team-card group">
                <div class="relative bg-white rounded-xl shadow-lg p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                    <div class="absolute -inset-1 bg-gradient-to-r from-purple-100 to-pink-100 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-balance-scale text-3xl text-purple-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-center text-gray-800 mb-4">Lupon Tagapamayapa</h3>
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
        </div>
    </div>
</section>

<!-- Who Can Access Our Services Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-16 text-gray-800">Who Can Access Our Services?</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Residents Card -->
            <div class="user-card group">
                <div class="relative bg-white rounded-xl shadow-lg p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="flex items-center gap-6 mb-8">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-home text-3xl text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800">Residents</h3>
                                <p class="text-blue-600">Full Service Access</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                <span class="text-gray-600">Create and manage personal BPAMIS accounts</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                <span class="text-gray-600">File complaints and track case progress</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                <span class="text-gray-600">Request official documents online</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                <span class="text-gray-600">Access community announcements and updates</span>
                            </div>
                            <a href="register.php" class="mt-6 inline-flex items-center text-blue-600 hover:text-blue-700">
                                Register as Resident
                                <i class="fas fa-arrow-right ml-2 transition-transform group-hover:translate-x-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- External Complainant Card -->
            <div class="user-card group">
                <div class="relative bg-white rounded-xl shadow-lg p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                    <div class="absolute -inset-1 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="flex items-center gap-6 mb-8">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-friends text-3xl text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800">External Complainant</h3>
                                <p class="text-purple-600">Limited Service Access</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-purple-600 mt-1"></i>
                                <span class="text-gray-600">File complaints against Panducot residents</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-purple-600 mt-1"></i>
                                <span class="text-gray-600">Track case status and updates remotely</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-purple-600 mt-1"></i>
                                <span class="text-gray-600">Receive hearing notifications</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-purple-600 mt-1"></i>
                                <span class="text-gray-600">Access case-related documents</span>
                            </div>
                            <a href="register.php?type=external" class="mt-6 inline-flex items-center text-purple-600 hover:text-purple-700">
                                Register as External Complainant
                                <i class="fas fa-arrow-right ml-2 transition-transform group-hover:translate-x-1"></i>
                            </a>
                        </div>
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

        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1
            });

            document.querySelectorAll('.service-block').forEach(block => {
                observer.observe(block);
            });
        });
    </script>
</body>
</html>