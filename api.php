<?php
header("Content-type: text/json");

$json = array();

include("config.php");
@include("reconfig.php");

include("connect.php");
include("projects.php");
include("tasks.php");

if(isset($_GET['action'])&&isset($_GET['entity'])) {
	//--------------
	//* get projects info
	if($_GET['action'] == "get" && $_GET['entity'] == "project") {
		get_all_projects();
		get_all_tasks();
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
		create_project($insert_data);
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
		update_project($update_data);
		print(json_encode($json));
		exit;
	}
	//--------------
	//* remove project
	if($_GET['action'] == "remove" && $_GET['entity'] == "project") {
		$project_id = intval($_GET['project_id']);
		remove_project($project_id);
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

		create_task($insert_data);
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
		
		update_task($update_data);
		print(json_encode($json));
		exit;
	}
	//--------------
	//* remove task
	if($_GET['action'] == "remove" && $_GET['entity'] == "task") {
		$id = intval($_GET['id']);
		remove_task($id);
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

		update_task_status($update_data);
		print(json_encode($json));
		exit;
	}
	//--------------
	//* change task position
	if($_GET['action'] == "reorder" && $_GET['entity'] == "task") {
		$task_by_priority = explode(',', $_GET['taskByPriority']);
		
		update_task_order($task_by_priority);
		print(json_encode($json));
		exit;
	}
}

?>