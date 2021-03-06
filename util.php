<?php
//util.php
require_once 'pdo.php';

function flashMessages() {
    //this code below defines what text is printed in the HTML when $empty is invoked
  if ( isset($_SESSION["error"]) ) {
      echo('<p style="color:red">'.$_SESSION["error"]."</p>\n");
      unset($_SESSION["error"]);
  }
  if ( isset($_SESSION['success']) ) {
    echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
    unset($_SESSION['success']);
  }
  //notice use of single nd double quotes
}

function validateProfile() {
  if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 ||
  strlen($_POST['headline'])<1 ||strlen($_POST['email']) < 1 ||
  strlen($_POST['summary']) < 1  )
  {
    return "All fields are required";
  }

  if (strpos($_POST['email'],'@')===false) {
    return "email address is invalid";
  }

  if ($_POST['email'] == "" ){
    return "insert email";
  }
  return true;
}

function validatePos() {
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['year'.$i]) ) continue;
        if ( ! isset($_POST['desc'.$i]) ) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];
        if ( strlen($year) == 0 || strlen($desc) == 0 ) {
            return "All fields are required";
        }
        if ( ! is_numeric($year) ) {
            return "Position year must be numeric";
        }
    }
    return true;
}

function validateEdu() {
  for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['edu_year'.$i]) ) continue;
      if ( ! isset($_POST['edu_school'.$i]) ) continue;
      $edu_year = $_POST['edu_year'.$i];
      $edu_school = $_POST['edu_school'.$i];
      if ( strlen($edu_year) == 0 || strlen($edu_school) == 0 ) {
          return "All fields are required";
      }
      if ( ! is_numeric($edu_year) ) {
          return "Education year must be numeric";
      }
  }
  return true;
}

/*NOTE : FETCHALL()  
  $profiles = array();
  while ($row = $stmt -> fetch(PDO:: FETCH_ASSOC) ) {
  $positions[] = $row;
  }
*/
  function loadPos($pdo, $profile_id){
  $stmt = $pdo-> prepare('SELECT * FROM position WHERE
          profile_id= :prof ORDER BY rank');
  $stmt -> execute ( array(':prof' => $profile_id)); 
  $positions = $stmt -> fetchAll(PDO:: FETCH_ASSOC);
  return $positions ;
}

/*this function and the one above Load up the postions/educations  
from the database to the server */
function loadEdu ($pdo, $profile_id){
  $stmt = $pdo-> prepare('SELECT year, name  FROM Education 
          JOIN Institution
            ON Education.institution_id =Institution.institution_id 
          WHERE profile_id= :prof ORDER BY rank');
  $stmt->execute(array(':prof' => $profile_id)); 
  $educations = $stmt -> fetchAll(PDO:: FETCH_ASSOC);
  return $educations;
}

function insertPositions($pdo, $profile_id) {
  //insert Into Positions Function
  $rank = 1;
  for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['year'.$i]) ) continue;
      if ( ! isset($_POST['desc'.$i]) ) continue;
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];

      $stmt = $pdo->prepare('INSERT INTO Position
          (profile_id, rank, year, description)
      VALUES ( :pid, :rank, :year, :desc)');
      $stmt->execute(array(
          ':pid' =>  $_REQUEST['profile_id'],
          ':rank' => $rank,
          ':year' => $year,
          ':desc' => $desc)
      );
      $rank++;
}
}

function insertEducations ($pdo, $profile_id) {
  //insert Into Education Function
  $rank = 1;
  for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['edu_year'.$i]) ) continue;
      if ( ! isset($_POST['edu_school'.$i]) ) continue;
      $year = $_POST['edu_year'.$i];
      $school = $_POST['edu_school'.$i];

      //Look Up the School from the database
      $institution_id = false;
      $stmt= $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :name');
      $stmt->execute(array(':name' => $school));  
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ( $row !== false ) $institution_id = $row['institution_id'];

      //If there is no intitution/school, insert it
      if ($institution_id === false) {
        $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:name)');
        $stmt->execute(array(':name' => $school));
        $institution_id = $pdo->lastInsertId();
      }

      //Insert into Education
      $stmt = $pdo->prepare('INSERT INTO Education
          (profile_id, rank, year, institution_id)
      VALUES ( :pid, :rank, :year, :iid)');
      $stmt->execute(array(
          ':pid' =>  $_REQUEST['profile_id'],
          ':rank' => $rank,
          ':year' => $year,
          ':iid' =>  $institution_id)
      );
      $rank++;
    }
  }
