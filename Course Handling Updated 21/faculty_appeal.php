<?php
session_start();
$conn = new mysqli("localhost", "root", "", "coursedb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch appeals, group info, and course limits
$sql = "
SELECT 
    ap.StudentID,
    ap.AppealID,
    ap.AppealDate,
    ac.CourseCode,
    c.GroupNumber AS TotalGroups,
    s.group AS CurrentGroup,
    cg.StudentsPerGroup
FROM Appeal ap
JOIN AddCourse ac ON ap.AppealID = ac.AppealID
JOIN Course c ON ac.CourseCode = c.CourseCode
JOIN studentcourse s ON s.CourseCode = c.CourseCode AND s.StudentID = ap.StudentID
JOIN CourseGroup cg ON cg.CourseCode = c.CourseCode AND s.group = cg.GroupNumber
ORDER BY ac.CourseCode, ap.StudentID
";

$result = $conn->query($sql);
$students = [];
$maxGroup = 1;

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
    if ((int)$row['CurrentGroup'] > $maxGroup) {
        $maxGroup = (int)$row['CurrentGroup'];
    }
}

// Group students by course code
$groupedByCourse = [];
foreach ($students as $stu) {
    $groupedByCourse[$stu['CourseCode']][] = $stu;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Appeal Grouping</title>
  <link rel="stylesheet" href="CSS/faculty_appeal.css">
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

  <h2 style="padding-top:120px;height:170px;text-align:center;">Student Appeal Grouping</h2>

  <!-- Group Filter -->
  <label style="text-align:center;display:block;">Filter by Group:</label>
  <select id="mainGroupFilter" onchange="filterByGroup()">
    <option value="all">All Groups</option>
    <?php for ($g = 1; $g <= $maxGroup; $g++): ?>
      <option value="<?= $g ?>">Group <?= $g ?></option>
    <?php endfor; ?>
  </select>

  <form method="post" action="save_studentcourse.php" style="max-width: 1100px; margin: auto; font-family: sans-serif;">
    <?php foreach ($groupedByCourse as $courseCode => $studentGroup): ?>
      <div class="course-section">
        <!-- Course Section Header -->
        <div style="background-color: #f8f9fa; padding: 12px 20px; border: 1px solid #ddd; border-radius: 6px;">
          <h2 style="margin: 0; font-size: 1.1rem; color: #333;">
            📘 Course Code: <strong><?= htmlspecialchars($courseCode) ?></strong>
          </h2>
        </div>

        <div style="background-color: #f8f9fa; padding: 12px 20px; border: 1px solid #ddd; border-radius: 6px;">
          <h2 style="margin: 0; font-size: 1.1rem; color: #333;">
            📘 Total Groups: <strong><?= htmlspecialchars($studentGroup[0]['TotalGroups']) ?></strong>
          </h2>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
          <thead>
            <tr style="background-color: #343a40; color: white;">
              <th style="padding: 10px;">Appeal ID</th>
              <th style="padding: 10px;">Student ID</th>
              <th style="padding: 10px;">Request Date</th>
              <th style="padding: 10px;">Current Group</th> <!-- New Column -->
              <th style="padding: 10px;">Assigned Group</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($studentGroup as $stu): ?>
              <tr class="student-row" data-group="<?= htmlspecialchars($stu['CurrentGroup']) ?>" style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;"><?= htmlspecialchars($stu['AppealID']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($stu['StudentID']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($stu['AppealDate']) ?></td>
                <td style="padding: 10px;">Group <?= htmlspecialchars($stu['CurrentGroup']) ?></td> <!-- Display Current Group -->
                <td style="padding: 10px;">
                  <select name="groups[<?= $stu['StudentID'] ?>][<?= $stu['CourseCode'] ?>]"
                    required
                    style="width: 80px; padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
                    <?php for ($g = 1; $g <= $stu['TotalGroups']; $g++): ?>
                      <?php
                      $groupCountRes = $conn->query("
                        SELECT COUNT(*) as cnt FROM studentcourse 
                        WHERE CourseCode = '{$stu['CourseCode']}' AND `group` = $g
                      ");
                      $groupData = $groupCountRes->fetch_assoc();
                      $isFull = $groupData['cnt'] >= $stu['StudentsPerGroup'];
                      ?>
                      <option value="<?= $g ?>" <?= ($stu['CurrentGroup'] == $g ? 'selected' : '') ?> <?= $isFull ? 'disabled' : '' ?>>
                        Group <?= $g ?> <?= $isFull ? '(Full)' : '' ?>
                      </option>
                    <?php endfor; ?>
                  </select>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div> <!-- end of .course-section -->
    <?php endforeach; ?>

    <!-- Submit Button -->
    <div style="text-align: center; margin-top: 30px;">
      <button type="submit"
        style="padding: 12px 24px; background-color: #007bff; color: white; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer;">
        💾 Save
      </button>
    </div>
  </form>

</body>

<script>
  function filterByGroup() {
    const selectedGroup = document.getElementById('mainGroupFilter').value;
    const courseSections = document.querySelectorAll('.course-section');

    courseSections.forEach(section => {
      const studentRows = section.querySelectorAll('.student-row');
      let showCourse = false;

      studentRows.forEach(row => {
        const rowGroup = row.getAttribute('data-group');
        const match = (selectedGroup === 'all' || rowGroup === selectedGroup);
        row.style.display = match ? '' : 'none';
        if (match) showCourse = true;
      });

      section.style.display = showCourse ? '' : 'none';
    });
  }
</script>

</html>
