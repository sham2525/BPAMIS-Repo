<?php
include '../server/server.php'; 
$resident_id = $_SESSION['user_id'] ?? 0;
// Fetch the resident's name
$query = "SELECT first_name, last_name FROM external_complainant WHERE external_complaint_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$result = $stmt->get_result();

$full_name = "Resident";
if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $full_name = $row['first_name'] . ' ' . $row['last_name'];
}

$unread_count = 0;
if ($resident_id > 0) {
    $sql = "SELECT COUNT(*) as total FROM notifications WHERE external_complaint_id = $resident_id AND is_read = 0";

    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $unread_count = (int)$row['total'];
    }
}

?>
<!-- Navigation Bar -->
<nav class="bg-white/95 backdrop-blur-md border-b border-blue-50 py-3 px-5 shadow-sm flex justify-between items-center sticky top-0 z-50">    <div class="flex items-center space-x-3">
        <img src="../Assets/Img/logo.png" alt="Logo" class="w-10 h-10 rounded-full border-2 border-blue-200 drop-shadow-lg">
        <h1 class="text-xl font-bold text-blue-700">BPAMIS</h1>
    </div><div class="hidden md:block mx-auto">
        <ul class="flex items-center space-x-6">
            <li>
                <a href="home-external.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Home">
                    <i class="fas fa-home text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Home</span>
                </a>
            </li>
            <li>
                <a href="submit_complaints.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Submit Complaint">
                    <i class="fas fa-file-alt text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Submit Complaint</span>
                </a>
            </li>
            <li>
                <a href="view_complaints.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="View Complaints">
                    <i class="fas fa-clipboard-list text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">View Complaints</span>
                </a>
            </li>
            <li>                <a href="view_cases.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="View Cases">
                    <i class="fas fa-gavel text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">View Cases</span>
                </a>
            </li>
           <li>
                <a href="notifications.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Notifications">
                    <div class="relative">
                        <i class="fas fa-bell text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                      <?php if ($unread_count > 0): ?>
    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full h-4 min-w-[16px] px-1 flex items-center justify-center shadow-sm group-hover:scale-125 transition-transform">
        <?= $unread_count ?>
    </span>
<?php endif; ?>
                    </div>
                    <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Notifications</span>
                </a>
            </li>
        </ul>
    </div>
      <div class="flex items-center space-x-5">   
          <!-- User Account Menu -->
        <div class="relative group">
            <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors duration-300 rounded-full px-3 py-1 hover:bg-blue-50">
                <div class="flex items-center justify-center rounded-full overflow-hidden border-2 border-blue-100 group-hover:border-blue-300 transition-colors">
                    <img src="../Assets/Img/resident.gif" alt="User" class="w-8 h-8 rounded-full object-cover">
                </div>
                <span class="hidden md:inline text-sm font-medium">External Complainant</span>
                <i class="fas fa-chevron-down text-xs opacity-70 group-hover:rotate-180 transition-transform duration-300"></i>
            </button>
            <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg py-2 border border-gray-100 invisible opacity-0 translate-y-1 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 ease-in-out">
                <div class="px-4 py-2 border-b border-gray-100">
                    <p class="text-xs text-gray-500">Signed in as</p>
                     <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($full_name) ?></p>
                </div>
                <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                    <i class="fas fa-user-circle mr-3 text-blue-500"></i>
                    <span>Your Profile</span>
                </a>
               
                <div class="border-t border-gray-100 my-1"></div>
                <a href="../bpamis_website/bpamis.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 group/logout transition-colors">
                    <i class="fas fa-sign-out-alt mr-3 text-red-500 group-hover/logout:translate-x-1 transition-transform"></i>
                    <span class="group-hover/logout:text-red-600 transition-colors">Sign out</span>
                </a>
            </div>
        </div>
        
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
    <div class="bg-white border-b border-gray-100 shadow-md rounded-b-2xl mx-4">        <div class="px-3 py-4 flex flex-wrap justify-center gap-4">
            <a href="home-external.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-gray-700 hover:bg-blue-50 hover-float transition-all">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <i class="fas fa-home text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-medium">Home</span>
            </a>
            <a href="submit_complaints.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-gray-700 hover:bg-blue-50 hover-float transition-all">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-medium">Submit</span>
            </a>
            <a href="view_complaints.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-gray-700 hover:bg-blue-50 hover-float transition-all">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-medium">Complaints</span>
            </a>            <a href="view_cases.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-gray-700 hover:bg-blue-50 hover-float transition-all">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <i class="fas fa-gavel text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-medium">Cases</span>
            </a>
            <a href="notifications.php" class="w-24 h-24 flex flex-col items-center justify-center rounded-xl text-gray-700 hover:bg-blue-50 hover-float transition-all">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-2 shadow-sm">
                    <div class="relative">
                        <i class="fas fa-bell text-blue-600 text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center shadow-sm">3</span>
                    </div>
                </div>
                <span class="text-xs font-medium">Notifications</span>
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

/* Tooltip style */
.tooltip {
  position: relative;
}

.tooltip-text {
  z-index: 60;
  pointer-events: none;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
}

.tooltip-text::after {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 50%;
  margin-left: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: transparent transparent #1f2937 transparent;
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
    });
</script>
