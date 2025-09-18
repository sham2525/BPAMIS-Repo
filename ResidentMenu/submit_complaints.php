<?php
session_start();
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../bpamis_website/login.php");
    exit();
}

// Get the resident ID from session
$resident_id = (int)$_SESSION['user_id'];
include_once '../server/server.php'; // provides $conn

// Helper: unify name building
function build_full_name($row){
    $parts = array_filter([$row['First_Name'] ?? '', $row['Middle_Name'] ?? '', $row['Last_Name'] ?? '']);
    return trim(implode(' ', $parts));
}

// Fetch resident full name for complainant display
$resident_name = '';
if(isset($conn) && $conn){
    if($stmt = $conn->prepare("SELECT First_Name, Middle_Name, Last_Name FROM resident_info WHERE Resident_ID = ? LIMIT 1")){
        $stmt->bind_param('i', $resident_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if($row = $res->fetch_assoc()){
            $resident_name = build_full_name($row);
        }
        $stmt->close();
    }
}

// Build respondent whitelist for Tagify (array of full names of other residents)
$respondent_whitelist = [];
if(isset($conn) && $conn){
    $rs = $conn->query("SELECT Resident_ID, First_Name, Middle_Name, Last_Name FROM resident_info WHERE Resident_ID <> $resident_id");
    if($rs){
        while($r = $rs->fetch_assoc()){
            $respondent_whitelist[] = build_full_name($r);
        }
    }
}

$insert_success = false; $error_message = '';
// Will hold parsed respondent names for possible prefill
$respondent_names = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Sanitize inputs
    $complainant_name = trim($_POST['complainant_name'] ?? ''); // already derived
    $respondent_raw   = $_POST['respondent_name'] ?? '';
    $incident_date    = trim($_POST['incident_date'] ?? '');
    $incident_time    = trim($_POST['incident_time'] ?? '');
    $description      = trim($_POST['complaint_description'] ?? '');

    // Parse Tagify JSON or plain text (comma separated)
    // $respondent_names declared above for later prefill in UI
    if(is_string($respondent_raw) && strlen($respondent_raw)){
        $trimmed = ltrim($respondent_raw);
        if(str_starts_with($trimmed, '[')){
            $decoded = json_decode($respondent_raw, true);
            if(is_array($decoded)){
                foreach($decoded as $item){ if(!empty($item['value'])) $respondent_names[] = trim($item['value']); }
            }
        } else {
            $respondent_names = array_filter(array_map('trim', preg_split('/\s*,\s*/',$respondent_raw)));
        }
    }

    if($description === '' || $incident_date === ''){
        $error_message = 'Please provide required fields (description, incident date).';
    } else {
        // Server-side word count validation: 100-200 words
        $wordCount = str_word_count(trim($description));
        if($wordCount < 100 || $wordCount > 200){
            $error_message = 'Description must be between 100 and 200 words. Currently: '.(int)$wordCount.' words.';
        } else {
        // Derive Complaint_Title from description (first 60 chars)
        $complaint_title = mb_substr($description,0,60);
        if($complaint_title === '') $complaint_title = 'Complaint '.date('Y-m-d H:i');

        // Optional main respondent: find first resolvable resident id
        $main_respondent_id = null;
        if(!empty($respondent_names)){
            if($stmt = $conn->prepare("SELECT Resident_ID, First_Name, Middle_Name, Last_Name FROM resident_info")){
                $stmt->execute();
                $res = $stmt->get_result();
                $pool = [];
                while($row = $res->fetch_assoc()){ $pool[strtolower(build_full_name($row))] = (int)$row['Resident_ID']; }
                $stmt->close();
                foreach($respondent_names as $rn){
                    $key = strtolower(preg_replace('/\s+/',' ', trim($rn)));
                    if(isset($pool[$key])){ $main_respondent_id = $pool[$key]; break; }
                }
            }
        }

        // Handle attachments (allow multi) with server-side 20MB validation
        $stored_paths = [];
        if(!empty($_FILES['complaint_attachment']['name'][0])){
            $MAX_FILE_BYTES = 20 * 1024 * 1024; // 20MB
            $oversized=[];
            foreach($_FILES['complaint_attachment']['name'] as $idx => $origName){
                if($_FILES['complaint_attachment']['error'][$idx] === UPLOAD_ERR_OK){
                    if($_FILES['complaint_attachment']['size'][$idx] > $MAX_FILE_BYTES){
                        $oversized[] = $origName;
                    }
                }
            }
            if(!empty($oversized)){
                $error_message = 'The following files exceed the 20MB limit: '.htmlspecialchars(implode(', ', $oversized));
            } else {
                $uploadDir = __DIR__.'/../uploads/';
                if(!is_dir($uploadDir)) @mkdir($uploadDir,0777,true);
                foreach($_FILES['complaint_attachment']['name'] as $idx => $origName){
                    if($_FILES['complaint_attachment']['error'][$idx] === UPLOAD_ERR_OK){
                        $safe = time().'_'.preg_replace('/[^A-Za-z0-9_.-]/','_', $origName);
                        $target = $uploadDir.$safe;
                        if(move_uploaded_file($_FILES['complaint_attachment']['tmp_name'][$idx], $target)){
                            $stored_paths[] = 'uploads/'.$safe;
                        }
                    }
                }
            }
        }
        $attachment_path = null;
        if(!empty($stored_paths)) $attachment_path = implode(';',$stored_paths); // simple concat

        $status = 'Pending';
        // Insert complaint
        if($attachment_path){
            $stmt = $conn->prepare("INSERT INTO COMPLAINT_INFO (Resident_ID, Respondent_ID, Complaint_Title, Complaint_Details, Date_Filed, Status, Attachment_Path) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param('iisssss', $resident_id, $main_respondent_id, $complaint_title, $description, $incident_date, $status, $attachment_path);
        } else {
            $stmt = $conn->prepare("INSERT INTO COMPLAINT_INFO (Resident_ID, Respondent_ID, Complaint_Title, Complaint_Details, Date_Filed, Status) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('iissss', $resident_id, $main_respondent_id, $complaint_title, $description, $incident_date, $status);
        }
        if(isset($stmt) && $stmt && empty($error_message) && $stmt->execute()){
            $complaint_id = $stmt->insert_id;
            // Insert any additional respondents to COMPLAINT_RESPONDENTS if table exists
            if(count($respondent_names) > 1){
                if($chk = $conn->query("SHOW TABLES LIKE 'COMPLAINT_RESPONDENTS'")){
                    if($chk->num_rows){
                        // Build mapping again (pool reused if earlier built)
                        if(!isset($pool)){
                            $pool = [];
                            $mapRs = $conn->query("SELECT Resident_ID, First_Name, Middle_Name, Last_Name FROM resident_info");
                            while($mapRs && $mr = $mapRs->fetch_assoc()){ $pool[strtolower(build_full_name($mr))] = (int)$mr['Resident_ID']; }
                        }
                        $ins = $conn->prepare('INSERT INTO COMPLAINT_RESPONDENTS (Complaint_ID, Respondent_ID) VALUES (?,?)');
                        foreach(array_slice($respondent_names,1) as $nm){
                            $k = strtolower(preg_replace('/\s+/',' ',trim($nm)));
                            if(isset($pool[$k])){ $rid = $pool[$k]; $ins->bind_param('ii',$complaint_id,$rid); $ins->execute(); }
                        }
                        $ins->close();
                    }
                }
            }
            $insert_success = true;
        } else if(empty($error_message)) {
            $error_message = 'Failed to save complaint.' . ($stmt? ' '.$stmt->error : '');
        }
        if($stmt) $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae2fd',
                            300: '#7cccfd',
                            400: '#36b3f9',
                            500: '#0c9ced',
                            600: '#0281d4',
                            700: '#026aad',
                            800: '#065a8f',
                            900: '#0a4b76'
                        }
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(to right, #f0f7ff, #e0effe);
        }
        .form-input:focus {
            border-color: #0c9ced;
            box-shadow: 0 0 0 3px rgba(12, 156, 237, 0.1);
            outline: none;
        }
        
    </style>
</head>
<body class="bg-gray-50 font-sans relative overflow-x-hidden">
    <?php include_once('../includes/resident_nav.php'); ?>

    <!-- Global Blue Blush Orbs Background -->
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-44 -left-44 w-[500px] h-[500px] bg-blue-200/40 blur-3xl rounded-full animate-[float_14s_ease-in-out_infinite]"></div>
        <div class="absolute top-1/3 -right-52 w-[560px] h-[560px] bg-cyan-200/40 blur-[160px] rounded-full animate-[float_18s_ease-in-out_infinite]"></div>
        <div class="absolute -bottom-64 left-1/3 w-[520px] h-[520px] bg-indigo-200/30 blur-3xl rounded-full animate-[float_16s_ease-in-out_infinite]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[900px] bg-gradient-to-br from-blue-50 via-white to-cyan-50 opacity-70 blur-[200px] rounded-full"></div>
    </div>

   <!-- Page Heading -->
    <header class="max-w-screen-2xl mx-auto px-5 pt-10 relative animate-fade-in">
                <div class="relative glass rounded-2xl shadow-glow border border-white/60 ring-1 ring-primary-100/50 px-6 py-8 md:px-10 md:py-12 overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full bg-primary-200/60 blur-2xl"></div>
                    <div class="absolute -bottom-12 -left-12 w-64 h-64 rounded-full bg-primary-300/40 blur-3xl"></div>
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-gray-800 flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-primary-100 text-primary-600 shadow-inner ring-1 ring-white/60"><i class="fa fa-file-pen text-lg"></i></span>
                                <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-700 to-primary-500">Submit Complaint</span>
                            </h1>
                            <p class="mt-3 text-sm md:text-base text-gray-600 max-w-prose">File a new complaint for barangay records. Provide necessary details for accurate processing.</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <div class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fa fa-shield-halved text-primary-500"></i> Secure Form</div>
                            <div class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fa fa-database text-primary-500"></i> Auto Link Residents</div>
                        </div>
                    </div>
                </div>
            </header>

    <!-- Form Card -->
    <div class="w-full mt-8 px-4 pb-16">
        <div class="w-full max-w-7xl mx-auto bg-white/95 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md p-8 md:p-10 relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full opacity-70"></div>
            <div class="absolute -bottom-16 -left-16 w-56 h-56 bg-gradient-to-tr from-blue-50 to-cyan-100 rounded-full opacity-60"></div>
            <div class="relative z-10">
                <?php if($insert_success): ?>
                    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-start gap-3"><i class="fa fa-check-circle mt-0.5"></i><div>Complaint submitted successfully.</div></div>
                <?php elseif($error_message): ?>
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-start gap-3"><i class="fa fa-exclamation-triangle mt-0.5"></i><div><?= htmlspecialchars($error_message) ?></div></div>
                <?php endif; ?>
                <form action="submit_complaints.php" method="POST" enctype="multipart/form-data" class="space-y-10" id="complaintForm">
                    <!-- Hidden title (derived client-side for AI scope check only) -->
                    <input type="hidden" id="complaint-title" value="" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label for="complainant-name" class="block text-sm font-medium text-gray-700">Complainant Name</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-user"></i></span>
                                <input type="text" id="complainant-name" value="<?= htmlspecialchars($resident_name) ?>" disabled aria-disabled="true" class="w-full pl-10 pr-3 py-3 h-[70px] rounded-lg border border-gray-200 bg-white text-gray-500 focus:outline-none cursor-not-allowed pointer-events-none select-none" />
                                <input type="hidden" name="complainant_name" value="<?= htmlspecialchars($resident_name) ?>" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="respondent-name" class="block text-sm font-medium text-gray-700">Respondent Name(s) <span class="text-gray-400 font-normal">(Optional)</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-gray-400"><i class="fa-solid fa-user-group"></i></span>
                                <input type="text" id="respondent-name" name="respondent_name" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input" placeholder="Type and select respondent names (optional)">
                            </div>
                            <p class="text-xs text-gray-500 italic">Use full names (First Middle Last). You can add multiple respondents.</p>
                        </div>
                       
                        <div class="space-y-2">
                            <label for="incident-date" class="block text-sm font-medium text-gray-700">Incident Date <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-calendar-day"></i></span>
                                <input type="date" id="incident-date" name="incident_date" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="incident-time" class="block text-sm font-medium text-gray-700">Incident Time <span class="text-gray-400 font-normal">(Optional)</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-clock"></i></span>
                                <input type="time" id="incident-time" name="incident_time" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input">
                            </div>
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <div class="flex items-center justify-between">
                                <label for="complaint-description" class="block text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-3">
                                    
                                    <button type="button" id="open-tips" class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-md bg-white/80 backdrop-blur border border-gray-200 text-primary-700 hover:text-primary-800 hover:bg-white shadow-sm transition" title="Tips for a good complaint" aria-haspopup="dialog" aria-controls="tips-modal">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-600 ring-1 ring-white/60"><i class="fa fa-file-pen text-lg"></i></span>
                                        <span class="hidden sm:inline text-[11px] font-semibold">Tips</span>
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <textarea id="complaint-description" name="complaint_description" rows="6" placeholder="Provide a clear and detailed description of the complaint..." class="w-full p-4 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input resize-y" required><?= isset($_POST['complaint_description']) && !$insert_success ? htmlspecialchars($_POST['complaint_description']) : '' ?></textarea>
                                
                            </div>
                            <div class="mt-1 flex items-center justify-between">
                                <p class="text-xs text-gray-500">Include detailed incident, location and involved parties if known. Minimum 100 words, maximum 200 words.</p>
                                <span id="desc-word-counter" class="text-xs text-gray-500">0/200 words</span>
                            </div>
                        </div>
                        <!-- Hidden out_of_scope field for AI classification -->
                        <input type="hidden" name="out_of_scope" id="out_of_scope" value="0">
                        
                    </div>

                    <!-- Attachments -->
                    <div class="space-y-3" id="attachments-block">
                        <label for="complaint-attachment" class="block text-sm font-medium text-gray-700">Attachments <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <div class="relative">
                            <label for="complaint-attachment" class="flex flex-col justify-center items-center w-full h-40 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-dashed border-gray-300 cursor-pointer hover:border-blue-300 hover:bg-blue-50/40 transition group">
                                <div class="flex flex-col justify-center items-center pt-4 pb-5">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-3 group-hover:text-blue-500 transition"></i>
                                    <p class="text-sm text-gray-600">Click to upload or drag & drop</p>
                                    <p class="text-xs text-gray-400">PNG, JPG or PDF (max. 20MB each)</p>
                                </div>
                                <input id="complaint-attachment" type="file" name="complaint_attachment[]" class="hidden" multiple />
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">You can upload multiple files as evidence for your complaint. Each file must not exceed 20MB.</p>
                        <div id="attachmentError" class="hidden mt-2 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-2">
                            <i class="fa fa-triangle-exclamation mt-0.5"></i>
                            <span id="attachmentErrorText"></span>
                        </div>
                        <div id="attachmentPreview" class="hidden mt-2 grid grid-cols-2 md:grid-cols-3 gap-3"></div>
                        <?php if($insert_success): ?>
                            <div class="mt-2 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm flex items-start gap-2" id="inline-success-msg">
                                <i class="fa fa-check-circle mt-0.5"></i>
                                <span>Your complaint has been recorded. Reference ID: <strong><?= isset($complaint_id)? (int)$complaint_id : '' ?></strong></span>
                            </div>
                        <?php elseif($error_message): ?>
                            <div class="mt-2 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-2" id="inline-error-msg">
                                <i class="fa fa-exclamation-triangle mt-0.5"></i>
                                <span><?= htmlspecialchars($error_message) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-3 sm:justify-between items-center">
                        <div class="text-xs text-gray-500 flex items-start gap-2 max-w-sm"><i class="fa-solid fa-shield-halved text-blue-500 mt-0.5"></i><span>Data recorded here becomes part of the official barangay intake record and is handled confidentially.</span></div>
                        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                            <a href="home-resident.php" type="button" class="py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg flex items-center justify-center gap-2 transition"><i class="fa-solid fa-xmark"></i> Cancel</a>
                <button type="submit" id="submit-btn" disabled class="py-3 px-8 bg-blue-400 cursor-not-allowed text-white font-medium rounded-lg flex items-center justify-center gap-2 shadow-sm transition disabled:opacity-70"><i class="fa-solid fa-paper-plane"></i> Submit Complaint</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // File input preview + drag/drop
        document.addEventListener('DOMContentLoaded',()=>{
            const submitBtn=document.getElementById('submit-btn');
            const descField=document.getElementById('complaint-description');
            const dateField=document.getElementById('incident-date');
            const respondentField=document.getElementById('respondent-name');
            const counterEl=document.getElementById('desc-word-counter');
            const MIN_WORDS=100, MAX_WORDS=200;
            function countWords(text){
                return (text.trim().match(/\b\w+\b/g) || []).length;
            }
            function updateCounterAndValidity(){
                const words=countWords(descField.value);
                if(counterEl){ counterEl.textContent = `${words}/${MAX_WORDS} words`; }
                // visual cues
                descField.classList.remove('ring-2','ring-red-300','ring-amber-300');
                if(words>0 && words<MIN_WORDS){
                    descField.classList.add('ring-2','ring-amber-300');
                } else if(words>MAX_WORDS){
                    descField.classList.add('ring-2','ring-red-300');
                }
                // hard cap: trim to MAX on input for UX
                if(words>MAX_WORDS){
                    // Attempt to trim by words to preserve whole words
                    const tokens = descField.value.trim().split(/(\b\w+\b)/).filter(Boolean);
                    let w=0, out='';
                    for(const t of tokens){
                        if(/\b\w+\b/.test(t)){
                            if(w>=MAX_WORDS) break; w++; out+=t;
                        } else { out+=t; }
                    }
                    descField.value = out.trim();
                }
                return words>=MIN_WORDS && words<=MAX_WORDS;
            }
            function refreshSubmit(){
                const respondentFilled = respondentField && respondentField.value.trim().length>0; // Tagify stores JSON
                const withinRange = updateCounterAndValidity();
                const ok = descField.value.trim().length>0 && dateField.value.trim().length>0 && respondentFilled && withinRange;
                if(ok){
                    submitBtn.disabled=false;
                    submitBtn.classList.remove('bg-blue-400','cursor-not-allowed');
                    submitBtn.classList.add('bg-blue-600','hover:bg-blue-700');
                } else {
                    submitBtn.disabled=true;
                    submitBtn.classList.add('bg-blue-400','cursor-not-allowed');
                    submitBtn.classList.remove('bg-blue-600','hover:bg-blue-700');
                }
            }
            ['input','change'].forEach(ev=>{descField.addEventListener(ev,refreshSubmit); dateField.addEventListener(ev,refreshSubmit); respondentField.addEventListener(ev,refreshSubmit);});
            // initialize counter
            updateCounterAndValidity();
            refreshSubmit();
            const inputFile=document.getElementById('complaint-attachment');
            const label=document.querySelector('label[for="complaint-attachment"]');
            const errorBox=document.getElementById('attachmentError');
            const errorText=document.getElementById('attachmentErrorText');
            const previewGrid=document.getElementById('attachmentPreview');
            let objectUrls=[];
            function resetPreview(){
                objectUrls.forEach(u=> URL.revokeObjectURL(u));
                objectUrls=[];
                previewGrid.innerHTML='';
                previewGrid.classList.add('hidden');
            }
            if(inputFile){
                const MAX_BYTES = 20 * 1024 * 1024; // 20MB
                const originalLabelHTML = label.innerHTML;
                inputFile.addEventListener('change',()=>{
                    resetPreview();
                    errorBox.classList.add('hidden');
                    errorText.textContent='';
                    if(!inputFile.files.length){ label.innerHTML=originalLabelHTML; refreshSubmit(); return; }
                    const files=[...inputFile.files];
                    const overs = files.filter(f=> f.size>MAX_BYTES);
                    if(overs.length){
                        errorText.textContent='These files exceed 20MB and were removed: '+ overs.map(o=>o.name).join(', ');
                        errorBox.classList.remove('hidden');
                        const dt=new DataTransfer();
                        files.filter(f=> f.size<=MAX_BYTES).forEach(f=> dt.items.add(f));
                        inputFile.files=dt.files;
                        if(!inputFile.files.length){ label.innerHTML=originalLabelHTML; refreshSubmit(); return; }
                    }
                    const validFiles=[...inputFile.files];
                    // Update label summary
                    let summaryHTML='';
                    if(validFiles.length===1){
                        summaryHTML=`<div class=\"flex flex-col justify-center items-center pt-4 pb-5\"><i class=\"fas fa-file-alt text-primary-500 text-3xl mb-2\"></i><p class=\"text-sm text-gray-700 font-medium truncate max-w-[240px]\" title=\"${validFiles[0].name}\">${validFiles[0].name}</p></div>`;
                    } else {
                        summaryHTML=`<div class=\"flex flex-col justify-center items-center pt-4 pb-5\"><i class=\"fas fa-file-alt text-primary-500 text-3xl mb-2\"></i><p class=\"text-sm text-gray-700 font-medium\">${validFiles.length} files selected</p></div>`;
                    }
                    label.innerHTML=summaryHTML;
                    // Build previews
                    validFiles.forEach((file,idx)=>{
                        const url = URL.createObjectURL(file); objectUrls.push(url);
                        let inner='';
                        if(file.type.startsWith('image/')){
                            inner = `<img src=\"${url}\" alt=\"${file.name}\" class=\"w-full h-24 object-cover rounded-md border\" />`;
                        } else if(file.type === 'application/pdf'){
                            inner = `<div class=\"flex flex-col items-center justify-center gap-2 p-3 rounded-md border bg-white\"><i class=\"fa fa-file-pdf text-red-500 text-2xl\"></i><span class=\"text-[11px] text-center line-clamp-2\">${file.name}</span></div>`;
                        } else {
                            inner = `<div class=\"flex flex-col items-center justify-center gap-2 p-3 rounded-md border bg-white\"><i class=\"fa fa-file text-gray-500 text-2xl\"></i><span class=\"text-[11px] text-center line-clamp-2\">${file.name}</span></div>`;
                        }
                        const wrap=document.createElement('div');
                        wrap.className='relative group';
                        wrap.innerHTML= inner + `\n<div class=\"absolute inset-0 bg-black/45 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-3 rounded-md\">\n  <button type=\"button\" data-action=\"view\" data-index=\"${idx}\" class=\"p-2 rounded-full bg-white/90 text-gray-700 hover:bg-white shadow\" title=\"View\"><i class=\"fa fa-eye\"></i></button>\n  <button type=\"button\" data-action=\"remove\" data-index=\"${idx}\" class=\"p-2 rounded-full bg-white/90 text-red-600 hover:bg-white shadow\" title=\"Remove\"><i class=\"fa fa-trash\"></i></button>\n</div>`;
                        previewGrid.appendChild(wrap);
                    });
                    // Add action handlers
                    previewGrid.querySelectorAll('button[data-action]')?.forEach(btn=>{
                        btn.addEventListener('click',(ev)=>{
                            ev.preventDefault();
                            const action = btn.getAttribute('data-action');
                            const index = parseInt(btn.getAttribute('data-index'));
                            if(isNaN(index)) return;
                            if(action==='view'){
                                window.open(objectUrls[index],'_blank');
                            } else if(action==='remove'){
                                const current=[...inputFile.files];
                                const dt=new DataTransfer();
                                current.forEach((f,i)=>{ if(i!==index) dt.items.add(f); });
                                inputFile.files=dt.files;
                                inputFile.dispatchEvent(new Event('change',{bubbles:true}));
                            }
                        });
                    });
                    if(validFiles.length){ previewGrid.classList.remove('hidden'); }
                    refreshSubmit();
                });
                ['dragenter','dragover','dragleave','drop'].forEach(ev=> label.addEventListener(ev,(e)=>{e.preventDefault();e.stopPropagation();},false));
                ['dragenter','dragover'].forEach(ev=> label.addEventListener(ev,()=>label.classList.add('border-primary-300','bg-primary-50/50'),false));
                ['dragleave','drop'].forEach(ev=> label.addEventListener(ev,()=>label.classList.remove('border-primary-300','bg-primary-50/50'),false));
                label.addEventListener('drop',(e)=>{ inputFile.files=e.dataTransfer.files; inputFile.dispatchEvent(new Event('change',{bubbles:true})); });
                // Clear previews after successful submission (handled by PHP flag) - done later in Tagify section if needed
            }
        });
    </script>
  <!-- for open router -->
         <?php
        $config = include '../chatbot/config.php';
        $apiKey = $config['openrouter_api_key'] ?? '';
        ?>
     <script>
            async function checkComplaintScope(title, description) {
    const prompt = `Determine if the following complaint is within the jurisdiction of a barangay in the Philippines. Respond with "IN_SCOPE" or "OUT_OF_SCOPE".\n\nTitle: ${title}\nDescription: ${description}`;
    //apiKey
    const apiKey = "<?php echo $apiKey; ?>";
    const response = await fetch("https://openrouter.ai/api/v1/chat/completions", {
        method: "POST",
        headers: {
            "Authorization": "Bearer " + apiKey,
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            model: "meta-llama/llama-3-8b-instruct",
            messages: [
                { role: "system", content: "You are a legal assistant who determines whether a complaint is within the jurisdiction of the Barangay in the Philippines."+

                    "Barangays only handle minor disputes. Serious crimes are OUT OF SCOPE."+

                    "Always answer only with: IN_SCOPE or OUT_OF_SCOPE"+

                    "The following are examples of OUT OF SCOPE complaints:"+
                    "- Murder (patay, pinatay, saksak, baril, patayan)"+
                    "- Rape (gahasain, panggagahasa)"+
                    "- Illegal drugs (droga, shabu, marijuana)"+
                    "- Major theft/robbery (nanakaw ng kotse, ninakaw ang 15 million pesos, holdap, akyat-bahay)"+
                    "- Any case that involves death, hospital confinement, or firearms"+
                    "- Cases that could cause death and large damage to property like arson"+
                    "- Nasunog ang bahay o Nasira ang bahay" },
                { role: "user", content: prompt }
            ]
        })
    });

    const data = await response.json();
    const result = data.choices?.[0]?.message?.content?.trim();
    if(result== "OUT_OF_SCOPE"){
        document.getElementById("out_of_scope").value = "1";
    }else if(result == "IN_SCOPE"){
        document.getElementById("out_of_scope").value = "0";
    }
    return result;
}
</script>
<!-- for modal-->
<div id="scope-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center">
    <div class="rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-white/60 bg-gradient-to-br from-blue-50 via-white to-cyan-50">
        <h2 class="text-xl font-semibold text-red-600">Possible Out-of-Scope Complaint</h2>
        <p>This complaint is outside the jurisdiction of the barangay.</p>
        <div class="flex justify-end gap-4 pt-4">
            <button id="proceed-submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Yes, Submit</button>
        </div>
    </div>
</div>

<!-- Tips modal -->
<div id="tips-modal" role="dialog" aria-modal="true" aria-labelledby="tips-title" class="fixed inset-0 hidden z-50 flex items-center justify-center">
    <div id="tips-overlay" class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 mx-auto max-w-lg w-[92%] sm:w-full">
        <div class="relative rounded-2xl p-6 md:p-7 border border-white/60 shadow-[0_18px_50px_-12px_rgba(14,116,144,0.25)] overflow-hidden bg-gradient-to-br from-blue-50 via-white to-cyan-50">
            <div class="absolute -top-16 -right-16 w-56 h-56 bg-gradient-to-br from-primary-200/60 to-primary-400/40 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-gradient-to-tr from-white/50 to-primary-100/50 rounded-full blur-3xl"></div>
            <div class="relative z-10">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-primary-100 text-primary-600 ring-1 ring-white/60 shadow-inner"><i class="fa-solid fa-lightbulb"></i></span>
                        <div>
                            <h3 id="tips-title" class="text-lg font-semibold text-sky-900">Tips for a good complaint</h3>
                            <p class="text-xs text-sky-700/80">Write clearly and stick to the facts.</p>
                        </div>
                    </div>
                    <button type="button" id="close-tips" class="p-2 rounded-lg bg-white border border-white/60 text-sky-700 hover:text-sky-900 hover:bg-white shadow-sm" aria-label="Close tips">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <ul class="space-y-3 text-sm text-sky-800">
                    <li class="flex items-start gap-3"><i class="fa-solid fa-check text-emerald-600 mt-1"></i><span><span class="font-medium">State the facts chronologically:</span> what happened, when, where, and who was involved.</span></li>
                    <li class="flex items-start gap-3"><i class="fa-solid fa-check text-emerald-600 mt-1"></i><span><span class="font-medium">Be specific:</span> include dates, times, locations, and names if known.</span></li>
                    <li class="flex items-start gap-3"><i class="fa-solid fa-check text-emerald-600 mt-1"></i><span><span class="font-medium">Avoid offensive language:</span> keep the tone respectful and objective.</span></li>
                    <li class="flex items-start gap-3"><i class="fa-solid fa-check text-emerald-600 mt-1"></i><span><span class="font-medium">Describe evidence:</span> photos, messages, receipts, or witnesses (attach files if available).</span></li>
                    <li class="flex items-start gap-3"><i class="fa-solid fa-check text-emerald-600 mt-1"></i><span><span class="font-medium">State the impact:</span> briefly explain how the incident affected you.</span></li>
                </ul>
                <div class="mt-5 flex justify-end">
                    <button type="button" id="got-it-tips" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-sky-600 text-white hover:bg-sky-700 shadow">
                        <i class="fa-solid fa-thumbs-up"></i>
                        Got it
                    </button>
                </div>
            </div>
        </div>
    </div>
  </div>

<!-- modal script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('complaintForm');
    const modal = document.getElementById("scope-modal");
    const proceedBtn = document.getElementById("proceed-submit");
    const titleField = document.getElementById('complaint-title');
    const descField = document.getElementById('complaint-description');
    const dateField = document.getElementById('incident-date');
    // Tips modal elements
    const tipsModal = document.getElementById('tips-modal');
    const tipsOverlay = document.getElementById('tips-overlay');
    const openTips = document.getElementById('open-tips');
    const closeTips = document.getElementById('close-tips');
    const gotItTips = document.getElementById('got-it-tips');

    let formSubmissionAllowed = false;
    let autoSubmitTimeout;

    form.addEventListener("submit", async (e) => {
        if (formSubmissionAllowed) return; // allow after modal confirm

        e.preventDefault(); // prevent actual submission for now
        // Basic required field guard (time & attachments optional)
        // Enforce word range at submit time too
        const words = (descField.value.trim().match(/\b\w+\b/g) || []).length;
        if(descField.value.trim()==='' || dateField.value.trim()==='' || words<100 || words>200){
            // highlight if missing
            if(descField.value.trim()==='') descField.classList.add('ring-2','ring-red-300');
            if(dateField.value.trim()==='') dateField.classList.add('ring-2','ring-red-300');
            return;
        }
        // Derive a temporary title from description (first 40 chars)
        titleField.value = descField.value.trim().substring(0,40) || 'Complaint';
        const title = titleField.value;
        const desc = descField.value.trim();
        let result = 'IN_SCOPE';
        try {
            result = await checkComplaintScope(title, desc);
        } catch(err){
            console.warn('AI scope check failed, defaulting to IN_SCOPE', err);
        }
        console.log("LLM API Response:", result);

        if (result === "OUT_OF_SCOPE") {
            modal.classList.remove("hidden");

            // Auto-submit after 5 seconds
            autoSubmitTimeout = setTimeout(() => {
                modal.classList.add("hidden");
                formSubmissionAllowed = true;
                form.submit();
            }, 5000);
        } else {
            formSubmissionAllowed = true;
            form.submit();
        }
    });

    proceedBtn.addEventListener("click", () => {
        clearTimeout(autoSubmitTimeout); // prevent double submission
        modal.classList.add("hidden");
        formSubmissionAllowed = true;
        form.submit();
    });

    // Tips modal wiring
    function showTips(){ tipsModal?.classList.remove('hidden'); }
    function hideTips(){ tipsModal?.classList.add('hidden'); }
    openTips?.addEventListener('click', showTips);
    closeTips?.addEventListener('click', hideTips);
    gotItTips?.addEventListener('click', hideTips);
    tipsOverlay?.addEventListener('click', hideTips);
});
</script>

    <?php include '../chatbot/bpamis_case_assistant.php'?>
</body>
</html>
<script>
// Initialize Tagify for respondent names if input exists
document.addEventListener('DOMContentLoaded', function(){
    const input = document.getElementById('respondent-name');
    if(!input) return;
    // Provide whitelist from PHP
    const whitelist = <?php echo json_encode($respondent_whitelist, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    // Lazy load Tagify if not already present (assumes Tagify library included elsewhere). If not, attempt dynamic load.
    function init(){
        const tagify = new Tagify(input, {
            whitelist: whitelist,
            dropdown: { classname: 'tags-look', enabled: 0, maxItems: 10, closeOnSelect: false },
            enforceWhitelist: false,
            editTags: 1,
            originalInputValueFormat: valuesArr => JSON.stringify(valuesArr.map(v => ({ value: v.value })))
        });
        // Prefill if form posted back with errors
        const prefill = <?php echo json_encode(array_map(function($n){ return ['value'=>$n]; }, $respondent_names)); ?>;
        if(prefill.length){
            tagify.addTags(prefill);
        }
    }
    if(window.Tagify){ init(); }
    else {
        // Dynamically load Tagify (adjust path if library stored locally)
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/@yaireo/tagify';
        script.onload = init;
        document.head.appendChild(script);
        const css = document.createElement('link');
        css.rel='stylesheet';
        css.href='https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css';
        document.head.appendChild(css);
    }
    // Auto-clear fields after successful submission (server-side flag via embedded PHP)
    const wasSuccess = <?php echo $insert_success ? 'true':'false'; ?>;
    if(wasSuccess){
        // Delay so user can read success messages
        setTimeout(()=>{
            const form = document.getElementById('complaintForm');
            if(form){
                // Preserve complainant disabled field; clear others
                const desc = document.getElementById('complaint-description');
                const dateF = document.getElementById('incident-date');
                const timeF = document.getElementById('incident-time');
                if(desc) desc.value='';
                if(dateF) dateF.value='';
                if(timeF) timeF.value='';
                if(window.Tagify && input && input.tagify){ input.tagify.removeAllTags(); }
                // Reset file input & preview label
                const fileInput=document.getElementById('complaint-attachment');
                const fileLabel=document.querySelector('label[for="complaint-attachment"]');
                if(fileInput){ fileInput.value=''; }
                if(fileLabel){
                    fileLabel.innerHTML=`<div class=\"flex flex-col justify-center items-center pt-4 pb-5\"><i class=\"fas fa-cloud-upload-alt text-gray-400 text-3xl mb-3\"></i><p class=\"text-sm text-gray-600\">Click to upload or drag & drop</p><p class=\"text-xs text-gray-400\">PNG, JPG or PDF (max. 5MB each)</p></div>`;
                }
                // Re-disable submit
                const submitBtn=document.getElementById('submit-btn');
                if(submitBtn){
                    submitBtn.disabled=true;
                    submitBtn.classList.add('bg-blue-400','cursor-not-allowed');
                    submitBtn.classList.remove('bg-blue-600','hover:bg-blue-700');
                }
            }
        }, 1500);
    }
});
</script>
