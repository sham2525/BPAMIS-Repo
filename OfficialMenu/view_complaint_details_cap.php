<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barangay_case_management");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($complaint_id <= 0) { echo "Invalid complaint."; exit; }
$is_case = false;
$editing = isset($_GET['edit']);

// Check if already in CASE_INFO
$case_result = $conn->query("SELECT 1 FROM CASE_INFO WHERE Complaint_ID = $complaint_id LIMIT 1");
$is_case = $case_result && $case_result->num_rows > 0;

// Keep server-side actions (not exposed in UI here)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_complaint']) && !$is_case) {
    $title = $conn->real_escape_string($_POST['complaint_title'] ?? '');
    $details = $conn->real_escape_string($_POST['complaint_details'] ?? '');
    $conn->query("UPDATE COMPLAINT_INFO SET Complaint_Title = '$title', Complaint_Details = '$details' WHERE Complaint_ID = $complaint_id");
    header("Location: view_complaint_details.php?id=$complaint_id"); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validate_case']) && !$is_case) {
    $date_opened = date('Y-m-d');
    $conn->query("INSERT INTO CASE_INFO (Complaint_ID, Case_Status, Date_Opened) VALUES ($complaint_id, 'Open', '$date_opened')");
    $conn->query("UPDATE COMPLAINT_INFO SET Status = 'IN CASE' WHERE Complaint_ID = $complaint_id");
    header("Location: view_complaint_details.php?id=$complaint_id"); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_complaint']) && !$is_case) {
    $conn->query("UPDATE COMPLAINT_INFO SET Status = 'Rejected' WHERE Complaint_ID = $complaint_id");
    header("Location: view_complaint_details.php?id=$complaint_id"); exit;
}

// Fetch complaint (with resident or external complainant name)
$sql = "SELECT c.*, r.First_Name AS Res_First_Name, r.Last_Name AS Res_Last_Name, 
               e.First_Name AS Ext_First_Name, e.Last_Name AS Ext_Last_Name
        FROM COMPLAINT_INFO c
        LEFT JOIN RESIDENT_INFO r ON c.Resident_ID = r.Resident_ID
        LEFT JOIN EXTERNAL_COMPLAINANT e ON c.External_Complainant_ID = e.External_Complaint_ID
        WHERE c.Complaint_ID = $complaint_id";
$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) { echo "Complaint not found."; exit; }
$complaint = $result->fetch_assoc();
$is_rejected = strtolower($complaint['Status']) === 'rejected';

$complainant_name = !empty($complaint['Res_First_Name'])
    ? $complaint['Res_First_Name'] . ' ' . $complaint['Res_Last_Name']
    : (!empty($complaint['Ext_First_Name']) ? $complaint['Ext_First_Name'] . ' ' . $complaint['Ext_Last_Name'] : 'Unknown');

