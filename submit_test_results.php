<?php
// Start the session
session_start();

// Session variables needed: class_id, date,
$teacher_id = $_SESSION["teacher_id"] = $_GET["tid"] ?? $_SESSION["teacher_id"] ?? NULL;
$date = $_SESSION["date"] = $_GET["date"] ?? $_SESSION["date"] ?? NULL;
$dow = date("l", strtotime($date));
$class_id = $_SESSION["class_id"] = $_GET["cid"] ?? $_SESSION["class_id"] ?? NULL;

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Confirmation of Attendance Data Entered</title>
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

// Loop through the POST array and create variables
foreach($_POST['test_results'] as $student_id => $student_data) {

  // The only reason for a present variable is to know whether to enter test results
  // The regular attendance entry will handle attendance, even for test days
  if ($student_data['present']) {
    // Enter data into Database
    // First, get class instance IDs from the get_class_instance function, sending class_id and date for each cinstance
    $cinstance_id = get_class_instance($class_id, $date) ?: create_class_instance($class_id, $date);
    // echo "<p>Class Instance ID:" . htmlspecialchars($cinstance_id, ENT_QUOTES, 'UTF-8') . "</p>";

    // Second, get the attendance ID for the class instance and the student ID
    // This means that attendance must be entered first
    $attendance_id = get_attendance_id($cinstance_id, $student_id);

    // Third, enter test results
    // ** This is only for students in the All Stars classes, aka with a class type of "Child Group" or "Child Private"
    if ($ginstance_id = upsert_test_grades($attendance_id, $student_data)) {
  		echo "Success. Test grades inserted.";
  		//echo $ginstance_id;
  	}
  	else {
  		echo "Sorry, the test grades were not entered.";
  	}
  }
  else {
    echo "<p>Student absent, no test information entered.</p>";
  }
}

// Links back to enter more classes or go to the beginning
echo "<p><a href=\"choose_class.php?date=$date\">Enter data for another class on the same day ($date).</a></p>";
echo "<p><a href=\"index.php\">Go back to the beginning of the system.</a></p>";
?>

</body>
</html>
