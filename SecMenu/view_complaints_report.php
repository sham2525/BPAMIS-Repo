<?php
/**
 * View Complaints Report Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaints Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include '../includes/case_assistant_styles.php'; ?>
</head>
<body class="bg-blue-50">
    <?php include '../includes/barangay_official_nav.php'; ?>
    
    <div class="container mx-auto mt-10 p-5 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-semibold text-blue-800 mb-4">Complaints Report</h2>
        
        <!-- Filter Options -->
        <div class="mb-4 flex flex-wrap gap-4">
            <div>
                <label for="dateRange" class="block text-sm font-medium text-gray-700">Date Range</label>
                <select id="dateRange" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="all">All Time</option>
                    <option value="this_month" selected>This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Under Investigation</option>
                    <option value="scheduled">Scheduled for Hearing</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                </select>
            </div>
            
            <div class="ml-auto">
                <label class="invisible block text-sm font-medium text-gray-700">Export</label>
                <div class="flex gap-2">
                    <button id="printReport" class="bg-gray-700 text-white px-3 py-2 rounded hover:bg-gray-800">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button id="exportPDF" class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button id="exportExcel" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Complaints by Status</h3>
                <canvas id="statusChart" height="200"></canvas>
            </div>
            <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Monthly Complaint Trends</h3>
                <canvas id="trendsChart" height="200"></canvas>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full mt-4 border-collapse">
                <thead>
                    <tr class="bg-blue-200">
                        <th class="p-2 text-left">Complaint ID</th>
                        <th class="p-2 text-left">Title</th>
                        <th class="p-2 text-left">Complainant</th>
                        <th class="p-2 text-left">Date Filed</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-left">Resolution Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // In a real application, this would be populated from database
                    // For now, we'll use sample data
                    $sampleComplaints = [
                        [
                            'id' => 'C2025-001',
                            'title' => 'Noise Complaint',
                            'complainant' => 'Juan Dela Cruz',
                            'date_filed' => '2025-05-01',
                            'status' => 'Pending',
                            'resolution_time' => 'N/A'
                        ],
                        [
                            'id' => 'C2025-002',
                            'title' => 'Property Boundary Dispute',
                            'complainant' => 'Maria Garcia',
                            'date_filed' => '2025-05-03',
                            'status' => 'Scheduled for Hearing',
                            'resolution_time' => 'N/A'
                        ],
                        [
                            'id' => 'C2025-003',
                            'title' => 'Unpaid Debt',
                            'complainant' => 'Elena Ramos',
                            'date_filed' => '2025-04-28',
                            'status' => 'Resolved',
                            'resolution_time' => '10 days'
                        ],
                        [
                            'id' => 'C2025-004',
                            'title' => 'Threat Complaint',
                            'complainant' => 'Ricardo Dalisay',
                            'date_filed' => '2025-05-05',
                            'status' => 'Under Investigation',
                            'resolution_time' => 'N/A'
                        ],
                        [
                            'id' => 'C2025-005',
                            'title' => 'Trespassing',
                            'complainant' => 'Pedro Penduko',
                            'date_filed' => '2025-04-20',
                            'status' => 'Dismissed',
                            'resolution_time' => '8 days'
                        ]
                    ];
                    
                    foreach ($sampleComplaints as $complaint) {
                        echo '<tr class="border-b hover:bg-gray-100">';
                        echo '<td class="p-2">' . $complaint['id'] . '</td>';
                        echo '<td class="p-2">' . $complaint['title'] . '</td>';
                        echo '<td class="p-2">' . $complaint['complainant'] . '</td>';
                        echo '<td class="p-2">' . $complaint['date_filed'] . '</td>';
                        
                        // Set status color based on status value
                        $statusClass = '';
                        switch ($complaint['status']) {
                            case 'Pending':
                                $statusClass = 'text-orange-600';
                                break;
                            case 'Under Investigation':
                                $statusClass = 'text-blue-600';
                                break;
                            case 'Scheduled for Hearing':
                                $statusClass = 'text-purple-600';
                                break;
                            case 'Resolved':
                                $statusClass = 'text-green-600';
                                break;
                            case 'Dismissed':
                                $statusClass = 'text-gray-600';
                                break;
                            default:
                                $statusClass = '';
                        }
                        
                        echo '<td class="p-2 ' . $statusClass . '">' . $complaint['status'] . '</td>';
                        echo '<td class="p-2">' . $complaint['resolution_time'] . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Summary Statistics -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-100 p-4 rounded-lg shadow-sm">
                <h4 class="text-sm font-semibold text-blue-800">Total Complaints</h4>
                <p class="text-2xl font-bold">5</p>
            </div>
            <div class="bg-orange-100 p-4 rounded-lg shadow-sm">
                <h4 class="text-sm font-semibold text-orange-800">Pending</h4>
                <p class="text-2xl font-bold">1</p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg shadow-sm">
                <h4 class="text-sm font-semibold text-green-800">Resolved</h4>
                <p class="text-2xl font-bold">1</p>
            </div>
            <div class="bg-purple-100 p-4 rounded-lg shadow-sm">
                <h4 class="text-sm font-semibold text-purple-800">Avg Resolution Time</h4>
                <p class="text-2xl font-bold">9 days</p>
            </div>
        </div>
    </div>
    
    <script>
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Pending', 'Under Investigation', 'Scheduled for Hearing', 'Resolved', 'Dismissed'],
                datasets: [{
                    label: 'Complaints by Status',
                    data: [1, 1, 1, 1, 1],
                    backgroundColor: [
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(201, 203, 207, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        const trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Number of Complaints',
                    data: [2, 3, 4, 3, 5, 0],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // Print button functionality
        document.getElementById('printReport').addEventListener('click', function() {
            window.print();
        });
          // For a real application, you would implement PDF and Excel export functionality
        // This could use libraries like jsPDF and SheetJS, or make requests to server-side code
    </script>
    
    <?php include '../includes/case_assistant.php'; ?>
</body>
</html>
