<?php
/**
 * View KP Forms Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KP Forms - Barangay Panducot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
    <?php include 'sidebar_.php';?>
</head>
<body class="bg-gray-50 font-sans">
    <?php include '../includes/barangay_official_sec_nav.php'; ?>

    <!-- Page Header -->
    <section class="container mx-auto mt-8 px-4">
        <div class="gradient-bg rounded-xl shadow-sm p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-20 -mt-20 opacity-70"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-primary-200 rounded-full -ml-10 -mb-10 opacity-60"></div>
            <div class="relative z-10">
                <h1 class="text-2xl font-medium text-primary-800">Katarungang Pambarangay (KP) forms</h1>
                <p class="mt-1 text-gray-600">The Katarungang Pambarangay (KP) forms are official documents used in the barangay justice system. 
                These forms help in documenting various stages of the dispute resolution process as mandated by 
                the Katarungang Pambarangay Law (P.D. 1508 and R.A. 7160).</p>
            </div>
        </div>
    </section>

    <div class="container  mx-auto mt-8 px-4 bg-gray-50">
      
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            // Official KP Forms based on DILG Katarungang Pambarangay Handbook
            $kpForms = [
                [
                    'id' => 'KP-Form-01',
                    'name' => 'Notice to Constitute the Lupon',
                    'description' => 'Official notice for the constitution of the Lupong Tagapamayapa.',
                    'icon' => 'fa-users'
                ],
                [
                    'id' => 'KP-Form-02',
                    'name' => 'Appointment Letter',
                    'description' => 'Letter of appointment for Lupon members.',
                    'icon' => 'fa-envelope'
                ],
                [
                    'id' => 'KP-Form-03',
                    'name' => 'Notice of Appointment',
                    'description' => 'Notice informing of appointment to the Lupon.',
                    'icon' => 'fa-bell'
                ],
                [
                    'id' => 'KP-Form-04',
                    'name' => 'List of Appointed Lupon Members',
                    'description' => 'Official list of appointed Lupon members.',
                    'icon' => 'fa-list'
                ],
                [
                    'id' => 'KP-Form-05',
                    'name' => 'Lupon Member Oath Statement',
                    'description' => 'Oath statement for Lupon members.',
                    'icon' => 'fa-hand-holding-heart'
                ],
                [
                    'id' => 'KP-Form-06',
                    'name' => 'Withdrawal of Appointment',
                    'description' => 'Form for withdrawal of Lupon appointment.',
                    'icon' => 'fa-times-circle'
                ],
                [
                    'id' => 'KP-Form-07',
                    'name' => 'Complainant\'s Form',
                    'description' => 'Official form to be filled out by the complainant to initiate a case.',
                    'icon' => 'fa-file-signature'
                ],
                [
                    'id' => 'KP-Form-08',
                    'name' => 'Notice of Hearing',
                    'description' => 'Notice sent to parties for scheduled hearing.',
                    'icon' => 'fa-calendar-check'
                ],
                [
                    'id' => 'KP-Form-09',
                    'name' => 'Summon for the Respondent',
                    'description' => 'Official summons requiring the respondent to appear before the Lupong Tagapamayapa.',
                    'icon' => 'fa-gavel'
                ],
                [
                    'id' => 'KP-Form-10',
                    'name' => 'Notice for Constitution of Pangkat',
                    'description' => 'Notice for the constitution of the Pangkat ng Tagapagkasundo.',
                    'icon' => 'fa-user-friends'
                ],
                [
                    'id' => 'KP-Form-11',
                    'name' => 'Notice to Chosen Pangkat Member',
                    'description' => 'Notice to inform chosen Pangkat members.',
                    'icon' => 'fa-user-check'
                ],
                [
                    'id' => 'KP-Form-12',
                    'name' => 'Notice of Hearing (Conciliation Proceedings)',
                    'description' => 'Notice for conciliation proceedings before the Pangkat.',
                    'icon' => 'fa-handshake'
                ],
                [
                    'id' => 'KP-Form-13',
                    'name' => 'Subpoena Letter',
                    'description' => 'Subpoena to compel attendance of witnesses.',
                    'icon' => 'fa-envelope-open-text'
                ],
                [
                    'id' => 'KP-Form-14',
                    'name' => 'Agreement for Arbitration',
                    'description' => 'Agreement to submit the dispute to arbitration by the Punong Barangay.',
                    'icon' => 'fa-balance-scale'
                ],
                [
                    'id' => 'KP-Form-15',
                    'name' => 'Arbitration Award',
                    'description' => 'Decision rendered by the Punong Barangay after arbitration.',
                    'icon' => 'fa-trophy'
                ],
                [
                    'id' => 'KP-Form-16',
                    'name' => 'Amicable Settlement',
                    'description' => 'Written agreement between the parties resolving their dispute.',
                    'icon' => 'fa-file-contract'
                ],
                [
                    'id' => 'KP-Form-17',
                    'name' => 'Repudiation',
                    'description' => 'Form for repudiation of amicable settlement.',
                    'icon' => 'fa-undo'
                ],
                [
                    'id' => 'KP-Form-18',
                    'name' => 'Notice of Hearing for Complainant',
                    'description' => 'Notice of hearing specifically for the complainant.',
                    'icon' => 'fa-calendar-alt'
                ],
                [
                    'id' => 'KP-Form-19',
                    'name' => 'Notice of Hearing for Respondent',
                    'description' => 'Notice of hearing specifically for the respondent.',
                    'icon' => 'fa-calendar-alt'
                ],
                [
                    'id' => 'KP-Form-20',
                    'name' => 'Certification to File Action (from Lupon Secretary)',
                    'description' => 'Certification from Lupon Secretary allowing parties to file in court.',
                    'icon' => 'fa-certificate'
                ],
                [
                    'id' => 'KP-Form-21',
                    'name' => 'Certification to File Action (from Pangkat Secretary)',
                    'description' => 'Certification from Pangkat Secretary allowing parties to file in court.',
                    'icon' => 'fa-certificate'
                ],
                [
                    'id' => 'KP-Form-22',
                    'name' => 'Certification to File Action',
                    'description' => 'General certification allowing parties to file a case in court after failed settlement.',
                    'icon' => 'fa-certificate'
                ],
                [
                    'id' => 'KP-Form-23',
                    'name' => 'Certification to Bar Action',
                    'description' => 'Certification barring complainant from filing action in court.',
                    'icon' => 'fa-ban'
                ],
                [
                    'id' => 'KP-Form-24',
                    'name' => 'Certification to Bar Counterclaim',
                    'description' => 'Certification barring respondent from filing counterclaim in court.',
                    'icon' => 'fa-ban'
                ],
                [
                    'id' => 'KP-Form-25',
                    'name' => 'Motion for Execution',
                    'description' => 'Motion requesting execution of amicable settlement or arbitration award.',
                    'icon' => 'fa-clipboard-check'
                ],
                [
                    'id' => 'KP-Form-26',
                    'name' => 'Notice of Hearing (Re: Motion for Execution)',
                    'description' => 'Notice of hearing for motion for execution.',
                    'icon' => 'fa-calendar-check'
                ],
                [
                    'id' => 'KP-Form-27',
                    'name' => 'Notice of Execution',
                    'description' => 'Notice informing parties of the execution of amicable settlement or arbitration award.',
                    'icon' => 'fa-clipboard-check'
                ],
                [
                    'id' => 'KP-Form-28',
                    'name' => 'Monthly Transmittal of Final Reports',
                    'description' => 'Monthly report of final settlements and arbitration awards.',
                    'icon' => 'fa-chart-bar'
                ]
            ];
            
            // Mapping from form_id to PDF filename (copied from print_kp_forms.php)
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
            
            foreach ($kpForms as $form) {
                echo '<div class="bg-white rounded-lg shadow-sm p-6 flex flex-col hover:shadow-md transition-shadow">';
                echo '<div class="flex items-center mb-4">';
                echo '<span class="text-blue-600 bg-blue-100 p-3 rounded-full mr-4"><i class="fas ' . $form['icon'] . ' text-xl"></i></span>';
                echo '<h3 class="text-lg font-semibold">' . $form['id'] . ': ' . $form['name'] . '</h3>';
                echo '</div>';
                echo '<p class="text-gray-600 flex-grow">' . $form['description'] . '</p>';
                echo '<div class="flex justify-between mt-4 pt-4 border-t border-gray-200">';
                // View Template button (unchanged)
                echo '<a href="../SecMenu/KP-Forms/fill_kp_forms.php" class="text-blue-600 hover:underline flex items-center">';
                echo '<i class="fas fa-edit mr-1"></i> Fill Out Template';
                echo '</a>';
                // Print Form button (new logic)
                $pdfFile = isset($formPdfMap[$form['id']]) ? $formPdfMap[$form['id']] : '';
                if ($pdfFile && file_exists(__DIR__ . '/KP-Forms/' . $pdfFile)) {
                    echo '<a href="KP-Forms/' . rawurlencode($pdfFile) . '" target="_blank" class="text-green-600 hover:underline flex items-center"><i class="fas fa-print mr-1"></i> Print Form</a>';
                } else {
                    echo '<span class="text-gray-400 flex items-center cursor-not-allowed"><i class="fas fa-print mr-1"></i> Print Form</span>';
                }
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="container mt-8 mb-8 p-4 bg-blue-100 rounded-lg">
            <h3 class="text-xl font-semibold text-blue-800">Need Help?</h3>
            <p class="mt-2 text-blue-700">
                For guidance on how to properly fill out these forms or for other legal inquiries, 
                please consult with the DILG staff or LGU staff.            </p>
        </div>
    </div>
    
    <!-- Case Assistant Chatbot -->
    <?php include '../chatbot/bpamis_case_assistant.php'?>
</body>
</html>
