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
<form action="submit_newclass.php" method="post">
	<fieldset>
		<legend>Class Details</legend>
		<ol>
			<li>
				<label for="location">Location:</label>
				<select id="location" name="location">
		        <option value="Anan">Anan</option>
		        <option value="Happy">Happy</option>
		        <option value="Minami-showa-ko">Minami-showa-ko</option>
		    </select>
			</li>
			<li>
				<label for="dow">Day of the Week:</label>
				<select id="dow" name="dow">
		        <option value="Monday">Monday</option>
		        <option value="Tuesday">Tuesday</option>
						<option value="Wednesday">Wednesday</option>
						<option value="Thursday">Thursday</option>
						<option value="Friday">Friday</option>
						<option value="Saturday">Saturday</option>
				    <option value="Sunday">Sunday</option>
		    </select>
			</li>
			<li>
				<label for="ctype">Class Type:</label>
				<select id="ctype" name="ctype">
		        <option value="Preschool Group">Preschool Group</option>
		        <option value="Preschool Private">Preschool Private</option>
		        <option value="Elementary Group">Elementary Group</option>
						<option value="Elementary Private">Elementary Private</option>
		    </select>
			</li>
			<li>
				<label for="level">Class Level:</label>
				<select id="level" name="level">
		        <option value="All Stars 1">All Stars 1</option>
		        <option value="All Stars 2">All Stars 2</option>
		        <option value="All Stars 3">All Stars 3</option>
						<option value="All Stars 4">All Stars 4</option>
		    </select>
			</li>
			<li>
				<label for="class_time">Class Time:</label>
				<input id="class_time" name="class_time" type="time" step="300" required />
			</li>
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
				<label for="start_date">Start Date:</label>
				<input id="start_date" name="start_date" type="date" value="2019-04-01" required />
			</li>
			<li>
				<label for="end_date">End Date:</label>
				<input id="end_date" name="end_date" type="date" />
				<span> (leave blank if no end date yet)</span>
			</li>
		</ol>
		<input type="submit" />
	</fieldset>

</form>
</body>
</html>
