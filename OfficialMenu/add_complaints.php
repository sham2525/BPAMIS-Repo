<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to MySQL
    $conn = new mysqli("localhost", "root", "", "barangay_case_management");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


//$complainant_name = $conn->real_escape_string($_POST['complainant_name']); // optional: if you plan to use this later
//$respondent_name = $conn->real_escape_string($_POST['respondent_name']);   // optional: if you plan to use this later
$complaint_title = $conn->real_escape_string($_POST['complaint_title']);
$complaint_description = $conn->real_escape_string($_POST['complaint_description']);
$incident_date = $conn->real_escape_string($_POST['incident_date']);

// TEMP: Manually assign Resident_ID
$resident_id = 1;

// Status can be 'Open' by default
$status = 'Pending';

// Insert with Complaint_Title included
$sql = "INSERT INTO COMPLAINT_INFO (Complaint_Title, Complaint_Details, Date_Filed, Status)
        VALUES ('$complaint_title', '$complaint_description', '$incident_date', '$status')";


    // Execute and check
    if ($conn->query($sql) === TRUE) {
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">';
        echo '<strong>Success!</strong> Complaint has been added.';
        echo '</div>';
    } else {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">';
        echo 'Error: ' . $conn->error;
        echo '</div>';
    }

    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Complaints</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
</head>
<body class="bg-gray-50 font-sans relative overflow-x-hidden">
    <?php include '../includes/barangay_official_cap_nav.php'; ?>

    <!-- Hero / Header -->
    <div class="w-full mt-8 px-4">
        <div class="relative gradient-bg rounded-2xl shadow-sm p-8 md:p-10 overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-100 rounded-full -mr-24 -mt-24 opacity-70 animate-[float_8s_ease-in-out_infinite]"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-blue-200 rounded-full -ml-14 -mb-14 opacity-60 animate-[float_6s_ease-in-out_infinite]"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-3xl md:text-4xl font-light text-blue-900 tracking-tight">Add <span class="font-semibold">Complaint</span></h1>
                    <p class="mt-3 text-gray-600 max-w-xl">Record a new complaint submitted in-person or through official intake. Ensure details are accurate before submission.</p>
                </div>
                <div class="hidden md:block">
                    <img src="../Assets/Img/complaint.svg" alt="Illustration" class="h-40 w-auto" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="w-full mt-8 px-4 pb-14">
        <div class="w-full max-w-5xl mx-auto bg-white/95 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md p-8 md:p-10 relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full opacity-70"></div>
            <div class="absolute -bottom-16 -left-16 w-56 h-56 bg-gradient-to-tr from-blue-50 to-cyan-100 rounded-full opacity-60"></div>
            <div class="relative z-10">
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    echo '<div class="mb-6 p-4 rounded-lg border border-green-200 bg-green-50 text-green-700 flex items-start gap-3"><i class="fa-solid fa-circle-check mt-0.5 text-green-500"></i><div><strong class="block">Success!</strong><span> Complaint has been added.</span></div></div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label for="complainant-name" class="block text-sm font-medium text-gray-700">Complainant Name</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-user"></i></span>
                                <input type="text" id="complainant-name" name="complainant_name" placeholder="Juan D. Dela Cruz" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input" required>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="respondent-name" class="block text-sm font-medium text-gray-700">Respondent Name</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-user-group"></i></span>
                                <input type="text" id="respondent-name" name="respondent_name" placeholder="Maria S. Santos" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input" required>
                            </div>
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label for="complaint-title" class="block text-sm font-medium text-gray-700">Complaint Title</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-tag"></i></span>
                                <input type="text" id="complaint-title" name="complaint_title" placeholder="e.g. Loud Noise Disturbance at Night" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input" required>
                            </div>
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label for="complaint-description" class="block text-sm font-medium text-gray-700">Description</label>
                            <div class="relative">
                                <textarea id="complaint-description" name="complaint_description" rows="6" placeholder="Provide a clear and detailed description of the complaint..." class="w-full p-4 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input resize-y" required></textarea>
                            </div>
                            <p class="text-xs text-gray-500">Include date, time, location and involved parties if known.</p>
                        </div>
                        <div class="space-y-2">
                            <label for="incident-date" class="block text-sm font-medium text-gray-700">Incident Date</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-calendar-day"></i></span>
                                <input type="date" id="incident-date" name="incident_date" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input" required>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Status (Auto)</label>
                            <div class="px-4 py-3 rounded-lg border border-dashed border-blue-200 bg-blue-50 text-sm text-blue-700 flex items-center gap-2"><i class="fa-solid fa-circle-info"></i> Pending</div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-3 sm:justify-between items-center">
                        <div class="text-xs text-gray-500 flex items-start gap-2 max-w-sm"><i class="fa-solid fa-shield-halved text-blue-500 mt-0.5"></i><span>Data recorded here becomes part of the official case intake log.</span></div>
                        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                            <a href="home-captain.php" class="py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg flex items-center justify-center gap-2 transition"><i class="fa-solid fa-xmark"></i> Cancel</a>
                            <button type="submit" class="py-3 px-8 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg flex items-center justify-center gap-2 shadow-sm transition"><i class="fa-solid fa-paper-plane"></i> Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../chatbot/bpamis_case_assistant.php'?>
    <?php include 'sidebar_.php';?>
</body>
</html>
