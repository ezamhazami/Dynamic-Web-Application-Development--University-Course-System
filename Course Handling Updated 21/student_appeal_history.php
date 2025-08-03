<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "coursedb");

if (!isset($_SESSION['currentID'])) {
  header("Location: student_login.php");
  exit();
}

$student_id = $_SESSION['currentID'];

// Fetch appeal history
$sql = "
  SELECT 
    a.AppealID,
    a.Problem,
    a.Request,
    a.AppealDate,
    'Add' AS Action,
    ac.CourseCode,
    a.Status
  FROM appeal a
  INNER JOIN addcourse ac ON a.AppealID = ac.AppealID
  WHERE a.StudentID = '$student_id'

  UNION

  SELECT 
    a.AppealID,
    a.Problem,
    a.Request,
    a.AppealDate,
    'Drop' AS Action,
    dc.CourseCode,
    a.Status
  FROM appeal a
  INNER JOIN dropcourse dc ON a.AppealID = dc.AppealID
  WHERE a.StudentID = '$student_id'

  ORDER BY AppealDate DESC
";

$result = mysqli_query($conn, $sql);
$appeals = [];
while ($row = mysqli_fetch_assoc($result)) {
  $appeals[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Appeal History</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f9fc;
      color: #2e4a62;
      padding: 20px;
    }

    .container {
      max-width: 900px;
      margin: auto;
      background: #ffffffcc;
      padding: 30px;
      border-radius: 12px;
      backdrop-filter: blur(6px);
      box-shadow: 0 0 10px #aacfd0;
      border: 2px solid #aacfd0;
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #fff;
      border: 1px solid #dbe4ec;
    }

    th,
    td {
      padding: 10px;
      text-align: center;
      border-bottom: 1px solid #dbe4ec;
    }

    th {
      background-color: #e1eef6;
      color: #2e4a62;
    }

    tr:nth-child(even) {
      background-color: #f5f9fc;
    }

    .status {
      font-weight: bold;
      padding: 6px 10px;
      border-radius: 8px;
    }

    .Received {
      background: #d9edf7;
      color: #31708f;
    }

    .Pending {
      background: #fff3cd;
      color: #856404;
    }

    .Approved {
      background: #d4edda;
      color: #155724;
    }

    .Rejected {
      background: #f8d7da;
      color: #721c24;
    }

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
  <a href="student_dashboard.php" class="back-btn">← Back</a>

  <div class="container">
    <h1>Your Appeal History</h1>

    <?php if (empty($appeals)): ?>
      <p style="text-align:center;">You haven't submitted any appeals yet.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Action</th>
            <th>Course Code</th>
            <th>Problem</th>
            <th>Request</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($appeals as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['AppealDate']) ?></td>
              <td><?= htmlspecialchars($a['Action']) ?></td>
              <td><?= htmlspecialchars($a['CourseCode']) ?></td>
              <td><?= htmlspecialchars($a['Problem']) ?></td>
              <td><?= htmlspecialchars($a['Request']) ?></td>
              <td>
                <span class="status <?= htmlspecialchars($a['Status']) ?>">
                  <?= htmlspecialchars($a['Status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>

</html>