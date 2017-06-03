<?php
// Continue the session
session_start();

// Session variables
$teacher_id = $_SESSION["teacher_id"] = $_GET["tid"] ?? $_SESSION["teacher_id"] ?? NULL;
$original_class_id = $_SESSION["original_class_id"] = $_GET["ocid"] ?? $_SESSION["original_class_id"] ?? NULL;
$original_date = $_SESSION["original_date"] = $_GET["date"] ?? $_SESSION["original_date"] ?? NULL;

require_once('../../config/db.inc.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Attendance System</title>
</head>
<body>
  <?php
	/* Debugging information
  echo (empty($_SESSION["is_makeup"]) ? "<p>Not makeup</p>" : "<p>Makeup</p>");
  echo (empty($teacher_id) ? "<p>No teacher set</p>" : "<p>Teacher set</p>");
  echo "<p>Teacher ID: $teacher_id</p>";
  echo "<p>Original Date: " . $_SESSION["original_date"] . "</p>";
  echo "<p>Original Class ID: $original_class_id</p>";
	*/
  ?>
<h1>Choose a student:</h1>
<ul>

<?php
  $stmt = $pdo->prepare("SELECT p.person_id AS student_id, concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name
  	FROM people p
  	INNER JOIN roster r ON r.person_id = p.person_id AND r.class_id = :class_id AND p.person_id != :teacher_id");
  $stmt->execute(['class_id' => $original_class_id, 'teacher_id' => $teacher_id]);
  if ($stmt->rowCount()) {
  	while ($row = $stmt->fetch())
  	{

      echo "<li><a href=\"choose_date.php?is_makeup=true&tid=" . htmlspecialchars($teacher_id, ENT_QUOTES, 'UTF-8') .
      "&sid=" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . "\">" .
      htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8') . "</a></li>";


  	}
  }
  else {
  	echo "No students found.";
  }
?>

</ul>
</body>
</html>
