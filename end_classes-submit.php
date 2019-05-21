<?php
// Start the session
session_start();

$teacher_id = $_SESSION["teacher_id"] ?? NULL;
$date = date("Y-m-d");

require_once('includes/model.php');
$teacher_name = get_person_name($teacher_id);
?>

<!DOCTYPE html>
<html>
<head>
	<title>Confirmation of Classes to End</title>
	<link rel="stylesheet" type="text/css" media="screen" href="css/main.css">
</head>
<body>

<?php


// The information has not been confirmed yet
if(empty($_POST["confirm"])) {
	$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	echo "<pre>";
	print_r($_POST);
	echo "</pre>";
	echo "<h1>Please confirm that the following classes are correct</h1>";
	echo "<table>\r\n<thead><tr><th>Class ID</th><th>Location</th><th>Day of the Week</th><th>Time</th><th>Level</th><th>Start Date</th><th>Teacher</th><th>Students</th></tr></thead>\r\n<tbody>\r\n";
	// make an array of class IDs to use for the DB update later
	$class_ids_to_end = array();
	foreach($_POST as $class_id => $ending) {
		$class_ids_to_end[] = $class_id;
		$class_info = get_class_info($class_id);
		$students = get_students_for_class($class_id, $teacher_id, $date);
		$student_names = array();
		foreach ($students as $student) {
			$student_names[] = $student['student_name'];
		}
		$student_list = implode(", ", $student_names);
		echo "<tr><td>" . htmlspecialchars($class_id, ENT_QUOTES, 'UTF-8') . "</td>\r\n";
		echo "<td>" . htmlspecialchars($class_info['location_name'], ENT_QUOTES, 'UTF-8') . "</td>\r\n";
		echo "<td>" . htmlspecialchars($class_info['dow_name'], ENT_QUOTES, 'UTF-8') . "</td>\r\n";
		echo "<td>" . htmlspecialchars(substr($class_info['class_time'],0,-3), ENT_QUOTES, 'UTF-8') . "</td>\r\n";
		echo "<td>" . htmlspecialchars($class_info['level_name'], ENT_QUOTES, 'UTF-8') . "</td>\r\n";
		echo "<td>" . htmlspecialchars($class_info['start_date'], ENT_QUOTES, 'UTF-8') . "</td>\r\n";
		echo "<td>" . htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8') . "</td>\r\n";
		echo "<td>" . htmlspecialchars($student_list, ENT_QUOTES, 'UTF-8') . "</td>\r\n";
	}
	echo "</tbody></table>";
	// Store the array of class IDs in a Session so we can use it after Confirmation
	$_SESSION["class_ids_to_end"] = $class_ids_to_end;
	/*
	// Set local and session variables from the form data
  $person_id = $_SESSION["person_id"] = $_POST['person_id'];
	$ptype_id = $_SESSION["ptype_id"] = $_POST['person_type'];
	$family_name_k = $_SESSION["family_name_k"] = $_POST['family_name_k'];
	$given_name_k = $_SESSION["given_name_k"] = $_POST['given_name_k'];
	$family_name_r = $_SESSION["family_name_r"] = $_POST['family_name_r'];
	$given_name_r = $_SESSION["given_name_r"] = $_POST['given_name_r'];
	$dob = $_SESSION["dob"] = $_POST['dob'] ?: NULL;
	$gender_id = $_SESSION["gender_id"] = $_POST['gender'];
	$start_date = $_SESSION["start_date"] = $_POST['start_date'];
	$end_date = $_SESSION["end_date"] = $_POST['end_date'] ?: "infinity"; // "infinity" means no end date
*/

	echo "<p class=\"afterform\"><strong>NOTE: </strong>When you confirm, all of the classes will be marked as ended on March 31, 2018.
				Also, any students or teachers currently enrolled in these classes will be marked as ended in the roster.
				This means you won't be able to access these past classes anymore with the online attendance system.
				So please be sure only to do this after your attendance is current for these classes.</p>";
	echo "<p class=\"afterform\">If the above information is correct, please choose \"confirm\".</p>";
	echo "<form action=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"confirm\" value=\"TRUE\" / />";
	echo "  <button type=\"submit\">Confirm</button>";
	echo "</form>";
}
// The information has been confirmed
else {
	// Get the session variable with an array of class IDs to end
	$class_ids_to_end = $_SESSION["class_ids_to_end"];
	$end_date = "2019-03-31";
	echo "<pre>";
	print_r($class_ids_to_end);
	echo "</pre>";

	echo "<h1>Information Confirmed</h1>";

	foreach ($class_ids_to_end as $class_id) {
		echo "<p>Attempting to end class $class_id ...</p>";
		if (end_class($class_id,$end_date)) {
			echo "<p>Class $class_id successfully ended on $end_date.</p>";
			echo "<p>Attempting to end roster entries for class $class_id ...</p>";
			$students = get_students_for_class($class_id, $teacher_id, $date);
			foreach ($students as $student) {
				echo "<p>Attempting to end roster entry for " . htmlspecialchars($student['student_name'], ENT_QUOTES, 'UTF-8') . " ...</p>";
				if (end_roster($student['student_id'],$class_id,$end_date)) {
					echo "<p>Roster entry for " . htmlspecialchars($student['student_name'], ENT_QUOTES, 'UTF-8') . " successfully ended on " . htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') . "</p>";
				}
				else {
					echo "<p><strong>ERROR:</strong> Roster entry for " . htmlspecialchars($student['student_name'], ENT_QUOTES, 'UTF-8') . " failed to end on " . htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') . "</p></p>";
				}
			}
			// End the roster entry for the teacher, too.
			echo "<p>Attempting to end roster entry for " . htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8') . " ...</p>";
			if (end_roster($teacher_id,$class_id,$end_date)) {
				echo "<p>Roster entry for " . htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8') . " successfully ended on " . htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') . "</p>";
			}
			else {
				echo "<p><strong>ERROR:</strong> Roster entry for " . htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8') . " failed to end on " . htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') . "</p></p>";
			}

		}
		else {
			echo "<p><strong>ERROR:</strong> Failure to end class $class_id on $end_date.</p>";
		}
	}


}

?>

</body>
</html>
