<?php
// check_session.php
session_start();

$timeout = 1800; // 30 min

if (
    isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY']) > $timeout
) {
    session_unset();
    session_destroy();

    echo json_encode(["expired" => true]);
    exit;
}

echo json_encode(["expired" => false]);