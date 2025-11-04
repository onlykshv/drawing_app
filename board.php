<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit();
}
$boardId = isset($_GET['id']) ? intval($_GET['id']) : 1;
$boardName = "Drawing Board"; // Set dynamically if saved in DB with a name

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
    .main-flex {
      display: flex;
      height: 100vh;
      width: 100vw;
      overflow: hidden;
    }
    .header-center {
      width: 100vw;
      text-align: center;
      margin-top: 18px;
      margin-bottom: 18px;
      font-size: 2em;
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
      height: 100vh;
      position: relative;
    }
    .toolbar {
      display: flex;
      gap: 18px;
      margin-bottom: 16px;
      margin-top: 35px;
      position: relative;
      z-index: 3;
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
      height: 100vh;
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
      max-height: calc(100vh - 190px);
      font-size: 1.08em;
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
      .header-center { font-size: 1.3em; }
    }
    @media (max-width:700px) {
      #board { width: 95vw !important; height: 38vh !important;}
      .toolbar { flex-wrap:wrap; }
    }
  </style>
</head>
<body>
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
        <button id="save-preview-btn" title="Save Preview" class="toolbar-btn">📝
</button>
      </div>
      <canvas id="board" width="700" height="480"></canvas>
    </div>
    
    <!-- Chat side -->
    <div class="chat-col">
      <div class="chat-head">Chat</div>
      <div id="user-list" style="margin-bottom:12px; font-weight:bold; color:#fff;">Online Users: </div>
      <div id="chat-box"></div>
      <div class="chat-input-row">
        <input type="text" id="chat-input" placeholder="Type a message..." autocomplete="off" />
        <button id="send-chat">Send</button>
      </div>
    </div>
  </div>
    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
  <?php if ($previewExists): ?>
    <script>
      // This script loads the preview image onto the canvas as soon as the DOM is ready.
      document.addEventListener("DOMContentLoaded", function () {
        var img = new Image();
        img.onload = function () {
          var canvas = document.getElementById('board');
          if (!canvas) return;
          var ctx = canvas.getContext('2d');
          ctx.clearRect(0, 0, canvas.width, canvas.height);
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        };
        img.src = "<?= $previewPath ?>";
      });
    </script>
  <?php endif; ?>
  <script src="board.js"></script>
</body>
</html>

