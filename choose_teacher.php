<?php
// Continue the session
session_start();
$_SESSION["is_test"] = $_GET["is_test"] ?? $_SESSION["is_test"] ?? FALSE;
$endclass2018 = $_GET["endclass2018"] ?? FALSE;

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose a Teacher</title>
</head>
<body>
<h1>Choose a teacher:</h1>
<ul>

<?php

$teachers = get_all_teachers();
if(empty($endclass2018)) {
	$url = "choose_class.php";
}
else {
	$url = "end_class-list_classes.php";
}
foreach ($teachers as $teacher)
{
    echo "<li><a href=\"$url?tid=" . htmlspecialchars($teacher['person_id'], ENT_QUOTES, 'UTF-8') . "\">" .
    htmlspecialchars($teacher['given_name_r'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($teacher['family_name_r'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
}
?>

</ul>
</body>
</html>
