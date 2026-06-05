<?php
// config/db.php
// BCS403 - DBMS Project
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'triage_db';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8mb4");
?>
