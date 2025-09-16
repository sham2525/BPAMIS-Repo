<?php
session_start();
include '../server/server.php';

// Redirect if not logged in
if (!isset($_SESSION['official_id'])) {
    header("Location: ../login.php");
    exit();
}

$official_id = $_SESSION['official_id'];
$official_name = $_SESSION['official_name'] ?? 'Unknown';

$success = '';
$error = '';
$cases = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $case_id = $_POST['case_id'] ?? '';
    $message = trim($_POST['message'] ?? '');

    if (!empty($case_id) && !empty($message)) {
        $stmt = $conn->prepare("
            INSERT INTO feedback (official_id, official_name, case_id, message, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isis", $official_id, $official_name, $case_id, $message);

        if ($stmt->execute()) {
            $success = "Feedback successfully submitted.";
        } else {
            $error = "Error inserting feedback: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Please select a case and enter your feedback.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Write Feedback</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme:{ extend:{ colors:{ primary:{50:'#f0f7ff',100:'#e0effe',200:'#bae2fd',300:'#7cccfd',400:'#36b3f9',500:'#0c9ced',600:'#0281d4',700:'#026aad',800:'#065a8f',900:'#0a4b76'}}, boxShadow:{glow:'0 0 0 1px rgba(12,156,237,0.10), 0 4px 18px -2px rgba(6,90,143,0.20)'}, keyframes:{fadeIn:{'0%':{opacity:0,transform:'translateY(4px)'},'100%':{opacity:1,transform:'translateY(0)'}},pulseSoft:{'0%,100%':{opacity:1},'50%':{opacity:.55}}}, animation:{'fade-in':'fadeIn .5s ease-out','pulse-soft':'pulseSoft 3s ease-in-out infinite'} } } };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .bg-orbs:before, .bg-orbs:after { content:""; position:absolute; border-radius:9999px; filter:blur(70px); opacity:.35; }
        .bg-orbs:before { width:480px; height:480px; background:linear-gradient(135deg,#7cccfd,#0c9ced); top:-160px; left:-140px; }
        .bg-orbs:after { width:420px; height:420px; background:linear-gradient(135deg,#bae2fd,#7cccfd); bottom:-140px; right:-120px; }
        .glass { background:linear-gradient(145deg,rgba(255,255,255,.88),rgba(255,255,255,.65)); backdrop-filter:blur(14px) saturate(140%); -webkit-backdrop-filter:blur(14px) saturate(140%); }
        .input-base { width:100%; border-radius:0.5rem; border:1px solid rgba(209,213,219,.7); background:rgba(255,255,255,.7); padding:.625rem .75rem; font-size:.875rem; transition:.2s; }
        .input-base:not(textarea){ height:44px; line-height:1.2; }
        .input-base:focus { outline:none; background:#fff; border-color:#36b3f9; box-shadow:0 0 0 4px rgba(12,156,237,.25); }
        .field-label { font-size:11px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; margin-bottom:4px; display:flex; gap:4px; align-items:center; color:#4b5563; }
    </style>
</head>
<body class="min-h-screen font-sans bg-gradient-to-br from-primary-50 via-white to-primary-100 text-gray-800 relative overflow-x-hidden bg-orbs">
    <?php include '../includes/barangay_official_cap_nav.php'; ?>

    <!-- Page Heading (mirrors secretary add complaints) -->
    <header class="relative max-w-6xl mx-auto px-4 md:px-8 pt-8 animate-fade-in">
        <div class="relative glass rounded-2xl shadow-glow border border-white/60 ring-1 ring-primary-100/50 px-6 py-8 md:px-10 md:py-12 overflow-hidden">
            <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full bg-primary-200/60 blur-2xl"></div>
            <div class="absolute -bottom-12 -left-12 w-64 h-64 rounded-full bg-primary-300/40 blur-3xl"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-gray-800 flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-primary-100 text-primary-600 shadow-inner ring-1 ring-white/60"><i class="fa fa-comment-dots text-lg"></i></span>
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-700 to-primary-500">Write Feedback</span>
                    </h1>
                    <p class="mt-3 text-sm md:text-base text-gray-600 max-w-prose">Provide guidance and feedback on barangay cases and proceedings.</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <div class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fa fa-shield-halved text-primary-500"></i> Secure Form</div>
                    <div class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fa fa-comments text-primary-500"></i> Case Feedback</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Form Section (mirrors secretary add complaints) -->
    <main class="relative z-10 max-w-5xl mx-auto px-4 md:px-8 mt-10 pb-24">
        <section class="glass rounded-2xl shadow-glow border border-white/60 ring-1 ring-primary-100/50 p-6 md:p-10 animate-fade-in">
            <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
                <h2 class="text-lg md:text-xl font-semibold text-gray-800 flex items-center gap-2"><i class="fa fa-pen-to-square text-primary-500"></i> Feedback Details</h2>
                <a href="home-captain.php" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 font-medium"><i class="fa fa-arrow-left"></i> Back</a>
            </div>

            <?php if (!empty($success)): ?>
                <div class="mb-6 rounded-lg border border-green-300 bg-green-50 text-green-700 px-4 py-3 text-sm flex items-start gap-2"><i class="fa fa-check-circle mt-0.5"></i><span><?= htmlspecialchars($success) ?></span></div>
            <?php elseif (!empty($error)): ?>
                <div class="mb-6 rounded-lg border border-red-300 bg-red-50 text-red-700 px-4 py-3 text-sm flex items-start gap-2"><i class="fa fa-circle-exclamation mt-0.5"></i><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>

            <form method="POST" class="space-y-8">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="case-id" class="field-label"><i class="fa fa-folder-open"></i> Select Case</label>
                        <select id="case-id" name="case_id" class="input-base" required>
                            <option value="">-- Select a Case --</option>
                            <?php
                            // Display all cases with compact label: Case #ID — case_type
                            $sql = "SELECT ci.Case_ID, COALESCE(NULLIF(TRIM(co.case_type), ''), 'N/A') AS case_type
                                    FROM case_info ci
                                    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID
                                    ORDER BY ci.Case_ID ASC";
                            $result = $conn->query($sql);
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $cid = (int)$row['Case_ID'];
                                    $ctype = $row['case_type'] ?? 'N/A';
                                    $label = 'Case #' . $cid . ' — ' . $ctype;
                                    echo '<option value="' . $cid . '" data-case-type="' . htmlspecialchars($ctype) . '">' . htmlspecialchars($label) . '</option>';
                                }
                            } else {
                                echo '<option disabled>No cases found</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <!-- Right-side Case Details Panel -->
                    <div>
                        <label class="field-label"><i class="fa fa-circle-info"></i> Case Details</label>
                        <div id="case-details" class="rounded-lg border border-gray-200 bg-white/70 p-4 min-h-[180px]">
                            <div class="text-sm text-gray-500">Select a case to view details here.</div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="message" class="field-label"><i class="fa fa-align-left"></i> Your Feedback</label>
                    <textarea id="message" name="message" rows="6" required class="input-base resize-y" placeholder="Enter your feedback here..."></textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-dashed border-primary-200/60">
                    <a href="home-captain.php" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-white/70 hover:bg-white text-gray-600 border border-gray-300 text-sm font-medium shadow-sm transition"><i class="fa fa-xmark"></i> Cancel</a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold shadow focus:outline-none focus:ring-4 focus:ring-primary-300/50 transition">
                        <i class="fa fa-paper-plane"></i> Submit Feedback
                    </button>
                </div>
            </form>
        </section>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const sel = document.getElementById('case-id');
        const panel = document.getElementById('case-details');
        async function loadCaseDetails(id){
            if(!id){ panel.innerHTML = '<div class="text-sm text-gray-500">Select a case to view details here.</div>'; return; }
            panel.innerHTML = '<div class="text-sm text-gray-500">Loading case details…</div>';
            try{
                const res = await fetch('../controllers/get_case_brief.php?case_id='+encodeURIComponent(id), { headers:{'Accept':'application/json'} });
                const data = await res.json();
                if(data && data.success){
                    const c = data.case || {};
                    const rows = [
                        {label:'Case #', value: c.case_id ?? ''},
                        {label:'Case Type', value: c.case_type ?? '—'},
                        {label:'Status', value: c.case_status ?? '—'},
                        {label:'Details', value: c.complaint ?? '—'},
                        {label:'Next Hearing', value: c.next_hearing ?? '—'}
                    ];
                    panel.innerHTML = '<div class="space-y-2">'+ rows.map(r=>`<div class=\"flex gap-3 text-sm\"><div class=\"w-32 text-gray-500 font-medium\">${r.label}</div><div class=\"flex-1 text-gray-800\">${r.value}</div></div>`).join('') + '</div>';
                } else {
                    panel.innerHTML = '<div class="text-sm text-red-600">Unable to load case details.</div>';
                }
            }catch(e){
                panel.innerHTML = '<div class="text-sm text-red-600">Failed to load case details.</div>';
            }
        }
        sel?.addEventListener('change', (e)=> loadCaseDetails(e.target.value));
        if(sel && sel.value){ loadCaseDetails(sel.value); }
    });
    </script>
</body>
</html>
