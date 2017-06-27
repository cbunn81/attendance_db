<?php
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
$stmt = $pdo->query("SELECT p.person_id, concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name, p2pt.ptype_id, pt.ptype_name
  FROM people p
  INNER JOIN people2person_types p2pt ON p.person_id = p2pt.person_id
  INNER JOIN person_types pt ON p2pt.ptype_id = pt.ptype_id
  WHERE pt.ptype_name = 'Student'
  ");
foreach ($stmt as $row)
{
    echo "<li><a href=\"print_grades.php?sid=" . htmlspecialchars($row['person_id'], ENT_QUOTES, 'UTF-8') . "\">" .
    htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
}
?>

</ul>
</body>
</html>