// Respondent list (read-only)
$respondents = [];
if (!empty($complaint['Respondent_ID'])) {
    $mr = $conn->query("SELECT First_Name, Last_Name FROM RESIDENT_INFO WHERE Resident_ID=".(int)$complaint['Respondent_ID']);
    if ($mr && $mr->num_rows > 0) { $r = $mr->fetch_assoc(); $respondents[] = $r['First_Name'].' '.$r['Last_Name']; }
}
$ar = $conn->query("SELECT r.First_Name, r.Last_Name FROM COMPLAINT_RESPONDENTS cr JOIN RESIDENT_INFO r ON cr.Respondent_ID=r.Resident_ID WHERE cr.Complaint_ID=$complaint_id");
if ($ar && $ar->num_rows > 0) { while($r = $ar->fetch_assoc()){ $respondents[] = $r['First_Name'].' '.$r['Last_Name']; } }
$respondent_names = !empty($respondents) ? implode(', ', $respondents) : 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Complaint • Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:{50:'#f0f7ff',100:'#e0effe',200:'#bae2fd',300:'#7cccfd',400:'#36b3f9',500:'#0c9ced',600:'#0281d4',700:'#026aad',800:'#065a8f',900:'#0a4b76'}},boxShadow:{glow:'0 0 0 1px rgba(12,156,237,.08),0 4px 20px -2px rgba(6,90,143,.18)'},animation:{'fade-in':'fadeIn .4s ease-out'},keyframes:{fadeIn:{'0%':{opacity:0},'100%':{opacity:1}}}}}};</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        .glass{background:linear-gradient(140deg,rgba(255,255,255,.92),rgba(255,255,255,.68));backdrop-filter:blur(14px) saturate(140%);-webkit-backdrop-filter:blur(14px) saturate(140%);} 
        .field-label{font-size:11px;letter-spacing:.05em;font-weight:600;text-transform:uppercase;color:#64748b;} 
    </style>
    
</head>
<body class="font-sans antialiased bg-gradient-to-br from-primary-50 via-white to-primary-100 min-h-screen text-gray-800 relative overflow-x-hidden">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-32 -left-24 w-96 h-96 bg-primary-200 opacity-30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -right-24 w-[30rem] h-[30rem] bg-primary-300 opacity-20 rounded-full blur-3xl"></div>
    </div>
    <?php include '../includes/barangay_official_cap_nav.php'; ?>
    <?php include 'sidebar_lupon.php'; ?>
    <?php $status=strtoupper(trim($complaint['Status'])); $statusStyles=['PENDING'=>'bg-amber-50 text-amber-600 border border-amber-200','IN CASE'=>'bg-sky-50 text-sky-600 border border-sky-200','REJECTED'=>'bg-rose-50 text-rose-600 border border-rose-200','RESOLVED'=>'bg-emerald-50 text-emerald-600 border border-emerald-200']; $statusClass=$statusStyles[$status]??'bg-gray-100 text-gray-600 border border-gray-200'; ?>
    <main class="relative z-10 max-w-5xl mx-auto px-4 md:px-8 pt-10 pb-24 animate-fade-in">
        <div class="mb-8 flex items-center gap-3">
            <a href="view_complaints.php" class="group inline-flex items-center text-sm font-medium text-primary-700 hover:text-primary-900 transition">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-white/70 shadow ring-1 ring-primary-100 group-hover:bg-primary-50"><i class="fa fa-arrow-left"></i></span>
                <span class="ml-2">Back to Complaint Lists</span>
            </a>
        </div>
        <section class="relative glass shadow-glow rounded-2xl p-6 md:p-10 border border-white/60 ring-1 ring-primary-100/40 overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-gradient-to-br from-primary-200/60 to-primary-400/40 blur-2xl opacity-40"></div>
            </div>
            <header class="relative flex flex-col md:flex-row md:items-start gap-6 mb-8">
                <div class="flex items-center">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center bg-primary-50 ring-4 ring-primary-100 shadow-inner">
                        <i class="fa fa-file-lines text-3xl text-primary-600"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-gray-800 flex flex-wrap items-center gap-3">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-700 to-primary-500">Complaint #<?= htmlspecialchars($complaint['Complaint_ID']) ?></span>
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full <?= $statusClass ?> shadow-sm"><i class="fa fa-circle text-[8px]"></i> <?= htmlspecialchars($complaint['Status']) ?></span>
                    </h1>
                    <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span class="inline-flex items-center gap-1"><i class="fa fa-user"></i> <?= htmlspecialchars($complainant_name) ?></span>
                        <span class="inline-flex items-center gap-1"><i class="fa fa-calendar"></i> <?= date('F d, Y', strtotime($complaint['Date_Filed'])) ?></span>
                    </div>
                </div>
            </header>
            <div class="space-y-10">
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Respondents</h2>
                    <div class="rounded-xl border bg-white/70 border-gray-200 p-4 shadow-sm">
                        <p class="text-gray-700 leading-relaxed"><?= htmlspecialchars($respondent_names) ?></p>
                    </div>
                </div>
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Complaint Information</h2>
                    <div class="grid gap-5 md:grid-cols-2">
                       
                        <div class="group rounded-xl border bg-white/70 border-gray-200 p-4 shadow-sm md:col-span-2">
                            <p class="field-label mb-1">Details</p>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line"><?= nl2br(htmlspecialchars($complaint['Complaint_Details'])) ?></p>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 p-4 shadow-sm">
                            <p class="field-label mb-1">Date Filed</p>
                            <p class="font-semibold text-gray-800"><?= date('F d, Y', strtotime($complaint['Date_Filed'])) ?></p>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 p-4 shadow-sm">
                            <p class="field-label mb-1">Status</p>
                            <p class="inline-flex items-center gap-2 font-semibold"><i class="fa fa-circle text-[8px]"></i> <?= htmlspecialchars($complaint['Status']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="pt-2 flex">
                    <a href="view_complaints.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/80 hover:bg-white text-primary-700 border border-primary-200 shadow-sm text-sm font-medium transition"><i class="fa fa-arrow-left"></i> Back to Complaint Lists</a>
                </div>
            </div>
        </section>
    </main>
    <?php $conn->close(); ?>
</body>
</html>
