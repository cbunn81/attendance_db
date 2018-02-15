<?php
// Continue the session
session_start();

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
foreach ($teachers as $teacher)
{
    echo "<li><a href=\"choose_date.php?tid=" . htmlspecialchars($teacher['person_id'], ENT_QUOTES, 'UTF-8') . "\">" .
    htmlspecialchars($teacher['given_name_r'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($teacher['family_name_r'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
}
?>

</ul>
</body>
</html>
