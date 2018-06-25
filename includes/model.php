<?php
// model.php - database interactions

function open_database_connection()
{
	// Get database credentials
	require(dirname(__FILE__).'/../../../config/db.inc.php');
  // Set driver
  $dsn = "pgsql:host=$host;dbname=$db";
  $opt = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
  ];

  // Instantiate the class
  $link = new PDO($dsn, $user, $pass, $opt);

  return $link;
}

function close_database_connection(&$link)
{
  $link = null;
}

function get_all_teachers()
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT p2pt.person_id, p.given_name_r, p.family_name_r, p2pt.ptype_id, pt.ptype_name
	  FROM people2person_types p2pt
	  INNER JOIN person_types pt ON p2pt.ptype_id = pt.ptype_id AND pt.ptype_name = 'Staff'
	  INNER JOIN people p ON p2pt.person_id = p.person_id
		ORDER BY p.given_name_r");
	$stmt->execute();
	$teachers = array();
	foreach ($stmt as $row)
	{
		$teachers[] = $row;
	}
	close_database_connection($link);
	return $teachers;
}

function get_classes_for_teacher($teacher_id,$dow,$date)
{
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT c.class_id,
                                p.person_id as teacher_id,
                                d.dow_name,
                                left(c.class_time::text, 5) as class_time,
                                l.level_name,
                                concat_ws(' ',p.given_name_r, p.family_name_r) as teacher_name
      FROM classes c
      INNER JOIN days_of_week d ON c.dow_id = d.dow_id
      INNER JOIN roster r ON c.class_id = r.class_id AND r.person_id = :teacher_id AND (d.dow_name = :dow OR d.dow_name = 'Flex')
      INNER JOIN levels l ON c.level_id = l.level_id
      INNER JOIN people p ON r.person_id = p.person_id
      WHERE :date BETWEEN r.start_date AND r.end_date
      ORDER BY d.dow_id, c.class_time");
  $stmt->execute(['teacher_id' => $teacher_id, 'dow' => $dow, 'date' => $date]);

  if ($stmt->rowCount()) {
    $classes = array();
    foreach ($stmt as $row)
    {
      $classes[] = $row;
    }
  }
  else {
    $classes = FALSE;
  }
  close_database_connection($link);
	return $classes;
}

// *** NOTE: should probably refactor using other functions ***
function get_classes_for_student($student_id,$dow)
{
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT c.class_id,
                                p.person_id,
                                d.dow_name,
                                left(c.class_time::text, 5) as time,
                                l.level_name,
                                concat_ws(' ',p.given_name_r, p.family_name_r) as name
			FROM classes c
			INNER JOIN days_of_week d ON c.dow_id = d.dow_id AND d.dow_name = :dow
			INNER JOIN roster r ON c.class_id = r.class_id AND r.person_id = :student_id
			INNER JOIN levels l ON c.level_id = l.level_id
			INNER JOIN people p ON r.person_id = p.person_id
			ORDER BY c.class_time");
  $stmt->execute(['student_id' => $student_id, 'dow' => $dow]);

  if ($stmt->rowCount()) {
    $classes = array();
    foreach ($stmt as $row)
    {
      $classes[] = $row;
    }
  }
  else {
    $classes = FALSE;
  }
  close_database_connection($link);
	return $classes;
}

function get_locations()
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT location_id, location_name FROM locations ORDER BY location_id");
	$stmt->execute();
	$locations = array();
	foreach ($stmt as $row)
	{
		$locations[] = $row;
	}
	close_database_connection($link);
	return $locations;
}

function get_classes_for_location($location_id,$dow)
{
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT c.class_id,
                                p.person_id,
                                d.dow_name,
                                left(c.class_time::text, 5) as time,
                                l.level_name,
                                concat_ws(' ',p.given_name_r, p.family_name_r) as name
      FROM classes c
      INNER JOIN days_of_week d ON c.dow_id = d.dow_id AND d.dow_name = :dow
      INNER JOIN roster r ON c.class_id = r.class_id AND c.location_id = :location_id
      INNER JOIN levels l ON c.level_id = l.level_id
      INNER JOIN people p ON r.person_id = p.person_id
      INNER JOIN person_types pt ON pt.ptype_name = 'Staff'
      INNER JOIN people2person_types p2pt ON p2pt.ptype_id = pt.ptype_id AND p2pt.person_id = p.person_id
			ORDER BY c.class_time");
  $stmt->execute(['dow' => $dow, 'location_id' => $location_id]);

  if ($stmt->rowCount()) {
    $classes = array();
    foreach ($stmt as $row)
    {
      $classes[] = $row;
    }
  }
  else {
    $classes = FALSE;
  }
  close_database_connection($link);
	return $classes;
}

