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
	<link rel="stylesheet" href="style.css?Thursday 24th of April 2008 04:45:21 PM">
	<link rel="icon" type="image" href="images/logo.png">
	<title>CS Department | Sign Up</title>

</head>
<body>

	<div class="prenav">
		<p>Back to Login Page</p>

		<a href="index.php">Login</a>
	</div>


	<div class="navigation_bar_div row">

		<div class="header_and_logo col-sm-6">
			<img src="images/logo.png" style="width: 120px; height: 100px;">
			<p class="heading_paragraph">Department of Computer Science</p>
		</div>


		<ul class="ul_div row col-sm-6">
			<li class="navitem btn disabled"><a href="">Alumni</a></li>
			<li class="navitem btn disabled"><a href="">Staff</a></li>
			<li class="navitem btn disabled"><a href="">Clubs</a></li>
			<li class="navitem btn disabled"><a href="">Programmes Offered</a></li>
			<li class="navitem btn disabled"><a href="">Home</a></li>
		</ul>
	</div>



	<form action="includes/signup.inc.php" method="post" class="wholeform">

		<div class="inputs fcenter">

				<img src="images/logo.png" style="width: 120px; height: 120px;" class="img-responsive fcenter"><br>

				<input type="username" name="name" placeholder="username" class="forminput fcenter"><br>

				<input type="email" name="mail" placeholder="enter email" class="forminput fcenter"><br>

				<input type="password" name="pwd" placeholder="password" class="forminput fcenter"><br>

				<input type="password" name="confpwd" placeholder=" re-enter password" class="forminput fcenter"><br>

				<button type="submit" name="signup-submit" class="btn fcenter login-submit">Sign Up!</button>

		</div>

	</form>

</body>
</html>