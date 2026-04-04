<?php

require '../../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] == "" ? "" : $_POST["action"];

switch ($action) {
    case "login": {
        $email = $_POST['email'] == '' ? '' : $_POST['email'];
        $password = $_POST['password'] == '' ? '' : $_POST['password'];

        if (!$email || !$password) {
            echo json_encode([
                "status" => false,
                "message" => "Email and password required"
            ]);
            exit;
        }

        // Fetch user
        $stmt = $con->prepare("SELECT user_account_id, first_name, password_hash, admin_flag FROM user_account WHERE email_address = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {

            // Store session
            session_start();
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_account_id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['is_admin'] = $user['admin_flag'];

            echo json_encode([
                "status" => true
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "Invalid email or password"
            ]);
        }

        $stmt->close();
        break;
    }
    case "logout": {
        echo "Logout";
        session_start();

        // Unset all session variables
        $_SESSION = [];

        // Destroy the session
        session_destroy();

        // Optional: delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        echo json_encode([
            "status" => true
        ]);
        break;
    }
}