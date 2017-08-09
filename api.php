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
	/*
	//--------------
	//* edit project
	if($_GET['action'] == "edit" && $_GET['entity'] == "project") {
		$name = trim(htmlspecialchars($_GET['name']));
		$project_id = intval($_GET['project_id']);
		if($name != "") {
			if($project_id != 0) {
				$mysqli = connect();
				$sql_editproject = "UPDATE projects SET name='".$mysqli->real_escape_string($name)."' WHERE id=".$project_id;
				$result_editproject = $mysqli->query($sql_editproject);
				if($result_editproject) {
					$json['project_id'] = $project_id;
					$json['name'] = $name;
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
			$mysqli = connect();
			$sql_rmproject = "DELETE FROM projects WHERE id=".$project_id.";";
			$sql_rmproject .= "DELETE FROM tasks WHERE project_id=".$project_id.";";
			$result_rmproject = $mysqli->multi_query($sql_rmproject);
			if($result_rmproject) {
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
		$name = htmlspecialchars($_GET['name']);
		$project_id = intval($_GET['project_id']);
		$deadline = $_GET['deadline'];
		if(preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $deadline) != 1) {
			$deadline = '0000-00-00';
		}

		if($name != "") {
			$mysqli = connect();
			$sql_newtask = "INSERT INTO tasks SET name='".$mysqli->real_escape_string($name)."', deadline='".$deadline."', project_id=".$project_id;
			$result_newptask = $mysqli->query($sql_newtask);
			if($result_newptask) {
				$json = array(
					'id' => $mysqli->insert_id,
					'name' => $name,
					'status' => 0,
					'deadline' => $deadline,
					'project_id' => $project_id
				);
			} else {
				$json['error'] = "Task wasn`t created: ".$mysqli->error;
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
		$name = htmlspecialchars($_GET['name']);
		$id = intval($_GET['id']);
		$deadline = $_GET['deadline'];
		if(preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $deadline) != 1) {
			$deadline = '0000-00-00';
		}

		if($name != "") {
			$mysqli = connect();
			$sql_edittask = "UPDATE tasks SET name='".$mysqli->real_escape_string($name)."', deadline='".$deadline."' WHERE id=".$id;
			$result_edittask = $mysqli->query($sql_edittask);
			if($result_edittask) {
				$json = array(
					'id' => $id,
					'name' => $name,
					'deadline' => $deadline
				);
			} else {
				$json['error'] = "Task wasn`t edited: ".$mysqli->error;
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
			$mysqli = connect();
			$sql_rmtask = "DELETE FROM tasks WHERE id='".$id."'";
			$result_rmtask = $mysqli->query($sql_rmtask);
			if($result_rmtask) {
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
		$id = intval($_GET['id']);
		$status = intval($_GET['status']) == 0 ? 0 : 1;
		if($id != "") {
			$mysqli = connect();
			$sql_statustask = "UPDATE tasks SET status=".$status." WHERE id=".$id;
			$result_statustask = $mysqli->query($sql_statustask);
			if($result_statustask) {
				$json['id'] = $id;
				$json['status'] = $status;
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
	//* change task status
	if($_GET['action'] == "reorder" && $_GET['entity'] == "task") {
		$taskByPriority = explode(',', $_GET['taskByPriority']);
		$tasksLength = count($taskByPriority);
		if($tasksLength > 1) {
			$mysqli = connect();
			$sql_prioritytask = "";
			for($i = 0; $i < $tasksLength; $i++) {
				$taskByPriority[$i] = intval($taskByPriority[$i]);
				if($taskByPriority[$i] != 0) {
					$sql_prioritytask .= 'UPDATE tasks SET priority='.($tasksLength-$i).' WHERE id='.$taskByPriority[$i].';';
				}
				
			}
			$result_prioritytask = $mysqli->multi_query($sql_prioritytask);
			if($result_prioritytask) {
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
	*/
}

//* DB connection
function connect() {
	global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD, $DB_CHARSET;

    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
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