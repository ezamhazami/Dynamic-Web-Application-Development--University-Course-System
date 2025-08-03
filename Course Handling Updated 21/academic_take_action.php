<?php
session_start();

// Session timeout (10 minutes)
$timeout = 600;
if (isset($_COOKIE['last_activity'])) {
  $inactive_time = time() - $_COOKIE['last_activity'];
  if ($inactive_time > $timeout) {
    session_unset();
    session_destroy();
    setcookie("last_activity", "", time() - 3600, "/");
    header("Location: academic_login.php?timeout=1");
    exit();
  }
}
setcookie("last_activity", time(), time() + 3600, "/");

if (!isset($_SESSION['current_academic'])) {
  header("Location: academic_login.php");
  exit();
}

$conn = mysqli_connect("localhost", "root", "", "coursedb");
$appeal_id = $_GET['appeal_id'] ?? null;

// Check if action already taken
$already_acted = mysqli_query($conn, "SELECT * FROM academic WHERE AppealID = '$appeal_id'");
if (mysqli_num_rows($already_acted) > 0) {
  echo "<script>alert('This appeal has already been acted upon.'); window.location='academic_view_appeals.php';</script>";
  exit();
}

$appeal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT StudentID FROM Appeal WHERE AppealID = '$appeal_id'"));
$student_id = $appeal['StudentID'] ?? null;

$current_credit_q = mysqli_query($conn, "SELECT SUM(Credit) as total FROM StudentCourse WHERE StudentID = '$student_id'");
$current_credit = mysqli_fetch_assoc($current_credit_q)['total'] ?? 0;

$add = mysqli_query($conn, "SELECT ac.CourseCode, c.credit FROM AddCourse ac JOIN Course c ON ac.CourseCode = c.CourseCode WHERE ac.AppealID = '$appeal_id'");
$drop = mysqli_query($conn, "SELECT dc.CourseCode, c.credit FROM DropCourse dc JOIN Course c ON dc.CourseCode = c.CourseCode WHERE dc.AppealID = '$appeal_id'");

$total_add = 0;
$total_drop = 0;
while ($a = mysqli_fetch_assoc($add)) $total_add += $a['credit'];
while ($d = mysqli_fetch_assoc($drop)) $total_drop += $d['credit'];

$new_total = $current_credit + $total_add - $total_drop;
$max = 23;
$min = 12;
$eligible = ($new_total <= $max && $new_total >= $min);
$eligibleAdd = $new_total <= $max;
$eligibleDrop = $new_total >= $min;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $note = $_POST['NoteActionTaken'];
  $signature = $_POST['Signature'] ?? '';
  $decision = $_POST['Decision'] ?? '';
  $date = date('Y-m-d');
  $username = $_SESSION['current_academic'];

  $insert = mysqli_query($conn, "INSERT INTO academic (
  AppealID, NoteActionTaken, OOA_SignatureAndStamp, OOA_CommentDate, username, Status
) VALUES (
  '$appeal_id', 
  CONCAT('Decision: $decision\nNote: $note'), 
  '$signature', 
  '$date', 
  '$username', 
  '$decision'
)");

  if ($insert) {

    // Update Appeal Status 
    mysqli_query($conn, "UPDATE Appeal SET Status = '$decision' WHERE AppealID = '$appeal_id'");

    if ($decision === 'Approved') {
      $drop_courses = mysqli_query($conn, "SELECT dc.CourseCode FROM DropCourse dc WHERE dc.AppealID = '$appeal_id'");
      while ($row = mysqli_fetch_assoc($drop_courses)) {
        $code = $row['CourseCode'];
        mysqli_query($conn, "DELETE FROM StudentCourse WHERE StudentID = '$student_id' AND CourseCode = '$code'");
      }

      $add_courses = mysqli_query($conn, "SELECT ac.CourseCode, c.credit 
      FROM AddCourse ac 
      JOIN Course c ON ac.CourseCode = c.CourseCode 
      WHERE ac.AppealID = '$appeal_id'");
      while ($row = mysqli_fetch_assoc($add_courses)) {
        $code = $row['CourseCode'];
        $credit = $row['credit'];

        // Pick a random group (if more than 1 group exists)
        $group_q = mysqli_query($conn, "SELECT GroupNumber FROM Course WHERE CourseCode = '$code' LIMIT 1");
        $group_data = mysqli_fetch_assoc($group_q);
        $group = $group_data['GroupNumber'] ?? 1;

        // Insert into StudentCourse (this table records what the student is taking)
        mysqli_query($conn, "INSERT INTO StudentCourse (StudentID, CourseCode, Credit, `Group`) 
          VALUES ('$student_id', '$code', '$credit', '$group')");
      }
    }

    echo "<script>alert('Action recorded and applied successfully.'); window.location='academic_view_appeals.php';</script>";
  } else {
    echo "<script>alert('Error recording action.');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Take Action</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: linear-gradient(135deg, #e6ecf0, #dbe3e8);
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .dashboard-container {
      width: 400px;
      background: #ffffffcc;
      padding: 30px 40px;
      border-radius: 16px;
      box-shadow: 0 0 10px #aacfd0;
      border: 2px solid #aacfd0;
    }

    h2 {
      color: #2e4a62;
      text-align: center;
      text-shadow: 0 0 4px #aacfd0;
      margin-bottom: 20px;
    }

    p {
      color: #2e4a62;
      font-weight: 500;
      margin: 8px 0;
    }

    .status {
      font-weight: bold;
    }

    .status.green {
      color: #28a745;
    }

    .status.red {
      color: #dc3545;
    }

    form {
      margin-top: 20px;
    }

    label {
      display: block;
      margin-top: 10px;
      color: #2e4a62;
    }

    select,
    textarea {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      background-color: #f4f6f8;
      color: #2e4a62;
      border: 1px solid #aacfd0;
      border-radius: 4px;
      margin-top: 5px;
    }

    button[type="submit"] {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      background-color: #aacfd0;
      color: #fff;
      font-weight: bold;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      box-shadow: 0 0 6px #aacfd0;
      margin-top: 20px;
      transition: background-color 0.3s ease;
    }

    button[type="submit"]:hover {
      background-color: #77a6b6;
      box-shadow: 0 0 12px #77a6b6;
    }
  </style>
</head>

<body>
  <div class="dashboard-container">
    <h2>Take Action</h2>
    <p><strong>Current Credit Hour:</strong> <?= $current_credit ?></p>
    <p><strong>Add:</strong> <?= $total_add ?> | <strong>Drop:</strong> <?= $total_drop ?></p>
    <p><strong>New Total If Approved:</strong> <?= $new_total ?></p>
    <p class="status <?= $eligible ? 'green' : 'red' ?>">
      <?= $eligible ? 'Student is eligible for approval.' : 'Student is NOT eligible (credit hour will be out of range).' ?>
    </p>

    <form method="POST">
      <select name="Decision" required>
        <option value="">-- Select --</option>
        <option value="Approved" <?= !$eligible ? 'disabled style="color:gray;"' : '' ?>>Approve</option>
        <option value="Rejected">Reject</option>
      </select>
      <?php if (!$eligibleAdd): ?>
        <small style="color: red;">Approval is disabled because total credits exceed the limit of <?= $max ?>.</small>
      <?php elseif (!$eligibleDrop): ?>
        <small style="color: red;">Approval is disabled because total credits will be lower than limit of <?= $min ?>.</small>
      <?php endif; ?>


      <label for="NoteActionTaken">Note/Action Taken:</label>
      <textarea name="NoteActionTaken" rows="4"></textarea>

      <button type="submit">Submit Decision</button>
    </form>
  </div>
</body>

</html>