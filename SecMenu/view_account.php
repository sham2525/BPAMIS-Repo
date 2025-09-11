<?php
include './schedule/db-connect.php';

$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(!$type || !$id){
    http_response_code(400);
    echo 'Missing parameters';
    exit;
}

$account = null;

switch($type){
    case 'resident':
        $stmt = $conn->prepare("SELECT Resident_ID AS id, CONCAT(First_name,' ',Middle_name,' ',Last_name) AS name, Birthdate, Address, Contact_Number AS contact_number, isVerify AS isActive, Resident_Username AS username, email FROM resident_info WHERE Resident_ID = ? LIMIT 1");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $res = $stmt->get_result();
        $account = $res->fetch_assoc();
        $account['account_type'] = 'resident';
        $account['role'] = 'Resident';
        break;
    case 'external':
        $stmt = $conn->prepare("SELECT External_Complaint_ID AS id, CONCAT(First_name,' ',Middle_name,' ',Last_name) AS name, Birthdate, Address, email, isActive, External_Username AS username FROM external_complainant WHERE External_Complaint_ID = ? LIMIT 1");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $res = $stmt->get_result();
        $account = $res->fetch_assoc();
        $account['account_type'] = 'external';
        $account['role'] = 'External Complaint';
        $account['contact_number'] = '';
        break;
    case 'official':
        $stmt = $conn->prepare("SELECT Official_ID AS id, Position AS role, Name AS name, contact_number, isActive, official_username AS username, email FROM barangay_officials WHERE Official_ID = ? LIMIT 1");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $res = $stmt->get_result();
        $account = $res->fetch_assoc();
        $account['account_type'] = 'official';
        $account['birthdate'] = null;
        $account['address'] = null;
        break;
    default:
        http_response_code(400);
        echo 'Invalid type';
        exit;
}

