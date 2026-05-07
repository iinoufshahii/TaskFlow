<?php
session_start();  // Start the session

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login if not logged in
    exit();
}

include 'TaskFlowDB.php';  // Database connection

$user_id = $_SESSION['user_id'];  // Retrieve user ID from session

// Always fetch the latest username from the database
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $_SESSION['username'] = $row['username'];  // Always update session with the latest username
}

$username = $_SESSION['username'];  // Assign the updated username

// Fetch user's notes (only their own)
$stmt_notes = $conn->prepare("SELECT * FROM notes WHERE users_id = ?");
$stmt_notes->bind_param("i", $user_id);
$stmt_notes->execute();
$result_notes = $stmt_notes->get_result();
$notes = $result_notes->fetch_all(MYSQLI_ASSOC);

// Handle task actions (add, delete, update)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    if ($action == "add" && isset($_POST["note_text"])) {
        $note_text = mysqli_real_escape_string($conn, $_POST["note_text"]);
        $query = "INSERT INTO notes (note_text, users_id) VALUES ('$note_text', '$user_id')";
        mysqli_query($conn, $query);
        exit();
    }

    if ($action == "delete" && isset($_POST["id"])) {
        $id = intval($_POST["id"]);
        $query = "DELETE FROM notes WHERE id = $id AND users_id = $user_id";
        mysqli_query($conn, $query);
        exit();
    }

    if ($action == "update" && isset($_POST["id"]) && isset($_POST["note_text"])) {
        $id = intval($_POST["id"]);
        $note_text = mysqli_real_escape_string($conn, $_POST["note_text"]);
        $query = "UPDATE notes SET note_text = '$note_text' WHERE id = $id AND users_id = $user_id";
        mysqli_query($conn, $query);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.0.0/moment.min.js"></script>
</head>
<body>


<!--Logout button-->
<button class="logout-btn" onclick="window.location.href='logout.php'">
    <i class="fas fa-sign-out-alt"></i> 
</button>


<!-- Home and homeline -->
<div class="home-container">
    <div class="home-title">Hello, <?= htmlspecialchars($username); ?> 👋</div>
    <div class="home-line"></div>
</div>

<div class="gif-container">
    <img src="https://media3.giphy.com/media/LpiVeIRgrqVsZJpM5H/giphy.gif?cid=ecf05e47wyxpsjgiqkpce10l1irsfqxkci7caqe589ppc440&rid=giphy.gif&ct=s" 
         alt="Animated GIF">
</div>

<!-- clock Container -->
<div class="rectangle">
<div id="clock" class="light">
        <div class="display">
            <div class="weekdays"></div>
            <div class="ampm"></div>
            <div class="alarm"></div>
            <div class="digits"></div>
        </div>
    </div>


    <div class="todo-container">
    <h2 class="todo-header">Notes</h2>
    <input type="text" id="newTaskInput" placeholder="Enter a new note">
    <button onclick="addTask()">Add</button>
    <ul>
        <?php
     
        $query = "SELECT * FROM notes WHERE users_id = $user_id ORDER BY created_at DESC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li data-id='{$row['id']}'>
                    <span class='task-text' contenteditable='true'>{$row['note_text']}</span>
                    <button onclick='deleteTask({$row['id']})'>Delete</button>
                  </li>";
        }
        ?>
    </ul>
</div>


<script>
function addTask() {
    var taskInput = document.getElementById("newTaskInput").value.trim();
    if (taskInput === "") {
        alert("Please enter a task!"); // Prevent empty input
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", window.location.href, true); // Send request to dashboard.php
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            console.log("Server response:", xhr.responseText); // Debugging
            if (xhr.status == 200) {
                location.reload(); // Refresh the page to show the new task
            } else {
                alert("Error adding task: " + xhr.responseText);
            }
        }
    };
    xhr.send("action=add&note_text=" + encodeURIComponent(taskInput));
}

function deleteTask(id) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", window.location.href, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            location.reload();
        }
    };
    xhr.send("action=delete&id=" + id);
}

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".task-text").forEach(task => {
        task.addEventListener("blur", function() {
            var id = this.parentElement.getAttribute("data-id");
            var updatedText = this.innerText.trim();
            var xhr = new XMLHttpRequest();
            xhr.open("POST", window.location.href, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("action=update&id=" + id + "&note_text=" + encodeURIComponent(updatedText));
        });
    });
});
</script>


    <script>
        function updateClock() {
            let now = new Date();
            let h = now.getHours();
            let m = now.getMinutes();
            let s = now.getSeconds();
            let ampm = h >= 12 ? "PM" : "AM";

            h = h % 12 || 12; // Convert to 12-hour format
            m = m < 10 ? "0" + m : m;
            s = s < 10 ? "0" + s : s;

            $(".digits").html(h + ":" + m + ":" + s);
            $(".ampm").html(ampm);

            let weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            $(".weekdays").html(weekdays[now.getDay()]);
        }

    

        setInterval(updateClock, 1000);
        updateClock();
    </script>

<meta http-equiv="refresh" content="60">



<!-- Sidebar -->
<div class="menu-container">
    <div class="logo">
        <img src="images/logo.png" alt="TaskFlow Logo" class="image">
    </div>

    <hr class="line">


    <!-- Sidebar Menu -->
    <div class="sidebar-menu">
    <a href="Dashboard.php" class="sidebar-menu-item selected">
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
    <a href="Notifications.php" class="sidebar-menu-item">
        <i class="fas fa-bell"></i> Notification
    </a>
    </a>
    <a href="Timer.php" class="sidebar-menu-item">
        <i class="fas fa-clock"></i> Timer
    </a>
    <a href="Settings.php" class="sidebar-menu-item">
        <i class="fas fa-cog"></i> Settings
    </a>
</div>


    <!-- Additional Line -->
    <hr class="line-bottom">

    <!-- Footer Text -->
    <div class="text">
        TaskFlow © 2025. All rights reserved.<br>
        Made with ❤️ by Nouf.<br>
        Fictional University Project
    </div>
</div>

</body>
</html>
