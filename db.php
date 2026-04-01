<?php
$host = "127.0.0.1";
$port = 3306;
$user = "root";
$password = "rootpassword";
$database = "MAGDYN";

$con = new mysqli($host, $user, $password, $database, $port);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>