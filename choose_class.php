<?php
// Continue the session
session_start();

// Get session variables
$is_makeup = $_SESSION["is_makeup"] = $_GET["is_makeup"] ?? $_SESSION["is_makeup"] ?? FALSE;
$is_test = $_SESSION["is_test"] = $_GET["is_test"] ?? $_SESSION["is_test"] ?? FALSE;
$location_id = $_SESSION["location_id"] = $_GET["lid"] ?? $_SESSION["location_id"] ?? NULL;
$original_date = $_SESSION["original_date"] = $_SESSION["original_date"] ?? NULL;
$original_class_id = $_SESSION["original_class_id"] = $_SESSION["original_class_id"] ?? NULL;
$teacher_id = $_SESSION["teacher_id"] = $_GET["tid"] ?? $_SESSION["teacher_id"] ?? NULL;
$student_id = $_SESSION["student_id"] = $_GET["sid"] ?? $_SESSION["student_id"] ?? NULL;
$date = $_GET["date"];
$dow = date("l", strtotime($date));

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose a Class</title>
</head>
<body>
<?php
/* Debugging information
echo (empty($is_makeup) ? "<p>Not makeup</p>" : "<p>Makeup</p>");
echo (empty($teacher_id) ? "<p>No teacher set</p>" : "<p>Teacher set</p>");
echo "<p>Teacher ID: $teacher_id</p>";
echo "<p>Date: $date</p>";
echo "<p>Day of the week: $dow</p>";
*/
?>

<h1>Choose a class:</h1>
<ul>

<?php

// Entering regular attendance information
if(empty($is_makeup) && empty($is_test)) {
    if ($classes = get_classes_for_teacher($teacher_id,$dow,$date)) {
    	foreach($classes as $class)
    	{
          //var_dump($class);
        	echo "<li><a href=\"enter_data.php?tid=" . htmlspecialchars($class['teacher_id'], ENT_QUOTES, 'UTF-8') . "&cid=" .
          htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . "&date=" . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "\">" .
          htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($class['dow_name'], ENT_QUOTES, 'UTF-8') . " - " .
          htmlspecialchars($class['class_time'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($class['teacher_name'], ENT_QUOTES, 'UTF-8') . " - " .
          htmlspecialchars($class['level_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
    	}
    }
    else {
    	echo "<p>No classes found.</p>";
    }
}

// *** NEXT ***

// Get the class id for a future absence date
elseif (empty($original_class_id) && empty($is_test)) {
	if ($classes = get_classes_for_student($student_id,$dow,$date)) {
		foreach ($classes as $class)
		{
				//var_dump($row);
				echo "<li><a href=\"choose_date.php?is_makeup=true&sid=" . htmlspecialchars($class['person_id'], ENT_QUOTES, 'UTF-8') .
	      "&ocid=" . htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . "&date=" . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "\">" .
	      htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . " - " .
	      htmlspecialchars($class['dow_name'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($class['time'], ENT_QUOTES, 'UTF-8') . " - " .
	      htmlspecialchars($class['level_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
		}
	}
	else {
		echo "<p>No classes found.</p>";
	}
}
// Continue makeup information
elseif(empty($is_test)) {
	if ($classes = get_classes_for_location($location_id,$dow,$date)) {
		foreach ($classes as $class)
    {
      //var_dump($row);
      echo "<li><a href=\"enter_makeup.php?mcid=" .
      htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . "&date=" . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "\">" .
      htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($class['dow_name'], ENT_QUOTES, 'UTF-8') . " - " .
      htmlspecialchars($class['time'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') . " - " .
      htmlspecialchars($class['level_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
    }
  }
  else {
    echo "<p>No classes found.</p>";
  }
	echo <<<EOT
	<h3>Or you can choose to create a standalone makeup lesson:</h3>
	<form action="enter_makeup.php" method="get">
	  <div>
			<label for="makeup_time">Time of makeup lesson:</label>
	    <input type="time" id="makeup_time" name="time" step="300" />
			<select id="reading/writing" name="mtid">
				<option value="">Select a teacher</option>
EOT;
	$teachers = get_all_teachers();
	foreach ($teachers as $teacher) {
		echo "<option value=\"" . htmlspecialchars($teacher['person_id'], ENT_QUOTES, 'UTF-8') . "\">" .
		htmlspecialchars($teacher['given_name_r'], ENT_QUOTES, 'UTF-8') . " " .
		htmlspecialchars($teacher['family_name_r'], ENT_QUOTES, 'UTF-8') . "</option>";
	}
echo <<<EOT
			</select>
	    <input type="hidden" name="standalone" value="true" />
	    <input type="hidden" name="date" value="$date" />
	  </div>
	  <input type="submit" />
	</form>
EOT;
}

// enter test results
else {
	if ($classes = get_classes_for_teacher($teacher_id,$dow,$date)) {
		foreach($classes as $class)
		{
				//var_dump($class);
				echo "<li><a href=\"enter_test_results.php?tid=" . htmlspecialchars($class['teacher_id'], ENT_QUOTES, 'UTF-8') . "&cid=" .
				htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . "&date=" . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "\">" .
				htmlspecialchars($class['class_id'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($class['dow_name'], ENT_QUOTES, 'UTF-8') . " - " .
				htmlspecialchars($class['class_time'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($class['teacher_name'], ENT_QUOTES, 'UTF-8') . " - " .
				htmlspecialchars($class['level_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
		}
	}
	else {
		echo "<p>No classes found.</p>";
	}
}
?>

</ul>
</body>
</html>
