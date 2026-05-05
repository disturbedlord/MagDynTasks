<?php
require '../../config.php';
require "../utils/dbscripts.php";
require "helper.php";

$searchQuery = $_GET["query"];
$page = $_GET["page"];

$searchQuery = strtolower($searchQuery);

function FindReservedQueryType($query)
{
    $RESERVED_KEYWORDS = ["pending", "finished", "overdue"];
    for ($i = 0; $i < 3; $i++) {
        if (strpos($RESERVED_KEYWORDS[$i], $query) === 0) {
            return $RESERVED_KEYWORDS[$i];
        }
    }
}

$queryType = FindReservedQueryType($searchQuery);

$placesToSearch = ["all"];

if ($queryType !== null && $queryType !== "")
    array_push($placesToSearch, $queryType);

$dbQuery = [];

for ($i = 0; $i < count($placesToSearch); $i++) {

    switch ($placesToSearch[$i]) {
        case "pending": {
            // search in status
            array_push($dbQuery, "SELECT id FROM events WHERE done = 0");
            break;
        }
        case "finished": {
            array_push($dbQuery, "SELECT id FROM events WHERE done = 1");
            break;
        }
        case "overdue": {
            // search in due date
            array_push($dbQuery, "SELECT id FROM events WHERE due_date < NOW()");
            break;
        }
        default: {
            // for any other query, search in title and userlist
            array_push($dbQuery, " SELECT id FROM events WHERE title LIKE '%{$searchQuery}%'");
            array_push($dbQuery, "SELECT id FROM events WHERE uid IN 
            (SELECT user_account_id FROM user_account WHERE first_name LIKE '%{$searchQuery}%')
            union
            SELECT event_id FROM task_assignees WHERE assignee IN 
            (SELECT user_account_id FROM user_account WHERE first_name LIKE '%{$searchQuery}%')");
        }
    }
}

$combinedQuery = implode(" UNION ", $dbQuery);

$finalQuery = $EVENTQUERY . " where e.id in (" . $combinedQuery . ") " . " GROUP BY e.id  ORDER BY e.time DESC  LIMIT {$page} , 20 ";

$countQuerySearchBar = "SELECT COUNT(*) AS TOTAL FROM (" . $combinedQuery . " ) AS RESULTS";
file_put_contents(
    "debugSearchBar.log",
    print_r($_GET, true) . "\n" .
    print_r($countQuerySearchBar, true) . "\n" .
    print_r($finalQuery, true) . "\n" . PHP_EOL,
    FILE_APPEND
);


$countStmt = $con->prepare($countQuerySearchBar);
if (count($params) > 0)
    $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$countResult = $countStmt->get_result();
$filteredCount = $countResult->fetch_assoc()['TOTAL'];

$stmt = $con->prepare($finalQuery);
$stmt->execute();

// Get Records Count

$result = $stmt->get_result();
$filteredData = [];
while ($row = $result->fetch_assoc()) {
    $filteredData[] = parseRow($row);
}

header('Content-Type: application/json');

echo json_encode([
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => $filteredCount,
    "data" => $filteredData
]);

?>