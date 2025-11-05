<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit();
}

// Include database connection
include 'db.php';

$boardId = isset($_GET['id']) ? intval($_GET['id']) : 1;
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Fetch board data from database
$stmt = $conn->prepare("SELECT title, drawing_data FROM drawings WHERE id = ?");
$stmt->bind_param("i", $boardId);
$stmt->execute();
$result = $stmt->get_result();
$board = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Use fetched title or default
$boardName = $board['title'] ?? "Drawing Board";
$drawingData = $board['drawing_data'] ?? null;

// Check if preview image exists for this board
$previewPath = "previews/board_{$boardId}_preview.png";
$previewExists = file_exists(__DIR__ . "/$previewPath");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($boardName) ?> | Board <?= htmlspecialchars($boardId) ?></title>
  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
      font-family: 'Segoe UI', Arial, sans-serif;
      background: linear-gradient(120deg, #233155 0%, #31518a 100%);
      color: #fff;
    }
    
    /* NEW: Top navigation bar */
    .top-nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: rgba(28, 42, 72, 0.95);
      backdrop-filter: blur(10px);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }
    
    .nav-left {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .home-btn, .logout-btn {
      background: rgba(36, 124, 255, 0.9);
      color: white;
      padding: 10px 18px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.95em;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.3s ease;
      cursor: pointer;
      border: none;
    }
    
    .logout-btn {
      background: rgba(231, 76, 60, 0.9);
    }
    
    .home-btn:hover {
      background: rgba(36, 124, 255, 1);
      transform: translateY(-2px);
    }
    
    .logout-btn:hover {
      background: rgba(231, 76, 60, 1);
      transform: translateY(-2px);
    }
    
    .user-info {
      font-size: 0.95em;
      opacity: 0.9;
    }
    
    .main-flex {
      display: flex;
      height: 100vh;
      width: 100vw;
      overflow: hidden;
      padding-top: 60px; /* Space for top nav */
    }
    
    .header-center {
      width: 100vw;
      text-align: center;
      margin-top: 78px; /* Adjusted for nav bar */
      margin-bottom: 18px;
      font-size: 1.8em;
      font-weight: 600;
      letter-spacing: 2px;
      color: #fff;
      text-shadow: 0 2px 8px #22335560;
      position: absolute;
      left: 0;
      top: 0;
      z-index: 2;
      pointer-events: none;
    }
    .draw-col {
      flex: 2;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-width: 0;
      height: calc(100vh - 60px);
      position: relative;
    }
    .toolbar {
      display: flex;
      gap: 18px;
      margin-bottom: 16px;
      margin-top: 10px;
      position: relative;
      z-index: 3;
      flex-wrap: wrap;
      justify-content: center;
    }
    .toolbar input[type="color"], .toolbar button, .toolbar input[type="range"] {
      outline: none;
      border: none;
      border-radius: 7px;
      padding: 0;
      background: #fff;
      cursor: pointer;
      box-shadow: 0 2px 16px rgba(36,124,255,0.12);
      transition: box-shadow 0.2s, background 0.15s;
      height: 44px;
    }
    .toolbar button, .toolbar input[type="color"] {
      width: 44px;
      font-size: 1.3em;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .toolbar button:hover, .toolbar input[type="color"]:hover {
      box-shadow: 0 4px 24px #247cff47;
      background: #f3f8ff;
    }
    .toolbar input[type="range"] {
      background: #e1eafd;
      width: 140px;
      margin: 0 10px;
      accent-color: #247cff;
      height: 40px;
    }
    #eraserBtn.active { background: #fce9ff !important; box-shadow: 0 0 9px #247cff88 inset; }
    
    #save-preview-btn {
      background: #10b981 !important;
      color: white;
      font-weight: 600;
      width: auto !important;
      padding: 0 14px !important;
      white-space: nowrap;
    }
    #save-preview-btn:hover {
      background: #059669 !important;
    }
    
    #board {
      border: 1.8px solid #247cff;
      border-radius: 11px;
      background: #fff;
      box-shadow: 0 4px 22px #2a447e14, 0 2px 14px #1c223614;
      margin-bottom: 5px;
    }
    .chat-col {
      flex: 1.2;
      display: flex;
      flex-direction: column;
      padding: 18px 16px 12px 10px;
      height: calc(100vh - 60px);
      min-width: 320px;
      max-width: 440px;
      background: transparent;
      z-index: 1;
    }
    .chat-head {
      margin-bottom:10px;
      color:#fff;
      font-size: 1.13em;
      font-weight: 600;
      letter-spacing: 1px;
    }
    #chat-box {
      background: #202b4b;
      border-radius: 8px;
      flex: 1;
      overflow-y: auto;
      padding: 13px 10px 10px 12px;
      color: #fff;
      border: 1px solid #2d4175;
      margin-bottom: 13px;
      min-height: 0;
      font-size: 1em;
    }
    .chat-message {
      background: rgba(255, 255, 255, 0.1);
      padding: 8px 12px;
      border-radius: 6px;
      margin-bottom: 8px;
      word-wrap: break-word;
    }
    .chat-message.own {
      background: rgba(36, 124, 255, 0.3);
      text-align: right;
    }
    .chat-message .username {
      font-weight: 700;
      color: #63a1ff;
      margin-right: 6px;
    }
    .chat-message.own .username {
      color: #10b981;
    }
    .chat-message .text {
      color: #fff;
    }
    .chat-message.system {
      background: rgba(16, 185, 129, 0.2);
      font-style: italic;
      font-size: 0.9em;
      text-align: center;
    }
    .chat-input-row {
      display: flex; gap: 10px; align-items: center;
    }
    #chat-input {
      flex: 1;
      padding: 11px;
      border-radius: 7px;
      border: none;
      background: #e9f2fd;
      color: #102147;
      font-size: 1em;
    }
    #send-chat {
      padding: 10px 20px;
      border-radius: 7px;
      background-color: #247cff;
      color: #fff;
      border: none;
      font-weight: bold;
      font-size: 1.07em;
      letter-spacing: 0.05em;
      cursor: pointer;
      transition: background 0.18s;
    }
    #send-chat:hover { background: #1860c1; }

    @media (max-width:1050px) {
      .main-flex { flex-direction: column; }
      .draw-col, .chat-col { width: 100vw; max-width: none; min-width: 0; height: auto;}
      .header-center { font-size: 1.3em; margin-top: 70px; }
      .top-nav { padding: 0 10px; }
      .user-info { display: none; }
    }
    @media (max-width:700px) {
      #board { width: 95vw !important; height: 38vh !important;}
      .toolbar { flex-wrap:wrap; }
      .nav-left { gap: 8px; }
      .home-btn, .logout-btn { padding: 8px 12px; font-size: 0.85em; }
    }
  </style>
