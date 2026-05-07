<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "1234";
$database = "taskflow";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assume session is started, and the user is logged in with their user_id in the session
session_start();
$user_id = $_SESSION['user_id']; // Get the logged-in user's ID from the session

// Fetch task details from database
$taskData = [];
if (isset($_GET['task_id']) && !empty($_GET['task_id'])) {
    $task_id = $_GET['task_id'];

    // Prepare the SQL query to fetch the task for the logged-in user
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND users_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id); // Bind task ID and user ID
    $stmt->execute();
    $result = $stmt->get_result();
    $taskData = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission to update task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];
    $task_name = trim($_POST['task_name']);
    $category = trim($_POST['category']);
    $due_date = trim($_POST['due_date']);
    $priority = trim($_POST['priority']);
    $status = trim($_POST['status']);
    $description = trim($_POST['description']);

    // Prepare the SQL query to update the task for the logged-in user
    $sql = "UPDATE tasks SET task_name=?, category=?, due_date=?, priority=?, status=?, description=? WHERE id=? AND users_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssii", $task_name, $category, $due_date, $priority, $status, $description, $task_id, $user_id);

    if ($stmt->execute()) {
        header("Location: Tasks.php?updated=1");
        exit();
    } else {
        echo "Error updating task: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
    <link rel="stylesheet" href="EditTasks.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="task-container">
    <h2>Edit Task</h2>
    <form method="POST" action="EditTasks.php">
        <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($_GET['task_id'] ?? ''); ?>">

        <div class="form-group">
            <label for="task_name">Task Name</label>
            <input type="text" name="task_name" id="task_name" value="<?php echo htmlspecialchars($taskData['task_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Task Description</label>
            <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($taskData['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" name="due_date" id="due_date" value="<?php echo htmlspecialchars($taskData['due_date'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" name="category" id="category" value="<?php echo htmlspecialchars($taskData['category'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select name="priority" id="priority" required>
                <option value="High" <?php echo (isset($taskData['priority']) && $taskData['priority'] == "High") ? "selected" : ""; ?>>High</option>
                <option value="Medium" <?php echo (isset($taskData['priority']) && $taskData['priority'] == "Medium") ? "selected" : ""; ?>>Medium</option>
                <option value="Low" <?php echo (isset($taskData['priority']) && $taskData['priority'] == "Low") ? "selected" : ""; ?>>Low</option>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="Pending" <?php echo (isset($taskData['status']) && $taskData['status'] == "Pending") ? "selected" : ""; ?>>Pending</option>
                <option value="In Progress" <?php echo (isset($taskData['status']) && $taskData['status'] == "In Progress") ? "selected" : ""; ?>>In Progress</option>
                <option value="Completed" <?php echo (isset($taskData['status']) && $taskData['status'] == "Completed") ? "selected" : ""; ?>>Completed</option>
            </select>
        </div>

        <button type="submit">Update Task</button>
    </form>

    <p class="back-link">
        <a href="Tasks.php">Back to Tasks</a>
    </p>
</div>

</body>
</html>
