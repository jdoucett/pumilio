<?php
session_start();

require("include/functions.php");

$config_file = 'config.php';

if (file_exists($config_file)) {
    require("config.php");
} else {
    header("Location: error.php?e=config");
    die();
}

require("include/apply_config.php");

require("include/check_login.php");

#Sanitize
$SoundID=filter_var($_GET["SoundID"], FILTER_SANITIZE_NUMBER_INT);
$newqf=filter_var($_GET["newqf"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

echo "
<html>
<head>

<title>$app_custom_name</title>";

#Get CSS
require("include/get_css.php");
require("include/get_jqueryui.php");

if ($use_googleanalytics) {
	echo $googleanalytics_code;
	}
?>

</head>
<body>

<div style="padding: 10px;">

<?php
$a = query_one("UPDATE Sounds SET QualityFlagID='$newqf' WHERE SoundID='$SoundID'", $connection);

echo "<div class=\"success\">Quality Flag changed for this file. $a</div>";

?>

<br><p><a href="#" onClick="opener.location.reload();window.close();">Close window.</a>

</div>

</body>
</html>
