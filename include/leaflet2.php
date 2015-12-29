<?php

use \DByte\DB;
DB::$c = $pdo;


if ($no_results_map>0){


	echo " <script src=\"libs/leaflet/leaflet.js\"></script>\n
			<link rel=\"stylesheet\" href=\"libs/leaflet/MarkerCluster.css\" />
			<link rel=\"stylesheet\" href=\"libs/leaflet/MarkerCluster.Default.css\" />
			<script src=\"libs/leaflet/leaflet.markercluster-src.js\"></script>
			<script src=\"libs/leaflet/leaflet-providers.js\"></script>
			
			<script>

			var map = L.map('map').setView([0, 0], 8);\n";

	echo "L.tileLayer.provider('OpenStreetMap.HOT').addTo(map);";

	echo "var markers = new L.MarkerClusterGroup();\n";


	for ($i=0; $i < $no_results_map; $i++) {

		$SiteID = $results_map[$i]->SiteID;
		$SiteLat = $results_map[$i]->SiteLat;
		$SiteLon = $results_map[$i]->SiteLon;
		$SiteName = $results_map[$i]->SiteName;
		$res_site = DB::column('SELECT COUNT(*) FROM `Sounds` WHERE SiteID = ' . $SiteID);


			echo "var marker = L.marker(new L.LatLng($SiteLat, $SiteLon));
					var title = \"<a href='browse_site.php?SiteID=$SiteID'><strong>$SiteName</strong></a><br>$res_site sounds at this site\";\n";
	
			echo "marker.bindPopup(title);
			markers.addLayer(marker);\n";

			}


		
	echo "
		map.addLayer(markers);
		var popup = L.popup();
		L.control.scale().addTo(map);
		
	map.fitBounds([";
		for ($i=0; $i < $no_results_map; $i++) {

			$SiteLat = $results_map[$i]->SiteLat;
			$SiteLon = $results_map[$i]->SiteLon;
			

				echo "[$SiteLat, $SiteLon]";
				if ($i != ($no_results_map-1)){
					echo ",";
				}
			}

		echo "]);
 	map.setZoom(map.getZoom()-1);

	</script>
	";

}
?>