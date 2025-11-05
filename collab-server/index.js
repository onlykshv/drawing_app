const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
  cors: { origin: "*" }
});

// In-memory structure: boardId -> Set of socket IDs (can use usernames if sent from client)
const usersInBoards = {};

app.get('/', (req, res) => {
  res.send('Socket.IO server is running!');
});

io.on('connection', (socket) => {
  console.log('User connected!');
  let currentBoard = null;
  let username = "User" + Math.floor(Math.random() * 10000);

  // Accept joinBoard as object with boardId and username
  socket.on('joinBoard', (data) => {
    if (typeof data === 'object' && data.boardId) {
      currentBoard = data.boardId;
      if (typeof data.username === 'string' && data.username.trim().length > 0) {
        username = data.username.trim();
      }
      if (!usersInBoards[currentBoard]) usersInBoards[currentBoard] = new Set();
      usersInBoards[currentBoard].add(username);
      socket.join(currentBoard);
      io.to(currentBoard).emit('userList', Array.from(usersInBoards[currentBoard]));
      console.log(`User joined board ${currentBoard} as ${username}`);
    }
  });

  socket.on('drawing', (data) => {
    socket.to(data.boardId).emit('drawing', data);
  });

  socket.on('chatMessage', (data) => {
    // Broadcast to all (including sender) with username and timestamp
    if (typeof data === 'object' && data.boardId && data.message) {
      const msgData = {
        boardId: data.boardId,
        username: username,
        message: data.message,
        timestamp: new Date().toISOString()
      };
      io.to(data.boardId).emit('chatMessage', msgData);
    }
  });

  socket.on('clearBoard', (data) => {
    socket.to(data.boardId).emit('clearBoard', data);
  });

  socket.on('disconnect', () => {
    if (currentBoard && usersInBoards[currentBoard]) {
      usersInBoards[currentBoard].delete(username);
      io.to(currentBoard).emit('userList', Array.from(usersInBoards[currentBoard]));
      console.log('User disconnected!');
    }
  });
});

const PORT = 4000;
server.listen(PORT, () => {
  console.log(`Collaboration server running on http://localhost:${PORT}`);
});
