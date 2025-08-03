<?php
session_start();

// Handle reset
if (isset($_POST['reset'])) {
  session_destroy();
  header("Location: multistep_form.php");
  exit();
}

// Define fields for each step
$fields = ['name', 'email', 'age'];
$step = $_SESSION['step'] ?? 1;
$message = "";

// Load previous values
foreach ($fields as $field) {
  $$field = $_SESSION['data'][$field] ?? "";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (is_numeric($step)) {
    $currentField = $fields[$step - 1];
    $inputValue = trim($_POST[$currentField] ?? "");

    if ($inputValue === "") {
      $message = "Please fill in the $currentField field.";
    } else {
      $_SESSION['data'][$currentField] = $inputValue;

      if ($step < count($fields)) {
        $_SESSION['step'] = $step + 1;
        header("Location: multistep_form.php");
        exit();
      } else {
        $_SESSION['step'] = 'done';
        $step = 'done';
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>3-Step Form</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      background: #f7f7f7;
      color: #333;
    }

    .form-container {
      max-width: 400px;
      margin: auto;
      background: white;
      padding: 20px;
      box-shadow: 0 0 10px #ccc;
      border-radius: 8px;
    }

    input {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
    }

    button {
      margin-top: 15px;
      padding: 10px 15px;
      background-color: steelblue;
      color: white;
      border: none;
      cursor: pointer;
    }

    .message {
      color: red;
      margin-top: 10px;
    }

    ul {
      padding-left: 20px;
    }

    li {
      margin-bottom: 8px;
    }
  </style>
</head>

<body>
  <div class="form-container">
    <?php if ($step !== 'done'): ?>
      <h2>Step <?= $step ?> of <?= count($fields) ?></h2>
      <?php
      $currentField = $fields[$step - 1];
      $currentValue = $_SESSION['data'][$currentField] ?? "";
      ?>
      <form method="post">
        <label for="<?= $currentField ?>"><?= ucfirst($currentField) ?>:</label>
        <input type="text" name="<?= $currentField ?>" value="<?= htmlspecialchars($currentValue) ?>" />
        <button type="submit">Next</button>
      </form>
      <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
      <?php endif; ?>
    <?php else: ?>
      <h2>All steps completed!</h2>
      <ul>
        <?php foreach ($fields as $field): ?>
          <li><strong><?= ucfirst($field) ?>:</strong> <?= htmlspecialchars($_SESSION['data'][$field]) ?></li>
        <?php endforeach; ?>
      </ul>
      <form method="post">
        <button type="submit" name="reset">Start Over</button>
      </form>
    <?php endif; ?>
  </div>
</body>

</html>