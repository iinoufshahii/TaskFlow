<?php
session_start();
include 'TaskFlowDB.php';

if (!isset($_SESSION['user_id'])) {
    exit("User not logged in");
}

$users_id = $_SESSION['user_id'];  // Corrected column name
$current_date = date("Y-m-d");
$tomorrow = date("Y-m-d", strtotime("+1 day"));
$one_week_later = date("Y-m-d", strtotime("+7 days"));

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get tasks that are due today, tomorrow, in 1 week, or overdue
$sql = "SELECT id, task_name, due_date FROM tasks WHERE users_id = ? 
        AND (due_date = ? OR due_date = ? OR due_date = ? OR due_date < ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("issss", $users_id, $current_date, $tomorrow, $one_week_later, $current_date);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $task_name = $row["task_name"];
    $due_date = $row["due_date"];

    // Determine notification message
    if ($due_date == $current_date) {
        $message = "Your task '$task_name' is due today! ⏳";
    } elseif ($due_date == $tomorrow) {
        $message = "Reminder: Your task '$task_name' is due tomorrow. 📅";
    } elseif ($due_date < $current_date) {
        $message = "Your task '$task_name' is overdue! ❗";
    } elseif ($due_date == $one_week_later) {
        $message = "Reminder: Your task '$task_name' is due in one week. 🗓️";
    }

    // Prevent duplicate notifications
    $check_sql = "SELECT id FROM notifications WHERE users_id = ? AND message = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        die("Check Prepare failed: " . $conn->error);
    }

    $check_stmt->bind_param("is", $users_id, $message);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows == 0) {
        // Insert notification (Fixed column names and query structure)
        $insert_sql = "INSERT INTO notifications (title, message, created_at, users_id) VALUES ('Task Reminder', ?, NOW(), ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        if (!$insert_stmt) {
            die("Insert Prepare failed: " . $conn->error);
        }

        $insert_stmt->bind_param("si", $message, $users_id);

        if (!$insert_stmt->execute()) {
            die("Error inserting notification: " . $insert_stmt->error);
        } else {
            echo "Notification added: $message <br>";  // Debugging output
        }

        $insert_stmt->close();
    } else {
        echo "Notification already exists: $message <br>";  // Debugging output
    }

    $check_stmt->close();
}

$stmt->close();
$conn->close();
?>
