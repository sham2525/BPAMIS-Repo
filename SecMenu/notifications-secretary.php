<?php
session_start();
include '../server/server.php';

// Function to check deadlines and insert notifications (legacy, kept if needed)
function checkDeadlines($conn, $table, $type, $idColumn, $daysBefore = 3) {
    $today = date('Y-m-d');
    $sql = "SELECT $idColumn AS id, Deadline 
            FROM $table 
            WHERE Deadline IS NOT NULL
              AND DATE(Deadline) = DATE_ADD('$today', INTERVAL $daysBefore DAY)";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $relatedId = $row['id'];
            $checkSql = "SELECT 1 FROM notifications 
                         WHERE related_id = '$relatedId' 
                           AND type = '$type' LIMIT 1";
            $checkResult = $conn->query($checkSql);

            if ($checkResult && $checkResult->num_rows === 0) {
                $message = "$type is high priority! Deadline on {$row['Deadline']}.";
                $insertSql = "INSERT INTO notifications (type, message, related_id, created_at) 
                              VALUES ('$type', '$message', '$relatedId', NOW())";
                $conn->query($insertSql);
            }
        }
    }
}

// ✅ Unverified accounts
$unverifiedAccounts = [];
$sql = "SELECT Resident_ID, First_Name, Last_Name, email 
        FROM resident_info 
        WHERE isVerify = 0 
        ORDER BY Resident_ID DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $unverifiedAccounts[] = $row;
    }
}

// ✅ Fetch ALL notifications (All filter should display everything, newest on top)
// Removed pagination per request; ordering strictly by created_at DESC to ensure newest first
$sql = "SELECT * FROM notifications ORDER BY created_at DESC";
$result = $conn->query($sql);

$notifications = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Counts for metrics (deadline removed per UI update request)
$allCount = count($notifications);
$unreadCount = count(array_filter($notifications, fn($n)=> (int)$n['is_read'] === 0));
$hearingCount = count(array_filter($notifications, fn($n)=> strcasecmp($n['type'],'Hearing')===0));
$caseCount = count(array_filter($notifications, fn($n)=> strcasecmp($n['type'],'Case')===0));
$complaintCount = count(array_filter($notifications, fn($n)=> strcasecmp($n['type'],'Complaint')===0));
$unverifiedCount = count($unverifiedAccounts); // accounts needing manual verification

// ✅ Helper: relative time (premium display)
function notif_relative_time($datetime) {
    if(!$datetime) return '';
    $ts = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    $units = [31536000=>'year',2592000=>'month',604800=>'week',86400=>'day',3600=>'hour',60=>'min'];
    foreach($units as $sec=>$label){
        if($diff >= $sec){
            $val = floor($diff / $sec);
            return $val.' '.$label.($val>1 && $label!=='min'?'s':'').' ago';
        }
    }
    return 'just now';
}

// ✅ Unified Deadline Check Function
function checkDeadlines3Days($conn, $table, $idCol, $typeLabel, $deadlineColumn, $hasPriorityCol = false) {
    $dt = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $dt->modify('+3 days');
    $targetDate = $dt->format('Y-m-d');

    $selectSql = "SELECT $idCol AS id, Case_ID, $deadlineColumn AS deadline 
                  FROM $table 
                  WHERE $deadlineColumn IS NOT NULL AND DATE($deadlineColumn) = ?";
    if (!$stmt = $conn->prepare($selectSql)) {
        error_log("Prep SELECT failed for $table: " . $conn->error);
        return;
    }

    $stmt->bind_param('s', $targetDate);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $id = (int)$row['id'];
        $caseId = $row['Case_ID'] ?? $id;
        $deadline = $row['deadline'];

        // ✅ Avoid duplicate notifications
        $dupSql = "SELECT 1 FROM notifications WHERE type = ? AND related_id = ? LIMIT 1";
        if (!$dup = $conn->prepare($dupSql)) {
            error_log("Prep DUP failed: " . $conn->error);
            continue;
        }
        $dup->bind_param('si', $typeLabel, $id);
        $dup->execute();
        $dupRes = $dup->get_result();

        if ($dupRes && $dupRes->num_rows === 0) {
            $title = "$typeLabel Approaching";
            $message = "$typeLabel for Case #$caseId is due on $deadline.";

            $insertSql = "INSERT INTO notifications (type, title, message, created_at, is_read, related_id, isPriority)
                          VALUES (?, ?, ?, NOW(), 0, ?, 1)";
            if ($ins = $conn->prepare($insertSql)) {
                $ins->bind_param('sssi', $typeLabel, $title, $message, $id);
                $ins->execute();
                $ins->close();
            }

            if ($hasPriorityCol) {
                $updateSql = "UPDATE $table SET isPriority = 1 WHERE $idCol = ?";
                if ($upd = $conn->prepare($updateSql)) {
                    $upd->bind_param('i', $id);
                    $upd->execute();
                    $upd->close();
                }
            }
        }
        $dup->close();
    }
    $stmt->close();
}

