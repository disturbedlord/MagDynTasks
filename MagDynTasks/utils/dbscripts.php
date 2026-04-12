<?php
$EVENTQUERY = "
SELECT 
    e.title,
    e.id,
    e.description,
    e.done,
    CASE 
        WHEN e.done = 0 THEN 'pending' 
        WHEN e.done = 1 THEN 'finished'
    END AS status,
    DATE(e.time) AS date,
    e.time,
    e.priority AS priority_id,
    d.first_name AS user_name,
    d.user_account_id,
    e.department,
    e.cron , 
    d2.first_name as assignee
FROM events e
LEFT JOIN user_account d 
 ON d.user_account_id = e.uid
 LEFT JOIN user_account d2 on e.department = d2.user_account_id ";

$EVENTQUERYCOUNT = "
SELECT 
    count(*) AS TOTAL
FROM events e
LEFT JOIN user_account d 
 ON d.user_account_id = e.uid
 LEFT JOIN user_account d2 on e.department = d2.user_account_id ";

$searchTitle = " e.title LIKE  ? ";
$searchDescription = " e.description LIKE  ? ";
$searchStatus = " e.done = ? ";
$searchPriority = " e.priority in (?) ";
$searchUid = " e.uid in (?) ";
?>