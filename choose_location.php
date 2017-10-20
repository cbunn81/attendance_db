<?php
// Continue the session
session_start();
$_SESSION["is_makeup"] = $_GET["is_makeup"] ?? $_SESSION["is_makeup"] ?? FALSE;

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose a Location</title>
</head>
<body>
<h1>Choose a location:</h1>
<ul>

<?php
$locations = get_locations();
foreach ($locations as $location)
{
    echo "<li><a href=\"choose_student.php?lid=" . htmlspecialchars($location['location_id'], ENT_QUOTES, 'UTF-8') . "\">" .
    htmlspecialchars($location['location_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
}
close_database_connection($link);
?>

</ul>
</body>
</html>
