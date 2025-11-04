<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit();
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drawing_id'])) {
  $drawingId = intval($_POST['drawing_id']);
  $userId = $_SESSION['user_id'];

  // Only allow deletion if the drawing belongs to the logged-in user
  $stmt = $conn->prepare("DELETE FROM drawings WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $drawingId, $userId);
  $stmt->execute();
  $stmt->close();
}

header("Location: my_drawings.php"); // Always redirect back to the dashboard
exit();
