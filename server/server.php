<?php
$dbhost = "localhost";
$dbname = "barangay_case_management";
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if($conn->connect_errno){
    die("Connection failed: " . $conn->connect_error);
}

// Ensure a session is available for scripts that rely on $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>