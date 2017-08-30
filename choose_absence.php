<?php
// Continue the session
session_start();
$student_id = $_SESSION["student_id"] = $_GET["sid"] ?? $_SESSION["student_id"] ?? NULL;

require_once('../../config/db.inc.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose an Absence</title>
</head>
<body>
<h1>Choose an absence:</h1>
<ul>

<?php
$stmt = $pdo->prepare("SELECT a.student_id, concat_ws(' ',p.given_name_r, p.family_name_r) as name, a.attendance_id, c.class_id, ci.cinstance_date, dow.dow_name, left(c.class_time::text, 5) as time, l.level_name, a.present
	FROM attendance a
	INNER JOIN people p ON a.student_id = p.person_id AND p.person_id = :student_id
	INNER JOIN class_instances ci ON a.cinstance_id = ci.cinstance_id
	INNER JOIN classes c ON ci.class_id = c.class_id
	INNER JOIN levels l ON c.level_id = l.level_id
	INNER JOIN days_of_week dow ON c.dow_id = dow.dow_id
	WHERE a.present = false
	ORDER BY ci.cinstance_date, c.class_time");
$stmt->execute(['student_id' => $student_id]);
if($absence_count = $stmt->rowCount()) {
  echo "<h3>Here are the absences available:</h3>";
  foreach ($stmt as $row)
  {
      echo "<li><a href=\"choose_date.php?is_makeup=true&sid=" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') .
      "&ocid=" . htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . "&date=" . htmlspecialchars($row['cinstance_date'], ENT_QUOTES, 'UTF-8') . "\">" .
      htmlspecialchars($row['cinstance_date'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . " - " .
      htmlspecialchars($row['dow_name'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['time'], ENT_QUOTES, 'UTF-8') . " - " .
      htmlspecialchars($row['level_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
  }
}
// no absences
else {
  echo "<h3>No previous absences, please choose one of the future dates below.</h3>";
}

?>

</ul>
</body>
</html>