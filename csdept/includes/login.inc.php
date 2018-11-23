<?php

	if (isset($_POST['login-submit'])) 
	{
		require 'dbh.inc.php';

		$uname = $_POST['name'];
		$password = $_POST['pwd'];

		if (empty($uname) || empty($password)) 
		{
			header("Location: ../index.php?error=emptyfields&username=".$uname);
			exit();
		}

		else
		{

			$sql = "SELECT * FROM users WHERE username=? OR email=?;";
			$stmt = mysqli_stmt_init($conn);

			if(!mysqli_stmt_prepare($stmt, $sql))
			{
				header("Location: ../index.php?sqlerror");
				exit();
			}
			else
			{
				mysqli_stmt_bind_param($stmt, "ss", $uname, $uname);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

					if ($row = mysqli_fetch_assoc($result)) 
					{
						$pwdCheck = password_verify($password, $row['password']);
							if ($pwdCheck == false) 
							{
								header("Location: ../index.php?wrongpassword");
								exit();
							}
							else if ($pwdCheck == true) 
							{
								session_start();
								$_SESSION['userID'] = $row['ID'];
								$_SESSION['userUid'] = $row['username'];

								header("Location: ../pages/home.php");
								exit();
							}
							else 
							{
								header("Location: ../index.php?wrongpassword");
								exit();
							}
					}
					else  
					{
						header("Location: ../index.php?usernotfound");
						exit();
					}
			}
		}


	}

	else
	{
		header("Location: ../index.php");
		exit();
	}