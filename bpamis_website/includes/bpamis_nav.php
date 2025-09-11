<?php
// Ultra-modern navigation bar for BPAMIS landing/dashboard
?>
<!-- Navigation Bar -->
<nav
    class="fixed-navbar bg-white/80 backdrop-blur-md border-b border-blue-100 shadow-lg flex items-center justify-between sticky top-0 z-50 transition-all duration-500">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-3">
            <div class="flex items-center space-x-3 relative" style="z-index:10;">
                <!-- Logo in a circle, sticking out of the navbar -->
                <div class="relative">
                    <span
                        class="flex items-center justify-center w-16 h-16 rounded-full border-4 border-white bg-gradient-to-br from-blue-100 via-white to-blue-50 shadow-xl">
                        <a href="bpamis.php"><img src="assets/images/logo.png" alt="Logo"
                                class="w-10 h-10 object-contain"></a>
                    </span>
                </div>
                <div class="flex flex-col mobile-text-container">
                    <h1 class="text-xl font-bold text-blue-700 tracking-wide drop-shadow-sm">BPAMIS</h1>
                    <h2 class="text-sm font-medium text-green-700 whitespace-nowrap">Barangay Panducot</h2>
                </div>
            </div>
            <div class="flex-1 flex justify-end sm:justify-center">
                <!-- Mobile Hamburger Menu -->
                <div class="block sm:hidden relative">
                    <button aria-label="Toggle navigation menu" aria-expanded="false" id="mobile-menu-button"
                        class="relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-100/60 focus:bg-blue-200/80 transition-all duration-300 shadow-sm">
                        <i class="fas fa-bars text-blue-600 text-lg transition-all duration-300 transform"
                            id="menu-icon-bars"></i>
                        <i class="fas fa-times text-blue-600 text-lg transition-all duration-300 absolute transform rotate-90 scale-0 opacity-0"
                            id="menu-icon-close"></i>
                    </button>
                </div>

                <!-- Desktop Navigation Icons -->
                <ul class="hidden sm:flex items-center space-x-6">
                    <li>
                        <a href="bpamis.php"
                            class="relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-100/60 focus:bg-blue-200/80 transition-all duration-300 shadow-sm">
                            <i
                                class="fas fa-home text-blue-600 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                            <span class="ml-2 font-medium text-gray-700 hidden md:inline"></span>
                            <span class="tooltip-text">Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="services.php"
                            class="relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-100/60 focus:bg-blue-200/80 transition-all duration-300 shadow-sm">
                            <i
                                class="fas fa-cogs text-blue-600 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                            <span class="ml-2 font-medium text-gray-700 hidden md:inline"></span>
                            <span class="tooltip-text">Services</span>
                        </a>
                    </li>
                    <li>
                        <a href="about.php"
                            class="relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-100/60 focus:bg-blue-200/80 transition-all duration-300 shadow-sm">
                            <i
                                class="fas fa-users text-blue-600 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                            <span class="ml-2 font-medium text-gray-700 hidden md:inline"></span>
                            <span class="tooltip-text">About Us</span>
                        </a>
                    </li>
                    <li>
                        <a href="contact.php"
                            class="relative group flex items-center justify-center p-3 rounded-full hover:bg-blue-100/60 focus:bg-blue-200/80 transition-all duration-300 shadow-sm">
                            <i
                                class="fas fa-envelope text-blue-600 text-lg group-hover:scale-125 group-hover:rotate-6 transition-all duration-300"></i>
                            <span class="ml-2 font-medium text-gray-700 hidden md:inline"></span>
                            <span class="tooltip-text">Contact Us</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Right side - Auth Buttons (Visible only on desktop) -->
            <div class="hidden sm:flex items-center space-x-2 sm:space-x-4">
                <a href="login.php"
                    class="nav-btn nav-btn-secondary bg-white/80 border-blue-600 text-blue-700 hover:bg-blue-50 hover:text-blue-800 transition-all duration-300 shadow-sm text-sm sm:text-base whitespace-nowrap">
                    Log In
                </a>
                <a href="register.php"
                    class="nav-btn nav-btn-primary bg-gradient-to-r from-blue-600 to-blue-400 text-white hover:from-blue-700 hover:to-blue-500 transition-all duration-300 shadow-md text-sm sm:text-base whitespace-nowrap">
                    Sign Up
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Slide-Down Menu -->
<div id="mobile-menu-panel"
    class="w-full bg-white/70 backdrop-blur-lg shadow-lg overflow-hidden max-h-0 transition-all duration-300 ease-in-out fixed left-0 right-0 z-40 border-b border-blue-100 slide-down-panel frosted-glass">
    <div class="container mx-auto px-4 py-0">
        <div class="flex flex-col py-2">
            <a href="bpamis.php"
                class="home-btn flex items-center px-4 py-4 text-base text-white font-medium hover:bg-blue-600 transition-all duration-200 rounded-md my-1">
                <i class="fas fa-home mr-3 w-6 text-center"></i>
                <span>Home</span>
            </a>
            <a href="services.php"
                class="flex items-center px-4 py-4 text-base text-gray-700 font-medium hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 rounded-md my-1 mobile-nav-item">
                <i class="fas fa-cogs text-blue-600 mr-3 w-6 text-center"></i>
                <span>Services</span>
            </a>
            <a href="about.php"
                class="flex items-center px-4 py-4 text-base text-gray-700 font-medium hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 rounded-md my-1 mobile-nav-item">
                <i class="fas fa-users text-blue-600 mr-3 w-6 text-center"></i>
                <span>About Us</span>
            </a>
            <a href="contact.php"
                class="flex items-center px-4 py-4 text-base text-gray-700 font-medium hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 rounded-md my-1 mobile-nav-item">
                <i class="fas fa-envelope text-blue-600 mr-3 w-6 text-center"></i>
                <span>Contact Us</span>
            </a>
            <div class="flex space-x-3 w-full mobile-horizontal-auth" style="margin-top: 10px;">
                <a href="login.php"
                    class="nav-btn nav-btn-secondary bg-white/80 border-blue-600 text-blue-700 hover:bg-blue-50 hover:text-blue-800 transition-all duration-200 shadow-sm text-sm sm:text-base whitespace-nowrap mobile-nav-item">
                     Log In
                </a>
                <a href="register.php"
                    class="nav-btn nav-btn-primary bg-gradient-to-r from-blue-600 to-blue-400 text-white hover:from-blue-700 hover:to-blue-500 transition-all duration-200 shadow-md text-sm sm:text-base whitespace-nowrap mobile-nav-item">
                     Sign Up
                </a>
            </div>

        </div>
    </div>
