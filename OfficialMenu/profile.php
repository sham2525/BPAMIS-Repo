<?php
session_start();
include '../server/server.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// AJAX handler for password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_change_password'])) {
    header('Content-Type: application/json');
    $current_pw = $_POST['current_password'] ?? '';
    $new_pw = $_POST['new_password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $response = ['success' => false, 'message' => ''];

    $pw_query = "SELECT password FROM barangay_officials WHERE Official_ID = ?";
    $stmt = $conn->prepare($pw_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $hashed_pw = $row['password'] ?? '';

    if (!password_verify($current_pw, $hashed_pw)) {
        $response['message'] = 'Current password is incorrect.';
    } elseif (strlen($new_pw) < 6) {
        $response['message'] = 'New password must be at least 6 characters.';
    } elseif ($new_pw !== $confirm_pw) {
        $response['message'] = 'New password and confirm password do not match.';
    } else {
        $new_hashed = password_hash($new_pw, PASSWORD_DEFAULT);
        $update_pw_query = "UPDATE barangay_officials SET password=? WHERE Official_ID=?";
        $stmt = $conn->prepare($update_pw_query);
        $stmt->bind_param("si", $new_hashed, $user_id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Password updated successfully!';
        } else {
            $response['message'] = 'Failed to update password. Please try again.';
        }
    }
    echo json_encode($response);
    exit;
}

// Get user data from session
$user_name = $_SESSION['official_name'] ?? 'User Name';
$user_email = $_SESSION['user'] ?? 'user@example.com';
$user_role = 'Barangay Secretary'; // Default role for secretary

// Handle Edit Profile form submission
$profile_success = false;
$profile_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $new_email = trim($_POST['new_email'] ?? '');
    $full_name = $first_name . ' ' . $last_name;

    // If new email is provided, append it to the existing email (comma-separated)
    if ($new_email) {
        if (strpos($email, $new_email) === false) {
            $email = $email . ',' . $new_email;
        }
    }

    // Basic validation
    if ($first_name && $last_name && $email) {
        $update_query = "UPDATE barangay_officials SET Name=?, email=?, Contact_Number=? WHERE Official_ID=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssi", $full_name, $email, $contact, $user_id);
        if ($stmt->execute()) {
            $profile_success = true;
            // Update session values
            $_SESSION['official_name'] = $full_name;
            $_SESSION['user'] = $email;
        } else {
            $profile_error = 'Failed to update profile. Please try again.';
        }
    } else {
        $profile_error = 'Please fill in all required fields.';
    }
}

// Fetch user data from database
$user_data = null;
$contact_number = '';
$address = '';
$member_since = '';

try {
    // Query to get user information from barangay_officials table
    $query = "SELECT * FROM barangay_officials WHERE Official_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $user_name = $user_data['Name'] ?? $user_name;
        $user_email = $user_data['email'] ?? $user_email;
        $user_role = $user_data['Position'] ?? $user_role;
        $contact_number = $user_data['Contact_Number'] ?? '+63 912 345 6789';
        
        // Set member since date (you can add a created_at field to the database if needed)
        $member_since = date('M Y');
        
        // For address, you might want to add an address field to the database
        $address = 'Barangay Hall, City';
    }
} catch (Exception $e) {
    // Handle database errors gracefully
    error_log("Database error: " . $e->getMessage());
}

// Get user statistics from database
$stats = [
    'cases_handled' => 0,
    'resolved' => 0,
    'pending' => 0 // This will now mean pending complaints
];

try {
    // Cases handled and resolved (same as before, but grouped)
    $caseQuery = "SELECT Case_Status as status, COUNT(*) as count FROM case_info GROUP BY Case_Status";
    $result = $conn->query($caseQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $status = strtolower(trim($row['status']));
            $count = (int)$row['count'];
            $stats['cases_handled'] += $count;
            if ($status === 'resolved' || $status === 'closed') {
                $stats['resolved'] += $count;
            }
        }
    }

    // Pending complaints
    $pendingComplaintsQuery = "SELECT COUNT(*) as count FROM complaint_info WHERE LOWER(status) = 'pending'";
    $pendingResult = $conn->query($pendingComplaintsQuery);
    if ($pendingResult) {
        $stats['pending'] = (int)$pendingResult->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    error_log("Statistics query error: " . $e->getMessage());
}

