<?php
$query_tags = "SELECT TagID,Tag FROM Tags WHERE SoundID='$SoundID' ORDER BY Tag";
$result_tags=query_several($query_tags, $connection);
$nrows_tags = mysqli_num_rows($result_tags);

if ($nrows_tags>0){

	echo "<p><strong>Tags</strong>: ";
	for ($t=0;$t<$nrows_tags;$t++){
		$row_tags = mysqli_fetch_array($result_tags);
		extract($row_tags);

		if (isset($where_to)){
			echo "$Tag<a href=\"include/deletetag.php?TagID=$TagID&SoundID=$SoundID&goto=db\" title=\"Delete tag\"><img src=\"images/tag_blue_delete.png\"></a> ";
			}
		else {
			echo "$Tag<a href=\"include/deletetag.php?TagID=$TagID&SoundID=$SoundID&goto=db\" title=\"Delete tag\"><img src=\"images/tag_blue_delete.png\"></a> ";
			}

		}
	echo "<br>";
	}
?>
