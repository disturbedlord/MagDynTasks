<?php
require __DIR__ . '/../db.php';

function getAllUsers()
{
    global $con;
    $get_all_users = "SELECT  e.uid , ua.first_name FROM events e , user_account ua WHERE e.uid =  ua.user_account_id
GROUP BY uid , first_name;";

    $result = mysqli_query($con, $get_all_users);
    return $result;
}
?>