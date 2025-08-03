<?php
session_start();
if (!isset($_SESSION['current_academic'])) {
  header("Location: academic_login.php");
  exit();
}
$current_academic = $_SESSION['current_academic'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Academic Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

  <style>
    body {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: linear-gradient(135deg, #e6ecf0, #dbe3e8);
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    header {
      width: 100%;
      background-color: #2e4a62;
      color: white;
      padding: 15px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 3px solid #aacfd0;
      position: fixed;
      top: 0;
      z-index: 1000;
    }

    header h1 {
      margin: 0;
      font-size: 22px;
      padding-left: 15px;
    }

    .icons a {
      color: #fff;
      font-size: 20px;
      text-decoration: none;
      transition: 0.3s;
      padding: 20px;
    }

    .icons a:hover {
      color: #aacfd0;
    }

    .profile-dropdown {
      position: relative;
      display: inline-block;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #fff;
      color: #2e4a62;
      min-width: 160px;
      box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
      right: 0;
      border-radius: 8px;
      z-index: 1001;
      border: 1px solid #aacfd0;
    }

    .dropdown-content a, .dropdown-username {
      color: #2e4a62;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
      font-size: 14px;
    }

    .dropdown-username {
      font-weight: bold;
      border-bottom: 1px solid #ccc;
    }

    .dropdown-content a:hover {
      background-color: #dbe3e8;
    }

    .profile-dropdown:hover .dropdown-content {
      display: block;
    }

    .dashboard-container {
      margin-top: 70px;
      background: #ffffffcc;
      padding: 30px 30px;
      border-radius: 16px;
      box-shadow: 0 0 10px #aacfd0;
      border: 2px solid #aacfd0;
      width: 350px;
      text-align: center;

    }

    h2 {
      color: #2e4a62;
      text-shadow: 0 0 4px #aacfd0;
      margin-bottom: 30px;
    }

    .dashboard-buttons {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .dashboard-buttons a {
      background-color: transparent;
      color: #2e4a62;
      border: 2px solid #aacfd0;
      padding: 12px 16px;
      font-size: 15px;
      font-weight: 500;
      border-radius: 6px;
      text-decoration: none;
      transition: 0.3s;
      box-shadow: 0 0 6px #aacfd0;
    }

    .dashboard-buttons a:hover {
      background-color: #dbe3e8;
      box-shadow: 0 0 10px #77a6b6;
    }
  </style>
</head>

<body>

  <header>
    <h1>Academic Dashboard</h1>
    <div class="icons">
      <div class="profile-dropdown" id="profileDropdown">
        <a href="#" id="profileToggle" title="Profile"><i class="fas fa-user"></i></a>
        <div class="dropdown-content" id="dropdownContent">
          <?php if (isset($_SESSION['current_academic'])): ?>
            <div class="dropdown-username"><?= htmlspecialchars($_SESSION['current_academic']) ?></div>
            <a href="academic_logout.php">Logout</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <div class="dashboard-container">
    <h2>Welcome, <?= htmlspecialchars($current_academic) ?>!</h2>
    <div class="dashboard-buttons">
      <a href="faculty_register.php">Register Faculty</a>
      <a href="academic_view_appeals.php">Check Appeals</a>
      <a href="academic_action_taken.php">Action Taken</a>
      <a href="academic_view_students.php">Registered Students</a>
      <a href="academic_view_faculty.php">Registered Faculty</a>
    </div>
  </div>

</body>
</html>
