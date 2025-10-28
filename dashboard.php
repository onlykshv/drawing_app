<?php
session_start();
include 'db.php';

// For now, simple session is omitted—after you add login sessions, use $_SESSION['user_id'] here
echo "<h2>Welcome to the Collaborative Drawing Dashboard</h2>";
echo "<a href='create_drawing.php'>Create New Drawing Board</a><br><br>";
echo "<a href='my_drawings.php'>View My Drawings</a><br><br>";
echo "<a href='join_board.php'>Join a Drawing Board</a><br><br>";
?>
