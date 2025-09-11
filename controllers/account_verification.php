<?php
session_start();
require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';
require '../phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../server/server.php'; 

if (isset($_POST['verify'])) {
    $residentId = $_POST['id'];

    // 1. Get user details
    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM resident_info WHERE resident_id = ?");
    $stmt->bind_param("i", $residentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $resident = $result->fetch_assoc();

    if ($resident) {

        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);  // ensures 4 digits
        $year = date("Y");
        //para sa username
        $generatedUsername = "R-{$random}{$residentId}{$year}";
        // para maging is verify true
        $update = $conn->prepare("UPDATE resident_info SET isverify = 1, resident_username = ? WHERE resident_id = ?");
        $update->bind_param("si", $generatedUsername, $residentId);
        $update->execute();

            $stmt = $conn->prepare("SELECT resident_username FROM resident_info WHERE resident_id = ?");
            $stmt->bind_param("i", $residentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $resident_username = $result->fetch_assoc();


        // 3. Send email to the user
        $mail = new PHPMailer(true);
        try {
            // SMTP settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            // 🔸 Replace this with your real Gmail + App Password
            $mail->Username = 'vincentaaronvicente7@gmail.com';        
            $mail->Password = 'qedl ibzg hqer soez';       

            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Email content
            $mail->setFrom('vincentaaronvicente7@gmail.com.com', 'Barangay Secretary');  // Sender
            $mail->addAddress($resident['email'], $resident['first_name']." ".$resident['last_name']);  // Receiver

            $mail->isHTML(true);
            $mail->Subject = 'Your Barangay Account is Verified';
            $mail->Body = "
                <p>Dear <strong>{$resident['first_name']} {$resident['last_name']}</strong>,</p>
                <p>Your account has been successfully <strong>verified</strong> by the Barangay system.</p>
                <p>You may now fully access the system using the username below. Thank you!</p><br>
                <h2>Username,</h2>
                <p><strong>{$resident_username['resident_username']}</strong></p>
                <br><p>Regards,<br>Barangay Admin</p>
            ";

            $mail->send();
            header("Location: ../SecMenu/notifications-secretary.php?user=verified");
            exit;
        } catch (Exception $e) {
            die("Verification succeeded but email failed to send: {$mail->ErrorInfo}");
        }
    } else {
        die("User not found.");
    }
}
// OPTIONAL: handle remove
if (isset($_POST['remove'])) {
    $residentId = $_POST['id'];
    $delete = $conn->prepare("DELETE FROM residents_info WHERE resident_id = ?");
    $delete->bind_param("i", $residentId);
    $delete->execute();
    $_SESSION['message'] = 'Account removed.';
    header("Location: ../SecMenu/notifications-secretary.php?user=remove");
    exit;
}

?>
