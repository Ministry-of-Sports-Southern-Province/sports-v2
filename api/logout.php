<?php
require_once '../includes/auth.php';

// Clear session
clearAdminSession();

// Redirect to login
header('Location: ../login.php');
exit();
