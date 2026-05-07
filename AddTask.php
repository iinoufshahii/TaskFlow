<?php
session_start();  // Start the session

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login if not logged in
    exit();
}

include 'TaskFlowDB.php';  // Database connection

$user_id = $_SESSION['user_id'];  // Retrieve the user_id from session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['task_name']) && !empty($_POST['task_name'])) {
        $task_name = trim($_POST['task_name']);
        $category = trim($_POST['category']);
        $due_date = trim($_POST['due_date']);
        $priority = trim($_POST['priority']);
        $status = trim($_POST['status']);
        $description = trim($_POST['description']);

        // Add user_id to the insert query
        $sql = "INSERT INTO tasks (task_name, category, due_date, priority, status, description, users_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $task_name, $category, $due_date, $priority, $status, $description, $user_id);

        if ($stmt->execute()) {
            // Pass task data to JavaScript
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Task Added Successfully!',
                        html: ` 
                            <p><strong>Task Name:</strong> " . htmlspecialchars($task_name) . "</p>
                            <p><strong>Description:</strong> " . htmlspecialchars($description) . "</p>
                            <p><strong>Due Date:</strong> " . htmlspecialchars($due_date) . "</p>
                            <p><strong>Priority:</strong> " . htmlspecialchars($priority) . "</p>
                            <p><strong>Status:</strong> " . htmlspecialchars($status) . "</p>
                            <p><strong>Category:</strong> " . htmlspecialchars($category) . "</p>
                        `,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'Tasks.php';
                    });
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Could not add task. Please try again.'
                });
            </script>";
        }

        $stmt->close();
    } else {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information!',
                text: 'Task Name is required!'
            });
        </script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="AddTask.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="task-container">
    <h2>Add Task</h2>
    <form method="POST" action="AddTask.php">
        <div class="form-group">
            <label for="task_name">Task Name</label>
            <input type="text" name="task_name" id="task_name" required>
        </div>

        <div class="form-group">
            <label for="description">Task Description</label>
            <textarea name="description" id="description" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" name="due_date" id="due_date" required>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" name="category" id="category" placeholder="e.g. Work, Personal">
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select name="priority" id="priority" required>
                <option value="">Select Priority</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="">Select Status</option>
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>
        </div>

        <button type="submit">Add Task</button>
    </form>

    <p class="back-link">
        <a href="Tasks.php">Back to Tasks</a>
    </p>
</div>

</body>
</html>

