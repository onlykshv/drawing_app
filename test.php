<?php
include 'db.php';

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  echo "Users:<br>";
  while($row = $result->fetch_assoc()) {
    echo "ID: " . $row["id"] . " - Username: " . $row["username"] . "<br>";
  }
} else {
  echo "No users found.";
}

$conn->close();
?>
