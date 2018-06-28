<?php
//Continue the session
session_start();

$test_id = $_SESSION["test_id"] = $_GET["testid"] ?? $_SESSION["test_id"] ?? NULL;
$student_id = $_SESSION["student_id"] = $_GET["sid"] ?? $_SESSION["student_id"] ?? NULL;

require_once('includes/model.php');
// XXXX - ONLY FOR TEST 4 PERIOD!! - XXXX
//$start_date = "2017-12-17";
//$end_date = "2018-03-31";
$test_info = get_test_by_id($test_id);
$start_date = $test_info["start_date"];
$end_date = $test_info["end_date"];


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
if(!(get_classes_for_student_by_date_range($student_id, $start_date, $end_date))) {
	echo "Student has no classes.";
}
else {
	$current_classes = get_classes_for_student_by_date_range($student_id, $start_date, $end_date);
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
			echo "<td>" . round((float)($grades_total[strtolower($grade_type)] / $grades_count[strtolower($grade_type)]), 1)  . "</td>";
		}
		echo "</tr>";
	}
	echo "<tr><td colspan=\"2\">Total Absences</td><td colspan=\"5\">$absence_count</td></tr>";
	echo "<tr><td colspan=\"2\">Total Makeups</td><td colspan=\"5\">$makeup_count</td></tr>";
?>

	</tbody>
</table>

	<h2><?php echo $test_info["test_name"]; ?> Results (<?php echo $class_info['level_name']; ?>)</h2>

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
<?php

$test_grade_types = get_test_grade_types();
$test_averages = get_test_averages($test_info["test_name"], $class_info['level_name']);
$test_grade_maximum_value_total = 0;
$test_grade_total = 0;
$test_averages_total = 0;

foreach($test_grade_types as $test_grade_type) {
	$test_grade_type_info = get_test_grade_type_info($test_grade_type);
	$test_grades = get_test_grades($attendance_id);
	$test_grade_maximum_value_total += $test_grade_type_info['tgtype_maximum_value'];
	$test_grade_total += $test_grades[$test_grade_type_info['tgtype_name']];
	$test_averages_total += $test_averages[$test_grade_type];
//print_r($test_grade_type_info);
//print_r($test_grades);
//print_r($test_averages);
echo " AVGTOT: $test_averages_total";

	echo "<tr>\r\n<td>" . $test_grade_type_info['tgtype_name'] . "</td>\r\n" .
	 		"<td>" . $test_grade_type_info['tgtype_maximum_value'] . "</td>\r\n" .
			"<td>" . $test_grades[$test_grade_type_info['tgtype_name']] . "</td>" .
			"<td>" . round((float)$test_averages[$test_grade_type],1) . "</td>\r\n</tr>";
}
?>
		</tbody>
		<tfoot>
			<tr>
				<td>Total</td>
				<td><?php echo $test_grade_maximum_value_total; ?></td>
				<td><?php echo $test_grade_total; ?></td>
				<td><?php echo round((float)$test_averages_total,1); ?></td>
			</tr>
		</tfoot>
	</table>

<?php
}
endforeach;  // End of loop for current classes
?>

</body>
</html>
