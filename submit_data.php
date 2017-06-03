<?php
// Start the session
session_start();

// Session variables needed: class_id, date,
$teacher_id = $_SESSION["teacher_id"] = $_GET["tid"] ?? $_SESSION["teacher_id"] ?? NULL;
$date = $_SESSION["date"] = $_GET["date"] ?? $_SESSION["date"] ?? NULL;
$dow = date("l", strtotime($date));
$class_id = $_SESSION["class_id"] = $_GET["cid"] ?? $_SESSION["class_id"] ?? NULL;

require_once('../../config/db.inc.php');
require_once('includes/common.inc.php');
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
  echo "<h2>Student $student_id</h2>";
  echo "<pre>";
  print_r($student_data);
  echo "</pre>";
  $present = $student_data['present'] ?? FALSE;
  //settype($present, "boolean");
  echo "<p>Present: " . var_dump($present) . "</p>";
  $notes = $student_data['notes'] ?? NULL;
  echo "<p>Notes: $notes</p>";

  echo "Grade types: ";
  print_r(get_grade_types());

  // Enter data into Database
  // First, get class instance IDs from the create_class_instance function, sending class_id and date for each cinstance
  $cinstance_id = create_class_instance($class_id, $date);
  // echo "<p>Class Instance ID:" . htmlspecialchars($cinstance_id, ENT_QUOTES, 'UTF-8') . "</p>";

  // Second,  set "present" and "notes" in attendance table, along with cinstance_id, teacher_id, student_id
  $ins_stmt_attend = $pdo->prepare("INSERT INTO attendance (cinstance_id, teacher_id, student_id, present, notes)
    VALUES (:cinstance_id, :teacher_id, :student_id, :present, :notes)
    RETURNING attendance_id");
  $ins_stmt_attend->execute(['cinstance_id' => $cinstance_id, 'teacher_id' => $teacher_id, 'student_id' => $student_id, 'present' => $present, 'notes' => $notes]);

  // Display confirmation of success or failure
  if ($attend_result = $ins_stmt_attend->fetch()) {
    // Insert successful, return attendance_id
    $attendance_id = $attend_result['attendance_id'];
    echo "<p>Success! The ID of the attendance information entered is " . htmlspecialchars($attendance_id, ENT_QUOTES, 'UTF-8') . ".</p>";
  }
  else {
    // Insert failure, return error
    echo "<p>Sorry, that didn't work. Error message: ";
    echo implode(":", $ins_stmt_attend->errorInfo());
    echo "</p>";
  }

  // Third, set a grade instance for each of the grades (speaking, listening, reading, writing, behavior)
  // ** This is only for students in the All Stars classes, aka with a class type of "Child Group" or "Child Private"
  if(is_graded_class($class_id) and ($present == TRUE)) {
    // echo "<p>It is a graded class and student was present. Begin entering grade information.</p>";
    $grade_types = get_grade_types();
    foreach ($grade_types as $grade_type) {
      // insert into grade_instances where (select gtype_id from grade_types where gtype_name = $grade_type)
      $ins_stmt_grade = $pdo->prepare("INSERT INTO grade_instances (gtype_id, attendance_id, grade)
        VALUES ((SELECT gtype_id FROM grade_types WHERE LOWER(gtype_name) = LOWER(:grade_type)),
                :attendance_id,
                :grade)
        RETURNING ginstance_id");
      $ins_stmt_grade->execute(['grade_type' => $grade_type,'attendance_id' => $attendance_id,'grade' => $student_data[strtolower($grade_type)]]);

      // Display confirmation of success or failure
      if ($grade_result = $ins_stmt_grade->fetch()) {
        // Insert successful, return attendance_id
        $ginstance_id = $grade_result['ginstance_id'];
        echo "<p>Success! The ID of the grade instance information entered is " . htmlspecialchars($ginstance_id, ENT_QUOTES, 'UTF-8') . ".</p>";
      }
      else {
        // Insert failure, return error
        echo "<p>Sorry, that didn't work. Error message: ";
        echo implode(":", $ins_stmt_grade->errorInfo());
        echo "</p>";
      }
    }
  }
  else {
    echo "<p>It is not a graded class or the student was absent, don't enter grade information.</p>";
  }

}
?>

</body>
</html>
