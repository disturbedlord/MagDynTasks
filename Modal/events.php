<?php

require __DIR__ . '/../db.php';
$action = $_POST['action'] ?? '';

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
    case "addTask": {
        $task = $_POST["description"] ?? "";
        $cron = $_POST["cron"] ?? "";
        $priority = $_POST["priority"] ?? "";
        $user = $_POST["user"] ?? "";
        $title = $_POST["title"] ?? "";
        $done = 0;
        $time = date("Y-m-d H:i:s"); // current timestamp
        $department = 1;
        $stmt = $con->prepare("INSERT INTO events(uid , description , done , time , priority , title , department , cron) VALUES(?,?,?,?,?,?,?,?)");
        // bind parameters
        $stmt->bind_param(
            "isisssss", // types: i=int, s=string
            $user,      // uid (int)
            $task,      // description (string)
            $done,      // done (int)
            $time,      // time (string)
            $priority,  // priority (int)
            $title,     // title (string)
            $department,// department (string)
            $cron       // cron (string)
        );
        $stmt->execute();
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
            "isissssii", // types: i=int, s=string
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