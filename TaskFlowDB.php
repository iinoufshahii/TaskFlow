<?php
$servername = "localhost"; // Change if needed
$username = "root";
$password = "1234";
$database = "taskflow";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 


?>
