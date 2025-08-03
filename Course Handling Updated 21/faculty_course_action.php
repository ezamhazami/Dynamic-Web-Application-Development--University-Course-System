<?php
session_start();
$conn = new mysqli("localhost", "root", "", "coursedb");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Query to get course info + student counts per group
$sql = "
SELECT 
  c.courseCode, 
  c.courseName, 
  c.credit,
  c.GroupNumber,
  IFNULL(sc.`group`, 0) AS StudentGroup,
  COUNT(sc.StudentID) AS StudentCount
FROM Course c
LEFT JOIN studentcourse sc ON c.courseCode = sc.courseCode
GROUP BY c.courseCode, c.courseName, c.credit, c.GroupNumber, StudentGroup
ORDER BY c.courseCode, StudentGroup
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Course Action Filter</title>
  <link rel="stylesheet" href="CSS/faculty_course_action.css">
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
        <span class="welcome-message"> Welcome, <?= htmlspecialchars($_SESSION['current_faculty']) ?></span>
      <?php else: ?>
        <button class="btnLogin-popup" onclick="window.location.href='faculty_login.php'">Login</button>
      <?php endif; ?>
    </nav>
  </header>

  <h2 style="padding-top:120px; text-align:center;">Course Action Filter</h2>

  <table>
    <tr>
      <th>Course Code</th>
      <th>Course Name</th>
      <th>Credit Hour</th>
      <th>Number of Groups</th>
      <th>Group</th>
      <th>Number of Students</th>
    </tr>
    <?php
    $lastCourseCode = '';
    while ($row = $result->fetch_assoc()):
    ?>
      <tr>
        <td><?= ($row['courseCode'] !== $lastCourseCode) ? htmlspecialchars($row['courseCode']) : '' ?></td>
        <td><?= ($row['courseCode'] !== $lastCourseCode) ? htmlspecialchars($row['courseName']) : '' ?></td>
        <td><?= ($row['courseCode'] !== $lastCourseCode) ? $row['credit'] : '' ?></td>
        <td><?= ($row['courseCode'] !== $lastCourseCode) ? $row['GroupNumber'] : '' ?></td>
        <td><?= $row['StudentGroup'] == 0 ? '-' : 'Group ' . $row['StudentGroup'] ?></td>
        <td><?= $row['StudentCount'] ?></td>
      </tr>
    <?php
      $lastCourseCode = $row['courseCode'];
    endwhile;
    ?>
  </table>

  <h3 style="text-align:center;">Add New Course</h3>
  <form method="post" action="add_course.php" style="width:50%; margin:20px auto; text-align:center;">
    <input type="text" name="CourseCode" placeholder="Course Code" required><br><br>
    <input type="text" name="CourseName" placeholder="Course Name" required><br><br>
    <input type="number" name="credit" placeholder="Credit" min="1" required><br><br>
    <button type="submit">Add Course</button>
  </form>

</body>

</html>