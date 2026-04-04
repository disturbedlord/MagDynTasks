<?php
require '../../config.php';

function getAllUsers()
{
    global $con;
    $get_all_users = "SELECT username as first_name , user_account_id as uid from user_account where active_flag = 1 ";

    $result = mysqli_query($con, $get_all_users);
    return $result;
}
?>