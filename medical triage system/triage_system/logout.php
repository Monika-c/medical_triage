<?php
// logout.php
// BCS403 - DBMS Project
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
