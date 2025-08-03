<?php
session_start();
$conn = new mysqli("localhost", "root", "", "coursedb");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "
SELECT 
  s.StudentID,
  s.StudentName,
  s.Faculty,
  s.ProgramCode,
  GROUP_CONCAT(DISTINCT c.CourseCode SEPARATOR ', ') AS CourseCodes,
  IFNULL(SUM(sc.credit), 0) AS TotalCredit
FROM Student s
LEFT JOIN StudentCourse sc ON s.StudentID = sc.StudentID
LEFT JOIN Course c ON sc.CourseCode = c.CourseCode
GROUP BY s.StudentID, s.StudentName, s.Faculty, s.ProgramCode
ORDER BY s.StudentID
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>View Student Info</title>
  <link rel="stylesheet" href="CSS/faculty_view_student.css">
</head>

<body>
  <header class="header">
    <h2 class="logo">Faculty Page</h2>
    <nav class="navigation">
      <a href="faculty_appeal.php">Student's Appeal</a>
      <a href="faculty_group.php">Group Handling</a>
      <a href="faculty_course_action.php">Course Action Filter</a>
      <a href="faculty_view_student.php">View Student Info</a>
      <?php if (isset($_SESSION['current_faculty'])): ?>
        <span class="welcome-message">Welcome, <?= htmlspecialchars($_SESSION['current_faculty']) ?></span>
      <?php else: ?>
        <button class="btnLogin-popup" onclick="window.location.href='faculty_login.php'">Login</button>
      <?php endif; ?>
    </nav>
  </header>

  <h2 style="padding-top:120px; text-align:center;">Student Information</h2>

  <table>
    <tr>
      <th>Student Name</th>
      <th>Faculty</th>
      <th>Program Code</th>
      <th>Courses</th>
      <th>Total Credit Hour</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['StudentName']) ?></td>
        <td><?= htmlspecialchars($row['Faculty']) ?></td>
        <td><?= htmlspecialchars($row['ProgramCode']) ?></td>
        <td><?= htmlspecialchars($row['CourseCodes']) ?: '-' ?></td>
        <td><?= $row['TotalCredit'] ?></td>
      </tr>
    <?php endwhile; ?>
  </table>

</body>

</html>