<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: create_drawing.php');
  exit();
}

include 'db.php';

$userId = $_SESSION['user_id'];
$title = isset($_POST['title']) ? trim($_POST['title']) : '';

// Sanitize and limit title length
if (strlen($title) > 45) {
  $title = substr($title, 0, 45);
}

if ($title === '') {
  $title = NULL; // Allow null for untitled boards
}

$stmt = $conn->prepare("INSERT INTO drawings (user_id, title, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("is", $userId, $title);
$stmt->execute();

if ($stmt->affected_rows > 0) {
  // New board created successfully, get inserted id
  $newBoardId = $stmt->insert_id;
  $stmt->close();

  // Redirect directly to board.php with this id
  header("Location: board.php?id=" . intval($newBoardId));
  exit();
} else {
  // Something went wrong, redirect back with error
  $stmt->close();
  header("Location: create_drawing.php?error=1");
  exit();
}
