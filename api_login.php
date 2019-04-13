<?php
include('includes.php');

if(isset($_GET['username'])&&isset($_GET['password'])) {
	// login
	login($_GET['username'], $_GET['password']);
}
if(isset($_GET['logout'])) {
	logout();
}

if(isset($_GET['login_check'])) {
	check_login();
}

if(isset($_GET['newUsername'])&&isset($_GET['newPassword'])) {
	register($_GET['newUsername'], $_GET['newPassword']);
}

print(json_encode($json));
exit;

?>