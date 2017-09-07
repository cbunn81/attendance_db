<?php
// Continue the session
session_start();
$_SESSION["is_makeup"] = $_GET["is_makeup"] ?? $_SESSION["is_makeup"] ?? FALSE;

require_once('../../config/db.inc.php');
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
$stmt = $pdo->prepare("SELECT location_id, location_name FROM locations ORDER BY location_id");
$stmt->execute();
foreach ($stmt as $row)
{
    echo "<li><a href=\"choose_student.php?lid=" . htmlspecialchars($row['location_id'], ENT_QUOTES, 'UTF-8') . "\">" .
    htmlspecialchars($row['location_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
}
?>

</ul>
</body>
</html>
