<?php // Do not put any HTML above this line
require_once 'pdo.php';
require_once 'util.php';
require_once 'head.php';

session_start();
unset($_SESSION['name']); //to Log the User Out
unset($_SESSION['user_id']);    //to Log the User Out

if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to index.php
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';
      // sample Pw is php123 and we add salt to strengthen it
      // stored hash is the concatenated of $salt.$stored_hash

  //check to see if we have some POST data, if we do, process it
  if ( isset($_POST['email']) && isset($_POST['pass']) ) {
      unset($_SESSION['email']);
      if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
          $_SESSION['error'] = "Username and password are required";
          header("Location: login.php");
          return;
      }
      if (!stristr($_POST['email'],"@") ) {
        $_SESSION['error']="Email must have an at-sign (@)";
        header("Location: login.php");
        return;
        }
      else {
          $check = hash('md5', $salt.$_POST['pass']);
          $stmt = $pdo->prepare('SELECT user_id, name FROM users
            WHERE email = :em AND password = :pw');
            $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

          if ( $row !== false ) {
                $_SESSION['name'] = $row['name'];
                $_SESSION['user_id'] = $row['user_id'];
                // Redirect the browser to index.php
                header("Location: index.php");
                return;
          } else {
            {error_log("Login fail ".$_POST['email']." $check"); }
              $_SESSION['error'] = "Incorrect password" ;
              header('Location: login.php');
              return;
      }
    }
  }
// done with model (the code above), we fall through into view(html) -> NEXT
?>

<!DOCTYPE html>
<html>
  <head>
    <title> Gabriel N Onike Log In Page - 8f3f311c </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
<body>
<div class="container" >
<h1> LOG IN HERE </h1>

<?php
//default password on page is set to null(nothing) = false by
//... by invoking failure
flashMessages();
?>
<form method="POST">
<label for="nam">User Name</label>
<input type="text" name="email" id="nam"><br/>
<label for="id_1723">Password</label>
<input type="text" name="pass" id="id_1723"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>
<p>
Find password hint in page source
<!-- HINT : password is 3 character name of the programming langauge on
on this page followed by 123 -->
</p>
<script>

  function doValidate() {
    console.log('Validating...');
    try {
        addr = document.getElementById('nam').value;
        pw = document.getElementById('id_1723').value;
        console.log("Validating addr="+addr+" pw="+pw);
        if (addr == null || addr == "" || pw == null || pw == "") {
            alert("Both fields must be filled out");
            return false;
        }
        if ( addr.indexOf('@') == -1 ) {
            alert("Invalid email address");
            return false;
        }
        return true;
    } catch(e) {
        return false;
    }
    return false;
}

</script>
</div>
</body>
