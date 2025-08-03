<?php
session_start();
if (isset($_SESSION['currentID'])) {
  unset($_SESSION['currentID']);
  header("Location: student_login.php");
}
