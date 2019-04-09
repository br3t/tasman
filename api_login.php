<?php
include('includes.php');

if(isset($_GET['username'])&&isset($_GET['password'])) {
	// login
	login($_GET['username'], $_GET['password']);
	print(json_encode($json));
	exit;
}

?>