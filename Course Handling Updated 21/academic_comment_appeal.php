<?php
session_start();
if (!isset($_SESSION['current_academic'])) {
  header("Location: academic_login.php");
  exit();
}

$conn = mysqli_connect("localhost", "root", "", "coursedb");
$appeal_id = $_GET['appeal_id'] ?? null;

// Fetch comments from faculty
$query = "SELECT Comment, SuggestedAction, OOF_Signature, OOF_CommentDate FROM faculty WHERE AppealID = '$appeal_id'";
$result = mysqli_query($conn, $query);
$comments = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Faculty Comments</title>
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      background: linear-gradient(135deg, #e6ecf0, #dbe3e8);
      padding: 40px 20px;
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .dashboard-container {
      width: 90%;
      max-width: 900px;
      background: #ffffffcc;
      padding: 30px 40px;
      border-radius: 16px;
      box-shadow: 0 0 10px #aacfd0;
      border: 2px solid #aacfd0;
    }

    h1 {
      color: #2e4a62;
      text-align: center;
      text-shadow: 0 0 4px #aacfd0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
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

    .no-comment {
      color: #dc3545;
      font-weight: bold;
      text-align: center;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <h1>Faculty Comments for Appeal ID: <?= htmlspecialchars($appeal_id) ?></h1>

    <?php if (count($comments) > 0): ?>
      <table>
        <tr>
          <th>Comment</th>
          <th>Suggested Action</th>
          <th>Signature</th>
          <th>Date</th>
        </tr>
        <?php foreach ($comments as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['Comment']) ?></td>
          <td><?= htmlspecialchars($c['SuggestedAction']) ?></td>
          <td><?= htmlspecialchars($c['OOF_Signature']) ?></td>
          <td><?= htmlspecialchars($c['OOF_CommentDate']) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    <?php else: ?>
      <p class="no-comment">No comments have been submitted by the faculty yet.</p>
    <?php endif; ?>
  </div>
</body>
</html>
