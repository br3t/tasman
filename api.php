<?php
header("Content-type: text/json");

$json = array();

include("config.php");
@include("reconfig.php");

if(isset($_GET['action'])&&isset($_GET['entity'])) {
	//--------------
	//* get projects info
	if($_GET['action'] == "get" && $_GET['entity'] == "project") {
		$mysqli = connect();
		//* get project
		$sql = "SELECT `id`, `name` FROM `projects`";
		$result = $mysqli->query($sql);
		if($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$json[$row['id']] = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'tasks' => array()
				);
			}
		}
		//* get tasks
		$sql_tasks = "SELECT * FROM tasks ORDER BY `priority` DESC";
		$result_tasks = $mysqli->query($sql_tasks);
		if($result_tasks->num_rows > 0) {

			while($row_tasks = $result_tasks->fetch_assoc()) {
				$json[$row_tasks['project_id']]['tasks'][] = array(
					'id' => $row_tasks['id'],
					'name' => $row_tasks['name'],
					'status' => $row_tasks['status'],
					'priority' => $row_tasks['priority'],
					'deadline' => $row_tasks['deadline']
				);
			}
		}
		print(json_encode($json));
		exit;
	}

	//--------------
	//* add new project
	if($_GET['action'] == "create" && $_GET['entity'] == "project") {
		$name = htmlspecialchars($_GET['name']);
		if($name != "") {
			$mysqli = connect();
			$sql_newproject = "INSERT INTO projects SET name='".$mysqli->real_escape_string($name)."'";
			$result_newproject = $mysqli->query($sql_newproject);
			if($result_newproject) {
				$json[$mysqli->insert_id] = array(
					'id' => $mysqli->insert_id,
					'name' => $name,
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
	//* remove project
	if($_GET['action'] == "remove" && $_GET['entity'] == "project") {
		$project_id = intval($_GET['project_id']);
		if($project_id != "") {
			$mysqli = connect();
			$sql_rmproject = "DELETE FROM projects WHERE id='".$project_id."'";
			$result_rmproject = $mysqli->query($sql_rmproject);
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
					'deadline' => $deadline,
					'project_id' => $project_id
				);
			} else {
				$json['error'] = "Task wasn`t created";
			}
		} else {
			$json['error'] = 'Please, give name for your new tak';
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
				$json['error'] = "Task status wasn`t changes";
			}
		} else {
			$json['error'] = 'Please, set task id for changing status';
		}
		print(json_encode($json));
		exit;
	}
}

//* DB connection
function connect() {
	global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD;
	$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
	if ($mysqli->connect_errno) {
		$json['error'] = array('errno' => $mysqli->connect_errno, 'error' => $mysqli->connect_error);
		print(json_encode($json));
		exit;
	} else {
		return $mysqli;
	}
}
?>