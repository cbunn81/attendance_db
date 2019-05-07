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
?>

<form id="addperson" class="addinfo" action="submit_newperson.php" method="post">
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
	if($person_type['ptype_id'] == $person_info['ptype_id']) {
		echo "<option value=\"" . htmlspecialchars($person_type['ptype_id'], ENT_QUOTES, 'UTF-8') . "\" selected>" . htmlspecialchars($person_type['ptype_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
	}
	else {
		echo "<option value=\"" . htmlspecialchars($person_type['ptype_id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($person_type['ptype_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
	}
}
?>
				</select>
			</li>
			<li>
				<label for="family_name_k">Family Name <span class="hint">(in Kanji/Kana)</span>:</label>
				<input type="text" id="family_name_k" name="family_name_k" size="30" required value="<?= htmlspecialchars($person_info['family_name_k'], ENT_QUOTES, 'UTF-8') ?>"/>
			</li>
			<li>
				<label for="given_name_k">Given Name <span class="hint">(in Kanji/Kana)</span>:</label>
				<input type="text" id="given_name_k" name="given_name_k" size="30" required  value="<?= htmlspecialchars($person_info['given_name_k'], ENT_QUOTES, 'UTF-8') ?>"/>
			</li>
			<li>
				<label for="family_name_k">Family Name <span class="hint">(in Romaji)</span>:</label>
				<input type="text" id="family_name_r" name="family_name_r" size="30" required  value="<?= htmlspecialchars($person_info['family_name_r'], ENT_QUOTES, 'UTF-8') ?>"/>
			</li>
			<li>
				<label for="given_name_k">Given Name <span class="hint">(in Romaji)</span>:</label>
				<input type="text" id="given_name_r" name="given_name_r" size="30" required  value="<?= htmlspecialchars($person_info['given_name_r'], ENT_QUOTES, 'UTF-8') ?>"/>
			</li>
			<li>
				<label for="dob">Date of Birth:</label>
				<input id="dob" name="dob" type="date"  value="<?= htmlspecialchars($person_info['dob'], ENT_QUOTES, 'UTF-8') ?>"/>
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
	if($person_type['ptype_id'] == $person_info['ptype_id']) {
		echo "<option value=\"" . htmlspecialchars($gender['gender_id'], ENT_QUOTES, 'UTF-8') . "\" selected>" . htmlspecialchars($gender['gender_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
	}
	else {
		echo "<option value=\"" . htmlspecialchars($gender['gender_id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($gender['gender_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
	}
}
?>
		    </select>
			</li>
			<li>
				<label for="start_date">Start Date:</label>
				<input id="start_date" name="start_date" type="date" required  value="<?= htmlspecialchars($person_info['start_date'], ENT_QUOTES, 'UTF-8') ?>"/>
			</li>
			<li>
				<label for="end_date">End Date:</label>
				<input id="end_date" name="end_date" type="date"  value="<?= htmlspecialchars($person_info['end_date'], ENT_QUOTES, 'UTF-8') ?>"/>
				<span class="hint"> (Leave blank if no end date yet)</span>
			</li>
		</ol>
	</fieldset>
	<input type="submit" />
</form>

<?php
}
else {
	echo "No person has been selected, please try again.";
}

?>

</body>
</html>
