<?php
require_once 'includes/dbh.inc.php';
require_once 'includes/functions.inc.php';
if (isset($_GET["token"])) {
    $token = "";
    if ($_GET["token"] != $token) {
        header("location: index.php");
        exit();
    }
} else {
    header("location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Quotes | Sign Up</title>
</head>
<body>
<noscript>
    <style type="text/css">
      #main-container {display:none;}
    </style>
    <div class="noscriptmsg">
      Please activate JavaScript in your browser to view this site.
    </div>
  </noscript>
  <div id="main-container">
<div id="cookie-banner">
        <h3>We value your privacy</h3>
        We use cookies to enhance your visit.<br>
        By clicking "Accept", you consent to our use of essential cookies.
        <a href="privacy-policy.html#cookies">Cookie Policy</a><br><br>
        <button id="cookie-accept" onclick="cookieAccept();">Accept</button>
    </div>
    <div id="content">
<div class="topbar">
    <ul class="topbar-ul">
        <li class="topbar-li"><a href="privacy-policy.html">Privacy Policy</a></li>
        <li class="topbar-li" style="display: none"><a href="includes/logout.inc.php">Logout</a></li>
    </ul>
</div>
<div class="signup-form">
    <form action="includes/signup.inc.php" method="post">
        <input type="hidden" name="token" value="<?php echo $token ?>">
        <input type="text" placeholder="Username" maxlength="16" name="username" required><br>
        <input type="password" placeholder="Password" maxlength="128" name="password" required><br>
        <button type="submit" name="submit">Signup</button><br>
    </form>

    <?php
    if (isset($_POST["error"])) {
        if ($_POST["error"] == "emptyinput") {
            echo '<span style="color: red;">Fill in all fields</span>';
        } else if ($_POST["error"] == "usernameexists") {
            echo '<span style="color: red;">Username is alredy taken</span>';
        } else if ($_POST["error"] == "stmtfailed") {
            echo '<span style="color: red;">Something went wrong, please try again</span>';
        }
    }
    ?>

</div>
</div>
</div>
<script src="script.js"></script>
</body>
</html>