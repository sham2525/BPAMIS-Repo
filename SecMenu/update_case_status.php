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

// Detect optional lupon_assign column
$hasLuponAssign = false;
$__col_l = $conn->query("SHOW COLUMNS FROM CASE_INFO LIKE 'lupon_assign'");
if ($__col_l && $__col_l->num_rows > 0) { $hasLuponAssign = true; }

// Detect optional COMPLAINT_INFO.case_type column
$hasComplaintCaseType = false;
$__col_ct = $conn->query("SHOW COLUMNS FROM COMPLAINT_INFO LIKE 'case_type'");
if ($__col_ct && $__col_ct->num_rows > 0) { $hasComplaintCaseType = true; }

// Fetch core case info (guard prepare and provide fallback)
$sql =
    "SELECT cs.Case_ID, cs.Case_Status, cs.Date_Opened, " .
    ($hasLuponAssign ? "cs.lupon_assign" : "NULL AS lupon_assign") . ",\n" .
    "        ci.Complaint_ID, ci.Complaint_Title, ci.Complaint_Details, ci.Date_Filed, ci.case_type,\n" .
    "        comp.First_Name AS Complainant_First, comp.Last_Name AS Complainant_Last,\n" .
    "        resp.First_Name AS Respondent_First, resp.Last_Name AS Respondent_Last\n" .
    "    FROM CASE_INFO cs\n" .
    "    LEFT JOIN COMPLAINT_INFO ci ON cs.Complaint_ID = ci.Complaint_ID\n" .
    "    LEFT JOIN RESIDENT_INFO comp ON ci.Resident_ID = comp.Resident_ID\n" .
    "    LEFT JOIN RESIDENT_INFO resp ON ci.Respondent_ID = resp.Resident_ID\n" .
    "    WHERE cs.Case_ID = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $caseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Fallback: inline ID into query as integer to avoid prepare fatal; log error
    error_log('update_case_status.php prepare failed: ' . $conn->error);
    $fallbackSql = str_replace('WHERE cs.Case_ID = ?', 'WHERE cs.Case_ID = ' . $caseId, $sql);
    $result = $conn->query($fallbackSql);
}

if (!$result || $result->num_rows === 0) {
    echo "<p class='text-center text-red-600'>Case not found or query error.</p>";
    if (!$result) {
        echo "<p class='text-center text-xs text-gray-500'>" . htmlspecialchars($conn->error) . "</p>";
    }
    exit;
}
$case = $result->fetch_assoc();
// Capture complaint id for updates
$complaintId = isset($case['Complaint_ID']) ? intval($case['Complaint_ID']) : 0;

// Fetch Lupon Tagapamayapa names for suggestions
$luponNames = [];
$luponQ = $conn->query("SELECT name FROM barangay_officials WHERE LOWER(TRIM(Position)) = 'lupon tagapamayapa' ORDER BY name ASC");
if ($luponQ && $luponQ->num_rows > 0) {
    while ($r = $luponQ->fetch_assoc()) { $luponNames[] = trim($r['name']); }
}

// Resolve case type display from COMPLAINT_INFO.case_type if present, else fallback to CASE_INFO.Case_Type
$caseType = '';
if (isset($case['case_type'])) {
    $caseType = trim((string)($case['case_type'] ?? ''));
}
if ($caseType === '') {
    $__col = $conn->query("SHOW COLUMNS FROM CASE_INFO LIKE 'Case_Type'");
    if ($__col && $__col->num_rows > 0) {
        if ($__st = $conn->prepare('SELECT Case_Type FROM CASE_INFO WHERE Case_ID = ?')) {
            $__st->bind_param('i', $caseId);
            $__st->execute();
            $__rs = $__st->get_result();
            if ($__rs && $__rs->num_rows > 0) {
                $row = $__rs->fetch_assoc();
                $caseType = trim((string)($row['Case_Type'] ?? ''));
            }
            $__st->close();
        } else {
            $tmp = $conn->query("SELECT Case_Type FROM CASE_INFO WHERE Case_ID = $caseId");
            if ($tmp && $tmp->num_rows > 0) {
                $row = $tmp->fetch_assoc();
                $caseType = trim((string)($row['Case_Type'] ?? ''));
            }
        }
    }
}

