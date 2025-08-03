<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "coursedb");

// Step tracker
$step = $_SESSION['step'] ?? 1;

// Field list
$fields = ['studentName', 'faculty', 'programCode', 'campus', 'status', 'email', 'phone', 'username', 'password', 'confirmPassword'];

// Retrieve session data or set default
$data = $_SESSION['reg_data'] ?? array_fill_keys($fields, '');

// Feedback message
$message = $_SESSION['message'] ?? '';
$success = $_SESSION['success'] ?? false;
unset($_SESSION['message'], $_SESSION['success']);

// Handle form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $valid = true;

  // Handle Back button
  if (isset($_POST['back'])) {
    if ($step > 1) {
      $_SESSION['step'] = $step - 1;
    }
    header("Location: student_register.php");
    exit();
  }


  if (isset($_POST['step1'])) {
    $step = 1;
    $currentFields = ['studentName', 'faculty', 'programCode', 'campus', 'status'];
  } elseif (isset($_POST['step2'])) {
    $step = 2;
    $currentFields = ['email', 'phone'];
  } elseif (isset($_POST['register'])) {
    $step = 3;
    $currentFields = ['username', 'password', 'confirmPassword'];
  }

  // Trim and store inputs in session
  foreach ($currentFields as $field) {
    $data[$field] = trim($_POST[$field] ?? '');
    if ($data[$field] === '') $valid = false;
  }

  $_SESSION['reg_data'] = $data;

  if (!$valid) {
    $message = "Please fill in all required fields.";
  } elseif ($step === 3 && isset($_POST['register'])) {
    // Check password match
    if ($data['password'] !== $data['confirmPassword']) {
      $message = "Passwords do not match.";
      $valid = false;
    } else {
      // Username uniqueness check
      $stmt = mysqli_prepare($conn, "SELECT StudentID FROM student WHERE username = ?");
      mysqli_stmt_bind_param($stmt, "s", $data['username']);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_store_result($stmt);

      if (mysqli_stmt_num_rows($stmt) > 0) {
        $message = "Username already taken.";
        $valid = false;
      } else {
        // All checks passed, insert into DB
        $stmt = mysqli_prepare($conn, "INSERT INTO student (StudentName, Faculty, ProgramCode, Email, PhoneNumber, Campus, GraduatingStatus, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param(
          $stmt,
          "sssssssss",
          $data['studentName'],
          $data['faculty'],
          $data['programCode'],
          $data['email'],
          $data['phone'],
          $data['campus'],
          $data['status'],
          $data['username'],
          $data['password']
        );

        if (mysqli_stmt_execute($stmt)) {
          $_SESSION['message'] = "✅ Registration successful. Redirecting...";
          $_SESSION['success'] = true;

          header("Location: student_register.php");
          exit();
        } else {
          $message = "❌ Database error: " . mysqli_error($conn);
        }
      }
    }
  }

  // Move to next step if valid
  if ($valid && empty($message)) {
    if (isset($_POST['step1'])) {
      $_SESSION['step'] = 2;
    } elseif (isset($_POST['step2'])) {
      $_SESSION['step'] = 3;
    }
    header("Location: student_register.php");
    exit();
  }
}
?>

<?php if ($success): ?>
  <?php session_unset() ?>
  <script>
    setTimeout(() => window.location.href = "student_login.php", 1500);
  </script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Student Registration</title>
  <link rel="stylesheet" href="CSS/style.css">
</head>

<body>
  <div class="form-container">
    <form method="post">
      <h1>Student Registration - Step <?= htmlspecialchars($_SESSION['step'] ?? 1) ?></h1>

      <?php if ($step == 1): ?>
        <div class="form-group"><label>Full Name</label><input type="text" name="studentName" value="<?= htmlspecialchars($data['studentName']) ?>"></div>
        <div class="form-group"><label>Faculty</label><input type="text" name="faculty" value="<?= htmlspecialchars($data['faculty']) ?>"></div>
        <div class="form-group">
          <label>Program Code</label>
          <select name="programCode">
            <option value="">-- Select Program --</option>
            <option value="CDCS230" <?= ($data['programCode'] == 'CDCS230') ? 'selected' : '' ?>>CDCS230</option>
            <option value="CDCS251" <?= ($data['programCode'] == 'CDCS251') ? 'selected' : '' ?>>CDCS251</option>
          </select>
        </div>
        <div class="form-group"><label>Campus</label><input type="text" name="campus" value="<?= htmlspecialchars($data['campus']) ?>"></div>
        <div class="form-group status">
          <label>Graduating Status</label><br>
          <input type="radio" name="status" value="Yes" <?= ($data['status'] == 'Yes') ? 'checked' : '' ?>> Yes
          <input type="radio" name="status" value="No" <?= ($data['status'] == 'No') ? 'checked' : '' ?>> No
        </div>
        <button name="step1" type="submit" class="submit-button">Next</button>
        <button type="button" onclick="window.location.href='student_login.php'" class="redirect-button">Go to Student Login</button>

      <?php elseif ($step == 2): ?>
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>"></div>
        <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($data['phone']) ?>"></div>
        <button name="back" type="submit" class="submit-button">← Back</button>
        <button name="step2" type="submit" class="submit-button">Next</button>


      <?php elseif ($step == 3): ?>
        <div class="form-group"><label>Username</label><input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>"></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" value="<?= htmlspecialchars($data['password']) ?>"></div>
        <div class="form-group"><label>Confirm Password</label><input type="password" name="confirmPassword" value="<?= htmlspecialchars($data['confirmPassword']) ?>"></div>
        <button name="back" type="submit" class="submit-button">← Back</button>
        <button name="register" type="submit" class="submit-button">Register</button>

      <?php endif; ?>

      <?php if (!empty($message)): ?>
        <div class="output" style="color: <?= $success ? 'green' : 'red' ?>;"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
    </form>
  </div>
</body>

</html>