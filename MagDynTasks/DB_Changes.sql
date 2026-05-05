-- Create Task Assignees table to track multi assignees per task
CREATE TABLE task_assignees (
	id INT(11) NOT NULL AUTO_INCREMENT,
	assignee INT(11) NOT NULL,
	event_Id INT(11) NOT NULL,
	PRIMARY KEY (id) USING BTREE,
	INDEX FK_task_assignees_events (event_Id) USING BTREE,
	CONSTRAINT FK_task_assignees_events FOREIGN KEY (event_Id) REFERENCES events (id) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1111;

-- Populate Task Assignees table for existing events data
INSERT INTO task_assignees(assignee , event_id)
SELECT department , id
FROM events

-- Modify Events Table to add new Column due_date to track due_date of a task
ALTER table events ADD COLUMN due_date DATETIME NOT NULL;

-- Create Index on title column in Events table
-- Currently useless since query is "%query%" and Indexing doesn't work when placeholder on both side
-- as search tree cannot be searched on such query
-- Works only is startsWith or endsWith type query is used is used
CREATE INDEX title_index ON events (title);