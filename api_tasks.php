<?php
require_once('includes.php');

$user = new User();

if(!$user->get_is_logged()) {

	$ajax_respond['error'] = 'You should be logged in for this action!';

} else {

	if(isset($_GET['action'])&&isset($_GET['entity'])) {
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

			$ajax_respond = create_task($insert_data, $ajax_respond);

		} else if($_GET['action'] == "edit" && $_GET['entity'] == "task") {
			//--------------
			//* edit task
			$update_data = array(
				'name' => htmlspecialchars($_GET['name']),
				'id' => intval($_GET['id']),
				'deadline' => $_GET['deadline']
			);
			
			if(preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $update_data['deadline']) != 1) {
				$deadline = '0000-00-00';
			}
			
			$ajax_respond = update_task($update_data, $ajax_respond);

		} else if($_GET['action'] == "remove" && $_GET['entity'] == "task") {
			//--------------
			//* remove task
			$id = intval($_GET['id']);
			$ajax_respond = remove_task($id, $ajax_respond);

		} else if($_GET['action'] == "set_status" && $_GET['entity'] == "task") {
			//--------------
			//* change task status
			$update_data = array(
				'id' => intval($_GET['id']),
				'status' => intval($_GET['status']) == 0 ? 0 : 1
			);
			$ajax_respond = update_task_status($update_data, $ajax_respond);

		} else if($_GET['action'] == "reorder" && $_GET['entity'] == "task") {
			//--------------
			//* change task position
			$task_by_priority = explode(',', $_GET['taskByPriority']);
			$ajax_respond = update_task_order($task_by_priority, $ajax_respond);

		}
	}
}
print(json_encode($ajax_respond));
exit;

?>