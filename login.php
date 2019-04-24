<?php

function login($login, $password, $data_bus) {

	$connection = new Connection($data_bus);
	if($connection != null) {
		$connection_query = $connection->prepare('
			SELECT *
			FROM `users`
			WHERE users.name = :name
		');
		$connection_query->execute(array('name' => $login));
		$row_login = $connection_query->fetch();
		
		if($row_login != false) {
			$hashed_pass = hash_password_1($password);
			if($hashed_pass == $row_login['password']) {
				$data_bus['user'] = array(
					'is_logged' => true,
					'uid' => $row_login['id'],
					'name' => $row_login['name'],
					'role' => $row_login['role']
				);
				setcookie('tasman_login', $row_login['name']);
				setcookie('tasman_pass', hash_password_2($row_login['password']));
			} else {
				$data_bus['error'] = "Wrong login/password combination";
			}
		} else {
			$data_bus['error'] = "User can't be found";
		}
	}
	return $data_bus;
}

function logout($data_bus) {
	setcookie( 'tasman_login' , '' , time() - 3600);
	setcookie( 'tasman_pass' , '' , time() - 3600);
	return $data_bus;
}

function check_login($data_bus) {

	$user = array('is_logged' => false);
	if(isset($_COOKIE["tasman_login"]) && isset($_COOKIE["tasman_pass"])) {

		$connection = new Connection($data_bus);
		if($connection != null) {
			$connection_query = $connection->prepare('
				SELECT *
				FROM `users`
				WHERE users.name = :name
			');
			$connection_query->execute(array(
				'name' => $_COOKIE["tasman_login"]
			));
			$row_check_login = $connection_query->fetch();
			if($row_check_login != false) {
				$hashed_pass = hash_password_2($row_check_login['password']);
				if($hashed_pass == $_COOKIE["tasman_pass"]) {
					$user = array(
						'is_logged' => true,
						'uid' => $row_check_login['id'],
						'name' => $row_check_login['name'],
						'role' => $row_check_login['role']
					);
				}
			}
		}
	}
	$data_bus['user'] = $user;
	return $data_bus;
}

function register($login, $password, $data_bus) {

	if(strlen($login) < 4) {
		$data_bus['error'] = 'Login too short. Please, use at least 4 symbols';
	} else if(strlen($password) < 4) {
		$data_bus['error'] = 'Password too short. Please, use at least 4 symbols';
	} else if(preg_match('/[^a-z\d]/', $login) == 1){
		$data_bus['error'] = 'Please, use only latin letters and digits for login';
	} else {
		// check if login is free
		$connection = new Connection($data_bus);
		if($connection != null) {
			$connection_query = $connection->prepare('
				SELECT *
				FROM `users`
				WHERE users.name = :name
			');
			$connection_query->execute(array(
				'name' => $login
			));
			$row_check_login = $connection_query->fetch();
			if($row_check_login != false) {
				$data_bus['error'] = 'Username is taken. Please choose another one.';
			} else {
				// register new user
				$connection_query_new_user = $connection->prepare('
					INSERT INTO `users`
					SET name = :name,
						password = :password,
						role = 2
				');
				$connection_query_new_user->execute(array(
					'name' => $login,
					'password' => hash_password_1($password)
				));
				if(!$connection_query_new_user) {
					$data_bus['error'] = 'Cannot create new user';
				}
			}
		}
	}
	return $data_bus;

}

function hash_password_1($password) {
	return md5(SALT_1.$password);
}
function hash_password_2($password) {
	return md5(SALT_2.$password);
}

?>