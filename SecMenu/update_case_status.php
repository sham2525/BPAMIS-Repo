<?php
// Secretary Update Case Status - Premium UI (clean file)
session_start();
date_default_timezone_set('Asia/Manila');
if (!isset($_GET['id'])) {
    header('Location: view_cases.php');
    exit;
}
$caseId = intval($_GET['id']);
$conn = new mysqli('localhost', 'root', '', 'barangay_case_management');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch core case info
$sql = "SELECT cs.Case_ID, cs.Case_Status, cs.Date_Opened, ci.Complaint_ID, ci.Complaint_Title, ci.Date_Filed,
                             comp.First_Name AS Complainant_First, comp.Last_Name AS Complainant_Last,
                             resp.First_Name AS Respondent_First, resp.Last_Name AS Respondent_Last
                FROM CASE_INFO cs
                LEFT JOIN COMPLAINT_INFO ci ON cs.Complaint_ID = ci.Complaint_ID
                LEFT JOIN RESIDENT_INFO comp ON ci.Resident_ID = comp.Resident_ID
                LEFT JOIN RESIDENT_INFO resp ON ci.Respondent_ID = resp.Resident_ID
                WHERE cs.Case_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $caseId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<p class='text-center text-red-600'>Case not found.</p>";
    exit;
}
$case = $result->fetch_assoc();
$stmt->close();

$current = $case['Case_Status'];
$transitions = ['Open' => ['Mediation', 'Resolved', 'Closed'], 'Mediation' => ['Resolution', 'Resolved', 'Closed'], 'Resolution' => ['Settlement', 'Resolved', 'Closed'], 'Settlement' => ['Resolved', 'Closed'], 'Resolved' => ['Closed'], 'Closed' => []];
$available = $transitions[$current] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $available) {
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, $available, true)) {
        $upd = $conn->prepare('UPDATE CASE_INFO SET Case_Status=? WHERE Case_ID=?');
        $upd->bind_param('si', $newStatus, $caseId);
        $upd->execute();
        $upd->close();
        $notifSql = "SELECT co.Resident_ID, co.External_Complainant_ID FROM case_info cs JOIN complaint_info co ON cs.Complaint_ID=co.Complaint_ID WHERE cs.Case_ID=?";
        $ns = $conn->prepare($notifSql);
        $ns->bind_param('i', $caseId);
        $ns->execute();
        $nres = $ns->get_result();
        if ($nres && $row = $nres->fetch_assoc()) {
            $resident_id = $row['Resident_ID'];
            $external_id = $row['External_Complainant_ID'];
            $title = 'Case Status Updated';
            $message = "The status of your case (ID: $caseId) has been updated to \"$newStatus\".";
            $created = date('Y-m-d H:i:s');
            if (!empty($resident_id))
                $conn->query("INSERT INTO notifications (resident_id,title,message,is_read,created_at) VALUES ($resident_id,'$title','$message',0,'$created')");
            if (!empty($external_id))
                $conn->query("INSERT INTO notifications (external_complaint_id,title,message,is_read,created_at) VALUES ($external_id,'$title','$message',0,'$created')");
        }
        $ns->close();
        $today = date('Y-m-d');
        $deadline = date('Y-m-d', strtotime('+15 days'));
        if ($newStatus === 'Mediation') {
            $check = $conn->query("SELECT 1 FROM mediation_info WHERE Case_ID=$caseId LIMIT 1");
            if ($check->num_rows === 0) {
                $conn->query("INSERT INTO mediation_info (Case_ID,Mediation_Date,Deadline) VALUES ($caseId,'$today','$deadline')");
                $case_deadline = date('Y-m-d', strtotime('+45 days'));
                $deadline_overdue = date('Y-m-d', strtotime('+60 days'));
                $conn->query("UPDATE case_info SET case_deadline='$case_deadline', deadline_overdue='$deadline_overdue' WHERE Case_ID=$caseId");
            }
        } elseif ($newStatus === 'Resolution') {
            $check = $conn->query("SELECT 1 FROM resolution WHERE Case_ID=$caseId LIMIT 1");
            if ($check->num_rows === 0) {
                $conn->query("INSERT INTO resolution (Case_ID,Resolution_Date,Deadline) VALUES ($caseId,'$today','$deadline')");
            }
        } elseif ($newStatus === 'Settlement') {
            $check = $conn->query("SELECT 1 FROM settlement WHERE Case_ID=$caseId LIMIT 1");
            if ($check->num_rows === 0) {
                $conn->query("INSERT INTO settlement (Case_ID,Date_Agreed,Deadline) VALUES ($caseId,'$today','$deadline')");
            }
        }
        header('Location: view_cases.php?status_updated=1');
        exit;
    }
}

