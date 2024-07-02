<?php
session_start();

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to the login page
header("Location: index.php");
exit();
