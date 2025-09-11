<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPAMIS - Contact Us</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            background: #ffffff;
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

        .glass-card {
            background: #ffffff;
            border: 1px solid rgba(229, 231, 235, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .calendar-day:hover {
            background: #EFF6FF;
            color: #2563EB;
        }

        .calendar-day.selected {
            background: #2563EB;
            color: white;
        }

        .time-slot.selected {
            background: #2563EB;
            color: white;
            border-color: #2563EB;
        }

        .duration-btn.selected {
            background: #2563EB;
            color: white;
            border-color: #2563EB;
        }

        input, textarea, select {
            transition: all 0.2s ease;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            background: #ffffff;
        }

        button {
            transition: all 0.2s ease;
        }

        .fade-up {
            opacity: 0;
            animation: fadeUp 0.5s ease-out forwards;
        }

        .fade-in-element {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .fade-in-element.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Update loader styles */
        .loader-wrapper {
            position: fixed;
            inset: 0;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 1;
            visibility: visible;
            transition: opacity 1s ease-out, visibility 1s ease-out;
        }

        .loader-wrapper.fade-out {
            opacity: 0;
            visibility: hidden;
        }

        .loader {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .loader-circle {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid transparent;
            border-radius: 50%;
        }

        .loader-circle:nth-child(1) {
            border-top-color: #2563eb;
            animation: spin 1s linear infinite;
        }

        .loader-circle:nth-child(2) {
            border-right-color: #2563eb;
            animation: spin 0.8s linear infinite reverse;
        }

        .loader-circle:nth-child(3) {
            border-bottom-color: #2563eb;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Loader Logo Animation */
        .loader-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            opacity: 0;
            animation: logoFade 0.5s ease-out 0.5s forwards;
        }

        @keyframes logoFade {
            to { opacity: 1; }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>  


<!-- Add this at the very top of the body -->
    <div class="loader-wrapper" id="loader">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-circle"></div>
            <div class="loader-circle"></div>
            <img src="Assets/Img/bpamis-logo.png" alt="BPAMIS Logo" class="loader-logo">
        </div>
    </div>

    
    <?php include_once('includes/bpamis_nav.php'); ?>
  
     <!-- Hero Section -->
    <section class="hero-section flex items-center justify-center text-white text-center">
        <div class="container mx-auto px-4">
            <h1 class="text-7xl font-bold mb-6">Reach Out for Support</h1>
            <p class="text-xl mb-8">Support & Inquiries — professional and service-focused</p>
            <div class="flex justify-center gap-4">
            <a href="register.php" class="hero-btn hero-btn-primary">Get Started</a>
            <a href="user-guide.php" class="hero-btn hero-btn-secondary">View User Guide</a>
            </div>
        </div>
    </section>

    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12 fade-up">
                <h1 class="text-3xl font-bold text-gray-900">Schedule an Appointment</h1>
                <p class="mt-4 text-gray-600">Choose your preferred date and time below</p>
            </div>

            <!-- Booking Container -->
            <div class="glass-card rounded-2xl p-8 mb-12">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Calendar Section -->
                    <div>
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-gray-800">Select Date</h2>
                            <div class="flex space-x-2">
                                <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-chevron-left text-gray-600"></i>
                                </button>
                                <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-chevron-right text-gray-600"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-2 mb-8">
                            <!-- Weekday headers -->
                            <div class="text-center text-sm font-medium text-gray-500 py-2">M</div>
                            <div class="text-center text-sm font-medium text-gray-500 py-2">T</div>
                            <div class="text-center text-sm font-medium text-gray-500 py-2">W</div>
                            <div class="text-center text-sm font-medium text-gray-500 py-2">T</div>
                            <div class="text-center text-sm font-medium text-gray-500 py-2">F</div>
                            <div class="text-center text-sm font-medium text-gray-500 py-2">S</div>
                            <div class="text-center text-sm font-medium text-gray-500 py-2">S</div>
                            <!-- Calendar days will be dynamically generated -->
                        </div>

                        <!-- Time Slots -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Available Times</h3>
                            <div class="grid grid-cols-3 gap-2">
                                <!-- Update time slot buttons with new styling -->
                                <button class="time-slot p-3 text-sm rounded-lg border transition-all hover:border-blue-500">
                                    9:00 AM
                                </button>
                                <button class="time-slot p-3 text-sm rounded-lg border transition-all hover:border-blue-500">
                                    10:00 AM
                                </button>
                                <button class="time-slot p-3 text-sm rounded-lg border transition-all hover:border-blue-500">
                                    11:00 AM
                                </button>
                                <button class="time-slot p-3 text-sm rounded-lg border transition-all hover:border-blue-500">
                                    1:00 PM
                                </button>
                                <button class="time-slot p-3 text-sm rounded-lg border transition-all hover:border-blue-500">
                                    2:00 PM
                                </button>
                                <button class="time-slot p-3 text-sm rounded-lg border transition-all hover:border-blue-500">
                                    3:00 PM
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Appointment Details</h2>
                        <form class="space-y-6">
                            <!-- Duration Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <button type="button" class="duration-btn p-3 text-sm rounded-lg border transition-all hover:border-blue-500">
                                        15 min
                                    </button>
                                    <button type="button" class="duration-btn p-3 text-sm rounded-lg border transition-all hover:border-blue-500">
                                        30 min
                                    </button>
                                    <button type="button" class="duration-btn p-3 text-sm rounded-lg border transition-all hover:border-blue-500">
                                        45 min
                                    </button>
                                </div>
                            </div>

                            <!-- Form Fields -->
                            <div class="space-y-4">
                                <input type="text" placeholder="Full Name" class="w-full p-3 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <input type="email" placeholder="Email Address" class="w-full p-3 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <select class="w-full p-3 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Purpose</option>
                                    <option>Document Request</option>
                                    <option>File a Complaint</option>
                                    <option>Mediation Session</option>
                                    <option>General Inquiry</option>
                                </select>
                                <textarea placeholder="Additional Notes" rows="3" class="w-full p-3 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors">
                                Confirm Appointment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="hidden fixed inset-0 modal-backdrop z-50 flex items-center justify-center">
        <div class="modal-content bg-white rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <div class="relative">
                <h3 class="text-2xl font-bold text-blue-800 mb-4">Confirm Appointment</h3>
                <div id="confirmationDetails" class="space-y-3 mb-6 text-gray-600"></div>
                <div class="flex justify-end space-x-4">
                    <button class="close-modal px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmAppointment" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <div id="successMessage" class="hidden fixed bottom-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg">
        Appointment booked successfully!
    </div>

    <script>
        $(document).ready(function() {
            let selectedTime = '';
            let selectedDate = '';
            let selectedDuration = '';

            // Initialize calendar
            generateCalendar();
            initializeCalendarSelection();

            // Time slot selection
            $('.time-slot').click(function() {
                $('.time-slot').removeClass('bg-blue-100 border-blue-500');
                $(this).addClass('bg-blue-100 border-blue-500');
                selectedTime = $(this).text();
                checkFormCompletion();
            });

            // Duration selection
            $('.duration-btn').click(function() {
                $('.duration-btn').removeClass('bg-blue-100 border-blue-500');
                $(this).addClass('bg-blue-100 border-blue-500');
                selectedDuration = $(this).text();
                checkFormCompletion();
            });

            function generateCalendar() {
                const calendarGrid = $('.grid-cols-7');
                const today = new Date();
                const daysInMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
                
                // Clear existing calendar days
                $('.calendar-day').remove();

                // Generate new calendar days
                for (let i = 1; i <= daysInMonth; i++) {
                    const dayElement = $('<div>', {
                        class: 'calendar-day text-center p-2 rounded-lg hover:bg-blue-50 cursor-pointer',
                        text: i
                    });
                    calendarGrid.append(dayElement);
                }
            }

            function initializeCalendarSelection() {
                $(document).on('click', '.calendar-day', function() {
                    $('.calendar-day').removeClass('selected bg-blue-100');
                    $(this).addClass('selected bg-blue-100');
                    selectedDate = $(this).text();
                    checkFormCompletion();
                });
            }

            function checkFormCompletion() {
                if (selectedDate && selectedTime && selectedDuration) {
                    showAppointmentForm();
                }
            }

            function showAppointmentForm() {
                // Update the form section with selected date and time
                const formattedDate = formatDate(selectedDate);
                $('#appointmentDateTime').html(
                    `<p class="text-lg font-medium text-gray-800 mb-4">
                        Selected Date: ${formattedDate}<br>
                        Time: ${selectedTime}<br>
                        Duration: ${selectedDuration}
                    </p>`
                );
                
                // Show the form section
                $('#appointmentForm').removeClass('hidden');
                
                // Smooth scroll to form
                $('html, body').animate({
                    scrollTop: $('#appointmentForm').offset().top - 100
                }, 500);
            }

            function formatDate(day) {
                const date = new Date();
                return new Date(date.getFullYear(), date.getMonth(), day)
                    .toLocaleDateString('en-US', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
            }

            // Handle form submission
            $('#appointmentForm').submit(function(e) {
                e.preventDefault();
                
                const formData = {
                    date: selectedDate,
                    time: selectedTime,
                    duration: selectedDuration,
                    firstName: $('#firstName').val(),
                    lastName: $('#lastName').val(),
                    email: $('#email').val(),
                    purpose: $('#purpose').val(),
                    notes: $('#notes').val()
                };

                // Show confirmation modal
                $('#confirmationModal').removeClass('hidden');
                $('#confirmationDetails').html(`
                    <p><strong>Date:</strong> ${formatDate(selectedDate)}</p>
                    <p><strong>Time:</strong> ${selectedTime}</p>
                    <p><strong>Duration:</strong> ${selectedDuration}</p>
                    <p><strong>Name:</strong> ${formData.firstName} ${formData.lastName}</p>
                    <p><strong>Email:</strong> ${formData.email}</p>
                    <p><strong>Purpose:</strong> ${formData.purpose}</p>
                `);
            });

            // Close modal
            $('.close-modal').click(function() {
                $('#confirmationModal').addClass('hidden');
            });

            // Confirm appointment
            $('#confirmAppointment').click(function() {
                // Here you would typically send the data to your server
                // For now, just show success message
                $('#confirmationModal').addClass('hidden');
                $('#successMessage').removeClass('hidden');
                
                // Reset form after 2 seconds
                setTimeout(() => {
                    $('#appointmentForm')[0].reset();
                    $('#successMessage').addClass('hidden');
                    $('.time-slot, .duration-btn').removeClass('bg-blue-100 border-blue-500');
                    $('.calendar-day').removeClass('selected bg-blue-100');
                    selectedTime = '';
                    selectedDate = '';
                    selectedDuration = '';
                }, 2000);
            });
        });

        // Loading animation
        window.addEventListener('load', function() {
            const loader = document.getElementById('loader');
            setTimeout(() => {
                loader.style.opacity = '0';
                loader.style.visibility = 'hidden';
            }, 1500);
        });
    </script>

    <!-- Message Section -->
    <section class="bg-gray-50 py-16">
        <!-- Header -->
            <div class="text-center mb-12 fade-up">
                <h1 class="text-3xl font-bold text-gray-900">Have a Question? Reach Out to Us.</h1>
                <p class="mt-4 text-gray-600">If you are not yet ready to schedule an appointment, you can also send us a message below.</p>
            </div>
    </section>

   


    <!-- Let's Talk Section -->
    <section class="bg-white py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <p class="mt-4 text-gray-600">You can use the form below to send us your concerns, inquiries, or clarifications. We'll respond as soon as possible to assist you.</p>
                <br><div class="space-y-8">
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <input type="text" 
                                    placeholder="Name" 
                                    class="w-full p-4 bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-blue-500 transition-all"
                                    required>
                            </div>
                            <div>
                                <input type="email" 
                                    placeholder="Email Address" 
                                    class="w-full p-4 bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-blue-500 transition-all"
                                    required>
                            </div>
                        </div>
                        <div>
                            <textarea 
                                rows="6" 
                                placeholder="Message" 
                                class="w-full p-4 bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-blue-500 transition-all"
                                required></textarea>
                        </div>
                        <div>
                            <button type="submit" 
                                class="w-full md:w-auto px-8 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Use BPAMIS Section -->
    <section class="py-20 bg-gradient-to-br from-blue-50 to-white">
        <div class="container mx-auto px-4">
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 fade-in-element">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Why Use BPAMIS?</h2>
                <p class="mt-4 text-gray-600">The Barangay Panducot Adjudication Management Information System
                    modernizes our justice system by bringing transparency, accuracy, and efficiency to case management.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-12">
                <!-- Make sure each card has fade-in-element and proper delay class -->
                <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 fade-in-element stagger-fade-delay-1">
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

                <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 fade-in-element stagger-fade-delay-2">
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

                <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 fade-in-element stagger-fade-delay-3">
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

                <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 fade-in-element stagger-fade-delay-4">
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
        // Add intersection observer for fade-in elements
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            // Observe all fade-in elements
            document.querySelectorAll('.fade-in-element').forEach(element => {
                observer.observe(element);
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
    </script>
</body>
</html>