<?php
$conn = new mysqli("localhost", "root", "", "coursedb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['courseCode'], $_POST['studentsPerGroup'])) {
    $courseCode = $conn->real_escape_string($_POST['courseCode']);
    $studentsPerGroup = $_POST['studentsPerGroup']; // array: groupNum => max capacity
    $groupCount = intval($_POST['groupCount']);

    $sqlUpdate = "UPDATE Course SET GroupNumber = $groupCount WHERE CourseCode = '$courseCode'";
    $conn->query($sqlUpdate);


    //echo "Course Code: " . $courseCode . "<br>";
    //echo "Group Count: " . $groupCount . "<br><br>";
    //echo $studentsPerGroup;

    // 1. Get all students in the course
    $studentQuery = "SELECT StudentID FROM studentcourse WHERE CourseCode=? ORDER BY StudentID";
    $studentStmt = $conn->prepare($studentQuery);
    $studentStmt->bind_param("s", $courseCode);
    $studentStmt->execute();
    $studentResult = $studentStmt->get_result();

    $students = [];
    while ($row = $studentResult->fetch_assoc()) {
        $students[] = $row['StudentID'];
    }

    // 2. Assign students to groups until each group reaches capacity
    $redistributed = [];
    $studentIndex = 0;
    ksort($studentsPerGroup); // Ensure group 1 comes before 2, 3, etc.

    foreach ($studentsPerGroup as $groupNum => $capacity) {
        $groupNum = intval($groupNum);
        //echo "Group Number: " . $groupNum . "<br>";
        $capacity = intval($capacity);
        //echo "Student Capacity for group ->" . $groupNum . ": " . $capacity . "<br><br>";
        for ($i = 0; $i < $capacity && $studentIndex < count($students); $i++) {
            $redistributed[] = [
                'StudentID' => $students[$studentIndex],
                'NewGroup' => $groupNum
            ];
            $studentIndex++;
        }
    }

    // 3. Update group for each student in studentcourse
    $updateStmt = $conn->prepare("UPDATE studentcourse SET `group` = ? WHERE CourseCode = ? AND StudentID = ?");
    foreach ($redistributed as $data) {
        $updateStmt->bind_param("isi", $data['NewGroup'], $courseCode, $data['StudentID']);
        $updateStmt->execute();
    }

    // 4. Delete CourseGroup records above new max group
    $maxGroup = max(array_keys($studentsPerGroup));
    $deleteSql = "DELETE FROM Coursegroup WHERE CourseCode = ? AND GroupNumber > ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("si", $courseCode, $maxGroup);
    $deleteStmt->execute();

    // 5. Update or insert each CourseGroup (StudentsPerGroup)
    foreach ($studentsPerGroup as $groupNum => $studentCount) {
        $groupNum = intval($groupNum);
        $studentCount = intval($studentCount);

        $checkSql = "SELECT * FROM Coursegroup WHERE CourseCode = ? AND GroupNumber = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $courseCode, $groupNum);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $updateGroupSql = "UPDATE Coursegroup SET StudentsPerGroup = ? WHERE CourseCode = ? AND GroupNumber = ?";
            $updateGroupStmt = $conn->prepare($updateGroupSql);
            $updateGroupStmt->bind_param("isi", $studentCount, $courseCode, $groupNum);
            $updateGroupStmt->execute();
        } else {
            $insertGroupSql = "INSERT INTO Coursegroup (CourseCode, GroupNumber, StudentsPerGroup) VALUES (?, ?, ?)";
            $insertGroupStmt = $conn->prepare($insertGroupSql);
            $insertGroupStmt->bind_param("sii", $courseCode, $groupNum, $studentCount);
            $insertGroupStmt->execute();
        }
    }

    header("Location: faculty_group.php");
    exit;
}
