<?php
session_start(); 


$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
include '../server/server.php'; 

$sql = "
SELECT 
    ci.Complaint_ID, 
    ci.Complaint_Title, 
    ci.Complaint_Details, 
    ci.Date_Filed, 
    ci.Status,
    n.created_at AS notification_created_at
FROM 
    complaint_info ci
LEFT JOIN (
    SELECT n1.*
    FROM notifications n1
    INNER JOIN (
        SELECT related_id, MAX(created_at) AS max_created
        FROM notifications 
        WHERE type = 'complaint'
        GROUP BY related_id
    ) n2 ON n1.related_id = n2.related_id AND n1.created_at = n2.max_created
    WHERE n1.type = 'complaint'
) n ON ci.Complaint_ID = n.related_id
WHERE 
    ci.resident_id = ?
ORDER BY 
    n.created_at DESC
";



$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$complaints = [];
while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
}
// Resolved Cases
$resolvedCases = $conn->query("
    SELECT COUNT(*) AS total 
    FROM case_info ci 
    JOIN complaint_info co ON ci.Complaint_ID = co.Complaint_ID 
    WHERE co.Resident_ID = $user_id AND Status = 'Resolved'
")->fetch_assoc()['total'] ?? 0;
$pendingComplaints = $conn->query("
    SELECT COUNT(*) AS total 
    FROM complaint_info 
    WHERE Resident_ID = $user_id AND Status = 'Pending'
")->fetch_assoc()['total'] ?? 0;
$rejectedComplaints = $conn->query("
    SELECT COUNT(*) AS total 
    FROM complaint_info 
    WHERE Resident_ID = $user_id AND Status = 'Rejected'
")->fetch_assoc()['total'] ?? 0;


$stmt->close();
$conn->close();

// We now load all complaints for client-side filtering (no slicing)
$totalComplaints = count($complaints);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaints</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
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
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        </style>
    <style>
        .status-badge {
            transition: all 0.3s ease;
        }
        .table-row {
            transition: all 0.2s ease;
        }
        .table-row:hover {
            background-color: #f0f7ff;
        }
        .c-chip.active {
            box-shadow: 0 0 0 2px rgba(59,130,246,.3);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans relative overflow-x-hidden">
    <?php include_once('../includes/resident_nav.php'); ?>

    <!-- Global Blue Blush Background Orbs -->
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[480px] h-[480px] rounded-full bg-blue-200/40 blur-3xl animate-[float_14s_ease-in-out_infinite]"></div>
        <div class="absolute top-1/3 -right-52 w-[560px] h-[560px] rounded-full bg-cyan-200/40 blur-[160px] animate-[float_18s_ease-in-out_infinite]"></div>
        <div class="absolute -bottom-52 left-1/3 w-[520px] h-[520px] rounded-full bg-indigo-200/30 blur-3xl animate-[float_16s_ease-in-out_infinite]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[900px] rounded-full bg-gradient-to-br from-blue-50 via-white to-cyan-50 opacity-70 blur-[200px]"></div>
    </div>
    <?php 
        // Additional count for 'In Case'
        $inCaseCount = 0; 
        foreach($complaints as $c){ if(strtolower($c['Status'])==='in case'){ $inCaseCount++; } }
    ?>
    <!-- Premium Hero Header -->
    <div class="w-full mt-8 px-4">
        <div class="relative gradient-bg rounded-2xl shadow-sm p-8 md:p-10 overflow-hidden max-w-screen-2xl mx-auto px-5 pt-10 relative">
            <div class="absolute top-0 right-0 w-72 h-72 bg-primary-100 rounded-full -mr-28 -mt-28 opacity-70 animate-[float_10s_ease-in-out_infinite]"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-primary-200 rounded-full -ml-16 -mb-16 opacity-60 animate-[float_7s_ease-in-out_infinite]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-br from-primary-50 via-white to-primary-100 opacity-30 blur-3xl rounded-full"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
                <div class="max-w-2xl">
                    <h1 class="text-3xl md:text-4xl font-light text-primary-900 tracking-tight">Your <span class="font-semibold">Complaints</span></h1>
                    <p class="mt-4 text-gray-600 leading-relaxed">Review status, track updates, and monitor resolutions. Use the smart filters below to narrow results instantly.</p>
                    <div class="mt-5 flex flex-wrap gap-3 text-xs text-primary-700/80 font-medium">
                        <span class="px-3 py-1.5 rounded-full bg-white/70 backdrop-blur border border-primary-100 shadow-sm flex items-center gap-1"><i class="fa-solid fa-layer-group text-primary-500"></i> Organized Timeline</span>
                        <span class="px-3 py-1.5 rounded-full bg-white/70 backdrop-blur border border-primary-100 shadow-sm flex items-center gap-1"><i class="fa-solid fa-magnifying-glass text-primary-500"></i> Smart Search</span>
                        <span class="px-3 py-1.5 rounded-full bg-white/70 backdrop-blur border border-primary-100 shadow-sm flex items-center gap-1"><i class="fa-solid fa-chart-line text-primary-500"></i> Quick Insights</span>
                    </div>
                </div>
                <div class="hidden md:flex flex-col gap-3 min-w-[260px]">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex flex-col items-center bg-white/80 backdrop-blur rounded-xl px-3 py-3 border border-amber-100 shadow-sm"><span class="text-[10px] uppercase tracking-wide text-amber-600 font-semibold">Pending</span><span class="mt-1 text-lg font-semibold text-amber-700"><?= $pendingComplaints ?></span></div>
                        <div class="flex flex-col items-center bg-white/80 backdrop-blur rounded-xl px-3 py-3 border border-green-100 shadow-sm"><span class="text-[10px] uppercase tracking-wide text-green-600 font-semibold">Resolved</span><span class="mt-1 text-lg font-semibold text-green-700"><?= $resolvedCases ?></span></div>
                        <div class="flex flex-col items-center bg-white/80 backdrop-blur rounded-xl px-3 py-3 border border-red-100 shadow-sm"><span class="text-[10px] uppercase tracking-wide text-red-600 font-semibold">Rejected</span><span class="mt-1 text-lg font-semibold text-red-700"><?= $rejectedComplaints ?></span></div>
                        <div class="flex flex-col items-center bg-white/80 backdrop-blur rounded-xl px-3 py-3 border border-blue-100 shadow-sm"><span class="text-[10px] uppercase tracking-wide text-blue-600 font-semibold">In Case</span><span class="mt-1 text-lg font-semibold text-blue-700"><?= $inCaseCount ?></span></div>
                    </div>
                    <div class="text-[11px] text-primary-700/70 text-center">Status overview</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters & Search -->
    <div class="w-full mt-8 px-4">
        <div class="max-w-screen-2xl mx-auto">
            <div class="relative bg-white/90 backdrop-blur-sm border border-gray-100 rounded-2xl shadow-sm p-6 md:p-7 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-primary-50 to-primary-100 rounded-full opacity-70"></div>
                <div class="absolute -bottom-12 -left-12 w-40 h-40 bg-gradient-to-tr from-primary-50 to-primary-100 rounded-full opacity-60"></div>
                <div class="relative z-10 space-y-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-3 text-primary-700/80 text-sm font-medium">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-50/70 border border-primary-100"><i class="fa-solid fa-magnifying-glass text-primary-500"></i> Search & Filter</span>
                            <span class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-50/70 border border-primary-100"><i class="fa-solid fa-sliders text-primary-500"></i> Refine</span>
                        </div>
                        <a href="submit_complaint.php" class="group relative inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white text-sm font-semibold shadow-sm hover:shadow-md transition-all">
                            <i class="fa-solid fa-circle-plus text-white"></i>
                            <span>Submit Complaint</span>
                            <span class="absolute inset-0 rounded-xl ring-1 ring-inset ring-white/20"></span>
                        </a>
                    </div>
                    <!-- Status Chips -->
                    <div class="flex flex-wrap gap-2 pt-1" id="statusChips">
                        <button type="button" data-status="" class="c-chip active px-3 py-1.5 text-xs font-medium rounded-full bg-primary-600 text-white shadow-sm">All</button>
                        <button type="button" data-status="Pending" class="c-chip px-3 py-1.5 text-xs font-medium rounded-full bg-amber-50 text-amber-600 border border-amber-100 hover:bg-amber-100 transition">Pending</button>
                        <button type="button" data-status="Resolved" class="c-chip px-3 py-1.5 text-xs font-medium rounded-full bg-green-50 text-green-600 border border-green-100 hover:bg-green-100 transition">Resolved</button>
                        <button type="button" data-status="Rejected" class="c-chip px-3 py-1.5 text-xs font-medium rounded-full bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 transition">Rejected</button>
                        <button type="button" data-status="In Case" class="c-chip px-3 py-1.5 text-xs font-medium rounded-full bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 transition">In Case</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-5 relative group">
                            <input id="searchInput" type="text" placeholder="Search by ID, status or description..." class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200/80 bg-white/70 focus:ring-2 focus:ring-primary-200 focus:border-primary-400 placeholder:text-gray-400 text-sm transition" />
                            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-primary-400 group-focus-within:text-primary-500 transition"></i>
                        </div>
                        <div class="md:col-span-2 relative">
                            <select id="monthFilter" class="w-full pl-3 pr-8 py-3 rounded-xl border border-gray-200 bg-white/70 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 appearance-none">
                                <option value="">All Months</option>
                                <?php foreach(range(1,12) as $m): $mn=date('F',mktime(0,0,0,$m,1)); ?>
                                    <option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>"><?= $mn ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fa-solid fa-caret-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-primary-400"></i>
                        </div>
                        <div class="md:col-span-2 relative">
                            <select id="yearFilter" class="w-full pl-3 pr-8 py-3 rounded-xl border border-gray-200 bg-white/70 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 appearance-none">
                                <option value="">All Years</option>
                                <?php $cy=date('Y'); for($y=$cy;$y>=$cy-5;$y--): ?>
                                    <option value="<?= $y ?>"><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                            <i class="fa-solid fa-caret-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-primary-400"></i>
                        </div>
                        <div class="md:col-span-2 relative">
                            <select id="sortOrder" class="w-full pl-3 pr-8 py-3 rounded-xl border border-gray-200 bg-white/70 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 appearance-none">
                                <option value="desc">Newest First</option>
                                <option value="asc">Oldest First</option>
                            </select>
                            <i class="fa-solid fa-caret-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-primary-400"></i>
                        </div>
                        <div class="md:col-span-1 flex">
                            <button id="resetFilters" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-xl border border-primary-100 bg-primary-50/60 text-primary-600 text-sm font-medium hover:bg-primary-100 transition"><i class="fa-solid fa-rotate-left"></i><span class="hidden xl:inline">Reset</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaints List -->
    <div class="w-full mt-8 px-4 pb-16">
        <div class="max-w-screen-2xl mx-auto bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-4 md:p-6">
            <div id="complaintsContainer" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($complaints as $complaint): 
                    $status = $complaint['Status'];
                    $statusClass = match($status){
                        'Resolved' => 'text-green-700 bg-green-50 border border-green-200',
                        'Rejected' => 'text-red-700 bg-red-50 border border-red-200',
                        'Pending' => 'text-amber-700 bg-amber-50 border border-amber-200',
                        'In Case' => 'text-blue-700 bg-blue-50 border border-blue-200',
                        default => 'text-gray-700 bg-gray-50 border border-gray-200'
                    };
                    $fullDesc = $complaint['Complaint_Details'] ?? ''; $shortDesc = mb_strlen($fullDesc)>140? htmlspecialchars(mb_substr($fullDesc,0,137)).'…':htmlspecialchars($fullDesc);
                    $filedDate = !empty($complaint['notification_created_at']) ? $complaint['notification_created_at'] : $complaint['Date_Filed'];
                    // Resolution message
                    $resMsg = match(strtolower($status)){
                        'in case' => 'This complaint is under resolution by the Barangay Justice System.',
                        'rejected' => 'This complaint is not part of the Barangay Justice System.',
                        'pending' => 'This complaint is currently being processed.',
                        'resolved' => 'This complaint was successfully resolved by the barangay.',
                        default => 'No resolution information available.'
                    };
                    $searchBlob = strtolower(
                        'COMP-' . str_pad($complaint['Complaint_ID'],3,'0',STR_PAD_LEFT).' '.
                        ($complaint['Complaint_Title'] ?? '').' '.
                        $status.' '.
                        $fullDesc
                    );
                ?>
                <div class="complaint-card bg-white/80 backdrop-blur rounded-xl border border-gray-100 p-4 flex flex-col gap-3 hover:-translate-y-[2px] hover:shadow-md transition-all" 
                     data-status="<?= strtolower($status) ?>" 
                     data-date="<?= htmlspecialchars($filedDate) ?>" 
                     data-id-text="<?= 'COMP-' . str_pad($complaint['Complaint_ID'],3,'0',STR_PAD_LEFT) ?>" 
                     data-title="<?= htmlspecialchars(strtolower($complaint['Complaint_Title'])) ?>" 
                     data-desc="<?= htmlspecialchars(strtolower($fullDesc)) ?>" 
                     data-search="<?= htmlspecialchars($searchBlob) ?>">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex flex-col">
                            <span class="text-[11px] font-mono tracking-wide text-gray-500">
                                <?= 'COMP-' . str_pad($complaint['Complaint_ID'], 3, '0', STR_PAD_LEFT); ?>
                            </span>
                        </div>
                        <span class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold <?= $statusClass ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>
                    </div>
                    <div class="text-xs text-gray-500">Filed on: <?= date('F j, Y', strtotime($filedDate)) ?></div>
                    <p class="text-sm text-gray-600 leading-snug line-clamp-3" title="<?= htmlspecialchars($fullDesc) ?>" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                        <?= $shortDesc ?: '<span class=\'italic text-gray-400\'>No description provided.</span>' ?>
                    </p>
                    <div class="mt-auto pt-1 flex items-center justify-end">
                        <a href="view_complaint_details.php?id=<?= $complaint['Complaint_ID'] ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-primary-50 text-primary-600 text-xs font-medium hover:bg-primary-100 transition">
                            <i class="fas fa-eye text-primary-500"></i> View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div id="noResults" class="hidden mt-6 p-8 border-2 border-dashed border-primary-200 rounded-xl text-center text-primary-700 bg-primary-50/40">
                <i class="fa-solid fa-circle-exclamation text-primary-500 text-2xl mb-2"></i>
                <p class="font-medium">No complaints match your filters.</p>
                <p class="text-sm opacity-80">Try adjusting status, date filters, or clearing the search.</p>
            </div>
            <div class="mt-6 flex items-center justify-between text-xs text-gray-600 flex-col md:flex-row gap-3">
                <div id="visibleCount" class="px-2.5 py-1 rounded-full bg-primary-50 text-primary-600 border border-primary-100 font-medium">Showing <?= $totalComplaints ?> items</div>
                <div class="text-[11px] text-gray-400">Client-side filtering enabled</div>
            </div>
        </div>
    </div>
    <?php include("../chatbot/bpamis_case_assistant.php")?>
    <script>
    (function(){
        const cards=[...document.querySelectorAll('.complaint-card')];
        const statusChips=document.getElementById('statusChips')?document.querySelectorAll('#statusChips .c-chip'):[];
        const searchInput=document.getElementById('searchInput');
        const monthFilter=document.getElementById('monthFilter');
        const yearFilter=document.getElementById('yearFilter');
        const sortOrder=document.getElementById('sortOrder');
        const resetBtn=document.getElementById('resetFilters');
        const visibleCount=document.getElementById('visibleCount');
        const noResults=document.getElementById('noResults');

        function parseDate(ds){return new Date(ds.replace(' ','T'));}
        function apply(){
            const q=(searchInput?.value||'').toLowerCase();
            const active=document.querySelector('#statusChips .c-chip.active');
            const statusFilter=active?active.dataset.status.toLowerCase():'';
            const m=monthFilter?monthFilter.value:''; const y=yearFilter?yearFilter.value:'';
            let vis=[];
            cards.forEach(c=>{
                const status=c.dataset.status; // already lowercase
                const blob=c.dataset.search;
                const dstr=c.dataset.date || '';
                let keep=true;
                if(statusFilter && status!==statusFilter) keep=false;
                if(keep && q && !blob.includes(q)) keep=false;
                if(keep && (m||y)){
                    if(dstr){
                        const d=parseDate(dstr);
                          if(m && String(d.getMonth()+1).padStart(2,'0')!==m) keep=false;
                          if(y && String(d.getFullYear())!==y) keep=false;
                    } else keep=false;
                }
                c.classList.toggle('hidden',!keep);
                if(keep) vis.push(c);
            });
            // sort
            if(sortOrder){
                const o=sortOrder.value;
                vis.sort((a,b)=>{
                    const da=parseDate(a.dataset.date).getTime();
                    const db=parseDate(b.dataset.date).getTime();
                    return o==='desc'? db-da : da-db;
                });
                const cont=document.getElementById('complaintsContainer');
                vis.forEach(v=>cont.appendChild(v));
            }
            visibleCount.textContent='Showing '+vis.length+' item'+(vis.length!==1?'s':'');
            noResults.classList.toggle('hidden', vis.length!==0);
        }
        let timer; if(searchInput) searchInput.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(apply,200);});
        statusChips.forEach(ch=>ch.addEventListener('click',()=>{statusChips.forEach(c=>c.classList.remove('active','bg-primary-600','text-white')); if(ch.dataset.status===''){ch.classList.add('bg-primary-600','text-white');} ch.classList.add('active'); apply();}));
        [monthFilter,yearFilter,sortOrder].forEach(sel=> sel && sel.addEventListener('change',apply));
        if(resetBtn) resetBtn.addEventListener('click',()=>{ if(searchInput) searchInput.value=''; if(monthFilter) monthFilter.value=''; if(yearFilter) yearFilter.value=''; if(sortOrder) sortOrder.value='desc'; statusChips.forEach(c=>c.classList.remove('active','bg-primary-600','text-white')); const all=document.querySelector('#statusChips .c-chip[data-status=""]'); if(all){all.classList.add('active','bg-primary-600','text-white');} apply(); });
        apply();
    })();
    </script>
</body>
</html>
