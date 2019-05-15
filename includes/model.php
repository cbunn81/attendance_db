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
                                concat_ws(' ',p.given_name_r, p.family_name_r) as teacher_name,
																r.start_date
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
function get_classes_for_student($student_id,$dow,$date)
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
			INNER JOIN roster r ON c.class_id = r.class_id AND r.person_id = :student_id AND :date BETWEEN r.start_date AND r.end_date
			INNER JOIN levels l ON c.level_id = l.level_id
			INNER JOIN people p ON r.person_id = p.person_id
			WHERE :date BETWEEN c.start_date AND c.end_date
			ORDER BY c.class_time");
  $stmt->execute(['student_id' => $student_id, 'dow' => $dow, 'date' => $date]);

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
	$stmt = $link->prepare("SELECT location_id, location_name FROM locations ORDER BY location_name");
	$stmt->execute();
	$locations = array();
	foreach ($stmt as $row)
	{
		$locations[] = $row;
	}
	close_database_connection($link);
	return $locations;
}

function get_days_of_week()
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT dow_id, dow_name FROM days_of_week ORDER BY dow_id");
	$stmt->execute();
	$days_of_week = array();
	foreach ($stmt as $row)
	{
		$days_of_week[] = $row;
	}
	close_database_connection($link);
	return $days_of_week;
}

function get_class_types()
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT ctype_id, ctype_name FROM class_types ORDER BY ctype_id");
	$stmt->execute();
	$class_types = array();
	foreach ($stmt as $row)
	{
		$class_types[] = $row;
	}
	close_database_connection($link);
	return $class_types;
}

function get_levels()
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT level_id, level_name FROM levels ORDER BY level_name");
	$stmt->execute();
	$levels = array();
	foreach ($stmt as $row)
	{
		$levels[] = $row;
	}
	close_database_connection($link);
	return $levels;
}

function get_person_types()
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT ptype_id, ptype_name FROM person_types ORDER BY ptype_id DESC");
	$stmt->execute();
	$person_types = array();
	foreach ($stmt as $row)
	{
		$person_types[] = $row;
	}
	close_database_connection($link);
	return $person_types;
}

function get_genders()
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT gender_id, gender_name FROM genders ORDER BY gender_id DESC");
	$stmt->execute();
	$genders = array();
	foreach ($stmt as $row)
	{
		$genders[] = $row;
	}
	close_database_connection($link);
	return $genders;
}

function get_location_by_id($location_id)
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT location_name FROM locations WHERE location_id = :location_id");
	$stmt->execute(['location_id' => $location_id]);
  if ($result = $stmt->fetch()) {
		$location_name = $result['location_name'];
	}
	close_database_connection($link);
	return $location_name;
}

function get_dow_by_id($dow_id)
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT dow_name FROM days_of_week WHERE dow_id = :dow_id");
	$stmt->execute(['dow_id' => $dow_id]);
  if ($result = $stmt->fetch()) {
		$dow_name = $result['dow_name'];
	}
	close_database_connection($link);
	return $dow_name;
}

function get_ctype_by_id($ctype_id)
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT ctype_name FROM class_types WHERE ctype_id = :ctype_id");
	$stmt->execute(['ctype_id' => $ctype_id]);
  if ($result = $stmt->fetch()) {
		$ctype_name = $result['ctype_name'];
	}
	close_database_connection($link);
	return $ctype_name;
}

function get_level_by_id($level_id)
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT level_name FROM levels WHERE level_id = :level_id");
	$stmt->execute(['level_id' => $level_id]);
  if ($result = $stmt->fetch()) {
		$level_name = $result['level_name'];
	}
	close_database_connection($link);
	return $level_name;
}

function get_ptype_by_id($ptype_id)
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT ptype_name FROM person_types WHERE ptype_id = :ptype_id");
	$stmt->execute(['ptype_id' => $ptype_id]);
  if ($result = $stmt->fetch()) {
		$ptype_name = $result['ptype_name'];
	}
	close_database_connection($link);
	return $ptype_name;
}

function get_gender_by_id($gender_id)
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT gender_name FROM genders WHERE gender_id = :gender_id");
	$stmt->execute(['gender_id' => $gender_id]);
  if ($result = $stmt->fetch()) {
		$gender_name = $result['gender_name'];
	}
	close_database_connection($link);
	return $gender_name;
}

