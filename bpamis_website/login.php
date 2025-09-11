<?php
// --- TEMPORARY ACCOUNT FOR SECRETARY LOGIN ---
$secretary_email = "secretary@gmail.com";
$secretary_pass = "1234";

$resident_email = "resident@gmail.com";
$resident_pass = "1234";

$external_email = "external@gmail.com";
$external_pass = "1234";

$resident_email = "residen@gmail.com";
$resident_pass = "1234";

$captain_email = "captain@gmail.com";
$captain_pass = "1234";

$lupon_email = "lupon@gmail.com";
$lupon_pass = "1234";

// --- LOGIN HANDLER ---
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_user'])) {
    $user = $_POST['login_user'];
    $pass = $_POST['login_pass'];

    // Check credentials and redirect
    if ($user === $secretary_email && $pass === $secretary_pass) {
        header("Location: ../SecMenu/home-secretary.php");
        exit;
    }
    else if ($user === $resident_email && $pass === $resident_pass) {
        header("Location: ../ResidentMenu/home-resident.php?role=resident");
        exit;
    }
    else if ($user === $external_email && $pass === $external_pass) {
        header("Location: ../ExternalMenu/home-external.php?role=external");
        exit;
    }
    else if ($user === $captain_email && $pass === $captain_pass) {
        header("Location: ../OfficialMenu/home-captain.php?role=captain");
        exit;
    }
    else if ($user === $lupon_email && $pass === $lupon_pass) {
        header("Location: ../OfficialMenu/home-lupon.php?role=lupon");
        exit;
    }
    else {
        $login_error = "Invalid email or password.";
    }
}

