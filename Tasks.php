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


// Handle Task Archiving Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["task_ids"])) {
    $taskIds = explode(",", $_POST["task_ids"]);
    $taskIds = array_map('intval', $taskIds); // Convert IDs to integers (prevents SQL injection)

    if (!empty($taskIds)) {
        $taskIdsString = implode(",", $taskIds);

        // Begin the transaction
        $conn->begin_transaction();

        try {
            // Move tasks to archives table (including the user's ID)
            $sqlInsert = "INSERT INTO archives (task_name, category, due_date, priority, status, description, users_id)
                          SELECT task_name, category, due_date, priority, status, description, users_id 
                          FROM tasks 
                          WHERE id IN ($taskIdsString) AND users_id = $user_id";

            if ($conn->query($sqlInsert) === TRUE) {
                // Check if rows were inserted
                if ($conn->affected_rows > 0) {
                    // Delete archived tasks from tasks table
                    $sqlDelete = "DELETE FROM tasks WHERE id IN ($taskIdsString) AND users_id = $user_id";
                    if ($conn->query($sqlDelete) === TRUE) {
                        $conn->commit();
                        echo json_encode(["status" => "success", "message" => "Task(s) archived successfully!"]);
                    } else {
                        $conn->rollback();
                        echo json_encode(["status" => "error", "message" => "Error deleting tasks: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "No tasks were archived. Check if they exist."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Error archiving tasks: " . $conn->error]);
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "An error occurred: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No valid task IDs provided."]);
    }
    exit; // Stop further execution to prevent sending HTML content in JSON response
}





// Base SQL query to fetch tasks for the logged-in user
$sql = "SELECT * FROM tasks WHERE users_id = ?";

// Prepare and bind parameters to avoid SQL injection
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

// Apply Filters (if selected)
if (!empty($_POST["category"])) {
    $category = $conn->real_escape_string($_POST["category"]);
    $sql .= " AND category = '$category'";
}

if (!empty($_POST["priority"])) {
    $priority = $conn->real_escape_string($_POST["priority"]);
    $sql .= " AND priority = '$priority'";
}

if (!empty($_POST["status"])) {
    $status = $conn->real_escape_string($_POST["status"]);
    $sql .= " AND status = '$status'";
}

// Apply Sorting (if selected)
if (!empty($_POST["sort_by"])) {
    $sort_option = $_POST["sort_by"];
    $allowed_sorts = [
        "due_date ASC", "due_date DESC",
        "priority ASC", "priority DESC",
        "status ASC", "status DESC"
    ];

    if (in_array($sort_option, $allowed_sorts)) {
        $sql .= " ORDER BY $sort_option";
    }
} else {
    $sql .= " ORDER BY due_date ASC"; // Default sorting
}

// Execute the final query to fetch tasks
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks | TaskFlow</title>
    <link rel="stylesheet" href="Tasks.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>

<!-- Main Content -->
<div class="content">
    <!-- Tasks Title & Line -->
    <div class="tasks-container">
        <div class="tasks-title">Tasks</div>
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
        <a href="Tasks.php" class="sidebar-menu-item selected">
            <i class="fas fa-tasks"></i> Tasks
        </a>
        <a href="ArchivedTasks.php" class="sidebar-menu-item">
            <i class="fas fa-box-archive"></i> Archived Tasks
        </a>
        <a href="Notifications.php" class="sidebar-menu-item">
            <i class="fas fa-bell"></i> Notifications
        </a>
        <a href="Timer.php" class="sidebar-menu-item ">
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
        <button class="addtaskbutton" onclick="window.location.href='AddTask.php'">
            <i class="fas fa-plus"></i> Add Task
        </button>

        <button class="edittaskbutton" onclick="editSelectedTasks()">
            <i class="fas fa-edit"></i> Edit Task
        </button>

        <button type="button" class="deletetaskbutton" onclick="deleteSelectedTasks()">
            <i class="fas fa-trash-alt"></i> Delete Task
        </button>

        <button type="button" class="button archive-button" onclick="archiveSelectedTasks()">
        <i class="fas fa-box-archive"></i> Archive Task
    </button>
    </div>

    <script>
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
        title: "Delete Task",
        text: "Are you sure you want to delete the selected task(s)?",
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
                body: `task_ids=${taskIds}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    selectedTasks.forEach(task => {
                        document.getElementById("task-" + task.value).remove();
                    });

                    Swal.fire({
                        icon: "success",
                        title: "Tasks Deleted",
                        text: "The selected task(s) have been deleted successfully."
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "An error occurred while deleting the tasks. Please try again."
                });
            });
        }
    });
}



    function editSelectedTasks() {
    let selectedTasks = document.querySelectorAll('.task-checkbox:checked');

    if (selectedTasks.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No Task Selected",
            text: "Please select a task to edit."
        });
        return;
    }

    if (selectedTasks.length > 1) {
        Swal.fire({
            icon: "warning",
            title: "Multiple Tasks Selected",
            text: "Please select only one task to edit."
        });
        return;
    }

    let taskId = selectedTasks[0].value;

    // Confirmation before redirection
    Swal.fire({
        title: "Edit Task",
        text: "Do you want to edit the selected task?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Edit"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "EditTasks.php?task_id=" + taskId;
        }
    });
}

function archiveSelectedTasks() {
    let selectedTasks = document.querySelectorAll('.task-checkbox:checked');
    
    if (selectedTasks.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No Tasks Selected",
            text: "Please select at least one task to archive."
        });
        return;
    }

    let taskIds = Array.from(selectedTasks).map(cb => cb.value).join(",");

    // Confirmation popup before archiving
    Swal.fire({
        title: "Are you sure?",
        text: "The selected task(s) will be archived.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, archive them!"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("Tasks.php", {
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
                        title: "Archived!",
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "An error occurred while archiving tasks. Please try again."
                });
            });
        }
    });
}

</script>

    <!-- Search Bar -->
    <div class="header">
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search tasks...">
            <svg class="search-icon" viewBox="0 0 24 24">
                <path d="M23 20.59l-5.81-5.81a9 9 0 1 0-2.42 2.42L20.59 23zM4 10a6 6 0 1 1 6 6 6 6 0 0 1-6-6z"></path>
            </svg>
        </div>
    </div>

    <!-- Tasks Table -->
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
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
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
                echo "<tr><td colspan='7'>No tasks found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>



<script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let searchText = this.value.toLowerCase();
        document.querySelectorAll(".tasks-table tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(searchText) ? "" : "none";
        });
    });
</script>

<div class="sort-filter-container">
    <!-- Sort Dropdown -->
    <div class="dropdown">
        <button class="dropdown-button" onclick="toggleDropdown('sortDropdown')">
            Sort <i class="fas fa-sort"></i>
        </button>
        <div id="sortDropdown" class="dropdown-content">
            <form method="POST" action="Tasks.php">
                <select name="sort_by" onchange="this.form.submit()">
                    <option value="due_date ASC">Due Date (Earliest)</option>
                    <option value="due_date DESC">Due Date (Latest)</option>
                    <option value="priority ASC">Priority (Low to High)</option>
                    <option value="priority DESC">Priority (High to Low)</option>
                    <option value="status ASC">Status (A-Z)</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Filter Dropdown -->
    <div class="dropdown">
        <button class="dropdown-button" onclick="toggleDropdown('filterDropdown')">
            Filter <i class="fas fa-filter"></i>
        </button>
        <div id="filterDropdown" class="dropdown-content">
            <form method="POST" action="Tasks.php">

                <label>Priority:</label>
                <select name="priority">
                    <option value="">All</option>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>

                <label>Status:</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>

                <button type="submit">Apply</button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleDropdown(id) {
        document.getElementById(id).classList.toggle("show");
    }
</script>

<meta http-equiv="refresh" content="60">

</body>
</html>
