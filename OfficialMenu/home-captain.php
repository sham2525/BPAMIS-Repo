<?php
session_start();

include '../server/server.php';


// Initialize all stats
$complaintsCount = $resolvedCount = $pendingCount = $rejectedCount = 0;
$casesCount = $mediatedCount = $resolutionCount = $settlementCount = $closedCount = $resolvedCaseCount = 0;
$scheduledHearings = 0;

// Complaints Count
$complaintsQuery = "SELECT status, COUNT(*) as count FROM complaint_info GROUP BY status";
$result = $conn->query($complaintsQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower(trim($row['status']));
        $count = (int) $row['count'];
        $complaintsCount += $count;

        if ($status === 'resolved')
            $resolvedCount = $count;
        elseif ($status === 'pending')
            $pendingCount = $count;
        elseif ($status === 'rejected')
            $rejectedCount = $count;
    }
}

// Cases Count
$caseQuery = "SELECT case_status as status, COUNT(*) as count FROM case_info GROUP BY case_status";
$result = $conn->query($caseQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower(trim($row['status']));
        $count = (int) $row['count'];
        $casesCount += $count;

        if ($status === 'mediation')
            $mediatedCount = $count;
        elseif ($status === 'resolution')
            $resolutionCount = $count;
        elseif ($status === 'settlement')
            $settlementCount = $count;
        elseif ($status === 'close')
            $closedCount = $count;
        elseif ($status === 'resolved')
            $resolvedCaseCount = $count;
    }
}

// Hearings Count
$hearingQuery = "SELECT COUNT(*) as count FROM schedule_list";
$result = $conn->query($hearingQuery);
if ($result && $row = $result->fetch_assoc()) {
    $scheduledHearings = (int) $row['count'];
}

