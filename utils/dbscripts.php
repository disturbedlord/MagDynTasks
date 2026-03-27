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
    e.cron
FROM events e
LEFT JOIN user_account d 
 ON d.user_account_id = e.uid";

$searchTitle = " e.title LIKE  ? ";
$searchDescription = " e.description LIKE  ? ";
$searchStatus = " e.done = ? ";
$searchPriority = " e.priority = ? ";
$searchUid = " e.uid in (?) ";
?>