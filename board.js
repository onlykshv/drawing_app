const socket = io('http://localhost:3000');  // Node.js server URL and port

const canvas = document.getElementById('board');
const ctx = canvas.getContext('2d');

let drawing = false;
let current = { x: 0, y: 0 };

// Join board room using board ID from URL
const urlParams = new URLSearchParams(window.location.search);
const boardId = urlParams.get('id');
socket.emit('joinBoard', boardId);

// Mouse Events
canvas.addEventListener('mousedown', (e) => {
  drawing = true;
  current.x = e.offsetX;
  current.y = e.offsetY;
});

canvas.addEventListener('mouseup', () => {
  drawing = false;
});

canvas.addEventListener('mouseout', () => {
  drawing = false;
});

canvas.addEventListener('mousemove', (e) => {
  if (!drawing) return;
  drawLine(current.x, current.y, e.offsetX, e.offsetY, true);
  current.x = e.offsetX;
  current.y = e.offsetY;
});

// Draw a line on canvas, optionally emit to server
function drawLine(x0, y0, x1, y1, emit) {
  ctx.beginPath();
  ctx.moveTo(x0, y0);
  ctx.lineTo(x1, y1);
  ctx.strokeStyle = '#000';
  ctx.lineWidth = 2;
  ctx.stroke();
  ctx.closePath();

  if (!emit) return;

  // Send line data to server
  socket.emit('drawing', {
    boardId: boardId,
    x0: x0,
    y0: y0,
    x1: x1,
    y1: y1
  });
}

// Receive drawing data from server
socket.on('drawing', (data) => {
  if (data.boardId !== boardId) return; // Only paint if same board
  drawLine(data.x0, data.y0, data.x1, data.y1, false);
});

// -------------------- Chat ---------------------

const chatForm = document.getElementById('chat-form') || null;
const chatInput = document.getElementById('chat-input');
const chatBox = document.getElementById('chat-box');
const sendBtn = document.getElementById('send-chat');

sendBtn.addEventListener('click', sendMessage);

chatInput.addEventListener('keypress', function(e) {
  if (e.key === 'Enter') sendMessage();
});

function sendMessage() {
  const message = chatInput.value.trim();
  if (!message) return;
  socket.emit('chatMessage', { boardId: boardId, message: message });
  chatInput.value = '';
}

// Receive chat messages
socket.on('chatMessage', (data) => {
  if (data.boardId !== boardId) return;

  const msgDiv = document.createElement('div');
  msgDiv.textContent = data.message;
  chatBox.appendChild(msgDiv);
  chatBox.scrollTop = chatBox.scrollHeight;
});
