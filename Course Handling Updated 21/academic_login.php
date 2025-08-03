<?php
session_start();

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

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  // Validation
  if (empty($username) || empty($password)) {
    $_SESSION['message'] = "All fields are required.";
    $_SESSION['success'] = false;
  } else {
    // Check if credentials are correct
    $stmt = mysqli_prepare($conn, "SELECT username FROM academic WHERE username = ? AND password = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
      mysqli_stmt_bind_result($stmt, $current_academic);
      mysqli_stmt_fetch($stmt);

      $_SESSION['current_academic'] = $current_academic;


      $_SESSION['message'] = "Login successful. Redirecting...";
      $_SESSION['success'] = true;

      // Don't redirect here — let the front-end JS handle it
    } else {
      $_SESSION['message'] = "Invalid Credentials, Please Try Again";
      $_SESSION['success'] = false;
    }
  }

  // Store entered values temporarily
  $_SESSION['old'] = [
    'username' => $username,
    'password' => $password,
  ];

  header("Location: academic_login.php");
  exit();
}

// Retrieve old values if available
if (isset($_SESSION['old'])) {
  $username = $_SESSION['old']['username'];
  $password = $_SESSION['old']['password'];
  unset($_SESSION['old']);
}
?>
<?php if ($success): ?>
  <script>
    // Wait for 1 second then redirect
    setTimeout(function() {
      window.location.href = "academic_page.php";
    }, 1000);
  </script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Academic Login Form</title>
  <link rel="stylesheet" href="CSS/style.css" />
</head>

<body>
  <div class="form-container">
    <form action="" method="post">
      <h1>Academic Login Form</h1><br>

      <div class="form-group">
        <label for="username">username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" />
      </div>

      <div class="form-group">
        <label for="password">password</label>
        <input type="text" id="password" name="password" value="<?= htmlspecialchars($password) ?>" />
      </div>

      <div class="form-group">
        <button type="submit" value="login" class="submit-button">Submit</button>
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