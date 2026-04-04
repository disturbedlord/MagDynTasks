<?php
$mysqlUserName = "root";
$mysqlPassword = "";
$mysqlHostName = "localhost";
$DbName = "MAGDYN";
$con = new mysqli($mysqlHostName, $mysqlUserName, $mysqlPassword, $DbName);
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
// $query = mysqli_query($con, "SELECT short_description
// 		  FROM user_account
// 		  JOIN role ON role.role_id = user_account.role_id
// 		  WHERE user_account.user_account_id = '4'");
// $fetch_query = mysqli_fetch_assoc($query);
// $user_role = $fetch_query['short_description'];
// $checkrole = 'Invoice Editor';

// Fix charset issue
// Force client encoding to utf8 using SQL query
$con->set_charset("utf8");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>