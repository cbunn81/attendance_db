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
  /* Debugging information
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
  $cinstance_id = get_class_instance($class_id, $date, 1);
  // echo "<p>Class Instance ID:" . htmlspecialchars($cinstance_id, ENT_QUOTES, 'UTF-8') . "</p>";

  // Second,  set "present" and "notes" in attendance table, along with cinstance_id, teacher_id, student_id
	// if the data has already been submitted, update the existing record
	if($student_data['update']) {
		$attend_stmt = $pdo->prepare("UPDATE attendance SET (teacher_id, present, notes)
	    = (:teacher_id, :present, :notes)
			WHERE cinstance_id = :cinstance_id AND student_id = :student_id
	    RETURNING attendance_id");
	}
	// otherwise, insert a new record
	else {
	  $attend_stmt = $pdo->prepare("INSERT INTO attendance (cinstance_id, teacher_id, student_id, present, notes)
	    VALUES (:cinstance_id, :teacher_id, :student_id, :present, :notes)
	    RETURNING attendance_id");
	}
  $attend_stmt->execute(['cinstance_id' => $cinstance_id, 'teacher_id' => $teacher_id, 'student_id' => $student_id, 'present' => $present, 'notes' => $notes]);

  // Display confirmation of success or failure
  if ($attend_result = $attend_stmt->fetch()) {
    // Insert successful, return attendance_id
    $attendance_id = $attend_result['attendance_id'];
    echo "<p>Success! The ID of the attendance information entered is " . htmlspecialchars($attendance_id, ENT_QUOTES, 'UTF-8') . ".</p>";
  }
  else {
    // Insert failure, return error
    echo "<p>Sorry, that didn't work. Error message: ";
    echo implode(":", $attend_stmt->errorInfo());
    echo "</p>";
  }

  // Third, set a grade instance for each of the grades (speaking, listening, reading, writing, behavior)
  // ** This is only for students in the All Stars classes, aka with a class type of "Child Group" or "Child Private"
  if(is_graded_class($class_id) and ($present == TRUE)) {
    // echo "<p>It is a graded class and student was present. Begin entering grade information.</p>";

		// get all grade instances for the attendance ID returned above, if any exist
		$grade_check_stmt = $pdo->prepare("SELECT ginstance_id FROM grade_instances WHERE attendance_id = :attendance_id");
		$grade_check_stmt->execute(['attendance_id' => $attendance_id]);

    $grade_types = get_grade_types();
    foreach ($grade_types as $grade_type) {
      // insert into grade_instances where (select gtype_id from grade_types where gtype_name = $grade_type)
			// if the data has already been submitted, update the existing record
			if ($grade_check_stmt->rowCount()) {
				$grade_stmt = $pdo->prepare("UPDATE grade_instances SET grade = :grade
	        WHERE attendance_id = :attendance_id AND gtype_id = (SELECT gtype_id FROM grade_types WHERE LOWER(gtype_name) = LOWER(:grade_type))
	        RETURNING ginstance_id");
			}
			// otherwise, insert a new record
			else {
				$grade_stmt = $pdo->prepare("INSERT INTO grade_instances (gtype_id, attendance_id, grade)
	        VALUES ((SELECT gtype_id FROM grade_types WHERE LOWER(gtype_name) = LOWER(:grade_type)),
	                :attendance_id,
	                :grade)
	        RETURNING ginstance_id");
			}
      $grade_stmt->execute(['grade_type' => $grade_type,'attendance_id' => $attendance_id,'grade' => $student_data[strtolower($grade_type)]]);

      // Display confirmation of success or failure
      if ($grade_result = $grade_stmt->fetch()) {
        // Insert successful, return attendance_id
        $ginstance_id = $grade_result['ginstance_id'];
        echo "<p>Success! The ID of the grade instance information entered is " . htmlspecialchars($ginstance_id, ENT_QUOTES, 'UTF-8') . ".</p>";
      }
      else {
        // Insert failure, return error
        echo "<p>Sorry, that didn't work. Error message: ";
        echo implode(":", $grade_stmt->errorInfo());
        echo "</p>";
      }
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