function get_person_name($person_id)
{
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT concat_ws(' ',given_name_r, family_name_r) as person_name FROM people WHERE person_id = :person_id");
	$stmt->execute(['person_id' => $person_id]);
  if ($result = $stmt->fetch()) {
		$person_name = $result['person_name'];
	}
	close_database_connection($link);
	return $person_name;
}

function get_classes_for_location($location_id,$dow,$date)
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
      INNER JOIN roster r ON c.class_id = r.class_id AND :date BETWEEN r.start_date AND r.end_date
      INNER JOIN levels l ON c.level_id = l.level_id
      INNER JOIN people p ON r.person_id = p.person_id
      INNER JOIN person_types pt ON pt.ptype_name = 'Staff'
      INNER JOIN people2person_types p2pt ON p2pt.ptype_id = pt.ptype_id AND p2pt.person_id = p.person_id
			WHERE c.location_id = :location_id AND :date BETWEEN c.start_date AND c.end_date
			ORDER BY c.class_time");
  $stmt->execute(['dow' => $dow, 'location_id' => $location_id, 'date' => $date]);

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
// Find the attendance id for a given cinstance_id and student ID, if one exists
// Arguments: class_id, date
// Returns the id of the class_instance
function get_attendance_id($cinstance_id, $student_id) {
	$link = open_database_connection();
  $stmt = $link->prepare("SELECT a.attendance_id
  	FROM attendance a
  	WHERE a.cinstance_id = :cinstance_id AND a.student_id = :student_id");
  $stmt->execute(['cinstance_id' => $cinstance_id, 'student_id' => $student_id]);

  // The class instance exists, return its ID
  if ($result = $stmt->fetch()) {
    // echo "<p>class instance exists</p>";
    $attendance_id = $result['attendance_id'];
  }
	else {
		$attendance_id = FALSE;
	}
	close_database_connection($link);
	return $attendance_id;
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

// Check if a level is an All Stars class that gets grades using the given class_id
// Arguments: level_id
// Returns boolean (true if it is an All Stars class, else false)
function is_graded_level($level_id) {
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT l.level_name
  	FROM levels l
  	WHERE l.level_id = :level_id");
  $stmt->execute(['level_id' => $level_id]);

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

// to get the grades, we need the attendance ID
function get_test_grades($attendance_id) {
	$test_grades = [];
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT tgi.tgrade, tgi.tgtype_id, tgt.tgtype_name
		FROM test_grade_instances tgi
		INNER JOIN test_grade_types tgt ON tgi.tgtype_id = tgt.tgtype_id
		WHERE tgi.attendance_id = :attendance_id
		ORDER BY tgi.tgtype_id");
	$stmt->execute(['attendance_id' => $attendance_id]);
	while ($row = $stmt->fetch()) {
		$test_grades[strtolower($row['tgtype_name'])] = $row['tgrade'];
	}
	close_database_connection($link);
  return $test_grades;
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

// Check if test grade for an attendance ID and grade type exist
function test_grade_exists($attendance_id, $test_grade_type) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT tginstance_id
														FROM test_grade_instances
														WHERE attendance_id = :attendance_id
															AND tgtype_id = (SELECT tgtype_id
																								FROM test_grade_types
																								WHERE LOWER(tgtype_name) = LOWER(:test_grade_type))");
	$stmt->execute(['attendance_id' => $attendance_id,'test_grade_type' => $test_grade_type]);
	if ($stmt->rowCount()) {
		$test_grade_exists = TRUE;
	}
	else {
		$test_grade_exists = FALSE;
	}
	close_database_connection($link);
	return $test_grade_exists;
}


// Add a grade
function add_test_grade($attendance_id, $test_grade_type, $tgrade) {
	$link = open_database_connection();
	$stmt = $link->prepare("INSERT INTO test_grade_instances (tgtype_id,
																												test_id,
																												attendance_id,
																												tgrade)
																VALUES ((SELECT tgtype_id
																					FROM test_grade_types
																					WHERE LOWER(tgtype_name) = LOWER(:test_grade_type)),
																				(SELECT t.test_id
																					FROM tests t
																					WHERE (SELECT ci.cinstance_date
																									FROM class_instances ci
																									INNER JOIN attendance a
																									ON ci.cinstance_id = a.cinstance_id
																									AND a.attendance_id = :attendance_id)
																									BETWEEN t.start_date AND t.end_date),
																				:attendance_id,
																				:tgrade)
																RETURNING tginstance_id");

	$stmt->execute(['test_grade_type' => $test_grade_type,'attendance_id' => $attendance_id,'tgrade' => $tgrade]);
	if ($stmt->rowCount()) {
		$result = $stmt->fetch();
		// Insert successful, return attendance_id
		$tginstance_id = $result['tginstance_id'];
	}
	else {
		$tginstance_id = FALSE;
	}
	close_database_connection($link);
	return $tginstance_id;
}

// UPDATE a test grade
function update_test_grade($attendance_id, $test_grade_type, $tgrade) {
	$link = open_database_connection();
	$stmt = $link->prepare("UPDATE test_grade_instances
														SET tgrade = :tgrade
														WHERE attendance_id = :attendance_id
														AND tgtype_id =
															(SELECT tgtype_id
																FROM test_grade_types
																WHERE LOWER(tgtype_name) = LOWER(:test_grade_type))
														RETURNING tginstance_id");
	$stmt->execute(['test_grade_type' => $test_grade_type,'attendance_id' => $attendance_id,'tgrade' => $tgrade]);

	if ($stmt->rowCount()) {
		$result = $stmt->fetch();
		// Insert successful, return attendance_id
		$tginstance_id = $result['tginstance_id'];
	}
	else {
		$tginstance_id = FALSE;
	}
	close_database_connection($link);
	return $tginstance_id;
}

// Insert or update test grades
function upsert_test_grades($attendance_id, $student_data) {
	$link = open_database_connection();

	$test_grade_types = get_test_grade_types();
	foreach ($test_grade_types as $test_grade_type) {
		// insert into grade_instances where (select gtype_id from grade_types where gtype_name = $grade_type)
		// if the data has already been submitted, update the existing record
		if (test_grade_exists($attendance_id, $test_grade_type)) {
			$test_grade_result = update_test_grade($attendance_id, $test_grade_type, $student_data[strtolower($test_grade_type)]);
		}
		// otherwise, insert a new record
		else {
			$test_grade_result = add_test_grade($attendance_id, $test_grade_type, $student_data[strtolower($test_grade_type)]);
		}

		// Display confirmation of success or failure
		if ($test_grade_result) {
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
	return $test_grade_result;
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
															WHERE p.person_id = :student_id");
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

// Get all the fields for a person
function get_person_info($person_id) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT p.person_id,
															   p.family_name_r,
															   p.given_name_r,
															   p.family_name_k,
															   p.given_name_k,
																 p.gender_id,
															   p.dob,
															   p.start_date,
															   p.end_date,
																 pt.ptype_id,
																 pt.ptype_name,
															   g.gender_name
															FROM people p
															INNER JOIN people2person_types p2pt ON p.person_id = p2pt.person_id
															INNER JOIN person_types pt ON pt.ptype_id = p2pt.ptype_id
															INNER JOIN genders g ON p.gender_id = g.gender_id
															WHERE p.person_id = :person_id");
	$stmt->execute(['person_id' => $person_id]);

	if ($stmt->rowCount()) {
		$person_info = $stmt->fetch();
	}
	else {
		$person_info = FALSE;
	}
	close_database_connection($link);
	return $person_info;
}

// Get all the fields for a class
function get_class_info($class_id) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT c.class_id,
																	lo.location_id,
																	lo.location_name,
																	lv.level_id,
																	lv.level_short_code,
																	lv.level_name,
																	ct.ctype_id,
																	ct.ctype_name,
																	dow.dow_id,
																	dow.dow_name,
																	c.class_time,
																	c.start_date,
																	c.end_date
															FROM classes c
															INNER JOIN locations lo ON c.location_id = lo.location_id
															INNER JOIN levels lv ON c.level_id = lv.level_id
															INNER JOIN class_types ct ON c.ctype_id = ct.ctype_id
															INNER JOIN days_of_week dow ON c.dow_id = dow.dow_id
															WHERE c.class_id = :class_id");
	$stmt->execute(['class_id' => $class_id]);

	if ($stmt->rowCount()) {
		$class_info = $stmt->fetch();
	}
	else {
		$class_info = FALSE;
	}
	close_database_connection($link);
	return $class_info;
}

// Get an array of class info for each class that the student is currently enrolled in
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
														WHERE r.person_id = :student_id");
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
																 l.level_id,
															   l.level_name,
																 l.level_short_code,
																 ct.ctype_name
														FROM classes c
														INNER JOIN levels l ON l.level_id = c.level_id
														INNER JOIN class_types ct ON ct.ctype_id = c.ctype_id
														INNER JOIN roster r ON r.class_id = c.class_id
															AND (:start_date, :end_date) OVERLAPS (r.start_date, r.end_date)
														WHERE r.person_id = :student_id");
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

// Get an array of class info for levels that the student is enrolled in for a given date period
function get_levels_for_student_by_date_range($student_id, $start_date, $end_date) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT DISTINCT l.level_id,	l.level_name, l.level_short_code
														FROM levels l
														INNER JOIN classes c ON l.level_id = c.level_id
														INNER JOIN roster r ON r.class_id = c.class_id
															AND (:start_date, :end_date) OVERLAPS (r.start_date, r.end_date)
														WHERE r.person_id = :student_id");
	$stmt->execute(['student_id' => $student_id, 'start_date' => $start_date, 'end_date' => $end_date]);
	if ($stmt->rowCount()) {
		$current_levels = $stmt->fetchall();
	}
	else {
		$current_levels = FALSE;
	}
	close_database_connection($link);
	return $current_levels;
}

// Get the attendance information for a student based on start and end dates
function get_attendance_from_date_range($student_id, $level_id, $start_date, $end_date) {
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
																					INNER JOIN levels l ON c.level_id = l.level_id AND l.level_id = :level_id
	  																			WHERE a.student_id = :student_id
																						AND ci.cinstance_date BETWEEN :start_date AND :end_date
																					ORDER BY ci.cinstance_date");
	$stmt->execute(['student_id' => $student_id, 'level_id' => $level_id, 'start_date' => $start_date, 'end_date' => $end_date]);
	if ($stmt->rowCount()) {
		$attendance = $stmt->fetchall();
	}
	else {
		$attendance = FALSE;
	}
	close_database_connection($link);
	return $attendance;
}

// Get an array containing the names of all the test grade types.
// Arguments: none
// Returns array of strings of the names of test grade types
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

// Get an array containing the info for a test grade type.
// Arguments: test grade type name
// Returns array of all the information about a test grade type
function get_test_grade_type_info($tgtype_name) {
  $link = open_database_connection();
  // initiate array for grade types
  $test_grade_type_info = array();
  $stmt = $link->prepare("SELECT tgtype_id, tgtype_name, tgtype_desc, tgtype_maximum_value FROM test_grade_types WHERE tgtype_name = :tgtype_name");
  $stmt->execute(['tgtype_name' => $tgtype_name]);

	if ($stmt->rowCount()) {
		$test_grade_type_info = $stmt->fetch();
	}
	else {
		$test_grade_type_info = FALSE;
	}
  close_database_connection($link);
  return $test_grade_type_info;
}

// Get a list of test information.
// Arguments: none
// Returns array of test IDs, test names, start dates and end dates for each test
function get_all_tests() {
  $link = open_database_connection();

	// initialize array for test periods
	$tests = array();
  $stmt = $link->prepare("SELECT test_id, test_name, start_date, end_date FROM tests ORDER BY start_date");
  $stmt->execute();

	if ($stmt->rowCount()) {
		$tests = $stmt->fetchall();
	}
	else {
		$tests = FALSE;
	}
	close_database_connection($link);
	return $tests;
}

// Get the information of a test based on the test ID.
// Arguments: test_id
// Returns array with the start date and end date
function get_test_by_id($test_id) {
  $link = open_database_connection();

  $stmt = $link->prepare("SELECT test_id, test_name, start_date, end_date FROM tests WHERE test_id = :test_id");
  $stmt->execute(['test_id' => $test_id]);

	if ($stmt->rowCount()) {
		$test = $stmt->fetch();
	}
	else {
		$test = FALSE;
	}
  close_database_connection($link);
  return $test;
}

// Get the name of a test based on the test date.
// Arguments: test_date
// Returns string with name of the test
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

// Get the averages for a given level and test.
// Arguments: level name, test name
// Returns array of averages for each of the test grade types
function get_test_averages($test_name, $level_name) {
	$link = open_database_connection();
  // initiate array for grade types
  $test_averages = array();
  $stmt = $link->prepare("SELECT tgt.tgtype_name, AVG(tgi.tgrade::INTEGER) AS avg_grade FROM test_grade_instances tgi
														INNER JOIN test_grade_types tgt ON tgi.tgtype_id = tgt.tgtype_id
														INNER JOIN tests t ON tgi.test_id = t.test_id AND LOWER(t.test_name) = LOWER(:test_name)
														INNER JOIN attendance a ON tgi.attendance_id = a.attendance_id
														INNER JOIN class_instances ci ON a.cinstance_id = ci.cinstance_id
														INNER JOIN classes c ON ci.class_id = c.class_id
														INNER JOIN levels l ON c.level_id = l.level_id AND LOWER(l.level_name) = LOWER(:level_name)
														GROUP BY tgt.tgtype_name");
  $stmt->execute(['test_name' => $test_name, 'level_name' => $level_name]);


	while($row = $stmt->fetch()) {
    $test_averages[$row['tgtype_name']] = $row['avg_grade'];
  }
  close_database_connection($link);
  return $test_averages;
}

function is_test_taken($student_id,$test_id) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT test_id FROM test_grade_instances tgi
														INNER JOIN attendance a ON tgi.attendance_id = a.attendance_id
														AND a.attendance_id IN (SELECT attendance_id FROM attendance WHERE student_id = :student_id)
														WHERE test_id = :test_id");
	$stmt->execute(['student_id' => $student_id, 'test_id' => $test_id]);
	if ($stmt->rowCount()) {
		$is_test_taken = TRUE;
	}
	else {
		$is_test_taken = FALSE;
	}
	close_database_connection($link);
  return $is_test_taken;
}

function get_test_attendance_id($student_id,$test_id) {
	$link = open_database_connection();
	$stmt = $link->prepare("SELECT DISTINCT a.attendance_id FROM attendance a
														INNER JOIN test_grade_instances tgi
														ON tgi.attendance_id = a.attendance_id
														AND a.attendance_id IN (SELECT attendance_id FROM attendance WHERE student_id = :student_id)
														WHERE test_id = :test_id");
	$stmt->execute(['student_id' => $student_id, 'test_id' => $test_id]);
	if ($result = $stmt->fetch()) {
		// echo "<p>class instance exists</p>";
		$test_attendance_id = $result['attendance_id'];
	}
	else {
		$test_attendance_id = FALSE;
	}
	close_database_connection($link);
  return $test_attendance_id;
}

// If the student isn't doing a makeup lesson with another regularly-scheduled lesson,
//  we need to create a standalone, one-off class that will allow us to schedule the
//  makeup lesson. This is done by creating a new class with given date and time, but
//  with start and end dates that are equal to the date of the makeup lesson.
function create_standalone_makeup_lesson($original_class_id,$makeup_date,$makeup_time) {
	$original_class_info = get_class_info($original_class_id);
	$dow_name = date("l", strtotime($makeup_date));
	$link = open_database_connection();
	$stmt = $link->prepare("INSERT INTO classes (location_id,
																								ctype_id,
																								level_id,
																								dow_id,
																								class_time,
																								start_date,
																								end_date)
															VALUES (:location_id,
																			:ctype_id,
																			:level_id,
																			(SELECT dow_id FROM days_of_week WHERE dow_name = :dow_name),
																			:class_time,
																			:start_date,
																			:end_date)
															RETURNING class_id");
	$stmt->execute(['location_id' => $original_class_info['location_id'],
									'ctype_id' => $original_class_info['ctype_id'],
									'level_id' => $original_class_info['level_id'],
									'dow_name' => $dow_name,
									'class_time' => $makeup_time,
									'start_date' => $makeup_date,
									'end_date' => $makeup_date]);
	if ($result = $stmt->fetch()) {
		// echo "<p>class instance exists</p>";
		$makeup_class_id = $result['class_id'];
	}
	else {
		$makeup_class_id = FALSE;
	}
	close_database_connection($link);
  return $makeup_class_id;
}

// Creates a roster entry for the given student/teacher for the given class
//  with the given start and end dates
function create_roster_entry($person_id,$class_id,$start_date,$end_date) {
	$link = open_database_connection();
	$stmt = $link->prepare("INSERT INTO roster (person_id,
																							class_id,
																							start_date,
																							end_date)
															VALUES (:person_id,
																			:class_id,
																			:start_date,
																			:end_date)");
	$stmt->execute(['person_id' => $person_id,
									'class_id' => $class_id,
									'start_date' => $start_date,
									'end_date' => $end_date]);
	if ($stmt->rowCount()) {
		$roster_entry_created = TRUE;
	}
	else {
		$roster_entry_created = FALSE;
	}
	close_database_connection($link);
  return $roster_entry_created;
}

// Create a new class given the appropriate information
// RETURN class_id for the new class
function create_new_class($location_id,$dow_id,$ctype_id,$level_id,$class_time,$start_date,$end_date) {
	$link = open_database_connection();
	$stmt = $link->prepare("INSERT INTO classes (location_id,
																								dow_id,
																								ctype_id,
																								level_id,
																								class_time,
																								start_date,
																								end_date)
															VALUES (:location_id,
																			:dow_id,
																			:ctype_id,
																			:level_id,
																			:class_time,
																			:start_date,
																			:end_date)
															RETURNING class_id");
	$stmt->execute(['location_id' => $location_id,
									'dow_id' => $dow_id,
									'ctype_id' => $ctype_id,
									'level_id' => $level_id,
									'class_time' => $class_time,
									'start_date' => $start_date,
									'end_date' => $end_date]);
	if ($result = $stmt->fetch()) {
		$class_id = $result['class_id'];
	}
	else {
		$class_id = FALSE;
	}
	close_database_connection($link);
  return $class_id;
}

// Insert an entry into the lookup table people2person_types
//  This matches a person to the person type (i.e. Staff or Student)
function create_people2person_types_entry($person_id,$ptype_id) {
	$link = open_database_connection();
	$stmt = $link->prepare("INSERT INTO people2person_types (person_id,
																														ptype_id)
															VALUES (:person_id,
																			:ptype_id)");
	$stmt->execute(['person_id' => $person_id,
									'ptype_id' => $ptype_id]);
	if ($stmt->rowCount()) {
		$people2person_types_entry_created = TRUE;
	}
	else {
		$people2person_types_entry_created = FALSE;
	}
	close_database_connection($link);
	return $people2person_types_entry_created;
}

// Create a new class given the appropriate information
// RETURN class_id for the new class
function add_new_person($ptype_id,$family_name_k,$given_name_k,$family_name_r,$given_name_r,$dob,$gender_id,$start_date,$end_date) {
	$link = open_database_connection();
	$stmt = $link->prepare("INSERT INTO people (family_name_k,
																								given_name_k,
																								family_name_r,
																								given_name_r,
																								dob,
																								gender_id,
																								start_date,
																								end_date)
															VALUES (:family_name_k,
																			:given_name_k,
																			:family_name_r,
																			:given_name_r,
																			:dob,
																			:gender_id,
																			:start_date,
																			:end_date)
															RETURNING person_id");
	$stmt->execute(['family_name_k' => $family_name_k,
									'given_name_k' => $given_name_k,
									'family_name_r' => $family_name_r,
									'given_name_r' => $given_name_r,
									'dob' => $dob,
									'gender_id' => $gender_id,
									'start_date' => $start_date,
									'end_date' => $end_date]);
	if ($result = $stmt->fetch()) {
		$person_id = $result['person_id'];
	}
	else {
		$person_id = FALSE;
	}
	close_database_connection($link);
	if (create_people2person_types_entry($person_id,$ptype_id)) {
		return $person_id;
	}
	else {
		return FALSE;
	}
}

// Update an entry into the lookup table people2person_types
//  This matches a person to the person type (i.e. Staff or Student)
function update_people2person_types_entry($person_id,$ptype_id) {
	$link = open_database_connection();
	$stmt = $link->prepare("UPDATE people2person_types
															SET ptype_id = :ptype_id
															WHERE person_id = :person_id");
	$stmt->execute(['person_id' => $person_id,
									'ptype_id' => $ptype_id]);
	if ($stmt->rowCount()) {
		$update_success = TRUE;
	}
	else {
		$update_success = FALSE;
	}
	close_database_connection($link);
	return $update_success;
}

// UPDATE a person's data
function update_person($person_id,$ptype_id,$family_name_k,$given_name_k,$family_name_r,$given_name_r,$dob,$gender_id,$start_date,$end_date) {
	$link = open_database_connection();
	$stmt = $link->prepare("UPDATE people
														SET  family_name_k = :family_name_k,
																 given_name_k = :given_name_k,
																 family_name_r = :family_name_r,
																 given_name_r = :given_name_r,
																 dob = :dob,
																 gender_id = :gender_id,
																 start_date = :start_date,
																 end_date = :end_date
														WHERE person_id = :person_id");
	$stmt->execute(['person_id' => $person_id,
									'family_name_k' => $family_name_k,
									'given_name_k' => $given_name_k,
									'family_name_r' => $family_name_r,
									'given_name_r' => $given_name_r,
									'dob' => $dob,
									'gender_id' => $gender_id,
									'start_date' => $start_date,
									'end_date' => $end_date]);

	if ($stmt->rowCount() && update_people2person_types_entry($person_id,$ptype_id)) {
		$update_success = TRUE;
	}
	else {
		$update_success = FALSE;
	}
	close_database_connection($link);
	return $update_success;
}

?>
