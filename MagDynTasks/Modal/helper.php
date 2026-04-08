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
    $userId = $row["user_account_id"];
    $date = date("d-m-y", strtotime($row['time']));
    $assginee = $row['assignee'];
    // 🕒 Format time
    $time = date("d M, g:i A", strtotime($row['time']));
    ;
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
        $userId,
        $cron,
        $department,
        $html,
        $date,
        $assginee,
        $name
    ];
}

