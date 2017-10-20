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
      WHERE :date BETWEEN c.start_date AND c.end_date
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

// Find the class instance id for a given class_id and Date
// Optionally, if create is set to TRUE, create a row in class_instances table if one doesn't exist with the given class_id and date
// Arguments: class_id, date, create (boolean)
// Returns the id of the class_instance
function get_class_instance($class_id, $date, $create) {
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT ci.cinstance_id
  	FROM class_instances ci
  	WHERE ci.class_id = :class_id AND ci.cinstance_date = :date");
  $stmt->execute(['class_id' => $class_id, 'date' => $date]);

  // The class instance exists, return its ID
  if ($result = $stmt->fetch()) {
    // echo "<p>class instance exists</p>";
	  close_database_connection($link);
    return $result['cinstance_id'];
  }
  // The class instance does not exist, insert it as a row and return the ID
  elseif ($create) {
    // echo "<p>class instance being created</p>";
    $ins_stmt = $link->prepare("INSERT INTO class_instances (class_id, cinstance_date)
      VALUES (:class_id, :date)
      RETURNING cinstance_id");
    $ins_stmt->execute(['class_id' => $class_id, 'date' => $date]);
    $result = $ins_stmt->fetch();
	  close_database_connection($link);
    return $result['cinstance_id'];
  }
  else {
    close_database_connection($link);
    return;
  }
}


// Check if a class is an All Stars class that gets grades (Child Group, Child Private) using the given class_id
// Arguments: class_id
// Returns boolean (true if it is an All Stars class, else false)
function is_graded_class($class_id) {
  $link = open_database_connection();
  $stmt = $link->prepare("SELECT ct.ctype_name
  	FROM class_types ct
    INNER JOIN classes c
    ON ct.ctype_id = c.ctype_id
  	WHERE c.class_id = :class_id");
  $stmt->execute(['class_id' => $class_id]);

  while ($result = $stmt->fetch()) {
    // The class type is either Child Group or Child Private
    if (stripos($result['ctype_name'], "child") !== FALSE) {
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

?>
