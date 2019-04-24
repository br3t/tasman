<?php
header("Content-type: text/json");

$ajax_respond = array();

require_once("config.php");
@require_once("reconfig.php");

require_once("connection.class.php");
require_once("login.php");
require_once("projects.php");
require_once("tasks.php");
?>