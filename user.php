<?php
//We received a form, oh my! What shall we do?!
if(isset($_POST["submit"]))
{	
	//Check to be sure there are no blank fields, and error if we do
	if(!strcmp($_POST["firstname"], "") || !strcmp($_POST["lastname"], "") || 
		!strcmp($_POST["email"], "") || !strcmp($_POST["subject"], "") || 
		!strcmp($_POST["problem"], ""))
	{
		showForm("Please enter all information");
		die();
	}
	else	//The form was good, well mostly....
	{
		//Connect to db
		$connection = mysql_connect("localhost", "BoselowitzM", "egg/put");
		if(!$connection)
			die("Could not connect to db " . mysql_error());
		mysql_select_db("BoselowitzM") or die("Error: Couldn't select db " . mysql_error());
		
		//Insert the ticket in the db
		mysql_query("insert into Tickets values(NULL, NOW(), '" . 
			addslashes(strip_tags($_POST["firstname"])) .
			"', '" . addslashes(strip_tags($_POST["lastname"])) . 
			"', '" . addslashes(strip_tags($_POST["email"])) . 
			"', '" . addslashes(strip_tags($_POST["subject"])) . 
			"', 1, '" . addslashes(strip_tags($_POST["problem"])) . "')") 
			or die("Error: Could not insert into table " . mysql_error());
			
		//Mail the submitter
		mail($_POST["email"], "Successful", "You were successfully added to the database, we will help you as soon as we can. \n\nThis is an automated message, please do not respond.");
		
		//Mail to all admins
		$results = mysql_query("select * from Admins");
		while($row = mysql_fetch_array($results))
			mail($row["Email"], "New Ticket!", "A new ticket was added!");
			
		//Show success on completion
		echo "Successfully Entered into Database!<br /><a href='index.html'>Home</a>";	
		mysql_close($connection);	
		die();
	}
}
else
{
	showForm("");
	die();
}
function showForm($error)
{
	?>
	<html>
	<head>
	<title>Ticket System</title>
	</head>
	<body>
	<a href="index.html">Home</a>
	<br /><br />
	<?php if(strcmp($error, ""))
			echo $error . "<br />"?>
	<form action="user.php" method="POST">
	<table>
	<tr><td>First Name:</td><td><input type="text" name="firstname" /></td></tr>
	<tr><td>Last Name:</td><td><input type="text" name="lastname" /></td></tr>
	<tr><td>Email:</td><td><input type="text" name="email" /></td></tr>
	<tr><td>Subject:</td><td><input type="text" name="subject" /></td></tr>
	<tr><td>Problem:</td></tr>
	</table>
	<table>
	<tr><td><textarea name="problem" cols=40 rows=6></textarea></td></tr>
	<tr><td><input type="submit" name="submit" value="Submit" /></td></tr>
	</table>
	</form>	
	</body>
	</html>
	<?php
}
?>