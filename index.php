<?php
require_once 'includes/dbh.inc.php';
require_once 'includes/functions.inc.php';
if (isset($_COOKIE["loginSession"])) {
  $tokenExists = cookieTokenExists($conn, $_COOKIE["loginSession"]);
  if ($tokenExists === false) {
    $_POST["redirect"] = "index.php";
    header("location: login.php");
    exit();
  }
} else {
  $_POST["redirect"] = "index.php";
  header("location: login.php");
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
  <title>Quotes | Home</title>
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
  <div class="content-wrapper" id="content">
    <div class="topbar">
      <ul class="topbar-ul">
        <li class="topbar-li"><a href="privacy-policy.html">Privacy Policy</a></li>
        <li class="topbar-li" style="float:right"><a href="includes/logout.inc.php">Logout</a></li>
      </ul>
    </div>
    <div id="searchbar">
      <form action="index.php" id="searchform">
        <?php
        if (isset($_GET["search"])) {
          echo '<input class="searchinput" name="search" placeholder="&#128269;Search" value="'.$_GET["search"].'">';
        } else {
        echo '<input class="searchinput" name="search" placeholder="&#128269;Search" value="'.$_GET["search"].'">';
        }
        ?>
        <button type="submit" class="searchinput-b">Search</button>
      </form>
    </div>
    <div class="add-quote" id="add-quote-div" class="centered">
    </div>
    <div class="centered">
      <button onclick="addQuote(0);">Add Quote</button>
    </div>
    
    <div id="main">
    </div>

      <div id="footer"></div>
  </div>
  </div>
  <script src="script.js"></script>
</body>
</html>