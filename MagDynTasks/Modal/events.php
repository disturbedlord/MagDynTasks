<?php
session_start();
require '../../config.php';
$action = isset($_POST['action']) ? $_POST['action'] : '';
switch ($action) {
    case "delete": {
        $id = $_POST['id'];
        $stmt = $con->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["status" => "success"]);
        break;
    }
    case "markFinished": {
        $id = $_POST['id'];
        $stmt = $con->prepare("UPDATE events SET done=1 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["status" => "success"]);
        break;
    }
    case "markPending": {
        $id = $_POST['id'];
        $stmt = $con->prepare("UPDATE events SET done=0 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["status" => "success"]);
        break;
    }
    case "addTask": {
        $task = isset($_POST["description"]) ? $_POST["description"] : "";
        $cron = isset($_POST["cron"]) ? $_POST["cron"] : "";
        $priority = isset($_POST["priority"]) ? $_POST["priority"] : "";
        $user = $_SESSION['user_id'];
        $title = isset($_POST["title"]) ? $_POST["title"] : "";
        $done = 0;
        $time = date("Y-m-d H:i:s"); // current timestamp
        $department = $_POST['user'];
        // echo $task . "<br/>" . $cron . "<br/>" . $priority . "<br/>" . $user . "<br/>" . $title . "<br/>" . $done . "<br/>" . $time . "<br/>" . $department;
        $stmt = $con->prepare("INSERT INTO events(uid , description , done , time , priority , title , department , cron) VALUES(?,?,?,?,?,?,?,?)");
        // bind parameters
        $stmt->bind_param(
            "isisssis",
            $user,       // uid (int)
            $task,       // description (string)
            $done,       // done (int)
            $time,       // time (string)
            $priority,   // priority (string) → s
            $title,      // title (string)
            $department, // department (int) → i
            $cron        // cron (string)
        );
        $dbCall = $stmt->execute();
        echo json_encode(["status" => true, "isEdit" => false]);
        break;
    }
    case "editTask": {
        $description = $_POST["description"];
        $id = $_POST["id"];
        $cron = $_POST["cron"];
        $priority = $_POST["priority"];
        $user = $_POST["user"];
        $title = $_POST["title"];
        $done = 0;
        $time = date("Y-m-d H:i:s"); // current timestamp
        $department = $_POST["department"];
        $stmt = $con->prepare("Update events set uid=? , description=? , done=? , time=? , priority=? , title=? , department=?,cron=? WHERE id=?");
        // bind parameters
        $stmt->bind_param(
            "isisssssi", // types: i=int, s=string
            $user,      // uid (int)
            $description,      // description (string)
            $done,      // done (int)
            $time,      // time (string)
            $priority,  // priority (int)
            $title,     // title (string)
            $department,// department (string)
            $cron,      // cron (string)
            $id // Id(int)
        );
        $stmt->execute();
        echo json_encode(["status" => true, "isEdit" => true]);
        break;
    }
}