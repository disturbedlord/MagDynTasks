<?php
require 'db.php';
error_reporting(0);

session_start();
// require '../../config.php';
// require '../../custom_api/authCheck.php';
$user_id = $_SESSION['intUserAccountId'];
$moduleid = 8;




$req = $_GET;

$draw = $req['draw'];
$start = $req['start'];

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


$query_1 = "SELECT 
                  case 
                      when e.done = 0 then 'pending' 
                      when e.done = 1 then 'finished'
                  end as status,
                  e.*,
                  e.uid,
                  date(e.time) as date,
                  priority,
                  e.priority as priority_id ,
                  d.first_name as 'user_department',
                  d.user_account_id as 'user_department_id'
            FROM `events` e
                left join user_account d
                on d.user_account_id=e.department 
                ORDER BY `done` ASC, `time` ";




$runQuery = mysqli_query($con, $query_1);
if (!$runQuery) {
       echo mysqli_error($con);
       exit;
}

$final_data = [];
while ($row = mysqli_fetch_array($runQuery, MYSQLI_ASSOC)) {

       $status = $row['status'];
       $task = $row['description'];
       $date = $row['date'];
       $priority = $row['priority'];
       $user_department = $row['user_department'];
       $user_department_id = $row['user_department_id'];
       $event_id = $row['id'];
       $admin_id = $row['uid'];
       $cron = $row['cron'];
       $options = "<div>
       <i class='bi bi-pencil-square'></i></div>";
       $final_data[] = [
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
