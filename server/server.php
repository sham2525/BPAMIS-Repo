<?php
$dbhost = "localhost";
$dbname = "barangay_case_management";
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if($conn->connect_errno){
    die("Connection failed: " . $conn->connect_error);
}
if(isset($SESSION)){
    session_start();
}
?>