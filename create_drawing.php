<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $user_id = 1; // Replace with $_SESSION['user_id'] after login system integration
    $sql = "INSERT INTO drawings (user_id, title) VALUES ($user_id, '$title')";
    if ($conn->query($sql)) {
        echo "Drawing board created! <a href='dashboard.php'>Back to dashboard</a>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<form method="POST" action="">
    <label>Board Title: <input type="text" name="title" required></label><br>
    <input type="submit" value="Create">
</form>
