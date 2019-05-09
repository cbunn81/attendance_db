<?php
// Start the session
session_start();
// Get the Student ID from the URL (editing a person) or set to NULL if nothing passed (adding a new person)
$person_id = $_SESSION["person_id"] = $_GET["pid"] ?? NULL;

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
<?php

if(!isset($person_id)) {
	echo "<title>Add a New Person</title>";
}
else {
	echo "<title>Edit a Person</title>";
}
?>
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
	echo "<h1>Add a New Person to the System</h1>";
}

?>

<form id="person_data" class="addinfo" action="submit_person_data.php" method="post">
	<input type="hidden" name="update" value="<?= isset($person_id) ? "true" : "false" ?>" />
	<input type="hidden" name="person_id" value="<?= isset($person_id) ? htmlspecialchars($person_id, ENT_QUOTES, 'UTF-8') : "" ?>" />
	<fieldset>
		<legend>Basic Details</legend>
		<ol>
			<li>
				<label for="person_type">Person Type:</label>
				<!-- Staff vs. Student -->
				<select id="person_type" name="person_type">
<?php
/* List person types */
$person_types = get_person_types();
foreach ($person_types as $person_type)
{
	echo "<option value=\"" . htmlspecialchars($person_type['ptype_id'], ENT_QUOTES, 'UTF-8') . "\"" .
														(isset($person_info['ptype_id']) && ($person_type['ptype_id'] == $person_info['ptype_id']) ? "selected" : "") . ">" .
														htmlspecialchars($person_type['ptype_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
}
?>
				</select>
			</li>
			<li>
				<label for="family_name_k">Family Name <span class="hint">(in Kanji/Kana)</span>:</label>
				<input type="text" id="family_name_k" name="family_name_k" size="30" required value="<?= isset($person_info['family_name_k']) ? htmlspecialchars($person_info['family_name_k'], ENT_QUOTES, 'UTF-8') : "" ?>"/>
			</li>
			<li>
				<label for="given_name_k">Given Name <span class="hint">(in Kanji/Kana)</span>:</label>
				<input type="text" id="given_name_k" name="given_name_k" size="30" required  value="<?= isset($person_info['given_name_k']) ? htmlspecialchars($person_info['given_name_k'], ENT_QUOTES, 'UTF-8') : "" ?>"/>
			</li>
			<li>
				<label for="family_name_k">Family Name <span class="hint">(in Romaji)</span>:</label>
				<input type="text" id="family_name_r" name="family_name_r" size="30" required  value="<?= isset($person_info['family_name_r']) ? htmlspecialchars($person_info['family_name_r'], ENT_QUOTES, 'UTF-8') : "" ?>"/>
			</li>
			<li>
				<label for="given_name_k">Given Name <span class="hint">(in Romaji)</span>:</label>
				<input type="text" id="given_name_r" name="given_name_r" size="30" required  value="<?= isset($person_info['given_name_r']) ? htmlspecialchars($person_info['given_name_r'], ENT_QUOTES, 'UTF-8') : "" ?>"/>
			</li>
			<li>
				<label for="dob">Date of Birth:</label>
				<input id="dob" name="dob" type="date"  value="<?= isset($person_info['dob']) ? htmlspecialchars($person_info['dob'], ENT_QUOTES, 'UTF-8') : "" ?>"/>
				<span class="hint"> (Optional)</span>
			</li>
			<li>
				<label for="gender">Gender:</label>
				<select id="gender" name="gender">
<?php
/* List genders */
$genders = get_genders();
foreach ($genders as $gender)
{
	echo "<option value=\"" . htmlspecialchars($gender['gender_id'], ENT_QUOTES, 'UTF-8') . "\"" .
														(isset($person_info['gender_id']) && ($gender['gender_id'] == $person_info['gender_id']) ? "selected" : "") . ">" .
														htmlspecialchars($gender['gender_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
}
?>
		    </select>
			</li>
			<li>
				<label for="start_date">Start Date:</label>
				<input id="start_date" name="start_date" type="date" required  value="<?= isset($person_info['start_date']) ? htmlspecialchars($person_info['start_date'], ENT_QUOTES, 'UTF-8') : "" ?>"/>
			</li>
			<li>
				<label for="end_date">End Date:</label>
				<input id="end_date" name="end_date" type="date"  value="<?= isset($person_info['end_date']) ? htmlspecialchars($person_info['end_date'], ENT_QUOTES, 'UTF-8') : "" ?>"/>
				<span class="hint"> (Leave blank if no end date yet)</span>
			</li>
		</ol>
	</fieldset>
	<input type="submit" />
</form>

</body>
</html>
