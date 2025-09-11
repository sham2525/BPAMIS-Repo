<?php
/**
 * Reschedule Hearing Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Hearing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <?php include '../includes/case_assistant_styles.php'; ?>
</head>
<body class="bg-blue-50">
    <?php include '../includes/barangay_official_nav.php'; ?>
    <div class="container mx-auto mt-10 p-5 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-semibold text-blue-800 mb-6">Reschedule Hearing</h2>
        
        <?php
        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Here you would typically:
            // 1. Validate the input
            // 2. Sanitize the input
            // 3. Update the database
            // For now we'll just show a success message
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">';
            echo '<strong>Success!</strong> Hearing has been rescheduled.';
            echo '</div>';
        }
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="mb-4">
                <label for="hearing-id" class="block text-blue-900 font-medium">Select Hearing</label>
                <select id="hearing-id" name="hearing_id" class="w-full p-2 border border-gray-300 rounded-lg" required>
                    <option value="">-- Select a Scheduled Hearing --</option>
                    <?php
                    // In a real application, you would fetch hearings from database
                    $sampleHearings = [
                        ['id' => 'H-2025-001', 'case' => 'Property Boundary Dispute', 'date' => '2025-05-15', 'time' => '09:00 AM'],
                        ['id' => 'H-2025-002', 'case' => 'Unpaid Debt', 'date' => '2025-05-17', 'time' => '10:30 AM'],
                        ['id' => 'H-2025-003', 'case' => 'Noise Complaint', 'date' => '2025-05-20', 'time' => '02:00 PM']
                    ];
                    
                    foreach ($sampleHearings as $hearing) {
                        echo '<option value="' . $hearing['id'] . '">' . $hearing['id'] . ' - ' . $hearing['case'] . ' (' . $hearing['date'] . ' at ' . $hearing['time'] . ')</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="new-hearing-date" class="block text-blue-900 font-medium">New Hearing Date</label>
                <input type="date" id="new-hearing-date" name="new_hearing_date" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="new-hearing-time" class="block text-blue-900 font-medium">New Hearing Time</label>
                <input type="time" id="new-hearing-time" name="new_hearing_time" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="reason" class="block text-blue-900 font-medium">Reason for Rescheduling</label>
                <textarea id="reason" name="reason" rows="4" class="w-full p-2 border border-gray-300 rounded-lg" required></textarea>
            </div>
            <div class="mb-4">
                <label for="notify-parties" class="block text-blue-900 font-medium">Notify Parties</label>
                <div class="flex items-center">
                    <input type="checkbox" id="notify-parties" name="notify_parties" class="mr-2" checked>
                    <span>Send notifications to all parties involved</span>
                </div>
            </div>
            <div class="flex justify-between">
                <button type="submit" class="bg-blue-600 text-white p-2 px-4 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-calendar-alt"></i> Reschedule Hearing
                </button>
                <a href="view_hearing_calendar.php" class="bg-gray-500 text-white p-2 px-4 rounded-lg hover:bg-gray-600">Cancel</a>
            </div>
        </form>
    </div>
      <script>
        // Ensure the new hearing date is in the future
        document.getElementById('new-hearing-date').min = new Date().toISOString().split('T')[0];
        
        // You could add additional validation here
    </script>
    
    <?php include '../includes/case_assistant.php'; ?>
</body>
</html>
