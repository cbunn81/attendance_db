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
foreach($_POST['adata'] as $student_id => $student_data) {
  /*Debugging information
	echo "<h2>Student $student_id</h2>";
  echo "<pre>";
  print_r($student_data);
  echo "</pre>";
	*/
  $present = $student_data['present'] ?? FALSE;

  $notes = $student_data['notes'] ?? NULL;
	/* Debugging information
	echo "<p>Present: " . var_dump($present) . "</p>";
	echo "<p>Notes: $notes</p>";

  echo "Grade types: ";
  print_r(get_grade_types());
	*/

  // Enter data into Database
  // First, get class instance IDs from the get_class_instance function, sending class_id and date for each cinstance
  $cinstance_id = get_class_instance($class_id, $date) ?: create_class_instance($class_id, $date);
  // echo "<p>Class Instance ID:" . htmlspecialchars($cinstance_id, ENT_QUOTES, 'UTF-8') . "</p>";

  // Second,  set "present" and "notes" in attendance table, along with cinstance_id, teacher_id, student_id
	// if the data has already been submitted, update the existing record
	if($student_data['update']) {
		$attendance_id = update_attendance($cinstance_id, $teacher_id, $student_id, $present, $notes);
	}
	// otherwise, insert a new record
	else {
	  $attendance_id = add_attendance($cinstance_id, $teacher_id, $student_id, $present, $notes);
	}

  // Display confirmation of success or failure
  if (is_int($attendance_id)) {
    // Insert successful, return attendance_id
    echo "<p>Success! The ID of the attendance information entered is " . htmlspecialchars($attendance_id, ENT_QUOTES, 'UTF-8') . ".</p>";
  }
  else {
    // Insert failure, return error
    echo "<p>Sorry, that didn't work. Error message: ";
    echo implode(":", $attendance_id);
    echo "</p>";
  }

  // Third, set a grade instance for each of the grades (speaking, listening, reading, writing, behavior)
  // ** This is only for students in the All Stars classes, aka with a class type of "Child Group" or "Child Private"
  if (is_graded_class($class_id) and ($present == TRUE)) {
    // echo "<p>It is a graded class and student was present. Begin entering grade information.</p>";

		if ($ginstance_id = upsert_grades($attendance_id, $student_data)) {
			echo "Success. Grades inserted.";
			//echo $ginstance_id;
		}
		else {
			echo "Sorry, the grades were not entered.";
		}
  }
  else {
    echo "<p>It is not a graded class or the student was absent, skipping grade information.</p>";
  }

}

// Links back to enter more classes or go to the beginning
echo "<p><a href=\"choose_class.php?date=$date\">Enter data for another class on the same day ($date).</a></p>";
echo "<p><a href=\"index.php\">Go back to the beginning of the system.</a></p>";
?>

</body>
</html>
