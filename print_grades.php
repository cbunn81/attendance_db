<?php
//Continue the session
session_start();

$student_id = $_SESSION["student_id"] = $_GET["sid"] ?? $_SESSION["student_id"] ?? NULL;

require_once('../../config/db.inc.php');
require_once('includes/common.inc.php');

// get the student's ID, student's name, class's ID, level name
$stmt = $pdo->prepare("SELECT p.person_id AS student_id, concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name, c.class_id, l.level_name, l.level_short_code
	FROM people p
	INNER JOIN roster r ON r.person_id = p.person_id AND p.person_id = :student_id
	INNER JOIN classes c ON c.class_id = r.class_id
	INNER JOIN levels l ON c.level_id = l.level_id");
$stmt->execute(['student_id' => $student_id]);
if ($result = $stmt->fetch()) {
	$student_name = $result['student_name'];
	$class_id = $result['class_id'];
	$level_name = $result['level_name'];
	$level_short_code = $result['level_short_code'];
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
	<link rel="stylesheet" type="text/css" media="print" href="css/print.css">
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
if(is_graded_class($class_id)) {
	foreach($grade_types as $grade_type)
	{
		echo "<td>$grade_type</td>";
	}
}
?>
		</tr>
	</thead>
	<tbody>

<?php
	// Initialize counters and totals
	$absence_count = 0;
	$makeup_count = 0;
	$grades_total = array_fill(1,5,0);
	$grades_count = array_fill(1,5,0);

	// Create query to get all attendance ids for the student
// XXXX - ONLY FOR TEST 3 PERIOD!! - XXXX
	$attendance_stmt = $pdo->prepare("SELECT a.attendance_id, ci.cinstance_id, ci.cinstance_date, a.present, a.notes
	  FROM attendance a
	  INNER JOIN class_instances ci ON a.cinstance_id = ci.cinstance_id
	  WHERE a.student_id = :student_id AND ci.cinstance_date BETWEEN '2017-09-17' AND '2017-12-16'
		ORDER BY ci.cinstance_date");
	$attendance_stmt->execute(['student_id' => $student_id]);

	// Loop through getting grade information for each attendance_id and printing them out
	foreach ($attendance_stmt as $attendance_row)
	{
		$attendance_id = $attendance_row['attendance_id'];
		$cinstance_id = $attendance_row['cinstance_id'];
		$cinstance_date = $attendance_row['cinstance_date'];
		$present = $attendance_row['present'] ? "Present" : "Absent";
		$notes = $attendance_row['notes'];

		echo "<tr>";
		echo "<td>" .$attendance_row['cinstance_date'] . "</td>";
		if (is_makeup_lesson($student_id, $attendance_row['cinstance_id'])) {
			$makeup_count++;
			echo "<td>" . $present . " (Makeup)</td>";
		}
		else {
			echo "<td>" . $present . "</td>";
		}


		// Only query for grades if the student is present
		if($attendance_row['present']) {
			if(is_graded_class($class_id)) {
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
		}
		// Otherwise, output 5 filler table cells and increment the Absence Counter
		else {
			if(is_graded_class($class_id)) {
				echo "<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>";
			}
			$absence_count++;
		}

		echo "</tr>";
	}
	if(is_graded_class($class_id)) {
		// Output the averages for all grade types
		echo "<tr><td></td><td>Average</td>";
		foreach($grade_types as $grade_key => $grade_type) {
			echo "<td>" . number_format((float)($grades_total[$grade_key] / $grades_count[$grade_key]), 2, '.','')  . "</td>";
		}
		echo "</tr>";
	}
	echo "<tr><td colspan=\"2\">Total Absences</td><td colspan=\"5\">$absence_count</td></tr>";
	echo "<tr><td colspan=\"2\">Total Makeups</td><td colspan=\"5\">$makeup_count</td></tr>";
?>

	</tbody>
</table>


<?php

if(is_graded_class($class_id)) {
	// Set the average scores for each level - each level has an associative array of scores
	$averages["AS1"] = array(
		"listening" => 4.1,
		"reading" => 1.5,
		"handwriting" => 3.0,
		"intonation" => 1.4,
		"pronunciation" => 1.3,
		"speed" => 1.5,
		"accuracy" => 1.2,
		"confidence" => 1.5,
		"total" => 15.7,
	);
	$averages["AS2"] = array(
		"listening" => 4.5,
		"reading" => 2.6,
		"handwriting" => 3.8,
		"intonation" => 1.4,
		"pronunciation" => 1.5,
		"speed" => 1.2,
		"accuracy" => 1.3,
		"confidence" => 1.3,
		"total" => 17.6,
	);
	$averages["AS3"] = array(
		"listening" => 4.6,
		"reading" => 3.0,
		"handwriting" => 4.0,
		"intonation" => 1.6,
		"pronunciation" => 1.5,
		"speed" => 1.4,
		"accuracy" => 1.4,
		"confidence" => 1.5,
		"total" => 19.2,
	);
	$averages["AS4"] = array(
		"listening" => 5.0,
		"reading" => 5.0,
		"handwriting" => 5.0,
		"intonation" => 1.8,
		"pronunciation" => 1.2,
		"speed" => 2.0,
		"accuracy" => 2.0,
		"confidence" => 2.0,
		"total" => 24.3,
	);
	$averages["AS5"] = array(
		"listening" => 5.0,
		"reading" => 3.0,
		"handwriting" => 4.5,
		"intonation" => 1.5,
		"pronunciation" => 1.0,
		"speed" => 1.5,
		"accuracy" => 1.0,
		"confidence" => 1.5,
		"total" => 19.0,
	);
?>
	<h2>Test #3 Results (<?php echo $level_name; ?>)</h2>

	<table>
		<thead>
			<tr>
				<td>Category</td>
				<td>Maximum Score</td>
				<td>Student's score</td>
				<td>Average Score</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Listening</td>
				<td>5</td>
				<td></td>
				<td><?php echo $averages[$level_short_code]["listening"]; ?></td>
			</tr>
			<tr>
				<td>Reading/Writing</td>
				<td>5</td>
				<td></td>
				<td><?php echo $averages[$level_short_code]["reading"]; ?></td>
			</tr>
			<tr>
				<td>Handwriting</td>
				<td>5</td>
				<td></td>
				<td><?php echo $averages[$level_short_code]["handwriting"]; ?></td>
			</tr>
			<tr>
				<td>Speaking - Intonation</td>
				<td>2</td>
				<td></td>
				<td><?php echo $averages[$level_short_code]["intonation"]; ?></td>
			</tr>
			<tr>
				<td>Speaking - Pronunciation</td>
				<td>2</td>
				<td></td>
				<td><?php echo $averages[$level_short_code]["pronunciation"]; ?></td>
			</tr>
			<tr>
				<td>Speaking - Speed</td>
				<td>2</td>
				<td></td>
				<td><?php echo $averages[$level_short_code]["speed"]; ?></td>
			</tr>
			<tr>
				<td>Speaking - Accuracy</td>
				<td>2</td>
				<td></td>
				<td><?php echo $averages[$level_short_code]["accuracy"]; ?></td>
			</tr>
			<tr>
				<td>Speaking - Confidence</td>
				<td>2</td>
				<td></td>
				<td><?php echo $averages[$level_short_code]["confidence"]; ?></td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td>Total</td>
				<td>25</td>
				<td></td>
				<td><?php echo $averages[$level_short_code]["total"]; ?></td>
			</tr>
		</tfoot>
	</table>
<?php
}
?>
</body>
</html>
