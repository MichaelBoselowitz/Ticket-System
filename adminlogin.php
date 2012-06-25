<?php
session_start();	//Start session
if(isset($_POST["login"]))	//Received login
{
	if(!strcmp($_POST["username"], "") || !strcmp($_POST["password"], ""))	//User or password not provided
	{
		showLogin("Error: Please enter all details");
		die();
	}
	else	//Valid username and password
	{
		//Connect to db
		$connection = mysql_connect("localhost", "BoselowitzM", "egg/put");
		if(!$connection)
			die("Could not connect to db " . mysql_error());
		mysql_select_db("BoselowitzM") or die("Error: Couldn't select db " . mysql_error());
		
		//Look for admin of the username provided
		$results = mysql_query("select * from Admins where Admins.ID = '" . addslashes(strip_tags($_POST["username"])) .
			"';");		//Select should only return one admin of the right ID		
		if(mysql_num_rows($results) == 0)	//If no rows were returned, username must not exist, show error and die
		{
			showLogin("Error: Not a valid username");
			mysql_close($connection);
			die();
		}
		$row = mysql_fetch_array($results);		//Fetch the one row
		if(!strcmp(stripslashes($row["Password"]), md5($_POST["password"])))	//Is the password right?
		{
			$_SESSION["active"] = true;	//Session is set
			$_SESSION["user"] = strip_tags($_POST["username"]);		//Set who the currently logged in user is
			mysql_close($connection);
			header("location: admin.php");		//Pass onto the next page
		}
		else	//Password was incorrect, show form again
		{
			showLogin("Error: Password incorrect");
			mysql_close($connection);
			die();
		}
	}
}
if(isset($_SESSION["active"]))		//If you come to the page with an active session move to main page
{
	if($_SESSION["active"])
		header("location: admin.php");
}
else	//Show login if you are not authenticated
{
	showLogin("");
	die();
}
function showLogin($error)		//Html for showing login and the error, if there is one
{?>
	<html>
	<head>
	<title>Ticket System</title>
	</head>
	<body>
	<a href = "index.html">Home</a>
	<br /><br />
	<?php if(strcmp($error, ""))
			echo $error;?>		
	<form action="adminlogin.php" method="POST">
	<table>
	<tr><td>Username:</td><td><input type="text" name="username" /></td></tr>
	<tr><td>Password:</td><td><input type="password" name="password" /></td></tr>
	<tr><td><input type="submit" name="login" value="Submit" /></td></tr>
	</table>
	</form>
	</body>
	</html>
<?php
}
?>