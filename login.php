<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            echo "Login successful. Welcome, " . htmlspecialchars($user['username']) . "!";
            // Next: Set session, redirect to dashboard
        } else {
            echo "Invalid credentials. <a href='login.html'>Try again</a>";
        }
    } else {
        echo "No account found for this email. <a href='register.html'>Register here</a>";
    }

    $conn->close();
} else {
    echo "Invalid request.";
}
?>
