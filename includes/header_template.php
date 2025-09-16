<?php
/**
 * Header Template
 * Barangay Panducot Adjudication Management Information System
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'BPAMIS'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        }
        .progress-bar {
            transition: width 1s ease-in-out;
        }
        .stat-card {
            border-radius: 12px;
            overflow: hidden;
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
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <?php include '../includes/barangay_official_nav.php'; ?>
