const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
  cors: { origin: "*" }
});

// Basic route for testing
app.get('/', (req, res) => {
  res.send('Socket.IO server is running!');
});

io.on('connection', (socket) => {
  console.log('User connected!');

  socket.on('joinBoard', (boardId) => {
    socket.join(boardId);
    console.log(`User joined board ${boardId}`);
  });

  socket.on('drawing', (data) => {
    socket.to(data.boardId).emit('drawing', data);
  });

  socket.on('chatMessage', (data) => {
    socket.to(data.boardId).emit('chatMessage', data);
  });

  socket.on('disconnect', () => {
    console.log('User disconnected!');
  });
});

const PORT = 3000;
server.listen(PORT, () => {
  console.log(`Collaboration server running on http://localhost:${PORT}`);
});
