<?php
include('includes.php');

if(isset($_GET['username'])&&isset($_GET['password'])) {
	// login
	login($_GET['username'], $_GET['password']);
}

if(isset($_GET['login_check'])) {
	check_login();
}
print(json_encode($json));
exit;

?>