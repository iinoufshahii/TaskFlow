<?php
session_start();
include 'TaskFlowDB.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "User not logged in."]));
}

$user_id = $_SESSION['user_id'];

// Fetch notifications for the logged-in user
$sql = "SELECT id, title, message, created_at FROM notifications WHERE users_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="Notifications.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>


<!-- Main Content -->
<div class="content">
    <div class="tasks-container">
        <div class="tasks-title">Notifications</div>
        <div class="tasks-line"></div>
    </div>

    <button class="logout-btn" onclick="window.location.href='logout.php'">
        <i class="fas fa-sign-out-alt"></i> 
    </button>

    <!-- Notification Box -->
    <div class="Noti-rectangle">
        <h2><i class="fas fa-bell"></i> Notifications</h2>
        <div class="notification-list">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item" id="noti-<?= $notification['id']; ?>">
                        <span class="notification-text">
                            📢 <strong><?= htmlspecialchars($notification["title"]); ?></strong><br>
                            <small><?= htmlspecialchars($notification["message"]); ?></small>
                        </span>
                        <button class="close-btn" onclick="removeNotification(<?= $notification['id']; ?>)">❌</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="notification-text">No new notifications found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>

// Remove notification
function removeNotification(notificationId) {
    fetch('DelNoti.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${notificationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById(`noti-${notificationId}`).remove();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message
            });
        }
    })
    .catch(error => console.error("Error:", error));
}
</script>

<!-- Sidebar -->
<div class="menu-container">
    <div class="logo">
        <img src="images/logo.png" alt="TaskFlow Logo" class="image">
    </div>

    <hr class="line">

    <div class="sidebar-menu">
        <a href="Dashboard.php" class="sidebar-menu-item">
            <i class="fas fa-home"></i> Home
        </a>
        <a href="Calendar.php" class="sidebar-menu-item">
            <i class="fas fa-calendar-alt"></i> Calendar
        </a>
        <a href="Tasks.php" class="sidebar-menu-item">
            <i class="fas fa-tasks"></i> Tasks
        </a>
        <a href="ArchivedTasks.php" class="sidebar-menu-item">
            <i class="fas fa-box-archive"></i> Archived Tasks
        </a>
        <a href="Notifications.php" class="sidebar-menu-item selected">
            <i class="fas fa-bell"></i> Notifications
        </a>
        <a href="Timer.php" class="sidebar-menu-item">
            <i class="fas fa-clock"></i> Timer
        </a>
        <a href="Settings.php" class="sidebar-menu-item">
            <i class="fas fa-cog"></i> Settings
        </a>
    </div>

    <hr class="line-bottom">

    <div class="footer-text">
        TaskFlow © 2025. All rights reserved.<br>
        Made with ❤️ by Nouf.<br>
        Fictional University Project
    </div>
</div>
<meta http-equiv="refresh" content="10">
</body>
</html>
