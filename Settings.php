<?php
session_start();
include 'TaskFlowDB.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close(); 

if (!$user) {
    header("Location: login.php");
    exit();
}

$update_message = ""; 

// Handle form submission (updating user details)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_details'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($email)) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $email, $user_id);
        }

        if ($stmt->execute()) {
            $update_message = "success";
        } else {
            $update_message = "error";
        }
        $stmt->close();
    } else {
        $update_message = "empty_fields";
    }
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        session_destroy(); // Logout user

        // Redirect immediately to register page
        header("Location: register.php");
        exit();
    } else {
        $update_message = "delete_error";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="Settings.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Main Content -->
<div class="content">
    <div class="tasks-container">
        <div class="tasks-title">Settings</div>
        <div class="tasks-line"></div>
    </div>
    
    <!-- Logout button -->
    <button class="logout-btn" onclick="window.location.href='logout.php'">
        <i class="fas fa-sign-out-alt"></i> 
    </button>

    <!-- Sidebar -->
    <div class="menu-container">
        <div class="logo">
            <img src="images/logo.png" alt="TaskFlow Logo" class="image">
        </div>

        <hr class="line">

        <div class="sidebar-menu">
            <a href="Dashboard.php" class="sidebar-menu-item"><i class="fas fa-home"></i> Home</a>
            <a href="Calendar.php" class="sidebar-menu-item"><i class="fas fa-calendar-alt"></i> Calendar</a>
            <a href="Tasks.php" class="sidebar-menu-item"><i class="fas fa-tasks"></i> Tasks</a>
            <a href="ArchivedTasks.php" class="sidebar-menu-item"><i class="fas fa-box-archive"></i> Archived Tasks</a>
            <a href="Notifications.php" class="sidebar-menu-item"><i class="fas fa-bell"></i> Notifications</a>
            <a href="Timer.php" class="sidebar-menu-item"><i class="fas fa-clock"></i> Timer</a>
            <a href="Settings.php" class="sidebar-menu-item selected"><i class="fas fa-cog"></i> Settings</a>
        </div>

        <hr class="line-bottom">

        <div class="footer-text">
            TaskFlow © 2025. All rights reserved.<br>
            Made with ❤️ by Nouf.<br>
            Fictional University Project
        </div>
    </div>

    <div class="container-wrapper">
        <div class="container-box">
            <h2 class="container-heading">User Details</h2>
            <form class="user-details-form" method="POST">
                <label for="name">Username</label>
                <input type="text" id="name" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label for="password">New Password (leave blank to keep current password)</label>
                <input type="password" id="password" name="password" placeholder="Enter new password">

                <button type="submit" name="update_details">Save Details</button>
            </form>

            <!-- Delete Account Button -->
            <form method="POST" id="deleteAccountForm">
                <input type="hidden" name="delete_account" value="1">
                <button type="button" id="deleteAccountBtn" class="delete-account-btn">Delete Account</button>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert Popups -->
<script>
    <?php if ($update_message == "success") { ?>
        Swal.fire({
            icon: 'success',
            title: 'Profile Updated!',
            text: 'Your details have been successfully updated.',
            confirmButtonColor: '#3085d6',
        }).then(() => {
            window.location.href = 'Settings.php';
        });
    <?php } elseif ($update_message == "error") { ?>
        Swal.fire({
            icon: 'error',
            title: 'Update Failed!',
            text: 'An error occurred while updating your details.',
            confirmButtonColor: '#d33',
        });
    <?php } elseif ($update_message == "empty_fields") { ?>
        Swal.fire({
            icon: 'warning',
            title: 'Fields Required!',
            text: 'Please fill in all fields before submitting.',
            confirmButtonColor: '#f39c12',
        });
    <?php } ?>

    // Confirm delete account
    document.getElementById('deleteAccountBtn').addEventListener('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteAccountForm').submit();
            }
        });
    });
</script>

</body>
</html>
