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
    COALESCE(GROUP_CONCAT(d2.first_name SEPARATOR ','), '') AS assignees,
    COALESCE(GROUP_CONCAT(d2.user_account_id SEPARATOR ','), '') AS assigneesId

FROM events e 
LEFT join task_assignees ta
	ON ta.event_id = e.id
LEFT JOIN user_account d 
 ON d.user_account_id = e.uid
LEFT JOIN user_account d2
ON d2.user_account_id = ta.assignee ";

$EVENTQUERYCOUNT = "
SELECT 
    count(*) AS TOTAL
FROM events e
LEFT JOIN user_account d 
 ON d.user_account_id = e.uid
 LEFT JOIN user_account d2 on e.department = d2.user_account_id ";

$GETSPECIFICUSERROWS = "
 EXISTS (
    SELECT 1 
    FROM task_assignees ta2 
    WHERE ta2.event_id = e.id 
      AND ta2.assignee in (?)
)";

$searchTitle = " e.title LIKE  ? ";
$searchDescription = " e.description LIKE  ? ";
$searchStatus = " e.done = ? ";
$searchPriority = " e.priority in (?) ";
$searchUid = " e.uid in (?) ";
?>