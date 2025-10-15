<?php
session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_bookings.php");
    exit();
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // You can store these in the database instead for better security
    $valid_username = "admin";
    $valid_password = "12345"; // Replace with a stronger password!

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION["admin_logged_in"] = true;
        header("Location: admin_bookings.php");
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - PrimeBuild</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 50px; }
        .login-box { width: 300px; margin: auto; background: white; padding: 20px; box-shadow: 0 0 10px #aaa; }
        h2 { text-align: center; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin: 8px 0; }
        button { width: 100%; padding: 10px; background: #ff9800; color: white; border: none; cursor: pointer; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Admin Login</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Log In</button>
    </form>
</div>

</body>
</html>
