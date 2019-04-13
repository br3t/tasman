<?php
function get_all_tasks() {
	global $json;
	$pdo = connect();
	if(!is_array($json['projects'])) {
		$json['projects'] = array();
	}
	$json['debug'] = 'start';
	//* get tasks
	$pdo_tasks_query = $pdo->query('SELECT * FROM tasks ORDER BY `priority` DESC');
	while ($row_tasks = $pdo_tasks_query->fetch()) {
		
			$json['debug'] = $row_tasks['project_id'];
		if(array_key_exists($row_tasks['project_id'], $json['projects'])) {
			$json['projects'][$row_tasks['project_id']]['tasks'][] = array(
				'id' => $row_tasks['id'],
				'name' => $row_tasks['name'],
				'status' => $row_tasks['status'],
				'priority' => $row_tasks['priority'],
				'deadline' => $row_tasks['deadline']
			);
		}	
	}
}

function create_task($insert_data) {
	global $json;
	if($insert_data['name'] != "") {
		$pdo = connect();
		$pdo_newtask_query = $pdo->prepare('
			INSERT INTO `tasks` 
			SET name = :name,
				deadline = :deadline,
				project_id = :project_id
		');
		$pdo_newtask_query->execute($insert_data);
		$json['error'] = 'Created';
		if($pdo_newtask_query) {
			//* get new task info
			$tid = $pdo->lastInsertId();
			
			$pdo_created_task_query = $pdo->prepare('
				SELECT *
				FROM `tasks`
				WHERE id = :tid
			');
			$pdo_created_task_query->execute(array('tid' => $tid));
			$row_created_task = $pdo_created_task_query->fetch();
			$json = $row_created_task;
		} else {
			$json['error'] = 'Task wasn`t created';
		}
	} else {
		$json['error'] = 'Please, give name for your new task';
	}
}

function update_task($update_data) {
	global $json;
	if($update_data['name'] != "") {
		$pdo = connect();
		$pdo_edittask_query = $pdo->prepare('
			UPDATE `tasks`
			SET name = :name,
				deadline = :deadline
			WHERE id = :id
		');
		$pdo_edittask_query->execute($update_data);
		if($pdo_edittask_query) {
			$json = $update_data;
		} else {
			$json['error'] = 'Task wasn`t edited';
		}
	} else {
		$json['error'] = 'Please, give new name for your task';
	}
}

function remove_task($id) {
	global $json;
	if($id != "") {
		$pdo = connect();
		$pdo_rmtask_query = $pdo->prepare('
			DELETE FROM tasks
			WHERE id = :id
		');
		$pdo_rmtask_query->execute(array('id' => $id));
		if($pdo_rmtask_query) {
			$json['id'] = $id;
		} else {
			$json['error'] = "Task wasn`t removed";
		}
	} else {
		$json['error'] = 'Please, set id of removing task';
	}
}

function update_task_status($update_data) {
	global $json;
	if($update_data['id'] != "") {
		$pdo = connect();
		$pdo_statustask_query = $pdo->prepare('
			UPDATE tasks 
			SET status = :status 
			WHERE id = :id
		');
		$pdo_statustask_query->execute($update_data);
		if($pdo_statustask_query) {
			$json = $update_data;
		} else {
			$json['error'] = "Task status wasn`t changed";
		}
	} else {
		$json['error'] = 'Please, set task id for changing status';
	}
}

function update_task_order($task_by_priority) {
	global $json;
	$tasks_length = count($task_by_priority);
	if($tasks_length > 1) {
		$pdo = connect();
		$pdo_prioritytask_query = '';
		for($i = 0; $i < $tasks_length; $i++) {
			$task_by_priority[$i] = intval($task_by_priority[$i]);
			if($task_by_priority[$i] != 0) {
				// ?
				$pdo_prioritytask_query .= 'UPDATE `tasks` SET priority='.($tasks_length - $i).' WHERE id='.$task_by_priority[$i].';';
			}	
		}
		
		$pdo_prioritytask = $pdo->prepare($pdo_prioritytask_query);
		$pdo_prioritytask->execute();
		if($pdo_prioritytask) {
			$json = 'ok';
		} else {
			$json['error'] = 'Tasks weren`t reordered';
		}
	} else {
		$json['error'] = 'Please, set tasks for reorder';
	}
}
?>