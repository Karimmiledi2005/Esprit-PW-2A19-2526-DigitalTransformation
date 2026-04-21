<?php
// CORRECTION : Created logout.php to destroy session and redirect to login
session_start();
$_SESSION = array();
session_destroy();
header("Location: ../login.html");
exit();