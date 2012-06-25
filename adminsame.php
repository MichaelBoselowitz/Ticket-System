<?php
//Start session and make sure user can access page
session_start();
if(!isset($_SESSION["active"]) || $_SESSION["active"] != true)
{
	header("location: adminlogin.php");
	session_destroy();
}
if(!isset($_SESSION["ticketedit"]))		//If no selection was made go back to admin home page
{
	header("Location: admin.php");
}
function show_similar()		//this function will show posts from the same username
{
	//Connect to db
	$connection = mysql_connect("localhost", "BoselowitzM", "egg/put");
		if(!$connection)
			die("Could not connect to db " . mysql_error());
	mysql_select_db("BoselowitzM") or die("Error: Couldn't select db " . mysql_error());
	
	//We received a form to process
	if(isset($_POST["submit"]))
	{
		//This block will make sure the bits are set properly when a selection is made
		if(!strcmp($_POST["submit"], "View Open Tickets") || !strcmp($_POST["submit"], "View All Tickets"))
			$_SESSION["all"] = !$_SESSION["all"];
		else if(!strcmp($_POST["submit"], "Sort"))
			$_SESSION["sort"] = $_POST["sort"];
		else if(!strcmp($_POST["submit"], "View Selected Ticket"))		//Set session to right ticket to look at
		{
			$_SESSION["ticketedit"] = $_POST["choice"];
			header("Location: adminedit.php");
		}
		else if(!strcmp($_POST["submit"], "View Other Tickets") || !strcmp($_POST["submit"], "View My Tickets"))
			$_SESSION["my"] = !$_SESSION["my"];
		else if(!strcmp($_POST["submit"], "Log Out"))	//Destroy session and kick to home page
		{
			session_destroy();
			header("Location: index.html");
		}
		else if(!strcmp($_POST["submit"], "View Assigned Tickets") || !strcmp($_POST["submit"], "View Unassigned Tickets") )
			$_SESSION["unassigned"] = !$_SESSION["unassigned"];
	}
	else		//No form, must be first time seeing page, set to defaults
	{
		$_SESSION["all"] = false;
		$_SESSION["my"] = false;
		$_SESSION["unassigned"] = false; 
		$_SESSION["sort"] = "";
	}
	
	//Get the one entry in question
	$result = mysql_query("select * from Tickets where Tickets.ID = '" . $_SESSION["ticketedit"] . "'") or die(mysql_error);
	$row = mysql_fetch_array($result);

	//This breaks down the SQL queries for the bits set, basically the same as admin.php, except with the where clause always comparing lastname and firstname of the submitter
	if(!$_SESSION["all"] && !$_SESSION["my"] && !$_SESSION["unassigned"])		//Default home view 000
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Lastname = '" . addslashes(strip_tags($row["Lastname"])) . "' and Tickets.Firstname = '" . addslashes(strip_tags($row["Firstname"])) . "' and Tickets.Status = 1";
	else if(!$_SESSION["all"] && !$_SESSION["my"] && $_SESSION["unassigned"])	//Unassigned 001
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Lastname = '" . addslashes(strip_tags($row["Lastname"])) . "' and Tickets.Firstname = '" . addslashes(strip_tags($row["Firstname"])) . "' and Tickets.Status = 1 and Admins.ID is NULL";
	else if(!$_SESSION["all"] && $_SESSION["my"] && !$_SESSION["unassigned"])	//Mine 010
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Lastname = '" . addslashes(strip_tags($row["Lastname"])) . "' and Tickets.Firstname = '" . addslashes(strip_tags($row["Firstname"])) . "' and Tickets.Status = 1 and Admins.ID = '" . $_SESSION["user"] . "'";
	else if(!$_SESSION["all"] && $_SESSION["my"] && $_SESSION["unassigned"])	//Will return no results though...in for completeness 011
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Lastname = '" . addslashes(strip_tags($row["Lastname"])) . "' and Tickets.Firstname = '" . addslashes(strip_tags($row["Firstname"])) . "' and Tickets.Status = 1 and Admins.ID is NULL and Admins.ID = '" . $_SESSION["user"] . "'";
	else if($_SESSION["all"] && !$_SESSION["my"] && !$_SESSION["unassigned"])	//All 100
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Lastname = '" . addslashes(strip_tags($row["Lastname"])) . "' and Tickets.Firstname = '" . addslashes(strip_tags($row["Firstname"])) . "'";
	else if($_SESSION["all"] && !$_SESSION["my"] && $_SESSION["unassigned"])	//All and unassigned 101
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Lastname = '" . addslashes(strip_tags($row["Lastname"])) . "' and Tickets.Firstname = '" . addslashes(strip_tags($row["Firstname"])) . "' and Admins.ID is NULL";
	else if($_SESSION["all"] && $_SESSION["my"] && !$_SESSION["unassigned"])	//All and mine 110
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Lastname = '" . addslashes(strip_tags($row["Lastname"])) . "' and Tickets.Firstname = '" . addslashes(strip_tags($row["Firstname"])) . "' and Admins.ID = '" . $_SESSION["user"] . "'";
	else if($_SESSION["all"] && $_SESSION["my"] && $_SESSION["unassigned"])
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem  from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Lastname = '" . addslashes(strip_tags($row["Lastname"])) . "' and Tickets.Firstname = '" . addslashes(strip_tags($row["Firstname"])) . "' and Admins.ID is NULL and Admins.ID = '" . $_SESSION["user"] . "'";
	
	//Add the sort if there is any
	if(!strcmp($_SESSION["sort"], "date"))
		$query = $query . " order by Tickets.Received";
	else if(!strcmp($_SESSION["sort"], "name"))
		$query = $query . " order by Tickets.Lastname, Tickets.Firstname";
	else if(!strcmp($_SESSION["sort"], "email"))
		$query = $query . " order by Tickets.Email";
	else if(!strcmp($_SESSION["sort"], "subject"))
		$query = $query . " order by Tickets.Subject";
		
	//Finally do the query
	$results = mysql_query($query) or die(mysql_error());
	$num_rows = mysql_num_rows($results);
	
	//Show the form, way too much html
	echo "<form action='adminsame.php' method='POST'><table cellpadding=5>";
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
	
	//The ifs are for showing the correct buttons at the correct time
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
//Show the html!!
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