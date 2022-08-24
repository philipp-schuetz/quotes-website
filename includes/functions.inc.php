<?php

function emptyInputLogin($username, $password)
{
    if (empty($username) || empty($password)) {
        return true;
    } else {
        return false;
    }
}

function usernameExists($conn, $username)
{
    $sql = "SELECT * FROM users WHERE username = ?;";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $_POST["error"] = "stmtfailed";
        exit();
    } else {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $resultData = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($resultData)) {
            return $row;
        } else {
            return false;
        }
    }
    mysqli_stmt_close($stmt);
}

function cookieTokenExists($conn, $token)
{
    $sql = "SELECT * FROM users WHERE token = ?;";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $_POST["error"] = "stmtfailed";
        exit();
    } else {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);

        $resultData = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($resultData)) {
            return $row;
        } else {
            return false;
        }
    }
    mysqli_stmt_close($stmt);
}

function loginUser($conn, $username, $password)
{
    $usernameExists = usernameExists($conn, $username);
    $userId = $usernameExists["userid"];

    if ($usernameExists === false) {
        $_POST["error"] = "wronglogin";
        header("location: ../login.php");
        exit();
    }

    $hashedPassword = $usernameExists["passwordhash"];
    $checkPassword = password_verify($password, $hashedPassword);

    if ($checkPassword === false) {
        $_POST["error"] = "wronglogin";
        header("location: ../login.php");
        exit();

    } else if ($checkPassword === true) {
        $_POST["userid"] = $userId;
        $_POST["username"] = $username;
        $cookieToken = $usernameExists["token"];
        setcookie("loginSession", $cookieToken, time() + (86400 * 30), "/");
        header("location: ../index.php");
        exit();
    }
}

function signupUser($conn, $username, $password, $token, $group_token)
{

    $usernameExists = usernameExists($conn, $username);

    if ($usernameExists !== false) {
        $_POST["error"] = "usernameexists";
        header("location: ../signup.php?token=" . $group_token);
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users(username, passwordhash, token) VALUES (?, ?, ?)";

    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $_POST["error"] = "stmtfailed";
        header("location: ../signup.php?token=" . $group_token);
        exit();
    } else {
        mysqli_stmt_bind_param($stmt, "sss", $username, $hashedPassword, $token);

        mysqli_stmt_execute($stmt);

    }
    mysqli_stmt_close($stmt);
        header("location: ../login.php");
        exit();
}

function getUserId($conn, $token) {
    $sql = "SELECT userid FROM users WHERE token = ?;";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $_POST["error"] = "stmtfailed";
    } else {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);

        $resultData = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($resultData)) {
            return $row;
        } else {
            return false;
        }
    }
    mysqli_stmt_close($stmt);
}

function randToken($conn)
{
    $token_exists = true;
    while ($token_exists !== false) {
        $length = 64;
        $keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        $token = implode('', $pieces);

        $token_exists = cookieTokenExists($conn, $token);
    }
    return $token;
}