<?php

echo "<hr noshade>
	<p><strong>Advanced search</strong>:";

echo "<form action=\"$advancedsearch_link\" method=\"GET\">
	<table styleborder=\"1\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-left: 10px;\">
	<tr>
	<td>
		Collection: 
	</td>
	<td style=\"text-align:right;\">
		<select name=\"Col_comparison\" class=\"ui-state-default ui-corner-all\">
			<option value=\"1\" SELECTED> is </option>
			<option value=\"2\"> is not </option>
		</select> &nbsp;

	</td>
	<td><select name=\"Col\" class=\"ui-state-default ui-corner-all\">
		<option value=\"0\"></option>";
					
		#Get all dates
		$query_dates = "SELECT ColID, CollectionName FROM Collections ORDER BY CollectionName";
		$result_dates=query_several($query_dates, $connection);
		$nrows_dates = mysqli_num_rows($result_dates);

		if ($nrows_dates>0) {
			for ($d=0;$d<$nrows_dates;$d++)	{
				$row_dates = mysqli_fetch_array($result_dates);
				extract($row_dates);
				echo "\n<option value=\"$ColID\">$CollectionName</option>";
				}
			}
	echo "</select>
	</td>
	</tr>

	<tr>
	<td>Time range:
	</td><td style=\"text-align:right;\">&nbsp;

	</td><td>
	
		<input type=\"text\" id=\"startTime\" name=\"startTime\" value=\"00:00\" size=\"10\" class=\"fg-button ui-state-default ui-corner-all\" readonly /> to 
		<input type=\"text\" id=\"endTime\" name=\"endTime\" value=\"23:59\" size=\"10\" class=\"fg-button ui-state-default ui-corner-all\" readonly /> 
		<div id=\"datemsg\"></div>
	
		</td>
	</tr>

	<tr>
	<td>
	Date range: 
	</td><td style=\"text-align:right;\">&nbsp;

	</td><td>
			
		<input type=\"text\" id=\"startDate\" name=\"startDate\" value=\"$DateLow1\" size=\"10\" class=\"fg-button ui-state-default ui-corner-all\" readonly /> to 
		<input type=\"text\" id=\"endDate\" name=\"endDate\" value=\"$DateHigh1\" size=\"10\" class=\"fg-button ui-state-default ui-corner-all\" readonly />
		
		</td>
	</tr>

	<tr>
	<td>Site: 
	</td><td style=\"text-align:right;\">
	<select name=\"Site_comparison\" class=\"ui-state-default ui-corner-all\">
		<option value=\"1\" SELECTED> is </option>
		<option value=\"2\"> is not </option>
		</select> &nbsp;

	</td><td>
	<select name=\"SiteID\" class=\"ui-state-default ui-corner-all\">
		<option value=\"0\"></option>";
					
		#Get all dates
		$query_sites = "SELECT SiteID, SiteName FROM Sites ORDER BY SiteName";
		$result_sites=query_several($query_sites, $connection);
		$nrows_sites = mysqli_num_rows($result_sites);

		if ($nrows_sites>0) {
			for ($s=0;$s<$nrows_sites;$s++) {
				$row_sites = mysqli_fetch_array($result_sites);
				extract($row_sites);

				$check_site=query_one("SELECT COUNT(*) FROM Sounds WHERE SiteID='$SiteID'", $connection);

				if ($check_site > 0){
					echo "\n<option value=\"$SiteID\">$SiteName</option>";
					}
				}
			}
		echo "</select>
	</td>
	</tr>

	<tr>

	<td>Tags:
	</td><td style=\"text-align:right;\">
	<select name=\"Tag_comparison\" class=\"ui-state-default ui-corner-all\">
		<option value=\"1\" SELECTED> include </option>
		</select> &nbsp;

	</td><td>
	<select name=\"Tags\" class=\"ui-state-default ui-corner-all\">
		<option value=\"0\"></option>";
				
		#Get all dates
		$query_tags = "SELECT Tag FROM Tags GROUP BY Tag ORDER BY Tag";
		$result_tags=query_several($query_tags, $connection);
		$nrows_tags = mysqli_num_rows($result_tags);

		if ($nrows_tags>0) {
			for ($t=0;$t<$nrows_tags;$t++) {
				$row_tags = mysqli_fetch_array($result_tags);
				extract($row_tags);

				echo "\n<option value=\"$Tag\">$Tag</option>";
				}
			}
		echo "</select>
	</td>
	</tr>

	<tr>

	<td>Duration:
	</td><td style=\"text-align:right;\">&nbsp;

	</td><td>
		<div style=\"margin: 10 10 10 10;\"><div id=\"durationslider\"></div></div>
		
		<input type=\"text\" id=\"startDuration\" name=\"startDuration\" value=\"$DurationLow\" size=\"10\" class=\"fg-button ui-state-default ui-corner-all\" readonly /> to 
		<input type=\"text\" id=\"endDuration\" name=\"endDuration\" value=\"$DurationHigh\" size=\"10\" class=\"fg-button ui-state-default ui-corner-all\" readonly /> seconds

	</td>
	</tr>

	<tr>
	<td>
	Number of channels: 
	</td><td style=\"text-align:right;\">
	<select name=\"Channels_comparison\" class=\"ui-state-default ui-corner-all\">
		<option value=\"1\" SELECTED> is </option>
		<option value=\"2\"> is not </option>
		</select> &nbsp;
	</td><td>
	<select name=\"Channels\" class=\"ui-state-default ui-corner-all\">
		<option value=\"0\" SELECTED></option>
		<option value=\"1\">1</option>
		<option value=\"2\">2</option>
		</select>
	</td>
	</tr>

	<tr>
	
	<td>Sampling Rate: 
	</td><td style=\"text-align:right;\">
	<select name=\"SamplingRate_comparison\" class=\"ui-state-default ui-corner-all\">
		<option value=\"1\" SELECTED> is </option>
		<option value=\"2\"> is not </option>
		</select> &nbsp;

	</td><td>
	<select name=\"SamplingRate\" class=\"ui-state-default ui-corner-all\">
		<option value=\"0\"></option>";
				
		#Get all dates
		$query_SamplingRate = "SELECT DISTINCT SamplingRate FROM Sounds WHERE SamplingRate IS NOT NULL ORDER BY SamplingRate";
		$result_SamplingRate=query_several($query_SamplingRate, $connection);
		$nrows_SamplingRate = mysqli_num_rows($result_SamplingRate);

		if ($nrows_SamplingRate>0) {
			for ($d=0;$d<$nrows_SamplingRate;$d++) {
				$row_SamplingRate = mysqli_fetch_array($result_SamplingRate);
				extract($row_SamplingRate);
				echo "\n<option value=\"$SamplingRate\">$SamplingRate</option>";
				}
			}
		echo "</select> Hz
	</td>
	</tr>

	<tr>
	<td colspan=\"3\">
		Order by: &nbsp; <select name=\"Orderby\" class=\"ui-state-default ui-corner-all\">
			<option value=\"Sounds.SoundID\" SELECTED>original order</option>
			<option value=\"Date\">Date</option>
			<option value=\"Time\">Time</option>
			<option value=\"SiteID\">Site</option>
			<option value=\"Duration\">Duration</option>
			</select> &nbsp; in &nbsp; 
 		<select name=\"Orderby_dir\" class=\"ui-state-default ui-corner-all\">
			<option value=\"ASC\" SELECTED>ascending order</option>
			<option value=\"DESC\">descending order</option>
			</select>
	</td>
	</tr>
	</table>

	<input type=submit value=\" Advanced Search \" class=\"fg-button ui-state-default ui-corner-all\" style=\"margin-left: 16px;\">
	</form>
	</p>";

?>
