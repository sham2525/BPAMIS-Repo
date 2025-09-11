<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$case_id = $_GET['id'] ?? 0;

// Save new hearing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hearing_date = $_POST['hearing_date'];
    $hearing_time = $_POST['hearing_time'] . ':00';  
    
    // Check if hearing_end_time is provided; if empty, use NULL
    $hearing_end_time = !empty($_POST['hearing_end_time']) ? $_POST['hearing_end_time'] . ':00' : null;
    
    $details = $_POST['details'];

    $complainant_status = $_POST['complainant_status'] ?? null;

    // If respondent_status is an array, join into a comma-separated string
    if (is_array($_POST['respondent_status'])) {
        $respondent_status = implode(', ', $_POST['respondent_status']);
    } else {
        $respondent_status = $_POST['respondent_status'] ?? null;
    }

    // Adjust SQL to include Hearing_End_Time
    $stmt = $conn->prepare("INSERT INTO MEETING_LOGS 
        (Case_ID, Hearing_Date, Hearing_Time, Hearing_End_Time, Hearing_Details, Complainant_Status, Respondent_Status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Use "issssss" and pass $hearing_end_time (can be NULL)
    $stmt->bind_param("issssss", $case_id, $hearing_date, $hearing_time, $hearing_end_time, $details, $complainant_status, $respondent_status);

    $stmt->execute();
    $stmt->close();

    header("Location: meeting_cases_log.php?id=" . $case_id);
    exit;
}


$latest_date = '';
$latest_time = '';

$date_stmt = $conn->prepare("SELECT HearingDateTime 
                             FROM schedule_list 
                             WHERE Case_ID = ? 
                             ORDER BY HearingDateTime DESC 
                             LIMIT 1");
$date_stmt->bind_param("i", $case_id);
$date_stmt->execute();
$date_result = $date_stmt->get_result();
if ($row = $date_result->fetch_assoc()) {
    $dt = new DateTime($row['HearingDateTime']);
    $latest_date = $dt->format('Y-m-d'); // For <input type="date">
    $latest_time = $dt->format('H:i');   // For <input type="time">
}
$date_stmt->close();

$caseDetails = null;
$sql = "
SELECT 
    cs.Case_ID,
    cs.Case_Status,
    ci.Complaint_ID,
    ci.Complaint_Title,
    ci.Date_Filed,
    CONCAT(
        COALESCE(res_com.First_Name, ext_com.First_Name, ''),
        ' ',
        COALESCE(res_com.Last_Name, ext_com.Last_Name, '')
    ) AS complainant_name,
    GROUP_CONCAT(
        CONCAT(
            COALESCE(res_res.First_Name, ''),
            ' ',
            COALESCE(res_res.Last_Name, '')
        ) SEPARATOR ', '
    ) AS respondent_names
FROM CASE_INFO cs
LEFT JOIN COMPLAINT_INFO ci 
    ON cs.Complaint_ID = ci.Complaint_ID
LEFT JOIN RESIDENT_INFO res_com 
    ON ci.Resident_ID = res_com.Resident_ID
LEFT JOIN external_complainant ext_com 
    ON ci.External_Complainant_ID = ext_com.External_Complaint_ID
LEFT JOIN COMPLAINT_RESPONDENTS cr
    ON ci.Complaint_ID = cr.Complaint_ID
LEFT JOIN RESIDENT_INFO res_res
    ON cr.Respondent_ID = res_res.Resident_ID
WHERE cs.Case_ID = ?
GROUP BY cs.Case_ID
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $case_id);

$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $caseDetails = $row;
}

