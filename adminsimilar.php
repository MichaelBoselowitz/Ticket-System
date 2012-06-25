<?php
//Start the session and remove people not suppose to be here
session_start();
if(!isset($_SESSION["active"]) || $_SESSION["active"] != true)
{
	header("location: adminlogin.php");
	session_destroy();
}
if(!isset($_SESSION["ticketedit"]))		//No selection made, escape while you can!!
{
	header("Location: admin.php");
}
function show_similar()		//This function will show similar results compared to the problem and subject of the selected ticket
{
	//Connect to db
	$connection = mysql_connect("localhost", "BoselowitzM", "egg/put");
	if(!$connection)
		die("Could not connect to db " . mysql_error());
	mysql_select_db("BoselowitzM") or die("Error: Couldn't select db " . mysql_error());
	
	//We received a form, process it!
	if(isset($_POST["submit"]))
	{
		//Flip the bits the right way to make it all work, keep the variables up to date!!
		if(!strcmp($_POST["submit"], "View Open Tickets") || !strcmp($_POST["submit"], "View All Tickets"))
			$_SESSION["all"] = !$_SESSION["all"];
		else if(!strcmp($_POST["submit"], "Sort"))
			$_SESSION["sort"] = $_POST["sort"];
		else if(!strcmp($_POST["submit"], "View Selected Ticket"))		//Go to edit page if you select something
		{
			$_SESSION["ticketedit"] = $_POST["choice"];
			header("Location: adminedit.php");
		}
		else if(!strcmp($_POST["submit"], "View Other Tickets") || !strcmp($_POST["submit"], "View My Tickets"))
			$_SESSION["my"] = !$_SESSION["my"];
		else if(!strcmp($_POST["submit"], "Log Out"))		//Kill, kill the session!! 
		{
			session_destroy();
			header("Location: index.html");
		}
		else if(!strcmp($_POST["submit"], "View Assigned Tickets") || !strcmp($_POST["submit"], "View Unassigned Tickets") )
			$_SESSION["unassigned"] = !$_SESSION["unassigned"];
	}
	else		//First time viewing in session, n00bs, set defaults
	{
		$_SESSION["all"] = false;
		$_SESSION["my"] = false;
		$_SESSION["unassigned"] = false; 
		$_SESSION["sort"] = "";
	}
	
	//Fetch the row we are suppose to be looking at
	$result = mysql_query("select * from Tickets where Tickets.ID = '" . $_SESSION["ticketedit"] . "'") or die(mysql_error);
	$row = mysql_fetch_array($result);

	//This will choose the correct SQL statement to use, I used mysql's match ... against to do the comparison for me
	//It works out really well, though I found boolean mode, though not the best for large db's works the best for this small one
	//This would not be the best search if I was to actually do this project.
	//I am not sure if I did exactly what Ramirez wanted here, but I feel like it was the better choice, this will actually order
	//the list by relavance, very nice feature, but it will not always find something with the same words, it will drop some words
	//if they are used in more than 50% of the tuples.
	
	if(!$_SESSION["all"] && !$_SESSION["my"] && !$_SESSION["unassigned"])		//Default home view 000
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Match(Tickets.Subject, Tickets.Problem) Against('" . addslashes(strip_tags($row["Problem"])) . " " .addslashes(strip_tags($row["Subject"])) . "' in boolean mode) and Tickets.Status = 1";
	else if(!$_SESSION["all"] && !$_SESSION["my"] && $_SESSION["unassigned"])	//Unassigned 001
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Match(Tickets.Subject, Tickets.Problem) Against('" . addslashes(strip_tags($row["Problem"])) . " " . addslashes(strip_tags($row["Subject"])) . "' in boolean mode) and Tickets.Status = 1 and Admins.ID is NULL";
	else if(!$_SESSION["all"] && $_SESSION["my"] && !$_SESSION["unassigned"])	//Mine 010
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Match(Tickets.Subject, Tickets.Problem) Against('" . addslashes(strip_tags($row["Problem"])) . " " . addslashes(strip_tags($row["Subject"])) . "' in boolean mode) and Tickets.Status = 1 and Admins.ID = '" . $_SESSION["user"] . "'";
	else if(!$_SESSION["all"] && $_SESSION["my"] && $_SESSION["unassigned"])	//Will return no results though...in for completeness 011
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Match(Tickets.Subject, Tickets.Problem) Against('" . addslashes(strip_tags($row["Problem"])) . " " . addslashes(strip_tags($row["Subject"])) . "' in boolean mode) and Tickets.Status = 1 and Admins.ID is NULL and Admins.ID = '" . $_SESSION["user"] . "'";
	else if($_SESSION["all"] && !$_SESSION["my"] && !$_SESSION["unassigned"])	//All 100
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Match(Tickets.Subject, Tickets.Problem) Against('" . addslashes(strip_tags($row["Problem"])) . " " . addslashes(strip_tags($row["Subject"])) . "' in boolean mode)";
	else if($_SESSION["all"] && !$_SESSION["my"] && $_SESSION["unassigned"])	//All and unassigned 101
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Match(Tickets.Subject, Tickets.Problem) Against('" . addslashes(strip_tags($row["Problem"])) . " " . addslashes(strip_tags($row["Subject"])) . "' in boolean mode) and Admins.ID is NULL";
	else if($_SESSION["all"] && $_SESSION["my"] && !$_SESSION["unassigned"])	//All and mine 110
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Match(Tickets.Subject, Tickets.Problem) Against('" . addslashes(strip_tags($row["Problem"])) . " " . addslashes(strip_tags($row["Subject"])) . "' in boolean mode) and Admins.ID = '" . $_SESSION["user"] . "'";
	else if($_SESSION["all"] && $_SESSION["my"] && $_SESSION["unassigned"])
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Match(Tickets.Subject, Tickets.Problem) Against('" . addslashes(strip_tags($row["Problem"])) . " " . addslashes(strip_tags($row["Subject"])) . "' in boolean mode) and Admins.ID is NULL and Admins.ID = '" . $_SESSION["user"] . "'";
	
	//Add a sort if necessary, really the default sort is because because it show the relavance to the search
	if(!strcmp($_SESSION["sort"], "date"))
		$query = $query . " order by Tickets.Received";
	else if(!strcmp($_SESSION["sort"], "name"))
		$query = $query . " order by Tickets.Lastname, Tickets.Firstname";
	else if(!strcmp($_SESSION["sort"], "email"))
		$query = $query . " order by Tickets.Email";
	else if(!strcmp($_SESSION["sort"], "subject"))
		$query = $query . " order by Tickets.Subject";
	
	//Actually make the query
	$results = mysql_query($query) or die(mysql_error());
	$num_rows = mysql_num_rows($results);
	
	//Show the results! w00t! Also way too much html, I can't believe I wrote all of that...save me.
	echo "<form action='adminsimilar.php' method='POST'><table cellpadding=5>";
	echo "<tr><th><div align='center'>Ticket #</div></th><th><div align='center'>Received</div></th><th><div align='center'>Sender's Name</div></th><th><div align='center'>Sender's Email</div></th><th><div align='center'>Subject</div></th><th><div align='center'>Tech</div></th><th><div align='center'>Status</div></th><th><div align='center'>Select</div></th></tr>";
	for($i = 0; $i < $num_rows; $i++)
	{
		$row = mysql_fetch_array($results);
		echo "<tr><td><div align='center'>".stripslashes($row["TID"]).
				"</div></td><td><div align='center'>".stripslashes($row["Received"]).
				"</div></td><td><div align='center'>".stripslashes($row["Firstname"]).
				" ".stripslashes($row["Lastname"]).
				"</div></td><td>".stripslashes($row["Email"]).
				"</td><td><div align='center'>".stripslashes($row["Subject"]).
				"</div></td><td><div align='center'>".stripslashes($row["AID"]);
		if(stripslashes($row["Status"]) == 1)
			echo "</div></td><td><div align='center'>Open</div></td>";
		else
			echo "</div></td><td><div align='center'>Closed</div></td>";			
				"</td></tr>";
		echo "<td><div align='center'><input type='radio' name='choice' value='".stripslashes($row["TID"])."' /></div></td></tr>";
	}
	echo "<tr><th></th><th><div align='center'>Sort By <input type='radio' name='sort' value='date' /></div></th><th><div align='center'>Sort By <input type='radio' name='sort' value='name' /></div></th><th><div align='center'>Sort By <input type='radio' name='sort' value='email' /></div></th><th><div align='center'>Sort By <input type='radio' name='sort' value='subject' /></div></th></tr></table>";
	
	//This will flip the button's text as necessary
	if($_SESSION["all"]) { $allsubmit = "View Open Tickets"; } else { $allsubmit = "View All Tickets"; }
	echo "<br /><table><tr><td><div align='center'><input type='submit' name='submit' value='" . $allsubmit . "' /></div></td>";
	echo "<td><div align='center'><input type='submit' name='submit' value='Sort' /></div></td>";
	echo "<td><div align='center'><input type='submit' name='submit' value='View Selected Ticket' /></div></td></tr>";
	if($_SESSION["my"]) { $mysubmit = "View Other Tickets"; } else { $mysubmit = "View My Tickets"; }
	echo "<tr><td><div align='center'><input type='submit' name='submit' value='" . $mysubmit . "' /></div></td>";
	echo "<td><div align='center'><input type='submit' name='submit' value='Log Out' /></div></td>";
	if($_SESSION["unassigned"]) { $unassignedsubmit = "View Assigned Tickets"; } else { $unassignedsubmit = "View Unassigned Tickets"; }
	echo "<td><div align='center'><input type='submit' name='submit' value='" . $unassignedsubmit . "' /></div></td></tr></table></form>";
}
//The html
?>
<html>
<head>
<title>Ticket System</ticket>
</head>
<body>
<div align="center"><a href = "index.html">Home</a> . <a href = "admin.php">Admin Home</a></div><br /><br />
<div align="center"><?php show_similar($_SESSION["all"], $_SESSION["my"], $_SESSION["unassigned"], $_SESSION["sort"])?></div>
</body>
</html>