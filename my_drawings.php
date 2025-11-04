<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit();
}
$userId = $_SESSION['user_id'];

include 'db.php';

// Fetch the actual username from database
$userQuery = "SELECT username FROM users WHERE id = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userData = $userResult->fetch_assoc();
$username = $userData['username'];

$sql = "SELECT id, title, created_at FROM drawings WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($username) ?>’s Drawings</title>
  <style>
    body {
      background: linear-gradient(150deg, #1e2f58, #385b9d);
      margin: 0;
      color: #f0f8ff;
      font-family: 'Segoe UI', Tahoma, Verdana, sans-serif;
      min-height: 100vh;
      padding: 36px 30px;
    }
    header {
      text-align: center;
      margin-bottom: 36px;
    }
    h1 {
      font-weight: 700;
      font-size: 2.5em;
      letter-spacing: 0.1em;
    }
    h2 {
      margin-top: 0;
      font-weight: 400;
      font-size: 1.2em;
      opacity: 0.75;
      letter-spacing: 0.03em;
    }
    main {
      max-width: 920px;
      margin: 0 auto;
    }
    .board-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .board-list li {
      background: rgba(255, 255, 255, 0.12);
      margin-bottom: 24px;
      border-radius: 14px;
      padding: 22px 26px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: background-color 0.3s ease;
    }
    .board-list li:hover {
      background: rgba(255, 255, 255, 0.25);
    }
    .board-title {
      font-weight: 600;
      font-size: 1.18em;
      letter-spacing: 0.03em;
      color: white;
      cursor: pointer;
      text-decoration: none;
      flex-grow: 1;
    }
    .board-date {
      font-size: 0.85em;
      opacity: 0.6;
      margin-left: 18px;
      white-space: nowrap;
    }
    .board-preview {
      width: 150px;
      height: 120px;
      background: #eee;
      border-radius: 8px;
      margin-right: 18px;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }
    .board-preview img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      border-radius: 8px;
    }
    .create-btn {
      display: block;
      margin: 52px auto 0 auto;
      max-width: 220px;
      padding: 14px 28px;
      color: #eaf4ff;
      text-align: center;
      font-weight: 700;
      font-size: 1.22em;
      border: 2px solid #b0d0ff;
      border-radius: 12px;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }
    .create-btn:hover {
      background-color: #618aed;
      border-color: transparent;
    }
    @media (max-width: 720px) {
      body, main { padding: 20px; }
      .board-list li {
        flex-direction: column;
        align-items: flex-start;
      }
      .board-date {
        margin-left: 0;
        margin-top: 8px;
      }
    }
  </style>
</head>
<body>

<header>
  <h1>Hello, <?= htmlspecialchars($username) ?></h1>
  <h2>Your Saved Drawing Boards</h2>
</header>

<main>
  <ul class="board-list">
    <?php while ($board = $result->fetch_assoc()):
      $previewPath = "previews/board_{$board['id']}_preview.png";
      $previewExists = file_exists(__DIR__ . "/$previewPath");
    ?>
    <li>
      <a href="board.php?id=<?= $board['id'] ?>" class="board-title" title="Enter board"><?= htmlspecialchars($board['title'] ?: "Untitled Board #{$board['id']}") ?></a>
      <div class="board-preview">
        <?php if ($previewExists): ?>
          <img src="<?= $previewPath ?>" alt="Preview" />
        <?php else: ?>
          <span style="color: #888; font-style: italic;">No preview</span>
        <?php endif; ?>
      </div>
      <span class="board-date"><?= date('M j, Y', strtotime($board['created_at'])) ?></span>
      <form action="delete_drawing.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this drawing?');">
        <input type="hidden" name="drawing_id" value="<?= $board['id'] ?>">
        <button type="submit" style="
          background: #e74c3c;
          color: #fff;
          border: none;
          border-radius: 7px;
          padding: 8px 14px;
          font-weight: 700;
          font-size: 1em;
          cursor: pointer;
          margin-left: 14px;
          transition: background 0.18s;">
          Delete
        </button>
      </form>
    </li>
    <?php endwhile; ?>
  </ul>

  <a href="create_drawing.php" class="create-btn">+ Create New Board</a>
</main>

</body>
</html>
