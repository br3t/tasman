<?php
header("Content-type: text/json");

$json = array();

include("config.php");
@include("reconfig.php");

if(isset($_GET['action'])&&isset($_GET['entity'])) {
	//--------------
	//* get projects info
	if($_GET['action'] == "get" && $_GET['entity'] == "project") {
		$pdo = connect();
		//* get project
		$pdo_projects_query = $pdo->query('
			SELECT projects.id, 
				projects.name AS pname,
				users.name AS uname
			FROM `projects`, `users`
			WHERE users.id = projects.owner_id');
		while ($row_projects = $pdo_projects_query->fetch()) {
			$json[$row_projects['id']] = array(
				'id' => $row_projects['id'],
				'name' => $row_projects['pname'],
				'owner' => $row_projects['uname'],
				'tasks' => array()
			);
		}

		//* get tasks
		$pdo_tasks_query = $pdo->query('SELECT * FROM tasks ORDER BY `priority` DESC');
		while ($row_tasks = $pdo_tasks_query->fetch()) {
			$json[$row_tasks['project_id']]['tasks'][] = array(
				'id' => $row_tasks['id'],
				'name' => $row_tasks['name'],
				'status' => $row_tasks['status'],
				'priority' => $row_tasks['priority'],
				'deadline' => $row_tasks['deadline']
			);
		}

		print(json_encode($json));
		exit;
	}

	//--------------
	//* add new project
	if($_GET['action'] == "create" && $_GET['entity'] == "project") {
		$insert_data = array(
			'name' => htmlspecialchars($_GET['name']),
			'owner_id' => 2 //TEMPORARY HARDCODED
		);
		if($insert_data['name'] != "") {
			$pdo = connect();
			$pdo_newproject_query = $pdo->prepare('
				INSERT INTO projects 
				SET name = :name,
					owner_id = :owner_id');
			$pdo_newproject_query->execute($insert_data);

			if($pdo_newproject_query) {
				//* get new project info
				$pid = $pdo->lastInsertId();
				$pdo_created_project_query = $pdo->prepare('
					SELECT projects.id, 
						projects.name AS pname,
						users.name AS uname
					FROM `projects`, `users`
					WHERE users.id = projects.owner_id AND
						projects.id = :pid
				');
				$pdo_created_project_query->execute(array('pid' => $pid));
				$row_created_project = $pdo_created_project_query->fetch();
				$json[$pid] = array(
					'id' => $pid,
					'name' => $row_created_project['pname'],
					'owner' => $row_created_project['uname'],
					'tasks' => array()
				);
			} else {
				$json['error'] = "Project wasn`t created";
			}
		} else {
			$json['error'] = 'Please, give name for your new project';
		}
		print(json_encode($json));
		exit;
	}
	//--------------
	//* edit project
	if($_GET['action'] == "edit" && $_GET['entity'] == "project") {
		$update_data = array(
			'name' => trim(htmlspecialchars($_GET['name'])),
			'project_id' => intval($_GET['project_id'])
		);
		if($update_data['name'] != "") {
			if($update_data['project_id'] != 0) {
				$pdo = connect();
				$pdo_edit_project_query = $pdo->prepare('
					UPDATE `projects`
					SET name = :name
					WHERE id = :project_id
				');
				$pdo_edit_project_query->execute($update_data);
				if($pdo_edit_project_query) {
					$json = $update_data;
				} else {
					$json['error'] = "Project wasn`t edited";
				}
			} else {
				$json['error'] = 'Please, provide id for project to edit';
			}
		} else {
			$json['error'] = 'Please, give new name for your project';
		}
		print(json_encode($json));
		exit;
	}
	//--------------
	//* remove project
	if($_GET['action'] == "remove" && $_GET['entity'] == "project") {
		$project_id = intval($_GET['project_id']);
		if($project_id != "") {
			$pdo = connect();
			$pdo_rmproject_query = $pdo->prepare('
				DELETE FROM projects 
				WHERE id = :project_id');
			$pdo_rmproject_query->execute(array('project_id' => $project_id));

			if($pdo_rmproject_query) {
				//TODO: remove all tasks from this project
				$json['project_id'] = $project_id;
			} else {
				$json['error'] = "Project wasn`t removed";
			}
		} else {
			$json['error'] = 'Please, set id of removing project';
		}
		print(json_encode($json));
		exit;
	}

	//--------------
	//* add new task
	if($_GET['action'] == "create" && $_GET['entity'] == "task") {
		$insert_data = array(
			'name' => htmlspecialchars($_GET['name']),
			'project_id' => intval($_GET['project_id']),
			'deadline' => $_GET['deadline']
		);

		if(preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $insert_data['deadline']) != 1) {
			$insert_data['deadline'] = '0000-00-00';
		}

		if($insert_data['name'] != "") {
			$pdo = connect();
			$pdo_newtask_query = $pdo->prepare('
				INSERT INTO `tasks` 
				SET name = :name,
					deadline = :deadline,
					project_id = :project_id
			');
			$pdo_newtask_query->execute($insert_data);
			$json['error'] = 'Created';
			if($pdo_newtask_query) {
				//* get new task info
				$tid = $pdo->lastInsertId();
				
				$pdo_created_task_query = $pdo->prepare('
					SELECT *
					FROM `tasks`
					WHERE id = :tid
				');
				$pdo_created_task_query->execute(array('tid' => $tid));
				$row_created_task = $pdo_created_task_query->fetch();
				$json = $row_created_task;
			} else {
				$json['error'] = 'Task wasn`t created';
			}
		} else {
			$json['error'] = 'Please, give name for your new tak';
		}
		print(json_encode($json));
		exit;
	}
	//--------------
	//* edit task
	if($_GET['action'] == "edit" && $_GET['entity'] == "task") {
		$update_data = array(
			'name' => htmlspecialchars($_GET['name']),
			'id' => intval($_GET['id']),
			'deadline' => $_GET['deadline']
		);
		
		if(preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $update_data['deadline']) != 1) {
			$deadline = '0000-00-00';
		}
		
		if($update_data['name'] != "") {
			$pdo = connect();
			$pdo_edittask_query = $pdo->prepare('
				UPDATE `tasks`
				SET name = :name,
					deadline = :deadline
				WHERE id = :id
			');
			$pdo_edittask_query->execute($update_data);
			if($pdo_edittask_query) {
				$json = $update_data;
			} else {
				$json['error'] = 'Task wasn`t edited';
			}
		} else {
			$json['error'] = 'Please, give new name for your tak';
		}
		print(json_encode($json));
		exit;
	}
	//--------------
	//* remove task
	if($_GET['action'] == "remove" && $_GET['entity'] == "task") {
		$id = intval($_GET['id']);
		if($id != "") {
			$pdo = connect();
			$pdo_rmtask_query = $pdo->prepare('
				DELETE FROM tasks
				WHERE id = :id
			');
			$pdo_rmtask_query->execute(array('id' => $id));
			if($pdo_rmtask_query) {
				$json['id'] = $id;
			} else {
				$json['error'] = "Task wasn`t removed";
			}
		} else {
			$json['error'] = 'Please, set id of removing task';
		}
		print(json_encode($json));
		exit;
	}
	//--------------
	//* change task status
	if($_GET['action'] == "set_status" && $_GET['entity'] == "task") {
		$update_data = array(
			'id' => intval($_GET['id']),
			'status' => intval($_GET['status']) == 0 ? 0 : 1
		);
		if($update_data['id'] != "") {
			$pdo = connect();
			$pdo_statustask_query = $pdo->prepare('
				UPDATE tasks 
				SET status = :status 
				WHERE id = :id
			');
			$pdo_statustask_query->execute($update_data);
			if($pdo_statustask_query) {
				$json = $update_data;
			} else {
				$json['error'] = "Task status wasn`t changed";
			}
		} else {
			$json['error'] = 'Please, set task id for changing status';
		}
		print(json_encode($json));
		exit;
	}
	//--------------
	//* change task position
	if($_GET['action'] == "reorder" && $_GET['entity'] == "task") {
		$taskByPriority = explode(',', $_GET['taskByPriority']);
		$tasksLength = count($taskByPriority);
		if($tasksLength > 1) {
			$pdo = connect();
			$pdo_prioritytask_query = '';
			for($i = 0; $i < $tasksLength; $i++) {
				$taskByPriority[$i] = intval($taskByPriority[$i]);
				if($taskByPriority[$i] != 0) {
					// ?
					$pdo_prioritytask_query .= 'UPDATE `tasks` SET priority='.($tasksLength - $i).' WHERE id='.$taskByPriority[$i].';';
				}	
			}
			
			$pdo_prioritytask = $pdo->prepare($pdo_prioritytask_query);
			$pdo_prioritytask->execute();
			if($pdo_prioritytask) {
				$json = 'ok';
			} else {
				$json['error'] = 'Tasks weren`t reordered';
			}
		} else {
			$json['error'] = 'Please, set tasks for reorder';
		}
		print(json_encode($json));
		exit;
	}
}

//* DB connection
function connect() {
	global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD, $DB_CHARSET;

    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => 1,
    ];
    
    try {
	    $pdo = new PDO($dsn, $DB_USER, $DB_PASSWORD, $opt);
	    return $pdo;
	} catch (PDOException $e) {
		$json['error'] = array('errno' => '0', 'error' => $e->getMessage());
		print(json_encode($json));
		exit;
	}
}
?>