$current = $case['Case_Status'];
$transitions = ['Open' => ['Mediation', 'Resolved', 'Closed'], 'Mediation' => ['Resolution', 'Resolved', 'Closed'], 'Resolution' => ['Settlement', 'Resolved', 'Closed'], 'Settlement' => ['Resolved', 'Closed'], 'Resolved' => ['Closed'], 'Closed' => []];
$available = $transitions[$current] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    // Allow assigning Lupon (mediator) even if status remains the same
    $luponAssign = isset($_POST['lupon_assign']) ? trim($_POST['lupon_assign']) : '';
    $luponUpdated = false;
    $caseTypeUpdated = false;

    // Optional: update complaint case_type when provided
    $postedType = isset($_POST['complaint_case_type']) ? strtolower(trim($_POST['complaint_case_type'])) : '';
    if ($postedType !== '' && $hasComplaintCaseType && $complaintId > 0) {
        // Normalize to canonical values
        if ($postedType === 'civil' || $postedType === 'civil case') { $postedType = 'civil case'; }
        elseif ($postedType === 'criminal' || $postedType === 'criminal case') { $postedType = 'criminal case'; }
        elseif ($postedType === 'blotter') { $postedType = 'blotter'; }
        else { $postedType = ''; }

        if ($postedType !== '') {
            if ($updCt = $conn->prepare('UPDATE COMPLAINT_INFO SET case_type = ? WHERE Complaint_ID = ?')) {
                $updCt->bind_param('si', $postedType, $complaintId);
                $updCt->execute();
                $updCt->close();
                $caseTypeUpdated = true;
            } else {
                $conn->query("UPDATE COMPLAINT_INFO SET case_type='" . $conn->real_escape_string($postedType) . "' WHERE Complaint_ID=" . $complaintId);
                $caseTypeUpdated = true;
            }
        }
    }
    if ($luponAssign !== '' && $hasLuponAssign) {
        if ($m = $conn->prepare('UPDATE CASE_INFO SET lupon_assign = ? WHERE Case_ID = ?')) {
            $m->bind_param('si', $luponAssign, $caseId);
            $m->execute();
            $m->close();
            $luponUpdated = true;
        } else {
            $conn->query("UPDATE CASE_INFO SET lupon_assign = '" . $conn->real_escape_string($luponAssign) . "' WHERE Case_ID = $caseId");
            $luponUpdated = true;
        }
        // Send notifications to each assigned Lupon (match by Name -> Official_ID) using value from DB
        $assignStr = '';
        if ($gs = $conn->prepare('SELECT lupon_assign FROM CASE_INFO WHERE Case_ID = ?')) {
            $gs->bind_param('i', $caseId);
            $gs->execute();
            $gRes = $gs->get_result();
            if ($gRes && $gRes->num_rows > 0) {
                $gr = $gRes->fetch_assoc();
                $assignStr = (string)($gr['lupon_assign'] ?? '');
            }
            $gs->close();
        } else {
            $gq = $conn->query("SELECT lupon_assign FROM CASE_INFO WHERE Case_ID = $caseId");
            if ($gq && $gq->num_rows > 0) {
                $gr = $gq->fetch_assoc();
                $assignStr = (string)($gr['lupon_assign'] ?? '');
            }
        }

        $names = array_values(array_filter(array_map('trim', explode(',', $assignStr))));
        if (!empty($names)) {
            // Prepare reusable statements
            $findStmt = $conn->prepare("SELECT Official_ID FROM barangay_officials WHERE Position = 'Lupon Tagapamayapa' AND Name = ? LIMIT 1");
            $checkStmt = $conn->prepare("SELECT 1 FROM notifications WHERE lupon_id = ? AND type = 'Case' AND message LIKE ? LIMIT 1");
            $notifStmt = $conn->prepare("INSERT INTO notifications (title, message, type, created_at, lupon_id, is_read) VALUES (?, ?, ?, NOW(), ?, 0)");
            if ($findStmt && $notifStmt) {
                foreach ($names as $nm) {
                    if ($nm === '') continue;
                    $findStmt->bind_param('s', $nm);
                    $findStmt->execute();
                    $res = $findStmt->get_result();
                    if ($res && $res->num_rows > 0) {
                        $off = $res->fetch_assoc();
                        $luponId = (int)$off['Official_ID'];
                        $title = 'New Case Assigned';
                        $message = "You have been assigned to Case #$caseId.";
                        $type = 'Case';
                        // Dedupe: skip if a similar notification for this lupon and case already exists
                        if ($checkStmt) {
                            $like = "%Case #$caseId%";
                            $checkStmt->bind_param('is', $luponId, $like);
                            $checkStmt->execute();
                            $cr = $checkStmt->get_result();
                            $exists = ($cr && $cr->num_rows > 0);
                        } else {
                            $exists = false; // best effort
                        }
                        if (!$exists) {
                            $notifStmt->bind_param('sssi', $title, $message, $type, $luponId);
                            $notifStmt->execute();
                        }
                    }
                }
                $findStmt->close();
                if ($checkStmt) { $checkStmt->close(); }
                $notifStmt->close();
            }
        }
    }
    if (in_array($newStatus, $available, true)) {
        $upd = $conn->prepare('UPDATE CASE_INFO SET Case_Status=? WHERE Case_ID=?');
        if ($upd) {
            $upd->bind_param('si', $newStatus, $caseId);
            $upd->execute();
            $upd->close();
        } else {
            $conn->query("UPDATE CASE_INFO SET Case_Status='" . $conn->real_escape_string($newStatus) . "' WHERE Case_ID=$caseId");
        }
        $notifSql = "SELECT co.Resident_ID, co.External_Complainant_ID FROM case_info cs JOIN complaint_info co ON cs.Complaint_ID=co.Complaint_ID WHERE cs.Case_ID=?";
        $ns = $conn->prepare($notifSql);
        if ($ns) {
            $ns->bind_param('i', $caseId);
            $ns->execute();
            $nres = $ns->get_result();
        } else {
            $nres = $conn->query(str_replace('WHERE cs.Case_ID=?', 'WHERE cs.Case_ID=' . $caseId, $notifSql));
        }
        if ($nres && $row = $nres->fetch_assoc()) {
            $resident_id = $row['Resident_ID'];
            $external_id = $row['External_Complainant_ID'];
            $title = 'Case Status Updated';
            $message = "The status of your case (ID: $caseId) has been updated to \"$newStatus\".";
            $created = date('Y-m-d H:i:s');
            if (!empty($resident_id)) {
                $conn->query("INSERT INTO notifications (resident_id,title,message,is_read,created_at) VALUES (" . intval($resident_id) . ", '" . $conn->real_escape_string($title) . "', '" . $conn->real_escape_string($message) . "', 0, '" . $conn->real_escape_string($created) . "')");
            }
            if (!empty($external_id)) {
                $conn->query("INSERT INTO notifications (external_complaint_id,title,message,is_read,created_at) VALUES (" . intval($external_id) . ", '" . $conn->real_escape_string($title) . "', '" . $conn->real_escape_string($message) . "', 0, '" . $conn->real_escape_string($created) . "')");
            }
        }
        if ($ns) { $ns->close(); }
        $today = date('Y-m-d');
        $deadline = date('Y-m-d', strtotime('+15 days'));
        if ($newStatus === 'Mediation') {
            $check = $conn->query("SELECT 1 FROM mediation_info WHERE Case_ID=$caseId LIMIT 1");
            if ($check && $check->num_rows === 0) {
                $conn->query("INSERT INTO mediation_info (Case_ID,Mediation_Date,Deadline) VALUES ($caseId,'$today','$deadline')");
                $case_deadline = date('Y-m-d', strtotime('+45 days'));
                $deadline_overdue = date('Y-m-d', strtotime('+60 days'));
                $conn->query("UPDATE case_info SET case_deadline='$case_deadline', deadline_overdue='$deadline_overdue' WHERE Case_ID=$caseId");
            }
        } elseif ($newStatus === 'Resolution') {
            $check = $conn->query("SELECT 1 FROM resolution WHERE Case_ID=$caseId LIMIT 1");
            if ($check && $check->num_rows === 0) {
                $conn->query("INSERT INTO resolution (Case_ID,Resolution_Date,Deadline) VALUES ($caseId,'$today','$deadline')");
            }
        } elseif ($newStatus === 'Settlement') {
            $check = $conn->query("SELECT 1 FROM settlement WHERE Case_ID=$caseId LIMIT 1");
            if ($check && $check->num_rows === 0) {
                $conn->query("INSERT INTO settlement (Case_ID,Date_Agreed,Deadline) VALUES ($caseId,'$today','$deadline')");
            }
        }
        header('Location: view_cases.php?status_updated=1');
        exit;
    }
    // If only mediator and/or case type were updated (no status change), refresh to show latest value
    if ((!in_array($newStatus, $available, true)) && ($luponUpdated || $caseTypeUpdated)) {
        $flags = [];
        if ($luponUpdated) { $flags[] = 'lupon_updated=1'; }
        if ($caseTypeUpdated) { $flags[] = 'case_type_updated=1'; }
        $qs = implode('&', $flags);
        header('Location: update_case_status.php?id=' . $caseId . ($qs ? ('&' . $qs) : ''));
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
                            <p class="field-label mb-1">Complaint Description</p>
                            <div class="text-gray-800 whitespace-pre-line break-words">
                                <?= nl2br(htmlspecialchars($case['Complaint_Details'] ?? '')) ?>
                            </div>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="field-label mb-1">Case Type</p>
                            <?php 
                                $ct = $caseType; 
                                $ctLower = strtolower(trim($ct));
                                // Normalize values like 'civil', 'civil case', 'criminal case', 'blotter'
                                if ($ctLower === 'civil case') $ctLower = 'civil';
                                if ($ctLower === 'criminal case') $ctLower = 'criminal';
                                $badge = 'bg-gray-100 text-gray-700 border border-gray-200';
                                if ($ctLower==='civil') $badge='bg-sky-50 text-sky-700 border border-sky-200';
                                elseif ($ctLower==='criminal') $badge='bg-rose-50 text-rose-700 border border-rose-200';
                                elseif ($ctLower==='blotter') $badge='bg-slate-50 text-slate-700 border border-slate-200';
                            ?>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[12px] font-semibold <?= $badge ?>">
                                <i class="fa fa-tag"></i> <?= htmlspecialchars($ct !== '' ? ($ctLower==='civil' ? 'Civil Case' : ($ctLower==='criminal' ? 'Criminal Case' : ($ctLower==='blotter' ? 'Blotter' : ucfirst($ctLower)))) : 'Not set') ?>
                            </span>
                        </div>
                    </div>
                    
                </div>
                <?php if ($hasLuponAssign): ?>
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Assign Lupon Tagapamayapa</h2>
                    <form method="POST" class="space-y-3">
                        <div>
                            <label class="field-label mb-2 block">Lupon Name(s)</label>
                            <div id="luponChips" class="flex flex-wrap gap-2 mb-2"></div>
                            <input id="luponAssignHidden" type="hidden" name="lupon_assign" value="<?= htmlspecialchars($case['lupon_assign'] ?? '') ?>" />
                            <input id="luponInput" list="luponList" placeholder="Type a Lupon name and press Enter" class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm" />
                            <datalist id="luponList">
                                <?php foreach ($luponNames as $ln): ?>
                                    <option value="<?= htmlspecialchars($ln) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                            <p class="mt-1 text-xs text-gray-500">Tip: Add multiple Lupon names. Type a name and press Enter, or pick from suggestions; click × on a chip to remove.</p>
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white shadow text-sm font-medium transition"><i class="fa fa-user-plus"></i> Save Assignment</button>
                            <?php if (!empty($_GET['lupon_updated'])): ?>
                                <span class="ml-3 text-emerald-700 text-sm">Assignment saved.</span>
                            <?php endif; ?>
                        </div>
                    </form>
                    <script>
                        (function () {
                            try {
                                const chipsEl = document.getElementById('luponChips');
                                const hiddenEl = document.getElementById('luponAssignHidden');
                                const inputEl = document.getElementById('luponInput');
                                if (!chipsEl || !hiddenEl || !inputEl) return;

                                const initial = (function() {
                                    try {
                                        return <?php 
                                            $initialLupon = array_values(array_filter(array_map('trim', explode(',', $case['lupon_assign'] ?? ''))));
                                            echo json_encode($initialLupon, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
                                        ?>;
                                    } catch (e) { return []; }
                                })();

                                let selected = Array.from(new Set(initial.filter(Boolean)));

                                function syncHidden() {
                                    hiddenEl.value = selected.join(', ');
                                }

                                function render() {
                                    chipsEl.innerHTML = '';
                                    selected.forEach((name, idx) => {
                                        const chip = document.createElement('span');
                                        chip.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 border border-primary-200 text-xs font-medium';
                                        const text = document.createElement('span');
                                        text.textContent = name;
                                        const btn = document.createElement('button');
                                        btn.type = 'button';
                                        btn.className = 'ml-1 inline-flex items-center justify-center h-5 w-5 rounded-full bg-primary-100 hover:bg-primary-200 text-primary-700';
                                        btn.setAttribute('aria-label', 'Remove ' + name);
                                        btn.innerHTML = '<i class="fa fa-times text-[10px]"></i>';
                                        btn.addEventListener('click', function () {
                                            selected = selected.filter(n => n !== name);
                                            render();
                                            syncHidden();
                                        });
                                        chip.appendChild(text);
                                        chip.appendChild(btn);
                                        chipsEl.appendChild(chip);
                                    });
                                }

                                function addName(raw) {
                                    if (!raw) return;
                                    // Support comma-separated input; split and add each
                                    const parts = String(raw).split(',').map(s => s.trim()).filter(Boolean);
                                    let changed = false;
                                    parts.forEach(p => {
                                        if (p && !selected.includes(p)) { selected.push(p); changed = true; }
                                    });
                                    if (changed) { render(); syncHidden(); }
                                }

                                inputEl.addEventListener('keydown', function (e) {
                                    if (e.key === 'Enter' || e.key === ',') {
                                        e.preventDefault();
                                        addName(inputEl.value.replace(/,+$/,'').trim());
                                        inputEl.value = '';
                                    }
                                });
                                inputEl.addEventListener('change', function () {
                                    if (inputEl.value && inputEl.value.trim() !== '') {
                                        addName(inputEl.value.trim());
                                        inputEl.value = '';
                                    }
                                });

                                // Initialize
                                render();
                                syncHidden();
                            } catch (err) {
                                console && console.warn && console.warn('Lupon chips init failed:', err);
                            }
                        })();
                    </script>
                </div>
                <?php endif; ?>
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Update Status</h2>
                    <form method="POST" class="space-y-5">
                        <?php if ($hasComplaintCaseType && trim($caseType) === ''): ?>
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="field-label mb-2 block">Case Type</label>
                                    <select name="complaint_case_type" class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm font-medium" required>
                                        <option value="" disabled selected>Select case type</option>
                                        <option value="civil">Civil</option>
                                        <option value="criminal">Criminal</option>
                                        <option value="blotter">Blotter</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Required: choose the case type to be saved to the complaint record.</p>
                                    <?php if (!empty($_GET['case_type_updated'])): ?>
                                        <p class="mt-1 text-xs text-emerald-700 font-medium">Case type saved.</p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($available): ?>
                                        <label class="field-label mb-2 block">Select New Status</label>
                                        <select name="status"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm font-medium">
                                            <?php foreach ($available as $s): ?>
                                                <option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <div class="rounded-xl border border-gray-200 bg-white/70 p-4 text-sm text-red-600 font-medium">This case is closed. No further updates permitted.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
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
                                <div class="rounded-xl border border-gray-200 bg-white/70 p-4 text-sm text-red-600 font-medium">This case is closed. No further updates permitted.</div>
                            <?php endif; ?>
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