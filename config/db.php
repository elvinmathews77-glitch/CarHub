<?php

/*
|--------------------------------------------------------------------------
| CarHub Database Connection
|--------------------------------------------------------------------------
| Works on:
| 1. Local XAMPP / MySQL
| 2. Railway MySQL
|--------------------------------------------------------------------------
*/

// Railway provides these environment variables.
// If they are not available, the local XAMPP values are used.

$host = getenv("MYSQLHOST") ?: "localhost";

$port = getenv("MYSQLPORT") ?: 3306;

$username = getenv("MYSQLUSER") ?: "root";

$password = getenv("MYSQLPASSWORD") ?: "";

$database = getenv("MYSQLDATABASE") ?: "carhub";


/*
|--------------------------------------------------------------------------
| Create MySQL Connection
|--------------------------------------------------------------------------
*/

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database,
    (int)$port
);


/*
|--------------------------------------------------------------------------
| Check Connection
|--------------------------------------------------------------------------
*/

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


/*
|--------------------------------------------------------------------------
| Character Set
|--------------------------------------------------------------------------
*/

$conn->set_charset("utf8mb4");

?>