function get_students_for_location($location_id)
{
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT DISTINCT p.person_id,
                                          p.given_name_r,
                                          p.family_name_r,
                                          p.family_name_k,
                                          p.given_name_k
      FROM people2person_types p2pt
      INNER JOIN person_types pt ON p2pt.ptype_id = pt.ptype_id AND pt.ptype_name = 'Student'
      INNER JOIN people p ON p2pt.person_id = p.person_id
  	  INNER JOIN roster r ON p.person_id = r.person_id
  	  INNER JOIN classes c ON r.class_id = c.class_id and c.location_id = :location_id
  	  ORDER BY p.family_name_r");
    $stmt->execute(['location_id' => $location_id]);

    if ($stmt->rowCount()) {
      $students = array();
      foreach ($stmt as $row)
      {
        $students[] = $row;
      }
    }
    else {
      $students = FALSE;
    }
    close_database_connection($link);
  	return $students;
}

function get_all_students()
{
	$link = open_database_connection();
	$stmt = $link->query("SELECT p.person_id,
															 p.given_name_r,
															 p.family_name_r,
															 p.family_name_k,
															 p.given_name_k
	  										FROM people p
	  										INNER JOIN people2person_types p2pt ON p.person_id = p2pt.person_id
	  										INNER JOIN person_types pt ON p2pt.ptype_id = pt.ptype_id
	  										WHERE pt.ptype_name = 'Student'
	  										ORDER BY p.family_name_r");

	if ($stmt->rowCount()) {
		$students = array();
		foreach ($stmt as $row)
		{
			$students[] = $row;
		}
	}
	else {
		$students = FALSE;
	}
	close_database_connection($link);
	return $students;
}

function get_student_absences($student_id)
{
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT a.student_id,
                                        concat_ws(' ',p.given_name_r, p.family_name_r) as name,
                                        a.attendance_id,
                                        c.class_id,
                                        ci.cinstance_id,
                                        ci.cinstance_date,
                                        dow.dow_name,
                                        left(c.class_time::text, 5) as time,
                                        l.level_name,
                                        a.present
                                    FROM attendance a
                                    INNER JOIN people p ON a.student_id = p.person_id AND p.person_id = :student_id
                                    INNER JOIN class_instances ci ON a.cinstance_id = ci.cinstance_id
                                    INNER JOIN classes c ON ci.class_id = c.class_id
                                    INNER JOIN levels l ON c.level_id = l.level_id
                                    INNER JOIN days_of_week dow ON c.dow_id = dow.dow_id
                                    WHERE a.present = false AND a.cinstance_id NOT IN (SELECT original_cinstance_id FROM makeup WHERE student_id = :student_id)
                                    ORDER BY ci.cinstance_date, c.class_time");
  $stmt->execute(['student_id' => $student_id]);

  if ($stmt->rowCount()) {
    $absences = array();
    foreach ($stmt as $row)
    {
      $absences[] = $row;
    }
  }
  else {
    $absences = FALSE;
  }
  close_database_connection($link);
  return $absences;
}

// *** NOTE: Should probably refactor this into separate functions ***
function get_student_and_class_info($student_id,$class_id)
{
	$link = open_database_connection();
  $stmt = $link->prepare("SELECT p.person_id AS student_id,
																concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name,
																c.class_id,
																l.level_name
  	FROM people p
  	INNER JOIN roster r ON r.person_id = p.person_id AND p.person_id = :student_id AND r.class_id = :class_id
    INNER JOIN classes c ON c.class_id = r.class_id
    INNER JOIN levels l ON c.level_id = l.level_id");
  $stmt->execute(['student_id' => $student_id, 'class_id' => $class_id]);
  $result = $stmt->fetch();
	close_database_connection($link);
  return $result;
}

function add_makeup_lesson($student_id,$original_cinstance_id,$makeup_cinstance_id)
{
	$link = open_database_connection();
  $ins_stmt = $link->prepare("INSERT INTO makeup (student_id, original_cinstance_id, makeup_cinstance_id)
    																			VALUES (:student_id, :original_cinstance_id, :makeup_cinstance_id)
    																			RETURNING makeup_id");
  $ins_stmt->execute(['student_id' => $student_id, 'original_cinstance_id' => $original_cinstance_id, 'makeup_cinstance_id' => $makeup_cinstance_id]);
	$result = $ins_stmt->fetch();
	close_database_connection($link);
  return $result;
}

// Find the class instance id for a given class_id and Date, if one exists
// Arguments: class_id, date
// Returns the id of the class_instance
function get_class_instance($class_id, $date) {
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT ci.cinstance_id
  	FROM class_instances ci
  	WHERE ci.class_id = :class_id AND ci.cinstance_date = :date");
  $stmt->execute(['class_id' => $class_id, 'date' => $date]);

  // The class instance exists, return its ID
  if ($result = $stmt->fetch()) {
    // echo "<p>class instance exists</p>";
    $cinstance_id = $result['cinstance_id'];
  }
	else {
		$cinstance_id = FALSE;
	}
	close_database_connection($link);
	return $cinstance_id;
}

// Create a class instance with the given class_id and date
// Arguments: class_id, date
// Returns the id of the class_instance or false if there was an error
function create_class_instance($class_id, $date) {
  $link = open_database_connection();
  // echo "<p>class instance being created</p>";
  $stmt = $link->prepare("INSERT INTO class_instances (class_id, cinstance_date)
    VALUES (:class_id, :date)
    RETURNING cinstance_id");
  $stmt->execute(['class_id' => $class_id, 'date' => $date]);
	if ($result = $stmt->fetch()) {
		// echo "<p>class instance exists</p>";
		$cinstance_id = $result['cinstance_id'];
	}
	else {
		$cinstance_id = FALSE;
	}
	close_database_connection($link);
	return $cinstance_id;

}


// Check if a class is an All Stars class that gets grades using the given class_id
// Arguments: class_id
// Returns boolean (true if it is an All Stars class, else false)
function is_graded_class($class_id) {
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT l.level_name
  	FROM levels l
    INNER JOIN classes c
    ON l.level_id = c.level_id
  	WHERE c.class_id = :class_id");
  $stmt->execute(['class_id' => $class_id]);

  while ($result = $stmt->fetch()) {
    // The level is All Stars
    if (stripos($result['level_name'], "stars") !== FALSE) {
      // echo "<p>It is a Child class.</p>";
		  close_database_connection($link);
      return TRUE;
    }
    // The class instance does not exist, insert it as a row and return the ID
    else {
      // echo "<p>It is NOT a Child class.</p>";
		  close_database_connection($link);
      return FALSE;
    }
  }
}

// Check if an attendance is a makeup lesson
// Arguments: student_id and cinstance_id
// Returns boolean (true if it is a makeup lesson, else false)
function is_makeup_lesson($student_id, $cinstance_id) {
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT m.makeup_cinstance_id
  	FROM makeup m
  	WHERE m.student_id = :student_id AND m.makeup_cinstance_id = :cinstance_id");
  $stmt->execute(['student_id' => $student_id, 'cinstance_id' => $cinstance_id]);

  if($stmt->fetch())  {
      // row found
		  close_database_connection($link);
      return TRUE;
  }
  else {
      // row not found
		  close_database_connection($link);
      return FALSE;
  }
}


// Get an array containing the names of all the grade types.
// Arguments: none
// Returns array of strings
function get_grade_types() {
  $link = open_database_connection();
  // initiate array for grade types
  $grade_types = array();
  $stmt = $link->prepare("SELECT gtype_id, gtype_name FROM grade_types ORDER BY gtype_id");
  $stmt->execute();

  while($row = $stmt->fetch()) {
    $grade_types[$row['gtype_id']] = $row['gtype_name'];
  }
  close_database_connection($link);
  return $grade_types;
}

// Select all the students in a class, including makeups. Exclude the teacher explicitly. It would be possible
//  to exclude based on person_type, but it's also possible that some staff members could be students
function get_students_for_class($class_id, $teacher_id, $date) {
	$link = open_database_connection();

	$stmt = $link->prepare("SELECT p.person_id AS student_id,
																concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name
													FROM people p
													INNER JOIN roster r
														ON r.person_id = p.person_id
														AND r.class_id = :class_id
														AND p.person_id != :teacher_id
													WHERE :date BETWEEN r.start_date AND r.end_date
													UNION
													SELECT p.person_id AS student_id,
																concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name
													FROM people p
													INNER JOIN makeup m
														ON p.person_id = m.student_id
													INNER JOIN class_instances ci
														ON ci.cinstance_id = m.makeup_cinstance_id
														AND ci.class_id = :class_id
														AND ci.cinstance_date = :date
													ORDER BY student_name");
	$stmt->execute(['class_id' => $class_id, 'teacher_id' => $teacher_id, 'date' => $date]);

	if ($stmt->rowCount()) {
    $students = array();
    foreach ($stmt as $row)
    {
      $students[] = $row;
    }
  }
  else {
    $students = FALSE;
  }
  close_database_connection($link);
  return $students;
}


// to get attendance, we need the student ID and the class instance ID
function get_attendance($cinstance_id, $student_id) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT attendance_id, present, notes
													FROM attendance
													WHERE cinstance_id = :cinstance_id AND student_id = :student_id");
	$stmt->execute(['cinstance_id' => $cinstance_id, 'student_id' => $student_id]);
	$attendance = $stmt->fetch();
	close_database_connection($link);
  return $attendance;
}

// to get the grades, we need the attendance ID
function get_grades($attendance_id) {
	$grades = [];
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT gi.grade, gi.gtype_id, gt.gtype_name
		FROM grade_instances gi
		INNER JOIN grade_types gt ON gi.gtype_id = gt.gtype_id
		WHERE gi.attendance_id = :attendance_id
		ORDER BY gi.gtype_id");
	$stmt->execute(['attendance_id' => $attendance_id]);
	while ($row = $stmt->fetch()) {
		$grades[strtolower($row['gtype_name'])] = $row['grade'];
	}
	close_database_connection($link);
  return $grades;
}

// Insert a new attendance record, returning its attendance ID
function add_attendance($cinstance_id, $teacher_id, $student_id, $present, $notes) {
	$link = open_database_connection();
	$stmt = $link->prepare("INSERT INTO attendance
																	(cinstance_id, teacher_id, student_id, present, notes)
																	VALUES (:cinstance_id, :teacher_id, :student_id, :present, :notes)
																	RETURNING attendance_id");
	$stmt->execute(['cinstance_id' => $cinstance_id,
													'teacher_id' => $teacher_id,
													'student_id' => $student_id,
													'present' => $present,
													'notes' => $notes]);
	$attendance_id = $stmt->fetchColumn();
	close_database_connection($link);
	return $attendance_id;
}

// Update an existing attendance record, returning its attendance ID
function update_attendance($cinstance_id, $teacher_id, $student_id, $present, $notes) {
	$link = open_database_connection();
	$stmt = $link->prepare("UPDATE attendance
																	SET (teacher_id, present, notes) = (:teacher_id, :present, :notes)
																	WHERE cinstance_id = :cinstance_id AND student_id = :student_id
																	RETURNING attendance_id");
	$stmt->execute(['cinstance_id' => $cinstance_id,
													'teacher_id' => $teacher_id,
													'student_id' => $student_id,
													'present' => $present,
													'notes' => $notes]);
	if ($stmt->rowCount()) {
		$attendance_id = $stmt->fetchColumn();
	}
	else {
		$attendance_id = $stmt->errorInfo();
		// Can't seem to be able to check for an error code vs. real data because even the attendance_id comes back as an array
		//$attendance_id = FALSE;
	}
	close_database_connection($link);
	return $attendance_id;
}

// Check if grade for an attendance ID and grade type exist
function grade_exists($attendance_id, $grade_type) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT ginstance_id
														FROM grade_instances
														WHERE attendance_id = :attendance_id
															AND gtype_id = (SELECT gtype_id
																								FROM grade_types
																								WHERE LOWER(gtype_name) = LOWER(:grade_type))");
	$stmt->execute(['attendance_id' => $attendance_id,'grade_type' => $grade_type]);
	if ($stmt->rowCount()) {
		$grade_exists = TRUE;
	}
	else {
		$grade_exists = FALSE;
	}
	close_database_connection($link);
	return $grade_exists;
}


// Add a grade
function add_grade($attendance_id, $grade_type, $grade) {
	$link = open_database_connection();
	$stmt = $link->prepare("INSERT INTO grade_instances (gtype_id,
																												attendance_id,
																												grade)
																VALUES ((SELECT gtype_id
																					FROM grade_types
																					WHERE LOWER(gtype_name) = LOWER(:grade_type)),
																				:attendance_id,
																				:grade)
																RETURNING ginstance_id");

	$stmt->execute(['grade_type' => $grade_type,'attendance_id' => $attendance_id,'grade' => $grade]);
	if ($stmt->rowCount()) {
		$result = $stmt->fetch();
		// Insert successful, return attendance_id
		$ginstance_id = $result['ginstance_id'];
	}
	else {
		$ginstance_id = FALSE;
	}
	close_database_connection($link);
	return $ginstance_id;
}

// UPDATE a grade
function update_grade($attendance_id, $grade_type, $grade) {
	$link = open_database_connection();
	$stmt = $link->prepare("UPDATE grade_instances
														SET grade = :grade
														WHERE attendance_id = :attendance_id
														AND gtype_id =
															(SELECT gtype_id
																FROM grade_types
																WHERE LOWER(gtype_name) = LOWER(:grade_type))
														RETURNING ginstance_id");
	$stmt->execute(['grade_type' => $grade_type,'attendance_id' => $attendance_id,'grade' => $grade]);

	if ($stmt->rowCount()) {
		$result = $stmt->fetch();
		// Insert successful, return attendance_id
		$ginstance_id = $result['ginstance_id'];
	}
	else {
		$ginstance_id = FALSE;
	}
	close_database_connection($link);
	return $ginstance_id;
}

// Insert or update grades
function upsert_grades($attendance_id, $student_data) {
	$link = open_database_connection();

	$grade_types = get_grade_types();
	foreach ($grade_types as $grade_type) {
		// insert into grade_instances where (select gtype_id from grade_types where gtype_name = $grade_type)
		// if the data has already been submitted, update the existing record
		if (grade_exists($attendance_id, $grade_type)) {
			$grade_result = update_grade($attendance_id, $grade_type, $student_data[strtolower($grade_type)]);
		}
		// otherwise, insert a new record
		else {
			$grade_result = add_grade($attendance_id, $grade_type, $student_data[strtolower($grade_type)]);
		}

		// Display confirmation of success or failure
		if ($grade_result) {
			//echo "<p>Success! The ID of the grade instance information entered is " . htmlspecialchars($grade_result, ENT_QUOTES, 'UTF-8') . ".</p>";
		}
		else {
			// Insert failure, return error
			//echo "<p>Sorry, that didn't work. Error message: ";
			// echo implode(":", $stmt->errorInfo());
			//echo "</p>";
		}
	}
	close_database_connection($link);
	return $grade_result;
}

// Get all the fields for a students
function get_student_info($student_id) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT p.person_id as student_id,
															   p.family_name_r,
															   p.given_name_r,
															   p.family_name_k,
															   p.given_name_k,
															   concat_ws(' ',p.given_name_r, p.family_name_r) AS student_name,
															   p.dob,
															   p.start_date,
															   p.end_date,
															   g.gender_name,
															   ARRAY(SELECT ROW(pn.phone_number, pt.ptype_name)
															   			FROM phone_numbers pn
															   			INNER JOIN people2phone_numbers p2pn ON pn.phone_id = p2pn.phone_id
															   			INNER JOIN phone_types pt ON pt.ptype_id = p2pn.ptype_id
															   			WHERE p2pn.person_id = p.person_id) AS phone_numbers,
															   ARRAY(SELECT ROW(a.address, at.atype_name)
															   			FROM addresses a
															   			INNER JOIN people2addresses p2a ON a.address_id = p2a.address_id
															   			INNER JOIN address_types at ON at.atype_id = p2a.atype_id
															   			WHERE p2a.person_id = p.person_id) AS addresses,
															   ARRAY(SELECT e.email_address
															   			FROM email_addresses e
															   			INNER JOIN people2email_addresses p2e ON e.email_address_id = p2e.email_address_id
															   			WHERE p2e.person_id = p.person_id) AS email_addresses
															FROM people p
															INNER JOIN genders g ON p.gender_id = g.gender_id
															WHERE p.person_id = :student_id;");
	$stmt->execute(['student_id' => $student_id]);

	if ($stmt->rowCount()) {
		$student_info = $stmt->fetch();
	}
	else {
		$student_info = FALSE;
	}
	close_database_connection($link);
	return $student_info;
}

// Get a list of class IDs that the student is currently enrolled in
function get_current_classes_for_student($student_id) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT c.class_id,
															   l.level_name,
																 l.level_short_code,
																 ct.ctype_name
														FROM classes c
														INNER JOIN levels l ON l.level_id = c.level_id
														INNER JOIN class_types ct ON ct.ctype_id = c.ctype_id
														INNER JOIN roster r ON r.class_id = c.class_id
															AND current_date BETWEEN r.start_date AND r.end_date
														WHERE r.person_id = :student_id;");
	$stmt->execute(['student_id' => $student_id]);
	if ($stmt->rowCount()) {
		$current_classes = $stmt->fetchall();
	}
	else {
		$current_classes = FALSE;
	}
	close_database_connection($link);
	return $current_classes;
}

// Get a list of class IDs that the student is currently enrolled in
function get_classes_for_student_by_date_range($student_id, $start_date, $end_date) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT c.class_id,
															   l.level_name,
																 l.level_short_code,
																 ct.ctype_name
														FROM classes c
														INNER JOIN levels l ON l.level_id = c.level_id
														INNER JOIN class_types ct ON ct.ctype_id = c.ctype_id
														INNER JOIN roster r ON r.class_id = c.class_id
															AND (:start_date, :end_date) OVERLAPS (r.start_date, r.end_date)
														WHERE r.person_id = :student_id;");
	$stmt->execute(['student_id' => $student_id, 'start_date'=> $start_date, 'end_date' => $end_date]);
	if ($stmt->rowCount()) {
		$current_classes = $stmt->fetchall();
	}
	else {
		$current_classes = FALSE;
	}
	close_database_connection($link);
	return $current_classes;
}

// Get all the fields for a class
function get_class_info($class_id) {
	$link = open_database_connection();
	//$stmt = $link->prepare("");
	//$stmt->execute(['' => $]);

	close_database_connection($link);
	return $class_info;
}

// Get the attendance information for a student based on start and end dates
function get_attendance_from_date_range($student_id, $class_id, $start_date, $end_date) {
	$link = open_database_connection();
	//$stmt = $link->prepare("");
	//$stmt->execute(['' => $]);
	$stmt = $link->prepare("SELECT a.attendance_id,
																						ci.cinstance_id,
																						ci.cinstance_date,
																						a.present,
																						a.notes
	  																			FROM attendance a
	  																			INNER JOIN class_instances ci ON a.cinstance_id = ci.cinstance_id
																					INNER JOIN classes c ON c.class_id = ci.class_id
	  																			WHERE a.student_id = :student_id
																						AND c.class_id = :class_id
																						AND ci.cinstance_date BETWEEN :start_date AND :end_date
																					ORDER BY ci.cinstance_date");
	$stmt->execute(['student_id' => $student_id, 'class_id' => $class_id, 'start_date' => $start_date, 'end_date' => $end_date]);
	$attendance = $stmt->fetchall();

	close_database_connection($link);
	return $attendance;
}

// Get an array containing the names of all the test grade types.
// Arguments: none
// Returns array of strings
function get_test_grade_types() {
  $link = open_database_connection();
  // initiate array for grade types
  $test_grade_types = array();
  $stmt = $link->prepare("SELECT tgtype_id, tgtype_name FROM test_grade_types ORDER BY tgtype_id");
  $stmt->execute();

  while($row = $stmt->fetch()) {
    $test_grade_types[$row['tgtype_id']] = $row['tgtype_name'];
  }
  close_database_connection($link);
  return $test_grade_types;
}

// Get an array containing the names of all the test grade types.
// Arguments: none
// Returns array of strings
function get_test_name($test_date) {
  $link = open_database_connection();

  $stmt = $link->prepare("SELECT test_name FROM tests WHERE :test_date BETWEEN start_date AND end_date");
  $stmt->execute(['test_date' => $test_date]);

	// The test name exists, return it
  if ($result = $stmt->fetch()) {
    // echo "<p>class instance exists</p>";
    $test_name = $result['test_name'];
  }
	else {
		$test_name = FALSE;
	}
  close_database_connection($link);
  return $test_name;
}
?>
