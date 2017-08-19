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

require_once('../../config/db.inc.php');
require_once('includes/common.inc.php');
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
  $stmt = $pdo->prepare("SELECT p.person_id AS student_id, concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name, c.class_id, l.level_name
  	FROM people p
  	INNER JOIN roster r ON r.person_id = p.person_id AND p.person_id = :student_id AND r.class_id = :class_id
    INNER JOIN classes c ON c.class_id = r.class_id
    INNER JOIN levels l ON c.level_id = l.level_id");
  $stmt->execute(['student_id' => $student_id, 'class_id' => $original_class_id]);
  if ($result = $stmt->fetch()) {
    $_SESSION["confirm"] = TRUE;
    // print_r($result);
    echo "<p>You want to enter a makeup lesson for " . htmlspecialchars($result['student_name'], ENT_QUOTES, 'UTF-8') .
    " (" . htmlspecialchars($result['level_name'], ENT_QUOTES, 'UTF-8') . ") with an original date of " .
    htmlspecialchars($original_date, ENT_QUOTES, 'UTF-8') . "(Class ID " . htmlspecialchars($original_class_id, ENT_QUOTES, 'UTF-8') .
    ") and a new date of " . htmlspecialchars($makeup_date, ENT_QUOTES, 'UTF-8') . "(Class ID " . htmlspecialchars($makeup_class_id, ENT_QUOTES, 'UTF-8') . ")?</p>";
    echo "<p><a href=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "?mcid=" . htmlspecialchars($makeup_class_id, ENT_QUOTES, 'UTF-8') .
    "&date=" . htmlspecialchars($makeup_date, ENT_QUOTES, 'UTF-8') . "\">Confirm</a></p>";
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
  $ins_stmt = $pdo->prepare("INSERT INTO makeup (student_id, original_cinstance_id, makeup_cinstance_id)
    VALUES (:student_id, :original_cinstance_id, :makeup_cinstance_id)
    RETURNING makeup_id");
  $ins_stmt->execute(['student_id' => $student_id, 'original_cinstance_id' => $original_cinstance_id, 'makeup_cinstance_id' => $makeup_cinstance_id]);

  // Display confirmation of success or failure
  if ($result = $ins_stmt->fetch()) {
    // Insert successful, return makeup_id
    echo "<p>Success! The ID of the makeup information entered is " . htmlspecialchars($result['makeup_id'], ENT_QUOTES, 'UTF-8') . ".</p>";
		echo "<p>Go back to <a href=\"/\">the start</a>.</p>";
  }
  else {
    // Insert failure, return error
    echo "Sorry, that didn't work. Error message: ";
    echo implode(":", $ins_stmt->errorInfo());
  }


}
?>

</body>
</html>
