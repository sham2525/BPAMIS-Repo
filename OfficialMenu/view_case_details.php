<?php
require_once(__DIR__ . '/../server/server.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id'])) {
    echo "<p class='text-center text-red-500'>Case ID not provided.</p>";
    exit;
}

$case_id = intval($_GET['id']);

// Schema guards
$hasDescCol = $conn->query("SHOW COLUMNS FROM COMPLAINT_INFO LIKE 'Complaint_Description'");
$descField = ($hasDescCol && $hasDescCol->num_rows > 0) ? 'Complaint_Description' : 'Complaint_Title';
$hasCaseTypeCol = $conn->query("SHOW COLUMNS FROM COMPLAINT_INFO LIKE 'case_type'");
$caseTypeSelect = ($hasCaseTypeCol && $hasCaseTypeCol->num_rows > 0) ? 'ci.case_type AS Case_Type,' : "NULL AS Case_Type,";

$sql = "SELECT 
            cs.Case_ID,
            cs.Case_Status,
            cs.Date_Opened,
            ci.Complaint_ID,
            ci.Complaint_Title,
            ci.$descField AS complaint_desc,
            ci.Complaint_Details,
            ci.Date_Filed,
            $caseTypeSelect
            COALESCE(comp.First_Name, ext_comp.First_Name) AS Complainant_First,
            COALESCE(comp.Last_Name, ext_comp.Last_Name) AS Complainant_Last,
            resp.First_Name AS Respondent_First,
            resp.Last_Name AS Respondent_Last
        FROM CASE_INFO cs
        LEFT JOIN COMPLAINT_INFO ci ON cs.Complaint_ID = ci.Complaint_ID
        LEFT JOIN RESIDENT_INFO comp ON ci.Resident_ID = comp.Resident_ID
        LEFT JOIN external_complainant ext_comp ON ci.External_Complainant_ID = ext_comp.External_Complaint_ID
        LEFT JOIN RESIDENT_INFO resp ON ci.Respondent_ID = resp.Resident_ID
        WHERE cs.Case_ID = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "<p class='text-center text-red-500'>Failed to prepare query.</p>";
    exit;
}
$stmt->bind_param("i", $case_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-center text-red-500'>Case not found.</p>";
    exit;
}

$case = $result->fetch_assoc();
$stmt->close();

