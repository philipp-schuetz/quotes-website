<?php
require_once 'dbh.inc.php';
require_once 'functions.inc.php';

if (isset($_POST["submit"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $token = randomStr(64);

    if (emptyInputLogin($username, $password) !== false) {
        $_POST["error"] = "emptyinput";
        header("location: ../signup.php");
        exit();
    }

    signupUser($conn, $username, $password, $token);
} else {
    header("location: ../signup.php");
    exit();
}
