<?php
header("Content-type: text/json");

$json = array();

include("config.php");
@include("reconfig.php");

include("connect.php");
include("login.php");
include("projects.php");
include("tasks.php");
?>