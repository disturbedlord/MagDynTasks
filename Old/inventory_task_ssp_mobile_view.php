<?php

require '../db.php';
error_reporting(0);

session_start();

$user_id = $_SESSION['intUserAccountId'];
$moduleid = 8;




$req = $_GET;

$draw = $req['draw'];
$start = $req['start'];
$uid = $req["uid"];

$length = $req['length'];
$column_order = $req['order'][0]['column'];
$order_dir = $req['order'][0]['dir'];
$all_data = [];

$column_map = [
       0 => 'date',
       1 => 'task',
       2 => 'priority',
       3 => 'status',
];


$query = "SELECT 
              CASE 
                     WHEN e.done = 0 THEN 'pending' 
                     WHEN e.done = 1 THEN 'finished'
              END AS status,
              e.*,
              DATE(e.time) AS date,
              e.priority AS priority_id,
              d.first_name AS user_department,
              d.user_account_id AS user_department_id
              FROM events e
              LEFT JOIN user_account d
              ON d.user_account_id = e.department";
$params = [];
$types = "";

// Add condition dynamically
if ($uid !== null && $uid !== "" && $uid != "-1") {
       $query .= " WHERE e.uid = ?";
       $params[] = $uid;
       $types .= "i";
}

$query .= " ORDER BY e.done ASC, e.time";


// Prepare
$stmt = $con->prepare($query);
if (!$stmt) {
       die("Prepare failed: " . $con->error);
}

if (!empty($params)) {
       $stmt->bind_param($types, ...$params);
}


$stmt->execute();
$runQuery = $stmt->get_result();


$final_data = [];
while ($row = $runQuery->fetch_assoc()) {
       $id = $row["id"];
       $status = $row['status'];
       $task = $row['description'];
       $date = $row['date'];
       $priority = $row['priority'];
       $user_department = $row['user_department'];
       $user_department_id = $row['user_department_id'];
       $event_id = $row['id'];
       $admin_id = $row['uid'];
       $cron = $row['cron'];
       $options = "<div class='w-full sm:w-24 flex flex-row justify-center'>
       <button name='editBtn'><i class='bi bi-pencil-square'></i></button>
       </div>";
       $final_data[] = [
              "id" => $id,
              'date' => $date,
              'task' => $task,
              'priority' => $priority,
              'status' => $status,
              'options' => $options
       ];
}





// for sorting 
function sortByColumn(&$array, $column, $direction = 'asc')
{

       $columnValues = array_column($array, $column);

       $sortDirection = strtolower($direction) === 'desc' ? SORT_DESC : SORT_ASC;

       array_multisort($columnValues, $sortDirection, $array);
}
$columnNameTObeSort = $column_map[$column_order];
sortByColumn($final_data, $columnNameTObeSort, $order_dir);


// for searching
$searchArray = [];
foreach ($req['columns'] as $index => $column) {
       $searchValue = $column['search']['value'];
       if ($searchValue != '') {

              if ($column_map[$index] != 'total_price') {
                     array_push($searchArray, [

                            $column_map[$index] => $searchValue,
                     ]);
              }
       }
}


// For Searching 
function filterData($data, $searchArray)
{
       $filteredData = $data;

       foreach ($searchArray as $search) {
              $filteredData = array_filter($filteredData, function ($item) use ($search) {
                     foreach ($search as $column => $value) {
                            if (!isset($item[$column])) {
                                   return false;
                            }
                            if (is_array($value)) {
                                   $found = false;
                                   foreach ($value as $val) {
                                          if (is_string($item[$column]) || is_numeric($item[$column])) {
                                                 if (stripos((string) $item[$column], (string) $val) !== false) {
                                                        $found = true;
                                                        break;
                                                 }
                                          } elseif ($item[$column] == $val) {
                                                 $found = true;
                                                 break;
                                          }
                                   }
                                   if (!$found) {
                                          return false;
                                   }
                            } elseif (is_string($item[$column]) || is_numeric($item[$column])) {
                                   if (stripos((string) $item[$column], (string) $value) === false) {
                                          return false;
                                   }
                            } else {
                                   if ($item[$column] != $value) {
                                          return false;
                                   }
                            }
                     }
                     return true;
              });
       }

       return array_values($filteredData);
}

if (!empty($searchArray)) {
       $final_data = filterData($final_data, $searchArray);
}


$totalrecords = count($final_data);

//Fetch data based on pagination 
$final_data = array_slice($final_data, $start, $length);


$send_date = [];
foreach ($final_data as $row) {
       $send_date[] = [
              $row["id"],
              $row['date'],
              $row['task'],
              $row['priority'],
              $row['status'],
              $row['options']
       ];
}



$response = [
       'draw' => $draw,
       'data' => $send_date,
       'recordsTotal' => $totalrecords,
       'recordsFiltered' => $totalrecords
];
$con->close();
echo json_encode($response);
