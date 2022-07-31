<?php

function emptyInputLogin($username, $password)
{
    $result = true;
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

function signupUser($conn, $username, $password, $token)
{

    $usernameExists = usernameExists($conn, $username);

    if ($usernameExists !== false) {
        $_GET["error"] = "usernameexists";
        header("location: ../signup.php");
        exit();
    }
    if ($usernameExists["token_used"] != 0) {
        $_GET["error"] = "token_in_use";
        header("location: ../login.php");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $tokenUsed = 1;
    $sql = "UPDATE users SET users.username = ?, users.passwordhash = ?, users.token_used = ? WHERE users.token = ?";

    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $_POST["error"] = "stmtfailed";
        header("location: ../signup.php");
        exit();
    } else {
        mysqli_stmt_bind_param($stmt, "ssis", $username, $hashedPassword, $tokenUsed, $token);

        mysqli_stmt_execute($stmt);

    }
    mysqli_stmt_close($stmt);
        header("location: ../login.php");
        exit();
}


function getQuotes($conn, $quoteStartId, $quoteEndId, $count = false) {
    if ($count === false) {
        $sql = "SELECT quotes.quoteid, quotes.unix_timestamp, users.username userid, quotes.content FROM quotes JOIN users ON quotes.userid = users.userid WHERE quoteid >= " . $quoteStartId . " AND quoteid <= " . $quoteEndId . " ORDER BY quoteid ASC";
    } else {
        $sql = "SELECT quoteid FROM quotes";
    }
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        return $result;
    } else {
        return false;
    }
}

function getQuotesSearch($conn, $quoteStartId, $quoteEndId, $search) {
    $param = "%{$search}%";

    $sql = "SELECT quotes.quoteid, quotes.unix_timestamp, users.username userid, quotes.content FROM quotes JOIN users ON quotes.userid = users.userid WHERE content LIKE ? AND quoteid >= ? AND quoteid <= ?";

    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $_POST["error"] = "stmtfailed";
        header("location: ../index.php");
        exit();
    } else {
        mysqli_stmt_bind_param($stmt, "sii", $param, $quoteStartId, $quoteEndId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            return $result;
        } else {
            return false;
        }
    }
    mysqli_stmt_close($stmt);
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