// Fetch recent activities for the current month (upcoming hearings and case status updates)
$currentMonth = date('m');
$currentYear = date('Y');

// Upcoming hearings (future dates, this month)
$hearings = [];
$hearing_query = "SELECT ml.*, ci.Case_ID 
                  FROM meeting_logs ml 
                  JOIN case_info ci ON ml.Case_ID = ci.Case_ID 
                  WHERE MONTH(ml.Hearing_Date) = ? AND YEAR(ml.Hearing_Date) = ? AND ml.Hearing_Date >= CURDATE()
                  ORDER BY ml.Hearing_Date ASC
                  LIMIT 5";
$stmt = $conn->prepare($hearing_query);
$stmt->bind_param("ss", $currentMonth, $currentYear);
$stmt->execute();
$hearing_result = $stmt->get_result();
while ($row = $hearing_result->fetch_assoc()) {
    $hearings[] = [
        'type' => 'Hearing',
        'description' => 'Upcoming hearing for case #' . $row['Case_ID'],
        'date' => $row['Hearing_Date'],
        'link' => 'home-secretary.php#calendar'
    ];
}

// Recent case status updates (this month)
$case_updates = [];
$case_query = "SELECT Case_ID, Case_Status, Date_Opened, Date_Closed 
               FROM case_info 
               WHERE (MONTH(Date_Opened) = ? AND YEAR(Date_Opened) = ?) 
                  OR (Date_Closed IS NOT NULL AND MONTH(Date_Closed) = ? AND YEAR(Date_Closed) = ?)
               ORDER BY GREATEST(IFNULL(Date_Closed, '0000-00-00'), Date_Opened) DESC
               LIMIT 5";
$stmt = $conn->prepare($case_query);
$stmt->bind_param("ssss", $currentMonth, $currentYear, $currentMonth, $currentYear);
$stmt->execute();
$case_result = $stmt->get_result();
while ($row = $case_result->fetch_assoc()) {
    $date = $row['Date_Closed'] ?: $row['Date_Opened'];
    $case_updates[] = [
        'type' => 'Status',
        'description' => 'Case #' . $row['Case_ID'] . ' status updated to ' . $row['Case_Status'],
        'date' => $date,
        'link' => 'home-secretary.php#calendar'
    ];
}

