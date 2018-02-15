<?php
// Continue the session
session_start();
$student_id = $_SESSION["student_id"] = $_GET["sid"] ?? $_SESSION["student_id"] ?? NULL;

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose an Absence</title>
</head>
<body>
<h1>Choose an absence:</h1>
<h3>Here are the absences not already linked to a makeup lesson:</h3>
<ul>

<?php
// show previous absences
// **** Make sure to exclude any absences that are already linked to makeups! -DONE
  // makeup.original_cinstance_id != attendance.cinstance_id
if ($absences = get_student_absences($student_id)) {
	foreach ($absences as $absence)
  {
      echo "<li><a href=\"choose_date.php?is_makeup=true&sid=" . htmlspecialchars($absence['student_id'], ENT_QUOTES, 'UTF-8') .
      "&ocid=" . htmlspecialchars($absence['class_id'], ENT_QUOTES, 'UTF-8') . "&date=" . htmlspecialchars($absence['cinstance_date'], ENT_QUOTES, 'UTF-8') . "\">" .
      htmlspecialchars($absence['cinstance_date'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($absence['class_id'], ENT_QUOTES, 'UTF-8') . " - " .
      htmlspecialchars($absence['dow_name'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($absence['time'], ENT_QUOTES, 'UTF-8') . " - " .
      htmlspecialchars($absence['level_name'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
  }
}
// no absences
else {
  echo "<p>No previous absences, please choose a future date below.</p>";
}
// show a few future classes - difficult because no class instances exist yet, so each would need to be created
// Maybe, instead, just allow the entry of the specific date of the future absence, then confirm it falls on the correct day of the week.
// Use HTML5 date input


?>

</ul>

<h3>Or you can choose a date in the future:</h3>
<form action="choose_class.php" method="get">
  <div>
    <label for="future_absence">Date of future absence:</label>
    <input type="date" id="future_absence" name="date">
<?php
    echo "<input type=\"hidden\" name=\"is_makeup\" value=\"true\"";
    echo "<input type=\"hidden\" name=\"sid\" value=\"$student_id\"";
?>
  </div>
  <input type="submit" />
</form>

</body>
</html>
