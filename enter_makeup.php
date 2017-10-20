<?php
// Start the session
session_start();

// Get session variables
$student_id = $_SESSION["student_id"] = $_SESSION["student_id"] ?? NULL;
$teacher_id = $_SESSION["teacher_id"] = $_SESSION["teacher_id"] ?? NULL;;
$original_date = $_SESSION["original_date"] = $_SESSION["original_date"] ?? NULL;
$makeup_date = $_SESSION["makeup_date"] = $_GET["date"] ?? $_SESSION["makeup_date"] ?? NULL;
//$dow = date("l", strtotime($date));
$original_class_id = $_SESSION["original_class_id"] = $_SESSION["original_class_id"] ?? NULL;
$makeup_class_id = $_SESSION["makeup_class_id"] = $_GET["mcid"] ?? $_SESSION["makeup_class_id"] ?? NULL;

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Confirm Makeup Information</title>
	<link rel="stylesheet" type="text/css" media="screen" href="css/main.css">
</head>
<body>
<?php
/* Debugging information
echo "<p>Student ID: $student_id</p>";
echo "<p>Teacher ID: $teacher_id</p>";
echo "<p>Original Date: $original_date</p>";
echo "<p>New Date: $makeup_date</p>";
//echo "<p>Day of the week: $dow</p>";
echo "<p>Original Class ID: $original_class_id</p>";
echo "<p>Makeup Class ID: $makeup_class_id</p>";
*/


// Not yet confirmed
if(empty($_SESSION["confirm"])) {
  echo "<h1>Confirm Makeup Information:</h1>";
  // get the student's ID, student's name, class's ID, level name
  if ($result = get_student_and_class_info($student_id,$original_class_id)) {
    $_SESSION["confirm"] = TRUE;
    // print_r($result);
    echo "<p>You want to enter a makeup lesson for " . htmlspecialchars($result['student_name'], ENT_QUOTES, 'UTF-8') .
    " (" . htmlspecialchars($result['level_name'], ENT_QUOTES, 'UTF-8') . ") with an original date of " .
    htmlspecialchars($original_date, ENT_QUOTES, 'UTF-8') . "(Class ID " . htmlspecialchars($original_class_id, ENT_QUOTES, 'UTF-8') .
    ") and a new date of " . htmlspecialchars($makeup_date, ENT_QUOTES, 'UTF-8') . "(Class ID " . htmlspecialchars($makeup_class_id, ENT_QUOTES, 'UTF-8') . ")?</p>";
    echo "<p><a href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "?mcid=" . htmlspecialchars($makeup_class_id, ENT_QUOTES, 'UTF-8') .
    "&date=" . htmlspecialchars($makeup_date, ENT_QUOTES, 'UTF-8') . "\">Confirm</a></p>";
  }
	else {
		echo "SQL error.";
	}
}

// confirmed
else {
  // Enter data into Database
  // get class instance IDs from the get_class_instance function, sending class_id and date for each cinstance
  $original_cinstance_id = get_class_instance($original_class_id, $original_date, 1);
  //echo "<p>Original Class Instance ID:" . htmlspecialchars($original_cinstance_id, ENT_QUOTES, 'UTF-8') . "</p>";
  $makeup_cinstance_id = get_class_instance($makeup_class_id, $makeup_date, 1);
  //echo "<p>Makeup Class Instance ID:" . htmlspecialchars($makeup_cinstance_id, ENT_QUOTES, 'UTF-8') . "</p>";

  // insert student_id, original_cinstance_id, makeup_cinstance_id and notes
  // Display confirmation of success or failure
  if ($result = add_makeup_lesson($student_id,$original_cinstance_id,$makeup_cinstance_id)) {
    // Insert successful, return makeup_id
    echo "<p>Success! The ID of the makeup information entered is " . htmlspecialchars($result['makeup_id'], ENT_QUOTES, 'UTF-8') . ".</p>";
		echo "<p>Go back to <a href=\"/\">the start</a>.</p>";
  }
  else {
    // Insert failure, return error
    //echo "Sorry, that didn't work. Error message: ";
    //echo implode(":", $ins_stmt->errorInfo());
		echo "Sorry, adding that makeup lesson failed.";
  }
}
?>

</body>
</html>
