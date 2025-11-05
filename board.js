window.onload = function () {
  const canvas = document.getElementById("board");
  const savePreviewBtn = document.getElementById("save-preview-btn");
  const colorPicker = document.getElementById("colorPicker");
  const sizeSlider = document.getElementById("sizeSlider");
  const eraserBtn = document.getElementById("eraserBtn");
  const clearBtn = document.getElementById("clearBtn");
  const exportBtn = document.getElementById("exportBtn");

  const ctx = canvas.getContext("2d");
  let drawing = false;
  let current = { x: 0, y: 0 };
  let brushColor = "#247cff";
  let brushSize = 4;
  let eraserMode = false;
  let allStrokes = [];

  const urlParams = new URLSearchParams(window.location.search);
  const boardId = urlParams.get("id");

  // Get username from PHP injection
  const username =
    typeof currentUsername !== "undefined" ? currentUsername : "User";

  // Load saved drawing data
  if (
    typeof savedDrawingData !== "undefined" &&
    savedDrawingData &&
    Array.isArray(savedDrawingData)
  ) {
    console.log("Loading saved drawing...");
    savedDrawingData.forEach((stroke) => {
      drawLine(
        stroke.x0,
        stroke.y0,
        stroke.x1,
        stroke.y1,
        stroke.color,
        stroke.width,
        false
      );
    });
    allStrokes = [...savedDrawingData];
  }

  // Save Preview Button
  if (savePreviewBtn) {
    savePreviewBtn.addEventListener("click", () => {
      if (!canvas) {
        alert("No canvas found!");
        return;
      }
      const dataUrl = canvas.toDataURL("image/png");
      const base64Image = dataUrl.split(",")[1];

      fetch("save_preview.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          boardId: boardId,
          previewImage: base64Image,
          drawingData: allStrokes,
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            alert("Drawing saved successfully!");
            window.location.href = "my_drawings.php";
          } else {
            alert("Failed to save drawing.");
          }
        })
        .catch((err) => {
          alert("Error saving drawing.");
          console.error(err);
        });
    });
  }

  // Real-time via Socket.io
  const socket = io("http://localhost:4000");
  socket.emit("joinBoard", { boardId, username });

  // Toolbar Functionality
  if (colorPicker) {
    colorPicker.addEventListener("input", (e) => {
      brushColor = e.target.value;
      eraserMode = false;
      if (eraserBtn) eraserBtn.classList.remove("active");
    });
  }
  if (sizeSlider) {
    sizeSlider.addEventListener("input", (e) => {
      brushSize = parseInt(e.target.value, 10);
    });
  }
  if (eraserBtn) {
    eraserBtn.addEventListener("click", () => {
      eraserMode = !eraserMode;
      eraserBtn.classList.toggle("active", eraserMode);
    });
  }
  if (clearBtn) {
    clearBtn.addEventListener("click", () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      allStrokes = [];
      socket.emit("clearBoard", { boardId });
    });
    socket.on("clearBoard", (data) => {
      if (data.boardId === boardId) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        allStrokes = [];
      }
    });
  }
  if (exportBtn) {
    exportBtn.addEventListener("click", () => {
      const link = document.createElement("a");
      link.download = `board_${boardId}.png`;
      link.href = canvas.toDataURL("image/png");
      link.click();
    });
  }

  // User Presence
  const userListDiv = document.getElementById("user-list");
  socket.on("userList", (users) => {
    if (!userListDiv) return;
    if (!users || users.length === 0) {
      userListDiv.innerHTML = "Online Users: <span id='user-count'>0</span>";
      return;
    }
    userListDiv.innerHTML =
      'Online Users: <span id="user-count">' +
      users.length +
      "</span> - " +
      users
        .map(
          (u) =>
            `<span style="background:rgba(36,124,255,0.3); padding: 4px 8px; border-radius:8px; margin-right:6px;">${u}</span>`
        )
        .join("");
  });

  // Drawing Events
  canvas.addEventListener("mousedown", (e) => {
    drawing = true;
    current.x = e.offsetX;
    current.y = e.offsetY;
  });
  canvas.addEventListener("mouseup", () => {
    drawing = false;
  });
  canvas.addEventListener("mouseout", () => {
    drawing = false;
  });
  canvas.addEventListener("mousemove", (e) => {
    if (!drawing) return;
    const color = eraserMode ? "#ffffff" : brushColor;
    const size = brushSize;
    drawLine(current.x, current.y, e.offsetX, e.offsetY, color, size, true);
    current.x = e.offsetX;
    current.y = e.offsetY;
  });

  function drawLine(x0, y0, x1, y1, color, size, emit) {
    ctx.beginPath();
    ctx.moveTo(x0, y0);
    ctx.lineTo(x1, y1);
    ctx.strokeStyle = color;
    ctx.lineWidth = size;
    ctx.lineCap = "round";
    ctx.stroke();
    ctx.closePath();

    allStrokes.push({ x0, y0, x1, y1, color, width: size });

    if (!emit) return;
    socket.emit("drawing", { boardId, x0, y0, x1, y1, color, size });
  }

  socket.on("drawing", (data) => {
    if (data.boardId !== boardId) return;
    drawLine(data.x0, data.y0, data.x1, data.y1, data.color, data.size, false);
  });

  // Chat - FIXED VERSION
  const chatInput = document.getElementById("chat-input");
  const chatBox = document.getElementById("chat-box");
  const sendBtn = document.getElementById("send-chat");

  if (sendBtn) sendBtn.addEventListener("click", sendMessage);
  if (chatInput) {
    chatInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") sendMessage();
    });
  }

  function sendMessage() {
    if (!chatInput) return;
    const message = chatInput.value.trim();
    if (!message) return;

    // Send to server (server will echo back with username)
    socket.emit("chatMessage", {
      boardId: boardId,
      message: message
    });
    chatInput.value = "";
  }

  // NEW: Display message function with username
  function displayMessage(user, message, isOwn = false) {
    if (!chatBox) return;
    const msgDiv = document.createElement("div");
    msgDiv.className = "chat-message" + (isOwn ? " own" : "");
    // Add time if available
    let timeHtml = "";
    if (arguments.length > 3 && arguments[3]) {
      const date = new Date(arguments[3]);
      if (!isNaN(date.getTime())) {
        const h = date.getHours().toString().padStart(2, '0');
        const m = date.getMinutes().toString().padStart(2, '0');
        timeHtml = `<div style='font-size:0.8em;color:#b3d1ff;margin-top:2px;'>${h}:${m}</div>`;
      }
    }
    msgDiv.innerHTML = `<span class="username">${user}:</span><span class="text">${message}</span>${timeHtml}`;
    chatBox.appendChild(msgDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
  }

  // NEW: Receive messages from others
  socket.on("chatMessage", (data) => {
  if (data.boardId !== boardId) return;
  // Always display, mark as own if username matches, pass timestamp
  displayMessage(data.username, data.message, data.username === username, data.timestamp);
  });
};
