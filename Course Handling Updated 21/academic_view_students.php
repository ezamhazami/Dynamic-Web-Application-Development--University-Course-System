<?php
session_start();
if (!isset($_SESSION['current_academic'])) {
  header("Location: academic_login.php");
  exit();
}

$conn = mysqli_connect("localhost", "root", "", "coursedb");
$students = mysqli_query($conn, "SELECT *, (SELECT SUM(Credit) FROM StudentCourse WHERE StudentID = Student.StudentID) AS TotalCredits FROM Student ORDER BY StudentName");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>View Students</title>
  <style>
    body {
      display: flex;
      flex-direction: column;
      align-items: center;
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
  </style>
</head>
<body>
<div class="dashboard-container">
  <h1>All Registered Students</h1>
  <table>
    <tr>
      <th>Student ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Program</th>
      <th>Faculty</th>
      <th>Campus</th>
      <th>Total Credits</th>
    </tr>
    <?php while ($s = mysqli_fetch_assoc($students)): ?>
    <tr>
      <td><?= $s['StudentID'] ?></td>
      <td><?= $s['StudentName'] ?></td>
      <td><?= $s['Email'] ?></td>
      <td><?= $s['ProgramCode'] ?></td>
      <td><?= $s['Faculty'] ?></td>
      <td><?= $s['Campus'] ?></td>
      <td><?= $s['TotalCredits'] ?? 0 ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>
</body>
</html>