// ✅ Run for all deadline types
checkDeadlines3Days($conn, 'mediation_info', 'Mediation_ID', 'Mediation Deadline', 'Deadline', false);
checkDeadlines3Days($conn, 'settlement', 'Settlement_ID', 'Settlement Deadline', 'Deadline', false);
checkDeadlines3Days($conn, 'resolution', 'Resolution_ID', 'Resolution Deadline', 'Deadline', false);
checkDeadlines3Days($conn, 'case_info', 'Case_ID', 'Case Deadline', 'Case_Deadline', false);
checkDeadlines3Days($conn, 'case_info', 'Case_ID', 'Deadline Overdue', 'Deadline_Overdue', false);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Proper Font Awesome CSS (icons were not showing because only JS was loaded) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Optional JS (kept if any dynamic FA functionality is needed) -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
    <?php include_once ('../includes/barangay_official_sec_nav.php'); ?>
    <!-- Global Orbs -->
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[480px] h-[480px] rounded-full bg-blue-200/40 blur-3xl animate-[float_14s_ease-in-out_infinite]"></div>
        <div class="absolute top-1/3 -right-52 w-[560px] h-[560px] rounded-full bg-cyan-200/40 blur-[160px] animate-[float_18s_ease-in-out_infinite]"></div>
        <div class="absolute -bottom-52 left-1/3 w-[520px] h-[520px] rounded-full bg-indigo-200/30 blur-3xl animate-[float_16s_ease-in-out_infinite]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[900px] rounded-full bg-gradient-to-br from-blue-50 via-white to-cyan-50 opacity-70 blur-[200px]"></div>
    </div>
    <!-- Premium Hero -->
    <div class="w-full mt-6 px-4">
        <div class="relative gradient-bg max-w-7xl mx-auto rounded-2xl shadow-sm p-8 md:p-10 overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-24 -mt-24 opacity-70 animate-[float_8s_ease-in-out_infinite]"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-14 -mb-14 opacity-60 animate-[float_6s_ease-in-out_infinite]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-br from-primary-50 via-white to-primary-100 opacity-30 blur-3xl rounded-full pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
                <div class="max-w-2xl">
                    <h1 class="text-3xl md:text-4xl font-light text-primary-900 tracking-tight">Secretary <span class="font-semibold">Notifications</span></h1>
                    <p class="mt-4 text-gray-600 leading-relaxed">Monitor all system and case lifecycle events including deadlines, hearings, complaints, and account verification. Use refined filters to focus on what matters now.</p>
                    <div class="mt-5 flex flex-wrap gap-3 text-xs text-primary-700/80 font-medium">
                        <span class="px-3 py-1.5 rounded-full bg-white/70 backdrop-blur border border-primary-100 shadow-sm flex items-center gap-1"><i class="fa-solid fa-bell text-primary-500"></i> Real-time</span>
                        <span class="px-3 py-1.5 rounded-full bg-white/70 backdrop-blur border border-primary-100 shadow-sm flex items-center gap-1"><i class="fa-solid fa-triangle-exclamation text-primary-500"></i> Deadlines</span>
                        <span class="px-3 py-1.5 rounded-full bg-white/70 backdrop-blur border border-primary-100 shadow-sm flex items-center gap-1"><i class="fa-solid fa-user-check text-primary-500"></i> Verification</span>
                    </div>
                </div>

                <div class="flex flex-col gap-3 min-w-[250px]">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex flex-col items-center bg-white/80 backdrop-blur rounded-xl px-3 py-3 border border-blue-100 shadow-sm"><span class="text-[10px] uppercase tracking-wide text-blue-600 font-semibold">All</span><span class="mt-1 text-lg font-semibold text-blue-700"><?= $allCount ?></span></div>
                        <div class="flex flex-col items-center bg-white/80 backdrop-blur rounded-xl px-3 py-3 border border-amber-100 shadow-sm"><span class="text-[10px] uppercase tracking-wide text-amber-600 font-semibold">Unread</span><span class="mt-1 text-lg font-semibold text-amber-700"><?= $unreadCount ?></span></div>
                    </div>
                    <div class="text-[11px] text-primary-700/70 text-center">Overview summary</div>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Advanced Filters Card -->
    <div class="w-full mt-8 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="relative bg-white/90 backdrop-blur-sm border border-gray-100 rounded-2xl shadow-sm p-6 md:p-7 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-primary-50 to-primary-100 rounded-full opacity-70"></div>
                <div class="absolute -bottom-12 -left-12 w-40 h-40 bg-gradient-to-tr from-primary-50 to-primary-100 rounded-full opacity-60"></div>
                <div class="relative z-10 space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-3 text-primary-700/80 text-sm font-medium">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-50/70 border border-primary-100"><i class="fa-solid fa-sliders text-primary-500"></i> Refine Notifications</span>
                        </div>
                        <button id="markAllReadBtn" class="group relative inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 text-white text-xs font-semibold shadow-sm hover:shadow-md transition-all">
                            <i class="fa-solid fa-check-double"></i>
                            <span>Mark All Read</span>
                        </button>
                    </div>
                    <!-- Chips -->
                    <div class="flex flex-wrap gap-2" id="notifChips">
                        <button type="button" data-filter="" class="s-chip active px-3 py-1.5 text-xs font-medium rounded-full bg-primary-600 text-white shadow-sm">All</button>
                        <button type="button" data-filter="unread" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-amber-50 text-amber-600 border border-amber-100 hover:bg-amber-100 transition">Unread</button>
                        <button type="button" data-filter="hearing" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-purple-50 text-purple-600 border border-purple-100 hover:bg-purple-100 transition">Hearings</button>
                        <button type="button" data-filter="complaint" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-cyan-50 text-cyan-600 border border-cyan-100 hover:bg-cyan-100 transition">Complaints</button>
                        <button type="button" data-filter="case" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-green-50 text-green-600 border border-green-100 hover:bg-green-100 transition">Cases</button>
                        <button type="button" data-filter="unverified" class="s-chip px-3 py-1.5 text-xs font-medium rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-100 transition">Unverified</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-5 relative group">
                            <input id="searchInput" type="text" placeholder="Search notifications..." class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200/80 bg-white/70 focus:ring-2 focus:ring-primary-200 focus:border-primary-400 placeholder:text-gray-400 text-sm transition" />
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

    <!-- Unified Notifications Section -->
    <div id="notificationSection" class="w-full mt-8 px-4 pb-24">
        <div class="max-w-7xl mx-auto">
            <div id="notificationList" class="space-y-4">
                <?php if(!empty($notifications)): foreach($notifications as $row):
                    $rawType = $row['type'];
                    $baseType = strtolower($rawType);
                    $priorityFlag = isset($row['isPriority']) ? (int)$row['isPriority'] : 0;
                    $group = $baseType; // deadline grouping removed
                    $icon='fa-bell'; $iconWrap='bg-gray-100 text-gray-600';
                    switch(true){
                        case $rawType==='Hearing': $icon='fa-calendar-alt'; $iconWrap='bg-purple-50 text-purple-600'; break;
                        case $rawType==='Complaint' && $priorityFlag===0: $icon='fa-file-alt'; $iconWrap='bg-cyan-50 text-cyan-600'; break;
                        case $rawType==='Complaint' && $priorityFlag===1: $icon='fa-exclamation-triangle'; $iconWrap='bg-red-100 text-red-600'; break;
                        case $rawType==='Case': $icon='fa-gavel'; $iconWrap='bg-green-50 text-green-600'; break;
                        case $rawType==='Unverified': $icon='fa-user-circle'; $iconWrap='bg-indigo-50 text-indigo-600'; break;
                    }
                    $isUnread = ((int)$row['is_read'])===0; $createdRaw=$row['created_at']; $createdDisp=date('M j, Y g:i A', strtotime($createdRaw));
                    $searchStr = strtolower(($row['title']??'').' '.($row['message']??''));
                ?>
                <div class="s-notif-card relative group bg-white/85 backdrop-blur rounded-xl border border-gray-100 p-5 flex flex-col gap-3 hover:-translate-y-[2px] hover:shadow-md transition-all" data-type="<?=htmlspecialchars($group)?>" data-base="<?=htmlspecialchars($baseType)?>" data-date="<?= date('Y-m-d H:i:s', strtotime($createdRaw)) ?>" data-unread="<?=$isUnread? '1':'0'?>" data-search="<?= htmlspecialchars($searchStr) ?>">
                    <?php if($isUnread): ?><span class="absolute top-3 right-3 inline-flex w-2.5 h-2.5 rounded-full bg-amber-500 shadow animate-pulse-subtle"></span><?php endif; ?>
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-12 h-12 rounded-full flex items-center justify-center <?=$iconWrap?> shadow-sm"><i class="fa-solid <?=$icon?> text-base"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-sm font-medium text-gray-800 leading-snug line-clamp-2" title="<?= htmlspecialchars($row['title'] ?: 'Notification') ?>"><?= htmlspecialchars($row['title'] ?: 'Notification') ?></h3>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-[10px] font-semibold tracking-wide uppercase text-gray-600"><i class="fa-solid <?=$icon?>"></i><?= htmlspecialchars($rawType) ?></span>
                                <?php if($priorityFlag===1): ?><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 text-[10px] font-semibold tracking-wide text-red-600"><i class="fa-solid fa-exclamation-triangle"></i>Priority</span><?php endif; ?>
                            </div>
                            <p class="mt-1 text-xs text-gray-600 line-clamp-3" title="<?= htmlspecialchars($row['message']) ?>"><?= htmlspecialchars($row['message']) ?></p>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-[11px] text-gray-500 font-medium flex items-center gap-1"><i class="fa-regular fa-clock"></i> <?= $createdDisp ?> <span class="hidden sm:inline">• <?= notif_relative_time($row['created_at']) ?></span></span>
                                <div class="flex gap-2">
                                    <a href="view_notification.php?id=<?= $row['notification_id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-50 text-primary-600 text-[11px] font-medium hover:bg-primary-100 transition"><i class="fa-solid fa-eye"></i> View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>

                <!-- Synthetic Unverified Accounts (actionable) -->
                <?php if(!empty($unverifiedAccounts)): foreach($unverifiedAccounts as $account):
                    $name = htmlspecialchars($account['First_Name'].' '.$account['Last_Name']);
                    $email = htmlspecialchars($account['email']);
                    $search = strtolower($name.' '.$email.' unverified account');
                ?>
                <div class="s-notif-card relative group bg-white/85 backdrop-blur rounded-xl border border-indigo-100 p-5 flex flex-col gap-3 hover:-translate-y-[2px] hover:shadow-md transition-all" data-type="unverified" data-base="unverified" data-date="1970-01-01 00:00:00" data-unread="0" data-search="<?= $search ?>">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-indigo-50 text-indigo-600 shadow-sm"><i class="fa-solid fa-user-circle text-base"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-sm font-medium text-gray-800 leading-snug">Unverified Account</h3>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-50 text-[10px] font-semibold tracking-wide text-indigo-600"><i class="fa-solid fa-user"></i>Pending</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-600"><?=$name?> (<?=$email?>)</p>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-[11px] text-gray-500 font-medium">Awaiting verification</span>
                                <div class="flex gap-2">
                                    <form method="POST" action="../controllers/account_verification.php" class="inline">
                                        <input type="hidden" name="id" value="<?= $account['Resident_ID'] ?>" />
                                        <button name="verify" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 text-green-600 text-[11px] font-medium hover:bg-green-100 transition"><i class="fa-solid fa-check"></i>Verify</button>
                                    </form>
                                    <form method="POST" action="../controllers/account_verification.php" class="inline" onsubmit="return confirm('Remove this unverified account?');">
                                        <input type="hidden" name="id" value="<?= $account['Resident_ID'] ?>" />
                                        <button name="remove" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-[11px] font-medium hover:bg-red-100 transition"><i class="fa-solid fa-user-xmark"></i>Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
                <?php if(empty($notifications) && empty($unverifiedAccounts)): ?>
                    <div class="py-16 text-center text-gray-500 text-sm">No notifications or pending accounts.</div>
                <?php endif; ?>
            </div>
            <div id="noResults" class="hidden mt-10 text-center text-gray-500 text-sm">No notifications match your filters.</div>
            <div class="mt-10 flex justify-center">
                <a href="home-secretary.php" class="inline-flex items-center gap-2 px-4 py-2 text-gray-500 hover:text-gray-700 text-sm font-medium transition"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
        </div>
    </div>

        </div>
    
    <!-- Legacy empty state removed (replaced by dynamic noResults) -->
