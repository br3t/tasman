<?php
require_once('includes.php');

$ajax_respond = check_login($ajax_respond);
if(!$ajax_respond['user']['is_logged']) {

	$ajax_respond['error'] = 'You should be logged in for this action!';

} else {

	if(isset($_GET['action']) && isset($_GET['entity'])) {
		// Check "routes"
		if($_GET['action'] == "get" && $_GET['entity'] == "project") {
			//--------------
			//* get projects info
			$filtered = $ajax_respond['user']['uid'];
			if($ajax_respond['user']['role'] == 1) {
				$filtered = 'all';
			}
			$data_bus['f'] = $filtered;
			$ajax_respond = get_all_projects($filtered, $ajax_respond);
			$ajax_respond = get_all_tasks($ajax_respond);

		} else if($_GET['action'] == "create" && $_GET['entity'] == "project") {
			//--------------
			//* add new project
			$insert_data = array(
				'name' => htmlspecialchars($_GET['name']),
				'owner_id' => $ajax_respond['user']['uid']
			);
			$ajax_respond = create_project($insert_data, $ajax_respond);

		} else if($_GET['action'] == "edit" && $_GET['entity'] == "project") {
			//--------------
			//* edit project
			$update_data = array(
				'name' => trim(htmlspecialchars($_GET['name'])),
				'project_id' => intval($_GET['project_id'])
			);
			$ajax_respond = update_project($update_data, $ajax_respond);
		} else if($_GET['action'] == "remove" && $_GET['entity'] == "project") {
			//--------------
			//* remove project
			$project_id = intval($_GET['project_id']);
			$ajax_respond = remove_project($project_id, $ajax_respond);
		}
	}
	
}
print(json_encode($ajax_respond));
exit;

?>