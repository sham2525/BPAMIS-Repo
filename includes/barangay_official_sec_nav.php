<?php
include '../server/server.php'; // Ensure connection is available

$notif_count = 0;

// Count all unread notifications for the secretary
$sqlNotif = "SELECT COUNT(*) AS count FROM notifications WHERE is_read = 0 AND type IN ('Unverified', 'Hearing', 'Complaint', 'Case')";
$resultNotif = $conn->query($sqlNotif);
if ($resultNotif && $row = $resultNotif->fetch_assoc()) {
    $notif_count = $row['count'];
}

// Count unverified residents (unverified accounts)
$sqlUnverified = "SELECT COUNT(*) AS count FROM resident_info WHERE isverify = 0";
$resultUnverified = $conn->query($sqlUnverified);
if ($resultUnverified && $row = $resultUnverified->fetch_assoc()) {
    $notif_count += $row['count'];
}
?>

<!-- Navigation Bar -->
<nav class="bg-white/95 backdrop-blur-md border-b border-blue-50 py-3 px-5 shadow-sm flex justify-between items-center sticky top-0 z-50">
    <div class="flex items-center space-x-3">
        <button id="menu-btn" class="text-blue-600 text-xl focus:outline-none hover:bg-blue-50 p-2 rounded-full transition-all duration-300">
            <i class="fas fa-bars"></i>
        </button>
        <a href="../SecMenu/home-secretary.php"><img src="../Assets/Img/logo.png" alt="Logo" class="w-10 h-10 rounded-full border-2 border-blue-200 drop-shadow-lg"></a>
        <h1 class="text-xl font-bold text-blue-700">BPAMIS</h1>
    </div>    
    <div class="hidden md:block mx-auto">
        <ul class="flex items-center space-x-6">
            <li>
                <a href="home-secretary.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Home">
                    <i class="fas fa-home text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Home</span>
                </a>
            </li>
            <li>
                <a href="add_complaints.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Add Complaint">
                    <i class="fas fa-plus-circle text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Add Complaint</span>
                </a>
            </li>
            <li>
                <a href="view_complaints.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="View Complaints">
                    <i class="fas fa-clipboard-list text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">View Complaints</span>
                </a>
            </li>
            <li>
                <a href="view_cases.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="View Cases">
                    <i class="fas fa-gavel text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">View Cases</span>
                </a>
            </li>
            
            <li>
                <a href="appoint_hearing.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Schedule Hearing">
                    <i class="fas fa-calendar-alt text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Schedule Hearing</span>
                </a>
            </li>
            <li>
                <a href="accounts.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Schedule Hearing">
                    <i class="fas fa-user-alt text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Users</span>
                </a>
            </li>
        </ul>    </div>
    
    <div class="flex items-center space-x-5">
        <!-- Notifications -->
        <div class="tooltip relative group">
            <a href="notifications-secretary.php" class="relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Notifications">
                <div class="relative">
                    <i class="fas fa-bell text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <?php if ($notif_count > 0): ?>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center shadow-sm">
                <?= $notif_count ?>
            </span>
                    <?php endif; ?>
                </div>
                <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Notifications</span>
            </a>
        </div>
        
        <!-- User Account Menu -->
        <div class="relative group">
            <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors duration-300 rounded-full px-3 py-1 hover:bg-blue-50">
                <div class="flex items-center justify-center rounded-full overflow-hidden border-2 border-blue-100 group-hover:border-blue-300 transition-colors">
                    <img src="../Assets/Img/secretary.gif" alt="User" class="w-8 h-8 rounded-full object-cover">
                </div>
                <span class="hidden md:inline text-sm font-medium">Barangay Official</span>
                <i class="fas fa-chevron-down text-xs opacity-70 group-hover:rotate-180 transition-transform duration-300"></i>
            </button>
            <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg py-2 border border-gray-100 invisible opacity-0 translate-y-1 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 ease-in-out">
                <div class="px-4 py-2 border-b border-gray-100">
                    <p class="text-xs text-gray-500">Signed in as</p>
                    <p class="text-sm font-medium text-gray-800">Barangay Secretary</p>
                </div>
                <a href="profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                    <i class="fas fa-user-circle mr-3 text-blue-500"></i>
                    <span>Your Profile</span>
                </a>
                
                <div class="border-t border-gray-100 my-1"></div>
                <a href="../bpamis_website/bpamis.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 group/logout transition-colors">
                    <i class="fas fa-sign-out-alt mr-3 text-red-500 group-hover/logout:translate-x-1 transition-transform"></i>
                    <span class="group-hover/logout:text-red-600 transition-colors">Sign out</span>
                </a>
            </div>        </div>
        
        <!-- Mobile menu button with animation -->
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar logic (existing)
        const menuButton = document.getElementById('menu-btn');
        const sidebar = document.getElementById('sidebar');
        if (menuButton && sidebar) {
            menuButton.addEventListener('click', function() {
                sidebar.classList.remove('-translate-x-full');
            });
        }
        document.addEventListener('click', function(event) {
            if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
                if (!sidebar.contains(event.target) && !menuButton.contains(event.target)) {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        });

        // User Account Menu Toggle: Desktop (hover), Mobile (click)
        const userMenuWrapper = document.querySelector('.relative.group');
        const userMenuBtn = userMenuWrapper ? userMenuWrapper.querySelector('button') : null;
        const userMenuDropdown = userMenuBtn ? userMenuBtn.nextElementSibling : null;
        let userMenuOpen = false;

        function isMobile() {
            return window.innerWidth < 768;
        }

        function openUserMenu() {
            userMenuDropdown.classList.remove('invisible', 'opacity-0', 'translate-y-1');
            userMenuDropdown.classList.add('visible', 'opacity-100', 'translate-y-0');
            userMenuOpen = true;
        }
        function closeUserMenu() {
            userMenuDropdown.classList.add('invisible', 'opacity-0', 'translate-y-1');
            userMenuDropdown.classList.remove('visible', 'opacity-100', 'translate-y-0');
            userMenuOpen = false;
        }

        if (userMenuBtn && userMenuDropdown) {
            // Mobile: click to toggle
            userMenuBtn.addEventListener('click', function(e) {
                if (isMobile()) {
                    e.stopPropagation();
                    if (userMenuOpen) {
                        closeUserMenu();
                    } else {
                        openUserMenu();
                    }
                }
            });
            // Mobile: close on menu item click
            userMenuDropdown.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (isMobile()) closeUserMenu();
                });
            });
            // Mobile: close on outside click
            document.addEventListener('click', function(e) {
                if (isMobile() && userMenuOpen && !userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                    closeUserMenu();
                }
            });
            // On resize, always close menu if switching to desktop
            window.addEventListener('resize', function() {
                if (!isMobile()) closeUserMenu();
            });
        }
    });
</script>

<style>

#mobile-menu::-webkit-scrollbar {
  display: none;
}
#mobile-menu {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
    /* Tooltip styles */
    .tooltip-text {
        z-index: 100;
        pointer-events: none;
    }
    
    /* Hover float effect */
    .hover-float:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    @keyframes wiggle {
      0%, 100% { transform: rotate(0); }
      25% { transform: rotate(-15deg); }
      50% { transform: rotate(0); }
      75% { transform: rotate(15deg); }
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-5px); }
    }
    
    .animate-wiggle:hover {
      animation: wiggle 0.4s ease-in-out 2;
    }
    
    .hover-float:hover {
      animation: float 2s ease-in-out infinite;
    }
    
    .hover-pulse:hover {
      animation: pulse 1s ease-in-out infinite;
    }
</style>
