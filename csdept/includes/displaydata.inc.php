<?php

	require 'dbh.inc.php';

	$sql = "SELECT head FROM homepage";
	$result = mysqli_query($conn, $sql);

		if(mysqli_num_rows($result) > 0)
		{
			while($row = mysqli_fetch_assoc($result))
			{
				echo "Head is ".$row['head']."<br>";
			}

		}
		else
		{
			echo "0 results";
		}

	mysqli_close($conn);

?>