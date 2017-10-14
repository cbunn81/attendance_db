<?php

require_once('../../config/db.inc.php');

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
?>
