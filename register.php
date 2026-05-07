<?php
session_start();
include 'TaskFlowDB.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters!";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username or Email already exists!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Correcting the field name: Insert into `id`, NOT `users_id`
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Get the last inserted user ID
                $last_id = $stmt->insert_id;

                // Store session variables
                $_SESSION["user_id"] = $last_id;
                $_SESSION["username"] = $username;
                $_SESSION["email"] = $email;

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Error registering user!";
            }
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - TaskFlow</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="signup-container">
        <h2>Welcome.</h2>
        <p>Create an account</p>

        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <form action="" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter Username" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="example.email@gmail.com" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Sign Up</button>
        </form>

        <p class="login-link">Already have an account? <a href="login.php">Sign in</a></p>
    </div>
</body>
</html>
