<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

// Get the current year
$year = date('Y');

// Allow user to set a different year via URL
if (isset($_GET['year'])) {
    $year = $_GET['year'];
}

$monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$daysOfTheWeek = ["S", "M", "T", "W", "T", "F", "S"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Year Calendar</title>
    <link rel="stylesheet" href="calendar.css">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body></body>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>TaskFlow</h2>
    <ul>
        <li><a href="dashboard.php">🏠 Home</a></li>
        <li><a href="tasks.php">📌 Tasks</a></li>
        <li><a href="calendar.php" class="active">📅 Calendar</a></li>
        <li><a href="settings.php">⚙️ Settings</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <section>
        <h1 id="year"><?php echo $year; ?></h1>
        <div class="calendar">
            <?php
            for ($month = 1; $month <= 12; $month++) {
                $firstDayOfMonth = date('w', strtotime("$year-$month-01"));
                $totalDays = date('t', strtotime("$year-$month-01"));
                echo "<div class='month'>
                        <h4>{$monthNames[$month - 1]}</h4>
                        <div class='daysOfTheWeek'>";
                
                // Print days of the week
                foreach ($daysOfTheWeek as $day) {
                    echo "<div class='day week'>$day</div>";
                }
                echo "</div><div class='days'>";

                // Print empty spaces before the first day
                for ($i = 0; $i < $firstDayOfMonth; $i++) {
                    echo "<div class='day empty'></div>";
                }

                // Print days of the month
                for ($day = 1; $day <= $totalDays; $day++) {
                    $class = ($day == date('j') && $month == date('m') && $year == date('Y')) ? "day today" : "day";
                    echo "<div class='$class'>$day</div>";
                }

                echo "</div></div>"; // Close month div
            }
            ?>
        </div>
    </section>
</div>
<!-- Logout Button -->
<button class="logout-btn" onclick="window.location.href='logout.php'">
    <i class="fas fa-sign-out-alt"></i> 
</button>

<!-- Tasks Title & Line -->
<div class="tasks-container">
    <div class="tasks-title">Calendar</div>
    <div class="tasks-line"></div>
</div>

<!-- Sidebar -->
<div class="menu-container">
    <div class="logo">
        <img src="images/logo.png" alt="TaskFlow Logo" class="image">
    </div>

    <hr class="line">

    <!-- Sidebar Menu -->
    <div class="sidebar-menu">
        <a href="Dashboard.php" class="sidebar-menu-item">
            <i class="fas fa-home"></i> Home
        </a>
        <a href="Calendar.php" class="sidebar-menu-item selected">
            <i class="fas fa-calendar-alt"></i> Calendar
        </a>
        <a href="Tasks.php" class="sidebar-menu-item ">
            <i class="fas fa-tasks"></i> Tasks
        </a>
        <a href="ArchivedTasks.php" class="sidebar-menu-item">
            <i class="fas fa-box-archive"></i> Archived Tasks
        </a>
        <a href="Notifications.php" class="sidebar-menu-item">
            <i class="fas fa-bell"></i> Notification
        </a>
    <a href="Timer.php" class="sidebar-menu-item ">
        <i class="fas fa-clock"></i> Timer
    </a>
        <a href="Settings.php" class="sidebar-menu-item">
            <i class="fas fa-cog"></i> Settings
        </a>
    </div>

    <!-- Additional Line -->
    <hr class="line-bottom">

    <!-- Footer Text -->
    <div class="footer-text">
        TaskFlow © 2025. All rights reserved.<br>
        Made with ❤️ by Nouf.<br>
        Fictional University Project
    </div>
</div>

</body>
</html>
