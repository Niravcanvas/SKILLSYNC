<?php
session_start();

$_SESSION = [];
session_unset();
session_destroy();

// index.php is at root, one level up from /public/
header("Location: ../index.php");
exit();