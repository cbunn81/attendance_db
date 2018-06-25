<?php
// Start the session
session_start();
// Unset all of the session variables.
$_SESSION = array();

require_once('../../config/db.inc.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Attendance System</title>
</head>
<body>
<h1>Choose a task:</h1>
<ul>

<li><a href="choose_teacher.php">Enter Attendance Data</a></li>
<li><a href="choose_location.php?is_makeup=true">Enter Makeup Information</a></li>
<li><a href="choose_teacher.php?is_test=true">Enter Test Results</a></li>
<li><a href="list_students.php">Print an Attendance Report</a></li>

</ul>
</body>
</html>
