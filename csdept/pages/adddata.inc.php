<?php

	include '../includes/dbh.inc.php';

	$sql = "INSERT INTO homepage (head, content) VALUES ('About Us', 'This is a test')";

	if(mysqli_query($conn, $sql))
	{
		echo ("Record successfully creted");
	}
	else
	{
		echo "Error Adding Content to Homepage table in DB";
	}

	mysqli_close($conn);

?>