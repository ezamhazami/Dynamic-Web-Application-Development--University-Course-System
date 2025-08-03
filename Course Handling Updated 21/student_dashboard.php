<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "coursedb");

if (!isset($_SESSION['currentID'])) {
  header("Location: student_login.php");
  exit();
}

$student_id = $_SESSION['currentID'];

// Fetch student username
$stmt = $conn->prepare("SELECT Username FROM Student WHERE StudentID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
  $problem = mysqli_real_escape_string($conn, $_POST['prob']);
  $request = mysqli_real_escape_string($conn, $_POST['req']);
  $date = date('Y-m-d');

  if (isset($_POST['add_course'])) {
    $check_sql = "SELECT ac.AddCourseID
      FROM AddCourse ac
      JOIN Appeal a ON ac.AppealID = a.AppealID
      WHERE a.StudentID = '$student_id' AND ac.CourseCode = '$course_code'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
      $_SESSION['flash_error'] = "You have already appealed to add this course.";
    } else {
      $sql = "INSERT INTO Appeal (StudentID, Problem, Request, Signature, AppealDate)
              VALUES ('$student_id', '$problem', '$request', 'SignedByStudent', '$date')";
      if (mysqli_query($conn, $sql)) {
        $appeal_id = mysqli_insert_id($conn);
        $sql_add = "INSERT INTO AddCourse (AppealID, CourseCode)
                    VALUES ('$appeal_id', '$course_code')";
        if (mysqli_query($conn, $sql_add)) {
          $_SESSION['flash_success'] = "Add course appeal submitted.";
        } else {
          $_SESSION['flash_error'] = "Error adding course: " . mysqli_error($conn);
        }
      } else {
        $_SESSION['flash_error'] = "Error submitting appeal: " . mysqli_error($conn);
      }
    }
  }

  if (isset($_POST['drop_course'])) {
    $check_sql = "SELECT dc.DropCourseID
      FROM DropCourse dc
      JOIN Appeal a ON dc.AppealID = a.AppealID
      WHERE a.StudentID = '$student_id' AND dc.CourseCode = '$course_code'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
      $_SESSION['flash_error'] = "You have already appealed to drop this course.";
    } else {
      $sql = "INSERT INTO Appeal (StudentID, Problem, Request, Signature, AppealDate)
              VALUES ('$student_id', '$problem', '$request', 'SignedByStudent', '$date')";
      if (mysqli_query($conn, $sql)) {
        $appeal_id = mysqli_insert_id($conn);
        $sql_drop = "INSERT INTO DropCourse (AppealID, CourseCode)
                     VALUES ('$appeal_id', '$course_code')";
        if (mysqli_query($conn, $sql_drop)) {
          $_SESSION['flash_success'] = "Drop course appeal submitted.";
        } else {
          $_SESSION['flash_error'] = "Error dropping course: " . mysqli_error($conn);
        }
      } else {
        $_SESSION['flash_error'] = "Error submitting appeal: " . mysqli_error($conn);
      }
    }
  }

  if (isset($_POST['update_info'])) {
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $sql = "UPDATE Student SET PhoneNumber = '$phone', Email = '$email' WHERE StudentID = '$student_id'";
    if (mysqli_query($conn, $sql)) {
      $_SESSION['flash_success'] = "Information updated!";
    } else {
      $_SESSION['flash_error'] = "Error updating info: " . mysqli_error($conn);
    }
  }

  header("Location: student_dashboard.php");
  exit();
}

// Fetch student info
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Email, PhoneNumber FROM Student WHERE StudentID = '$student_id'"));

// Get list of courses student is currently enrolled in
$courses = [];
$res = mysqli_query($conn, "SELECT CourseCode, Credit, `Group` FROM StudentCourse WHERE StudentID = '$student_id'");
while ($row = mysqli_fetch_assoc($res)) {
  $courses[] = $row;
}

