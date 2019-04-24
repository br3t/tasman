<?php
require_once('includes.php');

$user = new User();

if(isset($_GET['username'])&&isset($_GET['password'])) {
	// Log in
	$ajax_respond = $user->login($_GET['username'], $_GET['password'], $ajax_respond);
	if(!isset($ajax_respond['error'])) {
		$ajax_respond['user'] = $user->get_meta();
	}
} else if(isset($_GET['logout'])) {
	// Log out user
	$ajax_respond = $user->logout($ajax_respond);
} else if(isset($_GET['login_check'])) {
	
	// Check if user is logged in
	$ajax_respond['user'] = array(
		'is_logged' => false
	);

	if($user->get_is_logged()) {
		$ajax_respond['user'] = $user->get_meta();
	}
} else if(isset($_GET['newUsername'])&&isset($_GET['newPassword'])) {
	// Register new user
	$ajax_respond = $user->register($_GET['newUsername'], $_GET['newPassword'], $ajax_respond);
}

print(json_encode($ajax_respond));
exit;

?>