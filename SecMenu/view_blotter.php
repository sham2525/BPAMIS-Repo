<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$blotters = [];
$result = $conn->query("SELECT * FROM BLOTTER_INFO ORDER BY Date_Reported DESC");
while ($row = $result->fetch_assoc()) {
    $blotters[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Blotter Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        }        /* Chatbot Button Styles */
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
      
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <?php include '../includes/barangay_official_sec_nav.php'; ?>

    <!-- Page Header -->
    <section class="container mx-auto mt-8 px-4">
        <div class="gradient-bg rounded-xl shadow-sm p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h1 class="text-2xl font-medium text-primary-800">Blotter Reports</h1>
                <p class="mt-1 text-gray-600">View and manage all barangay blotter reports</p>
            </div>
        </div>
    </section>
    
    <div class="container mx-auto mt-8 px-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">        
            <!-- Search Bar -->
            <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div class="relative w-full md:w-1/2">
                    <input type="text" id="searchInput" placeholder="Search blotter reports..." class="w-full p-3 pl-10 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-300">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex gap-2">
                    <a href="add_blotter.php" class="card-hover flex items-center bg-primary-500 text-white py-3 px-4 rounded-lg hover:bg-primary-600 transition">
                        <i class="fas fa-plus mr-2"></i> Add Blotter
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full mt-4">
                    <thead>
                        <tr>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">#</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Description</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Reported By</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Date Reported</th>
                            <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b-2 border-gray-100">Action</th>
                        </tr>
                    </thead>
                    <tbody id="blotterTable">
                        <?php if (count($blotters) > 0): ?>
                            <?php foreach ($blotters as $index => $b): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="p-3 text-sm text-gray-700 text-center"><?= $index + 1 ?></td>
                                    <td class="p-3 text-sm text-gray-700 truncate max-w-[250px]"><?= htmlspecialchars($b['Blotter_Description']) ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($b['Reported_By']) ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($b['Date_Reported']) ?></td>
                                    <td class="p-3 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="view_blotter_details.php?id=<?= $b['Blotter_ID'] ?>" class="text-primary-600 hover:text-primary-800 transition p-1" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-3 text-center text-gray-500">No blotter reports found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="mt-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
                    <div>
                        Showing 1-<?= count($blotters) ?> of <?= count($blotters) ?> entries
                    </div>
                    <div class="flex mt-4 md:mt-0">
                        <a href="#" class="mx-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition disabled:opacity-50">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </a>
                        <a href="#" class="mx-1 px-4 py-2 bg-primary-500 text-white rounded-lg transition">1</a>
                        <a href="#" class="mx-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition disabled:opacity-50">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
      <?php include 'sidebar_.php';?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function() {
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll('#blotterTable tr');

                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
            
            // Mobile navigation toggle
            if (typeof menuButton !== 'undefined' && typeof mobileMenu !== 'undefined') {
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
</body>
</html>