</div>

<style>
    .fixed-navbar {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-bottom: 1.5px solid #dbeafe;
        box-shadow: 0 8px 32px 0 rgba(37, 99, 235, 0.08);
    }

    /* Mobile Slide-Down Menu Styles */
    .slide-down-panel {
        transition: max-height 0.4s cubic-bezier(0.19, 1, 0.22, 1), opacity 0.3s ease;
        max-height: 0;
        opacity: 0;
        pointer-events: none;
    }

    .slide-down-panel.open {
        max-height: 100vh;
        opacity: 1;
        pointer-events: auto;
    }

    /* Mobile menu panel styles */
    #mobile-menu-panel {
        margin-top: 25px;
        /* Position just below the navbar */
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    /* Frosted glass effect for the mobile menu */
    .frosted-glass {
        background-color: rgba(255, 255, 255, 0.6);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    /* Add subtle parallax effect to the menu background */
    #mobile-menu-panel::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(125deg, rgba(255, 255, 255, 0.4), rgba(239, 246, 255, 0.2), rgba(255, 255, 255, 0.3));
        background-size: 200% 200%;
        animation: shimmer 6s infinite ease-in-out;
        pointer-events: none;
        z-index: -1;
    }

    @keyframes shimmer {
        0% {
            background-position: 0% 0%;
        }

        50% {
            background-position: 100% 100%;
        }

        100% {
            background-position: 0% 0%;
        }
    }

    #mobile-menu-panel a {
        font-weight: 500;
        position: relative;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    #mobile-menu-panel a:not(.home-btn):hover {
        background-color: rgba(239, 246, 255, 0.8);
        transform: translateX(4px);
    }

    .mobile-nav-item {
        position: relative;
        overflow: hidden;
    }

    .mobile-nav-item::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        height: 2px;
        width: 0;
        background-color: #3b82f6;
        transition: width 0.3s ease;
    }

    .mobile-nav-item:hover::after {
        width: 100%;
    }

    /* Active state for mobile nav items */
    .active-mobile-item {
        background-color: rgba(239, 246, 255, 0.8) !important;
        border-left: 4px solid #3b82f6 !important;
        padding-left: 12px !important;
        color: #1d4ed8 !important;
        font-weight: 600 !important;
    }

    background-color: rgba(239, 246, 255, 0.8);
    transform: translateX(4px);
    }

    #mobile-menu-panel a i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease;
    }

    #mobile-menu-panel a:hover i {
        transform: scale(1.15);
    }

    /* Slide-in animation for menu items */
    #mobile-menu-panel.open a {
        animation: slideInRight 0.4s forwards;
        opacity: 0;
        transform: translateX(-10px);
    }

    #mobile-menu-panel.open a:nth-child(1) {
        animation-delay: 0.05s;
    }

    #mobile-menu-panel.open a:nth-child(2) {
        animation-delay: 0.1s;
    }

    #mobile-menu-panel.open a:nth-child(3) {
        animation-delay: 0.15s;
    }

    #mobile-menu-panel.open a:nth-child(4) {
        animation-delay: 0.2s;
    }

    #mobile-menu-panel.open .flex.flex-row a:nth-child(1) {
        animation-delay: 0.25s;
    }

    #mobile-menu-panel.open .flex.flex-row a:nth-child(2) {
        animation-delay: 0.3s;
    }

    @keyframes slideInRight {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Mobile Menu Button Styles */
    #mobile-menu-button {
        position: relative;
        z-index: 60;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.8);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.1);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
    }

    #mobile-menu-button:hover {
        background: rgba(239, 246, 255, 0.95);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(37, 99, 235, 0.15);
    }

    #mobile-menu-button.active {
        background: rgba(59, 130, 246, 0.1);
    }

    #mobile-menu-button i {
        position: absolute;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        color: #2563eb; /* text-blue-600 */
        font-size: 1.25rem;
    }

    #mobile-menu-button:hover i {
        color: #1d4ed8; /* text-blue-700 */
    }

    /* Icon transition states */
    #menu-icon-bars {
        opacity: 1;
        transform: scale(1) rotate(0);
    }

    #menu-icon-close {
        opacity: 0;
        transform: scale(0) rotate(-90deg);
    }

    /* Active state transitions */
    #mobile-menu-button.active #menu-icon-bars {
        opacity: 0;
        transform: scale(0) rotate(90deg);
    }

    #mobile-menu-button.active #menu-icon-close {
        opacity: 1;
        transform: scale(1) rotate(0);
    }

    @keyframes navIconFloat {

        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        50% {
            transform: translateY(-6px) scale(1.08);
        }
    }

    /* Navbar Icon Container Styling */
    .fixed-navbar .group {
        background: rgba(255, 255, 255, 0);
        border-radius: 100%;
        width: 2.25rem;
        height: 2.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s, box-shadow 0.3s;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.04);
        position: relative;
    }

    .fixed-navbar .group:hover,
    .fixed-navbar .group:focus-within {
        background: rgba(30, 64, 175, 0);
        color: rgb(102, 119, 173);
        box-shadow: 0 6px 18px rgba(30, 64, 175, 0);
    }

    .fixed-navbar .group i {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        font-size: 1.35rem;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fixed-navbar .group:hover i {
        filter: drop-shadow(0 6px 16px rgba(255, 255, 255, 0.18));
        color: rgba(37, 100, 235, 0.62);
    }

    .nav-btn-primary {
        background: linear-gradient(90deg, #2563eb 0%, #3b82f6 100%);
        color: #fff;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.10);
        border: none;
        display: inline-block;
    }

    .nav-btn-primary:hover {
        background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 100%);
        color: #fff;
        transform: translateY(-2px) scale(1.04);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.15);
    }

    .nav-btn-secondary {
        background: rgba(255, 255, 255, 0.8);
        color: #2563eb;
        padding: 0.75rem 1.5rem;
        border: 2px solid #2563eb;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.05);
        display: inline-block;
    }

    .nav-btn-secondary:hover {
        background: #e0e7ff;
        color: #1d4ed8;
        border-color: #1d4ed8;
        transform: translateY(-2px) scale(1.04);
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.10);
    }

    .tooltip-text {
        z-index: 60;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        background: #1e40af;
        color: #fff;
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-top: 10px;
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        letter-spacing: 0.01em;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease;
        min-width: max-content;
        width: auto !important;
        text-align: center;
        display: block;
    }

    .tooltip-text::before {
        content: "";
        position: absolute;
        top: -5px;
        left: 50%;
        transform: translateX(-50%);
        border-width: 0 5px 5px 5px;
        border-style: solid;
        border-color: transparent transparent #1e40af transparent;
    }

    @keyframes tooltipAppear {
        0% {
            opacity: 0;
            transform: translateX(-50%) translateY(-5px);
        }

        100% {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }

    .group:hover .tooltip-text {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
        width: auto !important;
        animation: tooltipAppear 0.3s ease-out;
    }

    /* Home button styles */
    .home-btn {
        background-color: #2563eb !important;
        color: white !important;
        transition: all 0.3s ease;
    }

    .home-btn i {
        color: white !important;
    }

    .home-btn:hover {
        background-color: #1d4ed8 !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
    }

    .home-btn:active {
        transform: translateY(0);
        box-shadow: 0 5px 10px -3px rgba(37, 99, 235, 0.4);
    }

    /* Mobile dropdown panel styling */
    #mobile-dropdown-panel {
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.15);
        border: 1px solid rgba(219, 234, 254, 0.8);
        right: -8px;
        z-index: 100;
    }

    /* Active menu item styles */
    .active-mobile-item {
        font-weight: 600 !important;
    }

    /* Mobile menu transition adjustments */
    @media (max-width: 640px) {
        #mobile-menu-panel {
            top: 60px;
            /* Position just below the navbar */
        }

        /* Make sure navbar stays above other content */
        .fixed-navbar {
            z-index: 50 !important;
        }

        /* Enhance the blur effect on mobile menu */
        #mobile-menu-panel {
            background-color: rgba(255, 255, 255, 0.75) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
        }

        /* Make the menu items stand out against the blur */
        #mobile-menu-panel a {
            background-color: rgba(255, 255, 255, 0.6);
            margin: 2px 0;
            border-radius: 8px;
        }
    }

    /* Smaller menu button on mobile */
    @media (max-width: 640px) {
        #mobile-menu-button {
            width: 2.5rem;
            height: 2.5rem;
            background-color: rgba(219, 234, 254, 0.9);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        #mobile-menu-button i {
            font-size: 0.85rem;
            color: #1d4ed8;
        }

        /* Add a subtle pulse animation to draw attention to the menu button */
        #mobile-menu-button::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 8px;
            background-color: rgba(59, 131, 246, 0.16);
            z-index: -1;
            animation: pulse-attention 5s infinite;
        }

        @keyframes pulse-attention {
            0% {
                transform: scale(1);
                opacity: 0.7;
            }

            50% {
                transform: scale(1.15);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 0;
            }
        }
    }

    #mobile-dropdown-panel a {
        font-weight: 500;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        border-left: 3px solid transparent;
    }

    #mobile-dropdown-panel a:hover,
    #mobile-dropdown-panel a:focus {
        background-color: rgba(239, 246, 255, 0.8);
        border-left: 3px solid #2563eb;
    }

    #mobile-dropdown-panel a i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.5rem;
        transition: transform 0.2s ease;
    }

    #mobile-dropdown-panel a:hover i {
        transform: scale(1.15);
        color: #1e40af;
    }

    /* Mobile text container styles */
    @media (max-width: 640px) {
        .mobile-text-container {
            max-width: 110px;
        }

        .mobile-text-container h1 {
            line-height: 1.1;
            margin-bottom: 1px;
        }

        .mobile-text-container h2 {
            line-height: 1;
            font-size: 0.65rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
    }

    @media (max-width: 360px) {
        .mobile-text-container {
            max-width: 90px;
        }
        
        .mobile-text-container h1 {
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .mobile-text-container h2 {
            font-size: 0.55rem;
        }
    }

    /* Extra small screen adjustments */
    @media (max-width: 320px) {
        .mobile-text-container {
            max-width: 85px;
        }
        
        .mobile-text-container h1 {
            font-size: 0.85rem;
        }
        
        .mobile-text-container h2 {
            font-size: 0.5rem;
            letter-spacing: -0.01em;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .fixed-navbar {
            padding: 0 0.5rem;
        }

        .nav-btn-primary,
        .nav-btn-secondary {
            padding: 0.5rem 1rem;
            font-size: 0.95rem;
        }
    }

    @media (max-width: 640px) {

        /* Make logo and text container smaller on mobile */
        .fixed-navbar .flex.items-center.space-x-3 {
            column-gap: 0.5rem !important;
            /* Reduce space between logo and text */
        }

        /* Make the logo circle smaller on mobile */
        .fixed-navbar .w-16.h-16 {
            width: 3rem !important;
            height: 3rem !important;
        }

        /* Make the logo image smaller */
        .fixed-navbar .w-10.h-10 {
            width: 1.75rem !important;
            height: 1.75rem !important;
        }

        /* Reduced logo border */
        .fixed-navbar .border-4 {
            border-width: 2px !important;
        }

        .fixed-navbar {
            padding: 0.5rem;
        }

        .fixed-navbar .container {
            padding: 0 0.25rem;
        }

        /* Auth buttons mobile optimization */
        .nav-btn-primary,
        .nav-btn-secondary {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            letter-spacing: -0.01em;
            line-height: 1.1;
            min-width: 65px;
            text-align: center;
        }
    }

    @media (max-width: 360px) {
        .fixed-navbar .container {
            padding: 0 0.125rem;
        }

        .nav-btn-primary,
        .nav-btn-secondary {
            padding: 0.45rem 0.65rem;
            font-size: 0.8125rem;
            border-radius: 6px;
        }

        .fixed-navbar h1 {
            font-size: 1rem;
        }

        .fixed-navbar h2 {
            font-size: 0.75rem;
            white-space: nowrap;
        }

        /* Even smaller text for very small screens */
        @media (max-width: 480px) {
            .fixed-navbar h1 {
                font-size: 0.85rem;
            }

            .fixed-navbar h2 {
                font-size: 0.65rem;
                letter-spacing: -0.01em;
            }
        }
    }

    /* Mobile horizontal auth buttons */
    .mobile-horizontal-auth {
        display: flex;
        flex-direction: row;
        gap: 0.75rem;
        width: 100%;
    }

    .mobile-horizontal-auth .nav-btn {
        flex: 1;
        justify-content: center;
        padding: 0.75rem 0.5rem;
        font-size: 0.95rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .mobile-horizontal-auth .nav-btn:hover {
        transform: translateY(-2px);
    }

    .mobile-horizontal-auth .nav-btn:active {
        transform: translateY(0);
    }

    .mobile-horizontal-auth .nav-btn i {
        margin-right: 0.375rem;
        font-size: 0.875rem;
    }

    @media (max-width: 400px) {
        .mobile-horizontal-auth {
            gap: 0.5rem;
        }

        .mobile-horizontal-auth .nav-btn {
            padding: 0.625rem 0.375rem;
            font-size: 0.875rem;
        }

        .mobile-horizontal-auth .nav-btn i {
            font-size: 0.75rem;
        }
    }

    /* Horizontal auth buttons animation */
    #mobile-menu-panel.open .mobile-horizontal-auth {
        animation: fadeInUp 0.5s forwards 0.25s;
        opacity: 0;
        transform: translateY(10px);
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        /* Make mobile navbar burger menu items and buttons smaller */
        .mobile-nav-item,
        .home-btn,
        .nav-btn {
            font-size: 0.85rem !important;
            padding-top: 0.6rem !important;
            padding-bottom: 0.6rem !important;
        }
    }
</style>

<script>
    // Toggle mobile menu with slide-down animation
    document.addEventListener('DOMContentLoaded', function () {
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenuPanel = document.getElementById('mobile-menu-panel');
        const menuIconBars = document.getElementById('menu-icon-bars');
        const menuIconClose = document.getElementById('menu-icon-close');

        if (menuButton && mobileMenuPanel) {
            menuButton.addEventListener('click', function (e) {
                e.preventDefault();

                // Toggle ARIA expanded attribute for accessibility
                const isExpanded = menuButton.getAttribute('aria-expanded') === 'true';
                menuButton.setAttribute('aria-expanded', !isExpanded);

                // Toggle active class on button for animation
                this.classList.toggle('active');

                // Toggle mobile menu visibility with slide-down animation
                mobileMenuPanel.classList.toggle('open');

                // Toggle between hamburger and close icons
                menuIconBars.classList.toggle('hidden');
                menuIconClose.classList.toggle('hidden');
            });

            // Close menu when clicking on a navigation link
            const mobileMenuLinks = mobileMenuPanel.querySelectorAll('a');
            mobileMenuLinks.forEach(link => {
                link.addEventListener('click', function () {
                    // Close the menu when a link is clicked
                    mobileMenuPanel.classList.remove('open');
                    menuButton.classList.remove('active');
                    menuButton.setAttribute('aria-expanded', 'false');
                    menuIconBars.classList.remove('hidden');
                    menuIconClose.classList.add('hidden');
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', function (e) {
                if (!menuButton.contains(e.target) && !mobileMenuPanel.contains(e.target) &&
                    mobileMenuPanel.classList.contains('open')) {
                    mobileMenuPanel.classList.remove('open');
                    menuButton.classList.remove('active');
                    menuButton.setAttribute('aria-expanded', 'false');
                    menuIconBars.classList.remove('hidden');
                    menuIconClose.classList.add('hidden');
                }
            });
        }

        // Enhance tooltip functionality for touch devices
        const navLinks = document.querySelectorAll('.fixed-navbar ul li a');
        navLinks.forEach(link => {
            link.addEventListener('touchstart', function (e) {
                // Prevent default action only if tooltip is not visible yet
                const tooltip = this.querySelector('.tooltip-text');
                if (tooltip && getComputedStyle(tooltip).opacity === '0') {
                    e.preventDefault();

                    // Hide all other tooltips first
                    document.querySelectorAll('.tooltip-text').forEach(t => {
                        t.style.opacity = '0';
                        t.style.visibility = 'hidden';
                    });

                    // Show this tooltip
                    tooltip.style.opacity = '1';
                    tooltip.style.visibility = 'visible';
                    tooltip.style.transform = 'translateX(-50%) translateY(0)';
                    tooltip.style.width = 'auto';

                    // Auto-hide after 2 seconds
                    setTimeout(() => {
                        tooltip.style.opacity = '0';
                        tooltip.style.visibility = 'hidden';
                    }, 2000);
                }
            });
        });
    });

    // Auth button toggle functionality
    document.addEventListener('DOMContentLoaded', function () {
        const authButtons = document.querySelectorAll('.auth-btn');

        authButtons.forEach(button => {
            button.addEventListener('click', function (e) {
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

        // Highlight the active menu item based on current page
        highlightActivePage();
    });

    // Function to highlight the active page in both desktop and mobile menus
    function highlightActivePage() {
        const currentPage = window.location.pathname.split('/').pop();

        // Highlight desktop menu items
        document.querySelectorAll('.fixed-navbar ul li a').forEach(link => {
            const linkHref = link.getAttribute('href');
            if (linkHref === currentPage) {
                link.classList.add('active-nav-item');
                link.classList.add('bg-blue-100/60');
            }
        });

        // Highlight mobile menu items
        document.querySelectorAll('#mobile-menu-panel a').forEach(link => {
            const linkHref = link.getAttribute('href');
            if (linkHref === currentPage && !link.classList.contains('home-btn')) {
                link.classList.add('active-mobile-item');
                link.classList.add('bg-blue-100');
                link.classList.add('border-l-4');
                link.classList.add('border-blue-500');
                link.classList.add('pl-3');
            }
        });

        // Special case for home button
        if (currentPage === 'bpamis.php' || currentPage === '' || currentPage === '/' || !currentPage) {
            document.querySelectorAll('a.home-btn').forEach(homeBtn => {
                homeBtn.classList.add('active-home');
                homeBtn.classList.add('bg-blue-700');
            });
        }
    }
</script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>