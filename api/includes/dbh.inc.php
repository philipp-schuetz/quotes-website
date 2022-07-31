<?php
$db_auth = parse_ini_file("db_auth.ini");

$serverName = $db_auth['serverName'];
$dbUsername = $db_auth['dbUsername'];
$dbPassword = $db_auth['dbPassword'];
$dbName = $db_auth['dbName'];
$port = $db_auth['port'];

$conn = mysqli_connect($serverName, $dbUsername, $dbPassword, $dbName, $port);

if ($conn === false) {
    die("Connection failed: " . mysqli_connect_error());
}