// Build respondents list (main + additional)
$respondent_names = [];
if (!empty($case['Respondent_First']) || !empty($case['Respondent_Last'])) {
    $respondent_names[] = trim(($case['Respondent_First'] ?? '') . ' ' . ($case['Respondent_Last'] ?? ''));
}
$complaint_id = (int)($case['Complaint_ID'] ?? 0);
if ($complaint_id > 0) {
    $addResSql = "SELECT ri.First_Name, ri.Last_Name FROM COMPLAINT_RESPONDENTS cr JOIN RESIDENT_INFO ri ON cr.Respondent_ID = ri.Resident_ID WHERE cr.Complaint_ID = ?";
    if ($rs = $conn->prepare($addResSql)) {
        $rs->bind_param('i', $complaint_id);
        $rs->execute();
        $res = $rs->get_result();
        while ($r = $res->fetch_assoc()) {
            $respondent_names[] = trim(($r['First_Name'] ?? '') . ' ' . ($r['Last_Name'] ?? ''));
        }
        $rs->close();
    }
}
$respondents_display = !empty($respondent_names) ? implode(', ', array_filter($respondent_names)) : 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {50:'#f0f7ff',100:'#e0effe',200:'#bae2fd',300:'#7cccfd',400:'#36b3f9',500:'#0c9ced',600:'#0281d4',700:'#026aad',800:'#065a8f',900:'#0a4b76'}
                    },
                    boxShadow: { glow:'0 0 0 1px rgba(12,156,237,.08),0 4px 20px -2px rgba(6,90,143,.18)' },
                    animation: { 'fade-in':'fadeIn .4s ease-out', 'float':'float 6s ease-in-out infinite' },
                    keyframes: {
                        fadeIn: { '0%':{opacity:0}, '100%':{opacity:1} },
                        float: { '0%,100%':{ transform:'translateY(0)' }, '50%':{ transform:'translateY(-8px)' } }
                    }
                }
            }
        }
    </script>
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

    <?php 
        $status = strtoupper(trim($case['Case_Status'] ?? ''));
        $statusStyles = [
            'OPEN' => 'bg-sky-50 text-sky-600 border border-sky-200',
            'PENDING HEARING' => 'bg-amber-50 text-amber-600 border border-amber-200',
            'MEDIATION' => 'bg-indigo-50 text-indigo-600 border border-indigo-200',
            'RESOLUTION' => 'bg-purple-50 text-purple-600 border border-purple-200',
            'SETTLEMENT' => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
            'RESOLVED' => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
            'CLOSED' => 'bg-gray-100 text-gray-600 border border-gray-200',
        ];
        $statusClass = $statusStyles[$status] ?? 'bg-gray-100 text-gray-600 border border-gray-200';
    ?>
    <main class="relative z-10 max-w-5xl mx-auto px-4 md:px-8 pt-10 pb-24 animate-fade-in">
        <div class="mb-8 flex items-center gap-3">
            <a href="view_cases.php" class="group inline-flex items-center text-sm font-medium text-primary-700 hover:text-primary-900 transition">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-white/70 shadow ring-1 ring-primary-100 group-hover:bg-primary-50"><i class="fa fa-arrow-left"></i></span>
                <span class="ml-2">Back to Case Lists</span>
            </a>
        </div>
        <section class="relative glass shadow-glow rounded-2xl p-6 md:p-10 border border-white/60 ring-1 ring-primary-100/40 overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-gradient-to-br from-primary-200/60 to-primary-400/40 blur-2xl opacity-40"></div>
            </div>
            <header class="relative flex flex-col md:flex-row md:items-start gap-6 mb-8">
                <div class="flex items-center">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center bg-primary-50 ring-4 ring-primary-100 shadow-inner">
                        <i class="fa fa-scale-balanced text-3xl text-primary-600"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-gray-800 flex flex-wrap items-center gap-3">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-700 to-primary-500">Case #<?= htmlspecialchars($case['Case_ID']) ?></span>
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full <?= $statusClass ?> shadow-sm"><i class="fa fa-circle text-[8px]"></i> <?= htmlspecialchars($case['Case_Status']) ?></span>
                        <?php if (!empty($case['Case_Type'])): ?>
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full bg-white/80 border border-primary-200 text-primary-700 shadow-sm"><i class="fa fa-briefcase"></i> <?= htmlspecialchars(ucwords(trim($case['Case_Type']))) ?></span>
                        <?php endif; ?>
                    </h1>
                    <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span class="inline-flex items-center gap-1"><i class="fa fa-user"></i> <?= htmlspecialchars(trim(($case['Complainant_First'] ?? '') . ' ' . ($case['Complainant_Last'] ?? ''))) ?></span>
                        <span class="inline-flex items-center gap-1"><i class="fa fa-calendar"></i> <?= date('F d, Y', strtotime($case['Date_Filed'])) ?></span>
                        <?php if (!empty($case['Date_Opened'])): ?>
                        <span class="inline-flex items-center gap-1"><i class="fa fa-folder-open"></i> Opened: <?= date('F d, Y', strtotime($case['Date_Opened'])) ?></span>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </header>
            <div class="space-y-10">
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Respondents</h2>
                    <div class="rounded-xl border bg-white/70 border-gray-200 p-4 shadow-sm">
                        <p class="text-gray-700 leading-relaxed"><?= htmlspecialchars($respondents_display) ?></p>
                    </div>
                </div>
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Case Information</h2>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="group rounded-xl border bg-white/70 border-gray-200 p-4 shadow-sm md:col-span-2">
                            <p class="field-label mb-1">Complaint Details</p>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line"><?= nl2br(htmlspecialchars($case['Complaint_Details'] ?? '')) ?></p>
                        </div>
                       
                        <div class="group rounded-xl border bg-white/70 border-gray-200 p-4 shadow-sm">
                            <p class="field-label mb-1">Date Filed</p>
                            <p class="font-semibold text-gray-800"><?= date('F d, Y', strtotime($case['Date_Filed'])) ?></p>
                        </div>
                    </div>
                </div>
                <div class="pt-2 flex">
                    <a href="view_cases.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/80 hover:bg-white text-primary-700 border border-primary-200 shadow-sm text-sm font-medium transition"><i class="fa fa-arrow-left"></i> Back to Case Lists</a>
                </div>
            </div>
        </section>
    </main>
    <?php include 'sidebar_.php'; ?>
 </body>
 </html>
