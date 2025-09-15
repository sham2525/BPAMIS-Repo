<?php
include '../server/server.php'; // Ensure connection is available

$notif_count = 0;

// Count notifications about complaints only (you can adjust 'type' or 'title' accordingly)
$sqlComplaints = "SELECT COUNT(*) AS count FROM notifications WHERE is_read = 0 AND title LIKE '%complaint%'";
$resultComplaints = $conn->query($sqlComplaints);
if ($resultComplaints && $row = $resultComplaints->fetch_assoc()) {
    $notif_count += $row['count'];
}

// Count unverified residents
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
        <img src="../Assets/Img/logo.png" alt="Logo" class="w-10 h-10 rounded-full border-2 border-blue-200 drop-shadow-lg">
        <h1 class="text-xl font-bold text-blue-700">BPAMIS</h1>
    </div>    
    <div class="hidden md:block mx-auto">
        <ul class="flex items-center space-x-6">
            <li>
                <a href="home-captain.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Home">
                    <i class="fas fa-home text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Home</span>
                </a>
            </li>
            <li>
                <a href="feedback_captain.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Add Feedback">
                    <i class="fas fa-plus-circle text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Add Feedback</span>
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
                <a href="view_hearing_calendar_captain.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="View Hearing">
                    <i class="fas fa-calendar-alt text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">View Hearing</span>
                </a>
            </li>
        </ul>    </div>
    
    <div class="flex items-center space-x-5">
        <!-- Notifications -->
        <div class="tooltip relative group">
            <a href="notifications-captain.php" class="relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Notifications">
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
                    <p class="text-sm font-medium text-gray-800">official@barangay.gov.ph</p>
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
        <button class="md:hidden relative w-10 h-10 flex justify-center items-center rounded-full hover:bg-blue-50 text-gray-600 hover:text-blue-600 transition-colors focus:outline-none group" id="mobile-menu-button">
            <div class="w-6 flex flex-col items-center justify-center">
                <span class="w-full h-0.5 bg-current rounded transform transition-all duration-300 group-[.active]:translate-y-1.5 group-[.active]:rotate-45"></span>
                <span class="w-full h-0.5 bg-current rounded my-1 transition-all duration-300 group-[.active]:opacity-0"></span>
                <span class="w-full h-0.5 bg-current rounded transform transition-all duration-300 group-[.active]:-translate-y-1.5 group-[.active]:-rotate-45"></span>
            </div>
        </button>
    </div>
</nav>

<!-- Mobile menu (hidden by default) -->
<div class="md:hidden fixed inset-x-0 top-[61px] transform transition-transform duration-300 ease-in-out translate-y-[-100%]" id="mobile-menu">
    <div class="bg-white border-b border-gray-100 shadow-md rounded-b-2xl mx-4">
        <div class="px-3 py-4 flex flex-wrap justify-center gap-4">
            <a href="home.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-gray-700 hover:bg-blue-50 hover-float transition-all">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <i class="fas fa-home text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-medium">Home</span>
            </a>
            <a href="add_complaints.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-gray-700 hover:bg-blue-50 hover-float transition-all">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <i class="fas fa-plus-circle text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-medium">Add Complaint</span>
            </a>
            <a href="view_complaints.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-gray-700 hover:bg-blue-50 hover-float transition-all">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-medium">Complaints</span>
            </a>
            <a href="view_cases.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-gray-700 hover:bg-blue-50 hover-float transition-all">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <i class="fas fa-gavel text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-medium">Cases</span>
            </a>
            <a href="appoint_hearing.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-gray-700 hover:bg-blue-50 hover-float transition-all">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-medium">Hearings</span>
            </a>
            <a href="../login.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-red-600 hover:bg-red-50 hover-float transition-all">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <i class="fas fa-sign-out-alt text-red-500 text-xl"></i>
                </div>
                <span class="text-xs font-medium">Sign out</span>
            </a>
        </div>
    </div>
</div>

<style>
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

<script>
    // Toggle mobile menu with animation
    document.addEventListener('DOMContentLoaded', function() {
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        menuButton.addEventListener('click', function() {
            // Toggle active class on button for animation
            this.classList.toggle('active');
            
            // Toggle mobile menu visibility with transform
            if (mobileMenu.style.transform === 'translateY(0%)' || mobileMenu.style.transform === '') {
                mobileMenu.style.transform = 'translateY(-100%)';
            } else {
                mobileMenu.style.transform = 'translateY(0%)';
            }
        });
        
        // Toggle sidebar for desktop
        const menuBtn = document.getElementById('menu-btn');
        const sidebar = document.getElementById('sidebar');
        const closeSidebar = document.getElementById('close-sidebar');
        
        if (menuBtn && sidebar) {
            menuBtn.addEventListener('click', function() {
                sidebar.classList.remove('-translate-x-full');
            });
        }
        
        if (closeSidebar && sidebar) {
            closeSidebar.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
            });
        }
        
        // Toggle submenu items
        const toggleMenuButtons = document.querySelectorAll('.toggle-menu');
        if (toggleMenuButtons) {
            toggleMenuButtons.forEach(button => {
                button.addEventListener('click', function() {
                    let submenu = this.nextElementSibling;
                    submenu.classList.toggle('hidden');
                });
            });
        }
    });
</script>
