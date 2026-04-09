<?php
// update_activity.php
session_start();

// Optional: validate request (recommended)
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['active'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
}

echo json_encode(["status" => "ok"]);