// Merge and sort by date (descending)
$all_activities = array_merge($hearings, $case_updates);
usort($all_activities, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
$recent_activities = array_slice($all_activities, 0, 5);

// Avatar choices (define at the top so it's always available)
$avatar_choices = [
    '../Assets/Img/avatar1.jpg',
    '..//Assets/Img/avatar2.jpg',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - BPAMIS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'gradient': 'gradient 15s ease infinite',
                        'shine': 'shine 3s infinite linear',
                        'particle': 'particle-float 3s ease-in-out infinite alternate',
                        'icon-light': 'icon-light 4s infinite',
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.8s ease-out',
                        'scale-in': 'scaleIn 0.5s ease-out'
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        gradient: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' }
                        },
                        shine: {
                            '0%': { backgroundPosition: '200% 0' },
                            '100%': { backgroundPosition: '-200% 0' }
                        },
                        'particle-float': {
                            '0%': { transform: 'translateY(0) translateX(0)' },
                            '100%': { transform: 'translateY(-10px) translateX(10px)' }
                        },
                        'icon-light': {
                            '0%': { left: '-150%' },
                            '100%': { left: '150%' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        scaleIn: {
                            '0%': { opacity: '0', transform: 'scale(0.9)' },
                            '100%': { opacity: '1', transform: 'scale(1)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .premium-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .premium-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .premium-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }
        
        .profile-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        
        .animated-border {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.2));
            background-size: 200% 100%;
            animation: shine 3s infinite linear;
        }
        
        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }
        
        .premium-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .premium-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        
        .premium-button:hover::before {
            left: 100%;
        }
        
        .premium-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .activity-item {
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            transform: translateX(5px);
            background: rgba(102, 126, 234, 0.05);
        }
        
        .premium-input {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(102, 126, 234, 0.2);
            transition: all 0.3s ease;
        }
        
        .premium-input:focus {
            background: rgba(255, 255, 255, 1);
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
            background: #e5e7eb;
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 26px;
            height: 26px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .toggle-switch.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .toggle-switch.active::after {
            transform: translateX(30px);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans min-h-screen">
    <!-- Header -->
    <?php include '../includes/barangay_official_cap_nav.php'; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-8">
        <!-- Profile Hero Section -->
        <div class="mb-8 animate-fade-in">
            <div class="premium-card rounded-3xl overflow-hidden">
                <div class="premium-gradient px-4 sm:px-8 py-8 sm:py-12 relative overflow-hidden">
                    <!-- Animated Background Elements -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-10 left-10 w-12 h-12 sm:w-20 sm:h-20 bg-white rounded-full floating-icon"></div>
                        <div class="absolute top-20 right-10 sm:right-20 w-10 h-10 sm:w-16 sm:h-16 bg-white rounded-full floating-icon" style="animation-delay: 1s;"></div>
                        <div class="absolute bottom-10 left-1/4 w-8 h-8 sm:w-12 sm:h-12 bg-white rounded-full floating-icon" style="animation-delay: 2s;"></div>
                    </div>
                    <div class="relative z-10 flex flex-col lg:flex-row items-center space-y-4 sm:space-y-6 lg:space-y-0 lg:space-x-8">
                        <!-- Profile Avatar -->
                        <div class="relative mb-4 sm:mb-0">
                            <div class="profile-avatar w-24 h-24 sm:w-32 sm:h-32 rounded-full flex items-center justify-center border-4 border-white shadow-2xl">
                                <img class="w-20 h-20 sm:w-28 sm:h-28 rounded-full object-cover" 
                                     src="https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=667eea&color=fff&size=112" 
                                     alt="Profile Image" id="profile-avatar-img">
                            </div>
                        </div>
                        <!-- Profile Info -->
                        <div class="text-center lg:text-left text-white w-full">
                            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-bold mb-1 sm:mb-2 animate-slide-up break-words"><?= htmlspecialchars($user_name) ?></h1>
                            <p class="text-lg sm:text-xl lg:text-2xl text-blue-100 mb-2 sm:mb-3 animate-slide-up" style="animation-delay: 0.2s;"><?= htmlspecialchars($user_role) ?></p>
                            <div class="space-y-1 animate-slide-up" style="animation-delay: 0.4s;">
                                <p class="text-blue-100 text-base sm:text-lg break-all"><?= htmlspecialchars($user_email) ?></p>
                                <p class="text-blue-200 text-xs sm:text-sm">
                                    <?php 
                                    $username = $_SESSION['username'] ?? '';
                                    if (strlen($username) > 1) {
                                        echo htmlspecialchars(substr($username, 0, 1)) . str_repeat('*', strlen($username) - 1);
                                    } else {
                                        echo htmlspecialchars($username);
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-8">
            <!-- Main Profile Content -->
            <div class="lg:col-span-2 space-y-6 sm:space-y-8">
                <!-- Personal Information Card -->
                <div class="premium-card rounded-2xl p-4 sm:p-8 animate-scale-in">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start mb-4 sm:mb-6">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-0 sm:mr-4 mb-2 sm:mb-0">
                            <i class="fas fa-user-circle text-white text-lg sm:text-xl"></i>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Personal Information</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div class="space-y-3 sm:space-y-4">
                            <div class="flex items-center justify-between py-2 sm:py-3 border-b border-gray-100">
                                <span class="text-xs sm:text-sm font-medium text-gray-600">Full Name</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-900 text-right max-w-[60%] break-words"><?= htmlspecialchars($user_name) ?></span>
                            </div>
                            <div class="flex items-center justify-between py-2 sm:py-3 border-b border-gray-100">
                                <span class="text-xs sm:text-sm font-medium text-gray-600">Email</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-900 text-right max-w-[60%] break-all"><?= htmlspecialchars($user_email) ?></span>
                            </div>
                            <div class="flex items-center justify-between py-2 sm:py-3 border-b border-gray-100">
                                <span class="text-xs sm:text-sm font-medium text-gray-600">Username</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-900 text-right max-w-[60%] break-words">
                                    <?php 
                                    $username = $_SESSION['username'] ?? '';
                                    if (strlen($username) > 1) {
                                        echo htmlspecialchars(substr($username, 0, 1)) . str_repeat('*', strlen($username) - 1);
                                    } else {
                                        echo htmlspecialchars($username);
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between py-2 sm:py-3 border-b border-gray-100">
                                <span class="text-xs sm:text-sm font-medium text-gray-600">Role</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-900 text-right max-w-[60%] break-words"><?= htmlspecialchars($user_role) ?></span>
                            </div>
                            <div class="flex items-center justify-between py-2 sm:py-3 border-b border-gray-100">
                                <span class="text-xs sm:text-sm font-medium text-gray-600">Member Since</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-900 text-right max-w-[60%] break-words"><?= $member_since ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 sm:space-y-4">
                            <div class="flex items-center py-2 sm:py-3 border-b border-gray-100">
                                <i class="fas fa-phone mr-2 sm:mr-4 text-primary-600 w-4"></i>
                                <span class="text-xs sm:text-sm font-semibold text-gray-900 break-all"><?= htmlspecialchars($contact_number) ?></span>
                            </div>
                            <div class="flex items-center py-2 sm:py-3 border-b border-gray-100">
                                <i class="fas fa-map-marker-alt mr-2 sm:mr-4 text-primary-600 w-4"></i>
                                <span class="text-xs sm:text-sm font-semibold text-gray-900 break-words"><?= htmlspecialchars($address) ?></span>
                            </div>
                            <div class="flex items-center py-2 sm:py-3 border-b border-gray-100">
                                <i class="fas fa-clock mr-2 sm:mr-4 text-primary-600 w-4"></i>
                                <span class="text-xs sm:text-sm font-semibold text-gray-900">Mon-Fri, 8:00 AM - 5:00 PM</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Dynamic Content Container -->
                <div id="dynamic-content" class="hidden"></div>
            </div>
            <!-- Sidebar Content -->
            <div class="space-y-6 sm:space-y-8 animate-scale-in">
                <!-- Quick Actions -->
                <div class="premium-card rounded-2xl p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start mb-4 sm:mb-6">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-green-500 to-blue-600 rounded-lg flex items-center justify-center mr-0 sm:mr-3 mb-2 sm:mb-0">
                            <i class="fas fa-cogs text-white text-base sm:text-lg"></i>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-800">Quick Actions</h3>
                    </div>
                    <div class="space-y-2 sm:space-y-3">
                        <button onclick="showContent('edit-profile')" class="w-full flex items-center p-3 sm:p-4 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-300 group">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 group-hover:bg-blue-200 transition-colors">
                                <i class="fas fa-edit text-blue-600"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">Edit Profile</span>
                        </button>
                        <button onclick="showContent('change-password')" class="w-full flex items-center p-3 sm:p-4 rounded-xl hover:bg-gradient-to-r hover:from-green-50 hover:to-blue-50 transition-all duration-300 group">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 group-hover:bg-green-200 transition-colors">
                                <i class="fas fa-lock text-green-600"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-medium text-gray-700 group-hover:text-green-700 transition-colors">Change Password</span>
                        </button>
                        <button onclick="showContent('notification-settings')" class="w-full flex items-center p-3 sm:p-4 rounded-xl hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 transition-all duration-300 group">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 group-hover:bg-purple-200 transition-colors">
                                <i class="fas fa-bell text-purple-600"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-medium text-gray-700 group-hover:text-purple-700 transition-colors">Notification Settings</span>
                        </button>
                        <button onclick="showContent('logout')" class="w-full flex items-center p-3 sm:p-4 rounded-xl hover:bg-gradient-to-r hover:from-red-50 hover:to-orange-50 transition-all duration-300 group">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 group-hover:bg-red-200 transition-colors">
                                <i class="fas fa-sign-out-alt text-red-600"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-medium text-gray-700 group-hover:text-red-700 transition-colors">Logout</span>
                        </button>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="premium-card rounded-2xl p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start mb-4 sm:mb-6">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center mr-0 sm:mr-3 mb-2 sm:mb-0">
                            <i class="fas fa-history text-white text-base sm:text-lg"></i>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-800">Recent Activity</h3>
                    </div>
                    <div class="space-y-3 sm:space-y-4">
                        <?php foreach (
                            isset($recent_activities) ? $recent_activities : [] as $activity): ?>
                            <div class="activity-item flex items-start space-x-2 sm:space-x-3 p-2 sm:p-3 rounded-lg">
                                <div class="w-2 h-2 <?= $activity['type'] === 'Hearing' ? 'bg-blue-600' : 'bg-green-600' ?> rounded-full mt-2 flex-shrink-0"></div>
                                <div class="flex-1">
                                    <p class="text-xs sm:text-sm font-medium text-gray-900"><?= htmlspecialchars($activity['description']) ?></p>
                                    <p class="text-[10px] sm:text-xs text-gray-500"><?= date('M d, Y', strtotime($activity['date'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-2 sm:mt-4">
                            <a href="home-secretary.php#calendar" class="text-blue-600 hover:underline font-semibold text-xs sm:text-base">View More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    </div>
      <?php include 'sidebar_.php';?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.getElementById('menu-btn');
            const sidebar = document.getElementById('sidebar');
            
            if (menuBtn && sidebar) {
                menuBtn.addEventListener('click', function() {
                    sidebar.classList.remove('-translate-x-full');
                });
            }
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (!sidebar.classList.contains('-translate-x-full')) {
                    if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                        sidebar.classList.add('-translate-x-full');
                    }
                }
            });
        });

        // Function to show content in the dynamic container
        function showContent(contentType) {
            const container = document.getElementById('dynamic-content');
            let content = '';

            switch(contentType) {
                case 'edit-profile':
                    content = `
                        <div class="premium-card rounded-2xl overflow-hidden animate-scale-in">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4">
                                <h3 class="text-xl font-bold text-white flex items-center">
                                    <i class="fas fa-edit mr-2"></i>
                                    Edit Profile
                                </h3>
                            </div>
                            <div class="p-6">
                                <form class="space-y-6" method="post" action="">
                                    <input type="hidden" name="edit_profile" value="1">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                            <input type="text" name="first_name" value="<?= explode(' ', $user_name)[0] ?? '' ?>" class="premium-input w-full px-4 py-3 rounded-lg focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                            <input type="text" name="last_name" value="<?= explode(' ', $user_name)[1] ?? '' ?>" class="premium-input w-full px-4 py-3 rounded-lg focus:outline-none">
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="email" name="email" value="<?= htmlspecialchars($user_email) ?>" class="premium-input w-full px-4 py-3 rounded-lg focus:outline-none pr-12" readonly>
                                        <button type="button" id="add-email-btn" class="absolute right-3 top-8 text-blue-600 hover:text-blue-800 focus:outline-none" onclick="toggleNewEmail()">
                                            <i class="fas fa-plus-circle text-xl"></i>
                                        </button>
                                    </div>
                                    <div id="new-email-container" class="hidden mt-2">
                                        <input type="email" name="new_email" placeholder="Add another email address" class="premium-input w-full px-4 py-3 rounded-lg focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                        <input type="tel" name="contact" value="<?= htmlspecialchars($contact_number) ?>" class="premium-input w-full px-4 py-3 rounded-lg focus:outline-none">
                                    </div>
                                    <div class="flex justify-end space-x-3 pt-4">
                                        <button type="button" onclick="hideContent()" class="px-6 py-3 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-300">Cancel</button>
                                        <button type="submit" class="premium-button px-6 py-3 text-white rounded-lg">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `;
                    break;

                case 'change-password':
                    content = `
                        <div class="premium-card rounded-2xl overflow-hidden animate-scale-in">
                            <div class="bg-gradient-to-r from-green-500 to-blue-600 px-6 py-4">
                                <h3 class="text-xl font-bold text-white flex items-center">
                                    <i class="fas fa-lock mr-2"></i>
                                    Change Password
                                </h3>
                            </div>
                            <div class="p-6">
                                <form class="space-y-6" method="post" action="" id="change-password-form">
                                    <input type="hidden" name="change_password" value="1">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                        <input type="password" name="current_password" class="premium-input w-full px-4 py-3 rounded-lg focus:outline-none" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                        <input type="password" name="new_password" class="premium-input w-full px-4 py-3 rounded-lg focus:outline-none" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="premium-input w-full px-4 py-3 rounded-lg focus:outline-none" required>
                                    </div>
                                    <div class="flex justify-end space-x-3 pt-4">
                                        <button type="button" onclick="hideContent()" class="px-6 py-3 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-300">Cancel</button>
                                        <button type="submit" class="premium-button px-6 py-3 text-white rounded-lg">Update Password</button>
                                    </div>
                                    <div id="change-password-message"></div>
                                </form>
                            </div>
                        </div>
                    `;
                    break;

                case 'notification-settings':
                    content = `
                        <div class="premium-card rounded-2xl overflow-hidden animate-scale-in">
                            <div class="bg-gradient-to-r from-blue-800 to-blue-300 px-6 py-4">
                                <h3 class="text-xl font-bold text-white flex items-center">
                                    <i class="fas fa-bell mr-2"></i>
                                    Notification Settings
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-6">
                                    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">Sound Notifications</h4>
                                            <p class="text-sm text-gray-500">Receive notifications sounds</p>
                                        </div>
                                        <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
                                    </div>
                                    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">Email Notifications</h4>
                                            <p class="text-sm text-gray-500">Receive notifications via email</p>
                                        </div>
                                        <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
                                    </div>
                                    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">Case Updates</h4>
                                            <p class="text-sm text-gray-500">Get notified about case status changes</p>
                                        </div>
                                        <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
                                    </div>
                                    <div class="flex justify-end pt-4">
                                        <button type="button" onclick="hideContent()" class="px-6 py-3 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-300 mr-3">Cancel</button>
                                        <button type="button" class="premium-button px-6 py-3 text-white rounded-lg">Save Settings</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    break;

                case 'logout':
                    content = `
                        <div class="premium-card rounded-2xl overflow-hidden animate-scale-in">
                            <div class="bg-gradient-to-r from-red-400 to-orange-300 px-6 py-4">
                                <h3 class="text-xl font-bold text-white flex items-center">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Logout
                                </h3>
                            </div>
                            <div class="p-8">
                                <div class="text-center">
                                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                        <i class="fas fa-sign-out-alt text-4xl text-red-600"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Are you sure you want to logout?</h3>
                                    <p class="text-sm text-gray-500 mb-8">You will be redirected to the login page.</p>
                                    <div class="flex justify-center space-x-4">
                                        <button type="button" onclick="hideContent()" class="px-6 py-3 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-300">Cancel</button>
                                        <a href="../bpamis_website/bpamis.php" class="premium-button px-6 py-3 text-white rounded-lg">Yes, Logout</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    break;
            }

            container.innerHTML = content;
            container.classList.remove('hidden');
            
            // Scroll to the content and center it in the viewport
            setTimeout(function() {
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 50);

            if (contentType === 'change-password') {
                attachChangePasswordHandler();
            }
        }

        // Function to hide content
        function hideContent() {
            const container = document.getElementById('dynamic-content');
            container.classList.add('hidden');
        }

        // Function to toggle switches
        function toggleSwitch(element) {
            element.classList.toggle('active');
        }

        function toggleNewEmail() {
            var container = document.getElementById('new-email-container');
            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }

        function attachChangePasswordHandler() {
            const form = document.getElementById('change-password-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    formData.append('ajax_change_password', '1');
                    fetch('profile.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        const msgDiv = document.getElementById('change-password-message');
                        if (data.success) {
                            msgDiv.className = 'mt-4 p-3 bg-green-100 text-green-800 rounded-lg text-center';
                            msgDiv.textContent = data.message;
                            form.reset();
                        } else if (data.message) {
                            msgDiv.className = 'mt-4 p-3 bg-red-100 text-red-800 rounded-lg text-center';
                            msgDiv.textContent = data.message;
                        } else {
                            msgDiv.textContent = '';
                        }
                    });
                });
            }
        }
    </script>
</body>
</html> 