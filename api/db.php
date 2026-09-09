<?php
// Inställningar för DDEV-miljön
$host = 'db';
$dbUser = 'db';
$dbPassword = 'db';
$dbName = 'db';

// Skapa anslutning med MySQLi
$con = mysqli_connect($host, $dbUser, $dbPassword, $dbName);

// Kontrollera om anslutningen lyckades
if (!$con) {
    die("Kunde inte ansluta till databasen: " . mysqli_connect_error());
}

// Sätt teckenkodning till utf8mb4 för korrekt svenska tecken (å, ä, ö)
mysqli_set_charset($con, "utf8mb4");