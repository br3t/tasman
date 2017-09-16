<?php
//* DB connection
function connect() {
	global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD, $DB_CHARSET;

    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => 1,
    ];
    
    try {
	    $pdo = new PDO($dsn, $DB_USER, $DB_PASSWORD, $opt);
	    return $pdo;
	} catch (PDOException $e) {
		$json['error'] = array('errno' => '0', 'error' => $e->getMessage());
		print(json_encode($json));
		exit;
	}
}
?>