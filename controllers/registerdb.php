<?php

include '../server/server.php';

if (isset($_POST['Signup'])) {
    $fname = trim($_POST['reg_fname']);
    $lname = trim($_POST['reg_lname']);
    $mname = trim($_POST['reg_mname']);

    // Get house number and purok from form
    $house_no = trim($_POST['reg_house_no']);
    $purok = trim($_POST['reg_purok']);

    // Build the full address
    $address = "$house_no, $purok, Barangay Panducot, Calumpit, Bulacan";

    $email = trim($_POST['reg_email']);
    $password = password_hash($_POST['reg_pass'], PASSWORD_DEFAULT);

    // Use prepared statements for security
    $stmt = $conn->prepare("INSERT INTO resident_info (first_name, last_name, middle_name, address, email, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fname, $lname, $mname, $address, $email, $password);

    if ($stmt->execute()) {
        echo '<script>alert("Sign up successful! Welcome, ' . htmlspecialchars($fname) . ' ' . htmlspecialchars($lname) . '"); window.location.href="../bpamis_website/login.php";</script>';
        exit();
    } else {
        echo '<script>alert("Registration failed: ' . $stmt->error . '"); window.location.href="../bpamis_website/register.php";</script>';
    }

    $stmt->close();
    $conn->close();
} else {
    echo '<script>alert("SIGNUP Failed"); window.location.href="../bpamis_website/register.php";</script>';
}
?>


