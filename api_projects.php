<?php
require_once('includes.php');

$user = new User();


if(!$user->get_is_logged()) {

	$ajax_respond['error'] = 'You should be logged in for this action!';

} else {

	if(isset($_GET['action']) && isset($_GET['entity']) && $_GET['entity'] == "project") {
		// Check "routes"
		switch ($_GET['action']) {
			case 'get':
				//--------------
				//* get projects info
				$filtered = $user->get_id();
				if($user->get_role() == 1) {
					$filtered = 'all';
				}
				$ajax_respond = Project::get_all($filtered, $ajax_respond);
				$ajax_respond = Task::get_all($ajax_respond);
				break;

			case 'create':
				//--------------
				//* add new project
				$insert_data = array(
					'name' => htmlspecialchars($_GET['name']),
					'owner_id' => $user->get_id()
				);
				$ajax_respond = Project::create($insert_data, $ajax_respond);
				break;

			case 'edit':
				//--------------
				//* edit project
				$update_data = array(
					'name' => trim(htmlspecialchars($_GET['name'])),
					'project_id' => intval($_GET['project_id'])
				);
				$ajax_respond = Project::update($update_data, $ajax_respond);
				break;

			case 'remove':
				//--------------
				//* remove project
				$project_id = intval($_GET['project_id']);
				$ajax_respond = Project::remove($project_id, $ajax_respond);
				break;

		}
	}
	
}
print(json_encode($ajax_respond));
exit;

?>