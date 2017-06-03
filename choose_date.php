<?php
// Continue the session
session_start();

// Set session variables
$_SESSION["is_makeup"] = $_GET["is_makeup"] ?? $_SESSION["is_makeup"] ?? FALSE;
$_SESSION["teacher_id"] = $_GET["tid"] ?? $_SESSION["teacher_id"] ?? NULL;
$_SESSION["student_id"] = $_GET["sid"] ?? $_SESSION["student_id"] ?? NULL;

?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose a Date</title>
	<link rel="stylesheet" type="text/css" media="screen" href="css/calendar.css">
</head>
<body>
	<?php
	// Debugging information
	//echo (empty($_SESSION["is_makeup"]) ? "<p>Not makeup</p>" : "<p>Makeup</p>");
	//echo (empty($_SESSION["teacher_id"]) ? "<p>No teacher set</p>" : "<p>Teacher set</p>");
	?>
<h1>Choose a date:</h1>
<ul>

<?php
  include("includes/calendar.php");

  $calendar = new Calendar();
  echo $calendar->show();
?>

</ul>
</body>
</html>
