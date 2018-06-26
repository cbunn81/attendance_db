<?php
// Start the session
session_start();

// Get session variables
$teacher_id = $_SESSION["teacher_id"] = $_GET["tid"] ?? $_SESSION["teacher_id"] ?? NULL;
$date = $_SESSION["date"] = $_GET["date"] ?? $_SESSION["date"] ?? NULL;
$dow = date("l", strtotime($date));
$class_id = $_SESSION["class_id"] = $_GET["cid"] ?? $_SESSION["class_id"] ?? NULL;

require_once('includes/model.php');

$test_name = get_test_name($date);
?>

<!DOCTYPE html>
<html>
<head>
	<title>Enter Test Results for <?= $test_name ?></title>
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

<h1>Enter <?= $test_name ?> Results:</h1>

<?php if(is_graded_class($class_id)): ?>

<form action="submit_test_results.php" method="post">
<table>
	<thead>
		<tr>
			<td>Student ID</td>
			<td>Student Name</td>
			<td>Present?</td>
<?php
    $test_grade_types = get_test_grade_types();
		foreach ($test_grade_types as $test_grade_type) {
      echo "<td>$test_grade_type</td>";
	  }
?>
		</tr>
	</thead>
	<tbody>

<?php

if ($students = get_students_for_class($class_id, $teacher_id, $date)) {
	foreach ($students as $student)
	{
		// unset grades array so that previous grade data doesn't get displayed for absent students
		$test_grades = [];
		// get class instance for the class id and date
		$cinstance_id = get_class_instance($class_id, $date);
		// if a test grade instance exists, then there must have been some test grade data entered
		if($cinstance_id) {
			// select the test grade data (present and notes) using cinstance_id and student_id
			$attendance = get_attendance($cinstance_id, $student['student_id']);
			$present = $attendance['present'];
			$test_grades = get_test_grades($attendance['attendance_id']);
				// create HTML form with default values from db query
					// if it's a graded class
						// select test_grades
							// add form fields for test_grades with default values from db query
		}
		// display form, using existing values if they are set from above
		echo "<tr><td>" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "</td>
				<td>" . htmlspecialchars($student['student_name'], ENT_QUOTES, 'UTF-8') . "</td>
				<td><input type=\"hidden\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][update]\" value=\"" .
						(isset($present) ? 1 : 0) . "\" />
						<input type=\"hidden\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][present]\" value=\"0\" />
				    <input type=\"checkbox\" id=\"present\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][present]\" value=\"1\" " .
						((!empty($present)) ? "checked" : "") . "/></td>";
		echo "<td><select id=\"listening\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][listening]\">
					<option value=\"0\"" . ((!isset($test_grades['listening'])) ? "selected" : "") . ">0</option>
					<option value=\"1\"" . (isset($test_grades['listening']) && ($test_grades['listening'] == '1') ? "selected" : "") . ">1</option>
					<option value=\"2\"" . (isset($test_grades['listening']) && ($test_grades['listening'] == '2') ? "selected" : "") . ">2</option>
					<option value=\"3\"" . (isset($test_grades['listening']) && ($test_grades['listening'] == '3') ? "selected" : "") . ">3</option>
					<option value=\"4\"" . (isset($test_grades['listening']) && ($test_grades['listening'] == '4') ? "selected" : "") . ">4</option>
					<option value=\"5\"" . (isset($test_grades['listening']) && ($test_grades['listening'] == '5') ? "selected" : "") . ">5</option>
				</select></td>
			<td><select id=\"reading/writing\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][reading/writing]\">
					<option value=\"0\"" . ((!isset($test_grades['reading/writing'])) ? "selected" : "") . ">0</option>
					<option value=\"1\"" . (isset($test_grades['reading/writing']) && ($test_grades['reading/writing'] == '1') ? "selected" : "") . ">1</option>
					<option value=\"2\"" . (isset($test_grades['reading/writing']) && ($test_grades['reading/writing'] == '2') ? "selected" : "") . ">2</option>
					<option value=\"3\"" . (isset($test_grades['reading/writing']) && ($test_grades['reading/writing'] == '3') ? "selected" : "") . ">3</option>
					<option value=\"4\"" . (isset($test_grades['reading/writing']) && ($test_grades['reading/writing'] == '4') ? "selected" : "") . ">4</option>
					<option value=\"5\"" . (isset($test_grades['reading/writing']) && ($test_grades['reading/writing'] == '5') ? "selected" : "") . ">5</option>
				</select></td>
			<td><select id=\"handwriting\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][handwriting]\">
					<option value=\"0\"" . ((!isset($test_grades['handwriting'])) ? "selected" : "") . ">0</option>
					<option value=\"1\"" . (isset($test_grades['handwriting']) && ($test_grades['handwriting'] == '1') ? "selected" : "") . ">1</option>
					<option value=\"2\"" . (isset($test_grades['handwriting']) && ($test_grades['handwriting'] == '2') ? "selected" : "") . ">2</option>
					<option value=\"3\"" . (isset($test_grades['handwriting']) && ($test_grades['handwriting'] == '3') ? "selected" : "") . ">3</option>
					<option value=\"4\"" . (isset($test_grades['handwriting']) && ($test_grades['handwriting'] == '4') ? "selected" : "") . ">4</option>
					<option value=\"5\"" . (isset($test_grades['handwriting']) && ($test_grades['handwriting'] == '5') ? "selected" : "") . ">5</option>
				</select></td>
			<td><select id=\"intonation\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][intonation]\">
					<option value=\"0\"" . ((!isset($test_grades['intonation'])) ? "selected" : "") . ">0</option>
					<option value=\"1\"" . (isset($test_grades['intonation']) && ($test_grades['intonation'] == '1') ? "selected" : "") . ">1</option>
					<option value=\"2\"" . (isset($test_grades['intonation']) && ($test_grades['intonation'] == '2') ? "selected" : "") . ">2</option>
				</select></td>
      <td><select id=\"pronunciation\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][pronunciation]\">
					<option value=\"0\"" . ((!isset($test_grades['pronunciation'])) ? "selected" : "") . ">0</option>
					<option value=\"1\"" . (isset($test_grades['pronunciation']) && ($test_grades['pronunciation'] == '1') ? "selected" : "") . ">1</option>
					<option value=\"2\"" . (isset($test_grades['pronunciation']) && ($test_grades['pronunciation'] == '2') ? "selected" : "") . ">2</option>
			 </select></td>
      <td><select id=\"speed\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][speed]\">
  				<option value=\"0\"" . ((!isset($test_grades['speed'])) ? "selected" : "") . ">0</option>
  				<option value=\"1\"" . (isset($test_grades['speed']) && ($test_grades['speed'] == '1') ? "selected" : "") . ">1</option>
  				<option value=\"2\"" . (isset($test_grades['speed']) && ($test_grades['speed'] == '2') ? "selected" : "") . ">2</option>
  		 </select></td>
      <td><select id=\"accuracy\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][accuracy]\">
    			<option value=\"0\"" . ((!isset($test_grades['accuracy'])) ? "selected" : "") . ">0</option>
    			<option value=\"1\"" . (isset($test_grades['accuracy']) && ($test_grades['accuracy'] == '1') ? "selected" : "") . ">1</option>
    			<option value=\"2\"" . (isset($test_grades['accuracy']) && ($test_grades['accuracy'] == '2') ? "selected" : "") . ">2</option>
    	 </select></td>
      <td><select id=\"confidence\" name=\"test_results[" . htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') . "][confidence]\">
      		<option value=\"0\"" . ((!isset($test_grades['confidence'])) ? "selected" : "") . ">0</option>
      		<option value=\"1\"" . (isset($test_grades['confidence']) && ($test_grades['confidence'] == '1') ? "selected" : "") . ">1</option>
      		<option value=\"2\"" . (isset($test_grades['confidence']) && ($test_grades['confidence'] == '2') ? "selected" : "") . ">2</option>
       </select></td>";
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

<?php else: ?>
  <p>This is not a graded class.</p>
<?php endif; ?>
</body>
</html>
