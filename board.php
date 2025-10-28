<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id'])) {
    die("Board ID missing.");
}

$board_id = intval($_GET['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Collaborative Drawing Board</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h2>Board: <?php echo htmlspecialchars($board_id); ?></h2>
  <canvas id="board" width="800" height="600" style="border:1px solid #000;"></canvas>

  <div id="chat">
    <h3>Chat</h3>
    <div id="chat-box" style="border:1px solid #ccc;height:200px;overflow-y:auto;"></div>
    <input type="text" id="chat-input" placeholder="Type a message..."/>
    <button id="send-chat">Send</button>
  </div>

  <script src="/socket.io/socket.io.js"></script>
  <script src="board.js"></script>
</body>
</html>