</head>
<body>
  <!-- NEW: Top navigation bar -->
  <div class="top-nav">
    <div class="nav-left">
      <a href="dashboard.php" class="home-btn">🏠 Home</a>
      <a href="logout.php" class="logout-btn">🚪 Logout</a>
    </div>
    <div class="user-info">
      👤 <?= htmlspecialchars($username) ?>
    </div>
  </div>
  
  <div class="header-center">
     <?= htmlspecialchars($boardName) ?> | Board ID: <?= htmlspecialchars($boardId) ?>
  </div>
  <div class="main-flex">
    <!-- Canvas side -->
    <div class="draw-col">
      <div class="toolbar">
        <input type="color" id="colorPicker" value="#247cff" title="Brush Color" />
        <input type="range" id="sizeSlider" min="2" max="28" value="4" title="Brush Size" />
        <button id="eraserBtn" title="Eraser">🧽</button>
        <button id="clearBtn" title="Clear Board">🗑️</button>
        <button id="exportBtn" title="Export Drawing">💾</button>
        <button id="save-preview-btn" title="Save Preview">💾 Save</button>
      </div>
      <canvas id="board" width="700" height="480"></canvas>
    </div>
    
    <!-- Chat side -->
    <div class="chat-col">
      <div class="chat-head">Chat</div>
      <div id="user-list" style="margin-bottom:12px; font-weight:bold; color:#fff;">Online Users: <span id="user-count">0</span></div>
      <div id="chat-box">
        <div class="chat-message system">Chat is ready!</div>
      </div>
      <div class="chat-input-row">
        <input type="text" id="chat-input" placeholder="Type a message..." autocomplete="off" />
        <button id="send-chat">Send</button>
      </div>
    </div>
  </div>

  <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
  
  <!-- NEW: Inject username and drawing data -->
  <script>
    const savedDrawingData = <?= $drawingData ? $drawingData : 'null' ?>;
    const currentUsername = "<?= htmlspecialchars($username) ?>";
  </script>
  
  <script src="board.js"></script>
</body>
</html>
