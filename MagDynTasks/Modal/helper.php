<?php

function parseRow($row)
{
    $id = $row['id'];
    $cron = htmlspecialchars($row["cron"]);
    $task = htmlspecialchars($row['description']);
    $status = $row['status'];
    $name = isset($row['user_name']) ? $row['user_name'] : 'Unknown';
    $title = $row["title"];
    $description = $row["description"];
    $creatorId = $row["user_account_id"];
    $assigneeId = $row["department"];
    $date = date("d-m-y", strtotime($row['time']));
    $dueDate = $row["due_date"];
    $assignees = explode(",", $row["assignees"]);
    $assigneesId = explode(",", $row["assigneesId"]);
    // 🕒 Format time
    $time = date("d M, g:i A", strtotime($row['time']));

    // Check if Past Due Date
    if ($dueDate) {
        $isPastDueDate = strtotime($dueDate) > time() ? false : true;
        $dueDate = date("Y-m-d", strtotime($dueDate));
    }

    $priority = $row['priority_id'];
    $department = $row["department"];
    // 🎨 Status color
    $statusColor = $status === 'finished'
        ? 'bg-green-400/10 text-green-600 inset-ring-green-500/20'
        : 'bg-yellow-400/10 text-yellow-600 inset-ring-yellow-400/20';

    $truncatedDescription = strlen($description) > 100 ? substr($description, 0, 100) . "..." : $description;
    $borderColor = $status !== 'pending' ? 'border-l-green-400' : 'border-l-yellow-400';
    // 🧱 HTML (Inbox UI)

    ob_start();
    include '../Templates/rowItem.php';
    $html = ob_get_clean();

    return [
        $id,
        $description,
        $status,
        $title,
        $priority,
        $creatorId,
        $cron,
        $department,
        $html,
        $date,
        $assginee,
        $name,
        $assigneesId,
        $assignees,
        $dueDate
    ];
}