// Get list of all course codes from Course table
$all_courses = [];
$res = mysqli_query($conn, "SELECT CourseCode FROM Course ORDER BY CourseCode");
while ($row = mysqli_fetch_assoc($res)) {
  $all_courses[] = $row['CourseCode'];
}

//Get Current Credit Hour
$current_credit_q = mysqli_query($conn, "SELECT SUM(Credit) as total FROM StudentCourse WHERE StudentID = '$student_id'");
$current_credit = mysqli_fetch_assoc($current_credit_q)['total'] ?? 0;
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="style_scrollbar.css" />
  <style>
    body {
      background: linear-gradient(135deg, #eaf2f8, #d6e3ec);
      /* soft blue gradient */
      color: #2e4a62;
      /* cool dark navy for body text */
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 20px;
    }

    .dashboard-container {
      max-width: 600px;
      margin: auto;
      background: #ffffffcc;
      padding: 30px;
      border-radius: 12px;
      backdrop-filter: blur(6px);
      box-shadow: 0 0 10px #aacfd0;
      border: 2px solid #aacfd0;
    }

    h1,
    h2,
    h3 {
      text-align: center;
      color: #2e4a62;
    }

    form {
      margin-bottom: 20px;
    }

    input[type="text"],
    input[type="email"],
    select {
      width: 100%;
      padding: 10px;
      border: 1px solid #aacfd0;
      border-radius: 4px;
      background: #f9fbfc;
      color: #2e4a62;
      margin-top: 5px;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    select:focus {
      outline: none;
      border-color: #4e768b;
      box-shadow: 0 0 6px rgba(78, 118, 139, 0.5);
    }


    button {
      padding: 10px 15px;
      border: none;
      background: #4e768b;
      /* DARKER BLUE-GRAY */
      color: #fff;
      font-weight: bold;
      border-radius: 4px;
      cursor: pointer;
      margin-top: 10px;
      transition: background-color 0.3s ease, box-shadow 0.2s ease;
      justify-content: center;
      text-align: center;
    }

    button:hover {
      background: #3e5f6d;
      /* Even darker on hover */
      box-shadow: 0 0 6px rgba(78, 118, 139, 0.6);
    }

    .styled-link {
      display: inline-block;
      padding: 10px 15px;
      background: #4e768b;
      color: #fff;
      font-weight: bold;
      border-radius: 4px;
      text-decoration: none;
      margin-top: 10px;
      transition: background-color 0.3s ease, box-shadow 0.2s ease;
    }

    .styled-link:hover {
      background: #3e5f6d;
      box-shadow: 0 0 6px rgba(78, 118, 139, 0.6);
    }


    .cancel-btn {
      background: #b0b8be;
      color: #2e4a62;
      margin-left: 10px;
    }

    .cancel-btn:hover {
      background: #9aa3aa;
    }

    .messages {
      margin-bottom: 10px;
      text-align: center;
    }

    .error {
      color: #ff5555;
      margin-bottom: 10px;
    }

    .success {
      color: #41748d;
      margin-bottom: 10px;
    }

    .course-list {
      list-style: none;
      padding: 0;
    }

    .course-list li {
      background: rgba(46, 74, 98, 0.08);
      padding: 6px 10px;
      margin-bottom: 6px;
      border-radius: 4px;
    }

    label {
      margin-top: 10px;
      display: block;
      color: #2e4a62;
    }

    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 30, 60, 0.4);
      backdrop-filter: blur(6px);
      justify-content: center;
      align-items: center;
      z-index: 999;
    }

    .modal-box {
      background: #ffffffcc;
      padding: 25px;
      border-radius: 12px;
      max-width: 400px;
      width: 90%;
      color: #2e4a62;
      box-shadow: 0 0 10px #aacfd0;
      border: 2px solid #aacfd0;
      animation: fadeInModal 0.3s ease-out;
    }

    @keyframes fadeInModal {
      from {
        transform: scale(0.95);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .form-group {
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
      gap: 20px;
    }

    .back-btn {
      position: fixed;
      top: 20px;
      right: 20px;
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
  <a class="back-btn" href="student_logout.php">logout</a>
  <div class="dashboard-container">
    <h1>Student Dashboard</h1>
    <p style="text-align:center; font-weight:bold; color:#2e4a62;">Welcome, <?= htmlspecialchars($username) ?>!</p>

    <div class="messages">
      <?php if (!empty($flash_success)): ?>
        <p class="success"><?= htmlspecialchars($flash_success) ?></p>
      <?php elseif (!empty($flash_error)): ?>
        <p class="error"><?= htmlspecialchars($flash_error) ?></p>
      <?php endif; ?>
    </div>

    <h2>Appeal Action</h2>
    <div class="form-group appeal">
      <!-- Trigger Button -->
      <button type="button" onclick="openAddCourseModal()">Appeal to Add Course</button>

      <!-- Trigger Button -->
      <button type="button" onclick="openDropCourseModal()">Appeal to Drop Course</button>
    </div>




    <h2>Appeal History</h2>
    <div class="form-group">
      <a href="student_appeal_history.php" class="styled-link">Check Appeal History</a>
    </div>


    <h2>My Courses</h2>
    <ul class="course-list">
      <?php foreach ($courses as $c): ?>
        <li>Course Code: <?= htmlspecialchars($c['CourseCode']) ?>, Credit Hour : <?= htmlspecialchars($c['Credit']) ?>, Group: <?= htmlspecialchars($c['Group']) ?></li>
      <?php endforeach; ?>
      <?php if (empty($courses)): ?>
        <li>No courses added yet.</li>
      <?php endif; ?>
      <li>Current Total Credit Hour: <?= htmlspecialchars($current_credit) ?> </li>
    </ul>

    <h2>Edit My Information</h2>
    <form action="" method="post">
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($student['Email']) ?>" readonly>
      <label>Phone</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($student['PhoneNumber']) ?>" readonly>
      <a href="student_edit_info.php" class="styled-link">Update Info</a>
    </form>
  </div>
  </div>

  <!-- Add Course Modal -->
  <div id="addCourseModal" class="modal-overlay">
    <div class="modal-box">
      <h3>Select Course & Group</h3>
      <form method="post">
        <label>Course</label>
        <select name="course_code" required>
          <option value="">-- Select Course --</option>
          <?php foreach ($all_courses as $code): ?>
            <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($code) ?></option>
          <?php endforeach; ?>

        </select>

        <label>Problem</label>
        <input type="text" name="prob">

        <label>Request</label>
        <input type="text" name="req">

        <div style="margin-top:10px;">
          <button type="submit" name="add_course">Confirm</button>
          <button type="button" onclick="closeAddCourseModal()" class="cancel-btn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Drop Course Modal -->
  <div id="dropCourseModal" class="modal-overlay">
    <div class="modal-box">
      <h3>Select Course to Drop</h3>
      <form method="post">
        <label>Course</label>
        <select name="course_code" required>
          <option value="">-- Select Course --</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= htmlspecialchars($c['CourseCode']) ?>"><?= htmlspecialchars($c['CourseCode']) ?></option>
          <?php endforeach; ?>
        </select>

        <label>Problem</label>
        <input type="text" name="prob">

        <label>Request</label>
        <input type="text" name="req">

        <div style="margin-top:10px;">
          <button type="submit" name="drop_course">Drop Course</button>
          <button type="button" onclick="closeDropCourseModal()" class="cancel-btn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</body>

</html>

<script>
  function openAddCourseModal() {
    document.getElementById('addCourseModal').style.display = 'flex';
  }

  function closeAddCourseModal() {
    document.getElementById('addCourseModal').style.display = 'none';
  }

  function openDropCourseModal() {
    document.getElementById('dropCourseModal').style.display = 'flex';
  }

  function closeDropCourseModal() {
    document.getElementById('dropCourseModal').style.display = 'none';
  }
</script>