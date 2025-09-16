<?php
/**
 * Print KP Forms Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();

// Include database connection
include '../server/server.php';

// Get the requested form
$form_id = isset($_GET['form']) ? $_GET['form'] : '';
$case_id = isset($_GET['case']) ? $_GET['case'] : '';

// Fetch form details based on DILG Katarungang Pambarangay Handbook
$formTitle = '';
$formDescription = '';

// Fetch cases from database
$cases = [];
$sql = "SELECT 
    c.Case_ID,
    ci.Complaint_Title,
    ci.Date_Filed,
    c.Case_Status,
    CASE 
        WHEN ci.Resident_ID IS NOT NULL THEN ri.First_Name
        WHEN ci.External_Complainant_ID IS NOT NULL THEN eci.first_name
        ELSE 'Unknown'
    END AS Complainant_First,
    CASE 
        WHEN ci.Resident_ID IS NOT NULL THEN ri.Last_Name
        WHEN ci.External_Complainant_ID IS NOT NULL THEN eci.last_name
        ELSE 'Unknown'
    END AS Complainant_Last,
    resp.First_Name AS Respondent_First,
    resp.Last_Name AS Respondent_Last
FROM case_info c
JOIN complaint_info ci ON c.Complaint_ID = ci.Complaint_ID
LEFT JOIN resident_info ri ON ci.Resident_ID = ri.Resident_ID
LEFT JOIN external_complainant eci ON ci.External_Complainant_ID = eci.external_complaint_id
LEFT JOIN resident_info resp ON ci.Respondent_ID = resp.Resident_ID
ORDER BY c.Date_Opened DESC";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cases[] = $row;
    }
}

// If no cases found, try a simpler query as fallback
if (empty($cases)) {
    $fallback_sql = "SELECT 
        c.Case_ID,
        ci.Complaint_Title,
        ci.Date_Filed,
        c.Case_Status,
        'Unknown' AS Complainant_First,
        'Unknown' AS Complainant_Last,
        'Unknown' AS Respondent_First,
        'Unknown' AS Respondent_Last
    FROM case_info c
    JOIN complaint_info ci ON c.Complaint_ID = ci.Complaint_ID
    ORDER BY c.Date_Opened DESC";
    
    $fallback_result = $conn->query($fallback_sql);
    if ($fallback_result && $fallback_result->num_rows > 0) {
        while ($row = $fallback_result->fetch_assoc()) {
            $cases[] = $row;
        }
    }
}

// Debug: Check if we're getting any results
if (empty($cases)) {
    // Try a simpler query to see if there are any cases at all
    $debug_sql = "SELECT COUNT(*) as count FROM case_info";
    $debug_result = $conn->query($debug_sql);
    if ($debug_result) {
        $debug_count = $debug_result->fetch_assoc()['count'];
        error_log("Debug: Found $debug_count cases in case_info table");
    }
    
    // Check if there are any complaints
    $debug_complaints_sql = "SELECT COUNT(*) as count FROM complaint_info";
    $debug_complaints_result = $conn->query($debug_complaints_sql);
    if ($debug_complaints_result) {
        $debug_complaints_count = $debug_complaints_result->fetch_assoc()['count'];
        error_log("Debug: Found $debug_complaints_count complaints in complaint_info table");
    }
}

// Mapping from form_id to PDF filename
$formPdfMap = [
    'KP-Form-01' => 'KP Form 1 - Notice to Constitute Lupon.pdf',
    'KP-Form-02' => 'KP Form 2 - Appointment.pdf',
    'KP-Form-03' => 'KP Form 3.pdf',
    'KP-Form-04' => 'KP Form 4 - List of Appointed Lupon Members.pdf',
    'KP-Form-05' => 'KP Form 5 - Oath of Office.pdf',
    'KP-Form-06' => 'KP Form 6.pdf',
    'KP-Form-07' => 'KP Form 7.pdf',
    'KP-Form-08' => 'KP Form 8.pdf',
    'KP-Form-09' => 'KP Form 9.pdf',
    'KP-Form-10' => 'KP Form 10.pdf',
    'KP-Form-11' => 'KP Form 11.pdf',
    'KP-Form-12' => 'KP Form 12.pdf',
    'KP-Form-13' => 'KP Form 13.pdf',
    'KP-Form-14' => 'KP Form 14.pdf',
    'KP-Form-15' => 'KP Form 15.pdf',
    'KP-Form-16' => 'KP Form 16.pdf',
    'KP-Form-17' => 'KP Form 17.pdf',
    'KP-Form-18' => 'KP Form 18.pdf',
    'KP-Form-19' => 'KP Form 19.pdf',
    'KP-Form-20' => 'KP Form 20.pdf',
    'KP-Form-20-A' => 'KP Form 20-A.pdf',
    'KP-Form-20-B' => 'KP Form 20-B.pdf',
    'KP-Form-21' => 'KP Form 21.pdf',
    'KP-Form-22' => 'KP Form 22.pdf',
    'KP-Form-23' => 'KP Form 23.pdf',
    'KP-Form-24' => 'KP Form 24.pdf',
    'KP-Form-25' => 'KP Form 25.pdf',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $formTitle ?> - Barangay Panducot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
   
    <?php include 'sidebar_.php';?>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                font-size: 12pt;
            }
            .print-container {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            .print-header {
                text-align: center;
                margin-bottom: 20px;
            }
            .form-field {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body class="bg-blue-50">
    <?php include '../includes/barangay_official_sec_nav.php'; ?>
    <div class="container mx-auto mt-10 no-print">
        <div class="bg-white p-5 shadow-md rounded-lg">
            <h2 class="text-2xl font-semibold text-blue-800 mb-6"><?= $formTitle ?></h2>
            
            <p class="mb-6 text-gray-700"><?= $formDescription ?></p>
            
            <?php if (!$case_id): ?>
            <!-- Form Selection and Case Selection -->
            <div class="mb-6">
                <form method="get" class="space-y-4">
                    <div>
                        <?php
                        // Display the selected KP form name and description
                        $formNames = [
                            'KP-Form-01' => 'Notice to Constitute the Lupon',
                            'KP-Form-02' => 'Appointment Letter',
                            'KP-Form-03' => 'Notice of Appointment',
                            'KP-Form-04' => 'List of Appointed Lupon Members',
                            'KP-Form-05' => 'Lupon Member Oath Statement',
                            'KP-Form-06' => 'Withdrawal of Appointment',
                            'KP-Form-07' => 'Complainant\'s Form',
                            'KP-Form-08' => 'Notice of Hearing',
                            'KP-Form-09' => 'Summon for the Respondent',
                            'KP-Form-10' => 'Notice for Constitution of Pangkat',
                            'KP-Form-11' => 'Notice to Chosen Pangkat Member',
                            'KP-Form-12' => 'Notice of Hearing (Conciliation Proceedings)',
                            'KP-Form-13' => 'Subpoena Letter',
                            'KP-Form-14' => 'Agreement for Arbitration',
                            'KP-Form-15' => 'Arbitration Award',
                            'KP-Form-16' => 'Amicable Settlement',
                            'KP-Form-17' => 'Repudiation',
                            'KP-Form-18' => 'Notice of Hearing for Complainant',
                            'KP-Form-19' => 'Notice of Hearing for Respondent',
                            'KP-Form-20' => 'Certification to File Action (from Lupon Secretary)',
                            'KP-Form-21' => 'Certification to File Action (from Pangkat Secretary)',
                            'KP-Form-22' => 'Certification to File Action',
                            'KP-Form-23' => 'Certification to Bar Action',
                            'KP-Form-24' => 'Certification to Bar Counterclaim',
                            'KP-Form-25' => 'Motion for Execution',
                            'KP-Form-26' => 'Notice of Hearing (Re: Motion for Execution)',
                            'KP-Form-27' => 'Notice of Execution',
                            'KP-Form-28' => 'Monthly Transmittal of Final Reports',
                        ];
                        if ($form_id && isset($formNames[$form_id])) {
                            echo '<div class="text-lg font-semibold text-blue-800">' . htmlspecialchars($form_id) . ': ' . htmlspecialchars($formNames[$form_id]) . '</div>';
                        } else {
                            echo '<div class="text-lg text-red-600">No KP form selected. Please select a form from the main list.</div>';
                        }
                        ?>
                    </div>
                    
                    <div>
                        <label for="case" class="block text-sm font-medium text-gray-700">Select Case</label>
                        <select id="case" name="case" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Select a Case --</option>
                            <?php if (empty($cases)): ?>
                                <option value="" disabled>No cases available in the database</option>
                                <?php if (isset($_GET['debug'])): ?>
                                    <div class="mt-2 text-sm text-red-600">
                                        Debug: Found <?= count($cases) ?> cases. 
                                        Check error logs for more details.
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php foreach ($cases as $case): ?>
                                    <option value="<?= $case['Case_ID'] ?>" <?= ($case_id == $case['Case_ID']) ? 'selected' : '' ?>>
                                        <?= $case['Case_ID'] ?>: <?= htmlspecialchars($case['Complaint_Title']) ?> 
                                        (<?= htmlspecialchars($case['Complainant_First'] . ' ' . $case['Complainant_Last']) ?> vs 
                                        <?= htmlspecialchars($case['Respondent_First'] . ' ' . $case['Respondent_Last']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (empty($cases)): ?>
                            <div class="mt-2 text-sm text-amber-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                No cases found. Please ensure there are cases in the database.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                    
                        <?php
$pdfFile = isset($formPdfMap[$form_id]) ? $formPdfMap[$form_id] : '';
if ($pdfFile && file_exists(__DIR__ . '/KP-Forms/' . $pdfFile)) {
    echo '<a href="KP-Forms/' . rawurlencode($pdfFile) . '" target="_blank" class="ml-2 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700"><i class="fas fa-file-alt mr-1"></i> Print Form</a>';
} else {
    echo '<button disabled class="ml-2 bg-gray-400 text-white py-2 px-4 rounded-lg cursor-not-allowed flex items-center"><i class="fas fa-file-alt mr-1"></i> Print Form</button>';
}
?>
                        <a href="../SecMenu/KP-Forms/fill_kp_forms.php" class="ml-2 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-note-sticky mr-1"></i> Fill Out Form
                        </a>
                        <a href="view_kp_forms.php" class="ml-2 bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600">
                            Back to Forms
                        </a>
                    </div>
                </form>
            </div>
       
            
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($case_id): ?>
    <?php
    // Fetch selected case details
    $selectedCase = null;
    foreach ($cases as $case) {
        if ($case['Case_ID'] == $case_id) {
            $selectedCase = $case;
            break;
        }
    }
    
    // Check if case was found
    if (!$selectedCase) {
        echo '<div class="container mx-auto mt-10 no-print">';
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">';
        echo '<strong>Error:</strong> Case not found. Please select a valid case.';
        echo '</div>';
        echo '</div>';
        exit;
    }
    ?>
        <?php endif; ?>
    
</body>
</html>
