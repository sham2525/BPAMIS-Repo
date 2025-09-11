<?php
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$blotter_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM BLOTTER_INFO WHERE Blotter_ID = ?");
$stmt->bind_param("i", $blotter_id);
$stmt->execute();
$result = $stmt->get_result();
$blotter = $result->fetch_assoc();
$stmt->close();

// Fetch reporter name if Reported_By is an ID
$reporter_name = $blotter['Reported_By'];
if (is_numeric($blotter['Reported_By'])) {
    $stmt = $conn->prepare("SELECT CONCAT(First_Name, ' ', Last_Name) AS full_name FROM resident_info WHERE resident_id = ?");
    $stmt->bind_param("i", $blotter['Reported_By']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $reporter_name = $row['full_name'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blotter Details</title>
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
                <h1 class="text-2xl font-medium text-primary-800">Blotter Report Details</h1>
                <p class="mt-1 text-gray-600">View detailed information about the blotter report</p>
            </div>
        </div>
    </section>
    
    <div class="container mx-auto mt-8 px-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <?php if ($blotter): ?>
                <!-- Blotter Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-user text-primary-600 mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-800">Reported By</h3>
                        </div>
                        <p class="text-gray-700 font-medium"><?= htmlspecialchars($reporter_name) ?></p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-calendar text-primary-600 mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-800">Date Reported</h3>
                        </div>
                        <p class="text-gray-700 font-medium"><?= htmlspecialchars($blotter['Date_Reported']) ?></p>
                    </div>
                </div>
                
                <!-- Description Section -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-file-alt text-primary-600 mr-3"></i>
                        <h3 class="text-lg font-semibold text-gray-800">Description</h3>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <p class="whitespace-pre-wrap text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($blotter['Blotter_Description'])) ?></p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <a href="view_blotter.php" class="card-hover flex items-center justify-center bg-gray-500 text-white py-3 px-6 rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to List
                    </a>
                    <a href="add_blotter.php" class="card-hover flex items-center justify-center bg-primary-500 text-white py-3 px-6 rounded-lg hover:bg-primary-600 transition">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Blotter
                    </a>
                </div>
            <?php else: ?>
                <!-- Error State -->
                <div class="text-center py-12">
                    <div class="bg-red-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Blotter Report Not Found</h3>
                    <p class="text-gray-600 mb-6">The blotter report you're looking for doesn't exist or has been removed.</p>
                    <a href="view_blotter.php" class="card-hover inline-flex items-center bg-primary-500 text-white py-3 px-6 rounded-lg hover:bg-primary-600 transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to List
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    </div>
      <?php include 'sidebar_.php';?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
    <?php include '../chatbot/bpamis_case_assistant.php'?>
</body>
</html>