// At the top of the file, add a check for AJAX requests
if (isset($_SERVER['HTTP_X-Requested-With']) && $_SERVER['HTTP_X-Requested-With'] === 'XMLHttpRequest') {
    // Handle AJAX login
    $response = ['success' => false, 'message' => '', 'redirect' => ''];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = $_POST['login_user'] ?? '';
        $pass = $_POST['login_pass'] ?? '';
        
        // Check credentials and set appropriate redirects
        if ($user === $secretary_email && $pass === $secretary_pass) {
            $response['success'] = true;
            $response['redirect'] = '../SecMenu/home-secretary.php';
        }
        else if ($user === $resident_email && $pass === $resident_pass) {
            $response['success'] = true;
            $response['redirect'] = '../ResidentMenu/home-resident.php';
        }
        else if ($user === $external_email && $pass === $external_pass) {
            $response['success'] = true;
            $response['redirect'] = '../ExternalMenu/home-external.php';
        }
        else if ($user === $captain_email && $pass === $captain_pass) {
            $response['success'] = true;
            $response['redirect'] = '../OfficialMenu/home-captain.php';
        }
        else if ($user === $lupon_email && $pass === $lupon_pass) {
            $response['success'] = true;
            $response['redirect'] = '../OfficialMenu/home-lupon.php';
        }
        else {
            $response['message'] = "Invalid email or password.";
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
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
    <link rel="stylesheet" href="styles/flip-animations.css">
    
    <style>
        body { 
            background: #e8f0fe;
            min-height: 100vh;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            width: 100%;
            max-width: 1200px;
            overflow: hidden;
        }
        .form-container {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            min-height: 600px;
        }
        .login-left {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(37, 99, 235, 0.2) 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-right {
            padding: 3rem;
            background: white;
        }
        .input-group {
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
        .input-field {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            background: #F9FAFB;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        .login-btn {
            background: #2563eb; /* BPAMIS blue color */
            color: #ffffff; /* White text */
            padding: 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background: #1d4ed8; /* Darker blue on hover */
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.1);
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
                display: none;
            }
            .login-container {
                margin: 1rem;
            }
        }
        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
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
        .flip-card-front, .flip-card-back {
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
    <?php include_once('includes/bpamis_nav.php'); ?>
    <!-- Add flex container for centering   register-bg flex flex-col min-h-screen -->
    <div class="register-bg flex flex-col min-h-screen flex-1 flex items-center justify-center">
      
                    <div class="login-container">
                        <div class="form-container">
                            <div class="login-left">
                                <img src="logo.png" alt="BPAMIS Logo" class="w-20 h-20 mb-8">
                                <h2 class="text-4xl font-bold text-gray-800 mb-4 leading-tight">
                                    Join our digital community — BPAMIS <br>brings adjudication to <br>your fingertips.
                                </h2>
                                <p class="text-gray-600">Experience a streamlined barangay management system</p>
                            </div>
                            
                            <div class="login-right">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Get Started</h3>
                                <p class="mb-6 text-gray-600">Don't have an account?  <a href="register.php" class="text-blue-600 font-medium toggle-form">Sign Up.</a></p>
                                
                                <form id="loginForm" onsubmit="handleLogin(event)" class="space-y-4">
                                    <div class="input-group form-field">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" name="login_user" id="login_user" 
                                               class="input-field" placeholder="Enter your email" required>
                                    </div>
                                    
                                    <div class="input-group form-field">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" name="login_pass" id="login_pass" 
                                               class="input-field" placeholder="••••••••" required>
                                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"
                                                onclick="togglePassword('login_pass', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="flex items-center justify-between checkbox-group">
                                        <label class="flex items-center">
                                            <input type="checkbox" class="w-4 h-4 mr-2">
                                            <span class="text-sm text-gray-600">Remember me</span>
                                        </label>
                                        <a href="#" class="text-sm text-blue-600 hover:text-blue-700 forgot-password">Forgot Password?</a>
                                    </div>
                                    
                                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-full hover:bg-blue-700 transition-all duration-300 login-button">
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
        </div>
   

    <script>
function handleLogin(event) {
    event.preventDefault();
    
    const form = document.getElementById('loginForm');
    const messageDiv = document.getElementById('loginMessage');
    const email = document.getElementById('login_user').value;
    const password = document.getElementById('login_pass').value;
    
    // Simple credential check
    let redirect = '';
    let isValid = false;
    
    switch(email) {
        case 'secretary@gmail.com':
            if(password === '1234') {
                isValid = true;
                redirect = '../SecMenu/home-secretary.php';
            }
            break;
        case 'resident@gmail.com':
            if(password === '1234') {
                isValid = true;
                redirect = '../ResidentMenu/home-resident.php';
            }
            break;
        case 'external@gmail.com':
            if(password === '1234') {
                isValid = true;
                redirect = '../ExternalMenu/home-external.php';
            }
            break;
        case 'captain@gmail.com':
            if(password === '1234') {
                isValid = true;
                redirect = '../OfficialMenu/home-captain.php';
            }
            break;
        case 'lupon@gmail.com':
            if(password === '1234') {
                isValid = true;
                redirect = '../OfficialMenu/home-lupon.php';
            }
            break;
    }
    
    messageDiv.classList.remove('hidden');
    
    if (isValid) {
        // Success case
        messageDiv.className = 'text-center p-4 mb-4 text-green-600 bg-green-50 border border-green-200 rounded-lg';
        messageDiv.textContent = 'Login successful! Redirecting...';
        
        // Redirect after delay
        setTimeout(() => {
            window.location.href = redirect;
        }, 1000);
    } else {
        // Error case
        messageDiv.className = 'text-center p-4 mb-4 text-red-600 bg-red-50 border border-red-200 rounded-lg';
        messageDiv.textContent = 'Invalid email or password.';
        
        // Add shake animation
        form.classList.add('animate-shake');
        setTimeout(() => {
            form.classList.remove('animate-shake');
        }, 500);

        // Clear password field
        document.getElementById('login_pass').value = '';
    }
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

function handleRegister(event) {
    event.preventDefault();
    
    const form = document.getElementById('registerForm');
    const messageDiv = document.getElementById('registerMessage');
    const fullName = document.getElementById('full_name').value;
    const email = document.getElementById('reg_email').value;
    const password = document.getElementById('reg_pass').value;
    const confirmPassword = document.getElementById('confirm_pass').value;
    
    // Simple validation checks
    let isValid = true;
    messageDiv.classList.remove('hidden');
    
    if (!fullName || !email || !password || !confirmPassword) {
        isValid = false;
        messageDiv.className = 'text-center p-4 mb-4 text-red-600 bg-red-50 border border-red-200 rounded-lg';
        messageDiv.textContent = 'Please fill in all fields.';
    } else if (password !== confirmPassword) {
        isValid = false;
        messageDiv.className = 'text-center p-4 mb-4 text-red-600 bg-red-50 border border-red-200 rounded-lg';
        messageDiv.textContent = 'Passwords do not match.';
    }
    
    if (isValid) {
        // Simulate successful registration (replace with actual registration logic)
        setTimeout(() => {
            messageDiv.className = 'text-center p-4 mb-4 text-green-600 bg-green-50 border border-green-200 rounded-lg';
            messageDiv.textContent = 'Registration successful! Redirecting to login...';
            
            setTimeout(() => {
                // Redirect to login page (replace with actual login page URL)
                window.location.href = 'login.php';
            }, 1000);
        }, 1000);
    }
}
</script>
</body>
</html>


