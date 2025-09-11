<?php
/**
 * Appoint Hearing Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
include '../SecMenu/schedule/db-connect.php';

// Handle success message from session
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedCase = (int) ($_POST['case_id'] ?? 0);
    $hearingTitle = $_POST['hearing_title'] ?? '';
    $hearingDate = $_POST['hearing_date'] ?? '';
    $hearingTime = $_POST['hearing_time'] ?? '';
    $venue = $_POST['venue'] ?? '';
    $participants = $_POST['participants'];
    $remarks = trim($_POST['hearing_remarks'] ?? '');

    if ($remarks === '') {
        $remarks = 'N/A';
    }

    $hearingDateTime = $hearingDate . ' ' . $hearingTime . ':00';

    $stmt = $conn->prepare("INSERT INTO schedule_list (Case_ID, hearingTitle, hearingDateTime, place, participant, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $selectedCase, $hearingTitle, $hearingDateTime, $venue, $participants, $remarks);

    if ($stmt->execute()) {
        // Get complainant & respondent IDs
        $residentQuery = "
            SELECT 
                co.Resident_ID, 
                cr.respondent_id AS Respondent_ID
            FROM case_info ci
            JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID
            JOIN complaint_respondents cr ON ci.Complaint_ID = cr.Complaint_ID
            WHERE ci.Case_ID = $selectedCase
            LIMIT 1
        ";
        $residentResult = $conn->query($residentQuery);

        if ($residentResult && $residentResult->num_rows > 0) {
            $resData = $residentResult->fetch_assoc();
            $resident_id = $resData['Resident_ID'];
            $respondent_id = $resData['Respondent_ID'];

            $notif_title = "Hearing Scheduled";
            $notif_message = "Your case (ID: $selectedCase) has been scheduled for a hearing on $hearingDate at $hearingTime.";
            $created_at = date('Y-m-d H:i:s');
            $type = 'Hearing';

            if (!empty($resident_id)) {
                $conn->query("
                    INSERT INTO notifications (resident_id, title, message, is_read, created_at, type)
                    VALUES ($resident_id, '$notif_title', '$notif_message', 0, '$created_at', '$type')
                ");
            }

            if (!empty($respondent_id)) {
                $conn->query("
                    INSERT INTO notifications (resident_id, title, message, is_read, created_at, type)
                    VALUES ($respondent_id, '$notif_title', '$notif_message', 0, '$created_at', '$type')
                ");
            }
        }

        // Store success message in session and redirect
        $_SESSION['success_message'] = "Hearing has been scheduled.";
        header("Location: appoint_hearing.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appoint Hearing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php include '../includes/barangay_official_cap_nav.php'; ?>

    <!-- Page Header -->
    <section class="container mx-auto mt-8 px-4">
        <div class="gradient-bg rounded-xl shadow-sm p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h1 class="text-2xl font-medium text-primary-800">Appoint Hearing</h1>
                <p class="mt-1 text-gray-600">Set a schedule for hearing appointment regarding a case.</p>
            </div>
        </div>
    </section>

    <div class="container mx-auto mt-8 mb-4 px-4">
        <div class="container mx-auto mt-10 p-5 bg-white shadow-md rounded-lg">
            
            <!-- Success Message -->
            <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- First Column -->
                    <div class="space-y-4">
                        <div>
                            <label for="case-id" class="block text-blue-900 font-medium">Select Case</label>
                            <select id="case-id" name="case_id" class="w-full p-2 border border-gray-300 rounded-lg" required>
                                <option value="">-- Select a Case --</option>
                                <?php
                                $sql = "SELECT ci.Case_ID, co.Complaint_Title
                                        FROM case_info ci
                                        JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID";
                                $result = $conn->query($sql);
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<option value="' . $row['Case_ID'] . '">' . $row['Case_ID'] . ' - ' . htmlspecialchars($row['Complaint_Title']) . '</option>';
                                    }
                                } else {
                                    echo '<option disabled>No cases found</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <input type="hidden" name="participants" id="participantsInput">

                        <div>
                            <label for="complainant_name" class="block text-blue-900 font-medium">Complainant Name</label>
                            <input type="text" id="complainant_name" name="complainant_name" readonly
                                class="w-full mt-1 border rounded p-2 bg-gray-100" />
                        </div>
                        <div>
                            <label for="Respondent_Name" class="block text-blue-900 font-medium">Respondent Name</label>
                            <input type="text" id="Respondent_Name" name="Respondent_Name"
                                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div> 
                        <div>
                            <label for="hearing-title" class="block text-blue-900 font-medium">Title</label>
                            <input type="text" id="hearing-title" name="hearing_title"
                                class="w-full p-2 border border-gray-300 rounded-lg" required>
                        </div>
                        <div>
                            <label for="hearing-date" class="block text-blue-900 font-medium">Date</label>
                            <input type="date" id="hearing-date" name="hearing_date"
                                class="w-full p-2 border border-gray-300 rounded-lg" required>
                        </div>
                    </div>
                    <!-- Second Column -->
                    <div class="space-y-4">
                        <div>
                            <label for="hearing-time" class="block text-blue-900 font-medium">Time</label>
                            <input type="time" id="hearing-time" name="hearing_time"
                                class="w-full p-2 border border-gray-300 rounded-lg" required>
                        </div>
                        <div>
                            <label for="venue" class="block text-blue-900 font-medium">Venue</label>
                            <input type="text" id="venue" name="venue"
                                class="w-full p-2 border border-gray-300 rounded-lg" required>
                        </div>
                        <div>
                            <label for="hearing-remarks" class="block text-blue-900 font-medium">Remarks</label>
                            <textarea id="hearing-remarks" name="hearing_remarks" class="w-full p-2 border border-gray-300 rounded-lg" rows="4"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between mt-6">
                    <button type="submit" class="bg-blue-600 text-white p-2 px-4 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-calendar-plus"></i> Schedule Hearing
                    </button>
                    <a href="home.php" class="bg-gray-500 text-white p-2 px-4 rounded-lg hover:bg-gray-600">Cancel</a>
                </div>
            </form>

        </div>
    </div>

    <?php include 'sidebar_.php';?>
</body>
<script>
    $('#case-id').on('change', function () {
        const caseId = $(this).val();
        if (caseId) {
            $.ajax({
                url: '../SecMenu/schedule/get_case_participants.php',
                type: 'GET',
                data: { Case_ID: caseId }, 
                success: function (response) {
                    try {
                        const data = JSON.parse(response);

                        if (data.complainant) {
                            $('#complainant_name').val(data.complainant);
                        } else {
                            $('#complainant_name').val('Not found');
                        }

                        if (data.respondents && data.respondents.length > 0) {
                            $('#Respondent_Name').val(data.respondents.join(', '));
                        } else {
                            $('#Respondent_Name').val('Not found');
                        }

                        const participantString = `Complainant: ${data.complainant ?? 'N/A'}, Respondent(s): ${data.respondents?.join(', ') || 'N/A'}`;
                        $('#participantsInput').val(participantString);

                    } catch (err) {
                        console.error('Invalid JSON:', err);
                        $('#complainant_name').val('');
                        $('#Respondent_Name').val('');
                    }
                },
                error: function () {
                    $('#complainant_name').val('');
                    $('#Respondent_Name').val('');
                    console.error('AJAX failed');
                }
            });
        } else {
            $('#complainant_name').val('');
            $('#Respondent_Name').val('');
        }
    });

    // Ensure no past dates
    document.getElementById('hearing-date').min = new Date().toISOString().split('T')[0];
</script>
</html>
