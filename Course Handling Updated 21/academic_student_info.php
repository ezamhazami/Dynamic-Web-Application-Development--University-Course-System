<?php
session_start();
if (!isset($_SESSION['current_academic'])) {
  header("Location: academic_login.php");
  exit();
}

$conn = mysqli_connect("localhost", "root", "", "coursedb");
$student_id = $_GET['student_id'] ?? null;

$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Student WHERE StudentID = '$student_id'"));
$courses = mysqli_query($conn, "
  SELECT c.CourseCode, c.CourseName, c.Credit AS CourseCredit, c.GroupNumber,
         sc.Credit AS TakenCredit, sc.Group
  FROM StudentCourse sc
  JOIN Course c ON sc.CourseCode = c.CourseCode
  WHERE sc.StudentID = '$student_id'
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Student Info</title>
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

        h1, h2 {
        color: #2e4a62;
        text-align: center;
        text-shadow: 0 0 4px #aacfd0;
        margin-bottom: 20px;
        }

        p {
        margin: 8px 0;
        color: #2e4a62;
        }

        table {
        width: 100%;
        background-color: white;
        color: #2e4a62;
        border-radius: 8px;
        overflow: hidden;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 14px;
        }

        th, td {
        padding: 10px;
        border-bottom: 1px solid #aacfd0;
        text-align: center;
        }

        th {
        background-color: #dbe3e8;
        }

        .btn-group {
        display: flex;
        gap: 15px;
        margin-top: 25px;
        justify-content: center;
        }

        .action-button {
        background-color: #aacfd0;
        color: #fff;
        font-weight: bold;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        box-shadow: 0 0 6px #aacfd0;
        transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .action-button:hover {
        background-color: #77a6b6;
        transform: scale(1.05);
        }

        .info-box {
        margin-top: 20px;
        }
    </style>
</head>
<body>
  <div class="dashboard-container">
    <h1>Student Info</h1>

    <p>Name: <?= htmlspecialchars($student['StudentName']) ?></p>
    <p>ID: <?= htmlspecialchars($student['StudentID']) ?></p>
    <p>Faculty: <?= htmlspecialchars($student['Faculty']) ?></p>
    <p>Program Code: <?= htmlspecialchars($student['ProgramCode']) ?></p>
    <p>Email: <?= htmlspecialchars($student['Email']) ?></p>
    <p>Phone: <?= htmlspecialchars($student['PhoneNumber']) ?></p>
    <p>Campus: <?= htmlspecialchars($student['Campus']) ?></p>
    <p>Graduating Status: <?= htmlspecialchars($student['GraduatingStatus']) ?></p>

    <h2>Current Courses</h2>
    <table>
        <tr>
            <th>Course Code</th>
            <th>Course Name</th>
            <th>Credit Hour</th>
            <th>Group</th>
        </tr>
        <?php
            $totalCredit = 0;
            while ($row = mysqli_fetch_assoc($courses)):
            $totalCredit += $row['TakenCredit'];
        ?>
            <tr>
            <td><?= $row['CourseCode'] ?></td>
            <td><?= $row['CourseName'] ?></td>
            <td><?= $row['TakenCredit'] ?></td>
            <td><?= $row['Group'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p class="status green">Total Credit Hour: <?= $totalCredit ?></p>


  </div>
</body>
</html>
