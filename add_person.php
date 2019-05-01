<?php
// Start the session
session_start();
// Unset all of the session variables.
$_SESSION = array();

require_once('includes/model.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Add a New Person</title>
	<link rel="stylesheet" type="text/css" media="screen" href="css/main.css">
</head>
<body>

<h1>Add a New Person to the System:</h1>
<form id="addperson" class="addinfo" action="submit_newperson.php" method="post">
	<fieldset>
		<legend>Basic Details</legend>
		<ol>
			<li>
				<label for="person_type">Person Type:</label>
				<!-- Staff vs. Student -->
				<select id="person_type" name="person_type">
<?php
/* List genders */
$person_types = get_person_types();
foreach ($person_types as $person_type)
{
	echo "<option value=\"" . htmlspecialchars($person_type['ptype_id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($person_type['ptype_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
}
?>
				</select>
			</li>
			<li>
				<label for="family_name_k">Family Name (in Kanji/Kana)</label>
				<input type="text" id="family_name_k" name="family_name_k" size="30" required />
			</li>
			<li>
				<label for="given_name_k">Given Name (in Kanji/Kana)</label>
				<input type="text" id="given_name_k" name="given_name_k" size="30" required />
			</li>
			<li>
				<label for="family_name_k">Family Name (in Romaji)</label>
				<input type="text" id="family_name_r" name="family_name_r" size="30" required />
			</li>
			<li>
				<label for="given_name_k">Given Name (in romaji)</label>
				<input type="text" id="given_name_r" name="given_name_r" size="30" required />
			</li>
			<li>
				<label for="gender">Gender:</label>
				<select id="gender" name="gender">
<?php
/* List genders */
$genders = get_genders();
foreach ($genders as $gender)
{
	echo "<option value=\"" . htmlspecialchars($gender['gender_id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($gender['gender_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
}
?>
		    </select>
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
	<input type="submit" />
</form>
</body>
</html>
