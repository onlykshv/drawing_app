<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit();
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create New Board</title>
  <style>
    body {
      background: linear-gradient(135deg, #3a64b1 0%, #2a4d82 100%);
      font-family: 'Segoe UI', Tahoma, Verdana, sans-serif;
      margin: 0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #f0f8ff;
    }
    .container {
      background: #1f2f57;
      padding: 40px 50px;
      border-radius: 14px;
      box-shadow: 0 12px 38px rgba(0, 0, 0, 0.25);
      width: 360px;
      text-align: center;
    }
    h2 {
      margin-bottom: 32px;
      font-weight: 700;
      letter-spacing: 2px;
      font-size: 2em;
    }
    input[type="text"] {
      width: 90%;
      padding: 14px 18px;
      margin-bottom: 30px;
      border: none;
      border-radius: 10px;
      font-size: 1.1em;
      font-weight: 600;
      outline: none;
      background: #2e467d;
      color: #e1e6f2;
      text-align: center;
    }
    input[type="text"]::placeholder {
      color: #a1b2d3;
      font-weight: 400;
      font-style: italic;
    }
    button {
      background: #4d7aff;
      border: none;
      padding: 14px 28px;
      border-radius: 14px;
      font-weight: 700;
      color: #f7f9ff;
      font-size: 1.1em;
      cursor: pointer;
      transition: background 0.3s ease;
      width: 100%;
      box-shadow: 0 0 20px #5c88ff8f;
    }
    button:hover {
      background: #3652d0;
      box-shadow: 0 0 24px #3e60f98a;
    }
    .welcome-msg {
      font-size: 1.1em;
      margin-bottom: 24px;
      opacity: 0.85;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Create New Board</h2>
    <div class="welcome-msg">Welcome, <?= htmlspecialchars($username) ?>!</div>
    <form action="create_drawing_handler.php" method="POST" id="create-board-form">
      <input type="text" name="title" placeholder="Enter board title" maxlength="45" />
      <button type="submit">Create Board</button>
    </form>
  </div>
</body>
</html>
