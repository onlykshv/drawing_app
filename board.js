window.onload = function() {
  const socket = io('http://localhost:3000');

  const canvas = document.getElementById('board');
  const ctx = canvas.getContext('2d');

  let drawing = false;
  let current = { x: 0, y: 0 };
  let brushColor = "#247cff";
  let brushSize = 4;
  let eraserMode = false;

  const urlParams = new URLSearchParams(window.location.search);
  const boardId = urlParams.get('id');
  socket.emit('joinBoard', boardId);

  // Toolbar
  const colorPicker = document.getElementById('colorPicker');
  const sizeSlider = document.getElementById('sizeSlider');
  const eraserBtn = document.getElementById('eraserBtn');
  const clearBtn = document.getElementById('clearBtn');
  const exportBtn = document.getElementById('exportBtn');

  if (colorPicker) {
    colorPicker.addEventListener('input', e => {
      brushColor = e.target.value;
      eraserMode = false;
      if (eraserBtn) eraserBtn.classList.remove("active");
    });
  }

  if (sizeSlider) {
    sizeSlider.addEventListener('input', e => {
      brushSize = parseInt(e.target.value, 10);
    });
  }

  if (eraserBtn) {
    eraserBtn.addEventListener('click', () => {
      eraserMode = !eraserMode;
      if (eraserMode) {
        eraserBtn.classList.add("active");
      } else {
        eraserBtn.classList.remove("active");
      }
    });
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      socket.emit('clearBoard', { boardId });
    });
    socket.on('clearBoard', data => {
      if (data.boardId === boardId)
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });
  }
  const userListDiv = document.getElementById('user-list');

socket.on('userList', users => {
  if (!userListDiv) return;
  if (!users || users.length === 0) {
    userListDiv.innerText = "Online Users: None";
    return;
  }
  // Map usernames (or IDs) to span tags for styling
  userListDiv.innerHTML = 'Online Users: ' + users.map(u =>
    `<span style="background:rgba(36,124,255,0.3); padding: 4px 8px; border-radius:8px; margin-right:6px;">${u}</span>`
  ).join('');
});


  if (exportBtn) {
    exportBtn.addEventListener('click', () => {
      const link = document.createElement('a');
      link.download = `board_${boardId}.png`;
      link.href = canvas.toDataURL("image/png");
      link.click();
    });
  }

  // Drawing
  canvas.addEventListener('mousedown', (e) => {
    drawing = true;
    current.x = e.offsetX;
    current.y = e.offsetY;
  });

  canvas.addEventListener('mouseup', () => { drawing = false; });
  canvas.addEventListener('mouseout', () => { drawing = false; });

  canvas.addEventListener('mousemove', (e) => {
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

    if (!emit) return;
    socket.emit('drawing', {
      boardId,
      x0, y0, x1, y1,
      color,
      size
    });
  }

  socket.on('drawing', data => {
    if (data.boardId !== boardId) return;
    drawLine(data.x0, data.y0, data.x1, data.y1, data.color, data.size, false);
  });

  // --- Chat
  const chatInput = document.getElementById('chat-input');
  const chatBox = document.getElementById('chat-box');
  const sendBtn = document.getElementById('send-chat');

  if (sendBtn) sendBtn.addEventListener('click', sendMessage);
  if (chatInput) {
    chatInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') sendMessage();
    });
  }

  function sendMessage() {
    if (!chatInput) return;
    const message = chatInput.value.trim();
    if (!message) return;
    socket.emit('chatMessage', { boardId: boardId, message: message });
    chatInput.value = '';
  }

  socket.on('chatMessage', (data) => {
    if (data.boardId !== boardId) return;
    if (!chatBox) return;
    const msgDiv = document.createElement('div');
    msgDiv.textContent = data.message;
    chatBox.appendChild(msgDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
  });
};
