<?php
//Continue the session
session_start();

$test_id = $_SESSION["test_id"] = $_GET["testid"] ?? $_SESSION["test_id"] ?? NULL;
$start_date = $_SESSION["start_date"] = $_GET["start_date"] ?? $_SESSION["start_date"] ?? NULL;
$end_date = $_SESSION["end_date"] = $_GET["end_date"] ?? $_SESSION["end_date"] ?? NULL;
require_once('includes/model.php');
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
$students = get_all_students();
foreach ($students as $student)
{
    echo "<li><a href=\"print_grades.php?sid=" . htmlspecialchars($student['person_id'], ENT_QUOTES, 'UTF-8') . "\">" .
		htmlspecialchars($student['family_name_r'], ENT_QUOTES, 'UTF-8') . ", " . htmlspecialchars($student['given_name_r'], ENT_QUOTES, 'UTF-8') . " (" .
		htmlspecialchars($student['family_name_k'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($student['given_name_k'], ENT_QUOTES, 'UTF-8') . ")</a></li>\r\n";
}
?>

</ul>
</body>
</html>
