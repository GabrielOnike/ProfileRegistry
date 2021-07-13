<?php // Do not put any HTML above this line
session_start();
require_once 'pdo.php';
require_once 'util.php';


//this STMT(statement) handles communication with SQL databse
//... for printing out values
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->query("SELECT first_name, last_name, email, headline, summary FROM profile");

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT profile_id,rank, year, description FROM Position");
$positions = loadPos($pdo, $_REQUEST['profile_id']);

$stmt = $pdo->query("SELECT profile_id,rank, year, institution_id FROM Education");
$schools= loadEdu($pdo, $_REQUEST['profile_id']);
?>

<!DOCTYPE html>
<head>
<?php require_once 'head.php'; ?>
  <title> Gabriel N Onike View Page - 8f3f311c </title>

<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="container">
<h1>Profile information</h1>

<?php
if ( $rows === false ) {
    $_SESSION['error'] = 'This profile has no data';
    header( 'Location: index.php' ) ;
    return;
}

foreach ($rows as $row ){
echo ( "First Name: ".htmlentities($row['first_name']) );
echo ("<p> </p>");
echo ( "Last Name: ".htmlentities($row['last_name']) );
echo ("<p> </p>");
echo ( "Email: ".htmlentities($row['email']) );
echo ("<p> </p>");
echo ( "Headline: ".htmlentities($row['headline']) );
echo ("<p> </p>");
echo ( "Summary: ".htmlentities($row['summary']) );
echo ("<p> </p>");
echo '<p>Education </p>';
echo '<ul>';
};
foreach ($schools as $school){
  echo '<li>';
  echo htmlentities($school['year']) . ': ' . $school['name'];
};
echo '</li><br/>';
echo '</ul>';
echo '<p>Position </p>';
echo '<ul>';
foreach ($positions as $position ){
  echo '<li>';
  echo htmlentities($position['year']) . ': ' . $position['description'];
};
echo '</li><br/>';
echo '</ul>';
?>

<input type="hidden" name="profile_id" value="<? $profile_id ?>" >
<a href="index.php">Done</a>
</div>


</body>
