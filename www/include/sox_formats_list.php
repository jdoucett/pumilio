<?php

// Check SoX formats
$out_formats=exec('sox -h | grep "FILE FORMATS:"', $out, $retval);
$formats_available=explode(" ", $out_formats);
$count_formats_available=count($formats_available);

$sox_formats=array_slice($formats_available, 3);

?>
