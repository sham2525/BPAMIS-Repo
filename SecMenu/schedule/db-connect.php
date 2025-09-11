<?php
$host     = 'localhost';
$username = 'root';
$password = '';
$dbname   ='barangay_case_management';

$conn = new mysqli($host, $username, $password, $dbname);
if(!$conn){
    die("Cannot connect to the database.". $conn->error);
}
$editMode = false;
$editData = [];

if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $editMode = true;
    $editId = intval($_GET['edit_id']);

    $result = $conn->query("SELECT * FROM schedule_list WHERE hearingID = $editId LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $editData = $result->fetch_assoc();
        // Separate datetime
        $dateTime = explode(' ', $editData['hearingDateTime']);
        $editData['date'] = $dateTime[0];
        $editData['time'] = substr($dateTime[1], 0, 5);
    }
}
?>