<?php
// Continue the session
session_start();

// Get session variables
$is_makeup = $_SESSION["is_makeup"] = $_GET["is_makeup"] ?? $_SESSION["is_makeup"] ?? FALSE;
$location_id = $_SESSION["location_id"] = $_GET["lid"] ?? $_SESSION["location_id"] ?? NULL;
$original_date = $_SESSION["original_date"] = $_SESSION["original_date"] ?? NULL;
$original_class_id = $_SESSION["original_class_id"] = $_SESSION["original_class_id"] ?? NULL;
$teacher_id = $_SESSION["teacher_id"] = $_GET["tid"] ?? $_SESSION["teacher_id"] ?? NULL;
$student_id = $_SESSION["student_id"] = $_GET["sid"] ?? $_SESSION["student_id"] ?? NULL;
$date = $_GET["date"];
$dow = date("l", strtotime($date));

require_once('../../config/db.inc.php');
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
if(empty($is_makeup)) {
    $stmt = $pdo->prepare("SELECT c.class_id, p.person_id, d.dow_name, left(c.class_time::text, 5) as time, l.level_name, concat_ws(' ',p.given_name_r, p.family_name_r) as name
      	FROM classes c
      	INNER JOIN days_of_week d ON c.dow_id = d.dow_id
      	INNER JOIN roster r ON c.class_id = r.class_id AND r.person_id = :teacher_id AND (d.dow_name = :dow OR d.dow_name = 'Flex')
      	INNER JOIN levels l ON c.level_id = l.level_id
      	INNER JOIN people p ON r.person_id = p.person_id");
    $stmt->execute(['teacher_id' => $teacher_id, 'dow' => $dow]);
    if ($stmt->rowCount()) {
    	while ($row = $stmt->fetch())
    	{
          //var_dump($row);
        	echo "<li><a href=\"enter_data.php?tid=" . htmlspecialchars($row['person_id'], ENT_QUOTES, 'UTF-8') . "&cid=" .
          htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . "&date=" . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "\">" .
          htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['dow_name'], ENT_QUOTES, 'UTF-8') . " - " .
          htmlspecialchars($row['time'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . " - " .
          htmlspecialchars($row['level_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
    	}
    }
    else {
    	echo "<p>No classes found.</p>";
    }
}
// Start makeup information
/*
elseif (empty($original_date)) {
  $stmt = $pdo->prepare("SELECT c.class_id, p.person_id, d.dow_name, left(c.class_time::text, 5) as time, l.level_name, concat_ws(' ',p.given_name_r, p.family_name_r) as name
      FROM classes c
      INNER JOIN days_of_week d ON c.dow_id = d.dow_id
      INNER JOIN roster r ON c.class_id = r.class_id AND d.dow_name = :dow
      INNER JOIN levels l ON c.level_id = l.level_id
      INNER JOIN people p ON r.person_id = p.person_id
      INNER JOIN person_types pt ON pt.ptype_name = 'Staff'
      INNER JOIN people2person_types p2pt ON p2pt.ptype_id = pt.ptype_id AND p2pt.person_id = p.person_id");
  $stmt->execute(['dow' => $dow]);
  if ($stmt->rowCount()) {
    while ($row = $stmt->fetch())
    {
        //var_dump($row);
        echo "<li><a href=\"choose_student.php?tid=" . htmlspecialchars($row['person_id'], ENT_QUOTES, 'UTF-8') . "&ocid=" .
        htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . "&date=" . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "\">" .
        htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['dow_name'], ENT_QUOTES, 'UTF-8') . " - " .
        htmlspecialchars($row['time'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . " - " .
        htmlspecialchars($row['level_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
    }
  }
  else {
    echo "<p>No classes found.</p>";
  }
}
*/
// Get the class id for a future absence date
elseif (empty($original_class_id)) {
	$stmt = $pdo->prepare("SELECT c.class_id, p.person_id, d.dow_name, left(c.class_time::text, 5) as time, l.level_name, concat_ws(' ',p.given_name_r, p.family_name_r) as name
			FROM classes c
			INNER JOIN days_of_week d ON c.dow_id = d.dow_id AND d.dow_name = :dow
			INNER JOIN roster r ON c.class_id = r.class_id AND r.person_id = :student_id
			INNER JOIN levels l ON c.level_id = l.level_id
			INNER JOIN people p ON r.person_id = p.person_id");
	$stmt->execute(['student_id' => $student_id, 'dow' => $dow]);
	if ($stmt->rowCount()) {
		while ($row = $stmt->fetch())
		{
				//var_dump($row);
				echo "<li><a href=\"choose_date.php?is_makeup=true&sid=" . htmlspecialchars($row['person_id'], ENT_QUOTES, 'UTF-8') .
	      "&ocid=" . htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . "&date=" . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "\">" .
	      htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . " - " .
	      htmlspecialchars($row['dow_name'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['time'], ENT_QUOTES, 'UTF-8') . " - " .
	      htmlspecialchars($row['level_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
		}
	}
	else {
		echo "<p>No classes found.</p>";
	}
}
// Continue makeup information
else {
  $stmt = $pdo->prepare("SELECT c.class_id, p.person_id, d.dow_name, left(c.class_time::text, 5) as time, l.level_name, concat_ws(' ',p.given_name_r, p.family_name_r) as name
      FROM classes c
      INNER JOIN days_of_week d ON c.dow_id = d.dow_id AND d.dow_name = :dow
      INNER JOIN roster r ON c.class_id = r.class_id AND c.location_id = :location_id
      INNER JOIN levels l ON c.level_id = l.level_id
      INNER JOIN people p ON r.person_id = p.person_id
      INNER JOIN person_types pt ON pt.ptype_name = 'Staff'
      INNER JOIN people2person_types p2pt ON p2pt.ptype_id = pt.ptype_id AND p2pt.person_id = p.person_id");
  $stmt->execute(['dow' => $dow, 'location_id' => $location_id]);
  if ($stmt->rowCount()) {
    while ($row = $stmt->fetch())
    {
        //var_dump($row);
        echo "<li><a href=\"enter_makeup.php?mcid=" .
        htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . "&date=" . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "\">" .
        htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['dow_name'], ENT_QUOTES, 'UTF-8') . " - " .
        htmlspecialchars($row['time'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . " - " .
        htmlspecialchars($row['level_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
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