// ========== RECENT ACTIVITY ==========
function getComplainantName($conn, $resident_id, $external_id)
{
    if (!empty($resident_id)) {
        $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM resident_info WHERE resident_id = ?");
        $stmt->bind_param("i", $resident_id);
    } else {
        $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM external_complainant WHERE external_complaint_id = ?");
        $stmt->bind_param("i", $external_id);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['first_name'] . ' ' . $result['middle_name'] . ' ' . $result['last_name'] : 'Unknown';
}

$recentActivities = [];
$query = "SELECT complaint_id, resident_id, external_complainant_id, date_filed FROM complaint_info ORDER BY date_filed DESC LIMIT 5";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $name = getComplainantName($conn, $row['resident_id'], $row['external_complainant_id']);
    $timeAgo = time() - strtotime($row['date_filed']);
    $hoursAgo = floor($timeAgo / 3600);
    $recentActivities[] = [
        'type' => 'complaint',
        'message' => "New complaint filed by $name",
        'time' => $row['date_filed']
    ];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Dashboard</title>
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
    <?php include '../includes/barangay_official_cap_nav.php'; ?>

    <!-- Welcome Banner -->
    <section class="container mx-auto mt-10 px-4">
        <div class="gradient-bg rounded-2xl shadow-sm p-8 md:p-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h2 class="text-3xl font-light text-primary-800">Welcome, <span class="font-medium">Barangay Official -
                        Captain</span></h2>
                <span class="block mt-1 text-2xl text-primary-700 font-semibold">
                    <?= isset($_SESSION['official_name']) ? htmlspecialchars($_SESSION['official_name']) : 'Unknown' ?>
                </span>
                <p class="mt-3 text-gray-600 max-w-md">Manage conciliation process, write feedback, and oversee the
                    Barangay Panducot Adjudication System with these powerful tools.</p>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <div class="container mx-auto mt-8 px-4">
        <h3 class="text-lg font-medium text-gray-700 mb-4 px-2">Quick Actions</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="feedback.php"
                class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-blue-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-plus-circle text-primary-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Write Feedback</h4>
                    <p class="text-sm text-gray-500">Create a Feedback</p>
                </div>
            </a>
            <a href="view_blotter_details.php"
                class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-green-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-file-alt text-green-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800 text-sm">View Complaints</h4>
                    <p class="text-xs text-gray-500">Monitor the complaints in the Barangay</p>
                </div>
            </a>
            <a href="view_cases.php"
                class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-yellow-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-gavel text-yellow-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">View Cases</h4>
                    <p class="text-sm text-gray-500">Monitor the cases in the Barangay</p>
                </div>
            </a>
            <a href="view_hearing_calendar.php"
                class="card-hover flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="bg-green-50 p-3 rounded-lg mr-4">
                    <i class="fas fa-calendar-alt text-green-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">View Hearing</h4>
                    <p class="text-sm text-gray-500">View hearings calendar</p>
                </div>
            </a>
        </div>
    </div>



    <!-- Dashboard Content -->
    <div class="container mx-auto mt-10 px-4 pb-10">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Statistics Section -->
            <div class="md:col-span-5 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-5 flex items-center">
                    <i class="fas fa-chart-bar text-primary-500 mr-2"></i>
                    Statistics
                </h2>

                <div class="space-y-6">
                    <!-- Complaints -->
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Total Complaints</span>
                            <span class="text-sm font-medium text-primary-600 bg-primary-50 px-2 py-0.5 rounded">
                                <?= $complaintsCount ?>
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="progress-bar bg-primary-400 h-2 rounded-full"
                                style="width: <?= $complaintResolvedPercent ?>%"></div>
                        </div>
                    </div>

                    <!-- Cases -->
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Total Cases</span>
                            <span class="text-sm font-medium text-primary-600 bg-primary-50 px-2 py-0.5 rounded">
                                <?= $casesCount ?>
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="progress-bar bg-green-400 h-2 rounded-full"
                                style="width: <?= $caseResolvedPercent ?>%"></div>
                        </div>
                    </div>

                    <!-- Scheduled Hearings -->
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Scheduled Hearings</span>
                            <span class="text-sm font-medium text-primary-600 bg-primary-50 px-2 py-0.5 rounded">
                                <?= $scheduledHearings ?>
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="progress-bar bg-blue-400 h-2 rounded-full"
                                style="width: <?= $hearingPercent ?>%"></div>
                        </div>
                    </div>

                    <!-- Status Summary Boxes -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Resolved Cases -->
                        <div class="bg-green-50 rounded-lg p-4 flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Resolved Cases</p>
                                <p class="text-lg font-medium text-green-600"><?= $resolvedCaseCount ?></p>
                            </div>
                        </div>

                        <!-- Pending Complaints -->
                        <div class="bg-yellow-50 rounded-lg p-4 flex items-center">
                            <i class="fas fa-hourglass-half text-yellow-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Pending Complaints</p>
                                <p class="text-lg font-medium text-yellow-600"><?= $pendingCount ?></p>
                            </div>
                        </div>

                        <!-- Mediated Cases -->
                        <div class="bg-purple-50 rounded-lg p-4 flex items-center">
                            <i class="fas fa-handshake text-purple-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Mediated Disputes</p>
                                <p class="text-lg font-medium text-purple-600"><?= $mediatedCount ?></p>
                            </div>
                        </div>

                        <!-- Rejected Complaints -->
                        <div class="bg-red-50 rounded-lg p-4 flex items-center">
                            <i class="fas fa-ban text-red-500 text-xl mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Rejected Complaints</p>
                                <p class="text-lg font-medium text-red-600"><?= $rejectedCount ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Calendar Section -->
            <div class="md:col-span-7 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-5 flex items-center">
                    <i class="fas fa-calendar text-primary-500 mr-2"></i>
                    Upcoming Hearings
                </h2>
                <div id='calendar' class="calendar-container mt-2 hidden"></div>
                <iframe src="../SecMenu/schedule/CalendarSec.php"
                    style="width:100%; height:800px; border:none;"></iframe>
            </div>
        </div>


        <?php include 'sidebar_.php'; ?>


        <script>
            document.querySelectorAll('.toggle-menu').forEach(button => {
                button.addEventListener('click', () => {
                    const submenu = button.nextElementSibling;
                    submenu.classList.toggle('hidden');
                });
            });
            document.addEventListener('DOMContentLoaded', function () {

                document.querySelectorAll('.submenu').forEach(submenu => {
                    if (submenu.classList.contains('hidden')) {
                        submenu.classList.remove('active');
                    }
                });

                // Calendar initialization
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next',
                        center: 'title',
                        right: 'today'
                    },
                    buttonText: {
                        today: 'Today'
                    },
                    dayHeaderFormat: { weekday: 'short' },
                    eventTimeFormat: {
                        hour: 'numeric',
                        minute: '2-digit',
                        meridiem: 'short'
                    },
                    eventOrder: 'start',
                    eventDisplay: 'block',
                    displayEventTime: true,
                    events: [
                        {
                            title: 'Noise Complaint',
                            start: '2025-05-20T10:00:00',
                            backgroundColor: 'rgba(79, 70, 229, 0.8)',
                            borderColor: 'rgba(79, 70, 229, 0)',
                            textColor: '#ffffff',
                            extendedProps: {
                                type: 'hearing'
                            }
                        },
                        {
                            title: 'Property Dispute Hearing',
                            start: '2025-05-22T14:30:00',
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgba(16, 185, 129, 0)',
                            textColor: '#ffffff',
                            extendedProps: {
                                type: 'hearing'
                            }
                        },
                        {
                            title: 'Unpaid Debt Case',
                            start: '2025-05-18T09:00:00',
                            backgroundColor: 'rgba(245, 158, 11, 0.8)',
                            borderColor: 'rgba(245, 158, 11, 0)',
                            textColor: '#ffffff',
                            extendedProps: {
                                type: 'hearing'
                            }
                        }
                    ]
                });
                calendar.render();
                // Sidebar toggle
                document.getElementById('menu-btn').addEventListener('click', function () {
                    const sidebar = document.getElementById('sidebar');
                    sidebar.classList.remove('-translate-x-full');
                    // Add overlay when sidebar is open
                    addSidebarOverlay();
                });

                document.getElementById('close-sidebar').addEventListener('click', function () {
                    const sidebar = document.getElementById('sidebar');
                    sidebar.classList.add('-translate-x-full');
                    // Remove overlay when sidebar is closed
                    removeSidebarOverlay();
                });            // Toggle submenu items with animation
                document.querySelectorAll('.toggle-menu').forEach(button => {
                    button.addEventListener('click', function () {
                        let submenu = this.nextElementSibling;

                        // Use both hidden and active classes for better animation control
                        submenu.classList.toggle('hidden');

                        // Add a slight delay before adding/removing active class
                        if (!submenu.classList.contains('hidden')) {
                            setTimeout(() => {
                                submenu.classList.add('active');
                            }, 10);
                        } else {
                            submenu.classList.remove('active');
                        }

                        // Rotate chevron icon when clicked
                        const chevron = this.querySelector('.fa-chevron-down');
                        if (chevron) {
                            chevron.classList.toggle('rotate-180');
                        }

                        // Add active state to the clicked menu item
                        this.classList.toggle('bg-primary-50');
                        this.classList.toggle('text-primary-700');
                    });
                });

                // Function to add overlay when sidebar is open
                function addSidebarOverlay() {
                    // Check if overlay already exists
                    if (!document.getElementById('sidebar-overlay')) {
                        const overlay = document.createElement('div');
                        overlay.id = 'sidebar-overlay';
                        overlay.className = 'fixed inset-0 bg-black bg-opacity-30 z-40';
                        document.body.appendChild(overlay);

                        // Close sidebar when overlay is clicked
                        overlay.addEventListener('click', function () {
                            document.getElementById('sidebar').classList.add('-translate-x-full');
                            removeSidebarOverlay();
                        });
                    }
                }

                // Function to remove overlay
                function removeSidebarOverlay() {
                    const overlay = document.getElementById('sidebar-overlay');
                    if (overlay) {
                        overlay.remove();
                    }
                }

                // Statistics loading
                loadStatistics();
            });

            // Fetch statistics dynamically (this would typically come from a database)
            function loadStatistics() {
                <?php
                // You would typically fetch these values from a database in PHP
                // For now we'll keep the static example but note how this could be replaced with PHP code
                ?>            let complaints = 20;
                let cases = 12;
                let hearings = 8;
                let resolved = 10;
                let pending = 5;
                let mediated = 7;
                let rejected = 3;

                document.getElementById('complaints-count').textContent = complaints;
                document.getElementById('cases-count').textContent = cases;
                document.getElementById('hearings-count').textContent = hearings;
                document.getElementById('resolved-count').textContent = resolved;
                document.getElementById('pending-count').textContent = pending;
                document.getElementById('mediated-count').textContent = mediated;
                document.getElementById('rejected-count').textContent = rejected;

                let max = Math.max(complaints, cases, hearings, resolved, pending, mediated, rejected, 1); document.getElementById('complaints-progress').style.width = (complaints / max) * 100 + "%";
                document.getElementById('cases-progress').style.width = (cases / max) * 100 + "%";
                document.getElementById('hearings-progress').style.width = (hearings / max) * 100 + "%";
                document.getElementById('resolved-progress').style.width = (resolved / max) * 100 + "%";
                document.getElementById('pending-progress').style.width = (pending / max) * 100 + "%";
                document.getElementById('mediated-progress').style.width = (mediated / max) * 100 + "%";
                document.getElementById('rejected-progress').style.width = (rejected / max) * 100 + "%";

                // Initialize Chart
                initChart();
            }

            function initChart() {
                const ctx = document.getElementById('statsChart').getContext('2d');
                const monthlyData = {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Complaints',
                            data: [5, 8, 12, 15, 20, 23],
                            borderColor: '#0c9ced',
                            backgroundColor: 'rgba(12, 156, 237, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Cases',
                            data: [3, 5, 7, 9, 12, 15],
                            borderColor: '#FBBF24',
                            backgroundColor: 'rgba(251, 191, 36, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Resolved',
                            data: [1, 3, 5, 6, 8, 10],
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        }
                    ]
                };

                new Chart(ctx, {
                    type: 'line',
                    data: monthlyData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false,
                                    color: '#f3f4f6'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }          // Call loadStatistics on page load
            document.addEventListener('DOMContentLoaded', function () {
                loadStatistics();
            });
        </script>


        <?php include '../chatbot/bpamis_case_assistant.php'; ?>
</body>

</html>