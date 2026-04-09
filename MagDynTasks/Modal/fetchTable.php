<?php
require '../../config.php';
require "../utils/dbscripts.php";
require "helper.php";
session_start();
header('Content-Type: application/json');

$draw = isset($_GET['draw']) ? $_GET["draw"] : 1;
$start = isset($_GET["start"]) ? $_GET["start"] : 0;
$length = isset($_GET["length"]) ? $_GET["length"] : 50;
$loggedInUserId = $_SESSION["user_id"];
$export = isset($_GET["export"]) ? $_GET["export"] : false;

$isAdminFlag = $_GET["isAdmin"] ? true : false;

// Nested filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : [];

$title = isset($filter['title']) ? $filter['title'] : '';
$status = isset($filter['status']) ? $filter['status'] : '';
$priority = isset($filter['priority']) ? $filter['priority'] : '';
$sortDate = isset($filter["sortDate"]) ? $filter["sortDate"] : "0";
$sortByDate = (isset($filter["sortByDate"]) && $filter["sortByDate"] === "1") ? true : false;
$sortPriority = isset($filter["sortPriority"]) ? $filter["sortPriority"] : "0";
$sortByPriority = (isset($filter["sortByPriority"]) && $filter["sortByPriority"] === "1") ? true : false;

$uid = isset($filter['uid']) ? $filter['uid'] : '';
$uidArray = explode(",", $uid);


$appliedFilters = [];


if ($loggedInUserId == null || $loggedInUserId == "")
    exit;


$stmt = '';
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

$query = $EVENTQUERY;
// query for counting filtered rows
$countQuery = $EVENTQUERYCOUNT;

$params = [];
$types = "";
$isWHEREAdded = false;

if (!$isAdminFlag) {
    $appliedFilters[] = " WHERE e.department = ?";
    $params[] = $loggedInUserId;
    $types .= "i";
    $isWHEREAdded = true;
}

if (($title || $status !== '' || $priority || $uid) && $isAdminFlag) {
    // If $isAdminFlag and first render, then filter will be empty and no WHERE clause in $query
    // if !$isAdminFlag then always has a WHERE clause due to default search
    // So Add WHERE clause in case if some filter is truthy and is Admin 
    $query .= !$isWHEREAdded ? " WHERE " : "";
    $countQuery .= !$isWHEREAdded ? " WHERE " : "";


}

if ($title) {
    $appliedFilters[] = $searchTitle;
    $params[] = "%{$title}%";
    $types .= "s";
}
if ($priority) {
    $appliedFilters[] = $searchPriority;
    $params[] = $priority;
    $types .= "i";
}
if ($uid) {
    $placeholder = implode(",", array_fill(0, count($uidArray), "?"));
    $appliedFilters[] = " e.department in ({$placeholder})";
    $params = array_merge($params, $uidArray);
    $types .= implode("", array_fill(0, count($uidArray), "i"));
}
if ($status !== '') {
    $appliedFilters[] = $searchStatus;
    $params[] = $status;
    $types .= "i";
}

$query .= implode(" AND ", $appliedFilters);
$countQuery .= implode(" AND ", $appliedFilters);



$query .= " ORDER BY ";

if ($sortByDate) {
    $sortDateValue = $sortDate === "0" ? "DESC" : "ASC";
    $query .= "e.time {$sortDateValue} ";
} else if ($sortByPriority) {
    $sortPriorityValue = $sortPriority === "0" ? "ASC" : "DESC";
    $query .= " CAST(e.priority AS UNSIGNED) {$sortPriorityValue} ";
}


if (!$export)
    $query .= " LIMIT {$start} , {$length} ";


file_put_contents(
    "debug.log",
    print_r($_POST, true) . "\n" .
    print_r($query, true) . "\n" .
    print_r($params, true) . PHP_EOL,
    FILE_APPEND
);

file_put_contents(
    "debug.log",
    print_r($countQuery, true) . PHP_EOL,
    FILE_APPEND
);

$stmt = $con->prepare($query);
if (count($params) > 0)
    $stmt->bind_param($types, ...$params);



$stmt->execute();
$result = $stmt->get_result();
$filteredData = [];

// Get Records Count
$countStmt = $con->prepare($countQuery);
if (count($params) > 0)
    $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$countResult = $countStmt->get_result();
$filteredCount = $countResult->fetch_assoc()['TOTAL'];


while ($row = $result->fetch_assoc()) {
    $filteredData[] = parseRow($row);
}



echo json_encode([
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => $filteredCount,
    "data" => $filteredData
]);