if(!$account){
    http_response_code(404);
    echo 'Account not found';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Account • Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: {50:'#f0f7ff',100:'#e0effe',200:'#bae2fd',300:'#7cccfd',400:'#36b3f9',500:'#0c9ced',600:'#0281d4',700:'#026aad',800:'#065a8f',900:'#0a4b76'} },
                    boxShadow: { 'glow': '0 0 0 1px rgba(12,156,237,0.08), 0 4px 20px -2px rgba(6,90,143,0.18)' },
                    animation: { 'fade-in': 'fadeIn .4s ease-out', 'scale-in': 'scaleIn .35s ease-out' },
                    keyframes: { fadeIn:{'0%':{opacity:0},'100%':{opacity:1}}, scaleIn:{'0%':{opacity:0,transform:'scale(.98)'},'100%':{opacity:1,transform:'scale(1)'}}
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .glass { background: linear-gradient(140deg, rgba(255,255,255,.88), rgba(255,255,255,.6)); backdrop-filter: blur(14px) saturate(140%); -webkit-backdrop-filter: blur(14px) saturate(140%); }
        .badge { font-size:11px; padding:4px 10px; border-radius:9999px; font-weight:600; letter-spacing:.5px; display:inline-flex; align-items:center; gap:4px; text-transform:uppercase; }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-primary-50 via-white to-primary-100 min-h-screen text-gray-800 relative overflow-x-hidden">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-32 -left-24 w-96 h-96 bg-primary-200 opacity-30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -right-24 w-[30rem] h-[30rem] bg-primary-300 opacity-20 rounded-full blur-3xl"></div>
    </div>

    <?php include '../includes/barangay_official_sec_nav.php';?>
    <?php include 'sidebar_.php';?>

    <?php
        // Icon mapping based on account type / role
        $iconMap = [
            'resident' => ['icon'=>'fa-user','bg'=>'bg-emerald-50','ring'=>'ring-emerald-100','accent'=>'text-emerald-600'],
            'external' => ['icon'=>'fa-user-pen','bg'=>'bg-amber-50','ring'=>'ring-amber-100','accent'=>'text-amber-600'],
            'official' => ['icon'=>'fa-id-card-badge','bg'=>'bg-sky-50','ring'=>'ring-sky-100','accent'=>'text-sky-600'],
        ];
        $style = $iconMap[$account['account_type']] ?? ['icon'=>'fa-user','bg'=>'bg-sky-50','ring'=>'ring-sky-100','accent'=>'text-sky-600'];
    ?>

    <main class="relative z-10 max-w-5xl mx-auto px-4 md:px-8 pt-10 pb-24 animate-fade-in">
        <div class="mb-8 flex items-center gap-3">
            <a href="Accounts.php" class="group inline-flex items-center text-sm font-medium text-primary-700 hover:text-primary-900 transition">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-white/70 shadow ring-1 ring-primary-100 group-hover:bg-primary-50">
                    <i class="fa fa-arrow-left"></i>
                </span>
                <span class="ml-2">Back to Accounts</span>
            </a>
        </div>

        <section class="relative glass shadow-glow rounded-2xl p-6 md:p-10 border border-white/60 ring-1 ring-primary-100/40 overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-gradient-to-br from-primary-200/60 to-primary-400/40 blur-2xl opacity-40"></div>
            </div>

            <header class="relative flex flex-col md:flex-row md:items-start gap-6 mb-8">
                <div class="flex items-center">
                    <div class="relative">
                        <div class="w-20 h-20 rounded-2xl flex items-center justify-center <?= $style['bg']; ?> ring-4 <?= $style['ring']; ?> shadow-inner">
                            <i class="fa <?= $style['icon']; ?> text-3xl <?= $style['accent']; ?>"></i>
                        </div>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-gray-800 flex flex-wrap items-center gap-3">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-700 to-primary-500">
                            <?= htmlspecialchars($account['name'] ?? 'N/A') ?>
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full bg-white/70 border border-primary-100 text-primary-700 shadow-sm">
                            <i class="fa <?= $style['icon']; ?>"></i>
                            <?= htmlspecialchars(ucfirst($account['account_type'])) ?>
                        </span>
                        <?php if(!empty($account['role'])): ?>
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full bg-gray-100 border border-gray-200 text-gray-700 shadow-sm">
                            <i class="fa fa-briefcase"></i>
                            <?= htmlspecialchars($account['role']) ?>
                        </span>
                        <?php endif; ?>
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full <?= !empty($account['isActive']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-600 border border-rose-200' ?> shadow-sm">
                            <i class="fa fa-circle text-[8px]"></i> <?= !empty($account['isActive']) ? 'Active' : 'Inactive' ?>
                        </span>
                    </h1>
                    <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <?php if(!empty($account['email'])): ?>
                        <span class="inline-flex items-center gap-1"><i class="fa fa-envelope"></i> <?= htmlspecialchars($account['email']) ?></span>
                        <?php endif; ?>
                        <?php if(!empty($account['contact_number'])): ?>
                        <span class="inline-flex items-center gap-1"><i class="fa fa-phone"></i> <?= htmlspecialchars($account['contact_number']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <div class="space-y-10">
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Core Information</h2>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-medium mb-1">Account ID</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($account['id']) ?></p>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-medium mb-1">Username</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($account['username'] ?? 'N/A') ?></p>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-medium mb-1">Type</p>
                            <p class="font-semibold text-gray-800 capitalize"><?= htmlspecialchars($account['account_type']) ?></p>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-medium mb-1">Role</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($account['role'] ?? 'N/A') ?></p>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm md:col-span-2">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-medium mb-1">Active</p>
                            <p class="inline-flex items-center gap-2 font-semibold <?= !empty($account['isActive']) ? 'text-emerald-600' : 'text-rose-600' ?>">
                                <i class="fa fa-circle text-[8px]"></i><?= !empty($account['isActive']) ? 'Yes' : 'No' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-gray-500 uppercase mb-3">Profile Details</h2>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-medium mb-1">Birthdate</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($account['Birthdate'] ?? $account['birthdate'] ?? 'N/A') ?></p>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-medium mb-1">Address</p>
                            <p class="font-semibold text-gray-800 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($account['Address'] ?? $account['address'] ?? 'N/A') ?></p>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-medium mb-1">Contact Number</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($account['contact_number'] ?? 'N/A') ?></p>
                        </div>
                        <div class="group rounded-xl border bg-white/70 border-gray-200 hover:border-primary-200 transition p-4 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-medium mb-1">Email</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($account['email'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-dashed border-primary-200/60 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex gap-2">
                        <a href="Accounts.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/80 hover:bg-white text-primary-700 border border-primary-200 shadow-sm text-sm font-medium transition">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        <button disabled class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600/40 text-white shadow text-sm font-medium cursor-not-allowed" title="Coming soon">
                            <i class="fa fa-pen"></i> Edit (Soon)
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include('../chatbot/bpamis_case_assistant.php'); ?>
</body>
</html>
