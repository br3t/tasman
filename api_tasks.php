<?php
require_once('includes.php');

$user = new User();

if(!$user->get_is_logged()) {

	$ajax_respond['error'] = 'You should be logged in for this action!';

} else {

	if(isset($_GET['action']) && isset($_GET['entity']) && $_GET['entity'] == "task") {
		switch ($_GET['action']) {
			case 'create':
				//--------------
				//* add new task
				$insert_data = array(
					'name' => htmlspecialchars($_GET['name']),
					'project_id' => intval($_GET['project_id']),
					'deadline' => $_GET['deadline']
				);

				if(preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $insert_data['deadline']) != 1) {
					$insert_data['deadline'] = '0000-00-00';
				}

				$ajax_respond = Task::create($insert_data, $ajax_respond);
				break;
			
			case 'edit':
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
				
				$ajax_respond = Task::update($update_data, $ajax_respond);
				break;
			
			case 'remove':
				//--------------
				//* remove task
				$id = intval($_GET['id']);
				$ajax_respond = Task::remove($id, $ajax_respond);
				break;
			
			case 'set_status':
				//--------------
				//* change task status
				$update_data = array(
					'id' => intval($_GET['id']),
					'status' => intval($_GET['status']) == 0 ? 0 : 1
				);
				$ajax_respond = Task::update_status($update_data, $ajax_respond);
				break;
			
			case 'reorder':
				//--------------
				//* change task position
				$task_by_priority = explode(',', $_GET['taskByPriority']);
				$ajax_respond = Task::update_order($task_by_priority, $ajax_respond);
				break;
		}
	}
}
print(json_encode($ajax_respond));
exit;

?>