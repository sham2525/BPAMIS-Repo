<?php
// Secretary Complaint Details (Premium UI) - Clean implementation
session_start();
$conn = new mysqli("localhost","root","","barangay_case_management");
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($complaint_id<=0){ echo "Invalid complaint."; exit; }
$editing = isset($_GET['edit']);
$error = '';

// Fetch complaint and complainant info
$sql = "SELECT c.*, r.First_Name Res_First_Name, r.Last_Name Res_Last_Name, e.First_Name Ext_First_Name, e.Last_Name Ext_Last_Name
                FROM COMPLAINT_INFO c
                LEFT JOIN RESIDENT_INFO r ON c.Resident_ID = r.Resident_ID
                LEFT JOIN EXTERNAL_COMPLAINANT e ON c.External_Complainant_ID = e.External_Complaint_ID
                WHERE c.Complaint_ID = $complaint_id";
$res = $conn->query($sql);
if(!$res || $res->num_rows===0){ echo "Complaint not found."; exit; }
$complaint = $res->fetch_assoc();

$is_case = $conn->query("SELECT 1 FROM CASE_INFO WHERE Complaint_ID=$complaint_id LIMIT 1")->num_rows>0;
$is_rejected = strtolower($complaint['Status'])==='rejected';

// Existing case type (if CASE_INFO has Case_Type column)
$existing_case_type = null;
if($is_case){
    $colCheck = $conn->query("SHOW COLUMNS FROM CASE_INFO LIKE 'Case_Type'");
    if($colCheck && $colCheck->num_rows>0){
        $ct = $conn->query("SELECT Case_Type FROM CASE_INFO WHERE Complaint_ID=$complaint_id ORDER BY Case_ID DESC LIMIT 1");
        if($ct && $ct->num_rows>0){ $existing_case_type = $ct->fetch_assoc()['Case_Type'] ?? null; }
    }
}

