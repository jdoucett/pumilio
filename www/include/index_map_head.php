<?php


$date_to_browse=filter_var($_GET["date_to_browse"], FILTER_SANITIZE_STRING);
$time_to_browse=filter_var($_GET["time_to_browse"], FILTER_SANITIZE_STRING);
$usekml=filter_var($_GET["usekml"], FILTER_SANITIZE_NUMBER_INT);
$nokml=filter_var($_GET["nokml"], FILTER_SANITIZE_NUMBER_INT);

$no_res = 0;
$error_msg="";

#Get points from the database
$query = "SELECT * FROM Sites WHERE SiteLat IS NOT NULL AND SiteLon IS NOT NULL ORDER BY SiteName";
$result=query_several($query, $connection);
$nrows = mysqli_num_rows($result);

if ($nrows>0) {
	$map_div_message="Your browser does not have JavaScript enabled, which is required to proceed. Please enable JavaScript or contact your system administrator for help.";
	}
else {
	$error_msg="There are no sound files with location data.";
	}

if ($nrows>0) {
	$sites_rows=array();
	$sites_bounds=array();
	}
	
if ($googlemaps_ver == "2"){
	echo "<script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=$googlemaps_key\" type=\"text/javascript\"></script>
	<script type=\"text/javascript\">

	function initialize() {
	if (GBrowserIsCompatible()) {
	var map = new GMap2(document.getElementById(\"map_canvas\"));\n";

	#Limit zoom for guests
	if (!sessionAuthenticate($connection) && $hide_latlon_guests) {
		echo "
		// ====== Restricting the range of Zoom Levels =====
		// Get the list of map types      
		var mt = map.getMapTypes();
		// Overwrite the getMinimumResolution() and getMaximumResolution() methods
		for (var i=0; i<mt.length; i++) {
		mt[i].getMaximumResolution = function() {return 12;}
		}";
		$approx_size="<br><em>The site is approximate.</em>";
		}
	else {
		$approx_size = "";
		}
		
	echo " \n\n	map.setCenter(new GLatLng(0,0),0);
		var bounds = new GLatLngBounds();
		map.addMapType(G_PHYSICAL_MAP);
		map.addControl(new GMapTypeControl());
		map.addControl(new GLargeMapControl3D());
		map.addControl(new GScaleControl());
		map.enableDoubleClickZoom();
		map.enableContinuousZoom();
		map.enableScrollWheelZoom(); 

		var baseIcon = new GIcon();
		baseIcon.iconSize=new GSize(32,32);
		baseIcon.shadowSize=new GSize(56,32);
		baseIcon.iconAnchor=new GPoint(16,32);
		baseIcon.infoWindowAnchor=new GPoint(16,0);

		var dicon = new GIcon(G_DEFAULT_ICON);
		var weathericon = new GIcon(baseIcon, \"http://maps.google.com/mapfiles/kml/pal4/icon22.png\", null, \"http://maps.google.com/mapfiles/kml/pal4/icon22s.png\");

		function createMarker(point,html,icon) {
			var marker = new GMarker(point,icon);
			GEvent.addListener(marker, \"click\", function() {
				marker.openInfoWindowHtml(html);
			});
			return marker;
		}\n";

	for ($i=0;$i<$nrows;$i++) {
		$row = mysqli_fetch_array($result);
		extract($row);

		#Add error to the lat long for guests
		if (!sessionAuthenticate($connection) && $hide_latlon_guests) {
			$rand_dir=rand(0,1);
			$rand_error=(rand(0,100))/10000;
			if ($rand_dir==0) {
				$SiteLat=$SiteLat+$rand_error;
				}
			else {
				$SiteLat=$SiteLat-$rand_error;
				}
			
			$rand_dir=rand(0,1);
			$rand_error=(rand(0,100))/10000;
			if ($rand_dir==0){
				$SiteLon=$SiteLon+$rand_error;
				}
			else {
				$SiteLon=$SiteLon-$rand_error;
				}
			}

		if ($date_to_browse=="") {
			$no_sounds=query_one("SELECT COUNT(*) AS no_sounds FROM Sounds WHERE SiteID=$SiteID AND Sounds.SoundStatus!='9' $qf_check", $connection);
			if ($no_sounds>0) {

				$SiteName=filter_var($SiteName, FILTER_SANITIZE_STRING);

				if ($no_sounds==1) {
					$no_sounds_f = "One sound";
					}
				else {
					$no_sounds_f = "$no_sounds sounds";
					}

				#Set each point
				#Each point will help determine the map's extent, from http://econym.org.uk/gmap/basic14.htm
				$first_date=query_one("SELECT DATE_FORMAT(Date,'%d-%b-%Y') AS first_date FROM Sounds 
					WHERE SiteID=$SiteID AND Sounds.SoundStatus!='9' $qf_check 
					ORDER BY Date ASC LIMIT 1", $connection);
				$last_date=query_one("SELECT DATE_FORMAT(Date,'%d-%b-%Y') AS last_date FROM Sounds 
					WHERE SiteID=$SiteID AND Sounds.SoundStatus!='9' $qf_check 
					ORDER BY Date DESC LIMIT 1", $connection);

				if ($special_wrapper==TRUE){
					echo "
					var point = new GLatLng($SiteLat, $SiteLon);
					var marker = createMarker(point,'<div style=\"width:240px\"><div class=\"highlight4 ui-corner-all\"><a href=\"$wrapper?page=browse_site&SiteID=$SiteID\" style=\"color: white;\">$SiteName</a></div>$no_sounds_f  at this site<br>Sounds available from $first_date to $last_date. $approx_size</div>', dicon)
					map.addOverlay(marker);
					bounds.extend(point);\n\n";
					}
				else {
					echo "
					var point = new GLatLng($SiteLat, $SiteLon);
					var marker = createMarker(point,'<div style=\"width:240px\"><div class=\"highlight4 ui-corner-all\"><a href=\"browse_site.php?SiteID=$SiteID\" style=\"color: white;\">$SiteName</a></div>$no_sounds_f  at this site<br>Sounds available from $first_date to $last_date. $approx_size</div>', dicon)
					map.addOverlay(marker);
					bounds.extend(point);\n\n";
					}
			
					$no_res++;
				}
			}
		else {

		$SiteName=filter_var($SiteName, FILTER_SANITIZE_STRING);

		if ($time_to_browse=="") {
			$no_sounds=query_one("SELECT COUNT(*) AS no_sounds FROM Sounds WHERE SiteID='$SiteID' AND Date='$date_to_browse' AND Sounds.SoundStatus!='9'", $connection);

			if ($no_sounds>0) {
				if ($no_sounds==1) {
					$no_sounds_f = "One sound";
					}
				else {
					$no_sounds_f = "$no_sounds sounds";
					}
				#Set each point
				#Each point will help determine the map's extent, from http://econym.org.uk/gmap/basic14.htm
				echo "	";

				if ($special_wrapper==TRUE){
					echo "	var point = new GLatLng($SiteLat,$SiteLon);
						var marker = createMarker(point,'<div style=\"width:240px; height:280px; overflow:auto;\"><div class=\"highlight4 ui-corner-all\">$SiteName</div><a href=\"$wrapper?page=browse_site_date&SiteID=$SiteID&Date=$date_to_browse\">$no_sounds_f at this site for this date</a>:";
					}
				else {
					echo "  var point = new GLatLng($SiteLat,$SiteLon);
						var marker = createMarker(point,'<div style=\"width:240px; height:280px; overflow:auto;\"><div class=\"highlight4 ui-corner-all\">$SiteName</div><a href=\"browse_site_date.php?SiteID=$SiteID&Date=$date_to_browse\">$no_sounds_f at this site for this date</a>:";
					}
	
				$query_by_dates = "SELECT DISTINCT DATE_FORMAT(Date,'%d-%b-%Y') AS Date_f, DATE_FORMAT(Time,'%h:%i %p') AS Time_f, SoundID, SoundName 
					FROM Sounds
					WHERE Date IS NOT NULL AND SiteID=$SiteID AND Date='$date_to_browse' 
					AND Sounds.SoundStatus!='9' $qf_check ORDER BY Time ASC";
				$result_by_dates=query_several($query_by_dates, $connection);
				$nrows_by_dates = mysqli_num_rows($result_by_dates);

				for ($dd=0;$dd<$nrows_by_dates;$dd++) {
					$row_by_dates = mysqli_fetch_array($result_by_dates);
					extract($row_by_dates);

					if (is_odd($dd)) {
						if ($special_wrapper==TRUE){
							echo "<p><a href=\"$wrapper?page=db_filedetails&SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
						else {
							echo "<p><a href=\"db_filedetails.php?SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
						}
					else {
						if ($special_wrapper==TRUE){
							echo "<p style=\"background-color:#E0EEEE;\"><a href=\"$wrapper?page=db_filedetails&SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
						else {
							echo "<p style=\"background-color:#E0EEEE;\"><a href=\"db_filedetails.php?SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
		
						}
					}

				echo "</div>$approx_size')
				map.addOverlay(marker);
				bounds.extend(point);\n\n";
				$no_res++;

				$this_page_title="Browse Map for $Date_f";
				}
			}
		else {
			$no_sounds=query_one("SELECT COUNT(*) AS no_sounds FROM Sounds WHERE SiteID=$SiteID AND Date='$date_to_browse' 
				AND Time='$time_to_browse' AND Sounds.SoundStatus!='9' $qf_check", $connection);
			if ($no_sounds>0) {
				if ($no_sounds==1) {
					$no_sounds_f = "One sound";
					}
				else {
					$no_sounds_f = "$no_sounds sounds";
					}
				#Set each point
				#Each point will help determine the map's extent, from http://econym.org.uk/gmap/basic14.htm
				echo "	var point = new GLatLng($SiteLat,$SiteLon);
					var marker = createMarker(point,'<div style=\"width:240px; height:280px; overflow:auto;\"><div class=\"highlight4 ui-corner-all\">$SiteName</div>$no_sounds_f at this site for this date and time:";

				$query_by_dates = "SELECT DISTINCT DATE_FORMAT(Date,'%d-%b-%Y') AS Date_f, DATE_FORMAT(Time,'%h:%i %p') AS Time_f, SoundID, SoundName FROM Sounds 
					WHERE Date IS NOT NULL AND SiteID=$SiteID AND Date='$date_to_browse' and Time='$time_to_browse' 
					AND Sounds.SoundStatus!='9' $qf_check ORDER BY Time ASC";
				$result_by_dates=query_several($query_by_dates, $connection);
				$nrows_by_dates = mysqli_num_rows($result_by_dates);

				for ($dd=0;$dd<$nrows_by_dates;$dd++) {
					$row_by_dates = mysqli_fetch_array($result_by_dates);
					extract($row_by_dates);

					if (is_odd($dd)) {
						if ($special_wrapper==TRUE){
							echo "<p><a href=\"$wrapper?page=db_filedetails&SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
						else {
							echo "<p><a href=\"db_filedetails.php?SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}						
						}
					else {
						if ($special_wrapper==TRUE){
							echo "<p style=\"background-color:#E0EEEE;\"><a href=\"$wrapper?page=db_filedetails&SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
						else {
							echo "<p style=\"background-color:#E0EEEE;\"><a href=\"db_filedetails.php?SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
						}
					}

				echo "</div>$approx_size')
				map.addOverlay(marker);
				bounds.extend(point);\n\n";

				$no_res++;
				$this_page_title="Browse Map for $Date_f $Time_f";
				}
			}
		}

		#use the data from the sites to create a pull-down
		if (isset($date_to_browse) && $date_to_browse!="") {
			if ($no_sounds>0) {
				array_push($sites_rows, "<option value=\"$SiteID\">$SiteName - $no_sounds sounds on $Date_f</option>\n");
				}
			}
		else {
			if ($no_sounds>0) {
				array_push($sites_rows, "<option value=\"$SiteID\">$SiteName - $no_sounds sounds between $first_date and $last_date</option>\n");
				}
			}
		}


	echo "
	map.setZoom(map.getBoundsZoomLevel(bounds));
	map.setCenter(bounds.getCenter());\n";
	#Check if any KML to use
	if ($usekml=="1"){
		for ($k=0;$k<$nokml;$k++) {
			$this_kmlID=filter_var($_GET["kml$k"], FILTER_SANITIZE_NUMBER_INT);
			$this_kmlurl=query_one("SELECT KmlURL FROM Kml WHERE KmlID='$this_kmlID'", $connection);
			#add selected kml layers
			echo "\nvar kml$k = new GGeoXml(\"$this_kmlurl\");
			map.addOverlay(kml$k);\n";
			}
		}
	else {
		$result_kml=query_several("SELECT * FROM Kml WHERE KmlDefault='1'", $connection);
		$nrows_kml = mysqli_num_rows($result_kml);

		if ($nrows_kml > 0) {
			$kml_default=1;
	
			for ($k=0;$k<$nrows_kml;$k++) {
				$row_kml = mysqli_fetch_array($result_kml);
				extract($row_kml);
	
				echo "\nvar kml$k = new GGeoXml(\"$KmlURL\");
				map.addOverlay(kml$k);\n";
				}
			}
		}

	echo "
		}
		}
	</script>\n";
	}
elseif ($googlemaps_ver == "3"){
########################
# GOOGLE MAPS v3
########################
	echo "<script src=\"http://maps.googleapis.com/maps/api/js?key=$googlemaps3_key&sensor=false\" type=\"text/javascript\"></script>\n";
	
	echo "<script type=\"text/javascript\">
		var infowindow = null;
    		$(document).ready(function () { initialize();  });

   		var sites = [\n";
		for ($i=0;$i<$nrows;$i++) {
			$row = mysqli_fetch_array($result);
			extract($row);

			#Add error to the lat long for guests
			if (!sessionAuthenticate($connection) && $hide_latlon_guests) {
				$rand_dir=rand(0,1);
				$rand_error=(rand(0,100))/10000;
				if ($rand_dir==0) {
					$SiteLat=$SiteLat+$rand_error;
					}
				else {
					$SiteLat=$SiteLat-$rand_error;
					}
			
				$rand_dir=rand(0,1);
				$rand_error=(rand(0,100))/10000;
				if ($rand_dir==0){
					$SiteLon=$SiteLon+$rand_error;
					}
				else {
					$SiteLon=$SiteLon-$rand_error;
					}
				}

			if ($date_to_browse=="") {
				$no_sounds=query_one("SELECT COUNT(*) AS no_sounds FROM Sounds WHERE SiteID=$SiteID AND Sounds.SoundStatus!='9' $qf_check", $connection);
				if ($no_sounds>0) {

					$SiteName=filter_var($SiteName, FILTER_SANITIZE_STRING);

					if ($no_sounds==1) {
						$no_sounds_f = "One sound";
						}
					else {
						$no_sounds_f = "$no_sounds sounds";
						}

					#Set each point
					#Each point will help determine the map's extent, from http://econym.org.uk/gmap/basic14.htm
					$first_date=query_one("SELECT DATE_FORMAT(Date,'%d-%b-%Y') AS first_date FROM Sounds 
						WHERE SiteID=$SiteID AND Sounds.SoundStatus!='9' $qf_check 
						ORDER BY Date ASC LIMIT 1", $connection);
					$last_date=query_one("SELECT DATE_FORMAT(Date,'%d-%b-%Y') AS last_date FROM Sounds 
						WHERE SiteID=$SiteID AND Sounds.SoundStatus!='9' $qf_check 
						ORDER BY Date DESC LIMIT 1", $connection);

					echo "['$SiteName', $SiteLat, $SiteLon, $SiteID, '$no_sounds_f at this site<br>Sounds available from $first_date to $last_date. $approx_size', 'browse_site.php?SiteID=$SiteID']";
					array_push($sites_bounds, "var p$i = new google.maps.LatLng($SiteLat, $SiteLon);\nmyBounds.extend(p$i);\n");
					
					$no_res++;
					if ($i == ($nrows - 1)){
						echo "\n";
						}
					else{
						echo ",\n";
						}
					}
				}
			else {

			$SiteName=filter_var($SiteName, FILTER_SANITIZE_STRING);

			if ($time_to_browse=="") {
				$no_sounds=query_one("SELECT COUNT(*) AS no_sounds FROM Sounds WHERE SiteID='$SiteID' AND Date='$date_to_browse' AND Sounds.SoundStatus!='9'", $connection);

				if ($no_sounds>0) {
					if ($no_sounds==1) {
						$no_sounds_f = "One sound";
						}
					else {
						$no_sounds_f = "$no_sounds sounds";
						}
					
					$query_by_dates = "SELECT DISTINCT DATE_FORMAT(Date,'%d-%b-%Y') AS Date_f, DATE_FORMAT(Time,'%h:%i %p') AS Time_f, SoundID, SoundName 
						FROM Sounds
						WHERE Date IS NOT NULL AND SiteID=$SiteID AND Date='$date_to_browse' 
						AND Sounds.SoundStatus!='9' $qf_check ORDER BY Time ASC";
					$result_by_dates=query_several($query_by_dates, $connection);
					$nrows_by_dates = mysqli_num_rows($result_by_dates);

					for ($dd=0;$dd<$nrows_by_dates;$dd++) {
						$row_by_dates = mysqli_fetch_array($result_by_dates);
						extract($row_by_dates);

						if (is_odd($dd)) {
							$thislist = $thislist . "<p><a href=\"db_filedetails.php?SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
						else {
							$thislist = $thislist . "<p style=\"background-color:#E0EEEE;\"><a href=\"db_filedetails.php?SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
						}
					
					
					echo "['$SiteName', $SiteLat, $SiteLon, $SiteID, '$no_sounds_f on $Date_f: $thislist', 'browse_site_date.php?SiteID=$SiteID&Date=$date_to_browse']\n";
					array_push($sites_bounds, "var p$i = new google.maps.LatLng($SiteLat, $SiteLon);\nmyBounds.extend(p$i);\n");

					$no_res++;

					$this_page_title="Browse Map for $Date_f";
					
					if ($i == ($nrows - 1)){
						echo "\n";
						}
					else{
						echo ",\n";
						}
					}
				}
			else {
				$no_sounds=query_one("SELECT COUNT(*) AS no_sounds FROM Sounds 
					WHERE SiteID=$SiteID AND Date='$date_to_browse' AND Time='$time_to_browse' 
					AND Sounds.SoundStatus!='9' $qf_check", $connection);
				if ($no_sounds>0) {
					if ($no_sounds==1) {
						$no_sounds_f = "One sound";
						}
					else {
						$no_sounds_f = "$no_sounds sounds";
						}
						
					$query_by_dates = "SELECT DISTINCT DATE_FORMAT(Date,'%d-%b-%Y') AS Date_f, DATE_FORMAT(Time,'%h:%i %p') AS Time_f, SoundID, SoundName 
						FROM Sounds WHERE Date IS NOT NULL 
						AND SiteID=$SiteID AND Date='$date_to_browse' and Time='$time_to_browse' 
						AND Sounds.SoundStatus!='9' $qf_check ORDER BY Time ASC";
					$result_by_dates=query_several($query_by_dates, $connection);
					$nrows_by_dates = mysqli_num_rows($result_by_dates);

					for ($dd=0;$dd<$nrows_by_dates;$dd++) {
						$row_by_dates = mysqli_fetch_array($result_by_dates);
						extract($row_by_dates);

						if (is_odd($dd)) {
							$thislist = $thislist . "<p><a href=\"db_filedetails.php?SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
						else {
							$thislist = $thislist . "<p style=\"background-color:#E0EEEE;\"><a href=\"db_filedetails.php?SoundID=$SoundID\">$SoundName</a><br> &nbsp;$Date_f - $Time_f";
							}
						}
						
					echo "['$SiteName', $SiteLat, $SiteLon, $SiteID, '$no_sounds_f on $Date_f $Time_f: $thislist', 'browse_site_date.php?SiteID=$SiteID&Date=$date_to_browse']\n";
					array_push($sites_bounds, "var p$i = new google.maps.LatLng($SiteLat, $SiteLon);\nmyBounds.extend(p$i);\n");
					
					$no_res++;
					$this_page_title="Browse Map for $Date_f $Time_f";
					if ($i == ($nrows - 1)){
						echo "\n";
						}
					else{
						echo ",\n";
						}
					}
				}
			}

			#use the data from the sites to create a pull-down
			if (isset($date_to_browse) && $date_to_browse!="") {
				if ($no_sounds>0) {
					array_push($sites_rows, "<option value=\"$SiteID\">$SiteName - $no_sounds sounds on $Date_f</option>\n");
					}
				}
			else {
				if ($no_sounds>0) {
					array_push($sites_rows, "<option value=\"$SiteID\">$SiteName - $no_sounds sounds between $first_date and $last_date</option>\n");
					}
				}
			}

			echo "];

			function setMarkers(map, markers) {
				for (var i = 0; i < markers.length; i++) {
				    var sites = markers[i];
				    var siteLatLng = new google.maps.LatLng(sites[1], sites[2]);
				    var marker = new google.maps.Marker({
					position: siteLatLng,
					map: map,
					title: sites[0],
					html: '<div style=\"width:220px\"><div class=\"highlight4 ui-corner-all\"><a href=\"' + sites[5] + '\" style=\"color: white;\">' + sites[0] + '</a></div>' + sites[4] + '</div>'
				 });
				    var contentString = \"Some content\";

				    google.maps.event.addListener(marker, \"click\", function () {
					infowindow.setContent(this.html);
					infowindow.open(map, this);
				    });
				}
			    }

			function initialize() {

				var centerMap = new google.maps.LatLng(0, 0);

				var myOptions = {
				    zoom: 4,
				    center: centerMap,
				    mapTypeId: google.maps.MapTypeId.ROADMAP
				}

				var map = new google.maps.Map(document.getElementById(\"map_canvas\"), myOptions);\n";


			#Check if any KML to use
			if ($usekml=="1"){
				for ($k=0;$k<$nokml;$k++) {
					$this_kmlID=filter_var($_GET["kml$k"], FILTER_SANITIZE_NUMBER_INT);
					$this_kmlurl=query_one("SELECT KmlURL FROM Kml WHERE KmlID='$this_kmlID'", $connection);
					#add selected kml layers
					echo "\nvar ctaLayer$k = new google.maps.KmlLayer('$this_kmlurl',{preserveViewport:true});
					        ctaLayer$k.setMap(map);\n";
					}
				}
			else {
				$result_kml=query_several("SELECT * FROM Kml WHERE KmlDefault='1'", $connection);
				$nrows_kml = mysqli_num_rows($result_kml);
				if ($nrows_kml > 0) {
					$kml_default=1;
					for ($k=0;$k<$nrows_kml;$k++) {
						$row_kml = mysqli_fetch_array($result_kml);
						extract($row_kml);
						echo "\nvar ctaLayer$k = new google.maps.KmlLayer('$this_kmlurl',{preserveViewport:true});
						        ctaLayer$k.setMap(map);\n";
						}
					}
				}

			echo "var myBounds = new google.maps.LatLngBounds(); 
			   
				setMarkers(map, sites);
				    infowindow = new google.maps.InfoWindow({
					content: \"loading...\"
				 	});\n";

			    for ($p=0;$p<(count($sites_bounds));$p++) {
					echo $sites_bounds[$p];
					}
			    
				echo "\nmap.fitBounds(myBounds);
				    }
				</script>\n";

	}
?>
