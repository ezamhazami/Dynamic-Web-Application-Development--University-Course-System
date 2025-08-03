<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "coursedb");

if (!isset($_SESSION['currentID'])) {
  header("Location: student_login.php");
  exit();
}

if (isset($_GET['reset'])) {
  $_SESSION['edit_step'] = 1;
  unset($_SESSION['edit_data']);
  header("Location: student_dashboard.php");
  exit();
}

$studentID = $_SESSION['currentID'];
$step = $_SESSION['edit_step'] ?? 1;
$message = "";
$success = false;

// Fetch from DB for initial population
$sql = "SELECT * FROM student WHERE studentID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Load cached data from session (or initialize)
$edit_data = $_SESSION['edit_data'] ?? [
  'studentName' => $data['StudentName'],
  'faculty' => $data['Faculty'],
  'programCode' => $data['ProgramCode'],
  'campus' => $data['Campus'],
  'status' => $data['GraduatingStatus'],
  'email' => $data['Email'],
  'phone' => $data['PhoneNumber'],
  'username' => $data['username'],
];

// Handle POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $valid = true;

  // Handle Back button
  if (isset($_POST['back'])) {
    if ($step > 1) {
      $_SESSION['edit_step'] = $step - 1;
    }
    header("Location: student_edit_info.php");
    exit();
  }


  if (isset($_POST['step1'])) {
    $fields = ['studentName', 'faculty', 'programCode', 'campus', 'status'];
    foreach ($fields as $f) {
      $edit_data[$f] = trim($_POST[$f] ?? '');
      if ($edit_data[$f] === '') $valid = false;
    }
    if ($valid) {
      $_SESSION['edit_data'] = $edit_data;
      $_SESSION['edit_step'] = 2;
      header("Location: student_edit_info.php");
      exit();
    } else {
      $message = "Please fill in all required fields.";
    }
  } elseif (isset($_POST['step2'])) {
    $fields = ['email', 'phone'];
    foreach ($fields as $f) {
      $edit_data[$f] = trim($_POST[$f] ?? '');
      if ($edit_data[$f] === '') $valid = false;
    }
    if ($valid) {
      $_SESSION['edit_data'] = $edit_data;
      $_SESSION['edit_step'] = 3;
      header("Location: student_edit_info.php");
      exit();
    } else {
      $message = "Please fill in all required fields.";
    }
  } elseif (isset($_POST['update'])) {
    $edit_data['username'] = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirmPassword'] ?? '');

    if ($edit_data['username'] === '') {
      $valid = false;
      $message = "Username is required.";
    }

    $updatePassword = false;
    if ($password || $confirm) {
      if ($password !== $confirm) {
        $valid = false;
        $message = "Passwords do not match.";
      } else {
        $updatePassword = true;
      }
    }

    if ($valid) {
      if ($updatePassword) {
        $stmt = mysqli_prepare($conn, "UPDATE student SET StudentName=?, Faculty=?, ProgramCode=?, Email=?, PhoneNumber=?, Campus=?, GraduatingStatus=?, username=?, password=? WHERE StudentID=?");
        mysqli_stmt_bind_param(
          $stmt,
          "sssssssssi",
          $edit_data['studentName'],
          $edit_data['faculty'],
          $edit_data['programCode'],
          $edit_data['email'],
          $edit_data['phone'],
          $edit_data['campus'],
          $edit_data['status'],
          $edit_data['username'],
          $password,
          $studentID
        );
      } else {
        $stmt = mysqli_prepare($conn, "UPDATE student SET StudentName=?, Faculty=?, ProgramCode=?, Email=?, PhoneNumber=?, Campus=?, GraduatingStatus=?, username=? WHERE StudentID=?");
        mysqli_stmt_bind_param(
          $stmt,
          "ssssssssi",
          $edit_data['studentName'],
          $edit_data['faculty'],
          $edit_data['programCode'],
          $edit_data['email'],
          $edit_data['phone'],
          $edit_data['campus'],
          $edit_data['status'],
          $edit_data['username'],
          $studentID
        );
      }

      if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "✅ Information updated successfully!";
        $_SESSION['success'] = true;
        unset($_SESSION['edit_step'], $_SESSION['edit_data']);
        header("Location: student_edit_info.php");
        exit();
      } else {
        $message = "❌ Database error: " . mysqli_error($conn);
      }
    }
  }
}

