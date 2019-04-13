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
	$row_login = $pdo_query->fetch();
	
	if($row_login != false) {
		$hashed_pass = hash_password_1($password);
		if($hashed_pass == $row_login['password']) {
			$json['user'] = array(
				'is_logged' => true,
				'name' => $row_login['name'],
				'role' => $row_login['role']
			);
			setcookie('tasman_login', $row_login['name']);
			setcookie('tasman_pass', hash_password_2($row_login['password']));
		} else {
			$json['error'] = "Wrong login/password combination";
		}
	} else {
		$json['error'] = "User can't be found";
	}
	//
	/**/
}

function logout() {
	setcookie( 'tasman_login' , '' , time() - 3600);
	setcookie( 'tasman_pass' , '' , time() - 3600);
}

function check_login() {
	global $json;
	$user = array('is_logged' => false);
	if(isset($_COOKIE["tasman_login"]) && isset($_COOKIE["tasman_pass"])) {

		$pdo = connect();
		$pdo_query = $pdo->prepare('
			SELECT *
			FROM `users`
			WHERE users.name = :name
		');
		$pdo_query->execute(array(
			'name' => $_COOKIE["tasman_login"]
		));
		$row_check_login = $pdo_query->fetch();
		if($row_check_login != false) {
			$hashed_pass = hash_password_2($row_check_login['password']);
			if($hashed_pass == $_COOKIE["tasman_pass"]) {
				$user = array(
					'is_logged' => true,
					'name' => $row_check_login['name'],
					'uid' => $row_check_login['id'],
					'role' => $row_check_login['role']
				);
			}
		}
	}
	$json['user'] = $user;
}

function hash_password_1($password) {
	return $password;
}

function hash_password_2($password) {
	return $password;
}

?>