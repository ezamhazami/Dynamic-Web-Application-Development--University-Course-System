<?php

session_start();
if (!isset($_SESSION['current_academic'])) {
  header('Location: academic_login.php');
  exit();
}
?>
<?php

// Connect to DB
$conn = mysqli_connect("localhost", "root", "", "coursedb");

// Message display
$message = $_SESSION['message'] ?? "";
$success = $_SESSION['success'] ?? false;

// Clear session message after displaying it once
unset($_SESSION['message'], $_SESSION['success']);

// Form values
$username = "";
$password = "";
$confirmPassword = "";

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $confirmPassword = trim($_POST['confirm-password']);

  // Validation
  if (empty($username) || empty($password) || empty($confirmPassword)) {
    $_SESSION['message'] = "All fields are required.";
    $_SESSION['success'] = false;
  } elseif ($password !== $confirmPassword) {
    $_SESSION['message'] = "Passwords do not match.";
    $_SESSION['success'] = false;
  } else {
    // Check if username is taken
    $stmt = mysqli_prepare($conn, "SELECT FacultyID FROM faculty WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
      $_SESSION['message'] = "Username is already taken.";
      $_SESSION['success'] = false;
    } else {
      // Insert new user (note: hash password in real apps)
      $insert = mysqli_prepare($conn, "INSERT INTO faculty (username, password) VALUES (?, ?)");
      mysqli_stmt_bind_param($insert, "ss", $username, $password);

      if (mysqli_stmt_execute($insert)) {
        $_SESSION['message'] = "Registration successful!";
        $_SESSION['success'] = true;

        // Optionally clear inputs
        header("Location: faculty_register.php");
        exit();
      } else {
        $_SESSION['message'] = "Error while registering.";
        $_SESSION['success'] = false;
      }
    }
  }

  // Store entered values temporarily
  $_SESSION['old'] = [
    'username' => $username,
    'password' => $password,
    'confirm-password' => $confirmPassword,
  ];

  header("Location: faculty_register.php");
  exit();
}

// Retrieve old values if available
if (isset($_SESSION['old'])) {
  $username = $_SESSION['old']['username'];
  $password = $_SESSION['old']['password'];
  $confirmPassword = $_SESSION['old']['confirm-password'];
  unset($_SESSION['old']);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Faculty Register Form</title>
  <link rel="stylesheet" href="CSS/style.css" />
</head>

<body>
  <div class="form-container">
    <form action="" method="post">
      <h1>Faculty Register Form</h1><br>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="text" id="password" name="password" value="<?= htmlspecialchars($password) ?>" />
      </div>

      <div class="form-group">
        <label for="confirm-password">Confirm Password</label>
        <input type="text" id="confirm-password" name="confirm-password" value="<?= htmlspecialchars($confirmPassword) ?>" />
      </div>

      <div class="form-group">
        <button type="submit" class="submit-button">Submit</button>
      </div>
      
    </form>

    <?php if (!empty($message)): ?>
      <div id="formOutput" class="output" style="color: <?= $success ? 'green' : 'red' ?>;">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>
  </div>
</body>

</html>