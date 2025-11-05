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
  <title><?= htmlspecialchars($username) ?>'s Drawings</title>
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
      transition: background-color 0.3s ease, transform 0.2s ease;
      position: relative;
    }
    .board-list li:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: translateY(-2px);
    }
    .board-info {
      display: flex;
      align-items: center;
      flex-grow: 1;
      text-decoration: none;
      color: inherit;
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
      flex-shrink: 0;
    }
    .board-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 8px;
    }
    .board-details {
      flex-grow: 1;
    }
    .board-title {
      font-weight: 600;
      font-size: 1.3em;
      letter-spacing: 0.03em;
      color: white;
      margin-bottom: 8px;
      display: block;
    }
    .board-date {
      font-size: 0.9em;
      opacity: 0.6;
    }
    .board-actions {
      display: flex;
      gap: 10px;
      margin-left: 20px;
    }
    .btn-open {
      background: #3b82f6;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 600;
      font-size: 1em;
      cursor: pointer;
      transition: background 0.18s;
      text-decoration: none;
      display: inline-block;
    }
    .btn-open:hover {
      background: #2563eb;
    }
    .btn-delete {
      background: #e74c3c;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px 16px;
      font-weight: 600;
      font-size: 1em;
      cursor: pointer;
      transition: background 0.18s;
    }
    .btn-delete:hover {
      background: #c0392b;
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
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      opacity: 0.7;
    }
    @media (max-width: 720px) {
      body, main { padding: 20px; }
      .board-list li {
        flex-direction: column;
        align-items: flex-start;
      }
      .board-info {
        flex-direction: column;
        width: 100%;
      }
      .board-preview {
        width: 100%;
        margin-right: 0;
        margin-bottom: 16px;
      }
      .board-actions {
        width: 100%;
        margin-left: 0;
        margin-top: 16px;
      }
    }
  </style>
</head>
<body>

<header>
  <h1>Hello, <?= htmlspecialchars($username) ?>! 👋</h1>
  <h2>Your Saved Drawing Boards</h2>
</header>

<main>
  <?php if ($result->num_rows > 0): ?>
    <ul class="board-list">
      <?php while ($board = $result->fetch_assoc()):
        $previewPath = "previews/board_{$board['id']}_preview.png";
        $previewExists = file_exists(__DIR__ . "/$previewPath");
      ?>
      <li>
        <div class="board-info">
          <div class="board-preview">
            <?php if ($previewExists): ?>
              <img src="<?= $previewPath ?>" alt="Preview" />
            <?php else: ?>
              <span style="color: #888; font-style: italic;">No preview</span>
            <?php endif; ?>
          </div>
          <div class="board-details">
            <div class="board-title"><?= htmlspecialchars($board['title'] ?: "Untitled Board #{$board['id']}") ?></div>
            <div class="board-date">Created: <?= date('M j, Y', strtotime($board['created_at'])) ?></div>
          </div>
        </div>
        <div class="board-actions">
          <a href="board.php?id=<?= $board['id'] ?>" class="btn-open">Open 🎨</a>
          <form action="delete_drawing.php" method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Delete this drawing?');">
            <input type="hidden" name="drawing_id" value="<?= $board['id'] ?>">
            <button type="submit" class="btn-delete">Delete 🗑️</button>
          </form>
        </div>
      </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <div class="empty-state">
      <h3>No boards yet!</h3>
      <p>Create your first drawing board to get started.</p>
    </div>
  <?php endif; ?>

  <a href="create_drawing.php" class="create-btn">+ Create New Board</a>
</main>

</body>
</html>
