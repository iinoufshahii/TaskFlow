<?php
session_start();
include 'TaskFlowDB.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $notification_id = intval($_POST['id']);
    $user_id = $_SESSION['user_id'];

    // Check if the notification exists for the logged-in user
    $check_sql = "SELECT id FROM notifications WHERE id = ? AND users_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $notification_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete the notification
        $delete_sql = "DELETE FROM notifications WHERE id = ? AND users_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $notification_id, $user_id);

        if ($delete_stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Notification deleted."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete notification."]);
        }
        $delete_stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Notification not found."]);
    }

    $check_stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
