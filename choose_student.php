<?php
// Continue the session
session_start();

// Session variables
$location_id = $_SESSION["location_id"] = $_GET["lid"] ?? $_SESSION["location_id"] ?? NULL;
$teacher_id = $_SESSION["teacher_id"] = $_GET["tid"] ?? $_SESSION["teacher_id"] ?? NULL;
$original_class_id = $_SESSION["original_class_id"] = $_GET["ocid"] ?? $_SESSION["original_class_id"] ?? NULL;
$original_date = $_SESSION["original_date"] = $_GET["date"] ?? $_SESSION["original_date"] ?? NULL;

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose a Student</title>
</head>
<body>

<h1>Choose a student:</h1>
<ul>

<?php

if ($students = get_students_for_location($location_id)) {
	foreach ($students as $student)
  {
    echo "<li><a href=\"choose_absence.php?sid=" . htmlspecialchars($student['person_id'], ENT_QUOTES, 'UTF-8') . "\">" .
		htmlspecialchars($student['family_name_r'], ENT_QUOTES, 'UTF-8') . ", " . htmlspecialchars($student['given_name_r'], ENT_QUOTES, 'UTF-8') . " (" .
		htmlspecialchars($student['family_name_k'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($student['given_name_k'], ENT_QUOTES, 'UTF-8') . ")</a></li>";
  }
}
else {
	echo "<p>No students found.</p>";
}
?>

</ul>
</body>
</html>
