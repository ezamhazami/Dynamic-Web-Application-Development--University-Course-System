<?php
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/faculty_page.css" />
    <title>Faculty Page</title>
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
                <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['current_faculty']); ?></span>
            <?php else: ?>
                <button class="btnLogin-popup" onclick="window.location.href='faculty_login.php'">Login</button>
            <?php endif; ?>
        </nav>
    </header>
</body>

</html>