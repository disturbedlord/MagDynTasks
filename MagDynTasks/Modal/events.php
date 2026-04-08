<?php
session_start();
require '../../config.php';
require 'helper.php';
require "../utils/dbscripts.php";

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
    case "finished": {
        $id = $_POST['id'];
        updateStatus($id, 1);
        break;
    }
    case "pending": {
        $id = $_POST['id'];
        updateStatus($id, 0);
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
        if ($stmt->affected_rows > 0) {
            $row = fetchRecord($id);
            echo json_encode(["status" => true, "isEdit" => true, "data" => parseRow($row)]);

        } else {
            echo json_encode([
                "status" => "error",
                "message" => "No row updated (invalid ID or already updated)"
            ]);
        }
        break;
    }
}

function fetchRecord($recordId)
{
    global $con, $EVENTQUERY;

    // ✅ Fetch updated row
    $stmt2 = $con->prepare($EVENTQUERY . " WHERE e.id = ?;");
    $stmt2->bind_param("i", $recordId);
    $stmt2->execute();

    $result = $stmt2->get_result();
    $row = $result->fetch_assoc();
    return $row;
}

function updateStatus($id, $status)
{
    global $con;
    $stmt = $con->prepare("UPDATE events SET done=? WHERE id=?");
    $params = [$status, $id];
    $stmt->bind_param("ii", ...$params);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $row = fetchRecord($id);

        echo json_encode([
            "status" => "success",
            "data" => parseRow($row)
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No row updated (invalid ID or already updated)"
        ]);
    }
}