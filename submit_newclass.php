<?php
// Start the session
session_start();

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Confirmation of New Class</title>
</head>
<body>

<?php

// The information has not been confirmed yet
if(empty($_SESSION["confirm"])) {
	// Set a session variable to skip this part on confirmation
	$_SESSION["confirm"] = TRUE;

	// Sanitize POST variables
	$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

	// Set local and session variables from the form data
	$location_id = $_SESSION["location_id"] = $_POST['location'];
	$dow_id = $_SESSION["dow_id"] = $_POST['dow'];
	$ctype_id = $_SESSION["ctype_id"] = $_POST['ctype'];
	$level_id = $_SESSION["level_id"] = $_POST['level'];
	$class_time = $_SESSION["class_time"] = $_POST['class_time'];
	$start_date = $_SESSION["start_date"] = $_POST['start_date'];
	$end_date = $_SESSION["end_date"] = $_POST['end_date'] ?: "infinity"; // "infinity" means no end date
	$teacher_id = $_SESSION["teacher_id"] = $_POST['teacher'];
	$student_ids = $_SESSION["student_ids"] = $_POST['students']; // should be an array of integers

	// get names instead of ID numbers for user confirmation
	$location_name = get_location_by_id($location_id);
	$dow_name = get_dow_by_id($dow_id);
	$ctype_name = get_ctype_by_id($ctype_id);
	$level_name = get_level_by_id($level_id);
	$teacher_name = get_person_name($teacher_id);
	// Since students are in an array, we'll get them as we output

	echo "<h1>Please confirm the following information submitted for the new class</h1>";
	echo "<p>Location Name: ". htmlspecialchars($location_name, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>DOW: ". htmlspecialchars($dow_name, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Class Type: ". htmlspecialchars($ctype_name, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Level: ". htmlspecialchars($level_name, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Class Time: ". htmlspecialchars($class_time, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Start Date: ". htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>End Date: ". htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Teacher Name: ". htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Student Names: ";
	echo "<ul>";
	foreach($student_ids as $student_id) {
		$student_name = get_person_name($student_id);
		echo "<li>" . htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ."</li>";
	}
	echo "</ul>";
	echo "</p>";

	echo "<p>If the above information is correct, please choose \"confirm\".</p>";
	echo "<form action=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\">";
	echo "  <button type=\"submit\">Confirm</button>";
	echo "</form>";
}

// The information has been confirmed
else {
	$location_id = $_SESSION["location_id"];
	$dow_id = $_SESSION["dow_id"];
	$ctype_id = $_SESSION["ctype_id"];
	$level_id = $_SESSION["level_id"];
	$class_time = $_SESSION["class_time"];
	$start_date = $_SESSION["start_date"];
	$end_date = $_SESSION["end_date"];
	$teacher_id = $_SESSION["teacher_id"];
	$student_ids = $_SESSION["student_ids"];
/*
	$location_name = get_location_by_id($location_id);
	$dow_name = get_dow_by_id($dow_id);
	$ctype_name = get_ctype_by_id($ctype_id);
	$level_name = get_level_by_id($level_id);
	$teacher_name = get_person_name($teacher_id);

	echo "<h1>Please confirm the information submitted for the new class</h1>";
	echo "<p>Location Name: ". htmlspecialchars($location_name, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>DOW: ". htmlspecialchars($dow_name, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Class Type: ". htmlspecialchars($ctype_name, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Level: ". htmlspecialchars($level_name, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Class Time: ". htmlspecialchars($class_time, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Start Date: ". htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>End Date: ". htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Teacher Name: ". htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8') ."</p>";
	echo "<p>Student Names: ";
	echo "<ul>";
	foreach($student_ids as $student_id) {
		$student_name = get_person_name($student_id);
		echo "<li>" . htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ."</li>";
	}
	echo "</ul>";
	echo "</p>";
*/
	echo "<h1>Information Confirmed</h1>";
	echo "<p>Attempting to insert the new class ...</p>";
	if($class_id = create_new_class($location_id,$dow_id,$ctype_id,$level_id,$class_time,$start_date,$end_date)) {
		echo "<p>New class inserted successfully!</p>";
		echo "<p>New Class ID: $class_id</p>";
	}
	else {
		echo "<p>An error has occurred while inserting the new class.</p>";
	}

	echo "<p>Attempting to insert the new entries in the roster ...</p>";
	if (create_roster_entry($teacher_id,$class_id,$start_date,$end_date)) {
		$teacher_name = get_person_name($teacher_id);
		echo "<p>\"". htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8') ."\" successfully inserted into the roster as teacher.</p>";
	}
	else {
		echo "<p>An error has occurred while inserting the teacher into the roster.</p>";
	}
	foreach($student_ids as $student_id) {
		if (create_roster_entry($student_id,$class_id,$start_date,$end_date)) {
			$student_name = get_person_name($student_id);
			echo "<p>\"". htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ."\" successfully inserted into the roster as a student.</p>";
		}
		else {
			echo "<p>An error has occurred while inserting a student into the roster.</p>";
		}
	}
}
?>

</body>
</html>
