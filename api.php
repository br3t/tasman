<?php
	header("Content-type: text/json");

	$json = array();

	include("config.php");
	@include("reconfig.php");

	//--------------
	//* get projects info
	if(isset($_GET['action']) && $_GET['action'] == "get" && isset($_GET['entity']) && $_GET['entity'] == "project") {
		$mysqli = connect();
		//* get project
		$sql = "SELECT id, name FROM projects";
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
		$sql_tasks = "SELECT * FROM tasks";
		$result_tasks = $mysqli->query($sql_tasks);
		if($result_tasks->num_rows > 0) {
			while($row_tasks = $result_tasks->fetch_assoc()) {
				$json[$row_tasks['project_id']]['tasks'][] = array(
					'id' => $row_tasks['id'],
					'name' => $row_tasks['name'],
					'status' => $row_tasks['status']
				);
			}
		}
		print(json_encode($json));
		exit;
	}

	//--------------
	//* get projects info
	if(isset($_GET['action']) && $_GET['action'] == "create" && isset($_GET['entity']) && $_GET['entity'] == "project") {
		$name = $_GET['name'];
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