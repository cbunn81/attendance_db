<?php
//Continue the session
session_start();

$student_id = $_SESSION["student_id"] = $_GET["sid"] ?? $_SESSION["student_id"] ?? NULL;

require_once('includes/model.php');


// get the student's ID, student's name, class's ID, level name
/* NOTE - This selects the first class ID, but a few students are enrolled in more than one class
 					or have a class in the rosters table that they moved from or quit. The real issue is when
					there's are multiple classes of different levels. So, either we need separate HTML tables
					for each enrolled class or some way to keep things straight in the same table. */
if (!(get_student_info($student_id))) {
	echo "Student doesn't exist.";
}
else {
	$student_info = get_student_info($student_id);
}
if(!(get_current_classes_for_student($student_id))) {
	echo "Student has no classes.";
}
else {
	$current_classes = get_current_classes_for_student($student_id);
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Attendance and Grades for <?php echo $student_info['student_name']; ?></title>
	<link rel="stylesheet" type="text/css" media="screen" href="css/main.css">
	<link rel="stylesheet" type="text/css" media="print" href="css/print.css">
</head>
<body>
<?php
// Get array of grade types
$grade_types = get_grade_types();
?>

<h1>Attendance and Grades for <?php echo $student_info['student_name']; ?></h1>

<?php
//Loop through a student's current classes, if there are more than one
foreach($current_classes as $class_info):
	?>

<h2>Level: <?php echo $class_info['level_name']; ?></h2>
<table>
	<thead>
		<tr>
			<td>Date</td>
			<td>Present</td>

<?php
if(is_graded_class($class_info['class_id'])) {
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
	$grades_total = array_change_key_case(array_fill_keys($grade_types,0), CASE_LOWER);
	$grades_count = array_change_key_case(array_fill_keys($grade_types,0), CASE_LOWER);

	// Create query to get all attendance ids for the student
// XXXX - ONLY FOR TEST 3 PERIOD!! - XXXX
	$start_date = "2017-12-17";
	$end_date = "2017-03-31";
	$attendance = get_attendance_from_date_range($student_id, $class_info['class_id'], $start_date, $end_date);

	// Loop through getting grade information for each attendance_id and printing them out
	foreach ($attendance as $attendance_instance)
	{
		$attendance_id = $attendance_instance['attendance_id'];
		$cinstance_id = $attendance_instance['cinstance_id'];
		$cinstance_date = $attendance_instance['cinstance_date'];
		$present = $attendance_instance['present'] ? "Present" : "Absent";
		$notes = $attendance_instance['notes'];

		echo "<tr>";
		echo "<td>" .$attendance_instance['cinstance_date'] . "</td>";
		if (is_makeup_lesson($student_id, $attendance_instance['cinstance_id'])) {
			$makeup_count++;
			echo "<td>" . $present . " (Makeup)</td>";
		}
		else {
			echo "<td>" . $present . "</td>";
		}


		// Only query for grades if the student is present
		if($attendance_instance['present']) {
			if(is_graded_class($class_info['class_id'])) {
				$grades = get_grades($attendance_id);
				foreach ($grades as $grade_type => $grade) {
					// Add the current grade the the total for this grade type
					$grades_total[$grade_type] += $grade;
					$grades_count[$grade_type]++;
					echo "<td>" . $grade . "</td>";
				}
			}
		}
		// Otherwise, output 5 filler table cells and increment the Absence Counter
		else {
			if(is_graded_class($class_info['class_id'])) {
				echo "<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>";
			}
			$absence_count++;
		}

		echo "</tr>";
	}
	if(is_graded_class($class_info['class_id'])) {
		// Output the averages for all grade types
		echo "<tr><td></td><td>Average</td>";
		//reset($grade_types);
		foreach($grade_types as $grade_type) {
			echo "<td>" . number_format((float)($grades_total[strtolower($grade_type)] / $grades_count[strtolower($grade_type)]), 2, '.','')  . "</td>";
		}
		echo "</tr>";
	}
	echo "<tr><td colspan=\"2\">Total Absences</td><td colspan=\"5\">$absence_count</td></tr>";
	echo "<tr><td colspan=\"2\">Total Makeups</td><td colspan=\"5\">$makeup_count</td></tr>";
?>

	</tbody>
</table>


<?php

if(is_graded_class($class_info['class_id'])) {
	// Set the average scores for each level - each level has an associative array of scores
	$averages["AS1"] = array(
	"listening" => 4.5,
	"reading" => 3.3,
	"handwriting" => 4.1,
	"intonation" => 1.9,
	"pronunciation" => 1.7,
	"speed" => 1.7,
	"accuracy" => 1.6,
	"confidence" => 1.7,
	"total" => 20.5,
);
$averages["AS2"] = array(
	"listening" => 4.5,
	"reading" => 2.8,
	"handwriting" => 4.4,
	"intonation" => 1.8,
	"pronunciation" => 1.9,
	"speed" => 1.8,
	"accuracy" => 1.6,
	"confidence" => 1.7,
	"total" => 20.4,
);
$averages["AS3"] = array(
	"listening" => 4.5,
	"reading" => 3.8,
	"handwriting" => 4.9,
	"intonation" => 2.0,
	"pronunciation" => 2.0,
	"speed" => 2.0,
	"accuracy" => 1.9,
	"confidence" => 1.8,
	"total" => 22.7,
);
$averages["AS4"] = array(
	"listening" => 5.0,
	"reading" => 5.0,
	"handwriting" => 5.0,
	"intonation" => 2.0,
	"pronunciation" => 2.0,
	"speed" => 2.0,
	"accuracy" => 2.0,
	"confidence" => 2.0,
	"total" => 25.0,
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
	<h2>Test #3 Results (<?php echo $class_info['level_name']; ?>)</h2>

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
				<td><?php echo $averages[$class_info['level_short_code']]["listening"]; ?></td>
			</tr>
			<tr>
				<td>Reading/Writing</td>
				<td>5</td>
				<td></td>
				<td><?php echo $averages[$class_info['level_short_code']]["reading"]; ?></td>
			</tr>
			<tr>
				<td>Handwriting</td>
				<td>5</td>
				<td></td>
				<td><?php echo $averages[$class_info['level_short_code']]["handwriting"]; ?></td>
			</tr>
			<tr>
				<td>Speaking - Intonation</td>
				<td>2</td>
				<td></td>
				<td><?php echo $averages[$class_info['level_short_code']]["intonation"]; ?></td>
			</tr>
			<tr>
				<td>Speaking - Pronunciation</td>
				<td>2</td>
				<td></td>
				<td><?php echo $averages[$class_info['level_short_code']]["pronunciation"]; ?></td>
			</tr>
			<tr>
				<td>Speaking - Speed</td>
				<td>2</td>
				<td></td>
				<td><?php echo $averages[$class_info['level_short_code']]["speed"]; ?></td>
			</tr>
			<tr>
				<td>Speaking - Accuracy</td>
				<td>2</td>
				<td></td>
				<td><?php echo $averages[$class_info['level_short_code']]["accuracy"]; ?></td>
			</tr>
			<tr>
				<td>Speaking - Confidence</td>
				<td>2</td>
				<td></td>
				<td><?php echo $averages[$class_info['level_short_code']]["confidence"]; ?></td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td>Total</td>
				<td>25</td>
				<td></td>
				<td><?php echo $averages[$class_info['level_short_code']]["total"]; ?></td>
			</tr>
		</tfoot>
	</table>

<?php
}
endforeach;  // End of loop for current classes
?>

</body>
</html>