<?php include 'sidebar_.php';?>

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
        let filterOverride='';
        function applyFilters(){
            const q=(searchInput.value||'').toLowerCase(); const m=monthFilter.value; const y=yearFilter.value; let shown=0;
            cards.forEach(c=>{ const type=c.dataset.type||''; const base=c.dataset.base||''; const unread=c.dataset.unread==='1'; const dateRaw=c.dataset.date||''; const text=c.dataset.search||''; let show=true;
                if(filterOverride){
                    if(filterOverride==='unread') show=unread; else show = type===filterOverride || base===filterOverride;
                }
                if(q) show = show && text.includes(q);
                if((m||y) && dateRaw && dateRaw!=='1970-01-01 00:00:00') { const d=new Date(dateRaw.replace(' ','T')); const M=('0'+(d.getMonth()+1)).slice(-2); const Y=d.getFullYear().toString(); if(m) show=show && M===m; if(y) show=show && Y===y; }
                c.style.display=show?'':'none'; if(show) shown++; });
            // Sorting only real dated notifications
            const list=document.getElementById('notificationList');
            const visible=cards.filter(c=>c.style.display!=="none").sort((a,b)=>{ const da=new Date(a.dataset.date.replace(' ','T')); const db=new Date(b.dataset.date.replace(' ','T')); return sortOrder.value==='asc'? da-db : db-da; });
            visible.forEach(el=>list.appendChild(el));
            noResults.classList.toggle('hidden', shown>0);
        }
        chips.forEach(ch=> ch.addEventListener('click',()=>{ chips.forEach(c=>c.classList.remove('active','bg-primary-600','text-white','shadow')); ch.classList.add('active','bg-primary-600','text-white','shadow'); filterOverride=(ch.dataset.filter||''); applyFilters(); }));
        [searchInput,monthFilter,yearFilter,sortOrder].forEach(el=> el.addEventListener('input',applyFilters));
        monthFilter.addEventListener('change',applyFilters); yearFilter.addEventListener('change',applyFilters); sortOrder.addEventListener('change',applyFilters);
        resetBtn.addEventListener('click',()=>{ searchInput.value=''; monthFilter.value=''; yearFilter.value=''; sortOrder.value='desc'; filterOverride=''; chips.forEach((c,i)=>{ c.classList.remove('active','bg-primary-600','text-white','shadow'); if(i===0){ c.classList.add('active','bg-primary-600','text-white','shadow'); } }); applyFilters(); });
        // Mark all read
        const markAll=document.getElementById('markAllReadBtn');
        markAll.addEventListener('click',()=>{ fetch('../controllers/mark_all_notifications_read.php',{method:'POST',headers:{'Content-Type':'application/json'}}).then(r=>r.json()).then(d=>{ if(d.success){ cards.forEach(c=>{ if(c.dataset.unread==='1'){ c.dataset.unread='0'; const dot=c.querySelector('.animate-pulse-subtle'); if(dot) dot.remove(); } }); } }); });
        applyFilters();
    });
    </script>
    <?php include('../chatbot/bpamis_case_assistant.php'); ?>
</body>
</html>

