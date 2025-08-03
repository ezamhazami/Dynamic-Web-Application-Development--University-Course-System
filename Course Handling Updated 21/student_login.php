<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "coursedb");

// Message display
$message = $_SESSION['message'] ?? "";
$success = $_SESSION['success'] ?? false;

// Clear session message after displaying it once
unset($_SESSION['message'], $_SESSION['success']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($username === "" || $password === "") {
    $message = "Please enter both username and password.";
  } else {
    $stmt = mysqli_prepare($conn, "SELECT StudentID FROM student WHERE username = ? AND password = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $student_id);
    mysqli_stmt_fetch($stmt);

    if ($student_id) {
      $_SESSION['currentID'] = $student_id;  // Store StudentID for later use


      $_SESSION['message'] = "Login successful. Redirecting...";
      $_SESSION['success'] = true;
      header("Location: student_login.php");
      exit();
    } else {
      $message = "Invalid username or password.";
    }
  }
}
?>
<?php if ($success): ?>
  <script>
    // Wait for 1 second then redirect
    setTimeout(function() {
      window.location.href = "student_dashboard.php";
    }, 1000);
  </script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Student Login</title>
  <link rel="stylesheet" href="CSS/style.css">
</head>

<body>
  <div class="form-container">
    <?php
    if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
      echo "<script>alert('Session expired due to inactivity. Please log in again.');</script>";
    }
    ?>
    <form method="post">
      <h1>Student Login</h1>

      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter your username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password">
      </div>

      <button type="submit" class="submit-button">Login</button>
      <button type="button" onclick="window.location.href='student_register.php'" class="redirect-button">Go to Student Register</button>
      <?php if (!empty($message)): ?>
        <div id="formOutput" class="output" style="color: <?= $success ? 'green' : 'red' ?>;">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
    </form>
  </div>
</body>

</html>