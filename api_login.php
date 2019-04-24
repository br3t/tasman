<?php
require_once('includes.php');

if(isset($_GET['username'])&&isset($_GET['password'])) {
	// Log in
	$ajax_respond = login($_GET['username'], $_GET['password'], $ajax_respond);
} else if(isset($_GET['logout'])) {
	// Log out user
	$ajax_respond = logout($ajax_respond);
} else if(isset($_GET['login_check'])) {
	// Check if user is logged in
	$ajax_respond = check_login($ajax_respond);
} else if(isset($_GET['newUsername'])&&isset($_GET['newPassword'])) {
	// Register new user
	$ajax_respond = register($_GET['newUsername'], $_GET['newPassword'], $ajax_respond);
}
print(json_encode($ajax_respond));
exit;

?>