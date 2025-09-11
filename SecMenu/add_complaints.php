<?php
/**
 * Add Complaints Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Complaints</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <?php include '../includes/case_assistant_styles.php'; ?>
</head>
<body class="bg-blue-50">
    <?php include '../includes/barangay_official_nav.php'; ?>
    <div class="container mx-auto mt-10 p-5 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-semibold text-blue-800 mb-6">Add New Complaint</h2>
        
        <?php
        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Here you would typically:
            // 1. Validate the input
            // 2. Sanitize the input
            // 3. Insert into database
            // For now we'll just show a success message
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">';
            echo '<strong>Success!</strong> Complaint has been added.';
            echo '</div>';
        }
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="mb-4">
                <label for="complainant-name" class="block text-blue-900">Complainant Name</label>
                <input type="text" id="complainant-name" name="complainant_name" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="respondent-name" class="block text-blue-900">Respondent Name</label>
                <input type="text" id="respondent-name" name="respondent_name" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="complaint-title" class="block text-blue-900">Complaint Title</label>
                <input type="text" id="complaint-title" name="complaint_title" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="complaint-description" class="block text-blue-900">Description</label>
                <textarea id="complaint-description" name="complaint_description" rows="4" class="w-full p-2 border border-gray-300 rounded-lg" required></textarea>
            </div>
            <div class="mb-4">
                <label for="incident-date" class="block text-blue-900">Incident Date</label>
                <input type="date" id="incident-date" name="incident_date" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="flex justify-between">
                <button type="submit" class="bg-blue-600 text-white p-2 px-4 rounded-lg hover:bg-blue-700">Submit Complaint</button>
                <a href="home.php" class="bg-gray-500 text-white p-2 px-4 rounded-lg hover:bg-gray-600">Cancel</a>
            </div>
        </form>
    </div>    <script>
        // Form validation can be added here using JavaScript
    </script>
    
    <?php include '../includes/case_assistant.php'; ?>
</body>
</html>
