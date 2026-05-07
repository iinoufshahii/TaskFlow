<?php
session_start(); 
include 'TaskFlowDB.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$user_id = $_SESSION['user_id']; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["task_ids"])) {
    $taskIds = explode(",", $_POST["task_ids"]);
    $taskIds = array_map('intval', $taskIds); // Convert to integer for safety

    if (!empty($taskIds)) {
        $taskIdsString = implode(",", $taskIds);

        // Check if tasks exist in 'tasks' table
        $sqlCheckTasks = "SELECT id FROM tasks WHERE id IN ($taskIdsString) AND users_id = ?";
        $stmtCheckTasks = $conn->prepare($sqlCheckTasks);
        $stmtCheckTasks->bind_param("i", $user_id);
        $stmtCheckTasks->execute();
        $resultTasks = $stmtCheckTasks->get_result();

        // If tasks found in 'tasks' table, delete them
        if ($resultTasks->num_rows > 0) {
            $sqlDeleteTasks = "DELETE FROM tasks WHERE id IN ($taskIdsString) AND users_id = ?";
            $stmtDeleteTasks = $conn->prepare($sqlDeleteTasks);
            $stmtDeleteTasks->bind_param("i", $user_id);

            if ($stmtDeleteTasks->execute()) {
                echo json_encode(["status" => "success", "message" => "Task(s) deleted from tasks successfully."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error deleting tasks from tasks: " . $conn->error]);
            }

            $stmtDeleteTasks->close();
        } else {
            // If no tasks were found in 'tasks', check 'archives'
            $sqlCheckArchives = "SELECT id FROM archives WHERE id IN ($taskIdsString) AND users_id = ?";
            $stmtCheckArchives = $conn->prepare($sqlCheckArchives);
            $stmtCheckArchives->bind_param("i", $user_id);
            $stmtCheckArchives->execute();
            $resultArchives = $stmtCheckArchives->get_result();

            // If tasks found in 'archives' table, delete them
            if ($resultArchives->num_rows > 0) {
                $sqlDeleteArchives = "DELETE FROM archives WHERE id IN ($taskIdsString) AND users_id = ?";
                $stmtDeleteArchives = $conn->prepare($sqlDeleteArchives);
                $stmtDeleteArchives->bind_param("i", $user_id);

                if ($stmtDeleteArchives->execute()) {
                    echo json_encode(["status" => "success", "message" => "Task(s) deleted from archives successfully."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error deleting tasks from archives: " . $conn->error]);
                }

                $stmtDeleteArchives->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Task(s) not found in either tasks or archives."]);
            }

            $stmtCheckArchives->close();
        }

        $stmtCheckTasks->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid task IDs provided."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}

$conn->close();
?>
