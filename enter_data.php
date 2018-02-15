<?php
// Start the session
session_start();

// Get session variables
$teacher_id = $_SESSION["teacher_id"] = $_GET["tid"] ?? $_SESSION["teacher_id"] ?? NULL;
$date = $_SESSION["date"] = $_GET["date"] ?? $_SESSION["date"] ?? NULL;
$dow = date("l", strtotime($date));
$class_id = $_SESSION["class_id"] = $_GET["cid"] ?? $_SESSION["class_id"] ?? NULL;

require_once('includes/model.php');
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

if ($students = get_students_for_class($class_id, $teacher_id, $date)) {
	foreach ($students as $student)
	{
		// unset grades array so that previous grade data doesn't get displayed for absent students
		$grades = [];
		// get class instance for the class id and date
		$cinstance_id = get_class_instance($class_id, $date);
		// if a class instance exists, then there must have been some attendance data entered
		if($cinstance_id) {
			// select the attendance data (present and notes) using cinstance_id and student_id
			$attendance = get_attendance($cinstance_id, $student['student_id']);
			$present = $attendance['present'];
			$notes = $attendance['notes'];
			if(is_graded_class($class_id)) {
				$grades = get_grades($attendance['attendance_id']);
			}
				// create HTML form with default values from db query
					// if it's a graded class
						// select grades
							// add form fields for grades with default values from db query
		}
		// display form, using existing values if they are set from above
		echo "<tr><td>" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "</td>
				<td>" . htmlspecialchars($student['student_name'], ENT_QUOTES, 'UTF-8') . "</td>
				<td><input type=\"hidden\" name=\"adata[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][update]\" value=\"" .
						(isset($present) ? 1 : 0) . "\" />
						<input type=\"hidden\" name=\"adata[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][present]\" value=\"0\" />
				    <input type=\"checkbox\" id=\"present\" name=\"adata[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][present]\" value=\"1\" " .
						((!empty($present)) ? "checked" : "") . "/></td>";
		if(is_graded_class($class_id)) {
			echo "<td><select id=\"speaking\" name=\"adata[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][speaking]\">
						<option value=\"0\"" . ((!isset($grades['speaking'])) ? "selected" : "") . ">0</option>
						<option value=\"1\"" . (isset($grades['speaking']) && ($grades['speaking'] == '1') ? "selected" : "") . ">1</option>
						<option value=\"2\"" . (isset($grades['speaking']) && ($grades['speaking'] == '2') ? "selected" : "") . ">2</option>
						<option value=\"3\"" . (isset($grades['speaking']) && ($grades['speaking'] == '3') ? "selected" : "") . ">3</option>
						<option value=\"4\"" . (isset($grades['speaking']) && ($grades['speaking'] == '4') ? "selected" : "") . ">4</option>
						<option value=\"5\"" . (isset($grades['speaking']) && ($grades['speaking'] == '5') ? "selected" : "") . ">5</option>
					</select></td>
				<td><select id=\"listening\" name=\"adata[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][listening]\">
						<option value=\"0\"" . ((!isset($grades['listening'])) ? "selected" : "") . ">0</option>
						<option value=\"1\"" . (isset($grades['listening']) && ($grades['listening'] == '1') ? "selected" : "") . ">1</option>
						<option value=\"2\"" . (isset($grades['listening']) && ($grades['listening'] == '2') ? "selected" : "") . ">2</option>
						<option value=\"3\"" . (isset($grades['listening']) && ($grades['listening'] == '3') ? "selected" : "") . ">3</option>
						<option value=\"4\"" . (isset($grades['listening']) && ($grades['listening'] == '4') ? "selected" : "") . ">4</option>
						<option value=\"5\"" . (isset($grades['listening']) && ($grades['listening'] == '5') ? "selected" : "") . ">5</option>
					</select></td>
				<td><select id=\"reading\" name=\"adata[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][reading]\">
						<option value=\"0\"" . ((!isset($grades['reading'])) ? "selected" : "") . ">0</option>
						<option value=\"1\"" . (isset($grades['reading']) && ($grades['reading'] == '1') ? "selected" : "") . ">1</option>
						<option value=\"2\"" . (isset($grades['reading']) && ($grades['reading'] == '2') ? "selected" : "") . ">2</option>
						<option value=\"3\"" . (isset($grades['reading']) && ($grades['reading'] == '3') ? "selected" : "") . ">3</option>
						<option value=\"4\"" . (isset($grades['reading']) && ($grades['reading'] == '4') ? "selected" : "") . ">4</option>
						<option value=\"5\"" . (isset($grades['reading']) && ($grades['reading'] == '5') ? "selected" : "") . ">5</option>
					</select></td>
				<td><select id=\"writing\" name=\"adata[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][writing]\">
						<option value=\"0\"" . ((!isset($grades['writing'])) ? "selected" : "") . ">0</option>
						<option value=\"1\"" . (isset($grades['writing']) && ($grades['writing'] == '1') ? "selected" : "") . ">1</option>
						<option value=\"2\"" . (isset($grades['writing']) && ($grades['writing'] == '2') ? "selected" : "") . ">2</option>
						<option value=\"3\"" . (isset($grades['writing']) && ($grades['writing'] == '3') ? "selected" : "") . ">3</option>
						<option value=\"4\"" . (isset($grades['writing']) && ($grades['writing'] == '4') ? "selected" : "") . ">4</option>
						<option value=\"5\"" . (isset($grades['writing']) && ($grades['writing'] == '5') ? "selected" : "") . ">5</option>
					</select></td>
				<td><select id=\"behavior\" name=\"adata[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][behavior]\">
						<option value=\"0\"" . ((!isset($grades['behavior'])) ? "selected" : "") . ">0</option>
						<option value=\"1\"" . (isset($grades['behavior']) && ($grades['behavior'] == '1') ? "selected" : "") . ">1</option>
						<option value=\"2\"" . (isset($grades['behavior']) && ($grades['behavior'] == '2') ? "selected" : "") . ">2</option>
						<option value=\"3\"" . (isset($grades['behavior']) && ($grades['behavior'] == '3') ? "selected" : "") . ">3</option>
						<option value=\"4\"" . (isset($grades['behavior']) && ($grades['behavior'] == '4') ? "selected" : "") . ">4</option>
						<option value=\"5\"" . (isset($grades['behavior']) && ($grades['behavior'] == '5') ? "selected" : "") . ">5</option>
					</select></td>";
		}
				echo "<td><textarea name=\"adata[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][notes]\" cols=\"30\" rows=\"2\">" .
					((isset($notes)) ? $notes : "") . "</textarea></td>";
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
