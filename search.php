<?php
session_start();

require("include/functions.php");

$config_file = 'config.php';

if (file_exists($config_file)) {
    require("config.php");
} else {
    header("Location: $app_url/error.php?e=config");
    die();
}

require("include/apply_config.php");

#If user is not logged in, add check for QF
if (!sessionAuthenticate($connection)) {
	$qf_check = "AND Sounds.QualityFlagID>='$default_qf'";
	}
else {
	$qf_check = "";
	}

echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
<title>$app_custom_name</title>";

require("include/get_css3.php");
require("include/get_jqueryui.php");

	echo "
	<!-- Form validation from http://bassistance.de/jquery-plugins/jquery-plugin-validation/ -->
	<script src=\"js/jquery.validate.js\"></script>";

	$DateLow = query_one("SELECT DATE_FORMAT(Date,'%Y, %c-1, %e') FROM Sounds WHERE SoundStatus!='9' $qf_check ORDER BY Date LIMIT 1", $connection);
	$DateHigh = query_one("SELECT DATE_FORMAT(Date,'%Y, %c-1, %e') FROM Sounds WHERE SoundStatus!='9' $qf_check ORDER BY Date DESC LIMIT 1", $connection);
	$DateLow1 = query_one("SELECT DATE_FORMAT(Date,'%d-%b-%Y') FROM Sounds WHERE SoundStatus!='9' $qf_check ORDER BY Date LIMIT 1", $connection);
	$DateHigh1 = query_one("SELECT DATE_FORMAT(Date,'%d-%b-%Y') FROM Sounds WHERE SoundStatus!='9' $qf_check ORDER BY Date DESC LIMIT 1", $connection);
	#from http://jsbin.com/orora3/75/
	echo "	
	<script type=\"text/javascript\">
	$(function() {
		var dates = $( \"#startDate, #endDate\" ).datepicker({
			minDate: new Date($DateLow),
			maxDate: new Date($DateHigh),
			numberOfMonths: 3,
			changeYear: true,
			dateFormat: 'dd-MM-yy',
			onSelect: function( selectedDate ) {
				var option = this.id == \"startDate\" ? \"minDate\" : \"maxDate\",
				instance = $( this ).data( \"datepicker\" ),
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
				dates.not( this ).datepicker( \"option\", option, date );
			}
		});
	});

	</script>
	";

	#Duration slider
	#Get min and max
	$DurationLow = floor(query_one("SELECT DISTINCT Duration FROM Sounds WHERE Duration IS NOT NULL AND SoundStatus!='9' $qf_check ORDER BY Duration LIMIT 1", $connection));
	$DurationHigh = ceil(query_one("SELECT DISTINCT Duration FROM Sounds WHERE Duration IS NOT NULL AND SoundStatus!='9' $qf_check ORDER BY Duration DESC LIMIT 1", $connection));

	echo "<script>
		$(function() {
			$( \"#durationslider\" ).slider({
			range: true,
				min: $DurationLow,
				max: $DurationHigh,
				values: [ $DurationLow, $DurationHigh ],
				slide: function( event, ui ) {
					$( \"#startDuration\" ).val( $( \"#durationslider\" ).slider( \"values\", 0 ));
					$( \"#endDuration\" ).val( $( \"#durationslider\" ).slider( \"values\", 1 ));
				}
			});
			$( \"#startDuration\" ).val( $( \"#durationslider\" ).slider( \"values\", 0 ));
			$( \"#endDuration\" ).val( $( \"#durationslider\" ).slider( \"values\", 1 ));
			});
		</script>";


if ($use_googleanalytics) {
	echo $googleanalytics_code;
	}
	

#Execute custom code for head, if set
if (is_file("$absolute_dir/customhead.php")) {
		include("customhead.php");
	}
	
	

echo "</head>\n";
echo "<body>";

?>

	<!--Blueprint container-->
	<div class="container">
		<?php
			require("include/topbar.php");
		?>
				
		<h2>Search</h2>
			<?php


			echo "<p>Search sounds in the archive using several options:<br><hr noshade>";

			$no_sounds_s = query_one("SELECT COUNT(*) as no_sounds FROM Sounds WHERE SoundStatus!='9' $qf_check", $connection);
			if ($no_sounds_s == 0){
				echo "<p>This archive does not have any sounds.";
				}
			else {
				#Search by ID
				echo "<div class=\"row\">
						<div class=\"col-lg-6\">



							<div class=\"panel panel-primary\">
								<div class=\"panel-heading\">
									<h3 class=\"panel-title\">Search by Sound ID</h3>
								</div>
								<div class=\"panel-body\">
									<form action=\"db_filedetails.php\" method=\"GET\" class=\"form-inline\">
									 	<label for=\"SoundID\">Sound ID: </label>
										<input type=\"text\" name=\"SoundID\" id=\"SoundID\" class=\"form-control\" placeholder=\"SoundID\">
										
										<button type=\"submit\" class=\"btn btn-primary\"> Search </button>
									</form>
								</div>
							</div>";

					echo "</div><div class=\"col-lg-6\">&nbsp;</div></div>\n";

			echo "<div class=\"row\">
				<div class=\"col-lg-8\">";
				require("include/mainsearch.php");
			echo "</div>

				<div class=\"col-lg-4\"></div>
			</div>
			<br><br>";
			}
			?>

		<br>
		</div>
		
<?php
	require("include/bottom.php");
?>


</body>
</html>