<?php

/*
#Update the list of sites
#Redundant - for now, to be fixed in a future release
	$query_of_sites="SELECT DISTINCT Location, Latitude, Longitude FROM Sounds WHERE Latitude IS NOT NULL AND Longitude IS NOT NULL";
	$result_of_sites = mysqli_query($connection, $query_of_sites)
				or die (mysqli_error($connection));
	$nrows_of_sites = mysqli_num_rows($result_of_sites);
	for ($s=0;$s<$nrows_of_sites;$s++) {
		$row_of_sites = mysqli_fetch_array($result_of_sites);
		extract($row_of_sites);
		$is_site=query_one("SELECT COUNT(*) FROM Sites WHERE SiteLat LIKE '$Latitude' AND SiteLon LIKE '$Longitude'", $connection);
		if ($is_site==0) {
			$querys = "INSERT INTO Sites (SiteName,SiteLat,SiteLon) VALUES ('$Location', '$Latitude', '$Longitude')";
			$results = mysqli_query($connection, $querys)
				or die (mysqli_error($connection));
			}
		}

#Update SiteID
	$query_sites="SELECT * FROM Sites";
	$result_sites = mysqli_query($connection, $query_sites)
				or die (mysqli_error($connection));
	$nrows_sites = mysqli_num_rows($result_sites);
	if ($nrows_sites>0) {
		for ($s1=0;$s1<$nrows_sites;$s1++) {
			$row_sites = mysqli_fetch_array($result_sites);
			extract($row_sites);

			$query_sites1="UPDATE Sounds SET SiteID=$SiteID WHERE Latitude LIKE '$SiteLat' AND Longitude LIKE '$SiteLon'";
			$result_sites1 = mysqli_query($connection, $query_sites1)
				or die (mysqli_error($connection));
			}
		}
*/


#Check if the file size is in the database
	$query_size="SELECT * FROM Sounds WHERE FileSize='' OR FileSize IS NULL OR FileSize='0'";
	$result_size = mysqli_query($connection, $query_size)
		or die (mysqli_error($connection));
	$nrows_size = mysqli_num_rows($result_size);
	if ($nrows_size>0) {
		for ($s2=0;$s2<$nrows_size;$s2++) {
			$row_size = mysqli_fetch_array($result_size);
			extract($row_size);
			$file_filesize=filesize("../sounds/sounds/$ColID/$DirID/$OriginalFilename");
			$result_size = mysqli_query($connection, "UPDATE Sounds SET FileSize='$file_filesize' WHERE SoundID='$SoundID' LIMIT 1")
				or die (mysqli_error($connection));
			}
		}



#Check if the MD5 hash is in the database
	$query_md5="SELECT * FROM Sounds WHERE MD5_hash='' OR  MD5_hash IS NULL OR MD5_hash='0'";
	$result_md5 = mysqli_query($connection, $query_md5)
		or die (mysqli_error($connection));
	$nrows_md5 = mysqli_num_rows($result_md5);
	if ($nrows_md5>0) {
		for ($s3=0;$s3<$nrows_md5;$s3++) {
			$row_md5 = mysqli_fetch_array($result_md5);
			extract($row_md5);
			if (is_file("../sounds/sounds/$ColID/$DirID/$OriginalFilename")) {
				$file_md5hash=md5_file("../sounds/sounds/$ColID/$DirID/$OriginalFilename");
				$result_md5 = mysqli_query($connection, "UPDATE Sounds Set MD5_hash='$file_md5hash' WHERE SoundID='$SoundID'")
					or die (mysqli_error($connection));
				}
			}
		}
		

#Check if the sampling rate is in the database
	$query_samp_rate="SELECT SoundID, ColID, OriginalFilename FROM Sounds WHERE SamplingRate='0' OR SamplingRate IS NULL 
			OR Channels='0' OR Channels IS NULL OR Duration='0' OR Duration IS NULL OR
			SoundFormat IS NULL";
	$result_samp_rate = mysqli_query($connection, $query_samp_rate)
		or die (mysqli_error($connection));
	$nrows_samp_rate = mysqli_num_rows($result_samp_rate);
	if ($nrows_samp_rate>0) {
		for ($s4=0;$s4<$nrows_samp_rate;$s4++) {
			$row_samp = mysqli_fetch_array($result_samp_rate);
			extract($row_samp);
			if (is_file("../sounds/sounds/$ColID/$DirID/$OriginalFilename")) {
				exec('./soundcheck.py ../sounds/sounds/' . $ColID . '/' . $DirID . '/' . $OriginalFilename, $lastline, $retval);
				if ($retval==0) {
					$file_info=$lastline[0];
					$file_info = explode(",", $file_info);
					$sampling_rate=$file_info[0];
					$no_channels=$file_info[1];
					$file_format=$file_info[2];
					$file_duration=$file_info[3];

					$query_file1 = "UPDATE Sounds SET 
							SamplingRate='$sampling_rate', Channels='$no_channels', 
							Duration='$file_duration',SoundFormat='$file_format' 
							WHERE SoundID='$SoundID' LIMIT 1";
					$result_file1 = mysqli_query($connection, $query_file1)
						or die (mysqli_error($connection));
					unset($lastline);
					unset($query_file);
					unset($retval);
					unset($file_info);
					}
				}
			else {
				die("<div class=\"error\">Could not find file $ColID/$OriginalFilename");
				}
			}
		}


#Change engine to MyISAM to keep all the same
$to = 'MyISAM';
$from = 'INNODB';

$query="SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$database' AND ENGINE = 'InnoDB'";
$result = mysqli_query($connection, $query)
	or die (mysqli_error($connection));
$nrows = mysqli_num_rows($result);
if ($nrows>0) {
	for ($i=0;$i<$nrows;$i++) {
		$row = mysqli_fetch_array($result);
		extract($row);
		query_one("ALTER TABLE $TABLE_NAME ENGINE = MyISAM", $connection);
		}
	}
	
	

#Change collation
$query="SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$database' AND TABLE_COLLATION != 'utf8_unicode_ci'";
$result = mysqli_query($connection, $query)
	or die (mysqli_error($connection));
$nrows = mysqli_num_rows($result);
if ($nrows>0) {
	for ($i=0;$i<$nrows;$i++) {
		$row = mysqli_fetch_array($result);
		extract($row);
		query_one("ALTER TABLE $TABLE_NAME DEFAULT CHARSET=utf8 COLLATE='utf8_unicode_ci'", $connection);
		}
	}
	
	
	

#Optimize tables
$query_opt="OPTIMIZE TABLE CheckAuxfiles, Collections, Cookies, Kml, ProcessLog, PumilioLog, PumilioSettings, QualityFlags, Queue, QueueJobs, SampleMembers, Samples, Scripts, Sensors, Sites, SitesPhotos, Sounds, SoundsImages, SoundsMarks, Tags, Tokens, Users, WeatherData, WeatherSites";
$result_opt = mysqli_query($connection, $query_opt)
	or die (mysqli_error($connection));

?>
