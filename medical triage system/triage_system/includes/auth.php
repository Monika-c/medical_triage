<?php
// includes/auth.php
// BCS403 - DBMS Project
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = '/triage_system';

// If admin is not logged in, redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: $base_url/login.php");
    exit();
}
?>
