<?php
require_once 'dbh.inc.php';
require_once 'functions.inc.php';

if (isset($_POST["submit"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $group_token = $_POST["token"];
    $token = randToken($conn);

    if (emptyInputLogin($username, $password) === true) {
        $_POST["error"] = "emptyinput";
        header("location: ../signup.php?token=" . $group_token);
        exit();
    }

    signupUser($conn, $username, $password, $token, $group_token);
} else {
    header("location: ../signup.php?token=" . $group_token);
    exit();
}
