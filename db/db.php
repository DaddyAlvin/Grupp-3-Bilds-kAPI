<?php
// Database settings for the DDEV environment.
$host = 'db';
$dbUser = 'db';
$dbPassword = 'db';
$dbName = 'db';

// Open the MySQLi database connection.
$con = mysqli_connect($host, $dbUser, $dbPassword, $dbName);

// Stop if the database connection could not be established.
if (!$con) {
    die("Kunde inte ansluta till databasen: " . mysqli_connect_error());
}

// Use utf8mb4 for full Unicode support.
mysqli_set_charset($con, "utf8mb4");