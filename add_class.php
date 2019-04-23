<?php
// Start the session
session_start();

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Create a New Class</title>
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

<h1>Create a New Class:</h1>
<form id="addclass" action="submit_newclass.php" method="post">
	<fieldset>
		<legend>Class Details</legend>
		<ol>
			<li>
				<label for="location">Location:</label>
				<select id="location" name="location">
<?php
/* List locations */
$locations = get_locations();
foreach ($locations as $location)
{
	echo "<option value=\"" . $location['location_id'] . "\">" . $location['location_name'] . "</option>";
}
?>
		    </select>
			</li>
			<li>
				<label for="dow">Day of the Week:</label>
				<select id="dow" name="dow">
<?php
/* List days of the week */
$days_of_week = get_days_of_week();
foreach ($days_of_week as $day_of_week)
{
	echo "<option value=\"" . $day_of_week['dow_id'] . "\">" . $day_of_week['dow_name'] . "</option>";
}
?>
		    </select>
			</li>
			<li>
				<label for="ctype">Class Type:</label>
				<select id="ctype" name="ctype">
<?php
/* List class types */
$class_types = get_class_types();
foreach ($class_types as $class_type)
{
	echo "<option value=\"" . $class_type['ctype_id'] . "\">" . $class_type['ctype_name'] . "</option>";
}
?>
		    </select>
			</li>
			<li>
				<label for="level">Class Level:</label>
				<select id="level" name="level">
<?php
/* List levels */
$levels = get_levels();
foreach ($levels as $level)
{
	echo "<option value=\"" . $level['level_id'] . "\">" . $level['level_name'] . "</option>";
}
?>
		    </select>
			</li>
			<li>
				<label for="class_time">Class Time:</label>
				<input id="class_time" name="class_time" type="time" step="300" required />
			</li>
			<li>
				<label for="start_date">Start Date:</label>
				<input id="start_date" name="start_date" type="date" value="2019-04-01" required />
			</li>
			<li>
				<label for="end_date">End Date:</label>
				<input id="end_date" name="end_date" type="date" />
				<span class="hint"> (Leave blank if no end date yet)</span>
			</li>
		</ol>
	</fieldset>
	<fieldset>
		<legend>Roster Details</legend>
		<ol>
			<li>
				<label for="teacher">Teacher:</label>
				<select id="teacher" name="teacher">
						<option value="Ahleen">Ahleen</option>
						<option value="Chris">Chris</option>
						<option value="Jill">Jill</option>
						<option value="Shayne">Shayne</option>
				</select>
			</li>
			<li>
				<label for="students">Students:</label>
				<select multiple size=10 id="teacher" name="teacher">
						<option value="1">First Student</option>
						<option value="2">Second Student</option>
						<option value="3">Third Student</option>
						<option value="4">Fourth Student</option>
						<option value="5">Fifth Student</option>
						<option value="6">Sixth Student</option>
						<option value="7">Seventh Student</option>
						<option value="8">Eighth Student</option>
						<option value="9">Ninth Student</option>
						<option value="10">Tenth Student</option>
						<option value="11">Eleventh Student</option>
						<option value="12">Twelfth Student</option>
						<option value="13">Thirteenth Student</option>
						<option value="14">Fourteenth Student</option>
						<option value="15">Fifteenth Student</option>
						<option value="16">Sixteenth Student</option>
				</select>
				<span class="hint"> (Ctrl-click or Cmd-click to select multiple students)</span>
			</li>
		</ol>
		<input type="submit" />
	</fieldset>

</form>
</body>
</html>
