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

  // Each socket gets a random "User" name (replace with session username if desired)
  let username = "User" + Math.floor(Math.random() * 10000);

  socket.on('joinBoard', (boardId) => {
    currentBoard = boardId;
    if (!usersInBoards[boardId]) usersInBoards[boardId] = new Set();
    usersInBoards[boardId].add(username);
    socket.join(boardId);

    // Send current user list to everyone in this board
    io.to(boardId).emit('userList', Array.from(usersInBoards[boardId]));
    console.log(`User joined board ${boardId}`);
  });

  socket.on('drawing', (data) => {
    socket.to(data.boardId).emit('drawing', data);
  });

  socket.on('chatMessage', (data) => {
    socket.to(data.boardId).emit('chatMessage', data);
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

const PORT = 3000;
server.listen(PORT, () => {
  console.log(`Collaboration server running on http://localhost:${PORT}`);
});
