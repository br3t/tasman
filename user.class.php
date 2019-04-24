<?php

class User {

	private $name;
	private $id;
	private $role;
	private $is_logged = false;

	public function __construct() {
		$this->check_login();
		return $this;
	}

	public function login($login, $password, $data_bus) {
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
				$hashed_pass = $this->hash_password($password);
				if($hashed_pass == $row_login['password']) {
					$this->is_logged = true;
					$this->id = $row_login['id'];
					$this->name = $row_login['name'];
					$this->role = $row_login['role'];

					setcookie('tasman_login', $row_login['name']);
					setcookie('tasman_pass', $this->hash_cookie($row_login['password']));
					$data_bus['cc'] = 1;
				} else {
					$data_bus['error'] = "Wrong login/password combination";
				}
			} else {
				$data_bus['error'] = "User can't be found";
			}
		}
		return $data_bus;
	}

	public function logout($data_bus) {
		setcookie( 'tasman_login' , '' , time() - 3600);
		setcookie( 'tasman_pass' , '' , time() - 3600);
		$this->is_logged = false;
		return $data_bus;
	}

	public function check_login() {

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
					$hashed_pass = $this->hash_cookie($row_check_login['password']);
					if($hashed_pass == $_COOKIE["tasman_pass"]) {
						$this->is_logged = true;
						$this->uid = $row_check_login['id'];
						$this->name = $row_check_login['name'];
						$this->role = $row_check_login['role'];
					}
				}
			}
		}
		//return $this;
	}

	public function register($login, $password, $data_bus) {

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
						'password' => $this->hash_password($password)
					));
					if(!$connection_query_new_user) {
						$data_bus['error'] = 'Cannot create new user';
					}
				}
			}
		}
		return $data_bus;
	}

	public function get_is_logged() {
		return $this->is_logged;
	}
	public function get_role() {
		return $this->role;
	}
	public function get_name() {
		return $this->name;
	}
	public function get_id() {
		return $this->id;
	}

	public function get_meta() {
		return array(
			'is_logged' => true,
			'name' => $this->get_name(),
			'id' => $this->get_id(),
			'role' => $this->get_role()
		);
	}

	private function hash_password($password) {
		return md5(SALT_1.$password);
	}

	private function hash_cookie($hashed_password) {
		return md5(SALT_2.$hashed_password);
	}

}




?>