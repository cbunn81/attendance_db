<?php
/* Common functions */



// Find the class instance id for a given class_id and Date
// Optionally, if create is set to TRUE, create a row in class_instances table if one doesn't exist with the given class_id and date
// Arguments: class_id, date, create (boolean)
// Returns the id of the class_instance
function get_class_instance($class_id, $date, $create) {
  require(dirname(__FILE__).'/../../../config/db.inc.php');
  $stmt = $pdo->prepare("SELECT ci.cinstance_id
  	FROM class_instances ci
  	WHERE ci.class_id = :class_id AND ci.cinstance_date = :date");
  $stmt->execute(['class_id' => $class_id, 'date' => $date]);

  // The class instance exists, return its ID
  if ($result = $stmt->fetch()) {
    // echo "<p>class instance exists</p>";
    return $result['cinstance_id'];
  }
  // The class instance does not exist, insert it as a row and return the ID
  elseif ($create) {
    // echo "<p>class instance being created</p>";
    $ins_stmt = $pdo->prepare("INSERT INTO class_instances (class_id, cinstance_date)
      VALUES (:class_id, :date)
      RETURNING cinstance_id");
    $ins_stmt->execute(['class_id' => $class_id, 'date' => $date]);
    $result = $ins_stmt->fetch();
    return $result['cinstance_id'];
  }
  return $cinstance_id;
}


// Check if a class is an All Stars class that gets grades (Child Group, Child Private) using the given class_id
// Arguments: class_id
// Returns boolean (true if it is an All Stars class, else false)
function is_graded_class($class_id) {
  require(dirname(__FILE__).'/../../../config/db.inc.php');
  $stmt = $pdo->prepare("SELECT ct.ctype_name
  	FROM class_types ct
    INNER JOIN classes c
    ON ct.ctype_id = c.ctype_id
  	WHERE c.class_id = :class_id");
  $stmt->execute(['class_id' => $class_id]);

  while ($result = $stmt->fetch()) {
    // The class type is either Child Group or Child Private
    if (stripos($result['ctype_name'], "child") !== FALSE) {
      // echo "<p>It is a Child class.</p>";
      return TRUE;
    }
    // The class instance does not exist, insert it as a row and return the ID
    else {
      // echo "<p>It is NOT a Child class.</p>";
      return FALSE;
    }
  }
}

// Check if an attendance is a makeup lesson
// Arguments: cinstance_id
// Returns boolean (true if it is a makeup lesson, else false)
function is_makeup_lesson($cinstance_id) {
  require(dirname(__FILE__).'/../../../config/db.inc.php');
  $stmt = $pdo->prepare("SELECT m.makeup_cinstance_id
  	FROM makeup m
  	WHERE m.makeup_cinstance_id = :cinstance_id");
  $stmt->execute(['cinstance_id' => $cinstance_id]);

  if($stmt->fetch())  {
      // row found
      return TRUE;
  }
  else {
      // row not found
      return FALSE;
  }
}


// Get an array containing the names of all the grade types.
// Arguments: none
// Returns array of strings
function get_grade_types() {
  require(dirname(__FILE__).'/../../../config/db.inc.php');
  // initiate array for grade types
  $grade_types = array();
  $stmt = $pdo->prepare("SELECT gtype_id, gtype_name FROM grade_types ORDER BY gtype_id");
  $stmt->execute();

  while($row = $stmt->fetch()) {
    $grade_types[$row['gtype_id']] = $row['gtype_name'];
  }
  return $grade_types;
}
?>
