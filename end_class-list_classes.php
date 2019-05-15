<?php
// Continue the session
session_start();

$teacher_id = $_SESSION["teacher_id"] = $_GET["tid"] ?? NULL;
$date = date("Y-m-d");

require_once('includes/model.php');
$teacher_name = get_person_name($teacher_id);
?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose Classes to End</title>
	<link rel="stylesheet" type="text/css" media="screen" href="css/main.css">
</head>
<body>
<h1>Choose the Classes For <?= $teacher_name ?> You Want to End</h1>

<form class="endclasses">

<?php

$days_of_week = get_days_of_week();
foreach ($days_of_week as $day_of_week) {
	echo "<fieldset>\r\n";
  echo "<legend>" . htmlspecialchars($day_of_week['dow_name'], ENT_QUOTES, 'UTF-8') . "</legend>\r\n";
	if($classes = get_classes_for_teacher($teacher_id,$day_of_week['dow_name'],$date)) {
		echo "<ol>\r\n";
		foreach ($classes as $class) {
			if (($class['dow_name'] != "Flex") || ($day_of_week['dow_name'] == "Flex")) {
				$students = get_students_for_class($class['class_id'], $teacher_id, $date);
				echo "<li><input type=\"checkbox\" id=\"class-" . htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . "\"
																			 name=\"" . htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . "\" />";
				echo "<label for==\"class-" . htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . "\">" .
										 htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . " - " .
										 htmlspecialchars($class['class_time'], ENT_QUOTES, 'UTF-8') . " - " .
										 htmlspecialchars($class['level_name'], ENT_QUOTES, 'UTF-8') . " - " .
										 "Start Date (" . htmlspecialchars($class['start_date'], ENT_QUOTES, 'UTF-8') . ")</label>";
				echo "<span class=\"hint\">(Students: ";
				$student_names = array();
	 			foreach ($students as $student) {
	 				$student_names[] = $student['student_name'];
	 			}
				echo htmlspecialchars(implode(", ", $student_names), ENT_QUOTES, 'UTF-8');
	 			echo ")</span>\r\n";
				echo "</li>\r\n";
			}
		}
		echo "</ol>\r\n";
	}
	else {
		echo "<p>No classes on this day.</p>";
	}
	echo "</fieldset>";
}
?>

</form>

</body>
</html>
