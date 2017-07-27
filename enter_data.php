<?php
// Start the session
session_start();

// Get session variables
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
	<title>Enter Attendance Information</title>
	<link rel="stylesheet" type="text/css" media="screen" href="css/main.css">
</head>
<body>
<?php
/* Debugging information
echo "<p>Teacher ID: $teacher_id</p>";
echo "<p>Date: $date</p>";
echo "<p>Day of the week: $dow</p>";
*/
?>

<h1>Enter data:</h1>
<form action="submit_data.php" method="post">
<table>
	<thead>
		<tr>
			<td>Student ID</td>
			<td>Student Name</td>
			<td>Present?</td>
<?php
	if(is_graded_class($class_id)) {
		echo "<td>Speaking</td>
			<td>Listening</td>
			<td>Reading</td>
			<td>Writing</td>
			<td>Behavior</td>";
	}
?>
			<td>Notes</td>
		</tr>
	</thead>
	<tbody>

<?php
$stmt = $pdo->prepare("SELECT p.person_id AS student_id, concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name
	FROM people p
	INNER JOIN roster r ON r.person_id = p.person_id AND r.class_id = :class_id AND p.person_id != :teacher_id
	UNION
	SELECT p.person_id AS student_id, concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name
	FROM people p
	INNER JOIN makeup m ON p.person_id = m.student_id
	INNER JOIN class_instances ci ON ci.cinstance_id = m.makeup_cinstance_id AND ci.class_id = :class_id AND ci.cinstance_date = :date");
$stmt->execute(['class_id' => $class_id, 'teacher_id' => $teacher_id, 'date' => $date]);
if ($stmt->rowCount()) {
	while ($row = $stmt->fetch())
	{
		echo "<tr><td>" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . "</td>
				<td>" . htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8') . "</td>
				<td><input type=\"hidden\" name=\"adata[" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . "][present]\" value=\"0\" />
				    <input type=\"checkbox\" id=\"present\" name=\"adata[" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . "][present]\" value=\"1\" /></td>";
		if(is_graded_class($class_id)) {
			echo "<td><select id=\"speaking\" name=\"adata[" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . "][speaking]\">
						<option selected value=\"0\">0</option>
						<option value=\"1\">1</option>
						<option value=\"2\">2</option>
						<option value=\"3\">3</option>
						<option value=\"4\">4</option>
						<option value=\"5\">5</option>
					</select></td>
				<td><select id=\"listening\" name=\"adata[" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . "][listening]\">
						<option selected value=\"0\">0</option>
						<option value=\"1\">1</option>
						<option value=\"2\">2</option>
						<option value=\"3\">3</option>
						<option value=\"4\">4</option>
						<option value=\"5\">5</option>
					</select></td>
				<td><select id=\"reading\" name=\"adata[" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . "][reading]\">
						<option selected value=\"0\">0</option>
						<option value=\"1\">1</option>
						<option value=\"2\">2</option>
						<option value=\"3\">3</option>
						<option value=\"4\">4</option>
						<option value=\"5\">5</option>
					</select></td>
				<td><select id=\"writing\" name=\"adata[" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . "][writing]\">
						<option selected value=\"0\">0</option>
						<option value=\"1\">1</option>
						<option value=\"2\">2</option>
						<option value=\"3\">3</option>
						<option value=\"4\">4</option>
						<option value=\"5\">5</option>
					</select></td>
				<td><select id=\"behavior\" name=\"adata[" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . "][behavior]\">
						<option selected value=\"0\">0</option>
						<option value=\"1\">1</option>
						<option value=\"2\">2</option>
						<option value=\"3\">3</option>
						<option value=\"4\">4</option>
						<option value=\"5\">5</option>
					</select></td>";
		}
				echo "<td><textarea name=\"adata[" . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . "][notes]\" cols=\"30\" rows=\"2\"></textarea></td>";
	}
}
else {
	echo "No students found.";
}
?>

	</tbody>
</table>
<input type="submit" />
</form>
</body>
</html>
