<?php
$conn = new mysqli("localhost", "root", "", "coursedb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['groups']) && is_array($_POST['groups'])) {
    foreach ($_POST['groups'] as $studentID => $courses) {
        foreach ($courses as $courseCode => $group) {
            $studentID = $conn->real_escape_string($studentID);
            $courseCode = $conn->real_escape_string($courseCode);
            $group = (int)$group;

            $sql = "UPDATE studentcourse 
                    SET `group` = $group 
                    WHERE StudentID = '$studentID' AND CourseCode = '$courseCode'";

            $conn->query($sql);
        }
    }
}

$conn->close();
header("Location: faculty_appeal.php");
exit();
