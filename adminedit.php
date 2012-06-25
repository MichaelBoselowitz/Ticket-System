<?php
//Start session and kick out if no credentials are present
session_start();
if(!isset($_SESSION["active"]) || $_SESSION["active"] != true)
{
	header("Location: adminlogin.php");
	session_destroy();
}
if(!isset($_SESSION["ticketedit"]))		//This means no ticket was selected, go back to admin main page
{
	header("Location: admin.php");
}

//Connect to db
$connection = mysql_connect("localhost", "BoselowitzM", "egg/put");
if(!$connection)
	die("Could not connect to db " . mysql_error());
mysql_select_db("BoselowitzM") or die("Error: Couldn't select db " . mysql_error());

//fetch the ticket we are suppose to be looking at
$result = mysql_query("select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.ID = '" . $_SESSION["ticketedit"] . "'");
$row = mysql_fetch_array($result);

//If we received a form, handle it!
if(isset($_POST["submit"]))
{
	if(!strcmp($_POST["submit"], "Toggle Status"))		//Toggle the status
	{
		if($row["Status"])		//Set to closed and mail to sender the result
		{
			mysql_query("update Tickets set Status = 0 where Tickets.ID = '" . addslashes(strip_tags($_SESSION["ticketedit"])) . "'");
			mail($row["Email"], "Your Ticket: \"" . $row["Subject"] . "\"", "Your ticket has been resolved, thank you for your patience!");
		}
		else		//Set to open
			mysql_query("update Tickets set Status = 1 where Tickets.ID = '" . addslashes(strip_tags($_SESSION["ticketedit"])) . "'");
	}
	else if(!strcmp($_POST["submit"], "Unassign"))	//Unassign yourself (delete from Assignments table)
		mysql_query("delete from Assignments where AdminsID = '" . addslashes(strip_tags($_SESSION["user"])) . "' and TicketsID = '" . addslashes(strip_tags($_SESSION["ticketedit"])) . "'");
	else if(!strcmp($_POST["submit"], "Assign"))	//Assign yourself to ticket (insert into Assignments table)
		mysql_query("insert into Assignments values('" . addslashes(strip_tags($_SESSION["user"])) . "', '" . addslashes(strip_tags($_SESSION["ticketedit"])) . "')");
	else if(!strcmp($_POST["submit"], "Email"))		//Show the email form and quit
	{
		show_html("show_email", "");
		die();
	}
	else if(!strcmp($_POST["submit"], "Delete"))	//Delete from db and jump back to admin main page
	{
		mysql_query("delete from Assignments where TicketsID = '" . addslashes(strip_tags($_SESSION["ticketedit"])) . "'");
		mysql_query("delete from Tickets where ID = '" . addslashes(strip_tags($_SESSION["ticketedit"])) . "'");
		header("Location: admin.php");
	}
	else if(!strcmp($_POST["submit"], "Same Submitter"))	//Go to the page that handles same submitter
		header("Location: adminsame.php");
	else if(!strcmp($_POST["submit"], "Find Similar"))		//Go to page that handles similar submitter
		header("Location: adminsimilar.php");
	else if(!strcmp($_POST["submit"], "Admin Home"))		//Link to admin home
		header("Location: admin.php");
}
if(isset($_POST["send"]))		//If we are on the email form we will receive this instead, just mail, if error show page again
{
	if(!mail($_POST["to"], $_POST["subject"], $_POST["message"], "From: " . $_POST["from"]))
		show_html("show_email", "Error: failed to send email");
}

show_html("show_ticket", "");		//Show the default form

