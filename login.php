<?php
session_start();
include 'TaskFlowDB.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Prepare SQL to fetch user
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Store user data in session
                $_SESSION['user_id'] = $user['id']; // Make sure to use correct session variable name
                $_SESSION['username'] = $user['username'];
                
                // Redirect to user dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - TaskFlow</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>Welcome Back.</h2>
        <p>Login to your account</p>

        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($_SESSION["success"])) {
            echo "<p class='success'>" . $_SESSION["success"] . "</p>";
            unset($_SESSION["success"]);
        } ?>

        <form action="" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter Username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required>

            <button type="submit">Login</button>
        </form>
         <p class="register-link">Don't have an account? <a href="register.php">Register</a></p>
         
</body>
</html>
