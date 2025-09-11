<?php
// --- CAPTCHA VERIFICATION FUNCTION ---
function verify_captcha($captcha_response)
{
    $secret = "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe"; // Google's test secret key
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $data = [
        'secret' => $secret,
        'response' => $captcha_response
    ];
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        return false;
    }
    $resultData = json_decode($result, true);
    return $resultData["success"] ?? false;
}

// --- REGISTER HANDLER (demo, does not save) ---
$errors = [];
$old = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reg_email'])) {
    $response = ['success' => false, 'message' => ''];

    $fname = trim($_POST['reg_fname']);
    $mname = trim($_POST['reg_mname']);
    $lname = trim($_POST['reg_lname']);
    $address = trim($_POST['reg_address']);
    $email = trim($_POST['reg_email']);
    $type = $_POST['reg_type'];
    $pass = $_POST['reg_pass'];
    $pass2 = $_POST['reg_pass_confirm'];
    $terms = isset($_POST['reg_terms']);
    $privacy = isset($_POST['reg_privacy']);
    $captcha = $_POST['g-recaptcha-response'];

    // Save old values except passwords
    $old = [
        'reg_fname' => $fname,
        'reg_mname' => $mname,
        'reg_lname' => $lname,
        'reg_address' => $address,
        'reg_email' => $email,
        'reg_type' => $type,
        'reg_terms' => $terms,
        'reg_privacy' => $privacy
    ];

    // No validation or qualification checks
    // Remove captcha validation
    // if (empty($captcha) || !verify_captcha($captcha)) {
    //     $errors['captcha'] = "Please complete the captcha correctly.";
    // }

    if (empty($errors)) {
        $response['success'] = true;
        $response['message'] = 'Registration successful!';

        // Send JSON response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        $response['message'] = implode('<br>', $errors);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add External User</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
        body {
            background: #e8f0fe;
            min-height: 100vh;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            width: 100%;
            max-width: 1200px;
            height: 600px;
            /* Fixed height to match design */
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            animation: none !important;
            opacity: 1 !important;
            transform: none !important;
        }
        
        @media (max-width: 768px) {
            .register-container {
                height: auto;
                min-height: 100vh;
                max-height: none;
                overflow: visible;
            }
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
                    rgba(139, 92, 246, 0.3)
                );
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
        
        /* Modal animation styles */
        #legalModalContent {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        #legalModalContent.scale-95 {
            transform: scale(0.95);
        }
        
        #legalModalContent.scale-100 {
            transform: scale(1);
        }
        
        #legalModalContent.opacity-0 {
            opacity: 0;
        }
        
        #legalModalContent.opacity-100 {
            opacity: 1;
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
            background-color: rgba(59, 130, 246, 0.5); /* blue */
            animation: float-particle 6s infinite 0.5s;
        }
        
        .particle-2 {
            width: 8px;
            height: 8px;
            top: 30%;
            left: 60%;
            background-color: rgba(16, 185, 129, 0.5); /* green */
            animation: float-particle 8s infinite 2s;
        }
        
        .particle-3 {
            width: 4px;
            height: 4px;
            top: 70%;
            left: 25%;
            background-color: rgba(236, 72, 153, 0.5); /* pink */
            animation: float-particle 5s infinite 1.5s;
        }
        
        .particle-4 {
            width: 7px;
            height: 7px;
            top: 10%;
            left: 85%;
            background-color: rgba(139, 92, 246, 0.5); /* purple */
            animation: float-particle 7s infinite 1s;
        }

        @keyframes float-particle {
            0%, 100% {
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

        .form-container {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            height: 100%;
            /* Changed from max-height */
        }
        
        @media (max-width: 768px) {
            .form-container {
                height: auto;
                min-height: 100vh;
            }
        }

        /* Legal Modal Styles */
        .legal-custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(59, 130, 246, 0.5) rgba(243, 244, 246, 1);
        }

        .legal-custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .legal-custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(243, 244, 246, 1);
            border-radius: 4px;
        }

        .legal-custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(59, 130, 246, 0.5);
            border-radius: 4px;
        }

        /* Animation for section transitions */
        .legal-section {
            scroll-margin-top: 140px;
            /* Ensures section headings aren't hidden under the fixed header */
        }

        /* Active tab indicator */
        .legal-tab.active {
            color: #1E40AF;
            border-bottom-color: #1E40AF;
        }

        /* Focus styles for accessibility */
        .focus-visible-ring:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }

        /* Ensure modal has proper height and scroll behavior */
        #legalModalContent {
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        #legalContentScroll {
            -webkit-overflow-scrolling: touch;
        }

        /* Mobile Responsive Styles for Legal Modal */
        @media (max-width: 768px) {
            /* Mobile Modal Container */
            #legalModalContent {
                width: 95vw !important;
                max-width: 95vw !important;
                margin: 1rem;
                max-height: 90vh;
            }

            /* Mobile Typography - Smaller font sizes */
            #legalModalContent .text-lg {
                font-size: 0.8rem !important;
                line-height: 1.4;
                font-weight: 600;
            }

            #legalModalContent h2 {
                font-size: 1rem !important;
                line-height: 1.3;
            }

            #legalModalContent h3 {
                font-size: 0.9rem !important;
                line-height: 1.4;
                margin-bottom: 0.75rem !important;
            }

            #legalModalContent h4 {
                font-size: 0.85rem !important;
                line-height: 1.4;
                font-weight: 500;
            }

            #legalModalContent p {
                font-size: 0.8rem !important;
                line-height: 1.6;
            }

            #legalModalContent .text-sm {
                font-size: 0.75rem !important;
                line-height: 1.5;
            }

            /* Mobile Header */
            #legalModalContent .bg-gradient-to-r {
                padding: 1rem !important;
            }

            #legalModalContent .bg-gradient-to-r h2 {
                font-size: 0.9rem !important;
            }

            #legalModalContent .bg-gradient-to-r .text-xl {
                font-size: 1rem !important;
            }

            #legalModalContent .bg-gradient-to-r .text-lg {
                font-size: 0.9rem !important;
            }

            /* Mobile Tab Navigation */
            #legalModalContent .border-b {
                padding: 0 1rem !important;
            }

            #legalModalContent .legal-tab {
                padding: 0.75rem 0.5rem !important;
                font-size: 0.75rem !important;
                white-space: nowrap;
            }

            #legalModalContent .legal-tab i {
                font-size: 0.7rem !important;
                margin-right: 0.25rem !important;
            }

            /* Mobile Content Area */
            #legalModalContent .p-6 {
                padding: 1rem !important;
            }

            #legalModalContent .legal-section {
                margin-bottom: 2rem !important;
            }

            #legalModalContent .legal-section h3 {
                font-size: 0.9rem !important;
                margin-bottom: 0.75rem !important;
                padding-bottom: 0.5rem !important;
            }

            #legalModalContent .legal-section h4 {
                font-size: 0.85rem !important;
                margin-top: 1rem !important;
                margin-bottom: 0.5rem !important;
            }

            #legalModalContent .legal-section p {
                margin-bottom: 0.75rem !important;
            }

            /* Mobile Lists */
            #legalModalContent .list-disc,
            #legalModalContent .list-decimal {
                padding-left: 1.25rem !important;
            }

            #legalModalContent .list-disc li,
            #legalModalContent .list-decimal li {
                margin-bottom: 0.5rem !important;
                font-size: 0.75rem !important;
                line-height: 1.5;
            }

            /* Mobile Links */
            #legalModalContent a {
                font-size: 0.75rem !important;
            }

            /* Mobile Footer */
            #legalModalContent .p-4 {
                padding: 1rem !important;
            }

            #legalModalContent .text-sm {
                font-size: 0.7rem !important;
            }

            #legalModalContent button {
                padding: 0.5rem 1rem !important;
                font-size: 0.8rem !important;
            }

            /* Mobile Scrollbar */
            #legalModalContent::-webkit-scrollbar {
                width: 6px;
            }

            #legalModalContent::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 3px;
            }

            #legalModalContent::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 3px;
            }

            #legalModalContent::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }

            /* Mobile Touch Targets */
            #legalModalContent .legal-tab,
            #legalModalContent button {
                min-height: 44px;
            }
        }

        /* Extra Small Mobile Devices */
        @media (max-width: 480px) {
            #legalModalContent {
                width: 98vw !important;
                max-width: 98vw !important;
                margin: 0.5rem;
            }

            #legalModalContent .text-lg {
                font-size: 0.75rem !important;
            }

            #legalModalContent h2 {
                font-size: 0.9rem !important;
            }

            #legalModalContent h3 {
                font-size: 0.85rem !important;
            }

            #legalModalContent h4 {
                font-size: 0.8rem !important;
            }

            #legalModalContent p {
                font-size: 0.75rem !important;
            }

            #legalModalContent .text-sm {
                font-size: 0.7rem !important;
            }

            #legalModalContent .list-disc li,
            #legalModalContent .list-decimal li {
                font-size: 0.7rem !important;
            }

            #legalModalContent .bg-gradient-to-r {
                padding: 0.75rem !important;
            }

            #legalModalContent .p-6 {
                padding: 0.75rem !important;
            }

            #legalModalContent .legal-tab {
                padding: 0.625rem 0.375rem !important;
                font-size: 0.7rem !important;
            }

            #legalModalContent .legal-tab i {
                font-size: 0.65rem !important;
            }

            #legalModalContent .p-4 {
                padding: 0.75rem !important;
            }

            #legalModalContent button {
                font-size: 0.75rem !important;
                padding: 0.5rem 0.875rem !important;
            }
        }

        /* Landscape Mobile Orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            #legalModalContent {
                max-height: 85vh;
            }

            #legalModalContent .bg-gradient-to-r {
                padding: 0.75rem !important;
            }

            #legalModalContent .p-6 {
                padding: 0.75rem !important;
            }

            #legalModalContent h3 {
                font-size: 0.85rem !important;
                margin-bottom: 0.5rem !important;
            }

            #legalModalContent .legal-section {
                margin-bottom: 1.5rem !important;
            }

            #legalModalContent .legal-section h4 {
                margin-top: 0.75rem !important;
            }
        }
        
        .register-left {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(37, 99, 235, 0.25) 100%);
            padding-top: 0rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            position: center;
            z-index: 1;
        }

        .register-left::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.05);
            z-index: -1;
        }

        .register-right {
            padding: 2rem;
            background: white;
            overflow-y: auto;
            height: 100%;
            /* Changed from max-height */
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }

        .register-content {
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: left;
            justify-content: center;
            text-align: left;
            margin-left:4rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 0.75rem;
            /* Reduced spacing */
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
            padding-right: 2.5rem;
            /* Make room for the eye icon */
        }

        .input-field:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .register-btn {
            background: #2563eb;
            color: #ffffff;
            padding: 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .register-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .input-group button:hover {
            color: #4B5563;
        }

        .input-group button:focus {
            outline: none;
        }

        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
            }

            .register-left {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                padding: 2rem 1rem;
                background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(37, 99, 235, 0.25) 100%);
                min-height: 100vh;
                overflow-y: auto;
                padding-bottom: 4rem;
            }

            .register-right {
                display: none;
            }

            .register-container {
                margin: 1rem;
            }
            
            .text-4xl {
                font-size: 0.9rem;
                text-align: center;
            }
            
            .mobile-register-form {
                width: 100%;
                max-width: 100%;
                margin-top: 2rem;
                padding-bottom: 4rem;
                height: auto;
                overflow-y: visible;
                padding-right: 0.5rem;
            }
            
            .mobile-register-form h3 {
                font-size: 1rem;
            }
            
            .mobile-register-form .input-field {
                font-size: 0.7rem;
                padding: 0.6rem 0.8rem 0.6rem 2.2rem;
            }
            
            .mobile-register-form .text-sm {
                font-size: 0.7rem;
            }
            
            .mobile-register-form .register-btn {
                font-size: 0.8rem;
                font-weight: 600;
            }
            
            /* Mobile scrollbar styling */
            .register-left::-webkit-scrollbar {
                width: 6px;
            }
            
            .register-left::-webkit-scrollbar-track {
                background: rgba(235, 245, 255, 0.3);
                border-radius: 3px;
            }
            
            .register-left::-webkit-scrollbar-thumb {
                background: rgba(37, 99, 235, 0.4);
                border-radius: 3px;
            }
            
            .register-left::-webkit-scrollbar-thumb:hover {
                background: rgba(37, 99, 235, 0.6);
            }
            
            /* Firefox scrollbar */
            .register-left {
                scrollbar-width: thin;
                scrollbar-color: rgba(37, 99, 235, 0.4) rgba(235, 245, 255, 0.3);
            }

            .text-gray-600 {
                font-size: 0.7rem;
            }

            .bg-blue-600 {
                font-size: 0.7rem;
            }

            .register-content {
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: left;
            justify-content: center;
            text-align: left;
            margin-top:1 rem;
            margin-left:1rem;
        }
        }

        /* Add this new style for form layout */
        #registerForm {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .flex.flex-col.gap-2 {
            gap: 0.5rem;
        }

        /* Update the scrollbar styles */
        .register-right::-webkit-scrollbar {
            width: 8px;
        }

        .register-right::-webkit-scrollbar-track {
            background: rgba(235, 245, 255, 0.5);
            /* Lowered track opacity */
            border-radius: 4px;
        }

        .register-right::-webkit-scrollbar-thumb {
            background: rgba(37, 99, 235, 0.6);
            /* Lowered thumb opacity */
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .register-right::-webkit-scrollbar-thumb:hover {
            background: rgba(29, 78, 216, 0.8);
            /* Slightly more opaque on hover */
        }

        /* For Firefox */
        .register-right {
            scrollbar-width: thin;
            scrollbar-color: rgba(37, 99, 235, 0.6) rgba(235, 245, 255, 0.5);
        }
        
         /* Password field and toggle styling */
         input[type="password"].input-field {
            padding-right: 3rem;
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
        
        /* Password toggle icon styling */
        .password-toggle {
            cursor: pointer;
            transition: color 0.2s ease;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: #4B5563;
        }
        
        @media (max-width: 768px) {
            .input-group button.password-toggle {
                right: 1.2rem; /* Slightly closer to the edge for mobile */
                top: 50%;
                transform: translateY(-50%);
                width: 1.3rem;
                height: 1.3rem;
            }
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

        /* Add transition classes */
        .fade-enter {
            opacity: 0;
            transform: translateY(20px);
        }

        .fade-enter-active {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        /* Smooth transitions for form elements */
        .input-field,
        .register-btn {
            transition: all 0.3s ease;
        }

        /* Button hover effect */
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        /* Form appear animation wrapper */
        .form-appear {
            opacity: 0;
            transform: translateY(20px);
            animation: formAppear 0.6s ease forwards 0.3s;
        }

        @keyframes formAppear {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-checkbox {
            appearance: none;
            -webkit-appearance: none;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            padding: 8px;
            display: inline-block;
            position: relative;
            vertical-align: middle;
            cursor: pointer;
            margin-right: 8px;
        }

        .form-checkbox:checked {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .form-checkbox:checked:after {
            content: '';
            display: block;
            position: absolute;
            left: 50%;
            top: 50%;
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: translate(-50%, -60%) rotate(45deg);
        }

        /* Update the form animation wrapper to include checkbox and button animations */
        .flex.flex-col.gap-3.mb-4,
        .w-full.bg-blue-600.text-white {
            opacity: 0;
            transform: translateY(20px);
            animation: formElementAppear 0.6s ease forwards;
        }

        /* Add different delays for checkboxes and button */
        .flex.flex-col.gap-3.mb-4 {
            animation-delay: 0.4s;
        }

        .w-full.bg-blue-600.text-white {
            animation-delay: 0.5s;
        }

        @keyframes formElementAppear {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Add smooth transitions */
        .form-checkbox,
        button[type="submit"] {
            transition: all 0.3s ease;
        }

        /* Slide-in animation for new elements */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Apply the slide-in animation to specific elements */
        .animate-slide-in {
            animation: slideIn 0.6s ease-out forwards;
        }

        /* Fade-in animation for mobile register */
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

        .mobile-fade-in-delay-6 {
            animation: fadeInUp 0.8s ease-out 0.6s forwards;
            opacity: 0;
        }

        .mobile-fade-in-delay-7 {
            animation: fadeInUp 0.8s ease-out 0.7s forwards;
            opacity: 0;
        }

        .mobile-fade-in-delay-8 {
            animation: fadeInUp 0.8s ease-out 0.8s forwards;
            opacity: 0;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">
    <?php include_once('../includes/barangay_official_sec_nav.php'); ?>
    <div class="register-bg flex flex-col min-h-screen flex-1 flex items-center justify-center py-8 px-4">
        <div class="register-container  premium-glass premium-stats-container">
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
                <div class="register-left relative premium-left-pattern">
                    <!-- Premium Animation Effects -->
                    <div class="premium-particles absolute inset-0 overflow-hidden pointer-events-none">
                        <div class="particle particle-1"></div>
                        <div class="particle particle-2"></div>
                        <div class="particle particle-3"></div>
                        <div class="particle particle-4"></div>
                    </div>
                    
                    <div class="register-content">
                        <div class="flex justify-center w-full mb-8">
                            <a href="../SecMenu/home-secretary.php"><img src="logo.png" alt="BPAMIS Logo" class="w-20 h-20 object-contain"></a>
                        </div>
                        <h2 class="text-4xl font-bold text-gray-800 mb-4 leading-tight">
                            Join our digital community — BPAMIS <br>brings adjudication to <br>your fingertips.
                        </h2>
                        <p class="text-gray-600 md:block hidden">Experience a streamlined barangay management system</p>
                    </div>
                </div>

                <div class="register-right form-appear">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Get Started</h3>
                    <p class="mb-6 text-gray-600">Creating an External User Account</p>

                    <form id="registerForm" method="POST" action="../controllers/create_externaldb.php" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" name="reg_fname" id="reg_fname" class="input-field"
                                    placeholder="First Name" required>
                            </div>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" name="reg_lname" id="reg_lname" class="input-field"
                                    placeholder="Last Name" required>
                            </div>
                        </div>

                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="reg_mname" id="reg_mname" class="input-field"
                                placeholder="Middle Name">
                        </div>

                        <div class="input-group">
                            <i class="fas fa-home"></i>
                            <input type="text" name="reg_address" id="reg_address" class="input-field"
                                placeholder="Address" required>
                        </div>

                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="reg_email" id="reg_email" class="input-field"
                                placeholder="Email Address" required>
                        </div>

                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="text" name="reg_contact" id="reg_email" class="input-field"
                                placeholder="Contact Number" required>
                        </div>

                        <div class="input-group relative">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="reg_pass" id="reg_pass" class="input-field pr-10"
                                placeholder="Password" required>
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                                onclick="togglePassword('reg_pass', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        <div class="input-group relative" style="animation-delay: 0.9s;">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="reg_pass_confirm" id="reg_pass_confirm"
                                class="input-field pr-10" placeholder="Confirm Password" required>
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                                onclick="togglePassword('reg_pass_confirm', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        <p id="passwordMismatchMsg" class="text-sm text-red-600 hidden mt-1">Passwords do not match.</p>

                        <div class="flex flex-col gap-3 mb-4 animate-slide-in">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="reg_terms" class="mt-1 form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" required>
                                <span class="text-sm text-gray-600">
                                    I agree to the <a href="#" class="text-blue-600 hover:text-blue-700 underline">Terms and Conditions</a>
                                </span>
                            </label>

                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="reg_privacy" class="mt-1 form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" required>
                                <span class="text-sm text-gray-600">
                                    I accept the <a href="#" class="text-blue-600 hover:text-blue-700 underline">Privacy Policy</a>
                                </span>
                            </label>
                        </div>

                        <button id="submitBtn" type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-all duration-300 font-medium animate-slide-in" name="Signup" disabled>
                            Create Account
                        </button>
                    </form>

                    <br>
                    <div id="registerMessage" class="hidden mt-4 text-center"></div>
                </div>
            </div>
        </div>
    </div>

    </div>
    <?php include 'sidebar_.php';?>
    <script>
        // Add this function before the closing 
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

        //para sa confirm password
         const password = document.getElementById('reg_pass');
         const confirmPassword = document.getElementById('reg_pass_confirm');
         const mismatchMsg = document.getElementById('passwordMismatchMsg');
         const submitBtn = document.getElementById('submitBtn');

            function validatePasswords() {
                if (password.value && confirmPassword.value && password.value !== confirmPassword.value) {
                    mismatchMsg.classList.remove('hidden');
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    mismatchMsg.classList.add('hidden');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

           password.addEventListener('input', validatePasswords);
           confirmPassword.addEventListener('input', validatePasswords);

    </script>
</body>

</html>