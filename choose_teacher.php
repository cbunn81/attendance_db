<?php
// Continue the session
session_start();

require_once('../../config/db.inc.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose a Teacher</title>
</head>
<body>
<h1>Choose a teacher:</h1>
<ul>

<?php
$stmt = $pdo->prepare("SELECT p2pt.person_id, p.given_name_r, p.family_name_r, p2pt.ptype_id, pt.ptype_name
  FROM people2person_types p2pt
  INNER JOIN person_types pt ON p2pt.ptype_id = pt.ptype_id AND pt.ptype_name = 'Staff'
  INNER JOIN people p ON p2pt.person_id = p.person_id
	ORDER BY p.given_name_r");
	$stmt->execute();
foreach ($stmt as $row)
{
    echo "<li><a href=\"choose_date.php?tid=" . htmlspecialchars($row['person_id'], ENT_QUOTES, 'UTF-8') . "\">" .
    htmlspecialchars($row['given_name_r'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($row['family_name_r'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
}
?>

</ul>
</body>
</html>
