<?php
//Start session
session_start();
if(!isset($_SESSION["active"]) || $_SESSION["active"] != true)	//Kick if not suppose to be here
{
	header("location: adminlogin.php");
	session_destroy();
}
function show_tickets($all, $my, $unassigned, $sort)	//this function takes 3 bools, all, my, and unassigned and then sorts it
{
	//Connect to db
	$connection = mysql_connect("localhost", "BoselowitzM", "egg/put");
		if(!$connection)
			die("Could not connect to db " . mysql_error());
		mysql_select_db("BoselowitzM") or die("Error: Couldn't select db " . mysql_error());
		
	//If we received a form process it
	if(isset($_POST["submit"]))
	{
		//What these series of ifs do is flip the bools if they were triggered or set the sort, plus any other buttons
		if(!strcmp($_POST["submit"], "View Open Tickets") || !strcmp($_POST["submit"], "View All Tickets"))		//If view all/open ticket button is selected
			$_SESSION["all"] = !$_SESSION["all"];
		else if(!strcmp($_POST["submit"], "Sort"))		//Change the sort
			$_SESSION["sort"] = $_POST["sort"];
		else if(!strcmp($_POST["submit"], "View Selected Ticket"))		//View the selected ticket
		{
			$_SESSION["ticketedit"] = $_POST["choice"];		//Store the ticket to look at in a session
			header("Location: adminedit.php");				//Kick over to the ticket viewer
		}
		else if(!strcmp($_POST["submit"], "View Other Tickets") || !strcmp($_POST["submit"], "View My Tickets"))	//View other/my ticket is selected
			$_SESSION["my"] = !$_SESSION["my"];
		else if(!strcmp($_POST["submit"], "Log Out"))	//Log out by destroying session and going to home page
		{
			session_destroy();
			header("Location: index.html");
		}
		else if(!strcmp($_POST["submit"], "View Assigned Tickets") || !strcmp($_POST["submit"], "View Unassigned Tickets") )		//View unassigned/assigned tickets
			$_SESSION["unassigned"] = !$_SESSION["unassigned"];
	}
	else	//First time visiting set the inital views and sort to default
	{
		$_SESSION["all"] = false;
		$_SESSION["my"] = false;
		$_SESSION["unassigned"] = false; 
		$_SESSION["sort"] = "";
	}
	
	//This block is the corresponding SQL statement to the pattern of bits set
	if(!$_SESSION["all"] && !$_SESSION["my"] && !$_SESSION["unassigned"])		//Default home view 000
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Status = 1";
	else if(!$_SESSION["all"] && !$_SESSION["my"] && $_SESSION["unassigned"])	//Unassigned 001
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Status = 1 and Admins.ID is NULL";
	else if(!$_SESSION["all"] && $_SESSION["my"] && !$_SESSION["unassigned"])	//Mine 010
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Status = 1 and Admins.ID = '" . $_SESSION["user"] . "'";
	else if(!$_SESSION["all"] && $_SESSION["my"] && $_SESSION["unassigned"])	//Will return no results though...in for completeness 011
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.Status = 1 and Admins.ID is NULL and Admins.ID = '" . $_SESSION["user"] . "'";
	else if($_SESSION["all"] && !$_SESSION["my"] && !$_SESSION["unassigned"])	//All 100
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID";
	else if($_SESSION["all"] && !$_SESSION["my"] && $_SESSION["unassigned"])	//All and unassigned 101
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Admins.ID is NULL";
	else if($_SESSION["all"] && $_SESSION["my"] && !$_SESSION["unassigned"])	//All and mine 110
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Admins.ID = '" . $_SESSION["user"] . "'";
	else if($_SESSION["all"] && $_SESSION["my"] && $_SESSION["unassigned"])		//Also will return no results...in for completeness 111
		$query = "select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Admins.ID is NULL and Admins.ID = '" . $_SESSION["user"] . "'";
	
	//This block tacks on the sort to the end of the SQL statement
	if(!strcmp($_SESSION["sort"], "date"))
		$query = $query . " order by Tickets.Received";
	else if(!strcmp($_SESSION["sort"], "name"))
		$query = $query . " order by Tickets.Lastname, Tickets.Firstname";
	else if(!strcmp($_SESSION["sort"], "email"))
		$query = $query . " order by Tickets.Email";
	else if(!strcmp($_SESSION["sort"], "subject"))
		$query = $query . " order by Tickets.Subject";
	
	//Execute query on db
	$results = mysql_query($query);
	$num_rows = mysql_num_rows($results);
	
	//Show results!
	echo "<form action='admin.php' method='POST'><table cellpadding=5>";
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
	
	//This is the table for the buttons, the ifs are for the toggling of the button names
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
//Show the html!
?>
<html>
<head>
<title>Ticket System</ticket>
</head>
<body>
<div align="center"><a href = "index.html">Home</a> . <a href = "admin.php">Admin Home</a></div><br /><br />
<div align="center"><?php show_tickets($_SESSION["all"], $_SESSION["my"], $_SESSION["unassigned"], $_SESSION["sort"])?></div>
</body>
</html>