function show_ticket($error)	//The default form, shows the one ticket and all the buttons for options
{	
	//Get the ticket to display
	$result = mysql_query("select Tickets.ID as TID, Tickets.Received, Tickets.Firstname, Tickets.Lastname, Tickets.Email, Tickets.Subject, Admins.ID as AID, Tickets.Status, Tickets.Problem from Tickets LEFT JOIN (Admins, Assignments) on Tickets.ID = Assignments.TicketsID and Assignments.AdminsID = Admins.ID where Tickets.ID = '" . $_SESSION["ticketedit"] . "'");
	$row = mysql_fetch_array($result);
	
	//Show error if there is one
	if(isset($error) && strcmp($error, ""))
		echo $error . "<br />";
		
	//Build form, oh my that is a lot of html crap!
	echo "<form action='adminedit.php' method='POST'>";
	echo "<table cellpadding=5>";
	echo "<tr><th><div align='center'>Ticket #</div></th>";
	echo "<th><div align='center'>Received</div></th>";
	echo "<th><div align='center'>Sender's Name</div></th>";
	echo "<th><div align='center'>Sender's Email</div></th>";
	echo "<th><div align='center'>Subject</div></th>";
	echo "<th><div align='center'>Tech</div></th>";
	echo "<th><div align='center'>Status</div></th>";
	
	echo "<tr><td><div align='center'>" . stripslashes($row["TID"]);
	echo "</div></td><td><div align='center'>" . stripslashes($row["Received"]);
	echo "</div></td><td><div align='center'>" . stripslashes($row["Firstname"]);
	echo " " . stripslashes($row["Lastname"]);
	echo "</div></td><td>" . stripslashes($row["Email"]);
	echo "</td><td><div align='center'>" . stripslashes($row["Subject"]);
	echo "</div></td><td><div align='center'>" . stripslashes($row["AID"]);
	if(stripslashes($row["Status"]) == 1)
		echo "</div></td><td><div align='center'>Open</div></td></tr>";
	else if(stripslashes($row["Status"]) == 0)
		echo "</div></td><td><div align='center'>Closed</div></td></tr>";	
	echo "<tr><th colspan=7><div align='center'>Problem</div></th></tr>";
	echo "<tr><td colspan=7><div align='center'>" . stripslashes($row["Problem"]) . "</div></td></tr></table>";
	
	echo "<table><tr><td><div align='center'><input type=submit name=submit value='Toggle Status' /></div></td>";
	if(!strcmp(stripslashes($row["AID"]), $_SESSION["user"]))
		echo "<td><div align='center'><input type=submit name=submit value='Unassign' /></div></td>";
	else if(is_null($row["AID"]))
		echo "<td><div align='center'><input type=submit name=submit value='Assign' /></div></td>";
	echo "<td><div align='center'><input type=submit name=submit value='Email' /></div></td>";
	echo "<td><div align='center'><input type=submit name=submit value='Delete' /></div></td></tr>";
	echo "<tr><td><div align='center'><input type=submit name=submit value='Same Submitter' /></div></td>";
	echo "<td><div align='center'><input type=submit name=submit value='Find Similar' /></div></td>";
	echo "<td><div align='center'><input type=submit name=submit value='Admin Home' /></div></td></tr></table>";
	echo "</form>";
}
function show_email($error)		//This will show the email form
{
	//Show error if there is one
	if(isset($error) && strcmp($error, ""))
		echo $error . "<br />";
		
	//Show the form with auto filled from and to boxes, decided to allow it to be editable.
	echo "<form method='POST' action='adminedit.php'><table cellpadding=5>";
	$result = mysql_query("select Tickets.Email from Tickets where Tickets.ID = '" . $_SESSION["ticketedit"] . "'");
	$row = mysql_fetch_array($result);
	echo "<tr><td><div align='center'>To:</div></td><td><div align='center'><input type='text' name='to' value='" . stripslashes($row["Email"]) . "' /></div></td></tr>";
	$result = mysql_query("select Email from Admins where ID = '" . $_SESSION["user"] . "'");
	$row = mysql_fetch_array($result);
	echo "<tr><td><div align='center'>From:</div></td><td><div align='center'><input type='text' name='from' value='" .stripslashes($row["Email"]) . "' /></div></td></tr>";
	echo "<tr><td><div align='center'>Subject:</div></td><td><div align='center'><input type='text' name='subject' /></div></td></tr>";
	echo "<tr><td colspan=2><div align='center'>Message:</div></td></tr>";
	echo "<tr><td colspan=2><div align='center'><textarea name='message' cols=40 rows=6></textarea></div></td></tr>";
	echo "<tr><td colspan=2><div align='center'><input type='submit' name='send' value='Send' /></div></td></tr></table></form>";
}
function show_html($object, $error)		//The basic html for all this.
{
?>
<html>
<head>
<title>Ticket System</ticket>
</head>
<body>
<div align="center"><a href = "index.html">Home</a> . <a href = "admin.php">Admin Home</a></div><br /><br />
<div align="center"><?php call_user_func($object, "") //Calls the proper object to be shown ?></div>	
</body>
</html>
<?php
}
?>