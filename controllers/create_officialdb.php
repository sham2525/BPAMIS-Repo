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
    $name    =  "$fname". " " . "$mname"." "."$lname";
    $contact = $_POST['reg_contact'];
    $email   = $_POST['reg_email'];
    $password = password_hash($_POST['reg_pass'], PASSWORD_DEFAULT);
    $position = $_POST['reg_type'];

    // Insert user without username yet
    $sql = "INSERT INTO barangay_officials 
        (Name, Contact_Number, email, password, Position)
        VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $contact, $email, $password, $position);

    if ($stmt->execute()) {
        $officialId = $conn->insert_id;

        // Generate official username: L-XXXX<ID><YEAR>
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $year = date("Y");
        if($position == "Lupon Tagapamayapa"){
            $official_username = "L-{$random}{$officialId}{$year}";   
        }else if($position == "Secretary"){
            $official_username = "S-{$random}{$officialId}{$year}";   
        }else if($position == "Barangay Captain"){
            $official_username = "C-{$random}{$officialId}{$year}";   
        }

        // Update username and activate account
        $update = $conn->prepare("UPDATE barangay_officials SET isActive = 1, official_username = ? WHERE Official_ID = ?");
        $update->bind_param("si", $official_username, $officialId);
        $update->execute();

        // Send Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'vincentaaronvicente7@gmail.com';
            $mail->Password = 'qedl ibzg hqer soez';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('vincentaaronvicente7@gmail.com', 'Barangay Secretary');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Your '.$position.' Account Has Been Created';
            $mail->Body = "
                <p>Dear <strong>$name</strong>,</p>
                <p>Your ".$position." account has been successfully created.</p>
                <p>Please use the following username to log in:</p>
                <h3>$official_username</h3>
                <br>
                <p>Regards,<br>Barangay Secretary</p>
            ";

            $mail->send();
             if($position == "Lupon Tagapamayapa"){
                 $official_username = "L-{$random}{$officialId}{$year}";   
            }else if($position == "Secretary"){
                $official_username = "S-{$random}{$officialId}{$year}";   
            }else if($position == "Barangay Captain"){
                $official_username = "C-{$random}{$officialId}{$year}";   
            }
            echo '<script>alert("'.$position.' account created! Username sent via email."); window.location.href="../SecMenu/home-secretary.php";</script>';
            exit;
        } catch (Exception $e) {
            echo '<script>alert("Account created but email failed to send: '. $mail->ErrorInfo .'"); window.location.href="../SecMenu/add_official_account.php";</script>';
            exit;
        }
    } else {
        echo '<script>alert("Registration failed."); window.location.href="../SecMenu/add_lupon_account.php";</script>';
    }

    $stmt->close();
    $conn->close();

} else {
    echo '<script>alert("Invalid access."); window.location.href="../SecMenu/add_lupon_account.php";</script>';
}
?>
