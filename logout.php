<?php
// Start the session
session_start();

// Destroy all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the correct login page
header("Location: http://localhost/SLPA-Update/login.php");
exit();
?>