// Actions
if($_SERVER['REQUEST_METHOD']==='POST'){
        if(isset($_POST['update_complaint']) && !$is_case){
                $title = $conn->real_escape_string($_POST['complaint_title']);
                $details = $conn->real_escape_string($_POST['complaint_details']);
                $conn->query("UPDATE COMPLAINT_INFO SET Complaint_Title='$title', Complaint_Details='$details' WHERE Complaint_ID=$complaint_id");
                $conn->query("DELETE FROM COMPLAINT_RESPONDENTS WHERE Complaint_ID=$complaint_id");
                $submitted = $_POST['respondents'] ?? [];
                $main_res_name='';
                if(!empty($complaint['Respondent_ID'])){ $mr=$conn->query("SELECT CONCAT(First_Name,' ',Last_Name) name FROM RESIDENT_INFO WHERE Resident_ID={$complaint['Respondent_ID']}"); if($mr&&$mr->num_rows>0){ $main_res_name=trim($mr->fetch_assoc()['name']); } }
                foreach($submitted as $full){
                        $full = trim($conn->real_escape_string($full));
                        if(!$full || $full === $main_res_name) continue;
                        $parts = preg_split('/\s+/', $full);
                        if(count($parts)<2) continue;
                        $fname=$conn->real_escape_string($parts[0]);
                        $lname=$conn->real_escape_string(end($parts));
                        $r=$conn->query("SELECT Resident_ID FROM RESIDENT_INFO WHERE First_Name='$fname' AND Last_Name='$lname' LIMIT 1");
                        if($r && $r->num_rows>0){ $rid=$r->fetch_assoc()['Resident_ID']; }
                        else { $conn->query("INSERT INTO RESIDENT_INFO (First_Name,Last_Name) VALUES ('$fname','$lname')"); $rid=$conn->insert_id; }
                        $conn->query("INSERT INTO COMPLAINT_RESPONDENTS (Complaint_ID,Respondent_ID) VALUES ($complaint_id,$rid)");
                }
                header("Location: view_complaint_details.php?id=$complaint_id"); exit;
        }
    if(isset($_POST['validate_case']) && !$is_case){
        $decision = $_POST['validate_decision'] ?? '';
        if($decision !== 'yes'){
            $error = 'Please select Yes to validate this complaint as a case.';
        } else {
            $case_type = trim($_POST['case_type'] ?? '');
            $allowed_types = ['Civil','Criminal','Blotter'];
            if(!in_array($case_type, $allowed_types, true)){
                $error = 'Please choose a valid case type (Civil, Criminal, or Blotter).';
            } else {
                $date_opened = date('Y-m-d');
                $case_type_esc = $conn->real_escape_string($case_type);
                $hasCaseType = false;
                $chk = $conn->query("SHOW COLUMNS FROM CASE_INFO LIKE 'Case_Type'");
                if($chk && $chk->num_rows>0){ $hasCaseType = true; }
                // Map selection to COMPLAINT_INFO.case_type values: 'civil case', 'criminal case', 'blotter'
                $ciTypeVal = '';
                $lc = strtolower($case_type);
                if($lc === 'civil') $ciTypeVal = 'civil case';
                elseif($lc === 'criminal') $ciTypeVal = 'criminal case';
                else $ciTypeVal = 'blotter';
                $ciTypeEsc = $conn->real_escape_string($ciTypeVal);
                $hasCIType = false; $tchk = $conn->query("SHOW COLUMNS FROM COMPLAINT_INFO LIKE 'case_type'"); if($tchk && $tchk->num_rows>0){ $hasCIType = true; }
                if($hasCaseType){
                    $conn->query("INSERT INTO CASE_INFO (Complaint_ID,Case_Status,Case_Type,Date_Opened) VALUES ($complaint_id,'Open','$case_type_esc','$date_opened')");
                } else {
                    $conn->query("INSERT INTO CASE_INFO (Complaint_ID,Case_Status,Date_Opened) VALUES ($complaint_id,'Open','$date_opened')");
                }
                if($hasCIType){
                    $conn->query("UPDATE COMPLAINT_INFO SET Status='IN CASE', case_type='$ciTypeEsc' WHERE Complaint_ID=$complaint_id");
                } else {
                    $conn->query("UPDATE COMPLAINT_INFO SET Status='IN CASE' WHERE Complaint_ID=$complaint_id");
                }
                $cr=$conn->query("SELECT Resident_ID, External_Complainant_ID FROM COMPLAINT_INFO WHERE Complaint_ID=$complaint_id");
                if($cr&&$cr->num_rows>0){ $row=$cr->fetch_assoc(); $rid=$row['Resident_ID']; $eid=$row['External_Complainant_ID']; $title='Complaint Converted to Case'; $msg="Your complaint with ID #$complaint_id has been validated as a $case_type case and is now an official case."; $now=date('Y-m-d H:i:s'); $type='Case'; if(!empty($rid)) $conn->query("INSERT INTO notifications (resident_id,title,message,type,is_read,created_at) VALUES ($rid,'$title','$msg','$type',0,'$now')"); elseif(!empty($eid)) $conn->query("INSERT INTO notifications (external_complaint_id,title,message,type,is_read,created_at) VALUES ($eid,'$title','$msg','$type',0,'$now')"); }
                header("Location: view_complaints.php?success=validated"); exit;
            }
        }
    }
        if(isset($_POST['reject_complaint']) && !$is_case){
                $conn->query("UPDATE COMPLAINT_INFO SET Status='Rejected' WHERE Complaint_ID=$complaint_id");
                $cr=$conn->query("SELECT Resident_ID, External_Complainant_ID FROM COMPLAINT_INFO WHERE Complaint_ID=$complaint_id");
                if($cr&&$cr->num_rows>0){ $row=$cr->fetch_assoc(); $rid=$row['Resident_ID']; $eid=$row['External_Complainant_ID']; $title='Complaint Rejected'; $msg="Your complaint with ID #$complaint_id has been rejected after evaluation."; $now=date('Y-m-d H:i:s'); if(!empty($rid)) $conn->query("INSERT INTO notifications (resident_id,title,message,is_read,created_at) VALUES ($rid,'$title','$msg',0,'$now')"); elseif(!empty($eid)) $conn->query("INSERT INTO notifications (external_complaint_id,title,message,is_read,created_at) VALUES ($eid,'$title','$msg',0,'$now')"); }
                header("Location: view_complaints.php?success=rejected"); exit;
        }
}

// Respondent display list
$respondents=[];
if(!empty($complaint['Respondent_ID'])){
        $mr=$conn->query("SELECT First_Name, Last_Name FROM RESIDENT_INFO WHERE Resident_ID={$complaint['Respondent_ID']}");
        if($mr&&$mr->num_rows>0){ $r=$mr->fetch_assoc(); $respondents[]=$r['First_Name'].' '.$r['Last_Name']; }
}
$ar=$conn->query("SELECT r.First_Name,r.Last_Name FROM COMPLAINT_RESPONDENTS cr JOIN RESIDENT_INFO r ON cr.Respondent_ID=r.Resident_ID WHERE cr.Complaint_ID=$complaint_id");
if($ar&&$ar->num_rows>0){ while($r=$ar->fetch_assoc()){ $respondents[]=$r['First_Name'].' '.$r['Last_Name']; }}
$respondent_names = $respondents ? implode(', ',$respondents) : 'N/A';

