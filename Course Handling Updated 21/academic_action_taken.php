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

// Filter input
$name = $_GET['name'] ?? '';
$id = $_GET['id'] ?? '';
$date = $_GET['date'] ?? '';
$course = $_GET['course'] ?? '';
$category = $_GET['category'] ?? '';

// Build filter conditions FIRST
$conditions = [];

if (!empty($name)) {
  $safe_name = mysqli_real_escape_string($conn, $name);
  $conditions[] = "s.StudentName LIKE '%$safe_name%'";
}
if (!empty($id)) {
  $safe_id = mysqli_real_escape_string($conn, $id);
  $conditions[] = "s.StudentID LIKE '%$safe_id%'";
}
if (!empty($date)) {
  $safe_date = mysqli_real_escape_string($conn, $date);
  $conditions[] = "a.AppealDate = '$safe_date'";
}
if (!empty($course)) {
  $safe_course = mysqli_real_escape_string($conn, $course);
  $conditions[] = "(ac.CourseCode = '$safe_course' OR dc.CourseCode = '$safe_course')";
}
if (!empty($category)) {
  if ($category === 'Add') {
    $conditions[] = "ac.CourseCode IS NOT NULL";
  } elseif ($category === 'Drop') {
    $conditions[] = "dc.CourseCode IS NOT NULL";
  }
}

// Start query
$query = "
SELECT a.AppealID, s.StudentID, s.StudentName, a.Problem, a.Request, a.AppealDate,
       GROUP_CONCAT(DISTINCT ac.CourseCode) AS AddCourses,
       GROUP_CONCAT(DISTINCT dc.CourseCode) AS DropCourses,
       acad.Status, acad.OOA_CommentDate
FROM academic acad
JOIN Appeal a ON acad.AppealID = a.AppealID
JOIN Student s ON a.StudentID = s.StudentID
LEFT JOIN AddCourse ac ON a.AppealID = ac.AppealID
LEFT JOIN DropCourse dc ON a.AppealID = dc.AppealID
";

// Add filters if there are any
if (!empty($conditions)) {
  $query .= " WHERE " . implode(" AND ", $conditions);
}

// Now group and order
$query .= " GROUP BY a.AppealID ORDER BY a.AppealDate DESC";


$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Appeal Action Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    <?php include("your-styles.css"); ?>.report-container {
      max-width: 1100px;
      margin: 40px auto;
      background: #ffffffcc;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 0 10px #aacfd0;
      border: 2px solid #aacfd0;
    }

    h2 {
      text-align: center;
      color: #2e4a62;
      margin-bottom: 50px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
    }

    th,
    td {
      padding: 12px;
      text-align: center;
      border: 1px solid #aacfd0;
    }

    th {
      background-color: #dbe3e8;
      color: #2e4a62;
    }

    td {
      background-color: #f9fbfc;
    }

    .filter-form {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      /* 5 columns */
      grid-template-rows: repeat(2, auto);
      /* 2 rows */
      gap: 15px;
      /* optional spacing between items */

      margin-top: 20px;
      margin-bottom: 10px;
    }


    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #2e4a62;
    }

    .form-group {
      margin-left: 10px;
    }

    input,
    select {
      width: 90%;
      padding: 8px 10px;
      font-size: 14px;
      border: 1px solid #aacfd0;
      border-radius: 6px;
      background-color: #f4f6f8;
    }

    .submit-button {
      background-color: #aacfd0;
      color: #fff;
      border: none;
      padding: 10px;
      font-weight: bold;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 23px;
      margin-right: 30px;
    }

    .submit-button:hover {
      background-color: #77a6b6;
    }
  </style>
</head>

<body>

  <div class="report-container">
    <h2>Appeal History / Action Taken Report</h2>

    <form class="filter-form" method="GET">
      <div class="form-group">
        <label>Student Name</label>
        <input type="text" name="name" placeholder="e.g. Aisyah" value="<?= htmlspecialchars($name) ?>">
      </div>

      <div class="form-group">
        <label>Student ID</label>
        <input type="text" name="id" placeholder="e.g. 20233211" value="<?= htmlspecialchars($id) ?>">
      </div>

      <div class="form-group">
        <label>Date</label>
        <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
      </div>

      <div class="form-group">
        <label>Course Code</label>
        <input type="text" name="course" placeholder="e.g. CSC101" value="<?= htmlspecialchars($course) ?>">
      </div>

      <div class="form-group">
        <label>Appeal Type</label>
        <select name="category">
          <option value="">-- All --</option>
          <option value="Add" <?= $category === 'Add' ? 'selected' : '' ?>>Add</option>
          <option value="Drop" <?= $category === 'Drop' ? 'selected' : '' ?>>Drop</option>
        </select>
      </div>

      <div class="form-group filters">
        <button type="submit" class="submit-button">Apply Filters</button>
        <a href="academic_action_taken.php" style="text-decoration: none; color: #555; font-weight: bold;">⟲ Reset Filters</a>
      </div>

    </form>

    <table>
      <tr>
        <th>Appeal ID</th>
        <th>Student</th>
        <th>Course Code</th>
        <th>Type</th>
        <th>Problem</th>
        <th>Request</th>
        <th>Appeal Date</th>
        <th>Decision</th>
        <th>Decision Date</th>
      </tr>
      <?php
      while ($row = mysqli_fetch_assoc($result)) {
        $addCodes = $row['AddCourses'] ? explode(',', $row['AddCourses']) : [];
        $dropCodes = $row['DropCourses'] ? explode(',', $row['DropCourses']) : [];

        foreach ($addCodes as $code) {
          echo "<tr>
            <td>{$row['AppealID']}</td>
            <td>{$row['StudentName']} ({$row['StudentID']})</td>
            <td>$code</td>
            <td><span class='text-green-600 font-semibold'>Add</span></td>
            <td>{$row['Problem']}</td>
            <td>{$row['Request']}</td>
            <td>{$row['AppealDate']}</td>
            <td>{$row['Status']}</td>
            <td>{$row['OOA_CommentDate']}</td>
          </tr>";
        }

        foreach ($dropCodes as $code) {
          echo "<tr>
            <td>{$row['AppealID']}</td>
            <td>{$row['StudentName']} ({$row['StudentID']})</td>
            <td>$code</td>
            <td><span class='text-red-600 font-semibold'>Drop</span></td>
            <td>{$row['Problem']}</td>
            <td>{$row['Request']}</td>
            <td>{$row['AppealDate']}</td>
            <td>{$row['Status']}</td>
            <td>{$row['OOA_CommentDate']}</td>
          </tr>";
        }
      }
      ?>

    </table>
  </div>

</body>

</html>