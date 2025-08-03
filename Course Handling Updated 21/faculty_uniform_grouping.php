<?php
session_start();

$conn = new mysqli("localhost", "root", "", "coursedb");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $groupCount = intval($_POST['globalGroupCount'] ?? 0);
  $studentsPerGroup = intval($_POST['studentsPerGroup'] ?? 0);

  if ($groupCount <= 0 || $studentsPerGroup <= 0) {
    echo "<script>alert('Both values must be greater than 0.'); history.back();</script>";
    exit;
  }

  // Get all courses
  $courses = $conn->query("SELECT CourseCode FROM course");

  while ($course = $courses->fetch_assoc()) {
    $code = $course['CourseCode'];

    // 1. Get all students in this course
    $studentQuery = "SELECT StudentID FROM studentcourse WHERE CourseCode=? ORDER BY StudentID";
    $studentStmt = $conn->prepare($studentQuery);
    $studentStmt->bind_param("s", $code);
    $studentStmt->execute();
    $studentResult = $studentStmt->get_result();

    $students = [];
    while ($row = $studentResult->fetch_assoc()) {
      $students[] = $row['StudentID'];
    }

    // 2. Redistribute students across uniform groups
    $redistributed = [];
    $studentIndex = 0;
    for ($groupNum = 1; $groupNum <= $groupCount; $groupNum++) {
      for ($i = 0; $i < $studentsPerGroup && $studentIndex < count($students); $i++) {
        $redistributed[] = [
          'StudentID' => $students[$studentIndex],
          'NewGroup' => $groupNum
        ];
        $studentIndex++;
      }
    }

    // 3. Update studentcourse group assignment
    $updateStmt = $conn->prepare("UPDATE studentcourse SET `group` = ? WHERE CourseCode = ? AND StudentID = ?");
    foreach ($redistributed as $data) {
      $updateStmt->bind_param("isi", $data['NewGroup'], $code, $data['StudentID']);
      $updateStmt->execute();
    }
    $updateStmt->close();

    // 4. Update GroupNumber in Course table
    $updateCourse = $conn->prepare("UPDATE course SET GroupNumber = ? WHERE CourseCode = ?");
    $updateCourse->bind_param("is", $groupCount, $code);
    $updateCourse->execute();
    $updateCourse->close();

    // 5. Remove excess CourseGroup rows
    $conn->query("DELETE FROM coursegroup WHERE CourseCode = '$code' AND GroupNumber > $groupCount");

    // 6. Upsert CourseGroup entries
    for ($groupNum = 1; $groupNum <= $groupCount; $groupNum++) {
      // Check if group exists
      $check = $conn->prepare("SELECT * FROM coursegroup WHERE CourseCode = ? AND GroupNumber = ?");
      $check->bind_param("si", $code, $groupNum);
      $check->execute();
      $result = $check->get_result();

      if ($result->num_rows > 0) {
        // Update existing group
        $updateGroup = $conn->prepare("UPDATE coursegroup SET StudentsPerGroup = ? WHERE CourseCode = ? AND GroupNumber = ?");
        $updateGroup->bind_param("isi", $studentsPerGroup, $code, $groupNum);
        $updateGroup->execute();
        $updateGroup->close();
      } else {
        // Insert new group
        $insertGroup = $conn->prepare("INSERT INTO coursegroup (CourseCode, GroupNumber, StudentsPerGroup) VALUES (?, ?, ?)");
        $insertGroup->bind_param("sii", $code, $groupNum, $studentsPerGroup);
        $insertGroup->execute();
        $insertGroup->close();
      }
    }
  }

  echo "<script>alert('Successfully applied to all courses!'); window.location.href='faculty_group.php';</script>";
  exit;
}
