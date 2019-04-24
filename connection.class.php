<?php

//* DB connection
class Connection {

	private $pdo;

	public function __construct($data_bus) {
		global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD, $DB_CHARSET;

		$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
		$opt = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => 1
		];
		
		try {
			$pdo = new PDO($dsn, $DB_USER, $DB_PASSWORD, $opt);
			//return $pdo;
			$this->pdo = $pdo;
		} catch (PDOException $e) {
			$data_bus['error'] = array('errno' => '0', 'error' => $e->getMessage());
			return null;
		}

	}

	public function prepare($query_string) {
		return $this->pdo->prepare($query_string);
	}

	public function query($query_string) {
		return $this->pdo->query($query_string);
	}

	public function lastInsertId() {
		return $this->pdo->lastInsertId();
	}

}


?>