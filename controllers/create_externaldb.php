<?php
session_start();
include '../server/server.php';
require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';
require '../phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['Signup'])) {
    $fname   = $_POST['reg_fname'];
    $lname   = $_POST['reg_lname'];
    $mname   = $_POST['reg_mname'];
    $address = $_POST['reg_address'];
    $email   = $_POST['reg_email'];
    $contact = $_POST['reg_contact'];
    $password = password_hash($_POST['reg_pass'], PASSWORD_DEFAULT);

    // 1. Insert user without username yet
    $sql = "INSERT INTO external_complainant 
        (first_name, last_name, middle_name, address, email, contact_number, password)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $fname, $lname, $mname, $address, $email, $contact, $password);

    if ($stmt->execute()) {
        $externalId = $conn->insert_id;

        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $year = date("Y");
        $external_username = "E-{$random}{$externalId}{$year}";

        // 3. Update the record with the generated username
        $update = $conn->prepare("UPDATE external_complainant SET isActive = 1, external_username = ? WHERE external_complaint_id = ?");
        $update->bind_param("si", $external_username, $externalId);
        $update->execute();

        // 4. Send email with the generated username
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'vincentaaronvicente7@gmail.com';
            $mail->Password = 'qedl ibzg hqer soez';  // App password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('vincentaaronvicente7@gmail.com', 'Barangay Secretary');
            $mail->addAddress($email, "$fname $lname");

            $mail->isHTML(true);
            $mail->Subject = 'Your Barangay External Account Has Been Created';
            $mail->Body = "
                <p>Dear <strong>$fname $lname</strong>,</p>
                <p>Welcome! Your account has been successfully created.</p>
                <p>Please use the following username to access your account:</p>
                <h3>$external_username</h3>
                <p>Thank you for using our system.</p>
                <br>
                <p>Regards,<br>Barangay Admin</p>
            ";

            $mail->send();

            echo '<script>alert("Sign up successful! Username sent via email."); window.location.href="../SecMenu/add_external_user.php";</script>';
            exit;
        } catch (Exception $e) {
            echo '<script>alert("Signup succeeded but email failed to send: '. $mail->ErrorInfo .'"); window.location.href="../SecMenu/add_external_user.php";</script>';
            exit;
        }
    } else {
        echo '<script>alert("Registration failed."); window.location.href="../SecMenu/add_external_user.php";</script>';
    }

    $stmt->close();
    $conn->close();

} else {
    echo '<script>alert("Invalid access."); window.location.href="../SecMenu/add_external_user.php";</script>';
}
?>
