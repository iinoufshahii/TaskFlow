<?php
session_start();  // Start the session

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {  // Correct the session variable to 'user_id'
    header("Location: login.php");  // Redirect to login if not logged in
    exit();
}

include 'TaskFlowDB.php';  // Database connection

$user_id = $_SESSION['user_id'];  // Retrieve the user_id from session
$username = $_SESSION['username'];  // Retrieve the username from session (if needed)
ini_set('display_errors', 1);

// Restore Tasks
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["restore_task_ids"])) {
    $taskIds = explode(",", $_POST["restore_task_ids"]);
    $taskIds = array_map('intval', $taskIds); // Convert IDs to integers

    if (!empty($taskIds)) {
        $taskIdsString = implode(",", $taskIds);

        // Prepare restore query
        $sqlRestore = "INSERT INTO tasks (task_name, category, due_date, priority, status, description, users_id)
                       SELECT task_name, category, due_date, priority, status, description, users_id 
                       FROM archives WHERE id IN ($taskIdsString) AND users_id = ?";
        $stmtRestore = $conn->prepare($sqlRestore);
        $stmtRestore->bind_param("i", $user_id);

        if ($stmtRestore->execute()) {
            // Delete from archives
            $sqlDelete = "DELETE FROM archives WHERE id IN ($taskIdsString) AND users_id = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("i", $user_id);
            if ($stmtDelete->execute()) {
                echo json_encode(["status" => "success", "message" => "Task(s) restored successfully."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to delete tasks from archives."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to restore tasks."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid task IDs."]);
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Tasks</title>
    <link rel="stylesheet" href="ArchivedTasks.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Main Content -->
<div class="content">
    <div class="tasks-container">
        <div class="tasks-title">Archived Tasks</div>
        <div class="tasks-line"></div>
    </div>

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
        <a href="Dashboard.php" class="sidebar-menu-item">
            <i class="fas fa-home"></i> Home
        </a>
        <a href="Calendar.php" class="sidebar-menu-item">
            <i class="fas fa-calendar-alt"></i> Calendar
        </a>
        <a href="Tasks.php" class="sidebar-menu-item">
            <i class="fas fa-tasks"></i> Tasks
        </a>
        <a href="ArchivedTasks.php" class="sidebar-menu-item selected">
            <i class="fas fa-box-archive"></i> Archived Tasks
        </a>
        <a href="Notifications.php" class="sidebar-menu-item">
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

<!-- Task Buttons -->
<div class="task-buttons">
    <button type="button" class="deletetaskbutton" onclick="deleteSelectedTasks()">
        <i class="fas fa-trash-alt"></i> Delete Task
    </button>

    <button type="button" class="button Move-button" onclick="restoreSelectedTasks()">
        <i class="fas fa-box-archive"></i> Move Task
    </button>
</div>

<script>
function restoreSelectedTasks() {
    let selectedTasks = document.querySelectorAll('.task-checkbox:checked');

    if (selectedTasks.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No Task Selected",
            text: "Please select at least one task to restore."
        });
        return;
    }

    Swal.fire({
        icon: "question",
        title: "Restore Tasks?",
        text: "Are you sure you want to restore the selected task(s)?",
        showCancelButton: true,
        confirmButtonText: "Yes, Restore",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            let taskIds = Array.from(selectedTasks).map(cb => cb.value).join(",");

            fetch("ArchivedTasks.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `restore_task_ids=${taskIds}`
            })
            .then(response => response.json())  // Parse the JSON response
            .then(data => {
                // Debugging the response
                console.log("Response Data:", data);  // Inspect the response to check its format

                // Ensure response has a valid 'status' and 'message'
                if (data.status === "success") {
                    selectedTasks.forEach(cb => document.getElementById("task-" + cb.value).remove());
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: data.message || "Task(s) restored successfully."
                    });
                } else {
                    // Display the server's error message
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: data.message || "Failed to restore tasks."
                    });
                }
            })
            .catch(error => {
                console.error("Error occurred while restoring tasks:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "An error occurred while restoring tasks."
                });
            });
        }
    });
}



function deleteSelectedTasks() {
    let selectedTasks = document.querySelectorAll('.task-checkbox:checked');

    if (selectedTasks.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No Task Selected",
            text: "Please select a task to delete."
        });
        return;
    }

    let taskIds = Array.from(selectedTasks).map(cb => cb.value).join(",");

    Swal.fire({
        title: "Are you sure?",
        text: "Deleted tasks cannot be recovered!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, Delete"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("DelTask.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "task_ids=" + taskIds
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    selectedTasks.forEach(cb => document.getElementById("task-" + cb.value).remove());
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: "Task(s) deleted successfully."
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Failed to delete tasks."
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "An error occurred while deleting tasks."
                });
            });
        }
    });
}
</script>

<div class="tasks-wrapper">
    <table class="tasks-table">
        <thead>
            <tr>
                <th></th>
                <th>Task</th>
                <th>Category</th>
                <th>Due Date</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Ensure that we get only the logged-in user's archived tasks
            $user_id = $_SESSION['user_id']; // Make sure we have the logged-in user's ID

            // Query to fetch only the logged-in user's archived tasks
            $sql = "SELECT * FROM archives WHERE users_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id); // Bind the user's ID
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Assign priority colors
                    $priorityColor = "";
                    switch ($row['priority']) {
                        case "High":
                            $priorityColor = "priority-high";
                            break;
                        case "Medium":
                            $priorityColor = "priority-medium";
                            break;
                        case "Low":
                            $priorityColor = "priority-low";
                            break;
                    }

                    // Assign status colors
                    $statusColor = "";
                    switch ($row['status']) {
                        case "Pending":
                            $statusColor = "status-pending";
                            break;
                        case "In Progress":
                            $statusColor = "status-inprogress";
                            break;
                        case "Completed":
                            $statusColor = "status-completed";
                            break;
                    }

                    echo "<tr id='task-{$row['id']}'>
                    <td><input type='checkbox' class='task-checkbox' value='{$row['id']}'></td>
                    <td>{$row['task_name']}</td>
                    <td>{$row['category']}</td>
                    <td>{$row['due_date']}</td>
                    <td><span class='$priorityColor'>{$row['priority']}</span></td>
                    <td><span class='$statusColor'>{$row['status']}</span></td>
                    <td>{$row['description']}</td>
                  </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No archived tasks found.</td></tr>";
            }

            $stmt->close();
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
