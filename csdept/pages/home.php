<!DOCTYPE html>
<html>
<head>

	<?php 

		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
		header('Cache-Control: no-store, no-cache, must-revalidate'); 
		header('Cache-Control: post-check=0, pre-check=0', FALSE); 
		header('Pragma: no-cache');

	?>
	
	<meta charset="utf-8">
	<meta http-equiv="Expires" content="Mon, 26 Jul 1997 05:00:00 GMT">
	<meta http-equiv="Pragma" content="no-cache">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" type="text/css">
	<link rel="stylesheet" href="../style.css?Thursday 24th of April 2008 04:45:21 PM">
	<link rel="icon" type="image" href="../images/logo.png">
	<title>CS Department | Home</title>

</head>
<body>

	<div class="navigation_bar_div row">

		<div class="header_and_logo col-sm-6">
			<img src="../images/logo.png" style="width: 120px; height: 100px;">
			<p class="heading_paragraph">Department of Computer Science</p>
		</div>


		<ul class="ul_div row col-sm-6">
			<li class="navitem btn"><a href="">Alumni</a></li>
			<li class="navitem btn"><a href="">Staff</a></li>
			<li class="navitem btn"><a href="">Clubs</a></li>
			<li class="navitem btn"><a href="">Programmes Offered</a></li>
			<li class="navitem btn active"><a href="">Home</a></li>
		</ul>
	</div>

	<main class="row">

		<div class="col-sm-12 cover">
			<img src="../images/deptcover.png" class="img-responsive">
		</div>



		<div class="about_us col-sm-12">

			<h3>About Us</h3>

			<p class="col-sm-12">Being a student in the Department of Computer Sciences can be a challenging and rewarding experience. The Department has evolved and changed over the years with more students enrolling at the undergraduate and postgraduate levels. Programmes continue to evolve to meet the needs of students. You are added to thousands of brilliant persons who are and have been student members of the Department over the decades.</p>

			<div class="about_us_link">
				<a href="aboutus.html" class="about_us_button">More About Us</a>
			</div>

		</div>

		<div class="about_us col-sm-12">

			<h3>Our activities</h3>

			<p class="col-sm-12">Being a student in the Department of Computer Sciences can be a challenging and rewarding experience. The Faculty has evolved and changed over the years with more students enrolling at the undergraduate and postgraduate levels. Programmes continue to evolve to meet the needs of students. You are added to thousands of brilliant persons who are and have been student members of the Faculty over the decades.</p>

			<div class="about_us_link">
				<a href="clubs.php" class="about_us_button">View our clubs</a>
			</div>
			
		</div>

	</main>

	<?php 

		include '../pages/adddata.inc.php';

		include '../includes/displaydata.inc.php';

	 ?>


</body>
</html>