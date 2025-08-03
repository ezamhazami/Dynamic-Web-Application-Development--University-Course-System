<?php
session_start();
if (isset($_SESSION['current_academic'])) {
  unset($_SESSION['current_academic']);
  header("Location: academic_login.php");
}


// Redirect to main page
exit;
