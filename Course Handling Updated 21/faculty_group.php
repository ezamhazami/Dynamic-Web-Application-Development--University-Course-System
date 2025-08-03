<?php
session_start();
$conn = new mysqli("localhost", "root", "", "coursedb");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "
SELECT 
  c.CourseCode, 
  c.CourseName, 
  c.GroupNumber, 
  COUNT(sc.StudentID) AS num_students
FROM Course c
LEFT JOIN studentcourse sc ON c.CourseCode = sc.CourseCode
GROUP BY c.CourseCode, c.CourseName, c.GroupNumber
";

$result = $conn->query($sql);


$sqlMax = "
SELECT MAX(num_students) AS max_students
FROM (
  SELECT COUNT(*) AS num_students
  FROM studentcourse
  GROUP BY CourseCode, `group`
) AS grouped_counts
";

$resultMax = $conn->query($sqlMax);
$maxStudents = 0;

if ($resultMax && $row = $resultMax->fetch_assoc()) {
    $maxStudents = $row['max_students'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Faculty Group Handling</title>
  <link rel="stylesheet" href="css/faculty_group.css">
  <style>
    .inline-form {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .inline-form input[type="number"] {
      width: 40px;
      padding: 6px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    .save-btn {
      padding: 6px 14px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .save-btn:hover {
      background-color: #0056b3;
    }

    /* Modal Styling */
    .modal {
      display: none;
      position: fixed;
      z-index: 99;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background: #fff;
      margin: 10% auto;
      padding: 30px;
      border-radius: 8px;
      width: 400px;
      max-width: 90%;
      text-align: center;
    }

    .modal-content h3 {
      margin-top: 0;
      color: #333;
    }

    .modal-buttons {
      margin-top: 20px;
      display: flex;
      justify-content: center;
      gap: 15px;
    }

    .modal-buttons button {
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }

    .confirm-btn {
      background-color: #28a745;
      color: white;
    }

    .cancel-btn {
      background-color: #dc3545;
      color: white;
    }
  </style>
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

  <h2 style="padding-top:120px;height:170px;text-align:center;">Course Group Management</h2>
  

  <div style="
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 40px 0;
">
    <form
      action="faculty_uniform_grouping.php"
      method="post"
      onsubmit="return confirm('Apply this setting to all courses?');"
      style="
      background: #f9f9f9;
      border: 1px solid #ccc;
      padding: 25px 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      font-family: Arial, sans-serif;
    ">
      <h3 style="text-align:center; margin-bottom: 20px;">Global Group Settings</h3>

      <label style="font-weight: bold;">Set all courses to have:</label>
      <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <input
          type="number"
          name="globalGroupCount"
          min="1"
          required
          placeholder="e.g. 3"
          style="flex: 1; padding: 8px; margin-left: 10px; border-radius: 4px; border: 1px solid #ccc;">
        <span style="margin-left: 10px;">Groups</span>
      </div>

      <label style="font-weight: bold;">Set all groups to have:</label>
      <div style="display: flex; align-items: center; margin-bottom: 20px;">
        <input
          type="number"
          name="studentsPerGroup"
          min="<?=$maxStudents?>"
          required
          placeholder="e.g. 5"
          style="flex: 1; padding: 8px; margin-left: 10px; border-radius: 4px; border: 1px solid #ccc;">
        <span style="margin-left: 10px;">Students</span>
      </div>

      <button
        type="submit"
        style="
        width: 100%;
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
      ">
        Apply to All Courses
      </button>
    </form>
  </div>



  <div class="table-container">
    <table>
      <tr>
        <th>Course Code</th>
        <th>Course Name</th>
        <th>Number of Students</th>
        <th>Current Groups</th>
        <th>Edit & Save</th>
      </tr>

      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['CourseCode']) ?></td>
          <td><?= htmlspecialchars($row['CourseName']) ?></td>
          <td><?= $row['num_students'] ?></td>
          <td><?= $row['GroupNumber'] ?></td>
          <!-- Table Row -->
          <td>
            <form method="post" class="inline-form" onsubmit="return openGroupModal(this);">
              <input type="hidden" name="courseCode" value="<?= htmlspecialchars($row['CourseCode']) ?>">
              <input type="hidden" name="numStudents" value="<?= $row['num_students'] ?>">
              <label>Total Groups:</label>
              <input type="number" name="groupCount" min="1" value="<?= $row['GroupNumber'] ?>" required>
              <button type="submit" class="save-btn">Save</button>
            </form>
          </td>

        </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <!-- Modal Structure -->
  <div id="confirmModal" class="modal">
    <div class="modal-content">
      <h3 id="modal-title">Confirm Group Update</h3>
      <p id="modal-description"></p>

      <!-- Dynamic Form -->
      <form id="groupStudentForm" action="save_group_students.php" method="post">
        <input type="hidden" name="courseCode" id="modalCourseCode" value="" readonly>
        <input type="hidden" name="groupCount" id="modalGroupCount" value="" readonly>
        <div id="groupInputsContainer"></div>

        <div class="modal-buttons" style="margin-top: 20px;">
          <button type="submit" class="confirm-btn">Submit Student Counts</button>
          <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>


  <script>
    function openGroupModal(form) {
      const courseCode = form.courseCode.value;
      const groupCountInput = form.querySelector('input[name="groupCount"]');
      const groupCount = parseInt(groupCountInput.value, 10);
      const numStudents = parseInt(form.numStudents.value, 10);

      if (isNaN(groupCount) || groupCount < 1) {
        alert("Please enter a valid group count.");
        return false;
      }

      // Store globally for validation on submit
       window.currentTotalStudents = numStudents;

      // Set hidden field in modal
      document.getElementById('modalCourseCode').value = courseCode;

      // Set hidden field in modal
      document.getElementById('modalGroupCount').value = groupCount;

      // Update modal content
      document.getElementById('modal-title').innerText = 'Enter students per group for ' + courseCode;
      document.getElementById('modal-description').innerText =
        'You are updating to ' + groupCount + ' total groups. Please enter the number of students for each group.';

      // Clear and build group inputs
      const container = document.getElementById('groupInputsContainer');
      container.innerHTML = '';

      for (var i = 1; i <= groupCount; i++) {
        const label = document.createElement('label');
        label.textContent = 'Group ' + i + ': ';
        label.style.display = 'block';
        label.style.marginTop = '10px';

        const input = document.createElement('input');
        input.type = 'number';
        input.name = `studentsPerGroup[${i}]`;
        input.min = numStudents;
        input.required = true;
        input.style.padding = '6px';
        input.style.marginTop = '5px';
        input.style.border = '1px solid #ccc';
        input.style.borderRadius = '4px';
        input.style.width = '100%';

        container.appendChild(label);
        container.appendChild(input);
      }

      // Show modal
      document.getElementById('confirmModal').style.display = 'block';
      return false; // Prevent default form submission
    }


    function closeModal() {
      document.getElementById('confirmModal').style.display = 'none';
    }

    // Optional: Close modal on outside click
    window.onclick = function(event) {
      const modal = document.getElementById('confirmModal');
      if (event.target === modal) {
        closeModal();
      }
    }

    document.getElementById('groupStudentForm').addEventListener('submit', function(event) {
  const inputs = document.querySelectorAll('#groupInputsContainer input');
  let total = 0;

  inputs.forEach(input => {
    const val = parseInt(input.value, 10);
    if (!isNaN(val)) total += val;
  });

  if (total < window.currentTotalStudents) {
    event.preventDefault();
    alert(`Total students assigned (${total}) is less than the actual enrolled students (${window.currentTotalStudents}).`);
    return false;
  }
});

  </script>

</body>

</html>