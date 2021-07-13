<?php // Do not put any HTML above this line
session_start();
require_once 'pdo.php';
require_once 'util.php';

  //if the username isnt in GET, it should stop all functions
  if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
  }

  //logout button redirects user back to index.php
  if  ( isset($_POST ['logout']) ) {
    unset($_SESSION['name']);
    header('Location: index.php');
    return;
  }

  if ( isset($_POST['cancel'] ) ) {
      // Redirect the browser to index.php
      header("Location: index.php");
      return;
  }

  $empty = true;  // this variable is for TRUE statements in data sent

  if ( isset($_POST ['first_name']) && isset($_POST ['last_name'])
      && isset($_POST['headline']) && isset($_POST['email']) &&
      isset($_POST['summary']))   {

  //this describes the conditions that must be met for the ADD
  //handle incoming data
        $msg = validateProfile(); {
          if ( is_string($msg)) {
            $_SESSION['error'] = $msg;
            header ("location: add.php");
            return;
          }
        }

        $msg = validatePos(); {
          if ( is_string($msg)) {
            $_SESSION['error'] = $msg;
            header ("location: add.php");
            return;
          }
        }
          //Validate EDucation Entries
        $msg = validateEdu(); {
          if(is_string($msg)){
            $_SESSION['error'] = $msg;
            header ("location: add.php");
            return;
          }
        }

  //this is the code inserting the data into the database with PDO
  $stmt = $pdo->prepare('INSERT INTO Profile
  (user_id, first_name, last_name, email, headline, summary)
  VALUES ( :uid, :fn, :ln, :em, :he, :su)');
  $stmt->execute(array(
      ':uid' => $_SESSION['user_id'],
      ':fn' => $_POST['first_name'],
      ':ln' => $_POST['last_name'],
      ':em' => $_POST['email'],
      ':he' => $_POST['headline'],
      ':su' => $_POST['summary'])
    );
    $profile_id = $pdo->lastInsertId();

  insertEducations($pdo, $_REQUEST['profile_id']);

  insertPositions($pdo, $_REQUEST['profile_id']);

  $_SESSION['success'] = "Profile added";
  header("Location: index.php");
  return;

};

?>

<!DOCTYPE html>
<html>
<head>
  <title>
    Gabriel N Onike Patients Database - 8f3f311c
  </title>
<meta name="viewport" content="width=device-width, initial-scale=1">
  <?php require_once "head.php"; ?>
</head>
<body>
<div class = "container" >
  <h1> Add Profile
  <?php
  // if there's a name value in the REQUEST, it presents it below in the HTML
    if (isset ($_REQUEST["name"]) ){
      echo "for ";
      echo '<p></p>';
      echo htmlentities ($_REQUEST["name"]);
    }
  ?>
  </h1>
  <?php
  flashMessages();
  ?>

<form method='post' >
          <p> <label for="first_name"> First Name:</label>
          <input
          type="text" name="first_name" id="first_name"
          size="40"
          />
          </p>

          <p> <label for="last_name"> Last Name:</label>
          <input
          type="text" name="last_name" id="last_name"
          size="40"
          />
          </p>

          <p> <label for="email"> Email:</label>
          <input
          type="text" name="email" id="email"
          size="30"
          />
          </p>

          <p> <label for="headline"> Headline:</label>
            <br>
          <input
          type="text" name="headline" id="headline"
          size="80"
          />
          </p>

          <p> <label for="summary"> Summary:</label>
            <br>
          <textarea name="summary" rows="8" cols="80" style="margin: 0px;
          height: 280px; width: 574px;"></textarea>
          </p>

          <p>
            Education: <input type="submit" id="addEdu" value="+">
            <div id="edu_fields">
            </div>
          </p>

          <p>
            Position: <input type="submit" id="addPos" value="+">
            <div id="position_fields">
            </div>
          </p>

          <input type="submit" value="Add"  >
          <input type="submit" name="cancel" value="Cancel">
</form>
<script>
countPos = 0;
countEdu = 0;

// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');

    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
               Description: <input type="text" name="desc'+countPos+'" size="60"></textarea>\
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            </div>');
    });

    $('#addEdu').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding Education "+countEdu);
        //Grab some substition HTML and insert into DOM
        var source= $("#edu-template").html();
        $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

        //Add the event handler to the new ones
        $('.school').autocomplete({ source: "school.php" });
      });
      $('.school').autocomplete({ source: "school.php" });

});
</script>

<!-- HTML with substitution HotSpots-->
<script id="edu-template" type="text">
        <div id="edu@COUNT@">
        <p> Year:   <input type="text" name="edu_year@COUNT@" value="" />
                    <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
        <p> School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
        </p>
        </div>
</script>

</div>
</body>
</html>
