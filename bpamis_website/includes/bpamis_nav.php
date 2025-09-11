<?php
// Ultra-modern navigation bar for BPAMIS landing/dashboard
?>
<!-- Navigation Bar -->
<nav class="fixed-navbar bg-white border-b border-blue-50 shadow-sm flex items-center justify-between sticky top-0 z-50">
    <div class="container mx-auto px-4"> <!-- Added container with matching padding -->
        <div class="flex items-center justify-between py-3"> <!-- Moved padding-y here -->
            <div class="flex items-center space-x-3 relative" style="z-index:10;">
                <!-- Logo in a circle, sticking out of the navbar -->
                <div class="relative" style="margin-top: 3px;"> <!-- Negative margin to stick out -->
                    <span class="flex items-center justify-center w-16 h-16 rounded-full border-4 border-white bg-white shadow-lg" style="box-shadow: 0 4px 16px rgba(0,0,0,0.10);">
                        <img src="logo.png" alt="Logo" class="w-10 h-10 object-contain">
                    </span>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-bold text-blue-600 ">BPAMIS</h1>
                    <h2 class="text-sm font-medium " style="color: #489a59;">Barangay Panducot</h2>
                </div>
            </div>
            <div class="flex-1 flex justify-center">
                <ul class="flex items-center space-x-6">
                    <li>
                        <a href="bpamis.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Home">
                            <i class="fas fa-home text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                            <span class="ml-2 font-medium text-gray-700 hidden md:inline"></span>
                            <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="services.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Services">
                            <i class="fas fa-cogs text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                            <span class="ml-2 font-medium text-gray-700 hidden md:inline"></span>
                            <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Services</span>
                        </a>
                    </li>
                    <li>
                        <a href="about.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="About Us">
                            <i class="fas fa-users text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                            <span class="ml-2 font-medium text-gray-700 hidden md:inline"></span>
                            <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">About Us</span>
                        </a>
                    </li>
                    <li>
                        <a href="contact.php" class="tooltip relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-50 transition-all duration-300" data-tooltip="Contact Us">
                            <i class="fas fa-envelope text-blue-500 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                            <span class="ml-2 font-medium text-gray-700 hidden md:inline"></span>
                            <span class="tooltip-text absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Contact Us</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Right side - Auth Buttons -->
            <div class="flex items-center space-x-4">
                <a href="login.php" class="nav-btn nav-btn-secondary">
                    Log In
                </a>
                <a href="register.php" class="nav-btn nav-btn-primary">
                    Sign Up
                </a>
            </div>
        </div>
    </div>
</nav>

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

.auth-btn {
    position: relative;
    overflow: hidden;
}

.auth-btn[data-active="true"] {
    background-color: #2563eb;
    color: white;
    border-radius: 9999px;
}

.auth-btn[data-active="false"] {
    background-color: transparent;
    color: #2563eb;
}

.auth-btn[data-active="false"]:hover {
    background-color: #f0f9ff;
    border-radius: 9999px;
}

.nav-btn {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    z-index: 1;
}

.nav-btn-primary {
    background: #2563eb;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
}

.nav-btn-secondary {
    background: transparent;
    color: #2563eb;
    padding: 0.75rem 1.5rem;
    border: 2px solid #2563eb;
    border-radius: 8px;
    font-weight: 500;
}

.nav-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transform: translateX(-100%);
    transition: 0.6s;
    z-index: -1;
}

.nav-btn:hover::before {
    transform: translateX(100%);
}

.nav-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.nav-btn-secondary:hover {
    color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
    background: rgba(37, 99, 235, 0.05);
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
    
    // Auth button toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const authButtons = document.querySelectorAll('.auth-btn');
        
        authButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Don't prevent default here to allow navigation
                
                // Reset all buttons
                authButtons.forEach(btn => {
                    btn.dataset.active = "false";
                });
                
                // Activate clicked button
                this.dataset.active = "true";
                
                // Store active state in session storage
                sessionStorage.setItem('activeAuthButton', this.getAttribute('href'));
            });
        });
        
        // Check for active state on page load
        const activeButton = sessionStorage.getItem('activeAuthButton');
        if (activeButton) {
            const button = document.querySelector(`a[href="${activeButton}"]`);
            if (button) {
                authButtons.forEach(btn => btn.dataset.active = "false");
                button.dataset.active = "true";
            }
        }
    });
</script>
