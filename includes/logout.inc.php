<?php
setcookie("loginSession", "", time() - 3600, "/");
header("location: ../login.php");
exit();