// Get Lupon Tagapamayapa / Mediator based on case status
$lupon_sql = "
    SELECT 
        CASE 
            WHEN cs.Case_Status = 'Mediation' THEN mi.Mediator_Name
            WHEN cs.Case_Status = 'Resolution' THEN ri.Mediator_Name
            WHEN cs.Case_Status = 'Settlement' THEN si.Mediator_Name
            ELSE NULL
        END AS lupon_tagapamayapa
    FROM CASE_INFO cs
    LEFT JOIN mediation_info mi 
        ON cs.Case_ID = mi.Case_ID
    LEFT JOIN resolution ri 
        ON cs.Case_ID = ri.Case_ID
    LEFT JOIN settlement si 
        ON cs.Case_ID = si.Case_ID
    WHERE cs.Case_ID = ?
";

$lupon_stmt = $conn->prepare($lupon_sql);
$lupon_stmt->bind_param("i", $case_id);
$lupon_stmt->execute();
$lupon_result = $lupon_stmt->get_result();
$lupon_name = null;
if ($row = $lupon_result->fetch_assoc()) {
    $lupon_name = $row['lupon_tagapamayapa'] ?? 'N/A';
}
$lupon_stmt->close();

$sql_respondents = "
    SELECT 
        r.Respondent_ID,
        CONCAT(COALESCE(res.First_Name, ''), ' ', COALESCE(res.Last_Name, '')) AS name
    FROM COMPLAINT_RESPONDENTS r
    LEFT JOIN RESIDENT_INFO res ON r.Respondent_ID = res.Resident_ID
    WHERE r.Complaint_ID = ?
";
$stmt_res = $conn->prepare($sql_respondents);
$stmt_res->bind_param("i", $caseDetails['Complaint_ID']); 
$stmt_res->execute();
$res_result = $stmt_res->get_result();

$respondents = [];
while ($row = $res_result->fetch_assoc()) {
    $respondents[] = $row;
}
$stmt_res->close();

$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Hearing Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</head>
<body class="bg-gray-50 font-sans">
<?php include '../includes/barangay_official_sec_nav.php'; ?>

  <!-- Page Header (copied from view_cases.php) -->
  <section class="container mx-auto mt-8 px-4">
        <div class="gradient-bg rounded-xl shadow-sm p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h1 class="text-2xl font-medium text-primary-800">Edit Case Hearing Log</h1>
                <p class="mt-1 text-gray-600">Record and view minutes of barangay meetings</p>
            </div>
        </div>
    </section>

  <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 mt-8 px-4">
    
    <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-8 flex flex-col">
      
      <!-- Header with title + button side by side -->
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold text-primary-700 flex items-center gap-2">
          <i class="fas fa-plus-circle text-primary-500"></i> Log New Hearing
        </h3>
        <?php if ($caseDetails): ?>
        <a href="view_case_details.php?id=<?= urlencode($caseDetails['Case_ID']) ?>" 
           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
           View Case Details
        </a>
        <?php endif; ?>
      </div>

      <?php if ($caseDetails): ?>
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4">
            <p class="text-lg font-semibold text-primary-700 mb-2">
                Lupon Tagapamayapa/Mediator: <p><?= htmlspecialchars($lupon_name) ?></p>
            </p>
          </div>
          
          <form method="POST" class="space-y-4">
              <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4">
              <h2 class="text-lg font-semibold text-primary-700 mb-2">Complainant:</h2>
              <div class="flex items-center justify-between">
                  <p class="mr-4"><?= htmlspecialchars($caseDetails['complainant_name'] ?? 'N/A') ?></p>
                  <select name="complainant_status" class="border border-gray-300 rounded px-2 py-1">
                      <option value="Present">Present</option>
                      <option value="Unattended">Unattended</option>
                      <option value="Excused">Excused</option>
                  </select>
              </div>
          </div>

          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4">
              <h2 class="text-lg font-semibold text-primary-700 mb-2">Respondent/s:</h2>

              <?php foreach ($respondents as $res): ?>
                  <div class="flex items-center justify-between mb-2">
                      <p class="mr-4"><?= htmlspecialchars($res['name']) ?></p>
                      <select name="respondent_status[<?= $res['Respondent_ID'] ?>]" class="border border-gray-300 rounded px-2 py-1">
                          <option value="Present">Present</option>
                          <option value="Unattended">Unattended</option>
                          <option value="Excused">Excused</option>
                      </select>
                  </div>
              <?php endforeach; ?>
          </div>



    <div>
        <label class="block mb-1 text-sm font-medium text-blue-900">Hearing Date</label>
        <input type="date" name="hearing_date" required readonly
               value="<?= htmlspecialchars($latest_date); ?>"
               class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
    </div>

    <div class="flex space-x-4">
      <div class="flex-1">
        <label class="block mb-1 text-sm font-medium text-blue-900" for="hearing_time">Hearing Start Time</label>
        <input type="time" id="hearing_time" name="hearing_time" required
              value="<?= htmlspecialchars($latest_time); ?>"
              class="w-full p-3 border border-gray-300 rounded-lg">
      </div>
      <div class="flex-1">
        <label class="block mb-1 text-sm font-medium text-blue-900" for="hearing_end_time">Hearing End Time</label>
        <input type="time" id="hearing_end_time" name="hearing_end_time"
              value=""
              class="w-full p-3 border border-gray-300 rounded-lg">
      </div>
    </div>



    <div>
        <label class="block mb-1 text-sm font-medium text-blue-900">Details</label>
        <textarea name="details" rows="5" required class="w-full p-3 border border-gray-300 rounded-lg"></textarea>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-700 transition">
            <i class="fas fa-save"></i> Save Hearing
        </button>
    </div>
