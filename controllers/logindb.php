<?php
session_start();
include '../server/server.php';

$username = $_POST['login_user'] ?? '';
$password = $_POST['login_pass'] ?? '';
$isAjax = isset($_POST['ajax']);

// First character determines role type
$firstChar = strtoupper($username[0]);

switch ($firstChar) {
    case 'R':
        $query = "SELECT resident_id AS id, resident_username AS username, email, password FROM resident_info WHERE resident_username = ?";
        $redirect = "../ResidentMenu/home-resident.php";
        break;
    case 'E':
        $query = "SELECT external_complaint_id AS id, external_username AS username, email, password FROM external_complainant WHERE external_username = ?";
        $redirect = "../ExternalMenu/home-external.php";
        break;
    case 'S':
        $query = "SELECT official_id AS id, official_username AS username, email, password, Name FROM barangay_officials WHERE official_username = ?";
        $redirect = "../SecMenu/home-secretary.php";
        break;
    case 'L':
        $query = "SELECT official_id AS id, official_username AS username, email, password, Name FROM barangay_officials WHERE official_username = ?";
        $redirect = "../OfficialMenu/home-lupon.php";
        break;
    case 'C':
        $query = "SELECT official_id AS id, official_username AS username, email, password, Name FROM barangay_officials WHERE official_username = ?";
        $redirect = "../OfficialMenu/home-captain.php";
        break;
    default:
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Invalid credentials.']);
            exit;
        }
        header("Location: ../bpamis_website/login.php?login_error=true");
        exit;
}

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Query prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

   if (password_verify($password, $user['password'])) {
    $_SESSION['user'] = $user['email'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_id'] = $user['id'];

    // Only set official_id and name if it's an official login
    if (in_array($firstChar, ['S', 'L', 'C'])) {
        $_SESSION['official_id'] = $user['id'];
        $_SESSION['official_name'] = $user['Name'] ?? '';
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'redirect'=>$redirect]);
        exit;
    }
    header("Location: $redirect");
    exit;

    } else {
        // Wrong password
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Invalid username or password.']);
            exit;
        }
        header("Location: ../bpamis_website/login.php?error=invalid_password");
        exit;
    }
}

// Manual fallback (hardcoded accounts — optional or for testing only)
// if ($username === "secretary@gmail.com" && $password === '1234') {
//     header('Location: ../SecMenu/home-secretary.php');
// } else if ($username === "captain@gmail.com" && $password === '1234') {
//     header('Location: ../OfficialMenu/home-captain.php');
// } else if ($username === "lupon@gmail.com" && $password === '1234') {
//     header('Location: ../OfficialMenu/home-lupon.php');
// } else if ($username === "resident@gmail.com" && $password === '1234') {
//     header('Location: ../ResidentMenu/home-resident.php');
// } else if ($username === "external@gmail.com" && $password === '1234') {
//     header('Location: ../ResidentMenu/home-external.php');
// } else {
//     header("Location: ../bpamis_website/login.php?login_error=true");
//     exit;
// }

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'message'=>'Invalid username or password.']);
    exit;
}

?>
