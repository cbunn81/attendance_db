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
$dow_id = $_POST['dow'];
$ctype_id = $_POST['ctype'];
$level_id = $_POST['level'];
$class_time = $_POST['class_time'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'] ?: "infinity";
$teacher_id = $_POST['teacher'];
$student_ids = $_POST['students'];

echo "<p>Location ID: ". htmlspecialchars($location_id, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>DOW ID: ". htmlspecialchars($dow_id, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Class Type ID: ". htmlspecialchars($ctype_id, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Level ID: ". htmlspecialchars($level_id, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Class Time: ". htmlspecialchars($class_time, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Start Date: ". htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>End Date: ". htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Teacher ID: ". htmlspecialchars($teacher_id, ENT_QUOTES, 'UTF-8') ."</p>";
echo "<p>Student IDs: ";
foreach($student_ids as $student_id) {
	echo htmlspecialchars($student_id, ENT_QUOTES, 'UTF-8') .", ";
}
echo "</p>";

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

?>

</body>
</html>
