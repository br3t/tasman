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


function register($login, $password) {
	global $json;
	if(strlen($login) < 4) {
		$json['error'] = 'Login too short. Please, use at least 4 symbols';
	} else if(strlen($password) < 4) {
		$json['error'] = 'Password too short. Please, use at least 4 symbols';
	} else if(preg_match('/[^a-z\d]/', $login) == 1){
		$json['error'] = 'Please, use only latin letters and digits for login';
	} else {
		// check if login is free
		$pdo = connect();
		$pdo_query = $pdo->prepare('
			SELECT *
			FROM `users`
			WHERE users.name = :name
		');
		$pdo_query->execute(array(
			'name' => $login
		));
		$row_check_login = $pdo_query->fetch();
		if($row_check_login != false) {
			$json['error'] = 'Username is taken. Please choose another one.';
		} else {
			// register new user
			$pdo_new_user_query = $pdo->prepare('
				INSERT INTO `users`
				SET name = :name,
					password = :password,
					role = 2
			');
			$pdo_new_user_query->execute(array(
				'name' => $login,
				'password' => hash_password_1($password)
			));
			if(!$pdo_new_user_query) {
				$json['error'] = 'Cannot create new user';
			}
		}
	}
}

function hash_password_1($password) {
	return md5(SALT_1.$password);
}

function hash_password_2($password) {
	return md5(SALT_2.$password);
}

?>