$statusUpper = strtoupper($current);
$statusStyles = ['OPEN' => 'bg-sky-50 text-sky-600 border border-sky-200', 'MEDIATION' => 'bg-amber-50 text-amber-600 border border-amber-200', 'RESOLUTION' => 'bg-indigo-50 text-indigo-600 border border-indigo-200', 'SETTLEMENT' => 'bg-fuchsia-50 text-fuchsia-600 border border-fuchsia-200', 'RESOLVED' => 'bg-emerald-50 text-emerald-600 border border-emerald-200', 'CLOSED' => 'bg-gray-100 text-gray-700 border border-gray-300'];
$statusClass = $statusStyles[$statusUpper] ?? 'bg-primary-50 text-primary-600 border border-primary-200';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Case • Update Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: { 50: '#f0f7ff', 100: '#e0effe', 200: '#bae2fd', 300: '#7cccfd', 400: '#36b3f9', 500: '#0c9ced', 600: '#0281d4', 700: '#026aad', 800: '#065a8f', 900: '#0a4b76' } }, boxShadow: { glow: '0 0 0 1px rgba(12,156,237,.08),0 4px 20px -2px rgba(6,90,143,.18)' }, animation: { 'fade-in': 'fadeIn .4s ease-out' }, keyframes: { fadeIn: { '0%': { opacity: 0 }, '100%': { opacity: 1 } } } } } };</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        .glass {
            background: linear-gradient(140deg, rgba(255, 255, 255, .92), rgba(255, 255, 255, .68));
            backdrop-filter: blur(14px) saturate(140%);
            -webkit-backdrop-filter: blur(14px) saturate(140%);
        }

        .field-label {
            font-size: 11px;
            letter-spacing: .05em;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
        }
    </style>
</head>

<body
    class="font-sans antialiased bg-gradient-to-br from-primary-50 via-white to-primary-100 min-h-screen text-gray-800 relative overflow-x-hidden">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-32 -left-24 w-96 h-96 bg-primary-200 opacity-30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -right-24 w-[30rem] h-[30rem] bg-primary-300 opacity-20 rounded-full blur-3xl">
        </div>
    </div>
    <?php include '../includes/barangay_official_sec_nav.php'; ?>
    <?php include 'sidebar_.php'; ?>
    <main class="relative z-10 max-w-4xl mx-auto px-4 md:px-8 pt-10 pb-24 animate-fade-in">
        <div class="mb-8 flex items-center gap-3"><a href="view_cases.php"
                class="group inline-flex items-center text-sm font-medium text-primary-700 hover:text-primary-900 transition"><span
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-white/70 shadow ring-1 ring-primary-100 group-hover:bg-primary-50"><i
                        class="fa fa-arrow-left"></i></span><span class="ml-2">Back to Cases</span></a></div>
        <section
            class="relative glass shadow-glow rounded-2xl p-6 md:p-10 border border-white/60 ring-1 ring-primary-100/40 overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div
                    class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-gradient-to-br from-primary-200/60 to-primary-400/40 blur-2xl opacity-40">
                </div>
            </div>
            <header class="relative flex flex-col md:flex-row md:items-start gap-6 mb-8">
                <div class="flex items-center">
                    <div
                        class="w-20 h-20 rounded-2xl flex items-center justify-center bg-primary-50 ring-4 ring-primary-100 shadow-inner">
                        <i class="fa fa-scale-balanced text-3xl text-primary-600"></i></div>
                </div>
                <div class="flex-1 min-w-0">
                    <h1
                        class="text-2xl md:text-3xl font-semibold tracking-tight text-gray-800 flex flex-wrap items-center gap-3">
                        <span
                            class="bg-clip-text text-transparent bg-gradient-to-r from-primary-700 to-primary-500">Case
                            #<?= htmlspecialchars($case['Case_ID']) ?></span><span
                            class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full <?= $statusClass ?> shadow-sm"><i
                                class="fa fa-circle text-[8px]"></i>
                            <?= htmlspecialchars($case['Case_Status']) ?></span></h1>
                    <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500"><span
                            class="inline-flex items-center gap-1"><i class="fa fa-calendar"></i> Opened
                            <?= date('F d, Y', strtotime($case['Date_Opened'])) ?></span><span
                            class="inline-flex items-center gap-1"><i class="fa fa-file-lines"></i> Filed
                            <?= date('F d, Y', strtotime($case['Date_Filed'])) ?></span></div>
                </div>
            </header>
            <div class="space-y-10">
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Case Context</h2>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div
                            class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="field-label mb-1">Complainant</p>
                            <p class="font-semibold text-gray-800">
                                <?= htmlspecialchars($case['Complainant_First'] . ' ' . $case['Complainant_Last']) ?></p>
                        </div>
                        <div
                            class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="field-label mb-1">Respondent</p>
                            <p class="text-gray-700">
                                <?= htmlspecialchars(trim($case['Respondent_First'] . ' ' . $case['Respondent_Last'])) ?>
                            </p>
                        </div>
                        <div
                            class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm md:col-span-2">
                            <p class="field-label mb-1">Complaint Title</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($case['Complaint_Title']) ?></p>
                        </div>
                    </div>
                </div>
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Update Status</h2>
                    <form method="POST" class="space-y-5">
                        <?php if ($available): ?>
                            <div>
                                <label class="field-label mb-2 block">Select New Status</label>
                                <select name="status"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm font-medium">
                                    <?php foreach ($available as $s): ?>
                                        <option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <div class="rounded-xl border border-gray-200 bg-white/70 p-4 text-sm text-red-600 font-medium">
                                This case is closed. No further updates permitted.</div>
                        <?php endif; ?>
                        <div class="flex flex-wrap gap-3 pt-2">
                            <a href="view_cases.php"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/80 hover:bg-white text-primary-700 border border-primary-200 shadow-sm text-sm font-medium transition"><i
                                    class="fa fa-arrow-left"></i> Cancel</a>
                            <button type="submit" <?= !$available ? 'disabled' : '' ?>
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white shadow text-sm font-medium transition disabled:opacity-60 disabled:cursor-not-allowed"><i
                                    class="fa fa-save"></i> Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <?php $conn->close(); ?>
</body>

</html>

</div>
</div>
</section>
</main>

</body>

</html>

</html>