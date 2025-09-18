<?php
session_start();

$success_message = '';
$error_message = '';

// Helper: resolve resident id by full name (with or without middle)
function getResidentId($conn, $full_name) {
    $full_name = preg_replace('/\s+/', ' ', trim($full_name));
    if ($full_name === '') return null;
    $sql = "SELECT Resident_ID,
                   CONCAT(First_Name, ' ', Middle_Name, ' ', Last_Name) AS full_with_middle,
                   CONCAT(First_Name, ' ', Last_Name) AS full_without_middle
            FROM resident_info";
    if (!$result = $conn->query($sql)) return null;
    $needle = strtolower($full_name);
    while ($row = $result->fetch_assoc()) {
        $a = strtolower(trim($row['full_with_middle']));
        $b = strtolower(trim($row['full_without_middle']));
        if ($needle === $a || $needle === $b) {
            return (int)$row['Resident_ID'];
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost','root','', 'barangay_case_management');
    if ($conn->connect_error) { die('Connection failed: '.$conn->connect_error); }

    $complainant_name      = trim($_POST['complainant_name'] ?? '');
    $rawRespondents        = $_POST['respondent_name'] ?? '';
    $complaint_description = trim($_POST['complaint_description'] ?? '');
    $incident_date         = trim($_POST['incident_date'] ?? '');
    $incident_time         = trim($_POST['incident_time'] ?? ''); // optional
    $status = 'Pending';

    // Detect Tagify JSON vs comma list for respondents
    $respondent_names = [];
    if (is_string($rawRespondents) && str_starts_with(ltrim($rawRespondents), '[')) {
        $decoded = json_decode($rawRespondents, true) ?? [];
        foreach ($decoded as $item) { if (!empty($item['value'])) $respondent_names[] = trim($item['value']); }
    } else {
        $respondent_names = array_filter(array_map('trim', preg_split('/\s*,\s*/', $rawRespondents)));
    }

    // Multi-file attachments (drag & drop) - store semicolon-separated relative paths
    $attachment_paths = [];
    if (!empty($_FILES['attachments']['name']) && is_array($_FILES['attachments']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
        foreach ($_FILES['attachments']['name'] as $i => $nm) {
            if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $size = (int)$_FILES['attachments']['size'][$i];
            // Size limit ~20MB per file
            if ($size > 20*1024*1024) continue;
            $safeName = time().'_'.preg_replace('/[^A-Za-z0-9_.-]/','_', $nm);
            if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $uploadDir.$safeName)) {
                $attachment_paths[] = 'uploads/'.$safeName;
            }
        }
    }
    $attachment_path_field = $attachment_paths ? implode(';', $attachment_paths) : null;

    if ($complainant_name && $complaint_description && $incident_date) {
        $complaint_title = mb_substr($complaint_description, 0, 60);
        if ($complaint_title === '') { $complaint_title = 'Complaint '.date('Y-m-d H:i'); }
        $complainant_id = getResidentId($conn, $complainant_name);
        $main_respondent_id = null;
        if (!empty($respondent_names)) { $main_respondent_id = getResidentId($conn, $respondent_names[0]); }

        // Choose insert columns based on optional Attachment_Path & Incident Time existing in schema
        $hasAttachmentColumn = false; $hasIncidentTimeColumn = false;
        if ($res=$conn->query("SHOW COLUMNS FROM COMPLAINT_INFO LIKE 'Attachment_Path'")) { if ($res->num_rows>0) $hasAttachmentColumn=true; $res->close(); }
        if ($res=$conn->query("SHOW COLUMNS FROM COMPLAINT_INFO LIKE 'Incident_Time'")) { if ($res->num_rows>0) $hasIncidentTimeColumn=true; $res->close(); }

        $columns = "Resident_ID, Respondent_ID, Complaint_Title, Complaint_Details, Date_Filed, Status";
        $placeholders = "?,?,?,?,?,?";
        $types = 'iissss';
        $values = [$complainant_id, $main_respondent_id, $complaint_title, $complaint_description, $incident_date, $status];

        if ($hasIncidentTimeColumn) { $columns .= ", Incident_Time"; $placeholders .= ",?"; $types .= 's'; $values[] = $incident_time ?: null; }
        if ($hasAttachmentColumn) { $columns .= ", Attachment_Path"; $placeholders .= ",?"; $types .= 's'; $values[] = $attachment_path_field; }

        $stmt = $conn->prepare("INSERT INTO COMPLAINT_INFO ($columns) VALUES ($placeholders)");
        if ($stmt) {
            $stmt->bind_param($types, ...$values);
            if ($stmt->execute()) {
                $complaint_id = $stmt->insert_id;
                if (count($respondent_names) > 1) {
                    $ins = $conn->prepare('INSERT INTO COMPLAINT_RESPONDENTS (Complaint_ID, Respondent_ID) VALUES (?, ?)');
                    for ($i=1;$i<count($respondent_names);$i++) {
                        $rid = getResidentId($conn, $respondent_names[$i]);
                        if ($rid) { $ins->bind_param('ii', $complaint_id, $rid); $ins->execute(); }
                    }
                    $ins->close();
                }
                $success_message = 'Complaint successfully recorded.';
            } else {
                $error_message = 'Failed to save complaint: '.$stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = 'Database error: '.$conn->error;
        }
        $conn->close();
    } else {
        $error_message = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Complaint</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme:{ extend:{ colors:{ primary:{50:'#f0f7ff',100:'#e0effe',200:'#bae2fd',300:'#7cccfd',400:'#36b3f9',500:'#0c9ced',600:'#0281d4',700:'#026aad',800:'#065a8f',900:'#0a4b76'}}, boxShadow:{glow:'0 0 0 1px rgba(12,156,237,0.10), 0 4px 18px -2px rgba(6,90,143,0.20)'}, keyframes:{fadeIn:{'0%':{opacity:0,transform:'translateY(4px)'},'100%':{opacity:1,transform:'translateY(0)'}},pulseSoft:{'0%,100%':{opacity:1},'50%':{opacity:.55}}}, animation:{'fade-in':'fadeIn .5s ease-out','pulse-soft':'pulseSoft 3s ease-in-out infinite'} } } };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" />
    <style>
        .bg-orbs:before, .bg-orbs:after { content:""; position:absolute; border-radius:9999px; filter:blur(70px); opacity:.35; }
        .bg-orbs:before { width:480px; height:480px; background:linear-gradient(135deg,#7cccfd,#0c9ced); top:-160px; left:-140px; }
        .bg-orbs:after { width:420px; height:420px; background:linear-gradient(135deg,#bae2fd,#7cccfd); bottom:-140px; right:-120px; }
        .glass { background:linear-gradient(145deg,rgba(255,255,255,.88),rgba(255,255,255,.65)); backdrop-filter:blur(14px) saturate(140%); -webkit-backdrop-filter:blur(14px) saturate(140%); }
        .input-base { width:100%; border-radius:0.5rem; border:1px solid rgba(209,213,219,.7); background:rgba(255,255,255,.7); padding:.625rem .75rem; font-size:.875rem; transition:.2s; }
        .input-base:not(textarea){ height:44px; line-height:1.2; }
        .input-base:focus { outline:none; background:#fff; border-color:#36b3f9; box-shadow:0 0 0 4px rgba(12,156,237,.25); }
        .field-label { font-size:11px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; margin-bottom:4px; display:flex; gap:4px; align-items:center; color:#4b5563; }
        .thumb-tile { position:relative; }
        .thumb-tile .overlay { position:absolute; inset:0; background:linear-gradient(135deg,rgba(0,0,0,.55),rgba(0,0,0,.35)); display:flex; flex-direction:column; justify-content:center; align-items:center; gap:.5rem; opacity:0; transition:.25s; }
        .thumb-tile:hover .overlay { opacity:1; }
        .action-row { display:flex; gap:.5rem; }
        .thumb-btn { height:34px; width:34px; display:inline-flex; align-items:center; justify-content:center; border-radius:.75rem; background:rgba(255,255,255,.92); color:#0c9ced; font-size:.85rem; font-weight:600; box-shadow:0 2px 6px -1px rgba(0,0,0,.25); backdrop-filter:blur(4px); }
        .thumb-btn:hover { background:#fff; }
        .thumb-btn.danger { color:#dc2626; }
    </style>
</head>
<body class="min-h-screen font-sans bg-gradient-to-br from-primary-50 via-white to-primary-100 text-gray-800 relative overflow-x-hidden bg-orbs">
    <?php include '../includes/barangay_official_sec_nav.php'; ?>

    <!-- Page Heading -->
    <header class="relative max-w-screen-2xl mx-auto px-4 md:px-8 pt-8 animate-fade-in">
                <div class="relative glass rounded-2xl shadow-glow border border-white/60 ring-1 ring-primary-100/50 px-6 py-8 md:px-10 md:py-12 overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full bg-primary-200/60 blur-2xl"></div>
                    <div class="absolute -bottom-12 -left-12 w-64 h-64 rounded-full bg-primary-300/40 blur-3xl"></div>
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-gray-800 flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-primary-100 text-primary-600 shadow-inner ring-1 ring-white/60"><i class="fa fa-file-pen text-lg"></i></span>
                                <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-700 to-primary-500">Add Complaint</span>
                            </h1>
                            <p class="mt-3 text-sm md:text-base text-gray-600 max-w-prose">File a new complaint for barangay records. Provide full names for accurate resident matching.</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <div class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fa fa-shield-halved text-primary-500"></i> Secure Form</div>
                            <div class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fa fa-database text-primary-500"></i> Auto Link Residents</div>
                        </div>
                    </div>
                </div>
            </header>

    <!-- Form Section -->
    <main class="relative z-10 max-w-7xl mx-auto px-4 md:px-8 mt-10 pb-24">
        <section class="glass rounded-2xl shadow-glow border border-white/60 ring-1 ring-primary-100/50 p-6 md:p-10 animate-fade-in">
            <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
                <h2 class="text-lg md:text-xl font-semibold text-gray-800 flex items-center gap-2"><i class="fa fa-circle-plus text-primary-500"></i> New Complaint Details</h2>
                <a href="home.php" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 font-medium"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <?php if($success_message): ?>
                <div class="mb-6 rounded-lg border border-green-300 bg-green-50 text-green-700 px-4 py-3 text-sm flex items-start gap-2"><i class="fa fa-check-circle mt-0.5"></i><span><?php echo htmlspecialchars($success_message); ?></span></div>
            <?php elseif($error_message): ?>
                <div class="mb-6 rounded-lg border border-red-300 bg-red-50 text-red-700 px-4 py-3 text-sm flex items-start gap-2"><i class="fa fa-circle-exclamation mt-0.5"></i><span><?php echo htmlspecialchars($error_message); ?></span></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" class="space-y-10" id="complaintForm">
                <!-- Row 1: Complainant / Respondent -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="complainant-name" class="field-label"><i class="fa fa-user"></i> Complainant Name</label>
                        <?php
                        $suggestName = '';
                        if (!empty($_SESSION['user_id'])) {
                            $conn_sn = new mysqli('localhost','root','', 'barangay_case_management');
                            if(!$conn_sn->connect_error){
                                $uid = (int)$_SESSION['user_id'];
                                $rsn = $conn_sn->query("SELECT First_Name, Middle_Name, Last_Name FROM resident_info WHERE Resident_ID = $uid");
                                if($rsn && $rsn->num_rows){
                                    $nm = $rsn->fetch_assoc();
                                    $suggestName = preg_replace('/\s+/', ' ', trim($nm['First_Name'].' '.($nm['Middle_Name']??'').' '.$nm['Last_Name']));
                                }
                                $conn_sn->close();
                            }
                        }
                        ?>
                        <input type="text" id="complainant-name" name="complainant_name" class="input-base" value="<?php echo htmlspecialchars($suggestName); ?>" placeholder="Full name" required />
                        <p class="mt-1 text-[11px] text-gray-500">Auto-filled with your account name.</p>
                    </div>
                    <div>
                        <label for="respondent-name" class="field-label"><i class="fa fa-users"></i> Respondent Name(s)</label>
                        <input type="text" id="respondent-name" name="respondent_name" class="input-base" required placeholder="Type & select names" />
                        <p class="mt-1 text-[11px] text-gray-500">Multiple names supported.</p>
                    </div>
                </div>
                <!-- Row 2: Date / Time -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="incident-date" class="field-label"><i class="fa fa-calendar-day"></i> Incident Date</label>
                        <input type="date" id="incident-date" name="incident_date" class="input-base" required />
                    </div>
                    <div>
                        <label for="incident-time" class="field-label"><i class="fa fa-clock"></i> Incident Time (Optional)</label>
                        <input type="time" id="incident-time" name="incident_time" class="input-base" />
                        <p class="mt-1 text-[11px] text-gray-500">Leave blank if unknown.</p>
                    </div>
                </div>
                <!-- Row 3: Description full width -->
                <div>
                    <label for="complaint-description" class="field-label"><i class="fa fa-align-left"></i> Description</label>
                    <textarea id="complaint-description" name="complaint_description" rows="5" class="input-base resize-y" placeholder="Provide a clear statement of the incident..." required></textarea>
                    <p class="mt-1 text-[11px] text-gray-500">Be as specific as possible. This will help in case assessment.</p>
                </div>
                <!-- Row 4: Attachments full width -->
                <div>
                    <label class="field-label"><i class="fa fa-paperclip"></i> Attachments (Optional)</label>
                    <div id="dropZone" class="mt-1 border-2 border-dashed border-primary-300/70 rounded-xl p-6 flex flex-col items-center justify-center gap-3 text-sm text-gray-500 bg-white/60 hover:bg-white transition cursor-pointer">
                        <i class="fa fa-cloud-arrow-up text-primary-500 text-2xl"></i>
                        <p class="text-center leading-snug"><span class="font-medium text-primary-600">Click to browse</span> or drag & drop files here<br><span class="text-[10px] text-gray-400">Images & documents (max 20MB each)</span></p>
                        <input type="file" name="attachments[]" id="attachmentInput" class="hidden" multiple />
                        <div id="fileGrid" class="mt-4 grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 w-full"></div>
                        <p id="fileHint" class="hidden w-full text-[11px] text-gray-500"></p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-dashed border-primary-200/60">
                    <a href="home.php" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-white/70 hover:bg-white text-gray-600 border border-gray-300 text-sm font-medium shadow-sm transition"><i class="fa fa-xmark"></i> Cancel</a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold shadow focus:outline-none focus:ring-4 focus:ring-primary-300/50 transition">
                        <i class="fa fa-paper-plane"></i> Submit Complaint
                    </button>
                </div>
            </form>
        </section>
    </main>

    <script>
        // Multi-file drag & drop with previews, view & delete actions
        (function(){
            const dz = document.getElementById('dropZone');
            const input = document.getElementById('attachmentInput');
            const grid = document.getElementById('fileGrid');
            const hint = document.getElementById('fileHint');
            if(!dz) return;

            let dt = new DataTransfer(); // holds current selection

            const over = e => { e.preventDefault(); e.stopPropagation(); dz.classList.add('ring-2','ring-primary-400','bg-white'); };
            const leave = e => { e.preventDefault(); e.stopPropagation(); dz.classList.remove('ring-2','ring-primary-400'); };
            ['dragenter','dragover'].forEach(evt=>dz.addEventListener(evt,over));
            ['dragleave','drop'].forEach(evt=>dz.addEventListener(evt,leave));
            dz.addEventListener('click', () => input.click());
            dz.addEventListener('drop', e => { e.preventDefault(); if(e.dataTransfer.files.length){ addFiles(e.dataTransfer.files); }});
            input.addEventListener('change', () => addFiles(input.files));

            function addFiles(fileList){
                Array.from(fileList).forEach(f => {
                    if(f.size > 20*1024*1024) return; // skip >20MB
                    // prevent duplicates by name+size
                    for(let i=0;i<dt.files.length;i++){
                        const existing = dt.files[i];
                        if(existing.name===f.name && existing.size===f.size) return;
                    }
                    dt.items.add(f);
                });
                input.files = dt.files;
                render();
            }

            function removeFile(idx){
                const newDT = new DataTransfer();
                for(let i=0;i<dt.files.length;i++) if(i!==idx) newDT.items.add(dt.files[i]);
                dt = newDT;
                input.files = dt.files;
                render();
            }

            function render(){
                grid.innerHTML = '';
                if(!dt.files.length){ hint.classList.add('hidden'); return; }
                hint.textContent = dt.files.length + ' file(s) ready for upload';
                hint.classList.remove('hidden');
                Array.from(dt.files).forEach((f,idx) => {
                    const ext = f.name.split('.').pop().toLowerCase();
                    const isImage = ['jpg','jpeg','png','gif','webp','bmp'].includes(ext);
                    const tile = document.createElement('div');
                    tile.className = 'thumb-tile relative rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden group';
                    tile.innerHTML = `
                        <div class="w-full aspect-video flex items-center justify-center bg-gray-100 overflow-hidden">
                            ${isImage ? `<img class=\"w-full h-full object-cover\" />` : `<div class=\"flex flex-col items-center justify-center text-gray-500 text-xs p-3\"><i class=\"fa fa-file text-2xl mb-2\"></i><span class=\"break-all leading-tight\">${escapeHtml(f.name)}</span></div>`}
                        </div>
                        <div class="overlay">
                            <div class="action-row">
                                ${isImage ? `<button type=\"button\" class=\"thumb-btn view-btn\" title=\"View\"><i class=\"fa fa-eye\"></i></button>` : ''}
                                <button type="button" class="thumb-btn danger del-btn" title="Remove"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>`;
                    grid.appendChild(tile);
                    if(isImage){
                        const imgEl = tile.querySelector('img');
                        const reader = new FileReader();
                        reader.onload = e => imgEl.src = e.target.result;
                        reader.readAsDataURL(f);
                    }
                    tile.querySelector('.del-btn').addEventListener('click', ()=> removeFile(idx));
                    if(isImage){
                        tile.querySelector('.view-btn').addEventListener('click', ()=> openPreview(f));
                    }
                });
            }

            function escapeHtml(str){ return str.replace(/[&<>"] /g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"," ":"&nbsp;"}[c]||c)); }

            // Simple modal preview
            function openPreview(file){
                const r = new FileReader();
                r.onload = e => {
                    const wrap = document.createElement('div');
                    wrap.className='fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-6';
                    wrap.innerHTML = `<div class=\"relative bg-white rounded-xl shadow-xl max-w-3xl w-full p-4\">
                        <button class=\"absolute -top-3 -right-3 h-10 w-10 rounded-full bg-white text-gray-600 shadow flex items-center justify-center hover:text-red-600 close-btn\"><i class=\"fa fa-xmark\"></i></button>
                        <img src='${e.target.result}' class='w-full h-auto rounded-md object-contain max-h-[70vh]' />
                    </div>`;
                    document.body.appendChild(wrap);
                    wrap.addEventListener('click', ev=>{ if(ev.target===wrap || ev.target.closest('.close-btn')) wrap.remove(); });
                };
                r.readAsDataURL(file);
            }
        })();

        // Tagify Respondents
        (function(){
            const names = [
                <?php
                $conn_js = new mysqli('localhost','root','', 'barangay_case_management');
                if(!$conn_js->connect_error){
                    $rjs = $conn_js->query("SELECT First_Name, Middle_Name, Last_Name FROM resident_info");
                    while($n = $rjs->fetch_assoc()){
                        $full = trim($n['First_Name'].' '.($n['Middle_Name']??'').' '.$n['Last_Name']);
                        echo json_encode(preg_replace('/\s+/', ' ', $full)).",";
                    }
                    $conn_js->close();
                }
                ?>
            ];
            const input = document.querySelector('#respondent-name');
            if(input){ new Tagify(input,{ whitelist:names, dropdown:{ maxItems:10, enabled:0, closeOnSelect:false }}); }
        })();
    </script>

    <?php include '../chatbot/bpamis_case_assistant.php'; ?>
    <?php include 'sidebar_.php'; ?>
</body>
</html>
