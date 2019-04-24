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

echo "<p>Location ID: $location_id</p>";
echo "<p>DOW ID: $dow_id</p>";
echo "<p>Class Type ID: $ctype_id</p>";
echo "<p>Level ID: $level_id</p>";
echo "<p>Class Time: $class_time</p>";
echo "<p>Start Date: $start_date</p>";
echo "<p>End Date: $end_date</p>";
echo "<p>Teacher ID: $teacher_id</p>";
echo "<p>Student IDs: ";
foreach($student_ids as $student_id)
{
	echo "$student_id, ";
}
echo "</p>";
?>

</body>
</html>
