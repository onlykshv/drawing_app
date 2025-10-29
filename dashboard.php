<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Game Lobby</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="game-header">
    <h1>🎨 Draw & Chat Lobby</h1>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
  </div>

  <div class="dashboard-flex">
    <div class="dashboard-card">
      <h3>Create a New Board</h3>
      <form action="create_drawing.php" method="POST" class="create-form">
        <input type="text" name="title" placeholder="Board Title" required>
        <button type="submit">Create</button>
      </form>
    </div>
    <div class="dashboard-card">
      <h3>Join a Board</h3>
      <form action="board.php" method="GET" class="join-form">
        <input type="number" name="id" placeholder="Board ID" required>
        <button type="submit">Join</button>
      </form>
    </div>
    <div class="dashboard-card">
      <h3>Your Boards</h3>
      <a href="my_drawings.php" class="view-btn">View My Boards</a>
    </div>
  </div>
</body>
</html>
