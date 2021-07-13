<?php
require_once "pdo.php";
require_once 'util.php';
require_once 'head.php';
session_start();

if  ( isset($_SESSION ['logout']) ) {
  header('Location: index.php');
  return;
}

$failure = false; // this occurs if we have no POST data sent
$empty = true;

//this STMT(statement) handles communication with SQL databse
//... for printing out values
$stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id FROM Profile");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>


<!DOCTYPE html/>
<html>
<head>
  <title> Gabriel N Onike - Res Profile </title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="container" >
<h1> PROFILE REGISTRY </h1>
<br/>

<p>

<?php flashMessages(); ?>

  <?php
  if ( ! isset($_SESSION['name']) ) {
    if ($rows == false){
      echo("No rows found");
      echo "<br></br>";
      echo ('<a href="login.php"> Please log in </a>');
    }
    else{
    echo ('<a href="login.php"> Please log in </a>');
    echo "<table border='1'.'\n'>";
    echo "<tr>
        <th>Name</th>
        <th>Headline </th>
        </tr>";
    foreach ($rows as $row) {
            echo "<tr><td>";
            echo('<a href="view.php?profile_id='.$row['profile_id'].'"> '.$row['first_name']." ".$row['last_name'].' </a>');
            echo("</td><td>");
            echo(htmlentities($row['headline']));
            echo("</td><td>");
            echo("</td></tr></tbody>\n");
        };
          echo ("</table>\n");
  //as there is no session, any code execution of this page ends here
  die();}
}

?>

<?php

//when there is a session, this table php runs
if (isset($_SESSION['name']) ) {
  if ($rows == false){
    echo("No rows found");
  }
  else {
  echo "<table border='1'.'\n'>";
  echo "<tr>
      <th>Name</th>
      <th>Headline </th>
      <th>Action</th>
        </tr>";
    foreach ($rows as $row) {
        echo "<tr><td>";
        echo('<a href="view.php?profile_id='.$row['profile_id'].'"> '.$row['first_name']." ".$row['last_name'].' </a>');
        echo("</td><td>");
        echo(htmlentities($row['headline']));
        echo("</td><td>");
        echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> ');
        echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
        echo("</td></tr></tbody>\n");

    };
      echo ("</table>\n");
}

echo('<p><a href="add.php">Add New Entry</a></p>'."\n");
echo('<p><a href="logout.php">Logout</a></p>'."\n");
}
  ?>

</p>
</body>
</div>
</html>
