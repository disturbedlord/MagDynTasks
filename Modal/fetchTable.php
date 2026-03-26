<?php
require '../db.php';
require "../utils/dbscripts.php";

header('Content-Type: application/json');

$draw = $_GET['draw'] ?? 1;
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;
$loggedInUserId = $_GET["loggedInUserId"];
$isAdminFlag = $_GET["isAdmin"] ?? false;

// Nested filter
$filter = $_GET['filter'] ?? [];

$title = $filter['title'] ?? '';
$description = $filter['description'] ?? '';
$priority = $filter['priority'] ?? '';
$uid = $filter['uid'] ?? '';
$uidArray = explode(",", $uid);

$appliedFilters = [];


if ($loggedInUserId == null || $loggedInUserId == "")
    exit;


// 🔢 TOTAL COUNT
if ($isAdminFlag) {
    // Admin → all rows
    $stmt = $con->prepare("SELECT COUNT(*) as total FROM events");
} else {
    // User → only their rows
    $stmt = $con->prepare("SELECT COUNT(*) as total FROM events WHERE uid = ?");
    $stmt->bind_param("i", $loggedInUserId);
}

$stmt->execute();
$result = $stmt->get_result();
$totalData = $result->fetch_assoc()['total'];

$stmt->close();

$query = $EVENTQUERY; // EventQuery -> get all events from events table
$params = [];
$types = "";
$isWHEREAdded = false;

if (!$isAdminFlag) {
    $appliedFilters[] = " WHERE e.uid = ?";
    $params[] = $loggedInUserId;
    $types .= "i";
    $isWHEREAdded = true;
}

if (($title || $description || $priority || $uid) && $isAdminFlag) {
    // If $isAdminFlag and first render, then filter will be empty and no WHERE clause in $query
    // if !$isAdminFlag then always has a WHERE clause due to default search
    // So Add WHERE clause in case if some filter is truthy and is Admin 
    $query .= !$isWHEREAdded ? " WHERE " : "";


}

if ($title) {
    $appliedFilters[] = $searchTitle;
    $params[] = "%{$title}%";
    $types .= "s";
}
if ($description) {
    $appliedFilters[] = $searchDescription;
    $params[] = "%{$description}%";
    $types .= "s";
}
if ($priority) {
    $appliedFilters[] = $searchPriority;
    $params[] = $priority;
    $types .= "i";
}
if ($uid) {
    $placeholder = implode(",", array_fill(0, count($uidArray), "?"));
    $appliedFilters[] = " e.uid in ({$placeholder})";
    $params = array_merge($params, $uidArray);
    $types .= implode("", array_fill(0, count($uidArray), "i"));

}

$query .= implode(" AND ", $appliedFilters);



// ✅ ORDER + LIMIT (REQUIRED for DataTables)
$query .= " ORDER BY e.time DESC LIMIT ?, ?";


$params[] = $start;
$params[] = $length;
$types .= "ii";


file_put_contents(
    "debug.log",
    print_r($_POST, true) . "\n" .
    print_r($query, true) . "\n" .
    print_r($params, true) . PHP_EOL,
    FILE_APPEND
);

$stmt = $con->prepare($query);
$stmt->bind_param($types, ...$params);



$stmt->execute();
$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {

    $id = $row['id'];
    $cron = htmlspecialchars($row["cron"]);
    $task = htmlspecialchars($row['description']);
    $status = $row['status'];
    $name = $row['user_name'] ?? 'Unknown';
    $title = $row["title"];
    $description = $row["description"];
    $userId = $row["user_account_id"];
    // 🕒 Format time
    $time = date("g:iA", strtotime($row['time']));
    $priority = $row['priority_id'];
    $department = $row["department"];
    // 🎨 Status color
    $statusColor = $status === 'finished'
        ? 'bg-green-400/10 text-green-600 inset-ring-green-500/20'
        : 'bg-yellow-400/10 text-yellow-600 inset-ring-yellow-400/20';

    $truncatedDescription = strlen($description) > 100 ? substr($description, 0, 100) . "..." : $description;
    // ✂ Preview
    $preview = strlen($task) > 60
        ? substr($task, 0, 60) . '...'
        : $task;
    $borderColor = $status !== 'pending' ? 'border-l-green-400' : 'border-l-yellow-400';
    // 🧱 HTML (Inbox UI)

    ob_start();
    include '../Templates/rowItem.php';
    $html = ob_get_clean();

    $data[] = [
        $id,
        $description,
        $status,
        $title,
        $priority,
        $userId,
        $cron,
        $department,
        $html,


    ];
}

// 📤 RESPONSE
echo json_encode([
    "draw" => intval($draw),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalData),
    "data" => $data
]);