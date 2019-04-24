<?php
function get_all_projects($filtered, $data_bus) {

	$connection = new Connection($data_bus);
	if($connection != null) {
		//* Get projects
		$projects = array();
		//* get project
		$query_raw = '
			SELECT projects.id, 
				projects.name AS pname,
				users.name AS uname
			FROM `projects`, `users`
			WHERE users.id = projects.owner_id';
		if($filtered != 'all') {
			$query_raw .= ' AND projects.owner_id='.intval($filtered);
		}
		$data_bus['q'] = $query_raw;
		$connection_query = $connection->query($query_raw);
		while ($row_projects = $connection_query->fetch()) {
			$projects[$row_projects['id']] = array(
				'id' => $row_projects['id'],
				'name' => $row_projects['pname'],
				'owner' => $row_projects['uname'],
				'tasks' => array()
			);
		}
		$data_bus['projects'] = $projects;
	}
	return $data_bus;
}

function create_project($insert_data, $data_bus) {

	if($insert_data['name'] != "") {
		$connection = new Connection($data_bus);
		if($connection != null) {
			// Add new project
			$connection_query = $connection->prepare('
				INSERT INTO projects 
				SET name = :name,
					owner_id = :owner_id');
			$connection_query->execute($insert_data);


			if($connection_query) {
				//* Get created project info
				$pid = $connection->lastInsertId();
				$connection_query_existed = $connection->prepare('
					SELECT projects.id, 
						projects.name AS pname,
						users.name AS uname
					FROM `projects`, `users`
					WHERE users.id = projects.owner_id AND
						projects.id = :pid
				');
				$connection_query_existed->execute(array('pid' => $pid));
				$row_created_project = $connection_query_existed->fetch();
				$data_bus['projects'] = array();
				$data_bus['projects'][$pid] = array(
					'id' => $pid,
					'name' => $row_created_project['pname'],
					'owner' => $row_created_project['uname'],
					'tasks' => array()
				);
			
			} else {
				$data_bus['error'] = "Project wasn`t created";
			}
		}
	} else {
		$data_bus['error'] = 'Please, give name for your new project';
	}
	return $data_bus;
}

function update_project($update_data, $data_bus) {

	if($update_data['name'] != "") {
		if($update_data['project_id'] != 0) {
			$connection = new Connection($data_bus);
			if($connection != null) {
				// Update project
				$connection_query = $connection->prepare('
					UPDATE `projects`
					SET name = :name
					WHERE id = :project_id
				');
				$connection_query->execute($update_data);
				if($connection_query) {
					$data_bus = $update_data;
				} else {
					$data_bus['error'] = "Project wasn`t edited";
				}
			}
		} else {
			$data_bus['error'] = 'Please, provide id for project to edit';
		}
	} else {
		$data_bus['error'] = 'Please, give new name for your project';
	}
	return $data_bus;
}

function remove_project($project_id, $data_bus) {

	if($project_id != "") {
		$connection = new Connection($data_bus);
		if($connection != null) {
			// Remove project
			$connection_query = $connection->prepare('
				DELETE FROM projects 
				WHERE id = :project_id');
			$connection_query->execute(array('project_id' => $project_id));

			if($connection_query) {
				//TODO: remove all tasks from this project
				$data_bus['project_id'] = $project_id;
			} else {
				$data_bus['error'] = "Project wasn`t removed";
			}
		}
	} else {
		$data_bus['error'] = 'Please, set id of removing project';
	}
	return $data_bus;
}
?>