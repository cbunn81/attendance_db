<?php
// Continue the session
session_start();

// Session variables
$location_id = $_SESSION["location_id"] = $_GET["lid"] ?? $_SESSION["location_id"] ?? NULL;
$teacher_id = $_SESSION["teacher_id"] = $_GET["tid"] ?? $_SESSION["teacher_id"] ?? NULL;
$original_class_id = $_SESSION["original_class_id"] = $_GET["ocid"] ?? $_SESSION["original_class_id"] ?? NULL;
$original_date = $_SESSION["original_date"] = $_GET["date"] ?? $_SESSION["original_date"] ?? NULL;

require_once('../../config/db.inc.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose a Student</title>
</head>
<body>

<h1>Choose a student:</h1>
<ul>

<?php

  $stmt = $pdo->prepare("SELECT DISTINCT p.person_id, p.given_name_r, p.family_name_r, p.family_name_k, p.given_name_k
  FROM people2person_types p2pt
  INNER JOIN person_types pt ON p2pt.ptype_id = pt.ptype_id AND pt.ptype_name = 'Student'
  INNER JOIN people p ON p2pt.person_id = p.person_id
	INNER JOIN roster r ON p.person_id = r.person_id
	INNER JOIN classes c ON r.class_id = c.class_id and c.location_id = :location_id
	ORDER BY p.family_name_r");
  $stmt->execute(['location_id' => $location_id]);
  if ($stmt->rowCount()) {
  	while ($row = $stmt->fetch())
  	{

      echo "<li><a href=\"choose_absence.php?sid=" . htmlspecialchars($row['person_id'], ENT_QUOTES, 'UTF-8') . "\">" .
			htmlspecialchars($row['family_name_r'], ENT_QUOTES, 'UTF-8') . ", " . htmlspecialchars($row['given_name_r'], ENT_QUOTES, 'UTF-8') . " (" .
			htmlspecialchars($row['family_name_k'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($row['given_name_k'], ENT_QUOTES, 'UTF-8') . ")</a></li>";


  	}
  }
  else {
  	echo "No students found.";
  }
?>

</ul>
</body>
</html>
