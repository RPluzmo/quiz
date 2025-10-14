<?php
require_once 'config/config.php';

// Logout user
User::logout();

// Redirect to login page
header('Location: index.php');
exit();
?>