if (isset($_SESSION['message'])) {
  $message = $_SESSION['message'];
  $success = $_SESSION['success'] ?? false;
  unset($_SESSION['message'], $_SESSION['success']);
}
?>
<?php if ($success): ?>
  <script>
    setTimeout(() => window.location.href = "student_dashboard.php", 1500);
  </script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Student Info</title>
  <link rel="stylesheet" href="CSS/style.css">
  <style>
    .back-btn {
      position: fixed;
      top: 20px;
      left: 20px;
      background: #4e768b;
      color: #fff;
      font-weight: bold;
      padding: 10px 15px;
      border-radius: 6px;
      text-decoration: none;
      box-shadow: 0 0 8px rgba(78, 118, 139, 0.4);
      transition: background-color 0.3s ease, transform 0.2s ease;
      z-index: 1000;
    }

    .back-btn:hover {
      background-color: #3e5f6d;
      transform: scale(1.05);
    }
  </style>
</head>

<body>
  <a class="back-btn" href="student_edit_info.php?reset=1">← Back</a>

  <div class="form-container">
    <form method="post">
      <h1>Edit Info - Step <?= $step ?></h1>

      <?php if ($step == 1): ?>
        <div class="form-group"><label>Full Name</label><input type="text" name="studentName" value="<?= htmlspecialchars($edit_data['studentName']) ?>"></div>
        <div class="form-group"><label>Faculty</label><input type="text" name="faculty" value="<?= htmlspecialchars($edit_data['faculty']) ?>"></div>
        <div class="form-group">
          <label>Program Code</label>
          <select name="programCode">
            <option value="">-- Select --</option>
            <option value="CDCS230" <?= ($edit_data['programCode'] == 'CDCS230') ? 'selected' : '' ?>>CDCS230</option>
            <option value="CDCS251" <?= ($edit_data['programCode'] == 'CDCS251') ? 'selected' : '' ?>>CDCS251</option>
          </select>
        </div>
        <div class="form-group"><label>Campus</label><input type="text" name="campus" value="<?= htmlspecialchars($edit_data['campus']) ?>"></div>
        <div class="form-group status">
          <label>Graduating Status</label><br>
          <input type="radio" name="status" value="Yes" <?= ($edit_data['status'] == 'Yes') ? 'checked' : '' ?>> Yes
          <input type="radio" name="status" value="No" <?= ($edit_data['status'] == 'No') ? 'checked' : '' ?>> No
        </div>
        <button class="submit-button" name="step1">Next</button>


      <?php elseif ($step == 2): ?>
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($edit_data['email']) ?>"></div>
        <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($edit_data['phone']) ?>"></div>
        <button class="submit-button" name="back">← Back</button>
        <button class="submit-button" name="step2">Next</button>


      <?php elseif ($step == 3): ?>
        <div class="form-group"><label>Username</label><input type="text" name="username" value="<?= htmlspecialchars($edit_data['username']) ?>"></div>
        <div class="form-group"><label>New Password (leave blank to keep current)</label><input type="password" name="password"></div>
        <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirmPassword"></div>
        <button class="submit-button" name="back">← Back</button>
        <button class="submit-button" name="update">Update Info</button>

      <?php endif; ?>

      <?php if (!empty($message)): ?>
        <div class="output" style="color: <?= $success ? 'green' : 'red' ?>;"><?= $message ?></div>
      <?php endif; ?>
    </form>
  </div>
</body>

</html>