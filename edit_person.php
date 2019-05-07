<?php
// Start the session
session_start();
// Get the Student ID from the URL or from a Session Variable
$person_id = $_SESSION["person_id"] = $_GET["pid"] ?? $_SESSION["person_id"] ?? NULL;

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Edit a Person</title>
	<link rel="stylesheet" type="text/css" media="screen" href="css/main.css">
</head>
<body>

<?php

if(isset($person_id)) {
	$person_info = get_person_info($person_id);
	echo "<pre>";
	print_r($person_info);
	echo "</pre>";
	echo "<h1>Edit " . htmlspecialchars($person_info['given_name_r'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($person_info['family_name_r'], ENT_QUOTES, 'UTF-8') . "'s Data:</h1>";
}
else {
	echo "No person has been selected, please try again.";
}

?>

</body>
</html>
