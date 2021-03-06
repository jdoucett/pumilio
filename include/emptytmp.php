<?php
session_start();

require("functions.php");
require("../config.php");
#require("apply_config.php");

$absolute_dir=dirname(__FILE__);

$absolute_dir = preg_replace('/include$/', '', $absolute_dir);

$app_dir = substr($absolute_dir, strlen($_SERVER['DOCUMENT_ROOT']));

$app_url = "http://" . $_SERVER['SERVER_NAME'] . $app_dir;

$app_url = rtrim(preg_replace('/include$/', '', $app_url), "/");

$connection = @mysqli_connect($host, $user, $password, $database);

#If could not connect, redirect
if (!$connection) {
	header("Location: ../error.php?e=db");
	die();
	}

mb_language('uni');
mb_internal_encoding('UTF-8');

$force_admin = TRUE;
require("check_admin.php");

echo "<!DOCTYPE html>
<html lang=\"en\">
<head>

<title>Administration Area</title>";

#Get CSS
	require("get_css3_include.php");
	require("get_jqueryui_include.php");


if (isset($_GET["run"])){
	$run = $_GET['run'];
	}
else{
	$run = FALSE;
	}
	
if ($run){
	set_time_limit(0);

	echo "</head>
	<body>

	<div style=\"padding: 10px;\">

	<br><br><br>\n";

	#Del temp files
	delSubTree("../tmp/");

	echo "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span>
		Temp directory cleared</div>
	<br><br><br>
	<p><a href=\"#\" onClick=\"window.close();\">Close window</a>\n";

	}
else{
	echo "<meta http-equiv=\"refresh\" content=\"1;url=emptytmp.php?run=TRUE\">

	</head>
	<body>

	<div style=\"padding: 10px;\">

		<br><br><br>
		<h3>Working... 
		<br>Please wait... <i class=\"fa fa-cog fa-spin\"></i>
		</h3>

		<br><br><br>
	<br><p><a href=\"#\" onClick=\"window.close();\">Cancel and close window</a>";
	}
	
?>

</div>

</body>
</html>