</form>


      <?php endif; ?>


      
    </div>
    <!-- RIGHT: Show previous hearings -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-md p-8 flex flex-col">
  <h3 class="text-xl font-semibold text-primary-700 mb-4 flex items-center gap-2">
    <i class="fas fa-history text-primary-500"></i> Previous Hearings
  </h3>
  <div class="overflow-y-auto max-h-[400px] pr-2">
    <?php
    $stmt = $conn->prepare("
      SELECT 
  m.Log_ID,
  m.Hearing_Date,
  m.Hearing_Time,
  m.Hearing_End_Time,
  m.Hearing_Details,
  m.Complainant_Status,
  m.Respondent_Status,
  c.Case_ID,
  CONCAT(
    COALESCE(res_com.First_Name, ext_com.First_Name, ''),
    ' ',
    COALESCE(res_com.Last_Name, ext_com.Last_Name, '')
  ) AS complainant_name,
  GROUP_CONCAT(
    DISTINCT CONCAT(
      COALESCE(res_res.First_Name, ''),
      ' ',
      COALESCE(res_res.Last_Name, '')
    ) SEPARATOR ', '
  ) AS respondents
FROM MEETING_LOGS m
JOIN CASE_INFO c ON m.Case_ID = c.Case_ID
LEFT JOIN COMPLAINT_INFO ci ON c.Complaint_ID = ci.Complaint_ID
LEFT JOIN RESIDENT_INFO res_com ON ci.Resident_ID = res_com.Resident_ID
LEFT JOIN external_complainant ext_com ON ci.External_Complainant_ID = ext_com.External_Complaint_ID
LEFT JOIN COMPLAINT_RESPONDENTS cr ON ci.Complaint_ID = cr.Complaint_ID
LEFT JOIN RESIDENT_INFO res_res ON cr.Respondent_ID = res_res.Resident_ID
WHERE m.Case_ID = ?
GROUP BY m.Log_ID
ORDER BY m.Hearing_Date DESC, m.Hearing_Time DESC

    ");
    $stmt->bind_param("i", $case_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      echo '<ol class="relative border-l-2 border-primary-200 ml-2">';
      while ($log = $result->fetch_assoc()) {
          $startTime = date("g:i A", strtotime($log['Hearing_Time']));

          // Format end time if not null or empty
          if (!empty($log['Hearing_End_Time'])) {
              $endTime = date("g:i A", strtotime($log['Hearing_End_Time']));
              $formattedTime = $startTime . " - " . $endTime;
          } else {
              $formattedTime = $startTime;
          }

          echo '<li class="mb-8 ml-6">';
          echo '<span class="absolute -left-3 flex items-center justify-center w-6 h-6 bg-primary-500 rounded-full ring-4 ring-white"><i class="fas fa-gavel text-white text-xs"></i></span>';
          echo '<div class="bg-primary-50 border border-primary-100 rounded-lg p-4 shadow-sm cursor-pointer hearing-item"
                  data-date="' . htmlspecialchars($log['Hearing_Date']) . '"
                  data-time="' . $formattedTime . '"
                  data-details="' . htmlspecialchars($log['Hearing_Details']) . '"
                  data-complainant="' . htmlspecialchars($log['complainant_name'] ?? 'N/A') . '"
                  data-complainantstatus="' . htmlspecialchars($log['Complainant_Status'] ?? 'N/A') . '"
                  data-respondents="' . htmlspecialchars($log['respondents'] ?? 'N/A') . '"
                  data-respondentstatus="' . htmlspecialchars($log['Respondent_Status'] ?? 'N/A') . '"
                  data-caseid="' . $log['Case_ID'] . '">
                  <p class="text-sm text-primary-700 font-semibold mb-1">
                    <i class="fas fa-calendar-day mr-1"></i> ' . htmlspecialchars($log['Hearing_Date']) . 
                    ' <span class="mx-2">|</span> 
                    <i class="fas fa-clock mr-1"></i>' . $formattedTime . '
                  </p>
                  <p class="text-gray-800 whitespace-pre-wrap mt-1">' . nl2br(htmlspecialchars($log['Hearing_Details'])) . '</p>
                </div>';
          echo '</li>';
      }

      echo '</ol>';
    } else {
      echo '<p class="text-gray-500">No hearings logged yet.</p>';
    }
    $stmt->close();
    ?>
  </div>
</div>

<!-- MODAL -->
<div id="hearingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

  <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg relative">
    <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700" id="closeModal">&times;</button>
    <h2 class="text-xl font-bold mb-4">Hearing Details</h2>
    <p><strong>Date:</strong> <span id="modalDate"></span></p>
    <p><strong>Time:</strong> <span id="modalTime"></span></p>
    <p><strong>Details:</strong> <span id="modalDetails"></span></p>
    <p><strong>Complainant:</strong> <span id="modalComplainant"></span> (<span id="modalComplainantStatus"></span>)</p>
    <p><strong>Respondents:</strong> <span id="modalRespondents"></span></p>
    <p><strong>Respondent Status:</strong> <span id="modalRespondentStatus"></span></p>
    <div class="mt-4">
      <a id="modalCaseLink" href="#" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">View Case Details</a>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.hearing-item').forEach(item => {
  item.addEventListener('click', function() {
    document.getElementById('modalDate').textContent = this.dataset.date;
    document.getElementById('modalTime').textContent = this.dataset.time;
    document.getElementById('modalDetails').textContent = this.dataset.details;
    document.getElementById('modalComplainant').textContent = this.dataset.complainant;
    document.getElementById('modalComplainantStatus').textContent = this.dataset.complainantstatus;
    document.getElementById('modalRespondents').textContent = this.dataset.respondents;
    document.getElementById('modalRespondentStatus').textContent = this.dataset.respondentstatus;
    document.getElementById('modalCaseLink').href = 'view_case_details.php?id=' + this.dataset.caseid;
    document.getElementById('hearingModal').classList.remove('hidden');
  });
});

const modal = document.getElementById('hearingModal');

document.querySelectorAll('.hearing-item').forEach(item => {
  item.addEventListener('click', function() {
    // ... set modal content as before ...
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  });
});

document.getElementById('closeModal').addEventListener('click', () => {
  modal.classList.add('hidden');
  modal.classList.remove('flex');
});

modal.addEventListener('click', (e) => {
  if (e.target === modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }
});

</script>

  </div>
  <?php include 'sidebar_.php';?>
</body>
</html>