$complainant_name = !empty($complaint['Res_First_Name']) ? $complaint['Res_First_Name'].' '.$complaint['Res_Last_Name'] : (!empty($complaint['Ext_First_Name']) ? $complaint['Ext_First_Name'].' '.$complaint['Ext_Last_Name'] : 'Unknown');

// Build attachments array if Attachment_Path column exists
$attachments = [];
if(array_key_exists('Attachment_Path', $complaint) && !empty($complaint['Attachment_Path'])){
    $raw = $complaint['Attachment_Path'];
    // Paths stored as semicolon-separated
    $parts = array_filter(array_map('trim', explode(';', $raw)), function($p){ return $p !== ''; });
    foreach($parts as $p){
        $clean = str_replace('..','', $p); // basic traversal guard
        $clean = str_replace('\\', '/', $clean);
        $clean = ltrim($clean, '/');
        // Encode each segment for safe URL usage
        $encoded = implode('/', array_map('rawurlencode', explode('/', $clean)));
        $attachments[] = [
            'raw' => $clean,
            'url' => $encoded,
            'is_image' => (bool)preg_match('/\.(jpe?g|png|gif|webp)$/i', $clean),
            'is_pdf' => (bool)preg_match('/\.pdf$/i', $clean)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Complaint • Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:{50:'#f0f7ff',100:'#e0effe',200:'#bae2fd',300:'#7cccfd',400:'#36b3f9',500:'#0c9ced',600:'#0281d4',700:'#026aad',800:'#065a8f',900:'#0a4b76'}},boxShadow:{glow:'0 0 0 1px rgba(12,156,237,.08),0 4px 20px -2px rgba(6,90,143,.18)'},animation:{'fade-in':'fadeIn .4s ease-out'},keyframes:{fadeIn:{'0%':{opacity:0},'100%':{opacity:1}}}}}};</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <style>
        .glass{background:linear-gradient(140deg,rgba(255,255,255,.92),rgba(255,255,255,.68));backdrop-filter:blur(14px) saturate(140%);-webkit-backdrop-filter:blur(14px) saturate(140%);} 
        .field-label{font-size:11px;letter-spacing:.05em;font-weight:600;text-transform:uppercase;color:#64748b;} 
        textarea[disabled],input[disabled]{background-color:rgba(148,163,184,.15)!important;cursor:not-allowed;}
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-primary-50 via-white to-primary-100 min-h-screen text-gray-800 relative overflow-x-hidden">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-32 -left-24 w-96 h-96 bg-primary-200 opacity-30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -right-24 w-[30rem] h-[30rem] bg-primary-300 opacity-20 rounded-full blur-3xl"></div>
    </div>
    <?php include '../includes/barangay_official_sec_nav.php'; ?>
    <?php include 'sidebar_.php'; ?>
    <?php $status=strtoupper(trim($complaint['Status'])); $statusStyles=['PENDING'=>'bg-amber-50 text-amber-600 border border-amber-200','IN CASE'=>'bg-sky-50 text-sky-600 border border-sky-200','REJECTED'=>'bg-rose-50 text-rose-600 border border-rose-200','RESOLVED'=>'bg-emerald-50 text-emerald-600 border border-emerald-200']; $statusClass=$statusStyles[$status]??'bg-gray-100 text-gray-600 border border-gray-200'; ?>
    <main class="relative z-10 max-w-5xl mx-auto px-4 md:px-8 pt-10 pb-24 animate-fade-in">
        <?php if(!empty($error)): ?>
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-sm shadow-sm"><i class="fa fa-circle-exclamation mr-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="mb-8 flex items-center gap-3">
            <a href="view_complaints.php" class="group inline-flex items-center text-sm font-medium text-primary-700 hover:text-primary-900 transition">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-white/70 shadow ring-1 ring-primary-100 group-hover:bg-primary-50"><i class="fa fa-arrow-left"></i></span>
                <span class="ml-2">Back to Complaints</span>
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
                        <?php if($is_case): ?><span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm"><i class="fa fa-gavel"></i> Case Opened</span><?php endif; ?>
                        <?php if($is_case && !empty($existing_case_type)): ?><span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full bg-primary-50 text-primary-700 border border-primary-200 shadow-sm"><i class="fa fa-tags"></i> Type: <?= htmlspecialchars($existing_case_type) ?></span><?php endif; ?>
                        <?php if($is_rejected): ?><span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full bg-rose-50 text-rose-600 border border-rose-200 shadow-sm"><i class="fa fa-ban"></i> Rejected</span><?php endif; ?>
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
                    <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                        <?php if($editing && !$is_case && !$is_rejected): ?>
                            <form id="editForm" method="POST" class="space-y-4">
                                <div id="respondents-container" class="space-y-2">
                                    <?php foreach($respondents as $n): ?>
                                        <input type="text" name="respondents[]" value="<?= htmlspecialchars($n) ?>" class="w-full px-3 py-2 rounded-lg border border-gray-300 respondent-input focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80" />
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" onclick="addRespondent()" class="mt-2 inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-700 font-medium"><i class="fa fa-plus text-xs"></i> Add Respondent</button>
                        <?php else: ?>
                            <p class="text-gray-700 leading-relaxed"><?= htmlspecialchars($respondent_names) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Complaint Information</h2>
                    <div class="grid gap-5 md:grid-cols-2">
                        
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm md:col-span-2">
                            <p class="field-label mb-1">Details</p>
                            <?php if($editing && !$is_case && !$is_rejected): ?>
                                <textarea name="complaint_details" rows="5" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80"><?= htmlspecialchars($complaint['Complaint_Details']) ?></textarea>
                            <?php else: ?>
                                <p class="text-gray-700 leading-relaxed whitespace-pre-line"><?= nl2br(htmlspecialchars($complaint['Complaint_Details'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="field-label mb-1">Date Filed</p>
                            <p class="font-semibold text-gray-800"><?= date('F d, Y', strtotime($complaint['Date_Filed'])) ?></p>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="field-label mb-1">Status</p>
                            <p class="inline-flex items-center gap-2 font-semibold <?= $status==='REJECTED' ? 'text-rose-600' : ($status==='PENDING' ? 'text-amber-600' : ($status==='IN CASE' ? 'text-sky-600':'text-emerald-600')) ?>"><i class="fa fa-circle text-[8px]"></i> <?= htmlspecialchars($complaint['Status']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="pt-4 border-t border-dashed border-primary-200/60 flex flex-col gap-4">
                    <?php if(!empty($attachments)): ?>
                    <div>
                        <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Attachments</h2>
                        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                            <?php foreach($attachments as $att): ?>
                                <div class="group relative rounded-xl border bg-white/70 border-gray-200 hover:border-primary-300 hover:shadow-glow transition overflow-hidden">
                                    <div class="aspect-video w-full bg-gray-100 flex items-center justify-center overflow-hidden">
                                        <?php if($att['is_image']): ?>
                                            <img src="../<?= htmlspecialchars($att['url']) ?>" alt="Attachment" class="w-full h-full object-cover object-center group-hover:scale-105 transition" />
                                        <?php elseif($att['is_pdf']): ?>
                                            <div class="flex flex-col items-center justify-center text-primary-600 text-sm font-medium">
                                                <i class="fa fa-file-pdf text-3xl mb-1"></i>
                                                PDF File
                                            </div>
                                        <?php else: ?>
                                            <div class="flex flex-col items-center justify-center text-primary-600 text-sm font-medium">
                                                <i class="fa fa-paperclip text-3xl mb-1"></i>
                                                File
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/45 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <div class="flex gap-2">
                                            <?php if($att['is_image']): ?>
                                                <button type="button" onclick="previewImage('../<?= htmlspecialchars($att['url']) ?>')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/90 hover:bg-white text-primary-700 text-xs font-medium shadow-sm"><i class="fa fa-eye"></i> View</button>
                                            <?php endif; ?>
                                            <a href="../<?= htmlspecialchars($att['url']) ?>" download class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium shadow-sm"><i class="fa fa-download"></i> Download</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if(!$is_case && !$is_rejected): ?>
                    <div class="rounded-xl border bg-white/70 border-gray-200 p-4 shadow-sm">
                        <p class="field-label mb-2">Validation Decision</p>
                        <div class="flex flex-wrap items-center gap-3">
                            <button id="btnValidate" type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow text-sm font-medium transition"><i class="fa fa-check"></i> Validate</button>
                            <form method="POST" class="inline-flex" onsubmit="return confirm('Reject this complaint? This action cannot be undone.');">
                                <button type="submit" name="reject_complaint" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow text-sm font-medium transition"><i class="fa fa-ban"></i> Reject</button>
                            </form>
                        </div>
                        <form id="convertForm" method="POST" class="mt-3 flex flex-col sm:flex-row sm:items-center gap-3 hidden" onsubmit="return confirm('Convert this complaint into a case?');">
                            <input type="hidden" name="validate_decision" value="yes" />
                            <div class="sm:inline-flex items-center gap-2">
                                <select name="case_type" id="caseTypeSelect" required class="px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80">
                                    <option value="">Select Case Type</option>
                                    <option value="Civil">Civil</option>
                                    <option value="Criminal">Criminal</option>
                                    <option value="Blotter">Blotter</option>
                                </select>
                            </div>
                            <button id="btnConvert" type="submit" name="validate_case" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow text-sm font-medium transition disabled:opacity-60 disabled:cursor-not-allowed" disabled><i class="fa fa-gavel"></i> Convert to Case</button>
                        </form>
                    </div>
                    <?php endif; ?>
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap gap-2">
                        <a href="view_complaints.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/80 hover:bg-white text-primary-700 border border-primary-200 shadow-sm text-sm font-medium transition"><i class="fa fa-arrow-left"></i> Back</a>
                        <?php if(!$is_case && !$editing && !$is_rejected): ?>
                            <a href="?id=<?= $complaint_id ?>&edit=1" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow text-sm font-medium transition"><i class="fa fa-pen"></i> Validate / Edit</a>
                        <?php endif; ?>
                        <?php if($editing && !$is_case && !$is_rejected): ?>
                            <button type="submit" name="update_complaint" form="editForm" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white shadow text-sm font-medium transition"><i class="fa fa-save"></i> Save Changes</button>
                        <?php endif; ?>
                        <?php if(!$is_case && !$editing && !$is_rejected): ?>
                            <!-- Rejection handled above in Validation Decision block -->
                        <?php endif; ?>
                        <?php if($is_case): ?>
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium cursor-not-allowed opacity-80"><i class="fa fa-check"></i> Already a Case</span>
                        <?php endif; ?>
                        <?php if($is_rejected): ?>
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-rose-600 text-white text-sm font-medium cursor-not-allowed opacity-80"><i class="fa fa-ban"></i> Complaint Rejected</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if($editing && !$is_case && !$is_rejected): ?></form><?php endif; ?>
            </div>
        </section>
    </main>
    <script>
        function addRespondent(){
            const c=document.getElementById('respondents-container'); if(!c) return;
            const i=document.createElement('input'); i.type='text'; i.name='respondents[]'; i.className='w-full px-3 py-2 rounded-lg border border-gray-300 respondent-input focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80';
            c.appendChild(i); setupAutocomplete();
        }
        function setupAutocomplete(){ if(typeof $==='undefined'||!$.fn.autocomplete) return; $('.respondent-input').autocomplete({source:'search_residents.php',minLength:1}); }
        function previewImage(src){
            const modal=document.getElementById('imgPreviewModal');
            const img=document.getElementById('imgPreviewTag');
            if(!modal||!img) return;
            img.src=src;
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        function closePreview(){
            const modal=document.getElementById('imgPreviewModal');
            if(!modal) return;
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        $(document).ready(function(){
            setupAutocomplete();
            const btnValidate = document.getElementById('btnValidate');
            const convertForm = document.getElementById('convertForm');
            const caseTypeSelect = document.getElementById('caseTypeSelect');
            const btnConvert = document.getElementById('btnConvert');
            if(btnValidate && convertForm){
                btnValidate.addEventListener('click', function(){
                    convertForm.classList.remove('hidden');
                    caseTypeSelect?.focus();
                    if(btnConvert) btnConvert.disabled = (caseTypeSelect?.value==='');
                });
            }
            if(caseTypeSelect && btnConvert){
                caseTypeSelect.addEventListener('change', function(){
                    btnConvert.disabled = (this.value==='');
                });
            }
        });
    </script>
    <div id="imgPreviewModal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-6">
        <div class="relative max-w-4xl w-full">
            <button onclick="closePreview()" class="absolute -top-4 -right-4 w-10 h-10 rounded-full bg-white text-gray-700 flex items-center justify-center shadow-lg hover:bg-primary-600 hover:text-white transition"><i class="fa fa-xmark text-lg"></i></button>
            <div class="bg-white rounded-2xl overflow-hidden shadow-glow ring-1 ring-primary-200/40">
                <img id="imgPreviewTag" src="" alt="Preview" class="w-full max-h-[80vh] object-contain bg-black" />
            </div>
        </div>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
