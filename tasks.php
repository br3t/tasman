<?php
function get_all_tasks($data_bus) {

	$connection = new Connection($data_bus);
	if($connection != null) {
		$projects_to_get_tasks = implode(',', array_keys($data_bus['projects']));
		//* Get all tasks
		$connection_query = $connection->query('SELECT * '.
			'FROM tasks '.
			'WHERE project_id IN ('.$projects_to_get_tasks.') '.
			'ORDER BY `priority` DESC');
		
		while ($row_tasks = $connection_query->fetch()) {
			$data_bus['projects'][$row_tasks['project_id']]['tasks'][] = array(
				'id' => $row_tasks['id'],
				'name' => $row_tasks['name'],
				'status' => $row_tasks['status'],
				'priority' => $row_tasks['priority'],
				'deadline' => $row_tasks['deadline']
			);
		}
	}
	return $data_bus;
}

function create_task($insert_data, $data_bus) {

	if($insert_data['name'] != "") {
		$connection = new Connection($data_bus);
		if($connection != null) {
			// Create task
			$connection_query = $connection->prepare('
				INSERT INTO `tasks` 
				SET name = :name,
					deadline = :deadline,
					project_id = :project_id
			');
			$connection_query->execute($insert_data);
			$data_bus['error'] = 'Created';
			if($connection_query) {
				//* Get created task info
				$tid = $connection->lastInsertId();
				
				$connection_query_created = $connection->prepare('
					SELECT *
					FROM `tasks`
					WHERE id = :tid
				');
				$connection_query_created->execute(array('tid' => $tid));
				$row_created_task = $connection_query_created->fetch();
				$data_bus = $row_created_task;
			} else {
				$data_bus['error'] = 'Task wasn`t created';
			}
		}
	} else {
		$data_bus['error'] = 'Please, give name for your new task';
	}
	return $data_bus;
}

function update_task($update_data, $data_bus) {

	if($update_data['name'] != "") {
		$connection = new Connection($data_bus);
		if($connection != null) {
			// Update task
			$connection_query = $connection->prepare('
				UPDATE `tasks`
				SET name = :name,
					deadline = :deadline
				WHERE id = :id
			');
			$connection_query->execute($update_data);
			if($connection_query) {
				$data_bus = $update_data;
			} else {
				$data_bus['error'] = 'Task wasn`t edited';
			}
		}
	} else {
		$data_bus['error'] = 'Please, give new name for your task';
	}
	return $data_bus;
}

function remove_task($id, $data_bus) {

	if($id != "") {
		$connection = new Connection($data_bus);
		if($connection != null) {
			$connection_query = $connection->prepare('
				DELETE FROM tasks
				WHERE id = :id
			');
			$connection_query->execute(array('id' => $id));
			if($connection_query) {
				$data_bus['id'] = $id;
			} else {
				$data_bus['error'] = "Task wasn`t removed";
			}
		}
	} else {
		$data_bus['error'] = 'Please, set id of removing task';
	}
	return $data_bus;
}

function update_task_status($update_data, $data_bus) {

	if($update_data['id'] != "") {
		
		$connection = new Connection($data_bus);
		if($connection != null) {
			$connection_query = $connection->prepare('
				UPDATE tasks 
				SET status = :status 
				WHERE id = :id
			');
			$connection_query->execute($update_data);
			if($connection_query) {
				$data_bus = $update_data;
			} else {
				$data_bus['error'] = "Task status wasn`t changed";
			}
		}
	} else {
		$data_bus['error'] = 'Please, set task id for changing status';
	}
	return $data_bus;
}

function update_task_order($task_by_priority, $data_bus) {

	$tasks_length = count($task_by_priority);
	if($tasks_length > 1) {
		
		$connection = new Connection($data_bus);
		if($connection != null) {
			$upate_priority_query = '';
			for($i = 0; $i < $tasks_length; $i++) {
				$task_by_priority[$i] = intval($task_by_priority[$i]);
				if($task_by_priority[$i] != 0) {
					// ?
					$upate_priority_query .= 'UPDATE `tasks` SET priority='.($tasks_length - $i).' WHERE id='.$task_by_priority[$i].';';
				}	
			}
			
			$connection_query = $connection->prepare($upate_priority_query);
			$connection_query->execute();
			if($connection_query) {
				$data_bus = 'ok';
			} else {
				$data_bus['error'] = 'Tasks weren`t reordered';
			}
		}
	} else {
		$data_bus['error'] = 'Please, set tasks for reorder';
	}
	return $data_bus;
}
?>