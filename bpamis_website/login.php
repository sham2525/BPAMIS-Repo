<?php session_start();?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPAMIS Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="styles/premium-animations.css">
    <link rel="stylesheet" href="styles/premium-patterns.css">

    <style>
        body {
            background: #e8f0fe;
            min-height: 100vh;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            width: 100%;
            max-width: 1200px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            animation: none !important;
            opacity: 1 !important;
            transform: none !important;
        }

        /* Premium container effects */
        .premium-stats-container {
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transform: translateY(0);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        .premium-stats-container:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        /* Animated gradient background */
        .premium-gradient-bg {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                    rgba(59, 130, 246, 0.3),
                    rgba(16, 185, 129, 0.3),
                    rgba(236, 72, 153, 0.3),
                    rgba(139, 92, 246, 0.3));
            animation: rotate-gradient 8s linear infinite;
            z-index: 0;
        }

        @keyframes rotate-gradient {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Floating particles effect */
        .premium-particles {
            position: absolute;
            inset: 0;
            z-index: 1;
            overflow: hidden;
        }

        .premium-particles::before,
        .premium-particles::after,
        .premium-particles .particle {
            content: '';
            position: absolute;
            background: white;
            border-radius: 50%;
            opacity: 0.4;
            animation-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);
            animation-iteration-count: infinite;
        }

        .premium-particles::before {
            width: 6px;
            height: 6px;
            top: 20%;
            left: 20%;
            animation: float-particle 4s infinite;
        }

        .premium-particles::after {
            width: 10px;
            height: 10px;
            bottom: 15%;
            right: 30%;
            animation: float-particle 7s infinite 1s;
        }

        /* Additional particles */
        .particle-1 {
            width: 5px;
            height: 5px;
            top: 65%;
            left: 75%;
            background-color: rgba(59, 130, 246, 0.5);
            /* blue */
            animation: float-particle 6s infinite 0.5s;
        }

        .particle-2 {
            width: 8px;
            height: 8px;
            top: 30%;
            left: 60%;
            background-color: rgba(16, 185, 129, 0.5);
            /* green */
            animation: float-particle 8s infinite 2s;
        }

        .particle-3 {
            width: 4px;
            height: 4px;
            top: 70%;
            left: 25%;
            background-color: rgba(236, 72, 153, 0.5);
            /* pink */
            animation: float-particle 5s infinite 1.5s;
        }

        .particle-4 {
            width: 7px;
            height: 7px;
            top: 10%;
            left: 85%;
            background-color: rgba(139, 92, 246, 0.5);
            /* purple */
            animation: float-particle 7s infinite 1s;
        }

        @keyframes float-particle {

            0%,
            100% {
                transform: translateY(0) translateX(0);
                opacity: 0.2;
            }

            25% {
                transform: translateY(-15px) translateX(10px);
                opacity: 0.6;
            }

            50% {
                transform: translateY(5px) translateX(15px);
                opacity: 0.4;
            }

            75% {
                transform: translateY(10px) translateX(-5px);
                opacity: 0.6;
            }
        }

        /* Fade-in animation for mobile */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mobile-fade-in {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .mobile-fade-in-delay-1 {
            animation: fadeInUp 0.8s ease-out 0.1s forwards;
            opacity: 0;
        }

        .mobile-fade-in-delay-2 {
            animation: fadeInUp 0.8s ease-out 0.2s forwards;
            opacity: 0;
        }

        .mobile-fade-in-delay-3 {
            animation: fadeInUp 0.8s ease-out 0.3s forwards;
            opacity: 0;
        }

        .mobile-fade-in-delay-4 {
            animation: fadeInUp 0.8s ease-out 0.4s forwards;
            opacity: 0;
        }

        .mobile-fade-in-delay-5 {
            animation: fadeInUp 0.8s ease-out 0.5s forwards;
            opacity: 0;
        }

        .form-container {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            min-height: 600px;
        }

        .login-left {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(37, 99, 235, 0.25) 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            position: relative;
            z-index: 1;
        }

        .login-left::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.05);
            z-index: -1;
        }

        .login-right {
            padding: 3rem;
            background: white;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
        }
        
        /* Password toggle button positioning */
        .input-group button.password-toggle {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #9CA3AF;
            cursor: pointer;
            z-index: 2;
            padding: 0;
            width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-field {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            background: #F9FAFB;
            transition: all 0.3s ease;
        }
        
        /* Password field should have right padding to accommodate the icon */
        input[type="password"].input-field {
            padding-right: 3rem;
        }

        .input-field:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .login-btn {
            background: #2563eb;
            /* BPAMIS blue color */
            color: #ffffff;
            /* White text */
            padding: 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background: #1d4ed8;
            /* Darker blue on hover */
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.1);
        }
        
        /* Password toggle icon styling */
        .password-toggle {
            cursor: pointer;
            transition: color 0.2s ease;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: #4B5563;
        }

        .social-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #E5E7EB;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
            }

            .login-left {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 2rem 1rem;
                background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(37, 99, 235, 0.25) 100%);
            }

            .login-right {
                display: none;
            }

            .login-container {
                margin: 1rem;
            }

            .text-4xl {
                font-size: 0.9rem;
                text-align: center;
            }

            #login_user.input-field {
                font-size: 0.7rem;
            }

            #login_pass.input-field {
                font-size: 0.7rem;
            }

            .text-sm {
                font-size: 0.7rem;
            }

            .text-gray-600 {
                font-size: 0.7rem;
            }

            .login-button {
                font-size: 0.8rem;
                font-weight: 600;
            }

            .register-link {
                font-size: 0.7rem;
            }
            
            .mobile-login-form h3 {
                font-size: 1rem;
            }
            
            .mobile-login-form .input-field {
                font-size: 1rem;
                padding: 0.75rem 1rem 0.75rem 2.5rem;
            }
            
            .mobile-login-form .checkbox-group label span {
                font-size: 0.7rem;
            }
            
            .mobile-login-form .forgot-password {
                font-size: 0.9rem;
            }
        }
        
        /* Styling for the login form */
        #loginForm {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        .forms-container {
            position: relative;
            width: 200%;
            display: flex;
            transition: transform 0.6s ease-in-out;
        }

        .forms-container.show-register {
            transform: translateX(-50%);
        }

        /* Flip card styles */
        .flip-card-container {
            perspective: 1000px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .flip-card {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
        }

        .flip-card-front,
        .flip-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 24px;
            overflow: hidden;
        }

        .flip-card-front {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem;
        }

        .flip-card-back {
            background: #f9fafb;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem;
            transform: rotateY(180deg);
        }

        .flip-card-container:hover .flip-card {
            transform: rotateY(180deg);
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">
    <!--  include_once(includes/bpamis_nav.php);-->
    <!-- Add flex container for centering   register-bg flex flex-col min-h-screen -->
    <div class="register-bg flex flex-col min-h-screen flex-1 flex items-center justify-center p-4">

        <div class="login-container premium-glass premium-stats-container">
            <!-- Animated gradient background -->
            <div class="premium-gradient-bg"></div>

            <!-- Glass morphism overlay -->
            <div class="absolute inset-0 bg-white/70 backdrop-blur-lg"></div>

            <!-- Floating particles effect -->
            <div class="premium-particles">
                <div class="particle particle-1"></div>
                <div class="particle particle-2"></div>
                <div class="particle particle-3"></div>
                <div class="particle particle-4"></div>
            </div>

            <div class="form-container relative z-10" style="height: 100%">
                <div class="login-left premium-left-pattern hide-mobile">
                    <div class="flex justify-center w-full">
                        <div class="relative mb-8">
                            <a href="bpamis.php"><img src="assets/images/logo.png" alt="BPAMIS Logo" class="w-20 h-20 object-contain"></a>
                           
                        </div>
                    </div>
                    <h2 class="text-4xl font-bold text-gray-800 mb-4 leading-tight">
                        Join our digital community — BPAMIS <br>brings adjudication to <br>your fingertips.
                    </h2>
                    <p class="text-gray-600 md:block hidden">Experience a streamlined barangay management system</p>
                    
                    <!-- Mobile Login Form -->
                    <div class="mobile-login-form md:hidden w-full max-w-sm mt-8 bg-white rounded-lg p-4 shadow-lg border border-gray-200 ">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6 text-center mobile-fade-in-delay-1">Get Started</h3>
                        <form id="loginFormMobile" method="POST" action = "../controllers/logindb.php" class="space-y-6">
                            <div class="input-group form-field mobile-fade-in-delay-1">
                                <i class="fas fa-envelope"></i>
                                <input type="input" name="login_user" id="login_user" class="input-field"
                                    placeholder="Enter your username" required>
                            </div>

                            <div class="input-group form-field mobile-fade-in-delay-2">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="login_pass" id="login_pass" class="input-field"
                                    placeholder="••••••••" required>
                                <button type="button" class="password-toggle hover:text-gray-600 focus:outline-none"
                                    onclick="togglePassword('login_pass', this)" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye text-gray-400"></i>
                                </button>
                            </div>

                            <div class="flex items-center justify-between checkbox-group mobile-fade-in-delay-3">
                                <label class="flex items-center">
                                    <input type="checkbox" class="w-4 h-4 mr-2">
                                    <span class="text-sm text-gray-600">Remember me</span>
                                </label>
                                <a href="#" class="text-sm text-blue-600 hover:text-blue-700 forgot-password">Forgot
                                    Password?</a>
                            </div>

                            <button type="submit"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-full hover:bg-blue-700 transition-all duration-300 login-button mobile-fade-in-delay-4">
                                Log in
                            </button>

                            <p class="text-center text-gray-600 mt-4 register-link mobile-fade-in-delay-5">
                                Don't have an account?
                                <a href="register.php" class="text-blue-600 hover:text-blue-700">Register here</a>
                            </p>
                        </form>

                        <div id="loginMessageMobile" class="hidden mt-4 text-center"></div>
                    </div>
                </div>

                <div class="login-right premium-staggered premium-pattern">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 ml-2">Get Started</h3>
                    <!-- <p class="mb-6 text-gray-600">Don't have an account? <a href="register.php"
                            class="text-blue-600 font-medium toggle-form">Sign Up.</a></p> -->

                    <form id="loginForm" method="POST" action = "../controllers/logindb.php" class="space-y-6">
                        <div class="input-group form-field">
                            <i class="fas fa-envelope"></i>
                            <input type="text" name="login_user" id="login_user" class="input-field"
                                placeholder="Enter your username" required>
                        </div>

                        <div class="input-group form-field">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="login_pass" id="login_pass" class="input-field"
                                placeholder="••••••••" required>
                            <button type="button" class="password-toggle hover:text-gray-600 focus:outline-none"
                                onclick="togglePassword('login_pass', this)" aria-label="Toggle password visibility">
                                <i class="fas fa-eye text-gray-400"></i>
                            </button>
                        </div>

                        <div class="flex items-center justify-between checkbox-group">
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 mr-2">
                                <span class="text-sm text-gray-600">Remember me</span>
                            </label>
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-700 forgot-password">Forgot
                                Password?</a>
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-full hover:bg-blue-700 transition-all duration-300 login-button">
                            Log in
                        </button>

                        <p class="text-center text-gray-600 mt-4 register-link">
                            Don't have an account?
                            <a href="register.php" class="text-blue-600 hover:text-blue-700">Register here</a>
                        </p>
                    </form>

                    <br>
                    <div id="loginMessage" class="hidden mt-4 text-center"></div>
                </div>
            </div>
        </div>

    </div>



    <script>
     function showLoginError() {
    const messageDiv = document.getElementById('loginMessage');
    const form = document.getElementById('loginForm');

    messageDiv.className = 'block text-center p-4 mb-4 text-red-600 bg-red-50 border border-red-200 rounded-lg';
    messageDiv.textContent = 'Invalid username or password.';
    form.classList.add('animate-shake');

    setTimeout(() => {
        form.classList.remove('animate-shake');
    }, 500);

    document.getElementById('login_pass').value = '';
    setTimeout(() => {
    messageDiv.classList.add('hidden');
    }, 5000);
    }

    // Password toggle function
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        if (params.get('login_error') === 'true') {
            showLoginError();
        }
    });

       
    </script>
</body>

</html>