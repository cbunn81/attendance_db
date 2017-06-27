<?php
//Continue the session
session_start();

$student_id = $_SESSION["student_id"] = $_GET["sid"] ?? $_SESSION["student_id"] ?? NULL;

require_once('../../config/db.inc.php');
require_once('includes/common.inc.php');

// get the student's ID, student's name, class's ID, level name
$stmt = $pdo->prepare("SELECT p.person_id AS student_id, concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name, c.class_id, l.level_name
	FROM people p
	INNER JOIN roster r ON r.person_id = p.person_id AND p.person_id = :student_id
	INNER JOIN classes c ON c.class_id = r.class_id
	INNER JOIN levels l ON c.level_id = l.level_id");
$stmt->execute(['student_id' => $student_id]);
if ($result = $stmt->fetch()) {
	$student_name = $result['student_name'];
	$class_id = $result['class_id'];
	$level_name = $result['level_name'];
}
else {
	echo "Query failed.";
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Attendance and Grades for <?php echo $student_name; ?></title>
	<link rel="stylesheet" type="text/css" media="screen" href="css/main.css">
</head>
<body>
<?php
// Get array of grade types
$grade_types = get_grade_types();
?>

<h1>Attendance and Grades for <?php echo $student_name; ?></h1>

<table>
	<thead>
		<tr>
			<td>Date</td>
			<td>Present</td>
<?php
foreach($grade_types as $grade_type)
{
	echo "<td>$grade_type</td>";
}
?>
			<td>Notes</td>
		</tr>
	</thead>
	<tbody>

<?php
	// Initialize counters and totals
	$absence_count = 0;
	$grades_total = array_fill(1,5,0);
	$grades_count = array_fill(1,5,0);

	// Create query to get all attendance ids for the student
	$attendance_stmt = $pdo->prepare("SELECT a.attendance_id, ci.cinstance_date, a.present, a.notes
	  FROM attendance a
	  INNER JOIN class_instances ci ON a.cinstance_id = ci.cinstance_id
	  WHERE a.student_id = :student_id");
	$attendance_stmt->execute(['student_id' => $student_id]);

	// Loop through getting grade information for each attendance_id and printing them out
	foreach ($attendance_stmt as $attendance_row)
	{
		$attendance_id = $attendance_row['attendance_id'];
		$cinstance_date = $attendance_row['cinstance_date'];
		$present = $attendance_row['present'] ? "Present" : "Absent";
		$notes = $attendance_row['notes'];

		echo "<tr>";
		echo "<td>" .$attendance_row['cinstance_date'] . "</td>";
		echo "<td>" . $present . "</td>";

		// Only query for grades if the student is present
		if($attendance_row['present']) {
			$grades_stmt = $pdo->prepare("SELECT gi.grade, gi.gtype_id
			  FROM grade_instances gi
				WHERE gi.attendance_id = :attendance_id
				ORDER BY gi.gtype_id");
			$grades_stmt->execute(['attendance_id' => $attendance_id]);

			foreach ($grades_stmt as $grades_row) {
				// Add the current grade the the total for this grade type
				$grades_total[$grades_row['gtype_id']] += $grades_row['grade'];
				$grades_count[$grades_row['gtype_id']]++;
				echo "<td>" . $grades_row['grade'] . "</td>";
			}
		}
		// Otherwise, output 5 filler table cells and increment the Absence Counter
		else {
			echo "<td></td><td></td><td></td><td></td><td></td>";
			$absence_count++;
		}
		echo "<td>" .$attendance_row['notes'] . "</td>";
		echo "</tr>";
	}

	// Output the averages for all grade types
	echo "<tr><td></td><td>Average</td>";
	foreach($grade_types as $grade_key => $grade_type) {
		echo "<td>" . number_format((float)($grades_total[$grade_key] / $grades_count[$grade_key]), 2, '.','')  . "</td>";
	}
	echo "<td></td></tr>";

?>

	</tbody>
</table>

<?php
	echo "<p>Absences: $absence_count</p>";
?>

</body>
</html>
