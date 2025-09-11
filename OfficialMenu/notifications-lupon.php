<?php
session_start();

include '../server/server.php';

if (!isset($_SESSION['official_id'])) {
    header("Location: ../login.php");
    exit();
}

$luponId = $_SESSION['official_id'];

// Fetch notifications only for this Lupon and of relevant types
$sql = "SELECT * FROM notifications 
        WHERE lupon_id = ? 
          AND type IN ('Unverified', 'Hearing', 'Complaint', 'Case') 
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $luponId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
$stmt->close();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
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
                        'pulse-subtle': 'pulse-subtle 2s infinite',
                        'bell-ring': 'bell-ring 1s ease-in-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        'pulse-subtle': {
                            '0%, 100%': { opacity: 1 },
                            '50%': { opacity: 0.8 }
                        },
                        'bell-ring': {
                            '0%, 100%': { transform: 'rotate(0)' },
                            '20%, 60%': { transform: 'rotate(8deg)' },
                            '40%, 80%': { transform: 'rotate(-8deg)' }
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
        .notification-card {
            transition: all 0.2s ease;
        }
        .notification-card:hover {
            background-color: #f9fafc;
        }
        .unread-indicator {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #0c9ced;
            top: 22px;
            right: 22px;
        }
        
        /* Notification item styles */
        .notification-item {
            transition: all 0.2s ease;
        }
        .notification-item:hover {
            background-color: #f9fafb;
        }
        .notification-dot {
            transition: all 0.2s ease;
        }
        .notification-item:hover .notification-dot {
            transform: scale(1.2);
        }
        .gradient-bg {
            background: linear-gradient(to right, #f0f7ff, #e0effe);
        }
        
        /* Empty state animation */
        .empty-icon-container {
            animation: float 4s ease-in-out infinite;
        }
        
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <?php include_once ('../includes/barangay_official_lupon_nav.php'); ?>
    <?php include_once ('../chatbot/bpamis_case_assistant.php'); ?>
    <!-- Page Header -->
    <div class="w-full mt-10 px-4">
        <div class="gradient-bg rounded-2xl shadow-sm p-8 md:p-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-light text-primary-800">Your <span class="font-medium">Notifications</span></h2>
                    <p class="mt-3 text-gray-600 max-w-md">Stay updated with the latest activity in your cases and complaints.</p>
                </div>
                <div class="hidden md:flex items-center">
                    <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center animate-bell-ring">
                        <i class="fas fa-bell text-primary-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters & Search -->
    <div class="w-full mt-6 px-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-wrap justify-between items-center">
                <div class="flex flex-wrap items-center gap-2 mb-2 md:mb-0">
                    <button class="px-3 py-1 bg-primary-50 text-primary-700 rounded-lg text-sm font-medium border border-primary-100">All</button>
                    <button class="px-3 py-1 text-gray-500 rounded-lg text-sm hover:bg-gray-50">Unread</button>
                    <button class="px-3 py-1 text-gray-500 rounded-lg text-sm hover:bg-gray-50">Cases</button>
                    <button class="px-3 py-1 text-gray-500 rounded-lg text-sm hover:bg-gray-50">Hearings</button>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input 
                            type="text" 
                            placeholder="Search notifications..." 
                            class="pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-100 focus:border-primary-300 w-full"
                        >
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    <button class="text-primary-600 hover:text-primary-700 text-sm font-medium whitespace-nowrap">
                        <i class="fas fa-check-double mr-1"></i> Mark all as read
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
<div id="notification-regular">
    <div class="w-full mt-6 px-4 pb-10">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-100">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $row): ?>
                        <?php
                            // Choose icon and colors based on type
                            $icon = 'fa-bell';
                            $bgColor = 'bg-gray-100';
                            $iconColor = 'text-gray-600';

                            switch ($row['type']) {
                                case 'Hearing':
                                    $icon = 'fa-calendar-alt';
                                    $bgColor = 'bg-blue-100';
                                    $iconColor = 'text-blue-600';
                                    break;
                                case 'Case':
                                    $icon = 'fa-gavel';
                                    $bgColor = 'bg-yellow-100';
                                    $iconColor = 'text-yellow-600';
                                    break;
                            }

                            $isUnread = $row['is_read'] == 0;
                            $created = date("M d, Y \\a\\t h:i A", strtotime($row['created_at']));
                        ?>
                        <a href="view_notification.php?id=<?= $row['notification_id'] ?>" class="block">
                       <div class="notification-card p-5 relative cursor-pointer <?= $isUnread ? 'bg-gray-50' : '' ?>" 
     data-type="<?= strtolower($row['type']) ?>" 
     data-unread="<?= $isUnread ? 'true' : 'false' ?>">

                            <?php if ($isUnread): ?>
                                <div class="unread-indicator animate-pulse-subtle"></div>
                            <?php endif; ?>
                            <div class="flex">
                                <div class="flex-shrink-0 mr-4">
                                    <div class="w-10 h-10 <?= $bgColor ?> rounded-full flex items-center justify-center">
                                        <i class="fas <?= $icon ?> <?= $iconColor ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <p class="text-sm font-medium"><?= htmlspecialchars($row['title']) ?></p>
                                    <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($row['message']) ?></p>
                                    <div class="flex justify-between items-center mt-2">
                                        <p class="text-xs text-gray-500"><?= $created ?></p>
                                        <div class="flex gap-2">
                                            <a href="view_notification.php?id=<?= $row['notification_id'] ?>" class="text-primary-600 hover:text-primary-700 text-sm">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-5 text-sm text-gray-500">No notifications found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        <nav class="flex items-center space-x-1">
            <button class="p-2 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 disabled:opacity-50" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="px-3 py-1 rounded-md bg-primary-50 text-primary-600 font-medium">1</button>
            <button class="px-3 py-1 rounded-md text-gray-500 hover:bg-gray-100">2</button>
            <button class="p-2 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50">
                <i class="fas fa-chevron-right"></i>
            </button>
        </nav>
    </div>
    
    <div class="mt-6 flex justify-center">
        <button onclick="window.location.href='home-lupon.php'" class="px-4 py-2 text-gray-500 hover:text-gray-700 flex items-center transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </button>
    </div>

    <!-- No notifications state (hidden by default) -->
    <div id="no-notifications" class="hidden w-full mt-10 px-4 pb-10">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-10 text-center">
            <div class="flex justify-center mb-4">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-bell-slash text-gray-300 text-3xl"></i>
                </div>
            </div>
            <h3 class="text-lg font-medium text-gray-700 mb-2">No notifications yet</h3>
            <p class="text-gray-500 max-w-md mx-auto">When you receive new notifications about your cases or complaints, they will appear here.</p>
            <div class="mt-6">
                <button onclick="window.location.href='home-lupon.php'" class="px-4 py-2 bg-primary-50 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-100 transition-colors">
                    Return to Dashboard
                </button>
            </div>
        </div>
    </div>
    <?php include 'sidebar_lupon.php';?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.px-3.py-1.rounded-lg.text-sm');
        const searchInput = document.querySelector('input[type="text"]');
        const notificationCards = document.querySelectorAll('.notification-card');
        let activeFilter = 'All';

        function applyFilters() {
            const query = searchInput.value.toLowerCase();
            let hasResults = false;

            notificationCards.forEach(card => {
                const type = card.dataset.type;
                const isUnread = card.dataset.unread === 'true';
                const content = card.textContent.toLowerCase();
                let matchesFilter = false;

                if (activeFilter === 'All') {
                    matchesFilter = true;
                } else if (activeFilter === 'Unread') {
                    matchesFilter = isUnread;
                } else {
                    const normalizedFilter = activeFilter.toLowerCase().replace(/s$/, '');
                    matchesFilter = type === normalizedFilter;
                }

                const matchesSearch = content.includes(query);

                if (matchesSearch && matchesFilter) {
                    card.style.display = '';
                    hasResults = true;
                } else {
                    card.style.display = 'none';
                }
            });

            const container = document.querySelector('.divide-y.divide-gray-100').parentElement;

            if (!hasResults) {
                container.classList.add('hidden');
                document.getElementById('no-notifications').classList.remove('hidden');
                document.getElementById('no-notifications').querySelector('h3').textContent = 'No matching notifications';
            } else {
                container.classList.remove('hidden');
                document.getElementById('no-notifications').classList.add('hidden');
            }

            document.querySelector('.mt-6.flex.justify-center').classList.toggle('hidden', activeFilter !== 'All');
        }

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                filterButtons.forEach(btn => {
                    btn.classList.remove('bg-primary-50', 'text-primary-700', 'border', 'border-primary-100');
                    btn.classList.add('text-gray-500');
                });
                this.classList.remove('text-gray-500');
                this.classList.add('bg-primary-50', 'text-primary-700', 'border', 'border-primary-100');

                activeFilter = this.textContent.trim();
                applyFilters();
            });
        });

        searchInput.addEventListener('input', function() {
            applyFilters();
        });

        applyFilters();

        // Mark all as read functionality
        const markAllButton = document.querySelector('button:has(.fa-check-double)');
        markAllButton.addEventListener('click', function() {
            document.querySelectorAll('.unread-indicator').forEach(indicator => {
                indicator.classList.add('opacity-0');
                setTimeout(() => {
                    indicator.remove();
                }, 300);
            });
        });

        // Mobile menu toggle
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (menuButton && mobileMenu) {
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


