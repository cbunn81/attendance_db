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
  /* Debugging information
  echo "<p>Teacher ID: $teacher_id</p>";
  echo "<p>Date: $date</p>";
  echo "<p>Day of the week: $dow</p>";
  echo "<p>Class ID: $class_id</p>"
  */
  ?>

<?php
// Sanitize POST variables
$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
echo "<pre>";
print_r($_POST);
echo "</pre>";

$location_id = $_POST['location'];
$location_name = get_location_by_id($location_id);
$dow_id = $_POST['dow'];
$dow_name = get_dow_by_id($dow_id);
$ctype_id = $_POST['ctype'];
$ctype_name = get_ctype_by_id($ctype_id);
$level_id = $_POST['level'];
$level_name = get_level_by_id($level_id);
$class_time = $_POST['class_time'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'] ?: "infinity";
$teacher_id = $_POST['teacher'];
$teacher_name = get_person_name($teacher_id);
$student_ids = $_POST['students'];

echo "<p>Location ID: ". htmlspecialchars($location_id, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Location Name: ". htmlspecialchars($location_name, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>DOW ID: ". htmlspecialchars($dow_id, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>DOW: ". htmlspecialchars($dow_name, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Class Type ID: ". htmlspecialchars($ctype_id, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Class Type: ". htmlspecialchars($ctype_name, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Level ID: ". htmlspecialchars($level_id, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Level: ". htmlspecialchars($level_name, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Class Time: ". htmlspecialchars($class_time, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Start Date: ". htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>End Date: ". htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Teacher ID: ". htmlspecialchars($teacher_id, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Teacher Name: ". htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Student IDs: ";
foreach($student_ids as $student_id) {
	echo htmlspecialchars($student_id, ENT_QUOTES, 'UTF-8') .", ";
}
echo "</p>";
echo "<p>Student Names: ";
echo "<ul>";
foreach($student_ids as $student_id) {
	$student_name = get_person_name($student_id);
	echo "<li>" . htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ."</li>";
}
echo "</ul>";
echo "</p>";

/*
echo "<p>Attempt class insert ....</p>";
$class_id = create_new_class($location_id,$dow_id,$ctype_id,$level_id,$class_time,$start_date,$end_date);

echo "<p>New Class ID: $class_id</p>";

echo "<p>Attempt Roster insert ...</p>";
if (create_roster_entry($teacher_id,$class_id,$start_date,$end_date)) {
	echo "<p>Success on inserting teacher ID $teacher_id!</p>";
}
foreach($student_ids as $student_id) {
	if (create_roster_entry($student_id,$class_id,$start_date,$end_date)) {
		echo "<p>Success on inserting student ID: $student_id!</p>";
	}
}
*/
?>

</body>
</html>
