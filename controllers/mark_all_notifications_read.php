<?php
session_start();
include '../server/server.php';

header('Content-Type: application/json');

// Read optional JSON body
$raw = file_get_contents('php://input');
$scope = '';
if ($raw) {
	$data = json_decode($raw, true);
	if (json_last_error() === JSON_ERROR_NONE) {
		$scope = strtolower(trim((string)($data['scope'] ?? '')));
	}
}

$ok = false;

// Secretary and Captain: mark all core types
if ($scope === 'secretary' || $scope === 'captain' || $scope === '') {
	$sql = "UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND type IN ('Unverified','Hearing','Complaint','Case')";
	$ok = (bool)$conn->query($sql);
}

// Lupon: mark only this lupon's notifications
if ($scope === 'lupon') {
	$luponId = isset($_SESSION['official_id']) ? (int)$_SESSION['official_id'] : 0;
	if ($luponId > 0) {
		if ($st = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND lupon_id = ?")) {
			$st->bind_param('i', $luponId);
			$st->execute();
			$ok = true;
			$st->close();
		}
	}
}

// Resident: mark only this resident's notifications
if ($scope === 'resident') {
	$residentId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
	if ($residentId > 0) {
		if ($st = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE resident_id = ?")) {
			$st->bind_param('i', $residentId);
			$st->execute();
			$ok = true;
			$st->close();
		}
	}
}

// External: attempt to detect correct FK column and mark for current external user
if ($scope === 'external') {
	$extId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; // external user session uses user_id in this app
	if ($extId > 0) {
		// Try common columns
		$columns = [];
		if ($res = $conn->query("SHOW COLUMNS FROM notifications")) {
			while ($c = $res->fetch_assoc()) { $columns[] = $c['Field']; }
		}
		$candidates = ['external_user_id','external_complainant_id','external_complaint_id'];
		$targetCol = '';
		foreach ($candidates as $cand) { if (in_array($cand, $columns, true)) { $targetCol = $cand; break; } }
		if ($targetCol !== '') {
			$sql = "UPDATE notifications SET is_read = 1 WHERE $targetCol = ?";
			if ($st = $conn->prepare($sql)) {
				$st->bind_param('i', $extId);
				$st->execute();
				$ok = true;
				$st->close();
			}
		}
	}
}

echo json_encode(['success' => $ok]);
?>