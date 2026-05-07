<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging Out...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<script>
    Swal.fire({
        icon: "success",
        title: "Logged Out",
        text: "You have been logged out successfully.",
        showConfirmButton: false,
        timer: 2000
    }).then(() => {
        window.location.href = "login.php";
    });
</script>
</body>
</html>

