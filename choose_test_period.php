<?php
// Continue the session
session_start();

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Choose a Test Period</title>
</head>
<body>
<h1>Choose a test period:</h1>
<ul>

<?php
$test_periods = get_all_tests();
foreach ($test_periods as $test_period)
{
    echo "<li><a href=\"list_students.php?testid=" . htmlspecialchars($test_period['test_id'], ENT_QUOTES, 'UTF-8') . "\">" .
    htmlspecialchars($test_period['test_name'], ENT_QUOTES, 'UTF-8') . " - " .
    htmlspecialchars($test_period['start_date'], ENT_QUOTES, 'UTF-8') . " to " .
    htmlspecialchars($test_period['end_date'], ENT_QUOTES, 'UTF-8') . "</a></li>\r\n";
}
?>

</ul>

<h2>Or you can choose a custom date range:</h2>
<form action="list_students.php" method="get">
  <div>
		<div>
    	<label for="start_date">Start Date:</label>
    	<input type="date" id="start_date" name="start_date" required />
		</div>
		<div>
			<label for="end_date">End Date:</label>
    	<input type="date" id="end_date" name="end_date" required />
		</div>

  </div>
  <input type="submit" />
</form>

</body>
</html>
