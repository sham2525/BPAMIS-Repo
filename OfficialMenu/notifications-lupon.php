<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../server/server.php';

if (!isset($_SESSION['official_id'])) {
    header("Location: ../login.php");
    exit();
}

$luponId = $_SESSION['official_id'];

// Optional: fetch Lupon name for fallback matching
$luponName = '';
if ($stn = $conn->prepare("SELECT Name FROM barangay_officials WHERE Official_ID = ?")) {
        $stn->bind_param('i', $luponId);
        $stn->execute();
        $rn = $stn->get_result();
        if ($rn && $r = $rn->fetch_assoc()) { $luponName = trim((string)($r['Name'] ?? '')); }
        $stn->close();
}

// Fetch notifications only for this Lupon and of relevant types (primary by lupon_id)
$sql = "SELECT * FROM notifications 
                WHERE lupon_id = ? 
                    AND type IN ('Unverified', 'Hearing', 'Complaint', 'Case') 
                ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $luponId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Fallback: log error and run safe direct query (luponId is from session; cast to int)
    error_log("notifications-lupon prepare failed: " . $conn->error);
    $luponIdInt = (int)$luponId;
    $fallbackSql = "SELECT * FROM notifications WHERE lupon_id = $luponIdInt AND type IN ('Unverified','Hearing','Complaint','Case') ORDER BY created_at DESC";
    $result = $conn->query($fallbackSql);
}

