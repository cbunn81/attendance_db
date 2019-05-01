<?php
// Start the session
session_start();

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Confirmation of New Person</title>
	<link rel="stylesheet" type="text/css" media="screen" href="css/main.css">
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
	$ptype_id = $_SESSION["ptype_id"] = $_POST['person_type'];
	$family_name_k = $_SESSION["family_name_k"] = $_POST['family_name_k'];
	$given_name_k = $_SESSION["given_name_k"] = $_POST['given_name_k'];
	$family_name_r = $_SESSION["family_name_r"] = $_POST['family_name_r'];
	$given_name_r = $_SESSION["given_name_r"] = $_POST['given_name_r'];
	$dob = $_SESSION["dob"] = $_POST['dob'] ?: NULL;
	$gender_id = $_SESSION["gender_id"] = $_POST['gender'];
	$start_date = $_SESSION["start_date"] = $_POST['start_date'];
	$end_date = $_SESSION["end_date"] = $_POST['end_date'] ?: "infinity"; // "infinity" means no end date

	// get names instead of ID numbers for user confirmation
	$ptype_name = get_ptype_by_id($ptype_id);
	$gender_name = get_gender_by_id($gender_id);

	echo "<h1>Please confirm the following information submitted for the new class</h1>";
	echo "<table><tbody>";
	echo "<tr><td><b>Person Type</b></td><td>". htmlspecialchars($ptype_name, ENT_QUOTES, 'UTF-8') ."</td></tr>";
	echo "<tr><td><b>Family Name <span class=\"hint\">(in Kanji/Kana)</span></b></td><td>". htmlspecialchars($family_name_k, ENT_QUOTES, 'UTF-8') ."</td></tr>";
	echo "<tr><td><b>Given Name <span class=\"hint\">(in Kanji/Kana)</span></b></td><td>". htmlspecialchars($given_name_k, ENT_QUOTES, 'UTF-8') ."</td></tr>";
	echo "<tr><td><b>Family Name <span class=\"hint\">(in Romaji)</span></b></td><td>". htmlspecialchars($family_name_r, ENT_QUOTES, 'UTF-8') ."</td></tr>";
	echo "<tr><td><b>Given Name <span class=\"hint\">(in Romaji)</span></b></td><td>". htmlspecialchars($given_name_r, ENT_QUOTES, 'UTF-8') ."</td></tr>";
	if(isset($dob)) {
		echo "<tr><td><b>Date of Birth</b></td><td>". htmlspecialchars(date("F j, Y",strtotime($dob))) ."</td></tr>";
	}
	else {
		echo "<tr><td><b>Date of Birth</b></td><td>unknown</td></tr>";
	}
	echo "<tr><td><b>Gender</b></td><td>". htmlspecialchars($gender_name, ENT_QUOTES, 'UTF-8') ."</td></tr>";
	echo "<tr><td><b>Start Date</b></td><td>". htmlspecialchars(date("F j, Y",strtotime($start_date)), ENT_QUOTES, 'UTF-8') ."</td></tr>";
	if($end_date == "infinity") {
		echo "<tr><td><b>End Date</b></td><td>none</td></tr>";
	}
	else {
		echo "<tr><td><b>End Date</b></td><td>". htmlspecialchars(date("F j, Y",strtotime($end_date)), ENT_QUOTES, 'UTF-8') ."</td></tr>";
	}
	echo "</tbody></table>";

	echo "<p class=\"afterform\">If the above information is correct, please choose \"confirm\".</p>";
	echo "<form action=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\">";
	echo "  <button type=\"submit\">Confirm</button>";
	echo "</form>";
}

// The information has been confirmed
else {
	$ptype_id = $_SESSION["ptype_id"];
	$family_name_k = $_SESSION["family_name_k"];
	$given_name_k = $_SESSION["given_name_k"];
	$family_name_r = $_SESSION["family_name_r"];
	$given_name_r = $_SESSION["given_name_r"];
	$dob = $_SESSION["dob"];
	$gender_id = $_SESSION["gender_id"];
	$start_date = $_SESSION["start_date"];
	$end_date = $_SESSION["end_date"];
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

	echo "<p>Attempting to insert the new person into the database ...</p>";
	if($person_id = add_new_person($ptype_id,$family_name_k,$given_name_k,$family_name_r,$given_name_r,$dob,$gender_id,$start_date,$end_date)) {
		echo "<p>New person inserted successfully!</p>";
		echo "<p>New Person ID: $person_id</p>";
	}
	else {
		echo "<p>An error has occurred while inserting the new person.</p>";
	}

	// link back to the script or the beginning
	echo "<p>Where to next?</p>";
	echo "<p><a href=\"add_person.php\">Add another new person.</a></p>";
	echo "<p><a href=\"index.php\">Go back to the beginning of the system.</a></p>";

}
?>

</body>
</html>
