<?php
session_start();
$timeout = 600;
if (isset($_COOKIE['last_activity'])) {
  $inactive = time() - $_COOKIE['last_activity'];
  if ($inactive > $timeout) {
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

// Add Course Appeals
$addQuery = "
SELECT a.AppealID, a.StudentID, a.Problem, a.Request, a.AppealDate,
       s.StudentName, ac.CourseCode, c.GroupNumber,
       (SELECT SUM(sc.Credit) FROM StudentCourse sc WHERE sc.StudentID = a.StudentID) AS CurrentCredit,
       (SELECT c2.Credit FROM Course c2 WHERE c2.CourseCode = ac.CourseCode) AS AddCredit
FROM Appeal a
JOIN Student s ON a.StudentID = s.StudentID
JOIN AddCourse ac ON ac.AppealID = a.AppealID
JOIN Course c ON ac.CourseCode = c.CourseCode
LEFT JOIN academic act ON act.AppealID = a.AppealID
WHERE act.AppealID IS NULL
ORDER BY a.AppealDate DESC
";

$addResult = mysqli_query($conn, $addQuery);

// Drop Course Appeals
$dropQuery = "
SELECT a.AppealID, a.StudentID, a.Problem, a.Request, a.AppealDate,
       s.StudentName, dc.CourseCode, c.GroupNumber,
       (SELECT SUM(sc.Credit) FROM StudentCourse sc WHERE sc.StudentID = a.StudentID) AS CurrentCredit,
       (SELECT c2.Credit FROM Course c2 WHERE c2.CourseCode = dc.CourseCode) AS DropCredit
FROM Appeal a
JOIN Student s ON a.StudentID = s.StudentID
JOIN DropCourse dc ON dc.AppealID = a.AppealID
JOIN Course c ON dc.CourseCode = c.CourseCode
LEFT JOIN academic act ON act.AppealID = a.AppealID
WHERE act.AppealID IS NULL
ORDER BY a.AppealDate DESC
";

$dropResult = mysqli_query($conn, $dropQuery);
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Academic Check Appeals</title>
  <link rel="stylesheet" href="CSS/style.css">
  <style>
    body {
      background: linear-gradient(135deg, #e6ecf0, #dbe3e8);
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      padding: 40px;
      display: block;
    }

    .table-section {
      max-width: 1000px;
      margin: 40px auto;
      background: #ffffffcc;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 0 10px #aacfd0;
      border: 2px solid #aacfd0;
    }

    h2 {
      text-align: center;
      color: #2e4a62;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    th,
    td {
      padding: 12px;
      border: 1px solid #aacfd0;
      text-align: center;
    }

    th {
      background-color: #dbe3e8;
      color: #2e4a62;
    }

    td {
      background-color: #f9fbfc;
    }

    .action-buttons a {
      text-decoration: none;
      padding: 6px 6px;
      margin: 2px;
      display: inline-block;
      font-size: 11px;
      background-color: #aacfd0;
      color: white;
      border-radius: 6px;
      transition: background-color 0.3s;
    }

    .action-buttons a:hover {
      background-color: #77a6b6;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      padding-top: 100px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: #fff;
      margin: auto;
      padding: 20px;
      border: 2px solid #aacfd0;
      border-radius: 10px;
      width: 70%;
      max-width: 600px;
      font-family: 'Segoe UI', sans-serif;
      color: #2e4a62;
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .close:hover {
      color: #000;
    }

    .redirect-button {
      font-size: 13px;
      padding: 6px 12px;
      margin: 0 auto;
      display: inline-block;
    }
  </style>
</head>

<body>

  <div class="table-section">
    <h2>Add Course Appeals</h2>
    <table>
      <tr>
        <th>Appeal ID</th>
        <th>Student</th>
        <th>Course Code</th>
        <th>Group</th>
        <th>Problem</th>
        <th>Request</th>
        <th>Date</th>
        <th>Eligibility</th>
        <th>Actions</th>

      </tr>
      <?php while ($row = mysqli_fetch_assoc($addResult)): ?>
        <tr>
          <td><?= $row['AppealID'] ?></td>
          <td><?= htmlspecialchars($row['StudentName']) ?> (<?= $row['StudentID'] ?>)</td>
          <td><?= $row['CourseCode'] ?></td>
          <td><?= $row['GroupNumber'] ?></td>

          <td>
            <button class="redirect-button"
              onclick='openModal("Problem", <?= json_encode($row["Problem"] ?? "") ?>)'>
              View
            </button>
          </td>
          <td>
            <button class="redirect-button"
              onclick='openModal("Request", <?= json_encode($row["Request"] ?? "") ?>)'>
              View
            </button>
          </td>
          <td><?= $row['AppealDate'] ?></td>

          <?php
          $finalCredit = ($row['CurrentCredit'] ?? 0) + ($row['AddCredit'] ?? 0);
          $eligibility = ($finalCredit >= 12 && $finalCredit <= 23) ? 'Eligible' : 'Not Eligible';
          ?>
          <td><?= $eligibility ?></td>

          <td class="action-buttons">
            <a href="academic_student_info.php?student_id=<?= $row['StudentID'] ?>">Student Info</a>
            <a href="academic_comment_appeal.php?appeal_id=<?= $row['AppealID'] ?>">Faculty Comment</a>
            <a href="academic_take_action.php?appeal_id=<?= $row['AppealID'] ?>">Take Action</a>
          </td>
        </tr>

      <?php endwhile; ?>
    </table>
  </div>

  <div class="table-section">
    <h2>Drop Course Appeals</h2>
    <table>
      <tr>
        <th>Appeal ID</th>
        <th>Student</th>
        <th>Course Code</th>
        <th>Group</th>
        <th>Problem</th>
        <th>Request</th>
        <th>Date</th>
        <th>Eligibility</th>
        <th>Actions</th>

      </tr>
      <?php while ($row = mysqli_fetch_assoc($dropResult)): ?>

        <tr>
          <td><?= $row['AppealID'] ?></td>
          <td><?= htmlspecialchars($row['StudentName']) ?> (<?= $row['StudentID'] ?>)</td>
          <td><?= $row['CourseCode'] ?></td>
          <td><?= $row['GroupNumber'] ?></td>

          <td>
            <button class="redirect-button"
              onclick='openModal("Problem", <?= json_encode($row["Problem"] ?? "") ?>)'>
              View
            </button>
          </td>
          <td>
            <button class="redirect-button"
              onclick='openModal("Request", <?= json_encode($row["Request"] ?? "") ?>)'>
              View
            </button>
          </td>

          <?php
          $finalCredit = ($row['CurrentCredit'] ?? 0) - ($row['DropCredit'] ?? 0);
          $eligibility = ($finalCredit >= 12 && $finalCredit <= 23) ? 'Eligible' : 'Not Eligible';
          ?>

          <td><?= $eligibility ?></td>

          <td><?= $row['AppealDate'] ?></td>
          <td class="action-buttons">
            <a href="academic_student_info.php?student_id=<?= $row['StudentID'] ?>">Student Info</a>
            <a href="academic_comment_appeal.php?appeal_id=<?= $row['AppealID'] ?>">Faculty Comment</a>
            <a href="academic_take_action.php?appeal_id=<?= $row['AppealID'] ?>">Take Action</a>
          </td>

        </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <!-- Modal structure -->
  <div id="infoModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h3 id="modalTitle"></h3>
      <p id="modalContent"></p>
    </div>
  </div>

  <script>
    function openModal(title, content) {
      document.getElementById('modalTitle').innerText = title;
      document.getElementById('modalContent').innerText = content;
      document.getElementById('infoModal').style.display = 'block';
    }

    function closeModal() {
      document.getElementById('infoModal').style.display = 'none';
    }

    // Close if click outside the modal
    window.onclick = function(event) {
      const modal = document.getElementById('infoModal');
      if (event.target == modal) modal.style.display = "none";
    };
  </script>


</body>

</html>