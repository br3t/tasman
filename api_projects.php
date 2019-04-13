<?php
include('includes.php');

check_login();
if(!$json['user']['is_logged']) {
	$json['error'] = 'You should be logged in for this action!';
	exit;
}

if(isset($_GET['action'])&&isset($_GET['entity'])) {
	//--------------
	//* get projects info
	if($_GET['action'] == "get" && $_GET['entity'] == "project") {
		$filtered = $json['user']['uid'];
		if($json['user']['role'] == 1) {
			$filtered = 'all';
		}
		get_all_projects($filtered);
		get_all_tasks();
		print(json_encode($json));
		exit;
	}

	//--------------
	//* add new project
	if($_GET['action'] == "create" && $_GET['entity'] == "project") {
		$insert_data = array(
			'name' => htmlspecialchars($_GET['name']),
			'owner_id' => $json['user']['uid']
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
}

?>