<?php
session_start();
include '../server/server.php';

if(!isset($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
$residentId = (int)$_SESSION['user_id'];
$caseId = isset($_GET['case_id']) ? (int)$_GET['case_id'] : 0;
if($caseId<=0){ header('Location: view_cases.php'); exit; }

// Fetch case + complaint ensuring ownership
$stmt = $conn->prepare("SELECT ci.Case_ID, ci.Case_Status, ci.Date_Opened, ci.Complaint_ID,
                               co.Complaint_Title, /* kept for legacy references */
                               co.case_type,       /* new field for resident display */
                               co.Complaint_Details, co.Status AS Complaint_Status, co.Resident_ID, co.Attachment_Path
                        FROM case_info ci
                        INNER JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID
                        WHERE ci.Case_ID = ? AND co.Resident_ID = ? LIMIT 1");
$stmt->bind_param('ii',$caseId,$residentId);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows===0){ $stmt->close(); header('Location: view_cases.php?error=notfound'); exit; }
$case = $res->fetch_assoc();
$stmt->close();

// Fetch all hearings for this case
$hearings = [];
if($hStmt = $conn->prepare("SELECT hearingTitle, hearingDateTime, place, participant, remarks FROM schedule_list WHERE Case_ID = ? ORDER BY hearingDateTime ASC")){
    $hStmt->bind_param('i',$caseId);
    $hStmt->execute();
    $hRes = $hStmt->get_result();
    while($row=$hRes->fetch_assoc()){ $hearings[]=$row; }
    $hStmt->close();
}

function relative_time($date){
    if(!$date) return '';
    $ts = strtotime($date); $diff = time()-$ts; if($diff<60) return 'just now';
    $units=[31536000=>'year',2592000=>'month',604800=>'week',86400=>'day',3600=>'hour',60=>'minute'];
    foreach($units as $secs=>$label){ if($diff>=$secs){ $v=floor($diff/$secs); return $v.' '.$label.($v>1?'s':'').' ago'; } }
    return 'just now';
}

$status = $case['Case_Status'];
$statusMap = [
    'open' => ['badge'=>'bg-blue-50 text-blue-700 border-blue-200','gradient'=>'from-blue-600 to-blue-500','note'=>'This case is currently active and undergoing barangay mediation or proceedings.'],
    'pending' => ['badge'=>'bg-amber-50 text-amber-700 border-amber-200','gradient'=>'from-amber-600 to-amber-500','note'=>'This case is awaiting the next scheduled action or validation.'],
    'resolved' => ['badge'=>'bg-green-50 text-green-700 border-green-200','gradient'=>'from-green-600 to-green-500','note'=>'This case has been successfully resolved.'],
    'closed' => ['badge'=>'bg-gray-50 text-gray-700 border-gray-200','gradient'=>'from-gray-600 to-gray-500','note'=>'This case has been formally closed.'],
];
$style = $statusMap[strtolower($status)] ?? ['badge'=>'bg-gray-50 text-gray-700 border-gray-200','gradient'=>'from-sky-600 to-sky-500','note'=>'Status information is limited for this case.'];

$caseDisplayId = 'CASE-'.str_pad($case['Case_ID'],3,'0',STR_PAD_LEFT);
$complaintDisplayId = 'COMP-'.str_pad($case['Complaint_ID'],3,'0',STR_PAD_LEFT);
$caseTypeRaw = trim($case['case_type'] ?? '');
$caseTypeDisplay = $caseTypeRaw !== '' ? ucwords($caseTypeRaw) : 'Unspecified Case Type';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Case Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme:{ extend:{ colors:{ primary:{50:'#f0f7ff',100:'#e0effe',200:'#bae2fd',300:'#7cccfd',400:'#36b3f9',500:'#0c9ced',600:'#0281d4',700:'#026aad',800:'#065a8f',900:'#0a4b76'} }, animation:{'float':'float 10s ease-in-out infinite','fade-in':'fadeIn .4s ease-out'}, keyframes:{ float:{'0%,100%':{transform:'translateY(0)'},'50%':{transform:'translateY(-16px)'}}, fadeIn:{'0%':{opacity:0},'100%':{opacity:1}} } } } };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />
    <style>
        .glass { background: linear-gradient(135deg, rgba(255,255,255,0.85), rgba(255,255,255,0.62)); backdrop-filter: blur(12px) saturate(140%); -webkit-backdrop-filter: blur(12px) saturate(140%); }
    </style>
</head>
<body class="bg-gray-50 font-sans relative overflow-x-hidden min-h-screen">
    <!-- Orbs Background -->
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[480px] h-[480px] rounded-full bg-blue-200/40 blur-3xl animate-float"></div>
        <div class="absolute top-1/3 -right-52 w-[560px] h-[560px] rounded-full bg-cyan-200/40 blur-[160px] animate-[float_18s_ease-in-out_infinite]"></div>
        <div class="absolute -bottom-52 left-1/3 w-[520px] h-[520px] rounded-full bg-indigo-200/30 blur-3xl animate-[float_16s_ease-in-out_infinite]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[900px] rounded-full bg-gradient-to-br from-blue-50 via-white to-cyan-50 opacity-70 blur-[200px]"></div>
    </div>

    <?php include '../includes/resident_nav.php'; ?>

    <main class="relative z-10 max-w-6xl mx-auto px-4 pt-10 pb-24 animate-fade-in">
        <!-- Back -->
        <div class="mb-6 flex items-center gap-3">
            <a href="view_cases.php" class="inline-flex items-center gap-2 text-sm text-primary-700 font-medium hover:text-primary-900 transition">
                <span class="w-8 h-8 rounded-lg bg-white/70 backdrop-blur flex items-center justify-center shadow"><i class="fa fa-arrow-left"></i></span>
                <span>Back to Cases</span>
            </a>
        </div>

        <section class="relative glass rounded-2xl p-8 md:p-10 border border-white/60 shadow-sm overflow-hidden">
            <div class="absolute -top-10 -right-10 w-48 h-48 bg-gradient-to-br from-primary-100 to-primary-300 rounded-full opacity-40 blur-2xl"></div>
            <header class="relative flex flex-col md:flex-row md:items-start gap-8 mb-6">
                <div class="flex items-center">
                    <div class="w-20 h-20 rounded-2xl bg-white/60 backdrop-blur flex items-center justify-center ring-4 ring-primary-100 shadow-inner">
                        <i class="fa fa-gavel text-primary-600 text-3xl"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-gray-800 flex flex-wrap items-center gap-3">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-700 to-primary-500">Case Details</span>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full border <?= $style['badge'] ?>"><?= htmlspecialchars($status) ?></span>
                    </h1>
                    <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-600">
                        <span class="inline-flex items-center gap-1"><i class="fa fa-hashtag text-primary-500"></i> <?= htmlspecialchars($caseDisplayId) ?></span>
                        <span class="inline-flex items-center gap-1"><i class="fa fa-file-alt text-primary-500"></i> <?= htmlspecialchars($complaintDisplayId) ?></span>
                        <span class="inline-flex items-center gap-1"><i class="fa fa-calendar text-primary-500"></i> <?= date('F d, Y', strtotime($case['Date_Opened'])) ?></span>
                        <span class="inline-flex items-center gap-1"><i class="fa fa-hourglass-half text-primary-500"></i> <?= relative_time($case['Date_Opened']) ?></span>
                    </div>
                </div>
            </header>

            <div class="grid gap-10 md:grid-cols-5">
                <!-- Main Column -->
                <div class="md:col-span-3 space-y-10">
                    <div>
                        <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Linked Complaint</h2>
                        <div class="relative rounded-xl border border-primary-100/70 bg-white/80 p-5 shadow-sm">
                            <div class="absolute -top-3 left-5 px-2 text-[10px] font-semibold tracking-wide uppercase bg-primary-100 text-primary-700 rounded-full">Complaint</div>
                            <p class="font-medium text-gray-800 leading-snug mb-2" title="<?= htmlspecialchars($caseTypeDisplay) ?>"><?= htmlspecialchars($caseTypeDisplay) ?></p>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line text-sm">
                                <?= nl2br(htmlspecialchars($case['Complaint_Details'] ?: 'No description provided.')) ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Status Notes</h2>
                        <div class="rounded-xl border border-primary-100/70 bg-white/80 p-5 shadow-sm leading-relaxed text-gray-700">
                            <?= htmlspecialchars($style['note']) ?>
                        </div>
                    </div>
                    <?php
                        $attachmentsRaw = $case['Attachment_Path'] ?? '';
                        $gallery = [];
                        if($attachmentsRaw){
                            $parts = array_filter(array_map('trim', explode(';',$attachmentsRaw)));
                            foreach($parts as $p){
                                if(!$p) continue;
                                $clean = str_replace('\\','/',$p);
                                $clean = preg_replace('#\.{2,}#','',$clean);
                                $clean = ltrim($clean,'/');
                                if($clean==='') continue;
                                $ext = strtolower(pathinfo($clean, PATHINFO_EXTENSION));
                                $type = in_array($ext,['png','jpg','jpeg','gif','webp','bmp']) ? 'image' : ($ext==='pdf' ? 'pdf' : 'file');
                                $segments = array_map('rawurlencode', explode('/', $clean));
                                $encoded = implode('/', $segments);
                                $gallery[] = ['raw'=>$clean,'encoded'=>$encoded,'ext'=>$ext,'type'=>$type];
                            }
                        }
                    ?>
                    <?php if(!empty($gallery)): ?>
                    <div>
                        <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-4 flex items-center gap-2"><i class="fa fa-paperclip text-primary-500"></i> Complaint Attachments</h2>
                        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            <?php foreach($gallery as $g): ?>
                            <div class="group relative rounded-xl border bg-white/80 border-gray-200 hover:border-primary-300 hover:shadow-glow transition overflow-hidden">
                                <div class="aspect-video w-full bg-gray-100 flex items-center justify-center overflow-hidden">
                                    <?php if($g['type']==='image'): ?>
                                        <img src="../<?= htmlspecialchars($g['encoded']) ?>" alt="Attachment" class="w-full h-full object-cover object-center group-hover:scale-105 transition" onerror="this.onerror=null;this.src='https://via.placeholder.com/300x180?text=Missing';" />
                                    <?php elseif($g['type']==='pdf'): ?>
                                        <div class="flex flex-col items-center justify-center text-primary-600 text-sm font-medium">
                                            <i class="fa fa-file-pdf text-3xl mb-1"></i>
                                            PDF File
                                        </div>
                                    <?php else: ?>
                                        <div class="flex flex-col items-center justify-center text-primary-600 text-xs font-medium p-2 text-center">
                                            <i class="fa fa-paperclip text-2xl mb-1"></i>
                                            <span class="break-all leading-tight">File</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/45 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <div class="flex gap-2">
                                        <?php if($g['type']==='image'): ?>
                                            <button type="button" onclick="previewImage('../<?= htmlspecialchars($g['encoded']) ?>')" aria-label="View image" class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-white/90 hover:bg-white text-primary-700 text-sm font-medium shadow-sm"><i class="fa fa-eye"></i></button>
                                        <?php endif; ?>
                                        <a href="../<?= htmlspecialchars($g['encoded']) ?>" download aria-label="Download file" class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium shadow-sm"><i class="fa fa-download"></i></a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div>
                        <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-4 flex items-center gap-2"><i class="fa fa-scale-balanced text-primary-500"></i> Hearings & Schedule</h2>
                        <?php if(empty($hearings)): ?>
                            <div class="rounded-xl border border-dashed border-primary-100/70 bg-white/70 p-5 text-sm text-gray-500">No hearings have been scheduled yet.</div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach($hearings as $h): ?>
                                <div class="group relative rounded-xl border border-gray-200 bg-white/70 p-5 shadow-sm hover:border-primary-200 transition">
                                    <div class="flex items-center justify-between gap-3 flex-wrap">
                                        <div class="font-medium text-gray-800 flex items-center gap-2"><i class="fa fa-calendar-day text-primary-500"></i> <?= htmlspecialchars($h['hearingTitle'] ?: 'Hearing') ?></div>
                                        <span class="text-xs px-2 py-1 rounded-md bg-primary-50 text-primary-600 border border-primary-100 font-medium">
                                            <?= $h['hearingDateTime'] ? date('M d, Y • g:i A', strtotime($h['hearingDateTime'])) : 'TBD' ?>
                                        </span>
                                    </div>
                                    <div class="mt-3 grid sm:grid-cols-3 gap-4 text-xs text-gray-600">
                                        <div><span class="font-semibold text-gray-500 uppercase block mb-1">Venue</span><?= htmlspecialchars($h['place'] ?: 'N/A') ?></div>
                                        <div><span class="font-semibold text-gray-500 uppercase block mb-1">Participants</span><?= htmlspecialchars($h['participant'] ?: 'N/A') ?></div>
                                        <div><span class="font-semibold text-gray-500 uppercase block mb-1">Remarks</span><?= htmlspecialchars($h['remarks'] ?: 'N/A') ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Sidebar -->
                <aside class="md:col-span-2 space-y-6">
                    <div class="rounded-2xl border border-primary-100 bg-white/80 p-6 shadow-sm">
                        <h3 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-4 flex items-center gap-2"><i class="fa fa-circle-info text-primary-500"></i> Metadata</h3>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li class="flex items-center gap-2"><i class="fa fa-hashtag text-primary-500"></i> <span class="font-medium">Case ID:</span> <?= htmlspecialchars($caseDisplayId) ?></li>
                            <li class="flex items-center gap-2"><i class="fa fa-file-alt text-primary-500"></i> <span class="font-medium">Complaint ID:</span> <?= htmlspecialchars($complaintDisplayId) ?></li>
                            <li class="flex items-center gap-2"><i class="fa fa-calendar text-primary-500"></i> <span class="font-medium">Opened:</span> <?= date('F d, Y', strtotime($case['Date_Opened'])) ?></li>
                            <li class="flex items-center gap-2"><i class="fa fa-clock-rotate-left text-primary-500"></i> <span class="font-medium">Relative:</span> <?= relative_time($case['Date_Opened']) ?></li>
                            <li class="flex items-center gap-2"><i class="fa fa-tag text-primary-500"></i> <span class="font-medium">Status:</span> <?= htmlspecialchars($status) ?></li>
                        </ul>
                    </div>
                    <div class="rounded-2xl bg-gradient-to-br <?= ''.$style['gradient'] ?> text-white p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center"><i class="fa fa-gavel text-white text-xl"></i></div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-white/70 font-semibold">Current Status</p>
                                <p class="text-base font-medium"><?= htmlspecialchars($status) ?></p>
                            </div>
                        </div>
                        <p class="text-sm text-white/90 leading-relaxed">This page summarizes your case progress and schedule activities. Check back for hearings and updates.</p>
                        <div class="mt-5 flex flex-col gap-3">
                            <a href="view_cases.php" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm font-medium transition"><i class="fa fa-arrow-left"></i> Back to Cases</a>
                            <a href="view_complaint_details.php?id=<?= $case['Complaint_ID'] ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm font-medium transition"><i class="fa fa-file-alt"></i> View Complaint</a>
                        </div>
                    </div>
                </aside>
            </div>
            <div class="mt-12 pt-6 border-t border-dashed border-primary-200 flex items-center justify-between flex-wrap gap-4">
                <div class="text-xs text-gray-500">Generated: <?= date('F d, Y h:i A') ?></div>
                <div class="flex gap-2">
                    <a href="view_cases.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/80 hover:bg-white text-primary-700 border border-primary-200 shadow-sm text-sm font-medium transition"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </section>
    </main>
    <?php include("../chatbot/bpamis_case_assistant.php"); ?>
        <script>
        function previewImage(src){
            var modal=document.getElementById('imgPreviewModal');
            var img=document.getElementById('imgPreviewTag');
            if(!modal||!img) return; img.src=src; modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden');
        }
        function closePreview(){
            var modal=document.getElementById('imgPreviewModal'); if(!modal) return; modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden');
        }
        </script>
        <div id="imgPreviewModal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-6">
            <div class="relative max-w-4xl w-full">
                <button onclick="closePreview()" class="absolute -top-4 -right-4 w-10 h-10 rounded-full bg-white text-gray-700 flex items-center justify-center shadow-lg hover:bg-primary-600 hover:text-white transition"><i class="fa fa-xmark text-lg"></i></button>
                <div class="bg-white rounded-2xl overflow-hidden shadow-glow ring-1 ring-primary-200/40">
                    <img id="imgPreviewTag" src="" alt="Preview" class="w-full max-h-[80vh] object-contain bg-black" />
                </div>
            </div>
        </div>
</body>
</html>
