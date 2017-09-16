<?php
function get_all_projects() {
	global $json;
	$pdo = connect();
	//* get project
	$pdo_projects_query = $pdo->query('
		SELECT projects.id, 
			projects.name AS pname,
			users.name AS uname
		FROM `projects`, `users`
		WHERE users.id = projects.owner_id');
	while ($row_projects = $pdo_projects_query->fetch()) {
		$json[$row_projects['id']] = array(
			'id' => $row_projects['id'],
			'name' => $row_projects['pname'],
			'owner' => $row_projects['uname'],
			'tasks' => array()
		);
	}
}

function create_project($insert_data) {
	global $json;

	if($insert_data['name'] != "") {
		$pdo = connect();
		$pdo_newproject_query = $pdo->prepare('
			INSERT INTO projects 
			SET name = :name,
				owner_id = :owner_id');
		$pdo_newproject_query->execute($insert_data);

		if($pdo_newproject_query) {
			//* get new project info
			$pid = $pdo->lastInsertId();
			$pdo_created_project_query = $pdo->prepare('
				SELECT projects.id, 
					projects.name AS pname,
					users.name AS uname
				FROM `projects`, `users`
				WHERE users.id = projects.owner_id AND
					projects.id = :pid
			');
			$pdo_created_project_query->execute(array('pid' => $pid));
			$row_created_project = $pdo_created_project_query->fetch();
			$json[$pid] = array(
				'id' => $pid,
				'name' => $row_created_project['pname'],
				'owner' => $row_created_project['uname'],
				'tasks' => array()
			);
		} else {
			$json['error'] = "Project wasn`t created";
		}
	} else {
		$json['error'] = 'Please, give name for your new project';
	}
}

function update_project($update_data) {
	global $json;
	if($update_data['name'] != "") {
		if($update_data['project_id'] != 0) {
			$pdo = connect();
			$pdo_edit_project_query = $pdo->prepare('
				UPDATE `projects`
				SET name = :name
				WHERE id = :project_id
			');
			$pdo_edit_project_query->execute($update_data);
			if($pdo_edit_project_query) {
				$json = $update_data;
			} else {
				$json['error'] = "Project wasn`t edited";
			}
		} else {
			$json['error'] = 'Please, provide id for project to edit';
		}
	} else {
		$json['error'] = 'Please, give new name for your project';
	}
}

function remove_project($project_id) {
	global $json;
	if($project_id != "") {
		$pdo = connect();
		$pdo_rmproject_query = $pdo->prepare('
			DELETE FROM projects 
			WHERE id = :project_id');
		$pdo_rmproject_query->execute(array('project_id' => $project_id));

		if($pdo_rmproject_query) {
			//TODO: remove all tasks from this project
			$json['project_id'] = $project_id;
		} else {
			$json['error'] = "Project wasn`t removed";
		}
	} else {
		$json['error'] = 'Please, set id of removing project';
	}
}
?>