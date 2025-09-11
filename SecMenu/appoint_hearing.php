<?php
/**
 * Appoint Hearing Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appoint Hearing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <?php include '../includes/case_assistant_styles.php'; ?>
</head>
<body class="bg-blue-50">
    <?php include '../includes/barangay_official_nav.php'; ?>
    <div class="container mx-auto mt-10 p-5 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-semibold text-blue-800 mb-6">Appoint Hearing</h2>
        
        <?php
        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Here you would typically:
            // 1. Validate the input
            // 2. Sanitize the input
            // 3. Insert into database
            // For now we'll just show a success message
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">';
            echo '<strong>Success!</strong> Hearing has been scheduled.';
            echo '</div>';
        }
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="mb-4">
                <label for="case-id" class="block text-blue-900 font-medium">Select Case</label>
                <select id="case-id" name="case_id" class="w-full p-2 border border-gray-300 rounded-lg" required>
                    <option value="">-- Select a Case --</option>
                    <?php
                    // In a real application, you would fetch cases from database
                    $sampleCases = [
                        ['id' => 'KP-2025-001', 'title' => 'Property Boundary Dispute'],
                        ['id' => 'KP-2025-002', 'title' => 'Unpaid Debt'],
                        ['id' => 'KP-2025-003', 'title' => 'Noise Complaint']
                    ];
                    
                    foreach ($sampleCases as $case) {
                        echo '<option value="' . $case['id'] . '">' . $case['id'] . ' - ' . $case['title'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="hearing-title" class="block text-blue-900 font-medium">Hearing Title</label>
                <input type="text" id="hearing-title" name="hearing_title" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="hearing-date" class="block text-blue-900 font-medium">Hearing Date</label>
                <input type="date" id="hearing-date" name="hearing_date" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="hearing-time" class="block text-blue-900 font-medium">Hearing Time</label>
                <input type="time" id="hearing-time" name="hearing_time" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="venue" class="block text-blue-900 font-medium">Venue</label>
                <input type="text" id="venue" name="venue" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="e.g., Barangay Hall" required>
            </div>
            <div class="mb-4">
                <label for="participants" class="block text-blue-900 font-medium">Participants</label>
                <textarea id="participants" name="participants" class="w-full p-2 border border-gray-300 rounded-lg" rows="3" placeholder="Enter names of all participants separated by commas"></textarea>
            </div>
            <div class="mb-4">
                <label for="hearing-remarks" class="block text-blue-900 font-medium">Remarks</label>
                <textarea id="hearing-remarks" name="hearing_remarks" class="w-full p-2 border border-gray-300 rounded-lg" rows="4"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="submit" class="bg-blue-600 text-white p-2 px-4 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-calendar-plus"></i> Schedule Hearing
                </button>
                <a href="home.php" class="bg-gray-500 text-white p-2 px-4 rounded-lg hover:bg-gray-600">Cancel</a>
            </div>
        </form>
    </div>
      <script>
        // You could add validation for dates, ensuring they are in the future
        document.getElementById('hearing-date').min = new Date().toISOString().split('T')[0];
    </script>
    
    <?php include '../includes/case_assistant.php'; ?>
</body>
</html>
