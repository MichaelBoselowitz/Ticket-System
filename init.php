<?php
//Connect to db
$connection = mysql_connect("localhost", "BoselowitzM", "egg/put") or die(mysql_error());
if(!$connection)
	die("Could not connect to db " . mysql_error());
mysql_select_db("BoselowitzM") or die("Error: Couldn't select db " . mysql_error());

//Drop tables if they exist
mysql_query("drop table Admins");
mysql_query("drop table Tickets");
mysql_query("drop table Assignments");

//Create tables
mysql_query("create table Admins(ID varchar(50) primary key not null, Password varchar(50) not null, Email varchar(50) not null) engine=MYISAM") or die("Error: couldn't create admins table " . mysql_error());
mysql_query("create table Tickets(ID int primary key not null auto_increment, Received datetime not null, Firstname varchar(50) not null, Lastname varchar(50) not null, Email varchar(50) not null, Subject varchar(50) not null, Status bool not null, Problem varchar(500) not null) engine=MYISAM") or die("Error: couldn't create tickets table " . mysql_error());
mysql_query("create table Assignments(AdminsID varchar(50) not null, TicketsID int not null, foreign key(AdminsID) references Admins(ID), foreign key(TicketsID) references Tickets(ID)) engine=MYISAM") or die("Error: couldn't create assignemnts table " . mysql_error());
mysql_query("alter table Tickets add FULLTEXT(Subject, Problem)") or die(mysql_error());		//Full text for comparing similar


//Admins
mysql_query("insert into Admins values('mjb162', '" . md5("password") . "', 'mjb162@pitt.edu')") or die("Error: couldn't insert into admin table " . mysql_error());
mysql_query("insert into Admins values('Uniqua', '" . md5("Uniqua") . "', 'mjb162+Uniqua@pitt.edu')") or die("Error: couldn't insert into admin table " . mysql_error());
mysql_query("insert into Admins values('Pablo', '" . md5("Pablo") . "', 'mjb162+Pablo@pitt.edu')") or die("Error: couldn't insert into admin table " . mysql_error());
mysql_query("insert into Admins values('Austin', '" . md5("Austin") . "', 'mjb162+Austin@pitt.edu')") or die("Error: couldn't insert into admin table " . mysql_error());
mysql_query("insert into Admins values('Tyrone', '" . md5("Tyron") . "', 'mjb162+Tyrone@pitt.edu')") or die("Error: couldn't insert into admin table " . mysql_error());

//Tickets
mysql_query("insert into Tickets values(NULL, 20110227090000.000000, 'Luke', 'Skywalker', 'mjb162+luke@pitt.edu', 'Robot malfunction', 1, 'Oh no!!')") or die("Error: couldn't insert into tickets table " . mysql_error());
mysql_query("insert into Tickets values(NULL, 20110227133000.000000, 'Leia', 'Organa', 'mjb162+leia@pitt.edu', 'Problem with computer', 1, 'Ahhhhhhh!')") or die("Error: couldn't insert into tickets table " . mysql_error());
mysql_query("insert into Tickets values(NULL, 20110228171500.000000, 'Han', 'Solo', 'mjb162+han@pitt.edu', 'Computer is frozen', 1, 'Help me now!')") or die("Error: couldn't insert into tickets table " . mysql_error());
mysql_query("insert into Tickets values(NULL, 20110228083000.000000, 'Darth', 'Vader', 'mjb162+darth@pitt.edu', 'Screen image on the dark side', 1, 'Cell phone broke!')") or die("Error: couldn't insert into tickets table " . mysql_error());
mysql_query("insert into Tickets values(NULL, 20110301110500.000000, 'Obi Wan', 'Kenobi', 'mjb162+obi@pitt.edu', 'Image has disappeared', 1, 'Look out your window! There is a car crash!')") or die("Error: couldn't insert into tickets table " . mysql_error());
mysql_query("insert into Tickets values(NULL, 20110301112500.000000, 'Boba', 'Fett', 'mjb162+boba@pitt.edu', 'Delivery problem', 1, 'Troubleshooting computers for idiots. RTFM')") or die("Error: couldn't insert into tickets table " . mysql_error());
mysql_query("insert into Tickets values(NULL, 20110301121500.000000, 'Darth', 'Vader', 'mjb162+darth@pitt.edu', 'Electrical problem', 1, 'There\'s a raptor!')") or die("Error: couldn't insert into tickets table " . mysql_error());
mysql_query("insert into Tickets values(NULL, 20110301125500.000000, 'Han', 'Solo', 'mjb162+han@pitt.edu', 'Hyperdrive malfunction', 1, 'My hobbie...')") or die("Error: couldn't insert into tickets table " . mysql_error());

//Assignments
mysql_query("insert into Assignments values('Uniqua', 1)") or die("Error: couldn't insert into assignment table " . mysql_error());
mysql_query("insert into Assignments values('Pablo', 2)") or die("Error: couldn't insert into assignment table " . mysql_error());
mysql_query("insert into Assignments values('Austin', 3)") or die("Error: couldn't insert into assignment table " . mysql_error());
mysql_query("insert into Assignments values('Tyrone', 4)") or die("Error: couldn't insert into assignment table " . mysql_error());
mysql_query("insert into Assignments values('Austin', 5)") or die("Error: couldn't insert into assignment table " . mysql_error());
mysql_query("insert into Assignments values('Uniqua', 6)") or die("Error: couldn't insert into assignment table " . mysql_error());

echo "Successfully created the database!\n";
?>