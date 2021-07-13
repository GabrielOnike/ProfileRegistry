<?php // Do not put any HTML above this line
require_once 'pdo.php';
require_once 'util.php';

session_start();

//if the username isnt set, it should stop all functions
if ( ! isset($_SESSION['user_id']) ) {
  die("ACCESS DENIED");
}

if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to index.php if the user selects cancel
    header("Location: index.php");
    return;
}
// Guardian: Make sure that REQUEST parameter is present
if ( ! isset($_REQUEST['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

//load up profile in question
$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :xyz AND user_id =:uid");
$stmt->execute(array(':xyz' => $_REQUEST['profile_id'], ':uid'=> $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = "Could not load profile";
    header( 'Location: index.php' ) ;
    return;
}

if ( isset($_POST ['first_name']) && isset($_POST ['last_name'])
    && isset($_POST['headline']) && isset($_POST['email']) &&
    isset($_POST['summary']))  {
 //Data validation - this describes the conditions that must be met for the ADD
 //handle incoming data
  $msg = validateProfile(); {
    if ( is_string($msg)) {
      $_SESSION['error'] = $msg;
      header ("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
      return;
    }
  }
  //Validate Position Entries
  $msg = validatePos(); {
    if ( is_string($msg)) {
      $_SESSION['error'] = $msg;
      header ("location: edit.php?profile_id=".$_REQUEST["profile_id"]);
      return;
    }
  }
  //Validate EDucation Entries
  $msg = validateEdu(); {
    if(is_string($msg)){
      $_SESSION['error'] = $msg;
      header ("location:edit.php?profile_id=".$_REQUEST["profile_id"]);
      return;
    }
  }

//Begin To Update the profile in the database using the profile Id
$stmt = $pdo->prepare('UPDATE Profile SET first_name = :fn,
          last_name = :lan, email = :em, headline = :hl, summary = :su
          WHERE profile_id = :pid AND user_id = :uid');
$stmt->execute(array(
    ':pid' => $_REQUEST['profile_id'],
    ':uid' => $_SESSION['user_id'],
    ':fn'  => $_POST['first_name'],
    ':lan' => $_POST['last_name'],
    ':em'  => $_POST['email'],
    ':hl'  => $_POST['headline'],
    ':su'  => $_POST['summary']
  ));

// Clear out the old position entries
   $stmt = $pdo->prepare('DELETE FROM Position
       WHERE profile_id=:pid');
   $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

   // Insert the position entries
   insertPositions($pdo, $_REQUEST['profile_id']);

// Clear out Old EDucation ENtries
  $stmt = $pdo->prepare('DELETE FROM Education
      WHERE profile_id=:pid');
  $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

  // INsert the Education Entries
   insertEducations($pdo, $_REQUEST['profile_id']);

  $_SESSION['success'] = 'Record edited';
  header( 'Location: index.php' ) ;
  return;
}


//load up Position Rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools= loadEdu($pdo, $_REQUEST['profile_id']);

?>


<!DOCTYPE html>
<head>
  <title> Gabriel N Onike Log In Page - 8f3f311c </title>
<meta name="viewport" content="width=device-width, initial-scale=1">
  <?php require_once "head.php"; ?>
</head>
<body>
<div class="container" >
<h1>Edit User</h1>
<?php flashMessages();  ?>

<form method="post" action="edit.php">
  <p>First:
  <input type="text" name="first_name" value="<?= htmlentities($row['first_name']); ?>"></p>
  <p>Last:
  <input type="text" name="last_name" value="<?= htmlentities($row['last_name']); ?>"></p>
  <p>Email:
  <input type="text" name="email" value="<?= htmlentities($row['email']); ?>"></p>
  <p>Headline:
  <input type="text" name="headline" value="<?= htmlentities($row['headline']); ?>"></p>
  <p>Summary:<br/>
  <textarea name="summary" rows="8" cols="80"> <?= htmlentities($row['summary']); ?> </textarea> </p>

<?php
$countEdu=0;
      echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
      echo('<div id="edu_fields">'."\n");
      if (count($schools)>0) {
        foreach($schools as $school) {
          $countEdu++;
        echo('<div id="edu'.$countEdu.'">');
        echo
              '<p> Year: <input type="text" name="edu_year'.$countEdu.'" value="'.htmlentities($school['year']).'" />
              <input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();return false;"></p>
              <p> School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school" id="school"
                  value="'.htmlentities($school['name']).'" />';
        echo "\n</div>\n";
      }
    }
      echo ("</div></p>\n");

$countPos=0;
      echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
      echo('<div id="position_fields">'."\n");
      if (count($positions) >0) {
      foreach ($positions as $position ){
        $countPos++;
        echo ( '<div id="position'.$countPos.'">'."\n");
        echo
        '<p>Year: <input type="text" name="year'.$countPos.'"value="'.htmlentities($position['year']).'" />
        <input type="button" value="-" onclick="$(\'#position'.$countPos.'\').remove();return false;"><br>';
        echo '<p> Description: <input type="text" name="desc'.$countPos.'" size="60"
              value="'.htmlentities($position['description']).'" />';
        echo "\n</textarea>\n</div>\n";
      }
    }
    echo("</div></p>\n");

?>
<p>
<input type="hidden" name="profile_id" value="<?= $_REQUEST['profile_id']; ?>" >
<input type="submit" value="Save"/>
<input type="submit" name="cancel" value="Cancel"></p>
</p>
</form>
<script>
    countPos = <?= $countPos ?>;
    countEdu = <?= $countEdu ?>;


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
          event.preventDefault();
            if( countEdu >= 9 ) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countEdu++;
            window.console && console.log("Adding Education "+countEdu);
            //Grab some HTML and insert into DOM
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
                    <input type="button" value="-" onclick="$('#edu@COUNT@').remove(); return false;"><br>
        <p> School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
        </p>
        </div>
</script>

</div>
</body>
</html>
