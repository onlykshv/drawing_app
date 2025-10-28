<?php
session_start();
include 'db.php';
$user_id = 1; // Replace with $_SESSION['user_id'] after session mgmt

$sql = "SELECT * FROM drawings WHERE user_id=$user_id";
$result = $conn->query($sql);

echo "<h2>My Drawing Boards</h2>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Board: " . htmlspecialchars($row['title']) . " | <a href='board.php?id=" . $row['id'] . "'>Open</a><br>";
    }
} else {
    echo "No boards found.";
}
?>
