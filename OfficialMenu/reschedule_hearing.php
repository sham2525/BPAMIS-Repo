<?php

include '../SecMenu/schedule/db-connect.php';

// Handle AJAX fetch
if (isset($_GET['ajax']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM schedule_list WHERE hearingID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
    exit;
}

// Fetch selected schedule if editing directly via URL (?id=...)
$editData = null;
if (isset($_GET['id'])) {
    $hearing_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM schedule_list WHERE hearingID = ?");
    $stmt->bind_param("i", $hearing_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $editId = isset($_POST['hearing_id']) ? intval($_POST['hearing_id']) : 0;
    $hearingTitle = $_POST['hearing_title'] ?? '';
    $hearingDate = $_POST['hearing_date'] ?? '';
    $hearingTime = $_POST['hearing_time'] ?? '';
    $venue = $_POST['venue'] ?? '';
    $participants = trim($_POST['participants'] ?? '');
    $remarks = trim($_POST['hearing_remarks'] ?? '') ?: 'N/A';
    $hearingDateTime = $hearingDate . ' ' . $hearingTime . ':00';

    if ($editId > 0) {
        $stmt = $conn->prepare("UPDATE schedule_list SET hearingTitle=?, hearingDateTime=?, place=?, participant=?, remarks=? WHERE hearingID=?");
        $stmt->bind_param("sssssi", $hearingTitle, $hearingDateTime, $venue, $participants, $remarks, $editId);
        $stmt->execute();
        $notice = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                     <strong>Success!</strong> Hearing has been updated.
                   </div>';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reschedule Hearing</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</head>
<body class="bg-gray-50">

<!-- Top Navigation -->
<?php include '../includes/barangay_official_cap_nav.php'; ?>

  <!-- Page Header -->
  <section class="container mx-auto mt-8 px-4">
        <div class="gradient-bg rounded-xl shadow-sm p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h1 class="text-2xl font-medium text-primary-800">Reschedule Hearing</h1>
                <p class="mt-1 text-gray-600">Set an reschedule for hearing appointment regarding about case.</p>
            </div>
        </div>
    </section>

<div class="flex">
  <!-- Sidebar -->
  <div class="w-64">
    <?php include 'sidebar_.php'; ?>
  </div>

  <!-- Main Content -->
  <div class="container mx-auto mt-8 mb-4 px-4">
    <div class="container mx-auto mt-10 p-5 bg-white shadow-md rounded-lg">
    
      <?php
        include '../server/server.php';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $hearing_id = $_POST['hearing_id'];
            $new_date = $_POST['hearing_date'];
            $new_time = $_POST['hearing_time'];
            $remark = $_POST['hearing_remarks'];
            $notify = isset($_POST['notify_parties']) ? 1 : 0;

          if ($hearing_id && $new_date && $new_time) {
            $new_datetime = date("Y-m-d H:i:s", strtotime("$new_date $new_time"));

            $stmt = $conn->prepare("UPDATE schedule_list SET hearingDateTime = ?, remarks = ? WHERE hearingID = ?");
            $stmt->bind_param("ssi", $new_datetime, $remark, $hearing_id);

            if ($stmt->execute()) {
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">';
                echo '<strong>Success!</strong> Hearing has been rescheduled.';
                echo '</div>';
              }

                if ($notify) {
                    // Fetch complaint_id from case_info
                    $get_case = $conn->prepare("
                        SELECT ci.complaint_id 
                        FROM schedule_list sl
                        JOIN case_info ci ON sl.case_id = ci.case_id
                        WHERE sl.hearingID = ?
                    ");
                    $get_case->bind_param("i", $hearing_id);
                    $get_case->execute();
                    $case_result = $get_case->get_result();

                    if ($case_result->num_rows > 0) {
                        $row = $case_result->fetch_assoc();
                        $complaint_id = $row['complaint_id'];

                        $msg = "Your hearing (ID: $hearing_id) has been rescheduled to $new_date at $new_time.";

                        // Notify resident
                        $get_resident = $conn->prepare("SELECT resident_id FROM complaint_info WHERE complaint_id = ?");
                        $get_resident->bind_param("i", $complaint_id);
                        $get_resident->execute();
                        $res_result = $get_resident->get_result();

                        if ($res_result->num_rows > 0) {
                            $res = $res_result->fetch_assoc();
                            $resident_id = $res['resident_id'];

                            $stmt_notif = $conn->prepare("
                                INSERT INTO notifications (resident_id, type, title, message, created_at, is_read)
                                VALUES (?, 'Hearing', 'Hearing Rescheduled', ?, NOW(), 0)
                            ");
                            $stmt_notif->bind_param("is", $resident_id, $msg);
                            $stmt_notif->execute();
                        }

                        // Notify external complainant
                        $get_external = $conn->prepare("SELECT external_complainant_id FROM complaint_info WHERE complaint_id = ?");
                        $get_external->bind_param("i", $complaint_id);
                        $get_external->execute();
                        $ext_result = $get_external->get_result();

                        if ($ext_result->num_rows > 0) {
                            $ext = $ext_result->fetch_assoc();
                            $external_id = $ext['external_complainant_id'];

                            $stmt_ext_notif = $conn->prepare("
                                INSERT INTO notifications (external_complaint_id, type, title, message, created_at, is_read)
                                VALUES (?, 'Hearing', 'Hearing Rescheduled', ?, NOW(), 0)
                            ");
                            $stmt_ext_notif->bind_param("is", $external_id, $msg);
                            $stmt_ext_notif->execute();
                        }
                    }
                }
            } else {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">';
                echo '<strong>Error!</strong> ' . $conn->error;
                echo '</div>';
            }
        }
        ?>

      <?= $notice ?? '' ?>

      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- First Column -->
                <div class="space-y-4">
                    <div>
                        <label for="schedule-dropdown" class="block text-blue-900 font-medium">Select Case</label>
                        <select id="schedule-dropdown" name="case_id" class="w-full p-2 border border-gray-300 rounded-lg" required>
                            <option value="">-- Select a Case --</option>
            <?php
            $sql = "SELECT hearingID, hearingTitle FROM schedule_list";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['hearingID']}'>{$row['hearingID']} - " . htmlspecialchars($row['hearingTitle']) . "</option>";
            }
            ?>
            </select>
        </div>

      <input type="hidden" id="hearing_id" name="hearing_id" value="<?= $editData['hearingID'] ?? '' ?>">
      
        <div class="mb-4">
          <label for="hearing-title" class="block text-blue-900 font-medium">Title</label>
          <input readonly type="text" id="hearing-title" name="hearing_title" value="<?= $editData['hearingTitle'] ?? '' ?>" class="w-full p-2 border border-gray-300 rounded-lg" required>
        </div>

        <div class="mb-4">
          <label for="hearing-date" class="block text-blue-900 font-medium">Date</label>
          <input type="date" id="hearing-date" name="hearing_date" value="<?= isset($editData['hearingDateTime']) ? date('Y-m-d', strtotime($editData['hearingDateTime'])) : '' ?>" class="w-full p-2 border border-gray-300 rounded-lg" required>
        </div>
        </div>

        <div class="space-y-4">
          <div class="mb-4">
          <label for="hearing-time" class="block text-blue-900 font-medium">Time</label>
          <input type="time" id="hearing-time" name="hearing_time" value="<?= isset($editData['hearingDateTime']) ? date('H:i', strtotime($editData['hearingDateTime'])) : '' ?>" class="w-full p-2 border border-gray-300 rounded-lg" required>
          </div>

          <div class="mb-4">
          <label for="venue" class="block text-blue-900 font-medium">Venue</label>
          <input readonly type="text" id="venue" name="venue" value="<?= $editData['place'] ?? '' ?>" class="w-full p-2 border border-gray-300 rounded-lg" required>
          </div>

          <div class="mb-4">
          <label for="participants" class="block text-blue-900 font-medium">Participants</label>
          <textarea readonly id="participants" name="participants" class="w-full p-2 border border-gray-300 rounded-lg" rows="3"><?= $editData['participant'] ?? '' ?></textarea>
        </div>


          

          <div class="mb-4">
          <label for="hearing-remarks" class="block text-blue-900 font-medium">Remarks</label>
          <textarea id="hearing-remarks" name="hearing_remarks" class="w-full p-2 border border-gray-300 rounded-lg" rows="4"><?= $editData['remarks'] ?? '' ?></textarea>
          </div>
          </div>
          </div>
          <div class="flex justify-between mt-6">
          <button type="submit" class="bg-blue-600 text-white p-2 px-4 rounded-lg hover:bg-blue-700">
            <i class="fas fa-calendar-check"></i> Update Hearing
          </button>
          <a href="home-secretary.php" class="bg-gray-500 text-white p-2 px-4 rounded-lg hover:bg-gray-600">Cancel</a>
          </div>
      </form>
    </div>
  </div>


<script>
  document.getElementById('hearing-date').min = new Date().toISOString().split('T')[0];

 document.getElementById('schedule-dropdown').addEventListener('change', function () {
  const id = this.value;
  if (!id) return;

  fetch(`<?= $_SERVER['PHP_SELF'] ?>?ajax=1&id=${id}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById('hearing_id').value = data.hearingID || '';
      document.getElementById('hearing-title').value = data.hearingTitle || '';
      
      if (data.hearingDateTime) {
        const [date, time] = data.hearingDateTime.split(' ');
        document.getElementById('hearing-date').value = date;
        document.getElementById('hearing-time').value = time.slice(0, 5);
      }

      document.getElementById('venue').value = data.place || '';
      document.getElementById('participants').value = data.participant || '';
      document.getElementById('hearing-remarks').value = data.remarks || '';
      document.getElementById('complainant_name').value = data.complainant_name || '';
      document.getElementById('Respondent_Name').value = data.respondent_name || '';
    });
});

</script>
<?php include '../chatbot/bpamis_case_assistant.php'?>
</body>
</html>