$notifications = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Fallback: if no direct lupon_id matches and we have a name, try matching name in title or message (legacy rows)
if (empty($notifications) && $luponName !== '') {
    $sql2 = "SELECT * FROM notifications 
             WHERE type IN ('Unverified','Hearing','Complaint','Case')
               AND (title LIKE CONCAT('%', ?, '%') OR message LIKE CONCAT('%', ?, '%'))
             ORDER BY created_at DESC";
    if ($st2 = $conn->prepare($sql2)) {
        $st2->bind_param('ss', $luponName, $luponName);
        $st2->execute();
        $res2 = $st2->get_result();
        if ($res2 && $res2->num_rows > 0) {
            while ($row = $res2->fetch_assoc()) { $notifications[] = $row; }
        }
        $st2->close();
    } else {
        $n = $conn->real_escape_string($luponName);
        $fallback2 = "SELECT * FROM notifications WHERE type IN ('Unverified','Hearing','Complaint','Case') AND (title LIKE '%$n%' OR message LIKE '%$n%') ORDER BY created_at DESC";
        $res2 = $conn->query($fallback2);
        if ($res2 && $res2->num_rows > 0) {
            while ($row = $res2->fetch_assoc()) { $notifications[] = $row; }
        }
    }
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
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
                        'pulse-subtle': 'pulse-subtle 2s infinite',
                        'bell-ring': 'bell-ring 1s ease-in-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        'pulse-subtle': {
                            '0%, 100%': { opacity: 1 },
                            '50%': { opacity: 0.8 }
                        },
                        'bell-ring': {
                            '0%, 100%': { transform: 'rotate(0)' },
                            '20%, 60%': { transform: 'rotate(8deg)' },
                            '40%, 80%': { transform: 'rotate(-8deg)' }
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
        .notification-card {
            transition: all 0.2s ease;
        }
        .notification-card:hover {
            background-color: #f9fafc;
        }
        .unread-indicator {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #0c9ced;
            top: 22px;
            right: 22px;
        }
        
        /* Notification item styles */
        .notification-item {
            transition: all 0.2s ease;
        }
        .notification-item:hover {
            background-color: #f9fafb;
        }
        .notification-dot {
            transition: all 0.2s ease;
        }
        .notification-item:hover .notification-dot {
            transform: scale(1.2);
        }
        .gradient-bg {
            background: linear-gradient(to right, #f0f7ff, #e0effe);
        }
        
        /* Empty state animation */
        .empty-icon-container {
            animation: float 4s ease-in-out infinite;
        }
        
    </style>
</head>
<body class="bg-gray-50 font-sans relative overflow-x-hidden">
    <?php include_once ('../includes/barangay_official_lupon_nav.php'); ?>
    <?php include_once ('../chatbot/bpamis_case_assistant.php'); ?>
    <!-- Page Header -->
    <div class="w-full mt-6 px-4">
        <div class="relative gradient-bg max-w-7xl mx-auto rounded-2xl shadow-sm p-8 md:p-10 overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70 animate-float"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60 animate-float"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-light text-primary-800">Your <span class="font-medium">Notifications</span></h2>
                    <p class="mt-3 text-gray-600 max-w-md">Stay updated with the latest activity in your cases and complaints.</p>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-600">
                    <span class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fas fa-shield-halved text-primary-500"></i> Secure Data</span>
                    <span class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fas fa-bell text-primary-500"></i> Live Feed</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters & Search (Enhanced) -->
    <div class="w-full mt-6 px-4">
        <div class="relative bg-white max-w-7xl mx-auto rounded-2xl shadow-sm p-8 md:p-10 overflow-hidden">
            <!-- Chips -->
            <div class="flex flex-wrap gap-2 mb-4" id="notifChips">
                <button type="button" data-filter="" class="s-chip active px-3 py-1.5 text-xs font-medium rounded-full bg-primary-600 text-white shadow-sm">All</button>
                <button type="button" data-filter="unread" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-amber-50 text-amber-600 border border-amber-100 hover:bg-amber-100 transition">Unread</button>
                <button type="button" data-filter="hearing" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-purple-50 text-purple-600 border border-purple-100 hover:bg-purple-100 transition">Hearings</button>
                <button type="button" data-filter="complaint" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-cyan-50 text-cyan-600 border border-cyan-100 hover:bg-cyan-100 transition">Complaints</button>
                <button type="button" data-filter="case" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-green-50 text-green-600 border border-green-100 hover:bg-green-100 transition">Cases</button>
                <button type="button" data-filter="unverified" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-100 transition">Unverified</button>
                <button type="button" data-filter="assigned" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-sky-50 text-sky-700 border border-sky-100 hover:bg-sky-100 transition">Assigned</button>
            </div>
            <!-- Controls -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <div class="md:col-span-5 relative">
                    <input id="searchInput" type="text" placeholder="Search notifications..." class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-white/70 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400" />
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-primary-400"></i>
                </div>
                <div class="md:col-span-2 relative">
                    <select id="monthFilter" class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 bg-white/70 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 appearance-none">
                        <option value="">All Months</option>
                        <?php foreach(range(1,12) as $m): $mn=date('F',mktime(0,0,0,$m,1)); $mv=str_pad((string)$m,2,'0',STR_PAD_LEFT); ?>
                            <option value="<?= $mv ?>"><?= $mn ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fa-solid fa-caret-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-primary-400"></i>
                </div>
                <div class="md:col-span-2 relative">
                    <select id="yearFilter" class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 bg-white/70 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 appearance-none">
                        <option value="">All Years</option>
                        <?php $cy=date('Y'); for($y=$cy;$y>=$cy-5;$y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <i class="fa-solid fa-caret-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-primary-400"></i>
                </div>
                <div class="md:col-span-2 relative">
                    <select id="sortOrder" class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 bg-white/70 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 appearance-none">
                        <option value="desc">Newest first</option>
                        <option value="asc">Oldest first</option>
                    </select>
                    <i class="fa-solid fa-caret-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-primary-400"></i>
                </div>
                <div class="md:col-span-1 flex gap-2 justify-end md:justify-start">
                    <button id="resetFilters" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg border border-primary-100 bg-primary-50/60 text-primary-600 text-sm font-medium hover:bg-primary-100 transition"><i class="fa-solid fa-rotate-left"></i><span class="hidden xl:inline">Reset</span></button>
                </div>
            </div>
            <div class="mt-3 text-right">
                <button id="markAllReadBtn" class="text-primary-600 hover:text-primary-700 text-sm font-medium whitespace-nowrap">
                    <i class="fas fa-check-double mr-1"></i> Mark all as read
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
<div id="notification-regular">
    <div class="w-full mt-6 px-4 pb-10">
    <div class="relative bg-white max-w-7xl mx-auto rounded-2xl shadow-sm p-8 md:p-10 overflow-hidden">
            <div id="notificationList" class="divide-y divide-gray-100">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $row): ?>
                        <?php
                            // Choose icon and colors based on type
                            $icon = 'fa-bell';
                            $bgColor = 'bg-gray-100';
                            $iconColor = 'text-gray-600';

                            switch ($row['type']) {
                                case 'Hearing':
                                    $icon = 'fa-calendar-alt';
                                    $bgColor = 'bg-blue-100';
                                    $iconColor = 'text-blue-600';
                                    break;
                                case 'Case':
                                    $icon = 'fa-gavel';
                                    $bgColor = 'bg-yellow-100';
                                    $iconColor = 'text-yellow-600';
                                    break;
                                case 'Complaint':
                                    $icon = 'fa-file-lines';
                                    $bgColor = 'bg-emerald-100';
                                    $iconColor = 'text-emerald-600';
                                    break;
                                case 'Unverified':
                                    $icon = 'fa-user-shield';
                                    $bgColor = 'bg-rose-100';
                                    $iconColor = 'text-rose-600';
                                    break;
                            }

                            $isUnread = ((int)$row['is_read']) === 0;
                            $createdAtRaw = $row['created_at'];
                            $created = date("M d, Y \\a\\t h:i A", strtotime($createdAtRaw));
                            $baseType = strtolower($row['type']);
                            $searchStr = strtolower(($row['title'] ?? '').' '.($row['message'] ?? ''));
                        ?>
                        <?php $isAssigned = (stripos(($row['title'] ?? ''), 'assigned') !== false) || (stripos(($row['message'] ?? ''), 'assigned') !== false); ?>
                        <?php $notifId = isset($row['notification_id']) ? $row['notification_id'] : (isset($row['id']) ? $row['id'] : ''); ?>
                        <a href="view_notification.php?id=<?= htmlspecialchars($notifId) ?>" class="s-notif-card block" data-type="<?= $baseType ?>" data-base="<?= $baseType ?>" data-unread="<?= $isUnread ? '1' : '0' ?>" data-date="<?= htmlspecialchars($createdAtRaw) ?>" data-search="<?= htmlspecialchars($searchStr) ?>" data-assigned="<?= $isAssigned ? '1' : '0' ?>">
                       <div class="notification-card p-5 relative cursor-pointer <?= $isUnread ? 'bg-gray-50' : '' ?>">

                            <?php if ($isUnread): ?>
                                <div class="unread-indicator animate-pulse-subtle"></div>
                            <?php endif; ?>
                            <div class="flex">
                                <div class="flex-shrink-0 mr-4">
                                    <div class="w-10 h-10 <?= $bgColor ?> rounded-full flex items-center justify-center">
                                        <i class="fas <?= $icon ?> <?= $iconColor ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <p class="text-sm font-medium flex items-center gap-2">
                                        <span><?= htmlspecialchars($row['title']) ?></span>
                                        <?php if ($isAssigned): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-sky-100 text-sky-700 text-[10px] font-semibold">Assigned</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($row['message']) ?></p>
                                    <div class="flex justify-between items-center mt-2">
                                        <p class="text-xs text-gray-500"><?= $created ?></p>
                                        <div class="flex gap-2">
                                            <a href="view_notification.php?id=<?= $row['notification_id'] ?>" class="text-primary-600 hover:text-primary-700 text-sm">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-5 text-sm text-gray-500 text-center">No notifications found.</div>
                <?php endif; ?>
            </div>
            <div id="noResults" class="hidden p-5 text-sm text-gray-500 text-center">No notifications match your filters.</div>
        </div>
    </div>
</div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        <nav class="flex items-center space-x-1">
            <button class="p-2 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 disabled:opacity-50" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="px-3 py-1 rounded-md bg-primary-50 text-primary-600 font-medium">1</button>
            <button class="px-3 py-1 rounded-md text-gray-500 hover:bg-gray-100">2</button>
            <button class="p-2 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50">
                <i class="fas fa-chevron-right"></i>
            </button>
        </nav>
    </div>
    
    <div class="mt-6 flex justify-center">
        <button onclick="window.location.href='home-lupon.php'" class="px-4 py-2 text-gray-500 hover:text-gray-700 flex items-center transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </button>
    </div>

    <!-- No notifications state (hidden by default) -->
    <div id="no-notifications" class="hidden w-full mt-10 px-4 pb-10">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-10 text-center">
            <div class="flex justify-center mb-4">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-bell-slash text-gray-300 text-3xl"></i>
                </div>
            </div>
            <h3 class="text-lg font-medium text-gray-700 mb-2">No notifications yet</h3>
            <p class="text-gray-500 max-w-md mx-auto">When you receive new notifications about your cases or complaints, they will appear here.</p>
            <div class="mt-6">
                <button onclick="window.location.href='home-lupon.php'" class="px-4 py-2 bg-primary-50 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-100 transition-colors">
                    Return to Dashboard
                </button>
            </div>
        </div>
    </div>
    <?php include 'sidebar_lupon.php';?>

    <script>
    document.addEventListener('DOMContentLoaded',()=>{
        const chips=document.querySelectorAll('#notifChips .s-chip');
        const searchInput=document.getElementById('searchInput');
        const monthFilter=document.getElementById('monthFilter');
        const yearFilter=document.getElementById('yearFilter');
        const sortOrder=document.getElementById('sortOrder');
        const resetBtn=document.getElementById('resetFilters');
        const cards=[...document.querySelectorAll('.s-notif-card')];
        const noResults=document.getElementById('noResults');
        const list=document.getElementById('notificationList');
        let filterOverride='';
        function applyFilters(){
            const q=(searchInput.value||'').toLowerCase(); const m=monthFilter.value; const y=yearFilter.value; let shown=0;
            cards.forEach(c=>{ const type=c.dataset.type||''; const base=c.dataset.base||''; const unread=c.dataset.unread==='1'; const dateRaw=c.dataset.date||''; const text=(c.dataset.search||'').toLowerCase(); const assigned=c.dataset.assigned==='1'; let show=true;
                if(filterOverride){ 
                    if(filterOverride==='unread') show=unread; 
                    else if(filterOverride==='assigned') show=assigned; 
                    else show = type===filterOverride || base===filterOverride; 
                }
                if(q) show = show && text.includes(q);
                if((m||y) && dateRaw){ const d=new Date(dateRaw.replace(' ','T')); const M=('0'+(d.getMonth()+1)).slice(-2); const Y=d.getFullYear().toString(); if(m) show=show && M===m; if(y) show=show && Y===y; }
                c.style.display=show?'':'none'; if(show) shown++; });
            const visible=cards.filter(c=>c.style.display!=='none').sort((a,b)=>{ const da=new Date(a.dataset.date.replace(' ','T')); const db=new Date(b.dataset.date.replace(' ','T')); return sortOrder.value==='asc'? da-db : db-da; });
            visible.forEach(el=> list.appendChild(el));
            noResults.classList.toggle('hidden', shown>0);
        }
        chips.forEach(ch=> ch.addEventListener('click',()=>{ chips.forEach(c=>c.classList.remove('active','bg-primary-600','text-white','shadow')); ch.classList.add('active','bg-primary-600','text-white','shadow'); filterOverride=(ch.dataset.filter||''); applyFilters(); }));
        [searchInput,monthFilter,yearFilter,sortOrder].forEach(el=> el.addEventListener('input',applyFilters));
        monthFilter.addEventListener('change',applyFilters); yearFilter.addEventListener('change',applyFilters); sortOrder.addEventListener('change',applyFilters);
        resetBtn.addEventListener('click',()=>{ searchInput.value=''; monthFilter.value=''; yearFilter.value=''; sortOrder.value='desc'; filterOverride=''; chips.forEach((c,i)=>{ c.classList.remove('active','bg-primary-600','text-white','shadow'); if(i===0){ c.classList.add('active','bg-primary-600','text-white','shadow'); } }); applyFilters(); });
        // Mark all read
        const markAll=document.getElementById('markAllReadBtn');
        if(markAll){ markAll.addEventListener('click', async ()=>{ try{
            const res = await fetch('../controllers/mark_all_notifications_read.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({scope:'lupon'})});
            const data = await res.json();
            if(data && data.success){
                document.querySelectorAll('.unread-indicator').forEach(ind=>{ ind.classList.add('opacity-0'); setTimeout(()=> ind.remove(),250); });
                cards.forEach(c=>{ c.dataset.unread='0'; });
            }
        }catch(e){ console.warn('Failed to mark all as read on server:', e); } }); }
        applyFilters();
        // Mobile menu toggle
        const menuButton=document.getElementById('mobile-menu-button');
        const mobileMenu=document.getElementById('mobile-menu');
        if(menuButton && mobileMenu){ menuButton.addEventListener('click',function(){ this.classList.toggle('active'); mobileMenu.style.transform=(mobileMenu.style.transform==='translateY(0%)')? 'translateY(-100%)':'translateY(0%)'; }); }
    });
    </script>
</body>
</html>


