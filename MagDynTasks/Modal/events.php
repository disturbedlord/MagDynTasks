<?php
session_start();
require '../../config.php';
require 'helper.php';
require "../utils/dbscripts.php";
date_default_timezone_set('Asia/Kolkata');

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
        $department = $_POST['assignees'];
        $assigneesList = explode(",", $department);

        $queriesExecuted = 0;
        // echo $task . "<br/>" . $cron . "<br/>" . $priority . "<br/>" . $user . "<br/>" . $title . "<br/>" . $done . "<br/>" . $time . "<br/>" . $department;
        $stmt = $con->prepare("INSERT INTO events(uid , description , done , time , priority , title , cron) VALUES(?,?,?,?,?,?,?)");
        // bind parameters
        $stmt->bind_param(
            "isissss",
            $user,       // uid (int)
            $task,       // description (string)
            $done,       // done (int)
            $time,       // time (string)
            $priority,   // priority (string) → s
            $title,      // title (string)
            $cron        // cron (string)
        );
        $dbCall = $stmt->execute();
        $eventId = $stmt->insert_id;
        $taskAssigneesCreated = createTaskAssignessEntry($eventId, $assigneesList);

        if ($stmt->affected_rows > 0 && $taskAssigneesCreated > 0) {
            echo json_encode(["status" => true, "isEdit" => false]);
        } else {
            echo json_encode(["status" => false, "isEdit" => false]);
        }
        break;
    }
    case "editTask": {
        $description = $_POST["description"];
        $id = $_POST["id"];
        $cron = $_POST["cron"];
        $priority = $_POST["priority"];
        $createdBy = $_SESSION['user_id'];
        $assignees = explode(",", $_POST["assignees"]);
        $title = $_POST["title"];
        $done = 0;
        $time = date("Y-m-d H:i:s"); // current timestamp
        // Update Events
        $stmt = $con->prepare("Update events set uid=? , description=? , done=? , time=? , priority=? , title=? , cron=? WHERE id=?");
        // bind parameters
        $stmt->bind_param(
            "isissssi", // types: i=int, s=string
            $createdBy,      // uid (int)
            $description,      // description (string)
            $done,      // done (int)
            $time,      // time (string)
            $priority,  // priority (int)
            $title,     // title (string)
            $cron,      // cron (string)
            $id // Id(int)
        );
        $stmt->execute();

        // Delete all existing task_assignees
        $stmtDeleteExistingAssignees = $con->prepare("delete from task_assignees where event_id = ?");
        $stmtDeleteExistingAssignees->bind_param("i", $id);
        $stmtDeleteExistingAssignees->execute();

        $taskAssigneesCreated = createTaskAssignessEntry($id, $assignees);

        if ($stmt->affected_rows > 0 && $taskAssigneesCreated) {
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

function createTaskAssignessEntry($eventId, $assigneesList)
{
    global $con;
    $assignee_query = "INSERT INTO task_assignees(assignee , event_id) values (? , ?)";


    $stmt = $con->prepare($assignee_query);
    foreach ($assigneesList as $assignee) {
        $stmt->bind_param("ii", $assignee, $eventId);
        $stmt->execute();

    }

    return $stmt->affected_rows;
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