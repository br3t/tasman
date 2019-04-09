<?php

function login($login, $password) {
	global $json;

	$pdo = connect();
	$pdo_query = $pdo->prepare('
		SELECT *
		FROM `users`
		WHERE users.name = :name
	');
	$pdo_query->execute(array('name' => $login));
	$row_check_login = $pdo_query->fetch();
	//$json['error'] = 'Debug';
	
	if($row_check_login != false) {
		$hashed_pass = hash_password($password);
		if($hashed_pass == $row_check_login['password']) {
			$json['user'] = array(
				'is_logged' => true,
				'name' => $row_check_login['name'],
				'role' => $row_check_login['role']
			);
		} else {
			$json['error'] = "Wrong login/password combination";
		}
	} else {
		$json['error'] = "User can't be found";
	}
	//
	/**/
}

function check_login() {
	$user = array('is_logged' => false);
	if(isset($_COOKIE["login"]) && isset($_COOKIE["pass"])) {
		$pdo = connect();
		$pdo_check_login_query = $pdo->prepare('
			SELECT *, 
			FROM `users`
			WHERE users.name = :name AND
				users.password = :password
		');
		$pdo_check_login_query->execute(array(
			'name' => $_COOKIE["login"],
			'password' => $_COOKIE["pass"]
		));
		$row_check_login = $pdo_created_project_query->fetch();
		if($row_check_login != false) {
			$user = array(
				'is_logged' => true,
				'name' => $row_check_login['name'],
				'role' => $row_check_login['role']
			);
		}
	}
	return $user;
}

function hash_password($password) {
	return $password;
}

?>