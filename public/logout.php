<?php
// NO whitespace or empty lines before <?php

session_start();

// Clear all session data
$_SESSION = [];      // optional, ensures session array is cleared
session_unset();     // frees all session variables
session_destroy();   // destroys the session

// Redirect to login page
header("Location: index.php");
exit();